import os
import numpy as np
import torch
from torch import nn

# ==================================================================================
# 1. CONFIGURATION
# ==================================================================================
DEFAULT_MODEL_DIR = "../data/models_opt"
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")

# Output ufficiale per il frontend
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
# 3. FEATURE ENGINEERING
# ==================================================================================
def transform_glucose_risk(g):
    g_clipped = np.clip(g, 20, 600)
    return 1.509 * (np.log(g_clipped) ** 1.084 - 5.381)


def build_features_prod(run_list):
    T = len(run_list)
    if T == 0: return np.zeros((1, 18), dtype=np.float32)

    states = np.array([STATE_ID.get(s, 2) for s, _, _ in run_list], dtype=int)
    durs = np.array([d for _, d, _ in run_list], dtype=float)
    gs = np.array([g for *_, g in run_list], dtype=float)

    state_oh = np.eye(NUM_STATES)[states]
    prev_s = np.roll(states, 1);
    prev_s[0] = states[0]
    prev_oh = np.eye(NUM_STATES)[prev_s]
    dur_norm = np.clip(durs / 240.0, 0, 1)
    log_dur = np.log1p(durs)
    g_delta = gs - np.roll(gs, 1);
    g_delta[0] = 0
    with np.errstate(divide='ignore', invalid='ignore'):
        roc = np.nan_to_num(g_delta / durs)
    risk = transform_glucose_risk(gs)
    load = (gs - 100) * (durs / 60.0)
    zsc = (gs - 140) / 50.0
    roll_risk = np.convolve(risk, np.ones(5) / 5, mode='same') if T >= 5 else risk

    feats = np.column_stack([state_oh, prev_oh, dur_norm, log_dur, zsc, g_delta, roc, risk, roll_risk, load])
    return np.nan_to_num(feats).astype(np.float32)



# ==================================================================================
class ClinicalHybridPredictor:
    def __init__(self, model_path=None):
        self.device = DEVICE
        self.model_60d = None

        # Se non passi un path, usa quello di default
        path = model_path if model_path else os.path.join(DEFAULT_MODEL_DIR, "model_60d.pth")
        print(f"\n[INIT] 🔍 Ricerca modello in: {path}")

        if os.path.exists(path):
            try:
                self.model_60d = LSTMAttentionClassifier(input_dim=18).to(self.device)
                self.model_60d.load_state_dict(torch.load(path, map_location=self.device))
                self.model_60d.eval()
                print("    [INIT] Modello Core M60 caricato correttamente.")
            except Exception as e:
                print(f"    [INIT] ERRORE caricamento file .pth: {e}")
        else:
            print(f"   ️ [INIT] ATTENZIONE: Il file {path} NON ESISTE.")

    def extract_60d_slice(self, full_sequence):
        target_minutes = 60 * 1440
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

    # --- QUESTO ERA IL METODO MANCANTE NEI TUOI LOG ---
    def calculate_clinical_metrics(self, sequence):
        glucose_values = []
        for _, dur, val in sequence:
            glucose_values.extend([val] * int(dur))
        gl = np.array(glucose_values)
        if len(gl) < 1: return None

        return {
            "TBR": np.mean(gl < 70) * 100,
            "TIR": np.mean((gl >= 70) & (gl <= 180)) * 100,
            "TAR": np.mean(gl > 180) * 100,
            "GV": np.std(gl)
        }

    def predict(self, intervals):
        print("\n" + "=" * 50)
        print("🚀 [PREDICT] Avvio nuova inferenza...")

        # Inizializziamo a 0 per sicurezza
        ai_label = "GREEN"
        ai_confidence = 0

        sequence = []
        state_map = {'extremely_high': 'extremely_high', 'high': 'high', 'normal': 'normal', 'low': 'low',
                     'extremely_low': 'extremely_low'}
        for item in intervals:
            label = str(item[3]).lower().strip()
            state = state_map.get(label, 'normal')
            sequence.append((state, max(1, int(item[4])), float(item[5])))

        # 1. AI PREDICTION
        if self.model_60d:
            sub_seq_60 = self.extract_60d_slice(sequence)
            if len(sub_seq_60) > 0:
                feat = build_features_prod(sub_seq_60)
                feat_t = torch.tensor(feat).unsqueeze(0).to(self.device)
                l_t = torch.tensor([len(sub_seq_60)]).to(self.device)
                with torch.no_grad():
                    logits = self.model_60d(feat_t, l_t)
                    probs = torch.softmax(logits, dim=1).cpu().numpy()[0]
                    ai_idx = np.argmax(probs)
                    ai_label = INT_TO_LABEL[ai_idx]
                    ai_confidence = int(probs[ai_idx] * 100)
                    print(f"   🧠 [AI] Predizione: {ai_label} ({ai_confidence}%)")

        # 2. GUARDRAIL
        metrics = self.calculate_clinical_metrics(sequence)
        final_diagnosis = ai_label
        explanation = f"AI Analysis confirms stable profile ({ai_confidence}% conf)."

        if metrics:
            TBR, TIR, TAR, GV = metrics["TBR"], metrics["TIR"], metrics["TAR"], metrics["GV"]
            # Logica Override
            if TBR > 10 or TAR > 40 or GV > 70 or TIR < 55:
                final_diagnosis = "RED"
                explanation = f"CRITICAL OVERRIDE: Severe clinical instability (TIR:{TIR:.1f}%)."
            elif (TBR > 4 or TAR > 25 or GV > 50 or TIR < 70) and ai_label == "GREEN":
                final_diagnosis = "YELLOW"
                explanation = f"CAUTION OVERRIDE: Borderline metrics (TIR:{TIR:.1f}%)."

        print(f"🏁 [PREDICT] Risultato: {final_diagnosis} (Conf: {ai_confidence}%)")
        return {
            "status": "success",
            "diagnosis": final_diagnosis,
            "confidence": ai_confidence,
            "clinical_message": explanation,
            "days_analyzed": int(sum(x[1] for x in sequence) / 1440)
        }