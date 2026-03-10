<style>
  /* Rooms & Cards scoped styles */
  .cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    margin-top: 30px;
    padding: 20px 0;
  }

  @media (max-width: 1024px) {
    .cards-grid {
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
    }
  }

  @media (max-width: 768px) {
    .cards-grid {
      grid-template-columns: 1fr;
      gap: 15px;
    }
  }

  /* Room card styles */
  .room-card {
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid rgba(30, 40, 50, 0.06);
    box-shadow: 0 6px 18px rgba(30, 40, 50, 0.04);
    transition: transform 0.28s ease, box-shadow 0.28s ease;
    cursor: pointer;
  }

  .room-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 50px rgba(20, 30, 40, 0.06);
  }

  .room-card .room-card-img {
    transition: transform 0.35s ease, filter 0.35s ease;
    display: block;
    width: 100%;
    height: 260px;
    object-fit: cover;
    border-radius: 8px;
    filter: saturate(0.98) contrast(0.98);
  }

  .room-card:hover .room-card-img {
    transform: scale(1.01);
  }

  .room-card .btn-zoom {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(30, 40, 50, 0.06);
    color: #1f2d3d;
  }

  .type-badge .badge {
    background: #fff;
    color: #1f2d3d;
    border: 1px solid rgba(30, 40, 50, 0.06);
    padding: 0.55rem 0.9rem;
    font-weight: 600;
    border-radius: 999px;
    box-shadow: 0 6px 18px rgba(15, 20, 25, 0.03);
  }

  .room-card .room-title {
    letter-spacing: 0.25px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #162028;
  }

  .room-card .price-amount {
    font-weight: 700;
    color: #1b3a4b;
  }

  .room-card .room-price small {
    display: block;
    color: #7b8a91;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .details-row .vr {
    width: 1px;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.04), rgba(0, 0, 0, 0.02));
  }

  .details-row .fw-bold {
    color: #23343f;
  }

  @media (max-width: 768px) {
    .room-card .room-card-img {
      height: 200px;
    }

    .room-card .card-body {
      padding: 12px;
    }
  }

  /* Gallery styles */
  .gallery-main-image-container {
    position: relative;
    overflow: hidden;
  }

  #galleryMainImage {
    transition: transform 0.3s ease;
    cursor: default;
  }

  #galleryMainImage.zoomed {
    cursor: move;
  }

  .btn-gallery-nav {
    opacity: 0.8;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }

  .btn-gallery-nav:hover {
    opacity: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  }

  .gallery-zoom-controls .btn {
    opacity: 0.9;
    transition: all 0.3s ease;
  }

  .gallery-zoom-controls .btn:hover {
    opacity: 1;
    transform: scale(1.1);
  }

  .gallery-thumbnail {
    transition: all 0.3s ease;
  }

  .gallery-thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }

  .gallery-thumbnails::-webkit-scrollbar {
    height: 8px;
  }

  .gallery-thumbnails::-webkit-scrollbar-track {
    background: #2a2a2a;
  }

  .gallery-thumbnails::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 4px;
  }

  .gallery-thumbnails::-webkit-scrollbar-thumb:hover {
    background: #777;
  }

  #imageGalleryModal.modal {
    z-index: 20050 !important;
  }

  /* Card image */
  .card-image {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
  }

  .card-image:hover {
    transform: scale(1.02);
  }

  /* Filter bar */
  .filter-bar {
    background: rgba(255, 255, 255, 0.8);
    padding: 25px;
    border-radius: 15px;
    border: 2px solid rgba(52, 152, 219, 0.2);
    margin-bottom: 25px;
    display: flex;
    gap: 20px;
    align-items: center;
    backdrop-filter: blur(10px);
    flex-wrap: wrap;
  }

  .filter-bar label {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-weight: 600;
    color: #2c3e50;
    min-width: 150px;
  }

  .filter-bar select,
  .filter-bar input {
    padding: 10px 15px;
    border: 2px solid rgba(52, 152, 219, 0.3);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    transition: all 0.3s ease;
  }

  .filter-bar select:focus,
  .filter-bar input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
  }

  /* Available now card */
  .available-now-card {
    transition: all 0.3s ease;
  }

  .available-now-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(23, 162, 184, 0.2);
    border-color: #17a2b8;
  }

  .available-now-card:hover .card-body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9f7fd 100%);
  }

  .available-now-card:active {
    transform: translateY(-2px);
  }

  /* Card actions */
  .card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 1rem;
  }

  .card-actions .btn {
    flex: 1;
    transition: all 0.3s ease;
  }

  .card-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }
</style>

