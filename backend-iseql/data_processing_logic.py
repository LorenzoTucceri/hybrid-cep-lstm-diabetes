import ast
import csv
import re
import os
import subprocess
import tempfile
from datetime import datetime

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
# INIZIALIZZAZIONE AI (Carica i modelli 15d, 30d, 60d, 90d all'avvio)
# ==============================================================================
# Assicurati che la cartella 'data/models_opt' esista e contenga i file .pth
lstm_engine = ClinicalEnsembleAdaptive(model_dir_path="./data/models_opt")


def create_interval_labeling_csv(intervals):
    filename = "intervalli_glucosio.csv"
    if os.path.exists(filename):
        os.remove(filename)

    # Scrivi gli intervalli nel CSV
    with open(filename, mode='w', newline='') as csvfile:
        writer = csv.writer(csvfile)
        writer.writerow(["symbol", "start_time", "end_time", "label"])  # intestazione

        for interval in intervals:
            symbol, start, end, label = interval
            writer.writerow([symbol, start.isoformat(), end.isoformat(), label])

    if os.path.exists("eventi.txt"):
        os.remove("eventi.txt")


@app.route('/process-csv', methods=['POST'])
def process_csv():
    parsed_time_swings = []
    parsed_extremely_time_swings = []
    parsed_top_k_pattern = []
    file = request.files.get('csv_file')

    if file is None:
        return jsonify({'error': 'No file provided'}), 400

    upload_folder = "./data/laravel_csv"
    if not os.path.exists(upload_folder):
        os.makedirs(upload_folder)

    file_path = os.path.join(upload_folder, file.filename)

    try:
        file.save(file_path)
        glucose_data = pd.read_csv(file_path, delimiter=';')
    except Exception as e:
        return jsonify({'error': str(e)}), 500

    # -------------------------------------------------------------------------
    # 1. (LSTM ENSEMBLE)
    # -------------------------------------------------------------------------
    print("--- Avvio Analisi AI ---")
    try:
        # Passiamo il path del file appena salvato al motore LSTM
        lstm_result = lstm_engine.predict_smart(file_path)
    except Exception as e:
        print(f"[AI ERROR] {e}")
        lstm_result = {"status": "error", "message": str(e), "diagnosis": "N/A"}
    print("--- Fine Analisi AI ---")
    # -------------------------------------------------------------------------

    # Preprocessing data standard
    columns_specific = ['Tipo di evento', 'Sottotipo di evento', 'Data e ora (AAAA-MM-GGThh:mm:ss)',
                        'Valore del glucosio (mg/dL)']

    # Gestione robusta delle righe iniziali (header variabile)
    glucose_data = glucose_data[columns_specific].iloc[18:]
    glucose_data_copia = glucose_data[columns_specific].iloc[18:]

    start_date = request.form.get('start_date')
    end_date = request.form.get('end_date')

    if start_date is not None and end_date is not None:
        date_column = 'Data e ora (AAAA-MM-GGThh:mm:ss)'

        if start_date:
            data_obj = datetime.strptime(start_date, '%Y-%m-%d')
            start_date = data_obj.strftime('%Y-%m-%dT00:00:00')

        if end_date:
            data_obj = datetime.strptime(end_date, '%Y-%m-%d')
            end_date = data_obj.strftime('%Y-%m-%dT00:00:00')

        if start_date and end_date:
            glucose_data = glucose_data[
                (glucose_data[date_column] >= start_date) & (glucose_data[date_column] <= end_date)]
        elif start_date:
            glucose_data = glucose_data[glucose_data[date_column] >= start_date]
        elif end_date:
            glucose_data = glucose_data[glucose_data[date_column] <= end_date]

    gmi, avg = calculate_gmi(glucose_data['Valore del glucosio (mg/dL)'])

    analyzer = IntervalActionDetector(glucose_data)
    results = analyzer.offline_interval_action_detection()

    # Per visualizzare csv
    intervals, events = analyzer.offline_interval_action_detection()
    create_interval_labeling_csv(intervals)

    # Detection pattern
    top_k_pattern = pattern_detection.extract_patient_patterns(intervals)

    with tempfile.NamedTemporaryFile(mode='w', delete=False, newline='') as f:
        f.write("pattern\n")  # header
        for p in top_k_pattern['pattern']:  # seleziona solo la colonna pattern
            if isinstance(p, (tuple, list)):
                p_str = ",".join(str(x) for x in p)
            else:
                p_str = str(p)
            f.write(f"{p_str}\n")
        tmp_file_path = f.name

    with open("eventi.txt", "w") as file:
        file.write("start_time,end_time,label\n")  # Header
        for item in results[0]:
            start = item[1]
            end = item[2]
            label = item[3]

            start_str = datetime_to_unix_timestamp(start)
            end_str = datetime_to_unix_timestamp(end)

            file.write(f"{start_str},{end_str},{label}\n")

    def parse_part(part):
        time_part, id_event, event_type = part.strip().split()
        start_str, end_str = time_part[1:-1].split(',')
        start_dt = unix_timestamp_to_datetime(int(start_str))
        end_dt = unix_timestamp_to_datetime(int(end_str))
        duration = end_dt - start_dt
        return Interval(start_dt, end_dt, int(id_event), event_type, duration)

    def run_and_parse(command):
        """Esegue un comando e restituisce una lista di tuple (interval1, interval2) o pattern/freq."""

        if command == "detection_pattern":
            result = subprocess.run(
                ["../cpp-iseql/build/src/iseql", "detection-pattern", tmp_file_path],
                check=True,
                capture_output=True,
                text=True
            )

            lines = result.stdout.strip().split("\n")
            pattern_re = re.compile(
                r"\{\s*pattern:\s*'(.+?)',\s*frequency:\s*(\d+),\s*occurrences:\s*\[(.*)\]\s*\}"
            )

            parsed = []

            for line in lines:
                line = line.strip()
                if not line:
                    continue

                m = pattern_re.match(line)
                if not m:
                    continue

                pattern_str = m.group(1)
                frequency = int(m.group(2))
                occurrences_str = m.group(3)

                # Converti pattern in tupla
                pattern = tuple(p.strip() for p in pattern_str.split(','))

                # Converti occurrences in lista di liste di datetime
                occurrences = []
                if occurrences_str:
                    try:
                        raw = ast.literal_eval("[" + occurrences_str + "]")
                        for match in raw:
                            formatted_match = []
                            for start_ts, end_ts in match:
                                start_dt = unix_timestamp_to_datetime(start_ts)
                                end_dt = unix_timestamp_to_datetime(end_ts)
                                duration = end_dt - start_dt
                                total_seconds = int(duration.total_seconds())
                                hours = total_seconds // 3600
                                minutes = (total_seconds % 3600) // 60
                                seconds = total_seconds % 60
                                duration_str = f"{hours:02d}:{minutes:02d}:{seconds:02d}"
                                formatted_match.append({
                                    "start": start_dt.strftime("%Y-%m-%d %H:%M"),
                                    "end": end_dt.strftime("%Y-%m-%d %H:%M"),
                                    "duration": duration_str
                                })
                            occurrences.append(formatted_match)
                    except Exception as e:
                        print("[WARNING] Errore parsing:", e)

                    # print(occurrences)

                parsed.append({
                    'pattern': pattern,
                    'frequency': frequency,
                    'occurrences': occurrences
                })

            df_patient_patterns = pd.DataFrame(parsed)

            df_enriched = pattern_detection.enrich_patient_patterns(df_patient_patterns)

            # Costruiamo la lista finale da restituire con tutte le info disponibili
            parsed_final = []

            for _, row in df_enriched.iterrows():
                # Calcoliamo la durata media del pattern in minuti
                durations = []
                for occ_list in row['occurrences']:
                    for occ in occ_list:
                        h, m, s = map(int, occ['duration'].split(":"))
                        total_minutes = h * 60 + m + s / 60
                        durations.append(total_minutes)
                avg_duration = round(sum(durations) / len(durations), 2) if durations else 0

                parsed_final.append({
                    'pattern': row['pattern'],
                    'frequency': row.get('frequency', 0),
                    'occurrences': row['occurrences'],
                    'target': row.get('dominant_target', 'N/A'),
                    'max_lift': row.get('max_lift', 0),
                    'total_duration': avg_duration
                })

            # Calcoliamo le statistiche dal paziente
            if parsed_final:
                targets = [p['target'].lower() for p in parsed_final]  # red, yellow, green in inglese
                most_freq_item = max(parsed_final, key=lambda x: x['frequency'])

                patient_stats = {
                    'total_patterns': len(parsed_final),
                    'most_frequent_pattern': " - ".join(
                        str(p) for p in most_freq_item['pattern']) + f" ({most_freq_item['frequency']})",
                    'target_distribution': {
                        'red': targets.count('red'),
                        'yellow': targets.count('yellow'),
                        'green': targets.count('green')
                    },
                    'avg_duration': round(
                        sum(p['total_duration'] for p in parsed_final) / len(parsed_final),
                        2
                    )
                }
            else:
                patient_stats = {
                    'total_patterns': 0,
                    'most_frequent_pattern': [],
                    'target_distribution': {'red': 0, 'yellow': 0, 'green': 0},
                    'avg_duration': 'N/A'
                }

            return parsed_final, patient_stats

        else:
            result = subprocess.run(
                ["../cpp-iseql/build/src/iseql", command, ""],
                check=True,
                capture_output=True,
                text=True
            )
            parsed = []
            lines = result.stdout.strip().split("\n")
            for line in lines:
                parts = line.strip().split(" -- ")
                if len(parts) != 2:
                    continue
                interval1 = parse_part(parts[0])
                interval2 = parse_part(parts[1])
                parsed.append((interval1, interval2))
            return parsed

    try:
        parsed_time_swings = run_and_parse("time-swing")
        parsed_extremely_time_swings = run_and_parse("extremely-time-swing")
        parsed_top_k_pattern, patient_stats = run_and_parse("detection_pattern")

    except subprocess.CalledProcessError as e:
        print(f"Errore durante l'esecuzione del programma C: {e}")

    iseq = ISEQL()
    for interval_labeling in results[0]:
        duration = interval_labeling[2] - interval_labeling[1]
        interval_iseql = Interval(interval_labeling[1], interval_labeling[2], interval_labeling[0],
                                  interval_labeling[3], duration)
        iseq.add_interval(interval_iseql)

    # Process results
    time_swing_duration = iseq.find_time_swing_with_too_long_glucose_anomalies()
    time_swings_too_frequent = iseq.find_too_frequent_time_swings()



    extremely_time_swing_duration = iseq.find_extremely_time_swing_with_too_long_glucose_anomalies()
    extremely_time_swings_too_frequent = iseq.find_too_frequent_extremely_time_swings()


    anomalous_frequency = iseq.find_too_frequent_glucose_anomalies()
    anomalous_duration = iseq.find_too_long_glucose_anomalies()

    result = {
        'avg': avg,
        'gmi': gmi,

        # --- OUTPUT AI LSTM ---
        'lstm_result': lstm_result,
        # ----------------------

        'time_swing': [
            {
                'day': format_day(pair[0].start_time.date()),
                'first_event': pair[0].event,
                'second_event': pair[1].event,
                'duration_time_swing': format_duration(pair[1].start_time - pair[0].end_time)
            }
            for pair in parsed_time_swings
        ],

        'too_frequent_glucose_anomalies': [
            {
                'day': f"{format_day(start_date)}",
                'high_count': high_anomalous_count,
                'low_count': low_anomalous_count,
                'extremely_high_count': extremely_high_anomalous_count,
                'extremely_low_count': extremely_low_anomalous_count,
                'total_count': total_count
            }
            for
            start_date, end_time, high_anomalous_count, low_anomalous_count, extremely_high_anomalous_count, extremely_low_anomalous_count, total_count
            in anomalous_frequency
        ],

        'too_frequent_time_swings': [
            {
                'Number of Time Swings': len(swing_set),
                'Events': [
                    {
                        'Day': f"{format_day(interval1.start_time.date())}",
                        'First event': interval1.event,
                        'Second event': interval2.event,
                        'Duration time swing': format_duration(interval2.start_time - interval1.end_time),
                    }
                    for interval1, interval2 in swing_set
                ]
            }
            for swing_set in time_swings_too_frequent
        ],

        'too_long_glucose_anomalies': [
            {
                'day': format_day(intrvl.start_time.date()),
                'event': intrvl.event,
                'start_time': format_datetime(intrvl.start_time),
                'end_time': format_datetime(intrvl.end_time),
                'duration': format_duration(intrvl.duration)
            }
            for intrvl in anomalous_duration
        ],

        'time_swing_with_too_long_glucose_anomalies': [
            {
                'day': format_day(interval1.start_time.date()),
                'first_event': interval1.event,
                'second_event': interval2.event,
                'duration_time_swing': format_duration(interval2.start_time - interval1.end_time),
                'anomalous_durations': description
            }
            for interval1, interval2, description in time_swing_duration
        ],

        'extremely_time_swing': [
            {
                'day': format_day(pair[0].start_time.date()),
                'first_event': pair[0].event,
                'second_event': pair[1].event,
                'duration_time_swing': format_duration(pair[1].start_time - pair[0].end_time)
            }
            for pair in parsed_extremely_time_swings
        ],

        'too_frequent_extremely_time_swings': [
            {
                'Number of Time Swings': len(swing_set),
                'Events': [
                    {
                        'Day': f"{format_day(interval1.start_time.date())}",
                        'First event': interval1.event,
                        'Second event': interval2.event,
                        'Duration time swing': format_duration(interval2.start_time - interval1.end_time),
                    }
                    for interval1, interval2 in swing_set
                ]
            }
            for swing_set in extremely_time_swings_too_frequent
        ],

        'extremely_time_swing_with_too_long_glucose_anomalies': [
            {
                'day': format_day(interval1.start_time.date()),
                'first_event': interval1.event,
                'second_event': interval2.event,
                'duration_time_swing': format_duration(interval2.start_time - interval1.end_time),
                'anomalous_durations': description
            }
            for interval1, interval2, description in extremely_time_swing_duration
        ],

        'parsed_top_k_patterns': [
            {
                'pattern': item['pattern'],
                'frequency': item.get('frequency', 0),
                'occurrences': item['occurrences'],
                'target': item.get('target'),
                'max_lift': item.get('max_lift'),
            }
            for item in parsed_top_k_pattern
        ],
        'patient_stats': patient_stats
    }

    date_column = 'Data e ora (AAAA-MM-GGThh:mm:ss)'

    # Convert the date column to datetime format
    glucose_data_copia[date_column] = pd.to_datetime(glucose_data_copia[date_column], format='%Y-%m-%dT%H:%M:%S')
    first_date = glucose_data_copia[date_column].iloc[0]
    last_date = glucose_data_copia[date_column].iloc[-1]
    result['start_time'] = first_date.strftime('%Y-%m-%d')
    result['end_time'] = last_date.strftime('%Y-%m-%d')

    totals_and_durations = analysis.analyze_glucose_data(iseq.get_intervals(), iseq)
    result['totals_and_durations'] = totals_and_durations

    return jsonify(result)


