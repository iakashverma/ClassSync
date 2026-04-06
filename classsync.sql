-- ClassSync Database Setup
-- Run this file in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS classsync;
USE classsync;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL,
    uid VARCHAR(10) NULL,
    reg_no VARCHAR(10) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Classwork / Assignments table
CREATE TABLE IF NOT EXISTS classwork (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    topic VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deadline DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Submissions table
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classwork_id INT NOT NULL,
    student_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'submitted',
    FOREIGN KEY (classwork_id) REFERENCES classwork(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================
-- Default Users (pre-seeded)
-- ========================

-- Admin: akash@admin.com / admin@121
INSERT INTO users (name, email, password, role) VALUES
('Akash Verma', 'akash@admin.com', '$2y$10$RR/MHvfHZlahcuAizAxsFe0COrpcNdZc/mAppF4CV4fKpdbvk5olC', 'admin');

-- Teacher: deep@teacher.com / teacher@121
INSERT INTO users (name, email, password, role, uid) VALUES
('Deep ', 'deep@teacher.com', '$2y$10$QgeYUFs58NhQhR7PjVPlZu4uAIDlu4dxPAJo2PWafu.f7EwJ7fzGG', 'teacher', '100001');

-- Student: aman@student.com / student@121
INSERT INTO users (name, email, password, role, reg_no) VALUES
('Aman Kumar', 'aman@student.com', '$2y$10$jxs2MHay8a1UqAzikr/nH.rGHIN6loWgxPF0dlqMkT4Lfh5X/INJO', 'student', '20230001');

-- Some default subjects
INSERT INTO subjects (subject_name) VALUES
('Data Structures'),
('Database Management System'),
('Web Technology'),
('Operating Systems'),
('Computer Networks');
