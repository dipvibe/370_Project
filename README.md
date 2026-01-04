# Household Network Project

## Setup Instructions

### 1. Database Setup
This project uses MySQL. You need to import the database schema.

Run the following command in your terminal (you will be prompted for your MySQL password):
```bash
mysql -u root -p < project_files/database.sql
```

### 2. Configuration
Check `project_files/connection.php`.
Ensure the `$user` and `$pass` variables match your local MySQL configuration.
If you installed MySQL officially on macOS, you likely set a root password during installation.

```php
$host = "localhost";
$user = "root";
$pass = "YOUR_MYSQL_PASSWORD"; // Update this!
$db   = "house_hold_network";
```

### 3. Running the Application
You can use the built-in PHP server to run the project locally.

Run this command from the root of the project:
```bash
php -S localhost:8000 -t project_files
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.
