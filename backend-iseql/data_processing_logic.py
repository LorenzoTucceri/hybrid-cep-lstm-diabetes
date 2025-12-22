import ast
import csv
import re
import os
import subprocess
import tempfile
from datetime import datetime
import numpy as np

import pandas as pd
from flask import Flask, request, jsonify
from flask_cors import CORS

import analysis
import pattern_detection
from interval_action_detector import IntervalActionDetector
from iseql import ISEQL
from interval import Interval
from utils import calculate_gmi, datetime_to_unix_timestamp, unix_timestamp_to_datetime, format_day, format_duration, \
    format_datetime

from lstm_logic import ClinicalEnsembleAdaptive

app = Flask(__name__)
CORS(app)

# ==============================================================================
# INIZIALIZZAZIONE AI
# ==============================================================================
lstm_engine = ClinicalEnsembleAdaptive(model_dir_path="./data/models_opt")


def create_interval_labeling_csv(intervals):
    """
    Salva il CSV degli intervalli.
    Supporta sia il formato vecchio (4 elementi) che quello nuovo arricchito (6 elementi).
    """
    filename = "intervalli_glucosio.csv"
    if os.path.exists(filename): os.remove(filename)

    with open(filename, mode='w', newline='') as csvfile:
        writer = csv.writer(csvfile)

        # Controlla la struttura del primo intervallo per decidere l'header
        if intervals and len(intervals[0]) >= 6:
            # Formato Arricchito (Detector Ottimizzato)
            writer.writerow(["symbol", "start_time", "end_time", "label", "duration_min", "avg_glucose"])
            for i in intervals:
                writer.writerow([i[0], i[1].isoformat(), i[2].isoformat(), i[3], i[4], i[5]])
        else:
            # Formato Legacy
            writer.writerow(["symbol", "start_time", "end_time", "label"])
            for i in intervals:
                writer.writerow([i[0], i[1].isoformat(), i[2].isoformat(), i[3]])

    if os.path.exists("eventi.txt"): os.remove("eventi.txt")


# --- FUNZIONE DI CARICAMENTO ROBUSTA ---
def load_and_clean_data(file_path):
    STD_DATE = 'Data e ora (AAAA-MM-GGThh:mm:ss)'
    STD_GLUC = 'Valore del glucosio (mg/dL)'
    STD_ID = 'ID trasmettitore'

    col_mapping = {
        'Timestamp (YYYY-MM-DDThh:mm:ss)': STD_DATE,
        'Glucose Value (mg/dL)': STD_GLUC,
        'Event Type': 'Tipo di evento',
        'Event Subtype': 'Sottotipo di evento',
        'Transmitter ID': STD_ID,
        'Timestamp': STD_DATE,
        'Glucose': STD_GLUC,
        'Sello de tiempo del dispositivo': STD_DATE,
        'Historial de glucosa mg/dL': STD_GLUC
    }

    df = None
    configs = [(',', 2), (';', 2), (',', 0), (';', 0), (',', 18), (';', 18)]

    for sep, h_row in configs:
        try:
            preview = pd.read_csv(file_path, sep=sep, header=h_row, nrows=2, on_bad_lines='skip')
            cols = [str(c).strip() for c in preview.columns]
            matches = sum(1 for c in cols if c in col_mapping or c == STD_DATE or c == STD_GLUC)
            if matches >= 2:
                df = pd.read_csv(file_path, sep=sep, header=h_row, low_memory=False, on_bad_lines='skip')
                df.columns = [str(c).strip() for c in df.columns]
                break
        except:
            continue

    if df is None: raise ValueError("Formato CSV non riconosciuto.")

    df = df.rename(columns=col_mapping)

    if STD_GLUC not in df.columns:
        for c in df.columns:
            if 'gluc' in c.lower() and 'insul' not in c.lower():
                df = df.rename(columns={c: STD_GLUC});
                break
    if STD_DATE not in df.columns:
        for c in df.columns:
            if ('time' in c.lower() or 'date' in c.lower()) and 'transmitt' not in c.lower():
                df = df.rename(columns={c: STD_DATE});
                break

    if STD_GLUC not in df.columns or STD_DATE not in df.columns:
        raise ValueError("Colonne essenziali mancanti.")

    if STD_ID in df.columns:
        valid_id_rows = df[df[STD_ID].notna() & (df[STD_ID].astype(str).str.strip() != '')].index
        if not valid_id_rows.empty: df = df.loc[valid_id_rows[0]:]

    df[STD_GLUC] = pd.to_numeric(df[STD_GLUC], errors='coerce')
    df = df.dropna(subset=[STD_GLUC])

    try:
        df[STD_DATE] = pd.to_datetime(df[STD_DATE], format='ISO8601')
    except:
        df[STD_DATE] = pd.to_datetime(df[STD_DATE], dayfirst=True, errors='coerce')

    df = df.dropna(subset=[STD_DATE])
    df = df.sort_values(STD_DATE).reset_index(drop=True)

    return df


