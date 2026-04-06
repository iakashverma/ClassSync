/**
 * ClassSync - Main JavaScript
 * Countdown timers, charts, AJAX helpers, modals, file upload
 */

// ============================================
// NAVBAR TOGGLE (Mobile)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('open');
        });

        // Close menu when clicking a link
        navLinks.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => navLinks.classList.remove('open'));
        });
    }

    // Auto-remove flash toasts after animation
    const flashToast = document.getElementById('flashToast');
    if (flashToast) {
        setTimeout(() => { if (flashToast.parentElement) flashToast.remove(); }, 5000);
    }

    // Initialize countdown timers
    initCountdowns();

    // Initialize charts if canvas exists
    initCharts();

    // Initialize drag-and-drop upload zones
    initUploadZones();
});

// ============================================
// COUNTDOWN TIMER
// ============================================
function initCountdowns() {
    const timers = document.querySelectorAll('[data-deadline]');
    timers.forEach(el => {
        updateCountdown(el);
        setInterval(() => updateCountdown(el), 1000);
    });
}

function updateCountdown(el) {
    const deadline = new Date(el.dataset.deadline).getTime();
    const now = Date.now();
    const diff = deadline - now;

    if (diff <= 0) {
        el.innerHTML = '⏰ Expired';
        el.className = 'countdown expired';
        // Disable related submit button if exists
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '🚫 Submission Closed';
        }
        // Show locked overlay if exists
        const uploadZone = document.querySelector('.upload-zone');
        if (uploadZone) {
            uploadZone.classList.add('disabled');
        }
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    let text = '⏱️ ';
    if (days > 0) text += days + 'd ';
    if (hours > 0 || days > 0) text += hours + 'h ';
    text += minutes + 'm ' + seconds + 's';

    el.innerHTML = text;

    // Urgent if less than 1 hour
    if (diff < 3600000) {
        el.className = 'countdown urgent';
    } else {
        el.className = 'countdown';
    }
}

// ============================================
// CHART.JS INITIALIZATION
// ============================================
function initCharts() {
    const progressCanvas = document.getElementById('progressChart');
    if (progressCanvas) {
        const submitted = parseInt(progressCanvas.dataset.submitted || 0);
        const missed = parseInt(progressCanvas.dataset.missed || 0);
        const pending = parseInt(progressCanvas.dataset.pending || 0);

        new Chart(progressCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Submitted', 'Missed', 'Pending'],
                datasets: [{
                    data: [submitted, missed, pending],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 6,
                    spacing: 3,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#8892b0',
                            font: { family: 'Inter', size: 12, weight: '500' },
                            padding: 16,
                            usePointStyle: true,
                            pointStyleWidth: 10
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
}

// ============================================
// FILE UPLOAD / DRAG & DROP
// ============================================
function initUploadZones() {
    const zones = document.querySelectorAll('.upload-zone');
    zones.forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        if (!input) return;

        zone.addEventListener('click', () => {
            if (!zone.classList.contains('disabled')) input.click();
        });

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            if (zone.classList.contains('disabled')) return;

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                handleFileSelected(input);
            }
        });

        input.addEventListener('change', () => handleFileSelected(input));
    });
}

function handleFileSelected(input) {
    const file = input.files[0];
    if (!file) return;

    const fileInfoEl = document.getElementById('fileInfo');
    const fileNameEl = document.getElementById('fileName');
    const fileSizeEl = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');

    // Validate extension
    const allowed = ['pdf', 'doc', 'docx'];
    const ext = file.name.split('.').pop().toLowerCase();
    if (!allowed.includes(ext)) {
        showToast('error', 'Only PDF, DOC, and DOCX files are allowed.');
        input.value = '';
        return;
    }

    // Validate size (10MB)
    if (file.size > 10 * 1024 * 1024) {
        showToast('error', 'File size must be less than 10MB.');
        input.value = '';
        return;
    }

    if (fileInfoEl) {
        fileInfoEl.style.display = 'flex';
        const icon = ext === 'pdf' ? '📄' : '📝';
        document.getElementById('fileIcon').textContent = icon;
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = formatFileSize(file.size);
    }

    if (submitBtn) {
        submitBtn.disabled = false;
    }
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(type, message) {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const icons = { success: '✅', error: '❌', info: 'ℹ️' };
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => { if (toast.parentElement) toast.remove(); }, 5000);
}

