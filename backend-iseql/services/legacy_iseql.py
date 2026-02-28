import os
import subprocess
import sys
import tempfile
import re
import ast
import pandas as pd
from utils import datetime_to_unix_timestamp, unix_timestamp_to_datetime
import machine_learning.pattern_detection as pattern_detection
from services.data_processing import parse_part

# 1. Trova la cartella esatta in cui si trova questo script Python
current_dir = os.path.dirname(os.path.abspath(__file__))

# 2. Definisci il nome dell'eseguibile e il percorso corretto per Windows o Mac
exe_name = "iseql.exe" if sys.platform == "win32" else "iseql"

if sys.platform == "win32":
    # Su Windows (Laragon), CMake + MSVC usa la sottocartella Release
    # Usiamo abspath per generare un percorso pulito senza i "../../"
    exe_path = os.path.abspath(os.path.join(current_dir, "..", "..", "cpp-iseql", "build", "src", "Release", exe_name))
else:
    # Su Mac, si trova solitamente direttamente in src
    exe_path = os.path.abspath(os.path.join(current_dir, "..", "..", "cpp-iseql", "build", "src", exe_name))

# Verifica subito se l'eseguibile esiste per prevenire crash
if not os.path.exists(exe_path):
    print(f"⚠️ ATTENZIONE: Eseguibile non trovato in: {exe_path}")

def run_legacy_analysis(intervals, intervals_legacy):
    # A. Estrazione Pattern
    top_k_pattern = pattern_detection.extract_patient_patterns(intervals_legacy)

    tmp_file_path = ""
    with tempfile.NamedTemporaryFile(mode='w', delete=False, newline='') as f:
        f.write("pattern\n")
        for p in top_k_pattern['pattern']:
            p_str = ",".join(str(x) for x in p) if isinstance(p, (tuple, list)) else str(p)
            f.write(f"{p_str}\n")
        tmp_file_path = f.name

    # --- FIX FONDAMENTALE ---
    # Salviamo eventi.txt NELLA STESSA CARTELLA DELLO SCRIPT.
    # Così il C++, che parte lavorando da current_dir (cwd=current_dir), lo troverà subito.
    eventi_path = os.path.join(current_dir, "eventi.txt")
    with open(eventi_path, "w") as file:
        file.write("start_time,end_time,label\n")
        for item in intervals_legacy:
            file.write(f"{datetime_to_unix_timestamp(item[1])},{datetime_to_unix_timestamp(item[2])},{item[3]}\n")

    swings, ext_swings, parsed_top_k_patterns, patient_stats = [], [], [], {}

    # B. Esecuzione C++
    # Nota: Abbiamo rimosso i "" vuoti alla fine delle chiamate per non confondere il C++
    try:
        res = subprocess.run([exe_path, "time-swing"], check=True, capture_output=True,
                             text=True, cwd=current_dir)
        for l in res.stdout.split('\n'):
            if " -- " in l:
                pts = l.split(" -- ")
                if len(pts) == 2:
                    i1, i2 = parse_part(pts[0]), parse_part(pts[1])
                    if i1 and i2: swings.append((i1, i2))
    except Exception as e:
        print(f"⚠️ Errore C++ (time-swing): {e}")

    try:
        res = subprocess.run([exe_path, "extremely-time-swing"], check=True,
                             capture_output=True, text=True, cwd=current_dir)
        for l in res.stdout.split('\n'):
            if " -- " in l:
                pts = l.split(" -- ")
                if len(pts) == 2:
                    i1, i2 = parse_part(pts[0]), parse_part(pts[1])
                    if i1 and i2: ext_swings.append((i1, i2))
    except Exception as e:
        print(f"⚠️ Errore C++ (extremely-time-swing): {e}")

    try:
        res = subprocess.run([exe_path, "detection-pattern", tmp_file_path], check=True,
                             capture_output=True, text=True, cwd=current_dir)
        pat_re = re.compile(r"\{\s*pattern:\s*'(.+?)',\s*frequency:\s*(\d+),\s*occurrences:\s*\[(.*)\]\s*\}")
        raw_pats = []
        for line in res.stdout.split('\n'):
            m = pat_re.match(line.strip())
            if m:
                pat_str, freq, occ_str = m.groups()
                pat = tuple(p.strip() for p in pat_str.split(','))
                occs = []
                if occ_str:
                    try:
                        raw = ast.literal_eval("[" + occ_str + "]")
                        for match in raw:
                            fmt = []
                            for s, e in match:
                                s_dt, e_dt = unix_timestamp_to_datetime(s), unix_timestamp_to_datetime(e)
                                dur = int((e_dt - s_dt).total_seconds())
                                fmt.append({"start": s_dt.strftime("%Y-%m-%d %H:%M"),
                                            "end": e_dt.strftime("%Y-%m-%d %H:%M"),
                                            "duration": f"{dur // 3600:02d}:{(dur % 3600) // 60:02d}:{dur % 60:02d}"})
                            occs.append(fmt)
                    except:
                        pass
                raw_pats.append({'pattern': pat, 'frequency': int(freq), 'occurrences': occs})

        if raw_pats: # Previene errori se raw_pats è vuoto
            df_en = pattern_detection.enrich_patient_patterns(pd.DataFrame(raw_pats))
            avg_d, cnt = 0, 0
            for _, r in df_en.iterrows():
                durs = []
                if isinstance(r['occurrences'], list):
                    for ol in r['occurrences']:
                        for o in ol:
                            try:
                                h, m, s = map(int, o['duration'].split(':'))
                                durs.append(h * 60 + m + s / 60)
                            except:
                                pass
                ad = round(sum(durs) / len(durs), 2) if durs else 0
                parsed_top_k_patterns.append(
                    {'pattern': r['pattern'], 'frequency': r.get('frequency', 0), 'occurrences': r['occurrences'],
                     'target': r.get('dominant_target', 'N/A'), 'max_lift': r.get('max_lift', 0), 'total_duration': ad})
                avg_d += ad
                cnt += 1

            # Stats
            if parsed_top_k_patterns:
                ts = [p['target'].lower() for p in parsed_top_k_patterns if p.get('target')]
                mf = max(parsed_top_k_patterns, key=lambda x: x['frequency'])
                patient_stats = {
                    'total_patterns': len(parsed_top_k_patterns),
                    'most_frequent_pattern': f"{' - '.join(mf['pattern'])} ({mf['frequency']})",
                    'target_distribution': {'red': ts.count('red'), 'yellow': ts.count('yellow'),
                                            'green': ts.count('green')},
                    'avg_duration': round(avg_d / cnt, 2)
                }

    except subprocess.CalledProcessError as e:
        print(f"❌ Errore critico nel pattern detection (C++ fallito con codice {e.returncode})")
        print(f"Dettaglio errore C++: {e.stderr}")
    except Exception as e:
        print(f"Errore pattern python: {e}")

    if os.path.exists(tmp_file_path): os.remove(tmp_file_path)

    return swings, ext_swings, parsed_top_k_patterns, patient_stats