<!-- Rooms & Facilities Section -->
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
                      <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchItems" placeholder="Search by name, room number, or description...">
                       
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

        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" data-bs-backdrop="false" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="fas fa-plus me-2"></i>Add New Room / Facility / Amenities
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                  <input type="hidden" name="add_item" value="1">

                  <div class="row">
                    <div class="col-12 mb-3">
                      <label class="form-label">Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Type <span class="text-danger">*</span></label>
                      <select name="item_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="room">Room</option>
                        <option value="facility">Facility</option>
                      </select>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Room Number</label>
                      <input type="text" class="form-control" name="room_number" placeholder="Optional">
                    </div>

                    <div class="col-12 mb-3">
                      <label class="form-label">Description</label>
                      <textarea class="form-control" name="description" rows="3" placeholder="Brief description of the room or facility"></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Capacity <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="capacity" min="1" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="price" min="0" step="1" required>
                    </div>

                    <div class="col-12 mb-3">
                      <label class="form-label">Image</label>
                      <input type="file" class="form-control" name="image" accept="image/*">
                      <div class="form-text">Optional: Upload an image for this room or facility</div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                  </button>
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Add Item
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
