-- Create the library database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- In production, store hashed passwords
    email VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a sample user for testing
-- In production, use PASSWORD_HASH() function in PHP to hash passwords
INSERT INTO users (username, password, email) 
VALUES ('testuser', 'testpass', 'test@example.com');

-- In production environment, you would use something like:
-- $hashed_password = password_hash('securepassword123', PASSWORD_DEFAULT);
-- INSERT INTO users (username, password, email) VALUES ('admin', '$hashed_password', 'admin@example.com'); 