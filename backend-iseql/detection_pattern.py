import pandas as pd
from datetime import datetime


def inizialize():
    glucose_data = pd.read_csv("intervalli_glucosio.csv", delimiter=';')
    print(glucose_data.head())
