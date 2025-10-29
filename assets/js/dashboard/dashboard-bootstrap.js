// Enhanced Dashboard JavaScript with Bootstrap Integration
console.log("📄 dashboard-bootstrap.js file is loading...");

// Preserve original functionality while adding Bootstrap enhancements
document.addEventListener("DOMContentLoaded", function () {
  console.log("🚀 DOMContentLoaded fired - starting initialization...");
  // Add a small delay to ensure all elements are ready
  setTimeout(() => {
    console.log("⏱️ Delay complete - calling initializeDashboard()...");
    initializeDashboard();
  }, 100);
});

console.log("📄 dashboard-bootstrap.js event listeners registered");

function initializeDashboard() {
  console.log("Dashboard initialization started...");
  console.log("📍 Checking DOM elements...");
  console.log("  - Sidebar links:", document.querySelectorAll(".nav-link-custom").length);
  console.log("  - Content sections:", document.querySelectorAll(".content-section").length);
  
  setupBootstrapComponents();
  setupMobileMenu();
  setupSectionNavigation();
  initializeCalendar();
  // Wait for Chart.js to be available before initializing charts
  waitForChartJS();
  enhanceForms();
  setupDarkMode();
  enhanceDataTables();
  setupItemManagement();
  setupBookingForms();
  // setupCommunication(); // Temporarily disabled to fix feedback system
  initializeFeedbackManagement();

  // Initialize calendar & items functionality
  initializeCalendarNavigation();
  initializeRoomSearch();
  initializeRoomCalendar();
  
  // Initialize bookings functionality
  if (typeof initializeBookingsFiltering === 'function') {
    initializeBookingsFiltering();
  }
  if (typeof initializeBookingsActions === 'function') {
    initializeBookingsActions();
  }

  // Initialize edit forms and rooms functionality after a short delay
  setTimeout(() => {
    console.log('🔧 Initializing edit forms and rooms functionality...');
    
    // Count items first
    updateTypeCountsFromDOM();
    
    // Initialize edit forms
    initializeEditForms();

    // Initialize rooms-specific functionality
    if (typeof initializeRoomsFiltering === 'function') {
      initializeRoomsFiltering();
    }
    if (typeof initializeRoomsSearch === 'function') {
      initializeRoomsSearch();
    }

    console.log("✅ Rooms and facilities functionality initialized");
    
    // Log what we found
    const editButtons = document.querySelectorAll('.edit-toggle-btn');
    const editForms = document.querySelectorAll('[id^="editForm"]');
    console.log(`Found ${editButtons.length} edit buttons and ${editForms.length} edit forms`);
  }, 500);
}

// Wait for Chart.js to be loaded before initializing charts
function waitForChartJS() {
  if (typeof Chart !== "undefined") {
    initializeCharts();
  } else {
    setTimeout(waitForChartJS, 100);
  }
}

// Initialize Bootstrap Components
function setupBootstrapComponents() {
  console.log("Setting up Bootstrap components...");
  
  // Check if Bootstrap is loaded
  if (typeof bootstrap === 'undefined') {
    console.error("Bootstrap is not loaded!");
    return;
  }

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  console.log(`Initialized ${tooltipTriggerList.length} tooltips`);

  // Initialize popovers
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
  console.log(`Initialized ${popoverTriggerList.length} popovers`);

  // Initialize modals - Bootstrap handles data-bs-toggle automatically
  // We don't need to manually initialize, but we can verify they exist
  const modalList = document.querySelectorAll(".modal");
  console.log(`Found ${modalList.length} modals in the page`);
  
  // Verify the Add Item modal specifically
  const addItemModal = document.getElementById("addItemModal");
  if (addItemModal) {
    console.log("✓ Add Item Modal found and ready");
  } else {
    console.error("✗ Add Item Modal NOT found!");
  }
  
  // Verify the floating button
  const floatingBtn = document.querySelector(".floating-add-btn");
  if (floatingBtn) {
    console.log("✓ Floating Add Button found");
    console.log("Button attributes:", {
      toggle: floatingBtn.getAttribute('data-bs-toggle'),
      target: floatingBtn.getAttribute('data-bs-target')
    });
  } else {
    console.error("✗ Floating Add Button NOT found!");
  }
  
  // Verify delete modals
  const deleteModals = document.querySelectorAll('[id^="deleteModal"]');
  console.log(`Found ${deleteModals.length} delete modals`);
  
  console.log("Bootstrap components setup complete!");
}

// Mobile Menu Setup
function setupMobileMenu() {
  const toggleBtn = document.querySelector(".mobile-menu-toggle");
  const sidebar = document.querySelector(".sidebar");

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

// Setup Section Navigation (preserving original functionality)
function setupSectionNavigation() {
  console.log("Setting up section navigation...");
  
  // Load last active section from localStorage
  const lastSectionId =
    localStorage.getItem("activeSection") || "dashboard-section";
  
  console.log("Last active section:", lastSectionId);

  // Show dashboard section by default or last active section
  showSection(lastSectionId);

  // Add event listeners to navigation links
  const navLinks = document.querySelectorAll(".nav-link-custom");
  console.log("Found navigation links:", navLinks.length);
  
  navLinks.forEach((link, index) => {
    const sectionId = link.getAttribute("data-section");
    console.log(`Setting up link ${index + 1}: ${sectionId}`);
    
    link.addEventListener("click", function (e) {
      // Don't prevent default - let the hash navigation work
      console.log("Navigation clicked:", sectionId);
      const sectionId = this.getAttribute("data-section");
      showSection(sectionId);

      // Update active state
      document
        .querySelectorAll(".nav-link-custom")
        .forEach((l) => l.classList.remove("active"));
      this.classList.add("active");

      // Save current section to localStorage
      localStorage.setItem("activeSection", sectionId);
    });
  });

  // Set initial active state for navigation
  const initialActiveLink = document.querySelector(
    `.nav-link-custom[data-section="${lastSectionId}"]`
  );
  if (initialActiveLink) {
    document
      .querySelectorAll(".nav-link-custom")
      .forEach((l) => l.classList.remove("active"));
    initialActiveLink.classList.add("active");
  }
  
  // Listen for hash changes (browser back/forward buttons)
  window.addEventListener('hashchange', function() {
    const hash = window.location.hash.substring(1); // Remove the '#'
    if (hash) {
      console.log("Hash changed to:", hash);
      showSection(hash);
      
      // Update active link
      document.querySelectorAll(".nav-link-custom").forEach((l) => l.classList.remove("active"));
      const activeLink = document.querySelector(`.nav-link-custom[data-section="${hash}"]`);
      if (activeLink) {
        activeLink.classList.add("active");
      }
    }
  });
}

// Enhanced Section Switching
function showSection(sectionId) {
  console.log("🔄 showSection called with:", sectionId);
  
  // Hide all sections - use both CSS classes and display property for consistency
  const allSections = document.querySelectorAll(".content-section");
  console.log("📦 Found sections:", allSections.length);
  
  if (allSections.length === 0) {
    console.error("❌ NO SECTIONS FOUND! Check if .content-section elements exist in DOM");
    return;
  }
  
  allSections.forEach((section, index) => {
    console.log(`  Hiding section ${index + 1}: ${section.id || 'NO ID'}`);
    // Remove all display classes first
    section.classList.remove("d-block", "active");
    section.classList.add("d-none");
    section.style.display = "none";
  });

  // Show target section
  const targetSection = document.getElementById(sectionId);
  console.log("🎯 Target section element:", targetSection);
  
  if (targetSection) {
    console.log("✅ Showing target section:", sectionId);
    // Remove d-none first, then add d-block
    targetSection.classList.remove("d-none");
    targetSection.classList.add("d-block", "active");
    targetSection.style.display = "block";
    
    console.log("  - Classes after:", targetSection.className);
    console.log("  - Display style:", targetSection.style.display);

    // Add animation
    targetSection.style.animation = "slideInFromRight 0.5s ease";

    // Store the active section
    localStorage.setItem("activeSection", sectionId);

    // Dispatch custom event for section change
    document.dispatchEvent(
      new CustomEvent("sectionChanged", {
        detail: { section: sectionId },
      })
    );

    // Re-initialize edit forms when rooms section is shown
    if (sectionId === "rooms-section") {
      setTimeout(() => {
        // Only re-initialize if not already done
        if (!window.roomsInitialized) {
          initializeEditForms();
          initializeRoomsFiltering();
          initializeRoomsSearch();
          window.roomsInitialized = true;
          console.log("Rooms functionality fully initialized");
        } else {
          // Just refresh edit forms
          initializeEditForms();
          console.log("Edit forms refreshed for rooms section");
        }
      }, 100);
    }
  } else {
    console.error("Target section not found:", sectionId);
  }

  // Close mobile menu if open
  const sidebar = document.querySelector(".sidebar");
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

// Enhanced Calendar Initialization
function initializeCalendar() {
  const calendarEl = document.getElementById("calendar");
  if (calendarEl && typeof FullCalendar !== "undefined") {
    // Check if bookingEvents is available
    const events = window.bookingEvents || [];

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
      },
      events: events,
      eventClick: function (info) {
        showBookingDetails(info.event);
      },
      eventDidMount: function (info) {
        // Add Bootstrap classes based on event status
        const status = info.event.extendedProps?.status;
        if (status === "confirmed") {
          info.el.classList.add("bg-success");
        } else if (status === "pending") {
          info.el.classList.add("bg-warning");
        } else if (status === "cancelled") {
          info.el.classList.add("bg-danger");
        }
      },
      height: "auto",
      responsive: true,
    });

    calendar.render();
  }

  // Dashboard Calendar (smaller version)
}

