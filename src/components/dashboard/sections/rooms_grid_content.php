<?php
// Rooms Grid Content
$res = $conn->query("SELECT * FROM items ORDER BY item_type, created_at DESC");
while ($item = $res->fetch_assoc()): ?>
  <div class="col-lg-4 col-md-6 mb-4 item-card" data-type="<?= $item['item_type'] ?>"
    data-searchable="<?= strtolower($item['name'] . ' ' . $item['room_number'] . ' ' . $item['description']) ?>">
    <div class="card border-0 shadow-sm h-100 hover-lift">
      <!-- Item Image -->
      <div class="position-relative">
        <?php if ($item['image'] && file_exists($item['image'])): ?>
          <img src="<?= htmlspecialchars($item['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>">
        <?php else: ?>
          <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 200px; background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
            <i class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> fa-3x text-muted"></i>
          </div>
        <?php endif; ?>

        <!-- Type Badge -->
        <div class="position-absolute top-0 end-0 m-2">
          <span class="badge <?= $item['item_type'] === 'room' ? 'bg-primary' : ($item['item_type'] === 'facility' ? 'bg-success' : 'bg-info') ?> px-3 py-2">
            <i class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> me-1"></i>
            <?= ucfirst($item['item_type']) ?>
          </span>
        </div>
      </div>

      <!-- Item Details -->
      <div class="card-body d-flex flex-column">
        <div class="flex-grow-1">
          <h5 class="card-title mb-2"><?= htmlspecialchars($item['name']) ?></h5>

          <?php if ($item['room_number']): ?>
            <p class="text-muted mb-2">
              <i class="fas fa-door-open me-1"></i>Room #<?= htmlspecialchars($item['room_number'] ?? '') ?>
            </p>
          <?php endif; ?>

          <p class="card-text text-muted small mb-3"><?= htmlspecialchars($item['description'] ?? '') ?></p>

          <div class="row text-center mb-3">
            <div class="col-6">
              <div class="border-end">
                <h6 class="text-primary mb-1">₱<?= number_format($item['price']) ?></h6>
                <small class="text-muted"><?= $item['item_type'] === 'room' ? 'per night' : 'per day' ?></small>
              </div>
            </div>
            <div class="col-6">
              <h6 class="text-success mb-1"><?= $item['capacity'] ?></h6>
              <small class="text-muted"><?= $item['item_type'] === 'room' ? 'guests' : 'people' ?></small>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-primary flex-fill edit-toggle-btn" data-item-id="<?= $item['id'] ?>">
            <i class="fas fa-edit me-1"></i>Edit
          </button>
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['id'] ?>">
            <i class="fas fa-trash me-1"></i>Delete
          </button>
        </div>

        <!-- Hidden Edit Form -->
        <div class="edit-form-container mt-3" id="editForm<?= $item['id'] ?>" style="display: none;">
          <form method="POST" enctype="multipart/form-data" class="border-top pt-3">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <input type="hidden" name="old_image" value="<?= $item['image'] ?>">

            <div class="row">
              <div class="col-12 mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Type</label>
                <select name="item_type" class="form-select">
                  <option value="room" <?= $item['item_type'] == 'room' ? 'selected' : '' ?>>Room</option>
                  <option value="facility" <?= $item['item_type'] == 'facility' ? 'selected' : '' ?>>Facility</option>
                  <option value="amenities" <?= $item['item_type'] == 'amenities' ? 'selected' : '' ?>>Amenities</option>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" class="form-control" name="room_number" value="<?= htmlspecialchars($item['room_number'] ?? '') ?>">
              </div>

              <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control" name="capacity" value="<?= $item['capacity'] ?>" required>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Price (₱)</label>
                <input type="number" class="form-control" name="price" value="<?= $item['price'] ?>" required>
              </div>

              <div class="col-12 mb-3">
                <label class="form-label">Change Image</label>
                <input type="file" class="form-control" name="image" accept="image/*">
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-fill">
                <i class="fas fa-save me-1"></i>Update
              </button>
              <button type="button" class="btn btn-secondary edit-cancel-btn" data-item-id="<?= $item['id'] ?>">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal<?= $item['id'] ?>" data-bs-backdrop="false" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong><?= htmlspecialchars($item['name']) ?></strong>?</p>
          <p class="text-muted small">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" class="d-inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endwhile; ?>