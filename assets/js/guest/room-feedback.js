/**
 * Room Feedback System
 * Handles room reviews, ratings, and feedback modal
 */

let currentRoomForFeedback = null;

// Initialize room feedback system
document.addEventListener('DOMContentLoaded', function() {
  initializeRoomFeedbackSystem();
});

function initializeRoomFeedbackSystem() {
  // Star rating input functionality
  const starRatingContainer = document.getElementById('feedbackStarRating');
  const ratingValueInput = document.getElementById('feedbackRatingValue');
  const ratingText = document.getElementById('feedbackRatingText');
  const submitBtn = document.getElementById('submitRoomFeedback');
  const anonymousCheckbox = document.getElementById('feedbackAnonymous');
  const guestNameInput = document.getElementById('feedbackGuestName');

  if (starRatingContainer) {
    const stars = starRatingContainer.querySelectorAll('.star-input');
    
    stars.forEach((star, index) => {
      star.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        ratingValueInput.value = rating;
        
        // Update star display
        stars.forEach((s, i) => {
          if (i < rating) {
            s.classList.add('active');
            s.querySelector('i').classList.remove('far');
            s.querySelector('i').classList.add('fas');
          } else {
            s.classList.remove('active');
            s.querySelector('i').classList.remove('fas');
            s.querySelector('i').classList.add('far');
          }
        });
        
        // Update rating text
        const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        ratingText.textContent = ratingTexts[rating];
        ratingText.style.color = '#ffc107';
        
        // Enable submit button
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      });
      
      // Hover effect
      star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.dataset.rating);
        stars.forEach((s, i) => {
          if (i < rating) {
            s.querySelector('i').classList.remove('far');
            s.querySelector('i').classList.add('fas');
          }
        });
      });
    });
    
    starRatingContainer.addEventListener('mouseleave', function() {
      const currentRating = parseInt(ratingValueInput.value) || 0;
      stars.forEach((s, i) => {
        if (i < currentRating) {
          s.querySelector('i').classList.remove('far');
          s.querySelector('i').classList.add('fas');
        } else {
          s.querySelector('i').classList.remove('fas');
          s.querySelector('i').classList.add('far');
        }
      });
    });
  }

  // Anonymous checkbox handler
  if (anonymousCheckbox && guestNameInput) {
    anonymousCheckbox.addEventListener('change', function() {
      if (this.checked) {
        guestNameInput.disabled = true;
        guestNameInput.value = '';
        guestNameInput.placeholder = 'Posting as Anonymous';
      } else {
        guestNameInput.disabled = false;
        guestNameInput.placeholder = 'Enter your name';
      }
    });
  }

  // Form submission
  const feedbackForm = document.getElementById('roomFeedbackForm');
  if (feedbackForm) {
    feedbackForm.addEventListener('submit', function(e) {
      e.preventDefault();
      submitRoomFeedback();
    });
  }

  // Add event delegation for dynamically created buttons
  document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-leave-review')) {
      const card = e.target.closest('.room-card-col');
      if (card) {
        const roomId = card.dataset.itemId;
        const roomName = card.querySelector('.room-title').textContent;
        openRoomFeedbackModal(roomId, roomName);
      }
    }
    
    if (e.target.closest('.btn-view-details')) {
      const card = e.target.closest('.room-card-col');
      if (card) {
        const roomId = card.dataset.itemId;
        openRoomDetailsModal(roomId);
      }
    }
  });
}

function openRoomFeedbackModal(roomId, roomName) {
  if (!roomId) {
    roomId = currentRoomForFeedback;
  }
  
  currentRoomForFeedback = roomId;
  
  const modal = new bootstrap.Modal(document.getElementById('roomFeedbackModal'));
  document.getElementById('feedbackRoomId').value = roomId;
  
  // Update modal title if room name is provided
  if (roomName) {
    document.getElementById('roomFeedbackLabel').innerHTML = 
      `<i class="fas fa-star me-2"></i>Review: ${roomName}`;
  }
  
  // Reset form
  document.getElementById('roomFeedbackForm').reset();
  document.getElementById('feedbackRatingValue').value = '';
  document.getElementById('feedbackRatingText').textContent = 'Click to rate';
  document.getElementById('feedbackRatingText').style.color = '';
  document.getElementById('submitRoomFeedback').disabled = true;
  document.getElementById('feedbackGuestName').disabled = false;
  
  // Reset stars
  document.querySelectorAll('.star-input').forEach(star => {
    star.classList.remove('active');
    star.querySelector('i').classList.remove('fas');
    star.querySelector('i').classList.add('far');
  });
  
  modal.show();
}