// Show Booking Details in Modal
function showBookingDetails(event) {
  const modalHtml = `
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookingModalLabel">
                            <i class="fas fa-calendar-check me-2"></i>Booking Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-4"><strong>Title:</strong></div>
                            <div class="col-sm-8">${event.title}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Start:</strong></div>
                            <div class="col-sm-8">${event.start.toLocaleDateString()}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>End:</strong></div>
                            <div class="col-sm-8">${
                              event.end ? event.end.toLocaleDateString() : "N/A"
                            }</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-${getStatusColor(
                                  event.extendedProps?.status
                                )}">
                                    ${event.extendedProps?.status || "Unknown"}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

  // Remove existing modal if present
  const existingModal = document.getElementById("bookingModal");
  if (existingModal) {
    existingModal.remove();
  }

  // Add new modal to body
  document.body.insertAdjacentHTML("beforeend", modalHtml);

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById("bookingModal"));
  modal.show();
}

// Get Status Color for Badge
function getStatusColor(status) {
  switch (status) {
    case "confirmed":
      return "success";
    case "pending":
      return "warning";
    case "cancelled":
      return "danger";
    default:
      return "secondary";
  }
}

// Enhanced Form Handling
function enhanceForms() {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    // Add Bootstrap validation
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          showToast("Please fill in all required fields correctly.", "warning");
        } else {
          showToast("Form submitted successfully!", "success");
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
    field.classList.remove("is-invalid");
    field.classList.add("is-valid");
  } else {
    field.classList.remove("is-valid");
    field.classList.add("is-invalid");
  }
}

// Clear Validation
function clearValidation(field) {
  field.classList.remove("is-valid", "is-invalid");
}

// Enhanced Dark Mode (preserving original functionality)
function setupDarkMode() {
  // Apply saved theme
  const savedTheme = localStorage.getItem("theme") || "light";
  document.documentElement.setAttribute("data-bs-theme", savedTheme);

  // Also apply to body for backward compatibility
  if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
  }

  // Update toggle button
  const toggleBtn = document.querySelector(".dark-toggle");
  if (toggleBtn) {
    const icon = toggleBtn.querySelector("i");
    if (icon) {
      icon.className = savedTheme === "dark" ? "fas fa-sun" : "fas fa-moon";
    } else {
      toggleBtn.textContent = savedTheme === "dark" ? "☀️" : "🌙";
    }
  }
}

// Toggle Dark Mode (enhanced version)
function toggleDarkMode() {
  const currentTheme =
    document.documentElement.getAttribute("data-bs-theme") || "light";
  const newTheme = currentTheme === "dark" ? "light" : "dark";

  document.documentElement.setAttribute("data-bs-theme", newTheme);
  localStorage.setItem("theme", newTheme);

  // Also toggle body class for backward compatibility
  document.body.classList.toggle("dark-mode", newTheme === "dark");

  // Update button
  const toggleBtn = document.querySelector(".dark-toggle");
  if (toggleBtn) {
    const icon = toggleBtn.querySelector("i");
    if (icon) {
      icon.className = newTheme === "dark" ? "fas fa-sun" : "fas fa-moon";
    } else {
      toggleBtn.textContent = newTheme === "dark" ? "☀️" : "🌙";
    }
  }

  showToast(`Switched to ${newTheme} mode`, "info");
}

// Enhance Data Tables
function enhanceDataTables() {
  const tables = document.querySelectorAll(".table");

  tables.forEach((table) => {
    // Add Bootstrap classes
    table.classList.add("table-hover", "table-responsive");

    // Add search functionality for large tables
    if (table.querySelectorAll("tbody tr").length > 5) {
      addTableSearch(table);
    }

    // Add sorting functionality
    addTableSorting(table);
  });
}

// Add Table Search
function addTableSearch(table) {
  const tableContainer = table.closest(".card-body") || table.parentElement;
  if (tableContainer && !tableContainer.querySelector(".table-search")) {
    const searchHtml = `
            <div class="table-search mb-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search table..." 
                           onkeyup="filterTable(this, '${
                             table.id || "table-" + Date.now()
                           }')">
                </div>
            </div>
        `;
    tableContainer.insertAdjacentHTML("afterbegin", searchHtml);

    if (!table.id) {
      table.id = "table-" + Date.now();
    }
  }
}

// Filter Table
function filterTable(input, tableId) {
  const filter = input.value.toLowerCase();
  const table = document.getElementById(tableId);
  const rows = table.querySelectorAll("tbody tr");

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase();
    if (text.includes(filter)) {
      row.classList.remove("d-none");
    } else {
      row.classList.add("d-none");
    }
  });
}

// Add Table Sorting
function addTableSorting(table) {
  const headers = table.querySelectorAll("th");
  headers.forEach((header, index) => {
    if (!header.classList.contains("no-sort")) {
      header.style.cursor = "pointer";
      header.innerHTML += ' <i class="fas fa-sort ms-1"></i>';
      header.addEventListener("click", () => sortTable(table, index));
    }
  });
}

// Sort Table
function sortTable(table, columnIndex) {
  const tbody = table.querySelector("tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));
  const isAscending = table.dataset.sortOrder !== "asc";

  rows.sort((a, b) => {
    const aText = a.cells[columnIndex].textContent.trim();
    const bText = b.cells[columnIndex].textContent.trim();

    if (isNaN(aText) || isNaN(bText)) {
      return isAscending
        ? aText.localeCompare(bText)
        : bText.localeCompare(aText);
    } else {
      return isAscending
        ? Number(aText) - Number(bText)
        : Number(bText) - Number(aText);
    }
  });

  rows.forEach((row) => tbody.appendChild(row));
  table.dataset.sortOrder = isAscending ? "asc" : "desc";

  // Update sort icon
  const headers = table.querySelectorAll("th");
  headers.forEach((header, index) => {
    const icon = header.querySelector("i");
    if (icon) {
      if (index === columnIndex) {
        icon.className = isAscending
          ? "fas fa-sort-up ms-1"
          : "fas fa-sort-down ms-1";
      } else {
        icon.className = "fas fa-sort ms-1";
      }
    }
  });
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
                <strong class="me-auto">Dashboard</strong>
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

// Item Management Functions
async function loadItems() {
  try {
    const res = await fetch("database/fetch_items.php");
    const items = await res.json();
    const container = document.getElementById("cards-grid");
    if (container) {
      container.innerHTML = "";

      // Create counters object to track counts
      const counters = {
        room: 0,
        facility: 0,
      };

      items.forEach((item) => {
        // Increment counter based on item type
        counters[item.item_type] = (counters[item.item_type] || 0) + 1;

        const card = document.createElement("div");
        card.classList.add("card", "shadow-sm");
        card.dataset.type = item.item_type;
        card.innerHTML = `
          <div class="card-body">
            ${
              item.image
                ? `<img src="${item.image}" class="card-img-top mb-3" style="height:150px;object-fit:cover;">`
                : ""
            }
            <h5 class="card-title mb-3">${item.name}</h5>
            ${
              item.room_number
                ? `<p class="card-text mb-2"><i class="fas fa-door-open me-2"></i>Room Number: ${item.room_number}</p>`
                : ""
            }
            <p class="card-text mb-2">
              <i class="fas fa-users me-2"></i>Capacity: ${item.capacity} ${
          item.item_type === "room" ? "persons" : "people"
        }

            </p>
            <p class="card-text mb-2">
              <i class="fas fa-tag me-2"></i>Price: ₱${item.price}${
          item.item_type === "room" ? "/night" : "/day"
        }
            </p>
            <p class="card-text text-muted small">${item.description}</p>
            <div class="mt-3 pt-3 border-top">
              <span class="badge ${
                item.item_type === "room" ? "bg-primary" : "bg-success"
              }">
                ${item.item_type.toUpperCase()}
              </span>
            </div>
          </div>
        `;
        container.appendChild(card);
      });

      // Update the counters in the UI
      document.querySelectorAll(".type-count").forEach((counter) => {
        const { type } = counter.dataset;
        if (type) {
          counter.textContent = counters[type] || 0;
        }
      });

      filterItems();
    }
  } catch (error) {
    console.error("Error loading items:", error);
    showToast("Error loading items", "error");
  }
}

// Filter Items by Type
function filterItems() {
  const selectedType = document.querySelector(
    'input[name="type_filter"]:checked'
  );
  if (selectedType) {
    const selectedValue = selectedType.value;
    document.querySelectorAll(".card").forEach((card) => {
      if (card.dataset && card.dataset.type) {
        if (selectedValue === "all") {
          card.style.display = "block";
        } else {
          card.style.display =
            card.dataset.type === selectedValue ? "block" : "none";
        }
      }
    });

    // Update active state of filter buttons
    document.querySelectorAll(".type-filter").forEach((btn) => {
      if (btn.value === selectedValue) {
        btn.classList.add("active");
      } else {
        btn.classList.remove("active");
      }
    });
  }
}

// Setup Item Management
function setupItemManagement() {
  // Setup type filter listeners
  document.querySelectorAll(".type-filter").forEach((radio) => {
    radio.addEventListener("change", (e) => {
      // Update active states
      document
        .querySelectorAll(".type-filter")
        .forEach((btn) =>
          btn.classList.toggle("active", btn.value === e.target.value)
        );
      filterItemsInRoomsSection();
    });
  });

  // Initialize counters from existing items (PHP-rendered)
  updateTypeCountsFromDOM();

  // DON'T call loadItems() - items are already rendered by PHP
  // loadItems();

  // Setup edit form toggles if they exist
  setupEditFormToggles();
}

// Initialize item counters and filters
function initializeCounters() {
  const filterSection = document.querySelector(".item-filters");
  if (!filterSection) {
    return;
  }

  // Clear existing content
  filterSection.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="btn-group" role="group" aria-label="Item type filter">
        <input type="radio" class="btn-check type-filter" name="type_filter" id="all" value="all" checked>
        <label class="btn btn-outline-primary" for="all">
          All <span class="badge bg-primary type-count" data-type="all">0</span>
        </label>

        <input type="radio" class="btn-check type-filter" name="type_filter" id="room" value="room">
        <label class="btn btn-outline-primary" for="room">
          Rooms <span class="badge bg-primary type-count" data-type="room">0</span>
        </label>

        <input type="radio" class="btn-check type-filter" name="type_filter" id="facility" value="facility">
        <label class="btn btn-outline-primary" for="facility">
          Facilities <span class="badge bg-primary type-count" data-type="facility">0</span>
        </label>
      </div>

      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="fas fa-plus me-2"></i>Add New
      </button>
    </div>
  `;

  // Attach event listeners
  document.querySelectorAll(".type-filter").forEach((radio) => {
    radio.addEventListener("change", filterItems);
  });
}

