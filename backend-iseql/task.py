import os
import requests
from celery import Celery
from config import Config
from machine_learning.forecasting_lstm import train_patient_specific_model

# Inizializza Celery
celery_app = Celery('iseql_tasks', broker=Config.CELERY_BROKER_URL, backend=Config.CELERY_RESULT_BACKEND)


@celery_app.task
def train_patient_model_async(patient_id, csv_path):
    print(f"🚀 [CELERY] Avvio training per ID Interno: {patient_id}")
    save_path = os.path.join(Config.MODELS_DIR_FORECASTING, f"patient_{patient_id}.pth")

    success, msg = train_patient_specific_model(csv_path, save_path)

    if success:
        try:
            payload = {'patient_id': str(patient_id)}
            print(f"📡 [DEBUG] Notifico Laravel: {payload}")
            response = requests.post(Config.LARAVEL_API_URL, data=payload, timeout=5)

            if response.status_code == 200:
                print(f"✅ [CELERY] Successo!")
            else:
                print(f"❌ [CELERY] Errore Laravel: {response.text}")
        except Exception as e:
            print(f" [CELERY] Errore connessione: {e}")

    return success