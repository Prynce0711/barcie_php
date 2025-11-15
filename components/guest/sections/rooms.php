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
          <h5 class="card-title mb-0 room-title">Penthouse</h5>
          <div class="text-end room-price">
            <div class="price-amount">₱10,000</div>
            <small class="text-muted">per night</small>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center details-row">
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
      </div>
    </div>
  </div>
</template>
