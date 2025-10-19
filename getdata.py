import requests
import json
from datetime import datetime

# Fetch JSON data
url = "https://dp4p6x0xfi5o9.cloudfront.net/maimai/data.json"
response = requests.get(url)
data = response.json()

song_sqls = []
sheet_sqls = []

for song in data['songs']:
    title = song.get('title')
    category = song.get('category')
    artist = song.get('artist')
    bpm = song.get('bpm')
    imageName = song.get('imageName')
    version = song.get('version')
    releaseDate = song.get('releaseDate')
    isNew = 'TRUE' if song.get('isNew') else 'FALSE'
    comment = "NULL" if song.get('comment') is None else f"'{song.get('comment')}'"
    
    song_insert = f"""INSERT INTO songs(title, category, artist, bpm, imageName, version, releaseDate, isNew, comment) VALUES (
    '{title}', '{category}', '{artist}', {bpm}, '{imageName}', '{version}', '{releaseDate}', {isNew}, {comment});"""
    song_sqls.append(song_insert)
    
    for sheet in song['sheets']:
        difficulty = sheet.get('difficulty')
        level = sheet.get('level')
        levelValue = sheet.get('levelValue')
        noteDesigner = sheet.get('noteDesigner', '-')
        tap = sheet.get('tap')
        hold = sheet.get('hold')
        slide = sheet.get('slide')
        touch = "NULL" if sheet.get('touch') is None else sheet.get('touch')
        breakCount = sheet.get('breakCount')
        breakCount = "NULL" if breakCount is None else breakCount
        total = sheet.get('total')
        
        sheet_insert = f"""INSERT INTO sheets (songId, difficulty, level, levelValue, noteDesigner, tap, hold, slide, touch, breakCount, total) VALUES
((SELECT songId FROM songs WHERE title = '{title}'), '{difficulty}', '{level}', {levelValue}, '{noteDesigner}', {tap}, {hold}, {slide}, {touch}, {breakCount}, {total});"""
        sheet_sqls.append(sheet_insert)

# Save to SQL file
with open(f"maimai_inserts_{datetime.now().date()}.sql", "w", encoding="utf-8") as f:
    for stmt in song_sqls + sheet_sqls:
        f.write(stmt + "\n")
