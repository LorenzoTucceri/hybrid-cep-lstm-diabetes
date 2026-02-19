from datetime import datetime, timezone

import pandas as pd


def calculate_gmi(gluc_data):
    count = 0
    tot = 0
    for level in gluc_data:
        if (level == "Basso"):
            tot += 50
        else:
            tot += int(level)
        count += 1
    avg = tot / count
    gmi = 3.31 + 0.02392 * avg
    return gmi, avg


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


def datetime_to_unix_timestamp(dt):
    if dt.tzinfo is None:
        dt = dt.replace(tzinfo=timezone.utc)
    return int(dt.timestamp())


def unix_timestamp_to_datetime(ts):
    return datetime.fromtimestamp(ts, tz=timezone.utc)


def csv_to_sequence(csv_path):
    df = pd.read_csv(csv_path)
    df["duration_minutes"] = pd.to_timedelta(df["Duration"]).dt.total_seconds() / 60
    return df[["Event", "duration_minutes"]].values.tolist()


def discretize_duration_by_state(state, duration, THRESHOLDS_DURATION=None):
    if not THRESHOLDS_DURATION or state not in THRESHOLDS_DURATION:
        return ""
    thr = THRESHOLDS_DURATION[state]
    if isinstance(thr, (list, tuple)):
        thr = thr[0]
    return "L" if duration > thr else "N"
