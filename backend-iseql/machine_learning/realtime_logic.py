import torch
import numpy as np
import pandas as pd
import os
import sys

# Import per Dexcom
from pydexcom import Dexcom, Region

# Import della configurazione
from config import Config

# Import del modello LSTM
from .forecasting_lstm import GlucosePredictor

# --- NUOVA CONFIGURAZIONE (90 min IN -> 60 min OUT) ---
INPUT_WINDOW = 18  # 90 minuti (18 step da 5 min)
OUTPUT_WINDOW = 12  # 60 minuti (12 step da 5 min)
# ------------------------------------------------------

MIN_GLUCOSE = 40.0
MAX_GLUCOSE = 400.0


class DexcomRealTimeService:
    def __init__(self, username, password, patient_id):
        self.username = username
        self.password = password
        self.patient_id = patient_id
        self.device = torch.device("cpu")
        self.dexcom = None

    def connect(self):
        """Stabilisce la connessione col Cloud Dexcom (Server EU - OUS)"""
        try:
            self.dexcom = Dexcom(
                username=self.username,
                password=self.password,
                region=Region.OUS  # Importante per utenti Europei
            )
            return True, "Connessione Dexcom riuscita"
        except Exception as e:
            return False, f"Credenziali errate o errore server: {str(e)}"

    def _load_patient_model(self):
        """Carica il modello specifico del paziente"""
        model_path = os.path.join(Config.MODELS_DIR_FORECASTING, f"patient_{self.patient_id}.pth")

        if not os.path.exists(model_path):
            raise FileNotFoundError(
                f"Modello AI non trovato per ID {self.patient_id}. Esegui prima il training sul CSV storico.")

        # --- FIX IMPORTANTE: Usa la costante aggiornata (12 step) ---
        model = GlucosePredictor(output_steps=OUTPUT_WINDOW)

        try:
            model.load_state_dict(torch.load(model_path, map_location=self.device))
        except RuntimeError as e:
            raise RuntimeError(f"Errore caricamento pesi (controlla input/output size): {str(e)}")

        model.eval()
        return model

    def normalize(self, value):
        val = (value - MIN_GLUCOSE) / (MAX_GLUCOSE - MIN_GLUCOSE)
        return max(0.0, min(1.0, val))

    def denormalize(self, value):
        return value * (MAX_GLUCOSE - MIN_GLUCOSE) + MIN_GLUCOSE

    def get_forecast(self):
        """
        Ciclo principale: Connessione -> Download -> Previsione -> Analisi Rischio
        """
        # --- Connessione se necessario ---
        if not self.dexcom:
            success, msg = self.connect()
            if not success:
                return {"error": msg}

        try:
            # Scarica 120 minuti di letture (copre comodamente INPUT_WINDOW)
            readings = self.dexcom.get_glucose_readings(minutes=120, max_count=30)
            if not readings:
                return {"error": "Nessun dato glucosio ricevuto dal sensore."}

            # Ordina per tempo (fondamentale)
            sorted_readings = sorted(readings, key=lambda r: r.datetime)
            current_glucose = sorted_readings[-1]

            # Preparazione input LSTM
            values_list = [r.value for r in sorted_readings]

            # Padding se mancano dati fino a INPUT_WINDOW
            if len(values_list) < INPUT_WINDOW:
                missing = INPUT_WINDOW - len(values_list)
                final_input = [values_list[0]] * missing + values_list
            else:
                final_input = values_list[-INPUT_WINDOW:]

            # Normalizzazione e reshape
            norm_input = [self.normalize(x) for x in final_input]
            input_tensor = torch.FloatTensor(norm_input).view(1, INPUT_WINDOW, 1).to(self.device)

            # --- Inferenza modello ---
            try:
                model = self._load_patient_model()
                with torch.no_grad():
                    preds_scaled = model(input_tensor).numpy().flatten()
                preds_mgdl = [int(self.denormalize(x)) for x in preds_scaled]

                # Calcolo orari futuri (OUTPUT_WINDOW * 5 min)
                future_times = [(current_glucose.datetime + pd.Timedelta(minutes=5 * i)).strftime("%H:%M")
                                for i in range(1, OUTPUT_WINDOW + 1)]
            except (FileNotFoundError, RuntimeError) as e:
                return {"error": str(e)}

            # --- Normalizzazione trend_arrow ---
            trend_raw = current_glucose.trend_arrow
            trend_arrow = self._normalize_trend(trend_raw)

            # --- Costruzione risposta JSON ---
            return {
                "current_value": current_glucose.value,
                "trend_arrow": trend_arrow,
                "trend_desc": current_glucose.trend_description or "",
                "timestamp": current_glucose.datetime.strftime("%H:%M:%S"),
                "forecast_values": preds_mgdl,
                "forecast_times": future_times,
                "risk_analysis": self._analyze_risk(current_glucose.value, preds_mgdl)
            }

        except Exception as e:
            return {"error": f"Errore runtime analisi: {str(e)}"}


    def _normalize_trend(self, trend):
        """
        Normalizza eventuali valori strani o Unicode in trend Dexcom standard
        """
        if not trend or trend in ["–", "—", "-", "–\u2013"]:
            return "steady"
        return trend.lower()


    def _analyze_risk(self, current, forecast):
        min_forecast = min(forecast)
        max_forecast = max(forecast)
        last_pred = forecast[-1]

        # Soglie definite da te
        HYPO_THRESH = 80.0
        HYPER_THRESH = 180.0
        SWING_THRESH = 40.0

        # 1. PERICOLO ASSOLUTO: Ipoglicemia (Immediata o Persistente)
        # Se scendo sotto 90 in qualsiasi momento, o se ci rimango.
        if min_forecast <= HYPO_THRESH:
            # Sotto-caso: Sono già basso e rimarrò basso
            if current < HYPO_THRESH and last_pred < HYPO_THRESH:
                return "DANGER: Persistent Hypoglycemia (Too Long Glucose Low)"
            return "DANGER: Hypoglycemia predicted within 60 min!"

        # 2. NUOVA CONDIZIONE: Persistenza dell'Iperglicemia
        # Se sono già alto (current > 170) E tra un'ora sarò ancora alto (last_pred > 170)
        elif current >= HYPER_THRESH and last_pred >= HYPER_THRESH:
            return "WARNING: Persistent Hyperglycemia (Too Long Glucose High)"

        # 3. Iperglicemia Prevista (ma magari ora sono ok)
        elif max_forecast >= HYPER_THRESH:
            return "WARNING: Hyperglycemia predicted."

        # 4. Dinamica: Salita Rapida
        elif (last_pred - current) > SWING_THRESH:
            return "ALERT: Rapid Rise (Time Swing) predicted."

        # 5. Dinamica: Discesa Rapida (senza toccare ancora l'ipo)
        elif (current - last_pred) > SWING_THRESH:
            return "ALERT: Rapid Drop (Time Swing) predicted."

        # 6. Tutto ok
        else:
            return "Stable."