# ⏱️ Time Tracker / Lampboard System

A full-featured employee time tracking system with a live “Lampboard” terminal, admin panel, reporting tools, Excel export, email automation, and multilingual support.

---

## 📌 Overview

This application allows employees to register **COME / GO (check-in/check-out)** actions via a live dashboard (“Lampboard”) and provides administrators with tools to:

- Manage employees
- View and edit time entries
- Generate reports
- Export Excel files
- Receive automated monthly email reports
- Monitor activity via calendar and dashboards

**Built with:**

- PHP 8.3
- MariaDB 11
- Docker
- JavaScript
- FullCalendar
- PhpSpreadsheet
- PHPMailer

---

## 🚀 Features

### 🧑‍💼 Employee Features

- Live tile-based “Lampboard” terminal
- One-click COME / GO time tracking
- Real-time status updates (auto-refresh every 5s)
- Multilingual interface (EN, DE, FR, ES)
- Time-based status display (last action shown per employee)

---

### 🛠 Admin Panel Features

- Secure login system
- Change admin password
- Manage deployment access key (Lampboard protection)
- Employee management (add / edit / delete)
- View last 200 time entries
- Filter entries by employee
- Calendar view of all time entries
- Edit time entries directly in calendar
- Export tools (Excel + reports)
- Logout system

---

### 📊 Reporting Features

- Employee work hour calculation (COME → GO pairing)
- Detailed per-day time logs
- Total hours calculation
- Printable report page
- Monthly report generation

---

### 📁 Excel Export (PhpSpreadsheet)

- Export all time entries per employee
- Export current month only
- Auto-formatted spreadsheets
- Downloadable `.xlsx` files

---

### 📅 Calendar System (FullCalendar)

- Monthly / weekly / daily views
- Color-coded events:
  - 🟢 COME
  - 🔴 GO
- Click-to-edit entries
- Inline update modal (AJAX)

---

### 📧 Email Automation

- Monthly automatic report generation
- Excel attachment per employee
- SMTP configurable
- Sent via cron job

---

### ⏰ Cron Worker

- Monthly scheduled execution (1st of month at midnight)
- Automatically performs:
  1. Logs in as admin
  2. Generates monthly report
  3. Sends email
  4. Logs out

---

### 🌐 Lampboard Terminal (Live Dashboard)

- Public-facing employee terminal
- Deployment ID protection
- Live status tiles per employee
- Auto-refresh every 5 seconds
- Click employee → COME / GO modal
- Real-time clock display

---

## ⚙️ Setup Instructions

### 1. Clone Project

```bash
git clone https://github.com/amuehle/lampboard-tt.git
cd lampboard-tt
docker-compose up -d
```

### 3. Access Services
Service	URL
Lampboard	http://localhost/lampboard/index.php?deployment_id=YOUR_KEY
Admin Login	http://localhost/admin/login.php
### 4. Default Login
Username: admin
Password: admin123