function submitRoomFeedback() {
  const form = document.getElementById('roomFeedbackForm');
  const submitBtn = document.getElementById('submitRoomFeedback');
  const rating = document.getElementById('feedbackRatingValue').value;
  
  if (!rating) {
    showAlert('Please select a star rating', 'danger');
    return;
  }
  
  const originalHtml = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
  submitBtn.disabled = true;
  
  const formData = new FormData(form);
  
  fetch('database/user_auth.php', {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert(data.message || 'Review submitted successfully!', 'success');
      
      // Close feedback modal
      bootstrap.Modal.getInstance(document.getElementById('roomFeedbackModal')).hide();
      
      // Refresh room details if open
      const detailsModal = document.getElementById('roomDetailsModal');
      if (detailsModal.classList.contains('show')) {
        loadRoomReviews(currentRoomForFeedback);
      }
      
      // Refresh room cards to update ratings
      if (window.loadRooms) {
        window.loadRooms();
      }
    } else {
      throw new Error(data.error || 'Failed to submit review');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert(error.message || 'Failed to submit review. Please try again.', 'danger');
  })
  .finally(() => {
    submitBtn.innerHTML = originalHtml;
    if (document.getElementById('feedbackRatingValue').value) {
      submitBtn.disabled = false;
    }
  });
}

function openRoomDetailsModal(roomId) {
  fetch(`api/items.php?id=${roomId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.item) {
        const item = data.item;
        
        // Update modal content
        document.getElementById('roomDetailsLabel').textContent = item.name;
        document.getElementById('modalRoomPrice').textContent = `₱${parseFloat(item.price).toLocaleString()}`;
        document.getElementById('modalRoomCapacity').textContent = item.capacity || 'N/A';
        
        // Hide or show beds info based on availability
        const bedsElement = document.getElementById('modalRoomBeds');
        const bedsContainer = bedsElement ? bedsElement.closest('.mb-3') : null;
        if (item.beds) {
          if (bedsElement) bedsElement.textContent = item.beds;
          if (bedsContainer) bedsContainer.style.display = '';
        } else {
          if (bedsContainer) bedsContainer.style.display = 'none';
        }
        
        document.getElementById('modalRoomType').textContent = item.item_type || 'Room';
        document.getElementById('modalRoomType').className = `badge ${item.item_type === 'room' ? 'bg-primary' : 'bg-success'}`;
        
        // Set image
        const images = item.images ? JSON.parse(item.images) : [];
        const imageSrc = images.length > 0 ? images[0] : '/assets/images/imageBg/barcie_logo.jpg';
        document.getElementById('modalRoomImage').src = imageSrc;
        document.getElementById('modalRoomImage').alt = item.name;
        
        // Update rating display
        updateModalRating(item.average_rating || 0, item.total_reviews || 0);
        
        // Store current room for feedback
        currentRoomForFeedback = roomId;
        
        // Load reviews
        loadRoomReviews(roomId);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('roomDetailsModal'));
        modal.show();
      }
    })
    .catch(error => {
      console.error('Error loading room details:', error);
      showAlert('Failed to load room details', 'danger');
    });
}

function loadRoomReviews(roomId) {
  const reviewsList = document.getElementById('roomReviewsList');
  reviewsList.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';
  
  fetch(`database/user_auth.php?action=get_room_reviews&room_id=${roomId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.reviews) {
        if (data.reviews.length === 0) {
          reviewsList.innerHTML = `
            <div class="text-center text-muted py-4">
              <i class="fas fa-comments fa-3x mb-3 opacity-50"></i>
              <p>No reviews yet. Be the first to review!</p>
            </div>
          `;
        } else {
          reviewsList.innerHTML = data.reviews.map(review => createReviewCard(review)).join('');
        }
      } else {
        reviewsList.innerHTML = `
          <div class="text-center text-muted py-4">
            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
            <p>Unable to load reviews</p>
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error loading reviews:', error);
      reviewsList.innerHTML = `
        <div class="text-center text-danger py-4">
          <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
          <p>Failed to load reviews</p>
        </div>
      `;
    });
}

function createReviewCard(review) {
  const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
  const displayName = review.is_anonymous == 1 || !review.guest_name ? 'Anonymous' : review.guest_name;
  const date = new Date(review.created_at).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
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
        ${review.comment ? `<p class="mb-0 text-muted">${escapeHtml(review.comment)}</p>` : ''}
      </div>
    </div>
  `;
}

function updateModalRating(averageRating, totalReviews) {
  const starsContainer = document.querySelector('.stars-display-modal');
  const reviewCount = document.getElementById('modalReviewCount');
  
  if (starsContainer) {
    const stars = starsContainer.querySelectorAll('i');
    const fullStars = Math.floor(averageRating);
    const hasHalfStar = averageRating % 1 >= 0.5;
    
    stars.forEach((star, index) => {
      if (index < fullStars) {
        star.className = 'fas fa-star';
        star.style.color = '#ffc107';
      } else if (index === fullStars && hasHalfStar) {
        star.className = 'fas fa-star-half-alt';
        star.style.color = '#ffc107';
      } else {
        star.className = 'far fa-star';
        star.style.color = '#ddd';
      }
    });
  }
  
  if (reviewCount) {
    reviewCount.textContent = totalReviews;
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function showAlert(message, type = 'info') {
  const alertClass = `alert-${type}`;
  const iconClass = type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle';
  const alert = document.createElement('div');
  alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
  alert.style.top = '20px';
  alert.style.right = '20px';
  alert.style.zIndex = '9999';
  alert.style.maxWidth = '400px';
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