@app.route('/process-csv', methods=['POST'])
def process_csv():
    file = request.files.get('csv_file')
    if file is None: return jsonify({'error': 'No file provided'}), 400

    upload_folder = "./data/laravel_csv"
    if not os.path.exists(upload_folder): os.makedirs(upload_folder)
    file_path = os.path.join(upload_folder, file.filename)

    try:
        file.save(file_path)
        glucose_data = load_and_clean_data(file_path)
    except Exception as e:
        print(f"Errore Load: {e}")
        return jsonify({'error': f"Errore lettura CSV: {str(e)}"}), 500

    # 1. FILTRO DATE
    start_date = request.form.get('start_date')
    end_date = request.form.get('end_date')
    date_column = 'Data e ora (AAAA-MM-GGThh:mm:ss)'

    if start_date or end_date:
        if start_date:
            s_d = datetime.strptime(start_date, '%Y-%m-%d')
            glucose_data = glucose_data[glucose_data[date_column] >= s_d]
        if end_date:
            e_d = datetime.strptime(end_date, '%Y-%m-%d').replace(hour=23, minute=59, second=59)
            glucose_data = glucose_data[glucose_data[date_column] <= e_d]

    if glucose_data.empty:
        return jsonify({'error': 'Nessun dato trovato nel range selezionato'}), 400

    glucose_data_copia = glucose_data.copy()
    gmi, avg = calculate_gmi(glucose_data['Valore del glucosio (mg/dL)'])

    # 2. DETECTOR (Calcola intervalli, durata e medie)
    glucose_data_for_det = glucose_data.copy()
    glucose_data_for_det[date_column] = glucose_data_for_det[date_column].dt.strftime('%Y-%m-%dT%H:%M:%S')

    analyzer = IntervalActionDetector(glucose_data_for_det)
    # intervals contiene ora: (symbol, start, end, label, duration, avg_glucose)
    intervals, events = analyzer.offline_interval_action_detection()

    create_interval_labeling_csv(intervals)

    # 3. LSTM AI (Usa direttamente gli intervalli arricchiti)
    print("--- Avvio Analisi AI (Direct Intervals) ---")
    try:
        # Nota: Non passiamo più 'glucose_data', il detector ha fatto tutto
        lstm_result = lstm_engine.predict_smart(intervals)
    except Exception as e:
        print(f"[AI ERROR] {e}")
        lstm_result = {"status": "error", "message": str(e), "diagnosis": "N/A"}
    print("--- Fine Analisi AI ---")

    # --- COMPATIBILITÀ LEGACY ---
    # I moduli Pattern Detection e C++ si aspettano tuple di 4 elementi (senza durata/media).
    # Creiamo una versione "slice" degli intervalli per loro.
    intervals_legacy = [i[:4] for i in intervals]

    # 4. PATTERN DETECTION
    top_k_pattern = pattern_detection.extract_patient_patterns(intervals_legacy)

    tmp_file_path = ""
    with tempfile.NamedTemporaryFile(mode='w', delete=False, newline='') as f:
        f.write("pattern\n")
        for p in top_k_pattern['pattern']:
            p_str = ",".join(str(x) for x in p) if isinstance(p, (tuple, list)) else str(p)
            f.write(f"{p_str}\n")
        tmp_file_path = f.name

    with open("eventi.txt", "w") as file:
        file.write("start_time,end_time,label\n")
        for item in intervals_legacy:
            file.write(f"{datetime_to_unix_timestamp(item[1])},{datetime_to_unix_timestamp(item[2])},{item[3]}\n")

    def parse_part(part):
        try:
            t, eid, etype = part.strip().split()
            s, e = map(int, t[1:-1].split(','))
            s_dt, e_dt = unix_timestamp_to_datetime(s), unix_timestamp_to_datetime(e)
            return Interval(s_dt, e_dt, int(eid), etype, e_dt - s_dt)
        except:
            return None

    def run_cpp_logic():
        swings, ext_swings, parsed_pats, p_stats = [], [], [], {}

        # C++ Swings
        try:
            res = subprocess.run(["../cpp-iseql/build/src/iseql", "time-swing", ""], check=True, capture_output=True,
                                 text=True)
            for l in res.stdout.split('\n'):
                if " -- " in l:
                    pts = l.split(" -- ")
                    if len(pts) == 2:
                        i1, i2 = parse_part(pts[0]), parse_part(pts[1])
                        if i1 and i2: swings.append((i1, i2))
        except:
            pass

        # C++ Extreme Swings
        try:
            res = subprocess.run(["../cpp-iseql/build/src/iseql", "extremely-time-swing", ""], check=True,
                                 capture_output=True, text=True)
            for l in res.stdout.split('\n'):
                if " -- " in l:
                    pts = l.split(" -- ")
                    if len(pts) == 2:
                        i1, i2 = parse_part(pts[0]), parse_part(pts[1])
                        if i1 and i2: ext_swings.append((i1, i2))
        except:
            pass

        # C++ Patterns
        try:
            res = subprocess.run(["../cpp-iseql/build/src/iseql", "detection-pattern", tmp_file_path], check=True,
                                 capture_output=True, text=True)
            pat_re = re.compile(r"\{\s*pattern:\s*'(.+?)',\s*frequency:\s*(\d+),\s*occurrences:\s*\[(.*)\]\s*\}")
            raw_pats = []
            for line in res.stdout.split('\n'):
                m = pat_re.match(line.strip())
                if m:
                    pat_str, freq, occ_str = m.groups()
                    pat = tuple(p.strip() for p in pat_str.split(','))
                    occs = []
                    if occ_str:
                        try:
                            raw = ast.literal_eval("[" + occ_str + "]")
                            for match in raw:
                                fmt = []
                                for s, e in match:
                                    s_dt, e_dt = unix_timestamp_to_datetime(s), unix_timestamp_to_datetime(e)
                                    dur = int((e_dt - s_dt).total_seconds())
                                    fmt.append({"start": s_dt.strftime("%Y-%m-%d %H:%M"),
                                                "end": e_dt.strftime("%Y-%m-%d %H:%M"),
                                                "duration": f"{dur // 3600:02d}:{(dur % 3600) // 60:02d}:{dur % 60:02d}"})
                                occs.append(fmt)
                        except:
                            pass
                    raw_pats.append({'pattern': pat, 'frequency': int(freq), 'occurrences': occs})

            df_en = pattern_detection.enrich_patient_patterns(pd.DataFrame(raw_pats))
            avg_d, cnt = 0, 0
            for _, r in df_en.iterrows():
                durs = []
                if isinstance(r['occurrences'], list):
                    for ol in r['occurrences']:
                        for o in ol:
                            try:
                                h, m, s = map(int, o['duration'].split(':'))
                                durs.append(h * 60 + m + s / 60)
                            except:
                                pass
                ad = round(sum(durs) / len(durs), 2) if durs else 0
                parsed_pats.append(
                    {'pattern': r['pattern'], 'frequency': r.get('frequency', 0), 'occurrences': r['occurrences'],
                     'target': r.get('dominant_target', 'N/A'), 'max_lift': r.get('max_lift', 0), 'total_duration': ad})
                avg_d += ad
                cnt += 1

            p_stats = {'total_patterns': 0, 'most_frequent_pattern': "N/A",
                       'target_distribution': {'red': 0, 'yellow': 0, 'green': 0}, 'avg_duration': 'N/A'}
            if parsed_pats:
                ts = [p['target'].lower() for p in parsed_pats]
                mf = max(parsed_pats, key=lambda x: x['frequency'])
                p_stats = {'total_patterns': len(parsed_pats),
                           'most_frequent_pattern': f"{' - '.join(mf['pattern'])} ({mf['frequency']})",
                           'target_distribution': {'red': ts.count('red'), 'yellow': ts.count('yellow'),
                                                   'green': ts.count('green')}, 'avg_duration': round(avg_d / cnt, 2)}
        except:
            pass

        return swings, ext_swings, parsed_pats, p_stats

    swings, ext_swings, parsed_top_k_patterns, patient_stats = run_cpp_logic()
    if os.path.exists(tmp_file_path): os.remove(tmp_file_path)

    # 5. STATISTICHE ISEQL
    iseq = ISEQL()
    # Usiamo intervals_legacy anche qui per sicurezza
    for i in intervals_legacy:
        iseq.add_interval(Interval(i[1], i[2], i[0], i[3], i[2] - i[1]))

    ts_dur = iseq.find_time_swing_with_too_long_glucose_anomalies()
    ts_freq = iseq.find_too_frequent_time_swings()
    ets_dur = iseq.find_extremely_time_swing_with_too_long_glucose_anomalies()
    ets_freq = iseq.find_too_frequent_extremely_time_swings()
    anom_freq = iseq.find_too_frequent_glucose_anomalies()
    anom_dur = iseq.find_too_long_glucose_anomalies()

    totals_and_durations = analysis.analyze_glucose_data(iseq.get_intervals(), iseq)

    start_t, end_t = 'N/A', 'N/A'
    if not glucose_data_copia.empty:
        start_t = glucose_data_copia[date_column].iloc[0].strftime('%Y-%m-%d')
        end_t = glucose_data_copia[date_column].iloc[-1].strftime('%Y-%m-%d')

    result = {
        'avg': avg, 'gmi': gmi, 'lstm_result': lstm_result,
        'parsed_top_k_patterns': [
            {'pattern': i['pattern'], 'frequency': i.get('frequency', 0), 'occurrences': i['occurrences'],
             'target': i.get('target'), 'max_lift': i.get('max_lift')} for i in parsed_top_k_patterns
        ],
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
        'totals_and_durations': totals_and_durations, 'start_time': start_t, 'end_time': end_t
    }
    return jsonify(result)


@app.route('/process-date', methods=['POST'])
def process_date():
    file = request.files.get('csv_file')
    if file is None: return jsonify({'error': 'No file provided'}), 400

    with tempfile.NamedTemporaryFile(delete=False, suffix='.csv') as tmp:
        file.save(tmp.name)
        tmp_path = tmp.name

    try:
        df = load_and_clean_data(tmp_path)
        col_gluc = 'Valore del glucosio (mg/dL)'
        col_date = 'Data e ora (AAAA-MM-GGThh:mm:ss)'
        gmi, avg = calculate_gmi(df[col_gluc])
        first = df[col_date].iloc[0].strftime('%Y-%m-%d %H:%M:%S')
        last = df[col_date].iloc[-1].strftime('%Y-%m-%d %H:%M:%S')
    except Exception as e:
        os.remove(tmp_path)
        return jsonify({'error': str(e)}), 500

    os.remove(tmp_path)
    return jsonify({'first_date': first, 'last_date': last, 'gmi': round(gmi, 2)})


if __name__ == '__main__':
    app.run(debug=True)