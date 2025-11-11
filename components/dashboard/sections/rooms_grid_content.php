<?php
require_once __DIR__ . '/../item_actions.php';

$res = $conn->query("SELECT * FROM items ORDER BY item_type, created_at DESC");
while ($item = $res->fetch_assoc()): ?>
  <div class="col-lg-4 col-md-6 mb-4 item-card" data-type="<?= $item['item_type'] ?>"
    data-searchable="<?= strtolower(($item['name'] ?? '') . ' ' . ($item['room_number'] ?? '') . ' ' . ($item['description'] ?? '')) ?>">
    <div class="card border-0 shadow-sm h-100 hover-lift">
      <!-- Item Image -->
      <div class="position-relative" id="imageCarousel<?= $item['id'] ?>">
        <?php
        // Handle multiple images
        $images = [];
        if (!empty($item['images'])) {
          $decoded = json_decode($item['images'], true);
          if (is_array($decoded)) {
            $images = $decoded;
          }
        }
        // Fall back to single image if exists
        if (empty($images) && !empty($item['image'])) {
          $images = [$item['image']];
        }
        // Final fallback to logo
        if (empty($images)) {
          $images = ['/assets/images/imageBg/barcie_logo.jpg'];
        }

        // Prepare web paths
        $webImages = [];
        foreach ($images as $img) {
          if (str_starts_with($img, 'http') || str_starts_with($img, '/')) {
            $webImages[] = $img;
          } else {
            $webImages[] = '/' . ltrim($img, '/');
          }
        }
        ?>

        <div class="image-slider-container" style="position: relative; height: 200px; overflow: hidden;">
          <?php foreach ($webImages as $idx => $webImg): ?>
            <img src="<?= htmlspecialchars($webImg) ?>" class="card-img-top carousel-image-<?= $item['id'] ?>"
              style="height: 200px; object-fit: cover; position: absolute; top: 0; left: 0; width: 100%; transition: opacity 0.3s; <?= $idx === 0 ? 'opacity: 1;' : 'opacity: 0;' ?>"
              alt="<?= htmlspecialchars($item['name']) ?> - Image <?= $idx + 1 ?>" data-index="<?= $idx ?>"
              onerror="this.style.display='none';">
          <?php endforeach; ?>

          <?php if (count($webImages) > 1): ?>
            <!-- Navigation Arrows -->
            <button class="btn btn-light btn-sm position-absolute start-0 top-50 translate-middle-y ms-2"
              style="opacity: 0.8; z-index: 10;" onclick="navigateImage(<?= $item['id'] ?>, -1)">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-light btn-sm position-absolute end-0 top-50 translate-middle-y me-2"
              style="opacity: 0.8; z-index: 10;" onclick="navigateImage(<?= $item['id'] ?>, 1)">
              <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Image Counter -->
            <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2" style="z-index: 10;">
              <span class="badge bg-white" id="imageCounter<?= $item['id'] ?>">1 / <?= count($webImages) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Zoom Button -->
        <button class="btn btn-light btn-sm position-absolute top-0 start-0 m-2" style="opacity: 0.8; z-index: 10;"
          onclick="openImageViewer(<?= $item['id'] ?>, <?= htmlspecialchars(json_encode($webImages)) ?>)">
          <i class="fas fa-search-plus"></i>
        </button>

        <!-- Type Badge -->
        <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
          <span
            class="badge <?= $item['item_type'] === 'room' ? 'bg-primary' : ($item['item_type'] === 'facility' ? 'bg-success' : 'bg-info') ?> px-3 py-2">
            <i
              class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> me-1"></i>
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
          <button type="button" class="btn btn-outline-primary flex-fill edit-toggle-btn"
            data-item-id="<?= $item['id'] ?>">
            <i class="fas fa-edit me-1"></i>Edit
          </button>
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
            data-bs-target="#deleteModal<?= $item['id'] ?>">
            <i class="fas fa-trash me-1"></i>Delete
          </button>
        </div>

        <!-- Hidden Edit Form -->
        <div class="edit-form-container mt-3" id="editForm<?= $item['id'] ?>" style="display: none;">
          <form method="POST" action="" enctype="multipart/form-data" class="border-top pt-3">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">

            <div class="row">
              <div class="col-12 mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($item['name']) ?>"
                  required>
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
                <input type="text" class="form-control" name="room_number"
                  value="<?= htmlspecialchars($item['room_number'] ?? '') ?>">
              </div>

              <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description"
                  rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Capacity</label>
                <input type="number" class="form-control" name="capacity" value="<?= $item['capacity'] ?>" required>
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Price (₱)</label>
                <input type="number" class="form-control" name="price" value="<?= $item['price'] ?>" step="0.01" required>
              </div>

              <div class="col-12 mb-3">
                <label class="form-label">Add-ons (optional)</label>
                <?php
                // Normalize addons: support JSON array of objects, serialized PHP arrays, or comma-separated names
                $addons = [];
                if (!empty($item['addons'])) {
                  $raw = $item['addons'];

                  // try JSON
                  $source = null;
                  $decoded = json_decode($raw, true);
                  if (is_array($decoded)) {
                    $source = $decoded;
                  } else {
                    // try PHP serialized data
                    $un = @unserialize($raw);
                    if (is_array($un)) {
                      $source = $un;
                    } else {
                      // fallback: comma-separated string
                      $parts = array_filter(array_map('trim', explode(',', $raw)));
                      $source = $parts;
                    }
                  }

                  // normalize each entry into ['name','price','type']
                  foreach ((array)$source as $a) {
                    if (is_string($a)) {
                      $addons[] = ['name' => $a, 'price' => '', 'type' => 'Per Event'];
                    } elseif (is_array($a)) {
                      $addons[] = [
                        'name' => $a['name'] ?? $a['title'] ?? '',
                        'price' => isset($a['price']) ? $a['price'] : ($a['amount'] ?? ''),
                        'type' => $a['type'] ?? ($a['billing'] ?? 'Per Event')
                      ];
                    }
                  }
                }
                ?>

                <div class="mb-2" id="addonsContainer<?= $item['id'] ?>">
                  <?php if (!empty($addons)): ?>
                    <?php foreach ($addons as $aidx => $a): ?>
                      <div class="card p-2 mb-2 addon-row" data-idx="<?= $aidx ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <strong class="me-2"><?= htmlspecialchars($a['name'] ?? '') ?: 'Add-on' ?></strong>
                          <button type="button" class="btn btn-danger btn-sm addon-remove-btn" title="Remove add-on"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="row g-2">
                          <div class="col-12 mb-2">
                            <input type="text" name="addons[name][]" class="form-control" placeholder="Add-on name" value="<?= htmlspecialchars($a['name'] ?? '') ?>">
                          </div>
                          <div class="col-6 mb-2">
                            <?php
                              $priceVal = $a['price'] ?? '';
                              // if numeric, format as currency to match client formatting (JS will strip on focus)
                              if ($priceVal !== '' && is_numeric(str_replace([',',' '],'', $priceVal))) {
                                $priceVal = '₱' . number_format((float)str_replace([',',' '],'', $priceVal), 2);
                              }
                            ?>
                            <input type="text" name="addons[price][]" class="form-control addon-price-input" placeholder="Price" value="<?= htmlspecialchars($priceVal) ?>">
                          </div>
                          <div class="col-6 mb-2">
                            <select name="addons[type][]" class="form-select">
                              <option value="Per Event" <?= ($a['type'] ?? '') === 'Per Event' ? 'selected' : '' ?>>Per Event</option>
                              <option value="Per Day" <?= ($a['type'] ?? '') === 'Per Day' ? 'selected' : '' ?>>Per Day</option>
                              <option value="Per Night" <?= ($a['type'] ?? '') === 'Per Night' ? 'selected' : '' ?>>Per Night</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
                <div class="d-grid">
                  <button type="button" class="btn btn-outline-dark" id="addAddonBtn<?= $item['id'] ?>">
                    <i class="fas fa-plus me-1"></i> Add Add-on
                  </button>
                </div>

                <script>
                  (function(){
                    const container = document.getElementById('addonsContainer<?= $item['id'] ?>');
                    const addBtn = document.getElementById('addAddonBtn<?= $item['id'] ?>');
                    const MAX_ADDONS = 10;

                    function createCard(name = '', price = '', type = 'Per Event'){
                      const card = document.createElement('div');
                      card.className = 'card p-2 mb-2 addon-row';
                      card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start mb-2">
                          <strong class="me-2">${name || 'Add-on'}</strong>
                          <button type="button" class="btn btn-danger btn-sm addon-remove-btn" title="Remove add-on"><i class="fas fa-trash"></i></button>
                        </div>
                        <div class="row g-2">
                          <div class="col-12 mb-2">
                            <input type="text" name="addons[name][]" class="form-control" placeholder="Add-on name" value="${name}">
                          </div>
                          <div class="col-6 mb-2">
                            <input type="text" name="addons[price][]" class="form-control addon-price-input" placeholder="Price" value="${price}">
                          </div>
                          <div class="col-6 mb-2">
                            <select name="addons[type][]" class="form-select">
                              <option value="Per Event">Per Event</option>
                              <option value="Per Day">Per Day</option>
                              <option value="Per Night">Per Night</option>
                            </select>
                          </div>
                        </div>
                      `;
                      const sel = card.querySelector('select');
                      sel.value = type;
                      const removeBtn = card.querySelector('.addon-remove-btn');
                      removeBtn.addEventListener('click', () => card.remove());
                      const priceInput = card.querySelector('.addon-price-input');
                      attachPriceFormatting(priceInput);

                      // keep the card header in sync with the name input
                      const header = card.querySelector('strong');
                      const nameInput = card.querySelector('input[name="addons[name][]"]');
                      if (nameInput && header) {
                        header.textContent = nameInput.value || 'Add-on';
                        nameInput.addEventListener('input', function() {
                          header.textContent = this.value || 'Add-on';
                        });
                      }

                      return card;
                    }

                    function attachPriceFormatting(input){
                      if (!input) return;
                      input.addEventListener('focus', function(){
                        // remove formatting
                        this.value = this.value.replace(/[^0-9.\-]/g, '');
                      });
                      input.addEventListener('blur', function(){
                        const v = this.value.replace(/[^0-9.\-]/g, '');
                        if (v === '') return;
                        const num = parseFloat(v);
                        if (!isNaN(num)) {
                          this.value = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 }).format(num);
                        }
                      });
                    }

                    if (addBtn) addBtn.addEventListener('click', function(){
                      const current = container.querySelectorAll('.addon-row').length;
                      if (current >= MAX_ADDONS) {
                        alert('Maximum of ' + MAX_ADDONS + ' add-ons allowed.');
                        return;
                      }
                      const card = createCard();
                      container.appendChild(card);
                      card.querySelector('input[name="addons[name][]"]').focus();
                    });

                    // attach remove handlers & price formatting for existing
                    container.querySelectorAll('.addon-remove-btn').forEach(btn => btn.addEventListener('click', function(){
                      const row = this.closest('.addon-row'); if (row) row.remove();
                    }));
                    container.querySelectorAll('.addon-price-input').forEach(attachPriceFormatting);

                    // attach name->header sync for existing rows
                    container.querySelectorAll('input[name="addons[name][]"]').forEach(inp => {
                      const row = inp.closest('.addon-row');
                      const hdr = row ? row.querySelector('strong') : null;
                      if (hdr) hdr.textContent = inp.value || 'Add-on';
                      inp.addEventListener('input', function() { if (hdr) hdr.textContent = this.value || 'Add-on'; });
                    });

                    // form validation on submit: numeric price and max addons
                    const form = document.querySelector('#editForm<?= $item['id'] ?> form');
                    if (form) {
                      form.addEventListener('submit', function(e){
                        const rows = container.querySelectorAll('.addon-row');
                        if (rows.length > MAX_ADDONS) {
                          alert('You have too many add-ons. Maximum allowed: ' + MAX_ADDONS);
                          e.preventDefault(); return;
                        }
                        let ok = true;
                        rows.forEach(r => {
                          const pname = r.querySelector('input[name="addons[price][]"]');
                          if (pname && pname.value.trim() !== '') {
                            const val = pname.value.replace(/[^0-9.\-]/g, '');
                            if (isNaN(parseFloat(val))) {
                              ok = false;
                            }
                          }
                        });
                        if (!ok) { alert('Please provide valid numeric prices for add-ons (or leave price empty).'); e.preventDefault(); }
                      });
                    }
                  })();
                </script>

                <label class="form-label">Images</label>
                <?php
                // Handle both old single image and new multiple images format
                $images = [];
                if (!empty($item['images'])) {
                  $decoded = json_decode($item['images'], true);
                  if (is_array($decoded)) {
                    $images = $decoded;
                  }
                } elseif (!empty($item['image'])) {
                  // Legacy single image
                  $images = [$item['image']];
                }

                if (!empty($images)):
                  ?>
                  <div class="mb-2 d-flex flex-wrap gap-2" id="currentImages<?= $item['id'] ?>">
                    <?php foreach ($images as $idx => $imgPath):
                      $displayImagePath = $imgPath;
                      $projectRoot = realpath(__DIR__ . '/../../..');
                      $imageFullPath = $projectRoot . '/' . ltrim($displayImagePath, '/');

                      if (file_exists($imageFullPath)) {
                        if (!str_starts_with($displayImagePath, '/') && !str_starts_with($displayImagePath, 'http')) {
                          $displayImagePath = '/' . $displayImagePath;
                        }
                        ?>
                        <div class="position-relative image-entry" data-image-path="<?= htmlspecialchars($imgPath) ?>" data-image-index="<?= $idx ?>" id="imageEntry<?= $item['id'] ?>_<?= $idx ?>">
                          <img src="<?= htmlspecialchars($displayImagePath) ?>" alt="Image <?= $idx + 1 ?>"
                            style="width: 80px; height: 80px; object-fit: cover;" class="rounded preview-image" id="previewImg<?= $item['id'] ?>_<?= $idx ?>">

                          <!-- Hidden replace input for this image -->
                          <input type="file" name="replace_images[<?= $item['id'] ?>][<?= $idx ?>]" accept="image/*" class="d-none replace-input" id="replaceInput<?= $item['id'] ?>_<?= $idx ?>">

                          <div class="btn-group position-absolute top-0 end-0 m-1">
                            <button type="button" class="btn btn-danger btn-sm p-1" style="font-size: 10px;" onclick="removeImage<?= $item['id'] ?>('<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>')" title="Remove image">
                              <i class="fas fa-times"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm p-1" style="font-size: 10px;" onclick="document.getElementById('replaceInput<?= $item['id'] ?>_<?= $idx ?>').click()" title="Replace image">
                              <i class="fas fa-exchange-alt"></i>
                            </button>
                          </div>

                          <span class="badge bg-primary position-absolute bottom-0 start-0 m-1"><?= $idx + 1 ?></span>
                        </div>
                      <?php }endforeach; ?>
                  </div>
                  <input type="hidden" name="removed_images" id="removedImages<?= $item['id'] ?>" value="">
                  <script>
                    function removeImage<?= $item['id'] ?>(imagePath) {
                      const container = document.getElementById('currentImages<?= $item['id'] ?>');
                      if (!container) return;
                      const currentEntries = container.querySelectorAll('.image-entry');
                      // Prevent removing the last remaining image
                      if (currentEntries.length <= 1) {
                        alert('Cannot remove the last image. Replace it instead or add a new image first.');
                        return;
                      }
                      const imageDiv = container.querySelector(`[data-image-path="${imagePath}"]`);
                      if (imageDiv) {
                        imageDiv.remove();
                        const removedInput = document.getElementById('removedImages<?= $item['id'] ?>');
                        const removed = removedInput.value ? removedInput.value.split(',') : [];
                        removed.push(imagePath);
                        removedInput.value = removed.join(',');
                      }
                    }

                    // preview for replace inputs
                    (function(){
                      const form = document.getElementById('editForm<?= $item['id'] ?>');
                      if (!form) return;
                      const inputs = form.querySelectorAll('.replace-input');
                      inputs.forEach(input => {
                        input.addEventListener('change', function(){
                          const file = this.files && this.files[0];
                          if (!file) return;
                          const parts = this.id.replace('replaceInput', '').split('_');
                          const previewId = 'previewImg' + parts[0] + '_' + parts[1];
                          const preview = document.getElementById(previewId);
                          if (preview) {
                            preview.src = URL.createObjectURL(file);
                            // mark original for removal so backend can replace it when processing
                            const imageEntry = this.closest('.image-entry');
                            if (imageEntry) {
                              const existingPath = imageEntry.getAttribute('data-image-path');
                              const removedInput = document.getElementById('removedImages<?= $item['id'] ?>');
                              const removed = removedInput.value ? removedInput.value.split(',') : [];
                              if (!removed.includes(existingPath)) {
                                removed.push(existingPath);
                                removedInput.value = removed.join(',');
                              }
                            }
                          }
                        });
                      });
                    })();
                  </script>
                <?php endif; ?>
                <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                <div class="form-text">Add new images or leave empty to keep current images (max 10 total). You cannot remove the last existing image; replace it instead.</div>
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

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" style="z-index: 99999;">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0 text-white">
        <h5 class="modal-title">Image Viewer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body position-relative p-0" style="min-height: 500px;">
        <div class="d-flex align-items-center justify-content-center" style="min-height: 500px; position: relative;">
          <img id="viewerImage" src="" alt="Viewer Image"
            style="max-width: 100%; max-height: 80vh; object-fit: contain; transform-origin: center center; transition: transform 0.3s;">

          <!-- Navigation Arrows -->
          <button class="btn btn-light position-absolute start-0 top-50 translate-middle-y ms-3" id="viewerPrevBtn"
            onclick="viewerNavigate(-1)" style="z-index: 10;">
            <i class="fas fa-chevron-left fa-2x"></i>
          </button>
          <button class="btn btn-light position-absolute end-0 top-50 translate-middle-y me-3" id="viewerNextBtn"
            onclick="viewerNavigate(1)" style="z-index: 10;">
            <i class="fas fa-chevron-right fa-2x"></i>
          </button>
        </div>
      </div>
      <div class="modal-footer border-0 text-white justify-content-between">
        <div>
          <span id="viewerCounter" class="badge bg-secondary">1 / 1</span>
        </div>
        <div class="btn-group">
          <button class="btn btn-outline-light" onclick="zoomImage(-0.2)" title="Zoom Out">
            <i class="fas fa-search-minus"></i>
          </button>
          <button class="btn btn-outline-light" onclick="resetZoom()" title="Reset Zoom">
            <i class="fas fa-sync-alt"></i>
          </button>
          <button class="btn btn-outline-light" onclick="zoomImage(0.2)" title="Zoom In">
            <i class="fas fa-search-plus"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Add-on card tweaks */
  .addon-row { border: 1px solid rgba(0,0,0,0.06); border-radius: 6px; }
  .addon-row strong { font-size: 0.95rem; }
  .addon-price-input { text-align: right; }
  #addImagesPreview img { border: 1px solid #e9ecef; padding: 2px; }
  .btn-outline-dark { border-radius: 8px; }
</style>

<script>
  // Image carousel navigation
  const carouselState = {};

  function navigateImage(itemId, direction) {
    if (!carouselState[itemId]) {
      carouselState[itemId] = { currentIndex: 0 };
    }

    const images = document.querySelectorAll(`.carousel-image-${itemId}`);
    if (images.length <= 1) return;

    // Hide current image
    images[carouselState[itemId].currentIndex].style.opacity = '0';

    // Calculate new index
    carouselState[itemId].currentIndex += direction;
    if (carouselState[itemId].currentIndex < 0) {
      carouselState[itemId].currentIndex = images.length - 1;
    } else if (carouselState[itemId].currentIndex >= images.length) {
      carouselState[itemId].currentIndex = 0;
    }

    // Show new image
    images[carouselState[itemId].currentIndex].style.opacity = '1';

    // Update counter
    const counter = document.getElementById(`imageCounter${itemId}`);
    if (counter) {
      counter.textContent = `${carouselState[itemId].currentIndex + 1} / ${images.length}`;
    }
  }

  // Image viewer
  let viewerImages = [];
  let viewerCurrentIndex = 0;
  let viewerZoom = 1;

  function openImageViewer(itemId, images) {
    viewerImages = images;
    viewerCurrentIndex = carouselState[itemId]?.currentIndex || 0;
    viewerZoom = 1;

    updateViewerImage();

    const modal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
    modal.show();
  }

  function updateViewerImage() {
    const img = document.getElementById('viewerImage');
    const counter = document.getElementById('viewerCounter');
    const prevBtn = document.getElementById('viewerPrevBtn');
    const nextBtn = document.getElementById('viewerNextBtn');

    if (viewerImages.length > 0) {
      img.src = viewerImages[viewerCurrentIndex];
      img.style.transform = `scale(${viewerZoom})`;
      counter.textContent = `${viewerCurrentIndex + 1} / ${viewerImages.length}`;

      // Show/hide navigation buttons
      if (viewerImages.length <= 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'none';
      } else {
        prevBtn.style.display = 'block';
        nextBtn.style.display = 'block';
      }
    }
  }

  function viewerNavigate(direction) {
    viewerCurrentIndex += direction;
    if (viewerCurrentIndex < 0) {
      viewerCurrentIndex = viewerImages.length - 1;
    } else if (viewerCurrentIndex >= viewerImages.length) {
      viewerCurrentIndex = 0;
    }
    updateViewerImage();
  }

  function zoomImage(delta) {
    viewerZoom += delta;
    if (viewerZoom < 0.5) viewerZoom = 0.5;
    if (viewerZoom > 3) viewerZoom = 3;
    document.getElementById('viewerImage').style.transform = `scale(${viewerZoom})`;
  }

  function resetZoom() {
    viewerZoom = 1;
    document.getElementById('viewerImage').style.transform = `scale(1)`;
  }

  // Keyboard navigation for viewer
  document.addEventListener('keydown', function (e) {
    const modal = document.getElementById('imageViewerModal');
    if (modal.classList.contains('show')) {
      if (e.key === 'ArrowLeft') {
        viewerNavigate(-1);
      } else if (e.key === 'ArrowRight') {
        viewerNavigate(1);
      } else if (e.key === '+' || e.key === '=') {
        zoomImage(0.2);
      } else if (e.key === '-') {
        zoomImage(-0.2);
      } else if (e.key === '0') {
        resetZoom();
      }
    }
  });
</script>