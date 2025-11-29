// Admin Panel JavaScript

// Mobile Menu Toggle
const menuToggle = document.getElementById('menuToggle');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.add('active');
    });
}

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.remove('active');
    });
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Auto-hide alerts
const alerts = document.querySelectorAll('.alert');
alerts.forEach(alert => {
    setTimeout(() => {
        alert.style.animation = 'slideInRight 0.3s ease reverse';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }, 5000);
});

// Confirm before delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Image preview for file inputs
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; border-radius: 8px;">`;
        }
        
        reader.readAsDataURL(file);
    }
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#ef4444';
            isValid = false;
        } else {
            field.style.borderColor = '#e2e8f0';
        }
    });
    
    return isValid;
}

// Character counter for textareas
function setupCharacterCounter(textareaId, counterId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    
    if (textarea && counter) {
        textarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            counter.textContent = `${remaining} characters remaining`;
            
            if (remaining < 0) {
                counter.style.color = '#ef4444';
            } else if (remaining < 50) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = '#64748b';
            }
        });
    }
}

// Table row actions
document.addEventListener('DOMContentLoaded', function() {
    // Add click events to table rows if they have data-href attribute
    const tableRows = document.querySelectorAll('tr[data-href]');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (!e.target.closest('a') && !e.target.closest('button')) {
                window.location.href = this.dataset.href;
            }
        });
    });
});

// Search functionality
function filterTable(tableId, searchId) {
    const search = document.getElementById(searchId);
    const table = document.getElementById(tableId);
    
    if (search && table) {
        search.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('MotivLab Admin Panel Loaded');
    
    // Initialize character counters
    setupCharacterCounter('excerpt', 'excerpt-counter', 160);
    setupCharacterCounter('content', 'content-counter', 5000);
    
    // Initialize table search
    filterTable('portfolioTable', 'portfolioSearch');
    filterTable('blogTable', 'blogSearch');
    filterTable('messagesTable', 'messagesSearch');
});