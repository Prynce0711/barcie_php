<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="editItemModalLabel">
          <i class="fas fa-edit me-2"></i>Edit Room / Facility
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data" id="editItemForm">
        <div class="modal-body">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editItemId">
          <input type="hidden" name="existing_images" id="editExistingImages">

          <!-- Basic Information -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8 mb-3">
                  <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="name" id="editItemName" required>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                  <select name="item_type" class="form-select" id="editItemType">
                    <option value="room">Room</option>
                    <option value="facility">Facility</option>
                    <option value="amenities">Amenities</option>
                  </select>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label fw-bold">Room Number</label>
                  <input type="text" class="form-control" name="room_number" id="editRoomNumber"
                    placeholder="e.g., 101">
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label fw-bold">Capacity <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="capacity" id="editCapacity" min="1" required>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label fw-bold">Price (₱) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="price" id="editPrice" step="0.01" min="0" required>
                </div>

                <div class="col-12 mb-3">
                  <label class="form-label fw-bold">Description</label>
                  <textarea class="form-control" name="description" id="editDescription" rows="3"
                    placeholder="Enter a detailed description..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Image Gallery -->
          <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="fas fa-images me-2"></i>Image Gallery</h6>
              <div>
                <button type="button" class="btn btn-sm btn-outline-danger me-2" id="selectAllImagesBtn">
                  <i class="fas fa-check-double me-1"></i>Select All
                </button>
                <span class="badge bg-primary" id="imageCountBadge">0 / 10 images</span>
              </div>
            </div>
            <div class="card-body">
              <!-- Current Images Gallery -->
              <div id="editImageGallery" class="row g-3 mb-3">
                <!-- Images will be loaded here dynamically -->
              </div>

              <!-- Add New Images -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Tips:</strong> Click on an image or use the remove button to select/deselect for deletion. Use
                "Select All" to select all images at once.
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Add New Images</label>
                <input type="file" class="form-control" name="images[]" id="editNewImages" accept="image/*" multiple>
                <div class="form-text">Select multiple images (JPG, PNG, GIF, WebP - max 20MB each)</div>
              </div>

              <!-- Preview New Images -->
              <div id="editNewImagesPreview" class="row g-3 mt-2">
                <!-- New image previews will appear here -->
              </div>

              <!-- Hidden field for removed images -->
              <input type="hidden" name="removed_images" id="editRemovedImages">
            </div>
          </div>

          <!-- Add-ons Section -->
          <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add-ons (Optional)</h6>
              <button type="button" class="btn btn-sm btn-primary" id="editAddAddonBtn">
                <i class="fas fa-plus me-1"></i>Add Add-on
              </button>
            </div>
            <div class="card-body">
              <div id="editAddonsContainer">
                <!-- Add-ons will be loaded here dynamically -->
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* Gallery Image Styles */
  .gallery-image-item {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
    border-radius: 8px;
    overflow: hidden;
  }

  .gallery-image-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .gallery-image-item.selected {
    border-color: #dc3545;
  }

  .gallery-image-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
  }

  .gallery-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(220, 53, 69, 0.7);
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
  }

  .gallery-image-item.selected .gallery-image-overlay {
    display: flex;
  }

  .image-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 10;
  }

  .zoom-icon {
    position: absolute;
    top: 8px;
    right: 40px;
    z-index: 10;
    opacity: 0;
    transition: opacity 0.3s;
  }

  .remove-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 10;
    opacity: 0;
    transition: opacity 0.3s;
  }

  .gallery-image-item:hover .zoom-icon,
  .gallery-image-item:hover .remove-icon {
    opacity: 1;
  }

  /* Add-on Styles */
  .addon-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #f8f9fa;
    transition: all 0.2s;
  }

  .addon-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editItemModal');
    const editForm = document.getElementById('editItemForm');
    const imageGallery = document.getElementById('editImageGallery');
    const removedImagesInput = document.getElementById('editRemovedImages');
    const newImagesInput = document.getElementById('editNewImages');
    const newImagesPreview = document.getElementById('editNewImagesPreview');
    const addonsContainer = document.getElementById('editAddonsContainer');
    const addAddonBtn = document.getElementById('editAddAddonBtn');

    let currentImages = [];
    let removedImages = [];
    let currentItemId = null;
    const MAX_IMAGES = 10;

    function normalizeImagePath(path) {
      if (!path) return '';
      if (/^https?:\/\//i.test(path)) return path;
      let normalized = String(path).replace(/\\/g, '/').replace(/^\/+/, '');
      if (normalized && !normalized.includes('/') && /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(normalized)) {
        normalized = 'uploads/' + normalized;
      }
      return normalized;
    }

    // Function to open edit modal with item data
    window.openEditModal = function (itemId) {
      currentItemId = itemId;
      removedImages = [];
      removedImagesInput.value = '';
      newImagesInput.value = '';
      newImagesPreview.innerHTML = '';

      // Fetch item data
      const itemCard = document.querySelector(`.item-card[data-type]`);
      const editButton = document.querySelector(`[data-item-id="${itemId}"]`);
      if (!editButton) {
        console.error('Edit button not found for item:', itemId);
        return;
      }

      const card = editButton.closest('.item-card');
      if (!card) {
        console.error('Item card not found for item:', itemId);
        return;
      }

      // Get item data from the card or make an AJAX call
      // For now, we'll extract from the existing inline edit form
      const editFormInline = document.getElementById(`editForm${itemId}`);
      if (!editFormInline) {
        console.error('Inline edit form not found for item:', itemId);
        return;
      }

      // Extract data from inline form
      document.getElementById('editItemId').value = itemId;
      document.getElementById('editItemName').value = editFormInline.querySelector('[name="name"]').value;
      document.getElementById('editItemType').value = editFormInline.querySelector('[name="item_type"]').value;
      document.getElementById('editRoomNumber').value = editFormInline.querySelector('[name="room_number"]').value || '';
      document.getElementById('editDescription').value = editFormInline.querySelector('[name="description"]').value || '';
      document.getElementById('editCapacity').value = editFormInline.querySelector('[name="capacity"]').value;
      document.getElementById('editPrice').value = editFormInline.querySelector('[name="price"]').value;

      // Load images
      loadImageGallery(itemId);

      // Load add-ons
      loadAddons(itemId);

      // Show modal
      const modal = new bootstrap.Modal(editModal);
      modal.show();
    };

    // Load image gallery
    function loadImageGallery(itemId) {
      imageGallery.innerHTML = '';
      currentImages = [];
      removedImages = [];
      removedImagesInput.value = '';

      // Method 1: Try to get images from the data attribute on the item card
      const itemCard = document.querySelector(`.item-card[data-item-id="${itemId}"]`);
      if (itemCard) {
        const imagesData = itemCard.getAttribute('data-images');
        if (imagesData) {
          try {
            const imagesArray = JSON.parse(imagesData);
            if (Array.isArray(imagesArray) && imagesArray.length > 0) {
              imagesArray.forEach((imgPath, index) => {
                const normalized = normalizeImagePath(imgPath);
                currentImages.push(normalized);
                addImageToGallery(normalized, normalized, index);
              });
            }
          } catch (e) {
            console.error('Error parsing images data:', e);
          }
        }
      }

      // Method 2: Try to get images from the inline edit form's current images container
      if (currentImages.length === 0) {
        const imageContainer = document.getElementById(`currentImages${itemId}`);
        if (imageContainer) {
          const imageEntries = imageContainer.querySelectorAll('.image-entry');
          imageEntries.forEach((entry, index) => {
            const imgPath = entry.getAttribute('data-image-path');
            const imgElement = entry.querySelector('img');
            if (!imgElement) return;

            const imgSrc = imgElement.src;
            const normalizedPath = normalizeImagePath(imgPath);
            currentImages.push(normalizedPath);

            addImageToGallery(normalizedPath, imgSrc, index);
          });
        }
      }

      // Method 3: If no images found in container, try to get from the carousel in the card
      if (currentImages.length === 0) {
        const carouselImages = document.querySelectorAll(`.carousel-image-${itemId}`);
        if (carouselImages.length > 0) {
          carouselImages.forEach((img, index) => {
            const imgSrc = img.src;
            // Extract relative path from full URL
            let imgPath = imgSrc.replace(window.location.origin, '');
            imgPath = normalizeImagePath(imgPath);

            currentImages.push(imgPath);
            addImageToGallery(imgPath, imgSrc, index);
          });
        }
      }

      // Method 4: Fallback - if still no images, show placeholder
      if (currentImages.length === 0) {
        imageGallery.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="fas fa-images fa-3x mb-2 d-block"></i><p>No images found. Add new images below.</p></div>';
      }

      // Store existing images in hidden field for backend preservation
      document.getElementById('editExistingImages').value = JSON.stringify(currentImages);

      updateImageCount();
    }

    function addImageToGallery(imgPath, imgSrc, index) {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-3';

      // Escape single quotes in paths for onclick handler
      const escapedSrc = imgSrc.replace(/'/g, "\\'");
      const escapedPath = imgPath.replace(/'/g, "\\'");

      col.innerHTML = `
      <div class="gallery-image-item" data-image-path="${imgPath}" data-index="${index}">
        <span class="badge bg-primary image-badge">${index + 1}</span>
        <button type="button" class="btn btn-light btn-sm zoom-icon" onclick="viewImageFullscreen('${escapedSrc}')">
          <i class="fas fa-search-plus"></i>
        </button>
        <button type="button" class="btn btn-danger btn-sm remove-icon">
          <i class="fas fa-times"></i>
        </button>
        <img src="${imgSrc}" alt="Image ${index + 1}" onerror="this.src='public/images/imageBg/barcie_logo.jpg'">
        <div class="gallery-image-overlay">
          <i class="fas fa-trash-alt fa-2x text-white"></i>
        </div>
      </div>
    `;

      const galleryItem = col.querySelector('.gallery-image-item');
      galleryItem.addEventListener('click', function (e) {
        if (e.target.closest('.zoom-icon') || e.target.closest('.remove-icon')) return;
        toggleImageSelection(this);
      });

      const removeBtn = col.querySelector('.remove-icon');
      removeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleImageSelection(galleryItem);
      });

      imageGallery.appendChild(col);
    }

    function toggleImageSelection(galleryItem) {
      const imgPath = galleryItem.getAttribute('data-image-path');
      const isSelected = galleryItem.classList.contains('selected');

      if (isSelected) {
        galleryItem.classList.remove('selected');
        removedImages = removedImages.filter(path => path !== imgPath);
      } else {
        galleryItem.classList.add('selected');
        if (!removedImages.includes(imgPath)) {
          removedImages.push(imgPath);
        }
      }

      removedImagesInput.value = removedImages.join(',');
      updateImageCount();
      updateSelectAllButton();
    }

    const selectAllBtn = document.getElementById('selectAllImagesBtn');
    selectAllBtn.addEventListener('click', function () {
      const galleryItems = imageGallery.querySelectorAll('.gallery-image-item');
      const allSelected = Array.from(galleryItems).every(item => item.classList.contains('selected'));

      if (allSelected) {
        galleryItems.forEach(item => {
          item.classList.remove('selected');
        });
        removedImages = [];
        this.innerHTML = '<i class="fas fa-check-double me-1"></i>Select All';
      } else {
        galleryItems.forEach(item => {
          const imgPath = item.getAttribute('data-image-path');
          item.classList.add('selected');
          if (!removedImages.includes(imgPath)) {
            removedImages.push(imgPath);
          }
        });
        this.innerHTML = '<i class="fas fa-times me-1"></i>Deselect All';
      }

      removedImagesInput.value = removedImages.join(',');
      updateImageCount();
    });

    // Update Select All button text
    function updateSelectAllButton() {
      const galleryItems = imageGallery.querySelectorAll('.gallery-image-item');
      const allSelected = Array.from(galleryItems).every(item => item.classList.contains('selected'));

      if (allSelected && galleryItems.length > 0) {
        selectAllBtn.innerHTML = '<i class="fas fa-times me-1"></i>Deselect All';
      } else {
        selectAllBtn.innerHTML = '<i class="fas fa-check-double me-1"></i>Select All';
      }
    }

    // View image in fullscreen
    window.viewImageFullscreen = function (imgSrc) {
      const modal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
      document.getElementById('viewerImage').src = imgSrc;
      viewerImages = [imgSrc];
      viewerCurrentIndex = 0;
      viewerZoom = 1;
      updateViewerImage();
      modal.show();
    };

    // Preview new images
    newImagesInput.addEventListener('change', function () {
      newImagesPreview.innerHTML = '';
      const files = Array.from(this.files);

      const totalImages = currentImages.length - removedImages.length + files.length;
      if (totalImages > MAX_IMAGES) {
        showToast(`Maximum ${MAX_IMAGES} images allowed. You can upload ${MAX_IMAGES - (currentImages.length - removedImages.length)} more images.`, 'warning');
        this.value = '';
        return;
      }

      files.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
          const col = document.createElement('div');
          col.className = 'col-6 col-md-3';
          col.innerHTML = `
          <div class="gallery-image-item">
            <span class="badge bg-success image-badge">New ${index + 1}</span>
            <img src="${e.target.result}" alt="New Image ${index + 1}">
          </div>
        `;
          newImagesPreview.appendChild(col);
        };
        reader.readAsDataURL(file);
      });

      updateImageCount();
    });

    // Update image count badge
    function updateImageCount() {
      const newFilesCount = newImagesInput.files.length;
      const currentCount = currentImages.length - removedImages.length;
      const totalCount = currentCount + newFilesCount;
      const badge = document.getElementById('imageCountBadge');
      badge.textContent = `${totalCount} / ${MAX_IMAGES} images`;
      badge.className = totalCount > MAX_IMAGES ? 'badge bg-danger' : 'badge bg-primary';
    }

    // Load add-ons
    function loadAddons(itemId) {
      addonsContainer.innerHTML = '';
      const addonsContainerInline = document.getElementById(`addonsContainer${itemId}`);
      if (!addonsContainerInline) {
        console.log('No add-ons container found for item:', itemId);
        return;
      }

      const addonRows = addonsContainerInline.querySelectorAll('.addon-row');
      console.log('Found', addonRows.length, 'add-ons for item:', itemId);
      let loadedCount = 0;

      addonRows.forEach(row => {
        const nameInput = row.querySelector('[name="addons[name][]"]');
        const priceInput = row.querySelector('[name="addons[price][]"]');
        const typeInput = row.querySelector('[name="addons[type][]"]');

        const name = nameInput ? nameInput.value : '';
        const price = priceInput ? priceInput.value : '';
        const type = typeInput ? typeInput.value : 'Per Event';

        if (name) {
          addAddonCard(name, price, type);
          loadedCount += 1;
        }
      });

      // Show message if no add-ons
      if (loadedCount === 0) {
        addonsContainer.innerHTML = '<div class="text-muted text-center py-3"><i class="fas fa-info-circle me-2"></i>No add-ons yet. Click "Add Add-on" to create one.</div>';
      }
    }

    // Add addon card
    addAddonBtn.addEventListener('click', function () {
      if (addonsContainer.children.length >= 10) {
        showToast('Maximum 10 add-ons allowed.', 'warning');
        return;
      }
      addAddonCard();
    });

    function addAddonCard(name = '', price = '', type = 'Per Event') {
      const addonCard = document.createElement('div');
      addonCard.className = 'addon-card';
      addonCard.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="addon-title">${name || 'New Add-on'}</strong>
        <button type="button" class="btn btn-danger btn-sm addon-remove-btn">
          <i class="fas fa-trash"></i>
        </button>
      </div>
      <div class="row g-2">
        <div class="col-12">
          <input type="text" name="addons[name][]" class="form-control addon-name-input" placeholder="Add-on name" value="${name}" required>
        </div>
        <div class="col-6">
          <input type="text" name="addons[price][]" class="form-control addon-price-input" placeholder="Price" value="${price}">
        </div>
        <div class="col-6">
          <select name="addons[type][]" class="form-select">
            <option value="Per Event" ${type === 'Per Event' ? 'selected' : ''}>Per Event</option>
            <option value="Per Day" ${type === 'Per Day' ? 'selected' : ''}>Per Day</option>
            <option value="Per Night" ${type === 'Per Night' ? 'selected' : ''}>Per Night</option>
          </select>
        </div>
      </div>
    `;

      // Remove button handler
      addonCard.querySelector('.addon-remove-btn').addEventListener('click', function () {
        addonCard.remove();
      });

      // Update title on name change
      const nameInput = addonCard.querySelector('.addon-name-input');
      const titleSpan = addonCard.querySelector('.addon-title');
      nameInput.addEventListener('input', function () {
        titleSpan.textContent = this.value || 'New Add-on';
      });

      // Price formatting
      const priceInput = addonCard.querySelector('.addon-price-input');
      priceInput.addEventListener('focus', function () {
        this.value = this.value.replace(/[^0-9.\-]/g, '');
      });
      priceInput.addEventListener('blur', function () {
        const v = this.value.replace(/[^0-9.\-]/g, '');
        if (v === '') return;
        const num = parseFloat(v);
        if (!isNaN(num)) {
          this.value = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            maximumFractionDigits: 2
          }).format(num);
        }
      });

      addonsContainer.appendChild(addonCard);
      nameInput.focus();
    }

    // Form validation
    editForm.addEventListener('submit', function (e) {
      const newFilesCount = newImagesInput.files.length;
      const currentCount = currentImages.length - removedImages.length;
      const totalCount = currentCount + newFilesCount;

      if (totalCount > MAX_IMAGES) {
        e.preventDefault();
        showToast(`Maximum ${MAX_IMAGES} images allowed.`, 'error');
        return;
      }

      // Validate add-ons
      const addonCards = addonsContainer.querySelectorAll('.addon-card');
      if (addonCards.length > 10) {
        e.preventDefault();
        showToast('Maximum 10 add-ons allowed.', 'error');
        return;
      }
    });
  });
</script>