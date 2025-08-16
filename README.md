# Simple Blog with PHP

A simple and beautiful blog system built with PHP and MySQL.

## Features

- ✅ User registration and login
- ✅ Write and publish new posts
- ✅ Display posts in chronological order
- ✅ Beautiful and responsive user interface
- ✅ High security with password hashing

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or XAMPP/WAMP

## Installation

### 1. Clone the project
```bash
git clone [repository-url]
cd weblog
```

### 2. Database setup
- Create MySQL database
- Run `database.sql` file in phpMyAdmin or MySQL Workbench
- Or create tables manually

### 3. Database connection setup
Edit `connect.php` file and enter your database information:
```php
$host = 'localhost';
$dbname = 'weblog';
$username = 'root';
$password = '';
```

### 4. Run
Place the project in your web server and open it through the browser.

## File Structure

- `index.php` - Main page and post display
- `login.php` - User login page
- `register.php` - User registration page
- `connect.php` - Database connection
- `database.sql` - Database structure
- `style.css` - CSS styles

## Usage

1. First register an account
2. Login
3. Write new posts
4. View your posts and others

## Security

- Passwords are hashed with `password_hash()`
- Prepared statements are used to prevent SQL injection
- User inputs are sanitized with `htmlspecialchars()`

## Support

For questions and issues, please create an issue.

## License

This project is published under MIT license. 