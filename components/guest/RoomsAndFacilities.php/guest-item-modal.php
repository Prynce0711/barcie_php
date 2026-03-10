<?php /* migrated from Components/Guest/js/guest-item-modal.js */ ?>
<script>
function populateItemModal(modal, item) {
  const modalBody = modal.querySelector("#itemDetailsBody");
  const modalTitle = modal.querySelector("#itemDetailsModalLabel");
  const bookNowBtn = modal.querySelector("#modalBookNowBtn");

  modalTitle.textContent = `${item.name} - Details`;

  // Parse images array
  let images = [];
  if (item.images && item.images !== "") {
    try {
      images =
        typeof item.images === "string" ? JSON.parse(item.images) : item.images;
      if (!Array.isArray(images)) images = [];
    } catch (e) {
      images = [];
    }
  }

  // Fallback to single image
  if (images.length === 0 && item.image && item.image.trim() !== "") {
    images = [item.image];
  }

  // Default image
  if (images.length === 0) {
    images = ["public/images/imageBg/barcie_logo.jpg"];
  }

  // Normalize image paths: ensure they're absolute from the site root
  images = images.map((img) => {
    if (typeof img !== "string" || img.trim() === "")
      return "public/images/imageBg/barcie_logo.jpg";
    if (img.startsWith("http://") || img.startsWith("https://")) return img;
    // Keep paths folder-relative so moving the app directory does not break assets.
    return img.replace(/^\/+/, "");
  });

  const detailsHtml = `
    <div class="row">
      <div class="col-md-6">
        <div class="position-relative" style="cursor: pointer;" onclick="openGalleryFromModal(${JSON.stringify(images).replace(/"/g, "&quot;")}, 0, '${item.name.replace(/'/g, "\\'")}', ${item.id})">
          <img src="${images[0]}" class="img-fluid rounded mb-3" alt="${item.name}" style="max-height: 300px; width: 100%; object-fit: cover;" onerror="this.src='public/images/imageBg/barcie_logo.jpg';">
          ${
            images.length > 1
              ? `
            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 8px 12px; border-radius: 20px; font-size: 14px;">
              <i class="fas fa-images me-2"></i>${images.length} Photos
            </div>
            <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: white; padding: 8px 12px; border-radius: 20px; font-size: 12px;">
              <i class="fas fa-search-plus me-1"></i>Click to view gallery
            </div>
          `
              : ""
          }
        </div>
      </div>
      <div class="col-md-6">
        <h4 class="mb-3">${item.name}</h4>
        
        <div class="detail-item mb-2">
          <strong><i class="fas fa-tag me-2"></i>Type:</strong>
          <span class="badge bg-primary ms-2">${item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1)}</span>
        </div>
        
        ${
          item.room_number
            ? `
          <div class="detail-item mb-2">
            <strong><i class="fas fa-door-open me-2"></i>Room Number:</strong>
            <span class="ms-2">${item.room_number}</span>
          </div>
        `
            : ""
        }
        
        <div class="detail-item mb-2">
          <strong><i class="fas fa-users me-2"></i>Capacity:</strong>
          <span class="ms-2">${item.capacity} ${item.item_type === "room" ? "persons" : "people"}</span>
        </div>
        
        <div class="detail-item mb-3">
          <strong><i class="fas fa-peso-sign me-2"></i>Price:</strong>
          <span class="ms-2 text-success fw-bold">₱${parseInt(item.price).toLocaleString()}${item.item_type === "room" ? "/night" : "/day"}</span>
        </div>
        
        <div class="availability-status mb-3">
          <strong><i class="fas fa-calendar-check me-2"></i>Availability:</strong>
          <span class="badge bg-success ms-2">Available for booking</span>
        </div>
      </div>
    </div>
    
    <div class="row mt-3">
      <div class="col-12">
        <h5><i class="fas fa-info-circle me-2"></i>Description</h5>
        <p class="text-muted">${item.description || "Comfortable accommodation with modern amenities and excellent service."}</p>
      </div>
    </div>
    
    <div class="row mt-3">
      <div class="col-12">
        <h5><i class="fas fa-star me-2"></i>Features & Amenities</h5>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-unstyled">
              ${
                item.item_type === "room"
                  ? `
                <li><i class="fas fa-wifi text-success me-2"></i>Free WiFi</li>
                <li><i class="fas fa-snowflake text-success me-2"></i>Air Conditioning</li>
                <li><i class="fas fa-tv text-success me-2"></i>Cable TV</li>
              `
                  : `
                <li><i class="fas fa-utensils text-success me-2"></i>Event Catering Available</li>
                <li><i class="fas fa-microphone text-success me-2"></i>Audio/Visual Equipment</li>
                <li><i class="fas fa-parking text-success me-2"></i>Parking Space</li>
              `
              }
            </ul>
          </div>
          <div class="col-md-6">
            <ul class="list-unstyled">
              <li><i class="fas fa-broom text-success me-2"></i>Daily Housekeeping</li>
              <li><i class="fas fa-phone text-success me-2"></i>24/7 Support</li>
              <li><i class="fas fa-shield-alt text-success me-2"></i>Secure Environment</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  `;

  modalBody.innerHTML = detailsHtml;

  // Setup Book Now button in modal
  bookNowBtn.onclick = function () {
    modal.querySelector('[data-bs-dismiss="modal"]').click(); // Close modal
    redirectToBooking(item.id);
  };
}

// Redirect to booking section with pre-filled item

</script>
