# Household Network Project

A comprehensive web-based platform connecting household workers with employers. This application facilitates job posting, application management, hiring, and dispute resolution within a secure environment.

## 📋 Prerequisites

Before running this project, ensure your machine has the following installed:

1.  **PHP**: Version 7.4 or higher.
2.  **MySQL**: Version 5.7 or higher (or MariaDB).
3.  **Web Server**: Apache, Nginx, or the built-in PHP development server.
    *   *Note: All-in-one packages like **XAMPP** (Windows/Linux/macOS), **MAMP** (macOS), or **WAMP** (Windows) are recommended for beginners as they include PHP, MySQL, and Apache.*

## 🚀 Installation & Setup Guide

Follow these steps to get the project running on your local machine.

### 1. Clone or Download the Repository
If you haven't already, download the project files to your computer.
```bash
git clone <https://github.com/dipvibe/370_Project.git>
cd 370_project
```

### 2. Database Configuration

1.  **Start your MySQL Server**.
2.  **Create the Database**:
    Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line) and create a new database named `house_hold_network`.
    ```sql
    CREATE DATABASE house_hold_network;
    ```
3.  **Import the Schema**:
    Import the provided `database.sql` file located in the `project_files` directory into your newly created database.
    
    **Using Command Line:**
    ```bash
    mysql -u root -p house_hold_network < project_files/database.sql
    ```
    
    **Using phpMyAdmin:**
    *   Select the `house_hold_network` database.
    *   Click on the "Import" tab.
    *   Choose the `project_files/database.sql` file.
    *   Click "Go".

### 3. Connect the Application to the Database

Open `project_files/connection.php` in a text editor. Update the database credentials to match your local MySQL setup.

```php
<?php
$host = "localhost";
$user = "root";      // Your MySQL username (default is often 'root')
$pass = "";          // Your MySQL password (leave empty for XAMPP default, 'root' for MAMP)
$db   = "house_hold_network";
// ...
?>
```

### 4. Running the Application

#### Option A: Using PHP Built-in Server (Recommended for Quick Testing)
This is the easiest way to run the project without configuring Apache/Nginx.

1.  Open your terminal/command prompt.
2.  Navigate to the project root directory.
3.  Run the following command:
    ```bash
    php -S localhost:8000 -t project_files
    ```