// Setup Edit Form Toggles
function setupEditFormToggles() {
  const editButtons = document.querySelectorAll(".edit-toggle-btn");
  const cancelButtons = document.querySelectorAll(".edit-cancel-btn");

  console.log(
    "Setting up edit form toggles - Edit buttons found:",
    editButtons.length,
    "Cancel buttons found:",
    cancelButtons.length
  );

  // Handle edit toggle buttons
  editButtons.forEach((toggleBtn) => {
    // Remove existing listeners to prevent duplicates
    toggleBtn.removeEventListener("click", handleEditToggle);
    toggleBtn.addEventListener("click", handleEditToggle);
    console.log(
      "Added click listener to edit button for item:",
      toggleBtn.getAttribute("data-item-id")
    );
  });

  // Handle cancel buttons
  cancelButtons.forEach((cancelBtn) => {
    // Remove existing listeners to prevent duplicates
    cancelBtn.removeEventListener("click", handleEditCancel);
    cancelBtn.addEventListener("click", handleEditCancel);
    console.log(
      "Added click listener to cancel button for item:",
      cancelBtn.getAttribute("data-item-id")
    );
  });
}

// Handler functions for better cleanup
function handleEditToggle(e) {
  e.preventDefault();
  console.log("Edit toggle clicked");

  const toggleBtn = e.target.closest(".edit-toggle-btn");
  const itemId = toggleBtn.getAttribute("data-item-id");
  const editFormContainer = document.getElementById(`editForm${itemId}`);

  console.log(
    "Toggle button:",
    toggleBtn,
    "Item ID:",
    itemId,
    "Form container:",
    editFormContainer
  );

  if (editFormContainer) {
    const isHidden =
      editFormContainer.style.display === "none" ||
      editFormContainer.style.display === "";

    console.log("Form is currently hidden:", isHidden);

    if (isHidden) {
      editFormContainer.style.display = "block";
      toggleBtn.innerHTML = '<i class="fas fa-times me-1"></i>Cancel';
      toggleBtn.classList.remove("btn-outline-primary");
      toggleBtn.classList.add("btn-outline-secondary");
      console.log("Edit form shown for item:", itemId);
    } else {
      editFormContainer.style.display = "none";
      toggleBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
      toggleBtn.classList.remove("btn-outline-secondary");
      toggleBtn.classList.add("btn-outline-primary");
      console.log("Edit form hidden for item:", itemId);
    }
  } else {
    console.error("Edit form container not found for item:", itemId);
  }
}

