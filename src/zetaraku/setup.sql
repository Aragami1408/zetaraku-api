-- Create Database
CREATE DATABASE IF NOT EXISTS zetaraku;
USE zetaraku;

DROP TABLE IF EXISTS `songs`, `sheets`;

-- Create Tables
CREATE TABLE songs (
    songId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    category VARCHAR(255),
    artist VARCHAR(255),
    bpm INT,
    imageName VARCHAR(255),
    version VARCHAR(50),
    releaseDate DATE,
    isNew BOOLEAN,
    comment TEXT
);

CREATE TABLE sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    songId INT UNSIGNED,
    difficulty VARCHAR(50),
    level VARCHAR(10),
    levelValue DECIMAL(3,1),
    noteDesigner VARCHAR(100),
    tap INT,
    hold INT,
    slide INT,
    touch INT,
    breakCount INT,
    total INT,
    FOREIGN KEY (songId) REFERENCES songs(songId)
);

-- Insert sample data

INSERT INTO songs(`title`, `category`, `artist`, `bpm`, `imageName`, `version`, `releaseDate`, `isNew`, `comment`) VALUES (
    '君の知らない物語',
    'POPS＆アニメ',
    'supercell「化物語」',
    181,
    'b9d06643b0f9db1f154aad251470631bb688d1b5fb36566738d2c1f3cd488566.png',
    'GreeN',
    '2013-07-11',
    FALSE,
    NULL
);
INSERT INTO sheets (songId, difficulty, level, levelValue, noteDesigner, tap, hold, slide, touch, breakCount, total) VALUES
((SELECT `songId` FROM `songs` WHERE `title` = '君の知らない物語'), 'basic', '2', 2, '-', 47, 18, 2, NULL, 3, 70),
((SELECT `songId` FROM `songs` WHERE `title` = '君の知らない物語'), 'advanced', '7', 7, '-', 151, 13, 11, NULL, 9, 184),
((SELECT `songId` FROM `songs` WHERE `title` = '君の知らない物語'), 'expert', '9', 9, 'はっぴー', 197, 29, 21, NULL, 4, 251),
((SELECT `songId` FROM `songs` WHERE `title` = '君の知らない物語'), 'master', '11+', 11.6, '某S氏', 187, 17, 61, NULL, 10, 275);
