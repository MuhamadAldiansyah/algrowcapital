import yfinance as yf
import pandas as pd
import pandas_ta as ta
import json
import sys
import os
import contextlib
import pytz
import numpy as np
from datetime import datetime, timedelta
import concurrent.futures

# --- TURBO SCALPER 5.2: EXPANDED RADAR & VELOCITY PULSE ---

# Categorization Map (Priority Groups)
GROUPS_MAP = {
    "Market Leaders": ["BBCA.JK", "BBRI.JK", "BMRI.JK", "BBNI.JK", "TLKM.JK", "ASII.JK", "GOTO.JK", "UNVR.JK", "ICBP.JK"],
    "Barito Group (Prajogo)": ["BREN.JK", "CUAN.JK", "PTRO.JK", "BRPT.JK", "TPIA.JK"],
    "Bakrie Group": ["BUMI.JK", "DEWA.JK", "BRMS.JK", "ENRG.JK", "VKTR.JK", "VIVA.JK", "MDIA.JK"],
    "Happy Hapsoro": ["RAJA.JK", "BUVA.JK", "MINA.JK", "SINI.JK", "FORU.JK"],
    "Digital Banks/Tech": ["ARTO.JK", "BBYB.JK", "BNBA.JK", "BVIC.JK", "BABY.JK", "BGTG.JK", "BUKA.JK", "WIFI.JK"],
    "High Volatility": ["PSAB.JK", "PYFA.JK", "LABA.JK", "KPIG.JK", "MSIN.JK", "NANO.JK", "TOSK.JK", "SRAJ.JK", "PANI.JK", "AWAN.JK", "CHIP.JK", "DATA.JK", "STRK.JK", "SOLU.JK", "NAIK.JK"],
}

# Scalper Pool: Massive 100+ Ticker Radar
SCALPER_POOL = [
    # Top Tier & Big Caps
    "BBCA.JK", "BBRI.JK", "BMRI.JK", "BBNI.JK", "TLKM.JK", "ASII.JK", "GOTO.JK", "UNVR.JK", "ICBP.JK", "INDF.JK", "KLBF.JK", "CPIN.JK", "AMRT.JK", "MAPI.JK", "ACES.JK",
    # Barito & Allied
    "BREN.JK", "CUAN.JK", "PTRO.JK", "BRPT.JK", "TPIA.JK", "GZCO.JK", "ESSA.JK", 
    # Bakrie & Commodity High Vol
    "BUMI.JK", "DEWA.JK", "BRMS.JK", "ENRG.JK", "VKTR.JK", "VIVA.JK", "MDIA.JK", "ADMR.JK", "MBMA.JK", "NCKL.JK", "HRUM.JK", "ADRO.JK", "ITMG.JK", "PTBA.JK", "ANTM.JK", "INCO.JK", "MDKA.JK", "MEDC.JK", "AKRA.JK", "PGAS.JK", "ELSA.JK",
    # Tech & Digital Banking
    "ARTO.JK", "BBYB.JK", "BNBA.JK", "BVIC.JK", "BABY.JK", "BGTG.JK", "BUKA.JK", "WIFI.JK", "CHIP.JK", "DATA.JK", "NAIK.JK", "AWAN.JK", "SOLU.JK", "STRK.JK",
    # Volatile Mid/Small Caps
    "PSAB.JK", "PYFA.JK", "LABA.JK", "KPIG.JK", "MSIN.JK", "NANO.JK", "TOSK.JK", "SRAJ.JK", "PANI.JK", "HEAL.JK", "MIKA.JK", "SIDO.JK", "MPMX.JK", "ASSA.JK", "WINS.JK", "SMDR.JK", "TMAS.JK", "PSSI.JK", "GZCO.JK", "RAJA.JK", "SINI.JK", "FORU.JK", "ARCI.JK", "HAIS.JK", "BULL.JK",
    # Property & Infrastructure
    "BSDE.JK", "SMRA.JK", "CTRA.JK", "ASRI.JK", "PWON.JK", "JSMR.JK", "META.JK", "WIKA.JK", "PTPP.JK", "ADHI.JK", "WEGE.JK", "WATT.JK", "BEST.JK",
    # Others/Trending
    "LPKR.JK", "DILD.JK", "KIJA.JK", "BKSL.JK", "SCMA.JK", "MNCN.JK"
]

