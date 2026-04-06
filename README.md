# Class Sync – Implementation Plan

A web-based classroom management system built with **PHP + MySQL** on **XAMPP**, replacing traditional physical registers.

## User Review Required

> [!IMPORTANT]
> **Database**: This plan uses MySQL via XAMPP's phpMyAdmin. The database will be named `classsync`. Please confirm this is acceptable.

> [!IMPORTANT]
> **Design**: Per requirements, the UI will be clean and simple (blue/white theme, basic CSS, no heavy frameworks). It should look student-developed, not overly polished.

> [!IMPORTANT]
> **Project Location**: All files will be created in `c:\xampp\htdocs\ClassSync\`. The app will be accessible at `http://localhost/ClassSync/`.

---

## Proposed Changes

### 1. Database Setup

#### [NEW] `database/classsync.sql`
SQL script to create the `classsync` database with these tables:

| Table | Key Columns |
|-------|-------------|
| `users` | id, name, email, password (hashed), role (admin/teacher/student), registration_number |
| `reports` | report_id, teacher_id, subject, topic, description, homework, date |
| `attendance` | id, student_id, date, status (present/absent/late) |
| `assignments` | id, title, description, deadline, teacher_id, created_at |
| `submissions` | id, assignment_id, student_id, file_path, status (pending/submitted/late), submitted_at |
| `notifications` | id, user_id, message, type, is_read, created_at |

Default users seeded:
- **Admin**: Akash@admin.com / class@121
- **Teacher**: Shivam@teacher.com / class@121 / Reg: 123456
- **Student**: Akku@student.com / class@121 / Reg: 12345678

---

### 2. Configuration & Includes

#### [NEW] `config/database.php`
- MySQL connection using `mysqli`
- Session initialization
- Error reporting settings

#### [NEW] `includes/header.php`
- Common HTML head, navbar, session checks

#### [NEW] `includes/footer.php`
- Common footer HTML

#### [NEW] `includes/auth_check.php`
- Role-based access control helper functions

---

### 3. Public Pages

#### [NEW] `index.php` – Home Page
Sections:
- **Navbar**: Logo "Class Sync", links (Home, About, Features, Login, Register)
- **Hero Section**: Title, subtitle, CTA buttons (Get Started → Register, Login)
- **Features Section**: 4 cards (Daily Reports, Attendance, Assignments, Timeline)
- **About Section**: Short system description
- **Footer**: Basic info

#### [NEW] `login.php` – Login Page
- Fields: Registration Number / Email, Password
- Smart login logic: 6-digit → Teacher, 8-digit → Student, email with @admin → Admin
- Redirects to appropriate dashboard after auth

#### [NEW] `register.php` – Registration Page
- Role selection (Teacher / Student) – shown as tabs or radio buttons
- Teacher form: Name, 6-digit Reg No, Email, Password
- Student form: Name, 8-digit Reg No, Email, Password
- Server-side validation + password hashing

#### [NEW] `logout.php`
- Destroy session, redirect to home

---

### 4. Admin Dashboard

#### [NEW] `admin/index.php` – Admin Dashboard
- Overview stats: total teachers, students, reports, assignments
- Quick links to management pages

#### [NEW] `admin/manage_users.php`
- List all users (teachers & students)
- Add / Edit / Delete users

#### [NEW] `admin/manage_reports.php`
- View all reports across teachers

#### [NEW] `admin/manage_attendance.php`
- View attendance records

#### [NEW] `admin/manage_assignments.php`
- View all assignments and submissions

---

### 5. Teacher Dashboard

#### [NEW] `teacher/index.php` – Teacher Dashboard
- Welcome message, quick stats
- Recent reports, upcoming deadlines

#### [NEW] `teacher/reports.php`
- Add new daily class report (Subject, Topic, Description, Homework, Date)
- View/edit own reports

#### [NEW] `teacher/attendance.php`
- Mark attendance for students (date picker, checkboxes)
- View attendance history

#### [NEW] `teacher/assignments.php`
- Create assignment (Title, Description, Deadline)
- View submissions and their status

