// Rooms Section JavaScript
// Functions for rooms functionality - called by dashboard-bootstrap.js

// Export functions immediately for global access
window.initializeRoomsFiltering = initializeRoomsFiltering;
window.initializeRoomsSearch = initializeRoomsSearch;
window.initializeEditForms = initializeEditForms;
window.hideAllEditForms = hideAllEditForms;
window.updateItemCounts = updateItemCounts;
window.updateVisibleCounts = updateVisibleCounts;

console.log('📄 rooms-section.js loaded');

function initializeRoomsFiltering() {
  // Type filter buttons
  const typeFilters = document.querySelectorAll('.type-filter');
  
  typeFilters.forEach(filter => {
    filter.addEventListener('change', function() {
      const filterValue = this.value;
      const items = document.querySelectorAll('.item-card');
      
      items.forEach(item => {
        const itemType = item.getAttribute('data-type');
        
        if (filterValue === 'all' || itemType === filterValue) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
      
      updateVisibleCounts();
    });
  });
}

function initializeRoomsSearch() {
  const searchInput = document.getElementById('searchItems');
  
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const items = document.querySelectorAll('.item-card');
      
      items.forEach(item => {
        const searchableText = item.getAttribute('data-searchable') || '';
        
        if (searchableText.includes(searchTerm)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
      
      updateVisibleCounts();
    });
  }
}

function initializeEditForms() {
  // Edit toggle buttons
  const editButtons = document.querySelectorAll('.edit-toggle-btn');
  const cancelButtons = document.querySelectorAll('.edit-cancel-btn');
  
  editButtons.forEach(button => {
    button.addEventListener('click', function() {
      const itemId = this.getAttribute('data-item-id');
      const editForm = document.getElementById(`editForm${itemId}`);
      
      if (editForm) {
        // Hide all other edit forms first
        hideAllEditForms();
        
        // Toggle this edit form
        if (editForm.style.display === 'none' || !editForm.style.display) {
          editForm.style.display = 'block';
          this.innerHTML = '<i class="fas fa-times me-1"></i>Cancel';
          this.classList.remove('btn-outline-primary');
          this.classList.add('btn-outline-secondary');
        } else {
          editForm.style.display = 'none';
          this.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
          this.classList.remove('btn-outline-secondary');
          this.classList.add('btn-outline-primary');
        }
      }
    });
  });
  
  cancelButtons.forEach(button => {
    button.addEventListener('click', function() {
      const itemId = this.getAttribute('data-item-id');
      const editForm = document.getElementById(`editForm${itemId}`);
      const editButton = document.querySelector(`.edit-toggle-btn[data-item-id="${itemId}"]`);
      
      if (editForm) {
        editForm.style.display = 'none';
      }
      
      if (editButton) {
        editButton.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
        editButton.classList.remove('btn-outline-secondary');
        editButton.classList.add('btn-outline-primary');
      }
    });
  });
}

function hideAllEditForms() {
  const editForms = document.querySelectorAll('[id^="editForm"]');
  const editButtons = document.querySelectorAll('.edit-toggle-btn');
  
  editForms.forEach(form => {
    form.style.display = 'none';
  });
  
  editButtons.forEach(button => {
    button.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-outline-primary');
  });
}

function updateItemCounts() {
  const allItems = document.querySelectorAll('.item-card');
  const roomItems = document.querySelectorAll('.item-card[data-type="room"]');
  const facilityItems = document.querySelectorAll('.item-card[data-type="facility"]');
  
  // Update count badges
  const countAll = document.getElementById('count-all') || document.querySelector('[data-type="all"] .badge');
  const countRooms = document.getElementById('count-rooms') || document.querySelector('[data-type="room"] .badge');
  const countFacilities = document.getElementById('count-facilities') || document.querySelector('[data-type="facility"] .badge');
  
  if (countAll) countAll.textContent = allItems.length;
  if (countRooms) countRooms.textContent = roomItems.length;
  if (countFacilities) countFacilities.textContent = facilityItems.length;
}

function updateVisibleCounts() {
  const visibleItems = document.querySelectorAll('.item-card[style*="block"], .item-card:not([style*="none"])');
  const visibleRooms = document.querySelectorAll('.item-card[data-type="room"]:not([style*="none"])');
  const visibleFacilities = document.querySelectorAll('.item-card[data-type="facility"]:not([style*="none"])');
  
  // Update count badges with visible items
  const countAll = document.getElementById('count-all') || document.querySelector('[data-type="all"] .badge');
  const countRooms = document.getElementById('count-rooms') || document.querySelector('[data-type="room"] .badge');
  const countFacilities = document.getElementById('count-facilities') || document.querySelector('[data-type="facility"] .badge');
  
  if (countAll) countAll.textContent = visibleItems.length;
  if (countRooms) countRooms.textContent = visibleRooms.length;
  if (countFacilities) countFacilities.textContent = visibleFacilities.length;
}

// Form validation and enhancement
function validateItemForm(form) {
  const requiredFields = form.querySelectorAll('[required]');
  let isValid = true;
  
  requiredFields.forEach(field => {
    if (!field.value.trim()) {
      field.classList.add('is-invalid');
      isValid = false;
    } else {
      field.classList.remove('is-invalid');
    }
  });
  
  return isValid;
}

// Handle form submissions
function handleItemFormSubmit(event) {
  const form = event.target;
  
  if (!validateItemForm(form)) {
    event.preventDefault();
    showAdminAlert('Please fill in all required fields.', 'warning');
    return false;
  }
  
  // Show loading state
  const submitButton = form.querySelector('button[type="submit"]');
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
  }
  
  return true;
}

// Add event listeners to all item forms
document.addEventListener('DOMContentLoaded', function() {
  const itemForms = document.querySelectorAll('form[method="POST"]');
  
  itemForms.forEach(form => {
    form.addEventListener('submit', handleItemFormSubmit);
  });
});

// Image preview functionality
function setupImagePreview() {
  const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
  
  imageInputs.forEach(input => {
    input.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          // Create or update preview image
          let preview = input.parentNode.querySelector('.image-preview');
          if (!preview) {
            preview = document.createElement('img');
            preview.className = 'image-preview mt-2';
            preview.style.maxWidth = '200px';
            preview.style.maxHeight = '150px';
            preview.style.objectFit = 'cover';
            input.parentNode.appendChild(preview);
          }
          preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  });
}

// Initialize image preview when document is ready
document.addEventListener('DOMContentLoaded', setupImagePreview);

// Inline admin alert helper for rooms section (fallback if a global helper isn't available)
function showAdminAlert(message, type = 'danger', duration = 6000) {
  try {
    // If a global helper exists (from bookings-section.js), reuse it
    if (typeof window.showAdminAlert === 'function' && window.showAdminAlert !== showAdminAlert) {
      return window.showAdminAlert(message, type, duration);
    }
  } catch (e) { /* ignore */ }

  let container = document.getElementById('admin_discount_alert');
  if (!container) {
    container = document.createElement('div');
    container.id = 'admin_discount_alert';
    container.style.position = 'fixed';
    container.style.top = '1rem';
    container.style.right = '1rem';
    container.style.zIndex = 1080;
    document.body.appendChild(container);
  }

  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.role = 'alert';
  alertDiv.style.minWidth = '260px';
  alertDiv.innerHTML = `
    <div style="font-size:0.95rem;">${message}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  container.appendChild(alertDiv);

  if (duration > 0) {
    setTimeout(() => {
      try { bootstrap && bootstrap.Alert && bootstrap.Alert.getOrCreateInstance(alertDiv).close(); } catch (e) { alertDiv.remove(); }
    }, duration);
  }
}

// Export functions for global access
window.hideAllEditForms = hideAllEditForms;
window.updateItemCounts = updateItemCounts;
window.updateVisibleCounts = updateVisibleCounts;