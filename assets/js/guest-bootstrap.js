// Complete Guest Portal JavaScript with Bootstrap Integration
// Consolidated from guest.js, guest-enhanced.js, and inline scripts

document.addEventListener("DOMContentLoaded", function () {
  initializeGuestPortal();
});

function initializeGuestPortal() {
  setupBootstrapComponents();
  setupMobileMenu();
  enhanceForms();
  setupSectionNavigation();
  setupBookingForms();
  setupCardFiltering();
  initializeReceiptGeneration();
  loadItems();
  addKeyboardNavigation();
  setupInteractiveOverview();
  // initializeChatSystem(); // Temporarily disabled to fix feedback system
  initializeStarRating();
  initializeGuestCalendar();
}

// Initialize Bootstrap Components
function setupBootstrapComponents() {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize popovers
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Initialize modals
  var modalList = [].slice.call(document.querySelectorAll(".modal"));
  modalList.map(function (modalEl) {
    return new bootstrap.Modal(modalEl);
  });
}

// Mobile Menu Setup
function setupMobileMenu() {
  const toggleBtn = document.querySelector(".mobile-menu-toggle");
  const sidebar = document.querySelector(".sidebar-guest");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("open");

      // Update icon
      const icon = this.querySelector("i");
      if (sidebar.classList.contains("open")) {
        icon.className = "fas fa-times";
      } else {
        icon.className = "fas fa-bars";
      }
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", function (e) {
      if (
        window.innerWidth <= 992 &&
        !sidebar.contains(e.target) &&
        !toggleBtn.contains(e.target) &&
        sidebar.classList.contains("open")
      ) {
        sidebar.classList.remove("open");
        toggleBtn.querySelector("i").className = "fas fa-bars";
      }
    });
  }
}

// Section Navigation (from inline script)
function showSection(sectionId, button) {
  // Hide all sections
  document
    .querySelectorAll(".content-section")
    .forEach((sec) => sec.classList.remove("active"));

  // Show target section
  const section = document.getElementById(sectionId);
  if (section) {
    section.classList.add("active");
    // Add smooth animation
    section.style.animation = "fadeInUp 0.4s ease";
  }

  // Update navigation buttons
  document
    .querySelectorAll(".sidebar-guest button")
    .forEach((btn) => btn.classList.remove("active"));
  if (button) {
    button.classList.add("active");
  }

  // Close mobile menu if open
  const sidebar = document.querySelector(".sidebar-guest");
  const toggleBtn = document.querySelector(".mobile-menu-toggle");
  if (
    window.innerWidth <= 992 &&
    sidebar &&
    sidebar.classList.contains("open")
  ) {
    sidebar.classList.remove("open");
    if (toggleBtn) {
      toggleBtn.querySelector("i").className = "fas fa-bars";
    }
  }
}

// Setup Section Navigation
function setupSectionNavigation() {
  // Show overview section by default
  setTimeout(() => {
    showSection("overview", document.querySelector(".sidebar-guest button"));
  }, 100);
}

// Booking Form Toggle (from guest.js)
function toggleBookingForm() {
  const type = document.querySelector(
    'input[name="bookingType"]:checked'
  ).value;
  document.getElementById("reservationForm").style.display =
    type === "reservation" ? "block" : "none";
  document.getElementById("pencilForm").style.display =
    type === "pencil" ? "block" : "none";

  // Generate new receipt number when switching to reservation form
  if (type === "reservation") {
    generateReceiptNumber();
  }
}

// Setup Booking Forms
function setupBookingForms() {
  const bookingTypeInputs = document.querySelectorAll(
    'input[name="bookingType"]'
  );

  bookingTypeInputs.forEach((input) => {
    input.addEventListener("change", function () {
      toggleBookingForm();
    });
  });
}

// Receipt Number Generation (from guest.js)
async function generateReceiptNumber() {
  try {
    const response = await fetch(
      "database/user_auth.php?action=get_receipt_no"
    );
    const data = await response.json();

    if (data.success) {
      const receiptField = document.getElementById("receipt_no");
      if (receiptField) {
        receiptField.value = data.receipt_no;
        receiptField.classList.add("is-valid");
      }
      console.log("Generated receipt number:", data.receipt_no);
    } else {
      console.error("Error generating receipt number:", data.error);
      generateFallbackReceiptNumber();
    }
  } catch (error) {
    console.error("Network error:", error);
    generateFallbackReceiptNumber();
  }
}

