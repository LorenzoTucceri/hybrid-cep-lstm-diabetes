import pandas as pd
import os
import csv
from utils import unix_timestamp_to_datetime
from iseql.interval import Interval


def create_interval_labeling_csv(intervals, filename="./intervalli_glucosio.csv"):
    if os.path.exists(filename): os.remove(filename)
    with open(filename, mode='w', newline='') as csvfile:
        writer = csv.writer(csvfile)
        writer.writerow(["symbol", "start_time", "end_time", "label"])
        for i in intervals:
            writer.writerow([i[0], i[1].isoformat(), i[2].isoformat(), i[3]])

    if os.path.exists("./eventi.txt"): os.remove("./eventi.txt")


def parse_part(part):
    try:
        t, eid, etype = part.strip().split()
        s, e = map(int, t[1:-1].split(','))
        s_dt, e_dt = unix_timestamp_to_datetime(s), unix_timestamp_to_datetime(e)
        return Interval(s_dt, e_dt, int(eid), etype, e_dt - s_dt)
    except:
        return None


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