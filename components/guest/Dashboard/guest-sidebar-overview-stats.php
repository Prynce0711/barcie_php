<?php /* migrated from Components/Guest/js/guest-sidebar-overview-stats.js */ ?>
<script>
  function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar-guest");
    const overlay = document.querySelector(".sidebar-overlay");
    const toggleBtn = document.querySelector(".mobile-menu-toggle");

    if (sidebar) {
      const isOpen = sidebar.classList.toggle("open");

      // Toggle overlay
      if (overlay) {
        overlay.classList.toggle("show", isOpen);
      }

      // Prevent body scroll when sidebar is open
      document.body.style.overflow = isOpen ? "hidden" : "";

      // Change icon
      if (toggleBtn) {
        const icon = toggleBtn.querySelector("i");
        if (isOpen) {
          icon.className = "fas fa-times";
        } else {
          icon.className = "fas fa-bars";
        }
      }
    }
  }

  // Close Sidebar Function
  function closeSidebar() {
    const sidebar = document.querySelector(".sidebar-guest");
    const overlay = document.querySelector(".sidebar-overlay");
    const toggleBtn = document.querySelector(".mobile-menu-toggle");

    if (sidebar) {
      sidebar.classList.remove("open");
    }
    if (overlay) {
      overlay.classList.remove("show");
    }

    // Restore body scroll
    document.body.style.overflow = "";

    // Reset icon
    if (toggleBtn) {
      const icon = toggleBtn.querySelector("i");
      icon.className = "fas fa-bars";
    }
  }

  // Global function exports for compatibility
  window.showSection = showSection;
  window.toggleBookingForm = toggleBookingForm;
  window.generateReceiptNumber = generateReceiptNumber;
  window.pencilReminder = pencilReminder;
  window.reservationReminder = reservationReminder;
  window.showToast = showToast;
  window.toggleSidebar = toggleSidebar;
  window.closeSidebar = closeSidebar;
  window.loadItems =
    typeof loadItems === "function"
      ? loadItems
      : function () {
        console.info("Guest: loadItems is not ready yet");
        return Promise.resolve([]);
      };
  window.loadRooms = window.loadItems; // Alias for room feedback system
  window.filterItems = filterItems;
  window.showItemDetails = showItemDetails;
  window.redirectToBooking = redirectToBooking;
  window.setupItemButtons = setupItemButtons;
  window.scrollToAvailability = scrollToAvailability;

  // Interactive Overview Setup - Typical Dashboard Style
  function setupInteractiveOverview() {
    updateOverviewStats();
    loadFeaturedItems();
  }

  // Update Overview Statistics
  function updateOverviewStats() {
    if (!window.allItems) {
      // Use default values if no data loaded yet
      setTimeout(updateOverviewStats, 1000);
      return;
    }

    updateOverviewStats.isLoading = true;

    const rooms = window.allItems.filter((item) => item.item_type === "room");
    const facilities = window.allItems.filter(
      (item) => item.item_type === "facility",
    );

    const totalRoomsEl = document.getElementById("total-rooms");
    const totalFacilitiesEl = document.getElementById("total-facilities");

    if (!totalRoomsEl || !totalFacilitiesEl) {
      if (!updateOverviewStats.hasLoggedMissingElements) {
        console.info("Guest: Overview stat elements are not present in this layout");
        updateOverviewStats.hasLoggedMissingElements = true;
      }
      return;
    }

    // Update statistics displays
    totalRoomsEl.textContent = rooms.length;
    totalFacilitiesEl.textContent = facilities.length;

    // Fetch real availability data
    fetchRealAvailability();
  }

  // Fetch real availability data from server
  async function fetchRealAvailability() {
    // Show loading state
    const availableElement = document.getElementById("available-rooms");
    if (!availableElement) {
      console.warn("Guest: available-rooms element not found; skipping availability fetch");
      return;
    }
    const originalText = availableElement.textContent;
    availableElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
      const apiBase =
        (window.BARCIE_GUEST && window.BARCIE_GUEST.apiBaseUrl) || "api";
      const response = await fetch(`${apiBase}/AvailableCount.php`);
      const data = await response.json();

      if (data && data.success) {
        availableElement.textContent = data.available_count;
        console.log("Real availability loaded:", data.available_count);
      } else {
        console.warn("API returned error or unexpected response:", data);
        // Fallback: calculate based on room status from items
        availableElement.textContent = originalText;
        calculateFallbackAvailability();
      }
    } catch (error) {
      console.error("Error fetching availability:", error);
      // Fallback: calculate based on room status from items
      availableElement.textContent = originalText;
      calculateFallbackAvailability();
    }
  }

  // Fallback availability calculation
  function calculateFallbackAvailability() {
    const availableElement = document.getElementById("available-rooms");
    if (!availableElement) return;

    if (!window.allItems) {
      availableElement.textContent = "0";
      return;
    }

    // Count items that are available or clean (not occupied, maintenance, etc.)
    const availableItems = window.allItems.filter(
      (item) =>
        !item.room_status ||
        item.room_status === "available" ||
        item.room_status === "clean",
    );

    availableElement.textContent = availableItems.length;
  }

  // Scroll to availability calendar section
  function scrollToAvailability() {
    const availabilitySection = document.getElementById(
      "availability-calendar-section",
    );

    if (availabilitySection) {
      availabilitySection.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });

      // Add a subtle highlight effect to the card
      const card = availabilitySection.querySelector(".card");
      if (card) {
        card.style.transition = "box-shadow 0.3s ease";
        card.style.boxShadow = "0 0 20px rgba(23, 162, 184, 0.3)";

        setTimeout(() => {
          card.style.boxShadow = "";
        }, 2000);
      }

      showToast("Viewing availability calendar below", "info");
    } else {
      showToast("Availability calendar section not found", "warning");
    }
  }

  // Load Featured Items for Overview

</script>
