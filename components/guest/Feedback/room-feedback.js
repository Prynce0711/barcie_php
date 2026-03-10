/**
 * Room Feedback System
 * Handles room reviews, ratings, and feedback modal with Google Sign-In
 */

let currentRoomForFeedback = null;
// Cache reviews per room to avoid refetch on filter
let roomReviewsCache = {};
// Google Sign-In state
let googleUser = null;

// Google Sign-In Callback
function handleGoogleSignIn(response) {
  // Decode the JWT token
  const responsePayload = parseJwt(response.credential);

  googleUser = {
    id: responsePayload.sub,
    email: responsePayload.email,
    name: responsePayload.name,
    picture: responsePayload.picture,
  };

  // Hide Google Sign-In option and name input
  const signInOption = document.getElementById("googleSignInOption");
  if (signInOption) signInOption.style.display = "none";

  const nameInputSection = document.getElementById("nameInputSection");
  if (nameInputSection) nameInputSection.style.display = "none";

  // Show signed in user info
  const signedInInfo = document.getElementById("signedInUserInfo");
  if (signedInInfo) signedInInfo.style.display = "block";

  // Update user info
  document.getElementById("feedbackGoogleId").value = googleUser.id;
  document.getElementById("feedbackGoogleEmail").value = googleUser.email;
  document.getElementById("feedbackGuestName").value = googleUser.name; // Store name for submission
  document.getElementById("userGoogleName").textContent = googleUser.name;
  document.getElementById("userGoogleEmail").textContent = googleUser.email;

  if (googleUser.picture) {
    const photoEl = document.getElementById("userGooglePhoto");
    photoEl.src = googleUser.picture;
    photoEl.style.display = "block";
  }

  // Re-check submit button state
  if (typeof updateSubmitState === "function") {
    updateSubmitState();
  }

  console.log("Google Sign-In successful:", googleUser.name);
}

function parseJwt(token) {
  const base64Url = token.split(".")[1];
  const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
  const jsonPayload = decodeURIComponent(
    atob(base64)
      .split("")
      .map(function (c) {
        return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
      })
      .join(""),
  );
  return JSON.parse(jsonPayload);
}

function signOutGoogle() {
  googleUser = null;

  // Show Google Sign-In option and name input
  const signInOption = document.getElementById("googleSignInOption");
  if (signInOption) signInOption.style.display = "block";

  const nameInputSection = document.getElementById("nameInputSection");
  if (nameInputSection) nameInputSection.style.display = "block";

  // Hide signed in user info
  const signedInInfo = document.getElementById("signedInUserInfo");
  if (signedInInfo) signedInInfo.style.display = "none";

  // Clear hidden fields
  document.getElementById("feedbackGoogleId").value = "";
  document.getElementById("feedbackGoogleEmail").value = "";

  console.log("Signed out from Google");
}

// Initialize room feedback system
document.addEventListener("DOMContentLoaded", function () {
  initializeRoomFeedbackSystem();
});