function handleEditCancel(e) {
  e.preventDefault();
  console.log("Edit cancel clicked");

  const cancelBtn = e.target.closest(".edit-cancel-btn");
  const itemId = cancelBtn.getAttribute("data-item-id");
  const editFormContainer = document.getElementById(`editForm${itemId}`);
  const toggleBtn = document.querySelector(
    `[data-item-id="${itemId}"].edit-toggle-btn`
  );

  console.log("Cancel button:", cancelBtn, "Item ID:", itemId);

  if (editFormContainer) {
    editFormContainer.style.display = "none";
    console.log("Edit form hidden for item:", itemId);
  }

  if (toggleBtn) {
    toggleBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
    toggleBtn.classList.remove("btn-outline-secondary");
    toggleBtn.classList.add("btn-outline-primary");
    console.log("Edit button reset for item:", itemId);
  }
}

// Helper function to count items from PHP-rendered DOM
function updateTypeCountsFromDOM() {
  const allItems = document.querySelectorAll('.item-card');
  const roomItems = document.querySelectorAll('.item-card[data-type="room"]');
  const facilityItems = document.querySelectorAll('.item-card[data-type="facility"]');
  
  console.log('Counting items from DOM:', {
    all: allItems.length,
    rooms: roomItems.length,
    facilities: facilityItems.length
  });
  
  // Update badges in filter buttons
  const allBadge = document.querySelector('.type-count[data-type="all"]');
  const roomBadge = document.querySelector('.type-count[data-type="room"]');
  const facilityBadge = document.querySelector('.type-count[data-type="facility"]');
  
  if (allBadge) allBadge.textContent = allItems.length;
  if (roomBadge) roomBadge.textContent = roomItems.length;
  if (facilityBadge) facilityBadge.textContent = facilityItems.length;
}

// Filter function for PHP-rendered items
function filterItemsInRoomsSection() {
  const selectedType = document.querySelector('input[name="type_filter"]:checked');
  if (!selectedType) return;
  
  const selectedValue = selectedType.value;
  const items = document.querySelectorAll('.item-card');
  
  console.log('Filtering items:', selectedValue);
  
  items.forEach(item => {
    const itemType = item.getAttribute('data-type');
    
    if (selectedValue === 'all' || itemType === selectedValue) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });
}


// Booking Type Toggle Function (Global)
function toggleBookingForm() {
  const selectedType = document.querySelector(
    'input[name="bookingType"]:checked'
  );

  if (!selectedType) {
    console.warn("No booking type selected");
    return;
  }

  const selectedValue = selectedType.value;
  const reservationForm = document.getElementById("reservationForm");
  const pencilForm = document.getElementById("pencilForm");

  if (!reservationForm || !pencilForm) {
    console.warn("Booking forms not found");
    return;
  }

  if (selectedValue === "reservation") {
    reservationForm.style.display = "block";
    pencilForm.style.display = "none";
    // Generate receipt number when switching to reservation form
    generateReceiptNumber();
  } else {
    reservationForm.style.display = "none";
    pencilForm.style.display = "block";
  }
}

// Immediately assign to global scope with fallback
window.toggleBookingForm = toggleBookingForm;

// Also create a backup reference in case of scope issues
if (typeof window.toggleBookingForm === "undefined") {
  window.toggleBookingForm = function () {
    return toggleBookingForm.apply(this, arguments);
  };
}

// Booking Form Management
function setupBookingForms() {
  // Initialize booking form display
  toggleBookingForm();

  // Attach listeners to booking type radio buttons
  document.querySelectorAll('input[name="bookingType"]').forEach((radio) => {
    radio.addEventListener("change", toggleBookingForm);
  });

  // Generate initial receipt number
  generateReceiptNumber();
}



// Receipt Number Generation (from guest.js)
async function generateReceiptNumber() {
  try {
    const response = await fetch(
      "database/user_auth.php?action=get_receipt_no"
    );
    const data = await response.json();

    if (data.success) {
      const receiptField =
        document.querySelector('input[name="receipt_no"]') ||
        document.getElementById("receipt_no");
      if (receiptField) {
        receiptField.value = data.receipt_no;
        receiptField.classList.add("is-valid");
        showToast("Receipt number generated: " + data.receipt_no, "success");
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





// Chart.js Initialization
function initializeCharts() {
  // Wait for Chart.js to be fully loaded
  if (typeof Chart === "undefined") {
    setTimeout(initializeCharts, 500);
    return;
  }

  // Bookings Overview Chart (Line Chart)
  const bookingsChartElement = document.getElementById("bookingsChart");
  if (bookingsChartElement) {
    const ctx = bookingsChartElement.getContext("2d");

    // Check if data exists and has content
    const monthlyData = window.monthlyBookingsData || [];

    // Destroy existing chart if it exists
    if (window.bookingsChartInstance) {
      window.bookingsChartInstance.destroy();
    }

    if (monthlyData && monthlyData.length > 0) {
      const labels = monthlyData.map((item) => {
        return item.month || item.label || "Unknown";
      });

      const data = monthlyData.map((item) => {
        return parseInt(item.count) || 0;
      });

      try {
        window.bookingsChartInstance = new Chart(ctx, {
          type: "line",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Monthly Bookings",
                data: data,
                backgroundColor: "rgba(78, 115, 223, 0.1)",
                borderColor: "rgba(78, 115, 223, 1)",
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                pointBorderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 4,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: "top",
              },
              title: {
                display: true,
                text: "Booking Trends",
              },
            },
            scales: {
              x: {
                grid: {
                  display: false,
                },
                title: {
                  display: true,
                  text: "Month",
                },
              },
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1,
                },
                title: {
                  display: true,
                  text: "Number of Bookings",
                },
              },
            },
          },
        });
      } catch (error) {
        console.error("Error creating bookings chart:", error);
      }
    } else {
      // Display "No data" message
      ctx.clearRect(
        0,
        0,
        bookingsChartElement.width,
        bookingsChartElement.height
      );
      ctx.fillStyle = "#6c757d";
      ctx.font = "16px Arial";
      ctx.textAlign = "center";
      ctx.fillText(
        "No booking data available",
        bookingsChartElement.width / 2,
        bookingsChartElement.height / 2
      );
    }
  }

  // Status Distribution Chart (Doughnut Chart)
  const statusChartElement = document.getElementById("statusChart");
  if (statusChartElement) {
    const ctx = statusChartElement.getContext("2d");

    // Check if data exists and has content
    const statusData = window.statusDistributionData || {};

    // Destroy existing chart if it exists
    if (window.statusChartInstance) {
      window.statusChartInstance.destroy();
    }

    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData).map(
      (val) => parseInt(val) || 0
    );
    const hasData = statusValues.some((value) => value > 0);

    if (hasData) {
      try {
        window.statusChartInstance = new Chart(ctx, {
          type: "doughnut",
          data: {
            labels: statusLabels.map(
              (label) => label.charAt(0).toUpperCase() + label.slice(1)
            ),
            datasets: [
              {
                data: statusValues,
                backgroundColor: [
                  "#f6c23e", // pending - yellow
                  "#1cc88a", // approved - green
                  "#36b9cc", // checked_in - info
                  "#5a5c69", // checked_out - secondary
                  "#e74a3b", // cancelled - red
                ],
                borderWidth: 2,
                borderColor: "#ffffff",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "bottom",
                labels: {
                  boxWidth: 12,
                  padding: 15,
                },
              },
              title: {
                display: true,
                text: "Booking Status Distribution",
              },
            },
            cutout: "60%",
          },
        });
      } catch (error) {
        console.error("Error creating status chart:", error);
      }
    } else {
      // Display "No data" message
      ctx.clearRect(0, 0, statusChartElement.width, statusChartElement.height);
      ctx.fillStyle = "#6c757d";
      ctx.font = "16px Arial";
      ctx.textAlign = "center";
      ctx.fillText(
        "No status data available",
        statusChartElement.width / 2,
        statusChartElement.height / 2
      );
    }
  }

  // Legacy chart for backward compatibility
  const legacyChartElement = document.getElementById("reportChart");
  if (legacyChartElement && typeof Chart !== "undefined") {
    const ctx = legacyChartElement.getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May"],
        datasets: [
          {
            label: "Bookings",
            data: [12, 19, 7, 15, 20],
            backgroundColor: "#1abc9c",
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  }
}

// Dashboard Data Management
function setDashboardData(events, monthlyData, statusData, stats) {
  window.dashboardData = {
    bookingEvents: events || [],
    monthlyBookingsData: monthlyData || [],
    statusDistributionData: statusData || {},
    dashboardStats: stats || {},
  };

  // Make variables globally accessible for chart functions
  window.bookingEvents = events || [];
  window.monthlyBookingsData = monthlyData || [];
  window.statusDistributionData = statusData || {};
  window.dashboardStats = stats || {};

  // Wait for DOM to be ready before initializing charts
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(() => {
        initializeCharts();
      }, 100);
    });
  } else {
    // DOM is already ready, initialize charts immediately
    setTimeout(() => {
      initializeCharts();
    }, 100);
  }
}

