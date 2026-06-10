CREATE DATABASE IF NOT EXISTS timetracker;
USE timetracker;

-- ADMIN USERS
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE deployments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deployment_id VARCHAR(100) NOT NULL
);

-- EMPLOYEES
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TIME ENTRIES (COME / GO LOGS)
CREATE TABLE time_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    action ENUM('come','go') NOT NULL,
    entry_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
        ON DELETE CASCADE
);

-- DEFAULT ADMIN (password: admin123)
INSERT INTO admins (username, password)
VALUES (
    'admin',
    '$2y$10$u5Sv4fGLOcABGk3cvqhNZuYReyC/z4wEIZQV8WsB8S9PH3a1kMv1a'
);
