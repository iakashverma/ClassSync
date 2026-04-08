-- Class Sync Database Schema
-- Run this script in phpMyAdmin or MySQL CLI

DROP DATABASE IF EXISTS classsync;
CREATE DATABASE classsync;
USE classsync;

-- 1. Courses Table
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 2. Sections Table
CREATE TABLE IF NOT EXISTS sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    year VARCHAR(20) NOT NULL,
    section_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Users Table (Updated with new fields)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    registration_number VARCHAR(20) UNIQUE,
    -- Student specific fields
    course_id INT NULL,
    year VARCHAR(20) NULL,
    section_id INT NULL,
    -- Teacher specific fields
    department VARCHAR(100) NULL,
    teacher_course_id INT NULL,
    subject_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_course_id) REFERENCES courses(course_id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Course-Subjects Mapping (Global list of subjects per course)
CREATE TABLE IF NOT EXISTS course_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_subject (course_id, subject_name)
) ENGINE=InnoDB;

-- Now add foreign key for subject_id in users
ALTER TABLE users ADD CONSTRAINT fk_teacher_subject FOREIGN KEY (subject_id) REFERENCES course_subjects(id) ON DELETE SET NULL;

-- 4c. Teacher-Subjects Junction Table (teacher <-> course_subject, many-to-many)
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    course_subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_subject_id) REFERENCES course_subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (teacher_id, course_subject_id)
) ENGINE=InnoDB;

-- 5. Class Assignments Table (Links Teacher <-> Subject <-> Class)
CREATE TABLE IF NOT EXISTS class_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    course_id INT NOT NULL,
    year VARCHAR(20) NOT NULL,
    section_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES course_subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Reports Table
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    course_id INT NOT NULL,
    year VARCHAR(20) NOT NULL,
    section_id INT NOT NULL,
    topic VARCHAR(200) NOT NULL,
    description TEXT,
    homework TEXT,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES course_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Attendance Table
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

-- 8. Assignments Table
CREATE TABLE IF NOT EXISTS assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    deadline DATE NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    course_id INT NOT NULL,
    year VARCHAR(20) NOT NULL,
    section_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES course_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Submissions Table
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    file_path VARCHAR(255),
    submission_text TEXT,
    status ENUM('pending', 'submitted', 'late') NOT NULL DEFAULT 'pending',
    submitted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (assignment_id, student_id)
) ENGINE=InnoDB;

-- 10. Notifications Table
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

INSERT INTO courses (course_name) VALUES ('MCA'), ('BCA'), ('BTech'), ('MTech');

INSERT INTO sections (course_id, year, section_name) VALUES 
-- MCA (ID: 1, 2 years)
(1, '1st', 'A'), (1, '1st', 'B'), (1, '1st', 'C'), (1, '1st', 'D'),
(1, '2nd', 'A'), (1, '2nd', 'B'), (1, '2nd', 'C'), (1, '2nd', 'D'),
-- BCA (ID: 2, 3 years)
(2, '1st', 'A'), (2, '1st', 'B'), (2, '1st', 'C'), (2, '1st', 'D'),
(2, '2nd', 'A'), (2, '2nd', 'B'), (2, '2nd', 'C'), (2, '2nd', 'D'),
(2, '3rd', 'A'), (2, '3rd', 'B'), (2, '3rd', 'C'), (2, '3rd', 'D'),
-- BTech (ID: 3, 4 years)
(3, '1st', 'A'), (3, '1st', 'B'), (3, '1st', 'C'), (3, '1st', 'D'),
(3, '2nd', 'A'), (3, '2nd', 'B'), (3, '2nd', 'C'), (3, '2nd', 'D'),
(3, '3rd', 'A'), (3, '3rd', 'B'), (3, '3rd', 'C'), (3, '3rd', 'D'),
(3, '4th', 'A'), (3, '4th', 'B'), (3, '4th', 'C'), (3, '4th', 'D'),
-- MTech (ID: 4, 2 years)
(4, '1st', 'A'), (4, '1st', 'B'), (4, '1st', 'C'), (4, '1st', 'D'),
(4, '2nd', 'A'), (4, '2nd', 'B'), (4, '2nd', 'C'), (4, '2nd', 'D');
    
