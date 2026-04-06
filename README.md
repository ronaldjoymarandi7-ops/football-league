# ⚽ FootballHub — Football League Management System
### BCA Project | PHP + MySQL + XAMPP

---

## 📁 Project Structure

```
football_league/
├── index.php              ← Dashboard (Home Page)
├── database.sql           ← Run this first in phpMyAdmin
├── includes/
│   ├── config.php         ← DB connection settings
│   ├── header.php         ← Navbar + session start
│   └── footer.php         ← Footer + JS link
├── pages/
│   ├── teams.php          ← Teams CRUD
│   ├── players.php        ← Players CRUD
│   ├── matches.php        ← Match scheduling + results
│   └── standings.php      ← Points table + stats
├── css/
│   └── style.css          ← Full dark stadium theme
└── js/
    └── main.js            ← Search, modals, animations
```

---

## 🚀 Setup Instructions

### Step 1 — Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### Step 2 — Copy Project
- Copy the `football_league/` folder to:
  ```
  C:\xampp\htdocs\football_league\
  ```

### Step 3 — Create Database
- Open browser → go to `http://localhost/phpmyadmin`
- Click **Import** tab
- Choose `database.sql` from the project folder
- Click **Go** — database + sample data will be created

### Step 4 — Open the App
- Go to: `http://localhost/football_league/`

---

## ✨ Features

| Feature | Description |
|---|---|
| 🏟️ Team Management | Add, Edit, Delete teams with coach/stadium info |
| 👟 Player Management | Full roster management with positions & stats |
| 📅 Match Scheduling | Schedule fixtures between any two teams |
| 📊 Score Updates | Enter results — standings auto-update! |
| 🏆 Live Standings | Points table with GD, W/D/L breakdown |
| 🥇 Top Scorers | Auto-ranked scorers and assists charts |
| 🔍 Live Search | Instant search on all pages |
| 🎨 Responsive UI | Works on desktop and mobile |

---

## 🔧 Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 8.x
- **Database:** MySQL (via phpMyAdmin)
- **Server:** Apache (XAMPP)
- **Fonts:** Google Fonts (Bebas Neue, Barlow)
- **Design:** Dark sports editorial theme

---

## 📐 Database Tables

| Table | Key Fields |
|---|---|
| `teams` | name, city, coach, stadium, wins, draws, losses |
| `players` | team_id, name, position, goals, assists |
| `matches` | home_team_id, away_team_id, date, score, status |

---

## 💡 BCA Concepts Demonstrated

- Database Design (3 related tables with FK constraints)
- CRUD Operations (Create, Read, Update, Delete)
- PHP Form Handling & Validation
- SQL Joins (multi-table queries)
- Session Management (flash messages)
- Responsive Web Design
- MVC-like separation (includes, pages)

---

*Built for BCA Students — Football League Management System*
