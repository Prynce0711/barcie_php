<?php /* migrated from Components/Guest/js/guest-rating-calendar.js */ ?>
<script>
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Star Rating System
function initializeStarRating() {
  const starRating = document.getElementById("star-rating");
  const ratingValue = document.getElementById("rating-value");
  const ratingText = document.getElementById("rating-text");
  const submitButton = document.getElementById("submit-feedback");

  if (!starRating) {
    return;
  }

  const stars = starRating.querySelectorAll(".star");
  const ratingTexts = {
    1: "Poor - Needs significant improvement",
    2: "Fair - Below expectations",
    3: "Good - Meets expectations",
    4: "Very Good - Exceeds expectations",
    5: "Excellent - Outstanding experience",
  };

  let currentRating = 0;

  // Add event listeners to stars
  stars.forEach((star, index) => {
    const rating = index + 1;

    // Hover effect
    star.addEventListener("mouseenter", () => {
      highlightStars(rating);
      ratingText.textContent = ratingTexts[rating];
      ratingText.className = "text-primary fw-bold";
    });

    // Click to select rating
    star.addEventListener("click", () => {
      currentRating = rating;
      ratingValue.value = rating;
      updateStarDisplay(rating);
      ratingText.textContent = ratingTexts[rating];
      ratingText.className = "text-success fw-bold";

      // Enable submit button
      submitButton.disabled = false;
      submitButton.className = "btn btn-primary";
    });
  });

  // Reset on mouse leave
  starRating.addEventListener("mouseleave", () => {
    updateStarDisplay(currentRating);
    if (currentRating > 0) {
      ratingText.textContent = ratingTexts[currentRating];
      ratingText.className = "text-success fw-bold";
    } else {
      ratingText.textContent = "Click to rate";
      ratingText.className = "text-muted";
    }
  });

  function highlightStars(rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.add("hover");
        star.classList.remove("active");
      } else {
        star.classList.remove("hover", "active");
      }
    });
  }

  function updateStarDisplay(rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.add("active");
        star.classList.remove("hover");
      } else {
        star.classList.remove("active", "hover");
      }
    });
  }

  // Form submission enhancement
  const feedbackForm = document.getElementById("feedback-form");
  if (feedbackForm) {
    feedbackForm.addEventListener("submit", (e) => {
      if (currentRating === 0) {
        e.preventDefault();
        showToast(
          "Please select a star rating before submitting your feedback.",
          "warning",
        );
        return false;
      }

      // Add loading state
      submitButton.innerHTML =
        '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
      submitButton.disabled = true;

      // Show a success message even before form submission
      setTimeout(() => {
        if (!e.defaultPrevented) {
          const alertDiv = document.createElement("div");
          alertDiv.className = "alert alert-info mt-3";
          alertDiv.innerHTML =
            '<i class="fas fa-clock me-2"></i>Processing your feedback...';
          feedbackForm.appendChild(alertDiv);
        }
      }, 100);
    });
  }

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    if (
      alert.classList.contains("alert-success") ||
      alert.classList.contains("alert-info")
    ) {
      setTimeout(() => {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";
        setTimeout(() => {
          if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
          }
        }, 500);
      }, 5000);
    }
  });
}

// Guest Availability Calendar implementation moved to availability section
function initializeGuestCalendar() {
  console.warn(
    "initializeGuestCalendar moved to components/guest/sections/availability.php",
  );
}

// Keep a small export so other modules can call the stub safely
window.initializeGuestCalendar = initializeGuestCalendar;

// Image Gallery Functions
let galleryImages = [];
let currentGalleryIndex = 0;
let currentZoomLevel = 1;
let galleryReturnItemId = null; // when set, reopen this item details after gallery closes


</script>
