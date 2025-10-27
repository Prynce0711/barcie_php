// Complete Guest Portal JavaScript with Bootstrap Integration
// Consolidated from guest.js, guest-enhanced.js, and inline scripts

document.addEventListener("DOMContentLoaded", function () {
  initializeGuestPortal();
});

function initializeGuestPortal() {
  try {
    setupBootstrapComponents();
  } catch (error) {
    console.error("Guest: Bootstrap setup failed:", error);
  }

  try {
    setupMobileMenu();
  } catch (error) {
    console.error("Guest: Mobile menu setup failed:", error);
  }

  try {
    enhanceForms();
  } catch (error) {
    console.error("Guest: Form enhancement failed:", error);
  }

  try {
    setupSectionNavigation();
  } catch (error) {
    console.error("Guest: Section navigation setup failed:", error);
  }

  try {
    setupBookingForms();
  } catch (error) {
    console.error("Guest: Booking forms setup failed:", error);
  }  try {
    setupCardFiltering();
    console.log("Guest: Card filtering initialized");
  } catch (error) {
    console.error("Guest: Card filtering setup failed:", error);
  }
  
  try {
    initializeReceiptGeneration();
    console.log("Guest: Receipt generation initialized");
  } catch (error) {
    console.error("Guest: Receipt generation failed:", error);
  }
  
  try {
    loadItems();
    console.log("Guest: Items loading started");
  } catch (error) {
    console.error("Guest: Items loading failed:", error);
  }
  
  try {
    addKeyboardNavigation();
    console.log("Guest: Keyboard navigation added");
  } catch (error) {
    console.error("Guest: Keyboard navigation failed:", error);
  }
  
  try {
    setupInteractiveOverview();
    console.log("Guest: Interactive overview initialized");
  } catch (error) {
    console.error("Guest: Interactive overview failed:", error);
  }
  
  try {
    initializeChatSystem();
    console.log("Guest: Chat system initialized");
  } catch (error) {
    console.warn('Guest: Chat system initialization failed:', error);
  }
  
  try {
    initializeStarRating();
    console.log("Guest: Star rating initialized");
  } catch (error) {
    console.error("Guest: Star rating failed:", error);
  }
  
  try {
    initializeGuestCalendar();
    console.log("Guest: Guest calendar initialized");
  } catch (error) {
    console.error("Guest: Guest calendar failed:", error);
  }
  
  console.log("Guest: Portal initialization complete");
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
  console.log("Guest: Attempting to show section:", sectionId);
  
  // Validate sectionId
  if (!sectionId) {
    console.error("Guest: No section ID provided");
    return false;
  }
  
  // Hide all sections
  const allSections = document.querySelectorAll(".content-section");
  console.log("Guest: Found", allSections.length, "total sections");
  
  allSections.forEach((sec) => {
    sec.classList.remove("active");
    sec.style.display = "none";
  });

  // Show target section
  const section = document.getElementById(sectionId);
  if (section) {
    section.classList.add("active");
    section.style.display = "block";
    section.style.opacity = "1";
    
    // Add smooth animation
    section.style.animation = "fadeInUp 0.4s ease";
    
    console.log("Guest: Section successfully displayed:", sectionId);
    
    // Scroll to top of section
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
  } else {
    console.error("Guest: Section element not found:", sectionId);
    
    // Try to find any section as fallback
    const availableSections = Array.from(allSections).map(sec => sec.id);
    console.log("Guest: Available sections:", availableSections);
    return false;
  }

  // Update navigation buttons
  const sidebarButtons = document.querySelectorAll(".sidebar-guest button");
  sidebarButtons.forEach((btn) => btn.classList.remove("active"));
  
  if (button) {
    button.classList.add("active");
    console.log("Guest: Navigation button activated");
  } else {
    // Try to find the button by section name
    const matchingButton = document.querySelector(`.sidebar-guest button[onclick*="${sectionId}"]`);
    if (matchingButton) {
      matchingButton.classList.add("active");
      console.log("Guest: Found and activated matching button");
    }
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
  
  return true;
}

// Setup Section Navigation
function setupSectionNavigation() {
  console.log("Guest: Setting up section navigation");
  
  // Check if sections exist
  const sections = document.querySelectorAll(".content-section");
  console.log("Guest: Found", sections.length, "sections");
  
  if (sections.length === 0) {
    console.error("Guest: No content sections found!");
    return;
  }
  
  // Check which section should be active on page load
  // First, remove any existing active class from sections
  sections.forEach((sec, index) => {
    sec.classList.remove("active");
    sec.style.display = "none";
    console.log(`Guest: Section ${index + 1} (${sec.id}): reset`);
  });
  
  // Show overview section by default
  setTimeout(() => {
    const overviewButton = document.querySelector(".sidebar-guest button[onclick*='overview']");
    console.log("Guest: Overview button found:", !!overviewButton);
    
    // Try to show overview section
    const overviewSection = document.getElementById("overview");
    if (overviewSection) {
      console.log("Guest: Showing overview section");
      showSection("overview", overviewButton);
    } else {
      console.warn("Guest: Overview section not found, trying first available section");
      const firstSection = sections[0];
      if (firstSection) {
        showSection(firstSection.id, null);
      }
    }
  }, 200);
}

// Booking Form Toggle (from guest.js)
function toggleBookingForm() {
  const selectedType = document.querySelector(
    'input[name="bookingType"]:checked'
  );
  
  if (!selectedType) {
    console.warn('No booking type selected');
    return;
  }
  
  const type = selectedType.value;
  const reservationForm = document.getElementById("reservationForm");
  const pencilForm = document.getElementById("pencilForm");
  
  if (!reservationForm || !pencilForm) {
    console.warn('Booking forms not found');
    return;
  }
  
  reservationForm.style.display = type === "reservation" ? "block" : "none";
  pencilForm.style.display = type === "pencil" ? "block" : "none";

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
  const response = await fetch("api/receipt.php");
    const data = await response.json();

    if (data && data.success && data.receipt_no) {
      const receiptField = document.getElementById("receipt_no");
      if (receiptField) {
        receiptField.value = data.receipt_no;
        receiptField.classList.add("is-valid");
      }
      console.log("Generated receipt number:", data.receipt_no);
    } else {
      console.error("Error generating receipt number:", data && data.error ? data.error : data);
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
  
  console.log("Guest: Filtering items by type:", selectedType);
  
  const cards = document.querySelectorAll("#cards-grid .card");
  let visibleCount = 0;
  
  cards.forEach((card) => {
    if (card.dataset.type === selectedType) {
      card.style.display = "block";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });
  
  console.log("Guest: Showing", visibleCount, "items of type", selectedType);
  
  // Show message if no items of selected type
  const container = document.getElementById("cards-grid");
  if (visibleCount === 0 && container) {
    const noItemsMessage = document.createElement("div");
    noItemsMessage.className = "no-items-message col-12";
    noItemsMessage.innerHTML = `
      <div class="alert alert-info text-center">
        <i class="fas fa-search fa-2x mb-2"></i>
        <h5>No ${selectedType}s Available</h5>
        <p>Try selecting a different type or check back later.</p>
      </div>
    `;
    
    // Remove existing no-items message
    const existingMessage = container.querySelector(".no-items-message");
    if (existingMessage) {
      existingMessage.remove();
    }
    
    container.appendChild(noItemsMessage);
  } else {
    // Remove no-items message if items are visible
    const existingMessage = container?.querySelector(".no-items-message");
    if (existingMessage) {
      existingMessage.remove();
    }
  }
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
  console.log("Guest: Loading items from API...");
  
  try {
  const res = await fetch("api/items.php");
    console.log("Guest: Response status:", res.status);
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    const response = await res.json();
    console.log("Guest: Raw response:", response);
    
    // Handle both old and new response formats
    let items = [];
    if (response && response.success && Array.isArray(response.items)) {
      // New format: { success: true, items: [...], count: X }
      items = response.items;
      console.log("Guest: Got wrapped items array:", items.length);
    } else if (Array.isArray(response)) {
      // Old format: plain array
      items = response;
      console.log("Guest: Got plain array of items:", items.length);
    } else if (response && Array.isArray(response.data)) {
      // Alternative format: { data: [...] }
      items = response.data;
      console.log("Guest: Got data array:", items.length);
    } else {
      console.warn("Guest: Unexpected response format:", response);
      
      // If response has error property, show it
      if (response && response.error) {
        throw new Error(response.message || response.error);
      }
      
      items = [];
    }

    // Store items globally for filtering
    window.allItems = items;

    const container = document.getElementById("cards-grid");
    if (!container) {
      console.error("Guest: Cards grid container not found!");
      return;
    }

    if (items.length === 0) {
      container.innerHTML = `
        <div class="col-12">
          <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <h5>No Rooms or Facilities Available</h5>
            <p>Please check back later or contact us for more information.</p>
          </div>
        </div>
      `;
      return;
    }

    console.log("Guest: Rendering", items.length, "items");
    container.innerHTML = "";
    
    items.forEach((item, index) => {
      console.log(`Guest: Rendering item ${index + 1}:`, item.name, `(${item.item_type})`);
      
      const card = document.createElement("div");
      card.classList.add("card");
      card.dataset.type = item.item_type;
      card.dataset.price = item.price || 0;
      card.dataset.itemId = item.id;
      
      // Use actual room status if available, fallback to available
      const roomStatus = item.room_status || 'available';
      card.dataset.availability = ['available', 'clean'].includes(roomStatus) ? "available" : "occupied";
      
      // Ensure proper image path - add leading slash if missing for absolute path
      let imageUrl = item.image && item.image.trim() !== '' ? item.image : '/assets/images/imageBg/barcie_logo.jpg';
      if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('/')) {
        imageUrl = '/' + imageUrl;
      }
      
      card.innerHTML = `
        <div class="card-image">
          <img src="${imageUrl}" 
               style="width:100%;height:200px;object-fit:cover;border-radius:15px 15px 0 0;" 
               onerror="this.onerror=null; this.src='/assets/images/imageBg/barcie_logo.jpg';"
               alt="${item.name}">
          <div class="availability-badge ${card.dataset.availability}">
            <i class="fas ${card.dataset.availability === 'available' ? 'fa-check-circle' : 'fa-times-circle'}"></i>
            ${card.dataset.availability === 'available' ? 'Available' : 'Occupied'}
          </div>
        </div>
        <div class="card-content" style="padding: 20px;">
          <h3 style="margin-bottom: 10px; color: #2c3e50;">${item.name}</h3>
          ${item.room_number ? `<p style="margin: 5px 0; color: #7f8c8d;"><i class="fas fa-door-open me-1"></i>Room Number: ${item.room_number}</p>` : ""}
          <p style="margin: 5px 0; color: #7f8c8d;"><i class="fas fa-users me-1"></i>Capacity: ${item.capacity} ${item.item_type === "room" ? "persons" : "people"}</p>
          <p style="margin: 5px 0; color: #27ae60; font-weight: bold; font-size: 1.1em;"><i class="fas fa-peso-sign me-1"></i>₱${parseInt(item.price || 0).toLocaleString()}${item.item_type === "room" ? "/night" : "/day"}</p>
          <p style="margin: 10px 0; color: #34495e; line-height: 1.4;">${item.description || 'Comfortable accommodation with modern amenities.'}</p>
          <div class="card-actions" style="margin-top: 15px; display: flex; gap: 10px;">
            <button class="btn btn-primary btn-sm view-details-btn flex-fill" data-item-id="${item.id}">
              <i class="fas fa-eye me-1"></i>View Details
            </button>
            <button class="btn btn-success btn-sm book-now-btn flex-fill" data-item-id="${item.id}" ${card.dataset.availability !== 'available' ? 'disabled' : ''}>
              <i class="fas fa-calendar-plus me-1"></i>Book Now
            </button>
          </div>
        </div>
      `;
      container.appendChild(card);
    });
    
    // Add event handlers for View Details and Book Now buttons
    setupItemButtons();
    filterItems();
    
    console.log("Guest: Items loaded and displayed successfully");
    
  } catch (error) {
    console.error("Guest: Error loading items:", error);
    
    const container = document.getElementById("cards-grid");
    if (container) {
      container.innerHTML = `
        <div class="col-12">
          <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <h5>Error Loading Rooms & Facilities</h5>
            <p>Unable to load rooms and facilities. Please refresh the page or contact support.</p>
            <small class="text-muted">Error: ${error.message}</small>
          </div>
        </div>
      `;
    }
    
    // Show user-friendly toast notification
    if (typeof showToast === 'function') {
      showToast('Failed to load rooms and facilities. Please refresh the page.', 'error');
    }
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
          <img src="${item.image.startsWith('http') || item.image.startsWith('/') ? item.image : '/' + item.image}" class="img-fluid rounded mb-3" alt="${item.name}" style="max-height: 300px; width: 100%; object-fit: cover;">
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
  console.log('Pre-filling booking form with item:', item);
  
  // Set the correct booking type based on item type
  if (item.item_type === 'facility') {
    // Select pencil booking for facilities
    const pencilRadio = document.querySelector('input[name="bookingType"][value="pencil"]');
    if (pencilRadio) {
      pencilRadio.checked = true;
      toggleBookingForm(); // Show pencil form
    }
  } else {
    // Select reservation for rooms
    const reservationRadio = document.querySelector('input[name="bookingType"][value="reservation"]');
    if (reservationRadio) {
      reservationRadio.checked = true;
      toggleBookingForm(); // Show reservation form
    }
  }
  
  // Wait a moment for form to be visible, then set the room/facility selection
  setTimeout(() => {
    const roomSelect = document.querySelector('#room_select, [name="room_id"]');
    if (roomSelect) {
      // Set the selected room/facility
      roomSelect.value = item.id;
      console.log('Set room selection to:', item.id, item.name);
      
      // Trigger change event to update any dependent fields
      roomSelect.dispatchEvent(new Event('change', { bubbles: true }));
      
      // Add visual feedback
      roomSelect.style.border = '2px solid #28a745';
      setTimeout(() => {
        roomSelect.style.border = '';
      }, 2000);
    } else {
      console.warn('Room select dropdown not found');
    }
    
    // Scroll to the booking form
    const activeForm = item.item_type === 'facility' 
      ? document.querySelector('#pencilForm')
      : document.querySelector('#reservationForm');
      
    if (activeForm) {
      activeForm.style.border = '2px solid #28a745';
      activeForm.style.borderRadius = '0.5rem';
      activeForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
      
      // Remove highlight after 3 seconds
      setTimeout(() => {
        activeForm.style.border = '';
        activeForm.style.borderRadius = '';
      }, 3000);
      
      // Focus on the first input field
      const firstInput = activeForm.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
      if (firstInput && !firstInput.value) {
        firstInput.focus();
      }
    }
  }, 300);
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

// Enhanced detailed toast for calendar events
function showDetailedToast(message, type = "info", title = "Room/Facility Information") {
  const toastContainer = getOrCreateToastContainer();
  const toastId = "detailed-toast-" + Date.now();

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
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" style="max-width: 350px;">
            <div class="toast-header">
                <i class="fas ${iconClass} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" style="font-size: 0.9rem; line-height: 1.4;">
                ${message}
                <hr class="my-2 opacity-50">
                <div class="d-flex justify-content-between align-items-center">
                    <small><i class="fas fa-calendar-alt me-1"></i>Calendar View</small>
                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="toast">Got it</button>
                </div>
            </div>
        </div>
    `;

  toastContainer.insertAdjacentHTML("beforeend", toastHtml);
  const toastElement = document.getElementById(toastId);
  const toast = new bootstrap.Toast(toastElement, { autohide: false }); // Don't auto-hide
  toast.show();

  // Remove element after it's hidden
  toastElement.addEventListener("hidden.bs.toast", function () {
    this.remove();
  });
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
window.scrollToAvailability = scrollToAvailability;

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

  // Update statistics displays
  document.getElementById("total-rooms").textContent = rooms.length;
  document.getElementById("total-facilities").textContent = facilities.length;
  
  // Fetch real availability data
  fetchRealAvailability();
}

// Fetch real availability data from server
async function fetchRealAvailability() {
  // Show loading state
  const availableElement = document.getElementById("available-rooms");
  const originalText = availableElement.textContent;
  availableElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  
  try {
  const response = await fetch('api/available_count.php');
    const data = await response.json();

    if (data && data.success) {
      availableElement.textContent = data.available_count;
      console.log('Real availability loaded:', data.available_count);
    } else {
      console.warn('API returned error or unexpected response:', data);
      // Fallback: calculate based on room status from items
      availableElement.textContent = originalText;
      calculateFallbackAvailability();
    }
  } catch (error) {
    console.error('Error fetching availability:', error);
    // Fallback: calculate based on room status from items
    availableElement.textContent = originalText;
    calculateFallbackAvailability();
  }
}

// Fallback availability calculation
function calculateFallbackAvailability() {
  if (!window.allItems) {
    document.getElementById("available-rooms").textContent = "0";
    return;
  }
  
  // Count items that are available or clean (not occupied, maintenance, etc.)
  const availableItems = window.allItems.filter(item => 
    !item.room_status || 
    item.room_status === 'available' || 
    item.room_status === 'clean'
  );
  
  document.getElementById("available-rooms").textContent = availableItems.length;
}

// Scroll to availability calendar section
function scrollToAvailability() {
  const availabilitySection = document.getElementById('availability-calendar-section');
  
  if (availabilitySection) {
    availabilitySection.scrollIntoView({ 
      behavior: 'smooth',
      block: 'start'
    });
    
    // Add a subtle highlight effect to the card
    const card = availabilitySection.querySelector('.card');
    if (card) {
      card.style.transition = 'box-shadow 0.3s ease';
      card.style.boxShadow = '0 0 20px rgba(23, 162, 184, 0.3)';
      
      setTimeout(() => {
        card.style.boxShadow = '';
      }, 2000);
    }
    
    showToast('Viewing availability calendar below', 'info');
  } else {
    showToast('Availability calendar section not found', 'warning');
  }
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
                        ? `<img src="${item.image.startsWith('http') || item.image.startsWith('/') ? item.image : '/' + item.image}" class="card-img-top" style="height:120px;object-fit:cover;" alt="${item.name}">`
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
  console.log('=== CALENDAR INITIALIZATION START ===');
  const calendarEl = document.getElementById('guestCalendar');
  
  if (!calendarEl) {
    console.error('Guest calendar element not found');
    return;
  }
  
  console.log('Guest calendar element found:', calendarEl);
  
  // Check if FullCalendar is loaded
  if (typeof FullCalendar === 'undefined') {
    console.error('FullCalendar library not loaded!');
    showToast('Calendar library not loaded. Please refresh the page.', 'error');
    return;
  }
  
  console.log('FullCalendar library loaded successfully');
  
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
      console.log('=== FETCHING CALENDAR DATA ===');
      console.log('Fetching guest availability data...');
  fetch('api/availability.php')
        .then(response => {
          console.log('Response received:', response);
          if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          console.log('=== CALENDAR DATA RECEIVED ===');
          console.log('Guest availability data:', data);
          // user_auth.php returns an array of events. Older API returned { success: true, events: [...] }
          if (Array.isArray(data)) {
            console.log('Number of events:', data.length);
            successCallback(data);
          } else if (data && data.success && Array.isArray(data.events)) {
            console.log('Number of events (wrapped):', data.events.length);
            successCallback(data.events);
          } else {
            console.log('No events found or invalid data structure');
            // If server returned an error object, show a toast for visibility
            if (data && data.error) {
              showToast('Calendar API error: ' + data.error, 'warning');
            }
            successCallback([]);
          }
        })
        .catch(error => {
          console.error('=== CALENDAR ERROR ===');
          console.error('Error fetching availability data:', error);
          failureCallback(error);
          showToast('Unable to load availability data. Please refresh the page.', 'warning');
        });
    },
    eventDisplay: 'block',
    dayMaxEvents: 3,
    moreLinkClick: 'popover',
    eventMouseEnter: function(info) {
      // Show enhanced tooltip with specific room information
      const { extendedProps } = info.event;
      const startDate = new Date(extendedProps.checkin_date || info.event.start);
      const endDate = new Date(extendedProps.checkout_date || info.event.end);
      const duration = extendedProps.duration_days || 1;
      const roomName = extendedProps.facility || 'Room/Facility';
      
      const tooltip = document.createElement('div');
      tooltip.className = 'custom-tooltip';
      tooltip.innerHTML = `
        <strong><i class="fas fa-bed me-1"></i>${roomName}</strong><br>
        <small><i class="fas fa-calendar me-1"></i>Check-in: ${startDate.toLocaleDateString()}</small><br>
        <small><i class="fas fa-calendar-check me-1"></i>Check-out: ${endDate.toLocaleDateString()}</small><br>
        <small><i class="fas fa-clock me-1"></i>Duration: ${duration} day${duration > 1 ? 's' : ''}</small><br>
        <small><i class="fas fa-info-circle me-1"></i>Status: ${extendedProps.booking_status || 'Occupied'}</small>
      `;
      tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        max-width: 250px;
        line-height: 1.4;
      `;
      
      document.body.appendChild(tooltip);
      
      const rect = info.el.getBoundingClientRect();
      tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
      tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
      
      // Adjust position if tooltip goes off screen
      if (tooltip.offsetLeft < 5) {
        tooltip.style.left = '5px';
      }
      if (tooltip.offsetLeft + tooltip.offsetWidth > window.innerWidth - 5) {
        tooltip.style.left = (window.innerWidth - tooltip.offsetWidth - 5) + 'px';
      }
      
      info.el.tooltip = tooltip;
    },
    eventMouseLeave: function(info) {
      if (info.el.tooltip) {
        document.body.removeChild(info.el.tooltip);
        info.el.tooltip = null;
      }
    },
    eventClick: function(info) {
      // Show detailed availability info with duration
      const { extendedProps } = info.event;
      const facility = extendedProps.facility || 'Room/Facility';
      const duration = extendedProps.duration_days || 1;
      const status = extendedProps.booking_status || 'occupied';
      const checkin = new Date(extendedProps.checkin_date || info.event.start).toLocaleDateString();
      const checkout = new Date(extendedProps.checkout_date || info.event.end).toLocaleDateString();
      
      const statusText = status === 'pending' ? 'has a pending booking' : 'is currently occupied';
      const message = `
        <strong>${facility}</strong> ${statusText} from <strong>${checkin}</strong> to <strong>${checkout}</strong> 
        (${duration} day${duration > 1 ? 's' : ''}). 
        <br><br>Please select alternative dates for your booking.
      `;
      
      // Create a custom modal-like toast for more detailed info
      showDetailedToast(message, 'info', facility);
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
      console.log('=== EVENT MOUNTED ===');
      console.log('Event mounted:', info.event.title);
      console.log('Event details:', info.event.extendedProps);
    }
  });
  
  // Render the calendar
  try {
    console.log('=== RENDERING CALENDAR ===');
    calendar.render();
    console.log('Guest calendar rendered successfully');
    console.log('Calendar object:', calendar);
    
    // Add a message for empty calendar
    setTimeout(() => {
      const events = calendar.getEvents();
      console.log('=== CALENDAR EVENTS CHECK ===');
      console.log('Number of events after render:', events.length);
      
      if (events.length === 0) {
        console.log('No events found, showing empty message');
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'text-center text-muted mt-3';
        emptyMessage.innerHTML = `
          <i class="fas fa-calendar-check fa-2x mb-2"></i>
          <p>All rooms and facilities are currently available!</p>
          <small>Book now to secure your preferred dates.</small>
        `;
        calendarEl.parentNode.appendChild(emptyMessage);
      } else {
        console.log('Events found:', events.map(e => e.title));
      }
    }, 3000); // Increased timeout
    
  } catch (error) {
    console.error('=== CALENDAR RENDER ERROR ===');
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

// Debug function to test section switching
window.testSectionSwitching = function() {
  console.log("Testing section switching...");
  const sections = ['overview', 'availability', 'rooms', 'booking', 'communication', 'feedback'];
  sections.forEach((sectionId, index) => {
    setTimeout(() => {
      console.log(`Testing section: ${sectionId}`);
      const button = document.querySelector(`button[onclick*="${sectionId}"]`);
      showSection(sectionId, button);
    }, index * 1000);
  });
};

// Debug function to test items loading
window.testItemsLoading = async function() {
  console.log("Testing items loading...");
  try {
    const response = await fetch("api/items.php");
    const data = await response.json();
    console.log("Raw API response:", data);
    console.log("Response type:", Array.isArray(data) ? 'array' : typeof data);
    if (Array.isArray(data)) {
      console.log("Items count:", data.length);
      data.forEach((item, index) => {
        console.log(`Item ${index + 1}:`, item.name, `(${item.item_type})`);
      });
    }
    return data;
  } catch (error) {
    console.error("Error testing items loading:", error);
  }
};

// Debug function to reload items
window.reloadItems = function() {
  console.log("Manually reloading items...");
  loadItems();
};

// Backup initialization system
function ensureSectionsWork() {
  console.log('=== BACKUP INITIALIZATION ===');
  
  // Force show home section if nothing is visible
  const visibleSections = document.querySelectorAll('.content-section.active, .content-section[style*="block"]');
  console.log('Currently visible sections:', visibleSections.length);
  
  if (visibleSections.length === 0) {
    console.log('No sections visible, forcing home section to show');
    const homeSection = document.getElementById('home');
    if (homeSection) {
      // Remove all active classes first
      document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
        section.style.display = 'none';
      });
      
      // Show home section
      homeSection.classList.add('active');
      homeSection.style.display = 'block';
      homeSection.style.opacity = '1';
      
      console.log('Home section forced to show');
    }
  }
  
  // Ensure navigation works
  const navButtons = document.querySelectorAll('button[onclick*="showSection"], .nav-link[data-section]');
  console.log('Found navigation buttons:', navButtons.length);
  
  navButtons.forEach(button => {
    if (!button.hasEventListener) {
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);
      
      newButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let sectionId = null;
        if (this.hasAttribute('data-section')) {
          sectionId = this.getAttribute('data-section');
        } else if (this.onclick) {
          const onclickStr = this.onclick.toString();
          const match = onclickStr.match(/showSection\(['"]([^'"]+)['"]/);
          if (match) {
            sectionId = match[1];
          }
        }
        
        if (sectionId) {
          console.log('Navigation clicked:', sectionId);
          showSection(sectionId, this);
        }
      });
      
      newButton.hasEventListener = true;
    }
  });
  
  console.log('Backup initialization completed');
}

// SINGLE initialization - prevent duplicate execution
console.log('=== GUEST PORTAL INITIALIZATION SEQUENCE ===');

// Flag to prevent duplicate initialization
if (!window.guestPortalInitialized) {
  window.guestPortalInitialized = false;
  
  // Immediate initialization if DOM is ready
  if (document.readyState !== 'loading') {
    console.log('DOM already ready, initializing immediately');
    if (!window.guestPortalInitialized) {
      window.guestPortalInitialized = true;
      initializeGuestPortal();
    }
  } else {
    console.log('DOM still loading, waiting for DOMContentLoaded');
    document.addEventListener('DOMContentLoaded', function() {
      if (!window.guestPortalInitialized) {
        window.guestPortalInitialized = true;
        initializeGuestPortal();
      }
    });
  }
  
  // Removed duplicate backup initialization
  // Removed duplicate fallback initialization
} else {
  console.log('Guest portal already initialized, skipping duplicate initialization');
}