---

### 6. Student Dashboard

#### [NEW] `student/index.php` – Student Dashboard
- Welcome, quick stats, notifications

#### [NEW] `student/reports.php`
- View daily reports
- Filter by subject and date
- Missed class recovery: see notes + homework for missed dates

#### [NEW] `student/attendance.php`
- View own attendance percentage
- Low attendance alert if below 75%

#### [NEW] `student/assignments.php`
- View assignments
- Submit assignments
- Status tracking (Pending/Submitted/Late)

#### [NEW] `student/timeline.php` – Academic Timeline
- Daily learning history
- Subject-wise tracking

---

### 7. API / Action Files

#### [NEW] `actions/auth_action.php`
- Handle login & registration form submissions

#### [NEW] `actions/report_action.php`
- CRUD operations for reports

#### [NEW] `actions/attendance_action.php`
- Mark and retrieve attendance

#### [NEW] `actions/assignment_action.php`
- CRUD for assignments + submission handling

#### [NEW] `actions/notification_action.php`
- Create and fetch notifications

---

### 8. Stylesheets

#### [NEW] `assets/css/style.css`
- Global styles, reset, typography
- Blue/white color scheme
- Responsive layout
- Clean card components, form styling, table styling

#### [NEW] `assets/css/dashboard.css`
- Sidebar layout for dashboards
- Dashboard-specific components

#### [NEW] `assets/css/auth.css`
- Login/register page specific styles

---

### 9. JavaScript

#### [NEW] `assets/js/main.js`
- Form validation
- Role-based form toggle on register page
- Search & filter functionality
- Notification polling

---

## File Structure Summary

```
ClassSync/
├── index.php                    # Home page
├── login.php                    # Login page
├── register.php                 # Registration page
├── logout.php                   # Logout handler
├── config/
│   └── database.php             # DB connection
├── includes/
│   ├── header.php               # Common header/navbar
│   ├── footer.php               # Common footer
│   └── auth_check.php           # Auth helpers
├── database/
│   └── classsync.sql            # DB schema + seed data
├── admin/
│   ├── index.php                # Admin dashboard
│   ├── manage_users.php         # User management
│   ├── manage_reports.php       # Reports overview
│   ├── manage_attendance.php    # Attendance overview
│   └── manage_assignments.php   # Assignments overview
├── teacher/
│   ├── index.php                # Teacher dashboard
│   ├── reports.php              # Add/view reports
│   ├── attendance.php           # Mark/view attendance
│   └── assignments.php          # Manage assignments
├── student/
│   ├── index.php                # Student dashboard
│   ├── reports.php              # View reports + missed class
│   ├── attendance.php           # View attendance
│   ├── assignments.php          # View/submit assignments
│   └── timeline.php             # Academic timeline
├── actions/
│   ├── auth_action.php          # Auth handlers
│   ├── report_action.php        # Report CRUD
│   ├── attendance_action.php    # Attendance handlers
│   ├── assignment_action.php    # Assignment handlers
│   └── notification_action.php  # Notification handlers
└── assets/
    ├── css/
    │   ├── style.css            # Global styles
    │   ├── dashboard.css        # Dashboard styles
    │   └── auth.css             # Auth page styles
    └── js/
        └── main.js              # Client-side logic
```

---

## Open Questions

> [!IMPORTANT]
> 1. **XAMPP running?** Is your XAMPP Apache + MySQL currently running? I'll need to create the database.
> 2. **File uploads for assignments**: Should students upload actual files, or just a text submission?
> 3. **Notifications**: Simple database-driven notifications shown on dashboard, or do you want browser push notifications?

---

## Verification Plan

### Automated Tests
- Run the SQL script to verify database creation
- Test each page loads without PHP errors via browser

### Manual Verification
- Register as teacher and student with the provided test credentials
- Login with each role and verify dashboard redirect
- Add a report as teacher, verify it shows for student
- Mark attendance, verify student sees percentage
- Create assignment, submit as student, verify status
- Test smart login detection (6-digit → teacher, 8-digit → student, email → admin)