function initializeRoomFeedbackSystem() {
  // Star rating input functionality
  const starRatingContainer = document.getElementById("feedbackStarRating");
  const ratingValueInput = document.getElementById("feedbackRatingValue");
  const ratingText = document.getElementById("feedbackRatingText");
  const submitBtn = document.getElementById("submitRoomFeedback");

  if (starRatingContainer) {
    const stars = starRatingContainer.querySelectorAll(".star-input");

    stars.forEach((star, index) => {
      star.addEventListener("click", function () {
        const rating = parseInt(this.dataset.rating);
        ratingValueInput.value = rating;

        // Update star display
        stars.forEach((s, i) => {
          if (i < rating) {
            s.classList.add("active");
            s.querySelector("i").classList.remove("far");
            s.querySelector("i").classList.add("fas");
          } else {
            s.classList.remove("active");
            s.querySelector("i").classList.remove("fas");
            s.querySelector("i").classList.add("far");
          }
        });

        // Update rating text
        const ratingTexts = [
          "",
          "Poor",
          "Fair",
          "Good",
          "Very Good",
          "Excellent",
        ];
        ratingText.textContent = ratingTexts[rating];
        ratingText.style.color = "#ffc107";

        // Enable submit button
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      });

      // Hover effect
      star.addEventListener("mouseenter", function () {
        const rating = parseInt(this.dataset.rating);
        stars.forEach((s, i) => {
          if (i < rating) {
            s.querySelector("i").classList.remove("far");
            s.querySelector("i").classList.add("fas");
          }
        });
      });
    });

    starRatingContainer.addEventListener("mouseleave", function () {
      const currentRating = parseInt(ratingValueInput.value) || 0;
      stars.forEach((s, i) => {
        if (i < currentRating) {
          s.querySelector("i").classList.remove("far");
          s.querySelector("i").classList.add("fas");
        } else {
          s.querySelector("i").classList.remove("fas");
          s.querySelector("i").classList.add("far");
        }
      });
    });
  }

  // Form submission
  const feedbackForm = document.getElementById("roomFeedbackForm");
  if (feedbackForm) {
    feedbackForm.addEventListener("submit", function (e) {
      e.preventDefault();
      submitRoomFeedback();
    });
  }

  function updateSubmitState() {
    const submitBtn = document.getElementById("submitRoomFeedback");
    const rating = document.getElementById("feedbackRatingValue").value;
    const googleId = document.getElementById("feedbackGoogleId").value;

    if (!submitBtn) return;

    // Enable submit button only when rating is selected AND user is signed in with Google
    if (rating && googleId) {
      submitBtn.disabled = false;
    } else {
      submitBtn.disabled = true;
    }
  }

  // Add event delegation for dynamically created buttons
  document.addEventListener("click", function (e) {
    if (e.target.closest(".btn-leave-review")) {
      const card = e.target.closest(".room-card-col");
      if (card) {
        const roomId = card.dataset.itemId;
        const roomName = card.querySelector(".room-title").textContent;
        openRoomFeedbackModal(roomId, roomName);
      }
    }

    // Handle Write Review button in modal
    if (e.target.closest("#writeReviewBtn")) {
      // Get the current room from the modal
      const modalTitle = document.getElementById("roomDetailsLabel");
      if (modalTitle && currentRoomForFeedback) {
        const roomName = modalTitle.textContent
          .replace("Room Details", "")
          .trim();
        openRoomFeedbackModal(currentRoomForFeedback, roomName);
      } else {
        openRoomFeedbackModal(currentRoomForFeedback);
      }
    }

    if (e.target.closest(".btn-view-details")) {
      const card = e.target.closest(".room-card-col");
      if (card) {
        const roomId = card.dataset.itemId;
        openRoomDetailsModal(roomId);
      }
    }
  });
}

function openRoomFeedbackModal(roomId, roomName) {
  console.log("openRoomFeedbackModal called with:", roomId, roomName);

  if (!roomId) {
    roomId = currentRoomForFeedback;
  }

  currentRoomForFeedback = roomId;

  // Find the modal element
  const modalElement = document.getElementById("roomFeedbackModal");
  if (!modalElement) {
    console.error("Modal element #roomFeedbackModal not found!");
    return;
  }

  // Check if Bootstrap is available
  if (typeof bootstrap === "undefined") {
    console.error("Bootstrap is not loaded!");
    return;
  }

  // Set room ID
  const roomIdInput = document.getElementById("feedbackRoomId");
  if (roomIdInput) {
    roomIdInput.value = roomId;
  }

  // Update modal title if room name is provided
  const labelElement = document.getElementById("roomFeedbackLabel");
  if (labelElement && roomName) {
    labelElement.innerHTML = `<i class="fas fa-star me-2"></i>Review: ${roomName}`;
  }

  // Reset form if it exists
  const formElement = document.getElementById("roomFeedbackForm");
  if (formElement) {
    formElement.reset();
  }

  const ratingValueInput = document.getElementById("feedbackRatingValue");
  if (ratingValueInput) {
    ratingValueInput.value = "";
  }

  const ratingText = document.getElementById("feedbackRatingText");
  if (ratingText) {
    ratingText.textContent = "Click to rate";
    ratingText.style.color = "";
  }

  const submitBtn = document.getElementById("submitRoomFeedback");
  if (submitBtn) {
    submitBtn.disabled = true;
  }

  // Reset stars
  document.querySelectorAll(".star-input").forEach((star) => {
    star.classList.remove("active");
    const icon = star.querySelector("i");
    if (icon) {
      icon.classList.remove("fas");
      icon.classList.add("far");
    }
  });

  // Create and show modal
  try {
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    console.log("Modal shown successfully");
  } catch (error) {
    console.error("Error showing modal:", error);
  }
}