4.  Open your web browser and go to: [http://localhost:8000](http://localhost:8000)

#### Option B: Using XAMPP/WAMP/MAMP
1.  Move the `370_project` folder into your web server's root directory (e.g., `htdocs` for XAMPP, `www` for WAMP).
2.  Start Apache and MySQL from the control panel.
3.  Access the site via: `http://localhost/370_project/project_files/`

---

## 🌟 Features

### 👥 User Roles
The system supports three distinct user roles, each with specific capabilities:

1.  **Employer**: Can post jobs, hire workers, and manage payments.
2.  **Worker**: Can search for jobs, apply, and manage their profile.
3.  **Administrator**: Has oversight of the entire platform, including user management and dispute resolution.

### 🛠 Key Functionalities

#### For Employers
*   **Post Jobs**: Create detailed job listings with salary, schedule, and area.
*   **Manage Applications**: View applicants for specific jobs and hire them.
*   **Profile Management**: Update personal and contact information.
*   **Reviews**: Rate and review workers after a job is completed.
*   **Issue Reporting**: Report issues with workers for admin intervention.

#### For Workers
*   **Job Search**: Filter available jobs by area.
*   **Apply**: Submit applications to job listings.
*   **Application Tracking**: Monitor the status of applications (Pending, Accepted).
*   **Profile Building**: Showcase skills, experience, and availability.
*   **Issue Reporting**: Report issues with employers.

#### For Administrators
*   **Dashboard**: View platform statistics (Total Users, Jobs, etc.).
*   **User Management**: Ban or unban users who violate policies.
*   **Dispute Resolution**: Review and resolve issue reports submitted by workers and employers.

### ⚖️ Dispute Resolution System
A robust logic ensures fair reporting:
*   **Contextual Reporting**: Workers can only report Employers they have worked for (last 3 jobs). Similarly, Employers can only report Workers they have hired.
*   **Admin Oversight**: Administrators review reports, can ban involved parties if necessary, and update the report status (Open -> Reviewed -> Resolved).

---

## 💻 Implementation Details

### Backend
*   **Language**: PHP (Native).
*   **Database Interaction**: MySQLi with **Prepared Statements** to prevent SQL Injection attacks.
*   **Authentication**: Session-based authentication (`$_SESSION`) with role-based access control checks on every protected page.

### Frontend
*   **Framework**: Bootstrap 5.3.2 for responsive, mobile-friendly design.
*   **Styling**: Custom CSS (`style.css`) for consistent branding (Hero sections, Cards, Color scheme).
*   **UX**: Interactive elements like hover effects, clear status badges, and intuitive navigation.

### Database Schema
The application uses a relational database with the following key tables:
*   `General_User`: Stores common user data (login, role, verification status).
*   `Worker` / `Employer`: Extension tables for role-specific data.
*   `Job_List`: Stores job postings.
*   `Job_Request`: Manages job applications.
*   `Hires`: Tracks active employment contracts.
*   `Review`: Stores ratings and comments.
*   `Issue_Report`: Manages disputes and complaints.

---

## 📧 Email Verification Setup

The application includes an email verification system to ensure users provide valid email addresses during registration. This section explains how to configure it.

### How Email Verification Works

1.  **User Signs Up**: When a user registers, the system generates a unique 64-character verification token and stores it in the database along with `is_verified = 0`.
2.  **Email Sent**: A verification email containing a unique link is sent to the user's email address.
3.  **User Clicks Link**: The link directs to `verify_email.php?token=<unique_token>`.
4.  **Verification Complete**: The system validates the token, sets `is_verified = 1`, and clears the token from the database.
5.  **Login Allowed**: Users can only log in after their email is verified.

### Files Involved
| File | Purpose |
|------|---------|
| `mail_config.php` | SMTP configuration and email sending functions |
| `signup.php` | Generates token and triggers verification email |
| `verify_email.php` | Validates token and activates account |
| `resend_verification.php` | Allows users to request a new verification email |
| `login.php` | Checks `is_verified` status before allowing login |

### Configuration Steps

#### Step 1: Get Gmail App Password

Since Gmail blocks regular password authentication for third-party apps, you need to create an **App Password**.

1.  **Enable 2-Step Verification** on your Google Account:
    *   Go to [Google Account Security](https://myaccount.google.com/security)
    *   Click on **2-Step Verification** and follow the setup process

2.  **Generate App Password**:
    *   Go to [App Passwords](https://myaccount.google.com/apppasswords)
    *   Sign in if prompted
    *   Under "Select app", choose **Mail**
    *   Under "Select device", choose **Other (Custom name)**
    *   Enter a name like `Household Network`
    *   Click **Generate**
    *   **Copy the 16-character password** (e.g., `abcd efgh ijkl mnop`)

    > ⚠️ **Important**: This password is shown only once. Save it securely!

#### Step 2: Configure `mail_config.php`

Open `project_files/mail_config.php` and update the SMTP credentials:

```php
// Inside the sendEmail() function, update these lines:
$mail->Username   = 'your-gmail@gmail.com';     // Your Gmail address
$mail->Password   = 'abcdefghijklmnop';         // 16-char App Password (no spaces)

// Also update the setFrom line:
$mail->setFrom('your-gmail@gmail.com', MAIL_FROM_NAME);
```

#### Step 3: Test the Configuration

You can test if emails are working by running this command in the terminal:

```bash
cd project_files
php -r "include 'mail_config.php'; var_dump(sendVerificationEmail('test@example.com', 'Test', 'abc123'));"
```

If it returns `bool(true)`, emails are working!

### SMTP Settings Reference

| Setting | Value |
|---------|-------|
| Host | `smtp.gmail.com` |
| Port | `587` |
| Encryption | `STARTTLS` |
| Authentication | Required (Gmail + App Password) |

### Troubleshooting

| Problem | Solution |
|---------|----------|
| "Authentication failed" | Ensure you're using an App Password, not your regular Gmail password |
| "Connection timed out" | Check if your firewall/antivirus is blocking port 587 |
| "Less secure app blocked" | You must use App Password with 2-Step Verification enabled |
| Email goes to spam | Add the sender email to contacts, or check spam folder |

### Skipping Email Verification (For Local Testing)

If you don't want to set up Gmail SMTP for local development:

1.  After signing up, a **"Verify Email Now"** button is displayed on the success message.
2.  Simply click this button to verify the account without receiving an actual email.

Alternatively, you can manually verify a user in the database:
```sql
UPDATE General_User SET is_verified = 1 WHERE email = 'user@example.com';
```

### PHPMailer Library

This project uses **PHPMailer** for sending emails. It's already included in the `vendor/phpmailer/` directory—no additional installation required.

> **Note**: The `vendor/` folder is local to this project only. Nothing is installed globally on your system.

---

## 🧪 Development & Testing Tools

### Generate Demo Data

To quickly populate the database with sample data for testing, use the `generate_demo_data.php` script.

**How to use:**
1.  Make sure your database is set up and empty (or freshly imported from `database.sql`).
2.  Visit: [http://localhost:8000/generate_demo_data.php](http://localhost:8000/generate_demo_data.php)
3.  The script will create:
    *   **1 Administrator** account
    *   **5 Employer** accounts
    *   **5 Worker** accounts (with skills)
    *   **7 Job listings** (with various work types)
    *   **Sample applications** (pending, accepted, rejected)
    *   **Active hires** with joining dates
    *   **Reviews** between employers and workers
    *   **Issue reports** for testing the dispute system

**Demo Login Credentials:**

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@admin.com` | `1234` |
| Employer | `john@example.com` | `1234` |
| Employer | `jane@example.com` | `1234` |
| Worker | `karim@worker.com` | `1234` |
| Worker | `fatima@worker.com` | `1234` |

> All demo accounts are pre-verified (`is_verified = 1`), so you can log in immediately.

### Database Viewer (Development Only)

The `view_database.php` file provides a quick way to inspect all database tables during development.

**How to use:**
*   Visit: [http://localhost:8000/view_database.php](http://localhost:8000/view_database.php)
*   This displays all records from every table in a readable format.

> ⚠️ **Security Warning**: This file should be **removed or disabled** before deploying to production, as it exposes all database contents without authentication.
