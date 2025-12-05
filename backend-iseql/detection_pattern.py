from collections import Counter
import numpy as np
import pandas as pd
import utils

STATE_NAME = {
    "a": "EXTREMELY_HIGH",
    "b": "HIGH",
    "c": "NORMAL",
    "d": "LOW",
    "e": "EXTREMELY_LOW"
}

def is_two_state_alternating(pattern):
    def clean_state(x):
        return x.split("(")[0]

    cleaned = [clean_state(p) for p in pattern]
    unique_states = list(set(cleaned))

    if len(unique_states) != 2:
        return False

    exp1 = [unique_states[i % 2] for i in range(len(cleaned))]
    exp2 = [unique_states[(i + 1) % 2] for i in range(len(cleaned))]

    return cleaned == exp1 or cleaned == exp2


def normalize_sequence(sequence):
    """
    Accetta:
      (state, start, end, gravity)
      (state, duration)
      (state, duration, gravity)

    Restituisce: (state, duration_seconds, gravity)
    """

    seq_full = []
    for item in sequence:

        # ndarray -> lista
        if isinstance(item, np.ndarray):
            item = item.tolist()

        # Caso originale (state, duration)
        if len(item) == 2:
            s, d = item
            seq_full.append((s, d, 0.0))

        # Caso (state, duration, gravity)
        elif len(item) == 3 and not isinstance(item[1], pd.Timestamp):
            s, d, g = item
            seq_full.append((s, d, g))

        # Caso nuovo: (state, start_time, end_time, gravity)
        elif len(item) == 4:
            s, start, end, g = item
            duration = (end - start).total_seconds()
            seq_full.append((s, duration, g))

        else:
            raise ValueError(f"Formato non supportato: {item}")

    return seq_full


def extract_ngrams_with_time(sequence, n_list=[4, 5], use_duration=False, THRESHOLDS_DURATION=None):
    seq_norm = normalize_sequence(sequence)

    raw_states = [item[0] for item in sequence]
    raw_start  = [item[1] for item in sequence]
    raw_end    = [item[2] for item in sequence]

    durations = [
        utils.discretize_duration_by_state(s, d, THRESHOLDS_DURATION)
        if (use_duration and THRESHOLDS_DURATION) else ""
        for s, d, g in seq_norm
    ]

    ngrams = []

    for n in n_list:
        for i in range(len(sequence) - n + 1):

            pattern = tuple(
                f"{STATE_NAME[st]}({du})" if du else STATE_NAME[st]
                for st, du in zip(raw_states[i:i+n], durations[i:i+n])
            )

            start_time = raw_start[i]
            end_time   = raw_end[i + n - 1]
            total_dur  = (end_time - start_time).total_seconds()

            ngrams.append({
                "pattern": pattern,
                "start_time": start_time,
                "end_time": end_time,
                "total_duration": total_dur
            })

    return ngrams

def extract_patient_patterns(sequence, n_list=[4, 5], top_k=10,
                             use_duration=False, THRESHOLDS_DURATION=None):

    raw = extract_ngrams_with_time(
        sequence,
        n_list=n_list,
        use_duration=use_duration,
        THRESHOLDS_DURATION=THRESHOLDS_DURATION
    )

    # Rimuovo n-gram alternanti
    filtered = [
        r for r in raw if not is_two_state_alternating(r["pattern"])
    ]

    # Frequenze
    freq = Counter([tuple(r["pattern"]) for r in filtered])

    # costruiamo il dataframe
    rows = []
    for pattern_tuple, count in freq.items():
        first = next(r for r in filtered if tuple(r["pattern"]) == pattern_tuple)
        rows.append({
            "pattern": pattern_tuple,
            "freq_paziente": count,
            "start_time": first["start_time"],
            "end_time": first["end_time"],
            "total_duration": first["total_duration"]
        })

    df = pd.DataFrame(rows).sort_values("freq_paziente", ascending=False)

    return df.head(top_k)