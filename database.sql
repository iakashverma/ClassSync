CREATE DATABASE IF NOT EXISTS classsync_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classsync_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    teacher_unique_id VARCHAR(4) NULL UNIQUE,
    student_unique_id VARCHAR(6) NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users
ADD COLUMN IF NOT EXISTS teacher_unique_id VARCHAR(4) NULL UNIQUE AFTER name;

ALTER TABLE users
ADD COLUMN IF NOT EXISTS student_unique_id VARCHAR(6) NULL UNIQUE AFTER teacher_unique_id;

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year_name VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(10) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(120) NOT NULL UNIQUE
);

ALTER TABLE subjects
ADD COLUMN IF NOT EXISTS subject_name VARCHAR(120) NOT NULL UNIQUE AFTER id;

ALTER TABLE subjects
ADD COLUMN IF NOT EXISTS name VARCHAR(120) NULL;

UPDATE subjects
SET subject_name = name
WHERE (subject_name IS NULL OR subject_name = '')
    AND name IS NOT NULL
    AND name <> '';

UPDATE subjects
SET name = subject_name
WHERE (name IS NULL OR name = '')
    AND subject_name IS NOT NULL
    AND subject_name <> '';

CREATE TABLE IF NOT EXISTS teacher_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    course_id INT NOT NULL,
    year_id INT NOT NULL,
    section_id INT NOT NULL,
    UNIQUE KEY uq_teacher_scope (teacher_id, subject_id, course_id, year_id, section_id),
    CONSTRAINT fk_ta_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_year FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS student_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL UNIQUE,
    course_id INT NOT NULL,
    year_id INT NOT NULL,
    section_id INT NOT NULL,
    CONSTRAINT fk_sa_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sa_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_sa_year FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE,
    CONSTRAINT fk_sa_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS classwork (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    assignment_id INT NOT NULL,
    course_id INT NOT NULL,
    year_id INT NOT NULL,
    section_id INT NOT NULL,
    subject_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    homework_instructions TEXT NOT NULL,
    deadline DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cw_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cw_assignment FOREIGN KEY (assignment_id) REFERENCES teacher_assignments(id) ON DELETE CASCADE,
    CONSTRAINT fk_cw_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_cw_year FOREIGN KEY (year_id) REFERENCES years(id) ON DELETE CASCADE,
    CONSTRAINT fk_cw_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    CONSTRAINT fk_cw_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classwork_id INT NOT NULL,
    student_id INT NOT NULL,
    type ENUM('text','pdf') NOT NULL,
    content TEXT NULL,
    file_path VARCHAR(255) NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_submission_once (classwork_id, student_id),
    CONSTRAINT fk_sub_classwork FOREIGN KEY (classwork_id) REFERENCES classwork(id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    classwork_id INT NOT NULL,
    status ENUM('Present','Absent') NOT NULL DEFAULT 'Present',
    submission_status ENUM('Submitted','Not Submitted') NOT NULL DEFAULT 'Not Submitted',
    UNIQUE KEY uq_attendance_once (student_id, classwork_id),
    CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_classwork FOREIGN KEY (classwork_id) REFERENCES classwork(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL UNIQUE,
    teacher_id INT NOT NULL,
    remarks TEXT,
    marks DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fb_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_fb_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    week_start DATE NOT NULL,
    attendance_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    submission_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_report_week (student_id, week_start),
    CONSTRAINT fk_report_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO courses (name)
SELECT * FROM (
    SELECT 'BCA' AS name UNION ALL
    SELECT 'MCA' UNION ALL
    SELECT 'BTech' UNION ALL
    SELECT 'MTech'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM courses LIMIT 1);

INSERT INTO years (year_name)
SELECT * FROM (
    SELECT '1st Year' AS year_name UNION ALL
    SELECT '2nd Year' UNION ALL
    SELECT '3rd Year' UNION ALL
    SELECT '4th Year'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM years LIMIT 1);

INSERT INTO sections (section_name)
SELECT * FROM (
    SELECT 'A' AS section_name UNION ALL
    SELECT 'B' UNION ALL
    SELECT 'C'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM sections LIMIT 1);

INSERT INTO subjects (subject_name, name)
SELECT * FROM (
    SELECT 'Java' AS subject_name, 'Java' AS name UNION ALL
    SELECT 'DSA', 'DSA' UNION ALL
    SELECT 'PHP', 'PHP' UNION ALL
    SELECT 'Networking', 'Networking' UNION ALL
    SELECT 'Linux', 'Linux' UNION ALL
    SELECT 'DBMS', 'DBMS'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM subjects LIMIT 1);

INSERT INTO users (name, email, password, role)
SELECT 'System Admin', 'admin@classsync.com', '$2y$10$ZiiEa.9RS8ZTQ9rbhVtlSO2ZU/Ot0CdRto4XfwiPplRqfFNM1AR.2', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@classsync.com'
);

UPDATE users
SET email = 'admin@classsync.com',
        password = '$2y$10$ZiiEa.9RS8ZTQ9rbhVtlSO2ZU/Ot0CdRto4XfwiPplRqfFNM1AR.2'
WHERE role = 'admin'
    AND email IN ('admin@classsync.local', 'admin@classsync.com');

-- Teacher login.....

-- Teacher ID: 3217, 
-- Email: kunal3217@classsync.com, 
-- MCA	1st Year	A	Java



-- Student login.....

-- Student ID: 980638, 
-- Email: dhiraj980638@classsync.com, 
-- MCA	1st Year	A


-- Default password for all user: classsync@121