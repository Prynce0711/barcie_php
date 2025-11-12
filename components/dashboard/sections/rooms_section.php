
<!-- Rooms & Facilities Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body text-white">
        <div class="text-center">
          <h2 class="mb-1"><i class="fas fa-building me-2"></i>Rooms & Facilities Management</h2>
          <p class="mb-0 opacity-75">Manage your property inventory and amenities</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Controls -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-3"><i class="fas fa-filter me-2 text-primary"></i>Filter & Search</h5>
            <div class="btn-group w-100 item-filters" role="group" aria-label="Type filter">
              <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-all" value="all" checked>
              <label class="btn btn-outline-primary" for="filter-all">
                <i class="fas fa-list me-1"></i>All
                <span class="badge bg-primary ms-1 type-count" data-type="all">0</span>
              </label>

              <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-room" value="room">
              <label class="btn btn-outline-primary" for="filter-room">
                <i class="fas fa-bed me-1"></i>Rooms
                <span class="badge bg-primary ms-1 type-count" data-type="room">0</span>
              </label>

              <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-facility" value="facility">
              <label class="btn btn-outline-primary" for="filter-facility">
                <i class="fas fa-building me-1"></i>Facilities
                <span class="badge bg-primary ms-1 type-count" data-type="facility">0</span>
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Search Items</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="searchItems" placeholder="Search by name, room number, or description...">
              </div>
              <!-- Add New Button -->
              <div class="d-grid">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
                  <i class="fas fa-plus me-2"></i>Add New Room / Facility
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Items Grid -->
<!-- Items Grid (wrapped to allow overlay spinner) -->
<div class="position-relative">
  <!-- Loading overlay (hidden by default) -->
  <div id="itemsLoadingOverlay" class="loading-overlay d-none" aria-hidden="true" role="status" aria-label="Loading items">
    <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <div class="row" id="items-container">
    <?php include 'rooms_grid_content.php'; ?>
  </div>
</div>


<!-- Add New Item Modal is now included in the main layout (dashboard.php) to avoid stacking/overflow issues -->

<!-- Inline styles for the items loading overlay (kept small and local to this component) -->
<style>
  /* Ensure the wrapper is the positioning context */
  .position-relative { position: relative; }

  /* Full-area overlay that sits above the items grid */
  .loading-overlay {
    position: absolute;
    inset: 0; /* top:0; right:0; bottom:0; left:0 */
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.72);
    z-index: 1080; /* above most UI but below modals */
    backdrop-filter: blur(2px);
  }

  /* Utility to hide/show using bootstrap d-none class */
</style>

<!-- Small script to show the overlay briefly when switching type filters (All/Rooms/Facilities) -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('itemsLoadingOverlay');
    if (!overlay) return;

    // Show overlay early using capture so it appears before other handlers run
    document.querySelectorAll('.type-filter').forEach(radio => {
      radio.addEventListener('change', function() {
        // Show overlay
        overlay.classList.remove('d-none');
        overlay.setAttribute('aria-hidden', 'false');

        // Hide overlay shortly after other handlers complete.
        // Filtering in this section is synchronous, so a short timeout is sufficient.
        // If you later change to async loading, replace this with logic that hides
        // the overlay when the async request completes.
        requestAnimationFrame(() => {
          setTimeout(() => {
            overlay.classList.add('d-none');
            overlay.setAttribute('aria-hidden', 'true');
          }, 120);
        });
      }, true); // use capture to show immediately
    });
  });
</script>