// Fallback receipt number generator (from guest.js)
function generateFallbackReceiptNumber() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");

  const receiptNo = `BARCIE-${year}${month}${day}-${hours}${minutes}${seconds}`;

  const receiptField = document.getElementById("receipt_no");
  if (receiptField) {
    receiptField.value = receiptNo;
    receiptField.classList.add("is-valid");
  }
}

// Initialize receipt number when page loads
function initializeReceiptGeneration() {
  generateReceiptNumber();
}

// Card Filtering (enhanced for overview integration)
function setupCardFiltering() {
  const radios = document.querySelectorAll('input[name="type"]');

  radios.forEach((radio) => {
    radio.addEventListener("change", () => {
      filterItems();
      syncOverviewWithRooms();
    });
  });
}

function filterItems() {
  const selectedType =
    document.querySelector('input[name="type"]:checked')?.value || "room";
  document.querySelectorAll("#cards-grid .card").forEach((card) => {
    if (card.dataset.type === selectedType) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

// Sync overview filters with rooms section
function syncOverviewWithRooms() {
  const selectedType =
    document.querySelector('input[name="type"]:checked')?.value || "room";
  const typeFilter = document.getElementById("typeFilter");

  if (typeFilter) {
    typeFilter.value =
      selectedType.charAt(0).toUpperCase() + selectedType.slice(1);
    applyOverviewFilters();
  }
}

// Load Items (from inline script) - Enhanced for overview integration
async function loadItems() {
  try {
    const res = await fetch("database/fetch_items.php");
    const items = await res.json();

    // Store items globally for filtering
    window.allItems = items;

    const container = document.getElementById("cards-grid");

    if (container) {
      container.innerHTML = "";
      items.forEach((item) => {
        const card = document.createElement("div");
        card.classList.add("card");
        card.dataset.type = item.item_type;
        card.dataset.price = item.price;
        card.dataset.availability =
          Math.random() > 0.3 ? "available" : "occupied"; // Random availability
        card.innerHTML = `
                    ${
                      item.image
                        ? `<img src="${item.image}" style="width:100%;height:150px;object-fit:cover;">`
                        : ""
                    }
                    <h3>${item.name}</h3>
                    ${
                      item.room_number
                        ? `<p>Room Number: ${item.room_number}</p>`
                        : ""
                    }
                    <p>Capacity: ${item.capacity} ${
          item.item_type === "room" ? "persons" : "people"
        }</p>
                    <p>Price: P${item.price}${
          item.item_type === "room" ? "/night" : "/day"
        }</p>
                    <p>${item.description}</p>
                    <div class="card-actions">
                        <button class="btn btn-primary btn-sm view-details-btn" data-item-id="${
                          item.id
                        }">View Details</button>
                        <button class="btn btn-success btn-sm book-now-btn" data-item-id="${
                          item.id
                        }">Book Now</button>
                    </div>
                `;
        container.appendChild(card);
      });
      
      // Add event handlers for View Details and Book Now buttons
      setupItemButtons();
      filterItems();
    }
  } catch (error) {
    console.error("Error loading items:", error);
  }
}

// Setup event handlers for item buttons
function setupItemButtons() {
  console.log('Setting up item buttons...');
  
  // View Details buttons
  const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
  console.log('Found view details buttons:', viewDetailsBtns.length);
  
  viewDetailsBtns.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const { itemId } = this.dataset;
      console.log('View details clicked for item:', itemId);
      showItemDetails(itemId);
    });
  });
  
  // Book Now buttons
  const bookNowBtns = document.querySelectorAll('.book-now-btn');
  console.log('Found book now buttons:', bookNowBtns.length);
  
  bookNowBtns.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const { itemId } = this.dataset;
      console.log('Book now clicked for item:', itemId);
      redirectToBooking(itemId);
    });
  });
  
  console.log('Item buttons setup complete');
}

