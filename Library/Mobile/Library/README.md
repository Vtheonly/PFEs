# Android-PHP-MySQL Library Application

This project demonstrates how to create an Android application that communicates with a PHP backend which interacts with a MySQL database.

## Project Structure

- **AndroidApp**: Contains the Android application
- **PhpBackend**: Contains the PHP scripts and SQL setup files

## Setup Instructions

### 1. Database Setup

1. Import the `PhpBackend/database_setup.sql` file into your MySQL server:
   ```
   mysql -u username -p < PhpBackend/database_setup.sql
   ```
   Or use phpMyAdmin or another MySQL client to run the SQL statements.

2. This will:
   - Create the `library_db` database
   - Create the `users` table
   - Add a sample user with username `testuser` and password `testpass`

### 2. PHP Backend Setup

1. Copy the PHP files from the `PhpBackend` directory to your web server's document root (e.g., `/var/www/html/` for Apache).

2. Edit the database configuration in each PHP file to match your MySQL setup:
   ```php
   $db_host = "localhost";  // Or your MySQL server IP
   $db_user = "your_db_user"; // Replace with your MySQL username
   $db_pass = "your_db_password"; // Replace with your MySQL password
   $db_name = "library_db";
   ```

3. Make sure your web server has PHP and the MySQLi extension installed and enabled.

### 3. Android Application Setup

1. Open the `AndroidApp` directory in Android Studio.

2. Edit the `MainActivity.java` file to update the `LOGIN_URL` to point to your server:
   - For Android Emulator and local server: `http://10.0.2.2/path/to/login.php`
   - For a physical device on the same network: `http://your_computer_ip/path/to/login.php`
   - For a live server: `https://yourdomain.com/path/to/login.php`

3. Build and run the application on your emulator or device.

## Security Considerations

1. **HTTPS**: Always use HTTPS in production to encrypt data transfer.

2. **Password Hashing**: 
   - The `secure_login.php` and `register.php` scripts use proper password hashing.
   - The `login.php` script is a simplified version for demonstration only and should NOT be used in production.

3. **Input Validation**: All scripts include basic input validation, but you may want to add more robust validation.

4. **Error Messages**: Be careful with error messages in production; don't expose sensitive information.

## API Documentation

### Login API (`login.php` or `secure_login.php`)

**Request:**
- Method: POST
- Parameters:
  - `username`: User's username
  - `password`: User's password

**Response:**
```json
{
    "status": "success",
    "message": "Login successful!",
    "user_id": "1",
    "username": "testuser"
}
```
or
```json
{
    "status": "error",
    "message": "Invalid username or password."
}
```

### Registration API (`register.php`)

**Request:**
- Method: POST
- Parameters:
  - `username`: Desired username
  - `password`: Desired password
  - `email`: User's email address

**Response:**
```json
{
    "status": "success",
    "message": "Registration successful!",
    "user_id": "2",
    "username": "newuser"
}
```
or
```json
{
    "status": "error",
    "message": "Username or email already exists."
}
```

## Further Improvements

1. Implement token-based authentication for better security
2. Add more features like password reset, email verification
3. Add CSRF protection to the PHP scripts
4. Implement rate limiting to prevent brute force attacks
5. Use HTTPS and certificate pinning in the Android app 