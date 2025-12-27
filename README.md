
## Authors

- **Rashene Dillon**
- **Tahmia Vincent**
- **Zakyla Petrie**
- **Damion Henry**

---

# Dolphin CRM

Dolphin CRM is a lightweight customer relationship management (CRM) web application built with PHP and MySQL.  
It allows users to manage contacts, assign leads, track support requests, and add notes — all through a clean, modern dashboard with AJAX-powered navigation.

The system supports role-based access control, ensuring that only Admin users can manage system users while Members focus on contact and note management.

---

## Features

- User authentication and role-based authorization (Admin / Member)
- Dashboard with contact filtering (All, Sales Leads, Support, Assigned to Me)
- Create, view, and manage contacts
- Assign contacts to users
- Toggle contact type (Sales Lead ↔ Support)
- Add and view notes for contacts
- AJAX-based navigation (no full page reloads)
- Responsive UI with Font Awesome icons

---

## Technologies Used

- **PHP 8+**
- **MySQL**
- **HTML5 / CSS3**
- **Vanilla JavaScript (AJAX / Fetch API)**
- **Font Awesome**
- **Apache (via XAMPP / LAMP / WAMP)**

---

## Setup Instructions

### 1. Prerequisites

Ensure you have the following installed:

- PHP 8.0 or higher
- MySQL / MariaDB
- Apache Web Server  
  (XAMPP, LAMP, or WAMP recommended)

---


### 2. Clone the Repository into your htdocs folder

Naviagate to your htdocs folder then clone the repo as shown below.

```bash
git clone https://github.com/rashdill10/info2180-project2.git
```

---

### 3. Database Setup

Ensure you're login to your MySQL account with a user that has permission to create database and tables.

Run the schema.sql file

This will:

- Create the dolphin_crm database (if it doesn’t exist)

- Create all required tables

- Insert a default Admin user
    - Email: admin@project2.com
    - Password: password123

### 4. Configure Database Connection

Edit the database configuration file

- config/database.php

Ensure credentials match your MySQL setup:

```
$host = 'localhost'; // or 127.0.0.1
$dbname = 'dolphin_crm'; // your MySQL database goes here
$user = ''; // your MySQL username goes here
$pass = ''; // your MySQL password goes here

```

### 5. Run The Application

Start Apache and MySQL, then visit:

- localhost/info2180-project2/public/login.php


- Note you can change your **DocumentRoot** path to;
    - lampp/htdocs/info2180-project2/public

  and then add
    - login.php
  to your **DirectoryIndex**

This way you can just launch the Application with just
- localhost:80