// Show item details in modal
function showItemDetails(itemId) {
  const item = window.allItems.find(item => item.id == itemId);
  
  if (!item) {
    showToast('Item details not found', 'error');
    return;
  }
  
  // Create or update modal
  let modal = document.getElementById('itemDetailsModal');
  if (!modal) {
    modal = createItemDetailsModal();
  }
  
  // Populate modal with item details
  populateItemModal(modal, item);
  
  // Show modal
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();
}

// Create item details modal
function createItemDetailsModal() {
  const modalHtml = `
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="itemDetailsModalLabel">Room/Facility Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="itemDetailsBody">
            <!-- Content will be populated dynamically -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" id="modalBookNowBtn">Book Now</button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHtml);
  return document.getElementById('itemDetailsModal');
}

// Populate modal with item details
function populateItemModal(modal, item) {
  const modalBody = modal.querySelector('#itemDetailsBody');
  const modalTitle = modal.querySelector('#itemDetailsModalLabel');
  const bookNowBtn = modal.querySelector('#modalBookNowBtn');
  
  modalTitle.textContent = `${item.name} - Details`;
  
  const detailsHtml = `
    <div class="row">
      <div class="col-md-6">
        ${item.image ? `
          <img src="${item.image}" class="img-fluid rounded mb-3" alt="${item.name}" style="max-height: 300px; width: 100%; object-fit: cover;">
        ` : `
          <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
            <i class="fas ${item.item_type === 'room' ? 'fa-bed' : 'fa-building'} fa-3x text-muted"></i>
          </div>
        `}
      </div>
      <div class="col-md-6">
        <h4 class="mb-3">${item.name}</h4>
        
        <div class="detail-item mb-2">
          <strong><i class="fas fa-tag me-2"></i>Type:</strong>
          <span class="badge bg-primary ms-2">${item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1)}</span>
        </div>
        
        ${item.room_number ? `
          <div class="detail-item mb-2">
            <strong><i class="fas fa-door-open me-2"></i>Room Number:</strong>
            <span class="ms-2">${item.room_number}</span>
          </div>
        ` : ''}
        
        <div class="detail-item mb-2">
          <strong><i class="fas fa-users me-2"></i>Capacity:</strong>
          <span class="ms-2">${item.capacity} ${item.item_type === 'room' ? 'persons' : 'people'}</span>
        </div>
        
        <div class="detail-item mb-3">
          <strong><i class="fas fa-peso-sign me-2"></i>Price:</strong>
          <span class="ms-2 text-success fw-bold">₱${parseInt(item.price).toLocaleString()}${item.item_type === 'room' ? '/night' : '/day'}</span>
        </div>
        
        <div class="availability-status mb-3">
          <strong><i class="fas fa-calendar-check me-2"></i>Availability:</strong>
          <span class="badge bg-success ms-2">Available for booking</span>
        </div>
      </div>
    </div>
    
    <div class="row mt-3">
      <div class="col-12">
        <h5><i class="fas fa-info-circle me-2"></i>Description</h5>
        <p class="text-muted">${item.description || 'Comfortable accommodation with modern amenities and excellent service.'}</p>
      </div>
    </div>
    
    <div class="row mt-3">
      <div class="col-12">
        <h5><i class="fas fa-star me-2"></i>Features & Amenities</h5>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-unstyled">
              ${item.item_type === 'room' ? `
                <li><i class="fas fa-wifi text-success me-2"></i>Free WiFi</li>
                <li><i class="fas fa-snowflake text-success me-2"></i>Air Conditioning</li>
                <li><i class="fas fa-tv text-success me-2"></i>Cable TV</li>
              ` : `
                <li><i class="fas fa-utensils text-success me-2"></i>Event Catering Available</li>
                <li><i class="fas fa-microphone text-success me-2"></i>Audio/Visual Equipment</li>
                <li><i class="fas fa-parking text-success me-2"></i>Parking Space</li>
              `}
            </ul>
          </div>
          <div class="col-md-6">
            <ul class="list-unstyled">
              <li><i class="fas fa-broom text-success me-2"></i>Daily Housekeeping</li>
              <li><i class="fas fa-phone text-success me-2"></i>24/7 Support</li>
              <li><i class="fas fa-shield-alt text-success me-2"></i>Secure Environment</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  `;
  
  modalBody.innerHTML = detailsHtml;
  
  // Setup Book Now button in modal
  bookNowBtn.onclick = function() {
    modal.querySelector('[data-bs-dismiss="modal"]').click(); // Close modal
    redirectToBooking(item.id);
  };
}

// Redirect to booking section with pre-filled item
function redirectToBooking(itemId) {
  console.log('Redirecting to booking for item ID:', itemId);
  
  const item = window.allItems.find(item => item.id == itemId);
  
  if (!item) {
    console.error('Item not found:', itemId);
    showToast('Item not found for booking', 'error');
    return;
  }
  
  console.log('Found item for booking:', item);
  
  // Find and activate the booking button in sidebar
  const bookingButton = document.querySelector('button[onclick*="showSection(\'booking\')"]');
  console.log('Booking button found:', bookingButton);
  
  // Switch to booking section (correct section ID)
  console.log('Switching to booking section...');
  showSection('booking', bookingButton);
  
  // Pre-fill booking form with selected item
  setTimeout(() => {
    console.log('Pre-filling booking form...');
    prefillBookingForm(item);
    showToast(`Ready to book ${item.name}`, 'success');
  }, 500); // Increased timeout to ensure section switch completes
}

// Pre-fill booking form with selected item
function prefillBookingForm(item) {
  // Set item name in form if there's a field for it
  const itemNameField = document.querySelector('#item_name, [name="item_name"]');
  if (itemNameField) {
    itemNameField.value = item.name;
  }
  
  // Try to find and populate other relevant fields
  const itemTypeField = document.querySelector('#item_type, [name="item_type"]');
  if (itemTypeField) {
    itemTypeField.value = item.item_type;
  }
  
  const priceField = document.querySelector('#price, [name="price"]');
  if (priceField) {
    priceField.value = item.price;
  }
  
  // Add item details to details field if it exists
  const detailsField = document.querySelector('#details, [name="details"], textarea');
  if (detailsField) {
    const currentDetails = detailsField.value;
    const itemInfo = `Item: ${item.name} | Type: ${item.item_type} | Price: P${item.price}${item.item_type === 'room' ? '/night' : '/day'} | Capacity: ${item.capacity}`;
    
    if (currentDetails) {
      detailsField.value = itemInfo + ' | ' + currentDetails;
    } else {
      detailsField.value = itemInfo;
    }
  }
  
  // Highlight the booking form
  const bookingForm = document.querySelector('#reservationForm, #pencilForm, form');
  if (bookingForm) {
    bookingForm.style.border = '2px solid #28a745';
    bookingForm.style.borderRadius = '0.5rem';
    bookingForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Remove highlight after 3 seconds
    setTimeout(() => {
      bookingForm.style.border = '';
      bookingForm.style.borderRadius = '';
    }, 3000);
  }
}

// Reminder Functions (from guest.js with Bootstrap enhancement)
function pencilReminder() {
  const message =
    "Reminder: We only allow two (2) weeks to pencil book. If we have not heard back from you after two weeks, your pencil booking will become null and void and deleted from our system.";

  if (typeof showToast === "function") {
    showToast(message, "warning");
  } else {
    alert(message);
  }
  return true;
}

function reservationReminder() {
  const message =
    "Reminder: We only allow one (1) week to pencil book. If we have not heard back from you after one week, your reservation will become null and void and deleted from our system. CONFIRMED ROOM RESERVATION IS NON-REFUNDABLE.";

  if (typeof showToast === "function") {
    showToast(message, "warning");
  } else {
    alert(message);
  }
  return true;
}

// Keyboard Navigation (from guest-enhanced.js)
function addKeyboardNavigation() {
  const sidebarButtons = document.querySelectorAll(
    ".sidebar-guest button, .sidebar-guest a"
  );

  sidebarButtons.forEach((button, index) => {
    button.addEventListener("keydown", function (e) {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        const nextButton = sidebarButtons[index + 1];
        if (nextButton) {
          nextButton.focus();
        }
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        const prevButton = sidebarButtons[index - 1];
        if (prevButton) {
          prevButton.focus();
        }
      }
    });
  });

  // Add escape key to close mobile menu
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      const sidebar = document.querySelector(".sidebar-guest");
      const toggleBtn = document.querySelector(".mobile-menu-toggle");
      if (sidebar && sidebar.classList.contains("open")) {
        sidebar.classList.remove("open");
        if (toggleBtn) {
          toggleBtn.querySelector("i").className = "fas fa-bars";
        }
      }
    }
  });
}

// Enhanced Form Handling
function enhanceForms() {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    // Add Bootstrap validation classes
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          showToast("Please fill in all required fields correctly.", "warning");
        }
        form.classList.add("was-validated");
      },
      false
    );

    // Real-time validation
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this);
      });

      input.addEventListener("input", function () {
        clearValidation(this);
      });
    });
  });
}

// Field Validation
function validateField(field) {
  if (field.checkValidity()) {
    field.classList.remove("is-invalid", "form-error");
    field.classList.add("is-valid", "form-success");
  } else {
    field.classList.remove("is-valid", "form-success");
    field.classList.add("is-invalid", "form-error");
  }
}

// Clear Validation
function clearValidation(field) {
  field.classList.remove(
    "is-valid",
    "is-invalid",
    "form-success",
    "form-error"
  );
}

// Enhanced Toast Notifications
function showToast(message, type = "info") {
  const toastContainer = getOrCreateToastContainer();
  const toastId = "toast-" + Date.now();

  let bgClass, iconClass;
  switch (type) {
    case "success":
      bgClass = "bg-success";
      iconClass = "fa-check-circle";
      break;
    case "warning":
      bgClass = "bg-warning";
      iconClass = "fa-exclamation-triangle";
      break;
    case "error":
    case "danger":
      bgClass = "bg-danger";
      iconClass = "fa-times-circle";
      break;
    default:
      bgClass = "bg-info";
      iconClass = "fa-info-circle";
  }

  const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas ${iconClass} me-2"></i>
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML("beforeend", toastHtml);
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement);
  toast.show();

  // Remove element after it's hidden
  toastElement.addEventListener("hidden.bs.toast", function () {
    this.remove();
  });
}

// Get or Create Toast Container
function getOrCreateToastContainer() {
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container position-fixed top-0 end-0 p-3";
    container.style.zIndex = "9999";
    document.body.appendChild(container);
  }
  return container;
}

// Global Sidebar Toggle Function
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar-guest");
  const toggleBtn = document.querySelector(".mobile-menu-toggle");

  if (sidebar) {
    sidebar.classList.toggle("open");

    if (toggleBtn) {
      const icon = toggleBtn.querySelector("i");
      if (sidebar.classList.contains("open")) {
        icon.className = "fas fa-times";
      } else {
        icon.className = "fas fa-bars";
      }
    }
  }
}

// Global function exports for compatibility
window.showSection = showSection;
window.toggleBookingForm = toggleBookingForm;
window.generateReceiptNumber = generateReceiptNumber;
window.pencilReminder = pencilReminder;
window.reservationReminder = reservationReminder;
window.showToast = showToast;
window.toggleSidebar = toggleSidebar;
window.loadItems = loadItems;
window.filterItems = filterItems;
window.showItemDetails = showItemDetails;
window.redirectToBooking = redirectToBooking;
window.setupItemButtons = setupItemButtons;

// Interactive Overview Setup - Typical Dashboard Style
function setupInteractiveOverview() {
  updateOverviewStats();
  loadFeaturedItems();
}

// Update Overview Statistics
function updateOverviewStats() {
  if (!window.allItems) {
    // Use default values if no data loaded yet
    setTimeout(updateOverviewStats, 1000);
    return;
  }

  const rooms = window.allItems.filter((item) => item.item_type === "room");
  const facilities = window.allItems.filter(
    (item) => item.item_type === "facility"
  );
  const availableCount = Math.floor(window.allItems.length * 0.7); // 70% available for demo

  // Update statistics displays
  document.getElementById("total-rooms").textContent = rooms.length;
  document.getElementById("total-facilities").textContent = facilities.length;
  document.getElementById("available-rooms").textContent = availableCount;

  // Get user bookings count (you can integrate with real booking data)
  getUserBookingsCount();
}

// Get user bookings count from server or estimate
function getUserBookingsCount() {
  // Try to get from user management section if available
  const bookingsTable = document.querySelector("#user .table-container tbody");
  let bookingsCount = 0;

  if (bookingsTable) {
    bookingsCount = bookingsTable.querySelectorAll("tr").length;
  } else {
    // Default estimate for demo
    bookingsCount = Math.floor(Math.random() * 5) + 1;
  }

  document.getElementById("total-bookings").textContent = bookingsCount;
}

// Load Featured Items for Overview
function loadFeaturedItems() {
  if (!window.allItems) {
    setTimeout(loadFeaturedItems, 1000);
    return;
  }

  const featuredContainer = document.getElementById("featured-items");
  if (!featuredContainer) {
    return;
  }

  // Get 3 featured items (mix of rooms and facilities)
  const rooms = window.allItems
    .filter((item) => item.item_type === "room")
    .slice(0, 2);
  const facilities = window.allItems
    .filter((item) => item.item_type === "facility")
    .slice(0, 1);
  const featuredItems = [...rooms, ...facilities];

  featuredContainer.innerHTML = "";

  featuredItems.forEach((item) => {
    const availability = Math.random() > 0.3 ? "Available" : "Occupied";
    const badgeClass =
      availability === "Available" ? "bg-success" : "bg-warning";
    const icon = item.item_type === "room" ? "fa-bed" : "fa-building";

    const itemHtml = `
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 featured-item" data-item-type="${
                  item.item_type
                }">
                    ${
                      item.image
                        ? `<img src="${item.image}" class="card-img-top" style="height:120px;object-fit:cover;" alt="${item.name}">`
                        : ""
                    }
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">
                            <i class="fas ${icon} me-1"></i>${item.name}
                        </h6>
                        <p class="card-text small text-muted mb-2">${
                          item.description
                            ? item.description.substring(0, 60) + "..."
                            : "Premium accommodation with modern amenities."
                        }</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge ${badgeClass} small">${availability}</span>
                            <small class="text-primary fw-bold">₱${parseInt(
                              item.price
                            ).toLocaleString()}</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

    featuredContainer.insertAdjacentHTML("beforeend", itemHtml);
  });

  // Add click handlers for featured items
  featuredContainer.addEventListener("click", function (e) {
    const card = e.target.closest(".featured-item");
    if (card) {
      const { itemType } = card.dataset;
      // Switch to rooms section and filter by type
      showSection("rooms");
      setTimeout(() => {
        const typeRadio = document.querySelector(
          `input[name="type"][value="${itemType}"]`
        );
        if (typeRadio) {
          typeRadio.checked = true;
          filterItems();
        }
      }, 300);
      showToast(`Viewing ${itemType}s in Rooms & Facilities`, "info");
    }
  });
}

