<section id="rooms" class="content-section">
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fas fa-door-open me-2"></i>Rooms & Facilities</h2>
    <div class="filter-controls">
      <div class="btn-group" role="group" aria-label="Filter by type">
        <input type="radio" class="btn-check" name="type" id="filter-all" value="all" checked>
        <label class="btn btn-outline-primary" for="filter-all">
          <i class="fas fa-th-large me-1"></i>All
        </label>

        <input type="radio" class="btn-check" name="type" id="filter-room" value="room">
        <label class="btn btn-outline-primary" for="filter-room">
          <i class="fas fa-bed me-1"></i>Rooms
        </label>

        <input type="radio" class="btn-check" name="type" id="filter-facility" value="facility">
        <label class="btn btn-outline-primary" for="filter-facility">
          <i class="fas fa-building me-1"></i>Facilities
        </label>
      </div>
    </div>
  </div>

  <div class="cards-grid row g-4" id="cards-grid"></div>
</section>

<!-- Image Gallery Modal -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-labelledby="imageGalleryLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <h5 class="modal-title text-white" id="imageGalleryLabel">Image Gallery</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 position-relative">
        <!-- Main Image Container -->
        <div class="gallery-main-image-container position-relative" style="min-height: 500px; display: flex; align-items: center; justify-content: center; background: #000;">
          <img id="galleryMainImage" src="" alt="" class="img-fluid" style="max-height: 70vh; max-width:95%; width: auto; object-fit:contain; transition: transform 0.3s ease; border:6px solid rgba(255,255,255,0.9); border-radius:8px; box-shadow: 0 6px 22px rgba(0,0,0,0.45);">
          
          <!-- Navigation Arrows -->
          <button class="btn btn-light btn-gallery-nav btn-gallery-prev" id="galleryPrevBtn" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); border-radius: 50%; width: 50px; height: 50px; z-index: 10;">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="btn btn-light btn-gallery-nav btn-gallery-next" id="galleryNextBtn" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); border-radius: 50%; width: 50px; height: 50px; z-index: 10;">
            <i class="fas fa-chevron-right"></i>
          </button>
          
          <!-- Zoom Controls -->
          <div class="gallery-zoom-controls" style="position: absolute; bottom: 20px; right: 20px; z-index: 10;">
            <button class="btn btn-light me-2" id="zoomOutBtn" title="Zoom Out">
              <i class="fas fa-search-minus"></i>
            </button>
            <button class="btn btn-light me-2" id="zoomInBtn" title="Zoom In">
              <i class="fas fa-search-plus"></i>
            </button>
            <button class="btn btn-light" id="zoomResetBtn" title="Reset Zoom">
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
          
          <!-- Image Counter -->
          <div class="gallery-counter" style="position: absolute; top: 20px; left: 20px; background: rgba(0,0,0,0.7); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px;">
            <i class="fas fa-images me-2"></i>
            <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
          </div>
        </div>
        
        <!-- Thumbnail Strip -->
        <div class="gallery-thumbnails" style="background: #1a1a1a; padding: 15px; overflow-x: auto; white-space: nowrap;">
          <div id="galleryThumbnails" style="display: inline-flex; gap: 10px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Room Details Modal with Reviews -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1" aria-labelledby="roomDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
        
      <div class="modal-header" style="background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%); color: white;">
        <div>
          <h5 class="modal-title mb-1" id="roomDetailsLabel">Room Details</h5>
          <div class="room-rating-modal">
            <span class="stars-display-modal">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </span>
            <small class="ms-1">(<span id="modalReviewCount">0</span> reviews)</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Room Info Section -->
        <div class="mb-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <img id="modalRoomImage" src="" alt="" class="img-fluid rounded shadow-sm" style="width: 100%; max-height: 250px; object-fit: cover;">
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <h6 class="text-muted mb-1">Price</h6>
                <h4 class="text-primary mb-0" id="modalRoomPrice">₱0</h4>
                <small class="text-muted">per night</small>
              </div>
              <div class="mb-3">
                <h6 class="text-muted mb-1">Capacity</h6>
                <p class="mb-0"><i class="fas fa-users me-2 text-primary"></i><span id="modalRoomCapacity">0</span> guests</p>
              </div>
              <div class="mb-3">
                <h6 class="text-muted mb-1">Beds</h6>
                <p class="mb-0"><i class="fas fa-bed me-2 text-primary"></i><span id="modalRoomBeds">0</span> beds</p>
              </div>
              <div>
                <h6 class="text-muted mb-1">Type</h6>
                <span class="badge bg-primary" id="modalRoomType">Room</span>
              </div>
            </div>
          </div>
        </div>

        <hr>

        <!-- Reviews Section -->
        <div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <h5 class="mb-0"><i class="fas fa-star text-warning me-2"></i>Guest Reviews</h5>
              <!-- Star filter controls -->
              <div id="reviewStarFilter" class="ms-3 btn-group btn-group-sm" role="group" aria-label="Filter by stars">
                <button type="button" class="btn btn-outline-secondary active" data-star="all">All</button>
                <button type="button" class="btn btn-outline-secondary" data-star="5">5★</button>
                <button type="button" class="btn btn-outline-secondary" data-star="4">4★</button>
                <button type="button" class="btn btn-outline-secondary" data-star="3">3★</button>
                <button type="button" class="btn btn-outline-secondary" data-star="2">2★</button>
                <button type="button" class="btn btn-outline-secondary" data-star="1">1★</button>
              </div>
            </div>
            <button class="btn btn-sm btn-outline-primary" id="writeReviewBtn">
              <i class="fas fa-plus me-1"></i>Write Review
            </button>
          </div>
          
          <div id="roomReviewsList">
            <div class="text-center text-muted py-4">
              <i class="fas fa-comments fa-3x mb-3 opacity-50"></i>
              <p>No reviews yet. Be the first to review!</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Room Feedback Modal -->