<section id="rooms"
  class="content-section bg-white/95 border-2 border-[rgba(52,152,219,0.2)] p-[30px] mb-[30px] rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] relative z-[1]">

  <h2 class="mb-3"><i class="fas fa-door-open me-2"></i>Rooms & Facilities</h2>

  <!-- Filters Bar -->
  <div class="card mb-3 border-0 bg-light">
    <div class="card-body py-2 px-3">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <?php include __DIR__ . '/../../Filter/FilterTypes.php'; ?>
        <div class="vr d-none d-md-block" style="height:28px;"></div>
        <?php $searchScope = 'guest-rooms'; $searchPlaceholder = 'Search rooms & facilities...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
      </div>
    </div>
  </div>

  <div class="cards-grid row g-4" id="cards-grid"></div>
</section>
<!-- Bridge: guest rooms search -->
<script>
(function(){
  document.addEventListener('search-changed', function(e){
    if(e.detail.scope!=='guest-rooms') return;
    var term = (e.detail.value||'').toLowerCase();
    var cards = document.querySelectorAll('#cards-grid .card');
    cards.forEach(function(c){
      var text = (c.textContent||'').toLowerCase();
      c.style.display = (!term || text.includes(term)) ? '' : 'none';
    });
  });
})();
</script>

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
        <div class="gallery-main-image-container position-relative"
          style="min-height: 500px; display: flex; align-items: center; justify-content: center; background: #000;">
          <img id="galleryMainImage" src="" alt="" class="img-fluid"
            style="max-height: 70vh; max-width:95%; width: auto; object-fit:contain; transition: transform 0.3s ease; border:6px solid rgba(255,255,255,0.9); border-radius:8px; box-shadow: 0 6px 22px rgba(0,0,0,0.45);">

          <!-- Navigation Arrows -->
          <button class="btn btn-light btn-gallery-nav btn-gallery-prev" id="galleryPrevBtn"
            style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); border-radius: 50%; width: 50px; height: 50px; z-index: 10;">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="btn btn-light btn-gallery-nav btn-gallery-next" id="galleryNextBtn"
            style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); border-radius: 50%; width: 50px; height: 50px; z-index: 10;">
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
          <div class="gallery-counter"
            style="position: absolute; top: 20px; left: 20px; background: rgba(0,0,0,0.7); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px;">
            <i class="fas fa-images me-2"></i>
            <span id="currentImageIndex">1</span> / <span id="totalImages">1</span>
          </div>
        </div>

        <!-- Thumbnail Strip -->
        <div class="gallery-thumbnails"
          style="background: #1a1a1a; padding: 15px; overflow-x: auto; white-space: nowrap;">
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
              <img id="modalRoomImage" src="" alt="" class="img-fluid rounded shadow-sm"
                style="width: 100%; max-height: 250px; object-fit: cover;">
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <h6 class="text-muted mb-1">Price</h6>
                <h4 class="text-primary mb-0" id="modalRoomPrice">₱0</h4>
                <small class="text-muted">per night</small>
              </div>
              <div class="mb-3">
                <h6 class="text-muted mb-1">Capacity</h6>
                <p class="mb-0"><i class="fas fa-users me-2 text-primary"></i><span id="modalRoomCapacity">0</span>
                  guests</p>
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
<?php include __DIR__ . '/../../Guest/Feedback/RoomFeedback.php'; ?>

<!-- Card template (hidden) -->
<template id="roomCardTemplate">
  <div class="col-12 col-md-6 room-card-col" data-room-id="" data-item-id="">
    <div class="card room-card card-hover-effect shadow-sm">
      <div class="card-image position-relative overflow-hidden"
        style="border-radius: 8px 8px 0 0; padding:12px; background:#f7fafb;">
        <div class="room-card-image-wrapper d-flex align-items-center justify-content-center" style="min-height:220px;">
          <img src="" alt="" class="img-fluid room-card-img"
            style="max-width:100%; max-height:200px; object-fit:contain; display:block; border-radius:8px; background:#ffffff;">
        </div>

        <!-- Quick actions -->
        <button class="btn btn-light btn-zoom position-absolute" title="Open gallery"
          style="left:12px; top:12px; border-radius:50%; width:44px; height:44px;">
          <i class="fas fa-search-plus"></i>
        </button>

        <!-- Type badge -->
        <div class="type-badge position-absolute text-uppercase" style="right:12px; top:12px;">
          <span class="badge bg-primary py-2 px-3"><i class="fas fa-bed me-1"></i> Room</span>
        </div>

        <!-- Nav arrows -->
        <button class="btn btn-white btn-gallery-prev position-absolute"
          style="left:8px; top:50%; transform:translateY(-50%); border-radius:50%; width:46px; height:46px;">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="btn btn-white btn-gallery-next position-absolute"
          style="right:8px; top:50%; transform:translateY(-50%); border-radius:50%; width:46px; height:46px;">
          <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Image counter -->
        <div class="image-counter position-absolute text-center"
          style="left:50%; transform:translateX(-50%); bottom:12px;">
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

<?php include __DIR__ . '/rooms-filter.php'; ?>
<?php include __DIR__ . '/guest-items-loader.php'; ?>