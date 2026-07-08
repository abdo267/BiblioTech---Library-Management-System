# Installation Guide

Steps to run this on XAMPP.

## Requirements
- XAMPP with PHP and MySQL

---

## Setup

1. **Copy files to XAMPP**
   - Move the project folder to: `C:\xampp\htdocs\Library Management System`

2. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

3. **Create database**
   - Go to http://localhost/phpmyadmin
   - Create database named `library_db`
   - Import the `database.sql` file

4. **Run the app**
   - Open: http://localhost/Library%20Management%20System/public/index.php

---

## Default Accounts

**Admin:**
- Email: admin@library.com
- Password: admin123

**Member:**
- Email: member@library.com
- Password: member123

---

## Notes

Email notifications are simulated and logged to `email_simulation.log` in the project folder.
