// cSpell:ignore barcie checkin lcuppersonnel lcupstudent LCUP
// Handle missing images gracefully
window.addEventListener("load", function () {
  document.querySelectorAll("img").forEach((img) => {
    img.addEventListener("error", function () {
      if (!this.classList.contains("error-handled")) {
        this.classList.add("error-handled");
        this.src = "public/images/imageBg/barcie_logo.jpg";
        this.alt = "Image not available";
        this.style.opacity = "0.7";
        console.warn(
          "Image failed to load, using fallback:",
          this.dataset.originalSrc || "unknown",
        );
      }
    });
    if (img.src) img.dataset.originalSrc = img.src;
  });
});

// Fallback: ensure a global showPencilSuccessModal exists in case component scripts
// are included in a different order. This stub will show a simple modal and
// attempt a soft refresh similar to the centralized implementation.
if (typeof window.showPencilSuccessModal !== "function") {
  window.showPencilSuccessModal = function (message) {
    try {
      const existing = document.getElementById("pencilSuccessModal");
      if (existing) existing.remove();
      const modalHtml = `
        <div class="modal fade" id="pencilSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Draft Reservation Submitted!</h5>
              </div>
              <div class="modal-body text-center py-4">
                <div class="mb-3"><i class="fas fa-check-circle text-success" style="font-size: 3.5rem;"></i></div>
                <h4 class="text-success mb-2">Success!</h4>
                <p class="mb-3">${typeof escapeHtml === "function" ? escapeHtml(message) : String(message)}</p>
                <p class="small text-muted">Click <strong>Done</strong> to refresh the guest view (soft refresh). Close will keep the page as-is.</p>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="pencilDoneBtn" class="btn btn-success">Done</button>
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML("beforeend", modalHtml);
      const modalEl = document.getElementById("pencilSuccessModal");
      const bsModal = new bootstrap.Modal(modalEl);
      const doneBtn = modalEl.querySelector("#pencilDoneBtn");
      if (doneBtn) {
        doneBtn.addEventListener("click", function () {
          try {
            bsModal.hide();
          } catch (e) {}
          setTimeout(() => {
            try {
              let didSoft = false;
              if (typeof window.loadItems === "function") {
                window.loadItems();
                didSoft = true;
              }
              if (typeof window.loadRooms === "function") {
                window.loadRooms();
                didSoft = true;
              }
              if (typeof window.reloadBookings === "function") {
                window.reloadBookings();
                didSoft = true;
              }
              if (!didSoft) {
                // Intentionally avoid forcing a full reload — keep the page state as-is.
                console.log(
                  "Pencil success fallback: no soft-refresh functions available; not reloading page.",
                );
              }
            } catch (err) {
              console.error("Soft refresh failed; leaving page as-is", err);
            }
          }, 200);
        });
      }
      modalEl.addEventListener("hidden.bs.modal", function () {
        setTimeout(() => {
          try {
            modalEl.remove();
          } catch (e) {}
        }, 200);
      });
      bsModal.show();
    } catch (e) {
      try {
        showToast(
          message || "Draft reservation submitted successfully!",
          "success",
        );
      } catch (err) {}
    }
  };
}

// Booking & feedback form logic
(function () {
  document.addEventListener("DOMContentLoaded", function () {
    const roomSelect = document.getElementById("room_select");
    const checkinInput = document.querySelector('input[name="checkin"]');
    const checkoutInput = document.querySelector('input[name="checkout"]');
    const occupantsInput = document.querySelector('input[name="occupants"]');

    function checkAvailability() {
      if (!roomSelect || !checkinInput || !checkoutInput) return;
      const roomId = roomSelect.value;
      const checkin = checkinInput.value;
      const checkout = checkoutInput.value;
      if (roomId && checkin && checkout) {
        roomSelect.style.borderColor = "#28a745";
        if (new Date(checkin) >= new Date(checkout)) {
          checkinInput.style.borderColor = "#dc3545";
          checkoutInput.style.borderColor = "#dc3545";
        } else {
          checkinInput.style.borderColor = "#28a745";
          checkoutInput.style.borderColor = "#28a745";
        }
      }
    }

    function validateCapacity() {
      if (!roomSelect || !occupantsInput) return;
      const selectedOption = roomSelect.options[roomSelect.selectedIndex];
      if (selectedOption && occupantsInput.value) {
        // selectedOption.text is a string, do not destructure it. Use a safe fallback for different browsers
        const text =
          selectedOption.text ||
          selectedOption.textContent ||
          selectedOption.innerText ||
          "";
        const match = text.match(/(\d+)\s+persons/);
        if (match) {
          const capacity = parseInt(match[1], 10);
          const occupants = parseInt(occupantsInput.value, 10);
          if (occupants > capacity) {
            occupantsInput.style.borderColor = "#dc3545";
            occupantsInput.title = `Maximum capacity is ${capacity} persons`;
          } else {
            occupantsInput.style.borderColor = "#28a745";
            occupantsInput.title = "";
          }
        }
      }
    }

    if (roomSelect) roomSelect.addEventListener("change", checkAvailability);
    if (checkinInput)
      checkinInput.addEventListener("change", checkAvailability);
    if (checkoutInput)
      checkoutInput.addEventListener("change", checkAvailability);
    if (occupantsInput)
      occupantsInput.addEventListener("input", validateCapacity);

    const reservationForm = document.getElementById("reservationForm");
    const pencilForm = document.getElementById("pencilForm");

    function handleFormSubmission(form, buttonId) {
      const submitBtn = document.getElementById(buttonId);
      if (!submitBtn) return;
      const originalHtml = submitBtn.innerHTML;
      const requiredFields = form.querySelectorAll("[required]");
      let isValid = true;
      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          field.style.borderColor = "#dc3545";
          isValid = false;
        } else {
          field.style.borderColor = "#28a745";
        }
      });
      if (!isValid) {
        showAlert("Please fill in all required fields.", "danger");
        return;
      }
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
      submitBtn.disabled = true;
      const formData = new FormData(form);
      const urlEncodedData = new URLSearchParams(formData).toString();
      const isPencilBooking = buttonId === "pencilSubmitBtn";
      fetch("database/user_auth.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: urlEncodedData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            if (isPencilBooking) {
              // For pencil bookings, show success modal — reload only when user clicks Done
              showPencilSuccessModal(
                data.message ||
                  "Draft reservation submitted successfully! Please check your email for the conversion link.",
              );
            } else {
              showAlert(
                data.message || "Booking submitted successfully!",
                "success",
              );
              form.reset();
              form
                .querySelectorAll("input, select, textarea")
                .forEach((field) => (field.style.borderColor = ""));
            }
          } else {
            throw new Error(data.error || "Unknown error occurred");
          }
        })
        .catch((err) => {
          console.error("Error:", err);
          showAlert(
            err.message || "Failed to submit booking. Please try again.",
            "danger",
          );
        })
        .finally(() => {
          if (!isPencilBooking) {
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
          }
        });
    }

    function showAlert(message, type = "info") {
      const alertClass = `alert-${type}`;
      const iconClass =
        type === "success"
          ? "check-circle"
          : type === "danger"
            ? "exclamation-triangle"
            : "info-circle";
      const alertEl = document.createElement("div");
      alertEl.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
      alertEl.style.top = "20px";
      alertEl.style.right = "20px";
      alertEl.style.zIndex = "9999";
      alertEl.style.maxWidth = "400px";
      alertEl.innerHTML = `<i class="fas fa-${iconClass} me-2"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
      document.body.appendChild(alertEl);
      setTimeout(() => {
        if (alertEl.parentNode) alertEl.remove();
      }, 5000);
    }

    // showPencilSuccessModal is provided centrally in `components/guest/sections/pencil_booking.php`.
    // This file will call `showPencilSuccessModal(message)` when a pencil booking succeeds.

    if (reservationForm) {
      reservationForm.addEventListener("submit", function (e) {
        e.preventDefault();
        handleFormSubmission(this, "reservationSubmitBtn");
      });
    }
    if (pencilForm) {
      pencilForm.addEventListener("submit", function (e) {
        e.preventDefault();
        if (!window.pencilReminder || window.pencilReminder() !== false)
          handleFormSubmission(this, "pencilSubmitBtn");
      });
    }

    const feedbackForm = document.getElementById("feedback-form");
    const submitFeedbackBtn = document.getElementById("submit-feedback");
    if (feedbackForm && submitFeedbackBtn) {
      feedbackForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const rating = document.getElementById("rating-value").value;
        if (!rating) {
          showAlert("Please select a star rating.", "danger");
          return;
        }
        const originalHtml = submitFeedbackBtn.innerHTML;
        submitFeedbackBtn.innerHTML =
          '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        submitFeedbackBtn.disabled = true;
        const formData = new FormData(this);
        const urlEncodedData = new URLSearchParams(formData).toString();
        fetch("database/user_auth.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: urlEncodedData,
        })
          .then((r) => r.json())
          .then((data) => {
            if (data.success) {
              showAlert(
                data.message || "Feedback submitted successfully!",
                "success",
              );
              this.reset();
              document.getElementById("rating-value").value = "";
              document
                .querySelectorAll(".star")
                .forEach((star) => star.classList.remove("active"));
              document.getElementById("rating-text").textContent =
                "Click to rate";
              submitFeedbackBtn.disabled = true;
            } else {
              throw new Error(data.error || "Unknown error occurred");
            }
          })
          .catch((err) => {
            console.error("Error:", err);
            showAlert(
              err.message || "Failed to submit feedback. Please try again.",
              "danger",
            );
          })
          .finally(() => {
            submitFeedbackBtn.innerHTML = originalHtml;
            if (document.getElementById("rating-value").value)
              submitFeedbackBtn.disabled = false;
          });
      });
    }

    // Discount fields show/hide
    const discountType = document.getElementById("discount_type");
    const proofSection = document.getElementById("discount_proof_section");
    const detailsSection = document.getElementById("discount_details_section");
    const infoText = document.getElementById("discount_info_text");
    if (discountType) {
      discountType.addEventListener("change", function () {
        if (this.value === "") {
          proofSection.style.display = "none";
          detailsSection.style.display = "none";
          infoText.style.display = "none";
        } else {
          proofSection.style.display = "";
          detailsSection.style.display = "";
          infoText.style.display = "";
          if (this.value === "pwd_senior")
            infoText.innerHTML =
              "<b>20% Discount</b> for PWD/Senior Citizens. Please upload a valid government-issued ID.";
          else if (this.value === "lcuppersonnel")
            infoText.innerHTML =
              "<b>10% Discount</b> for LCUP Personnel. Please upload your personnel ID or certificate.";
          else if (this.value === "lcupstudent")
            infoText.innerHTML =
              "<b>7% Discount</b> for LCUP Students/Alumni. Please upload your student/alumni ID.";
        }
      });
    }
  });
})();