def get_ara_limit(price):
    """
    Calculate ARA (Auto Rejection Atas) limit based on IDX tiers.
    """
    if price < 50: return 0.35 # Pre-opening/speculative
    if 50 <= price <= 200: return 0.35
    if 200 < price <= 5000: return 0.25
    return 0.20

def get_wib_time():
    return datetime.now(pytz.timezone('Asia/Jakarta'))

def get_expected_volume_ratio():
    """
    Calculate the expected volume ratio based on WIB market hours.
    IDX Hours: 09:00 - 12:00, 13:30 - 16:00
    """
    now = get_wib_time()
    s1_start = now.replace(hour=9, minute=0, second=0, microsecond=0)
    s1_end = now.replace(hour=12, minute=0, second=0, microsecond=0)
    s2_start = now.replace(hour=13, minute=30, second=0, microsecond=0)
    s2_end = now.replace(hour=16, minute=15, second=0, microsecond=0)

    total_market_mins = 180 + 150
    elapsed_mins = 0

    if now < s1_start: return 0.01
    elif s1_start <= now <= s1_end:
        elapsed_mins = (now - s1_start).total_seconds() / 60
    elif s1_end < now < s2_start:
        elapsed_mins = 180
    elif s2_start <= now <= s2_end:
        elapsed_mins = 180 + (now - s2_start).total_seconds() / 60
    else:
        return 1.0
    
    if elapsed_mins <= 30: return (elapsed_mins / 30) * 0.15
    return 0.15 + (elapsed_mins - 30) / (total_market_mins - 30) * 0.85

def safe_val(val):
    if pd.isna(val) or val is None or (isinstance(val, float) and (np.isinf(val) or np.isnan(val))):
        return None
    return float(val)