function submitRoomFeedback() {
  const form = document.getElementById("roomFeedbackForm");
  const submitBtn = document.getElementById("submitRoomFeedback");
  const rating = document.getElementById("feedbackRatingValue").value;
  const googleId = document.getElementById("feedbackGoogleId")
    ? document.getElementById("feedbackGoogleId").value
    : "";

  if (!rating) {
    showAlert("Please select a star rating", "danger");
    return;
  }

  if (!googleId) {
    showAlert("Please sign in with Google to submit your review.", "danger");
    return;
  }

  const originalHtml = submitBtn.innerHTML;
  submitBtn.innerHTML =
    '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
  submitBtn.disabled = true;

  const formData = new FormData(form);
  // Disable form controls while submitting
  const controls = form.querySelectorAll("input, textarea, button, select");
  controls.forEach((el) => (el.disabled = true));

  fetch("database/UserAuth/user_auth.php", {
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert(
          "Thank you for your review! Redirecting to Rooms & Facilities...",
          "success",
        );

        // Close the feedback modal and redirect back to the rooms section so the guest sees their review in context
        try {
          const modalEl = document.getElementById("roomFeedbackModal");
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();
        } catch (e) {
          /* ignore */
        }

        // Short delay to allow modal to close smoothly, then navigate to rooms
        setTimeout(() => {
          // Assuming this script runs on Guest.php; navigate to the rooms anchor
          window.location.href = "Guest.php#rooms";
        }, 300);
      } else {
        throw new Error(data.error || "Failed to submit review");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert(
        error.message || "Failed to submit review. Please try again.",
        "danger",
      );
    })
    .finally(() => {
      // Re-enable controls and restore button
      controls.forEach((el) => (el.disabled = false));
      submitBtn.innerHTML = originalHtml;
      // Re-evaluate submit enabled state
      const hasRating = document.getElementById("feedbackRatingValue").value;
      const anonCheckedNow = document.getElementById("feedbackAnonymous")
        ? document.getElementById("feedbackAnonymous").checked
        : false;
      const nameFilledNow = document.getElementById("feedbackGuestName")
        ? document.getElementById("feedbackGuestName").value.trim() !== ""
        : false;
      if (hasRating && (anonCheckedNow || nameFilledNow)) {
        submitBtn.disabled = false;
      } else {
        submitBtn.disabled = true;
      }
    });
}

function openRoomDetailsModal(roomId) {
  fetch(`api/items.php?id=${roomId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.item) {
        const item = data.item;

        // Update modal content
        document.getElementById("roomDetailsLabel").textContent = item.name;
        document.getElementById("modalRoomPrice").textContent =
          `₱${parseFloat(item.price).toLocaleString()}`;
        document.getElementById("modalRoomCapacity").textContent =
          item.capacity || "N/A";

        // Hide or show beds info based on availability
        const bedsElement = document.getElementById("modalRoomBeds");
        const bedsContainer = bedsElement ? bedsElement.closest(".mb-3") : null;
        if (item.beds) {
          if (bedsElement) bedsElement.textContent = item.beds;
          if (bedsContainer) bedsContainer.style.display = "";
        } else {
          if (bedsContainer) bedsContainer.style.display = "none";
        }

        document.getElementById("modalRoomType").textContent =
          item.item_type || "Room";
        document.getElementById("modalRoomType").className =
          `badge ${item.item_type === "room" ? "bg-primary" : "bg-success"}`;

        // Set image
        const images = item.images ? JSON.parse(item.images) : [];
        const imageSrc =
          images.length > 0
            ? images[0]
            : "assets/images/imageBg/barcie_logo.jpg";
        document.getElementById("modalRoomImage").src = imageSrc;
        document.getElementById("modalRoomImage").alt = item.name;

        // Update rating display
        updateModalRating(item.average_rating || 0, item.total_reviews || 0);

        // Store current room for feedback
        currentRoomForFeedback = roomId;

        // Load reviews
        loadRoomReviews(roomId);

        // Show modal
        const modal = new bootstrap.Modal(
          document.getElementById("roomDetailsModal"),
        );
        modal.show();
      }
    })
    .catch((error) => {
      console.error("Error loading room details:", error);
      showAlert("Failed to load room details", "danger");
    });
}

function loadRoomReviews(roomId) {
  const reviewsList = document.getElementById("roomReviewsList");
  fetch(
    `database/UserAuth/user_auth.php?action=get_room_reviews&room_id=${roomId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.reviews) {
        // cache reviews and render
        roomReviewsCache[roomId] = data.reviews;
        try {
          renderReviews(roomId, data.reviews);
        } catch (err) {
          console.error("Error rendering reviews:", err);
          reviewsList.innerHTML = `\n            <div class="text-center text-danger py-4">\n              <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>\n              <p>Unable to display reviews</p>\n            </div>\n          `;
        }
        try {
          renderReviewFilterControls(roomId);
        } catch (err) {
          console.warn("Could not attach review filter controls", err);
        }
      } else {
        reviewsList.innerHTML = `
          <div class="text-center text-muted py-4">
            <i class="fas fa-comments fa-3x mb-3 opacity-50"></i>
            <p>No reviews yet. Be the first to review!</p>
          </div>
        `;
      }
    })
    .catch((error) => {
      console.error("Error loading reviews:", error);
      reviewsList.innerHTML = `
        <div class="text-center text-danger py-4">
          <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
          <p>Failed to load reviews</p>
        </div>
      `;
    });
}

