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
  initializeChatSystem();
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
      filterItems();
    }
  } catch (error) {
    console.error("Error loading items:", error);
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

// Initialize Guest Chat System
function initializeChatSystem() {
  const chatForm = document.getElementById("chat-form");
  const chatInput = document.getElementById("chat-input");

  // Load initial messages
  loadChatMessages();

  // Set up periodic refresh for real-time updates
  setInterval(() => {
    loadChatMessages();
    updateUnreadCount();
  }, 3000); // Refresh every 3 seconds

  // Handle message sending
  if (chatForm && chatInput) {
    chatForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const message = chatInput.value.trim();

      if (message) {
        sendChatMessage(message);
        chatInput.value = "";
      }
    });
  }

  // Initial unread count check
  updateUnreadCount();
}

// Load chat messages for guest
async function loadChatMessages() {
  try {
    // Get user ID from PHP session
    const userIdElement = document.querySelector('meta[name="user-id"]');
    const userId = userIdElement ? userIdElement.content : 0;

    if (userId <= 0) {
      console.error("User ID not found");
      return;
    }

    const response = await fetch(
      `database/user_auth.php?action=get_chat_messages&user_id=${userId}&user_type=guest&other_user_id=1&other_user_type=admin`
    );
    const data = await response.json();

    if (data.success) {
      displayChatMessages(data.messages);
    } else {
      console.error("Error loading messages:", data.error);
    }
  } catch (error) {
    console.error("Network error loading messages:", error);
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

// Send chat message from guest
async function sendChatMessage(message) {
  try {
    const userIdElement = document.querySelector('meta[name="user-id"]');
    const userId = userIdElement ? userIdElement.content : 0;

    if (userId <= 0) {
      showToast("Error: User not authenticated", "error");
      return;
    }

    const formData = new FormData();
    formData.append("action", "send_chat_message");
    formData.append("sender_id", userId.toString());
    formData.append("sender_type", "guest");
    formData.append("receiver_id", "1"); // Admin ID
    formData.append("receiver_type", "admin");
    formData.append("message", message);

    const response = await fetch("database/user_auth.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      // Reload messages to show the new message
      loadChatMessages();
      showToast("Message sent successfully", "success");
    } else {
      showToast("Error sending message: " + data.error, "error");
    }
  } catch (error) {
    console.error("Network error sending message:", error);
    showToast("Network error sending message", "error");
  }
}

// Send quick message (for quick help buttons)
function sendQuickMessage(message) {
  const chatInput = document.getElementById("chat-input");
  if (chatInput) {
    chatInput.value = message;
    chatInput.focus();
  }
}

// Update unread message count
async function updateUnreadCount() {
  try {
    const userIdElement = document.querySelector('meta[name="user-id"]');
    const userId = userIdElement ? userIdElement.content : 0;

    if (userId <= 0) {
      return;
    }

    const response = await fetch(
      `database/user_auth.php?action=get_unread_count&user_id=${userId}&user_type=guest`
    );
    const data = await response.json();

    if (data.success) {
      const unreadCount = data.unread_count;
      const unreadBadge = document.getElementById("unread-count");

      if (unreadBadge) {
        if (unreadCount > 0) {
          unreadBadge.textContent = unreadCount;
          unreadBadge.style.display = "inline";
        } else {
          unreadBadge.style.display = "none";
        }
      }
    }
  } catch (error) {
    console.error("Error updating unread count:", error);
  }
}

// Export new functions for global access
window.sendQuickMessage = sendQuickMessage;
window.initializeChatSystem = initializeChatSystem;
