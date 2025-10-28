
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
<div class="row" id="items-container">
  <?php include 'rooms_grid_content.php'; ?>
</div>



<!-- Add New Item Modal is now included in the main layout (dashboard.php) to avoid stacking/overflow issues -->

