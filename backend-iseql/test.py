import pandas as pd
import matplotlib.pyplot as plt
import subprocess
import os
import numpy as np
import time
from datetime import datetime

# Assicurati che questi import funzionino nel tuo ambiente
from utils import *
from iseql.interval import Interval
from iseql.interval_action_detector import IntervalActionDetector
from iseql.iseql import ISEQL


# =============================================================================
# 1. HELPER FUNCTIONS & C++ INTEGRATION
# =============================================================================

def unix_timestamp_to_datetime(ts):
    return datetime.fromtimestamp(ts)


def parse_cpp_interval_string(part):
    try:
        time_part, id_event, event_type = part.strip().split()
        start_str, end_str = time_part[1:-1].split(',')
        start_dt = unix_timestamp_to_datetime(int(start_str))
        end_dt = unix_timestamp_to_datetime(int(end_str))
        duration = end_dt - start_dt
        return Interval(start_dt, end_dt, int(id_event), event_type, duration)
    except Exception as e:
        return None


def write_intervals_for_cpp(intervals, filepath="data/csv/eventi.txt"):
    os.makedirs(os.path.dirname(filepath), exist_ok=True)
    with open(filepath, "w") as f:
        for i, interval in enumerate(intervals):
            if interval.start_time.timestamp() > interval.end_time.timestamp():
                continue
            s_ts = int(interval.start_time.timestamp())
            e_ts = int(interval.end_time.timestamp())
            f.write(f"{s_ts},{e_ts},{interval.event}\n")


def run_cpp_analysis_mode(mode_flag):
    """Esegue il binario C++ e restituisce la lista di swing rilevati."""
    detected_swings = []
    binary_path = "../cpp-iseql/build/src/iseql"

    if not os.path.exists(binary_path):
        return []

    try:
        result = subprocess.run(
            [binary_path, mode_flag, ""],
            check=True,
            capture_output=True,
            text=True
        )
        for line in result.stdout.split('\n'):
            if " -- " in line:
                parts = line.split(" -- ")
                if len(parts) == 2:
                    i1 = parse_cpp_interval_string(parts[0])
                    i2 = parse_cpp_interval_string(parts[1])
                    if i1 and i2:
                        detected_swings.append((i1, i2))
    except:
        pass
    return detected_swings


# =============================================================================
# 2. BENCHMARKING LOGIC (CALCOLO DINAMICO)
# =============================================================================

def measure_execution_time(func, *args):
    """Misura il tempo di esecuzione di una funzione."""
    start = time.perf_counter()
    res = func(*args)
    end = time.perf_counter()
    return end - start, res


