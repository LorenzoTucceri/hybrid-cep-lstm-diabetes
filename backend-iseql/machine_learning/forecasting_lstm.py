import torch
import torch.nn as nn
import numpy as np
import pandas as pd
from torch.utils.data import DataLoader
from sklearn.preprocessing import MinMaxScaler
import os
import time

# --- CONFIGURAZIONE ---
INPUT_WINDOW = 18
OUTPUT_WINDOW = 12
BATCH_SIZE = 64
EPOCHS = 30  # Numero di epoche
LR = 0.001
DEVICE = torch.device("cpu")


class GlucosePredictor(nn.Module):
    def __init__(self, input_size=1, hidden_dim=64, output_steps=6):
        super(GlucosePredictor, self).__init__()
        self.lstm = nn.LSTM(input_size, hidden_dim, num_layers=2, batch_first=True, dropout=0.2)
        self.fc = nn.Linear(hidden_dim, output_steps)

    def forward(self, x):
        out, _ = self.lstm(x)
        last_step = out[:, -1, :]
        return self.fc(last_step)


def train_patient_specific_model(csv_path, save_path):
    print(f" [TRAINING] Inizio elaborazione file: {os.path.basename(csv_path)}")
    start_time = time.time()

    # 1. Caricamento Dati
    try:
        df = pd.read_csv(csv_path, sep=None, engine='python')
    except:
        return False, "Errore lettura CSV"

    target_col = None
    for c in df.columns:
        if "Valore del glucosio" in c or "Glucose Value" in c or "lucose" in c:
            target_col = c
            break

    if not target_col:
        return False, "Colonna glucosio non trovata"

    # Pulizia
    df[target_col] = pd.to_numeric(df[target_col], errors='coerce')
    df = df.dropna(subset=[target_col])
    values = df[target_col].values.astype(float).reshape(-1, 1)

    if len(values) < 200:
        return False, "Dati insufficienti per il training"

    # Normalizzazione
    scaler = MinMaxScaler(feature_range=(0, 1))
    data_scaled = scaler.fit_transform(values)

    # Dataset
    xs, ys = [], []
    for i in range(len(data_scaled) - INPUT_WINDOW - OUTPUT_WINDOW):
        x = data_scaled[i:(i + INPUT_WINDOW)]
        y = data_scaled[(i + INPUT_WINDOW):(i + INPUT_WINDOW + OUTPUT_WINDOW)]
        xs.append(x)
        ys.append(y)

    loader = DataLoader(list(zip(torch.FloatTensor(np.array(xs)), torch.FloatTensor(np.array(ys)))),
                        shuffle=True, batch_size=BATCH_SIZE)

    # Inizializza Modello
    model = GlucosePredictor(output_steps=OUTPUT_WINDOW).to(DEVICE)
    optimizer = torch.optim.Adam(model.parameters(), lr=LR)
    criterion = nn.MSELoss()

    # --- TRAINING LOOP CON LOG VISIBILI ---
    print(f" [TRAINING] Avvio addestramento su {EPOCHS} epoche...")
    model.train()

    for epoch in range(EPOCHS):
        epoch_loss = 0
        for batch_X, batch_y in loader:
            batch_X, batch_y = batch_X.to(DEVICE), batch_y.to(DEVICE).squeeze(-1)
            optimizer.zero_grad()
            outputs = model(batch_X)
            loss = criterion(outputs, batch_y)
            loss.backward()
            optimizer.step()
            epoch_loss += loss.item()

        # LOG NEL TERMINALE OGNI 5 EPOCHE (O ANCHE OGNI 1 SE PREFERISCI)
        if (epoch + 1) % 5 == 0 or epoch == 0:
            avg_loss = epoch_loss / len(loader)
            print(f"    Epoca {epoch + 1}/{EPOCHS} | Loss: {avg_loss:.6f}")

    # Salvataggio
    try:
        torch.save(model.state_dict(), save_path)
        duration = round(time.time() - start_time, 2)
        print(f" [TRAINING] Completato in {duration}s. Modello salvato.")
        return True, "Successo"
    except Exception as e:
        return False, str(e)