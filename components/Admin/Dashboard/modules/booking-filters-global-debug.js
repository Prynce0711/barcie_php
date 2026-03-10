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

        if (statusFilter && !status.includes(statusFilter)) {
          showRow = false;
        }

        if (typeFilter && !type.includes(typeFilter)) {
          showRow = false;
        }

        if (guestSearch && !guestText.includes(guestSearch)) {
          showRow = false;
        }

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

window.setDashboardData = setDashboardData;
window.updateBookingStatus = updateBookingStatus;
window.updateDiscountStatus = updateDiscountStatus;
window.viewBookingDetails = viewBookingDetails;
window.filterBookings = filterBookings;
window.resetFilters = resetFilters;

console.log("âœ… Global functions exposed:", {
  setDashboardData: typeof window.setDashboardData,
  updateBookingStatus: typeof window.updateBookingStatus,
  viewBookingDetails: typeof window.viewBookingDetails,
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
      btn.getAttribute("data-item-id"),
    );
  });

  const editForms = document.querySelectorAll('[id^="editForm"]');
  console.log("Found edit forms:", editForms.length);
  editForms.forEach((form, index) => {
    console.log(`Edit form ${index + 1}:`, form.id);
  });
};
