import requests
import json
from datetime import datetime

# Fetch JSON data
url = "https://dp4p6x0xfi5o9.cloudfront.net/maimai/data.json"
response = requests.get(url)
data = response.json()

song_sqls = []
sheet_sqls = []

def esc(val):
    """Return a safe, fully-quoted SQL literal for MySQL (or NULL)."""
    if val is None:
        return "NULL"
    s = str(val)
    # Escape backslash first, then single quote
    s = s.replace("\\", "\\\\").replace("'", "\\'")
    # (Optional) If you really want to escape double quotes too:
    s = s.replace('"', '\\"')
    return f"{s}"

for song in data['songs']:
    title = song.get('title')
    category = song.get('category')
    artist = song.get('artist')
    bpm = 0 if song.get('bpm') == None else song.get('bpm')
    imageName = song.get('imageName')
    version = song.get('version')
    releaseDate = song.get('releaseDate')
    isNew = 1 if song.get('isNew') else 0
    comment = "NULL" if song.get('comment') is None else f"'{song.get('comment')}'"

    if category != "宴会場":
        song_insert = f"""INSERT INTO songs(title, category, artist, bpm, imageName, version, releaseDate, isNew, comment) VALUES (
    \"{esc(title)}\", \"{esc(category)}\", \"{esc(artist)}\", {bpm}, \"{imageName}\", \"{version}\", \"{releaseDate}\", {isNew}, \"{comment}\");"""
        song_sqls.append(song_insert)

    for sheet in song['sheets']:
        difficulty = sheet.get('difficulty')
        level = sheet.get('level')
        levelValue = sheet.get('levelValue')
        noteDesigner = sheet.get('noteDesigner', '-')
        tap = "NULL" if sheet.get('noteCounts').get('tap') is None else sheet.get('noteCounts').get('tap')
        hold = "NULL" if sheet.get('noteCounts').get('hold') is None else sheet.get('noteCounts').get('hold')
        slide = "NULL" if sheet.get('noteCounts').get('slide') is None else sheet.get('noteCounts').get('slide')
        touch = "NULL" if sheet.get('noteCounts').get('touch') is None else sheet.get('noteCounts').get('touch')
        breakCount = sheet.get('noteCounts').get('break')
        breakCount = "NULL" if breakCount is None else breakCount
        total = "NULL" if sheet.get('noteCounts').get('total') is None else sheet.get('noteCounts').get('total')


        if level != '*':
            sheet_insert = f"""INSERT INTO sheets (songId, difficulty, level, levelValue, noteDesigner, tap, hold, slide, touch, breakCount, total) VALUES
((SELECT songId FROM songs WHERE title = \"{title}\"), \"{difficulty}\", \"{level}\", {levelValue}, \"{esc(noteDesigner)}\", {tap}, {hold}, {slide}, {touch}, {breakCount}, {total});"""
            sheet_sqls.append(sheet_insert)

# Save to SQL file
with open(f"maimai_inserts_songs_{datetime.now().date()}.sql", "w", encoding="utf-8") as f:
    for stmt in song_sqls:
        f.write(stmt + "\n")

with open(f"maimai_inserts_sheets_{datetime.now().date()}.sql", "w", encoding="utf-8") as f:
    for stmt in sheet_sqls:
        f.write(stmt + "\n")
