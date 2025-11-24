/**
 * Pencil Booking Conversion Handler
 * Handles pre-filling the reservation form when converting from pencil booking
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we have pencil conversion data from the server
        const pencilData = window.BARCIE_GUEST && window.BARCIE_GUEST.pencilConversion;
        
        if (pencilData && pencilData.pencil_id) {
            console.log('Pencil booking conversion detected:', pencilData);
            
            // Switch to reservation form tab
            const reservationToggle = document.getElementById('reservationToggle');
            if (reservationToggle) {
                reservationToggle.checked = true;
                const changeEvent = new Event('change', { bubbles: true });
                reservationToggle.dispatchEvent(changeEvent);
            }
            
            // Show reservation form, hide pencil form
            const reservationForm = document.getElementById('reservationForm');
            const pencilForm = document.getElementById('pencilForm');
            
            if (reservationForm) {
                reservationForm.style.display = 'block';
            }
            if (pencilForm) {
                pencilForm.style.display = 'none';
            }
            
            // Pre-fill the reservation form fields
            setTimeout(function() {
                preFillReservationForm(pencilData);
                
                // Scroll to booking section
                const bookingSection = document.getElementById('booking') || document.querySelector('[name="booking"]');
                if (bookingSection) {
                    bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                
                // Show notification
                showConversionNotification();
            }, 500);
        }
    });
    
    // Function to pre-fill reservation form
    function preFillReservationForm(data) {
        console.log('Pre-filling reservation form with data:', data);
        
        const form = document.getElementById('reservationForm');
        if (!form) {
            console.error('Reservation form not found');
            return;
        }
        
        // Pre-fill each field
        const fieldMappings = {
            'room_id': data.room_id,
            'guest_name': data.guest_name,
            'contact_number': data.contact_number,
            'email': data.email,
            'checkin': data.checkin,
            'checkout': data.checkout,
            'occupants': data.occupants,
            'company': data.company,
            'company_contact': data.company_contact
        };
        
        Object.keys(fieldMappings).forEach(function(fieldName) {
            const field = form.querySelector('[name="' + fieldName + '"]');
            const value = fieldMappings[fieldName];
            
            if (field && value) {
                field.value = value;
                
                // Trigger change event for any listeners
                const changeEvent = new Event('change', { bubbles: true });
                field.dispatchEvent(changeEvent);
                
                // Add highlighting effect
                field.style.backgroundColor = '#fff3cd';
                setTimeout(function() {
                    field.style.transition = 'background-color 1s ease';
                    field.style.backgroundColor = '';
                }, 2000);
                
                console.log('Pre-filled field:', fieldName, '=', value);
            } else if (!field) {
                console.warn('Field not found:', fieldName);
            }
        });
        
        // Add a hidden field to track that this is a conversion from pencil booking
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = 'converted_from_pencil_id';
        hiddenField.value = data.pencil_id;
        form.appendChild(hiddenField);
        
        console.log('Form pre-fill complete');
    }
    
    // Function to show conversion notification
    function showConversionNotification() {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 9999;
            animation: slideInRight 0.5s ease-out;
            max-width: 400px;
        `;
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                <div>
                    <strong style="display: block; margin-bottom: 5px;">Pencil Booking Loaded!</strong>
                    <small>Your draft reservation details have been pre-filled. Please review and complete the booking process.</small>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; margin-left: 10px;">×</button>
            </div>
        `;
        
        // Add animation keyframes
        if (!document.getElementById('pencil-conversion-styles')) {
            const style = document.createElement('style');
            style.id = 'pencil-conversion-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        // Auto-remove after 8 seconds
        setTimeout(function() {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.5s ease-out';
                setTimeout(function() {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 500);
            }
        }, 8000);
    }
})();
