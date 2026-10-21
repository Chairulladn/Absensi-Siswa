# 🎓 Absensi Siswa

### Student Attendance Management System

**A simple web-based student attendance management system built with PHP and MySQL.**

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-323330?style=for-the-badge&logo=javascript&logoColor=F7DF1E)

![Developer](https://img.shields.io/badge/Developer-Chairulladn-181717?style=flat-square&logo=github)
![Status](https://img.shields.io/badge/Status-Active%20Development-2ea44f?style=flat-square)

---

## ✨ Overview

**Absensi Siswa** is a web-based application designed to manage student records and attendance efficiently.

Built with **PHP, MySQL, and JavaScript**, it provides a straightforward attendance workflow through a clean, dark-themed dashboard.

## 🚀 Key Features

- 📊 **Dashboard** — Attendance overview
- 👨‍🎓 **Student Management** — Manage student information
- 📝 **Manual Attendance** — Record student attendance
- 📅 **Attendance History** — Track attendance records
- 🔐 **Admin Authentication** — Administrator login system

## 🛠️ Tech Stack

| Category | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Web Server | Apache |

## ⚙️ Getting Started

### 1. Clone Repository

```bash
git clone https://github.com/Chairulladn/Absensi-Siswa.git
```

### 2. Configure Database

Create a MySQL database and copy `config/koneksi.example.php` to `config/koneksi.php`.

Update the database credentials according to your local environment.

> **Note:** An importable database schema is not yet included. Required tables must be created before running the application.

### 3. Create Admin

After setting up the required database tables, run:

```bash
php setup/create_admin.php
```

### 4. Run Application

Start Apache and MySQL, place the project in your web server's document root, and open:

```text
http://localhost/Absensi-Siswa/
```

## 👨‍💻 Developer

**Built and maintained by Chairulladn**

[![GitHub](https://img.shields.io/badge/GitHub-Visit%20Profile-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/Chairulladn)

---

*Built with curiosity. Improved through experience.*
