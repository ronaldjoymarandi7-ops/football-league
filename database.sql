-- Football League Management System
-- Database Setup Script
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS football_league;
USE football_league;

-- Teams Table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    coach VARCHAR(100) NOT NULL,
    stadium VARCHAR(150) NOT NULL,
    founded_year INT NOT NULL,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    draws INT DEFAULT 0,
    goals_scored INT DEFAULT 0,
    goals_conceded INT DEFAULT 0,
    logo_color VARCHAR(7) DEFAULT '#e74c3c',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Players Table
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    position ENUM('Goalkeeper','Defender','Midfielder','Forward') NOT NULL,
    jersey_number INT NOT NULL,
    nationality VARCHAR(80) NOT NULL,
    age INT NOT NULL,
    goals INT DEFAULT 0,
    assists INT DEFAULT 0,
    matches_played INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- Matches Table
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    match_date DATE NOT NULL,
    match_time TIME NOT NULL,
    venue VARCHAR(150) NOT NULL,
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    status ENUM('Scheduled','Live','Completed','Postponed') DEFAULT 'Scheduled',
    round VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (home_team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- Sample Data: Teams
INSERT INTO teams (name, city, coach, stadium, founded_year, wins, losses, draws, goals_scored, goals_conceded, logo_color) VALUES
('Thunder FC', 'Mumbai', 'Ravi Sharma', 'Thunder Arena', 2010, 12, 3, 5, 45, 22, '#e74c3c'),
('City Wolves', 'Delhi', 'Anil Mehta', 'Wolf Den Stadium', 2008, 10, 5, 5, 38, 28, '#3498db'),
('Golden Eagles', 'Bangalore', 'Suresh Nair', 'Eagle Ground', 2015, 9, 6, 5, 31, 25, '#f39c12'),
('Royal Kings', 'Chennai', 'Pradeep Rao', 'Kings Palace Stadium', 2012, 8, 7, 5, 29, 31, '#9b59b6'),
('Green Arrows', 'Kolkata', 'Deepak Singh', 'Arrow Arena', 2011, 7, 8, 5, 27, 35, '#27ae60'),
('Blue Sharks', 'Pune', 'Kiran Patil', 'Shark Bay Ground', 2016, 5, 10, 5, 22, 40, '#2980b9');

-- Sample Data: Players
INSERT INTO players (team_id, name, position, jersey_number, nationality, age, goals, assists, matches_played) VALUES
(1, 'Arjun Verma', 'Forward', 9, 'Indian', 24, 18, 6, 20),
(1, 'Rahul Das', 'Midfielder', 8, 'Indian', 26, 7, 12, 20),
(1, 'Sunil Kumar', 'Goalkeeper', 1, 'Indian', 30, 0, 0, 20),
(1, 'Amit Patel', 'Defender', 5, 'Indian', 27, 2, 3, 19),
(2, 'Vikram Singh', 'Forward', 10, 'Indian', 23, 14, 5, 20),
(2, 'Rohan Gupta', 'Midfielder', 6, 'Indian', 25, 5, 9, 18),
(2, 'Deepak Joshi', 'Goalkeeper', 1, 'Indian', 28, 0, 0, 20),
(3, 'Karthik Raj', 'Forward', 11, 'Indian', 22, 11, 4, 20),
(3, 'Arun Pillai', 'Midfielder', 7, 'Indian', 24, 4, 8, 20),
(4, 'Manoj Reddy', 'Forward', 9, 'Indian', 25, 9, 3, 20),
(4, 'Sanjay Iyer', 'Defender', 4, 'Indian', 29, 1, 2, 20),
(5, 'Pranav Das', 'Forward', 10, 'Indian', 21, 8, 4, 20),
(6, 'Nikhil More', 'Midfielder', 8, 'Indian', 23, 3, 7, 20);

-- Sample Data: Matches
INSERT INTO matches (home_team_id, away_team_id, match_date, match_time, venue, home_score, away_score, status, round) VALUES
(1, 2, '2024-11-15', '18:00:00', 'Thunder Arena', 3, 1, 'Completed', 'Round 1'),
(3, 4, '2024-11-16', '17:00:00', 'Eagle Ground', 2, 2, 'Completed', 'Round 1'),
(5, 6, '2024-11-17', '16:00:00', 'Arrow Arena', 1, 0, 'Completed', 'Round 1'),
(2, 3, '2024-11-22', '18:00:00', 'Wolf Den Stadium', 2, 3, 'Completed', 'Round 2'),
(1, 4, '2024-11-23', '17:30:00', 'Thunder Arena', 4, 1, 'Completed', 'Round 2'),
(6, 5, '2024-11-24', '16:00:00', 'Shark Bay Ground', 0, 2, 'Completed', 'Round 2'),
(1, 3, CURDATE() + INTERVAL 5 DAY, '18:00:00', 'Thunder Arena', NULL, NULL, 'Scheduled', 'Round 3'),
(2, 4, CURDATE() + INTERVAL 5 DAY, '17:00:00', 'Wolf Den Stadium', NULL, NULL, 'Scheduled', 'Round 3'),
(5, 1, CURDATE() + INTERVAL 10 DAY, '19:00:00', 'Arrow Arena', NULL, NULL, 'Scheduled', 'Round 4'),
(6, 3, CURDATE() + INTERVAL 10 DAY, '17:30:00', 'Shark Bay Ground', NULL, NULL, 'Scheduled', 'Round 4');
