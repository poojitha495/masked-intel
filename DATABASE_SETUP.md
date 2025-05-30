# Database Setup Instructions

## Prerequisites
- XAMPP, WAMP, or similar local server environment
- phpMyAdmin
- PHP 7.0 or higher

## Setup Steps

1. **Install Database Server**
   - Install XAMPP or WAMP if you haven't already
   - Start Apache and MySQL services

2. **Create Database**
   - Open phpMyAdmin (usually at http://localhost/phpmyadmin)
   - Create a new database named `masked_intel`
   - Import the provided SQL file (if available)

3. **Configure Database Connection**
   - Copy `config.template.php` to `config.php`
   - Edit `config.php` and update the following:
     ```php
     define('DB_USER', 'your_username');  // Usually 'root' for local development
     define('DB_PASS', 'your_password');  // Usually blank for local development
     define('BASE_URL', 'http://localhost/your_project_folder/'); // Update this path
     ```

4. **Verify Setup**
   - Navigate to the project URL in your browser
   - Try to log in with the provided test credentials (if any)
   - If you encounter any errors, check the error log

## Security Notes
- Never share your actual `config.php` file containing real credentials
- Change default passwords in production environment
- Enable error reporting only during development
- Use strong passwords for database users
- Regularly backup your database

## Common Issues
1. **Connection Failed**
   - Verify MySQL service is running
   - Check if username and password are correct
   - Ensure database name matches exactly

2. **Access Denied**
   - Check if the MySQL user has proper permissions
   - Verify the host setting (usually 'localhost')

For any issues, check the error log file or contact the system administrator. 