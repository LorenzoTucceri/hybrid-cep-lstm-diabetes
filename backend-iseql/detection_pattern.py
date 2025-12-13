from collections import Counter
import numpy as np
import pandas as pd
import utils

# -------------------------------------------------------
# MAPPE DI CONVERSIONE STATI
# -------------------------------------------------------

STATE_NAME = {
    "a": "EXTREMELY_HIGH",
    "b": "HIGH",
    "c": "NORMAL",
    "d": "LOW",
    "e": "EXTREMELY_LOW"
}

# short → long
STATE_MAP_SHORT_TO_LONG = {
    "extremely_high": "EXTREMELY_HIGH",
    "high": "HIGH",
    "normal": "NORMAL",
    "low": "LOW",
    "extremely_low": "EXTREMELY_LOW",
}

# long → short
STATE_MAP_LONG_TO_SHORT = {v: k for k, v in STATE_MAP_SHORT_TO_LONG.items()}


# -------------------------------------------------------
# NORMALIZZAZIONE PATTERN
# -------------------------------------------------------

def normalize_pattern_tuple(pattern):
    """
    Converte qualsiasi pattern in forma CANONICA:
    ('extremely_high', 'high', 'normal', ...)
    """
    normalized = []

    for elem in pattern:

        # Togliamo eventuali "(...)" per uniformare
        if "(" in elem:
            elem = elem.split("(")[0]

        elem = elem.strip()

        # LONG → SHORT
        if elem in STATE_MAP_LONG_TO_SHORT:
            normalized.append(STATE_MAP_LONG_TO_SHORT[elem])

        else:
            # fallback lowercase
            normalized.append(elem.lower())

    return tuple(normalized)


# -------------------------------------------------------
# FILTRO PER PATTERN ALTERNANTI
# -------------------------------------------------------

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


# -------------------------------------------------------
# NORMALIZZAZIONE SEQUENZA
# -------------------------------------------------------

def normalize_sequence(sequence):
    seq_full = []

    for item in sequence:

        if isinstance(item, np.ndarray):
            item = item.tolist()

        if len(item) == 2:
            s, d = item
            seq_full.append((s, d, 0.0))

        elif len(item) == 3 and not isinstance(item[1], pd.Timestamp):
            s, d, g = item
            seq_full.append((s, d, g))

        elif len(item) == 4:
            s, start, end, g = item
            duration = (end - start).total_seconds()
            seq_full.append((s, duration, g))

        else:
            raise ValueError(f"Formato non supportato: {item}")

    return seq_full


# -------------------------------------------------------
# ESTRATTORE DI NGRAM
# -------------------------------------------------------

def extract_ngrams_with_time(sequence, n_list=[4, 5], use_duration=False, THRESHOLDS_DURATION=None):
    seq_norm = normalize_sequence(sequence)

    raw_states = [item[0] for item in sequence]
    raw_start = [item[1] for item in sequence]
    raw_end   = [item[2] for item in sequence]

    durations = [
        utils.discretize_duration_by_state(s, d, THRESHOLDS_DURATION)
        if (use_duration and THRESHOLDS_DURATION) else ""
        for s, d, g in seq_norm
    ]

    ngrams = []

    for n in n_list:
        for i in range(len(sequence) - n + 1):

            pattern = tuple(
                STATE_NAME[st] if not du else f"{STATE_NAME[st]}({du})"
                for st, du in zip(raw_states[i:i+n], durations[i:i+n])
            )

            ngrams.append({
                "pattern": pattern,
                "start_time": raw_start[i],
                "end_time": raw_end[i + n - 1],
                "total_duration": (raw_end[i + n - 1] - raw_start[i]).total_seconds()
            })

    return ngrams


# -------------------------------------------------------
# PATTERN DEL PAZIENTE + ARRICCHIMENTO
# -------------------------------------------------------

def extract_patient_patterns(sequence, n_list=[4, 5], top_k=10,
                             use_duration=False, THRESHOLDS_DURATION=None):

    raw = extract_ngrams_with_time(
        sequence,
        n_list=n_list,
        use_duration=use_duration,
        THRESHOLDS_DURATION=THRESHOLDS_DURATION
    )

    # Filtro n-gram alternanti
    filtered = [r for r in raw if not is_two_state_alternating(r["pattern"])]

    # Frequenze
    freq = Counter(tuple(r["pattern"]) for r in filtered)

    rows = []
    for pattern_tuple, count in freq.items():

        first = next(r for r in filtered if tuple(r["pattern"]) == pattern_tuple)

        rows.append({
            "pattern": pattern_tuple,
            "pattern_norm": normalize_pattern_tuple(pattern_tuple),
            "freq_paziente": count,
            "start_time": first["start_time"],
            "end_time": first["end_time"],
            "total_duration": first["total_duration"]
        })

    df_patient = pd.DataFrame(rows).sort_values("freq_paziente", ascending=False)

    return df_patient.head(top_k)


# -------------------------------------------------------
# ARRICCHIMENTO: MATCH CON PATTERN GLOBALI
# -------------------------------------------------------


def enrich_patient_patterns(df_patient, global_patterns_csv="global_pattern/global_final_pattern.csv"):
    """
    Arricchisce i pattern del paziente con le informazioni globali (target, max_lift, ecc.)
    senza perdere le colonne originali. Converte i valori del target in inglese (Red, Yellow, Green).
    """

    df_patient = df_patient.copy()
    df_global = pd.read_csv(global_patterns_csv)

    # Normalizziamo i pattern per confrontarli correttamente
    def normalize_pattern(pat):
        if isinstance(pat, str):
            try:
                pat_tuple = eval(pat)
            except:
                return str(pat).upper()
        else:
            pat_tuple = pat
        return str(tuple(s.split("(")[0].upper() for s in pat_tuple))

    # Creiamo colonne temporanee con nomi diversi
    df_patient["_pattern_str_patient"] = df_patient["pattern"].apply(lambda x: str(tuple(s.upper() for s in x)))
    df_global["_pattern_str_global"] = df_global["pattern"].apply(normalize_pattern)

    # Selezioniamo le colonne globali da unire
    global_cols = [c for c in df_global.columns if c not in ["pattern", "_pattern_str_global"]]

    # Merge usando le colonne temporanee
    df_merged = df_patient.merge(
        df_global[["_pattern_str_global"] + global_cols],
        left_on="_pattern_str_patient",
        right_on="_pattern_str_global",
        how="left"
    )

    # Valori di default se mancanti
    if "dominant_target" in df_merged.columns:
        df_merged["dominant_target"] = df_merged["dominant_target"].fillna("N/A")

        # Mappatura in inglese
        color_map = {
            "rosso": "Red",
            "giallo": "Yellow",
            "verde": "Green",
            "N/A": "N/A"
        }
        df_merged["dominant_target"] = df_merged["dominant_target"].str.lower().map(color_map).fillna("N/A")

    if "max_lift" in df_merged.columns:
        df_merged["max_lift"] = df_merged["max_lift"].fillna(0)

    df_merged.drop(columns=["_pattern_str_patient", "_pattern_str_global"], inplace=True)

    return df_merged