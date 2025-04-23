
CREATE DATABASE IF NOT EXISTS exam_platform;
USE exam_platform;


CREATE TABLE IF NOT EXISTS account (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS candidate (
    candidate_id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    group_name VARCHAR(20) NOT NULL,
    section VARCHAR(20) NOT NULL,
    FOREIGN KEY (account_id) REFERENCES account(account_id)
);


CREATE TABLE IF NOT EXISTS module (
    module_id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(100) NOT NULL,
    module_code VARCHAR(20) NOT NULL UNIQUE,
    exam_duration INT NOT NULL, 
    total_questions INT NOT NULL,
    points_per_question INT NOT NULL,
    passing_score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS candidate_module (
    candidate_module_id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT NOT NULL,
    module_id INT NOT NULL,
    score INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    passed BOOLEAN NOT NULL,
    exam_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidate(candidate_id),
    FOREIGN KEY (module_id) REFERENCES module(module_id)
);



INSERT INTO account (username, password) VALUES
('student1', 'pass123'),
('student2', 'pass456'),
('student3', 'pass789');


INSERT INTO candidate (account_id, first_name, last_name, email, group_name, section) VALUES
(1, 'Ahmed', 'Benali', 'ahmed.benali@example.com', 'L2', 'A'),
(2, 'Fatiha', 'Kaddour', 'fatiha.kaddour@example.com', 'L2', 'A'),
(3, 'Karim', 'Laribi', 'karim.laribi@example.com', 'L2', 'A');


INSERT INTO module (module_name, module_code, exam_duration, total_questions, points_per_question, passing_score) VALUES
('Web Application Development', 'WAD', 60, 10, 2, 12),
('Object-Oriented Programming', 'OOP', 45, 10, 2, 12),
('Language Theory', 'LTH', 50, 11, 2, 14);