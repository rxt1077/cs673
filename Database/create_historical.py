import fix_yahoo_finance as yf
import requests
from datetime import timedelta

# The Dow30 and Nifty50
symbols = ['MMM', 'AXP', 'AAPL', 'BA', 'CAT', 'CVX', 'CSCO', 'KO', 'DWDP',
    'XOM', 'GS', 'HD', 'IBM', 'INTC', 'JNJ', 'JPM', 'MCD', 'MRK', 'MSFT', 'NKE',
    'PFE', 'PG', 'TRV', 'UNH', 'UTX', 'VZ', 'V', 'WMT', 'WBA', 'DIS',
    'ADANIPORTS.NS', 'ASIANPAINT.NS', 'AXISBANK.NS', 'BAJAJ-AUTO.NS',
    'BAJFINANCE.NS', 'BAJAJFINSV.NS', 'BHARTIARTL.NS', 'INFRATEL.NS', 'BPCL.NS',
    'CIPLA.NS', 'COALINDIA.NS', 'DRREDDY.NS', 'EICHERMOT.NS', 'GAIL.NS',
    'GRASIM.NS', 'HCLTECH.NS', 'HDFC.NS', 'HDFCBANK.NS', 'HEROMOTOCO.NS',
    'HINDALCO.NS', 'HINDUNILVR.NS', 'HINDPETRO.NS', 'ICICIBANK.NS',
    'IBULHSGFIN.NS', 'INDUSINDBK.NS', 'INFY.NS', 'IOC.NS', 'ITC.NS',
    'JSWSTEEL.NS', 'KOTAKBANK.NS', 'LT.NS', 'M&M.NS', 'MARUTI.NS', 'NTPC.NS',
    'ONGC.NS', 'POWERGRID.NS', 'RELIANCE.NS', 'SBIN.NS', 'SUNPHARMA.NS',
    'TCS.NS', 'TATAMOTORS.NS', 'TATASTEEL.NS', 'TECHM.NS', 'TITAN.NS',
    'ULTRACEMCO.NS', 'UPL.NS', 'VEDL.NS', 'WIPRO.NS', 'YESBANK.NS', 'ZEEL.NS']

start = '2018-03-13'
exchange_start = '2018-03-06' # Currency exchange may not be open on start
end = '2019-03-13'

# Exchange rates for INR -> USD by day
url = f'https://api.exchangeratesapi.io/history?start_at={exchange_start}&end_at={end}&base=INR&symbols=USD'
resp = requests.get(url)
exchange_rates = resp.json()

with open('historical.sql', 'w+') as f:
    f.write('''USE rxt1077;

CREATE TABLE IF NOT EXISTS historical (
    date DATE,
    symbol VARCHAR(32),
    price DECIMAL(10,2)
);\n\n''')

    for symbol in symbols:
        df = yf.download(symbol, start, end)
        for date, row in df.iterrows():
            day = date.strftime('%Y-%m-%d')

            # find the closest previous day when the currency exchange was open
            exchange_day = day
            exchange_date = date.to_pydatetime();
            while exchange_day not in exchange_rates['rates']:
                exchange_date = exchange_date - timedelta(days=1)
                exchange_day = exchange_date.strftime('%Y-%m-%d');

            # if it is a Nifty50 stock, convert it
            if symbol.endswith('.NS'):
                close = row['Close'] * exchange_rates['rates'][exchange_day]['USD']
            else: # otherwise just set it to close
                close = row['Close']
            close = round(close, 2)

            f.write(f"INSERT INTO historical VALUES ('{day}', '{symbol}', {close});\n")
