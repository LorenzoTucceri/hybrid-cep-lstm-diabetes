import matplotlib

from interval import Interval
from interval_action_detector import IntervalActionDetector
from iseql import ISEQL
import pandas as pd
import time
import matplotlib.pyplot as plt
font = {'size': 14}
matplotlib.rc('font', **font)


def calculate_totals_and_durations(intervals):
    """Calculate totals and durations for glucose intervals and return as a dictionary."""
    # Calculate totals for each glucose category
    total_extremely_high = sum(1 for interval in intervals if interval.event == 'extremely_high')
    total_high = sum(1 for interval in intervals if interval.event == 'high')
    total_low = sum(1 for interval in intervals if interval.event == 'low')
    total_extremely_low = sum(1 for interval in intervals if interval.event == 'extremely_low')
    total_normal = sum(1 for interval in intervals if interval.event == 'normal')

    # Calculate total duration for each glucose category
    total_duration_extremely_high = sum((interval.end_time - interval.start_time).total_seconds() for interval in intervals if interval.event == 'extremely_high')
    total_duration_high = sum((interval.end_time - interval.start_time).total_seconds() for interval in intervals if interval.event == 'high')
    total_duration_low = sum((interval.end_time - interval.start_time).total_seconds() for interval in intervals if interval.event == 'low')
    total_duration_extremely_low = sum((interval.end_time - interval.start_time).total_seconds() for interval in intervals if interval.event == 'extremely_low')
    total_duration_normal = sum((interval.end_time - interval.start_time).total_seconds() for interval in intervals if interval.event == 'normal')

    # Conversion from seconds to "HH:MM" format
    def convert_seconds_to_hhmm(seconds):
        hours = int(seconds // 3600)
        minutes = int((seconds % 3600) // 60)
        return f"{hours:02}:{minutes:02}"

    duration_extremely_high = convert_seconds_to_hhmm(total_duration_extremely_high)
    duration_high = convert_seconds_to_hhmm(total_duration_high)
    duration_low = convert_seconds_to_hhmm(total_duration_low)
    duration_extremely_low = convert_seconds_to_hhmm(total_duration_extremely_low)
    duration_normal = convert_seconds_to_hhmm(total_duration_normal)

    # Print results for verification
    print(f"Total extremely high: {total_extremely_high}")
    print(f"Total high: {total_high}")
    print(f"Total low: {total_low}")
    print(f"Total extremely low: {total_extremely_low}")
    print(f"Total normal: {total_normal}")

    print(f"Total duration extremely high: {duration_extremely_high}")
    print(f"Total duration high: {duration_high}")
    print(f"Total duration low: {duration_low}")
    print(f"Total duration extremely low: {duration_extremely_low}")
    print(f"Total duration normal: {duration_normal}")

    # Calculate percentage of time for normal intervals
    total_duration_all = sum((interval.end_time - interval.start_time).total_seconds() for interval in intervals)
    percentage_duration_normal = (total_duration_normal / total_duration_all) * 100 if total_duration_all > 0 else 0

    print(f"Percentage of normal duration: {percentage_duration_normal:.2f}%")

    return {
        'duration_normal': duration_normal,
        'duration_high': duration_high,
        'duration_low': duration_low,
        'duration_extremely_high': duration_extremely_high,
        'duration_extremely_low': duration_extremely_low,
        'total_extremely_high': total_extremely_high,
        'total_normal': total_normal,
        'total_high': total_high,
        'total_low': total_low,
        'total_extremely_low': total_extremely_low,
    }


def convert_hhmm_to_minutes(hhmm):
    """Convert duration in HH:MM format to total minutes."""
    hours, minutes = map(int, hhmm.split(':'))
    return hours * 60 + minutes


def calculate_percentages(results):
    """Calculate and print percentages of durations for each glucose category."""
    # Convert durations from "HH:MM" to minutes
    total_duration_extremely_high_minutes = convert_hhmm_to_minutes(results['duration_extremely_high'])
    total_duration_high_minutes = convert_hhmm_to_minutes(results['duration_high'])
    total_duration_low_minutes = convert_hhmm_to_minutes(results['duration_low'])
    total_duration_extremely_low_minutes = convert_hhmm_to_minutes(results['duration_extremely_low'])
    total_duration_normal_minutes = convert_hhmm_to_minutes(results['duration_normal'])

    # Calculate total duration in minutes
    total_duration = (
            total_duration_extremely_high_minutes +
            total_duration_high_minutes +
            total_duration_low_minutes +
            total_duration_extremely_low_minutes +
            total_duration_normal_minutes
    )

    # Calculate percentages
    percentage_extremely_high = (
                                            total_duration_extremely_high_minutes / total_duration) * 100 if total_duration > 0 else 0
    percentage_high = (total_duration_high_minutes / total_duration) * 100 if total_duration > 0 else 0
    percentage_low = (total_duration_low_minutes / total_duration) * 100 if total_duration > 0 else 0
    percentage_extremely_low = (
                                           total_duration_extremely_low_minutes / total_duration) * 100 if total_duration > 0 else 0
    percentage_normal = (total_duration_normal_minutes / total_duration) * 100 if total_duration > 0 else 0

    # Print results
    print(f"Percentage of extremely high glucose intervals: {percentage_extremely_high:.2f}%")
    print(f"Percentage of high glucose intervals: {percentage_high:.2f}%")
    print(f"Percentage of low glucose intervals: {percentage_low:.2f}%")
    print(f"Percentage of extremely low glucose intervals: {percentage_extremely_low:.2f}%")
    print(f"Percentage of normal glucose intervals: {percentage_normal:.2f}%")


def find_too_frequent_glucose_anomalies_data(iseql):
    """Retrieve and print anomalous frequency data."""
    result = iseql.find_too_frequent_glucose_anomalies()

    # Initialize a dictionary for the data
    data = {
        "Day": [],
        "High Count": [],
        "Low Count": [],
        "Extremely High Count": [],
        "Extremely Low Count": [],
        "Total Count": [],
    }


    # Populate the dictionary with data from anomalous frequency
    for day in result:
        start_time, end_time = day[0], day[1]
        high_count = day[2]
        low_count = day[3]
        extremely_high_count = day[4]
        extremely_low_count = day[5]
        total_count = day[6]


        data["Day"].append(start_time)
        data["High Count"].append(high_count)
        data["Low Count"].append(low_count)
        data["Extremely High Count"].append(extremely_high_count)
        data["Extremely Low Count"].append(extremely_low_count)
        data["Total Count"].append(total_count)

    # Find the day with the maximum total count
    max_total_count_index = data["Total Count"].index(max(data["Total Count"]))
    max_total_count_day = data["Day"][max_total_count_index]
    max_total_count_data = {
        "High Count": data["High Count"][max_total_count_index],
        "Low Count": data["Low Count"][max_total_count_index],
        "Extremely High Count": data["Extremely High Count"][max_total_count_index],
        "Extremely Low Count": data["Extremely Low Count"][max_total_count_index],
        "Total Count": data["Total Count"][max_total_count_index],
    }

    formatted_date = max_total_count_day.strftime('%Y-%m-%d')

    # Print results
    print(f"Day with the most anomalies: {formatted_date}")
    print(f"Details: {max_total_count_data}")


def calculate_time_swing_statistics(iseql):
    """Calculate and print statistics about glycemic swings and anomalies."""
    total_time_swings = len(iseql.find_time_swing())

    # Trova le oscillazioni glicemiche seguite da frequenza anomala
    total_too_frequent_time_swings = iseql.find_too_frequent_time_swings()
    total_too_frequent_time_swings = len(total_too_frequent_time_swings)


    # Calcola le percentuali mancanti
    percentage_total_too_frequent_time_swings = (total_too_frequent_time_swings / total_time_swings) * 100
    # Troviamo le oscillazioni seguite da durata anomala
    time_swing_too_long = iseql.find_time_swing_with_too_long_glucose_anomalies()
    total_time_swing_too_long = len(time_swing_too_long)

    # Calcoliamo la percentuale di oscillazioni seguite da durata anomala
    if total_time_swings > 0:
        percentage_time_swing_too_long = (total_time_swing_too_long / total_time_swings) * 100
    else:
        percentage_time_swing_too_long = 0.0

    # Stampa dei risultati
    print(f"Total significant Time Swing: {total_time_swings}")
    print(f"Total Time Swings With Too Long Glucose Anomalies: {total_time_swing_too_long}")
    print(f"Percentage of Time Swings With Too Long Glucose Anomalies: {percentage_time_swing_too_long:.2f}%")
    print(f"Total Too Frequent Time Swing: {total_too_frequent_time_swings}")
    print(f"Percentage of Too Frequent Time Swing: {percentage_total_too_frequent_time_swings:.2f}%\n")



def evaluate_efficiency_and_scalability(glucose_data, percentages=None):
    if percentages is None:
        percentages = [25, 50, 75, 100]

    print("Loading data...")

    total_rows = len(glucose_data)
    execution_times = {method: [] for method in [
        'offline_interval_action_detection',
        'find_time_swing',
        'find_too_frequent_glucose_anomalies',
        'find_too_frequent_time_swings',
        'find_too_long_glucose_anomalies',
        'find_time_swing_with_too_long_glucose_anomalies',
    ]}

    for percent in percentages:
        print(f"\nTesting with {percent}% of the dataset ({total_rows * percent // 100} rows)...")
        subset_size = total_rows * percent // 100
        subset_data = glucose_data.iloc[:subset_size]

        print("Creating the analyzer and detecting interval actions...")
        analyzer = IntervalActionDetector(subset_data)

        start_time = time.perf_counter()
        results = analyzer.offline_interval_action_detection()
        end_time = time.perf_counter()
        execution_times['offline_interval_action_detection'].append(end_time - start_time)
        print(f"Execution time for offline_interval_action_detection: {end_time - start_time:.4f} seconds")

        print("Creating ISEQL instance and adding intervals...")
        iseq = ISEQL()
        for interval_labeling in results[0]:
            duration = interval_labeling[2] - interval_labeling[1]
            interval_iseql = Interval(interval_labeling[1], interval_labeling[2], interval_labeling[0], interval_labeling[3], duration)
            iseq.add_interval(interval_iseql)

        intervals = iseq.get_intervals()

        def measure_method_execution_time(method_name, method, *args, **kwargs):
            repetitions = 1000  # Number of repetitions
            start_time = time.perf_counter()
            for _ in range(repetitions):
                if callable(method):
                    result = method(*args, **kwargs)
            end_time = time.perf_counter()
            total_time = (end_time - start_time) / repetitions
            execution_times[method_name].append(total_time)
            print(f"{method_name} execution time: {total_time:.8f} seconds per call")

            return result

        methods_to_measure = [
            ('find_time_swing', iseq.find_time_swing),
            ('find_too_frequent_glucose_anomalies', iseq.find_too_frequent_glucose_anomalies),
            ('find_too_frequent_time_swings', iseq.find_too_frequent_time_swings),
            ('find_too_long_glucose_anomalies', iseq.find_too_long_glucose_anomalies),
            ('find_time_swing_with_too_long_glucose_anomalies', iseq.find_time_swing_with_too_long_glucose_anomalies),
        ]

        for method_name, method in methods_to_measure:
            measure_method_execution_time(method_name, method)

    print("\nAverage execution times:")
    avg_execution_times = {}
    for method, times in execution_times.items():
        if times:
            average_time = sum(times) / len(times)
            avg_execution_times[method] = average_time
            print(f"{method}: {average_time:.8f} seconds")

    print("\nAverage growth rate of execution times:")
    growth_rates = {}
    for method, times in execution_times.items():
        if len(times) > 1:
            growth_rates[method] = []
            for i in range(1, len(times)):
                if times[i - 1] > 0:  # Avoid division by zero
                    growth_rate = (times[i] - times[i - 1]) / times[i - 1]
                    growth_rates[method].append(growth_rate)
            average_growth_rate = sum(growth_rates[method]) / len(growth_rates[method])
            print(f"{method}: {average_growth_rate:.4f}")
        else:
            print(f"{method}: Not enough data to compute growth rate.")

    print("\nFinal considerations:")
    print("Average execution times for each method have been calculated.")
    print("Average growth rates show how execution times increase with the size of the dataset.")
    for method, avg_time in avg_execution_times.items():
        print(f"Method: {method}, Average time: {avg_time:.8f} seconds")

    for method, growth_rate_list in growth_rates.items():
        if growth_rate_list:
            average_growth_rate = sum(growth_rate_list) / len(growth_rate_list)
            print(f"Method: {method}, Average growth rate: {average_growth_rate:.4f}")
        else:
            print(f"Method: {method}, No growth rate data available.")
def analyze_glucose_data(intervals, iseql):
    from datetime import timedelta

    def convert_seconds_to_hhmm(seconds):
        hours = int(seconds // 3600)
        minutes = int((seconds % 3600) // 60)
        return f"{hours:02}:{minutes:02}"

    def convert_hhmm_to_minutes(hhmm):
        hours, minutes = map(int, hhmm.split(':'))
        return hours * 60 + minutes

    def format_timedelta(td):
        """Convert a timedelta object to a string in HH:MM format."""
        if isinstance(td, timedelta):
            total_seconds = td.total_seconds()
            return convert_seconds_to_hhmm(total_seconds)
        return str(td)

    # Calculate totals and durations for glucose categories
    categories = ['extremely_high', 'high', 'low', 'extremely_low', 'normal']
    totals = {category: 0 for category in categories}
    durations = {category: 0 for category in categories}

    for interval in intervals:
        category = interval.event
        if category in categories:
            totals[category] += 1
            duration = (interval.end_time - interval.start_time).total_seconds()
            durations[category] += duration

    # Convert durations to HH:MM format
    durations_hhmm = {category: convert_seconds_to_hhmm(durations[category]) for category in durations}

    # Calculate total duration and percentage of normal intervals
    total_duration_all_seconds = sum(durations.values())
    total_duration_normal_seconds = durations['normal']
    percentage_normal = (
                                    total_duration_normal_seconds / total_duration_all_seconds) * 100 if total_duration_all_seconds > 0 else 0

    # Calculate percentages for each category
    durations_minutes = {category: convert_hhmm_to_minutes(durations_hhmm[category]) for category in durations_hhmm}
    total_duration_minutes = sum(durations_minutes.values())
    percentages = {
        category: (durations_minutes[category] / total_duration_minutes) * 100 if total_duration_minutes > 0 else 0 for
        category in categories}

    # Find the day with the most anomalies
    anomalous_frequency_data = iseql.find_too_frequent_glucose_anomalies()
    data = {category: [] for category in
            ["Day", "High Count", "Low Count", "Extremely High Count", "Extremely Low Count", "Total Count"]}
    for day_data in anomalous_frequency_data:
        start_time, end_time, high_count, low_count, extremely_high_count, extremely_low_count, total_count = day_data
        data["Day"].append(start_time)
        data["High Count"].append(high_count)
        data["Low Count"].append(low_count)
        data["Extremely High Count"].append(extremely_high_count)
        data["Extremely Low Count"].append(extremely_low_count)
        data["Total Count"].append(total_count)

    max_total_count_index = data["Total Count"].index(max(data["Total Count"]))
    max_total_count_day = data["Day"][max_total_count_index]
    max_total_count_data = {
        "High Count": data["High Count"][max_total_count_index],
        "Low Count": data["Low Count"][max_total_count_index],
        "Extremely High Count": data["Extremely High Count"][max_total_count_index],
        "Extremely Low Count": data["Extremely Low Count"][max_total_count_index],
        "Total Count": data["Total Count"][max_total_count_index],
    }
    formatted_date = max_total_count_day.strftime('%Y-%m-%d')

    # Calculate glycemic swings and anomalies statistics
    time_swings = iseql.find_time_swing()
    total_time_swings = len(time_swings)

    time_swing_too_long = iseql.find_time_swing_with_too_long_glucose_anomalies()
    total_time_swing_too_long = len(time_swing_too_long)

    percentage_time_swing_too_long = (
                                             total_time_swing_too_long / total_time_swings) * 100 if total_time_swings > 0 else 0

    total_too_frequent_time_swings = iseql.find_too_frequent_time_swings()
    total_too_frequent_time_swings = len(total_too_frequent_time_swings)
    percentage_too_frequent_time_swings = (
                                                 total_too_frequent_time_swings / total_time_swings) * 100 if total_time_swings > 0 else 0

    # Returning all results as a dictionary
    return {
        'totals': totals,
        'durations_hhmm': durations_hhmm,
        'percentages': percentages,
        'percentage_normal': percentage_normal,
        'max_anomalous_day': {
            'date': formatted_date,
            'details': max_total_count_data,
        },
        'time_swings_stats': {
            'total_time_swings': total_time_swings,
            'total_time_swing_too_long': total_time_swing_too_long,
            'percentage_time_swing_too_long': percentage_time_swing_too_long,
            'total_too_frequent_time_swings': total_too_frequent_time_swings,
            'percentage_too_frequent_time_swings': percentage_too_frequent_time_swings,
        },
    }


def draw_graphs(dataset_sizes, times_offline, times_find_time_swing, times_find_too_frequent_glucose_anomalies,
                times_find_too_frequent_time_swings, times_find_too_long_glucose_anomalies,
                times_find_time_swing_with_too_long_glucose_anomalies, growth_rates):
    plt.figure(figsize=(12, 8))

    # Colori vivaci e markers più grandi
    plt.plot(dataset_sizes, times_find_time_swing_with_too_long_glucose_anomalies, marker='o', linestyle='--',
             color='purple', label='Find Time Swing With Too Long Glucose Anomalies')
    plt.plot(dataset_sizes, times_offline, marker='o', linestyle='-', color='blue',
             label='Offline Interval Action Detection')
    plt.plot(dataset_sizes, times_find_time_swing, marker='s', linestyle='--', color='green', label='Find Time Swing')
    plt.plot(dataset_sizes, times_find_too_frequent_glucose_anomalies, marker='^', linestyle='-.', color='red',
             label='Find Too Frequent Glucose Anomalies')
    plt.plot(dataset_sizes, times_find_too_frequent_time_swings, marker='d', linestyle=':', color='cyan',
             label='Find Too Frequent Time Swings')
    plt.plot(dataset_sizes, times_find_too_long_glucose_anomalies, marker='x', linestyle='-', color='magenta',
             label='Find Too Long Glucose Anomalies')

    plt.xlabel('Dataset Size (%)', fontsize=14)
    plt.ylabel('Execution Time (seconds)', fontsize=14)
    plt.title('Execution Time vs Dataset Size for Each Method', fontsize=16)
    plt.yscale('log')  # Usa la scala logaritmica per visualizzare chiaramente le piccole differenze
    plt.legend(fontsize=12)
    plt.grid(True, which='both', linestyle='--', linewidth=0.5)
    plt.tight_layout()
    plt.savefig('./data/plot/execution_times.png')  # Salva il grafico come immagine
    plt.show()

    methods = list(growth_rates.keys())
    rates = list(growth_rates.values())

    plt.figure(figsize=(10, 6))
    plt.barh(methods, rates, color='skyblue')
    plt.xlabel('Average Growth Rate', fontsize=14)
    plt.title('Average Growth Rates of Execution Times', fontsize=16)
    plt.grid(True, linestyle='--', linewidth=0.5)
    plt.tight_layout()
    plt.savefig('./data/plot/growth_rates.png')  # Salva il grafico come immagine
    plt.show()
def main():
    # Loading data (modify the file path as necessary)
    print("Loading data...")
    glucose_data = pd.read_csv('./data/glucoseLevel.csv', delimiter=';')
    print("Data loaded successfully.\n")

    # Selecting specific columns and filtering data
    print("Selecting specific columns and filtering data...")
    specific_columns = ['Tipo di evento', 'Sottotipo di evento', 'Data e ora (AAAA-MM-GGThh:mm:ss)', 'Valore del glucosio (mg/dL)']
    glucose_data = glucose_data[specific_columns].iloc[18:]
    print("Columns selected and data filtered.\n")

    # Creating the analyzer and detecting actions over intervals
    print("Creating the analyzer and detecting actions over intervals...")
    analyzer = IntervalActionDetector(glucose_data)
    results = analyzer.offline_interval_action_detection()
    print("Interval actions detection completed.\n")

    # Creating an instance of ISEQL and adding the intervals
    print("Creating ISEQL instance and adding intervals...")
    iseq = ISEQL()
    for interval_labeling in results[0]:
        duration = interval_labeling[2] - interval_labeling[1]
        interval_iseql = Interval(interval_labeling[1], interval_labeling[2], interval_labeling[0],
                                  interval_labeling[3], duration)
        iseq.add_interval(interval_iseql)
    intervals = iseq.intervals  # Assuming iseq.intervals contains the intervals
    print("Intervals added to ISEQL.\n")

    # Calculating totals and durations
    print("Calculating totals and durations...")
    results = calculate_totals_and_durations(intervals)
    print("Totals and durations calculated.\n")

    # Calculating and printing percentages
    print("Calculating and printing percentages...")
    calculate_percentages(results)
    print("Percentages calculated and printed.\n")

    # Finding and printing anomalous frequency data
    print("Finding and printing too frequency data...")
    find_too_frequent_glucose_anomalies_data(iseq)
    print("Too frequency data found and printed.\n")

    # Calculating and printing statistics on glycemic swings
    print("Calculating and printing statistics on time swings...")
    calculate_time_swing_statistics(iseq)
    print("Time swings statistics calculated and printed.\n")

    print("Scalability and efficiency of the program.\n")
    evaluate_efficiency_and_scalability(glucose_data)

    # Dati raccolti dinamicamente
    dataset_sizes = [25, 50, 75, 100]
    times_offline = [0.1405, 0.1936, 0.1950, 0.1940]
    times_find_time_swing = [0.00007130, 0.00012424, 0.00012253, 0.00012257]
    times_find_too_frequent_glucose_anomalies = [0.00013599, 0.00021680, 0.00021455, 0.00021596]
    times_find_too_frequent_time_swings = [0.00117088, 0.00282235, 0.00280677, 0.00281274]
    times_find_too_long_glucose_anomalies = [0.00001801, 0.00002962, 0.00002929, 0.00002938]
    times_find_time_swing_with_too_long_glucose_anomalies = [0.00011716, 0.00022015, 0.00021827, 0.00021965]

    # Dati di tasso di crescita medio
    growth_rates = {
        'Offline Interval Action Detection': 0.1267,
        'Find Time Swing': 0.2431,
        'Find Too Frequent Glucose Anomalies': 0.1968,
        'Find Too Frequent Time Swings': 0.4690,
        'Find Too Long Glucose Anomalies': 0.2123,
        'Find Time Swing With Too Long Glucose Anomalies': 0.2923
    }

    draw_graphs(dataset_sizes, times_offline, times_find_time_swing, times_find_too_frequent_glucose_anomalies,
                times_find_too_frequent_time_swings, times_find_too_long_glucose_anomalies,
                times_find_time_swing_with_too_long_glucose_anomalies, growth_rates)

def prova():
    gl = pd.read_csv('./data/glucoseLevel.csv', delimiter=';')

    colonne_specifiche = ['Tipo di evento', 'Sottotipo di evento', 'Data e ora (AAAA-MM-GGThh:mm:ss)',
                              'Valore del glucosio (mg/dL)']

    gl = gl[colonne_specifiche].iloc[18:]
    iseq = ISEQL()
    analyzer = IntervalActionDetector(gl)
    results = analyzer.offline_interval_action_detection()
    data = []
    iseql = ISEQL()
    for interval_labeling in results[0]:
        duration = interval_labeling[2] - interval_labeling[1]
        interval_iseql = Interval(interval_labeling[1], interval_labeling[2], interval_labeling[0],
                                  interval_labeling[3], duration)
        iseql.add_interval(interval_iseql)
        data.append({
            'Symbol': interval_labeling[0],
            'Start time': interval_labeling[1],
            'End time': interval_labeling[2],
            'Event': interval_labeling[3],
            'Duration': duration
        })
        # Adjusted function to use start_time and end_time correctly
        time_swings_too_frequent = iseql.find_too_frequent_time_swings()

        data = []
        for swing_set in time_swings_too_frequent:
            event_details = []
            for interval1, interval2 in swing_set:
                time_swing = interval2.start_time - interval1.end_time
                event_details.append({
                    'Day': f"{interval1.start_time.date()}",
                    'First event': interval1.event,
                    'Second event': interval2.event,
                    'Duration time swing': time_swing,
                })

            # Create a summary for each set of swings
            data.append({
                'Number of Time Swings': len(swing_set),
                'Events': event_details
            })

        dfgs = pd.DataFrame(data)
        dfgs.to_csv('time_swings_report.csv', index=False)

if __name__ == "__main__":
    main()

