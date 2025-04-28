from datetime import datetime

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import os
import analysis
from interval_action_detector import IntervalActionDetector
from iseql import ISEQL
from interval import Interval

app = Flask(__name__)
CORS(app)

def format_duration(duration):
    """Format duration as days, HH:mm:ss, and remove leading '0 days ' if not needed."""
    days, remainder = divmod(duration.total_seconds(), 86400)
    hours, remainder = divmod(remainder, 3600)
    minutes, seconds = divmod(remainder, 60)
    if days > 0:
        return f"{int(days)} days {int(hours):02}:{int(minutes):02}:{int(seconds):02}"
    return f"{int(hours):02}:{int(minutes):02}:{int(seconds):02}"

def format_datetime(dt):
    """Format datetime to 'HH:mm:ss'."""
    return dt.strftime('%H:%M:%S')

def format_day(dt):
    """Format datetime to 'Day, DD Month YYYY'."""
    return dt.strftime('%a, %d %b %Y')


@app.route('/process-csv', methods=['POST'])
def process_csv():
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

        # Apply filtering based on available dates
        if start_date and end_date:
            glucose_data = glucose_data[
                (glucose_data[date_column] >= start_date) & (glucose_data[date_column] <= end_date)]
        elif start_date:
            glucose_data = glucose_data[glucose_data[date_column] >= start_date]
        elif end_date:
            glucose_data = glucose_data[glucose_data[date_column] <= end_date]

    analyzer = IntervalActionDetector(glucose_data)
    results = analyzer.offline_interval_action_detection()

    iseq = ISEQL()
    for interval_labeling in results[0]:
        duration = interval_labeling[2] - interval_labeling[1]
        interval_iseql = Interval(interval_labeling[1], interval_labeling[2], interval_labeling[0],
                                  interval_labeling[3], duration)
        iseq.add_interval(interval_iseql)

    # Process results
    time_swings = iseq.find_time_swing()
    anomalous_frequency = iseq.find_too_frequent_glucose_anomalies()
    time_swings_too_frequent = iseq.find_too_frequent_time_swings()
    anomalous_duration = iseq.find_too_long_glucose_anomalies()
    time_swing_duration = iseq.find_time_swing_with_too_long_glucose_anomalies()

    # Format results
    result = {
        'time_swing': [
            {
                'day': format_day(time_swing[0].start_time.date()),
                'first_event': time_swing[0].event,
                'second_event': time_swing[1].event,
                'duration_time_swing': format_duration(time_swing[1].start_time - time_swing[0].end_time)
            }
            for time_swing in time_swings
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
    columns_specific = ['Tipo di evento', 'Sottotipo di evento', 'Data e ora (AAAA-MM-GGThh:mm:ss)', 'Valore del glucosio (mg/dL)']
    glucose_data = glucose_data[columns_specific].iloc[18:]

    # Extracting the date column
    date_column = 'Data e ora (AAAA-MM-GGThh:mm:ss)'

    # Convert the date column to datetime format
    glucose_data[date_column] = pd.to_datetime(glucose_data[date_column], format='%Y-%m-%dT%H:%M:%S')

    # Get the first and last date from the specified rows
    first_date = glucose_data[date_column].iloc[0]  # Row 19 (0-based index)
    last_date = glucose_data[date_column].iloc[-1]  # Last row

    return jsonify({
        'first_date': first_date.strftime('%Y-%m-%d %H:%M:%S'),
        'last_date': last_date.strftime('%Y-%m-%d %H:%M:%S')
    })

if __name__ == '__main__':
    app.run(debug=True)