// Refresh chart with different timeframes (using existing database data for now)
function refreshChart(timeframe, event) {
  // Update active button
  const buttons = document.querySelectorAll(".btn-group .btn");
  buttons.forEach((btn) => btn.classList.remove("active"));
  if (event && event.target) {
    event.target.classList.add("active");
  }

  // Note: Since we removed API calls, we're using the existing monthly data
  // In a real application, you could reload the page with a timeframe parameter
  // or implement server-side filtering

  if (timeframe === "7days" || timeframe === "30days") {
    // For demo purposes, show a subset of data for shorter timeframes
    let filteredData = window.monthlyBookingsData;
    if (timeframe === "7days") {
      filteredData = window.monthlyBookingsData.slice(-7);
    } else if (timeframe === "30days") {
      filteredData = window.monthlyBookingsData.slice(-5);
    }

    // Update the chart with filtered data
    if (window.bookingsChartInstance) {
      window.bookingsChartInstance.data.labels = filteredData.map(
        (item) => item.month
      );
      window.bookingsChartInstance.data.datasets[0].data = filteredData.map(
        (item) => item.count
      );
      window.bookingsChartInstance.update();
    }
  } else if (window.bookingsChartInstance) {
    window.bookingsChartInstance.data.labels = window.monthlyBookingsData.map(
      (item) => item.month
    );
    window.bookingsChartInstance.data.datasets[0].data =
      window.monthlyBookingsData.map((item) => item.count);
    window.bookingsChartInstance.update();
  }

  showToast(`Chart updated to show ${timeframe} view`, "info");
}

// Initialize Rooms Filtering Function
function initializeRoomsFiltering() {
  // Filter buttons functionality
  const filterButtons = document.querySelectorAll(".filter-btn");
  const roomCards = document.querySelectorAll(".item-card");

  if (filterButtons.length > 0) {
    filterButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const filterType = this.dataset.filter;

        // Update active button
        filterButtons.forEach((btn) => btn.classList.remove("active"));
        this.classList.add("active");

        // Filter rooms/facilities
        roomCards.forEach((card) => {
          const cardType = card.dataset.type;

          if (filterType === "all" || cardType === filterType) {
            card.style.display = "block";
            card.classList.remove("d-none");
          } else {
            card.style.display = "none";
            card.classList.add("d-none");
          }
        });
      });
    });
  }

  // Type filter radio buttons (if they exist)
  const typeFilters = document.querySelectorAll(
    'input[name="type_filter"], .type-filter'
  );
  if (typeFilters.length > 0) {
    typeFilters.forEach((filter) => {
      filter.addEventListener("change", function () {
        const selectedType = this.value;

        // Update counts for each type
        updateTypeCounts();

        roomCards.forEach((card) => {
          const cardType = card.dataset.type;

          if (selectedType === "all" || cardType === selectedType) {
            card.style.display = "block";
            card.classList.remove("d-none");
          } else {
            card.style.display = "none";
            card.classList.add("d-none");
          }
        });
      });
    });
  }

  // Update type counts
  updateTypeCounts();
}

// Helper function to update type counts
function updateTypeCounts() {
  const allItems = document.querySelectorAll(".item-card");
  const roomItems = document.querySelectorAll('.item-card[data-type="room"]');
  const facilityItems = document.querySelectorAll(
    '.item-card[data-type="facility"]'
  );

  // Update count badges
  const allCount = document.querySelector('.type-count[data-type="all"]');
  const roomCount = document.querySelector('.type-count[data-type="room"]');
  const facilityCount = document.querySelector(
    '.type-count[data-type="facility"]'
  );

  if (allCount) allCount.textContent = allItems.length;
  if (roomCount) roomCount.textContent = roomItems.length;
  if (facilityCount) facilityCount.textContent = facilityItems.length;
}

// Initialize Rooms Search Function
function initializeRoomsSearch() {
  const searchInput = document.querySelector(
    "#searchItems, #rooms-search, .rooms-search-input"
  );
  const roomCards = document.querySelectorAll(".item-card");

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase().trim();

      roomCards.forEach((card) => {
        const title = card.querySelector(".card-title");
        const description = card.querySelector(".card-text");
        const searchableData = card.dataset.searchable || "";

        let cardText = searchableData;
        if (title) {
          cardText += " " + title.textContent.toLowerCase();
        }
        if (description) {
          cardText += " " + description.textContent.toLowerCase();
        }

        if (searchTerm === "" || cardText.includes(searchTerm)) {
          card.style.display = "block";
          card.classList.remove("d-none");
        } else {
          card.style.display = "none";
          card.classList.add("d-none");
        }
      });
    });
  }
}

// Initialize Edit Forms Function
function initializeEditForms() {
  // Initialize edit form toggles with the correct selectors
  setupEditFormToggles();

  console.log("Edit forms initialized with proper event handlers");
}

// Function to refresh dashboard data
function refreshDashboardData() {
  // Reinitialize the charts with existing data
  initializeCharts();
}

// Global function exports (to maintain compatibility)
// Using try-catch to handle any reference errors gracefully
try {
  window.showSection = showSection;
  window.toggleDarkMode = toggleDarkMode;
  window.filterTable = filterTable;
  window.showToast = showToast;
  window.loadItems = loadItems;
  window.filterItems = filterItems;
  window.toggleBookingForm = toggleBookingForm;
  window.pencilReminder = pencilReminder;
  window.generateReceiptNumber = generateReceiptNumber;

  // Calendar & Items functions
  window.initializeCalendarNavigation = initializeCalendarNavigation;
  window.initializeRoomSearch = initializeRoomSearch;
  window.initializeRoomCalendar = initializeRoomCalendar;
  window.generateRoomEvents = generateRoomEvents;

  // Dashboard data function
  window.setDashboardData = setDashboardData;

  // Rooms management functions
  window.initializeRoomsFiltering = initializeRoomsFiltering;
  window.initializeRoomsSearch = initializeRoomsSearch;
  window.initializeEditForms = initializeEditForms;

  // Chart functions
  window.initializeCharts = initializeCharts;
  window.refreshDashboardData = refreshDashboardData;
} catch (error) {
  console.warn("Error assigning global functions:", error);
} // Feedback Management System
function initializeFeedbackManagement() {
  // Initialize feedback section when it becomes active
  document.addEventListener("sectionChanged", function (e) {
    if (e.detail.section === "feedback") {
      loadFeedbackData();
    }
  });

  // Load feedback data if feedback section is already active
  const feedbackSection = document.getElementById("feedback");
  if (feedbackSection && feedbackSection.classList.contains("active")) {
    loadFeedbackData();
  }
}

