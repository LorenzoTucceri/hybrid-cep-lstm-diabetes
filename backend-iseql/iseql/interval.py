class Interval:
    def __init__(self, start_time, end_time, symbol=None, event=None, duration=None):
        self.symbol = symbol
        self.start_time = start_time
        self.end_time = end_time
        self.event = event
        self.duration = duration if duration else self.end_time - self.start_time

