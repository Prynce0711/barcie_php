<?php /* migrated from Components/Guest/js/guest-gallery-core.js */ ?>
<script>
  function openImageGallery(
    images,
    startIndex = 0,
    title = "Image Gallery",
    returnToItemId = null,
  ) {
    galleryImages = images;
    currentGalleryIndex = startIndex;
    currentZoomLevel = 1;
    galleryReturnItemId = returnToItemId || null;

    // Update modal title
    const titleEl = document.getElementById("imageGalleryLabel");
    if (titleEl) titleEl.textContent = title;

    // Update image counter
    const totalEl = document.getElementById("totalImages");
    if (totalEl) totalEl.textContent = images.length;

    // Show the image
    showGalleryImage(currentGalleryIndex);

    // Generate thumbnails
    generateThumbnails();

    // Show/hide navigation arrows based on image count
    const prevBtn = document.getElementById("galleryPrevBtn");
    const nextBtn = document.getElementById("galleryNextBtn");

    if (prevBtn && nextBtn) {
      if (images.length <= 1) {
        prevBtn.style.display = "none";
        nextBtn.style.display = "none";
      } else {
        prevBtn.style.display = "block";
        nextBtn.style.display = "block";
      }
    }

    // Ensure modal is attached to body so it overlaps everything
    const modalEl = document.getElementById("imageGalleryModal");
    if (modalEl && modalEl.parentNode !== document.body) {
      document.body.appendChild(modalEl);
    }

    // Open modal
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Setup keyboard navigation
    setupGalleryKeyboardNavigation();
  }

  // Helper used from the item details modal: hide details first, then open gallery and
  // remember which item to return to when the gallery closes.
  function openGalleryFromModal(
    images,
    startIndex = 0,
    title = "Image Gallery",
    returnItemId = null,
  ) {
    const detailsModalEl = document.getElementById("itemDetailsModal");
    try {
      if (detailsModalEl) {
        const bs =
          bootstrap.Modal.getInstance(detailsModalEl) ||
          new bootstrap.Modal(detailsModalEl);
        bs.hide();
      }
    } catch (e) {
      // ignore
    }
    openImageGallery(images, startIndex, title, returnItemId);
  }

  function showGalleryImage(index) {
    if (index < 0 || index >= galleryImages.length) return;

    currentGalleryIndex = index;
    currentZoomLevel = 1;

    const mainImage = document.getElementById("galleryMainImage");
    mainImage.src = galleryImages[index];
    mainImage.style.transform = "scale(1)";
    mainImage.alt = `Image ${index + 1}`;


    document.getElementById("currentImageIndex").textContent = index + 1;

    updateThumbnailSelection();
  }

  function generateThumbnails() {
    const thumbnailContainer = document.getElementById("galleryThumbnails");
    thumbnailContainer.innerHTML = "";

    galleryImages.forEach((img, index) => {
      const thumb = document.createElement("div");
      thumb.className = "gallery-thumbnail";
      thumb.style.cssText = `
      display: inline-block;
      width: 80px;
      height: 60px;
      cursor: pointer;
      border: 3px solid transparent;
      border-radius: 5px;
      overflow: hidden;
      transition: border-color 0.3s ease;
    `;

      const thumbImg = document.createElement("img");
      thumbImg.src = img;
      thumbImg.style.cssText = "width: 100%; height: 100%; object-fit: cover;";
      thumbImg.onerror = function () {
        this.src = "public/images/imageBg/barcie_logo.jpg";
      };

      thumb.appendChild(thumbImg);

      thumb.addEventListener("click", () => {
        showGalleryImage(index);
      });

      thumbnailContainer.appendChild(thumb);
    });

    updateThumbnailSelection();
  }

  function updateThumbnailSelection() {
    const thumbnails = document.querySelectorAll(".gallery-thumbnail");
    thumbnails.forEach((thumb, index) => {
      if (index === currentGalleryIndex) {
        thumb.style.borderColor = "#007bff";
      } else {
        thumb.style.borderColor = "transparent";
      }
    });
  }

  function navigateGallery(direction) {
    let newIndex = currentGalleryIndex + direction;


    if (newIndex < 0) {
      newIndex = galleryImages.length - 1;
    } else if (newIndex >= galleryImages.length) {
      newIndex = 0;
    }

    showGalleryImage(newIndex);
  }

  function zoomImage(zoomIn) {
    const mainImage = document.getElementById("galleryMainImage");

    if (zoomIn) {
      currentZoomLevel = Math.min(currentZoomLevel + 0.25, 3);
    } else {
      currentZoomLevel = Math.max(currentZoomLevel - 0.25, 0.5);
    }

    mainImage.style.transform = `scale(${currentZoomLevel})`;
    mainImage.style.cursor = currentZoomLevel > 1 ? "move" : "default";
  }


</script>