-- Course-Subject Mappings
-- BCA Subjects (course_id = 2)
INSERT INTO course_subjects (course_id, subject_name) VALUES
(2, 'Programming'), (2, 'Python'), (2, 'Java'), (2, 'Data Structures'),
(2, 'DBMS'), (2, 'Operating Systems'), (2, 'Computer Networks'),
(2, 'Web Development'), (2, 'Software Engineering'), (2, 'Mathematics');

-- MCA Subjects (course_id = 1)
INSERT INTO course_subjects (course_id, subject_name) VALUES
(1, 'Data Structures (Advanced)'), (1, 'DBMS'), (1, 'Operating Systems'),
(1, 'Computer Networks'), (1, 'Software Engineering'), (1, 'Artificial Intelligence'),
(1, 'Machine Learning'), (1, 'Cloud Computing'), (1, 'Mobile App Development'),
(1, 'Cyber Security'), (1, 'Data Science');

-- BTech Subjects (course_id = 3)
INSERT INTO course_subjects (course_id, subject_name) VALUES
(3, 'Programming'), (3, 'Data Structures'), (3, 'DBMS'), (3, 'Operating Systems'),
(3, 'Computer Networks'), (3, 'Software Engineering'), (3, 'Mathematics (Advanced)'),
(3, 'Artificial Intelligence'), (3, 'Machine Learning'), (3, 'Cyber Security');

-- MTech Subjects (course_id = 4)
INSERT INTO course_subjects (course_id, subject_name) VALUES
(4, 'Artificial Intelligence'), (4, 'Machine Learning'), (4, 'Data Science'),
(4, 'Cloud Computing'), (4, 'Cyber Security'), (4, 'Advanced Data Structures'),
(4, 'Advanced Software Engineering');

-- Admin User (Password: class@121)
INSERT INTO users (name, email, password, role, registration_number) VALUES
('Admin', 'Akash@admin.com', '$2y$10$Y3rJMdHV9CeFagbxVmpu1O03xa3Sfytjpr6JZ.YIGEv5UP9/ju5G2', 'admin', NULL);

-- Teacher User (Password: class@121)
INSERT INTO users (name, email, password, role, registration_number, teacher_course_id) VALUES
('Shivam', 'Shivam@teacher.com', '$2y$10$Y3rJMdHV9CeFagbxVmpu1O03xa3Sfytjpr6JZ.YIGEv5UP9/ju5G2', 'teacher', '123456', 1);

-- Student User (Password: class@121)
-- Map to MCA, 1st Year, Section A (which is section_id=1)
INSERT INTO users (name, email, password, role, registration_number, course_id, year, section_id) VALUES
('Akku', 'Akku@student.com', '$2y$10$Y3rJMdHV9CeFagbxVmpu1O03xa3Sfytjpr6JZ.YIGEv5UP9/ju5G2', 'student', '12345678', 1, '1st', 1);

-- Teacher Subjects (Map Teacher Shivam -> course_subject 1 and 2)
INSERT INTO teacher_subjects (teacher_id, course_subject_id) VALUES 
(2, 1), (2, 2);

-- Sample Reports
INSERT INTO reports (teacher_id, subject_id, course_id, year, section_id, topic, description, homework, date) VALUES
(2, 2, 1, '1st', 1, 'Introduction to Java', 'Covered basic OOP concepts', 'Practice Classes', '2026-04-01');

-- Sample Assignments
INSERT INTO assignments (title, description, deadline, teacher_id, subject_id, course_id, year, section_id) VALUES
('Java Practice Set', 'Complete 20 Java programs.', '2026-04-15', 2, 2, 1, '1st', 1);

-- Sample Attendance
INSERT INTO attendance (student_id, date, status, marked_by) VALUES
(3, '2026-04-01', 'present', 2);

-- Sample Notifications
INSERT INTO notifications (user_id, message, type) VALUES
(3, 'New report uploaded: Introduction to SQL', 'report');
