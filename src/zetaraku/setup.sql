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
