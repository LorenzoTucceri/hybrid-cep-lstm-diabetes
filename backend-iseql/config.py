import os


class Config:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))

    # Cartelle Dati
    MODELS_DIR = os.path.join(BASE_DIR, "data", "models_opt")
    MODELS_DIR_FORECASTING = os.path.join(BASE_DIR, "data", "forecasting_models")
    UPLOAD_FOLDER = os.path.join(BASE_DIR, "data", "laravel_csv")

    # Configurazione Celery / Redis
    CELERY_BROKER_URL = 'redis://localhost:6379/0'
    CELERY_RESULT_BACKEND = 'redis://localhost:6379/0'

    # URL Laravel per la callback
    LARAVEL_API_URL = "http://127.0.0.1:8000/api/internal/model-ready"

    @staticmethod
    def init_dirs():
        os.makedirs(Config.MODELS_DIR, exist_ok=True)
        os.makedirs(Config.MODELS_DIR_FORECASTING, exist_ok=True)
        os.makedirs(Config.UPLOAD_FOLDER, exist_ok=True)