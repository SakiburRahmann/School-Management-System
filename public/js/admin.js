// Admin Panel JavaScript

// Modern Delete Confirmation Modal
function confirmDelete(message, deleteUrl) {
    // If no URL provided, this is being called with onclick return - use legacy mode
    if (!deleteUrl) {
        return showDeleteModal(message);
    }

    showDeleteModal(message, deleteUrl);
    return false; // Prevent default link action
}

function showDeleteModal(message, deleteUrl) {
    // Remove any existing modal
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal backdrop and content
    const modal = document.createElement('div');
    modal.id = 'deleteConfirmModal';
    modal.className = 'confirm-modal-overlay';

    modal.innerHTML = `
        <div class="confirm-modal">
            <div class="confirm-modal-icon">
                <div class="confirm-modal-icon-circle">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="confirm-modal-icon-pulse"></div>
            </div>
            <h3 class="confirm-modal-title">Confirm Delete</h3>
            <p class="confirm-modal-message">${message || 'Are you sure you want to delete this item?'}</p>
            <p class="confirm-modal-warning">
                <i class="fas fa-exclamation-triangle"></i>
                This action cannot be undone!
            </p>
            <div class="confirm-modal-actions">
                <button type="button" class="confirm-modal-btn confirm-modal-btn-cancel" id="cancelDeleteBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="confirm-modal-btn confirm-modal-btn-delete" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Trigger animation
    requestAnimationFrame(() => {
        modal.classList.add('active');
    });

    return new Promise((resolve) => {
        const cancelBtn = document.getElementById('cancelDeleteBtn');
        const confirmBtn = document.getElementById('confirmDeleteBtn');

        function closeModal(confirmed) {
            modal.classList.remove('active');
            modal.classList.add('closing');

            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
                resolve(confirmed);
            }, 300);
        }

        cancelBtn.addEventListener('click', () => closeModal(false));
        confirmBtn.addEventListener('click', () => {
            if (deleteUrl) {
                // Redirect to delete URL
                window.location.href = deleteUrl;
            }
            closeModal(true);
        });

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(false);
            }
        });

        // Close on Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                closeModal(false);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    });
}

// Delete button click handler - attach to delete buttons
function initDeleteButtons() {
    document.querySelectorAll('.delete-btn, [data-delete-url]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('data-delete-url') || this.getAttribute('href');
            const message = this.getAttribute('data-delete-message') || 'Are you sure you want to delete this item?';
            showDeleteModal(message, url);
        });
    });
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Initialize delete buttons
    initDeleteButtons();
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger)';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });

    return isValid;
}

// Search functionality
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (!input || !table) return;

    input.addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
}

// Toggle sidebar on mobile
// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    sidebar.classList.toggle('active');
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

// Toast Notification System
function showToast(message, type = 'info', duration = 4000) {
    // Create toast container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    // Get icon based on type
    const icons = {
        success: 'fa-check-circle',
        danger: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    const icon = icons[type] || icons.info;

    // Build toast HTML
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icon}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Add to container
    container.appendChild(toast);

    // Auto-dismiss after duration
    setTimeout(() => {
        toast.classList.add('hiding');
        setTimeout(() => {
            toast.remove();
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        }, 300);
    }, duration);
}