function renderReviewFilterControls(roomId) {
  const container = document.getElementById("reviewStarFilter");
  if (!container) return;

  // attach click handlers to buttons
  container.querySelectorAll("button[data-star]").forEach((btn) => {
    btn.onclick = function () {
      // toggle active class
      container
        .querySelectorAll("button")
        .forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      const star = this.getAttribute("data-star");
      applyReviewFilter(roomId, star);
    };
  });
}

function applyReviewFilter(roomId, star) {
  const reviewsList = document.getElementById("roomReviewsList");
  const reviews = roomReviewsCache[roomId] || [];
  let filtered = reviews;
  if (star && star !== "all") {
    const n = parseInt(star, 10);
    filtered = reviews.filter((r) => parseInt(r.rating, 10) === n);
  }
  renderReviews(roomId, filtered);
}

function renderReviews(roomId, reviews) {
  const reviewsList = document.getElementById("roomReviewsList");
  if (!reviews || reviews.length === 0) {
    reviewsList.innerHTML = `
      <div class="text-center text-muted py-4">
        <i class="fas fa-comments fa-3x mb-3 opacity-50"></i>
        <p>No reviews yet. Be the first to review!</p>
      </div>
    `;
    return;
  }
  // render review cards
  reviewsList.innerHTML = reviews
    .map((review) => createReviewCard(review))
    .join("");
}

function createReviewCard(review) {
  const stars = "★".repeat(review.rating) + "☆".repeat(5 - review.rating);
  const displayName =
    review.is_anonymous == 1 || !review.guest_name
      ? "Anonymous"
      : review.guest_name;
  const date = new Date(review.created_at).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });

  return `
    <div class="card review-card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h6 class="mb-1">
              <i class="fas fa-user-circle me-2 text-primary"></i>${escapeHtml(displayName)}
            </h6>
            <div class="review-stars mb-1" style="color: #ffc107; font-size: 1.1rem;">
              ${stars}
            </div>
          </div>
          <small class="text-muted">${date}</small>
        </div>
        ${review.comment ? `<p class="mb-0 text-muted">${escapeHtml(review.comment)}</p>` : ""}
      </div>
    </div>
  `;
}

function updateModalRating(averageRating, totalReviews) {
  const starsContainer = document.querySelector(".stars-display-modal");
  const reviewCount = document.getElementById("modalReviewCount");

  if (starsContainer) {
    const stars = starsContainer.querySelectorAll("i");
    const fullStars = Math.floor(averageRating);
    const hasHalfStar = averageRating % 1 >= 0.5;

    stars.forEach((star, index) => {
      if (index < fullStars) {
        star.className = "fas fa-star";
        star.style.color = "#ffc107";
      } else if (index === fullStars && hasHalfStar) {
        star.className = "fas fa-star-half-alt";
        star.style.color = "#ffc107";
      } else {
        star.className = "far fa-star";
        star.style.color = "#ddd";
      }
    });
  }

  if (reviewCount) {
    reviewCount.textContent = totalReviews;
  }
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function showAlert(message, type = "info") {
  const alertClass = `alert-${type}`;
  const iconClass =
    type === "success"
      ? "check-circle"
      : type === "danger"
        ? "exclamation-triangle"
        : "info-circle";
  const alert = document.createElement("div");
  alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
  alert.style.top = "20px";
  alert.style.right = "20px";
  alert.style.zIndex = "9999";
  alert.style.maxWidth = "400px";
  alert.innerHTML = `<i class="fas fa-${iconClass} me-2"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
  document.body.appendChild(alert);
  setTimeout(() => {
    if (alert.parentNode) alert.remove();
  }, 5000);
}

// Export functions for global access
window.openRoomFeedbackModal = openRoomFeedbackModal;
window.openRoomDetailsModal = openRoomDetailsModal;
window.loadRoomReviews = loadRoomReviews;
