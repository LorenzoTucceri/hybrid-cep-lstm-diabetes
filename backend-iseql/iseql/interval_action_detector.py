from datetime import datetime as dt
import pandas as pd
import numpy as np


class IntervalActionDetector:
    def __init__(self, glucose_data, extreme_high_threshold=250, high_threshold=180, low_threshold=80,
                 extreme_low_threshold=55):
        self.glucose_data = glucose_data
        self.extreme_high_threshold = extreme_high_threshold
        self.high_threshold = high_threshold
        self.low_threshold = low_threshold
        self.extreme_low_threshold = extreme_low_threshold

    def offline_interval_action_detection(self):
        """
        Returns:
        intervals : list of tuples
            (symbol, start_time, end_time, label, duration_minutes, avg_glucose)
        events : list
        """
        intervals = []
        events = []

        # Temp storage for current interval calculation
        current_values = []

        for index, row in self.glucose_data.iterrows():
            # Robust parsing
            try:
                ts_raw = row['Data e ora (AAAA-MM-GGThh:mm:ss)']
                if isinstance(ts_raw, str):
                    timestamp = dt.fromisoformat(ts_raw)
                else:
                    timestamp = ts_raw  # Already datetime

                val_raw = row['Valore del glucosio (mg/dL)']
            except:
                continue  # Skip bad rows

            # Determine Event & Symbol
            if str(val_raw).strip() == "Basso":
                event, symbol = 'extremely_low', 'e'  # Fixed symbol mapping
                glucose_level = 40  # Numeric fallback for avg calculation
            elif str(val_raw).strip() == "Alto":
                event, symbol = 'extremely_high', 'a'
                glucose_level = 400
            else:
                try:
                    glucose_level = int(float(val_raw))
                except:
                    continue

                if glucose_level >= self.extreme_high_threshold:
                    event, symbol = 'extremely_high', 'a'
                elif glucose_level >= self.high_threshold:
                    event, symbol = 'high', 'b'
                elif glucose_level <= self.extreme_low_threshold:
                    event, symbol = 'extremely_low', 'e'
                elif glucose_level <= self.low_threshold:
                    event, symbol = 'low', 'd'
                else:
                    event, symbol = 'normal', 'c'

            # Logic to build intervals
            if not intervals or intervals[-1][3] != event:
                # Close previous interval stats if exists
                if intervals:
                    # Update duration and avg for the finished interval
                    prev = intervals[-1]
                    dur = (prev[2] - prev[1]).total_seconds() / 60.0
                    avg = sum(current_values) / len(current_values) if current_values else 0
                    intervals[-1] = (prev[0], prev[1], prev[2], prev[3], max(1, int(dur)), round(avg, 1))

                # Start new interval
                intervals.append((symbol, timestamp, timestamp, event, 0, 0))  # Placeholder
                current_values = [glucose_level]
            else:
                # Extend current interval
                prev = intervals[-1]
                intervals[-1] = (prev[0], prev[1], timestamp, event, 0, 0)  # Update End Time
                current_values.append(glucose_level)

            events.append((symbol, event, glucose_level))

        # Close the very last interval
        if intervals:
            prev = intervals[-1]
            dur = (prev[2] - prev[1]).total_seconds() / 60.0
            avg = sum(current_values) / len(current_values) if current_values else 0
            intervals[-1] = (prev[0], prev[1], prev[2], prev[3], max(1, int(dur)), round(avg, 1))

        return intervals, events