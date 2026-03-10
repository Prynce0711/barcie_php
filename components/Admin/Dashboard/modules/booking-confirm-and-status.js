// showConfirmModal is provided by components/Popup/popup-manager.js (window.showConfirmModal = window.showConfirm)

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
    const confirmed = await showConfirmModal(confirmMessages[action], {
      title: "Please confirm",
    });
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
        "success",
      );
      // If the server returned refreshed data, update UI in-place. Otherwise fallback to reload.
      try {
        if (data.roomList && Array.isArray(data.roomList)) {
          // Update global roomList and refresh DOM list
          window.roomList = data.roomList;
          if (typeof window.refreshRoomList === "function") {
            window.refreshRoomList();
          } else {
            console.log("refreshRoomList not available");
          }
        }

        if (data.roomEvents && Array.isArray(data.roomEvents)) {
          // Update global events and refresh main calendar instance
          window.roomEvents = data.roomEvents;
          if (
            window.calendarInstance &&
            typeof window.calendarInstance.removeAllEvents === "function"
          ) {
            try {
              // Remove existing events and add new ones
              window.calendarInstance.removeAllEvents();
              window.calendarInstance.addEventSource(window.roomEvents);
              // some FullCalendar builds require rerender
              if (typeof window.calendarInstance.render === "function")
                window.calendarInstance.render();
              console.log("Main calendar updated with new roomEvents");
            } catch (e) {
              console.warn("Error updating main calendar in-place:", e);
            }
          } else {
            console.log("calendarInstance not available - will reload page");
            setTimeout(() => {
              window.location.reload();
            }, 800);
          }
        }

        // Reinitialize modal calendar if it's open for a room
        if (
          window.currentModalRoomId &&
          typeof initializeRoomModalCalendar === "function"
        ) {
          initializeRoomModalCalendar(window.currentModalRoomId);
        }
      } catch (err) {
        console.error("Error applying live update after booking change:", err);
        // fallback: reload page to ensure consistent state
        setTimeout(() => {
          window.location.reload();
        }, 1200);
      }
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