// Initialize Guest Chat System (Simplified)
function initializeChatSystem() {
  console.log('Chat system temporarily disabled for feedback system stability');
  
  // Only initialize basic form handling without server calls
  const chatForm = document.getElementById("chat-form");
  const chatInput = document.getElementById("chat-input");

  if (chatForm && chatInput) {
    chatForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const message = chatInput.value.trim();

      if (message) {
        // Show message locally without server call
        showToast('Message received. Chat system is currently under maintenance.', 'info');
        chatInput.value = "";
      }
    });
  }
}

// Load chat messages for guest (Simplified)
function loadChatMessages() {
  // Temporarily disabled to prevent errors
  console.log('Chat messages loading disabled for system stability');
  
  const chatMessages = document.getElementById("chat-messages");
  if (chatMessages) {
    chatMessages.innerHTML = `
      <div class="text-center text-muted">
        <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
        <h5>Chat System Under Maintenance</h5>
        <p>Please use the feedback system to contact us</p>
      </div>
    `;
  }
}

// Display chat messages in guest interface
function displayChatMessages(messages) {
  const chatMessages = document.getElementById("chat-messages");
  if (!chatMessages) {
    return;
  }

  if (messages.length === 0) {
    chatMessages.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
                <h5>Welcome to BarCIE Support</h5>
                <p>Send us a message and we'll respond as soon as possible</p>
            </div>
        `;
    return;
  }

  let messagesHtml = "";

  messages.forEach((message) => {
    const isFromGuest = message.sender_type === "guest";
    const messageClass = isFromGuest ? "sent" : "received";
    const messageTime = new Date(message.created_at).toLocaleString();
    const senderName = isFromGuest ? "You" : "Support";

    messagesHtml += `
            <div class="chat-message ${messageClass}">
                <div class="message-content">
                    ${escapeHtml(message.message)}
                    <div class="message-time">${senderName} • ${messageTime}</div>
                </div>
            </div>
        `;
  });

  chatMessages.innerHTML = messagesHtml;

  // Scroll to bottom
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Send chat message from guest (Simplified)
function sendChatMessage(message) {
  // Temporarily disabled to prevent errors
  showToast('Chat system is under maintenance. Please use the feedback system instead.', 'info');
}

// Send quick message (for quick help buttons)
function sendQuickMessage(message) {
  const chatInput = document.getElementById("chat-input");
  if (chatInput) {
    chatInput.value = message;
    chatInput.focus();
  }
}

// Update unread message count (Simplified)
function updateUnreadCount() {
  // Temporarily disabled to prevent errors
  const unreadBadge = document.getElementById("unread-count");
  if (unreadBadge) {
    unreadBadge.style.display = "none";
  }
}

// Export new functions for global access
window.sendQuickMessage = sendQuickMessage;
window.initializeChatSystem = initializeChatSystem;

// Utility function to escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Star Rating System
function initializeStarRating() {
  const starRating = document.getElementById('star-rating');
  const ratingValue = document.getElementById('rating-value');
  const ratingText = document.getElementById('rating-text');
  const submitButton = document.getElementById('submit-feedback');
  
  if (!starRating) {
    return;
  }
  
  const stars = starRating.querySelectorAll('.star');
  const ratingTexts = {
    1: 'Poor - Needs significant improvement',
    2: 'Fair - Below expectations',
    3: 'Good - Meets expectations',
    4: 'Very Good - Exceeds expectations',
    5: 'Excellent - Outstanding experience'
  };
  
  let currentRating = 0;
  
  // Add event listeners to stars
  stars.forEach((star, index) => {
    const rating = index + 1;
    
    // Hover effect
    star.addEventListener('mouseenter', () => {
      highlightStars(rating);
      ratingText.textContent = ratingTexts[rating];
      ratingText.className = 'text-primary fw-bold';
    });
    
    // Click to select rating
    star.addEventListener('click', () => {
      currentRating = rating;
      ratingValue.value = rating;
      updateStarDisplay(rating);
      ratingText.textContent = ratingTexts[rating];
      ratingText.className = 'text-success fw-bold';
      
      // Enable submit button
      submitButton.disabled = false;
      submitButton.className = 'btn btn-primary';
    });
  });
  
  // Reset on mouse leave
  starRating.addEventListener('mouseleave', () => {
    updateStarDisplay(currentRating);
    if (currentRating > 0) {
      ratingText.textContent = ratingTexts[currentRating];
      ratingText.className = 'text-success fw-bold';
    } else {
      ratingText.textContent = 'Click to rate';
      ratingText.className = 'text-muted';
    }
  });
  
  function highlightStars(rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.add('hover');
        star.classList.remove('active');
      } else {
        star.classList.remove('hover', 'active');
      }
    });
  }
  
  function updateStarDisplay(rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.add('active');
        star.classList.remove('hover');
      } else {
        star.classList.remove('active', 'hover');
      }
    });
  }
  
  // Form submission enhancement
  const feedbackForm = document.getElementById('feedback-form');
  if (feedbackForm) {
    feedbackForm.addEventListener('submit', (e) => {
      if (currentRating === 0) {
        e.preventDefault();
        alert('Please select a star rating before submitting your feedback.');
        return false;
      }
      
      // Add loading state
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
      submitButton.disabled = true;
      
      // Show a success message even before form submission
      setTimeout(() => {
        if (!e.defaultPrevented) {
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert alert-info mt-3';
          alertDiv.innerHTML = '<i class="fas fa-clock me-2"></i>Processing your feedback...';
          feedbackForm.appendChild(alertDiv);
        }
      }, 100);
    });
  }
  
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
      setTimeout(() => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => {
          if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
          }
        }, 500);
      }, 5000);
    }
  });
}

// Guest Availability Calendar - Privacy-Focused
function initializeGuestCalendar() {
  const calendarEl = document.getElementById('guestCalendar');
  
  if (!calendarEl) {
    console.log('Guest calendar element not found');
    return;
  }
  
  console.log('Initializing guest calendar...');
  
  // Check if FullCalendar is loaded
  if (typeof FullCalendar === 'undefined') {
    console.error('FullCalendar library not loaded!');
    showToast('Calendar library not loaded. Please refresh the page.', 'error');
    return;
  }
  
  // Initialize FullCalendar for guest availability view
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,listWeek'
    },
    height: 'auto',
    events: function(fetchInfo, successCallback, failureCallback) {
      console.log('Fetching guest availability data...');
      fetch('database/user_auth.php?action=fetch_guest_availability')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          console.log('Guest availability data received:', data);
          successCallback(data);
        })
        .catch(error => {
          console.error('Error fetching availability data:', error);
          failureCallback(error);
          showToast('Unable to load availability data. Please refresh the page.', 'warning');
        });
    },
    eventDisplay: 'block',
    dayMaxEvents: 3,
    moreLinkClick: 'popover',
    eventMouseEnter: function(info) {
      // Show tooltip with room/facility info (privacy-safe)
      const tooltip = document.createElement('div');
      tooltip.className = 'custom-tooltip';
      tooltip.innerHTML = `
        <strong>${info.event.title}</strong><br>
        <small>Check availability for other dates</small>
      `;
      tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
      `;
      
      document.body.appendChild(tooltip);
      
      const rect = info.el.getBoundingClientRect();
      tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
      tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
      
      info.el.tooltip = tooltip;
    },
    eventMouseLeave: function(info) {
      if (info.el.tooltip) {
        document.body.removeChild(info.el.tooltip);
        info.el.tooltip = null;
      }
    },
    eventClick: function(info) {
      // Show availability info without personal details
      const { facility } = info.event.extendedProps;
      const message = `${facility} is currently occupied during this period. Please select alternative dates for your booking.`;
      showToast(message, 'info');
    },
    loading: function(isLoading) {
      const loadingIndicator = document.getElementById('calendar-loading');
      if (loadingIndicator) {
        loadingIndicator.style.display = isLoading ? 'block' : 'none';
      }
    },
    // Responsive settings
    aspectRatio: window.innerWidth < 768 ? 1.0 : 1.35,
    locale: 'en',
    firstDay: 1, // Monday first
    businessHours: {
      daysOfWeek: [0, 1, 2, 3, 4, 5, 6], // All days
      startTime: '06:00',
      endTime: '22:00'
    },
    selectMirror: true,
    weekends: true,
    nowIndicator: true,
    eventTextColor: '#ffffff',
    eventBorderColor: 'transparent',
    // Add some demo events if no real data
    eventDidMount: function(info) {
      console.log('Event mounted:', info.event.title);
    }
  });
  
  // Render the calendar
  try {
    calendar.render();
    console.log('Guest calendar rendered successfully');
    
    // Add a message for empty calendar
    setTimeout(() => {
      if (calendar.getEvents().length === 0) {
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'text-center text-muted mt-3';
        emptyMessage.innerHTML = `
          <i class="fas fa-calendar-check fa-2x mb-2"></i>
          <p>All rooms and facilities are currently available!</p>
          <small>Book now to secure your preferred dates.</small>
        `;
        calendarEl.parentNode.appendChild(emptyMessage);
      }
    }, 2000);
    
  } catch (error) {
    console.error('Error rendering guest calendar:', error);
    showToast('Failed to initialize calendar. Please refresh the page.', 'error');
  }
  
  // Store calendar globally for potential updates
  window.guestCalendar = calendar;
  
  // Responsive handling
  window.addEventListener('resize', function() {
    if (calendar) {
      calendar.updateSize();
    }
  });
  
  // Add loading indicator if it doesn't exist
  if (!document.getElementById('calendar-loading')) {
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'calendar-loading';
    loadingDiv.style.cssText = `
      display: none;
      text-align: center;
      padding: 20px;
      color: #6c757d;
    `;
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading availability...';
    calendarEl.parentNode.insertBefore(loadingDiv, calendarEl);
  }
}

// Export guest calendar functions
window.initializeGuestCalendar = initializeGuestCalendar;
