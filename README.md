# ClassSync - Daily College Class Work Report System

## Tech Stack
- PHP (no framework)
- MySQL
- HTML, CSS, JavaScript

## Setup (XAMPP)
1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin and import `database.sql` from this project.
3. Ensure database credentials in `includes/config.php` are correct.
4. Open in browser:
   - `http://localhost/ClassSync%202.0/`

## Default Admin
- Email: admin@classsync.com
- Password: classsync@121

## Project Structure
- /admin
- /teacher
- /student
- /includes
- /uploads
- /css
- /js
- index.php

## Highlights
- Role-based secure login (admin/teacher/student)
- Strict assignment mapping:
  - Teacher -> Course + Year + Section + Subject
  - Student -> Course + Year + Section
- Section-based visibility checks on backend
- Deadline validation in PHP (late submissions blocked)
- Submission types: text or PDF only
- Attendance + submission status tracking
- Feedback with remarks and marks
- Weekly report generation for admin, teacher, and student views

## Notes
- Keep exactly 5 subjects per course-year-section using admin subject management.
- Upload directory is `/uploads` and stores uniquely named PDF files.
