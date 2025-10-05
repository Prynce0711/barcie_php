/**
 * Simplified Guest Portal JavaScript
 * Provides basic interactive features without animations
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeBasicFeatures();
});

function initializeBasicFeatures() {
    addMobileMenuToggle();
    enhanceFormInteractions();
    addKeyboardNavigation();
}

// Mobile Menu Toggle
function addMobileMenuToggle() {
    // Create mobile menu button if it doesn't exist
    if (!document.querySelector('.mobile-menu-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'mobile-menu-toggle';
        toggleBtn.innerHTML = '☰';
        toggleBtn.setAttribute('aria-label', 'Toggle navigation menu');
        document.body.appendChild(toggleBtn);
        
        const sidebar = document.querySelector('.sidebar-guest');
        
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            toggleBtn.innerHTML = sidebar.classList.contains('open') ? '✕' : '☰';
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                toggleBtn.innerHTML = '☰';
            }
        });
    }
}

// Basic Form Interactions
function enhanceFormInteractions() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Enhanced input validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', validateInput);
            input.addEventListener('input', clearValidationState);
        });
    });
}

function validateInput(e) {
    const input = e.target;
    const isValid = input.checkValidity();
    
    input.classList.remove('form-success', 'form-error');
    
    if (input.value) {
        input.classList.add(isValid ? 'form-success' : 'form-error');
    }
}

function clearValidationState(e) {
    const input = e.target;
    input.classList.remove('form-success', 'form-error');
}

// Keyboard Navigation
function addKeyboardNavigation() {
    // Add keyboard support for sidebar navigation
    const sidebarButtons = document.querySelectorAll('.sidebar-guest button, .sidebar-guest a');
    
    sidebarButtons.forEach((button, index) => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextButton = sidebarButtons[index + 1];
                if (nextButton) {
                    nextButton.focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevButton = sidebarButtons[index - 1];
                if (prevButton) {
                    prevButton.focus();
                }
            }
        });
    });
    
    // Add escape key to close mobile menu
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.querySelector('.sidebar-guest');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            if (sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                if (toggleBtn) {
                    toggleBtn.innerHTML = '☰';
                }
            }
        }
    });
}

// Auto-generate receipt numbers
function generateReceiptNumber() {
    const receiptInput = document.getElementById('receipt_no');
    if (receiptInput && !receiptInput.value) {
        const timestamp = Date.now();
        const random = Math.floor(Math.random() * 1000);
        receiptInput.value = `RC-${timestamp}-${random}`;
    }
}

// Initialize receipt number generation
document.addEventListener('DOMContentLoaded', function() {
    const bookingForms = document.querySelectorAll('#reservationForm, #pencilForm');
    bookingForms.forEach(form => {
        form.addEventListener('focusin', generateReceiptNumber);
    });
});

// Enhanced section switching without animations
function showSection(sectionId, button) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
    });
    
    // Show selected section
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
    }
    
    // Update navigation
    document.querySelectorAll('.sidebar-guest button, .sidebar-guest a').forEach(btn => {
        btn.classList.remove('active');
    });
    if (button) {
        button.classList.add('active');
    }
    
    // Close mobile menu if open
    const sidebar = document.querySelector('.sidebar-guest');
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        if (toggleBtn) {
            toggleBtn.innerHTML = '☰';
        }
    }
}

// Export functions for global use
window.showSection = showSection;