def analyze_live_momentum(ticker, intraday_df, hist_df, exp_ratio):
    """
    Turbo 5.1: Priority Volatile & Mid-Day Calibrated Engine
    """
    try:
        if intraday_df.empty or hist_df.empty or len(intraday_df) < 2: return None
        
        # 0. Identify Group First
        group_name = "Other"
        for name, tkrs in GROUPS_MAP.items():
            if ticker in tkrs: group_name = name; break
            
        is_bluechip = group_name == "Market Leaders"
        is_volatile = group_name in ["High Volatility", "Barito Group (Prajogo)", "Bakrie Group"]
        
        current_price = float(intraday_df['Close'].iloc[-1])
        is_institutional_range = 100 <= current_price <= 1000

        # 1. Benchmarks from History (Ensure we get YESTERDAY'S close)
        # yf.download(period="10d") includes today. iloc[-1] is today, iloc[-2] is yesterday.
        if len(hist_df) >= 2:
            prev_close = float(hist_df['Close'].iloc[-2])
        else:
            prev_close = float(hist_df['Close'].iloc[-1])
            
        avg_daily_vol = hist_df['Volume'].mean() or 1
        
        # 2. Intraday Metrics
        open_price = float(intraday_df['Open'].iloc[0])
        day_high = float(intraday_df['High'].max())
        day_low = float(intraday_df['Low'].min())
        morning_vol = float(intraday_df['Volume'].sum())
        
        # 3. Gap Analysis
        gap_pct = (open_price - prev_close) / prev_close
        daily_change = (current_price - prev_close) / prev_close
        
        # 4. ORB (Opening Range Breakout)
        orb_high = intraday_df['High'].iloc[:15].max() if len(intraday_df) >= 15 else open_price
        is_orb_breakout = current_price > orb_high
        
        # 4.5 VELOCITY PULSE (Last 15 Minutes)
        velocity_15m = 0
        if len(intraday_df) >= 15:
            price_15m_ago = float(intraday_df['Close'].iloc[-15])
            velocity_15m = (current_price - price_15m_ago) / price_15m_ago
        
        is_heating_up = velocity_15m >= 0.01 # Moving up >1% in 15 mins
        
        # 5. DYNAMIC RVOL (Relative Volume)
        expected_now_vol = avg_daily_vol * exp_ratio
        current_rvol = morning_vol / expected_now_vol if expected_now_vol > 0 else 0
        
        # Bandar Pulse (Intraday Money Flow)
        mfi_live = ta.mfi(intraday_df['High'], intraday_df['Low'], intraday_df['Close'], intraday_df['Volume'], length=min(len(intraday_df)-1, 14))
        live_mfi = safe_val(mfi_live.iloc[-1]) if mfi_live is not None and not mfi_live.empty else 50
        
        # 6. Scoring Logic (VOLATILE FOCUS)
        score = 0; reasons = []; strategy = "Scalping"
        
        # Price Action Bonus (The "Feel") - AGGRESSIVE TIERS
        if daily_change >= 0.15:
            score += 100; reasons.append(f"SUPER GAINER {daily_change*100:.1f}% ✨")
        elif daily_change >= 0.10:
            score += 70; reasons.append(f"High Momentum {daily_change*100:.1f}% 🔥")
        elif daily_change >= 0.05:
            score += 45; reasons.append(f"Strong Uptrend {daily_change*100:.1f}% ⬆️")
        elif daily_change >= 0.02:
            score += 25; reasons.append(f"Bullish Move {daily_change*100:.1f}%")
        elif -0.005 <= daily_change <= 0.005:
            score -= 60; reasons.append("Stagnant (No Volume/Volat)")
        elif daily_change <= -0.05:
            score -= 100; reasons.append("DUMPING/GOTO HELL 💀")
            
        # VELOCITY BOOST (The "Freshness" factor)
        if is_heating_up:
            v_bonus = int(velocity_15m * 1000) # e.g. +1% in 15m = +10 pts
            score += v_bonus
            reasons.append(f"HEATING UP! (+{velocity_15m*100:.1f}% in 15m) ⚡")
            if velocity_15m >= 0.025:
                score += 30; reasons.append("FRESH BREAKOUT DETECTED! 🚀")
                strategy = "Velocity Breakout"
            
        # Gap Logic
        if gap_pct >= 0.02:
            score += 15; reasons.append(f"Gap Up {gap_pct*100:.1f}% ⬆️")
        elif gap_pct <= -0.03:
            score -= 50; reasons.append("Gap Down (Bleeding)")
            
        # ORB
        if is_orb_breakout:
            score += 30; reasons.append("ORB Breakout 🚀")
        
        # Dynamic Volume Logic
        vol_threshold = 1.8 if is_bluechip else 2.5
        if current_rvol > vol_threshold:
            score += 40
            if live_mfi > 55:
                score += 30
                reasons.append(f"HIGH HAKA ACTIVITY ({current_rvol:.1f}x)")
                strategy = "Aggressive Entry" if is_volatile else "Accumulation"
            else:
                reasons.append(f"Volume Spike ({current_rvol:.1f}x)")
        elif current_rvol < 0.3 and exp_ratio > 0.2:
            score -= 20; reasons.append("Low Liquidity Today")
            
        # Volatile Boost
        if is_volatile and daily_change > 0.03:
            score += 20; reasons.append("VOLATILE SURGE! ⚡")

        # 7. Precision Entry Calculation (The "Titik Entry Terbaik")
        entry_1 = current_price
        entry_2 = round(current_price * 0.985, 0)
        entry_3 = round(prev_close * 1.01, 0) if current_price > prev_close * 1.05 else round(current_price * 0.965, 0)
        
        # Confidence Logic
        conf_score = min(max(int(score), 0), 100)
        conf_label = "HIGH" if conf_score >= 75 else ("MEDIUM" if conf_score >= 40 else "LOW")

        # 8. ARA Potential Detection
        ara_limit_pct = get_ara_limit(prev_close)
        is_ara_potential = False
        dist_to_ara = (ara_limit_pct - daily_change) * 100
        
        if daily_change >= 0.08 and current_rvol > 2.0:
            if dist_to_ara <= 5.0 and dist_to_ara > 0.2:
                is_ara_potential = True
                score += 60
                reasons.append(f"HEADING TO ARA! ({dist_to_ara:.1f}% to limit) 🏹")
                strategy = "ARA Hunter"

        # Determine Rating
        rating = "Neutral"
        if daily_change < -0.02: rating = "Avoid (Down)"
        elif is_heating_up and daily_change > 0: rating = "Buy (Breakout)"
        elif is_ara_potential: rating = "Strong Buy (ARA Potential)"
        elif score >= 120: rating = "Strong Buy (Extreme)"
        elif score >= 80: rating = "Strong Buy"
        elif score >= 50: rating = "Buy"
        elif score < 0 or abs(daily_change) < 0.01: rating = "Wait & See"
        
        # Trading Plan Update
        tp_plan = {
            "entry_aggressive": float(entry_1),
            "entry_support": float(entry_2),
            "entry_safety": float(entry_3),
            "sl": round(day_low * 0.99, 0),
            "tp": round(prev_close * (1 + ara_limit_pct), 0),
            "tp_scalp": round(prev_close * (1 + ara_limit_pct), 0),
            "confidence": conf_label,
            "conf_score": conf_score,
            "dist_to_ara": float(round(dist_to_ara, 2)) if dist_to_ara > 0 else 0,
            "is_ara_potential": is_ara_potential,
            "velocity_15m": float(round(velocity_15m * 100, 2)),
            "is_heating_up": is_heating_up,
            "entry_note": "BREAKOUT KENCANG! Masuk saat pullback kecil." if is_heating_up else ("VOLATILITAS ARA! Pantau bid tebal di bawah." if is_ara_potential else "Masuk bertahap, pantau arus kas.")
        }
        
        return {
            "group": group_name, "ticker": ticker.replace(".JK", ""), 
            "price": float(current_price), "change": float(round(daily_change * 100, 2)),
            "gap": float(round(gap_pct * 100, 2)), "rvol": float(round(current_rvol, 2)),
            "score": int(score), "reasoning": " | ".join(reasons), 
            "rating": rating, "strategy": strategy, "plan": tp_plan,
            "is_orb": bool(is_orb_breakout)
        }
    except Exception as e: return None