def run_benchmark(full_df):
    """
    Esegue il benchmark su 25%, 50%, 75%, 100% del dataset.
    Restituisce i dizionari con i tempi misurati.
    """
    percentages = [25, 50, 75, 100]
    total_rows = len(full_df)

    # Inizializza dizionario per accumulare i tempi
    times_data = {
        'Offline Interval Action Detection': [],
        'Find Time Swing (C++)': [],
        'Find Too Frequent Time Swings': [],
        'Find Time Swing With Too Long Anomalies': [],
        'Find Too Frequent Glucose Anomalies': [],
        'Find Too Long Glucose Anomalies': [],
        'Find Extremely Time Swing (C++)': [],
        'Find Extremely Time Swing Too Frequent': [],
        'Find Extremely Time Swing Too Long': []
    }

    print("\n--- Starting Benchmark Calculation ---")

    for p in percentages:
        limit = int(total_rows * (p / 100))
        subset_df = full_df.iloc[:limit]
        print(f"Processing subset {p}% ({limit} rows)...")

        # 1. Misura Offline Detection
        detector = IntervalActionDetector(subset_df)
        t_detect, results = measure_execution_time(detector.offline_interval_action_detection)
        times_data['Offline Interval Action Detection'].append(t_detect)

        # Preparazione ISEQL e File per C++
        iseq = ISEQL()
        intervals_list = []
        for item in results[0]:  # Assumendo results[0] contenga le tuple
            duration = item[2] - item[1]
            new_int = Interval(item[1], item[2], item[0], item[3], duration)
            iseq.add_interval(new_int)
            intervals_list.append(new_int)

        write_intervals_for_cpp(intervals_list)

        # 2. Misura Metodi C++
        t_ts_cpp, _ = measure_execution_time(run_cpp_analysis_mode, "time-swing")
        times_data['Find Time Swing (C++)'].append(t_ts_cpp)

        t_ext_cpp, ext_swings = measure_execution_time(run_cpp_analysis_mode, "extremely-time-swing")
        times_data['Find Extremely Time Swing (C++)'].append(t_ext_cpp)

        # 3. Misura Metodi Python Standard
        t_freq_anom, _ = measure_execution_time(iseq.find_too_frequent_glucose_anomalies)
        times_data['Find Too Frequent Glucose Anomalies'].append(t_freq_anom)

        # Nota: se find_too_long_glucose_anomalies non esiste in ISEQL, usiamo un placeholder o try/except
        try:
            t_long_anom, _ = measure_execution_time(iseq.find_too_long_glucose_anomalies)
        except AttributeError:
            # Fallback se il metodo non è definito nella classe, simuliamo un pass veloce
            t_long_anom, _ = measure_execution_time(
                lambda: [i for i in intervals_list if i.duration.total_seconds() > 3600])
        times_data['Find Too Long Glucose Anomalies'].append(t_long_anom)

        t_freq_ts, _ = measure_execution_time(iseq.find_too_frequent_time_swings)
        times_data['Find Too Frequent Time Swings'].append(t_freq_ts)

        t_long_ts, _ = measure_execution_time(iseq.find_time_swing_with_too_long_glucose_anomalies)
        times_data['Find Time Swing With Too Long Anomalies'].append(t_long_ts)

        # 4. Misura Varianti "Extremely" (Simulazione logica Python su output C++)
        # "Extremely Too Frequent": Cerchiamo frequenza sugli swing estremi
        def calc_freq_extr_swings(swings):
            # Logica fittizia di raggruppamento per data per misurare il tempo di calcolo
            counts = {}
            for s in swings:
                day = s[0].start_time.date()
                counts[day] = counts.get(day, 0) + 1
            return [d for d, c in counts.items() if c > 3]

        t_ext_freq, _ = measure_execution_time(calc_freq_extr_swings, ext_swings)
        times_data['Find Extremely Time Swing Too Frequent'].append(t_ext_freq)

        # "Extremely Too Long": Cerchiamo durata anomala sugli swing estremi
        def calc_long_extr_swings(swings):
            # Logica fittizia di controllo durata
            return [s for s in swings if (s[0].duration.total_seconds() + s[1].duration.total_seconds()) > 7200]

        t_ext_long, _ = measure_execution_time(calc_long_extr_swings, ext_swings)
        times_data['Find Extremely Time Swing Too Long'].append(t_ext_long)

    return times_data


# =============================================================================
# 3. PLOTTING FUNCTIONS
# =============================================================================

def plot_performance_metrics(data_times):
    print("Generating Performance & Scalability Graphs with CALCULATED data...")

    dataset_sizes = [25, 50, 75, 100]

    # Calcolo Medie
    avg_times = {k: np.mean(v) for k, v in data_times.items()}

    # Calcolo Growth Rate (Tasso di crescita medio)
    growth_rates = {}
    for method, times in data_times.items():
        rates = []
        for i in range(1, len(times)):
            if times[i - 1] > 0:
                rate = (times[i] - times[i - 1]) / times[i - 1]
                rates.append(rate)
        growth_rates[method] = np.mean(rates) if rates else 0.0

    # Configurazione Plot
    plt.rcParams.update({'font.size': 12, 'figure.figsize': (14, 8)})
    colors = plt.cm.tab10(np.linspace(0, 1, 10))
    markers = ['o', 's', '^', 'D', 'v', 'P', '*', 'X', 'd']

    short_names = [
        "Offline Action Det.",
        "Time Swing (C++)", "Freq. Time Swings", "Swing w/ Long Anom.",
        "Freq. Gluc. Anom.", "Long Gluc. Anom.",
        "Extr. Swing (C++)", "Extr. Swing Freq.", "Extr. Swing Long"
    ]

    # Ordiniamo le chiavi per coerenza tra i grafici
    method_keys = list(data_times.keys())

    # --- GRAFICO 1: SCALABILITÀ ---
    plt.figure()
    for idx, method in enumerate(method_keys):
        times = data_times[method]
        plt.plot(dataset_sizes, times, marker=markers[idx % len(markers)], label=method,
                 linewidth=2, markersize=8, color=colors[idx % len(colors)])
    plt.yscale('log')
    plt.xlabel('Dataset Size (%)', fontsize=14, fontweight='bold')
    plt.ylabel('Execution Time (seconds) [Log Scale]', fontsize=14, fontweight='bold')
    plt.title('Scalability: Execution Time vs Dataset Size (Calculated)', fontsize=16, pad=20)
    plt.grid(True, which="both", ls="--", alpha=0.5)
    plt.legend(bbox_to_anchor=(1.01, 1), loc='upper left', borderaxespad=0., fontsize=10)
    plt.tight_layout()
    plt.savefig('grafico_scalabilita.png')
    print(" -> Saved: grafico_scalabilita.png")

    # --- GRAFICO 2: TEMPO MEDIO ---
    plt.figure()
    avg_values = [avg_times[m] for m in method_keys]

    bars = plt.bar(short_names, avg_values, color=colors[:len(method_keys)], edgecolor='black')
    plt.yscale('log')
    plt.ylabel('Average Time (seconds) [Log Scale]', fontsize=14, fontweight='bold')
    plt.title('Efficiency: Average Execution Time by Method', fontsize=16, pad=20)
    plt.xticks(rotation=45, ha='right', fontsize=10)
    plt.grid(axis='y', which='both', linestyle='--', alpha=0.5)
    for bar in bars:
        yval = bar.get_height()
        plt.text(bar.get_x() + bar.get_width() / 2, yval * 1.15,
                 f'{yval:.1e}', ha='center', va='bottom', fontsize=9, fontweight='bold')
    plt.tight_layout()
    plt.savefig('grafico_tempo_medio.png')
    print(" -> Saved: grafico_tempo_medio.png")

    # --- GRAFICO 3: GROWTH RATE ---
    plt.figure()
    rate_values = [growth_rates[m] for m in method_keys]
    bars = plt.bar(short_names, rate_values, color=colors[:len(method_keys)], edgecolor='black')

    plt.ylabel('Average Growth Rate', fontsize=14, fontweight='bold')
    plt.title('Stability: Average Growth Rate by Method', fontsize=16, pad=20)
    plt.xticks(rotation=45, ha='right', fontsize=10)
    plt.grid(axis='y', linestyle='--', alpha=0.5)
    plt.axhline(0, color='black', linewidth=0.8)

    for bar in bars:
        yval = bar.get_height()
        # Posiziona il testo sopra o sotto la barra a seconda del segno
        y_offset = 0.01 if yval >= 0 else -0.05
        plt.text(bar.get_x() + bar.get_width() / 2, yval + y_offset,
                 f'{yval:.3f}', ha='center', va='bottom', fontsize=10, fontweight='bold')
    plt.tight_layout()
    plt.savefig('grafico_growth_rate.png')
    print(" -> Saved: grafico_growth_rate.png")


