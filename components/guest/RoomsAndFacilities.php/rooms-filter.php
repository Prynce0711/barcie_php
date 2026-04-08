<script>
    // Rooms & Facilities filter logic (moved from guest-booking-filters.js)

    function getSelectedItemType() {
        const scope = "guest-rooms";

        if (
            window.FilterTypes &&
            window.FilterTypes[scope] &&
            typeof window.FilterTypes[scope].getFilter === "function"
        ) {
            const scoped = window.FilterTypes[scope].getFilter();
            if (scoped) return scoped;
        }

        // 1) Legacy radio group
        const radioValue = document.querySelector('input[name="type"]:checked')?.value;
        if (radioValue) return radioValue;

        // 2) Shared global set by FilterTypes.php
        if (window.FilterTypes && typeof window.FilterTypes.getFilter === "function") {
            const current = window.FilterTypes.getFilter(scope);
            if (current) return current;
        }

        // 3) Backward-compat global
        if (window._availabilityFilter) return window._availabilityFilter;

        return "all";
    }

    function filterItems() {
        const selectedType = getSelectedItemType();
        console.log("Guest: Filtering items by type:", selectedType);

        const cards = document.querySelectorAll("#cards-grid .card");
        let visibleCount = 0;

        cards.forEach((card) => {
            if (selectedType === "all" || card.dataset.type === selectedType) {
                card.style.display = "";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        console.log("Guest: Showing", visibleCount, "items for filter", selectedType);

        const container = document.getElementById("cards-grid");
        if (visibleCount === 0 && container) {
            const noItemsMessage = document.createElement("div");
            noItemsMessage.className = "no-items-message col-12";
            const typeLabel =
                selectedType === "all"
                    ? "Rooms or Facilities"
                    : selectedType.charAt(0).toUpperCase() +
                    selectedType.slice(1) +
                    "s";
            noItemsMessage.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <h5>No ${typeLabel} Available</h5>
                    <p>Try selecting a different type or check back later.</p>
                </div>
            `;

            const existingMessage = container.querySelector(".no-items-message");
            if (existingMessage) existingMessage.remove();

            container.appendChild(noItemsMessage);
        } else {
            const existingMessage = container?.querySelector(".no-items-message");
            if (existingMessage) existingMessage.remove();
        }
    }

    function syncOverviewWithRooms() {
        const selectedType = getSelectedItemType() || "room";
        const typeFilter = document.getElementById("typeFilter");

        if (typeFilter) {
            typeFilter.value =
                selectedType.charAt(0).toUpperCase() + selectedType.slice(1);
            applyOverviewFilters();
        }
    }

    function setupCardFiltering() {
        // Legacy radios (kept for backward compatibility)
        const radios = document.querySelectorAll('input[name="type"]');

        radios.forEach((radio) => {
            radio.addEventListener("change", () => {
                filterItems();
                syncOverviewWithRooms();
            });
        });

        // New FilterTypes component emits this when buttons are clicked.
        document.addEventListener("filter-changed", (e) => {
            if (!e || !e.detail || e.detail.scope !== "guest-rooms") return;
            filterItems();
            syncOverviewWithRooms();
        });
    }
</script>