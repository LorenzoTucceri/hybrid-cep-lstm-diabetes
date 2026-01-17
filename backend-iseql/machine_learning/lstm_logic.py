import pandas as pd
import numpy as np
import torch
import os
from torch import nn

# ==================================================================================
# 1. CONFIGURATION
# ==================================================================================
DEFAULT_MODEL_DIR = "../data/models_opt"
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")

INT_TO_LABEL = {0: "RED", 1: "YELLOW", 2: "GREEN"}
# Internal Mapping (must match training keys, but these are internal)
TARGET_TO_INT = {"rosso": 0, "giallo": 1, "verde": 2}
STATE_ID = {"extremely_low": 0, "low": 1, "normal": 2, "high": 3, "extremely_high": 4}
NUM_STATES = 5


# ==================================================================================
# 2. NEURAL NETWORK ARCHITECTURE (MATCHING TRAINING EXACTLY)
# ==================================================================================
class AttentionBlock(nn.Module):
    def __init__(self, h_dim):
        super().__init__()
        self.att = nn.Sequential(nn.Linear(h_dim, h_dim // 2), nn.Tanh(), nn.Linear(h_dim // 2, 1))

    def forward(self, x, lens):
        sc = self.att(x)
        mask = torch.zeros_like(sc, dtype=torch.bool)
        for i, l in enumerate(lens): mask[i, :l, :] = 1
        sc = sc.masked_fill(~mask, -float('inf'))
        return torch.sum(torch.softmax(sc, dim=1) * x, dim=1)


class LSTMAttentionClassifier(nn.Module):
    def __init__(self, input_dim=18, hidden_dim=128, num_classes=3):
        super().__init__()
        self.lstm = nn.LSTM(input_dim, hidden_dim, num_layers=2, batch_first=True, bidirectional=True, dropout=0.3)
        self.attn = AttentionBlock(hidden_dim * 2)
        self.ln = nn.LayerNorm(hidden_dim * 2)
        self.fc = nn.Sequential(nn.Linear(hidden_dim * 2, 128), nn.ReLU(), nn.Dropout(0.4), nn.Linear(128, num_classes))

    def forward(self, x, l):
        packed = nn.utils.rnn.pack_padded_sequence(x, l.cpu(), batch_first=True, enforce_sorted=False)
        out, _ = self.lstm(packed)
        unpacked, _ = nn.utils.rnn.pad_packed_sequence(out, batch_first=True)
        return self.fc(self.ln(self.attn(unpacked, l)))


# ==================================================================================
# 3. FEATURE ENGINEERING (ALIGNED WITH 'build_compressed_features_v2')
# ==================================================================================
def transform_glucose_risk(g):
    g_clipped = np.clip(g, 20, 600)
    return 1.509 * (np.log(g_clipped) ** 1.084 - 5.381)


def build_features_prod(run_list):
    """
    Exact replica of build_compressed_features_v2 used in training.
    Output shape: (T, 18)
    """
    T = len(run_list)
    if T == 0: return np.zeros((1, 18))

    # Extract raw data
    states = np.array([STATE_ID.get(s, 2) for s, _, _ in run_list], dtype=int)
    durs = np.array([d for _, d, _ in run_list], dtype=float)
    gs = np.array([g for *_, g in run_list], dtype=float)

    # 1. One-Hot Encoding Current State (5 features)
    state_oh = np.eye(NUM_STATES)[states]

    # 2. One-Hot Encoding Previous State (5 features)
    prev_s = np.roll(states, 1);
    prev_s[0] = states[0]
    prev_oh = np.eye(NUM_STATES)[prev_s]

    # 3. Duration Features (2 features)
    dur_norm = np.clip(durs / 240.0, 0, 1)
    log_dur = np.log1p(durs)

    # 4. Glucose Dynamics (2 features)
    g_delta = gs - np.roll(gs, 1);
    g_delta[0] = 0
    with np.errstate(divide='ignore', invalid='ignore'):
        roc = np.nan_to_num(g_delta / durs)

    # 5. Risk & Load Metrics (4 features)
    risk = transform_glucose_risk(gs)
    load = (gs - 100) * (durs / 60.0)
    zsc = (gs - 140) / 50.0
    roll_risk = np.convolve(risk, np.ones(5) / 5, mode='same')

    # Stack: 5 + 5 + 1 + 1 + 1 + 1 + 1 + 1 + 1 + 1 = 18 Features
    feats = np.column_stack([state_oh, prev_oh, dur_norm, log_dur, zsc, g_delta, roc, risk, roll_risk, load])
    return np.nan_to_num(feats).astype(np.float32)


# ==================================================================================
# 4. INFERENCE ENGINE (CLINICAL BACKEND)
# ==================================================================================
class ClinicalEnsembleAdaptive:
    def __init__(self, model_dir_path=None):
        self.device = DEVICE
        self.models = {}
        search_path = model_dir_path if model_dir_path else DEFAULT_MODEL_DIR
        print(f"[LSTM INIT] Loading models from: {search_path}")

        # Load all available models (15, 30, 60, 90)
        for d in [15, 30, 60, 90]:
            path = os.path.join(search_path, f"model_{d}d.pth")
            if os.path.exists(path):
                try:
                    # Input dim fixed at 18 as per training
                    m = LSTMAttentionClassifier(input_dim=18).to(self.device)
                    m.load_state_dict(torch.load(path, map_location=self.device))
                    m.eval()
                    self.models[d] = m
                    print(f"   ✅ Model {d}d ready.")
                except Exception as e:
                    print(f"   ❌ Error {d}d: {e}")

    def extract_slice(self, full_sequence, days):
        target_minutes = days * 1440
        current_minutes = 0
        sliced_seq = []
        for event in reversed(full_sequence):
            state, dur, val = event
            if current_minutes + dur > target_minutes:
                rem_dur = target_minutes - current_minutes
                if rem_dur > 0: sliced_seq.insert(0, (state, int(rem_dur), val))
                break
            else:
                sliced_seq.insert(0, event)
                current_minutes += dur
        return sliced_seq

    def predict_smart(self, intervals):
        """
        Input: list of tuples (symbol, start, end, label, DURATION, AVG_GLUCOSE)
        """
        if not intervals:
            return {"status": "error", "message": "No data available", "diagnosis": "N/A"}

        # Map labels from Detector to LSTM internal labels
        state_map = {
            'extremely_high': 'extremely_high', 'high': 'high',
            'normal': 'normal', 'low': 'low', 'extremely_low': 'extremely_low',
            'hyper': 'high', 'hypo': 'low'
        }

        sequence = []

        # Parse frontend input
        for item in intervals:
            if len(item) < 6: continue
            label_raw = str(item[3]).lower().strip()
            duration_minutes = item[4]
            avg_val = item[5]

            state = 'normal'
            if label_raw in state_map:
                state = state_map[label_raw]
            else:
                for k, v in state_map.items():
                    if k in label_raw: state = v; break

            sequence.append((state, max(1, int(duration_minutes)), float(avg_val)))

        if not sequence:
            return {"status": "error", "message": "Could not build sequence", "diagnosis": "N/A"}

        # Global stats for Guardrails
        total_minutes = sum(x[1] for x in sequence)
        total_days = total_minutes / 1440.0

        if total_days > 0:
            avg_g = sum(x[1] * x[2] for x in sequence) / total_minutes
        else:
            avg_g = 100

        runnable = [d for d in [15, 30, 60, 90] if d in self.models]
        if not runnable: return {"status": "error", "message": "No models loaded", "diagnosis": "N/A"}

        probs_sum = np.zeros(3)
        active_weight = 0
        single_preds = {}

        # --- ENSEMBLE LOGIC (Aligned with Stress Test) ---
        # --- ENSEMBLE LOGIC (With Per-Model Debug Prints) ---
        print(f"\n--- [DEBUG MODELS] Analyzing {total_days:.1f} days of data ---")

        with torch.no_grad():
            for days in runnable:
                model = self.models[days]
                sub_seq = self.extract_slice(sequence, days)

                if not sub_seq:
                    print(f"   [Model {days:2d}d]: No data in window. Skipping.")
                    single_preds[days] = np.array([0.0, 0.0, 0.0])
                    continue

                feat = build_features_prod(sub_seq)
                feat_t = torch.tensor(feat, dtype=torch.float32).unsqueeze(0).to(self.device)
                l_t = torch.tensor([len(sub_seq)]).to(self.device)

                logits = model(feat_t, l_t)
                probs = torch.softmax(logits, dim=1).cpu().numpy()[0]
                single_preds[days] = probs

                # --- STAMPA DI DEBUG PER SINGOLO MODELLO ---
                m_class = np.argmax(probs)
                m_label = INT_TO_LABEL[m_class]
                m_conf = probs[m_class] * 100
                print(
                    f"   [Model {days:2d}d]: Prediction={m_label:6s} | Confidence={m_conf:5.1f}% | (R:{probs[0]:.2f}, Y:{probs[1]:.2f}, G:{probs[2]:.2f})")

                # --- WEIGHTING STRATEGY ---
                w = 1.0


                probs_sum += probs * w
                active_weight += w

        print(f"--- [DEBUG END] ---\n")

        if active_weight == 0: return {"status": "error", "message": "Prediction failed", "diagnosis": "N/A"}

        ens_probs = probs_sum / active_weight
        ai_class = np.argmax(ens_probs)
        ai_label = INT_TO_LABEL[ai_class]

        # =========================================================
        # TREND ANALYSIS (History vs Recent)
        # =========================================================
        insight_msg = ""

        # Use 90d (if available) as historical baseline and ensemble as "today"
        if 90 in single_preds and np.sum(single_preds[90]) > 0:
            probs_90 = single_preds[90]
            class_90 = np.argmax(probs_90)
            label_90 = INT_TO_LABEL[class_90]

            # If history differs from current
            if label_90 != ai_label:
                if label_90 == "GREEN" and ai_label in ["YELLOW", "RED"]:
                    insight_msg = " ⚠️ Warning: Deterioration detected compared to stable history."
                elif label_90 in ["RED", "YELLOW"] and ai_label == "GREEN":
                    insight_msg = " ✅ Positive signs: Improvement detected compared to history."

        # =========================================================
        # SAFETY GUARDRAILS (Hard-Coded Clinical Rules)
        # =========================================================
        final_diagnosis = ai_label
        explanation = f"AI Diagnosis: {ai_label} ({int(ens_probs[ai_class] * 100)}% conf). " + insight_msg

        # Clinical Override (Independent of AI)
        if avg_g > 180:
            final_diagnosis = "RED"
            explanation = "Override: RED. Critical average Hyperglycemia (> 180 mg/dL)."
        elif avg_g < 70:
            final_diagnosis = "RED"
            explanation = "Override: RED. Critical average Hypoglycemia (< 70 mg/dL)."
        elif 150 < avg_g <= 180 or 70 <= avg_g < 80:
            if ai_label == "GREEN":
                final_diagnosis = "YELLOW"
                explanation = "Override: YELLOW. Borderline average values, caution required."

        return {
            "status": "success",
            "diagnosis": final_diagnosis,
            "confidence": round(float(np.max(ens_probs)) * 100, 1),
            "days_analyzed": round(total_days, 1),
            "models_used": runnable,
            "clinical_message": explanation,
            "details": {k: v.tolist() for k, v in single_preds.items()}
        }