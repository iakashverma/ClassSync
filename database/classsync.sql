-- Class Sync Database Schema
-- Run this script in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS classsync;
USE classsync;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    registration_number VARCHAR(20) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Reports Table (Daily Class Reports)
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    topic VARCHAR(200) NOT NULL,
    description TEXT,
    homework TEXT,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL DEFAULT 'present',
    marked_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, date)
) ENGINE=InnoDB;

-- Assignments Table
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    deadline DATE NOT NULL,
    teacher_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Submissions Table
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT,
    status ENUM('pending', 'submitted', 'late') NOT NULL DEFAULT 'pending',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (assignment_id, student_id)
) ENGINE=InnoDB;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(500) NOT NULL,
    type ENUM('report', 'assignment', 'attendance', 'general') NOT NULL DEFAULT 'general',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Default / Seed Data
-- ============================================

-- Admin User (Password: class@121)
INSERT INTO users (name, email, password, role, registration_number) VALUES
('Admin', 'Akash@admin.com', '$2y$10$YMU2OTYwNjRjZTg1ZTA3OeQvbxKgnLqkGRMFr2XGq8kAAEbUKSWi2', 'admin', NULL);

-- Teacher User (Password: class@121)
INSERT INTO users (name, email, password, role, registration_number) VALUES
('Shivam', 'Shivam@teacher.com', '$2y$10$YMU2OTYwNjRjZTg1ZTA3OeQvbxKgnLqkGRMFr2XGq8kAAEbUKSWi2', 'teacher', '123456');

-- Student User (Password: class@121)
INSERT INTO users (name, email, password, role, registration_number) VALUES
('Akku', 'Akku@student.com', '$2y$10$YMU2OTYwNjRjZTg1ZTA3OeQvbxKgnLqkGRMFr2XGq8kAAEbUKSWi2', 'student', '12345678');

-- Sample Reports
INSERT INTO reports (teacher_id, subject, topic, description, homework, date) VALUES
(2, 'Database Management', 'Introduction to SQL', 'Covered basic SQL commands including SELECT, INSERT, UPDATE, and DELETE operations.', 'Practice 10 SQL queries from textbook Chapter 3', '2026-04-01'),
(2, 'Web Technology', 'HTML Forms & Validation', 'Discussed form elements, input types, and client-side validation using JavaScript.', 'Create a registration form with validation', '2026-04-02'),
(2, 'Data Structures', 'Linked Lists', 'Covered singly linked list operations: insertion, deletion, and traversal.', 'Implement doubly linked list in C', '2026-04-03'),
(2, 'Database Management', 'Normalization', 'Explained 1NF, 2NF, 3NF, and BCNF with examples.', 'Normalize the given table to 3NF', '2026-04-04');

-- Sample Assignments
INSERT INTO assignments (title, description, deadline, teacher_id) VALUES
('SQL Practice Set', 'Complete 20 SQL queries covering joins, subqueries, and aggregate functions.', '2026-04-15', 2),
('Web Portfolio Project', 'Build a personal portfolio website using HTML, CSS, and JavaScript.', '2026-04-20', 2);

-- Sample Attendance
INSERT INTO attendance (student_id, date, status, marked_by) VALUES
(3, '2026-04-01', 'present', 2),
(3, '2026-04-02', 'present', 2),
(3, '2026-04-03', 'absent', 2),
(3, '2026-04-04', 'present', 2);

-- Sample Notifications
INSERT INTO notifications (user_id, message, type) VALUES
(3, 'New report uploaded: Introduction to SQL', 'report'),
(3, 'Assignment deadline approaching: SQL Practice Set', 'assignment');
