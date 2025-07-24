// Main JavaScript file for Computer Store

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Confirm dialogs for delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            const message = button.getAttribute('data-confirm');
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Cart quantity validation
    const quantityInputs = document.querySelectorAll('input[name="quantity"]');
    quantityInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const min = parseInt(input.getAttribute('min')) || 1;
            const max = parseInt(input.getAttribute('max')) || Infinity;
            const value = parseInt(input.value);
            
            if (value < min) {
                input.value = min;
            } else if (value > max) {
                input.value = max;
                alert('Maximum quantity available: ' + max);
            }
        });
    });

    // Search functionality enhancement
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.closest('form').submit();
            }
        });
    }

    // Price formatting for admin forms
    const priceInputs = document.querySelectorAll('input[name="price"]');
    priceInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    });

    // Bootstrap tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Bootstrap modal enhancements
    const modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            // Clear form data when modal is closed
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
            }
        });
    });

    // Loading state for buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const form = button.closest('form');
            if (form && form.checkValidity()) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                
                // Re-enable button after 5 seconds as fallback
                setTimeout(function() {
                    button.disabled = false;
                    button.innerHTML = button.getAttribute('data-original-text') || 'Submit';
                }, 5000);
            }
        });
    });

    // Save original button text
    submitButtons.forEach(function(button) {
        button.setAttribute('data-original-text', button.innerHTML);
    });

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                event.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Image lazy loading fallback
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(function(img) {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without IntersectionObserver
        images.forEach(function(img) {
            img.src = img.dataset.src;
        });
    }

    // Update cart count in navbar (if using AJAX)
    function updateCartCount() {
        // This would be used with AJAX implementation
        // For now, page refresh handles cart count updates
    }

    // Form field focus enhancement
    const formFields = document.querySelectorAll('.form-control, .form-select');
    formFields.forEach(function(field) {
        field.addEventListener('focus', function() {
            this.parentElement.classList.add('field-focused');
        });
        
        field.addEventListener('blur', function() {
            this.parentElement.classList.remove('field-focused');
        });
    });
});

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            alertDiv.style.transition = 'opacity 0.5s';
            alertDiv.style.opacity = '0';
            setTimeout(function() {
                alertDiv.remove();
            }, 500);
        }, 5000);
    }
}

// Error handling for images
document.addEventListener('error', function(event) {
    if (event.target.tagName === 'IMG') {
        event.target.src = 'https://via.placeholder.com/300x200?text=Image+Not+Available';
        event.target.alt = 'Image not available';
    }
}, true);