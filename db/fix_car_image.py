#!/usr/bin/env python3
"""Cari ulang foto untuk slug tertentu: tampilkan kandidat teratas.
Pakai: python db/fix_car_image.py <slug> "<query>" [--apply <index>]
"""
import json
import os
import sys
import urllib.parse
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(ROOT, "db", "cars.json")
OUT = os.path.join(ROOT, "public", "assets", "images", "cars")
API = "https://commons.wikimedia.org/w/api.php"
UA = "SewaMobilSBY-DataSeeder/1.0 (project kuliah)"


def api(params):
    params = dict(params); params["format"] = "json"
    req = urllib.request.Request(API + "?" + urllib.parse.urlencode(params), headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=30) as r:
        return json.loads(r.read().decode("utf-8"))


def main():
    slug, query = sys.argv[1], sys.argv[2]
    apply_idx = None
    if "--apply" in sys.argv:
        apply_idx = int(sys.argv[sys.argv.index("--apply") + 1])
    data = api({"action": "query", "generator": "search", "gsrsearch": f"filetype:bitmap {query}",
                "gsrnamespace": 6, "gsrlimit": 15, "prop": "imageinfo",
                "iiprop": "url|size|mime", "iiurlwidth": "1000"})
    pages = (data.get("query") or {}).get("pages") or {}
    pages = sorted(pages.values(), key=lambda p: p.get("index", 99))
    for i, p in enumerate(pages):
        info = (p.get("imageinfo") or [{}])[0]
        print(f"{i:2d}. {p['title']}  [{info.get('width')}x{info.get('height')}]")
    if apply_idx is None:
        return
    p = pages[apply_idx]
    info = (p.get("imageinfo") or [{}])[0]
    url = info.get("thumburl") or info.get("url")
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    blob = urllib.request.urlopen(req, timeout=60).read()
    with open(DATA, encoding="utf-8") as f:
        doc = json.load(f)
    for car in doc["cars"]:
        if car["slug"] == slug:
            fname = (car.get("image") or f"cars/{slug}.jpg").split("/")[-1]
            with open(os.path.join(OUT, fname), "wb") as fh:
                fh.write(blob)
            car["source"] = "https://commons.wikimedia.org/wiki/" + urllib.parse.quote(p["title"].replace(" ", "_"))
            print("APPLIED", fname, "<-", p["title"], len(blob) // 1024, "KB")
    with open(DATA, "w", encoding="utf-8") as f:
        json.dump(doc, f, ensure_ascii=False, indent=2); f.write("\n")


if __name__ == "__main__":
    main()