async function loadFeedbackData(limit = 50, offset = 0) {
  try {
    // First initialize the feedback table
    await fetch("database/user_auth.php?action=init_feedback_table");

    // Then load the feedback data
    const response = await fetch(
      `database/user_auth.php?action=get_feedback_data&limit=${limit}&offset=${offset}`
    );
    const data = await response.json();

    if (data.success) {
      updateFeedbackStats(data.stats);
      updateFeedbackTable(data.feedback);
      updateRatingChart(data.stats);
      updateFeedbackInsights(data.stats);
    } else {
      console.error("Error loading feedback data:", data.error);
      showFeedbackError(
        "Failed to load feedback data: " + (data.error || "Unknown error")
      );
    }
  } catch (error) {
    console.error("Error fetching feedback data:", error);
    showFeedbackError("Network error while loading feedback");
  }
}

function updateFeedbackStats(stats) {
  document.getElementById("total-feedback").textContent =
    stats.total_feedback || 0;
  document.getElementById("avg-rating").textContent = parseFloat(
    stats.avg_rating || 0
  ).toFixed(1);
  document.getElementById("five-star-count").textContent = stats.five_star || 0;
  document.getElementById("low-rating-count").textContent =
    parseInt(stats.one_star || 0) + parseInt(stats.two_star || 0);
}

function updateFeedbackTable(feedback) {
  const tbody = document.getElementById("feedback-tbody");

  if (!feedback || feedback.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center text-muted py-4">
          <i class="fas fa-comment-slash fa-2x mb-3 opacity-50"></i>
          <br>No feedback received yet
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = feedback
    .map((item) => {
      const stars = generateStarDisplay(item.rating);
      const date = new Date(item.created_at).toLocaleDateString();
      const message = item.message || "No additional comments";

      return `
      <tr>
        <td>
          <div class="d-flex align-items-center">
            <div class="avatar-circle bg-primary text-white me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.8rem;">
              ${item.username.charAt(0).toUpperCase()}
            </div>
            <div>
              <div class="fw-semibold">${escapeHtml(item.username)}</div>
              <small class="text-muted">${escapeHtml(item.email)}</small>
            </div>
          </div>
        </td>
        <td>
          <div class="d-flex align-items-center">
            <div class="star-display me-2">${stars}</div>
            <small class="text-muted">(${item.rating}/5)</small>
          </div>
        </td>
        <td>
          <div class="feedback-message" style="max-width: 300px;">
            ${escapeHtml(message)}
          </div>
        </td>
        <td>
          <small class="text-muted">${date}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary btn-sm" onclick="viewFeedbackDetails(${
              item.id
            })" title="View Details">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="respondToFeedback(${
              item.id
            })" title="Respond">
              <i class="fas fa-reply"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    })
    .join("");
}

function generateStarDisplay(rating) {
  const fullStars = Math.floor(rating);
  const emptyStars = 5 - fullStars;

  return "★".repeat(fullStars) + "☆".repeat(emptyStars);
}

function updateRatingChart(stats) {
  const ctx = document.getElementById("ratingChart");
  if (!ctx) {
    return;
  }

  // Destroy existing chart if it exists
  if (window.ratingChartInstance) {
    window.ratingChartInstance.destroy();
  }

  window.ratingChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["1 Star", "2 Stars", "3 Stars", "4 Stars", "5 Stars"],
      datasets: [
        {
          label: "Number of Reviews",
          data: [
            stats.one_star || 0,
            stats.two_star || 0,
            stats.three_star || 0,
            stats.four_star || 0,
            stats.five_star || 0,
          ],
          backgroundColor: [
            "#dc3545", // Red for 1 star
            "#fd7e14", // Orange for 2 stars
            "#ffc107", // Yellow for 3 stars
            "#20c997", // Teal for 4 stars
            "#28a745", // Green for 5 stars
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
      },
    },
  });
}

function updateFeedbackInsights(stats) {
  const insights = document.getElementById("feedback-insights");
  const totalFeedback = parseInt(stats.total_feedback || 0);
  const avgRating = parseFloat(stats.avg_rating || 0);
  const fiveStarPercent =
    totalFeedback > 0
      ? (((stats.five_star || 0) / totalFeedback) * 100).toFixed(1)
      : 0;
  const lowRatingCount =
    parseInt(stats.one_star || 0) + parseInt(stats.two_star || 0);

  if (totalFeedback === 0) {
    insights.innerHTML = `
      <div class="text-center text-muted">
        <i class="fas fa-star-o fa-2x mb-3 opacity-50"></i>
        <h6>No Feedback Yet</h6>
        <p class="mb-0">Encourage guests to share their experiences!</p>
      </div>
    `;
    return;
  }

  let insightClass = "success";
  let insightIcon = "fa-smile";
  let insightTitle = "Excellent Performance!";
  let insightMessage = "Guests are highly satisfied with their experience.";

  if (avgRating < 3) {
    insightClass = "danger";
    insightIcon = "fa-frown";
    insightTitle = "Needs Improvement";
    insightMessage = "Consider addressing common concerns in feedback.";
  } else if (avgRating < 4) {
    insightClass = "warning";
    insightIcon = "fa-meh";
    insightTitle = "Good but Room for Growth";
    insightMessage = "Focus on enhancing guest satisfaction areas.";
  }

  insights.innerHTML = `
    <div class="text-center">
      <div class="text-${insightClass} mb-3">
        <i class="fas ${insightIcon} fa-3x"></i>
      </div>
      <h6 class="text-${insightClass}">${insightTitle}</h6>
      <p class="mb-3">${insightMessage}</p>
      <div class="row text-center">
        <div class="col-6">
          <h5 class="text-${insightClass} mb-1">${fiveStarPercent}%</h5>
          <small class="text-muted">5-Star Reviews</small>
        </div>
        <div class="col-6">
          <h5 class="text-${
            lowRatingCount > 0 ? "warning" : "success"
          } mb-1">${lowRatingCount}</h5>
          <small class="text-muted">Low Ratings</small>
        </div>
      </div>
    </div>
  `;
}

function showFeedbackError(message) {
  const tbody = document.getElementById("feedback-tbody");
  tbody.innerHTML = `
    <tr>
      <td colspan="5" class="text-center text-danger py-4">
        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
        <br>${message}
      </td>
    </tr>
  `;
}

function refreshFeedback() {
  loadFeedbackData();
}

function exportFeedback() {
  // Implementation for exporting feedback data
  showToast("Export functionality would be implemented here", "info");
}

function viewFeedbackDetails(feedbackId) {
  // Implementation for viewing detailed feedback
  showToast(`View details for feedback ID: ${feedbackId}`, "info");
}

function respondToFeedback(feedbackId) {
  // Implementation for responding to feedback
  showToast(`Respond to feedback ID: ${feedbackId}`, "info");
}

// Export feedback functions globally
window.refreshFeedback = refreshFeedback;
window.exportFeedback = exportFeedback;
window.viewFeedbackDetails = viewFeedbackDetails;
window.respondToFeedback = respondToFeedback;

// Calendar & Items Navigation Functions
function initializeCalendarNavigation() {
  const calendarViewBtn = document.getElementById("calendar-view-btn");
  const roomListBtn = document.getElementById("room-list-btn");
  const calendarContent = document.getElementById("calendar-view-content");
  const roomListContent = document.getElementById("room-list-content");

  if (calendarViewBtn && roomListBtn && calendarContent && roomListContent) {
    // Calendar View Button
    calendarViewBtn.addEventListener("click", function () {
      // Update button states
      calendarViewBtn.classList.add("active");
      roomListBtn.classList.remove("active");

      // Show/hide content
      calendarContent.style.display = "block";
      roomListContent.style.display = "none";

      // Re-render calendar if it exists
      if (window.roomCalendarInstance) {
        setTimeout(() => window.roomCalendarInstance.render(), 100);
      }
    });

    // Room List Button
    roomListBtn.addEventListener("click", function () {
      // Update button states
      roomListBtn.classList.add("active");
      calendarViewBtn.classList.remove("active");

      // Show/hide content
      calendarContent.style.display = "none";
      roomListContent.style.display = "block";
    });
  }
}

function initializeRoomSearch() {
  const searchInput = document.getElementById("room-search");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      const roomItems = document.querySelectorAll(".item-card, .room-item");

      roomItems.forEach((item) => {
        const roomName = item.getAttribute("data-room-name") || "";
        const roomNumber = item.getAttribute("data-room-number") || "";
        const itemType = item.getAttribute("data-item-type") || "";
        const text = item.textContent.toLowerCase();

        const matches =
          text.includes(searchTerm) ||
          roomName.includes(searchTerm) ||
          roomNumber.includes(searchTerm) ||
          itemType.includes(searchTerm);

        if (matches) {
          item.style.display = "";
          item.classList.remove("d-none");
        } else {
          item.style.display = "none";
          item.classList.add("d-none");
        }
      });
    });
  }
}

function initializeRoomCalendar() {
  const calendarEl = document.getElementById("roomCalendar");
  if (!calendarEl || typeof FullCalendar === "undefined") {
    return;
  }

  // Generate room events based on current booking data
  const roomEvents = generateRoomEvents();

  window.roomCalendarInstance = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    events: roomEvents,
    eventDisplay: "block",
    dayMaxEvents: true,
    height: "auto",
    aspectRatio: 1.8,
    eventOverlap: false,
    slotEventOverlap: false,
    displayEventTime: true,
    displayEventEnd: true,
    nowIndicator: true,
    businessHours: {
      daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
      startTime: "08:00",
      endTime: "20:00",
    },
    eventClick: function (info) {
      const { title } = info.event;
      const { itemName, roomNumber, guest, status, checkIn, checkOut } =
        info.event.extendedProps;

      let details = `${title}<br><br>`;
      if (itemName) {
        details += `Item: ${itemName}<br>`;
      }
      if (roomNumber) {
        details += `Room #: ${roomNumber}<br>`;
      }
      if (guest) {
        details += `Guest: ${guest}<br>`;
      }
      if (status) {
        details += `Status: ${status}<br>`;
      }
      if (checkIn) {
        details += `Check-in: ${checkIn}<br>`;
      }
      if (checkOut) {
        details += `Check-out: ${checkOut}<br>`;
      }

      showToast(details, "info");
    },
    dateClick: function (info) {
      // Date click handler can be added here if needed
    },
    eventDidMount: function (info) {
      // Add tooltips or additional styling if needed
      info.el.title = info.event.title;
    },
  });

  window.roomCalendarInstance.render();
}