<div class="modal fade" id="roomFeedbackModal" tabindex="-1" aria-labelledby="roomFeedbackLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%); color: white;">
        <h5 class="modal-title" id="roomFeedbackLabel">
          <i class="fas fa-star me-2"></i>Leave a Review
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="roomFeedbackForm">
          <input type="hidden" name="action" value="room_feedback">
          <input type="hidden" name="room_id" id="feedbackRoomId" value="">
          <input type="hidden" name="rating" id="feedbackRatingValue" value="">
          <input type="hidden" name="google_id" id="feedbackGoogleId" value="">
          <input type="hidden" name="google_email" id="feedbackGoogleEmail" value="">
          <input type="hidden" name="guest_name" id="feedbackGuestName" value="">

          <!-- Google Sign-In Required -->
          <div id="googleSignInOption" class="alert alert-primary mb-3">
            <div class="d-flex align-items-start mb-3">
              <i class="fab fa-google fa-2x text-danger me-3"></i>
              <div class="flex-fill">
                <h6 class="mb-1"><i class="fas fa-shield-alt me-1"></i>Google Sign-In Required</h6>
                <small class="text-muted">Please sign in with your Google account to verify your review and build trust with future guests.</small>
              </div>
            </div>
            <div id="g_id_onload"
                 data-client_id="173306587840-ine8ao88f5a8r5mnjnuc7fa8sdmo110c.apps.googleusercontent.com"
                 data-callback="handleGoogleSignIn"
                 data-auto_prompt="false">
            </div>
            <div class="g_id_signin"
                 data-type="standard"
                 data-size="large"
                 data-theme="outline"
                 data-text="signin_with"
                 data-shape="rectangular"
                 data-logo_alignment="left">
            </div>
          </div>

          <!-- Signed In User Info -->
          <div class="mb-3 p-3 bg-light rounded" id="signedInUserInfo" style="display:none;">
            <div class="d-flex align-items-center">
              <img id="userGooglePhoto" src="" alt="Profile" class="rounded-circle me-3" width="50" height="50" style="display:none;">
              <div>
                <p class="mb-0 fw-bold">Signed in as: <span id="userGoogleName"></span></p>
                <small class="text-muted"><span id="userGoogleEmail"></span></small>
              </div>
            </div>
            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" name="is_anonymous" id="feedbackAnonymous" value="1">
              <label class="form-check-label" for="feedbackAnonymous">
                <i class="fas fa-user-secret me-1"></i>Post as Anonymous (Only first 2 letters of name will be shown)
              </label>
            </div>
          </div>

          <!-- Star Rating -->
          <div class="mb-4">
            <label class="form-label fw-bold">Rating *</label>
            <div class="d-flex align-items-center">
              <div class="star-rating-input me-3" id="feedbackStarRating">
                <span class="star-input" data-rating="1"><i class="far fa-star fa-2x"></i></span>
                <span class="star-input" data-rating="2"><i class="far fa-star fa-2x"></i></span>
                <span class="star-input" data-rating="3"><i class="far fa-star fa-2x"></i></span>
                <span class="star-input" data-rating="4"><i class="far fa-star fa-2x"></i></span>
                <span class="star-input" data-rating="5"><i class="far fa-star fa-2x"></i></span>
              </div>
              <small class="text-muted" id="feedbackRatingText">Click to rate</small>
            </div>
          </div>

          <!-- Comment -->
          <div class="mb-4">
            <label for="feedbackComment" class="form-label fw-bold">Your Review</label>
            <textarea class="form-control" name="comment" id="feedbackComment" rows="4" placeholder="Share your experience with this room..."></textarea>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
              <i class="fas fa-info-circle me-1"></i>Help others by sharing your honest feedback
            </small>
            <button type="submit" class="btn btn-warning" id="submitRoomFeedback" disabled>
              <i class="fas fa-paper-plane me-2"></i>Submit Review
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
/* Star Rating Styles */
.star-rating-input {
  display: inline-flex;
  gap: 5px;
}

