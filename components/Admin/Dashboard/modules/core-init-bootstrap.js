// Enhanced Dashboard JavaScript with Bootstrap Integration
console.log("ðŸ“„ dashboard-bootstrap.js file is loading...");

// Preserve original functionality while adding Bootstrap enhancements
document.addEventListener("DOMContentLoaded", function () {
  console.log("ðŸš€ DOMContentLoaded fired - starting initialization...");
  // Add a small delay to ensure all elements are ready
  setTimeout(() => {
    console.log("â±ï¸ Delay complete - calling initializeDashboard()...");
    initializeDashboard();
  }, 100);
});

console.log("ðŸ“„ dashboard-bootstrap.js event listeners registered");

function initializeDashboard() {
  console.log("Dashboard initialization started...");
  console.log("ðŸ“ Checking DOM elements...");
  console.log(
    "  - Sidebar links:",
    document.querySelectorAll(".nav-link-custom").length,
  );
  console.log(
    "  - Content sections:",
    document.querySelectorAll(".content-section").length,
  );

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
  if (typeof initializeBookingsFiltering === "function") {
    initializeBookingsFiltering();
  }
  if (typeof initializeBookingsActions === "function") {
    initializeBookingsActions();
  }

  // Initialize edit forms and rooms functionality after a short delay
  setTimeout(() => {
    console.log("ðŸ”§ Initializing edit forms and rooms functionality...");

    // Count items first
    updateTypeCountsFromDOM();

    // Initialize edit forms
    initializeEditForms();

    // Initialize rooms-specific functionality
    if (typeof initializeRoomsFiltering === "function") {
      initializeRoomsFiltering();
    }
    if (typeof initializeRoomsSearch === "function") {
      initializeRoomsSearch();
    }

    console.log("âœ… Rooms and facilities functionality initialized");

    // Log what we found
    const editButtons = document.querySelectorAll(".edit-toggle-btn");
    const editForms = document.querySelectorAll('[id^="editForm"]');
    console.log(
      "Found " +
        editButtons.length +
        " edit buttons and " +
        editForms.length +
        " edit forms",
    );
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
  if (typeof bootstrap === "undefined") {
    console.error("Bootstrap is not loaded!");
    return;
  }

  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  console.log(`Initialized ${tooltipTriggerList.length} tooltips`);

  // Initialize popovers
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
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
    console.log("âœ“ Add Item Modal found and ready");
  } else {
    console.error("âœ— Add Item Modal NOT found!");
  }

  // Verify the floating button
  const floatingBtn = document.querySelector(".floating-add-btn");
  if (floatingBtn) {
    console.log("âœ“ Floating Add Button found");
    console.log("Button attributes:", {
      toggle: floatingBtn.getAttribute("data-bs-toggle"),
      target: floatingBtn.getAttribute("data-bs-target"),
    });
  } else {
    console.error("âœ— Floating Add Button NOT found!");
  }

  // Verify delete modals
  const deleteModals = document.querySelectorAll('[id^="deleteModal"]');
  console.log(`Found ${deleteModals.length} delete modals`);

  console.log("Bootstrap components setup complete!");
}

// Mobile Menu Setup