function generateRoomEvents() {
  // Events will be populated by PHP in the dashboard.php script
  if (typeof window.roomEvents !== "undefined") {
    return window.roomEvents;
  }

  return [];
}

// Global sidebar toggle function
window.toggleSidebar = function () {
  const sidebar = document.querySelector(".sidebar");
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
};

// Promise-based confirmation modal helper
function showConfirmModal(message, options = {}) {
  return new Promise((resolve) => {
    const modalId = 'confirm-modal-' + Date.now();
    const title = options.title || 'Please confirm';
    const confirmText = options.confirmText || 'Confirm';
    const cancelText = options.cancelText || 'Cancel';

    const modalHTML = `
      <div class="modal fade" id="${modalId}" tabindex="-1">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">${title}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">${message.replace(/\n/g,'<br/>')}</div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">${cancelText}</button>
              <button type="button" class="btn btn-primary btn-sm" id="${modalId}-confirm">${confirmText}</button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modalEl = document.getElementById(modalId);
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    const cleanup = () => {
      try { bsModal.hide(); } catch(e) {}
      setTimeout(() => { modalEl.remove(); }, 300);
    };

    modalEl.addEventListener('hidden.bs.modal', function () {
      resolve(false);
      try { modalEl.remove(); } catch(e) {}
    }, { once: true });

    document.getElementById(`${modalId}-confirm`).addEventListener('click', function () {
      resolve(true);
      cleanup();
    }, { once: true });
  });
}

// Booking Management Functions
async function updateBookingStatus(bookingId, newStatus) {
  console.log("updateBookingStatus called with:", bookingId, newStatus);

  if (!bookingId || !newStatus) {
    console.error("Invalid parameters:", { bookingId, newStatus });
    showToast("Invalid booking ID or status", "error");
    return;
  }

  // Show confirmation dialog for certain actions
  const actionMap = {
    approved: "approve",
    confirmed: "approve",
    rejected: "reject",
    cancelled: "cancel",
    checked_in: "checkin",
    checked_out: "checkout",
  };

  const action = actionMap[newStatus] || newStatus;

  // Confirm action
  const confirmMessages = {
    approve: "Are you sure you want to approve this booking?",
    reject: "Are you sure you want to reject this booking?",
    cancel: "Are you sure you want to cancel this booking?",
    checkin: "Confirm guest check-in?",
    checkout: "Confirm guest check-out?",
  };

  // Show inline confirmation modal instead of native confirm()
  if (confirmMessages[action]) {
    const confirmed = await showConfirmModal(confirmMessages[action], { title: 'Please confirm' });
    if (!confirmed) return;
  }

  try {
    // Show loading state - get the button from the onclick event
    const button = window.event?.target || document.activeElement;
    const originalText = button?.innerHTML;
    if (button && button.tagName === "BUTTON") {
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
      button.disabled = true;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append("action", "admin_update_booking");
    formData.append("booking_id", bookingId);
    formData.append("admin_action", action);

    // Send AJAX request
    const response = await fetch("database/user_auth.php", {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      showToast(
        data.message || `Booking updated to ${newStatus} successfully!`,
        "success"
      );

      // Reload the page after a short delay to show updated status
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      throw new Error(data.error || "Unknown error occurred");
    }
  } catch (error) {
    console.error("Error updating booking status:", error);
    showToast(`Error updating booking: ${error.message}`, "error");
  } finally {
    // Restore button state
    if (button && originalText) {
      button.innerHTML = originalText;
      button.disabled = false;
    }
  }
}

// Discount Management Function (SEPARATE from booking approval)
async function updateDiscountStatus(bookingId, discountAction) {
  console.log("updateDiscountStatus called with:", bookingId, discountAction);

  if (!bookingId || !discountAction) {
    console.error("Invalid parameters:", { bookingId, discountAction });
    showToast("Invalid booking ID or discount action", "error");
    return;
  }

  // Confirm action
  const confirmMessages = {
    approve:
      "Are you sure you want to APPROVE this discount application?\n\nNote: This only approves the discount, not the booking itself.",
    reject:
      "Are you sure you want to REJECT this discount application?\n\nNote: The booking can still be approved separately with standard rates.",
  };

  if (confirmMessages[discountAction]) {
    const confirmed = await showConfirmModal(confirmMessages[discountAction], { title: 'Confirm discount action' });
    if (!confirmed) return;
  }

  try {
    // Show loading state
    const button = window.event?.target || document.activeElement;
    const originalText = button?.innerHTML;
    if (button && button.tagName === "BUTTON") {
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
      button.disabled = true;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append("action", "admin_update_discount");
    formData.append("booking_id", bookingId);
    formData.append("discount_action", discountAction);

    // Send AJAX request
    const response = await fetch("database/user_auth.php", {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      const actionText = discountAction === "approve" ? "approved" : "rejected";
      showToast(
        data.message ||
          `Discount ${actionText} successfully! Guest will be notified via email.`,
        "success"
      );

      // Reload the page after a short delay to show updated status
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      throw new Error(data.error || "Unknown error occurred");
    }
  } catch (error) {
    console.error("Error updating discount status:", error);
    showToast(`Error updating discount: ${error.message}`, "error");
  } finally {
    // Restore button state
    if (button && originalText) {
      button.innerHTML = originalText;
      button.disabled = false;
    }
  }
}

// View booking details function
function viewBookingDetails(bookingId) {
  if (!bookingId) {
    showToast("Invalid booking ID", "error");
    return;
  }

  // Find the booking row
  const bookingRow = document.querySelector(
    `tr[data-booking-id="${bookingId}"]`
  );
  if (!bookingRow) {
    showToast("Booking details not found", "error");
    return;
  }

  // Extract details from the row
  // sourcery skip: use-object-destructuring
  const cells = bookingRow.cells;
  let details = '<div class="booking-details-modal">';
  details += "<h6>Booking Information</h6>";

  // Correct mapping based on the actual table structure:
  // 0: Receipt #, 1: Room/Facility, 2: Type, 3: Guest Details, 4: Schedule, 5: Status, 6: Discount Application, 7: Created, 8: Admin Actions
  try {
    if (cells[0]) {
      details += `<p><strong>Receipt #:</strong> ${cells[0].textContent.trim()}</p>`;
    }
    if (cells[1]) {
      details += `<p><strong>Room/Facility:</strong> ${cells[1].textContent.trim()}</p>`;
    }
    if (cells[2]) {
      details += `<p><strong>Type:</strong> ${cells[2].textContent.trim()}</p>`;
    }
    if (cells[3]) {
      details += `<p><strong>Guest Details:</strong> ${cells[3].textContent.trim()}</p>`;
    }
    if (cells[4]) {
      details += `<p><strong>Schedule:</strong> ${cells[4].textContent.trim()}</p>`;
    }
    if (cells[5]) {
      details += `<p><strong>Status:</strong> ${cells[5].textContent.trim()}</p>`;
    }
    if (cells[6]) {
      details += `<p><strong>Discount Application:</strong> ${cells[6].textContent.trim()}</p>`;
    }
    if (cells[7]) {
      details += `<p><strong>Created:</strong> ${cells[7].textContent.trim()}</p>`;
    }
  } catch (error) {
    console.error("Error extracting booking details:", error);
    details += '<p class="text-danger">Error loading booking details</p>';
  }

  details += "</div>";

  // Create and show modal
  const modal = document.createElement("div");
  modal.className = "modal fade";
  modal.id = `booking-details-modal-${bookingId}`;
  modal.innerHTML = `
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-info-circle me-2"></i>Booking Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ${details}
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  try {
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Remove modal after it's hidden
    modal.addEventListener("hidden.bs.modal", () => {
      modal.remove();
    });
  } catch (error) {
    console.error("Error showing modal:", error);
    showToast("Error displaying booking details", "error");
    modal.remove();
  }
}

// Booking filter functions
function filterBookings() {
  try {
    const statusFilter =
      document.getElementById("statusFilter")?.value?.toLowerCase() || "";
    const typeFilter =
      document.getElementById("typeFilter")?.value?.toLowerCase() || "";
    const guestSearch =
      document.getElementById("guestSearch")?.value?.toLowerCase() || "";
    const bookingRows = document.querySelectorAll("#bookingsTable tbody tr");

    if (!bookingRows || bookingRows.length === 0) {
      console.warn("No booking rows found to filter");
      return;
    }

    bookingRows.forEach((row) => {
      try {
        const status = (row.dataset.status || "").toLowerCase();
        const type = (row.dataset.type || "").toLowerCase();
        const guestText = (row.dataset.guest || "").toLowerCase();

        let showRow = true;

        // Status filter
        if (statusFilter && !status.includes(statusFilter)) {
          showRow = false;
        }

        // Type filter
        if (typeFilter && !type.includes(typeFilter)) {
          showRow = false;
        }

        // Guest search
        if (guestSearch && !guestText.includes(guestSearch)) {
          showRow = false;
        }

        // Show/hide row
        if (showRow) {
          row.style.display = "";
          row.classList.remove("d-none");
        } else {
          row.style.display = "none";
          row.classList.add("d-none");
        }
      } catch (rowError) {
        console.error("Error processing booking row:", rowError, row);
      }
    });
  } catch (error) {
    console.error("Error filtering bookings:", error);
    showToast("Error filtering bookings", "error");
  }
}

function resetFilters() {
  try {
    // Reset filter controls
    const statusFilter = document.getElementById("statusFilter");
    const typeFilter = document.getElementById("typeFilter");
    const guestSearch = document.getElementById("guestSearch");

    if (statusFilter) {
      statusFilter.value = "";
    }
    if (typeFilter) {
      typeFilter.value = "";
    }
    if (guestSearch) {
      guestSearch.value = "";
    }

    // Show all rows
    const bookingRows = document.querySelectorAll("#bookingsTable tbody tr");
    bookingRows.forEach((row) => {
      row.style.display = "";
      row.classList.remove("d-none");
    });

    showToast("Filters reset", "info");
  } catch (error) {
    console.error("Error resetting filters:", error);
    showToast("Error resetting filters", "error");
  }
}

// Make functions globally available
window.setDashboardData = setDashboardData;
window.updateBookingStatus = updateBookingStatus;
window.updateDiscountStatus = updateDiscountStatus;
window.viewBookingDetails = viewBookingDetails;
window.filterBookings = filterBookings;
window.resetFilters = resetFilters;

console.log("✅ Global functions exposed:", {
  setDashboardData: typeof window.setDashboardData,
  updateBookingStatus: typeof window.updateBookingStatus,
  viewBookingDetails: typeof window.viewBookingDetails
});

// Debug function to test modal functionality
window.testAddItemModal = function () {
  console.log("Testing Add Item Modal...");
  const modal = document.getElementById("addItemModal");
  if (modal) {
    console.log("Modal element found:", modal);
    try {
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
      console.log("Modal shown successfully");
    } catch (error) {
      console.error("Error showing modal:", error);
    }
  } else {
    console.error("Modal element not found");
  }
};

// Debug function to test edit buttons
window.testEditButtons = function () {
  console.log("Testing Edit Buttons...");
  const editButtons = document.querySelectorAll(".edit-toggle-btn");
  console.log("Found edit buttons:", editButtons.length);
  editButtons.forEach((btn, index) => {
    console.log(
      `Edit button ${index + 1}:`,
      btn,
      "Item ID:",
      btn.getAttribute("data-item-id")
    );
  });

  const editForms = document.querySelectorAll('[id^="editForm"]');
  console.log("Found edit forms:", editForms.length);
  editForms.forEach((form, index) => {
    console.log(`Edit form ${index + 1}:`, form.id);
  });
};
