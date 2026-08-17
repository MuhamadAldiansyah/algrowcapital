import yfinance as yf
import sys
import json
from datetime import datetime

def get_live_quote(ticker):
    try:
        # Ticker format CODE.JK
        clean_ticker = ticker.upper()
        if not clean_ticker.endswith(".JK") and clean_ticker != "^JKSE":
            full_ticker = f"{clean_ticker}.JK"
        else:
            full_ticker = clean_ticker
            
        t = yf.Ticker(full_ticker)
        # Fetch 1-day period with 1-minute interval
        # This gives us the absolute latest candle in the trading session
        df = t.history(period="1d", interval="1m")
        
        if df.empty:
            # Fallback if market isn't open yet or no intraday data
            df = t.history(period="5d", interval="1d")
            
        if df.empty:
            return {"error": "No data found for " + full_ticker}

        current_price = float(df.iloc[-1]['Close'])
        
        # Try to get previous close for change calculation
        # info can be slow, so we prefer getting it from the dataframe if possible
        # or use the first row of today's history if we had more than 1 day
        prev_close = current_price
        try:
            prev_df = t.history(period="2d", interval="1d")
            if len(prev_df) > 1:
                prev_close = float(prev_df.iloc[-2]['Close'])
            else:
                # If only 1 day available, use info as fallback
                prev_close = t.info.get('previousClose', current_price)
        except:
            pass
        
        change = current_price - prev_close
        change_pct = (change / prev_close) * 100 if prev_close != 0 else 0
        
        return {
            "symbol": full_ticker,
            "current_price": round(current_price, 2),
            "change": round(change, 2),
            "change_pct": round(change_pct, 2),
            "status": "success",
            "last_update": datetime.now().strftime("%H:%M:%S")
        }
    except Exception as e:
        return {"error": str(e), "status": "error"}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No ticker provided"}))
        sys.exit(1)
        
    ticker_arg = sys.argv[1]
    result = get_live_quote(ticker_arg)
    print(json.dumps(result))
