#!/usr/bin/env python3
"""Ambil foto armada dari Wikimedia Commons lalu simpan ke public/assets/images/cars/.
Sumber tunggal data: db/cars.json (field "image" akan diisi otomatis).
Jalankan: python db/fetch_car_images.py [--force]
"""
import json
import os
import re
import sys
import time
import urllib.parse
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, "db", "cars.json")
OUT = os.path.join(ROOT, "public", "assets", "images", "cars")
API = "https://commons.wikimedia.org/w/api.php"
UA = "SewaMobilSBY-DataSeeder/1.0 (project kuliah; kontak: admin@sewamobilsby.id)"

BAD_WORDS = ("interior", "engine", "dashboard", "seat", "logo", "badge", "wheel",
             "rear", "back", "side", "detail", "motor", "concept", "cutaway")


def norm(s):
    return re.sub(r"[^a-z0-9]", "", s.lower())


def api(params):
    params = dict(params)
    params["format"] = "json"
    url = API + "?" + urllib.parse.urlencode(params)
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=30) as r:
        return json.loads(r.read().decode("utf-8"))


def candidates(query):
    data = api({
        "action": "query", "generator": "search",
        "gsrsearch": f"filetype:bitmap {query}", "gsrnamespace": 6,
        "gsrlimit": 12, "prop": "imageinfo",
        "iiprop": "url|size|mime", "iiurlwidth": "1000",
    })
    pages = (data.get("query") or {}).get("pages") or {}
    return sorted(pages.values(), key=lambda p: p.get("index", 99))


def pick(car, pages):
    model = norm(car["model"])
    make = norm(car["make"])
    best, best_score = None, -99
    for p in pages:
        info = (p.get("imageinfo") or [{}])[0]
        title = p.get("title", "")
        t = norm(title)
        score = 0
        if model and model in t:
            score += 6
        elif model[:5] and model[:5] in t:
            score += 2
        else:
            continue
        if make in t:
            score += 2
        if info.get("mime") not in ("image/jpeg", "image/png", "image/webp"):
            continue
        w, h = info.get("width", 0), info.get("height", 0)
        if w < 700:
            score -= 3
        ratio = (w / h) if h else 0
        if 1.15 <= ratio <= 2.1:
            score += 3
        else:
            score -= 2
        low = title.lower()
        score += sum(1 for b in BAD_WORDS if b in low) * -4
        if "front" in low:
            score += 2
        if score > best_score:
            best, best_score = (info, title), score
    return best


def download(url, path):
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=60) as r:
        blob = r.read()
    with open(path, "wb") as f:
        f.write(blob)
    return len(blob)


def main():
    force = "--force" in sys.argv
    with open(DATA, encoding="utf-8") as f:
        doc = json.load(f)
    os.makedirs(OUT, exist_ok=True)
    credits = []
    for i, car in enumerate(doc["cars"], 1):
        slug = car["slug"]
        existing = car.get("image")
        if existing and not force and os.path.isfile(os.path.join(OUT, os.path.basename(existing))):
            credits.append((slug, existing, car.get("source", "")))
            print(f"[{i:02d}/50] skip {slug}")
            continue
        try:
            pages = candidates(car["query"])
            chosen = pick(car, pages)
            if not chosen:
                print(f"[{i:02d}/50] GAGAL cari {slug} ({car['query']})")
                continue
            info, title = chosen
            url = info.get("thumburl") or info.get("url")
            ext = ".jpg"
            if info.get("mime") == "image/png":
                ext = ".png"
            elif info.get("mime") == "image/webp":
                ext = ".webp"
            fname = slug + ext
            size = download(url, os.path.join(OUT, fname))
            car["image"] = "cars/" + fname
            car["source"] = "https://commons.wikimedia.org/wiki/" + urllib.parse.quote(title.replace(" ", "_"))
            credits.append((slug, car["image"], car["source"]))
            print(f"[{i:02d}/50] ok {fname} ({size//1024} KB) <- {title}")
        except Exception as e:  # noqa: BLE001
            print(f"[{i:02d}/50] ERROR {slug}: {e}")
        time.sleep(0.4)

    with open(DATA, "w", encoding="utf-8") as f:
        json.dump(doc, f, ensure_ascii=False, indent=2)
        f.write("\n")

    with open(os.path.join(OUT, "CREDITS.md"), "w", encoding="utf-8") as f:
        f.write("# Sumber foto armada\n\nSemua foto diambil dari Wikimedia Commons "
                "(lisensi bebas, atribusi lengkap di tautan).\n\n")
        for slug, img, src in credits:
            f.write(f"- `{img}` — {src}\n")


if __name__ == "__main__":
    main()