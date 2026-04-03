# 🎓 InternMatch — Internship Placement System

A full-stack PHP web application for managing internship placements across four user roles: **Students**, **Companies**, **Coordinators**, and **Supervisors**.

---

## 📁 Project Structure

```
internship_system/
├── index.php                    # Login & Registration landing page
├── dashboard_student.php        # Student portal
├── dashboard_company.php        # Company portal
├── dashboard_coordinator.php    # Coordinator portal
├── dashboard_supervisor.php     # Supervisor portal
├── database.sql                 # MySQL schema + seed data
│
├── api/
│   ├── auth_actions.php         # Login, Register, Logout
│   ├── application_actions.php  # Apply for internships, approve/reject
│   ├── internship_actions.php   # Post, edit, delete, toggle internships
│   └── log_actions.php          # Submit progress logs, give feedback
│
├── includes/
│   ├── db.php                   # PDO database connection
│   └── auth.php                 # Session helpers & role enforcement
│
└── assets/
    ├── css/style.css            # Global styles (glassmorphism dark theme)
    └── js/main.js               # Search, modals, form validation
```

---

## 🚀 Getting Started

### Prerequisites
- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Apache / Nginx with `mod_rewrite` (or any PHP-capable server)

### Installation

**1. Clone or upload the project**
```bash
git clone https://github.com/your-repo/internship-system.git
```
Or upload via FTP to your hosting provider (e.g., InfinityFree, cPanel).

**2. Create the database**

Log into phpMyAdmin (or MySQL CLI) and run:
```sql
SOURCE database.sql;
```
This creates the `internship_system` database, all tables, and two seed accounts.

**3. Configure the database connection**

Edit `includes/db.php` and fill in your credentials:
```php
$servername = "your_host";        // e.g., sqlXXX.infinityfree.com
$username   = "your_db_user";
$password   = "your_db_password";
$dbname     = "your_db_name";
```

> ⚠️ **Note:** The current `db.php` uses the legacy `mysqli_connect()` but the rest of the app expects a `$pdo` PDO object. Update `db.php` to use PDO (see [Fix: db.php](#fix-dbphp) below).

**4. Visit the app**

Open `http://localhost/internship-system/` (or your live domain) in a browser.

---

## 🔐 Default Accounts (from `database.sql`)

| Username      | Password    | Role        |
|---------------|-------------|-------------|
| `admin_coord` | `password`  | Coordinator |
| `prof_smith`  | `password`  | Supervisor  |

> Passwords are BCrypt hashed. The seeded hash corresponds to the string `"password"` (Laravel's default test hash). Create your own accounts via the Register form for Students and Companies.

---

## 👥 User Roles & Capabilities

### 🎒 Student
- Browse all **open internship listings**
- **Apply** for positions (one application per internship enforced)
- Track **application status** (pending / approved / rejected)
- Submit **weekly progress logs**
- View **supervisor feedback** on each log

### 🏢 Company
- **Post** new internship opportunities
- **Edit** or **delete** their own listings
- **Toggle** internship status between Open / Closed
- View all **applicants** for their positions

### 🧑‍💼 Coordinator
- View **all applications** system-wide
- **Approve** or **Reject** student placement applications
- Search/filter applications by student or company name

### 👩‍🏫 Supervisor
- View **all student progress logs**
- Submit **feedback/evaluations** per weekly log entry
- Search logs by student name

---

## 🗄️ Database Schema

```
users            — All user accounts (student, company, coordinator, supervisor)
internships      — Jobs posted by companies
applications     — Student applications linking users ↔ internships
progress_logs    — Weekly logs with optional supervisor_feedback
```

**Key relationships:**
- `internships.company_id` → `users.id`
- `applications.student_id` → `users.id`
- `applications.internship_id` → `internships.id`
- `progress_logs.student_id` → `users.id`

---

## ⚠️ Fix: `db.php`

The API files use PDO (`$pdo`), but `includes/db.php` currently uses `mysqli`. Replace the contents of `includes/db.php` with:

```php
<?php
$host   = "your_host";
$dbname = "your_db_name";
$user   = "your_db_user";
$pass   = "your_db_password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
```

---

## 🔒 Security Notes

| Area | Current State | Recommendation |
|------|--------------|----------------|
| Passwords | `password_hash()` / `password_verify()` ✅ | Good — keep BCrypt |
| SQL Injection | PDO prepared statements ✅ | Good |
| XSS | `htmlspecialchars()` on all output ✅ | Good |
| CSRF | ❌ No CSRF tokens | Add `csrf_token` hidden fields |
| Role validation | Server-side `hasRole()` checks ✅ | Good |
| Status input | ❌ `$_GET['status']` not whitelisted | Whitelist `['approved','rejected']` |
| File uploads | None — N/A ✅ | N/A |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4+ (procedural) |
| Database | MySQL via PDO |
| Frontend | Vanilla HTML/CSS/JS |
| Styling | Custom CSS — glassmorphism dark theme |
| Auth | PHP Sessions |
| Hosting | InfinityFree / cPanel / any PHP host |

---

## 🧩 Key Features

- **Glassmorphism UI** — dark theme with blurred glass cards
- **Role-based access control** — server-enforced per route
- **Live table search** — client-side JS filtering without page reload
- **Modal forms** — add/edit internships and submit logs in overlay modals
- **Client-side validation** — real-time required-field checks before submission
- **Flash messages** — success/error alerts that auto-dismiss after 5 seconds
- **Status badges** — color-coded `pending`, `approved`, `rejected`, `open`, `closed`

---

## 📌 Roadmap / Suggested Improvements

- [ ] Fix `db.php` to use PDO (critical — see above)
- [ ] Add CSRF token protection to all forms
- [ ] Whitelist allowed status values in `application_actions.php`
- [ ] Add admin panel for managing all users
- [ ] Assign supervisors to specific students (currently supervisors see all logs)
- [ ] Email notifications on application status change
- [ ] Pagination for large tables
- [ ] File upload support (CV/resume attachment on applications)
- [ ] REST API layer for potential mobile app integration

---

## 📄 License

MIT — free to use, modify, and distribute.
