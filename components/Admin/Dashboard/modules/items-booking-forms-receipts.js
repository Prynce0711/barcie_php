function handleEditCancel(e) {
  e.preventDefault();
  console.log("Edit cancel clicked");

  const cancelBtn = e.target.closest(".edit-cancel-btn");
  const itemId = cancelBtn.getAttribute("data-item-id");
  const editFormContainer = document.getElementById("editForm" + itemId);
  const toggleBtn = document.querySelector(
    `[data-item-id="${itemId}"].edit-toggle-btn`,
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
  const allItems = document.querySelectorAll(".item-card");
  const roomItems = document.querySelectorAll('.item-card[data-type="room"]');
  const facilityItems = document.querySelectorAll(
    '.item-card[data-type="facility"]',
  );

  console.log("Counting items from DOM:", {
    all: allItems.length,
    rooms: roomItems.length,
    facilities: facilityItems.length,
  });

  // Update badges in filter buttons
  const allBadge = document.querySelector('.type-count[data-type="all"]');
  const roomBadge = document.querySelector('.type-count[data-type="room"]');
  const facilityBadge = document.querySelector(
    '.type-count[data-type="facility"]',
  );

  if (allBadge) allBadge.textContent = allItems.length;
  if (roomBadge) roomBadge.textContent = roomItems.length;
  if (facilityBadge) facilityBadge.textContent = facilityItems.length;
}

// Filter function for PHP-rendered items
function filterItemsInRoomsSection() {
  const selectedType = document.querySelector(
    'input[name="type_filter"]:checked',
  );
  if (!selectedType) return;

  const selectedValue = selectedType.value;
  const items = document.querySelectorAll(".item-card");

  console.log("Filtering items:", selectedValue);

  items.forEach((item) => {
    const itemType = item.getAttribute("data-type");

    if (selectedValue === "all" || itemType === selectedValue) {
      item.style.display = "block";
    } else {
      item.style.display = "none";
    }
  });
}

// Booking Type Toggle Function (Global)
function toggleBookingForm() {
  const selectedType = document.querySelector(
    'input[name="bookingType"]:checked',
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
      "database/index.php?endpoint=user_auth&action=get_receipt_no",
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

// Fallback receipt number generator
function generateFallbackReceiptNumber() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");

  const receiptNo = `BARCIE-${year}${month}${day}-${hours}${minutes}${seconds}`;

  const receiptField =
    document.querySelector('input[name="receipt_no"]') ||
    document.getElementById("receipt_no");
  if (receiptField) {
    receiptField.value = receiptNo;
    receiptField.classList.add("is-valid");
    console.log("Generated fallback receipt number:", receiptNo);
  }
}

// Chart.js Initialization

