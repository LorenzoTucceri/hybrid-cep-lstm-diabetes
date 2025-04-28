from datetime import datetime as dt

class IntervalActionDetector:
    def __init__(self, glucose_data, extreme_high_threshold=250, high_threshold=180, low_threshold=80, extreme_low_threshold=55):
        """
        Inizializza l'analizzatore con i dati sui livelli di glucosio e le soglie.

        Argomenti:
        glucose_data : DataFrame
            Il DataFrame contenente i dati sui livelli di glucosio.
        extreme_high_threshold : int, default=250
            La soglia per il livello di glucosio estremamente alto.
        high_threshold : int, default=180
            La soglia per il livello di glucosio alto.
        low_threshold : int, default=80
            La soglia per il livello di glucosio basso.
        extreme_low_threshold : int, default=55
            La soglia per il livello di glucosio estremamente basso.
        """
        self.glucose_data = glucose_data
        self.extreme_high_threshold = extreme_high_threshold
        self.high_threshold = high_threshold
        self.low_threshold = low_threshold
        self.extreme_low_threshold = extreme_low_threshold

    def offline_interval_action_detection(self):
        """
        Crea intervalli per ciascun livello di glucosio nel dataset fornito.

        Ritorna:
        intervals : list
            Una lista di tuple che rappresentano gli intervalli di tempo associati a ciascun livello di glucosio.
        events : list
            Una lista di eventi rilevati con i relativi simboli e valori di glucosio.
        """
        intervals, events = [], []
        for index, row in self.glucose_data.iloc[:10000].iterrows():
            timestamp = dt.fromisoformat(row['Data e ora (AAAA-MM-GGThh:mm:ss)'])
            glucose_value = row['Valore del glucosio (mg/dL)']

            if glucose_value == "Basso":
                event, symbol = 'extreme_low', 'd'
            else:
                glucose_level = int(glucose_value)
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

            if not intervals or intervals[-1][3] != event:
                intervals.append((symbol, timestamp, timestamp, event))
            else:
                intervals[-1] = (intervals[-1][0], intervals[-1][1], timestamp, event)

            events.append((symbol, event, glucose_level if glucose_value != "Basso" else "extreme_low"))

        return intervals, events

