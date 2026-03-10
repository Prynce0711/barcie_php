<?php /* migrated from Components/Guest/js/guest-gallery-bindings.js */ ?>
<script>
  function resetZoom() {
    currentZoomLevel = 1;
    const mainImage = document.getElementById("galleryMainImage");
    mainImage.style.transform = "scale(1)";
    mainImage.style.cursor = "default";
  }

  function setupGalleryKeyboardNavigation() {
    const modal = document.getElementById("imageGalleryModal");

    const keyHandler = function (e) {
      if (!modal.classList.contains("show")) return;

      switch (e.key) {
        case "ArrowLeft":
          e.preventDefault();
          navigateGallery(-1);
          break;
        case "ArrowRight":
          e.preventDefault();
          navigateGallery(1);
          break;
        case "+":
        case "=":
          e.preventDefault();
          zoomImage(true);
          break;
        case "-":
        case "_":
          e.preventDefault();
          zoomImage(false);
          break;
        case "0":
          e.preventDefault();
          resetZoom();
          break;
        case "Escape":
          // Let Bootstrap handle modal closing
          break;
      }
    };

    // Remove existing listener if any
    document.removeEventListener("keydown", keyHandler);
    document.addEventListener("keydown", keyHandler);
  }

  // Setup gallery button event listeners when DOM is ready
  document.addEventListener("DOMContentLoaded", function () {
    // Navigation buttons
    const prevBtn = document.getElementById("galleryPrevBtn");
    const nextBtn = document.getElementById("galleryNextBtn");

    if (prevBtn) {
      prevBtn.addEventListener("click", () => navigateGallery(-1));
    }

    if (nextBtn) {
      nextBtn.addEventListener("click", () => navigateGallery(1));
    }

    // Zoom buttons
    const zoomInBtn = document.getElementById("zoomInBtn");
    const zoomOutBtn = document.getElementById("zoomOutBtn");
    const zoomResetBtn = document.getElementById("zoomResetBtn");

    if (zoomInBtn) {
      zoomInBtn.addEventListener("click", () => zoomImage(true));
    }

    if (zoomOutBtn) {
      zoomOutBtn.addEventListener("click", () => zoomImage(false));
    }

    if (zoomResetBtn) {
      zoomResetBtn.addEventListener("click", resetZoom);
    }

    // Image dragging when zoomed
    const mainImage = document.getElementById("galleryMainImage");
    if (mainImage) {
      let isDragging = false;
      let startX, startY, scrollLeft, scrollTop;

      mainImage.addEventListener("mousedown", function (e) {
        if (currentZoomLevel > 1) {
          isDragging = true;
          startX = e.pageX;
          startY = e.pageY;
          mainImage.style.cursor = "grabbing";
        }
      });

      mainImage.addEventListener("mousemove", function (e) {
        if (!isDragging || currentZoomLevel <= 1) return;
        e.preventDefault();

        const x = e.pageX - startX;
        const y = e.pageY - startY;

        // Update transform origin for panning effect
        mainImage.style.transformOrigin = `${50 - x / 10}% ${50 - y / 10}%`;
      });

      mainImage.addEventListener("mouseup", function () {
        isDragging = false;
        if (currentZoomLevel > 1) {
          mainImage.style.cursor = "move";
        }
      });

      mainImage.addEventListener("mouseleave", function () {
        isDragging = false;
      });
    }

    // Reset zoom when modal closes
    const modal = document.getElementById("imageGalleryModal");
    if (modal) {
      modal.addEventListener("hidden.bs.modal", function () {
        resetZoom();
        // If gallery was opened from an item details modal, reopen that details view
        if (galleryReturnItemId) {
          const returnId = galleryReturnItemId;
          galleryReturnItemId = null;
          // small defer to ensure modal stack is stable
          setTimeout(() => {
            try {
              showItemDetails(returnId);
            } catch (e) {
              console.warn("Failed to reopen item details", e);
            }
          }, 120);
        }
      });
    }
  });

  // Export gallery functions
  window.openImageGallery = openImageGallery;
  window.navigateGallery = navigateGallery;
  window.zoomImage = zoomImage;
  window.resetZoom = resetZoom;
  window.openGalleryFromModal = openGalleryFromModal;

</script>