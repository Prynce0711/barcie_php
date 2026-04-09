<?php /* guest-items-loader.php — card template + loadItems() */ ?>

<template id="room-card-template">
    <div
        class="card group relative overflow-hidden rounded-3xl border border-sky-100 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">

        <!-- Image section -->
        <div class="card-image relative cursor-pointer bg-slate-100">
            <img class="room-card-img h-52 w-full object-cover" src="" alt=""
                onerror="this.onerror=null;this.src='public/images/imageBg/barcie_logo.jpg';">
            <!-- Multi-image count badge (hidden until JS shows it) -->
            <div
                class="image-count-badge absolute right-2.5 top-2.5 hidden items-center gap-1 rounded-full bg-black/70 px-2.5 py-1 text-xs text-white">
                <i class="fas fa-images"></i>
                <span class="count-num"></span>
            </div>
        </div>

        <!-- Content section -->
        <div class="card-content p-5">

            <!-- Name + Price row -->
            <div class="mb-3 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="card-name mb-1 truncate text-xl font-extrabold uppercase tracking-wide text-sky-900">
                    </h3>
                    <div class="card-stars mb-1 text-sm"></div>
                </div>
                <div class="shrink-0 text-right">
                    <p class="card-price m-0 text-2xl font-extrabold text-emerald-600"></p>
                    <small class="card-period text-xs text-slate-400"></small>
                </div>
            </div>

            <!-- Room number row (hidden until populated) -->
            <div class="card-room-number-row hidden my-1 text-sm text-slate-500">
                <i class="fas fa-door-open me-1"></i>Room&nbsp;No:&nbsp;<span
                    class="card-room-number font-medium"></span>
            </div>

            <!-- Capacity -->
            <div class="my-1 text-sm text-slate-500">
                <i class="fas fa-users me-1"></i>Capacity:&nbsp;<span class="card-capacity font-medium"></span>
            </div>

            <!-- Description -->
            <p class="card-description my-2 line-clamp-3 text-sm leading-relaxed text-slate-600"></p>

            <!-- Action buttons -->
            <div class="card-actions mt-4 grid grid-cols-3 gap-2">
                <button class="btn btn-primary btn-sm btn-view-details w-full rounded-xl">
                    <i class="fas fa-info-circle me-1"></i>Details
                </button>
                <button class="btn btn-outline-warning btn-sm btn-leave-review w-full rounded-xl">
                    <i class="fas fa-star me-1"></i>Review
                </button>
                <button class="btn btn-success btn-sm book-now-btn w-full rounded-xl">
                    <i class="fas fa-calendar-plus me-1"></i>Book
                </button>
            </div>

        </div>
    </div>
</template>

<script>
    // ── Helpers ──────────────────────────────────────────────────────────────

    function buildStarsHtml(averageRating) {
        const full = Math.floor(averageRating);
        const half = (averageRating % 1) >= 0.5;
        let html = "";
        for (let i = 0; i < 5; i++) {
            if (i < full) {
                html += '<i class="fas fa-star text-amber-400"></i>';
            } else if (i === full && half) {
                html += '<i class="fas fa-star-half-alt text-amber-400"></i>';
            } else {
                html += '<i class="far fa-star text-slate-300"></i>';
            }
        }
        return html;
    }

    // ── loadItems ─────────────────────────────────────────────────────────────

    async function loadItems() {
        console.log("Guest: Loading items from API...");

        try {
            const apiBase =
                (window.BARCIE_GUEST && window.BARCIE_GUEST.apiBaseUrl) || "api";
            const res = await fetch(`${apiBase}/items.php`);
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

            const response = await res.json();

            let items = [];
            if (response?.success && Array.isArray(response.items)) {
                items = response.items;
            } else if (Array.isArray(response)) {
                items = response;
            } else if (Array.isArray(response?.data)) {
                items = response.data;
            } else {
                if (response?.error) throw new Error(response.message || response.error);
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

            const tpl = document.getElementById("room-card-template");
            container.innerHTML = "";

            items.forEach((item, index) => {
                console.log(`Guest: Rendering item ${index + 1}:`, item.name, `(${item.item_type})`);

                // Parse images
                let images = [];
                if (item.images) {
                    try { images = JSON.parse(item.images); if (!Array.isArray(images)) images = []; }
                    catch (e) { console.warn("Failed to parse images for item:", item.id); images = []; }
                }
                if (!images.length && item.image?.trim()) images = [item.image];
                if (!images.length) images = ["public/images/imageBg/barcie_logo.jpg"];
                images = images.map(img => {
                    if (!img?.trim()) return "public/images/imageBg/barcie_logo.jpg";
                    if (/^https?:\/\//.test(img)) return img;
                    return img.replace(/^\/+/, "");
                });

                const avail = ["available", "clean"].includes(item.room_status) ? "available" : "occupied";

                // Clone template
                const card = tpl.content.cloneNode(true).firstElementChild;
                card.dataset.type = item.item_type;
                card.dataset.price = item.price || 0;
                card.dataset.itemId = item.id;
                card.dataset.images = JSON.stringify(images);
                card.dataset.availability = avail;

                // Image
                const imgEl = card.querySelector(".room-card-img");
                imgEl.src = images[0];
                imgEl.alt = item.name;
                card.querySelector(".card-image").dataset.itemId = item.id;

                if (images.length > 1) {
                    const badge = card.querySelector(".image-count-badge");
                    badge.classList.remove("hidden");
                    badge.classList.add("flex");
                    badge.querySelector(".count-num").textContent = images.length;
                }

                // Text content
                card.querySelector(".card-name").textContent = item.name;
                card.querySelector(".card-stars").innerHTML =
                    buildStarsHtml(parseFloat(item.average_rating) || 0) +
                    ` <small class="text-slate-400 ms-1">(${parseInt(item.total_reviews) || 0})</small>`;
                card.querySelector(".card-price").innerHTML = `&#8369;${parseInt(item.price || 0).toLocaleString()}`;
                card.querySelector(".card-period").textContent = item.item_type === "room" ? "/night" : "/day";
                card.querySelector(".card-capacity").textContent =
                    `${item.capacity} ${item.item_type === "room" ? "persons" : "people"}`;
                card.querySelector(".card-description").textContent =
                    item.description || "Comfortable accommodation with modern amenities.";

                if (item.room_number) {
                    const row = card.querySelector(".card-room-number-row");
                    row.classList.remove("hidden");
                    row.querySelector(".card-room-number").textContent = item.room_number;
                }

                // Buttons
                const btnDetails = card.querySelector(".btn-view-details");
                const btnReview = card.querySelector(".btn-leave-review");
                const btnBook = card.querySelector(".book-now-btn");

                btnDetails.dataset.itemId = item.id;
                btnDetails.onclick = () => openRoomDetailsModal(item.id);
                btnReview.dataset.itemId = item.id;
                btnReview.onclick = () => openRoomFeedbackModal(item.id, item.name);
                btnBook.dataset.itemId = item.id;
                if (avail !== "available") btnBook.disabled = true;

                container.appendChild(card);
            });

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
                            <h5>Error Loading Rooms &amp; Facilities</h5>
                            <p>Unable to load rooms and facilities. Please refresh the page or contact support.</p>
                            <small class="text-muted">Error: ${error.message}</small>
                        </div>
                    </div>`;
            }
            if (typeof showToast === "function") {
                showToast("Failed to load rooms and facilities. Please refresh the page.", "error");
            }
        }
    }
</script>
