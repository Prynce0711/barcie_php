<?php /* migrated from Components/Guest/js/guest-booking-flow.js */ ?>
<script>
  function redirectToBooking(itemId) {
    console.log("Redirecting to booking for item ID:", itemId);

    const item = window.allItems.find((item) => item.id == itemId);

    if (!item) {
      console.error("Item not found:", itemId);
      showToast("Item not found for booking", "error");
      return;
    }

    console.log("Found item for booking:", item);

    // Find and activate the booking button in sidebar
    const bookingButton = document.querySelector(
      "button[onclick*=\"showSection('booking')\"]",
    );
    console.log("Booking button found:", bookingButton);

    // Switch to booking section (correct section ID, don't save to sessionStorage)
    console.log("Switching to booking section...");
    showSection("booking", bookingButton, false);

    // Pre-fill booking form with selected item
    setTimeout(() => {
      console.log("Pre-filling booking form...");
      prefillBookingForm(item);
      showToast(`Ready to book ${item.name}`, "success");
    }, 500); // Increased timeout to ensure section switch completes
  }

  // Pre-fill booking form with selected item
  function prefillBookingForm(item) {
    console.log("Pre-filling booking form with item:", item);

    // Set the correct booking type based on item type
    if (item.item_type === "facility") {
      // Select pencil booking for facilities
      const pencilRadio = document.querySelector(
        'input[name="bookingType"][value="pencil"]',
      );
      if (pencilRadio) {
        pencilRadio.checked = true;
        toggleBookingForm(); // Show pencil form
      }
    } else {
      // Select reservation for rooms
      const reservationRadio = document.querySelector(
        'input[name="bookingType"][value="reservation"]',
      );
      if (reservationRadio) {
        reservationRadio.checked = true;
        toggleBookingForm(); // Show reservation form
      }
    }

    // Wait a moment for form to be visible, then set the room/facility selection
    setTimeout(() => {
      // Prefer the actual select by id. If not found, fall back to a select with name="room_id".
      // Avoid matching hidden inputs (e.g. feedbackRoomId) that also use name="room_id".
      const roomSelect =
        document.getElementById("room_select") ||
        document.querySelector('select[name="room_id"]');
      if (roomSelect) {
        // Set the selected room/facility
        roomSelect.value = item.id;
        console.log("Set room selection to:", item.id, item.name);

        // Trigger change event to update any dependent fields
        roomSelect.dispatchEvent(new Event("change", { bubbles: true }));

        // Add visual feedback
        roomSelect.style.border = "2px solid #28a745";
        setTimeout(() => {
          roomSelect.style.border = "";
        }, 2000);
      } else {
        console.warn("Room select dropdown not found");
      }

      // Scroll to the booking form
      const activeForm =
        item.item_type === "facility"
          ? document.querySelector("#pencilForm")
          : document.querySelector("#reservationForm");

      if (activeForm) {
        activeForm.style.border = "2px solid #28a745";
        activeForm.style.borderRadius = "0.5rem";
        activeForm.scrollIntoView({ behavior: "smooth", block: "center" });

        // Remove highlight after 3 seconds
        setTimeout(() => {
          activeForm.style.border = "";
          activeForm.style.borderRadius = "";
        }, 3000);

        // Focus on the first input field
        const firstInput = activeForm.querySelector(
          'input:not([type="hidden"]):not([readonly]), select, textarea',
        );
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
      showToast(message, "warning");
    }
    return true;
  }

  function reservationReminder() {
    const message =
      "Reminder: We only allow one (1) week to pencil book. If we have not heard back from you after one week, your reservation will become null and void and deleted from our system. CONFIRMED ROOM RESERVATION IS NON-REFUNDABLE.";

    if (typeof showToast === "function") {
      showToast(message, "warning");
    } else {
      showToast(message, "warning");
    }
    return true;
  }

  // Keyboard Navigation (from guest-enhanced.js)
  function addKeyboardNavigation() {
    const sidebarButtons = document.querySelectorAll(
      ".sidebar-guest button, .sidebar-guest a",
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

</script>