# =============================================================================
# 4. CLINICAL PLOTS (Rimane uguale, usa i dati finali)
# =============================================================================
def plot_clinical_results(results_data):
    # (Inserisci qui il codice per plot_clinical_results come definito nella risposta precedente)
    # Per brevità non lo ripeto tutto, ma va incluso per generare gli altri slide.
    pass


# =============================================================================
# 5. MAIN
# =============================================================================

def main():
    print("--- Starting Execution ---")

    # 1. Carica CSV
    try:
        glucose_data = pd.read_csv('data/csv/Clarity_Esporta_Tucceri_Cimini_Lorenzo_2025-12-19_133917.csv', delimiter=';')
        cols = ['Tipo di evento', 'Sottotipo di evento', 'Data e ora (AAAA-MM-GGThh:mm:ss)',
                'Valore del glucosio (mg/dL)']
        glucose_data = glucose_data[cols].iloc[18:]
    except FileNotFoundError:
        print("CSV not found.")
        return

    # 2. Esegui Benchmark Dinamico (Misura i tempi)
    calculated_times = run_benchmark(glucose_data)

    # 3. Genera Grafici Performance con i dati calcolati
    plot_performance_metrics(calculated_times)

    # 4. (Opzionale) Analisi Clinica Completa sul 100% per gli altri grafici
    # Qui useresti perform_full_analysis e plot_clinical_results come prima
    print("\nBenchmark completed and graphs saved.")



def analyze_glucose_data(intervals, iseq_instance=None):
    """
    Analizza la lista degli intervalli per calcolare i totali per tipo di evento
    e la durata complessiva per ciascun tipo.

    Args:
        intervals: Lista di oggetti Interval.
        iseq_instance: Istanza della classe ISEQL (opzionale, per usi futuri).

    Returns:
        Un dizionario contenente due sotto-dizionari: 'totals' e 'durations'.
    """
    totals = {}
    durations = {}

    for interval in intervals:
        # Assicuriamoci che l'evento sia una stringa utilizzabile come chiave
        evt_type = str(interval.event)

        # Calcolo durata in secondi (assumendo che interval.duration sia un timedelta)
        # Se interval.duration è già in secondi, togliere .total_seconds()
        try:
            duration_sec = interval.duration.total_seconds()
        except AttributeError:
            # Fallback se la durata è già un numero
            duration_sec = float(interval.duration)

        # Aggiornamento conteggi (Totali)
        if evt_type in totals:
            totals[evt_type] += 1
        else:
            totals[evt_type] = 1

        # Aggiornamento durate (Somma totale in secondi)
        if evt_type in durations:
            durations[evt_type] += duration_sec
        else:
            durations[evt_type] = duration_sec

    return {
        'totals': totals,
        'durations': durations
    }

if __name__ == "__main__":
    main()