// AquaBill - Water Billing System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Initialize sidebar
    initSidebar();
    
    // Initialize notifications
    initNotifications();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize delete confirmations
    initDeleteConfirm();
    
    // Initialize modal
    initModal();
});

// ==================== Theme ====================
let themeHandlerAttached = false;

function initTheme() {
    const themeToggle = document.querySelector('.theme-toggle');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcons(savedTheme);
    
    if (themeToggle && !themeHandlerAttached) {
        themeToggle.addEventListener('click', handleThemeToggle);
        themeHandlerAttached = true;
    }
}

function handleThemeToggle() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcons(newTheme);
}

function updateThemeIcons(theme) {
    const sunIcon = document.querySelector('.theme-toggle .sun');
    const moonIcon = document.querySelector('.theme-toggle .moon');
    
    if (sunIcon && moonIcon) {
        if (theme === 'dark') {
            sunIcon.style.opacity = '0.3';
            moonIcon.style.opacity = '1';
        } else {
            sunIcon.style.opacity = '1';
            moonIcon.style.opacity = '0.3';
        }
    }
}

function updateChartsTheme() {
    // Charts read CSS data-theme attribute automatically via CSS vars
    // No action needed as Chart.js respects CSS changes
}

// ==================== Sidebar ====================
function initSidebar() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const closeSidebar = document.querySelector('.close-sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
        });
    }
    
    if (closeSidebar && sidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    }
}

// ==================== Notifications ====================
function initNotifications() {
    const notification = document.getElementById('notification');
    
    if (notification) {
        // Auto close after 5 seconds
        setTimeout(function() {
            closeNotification();
        }, 5000);
    }
}

function closeNotification() {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }
}

// Add slideOut animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ==================== Form Validation ====================
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    
    // Phone validation
    if (field.type === 'tel' && value) {
        const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number';
        }
    }
    
    // Password validation
    if (field.type === 'password' && value) {
        if (field.id === 'confirm_password' || field.name === 'confirm_password') {
            const password = document.getElementById('password') || document.querySelector('input[name="password"]');
            if (password && value !== password.value) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
        } else if (value.length < 6) {
            isValid = false;
            errorMessage = 'Password must be at least 6 characters';
        }
    }
    
    // Number validation
    if (field.type === 'number' && value) {
        const min = parseFloat(field.min);
        const max = parseFloat(field.max);
        const numValue = parseFloat(value);
        
        if (!isNaN(min) && numValue < min) {
            isValid = false;
            errorMessage = `Minimum value is ${min}`;
        }
        
        if (!isNaN(max) && numValue > max) {
            isValid = false;
            errorMessage = `Maximum value is ${max}`;
        }
    }
    
    // Update UI
    const formGroup = field.closest('.form-group');
    const existingError = formGroup ? formGroup.querySelector('.form-error') : null;
    
    if (!isValid) {
        field.classList.add('error');
        field.classList.remove('valid');
        
        if (formGroup && !existingError) {
            const errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            errorElement.textContent = errorMessage;
            formGroup.appendChild(errorElement);
        } else if (existingError) {
            existingError.textContent = errorMessage;
        }
    } else {
        field.classList.remove('error');
        field.classList.add('valid');
        
        if (existingError) {
            existingError.remove();
        }
    }
    
    return isValid;
}

// ==================== Delete Confirmation ====================
function initDeleteConfirm() {
    const deleteButtons = document.querySelectorAll('.btn-delete, .delete-btn');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ==================== Modal ====================
function initModal() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloseButtons = document.querySelectorAll('.modal-close, .modal-overlay');
    
    modalTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    modalCloseButtons.forEach(function(closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeModal();
        });
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay.active');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// ==================== Utility Functions ====================

// Format currency
function formatCurrency(amount) {
    return 'KSh ' + parseFloat(amount).toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Show loading spinner
function showLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading-overlay';
    loading.id = 'loading-overlay';
    loading.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loading);
}

// Hide loading spinner
function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const container = document.querySelector('.notification-container') || createNotificationContainer();
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(notification);
    
    // Auto remove
    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 5000);
}

function createNotificationContainer() {
    const container = document.createElement('div');
    container.className = 'notification-container';
    document.body.appendChild(container);
    return container;
}

// Calculate bill amount
function calculateBillAmount(consumption) {
const meterCharge = 50.00; // KSh 50 meter charge
    let total = 0;
    
    if (consumption <= 10) {
        total = consumption * 2.50;
    } else if (consumption <= 30) {
        total = (10 * 2.50) + ((consumption - 10) * 3.00);
    } else if (consumption <= 50) {
        total = (10 * 2.50) + (20 * 3.00) + ((consumption - 30) * 3.50);
    } else {
        total = (10 * 2.50) + (20 * 3.00) + (20 * 3.50) + ((consumption - 50) * 4.00);
    }
    
    return (total + meterCharge).toFixed(2);
}

// Update bill preview on consumption input change
function updateBillPreview(consumptionInput, previewElement) {
    if (consumptionInput && previewElement) {
        consumptionInput.addEventListener('input', function() {
            const consumption = parseInt(this.value) || 0;
            const amount = calculateBillAmount(consumption);
            previewElement.textContent = formatCurrency(amount);
        });
    }
}

// Search functionality
function initSearch(inputId, tableId) {
    const searchInput = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

// Table sorting
function initTableSort(tableId) {
    const table = document.getElementById(tableId);
    
    if (table) {
        const headers = table.querySelectorAll('th');
        
        headers.forEach(function(header, index) {
            if (header.dataset.sortable) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    sortTable(tableId, index);
                });
            }
        });
    }
}

function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isAscending = table.dataset.sortOrder !== 'asc';
    table.dataset.sortOrder = isAscending ? 'asc' : 'desc';
    
    rows.sort(function(a, b) {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Check if numeric
        const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        return isAscending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
    });
    
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
}

// Export table to CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = Array.from(table.querySelectorAll('tr'));
    
    const csvContent = rows.map(function(row) {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(function(cell) {
            return cell.textContent.trim().replace(/"/g, '""');
        }).join(',');
    }).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename || 'export.csv';
    link.click();
}

// Print functionality
function printElement(elementId) {
    const element = document.getElementById(elementId);
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <link rel="stylesheet" href="../css/style.css">
            <style>
                body { padding: 20px; }
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Payment form handling
function initPaymentForm() {
    const paymentForm = document.getElementById('payment-form');
    
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show processing
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
            submitBtn.disabled = true;
            
            // Simulate payment processing
            setTimeout(function() {
                // Show success
                showNotification('Payment processed successfully!', 'success');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Redirect or refresh
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }, 2000);
        });
    }
}

// Initialize payment form when DOM is ready
document.addEventListener('DOMContentLoaded', initPaymentForm);



