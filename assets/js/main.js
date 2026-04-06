/* ================================
   Class Sync - Main JavaScript
   ================================ */

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Navbar mobile toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }

    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
});

// Registration form - Role toggle
function switchRole(role) {
    const teacherForm = document.getElementById('teacher-form');
    const studentForm = document.getElementById('student-form');
    const teacherTab = document.getElementById('tab-teacher');
    const studentTab = document.getElementById('tab-student');
    const roleInput = document.getElementById('role-input');

    if (!teacherForm || !studentForm) return;

    if (role === 'teacher') {
        teacherForm.style.display = 'block';
        studentForm.style.display = 'none';
        teacherTab.classList.add('active');
        studentTab.classList.remove('active');
        if (roleInput) roleInput.value = 'teacher';
    } else {
        teacherForm.style.display = 'none';
        studentForm.style.display = 'block';
        studentTab.classList.remove('active');
        teacherTab.classList.remove('active');
        studentTab.classList.add('active');
        if (roleInput) roleInput.value = 'student';
    }
}

// Password visibility toggle
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}

// Form validation
function validateRegistration(formType) {
    if (formType === 'teacher') {
        const regNo = document.getElementById('teacher-reg-no').value.trim();
        if (regNo.length !== 6 || isNaN(regNo)) {
            alert('Teacher Registration Number must be exactly 6 digits.');
            return false;
        }
    } else {
        const regNo = document.getElementById('student-reg-no').value.trim();
        if (regNo.length !== 8 || isNaN(regNo)) {
            alert('Student Registration Number must be exactly 8 digits.');
            return false;
        }
    }
    return true;
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

// Date formatting helper
function formatDate(dateStr) {
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Search filter for tables
function filterTable(inputId, tableId) {
    const searchInput = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!searchInput || !table) return;

    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}
