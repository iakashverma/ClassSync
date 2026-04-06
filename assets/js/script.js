// ClassSync - Main JavaScript
// sidebar toggle, countdown timer, form stuff

// Toggle sidebar on mobile
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

// close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('sidebar');
    var toggleBtn = document.getElementById('sidebarToggle');
    
    if (sidebar && toggleBtn) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// Highlight active nav link based on current page
document.addEventListener('DOMContentLoaded', function() {
    var currentPage = window.location.pathname;
    var navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        if (link.getAttribute('href') && currentPage.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});

// Countdown timer for deadlines
// call this with the deadline string and element id
function startCountdown(deadlineStr, elementId) {
    var el = document.getElementById(elementId);
    if (!el) return;

    var deadline = new Date(deadlineStr).getTime();

    var timer = setInterval(function() {
        var now = new Date().getTime();
        var diff = deadline - now;

        if (diff <= 0) {
            el.innerHTML = '<i class="fas fa-lock"></i> Time Up!';
            el.classList.add('urgent');
            clearInterval(timer);
            return;
        }

        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var secs = Math.floor((diff % (1000 * 60)) / 1000);

        var text = '';
        if (days > 0) text += days + 'd ';
        text += hours + 'h ' + mins + 'm ' + secs + 's';

        el.innerHTML = '<i class="fas fa-clock"></i> ' + text + ' left';

        // add urgent class if less than 1 hour
        if (diff < 3600000) {
            el.classList.add('urgent');
        }
    }, 1000);
}

// Quick deadline buttons for teacher
function setDeadline(type) {
    var deadlineInput = document.getElementById('deadline');
    if (!deadlineInput) return;

    var now = new Date();
    var target;

    if (type === '2days') {
        target = new Date(now.getTime() + 2 * 24 * 60 * 60 * 1000);
    } else if (type === '3days') {
        target = new Date(now.getTime() + 3 * 24 * 60 * 60 * 1000);
    } else if (type === 'nextMonday') {
        // find next monday
        target = new Date(now);
        var dayOfWeek = target.getDay();
        var daysUntilMonday = (8 - dayOfWeek) % 7;
        if (daysUntilMonday === 0) daysUntilMonday = 7; // if today is monday, go to next week
        target.setDate(target.getDate() + daysUntilMonday);
        target.setHours(12, 0, 0, 0); // noon
    }

    if (target) {
        // format as YYYY-MM-DDTHH:mm for datetime-local input
        var year = target.getFullYear();
        var month = String(target.getMonth() + 1).padStart(2, '0');
        var day = String(target.getDate()).padStart(2, '0');
        var hours = String(target.getHours()).padStart(2, '0');
        var minutes = String(target.getMinutes()).padStart(2, '0');
        
        deadlineInput.value = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    }
}

// File upload label update
function updateFileName(input) {
    var label = document.getElementById('file-label');
    if (label && input.files.length > 0) {
        label.textContent = input.files[0].name;
    }
}

// role tab switching on register page
function switchRole(role) {
    // update tab styles
    document.querySelectorAll('.role-tab').forEach(function(tab) {
        tab.classList.remove('active');
    });
    document.querySelector('[data-role="' + role + '"]').classList.add('active');

    // show/hide fields
    var teacherFields = document.getElementById('teacher-fields');
    var studentFields = document.getElementById('student-fields');
    var roleInput = document.getElementById('role-input');

    if (role === 'teacher') {
        teacherFields.style.display = 'block';
        studentFields.style.display = 'none';
    } else {
        teacherFields.style.display = 'none';
        studentFields.style.display = 'block';
    }
    roleInput.value = role;
}