.star-input {
  cursor: pointer;
  transition: all 0.2s ease;
  color: #ddd;
}

.star-input:hover,
.star-input.active {
  color: #ffc107;
}

.star-input:hover i,
.star-input.active i {
  transform: scale(1.2);
}

.star-input i {
  transition: all 0.2s ease;
}

/* Room Rating Display */
.room-rating .stars-display i {
  font-size: 0.9rem;
}

.room-rating-modal .stars-display-modal i {
  font-size: 1rem;
}

/* Review Card Styles */
.review-card {
  border-left: 3px solid #2a5298;
  transition: all 0.3s ease;
}

.review-card:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Feedback modal loading overlay */
/* removed feedback loading overlay styling */

.review-stars {
  color: #ffc107;
}
</style>

<!-- Card template (hidden) -->
<template id="roomCardTemplate">
  <div class="col-12 col-md-6 room-card-col" data-room-id="" data-item-id="">
      <div class="card room-card card-hover-effect shadow-sm">
      <div class="card-image position-relative overflow-hidden" style="border-radius: 8px 8px 0 0; padding:12px; background:#f7fafb;">
        <div class="room-card-image-wrapper d-flex align-items-center justify-content-center" style="min-height:220px;">
          <img src="" alt="" class="img-fluid room-card-img" style="max-width:100%; max-height:200px; object-fit:contain; display:block; border-radius:8px; background:#ffffff;">
        </div>

        <!-- Quick actions -->
        <button class="btn btn-light btn-zoom position-absolute" title="Open gallery" style="left:12px; top:12px; border-radius:50%; width:44px; height:44px;">
          <i class="fas fa-search-plus"></i>
        </button>

        <!-- Type badge -->
        <div class="type-badge position-absolute text-uppercase" style="right:12px; top:12px;">
          <span class="badge bg-primary py-2 px-3"><i class="fas fa-bed me-1"></i> Room</span>
        </div>

        <!-- Nav arrows -->
        <button class="btn btn-white btn-gallery-prev position-absolute" style="left:8px; top:50%; transform:translateY(-50%); border-radius:50%; width:46px; height:46px;">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="btn btn-white btn-gallery-next position-absolute" style="right:8px; top:50%; transform:translateY(-50%); border-radius:50%; width:46px; height:46px;">
          <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Image counter -->
        <div class="image-counter position-absolute text-center" style="left:50%; transform:translateX(-50%); bottom:12px;">
          <span class="badge bg-white text-dark py-2 px-3">1 / 4</span>
        </div>
      </div>

      <div class="card-body bg-white">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="card-title mb-1 room-title">Penthouse</h5>
            <div class="room-rating mb-0">
              <span class="stars-display">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-muted"></i>
              </span>
              <small class="text-muted ms-1">(<span class="review-count">0</span>)</small>
            </div>
          </div>
          <div class="text-end room-price">
            <div class="price-amount">₱10,000</div>
            <small class="text-muted">per night</small>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center details-row mb-3">
          <div class="text-center flex-fill">
            <div class="fw-bold">4</div>
            <small class="text-muted">guests</small>
          </div>
          <div class="vr mx-3" style="height:36px;"></div>
          <div class="text-center flex-fill">
            <div class="fw-bold">2</div>
            <small class="text-muted">beds</small>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary btn-sm flex-fill btn-view-details">
            <i class="fas fa-info-circle me-1"></i>View Details
          </button>
          <button class="btn btn-outline-warning btn-sm flex-fill btn-leave-review">
            <i class="fas fa-star me-1"></i>Leave Review
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
