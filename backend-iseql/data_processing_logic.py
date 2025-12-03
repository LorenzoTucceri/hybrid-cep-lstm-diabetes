import csv

from flask import Flask, request, jsonify
from flask_cors import CORS
import os

import analysis
import detection_pattern
from interval_action_detector import IntervalActionDetector
from iseql import ISEQL
from interval import Interval
import subprocess
from utils import *
import tempfile
app = Flask(__name__)
CORS(app)




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
    top_k_pattern = detection_pattern.extract_patient_patterns(intervals)

    with tempfile.NamedTemporaryFile(mode='w', delete=False, newline='') as f:
        f.write("pattern\n")  # header
        for p in top_k_pattern['pattern']:  # seleziona solo la colonna pattern
            if isinstance(p, (tuple, list)):
                p_str = ",".join(str(x) for x in p)
            else:
                p_str = str(p)
            f.write(f"{p_str}\n")
        tmp_file_path = f.name

    print(f"File CSV temporaneo creato: {tmp_file_path}")


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
            # Passa il file temporaneo con i pattern
            result = subprocess.run(
                ["../cpp-iseql/build/src/iseql", "detection-pattern", tmp_file_path],
                check=True,
                capture_output=True,
                text=True
            )

            # Parse output C++: ogni riga -> pattern,freq
            parsed = []
            lines = result.stdout.strip().split("\n")
            for line in lines:
                parts = line.strip().split(",")
                if len(parts) < 2:
                    continue
                pattern = parts[:-1]
                freq = int(parts[-1])
                parsed.append((pattern, freq))

        else:
            # vecchio comportamento per time-swing / extremely-time-swing
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
        parsed_top_k_pattern = run_and_parse("detection_pattern")

    except subprocess.CalledProcessError as e:
        print(f"Errore durante l'esecuzione del programma C: {e}")




    iseq = ISEQL()
    for interval_labeling in results[0]:
        duration = interval_labeling[2] - interval_labeling[1]
        interval_iseql = Interval(interval_labeling[1], interval_labeling[2], interval_labeling[0],
                                  interval_labeling[3], duration)
        iseq.add_interval(interval_iseql)


    # Process results
    #time_swings = iseq.find_time_swing()
    time_swing_duration = iseq.find_time_swing_with_too_long_glucose_anomalies()
    time_swings_too_frequent = iseq.find_too_frequent_time_swings()

    extremely_time_swing_duration = iseq.find_extremely_time_swing_with_too_long_glucose_anomalies()
    extremely_time_swings_too_frequent = iseq.find_too_frequent_extremely_time_swings()

    anomalous_frequency = iseq.find_too_frequent_glucose_anomalies()
    anomalous_duration = iseq.find_too_long_glucose_anomalies()

    '''
             'time_swing': [
            {
                'day': format_day(time_swing[0].start_time.date()),
                'first_event': time_swing[0].event,
                'second_event': time_swing[1].event,
                'duration_time_swing': format_duration(time_swing[1].start_time - time_swing[0].end_time)
            }
            for time_swing in time_swings
        ],
        
        '''

    result = {

        'avg': avg,
        'gmi': gmi,

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
            in
            anomalous_frequency
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
                'day': format_day(intrvl.start_time.date()),  # Adjusted to match 'Day' in time_swing_too_frequent
                'event': intrvl.event,
                'start_time': format_datetime(intrvl.start_time),  # Adjusted to match 'Start time' in format
                'end_time': format_datetime(intrvl.end_time),  # Adjusted to match 'End time' in format
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
        'top_k_pattern' : [
            {
                'pattern': pattern,
                'freq': freq,
            }
            for pattern, freq in parsed_top_k_pattern
        ]

    }

    date_column = 'Data e ora (AAAA-MM-GGThh:mm:ss)'

    # Convert the date column to datetime format
    glucose_data_copia[date_column] = pd.to_datetime(glucose_data_copia[date_column], format='%Y-%m-%dT%H:%M:%S')
    first_date = glucose_data_copia[date_column].iloc[0]  # Row 19 (0-based index)
    last_date = glucose_data_copia[date_column].iloc[-1]  # Last row
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