// ============================================
// MODALS
// ============================================
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => {
            m.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// ============================================
// QUICK DEADLINE BUTTONS
// ============================================
function setQuickDeadline(option) {
    const input = document.getElementById('deadlineInput');
    if (!input) return;

    const now = new Date();
    let deadline;

    switch (option) {
        case '+2days':
            deadline = new Date(now.getTime() + 2 * 24 * 60 * 60 * 1000);
            deadline.setHours(23, 59, 0, 0);
            break;
        case '+3days':
            deadline = new Date(now.getTime() + 3 * 24 * 60 * 60 * 1000);
            deadline.setHours(23, 59, 0, 0);
            break;
        case '+5days':
            deadline = new Date(now.getTime() + 5 * 24 * 60 * 60 * 1000);
            deadline.setHours(23, 59, 0, 0);
            break;
        case '+1week':
            deadline = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
            deadline.setHours(23, 59, 0, 0);
            break;
        case 'next_monday':
            deadline = new Date(now);
            const dayOfWeek = deadline.getDay();
            const daysUntilMonday = dayOfWeek === 0 ? 1 : (8 - dayOfWeek);
            deadline.setDate(deadline.getDate() + daysUntilMonday);
            deadline.setHours(12, 0, 0, 0);
            break;
        default:
            return;
    }

    // Format for datetime-local input: YYYY-MM-DDTHH:MM
    const year = deadline.getFullYear();
    const month = String(deadline.getMonth() + 1).padStart(2, '0');
    const day = String(deadline.getDate()).padStart(2, '0');
    const hours = String(deadline.getHours()).padStart(2, '0');
    const mins = String(deadline.getMinutes()).padStart(2, '0');

    input.value = `${year}-${month}-${day}T${hours}:${mins}`;

    // Visual feedback
    document.querySelectorAll('.quick-btn').forEach(b => b.style.borderColor = '');
    event.target.style.borderColor = 'var(--accent-primary)';
}

// ============================================
// EXTEND DEADLINE (AJAX)
// ============================================
function extendDeadline(classworkId) {
    const newDeadline = document.getElementById('extendDeadline_' + classworkId).value;

    if (!newDeadline) {
        showToast('error', 'Please select a new deadline.');
        return;
    }

    fetch(window.BASE_URL + '/api/extend_deadline.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            classwork_id: classworkId,
            new_deadline: newDeadline
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Deadline extended successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.error || 'Failed to extend deadline.');
        }
    })
    .catch(() => {
        showToast('error', 'Network error. Please try again.');
    });
}

// ============================================
// AJAX FILE UPLOAD
// ============================================
function uploadSubmission(classworkId) {
    const form = document.getElementById('uploadForm');
    const input = document.getElementById('fileInput');
    const progressBar = document.querySelector('.upload-progress');
    const progressFill = document.getElementById('uploadProgressFill');
    const submitBtn = document.getElementById('submitBtn');

    if (!input.files[0]) {
        showToast('error', 'Please select a file first.');
        return;
    }

    const formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('classwork_id', classworkId);

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Uploading...';
    if (progressBar) progressBar.classList.add('active');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.BASE_URL + '/api/upload.php');

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable && progressFill) {
            const pct = Math.round((e.loaded / e.total) * 100);
            progressFill.style.width = pct + '%';
        }
    };

    xhr.onload = function() {
        try {
            const data = JSON.parse(xhr.responseText);
            if (data.success) {
                showToast('success', 'Assignment submitted successfully! 🎉');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', data.error || 'Upload failed.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📤 Submit Assignment';
            }
        } catch(e) {
            showToast('error', 'Server error. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '📤 Submit Assignment';
        }
    };

    xhr.onerror = function() {
        showToast('error', 'Network error. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '📤 Submit Assignment';
    };

    xhr.send(formData);
}

// ============================================
// ROLE SELECT (REGISTRATION)
// ============================================
function selectRole(role) {
    document.querySelectorAll('.role-option').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    document.getElementById('roleInput').value = role;

    // Show/hide fields
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');

    if (studentFields) studentFields.style.display = role === 'student' ? 'block' : 'none';
    if (teacherFields) teacherFields.style.display = role === 'teacher' ? 'block' : 'none';
}

// ============================================
// CONFIRM DELETE
// ============================================
function confirmDelete(message, formId) {
    if (confirm(message || 'Are you sure you want to delete this?')) {
        document.getElementById(formId).submit();
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function formatFileSize(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
}

// Set BASE_URL for AJAX calls
window.BASE_URL = '/ClassSync';
