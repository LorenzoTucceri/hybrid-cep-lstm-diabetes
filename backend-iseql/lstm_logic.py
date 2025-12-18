import pandas as pd
import numpy as np
import torch
import os
from torch import nn

# ==================================================================================
# 1. CONFIGURATION
# ==================================================================================
DEFAULT_MODEL_DIR = "./data/models_opt"
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")

# --- OLD THRESHOLDS (Compatible with current .pth models) ---
# Extremely Low: < 60
# Low: 60 - 80
# Normal: 80 - 160
# High: 160 - 240
# Extremely High: > 240
THRESHOLDS = {
    "hypo_extreme": 60,
    "hypo": 80,
    "hyper": 160,
    "hyper_extreme": 240
}

# English Mappings
INT_TO_LABEL = {0: "RED", 1: "YELLOW", 2: "GREEN"}
STATE_ID = {"extremely_low": 0, "low": 1, "normal": 2, "high": 3, "extremely_high": 4}
NUM_STATES = 5


# ==================================================================================
# 2. NEURAL NETWORK ARCHITECTURE
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
# 3. DATA TRANSLATION (Parser & Features)
# ==================================================================================

def get_state_dynamic(g):
    if g < THRESHOLDS["hypo_extreme"]:
        return "extremely_low"
    elif g < THRESHOLDS["hypo"]:
        return "low"
    elif g <= THRESHOLDS["hyper"]:
        return "normal"
    elif g <= THRESHOLDS["hyper_extreme"]:
        return "high"
    else:
        return "extremely_high"


def transform_glucose_risk(g):
    g_clipped = np.clip(g, 20, 600)
    return 1.509 * (np.log(g_clipped) ** 1.084 - 5.381)


def parse_csv_specialized(csv_path):
    try:
        df = pd.read_csv(csv_path, delimiter=';', low_memory=False)

        col_date = 'Data e ora (AAAA-MM-GGThh:mm:ss)'
        col_gluc = 'Valore del glucosio (mg/dL)'

        if col_date not in df.columns:
            df = pd.read_csv(csv_path, delimiter=';', skiprows=1, low_memory=False)

        if col_date not in df.columns or col_gluc not in df.columns:
            if df.shape[1] >= 4:
                col_date = df.columns[2]
                col_gluc = df.columns[3]
            else:
                return [], 0

        df[col_date] = pd.to_datetime(df[col_date], errors='coerce')
        df[col_gluc] = pd.to_numeric(df[col_gluc], errors='coerce')
        df = df.dropna(subset=[col_date, col_gluc]).sort_values(col_date).reset_index(drop=True)

        if len(df) < 5: return [], 0

        total_duration_days = (df[col_date].iloc[-1] - df[col_date].iloc[0]).total_seconds() / 86400.0

        sequence = []
        for i in range(len(df) - 1):
            curr = df.iloc[i]
            nex = df.iloc[i + 1]
            dur_min = (nex[col_date] - curr[col_date]).total_seconds() / 60.0
            dur_min = max(1, min(dur_min, 480))

            val = float(curr[col_gluc])
            state = get_state_dynamic(val)
            sequence.append((state, int(dur_min), val))

        last_val = float(df.iloc[-1][col_gluc])
        sequence.append((get_state_dynamic(last_val), 15, last_val))

        return sequence, total_duration_days

    except Exception as e:
        print(f"[AI PARSER ERROR] {e}")
        return [], 0


def build_features_18(run_list):
    T = len(run_list)
    if T == 0: return np.zeros((1, 18))

    states = np.array([STATE_ID.get(s, 2) for s, _, _ in run_list], dtype=int)
    durs = np.array([d for _, d, _ in run_list], dtype=float)
    gs = np.array([g for *_, g in run_list], dtype=float)

    state_oh = np.eye(NUM_STATES)[states]
    prev_s = np.roll(states, 1);
    prev_s[0] = states[0];
    prev_oh = np.eye(NUM_STATES)[prev_s]
    dur_norm = np.clip(durs / 240.0, 0, 1)
    log_dur = np.log1p(durs)
    g_delta = gs - np.roll(gs, 1);
    g_delta[0] = 0
    with np.errstate(divide='ignore', invalid='ignore'): roc = np.nan_to_num(g_delta / durs)
    risk = transform_glucose_risk(gs)
    load = (gs - 100) * (durs / 60.0)
    zsc = (gs - 140) / 50.0
    roll_risk = np.convolve(risk, np.ones(5) / 5, mode='same')

    feats = np.column_stack([state_oh, prev_oh, dur_norm, log_dur, zsc, g_delta, roc, risk, roll_risk, load])
    return np.nan_to_num(feats).astype(np.float32)


