<?php /* migrated from Components/Guest/js/guest-items-loader.js */ ?>
<script>
  async function loadItems() {
    console.log("Guest: Loading items from API...");

    try {
      const res = await fetch("api/items.php");
      console.log("Guest: Response status:", res.status);

      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const response = await res.json();
      console.log("Guest: Raw response:", response);


      let items = [];
      if (response && response.success && Array.isArray(response.items)) {

        items = response.items;
        console.log("Guest: Got wrapped items array:", items.length);
      } else if (Array.isArray(response)) {

        items = response;
        console.log("Guest: Got plain array of items:", items.length);
      } else if (response && Array.isArray(response.data)) {

        items = response.data;
        console.log("Guest: Got data array:", items.length);
      } else {
        console.warn("Guest: Unexpected response format:", response);


        if (response && response.error) {
          throw new Error(response.message || response.error);
        }

        items = [];
      }


      window.allItems = items;

      const container = document.getElementById("cards-grid");
      if (!container) {
        console.error("Guest: Cards grid container not found!");
        return;
      }

      if (items.length === 0) {
        container.innerHTML = `
        <div class="col-12">
          <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <h5>No Rooms or Facilities Available</h5>
            <p>Please check back later or contact us for more information.</p>
          </div>
        </div>
      `;
        return;
      }

      console.log("Guest: Rendering", items.length, "items");
      container.innerHTML = "";

      items.forEach((item, index) => {
        console.log(
          `Guest: Rendering item ${index + 1}:`,
          item.name,
          `(${item.item_type})`,
        );

        const card = document.createElement("div");
        card.classList.add("card");
        card.dataset.type = item.item_type;
        card.dataset.price = item.price || 0;
        card.dataset.itemId = item.id;


        let images = [];
        if (item.images && item.images !== "") {
          try {
            images = JSON.parse(item.images);
            if (!Array.isArray(images)) images = [];
          } catch (e) {
            console.warn("Failed to parse images for item:", item.id);
            images = [];
          }
        }

        if (images.length === 0 && item.image && item.image.trim() !== "") {
          images = [item.image];
        }

        if (images.length === 0) {
          images = ["public/images/imageBg/barcie_logo.jpg"];
        }


        images = images.map((img) => {
          if (typeof img !== "string" || img.trim() === "")
            return "public/images/imageBg/barcie_logo.jpg";
          if (img.startsWith("http://") || img.startsWith("https://")) return img;

          return img.replace(/^\/+/, "");
        });


        card.dataset.images = JSON.stringify(images);

        const roomStatus = item.room_status || "available";
        card.dataset.availability = ["available", "clean"].includes(roomStatus)
          ? "available"
          : "occupied";


        const previewImage = images[0];
        const averageRating = parseFloat(item.average_rating) || 0;
        const totalReviews = parseInt(item.total_reviews) || 0;
        const fullStars = Math.floor(averageRating);
        const hasHalfStar = averageRating % 1 >= 0.5;
        let starsHTML = "";

        for (let i = 0; i < 5; i++) {
          if (i < fullStars) {
            starsHTML += '<i class="fas fa-star" style="color: #ffc107;"></i>';
          } else if (i === fullStars && hasHalfStar) {
            starsHTML +=
              '<i class="fas fa-star-half-alt" style="color: #ffc107;"></i>';
          } else {
            starsHTML += '<i class="far fa-star" style="color: #ddd;"></i>';
          }
        }

        card.innerHTML = `
        <div class="card-image position-relative" style="cursor: pointer;" data-item-id="${item.id}">
          <img src="${previewImage}" class="room-card-img" 
            style="width:100%;height:200px;object-fit:cover;border-radius:15px 15px 0 0;" 
            onerror="this.onerror=null; this.src='public  /images/imageBg/barcie_logo.jpg';"
            alt="${item.name}">
          ${images.length > 1
            ? `
          <div class="image-count-badge" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
            <i class="fas fa-images me-1"></i>${images.length}
          </div>
          `
            : ""
          }
        </div>
        <div class="card-content" style="padding: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
            <div>
              <h3 style="margin-bottom: 5px; color: #2c3e50;">${item.name}</h3>
              <div class="room-rating" style="font-size: 0.9rem; margin-bottom: 5px;">
                ${starsHTML}
                <small style="color: #6c757d; margin-left: 5px;">(${totalReviews})</small>
              </div>
            </div>
            <div style="text-align: right;">
              <p style="margin: 0; color: #27ae60; font-weight: bold; font-size: 1.2em;">₱${parseInt(item.price || 0).toLocaleString()}</p>
              <small style="color: #7f8c8d;">${item.item_type === "room" ? "/night" : "/day"}</small>
            </div>
          </div>
          ${item.room_number ? `<p style="margin: 5px 0; color: #7f8c8d;"><i class="fas fa-door-open me-1"></i>Room Number: ${item.room_number}</p>` : ""}
          <p style="margin: 5px 0; color: #7f8c8d;"><i class="fas fa-users me-1"></i>Capacity: ${item.capacity} ${item.item_type === "room" ? "persons" : "people"}</p>
          <p style="margin: 10px 0; color: #34495e; line-height: 1.4;">${item.description || "Comfortable accommodation with modern amenities."}</p>
          <div class="card-actions" style="margin-top: 15px; display: flex; gap: 8px;">
            <button class="btn btn-primary btn-sm btn-view-details flex-fill" data-item-id="${item.id}" onclick="openRoomDetailsModal(${item.id})">
              <i class="fas fa-info-circle me-1"></i>Details
            </button>
            <button class="btn btn-outline-warning btn-sm btn-leave-review flex-fill" data-item-id="${item.id}" onclick="openRoomFeedbackModal(${item.id}, '${item.name.replace(/'/g, "\\'")}')">
              <i class="fas fa-star me-1"></i>Review
            </button>
            <button class="btn btn-success btn-sm book-now-btn flex-fill" data-item-id="${item.id}" ${card.dataset.availability !== "available" ? "disabled" : ""}>
              <i class="fas fa-calendar-plus me-1"></i>Book
            </button>
          </div>
        </div>
      `;
        container.appendChild(card);
      });

      // Add event handlers for View Details and Book Now buttons
      setupItemButtons();
      filterItems();

      console.log("Guest: Items loaded and displayed successfully");
    } catch (error) {
      console.error("Guest: Error loading items:", error);

      const container = document.getElementById("cards-grid");
      if (container) {
        container.innerHTML = `
        <div class="col-12">
          <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <h5>Error Loading Rooms & Facilities</h5>
            <p>Unable to load rooms and facilities. Please refresh the page or contact support.</p>
            <small class="text-muted">Error: ${error.message}</small>
          </div>
        </div>
      `;
      }

      // Show user-friendly toast notification
      if (typeof showToast === "function") {
        showToast(
          "Failed to load rooms and facilities. Please refresh the page.",
          "error",
        );
      }
    }
  }


</script>