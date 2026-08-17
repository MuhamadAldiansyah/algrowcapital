import requests
import xml.etree.ElementTree as ET
import json
import pytz
from datetime import datetime, timedelta
import sys

def get_wib_time():
    return datetime.now(pytz.timezone('Asia/Jakarta'))

def get_sentiment(title):
    bad_keywords = [
        'perang', 'war', 'crash', 'collapse', 'inflation', 'sanksi', 'fed hike', 
        'recession', 'turun', 'drop', 'fall', 'lemah', 'negative', 'conflict', 
        'iran', 'israel', 'oil surge', 'spike', 'surged', 'plunging', 'soaring',
        'hawkish', 'correction', 'uncertainty', 'threat', 'tensi', 'geopolitik'
    ]
    good_keywords = [
        'naik', 'up', 'rebound', 'bullish', 'dividend', 'untung', 'profit', 
        'growth', 'proyek', 'kerjasama', 'ipo', 'buyback', 'positive', 'ceasefire', 
        'optimistic', 'dovish', 'recovery', 'stimulus'
    ]
    
    title_low = title.lower()
    if any(k in title_low for k in bad_keywords): return "Bad"
    if any(k in title_low for k in good_keywords): return "Good"
    return "Neutral"

def fetch_market_news(query, hl='id-ID', gl='ID', ceid='ID:id'):
    headlines = []
    url = f"https://news.google.com/rss/search?q={query}&hl={hl}&gl={gl}&ceid={ceid}"
    
    try:
        response = requests.get(url, timeout=10)
        if response.status_code == 200:
            root = ET.fromstring(response.content)
            for item in root.findall('.//item')[:15]:
                title = item.find('title').text
                link = item.find('link').text
                pub_date = item.find('pubDate').text
                
                # Format date
                dt = datetime.strptime(pub_date, '%a, %d %b %Y %H:%M:%S %Z')
                dt_wib = dt.replace(tzinfo=pytz.UTC).astimezone(pytz.timezone('Asia/Jakarta'))
                
                headlines.append({
                    "title": title,
                    "link": link,
                    "sentiment": get_sentiment(title),
                    "impact": False,
                    "date_str": pub_date,
                    "timestamp": int(dt_wib.timestamp())
                })
    except Exception as e:
        pass
    return headlines

def generate_market_outlook(domestic, global_h):
    bad_count = 0
    good_count = 0
    
    all_headlines = domestic + global_h
    for h in all_headlines:
        if h['sentiment'] == "Bad": bad_count += 1
        elif h['sentiment'] == "Good": good_count += 1
        
    bias = "NEUTRAL"
    themes = []
    all_text = str(all_headlines).lower()
    if any(k in all_text for k in ["fed", "rate hike", "inflation", "inflasi"]): themes.append("Macro/Inflation")
    if any(k in all_text for k in ["war", "iran", "conflict", "perang"]): themes.append("Geopolitical Risk")
    if any(k in all_text for k in ["oil", "minyak", "energy"]): themes.append("Energy Shock")
    if any(k in all_text for k in ["dividen", "dividend"]): themes.append("Dividend Season")
    if any(k in all_text for k in ["msci", "ftse", "rebalancing", "rebalance"]): themes.append("Index Rebalancing")
    
    theme_str = f" (Tema Dominan: {', '.join(themes)})" if themes else ""

    if bad_count > (good_count * 1.5):
        bias = "CAUTION / BEARISH BIAS"
        reasoning = f"Tekanan jual meningkat{theme_str}. Sentimen global memburuk, market cenderung defensif."
    elif good_count > (bad_count * 1.2):
        bias = "OPTIMISTIC / BULLISH BIAS"
        reasoning = f"Sentimen positif mendominasi{theme_str}. Peluang penguatan IHSG terbuka didukung data domestik/korporasi."
    else:
        bias = "CONSOLIDATING / NEUTRAL"
        reasoning = f"Pasar bergerak sideways{theme_str}. Investor cenderung wait and see menanti katalis baru."
        
    return {
        "bias": bias,
        "reasoning": reasoning,
        "summary": f"Hasil scan: {bad_count} risiko vs {good_count} peluang. {len(all_headlines)} berita diproses."
    }

if __name__ == "__main__":
    # 1. Fetch Domestic News
    domestic_query = "MSCI Indonesia OR FTSE Indonesia OR IHSG OR Ekonomi Indonesia OR Kebijakan Ekonomi OR Aksi Korporasi when:7d"
    domestic = fetch_market_news(domestic_query)
    
    # 2. Fetch Global News
    global_query = "Fed rate OR geopolitics OR war OR oil prices OR recession when:7d"
    global_h = fetch_market_news(global_query, hl='en-US', gl='US', ceid='US:en')
    
    # 3. Analyze
    outlook = generate_market_outlook(domestic, global_h)
    
    # Calculate Risk Score (Simplified for news)
    bad_ratio = len([h for h in global_h if h['sentiment'] == "Bad"]) / max(len(global_h), 1)
    risk_score = int(bad_ratio * 100)
    
    output = {
        "score": risk_score,
        "status": "High" if risk_score > 60 else "Medium",
        "global_headlines": global_h,
        "domestic_headlines": domestic,
        "outlook": outlook,
        "scan_time": get_wib_time().strftime("%Y-%m-%d %H:%M:%S")
    }
    
    print(json.dumps(output))