@app.route('/process-date', methods=['POST'])
def process_date():
    file = request.files.get('csv_file')

    if file is None:
        return jsonify({'error': 'No file provided'}), 400

    upload_folder = "./data/laravel_csv"
    file_path = os.path.join(upload_folder, file.filename)

    try:
        file.save(file_path)
        glucose_data = pd.read_csv(file_path, delimiter=';')
    except Exception as e:
        return jsonify({'error': str(e)}), 500

    # Preprocessing data
    columns_specific = ['Tipo di evento', 'Sottotipo di evento', 'Data e ora (AAAA-MM-GGThh:mm:ss)',
                        'Valore del glucosio (mg/dL)']
    glucose_data = glucose_data[columns_specific].iloc[18:]
    gmi, avg = calculate_gmi(glucose_data['Valore del glucosio (mg/dL)'])
    gmi = round(gmi, 2)
    # Extracting the date column
    date_column = 'Data e ora (AAAA-MM-GGThh:mm:ss)'

    # Convert the date column to datetime format
    glucose_data[date_column] = pd.to_datetime(glucose_data[date_column], format='%Y-%m-%dT%H:%M:%S')

    # Get the first and last date from the specified rows
    first_date = glucose_data[date_column].iloc[0]  # Row 19 (0-based index)
    last_date = glucose_data[date_column].iloc[-1]  # Last row

    return jsonify({
        'first_date': first_date.strftime('%Y-%m-%d %H:%M:%S'),
        'last_date': last_date.strftime('%Y-%m-%d %H:%M:%S'),
        'gmi': gmi
    })


if __name__ == '__main__':
    app.run(debug=True)