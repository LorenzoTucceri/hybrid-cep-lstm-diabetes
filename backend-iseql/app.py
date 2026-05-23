import multiprocessing
import os

if __name__ == '__main__':
    try:
        multiprocessing.set_start_method('spawn', force=True)
    except RuntimeError:
        pass
    os.environ['OBJC_DISABLE_INITIALIZE_FORK_SAFETY'] = 'YES'

from flask import Flask, request, jsonify
from flask_cors import CORS
from config import Config
from task import train_patient_model_async
from services.data_processing import load_and_clean_data, create_interval_labeling_csv
from services.legacy_iseql import run_legacy_analysis
from machine_learning.lstm_logic import ClinicalHybridPredictor
from iseql.interval_action_detector import IntervalActionDetector
from iseql.iseql import ISEQL
from iseql.interval import Interval
from utils import calculate_gmi, format_day, format_duration, format_datetime
from machine_learning.realtime_logic import DexcomRealTimeService
import analysis
import tempfile

Config.init_dirs()
app = Flask(__name__)
CORS(app)

specific_model_path = os.path.join(Config.MODELS_DIR, "model_60d.pth")
lstm_engine = ClinicalHybridPredictor(model_path=specific_model_path)


@app.route('/process-csv', methods=['POST'])
def process_csv():
    # 1. Caricamento
    file = request.files.get('csv_file')
    if file is None: return jsonify({'error': 'No file provided'}), 400

    file_path = os.path.join(Config.UPLOAD_FOLDER, file.filename)
    try:
        file.save(file_path)
        glucose_data = load_and_clean_data(file_path)
    except Exception as e:
        return jsonify({'error': f"Errore lettura: {str(e)}"}), 500

    # 2. Identificazione Paziente
    laravel_patient_id = request.form.get('patient_id')
    patient_id = str(laravel_patient_id).strip() if laravel_patient_id else "guest_unknown"

    if patient_id == "guest_unknown" and 'ID trasmettitore' in glucose_data.columns:
        ids = glucose_data['ID trasmettitore'].unique()
        if len(ids) > 0: patient_id = str(ids[0]).strip()

    print(f" Paziente: {patient_id}")

    # 3. Logica Training
    training_info = "Modello presente o dati insufficienti."
    if patient_id != "guest_unknown":
        model_path = os.path.join(Config.MODELS_DIR_FORECASTING, f"patient_{patient_id}.pth")
        if os.path.exists(model_path):
            training_info = "Modello ATTIVO "
        else:
            col_date = 'Data e ora (AAAA-MM-GGThh:mm:ss)'
            delta = glucose_data[col_date].max() - glucose_data[col_date].min()
            if delta.days >= 1:
                train_patient_model_async.delay(patient_id, file_path)
                training_info = " Training in background "

    # 4. Analisi Dati (AI + Detector)
    gmi, avg = calculate_gmi(glucose_data['Valore del glucosio (mg/dL)'])

    col_date = 'Data e ora (AAAA-MM-GGThh:mm:ss)'
    glucose_data_for_det = glucose_data.copy()
    glucose_data_for_det[col_date] = glucose_data_for_det[col_date].dt.strftime('%Y-%m-%dT%H:%M:%S')

    analyzer = IntervalActionDetector(glucose_data_for_det)
    intervals, events = analyzer.offline_interval_action_detection()
    create_interval_labeling_csv(intervals)

    try:
        # CAMBIA predict_smart in predict
        lstm_result = lstm_engine.predict(intervals)

        # Aggiungi questo log per vedere cosa succede nel terminale Flask
        print(f" [SUCCESS] Risultato AI: {lstm_result['diagnosis']} con confidenza {lstm_result['confidence']}%")

    except Exception as e:
        # Stampa l'errore reale nel terminale per il debug
        print(f" [ERROR] Fallimento durante predict: {str(e)}")
        lstm_result = {
            "status": "error",
            "diagnosis": "N/A",
            "clinical_message": f"Errore interno: {str(e)}",
            "confidence": 0  # Forza a 0 in caso di crash
        }

    # 5. Analisi Legacy (C++ / ISEQL)
    intervals_legacy = [i[:4] for i in intervals]
    swings, ext_swings, parsed_top_k_patterns, patient_stats = run_legacy_analysis(intervals, intervals_legacy)

    # ISEQL Stats
    iseq = ISEQL()
    for i in intervals_legacy:
        iseq.add_interval(Interval(i[1], i[2], i[0], i[3], i[2] - i[1]))


    totals_and_durations = analysis.analyze_glucose_data(iseq.get_intervals(), iseq)
    ts_dur = iseq.find_time_swing_with_too_long_glucose_anomalies()
    ts_freq = iseq.find_too_frequent_time_swings()
    ets_dur = iseq.find_extremely_time_swing_with_too_long_glucose_anomalies()
    ets_freq = iseq.find_too_frequent_extremely_time_swings()
    anom_freq = iseq.find_too_frequent_glucose_anomalies()
    anom_dur = iseq.find_too_long_glucose_anomalies()

    start_t = glucose_data[col_date].iloc[0].strftime('%Y-%m-%d')
    end_t = glucose_data[col_date].iloc[-1].strftime('%Y-%m-%d')

    # 6. Risposta
    return jsonify({
        'training_status': training_info,
        'patient_id': patient_id,
        'start_time': start_t,
        'end_time': end_t,
        'avg': round(float(avg), 2),
        'gmi': round(float(gmi), 2),
        'lstm_result': lstm_result,
        'parsed_top_k_patterns': parsed_top_k_patterns,
        'patient_stats': patient_stats,
        'time_swing': [
            {'day': format_day(p[0].start_time.date()), 'first_event': p[0].event, 'second_event': p[1].event,
             'duration_time_swing': format_duration(p[1].start_time - p[0].end_time)} for p in swings],
        'extremely_time_swing': [
            {'day': format_day(p[0].start_time.date()), 'first_event': p[0].event, 'second_event': p[1].event,
             'duration_time_swing': format_duration(p[1].start_time - p[0].end_time)} for p in ext_swings],
        'too_frequent_glucose_anomalies': [
            {'day': format_day(s), 'high_count': h, 'low_count': l, 'extremely_high_count': eh,
             'extremely_low_count': el, 'total_count': t} for s, _, h, l, eh, el, t in anom_freq],
        'too_long_glucose_anomalies': [
            {'day': format_day(i.start_time.date()), 'event': i.event, 'start_time': format_datetime(i.start_time),
             'end_time': format_datetime(i.end_time), 'duration': format_duration(i.duration)} for i in anom_dur],
        'too_frequent_time_swings': [{'Number of Time Swings': len(s), 'Events': [
            {'Day': format_day(i1.start_time.date()), 'First event': i1.event, 'Second event': i2.event,
             'Duration time swing': format_duration(i2.start_time - i1.end_time)} for i1, i2 in s]} for s in ts_freq],
        'too_frequent_extremely_time_swings': [{'Number of Time Swings': len(s), 'Events': [
            {'Day': format_day(i1.start_time.date()), 'First event': i1.event, 'Second event': i2.event,
             'Duration time swing': format_duration(i2.start_time - i1.end_time)} for i1, i2 in s]} for s in ets_freq],
        'time_swing_with_too_long_glucose_anomalies': [
            {'day': format_day(i1.start_time.date()), 'first_event': i1.event, 'second_event': i2.event,
             'duration_time_swing': format_duration(i2.start_time - i1.end_time), 'anomalous_durations': d} for
            i1, i2, d in ts_dur],
        'extremely_time_swing_with_too_long_glucose_anomalies': [
            {'day': format_day(i1.start_time.date()), 'first_event': i1.event, 'second_event': i2.event,
             'duration_time_swing': format_duration(i2.start_time - i1.end_time), 'anomalous_durations': d} for
            i1, i2, d in ets_dur],
        'totals_and_durations': totals_and_durations
    })