if __name__ == "__main__":
    tickers = list(dict.fromkeys(SCALPER_POOL))
    all_results = []
    exp_ratio = get_expected_volume_ratio()
    
    with open(os.devnull, 'w') as f, contextlib.redirect_stdout(f), contextlib.redirect_stderr(f):
        ihsg = yf.Ticker("^JKSE")
        ihsg_hist = ihsg.history(period="10d")
        ihsg_live = ihsg.history(period="1d", interval="1m")
        
        ihsg_data = {"price": 0, "change": 0, "status": "Stable"}
        if len(ihsg_hist) >= 2:
            prev_close = ihsg_hist['Close'].iloc[-2]
            curr_price = ihsg_live['Close'].iloc[-1] if not ihsg_live.empty else ihsg_hist['Close'].iloc[-1]
            change_pct = ((curr_price - prev_close) / prev_close) * 100
            ihsg_data = {
                "price": round(curr_price, 2), "change": round(change_pct, 2),
                "status": "Bullish" if change_pct > 0.3 else ("Bearish" if change_pct < -0.3 else "Stable")
            }

        hist_data = yf.download(tickers, period="10d", interval="1d", group_by='ticker', progress=False)
        live_data = yf.download(tickers, period="1d", interval="1m", group_by='ticker', progress=False)
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
            futures = []
            for ticker in tickers:
                if ticker in live_data.columns.get_level_values(0) and ticker in hist_data.columns.get_level_values(0):
                    futures.append(executor.submit(
                        analyze_live_momentum, 
                        ticker, 
                        live_data[ticker].dropna(), 
                        hist_data[ticker].dropna(),
                        exp_ratio
                    ))
            
            for future in concurrent.futures.as_completed(futures):
                try:
                    res = future.result()
                    if res: all_results.append(res)
                except: continue

    output = {
        "stocks": sorted(all_results, key=lambda x: x['score'], reverse=True),
        "ihsg": ihsg_data,
        "is_scalping_mode": True,
        "scan_time": get_wib_time().strftime("%Y-%m-%d %H:%M:%S"),
        "total_scanned": len(all_results),
        "calibration_ratio": round(exp_ratio * 100, 1)
    }
    
    print(json.dumps(output))
