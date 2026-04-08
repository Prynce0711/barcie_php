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
    const confirmed = await showConfirmModal(confirmMessages[discountAction], {
      title: "Confirm discount action",
    });
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
        "success",
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

function viewBookingDetails(bookingId) {
  if (!bookingId) {
    showToast("Invalid booking ID", "error");
    return;
  }

  const bookingRow = document.querySelector(
    `tr[data-booking-id="${bookingId}"]`,
  );
  if (!bookingRow) {
    showToast("Booking details not found", "error");
    return;
  }

  const cells = bookingRow.cells;
  let details = '<div class="booking-details-modal">';
  details += "<h6>Booking Information</h6>";

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

    modal.addEventListener("hidden.bs.modal", () => {
      modal.remove();
    });
  } catch (error) {
    console.error("Error showing modal:", error);
    showToast("Error displaying booking details", "error");
    modal.remove();
  }
}