# Route Realtime
@app.route('/dexcom-live', methods=['POST'])
def dexcom_live():
    data = request.json
    username = data.get('username')
    password = data.get('password')
    patient_id = data.get('patient_id')

    if not username or not password or not patient_id:
        return jsonify({"error": "Dati mancanti"}), 400

    service = DexcomRealTimeService(username, password, patient_id)
    return jsonify(service.get_forecast())


# Route Calcolo Date
@app.route('/process-date', methods=['POST'])
def process_date():
    file = request.files.get('csv_file')
    if file is None: return jsonify({'error': 'No file provided'}), 400

    with tempfile.NamedTemporaryFile(delete=False, suffix='.csv') as tmp:
        file.save(tmp.name);
        tmp_path = tmp.name
    try:
        df = load_and_clean_data(tmp_path)
        col_gluc = 'Valore del glucosio (mg/dL)'
        col_date = 'Data e ora (AAAA-MM-GGThh:mm:ss)'
        gmi, avg = calculate_gmi(df[col_gluc])
        first = df[col_date].iloc[0].strftime('%Y-%m-%d %H:%M:%S')
        last = df[col_date].iloc[-1].strftime('%Y-%m-%d %H:%M:%S')
    except Exception as e:
        os.remove(tmp_path);
        return jsonify({'error': str(e)}), 500
    os.remove(tmp_path)
    return jsonify({'first_date': first, 'last_date': last, 'gmi': round(gmi, 2)})


if __name__ == '__main__':
    app.run(debug=True)