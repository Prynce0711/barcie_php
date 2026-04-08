function filterItems() {
  const selectedType = document.querySelector(
    'input[name="type_filter"]:checked',
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
          btn.classList.toggle("active", btn.value === e.target.value),
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
  // Check if delegated handler is already installed by rooms-section.js
  if (window.__editFormDelegationInstalled) {
    console.log(
      "Edit form delegation already installed, skipping duplicate setup",
    );
    return;
  }

  const editButtons = document.querySelectorAll(".edit-toggle-btn");
  const cancelButtons = document.querySelectorAll(".edit-cancel-btn");

  console.log(
    "Setting up edit form toggles - Edit buttons found:",
    editButtons.length,
    "Cancel buttons found:",
    cancelButtons.length,
  );

  // Handle edit toggle buttons
  editButtons.forEach((toggleBtn) => {
    // Remove existing listeners to prevent duplicates
    toggleBtn.removeEventListener("click", handleEditToggle);
    toggleBtn.addEventListener("click", handleEditToggle);
    console.log(
      "Added click listener to edit button for item:",
      toggleBtn.getAttribute("data-item-id"),
    );
  });

  // Handle cancel buttons
  cancelButtons.forEach((cancelBtn) => {
    // Remove existing listeners to prevent duplicates
    cancelBtn.removeEventListener("click", handleEditCancel);
    cancelBtn.addEventListener("click", handleEditCancel);
    console.log(
      "Added click listener to cancel button for item:",
      cancelBtn.getAttribute("data-item-id"),
    );
  });
}

// Handler functions for better cleanup
function handleEditToggle(e) {
  e.preventDefault();
  console.log("Edit toggle clicked");

  const toggleBtn = e.target.closest(".edit-toggle-btn");
  const itemId = toggleBtn.getAttribute("data-item-id");
  const editFormContainer = document.getElementById("editForm" + itemId);

  console.log(
    "Toggle button:",
    toggleBtn,
    "Item ID:",
    itemId,
    "Form container:",
    editFormContainer,
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

