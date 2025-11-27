from datetime import datetime as dt, timedelta
from interval import Interval


class ISEQL:
    def __init__(self):
        self.intervals = []

    def add_interval(self, interval):
        self.intervals.append(interval)

    def get_intervals(self):
        return self.intervals

    def find_event_interval(self, event_name):
        for interval in self.intervals:
            if interval.event == event_name:
                return interval
        return None

    def check_anomalies_event(self, interval):
        return interval.event != 'normal'

    def create_daily_intervals(self):
        daily_intervals = {}
        for interval in self.intervals:
            start_date = interval.start_time.date()
            if start_date not in daily_intervals:
                daily_intervals[start_date] = []
            daily_intervals[start_date].append(interval)

        # Imposta la frequenza a 1 giorno
        frequency_threshold = timedelta(days=1)

        result = {}
        for start_date, intervals in daily_intervals.items():
            end_date = start_date + frequency_threshold
            start_time = dt.combine(start_date, dt.min.time())
            end_time = dt.combine(end_date, dt.min.time())
            result[start_date] = {
                'intervals': intervals,
                'start_time': start_time,
                'end_time': end_time
            }

        return result


    def during(self, interval1, interval2):
        start_time1 = interval1.start_time
        end_time1 = interval1.end_time
        start_time2 = interval2.start_time
        end_time2 = interval2.end_time

        return start_time2 <= start_time1 and end_time1 <= end_time2


    def before(self, interval1, interval2, delta):
        """
        Check if interval1 occurs completely before interval2 with a specified delta.

        Args:
            interval1 (Interval): The first interval.
            interval2 (Interval): The second interval.
            delta (timedelta): The time difference threshold.

        Returns:
            bool: True if interval1 occurs completely before interval2 considering the delta, False otherwise.
        """
        # Check if interval1 ends before interval2 starts and the difference in time is less than or equal to delta
        return interval1.end_time < interval2.start_time <= interval1.end_time + delta


    def cardinality_constraints(self, intervals, event, min_count):
        """
        Check if the number of intervals with a specific level within a given time frame meets the minimum count.

        Args:
            level (str): The level to count (e.g., "high", "low", "extremely high", "extremely low").
            min_count (int): The minimum number of occurrences required.
            time_frame_start (datetime): The start of the time frame to consider.
            time_frame_end (datetime): The end of the time frame to consider.

        Returns:
            bool: True if the count of intervals meeting the criteria is at least min_count, False otherwise.
        """
        count = 0
        for interval in intervals:
            if (interval.event == event):
                count += 1

        return count >= min_count

    def overlap_percentage_delta(self, interval, extremely_high_delta=timedelta(minutes=45),
                                 high_delta=timedelta(hours=1, minutes=30), low_delta=timedelta(minutes=30),
                                 extremely_low_delta=timedelta(minutes=10)):
        # Determine the delta threshold based on the event
        if interval.event == "extremely_high":
            delta = extremely_high_delta
        elif interval.event == "high":
            delta = high_delta
        elif interval.event == "low":
            delta = low_delta
        elif interval.event == "extremely_low":
            delta = extremely_low_delta
        else:
            return False  # If the event is not recognized, return False

        # Check if the interval's duration meets or exceeds the delta threshold
        return interval.duration >= delta

    def find_time_swing(self, time_threshold=timedelta(hours=2)):
        time_swings = []
        for i in range(len(self.intervals) - 2):
            interval1 = self.intervals[i]
            interval2 = self.intervals[i + 2]

            if self.before(interval1, interval2,
                           time_threshold) and interval1.event != interval2.event and self.check_anomalies_event(
                    interval1) and self.check_anomalies_event(interval2):
                time_swings.append((interval1, interval2))
        return time_swings

    #DA TESTARE
    def find_extremely_time_swing(self, time_threshold=timedelta(minutes=30)):
        extremely_time_swings = []
        for i in range(len(self.intervals) - 2):
            interval1 = self.intervals[i]
            interval2 = self.intervals[i + 2]

            if self.before(interval1, interval2,
                           time_threshold) and interval1.event == interval2.event and self.check_anomalies_event(
                    interval1) and self.check_anomalies_event(interval2) and (interval1.event=="extremely_high" or interval1.event=="extremely_low"):
                extremely_time_swings.append((interval1, interval2))
        return extremely_time_swings


    def find_too_frequent_glucose_anomalies(self, min_high=3, min_low=3, min_extremely_high=1, min_extremely_low=1):
        anomalous_frequency = []

        daily_intervals = self.create_daily_intervals()

        for start_date, interval_info in daily_intervals.items():
            start_time = interval_info['start_time']
            end_time = interval_info['end_time']

            # Create a reference interval for the current time frame
            reference_interval = Interval(start_time, end_time)

            # Initialize event lists
            extremely_high_event = []
            high_event = []
            low_event = []
            extremely_low_event = []

            # Process each interval within the daily interval
            for current_interval in interval_info['intervals']:
                # Check if the current interval occurs during the reference interval
                if self.during(current_interval, reference_interval):
                    if self.check_anomalies_event(current_interval):
                        if current_interval.event == 'extremely_high':
                            extremely_high_event.append(current_interval)
                        elif current_interval.event == 'high':
                            high_event.append(current_interval)
                        elif current_interval.event == 'low':
                            low_event.append(current_interval)
                        elif current_interval.event == 'extremely_low':
                            extremely_low_event.append(current_interval)

            # Apply cardinality constraints
            high_anomalous_count = len(high_event)
            low_anomalous_count = len(low_event)
            extremely_high_anomalous_count = len(extremely_high_event)
            extremely_low_anomalous_count = len(extremely_low_event)

            total_count = high_anomalous_count + low_anomalous_count + extremely_high_anomalous_count + extremely_low_anomalous_count

            if (self.cardinality_constraints(high_event, "high", min_high) or
                    self.cardinality_constraints(low_event, "low", min_low) or
                    self.cardinality_constraints(extremely_high_event, "extremely_high", min_extremely_high) or
                    self.cardinality_constraints(extremely_low_event, "extremely_low", min_extremely_low)):
                anomalous_frequency.append((
                    start_date,
                    end_time,
                    high_anomalous_count,
                    low_anomalous_count,
                    extremely_high_anomalous_count,
                    extremely_low_anomalous_count,
                    total_count
                ))

        return anomalous_frequency

    def find_too_frequent_time_swings(self,time_swing_threshold=timedelta(hours=2), min_ts=2):
        time_swings_too_frequent = []
        daily_intervals = self.create_daily_intervals()  # Ensure this returns a dict with intervals for each day

        for start_date, interval_info in daily_intervals.items():
            start_time = interval_info['start_time']
            end_time = interval_info['end_time']
            reference_interval = Interval(start_time, end_time)

            # Initialize list to store detected time swings
            time_swings = []

            intervals = interval_info['intervals']

            for i in range(len(intervals) - 2):
                interval1 = intervals[i]
                interval2 = intervals[i + 2]

                if self.during(interval1, reference_interval) and self.during(interval2, reference_interval):
                    if self.before(interval1, interval2,
                                   time_swing_threshold) and interval1.event != interval2.event and self.check_anomalies_event(
                            interval1) and self.check_anomalies_event(interval2):
                        time_swings.append((interval1, interval2))

                        # cardinality constrains
            if len(time_swings) >= min_ts:
                time_swings_too_frequent.append(time_swings)

        return time_swings_too_frequent


    def find_too_long_glucose_anomalies(self, extremely_high_delta=timedelta(minutes=45),
                                high_delta=timedelta(hours=1, minutes=30), low_delta=timedelta(minutes=30),
                                extremely_low_delta=timedelta(minutes=10)):
        anomalous_duration = []

        # Create a dictionary for event types and their corresponding thresholds
        thresholds = {
            "extremely_high": extremely_high_delta,
            "high": high_delta,
            "low": low_delta,
            "extremely_low": extremely_low_delta
        }

        for interval in self.intervals:
            event_type = interval.event

            # Determine the threshold for the current event type
            if event_type in thresholds:
                delta = thresholds[event_type]
            else:
                continue  # Skip unrecognized events

            # Check if the interval's duration meets or exceeds the threshold
            if interval.duration >= delta:
                anomalous_duration.append(interval)

        return anomalous_duration

    def find_time_swing_with_too_long_glucose_anomalies(self, time_swings=None):
        time_swings_duration = []
        anomalous_duration = self.find_too_long_glucose_anomalies()
        if time_swings is None:
            time_swings = self.find_time_swing()

        for ad in anomalous_duration:
            for ts in time_swings:

                if (ad.start_time == ts[0].start_time and ad.end_time == ts[0].end_time) or \
                        (ad.start_time == ts[1].start_time and ad.end_time == ts[1].end_time):
                    event = f"{ad.event.capitalize()} event"
                    time_swings_duration.append((ts[0], ts[1], f"{event}: {ad.duration}"))


        return time_swings_duration



    #DA TESTARE
    def find_too_frequent_extremely_time_swings(self,extremely_time_swing_threshold=timedelta(minutes=30), min_ts=2):
        extremely_time_swings_too_frequent = []
        daily_intervals = self.create_daily_intervals()  # Ensure this returns a dict with intervals for each day

        for start_date, interval_info in daily_intervals.items():
            start_time = interval_info['start_time']
            end_time = interval_info['end_time']
            reference_interval = Interval(start_time, end_time)

            # Initialize list to store detected time swings
            extremely_time_swings = []

            intervals = interval_info['intervals']

            for i in range(len(intervals) - 2):
                interval1 = intervals[i]
                interval2 = intervals[i + 2]

                if self.during(interval1, reference_interval) and self.during(interval2, reference_interval):
                    if self.before(interval1, interval2,
                                   extremely_time_swing_threshold) and interval1.event == interval2.event and self.check_anomalies_event(
                            interval1) and self.check_anomalies_event(interval2) and (interval1.event=="extremely_high" or interval1.event=="extremely_low"):
                        extremely_time_swings.append((interval1, interval2))

                        # cardinality constrains
            if len(extremely_time_swings) >= min_ts:
                extremely_time_swings_too_frequent.append(extremely_time_swings)

        return extremely_time_swings_too_frequent

    #DA TESTARE
    def find_extremely_time_swing_with_too_long_glucose_anomalies(self, time_swings=None):
        extremely_time_swings_duration = []
        anomalous_duration = self.find_too_long_glucose_anomalies()
        if time_swings is None:
            time_swings = self.find_extremely_time_swing()

        for ad in anomalous_duration:
            for ts in time_swings:

                if (ad.start_time == ts[0].start_time and ad.end_time == ts[0].end_time) or \
                        (ad.start_time == ts[1].start_time and ad.end_time == ts[1].end_time):
                    event = f"{ad.event.capitalize()} event"
                    extremely_time_swings_duration.append((ts[0], ts[1], f"{event}: {ad.duration}"))


        return extremely_time_swings_duration