# ==================================================================================
# 4. INFERENCE ENGINE (FULL ENSEMBLE)
# ==================================================================================
class ClinicalEnsembleAdaptive:
    def __init__(self, model_dir_path=None):
        self.device = DEVICE
        self.models = {}

        search_path = model_dir_path if model_dir_path else DEFAULT_MODEL_DIR
        print(f"[LSTM INIT] Loading models from: {search_path}")

        for d in [15, 30, 60, 90]:
            path = os.path.join(search_path, f"model_{d}d.pth")
            if os.path.exists(path):
                try:
                    m = LSTMAttentionClassifier(input_dim=18).to(self.device)
                    m.load_state_dict(torch.load(path, map_location=self.device))
                    m.eval()
                    self.models[d] = m
                    print(f"   ✅ Model {d}d ready.")
                except Exception as e:
                    print(f"   ❌ Error loading {d}d: {e}")
            else:
                print(f"   ⚠️ Model {d}d not found in {path}")

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

    def predict_smart(self, csv_path):
        """
        FULL ENSEMBLE ALWAYS:
        Uses all available models, weighing them dynamically.
        """
        sequence, total_days = parse_csv_specialized(csv_path)

        if not sequence or total_days < 3:
            return {
                "status": "error",
                "message": "Insufficient data (Minimum 3 days required)",
                "diagnosis": "N/A",
                "days_analyzed": round(total_days, 1)
            }

        runnable = [d for d in [15, 30, 60, 90] if d in self.models]

        if not runnable:
            return {"status": "error", "message": "No LSTM models available", "diagnosis": "N/A"}

        single_preds = {}
        probs_sum = np.zeros(3)
        active_weight = 0

        with torch.no_grad():
            for days in runnable:
                model = self.models[days]
                sub_seq = self.extract_slice(sequence, days)
                feat = build_features_18(sub_seq)

                feat_t = torch.tensor(feat, dtype=torch.float32).unsqueeze(0).to(self.device)
                l_t = torch.tensor([len(sub_seq)]).to(self.device)

                logits = model(feat_t, l_t)
                probs = torch.softmax(logits, dim=1).cpu().numpy()[0]
                single_preds[days] = probs.tolist()

                # --- DYNAMIC WEIGHTING (Full Ensemble) ---
                w = 1.0

                # A. SHORT FILE (< 30d)
                if total_days <= 30:
                    if days <= 30:
                        w = 2.0  # Boost short-term
                    else:
                        w = 1.0

                # B. LONG FILE (> 30d)
                else:
                    if days == 15:
                        w = 2.0  # Safety First (Rapid Alert)
                    elif days == 90:
                        w = 1.5  # Historical Stability
                    else:
                        w = 1.0

                probs_sum += probs * w
                active_weight += w

        ens_probs = probs_sum / active_weight
        ens_class = np.argmax(ens_probs)

        return {
            "status": "success",
            "diagnosis": INT_TO_LABEL[ens_class],
            "confidence": round(float(np.max(ens_probs)) * 100, 1),
            "days_analyzed": round(total_days, 1),
            "models_used": runnable,
            "clinical_message": self._generate_explanation(ens_class, single_preds),
            "details": single_preds
        }

    def _generate_explanation(self, ens_idx, singles):
        label = INT_TO_LABEL[ens_idx]
        msg = f"Clinical Profile: {label}."

        if 15 in singles and 90 in singles:
            i15 = np.argmax(singles[15])
            i90 = np.argmax(singles[90])

            if i15 < i90:
                msg += " Warning: Recent deterioration detected over stable history."
            elif i15 > i90:
                msg += " Positive signs: Recent improvement trend detected."
            else:
                msg += " Condition stable over time."
        elif 15 in singles:
            msg += " Analysis based on short-term data."

        return msg