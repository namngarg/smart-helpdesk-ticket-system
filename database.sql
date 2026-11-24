CREATE DATABASE IF NOT EXISTS helpdesk_db;
USE helpdesk_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    deadline DATE NULL,
    created_by INT NOT NULL,
    assigned_to INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

CREATE TABLE ticket_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Seed users: password for admin is "admin123", for employee is "employee123"
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@helpdesk.com', '$2y$12$cfr5wqdqdyo.2LDRw0D2A.z9l.LnXiVTzeYBRQDbbKgyVUNG1TpWu', 'admin'),
('Employee One', 'employee1@helpdesk.com', '$2y$12$OZMmDV7LKsNGM2lvtndIY.jl4CZFrS23ESkzGpJnMIQxbBTTGRFge', 'employee');
