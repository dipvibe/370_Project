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
git clone https://github.com/dipvibe/370_Project.git
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
*   `General_User`: Stores common user data (login, role).
*   `Worker` / `Employer`: Extension tables for role-specific data.
*   `Job_List`: Stores job postings.
*   `Job_Request`: Manages job applications.
*   `Hires`: Tracks active employment contracts.
*   `Review`: Stores ratings and comments.
*   `Issue_Report`: Manages disputes and complaints.
