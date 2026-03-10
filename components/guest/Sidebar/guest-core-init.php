<?php /* migrated from Components/Guest/js/guest-core-init.js */ ?>
<script>
  // Complete Guest Portal JavaScript with Bootstrap Integration
  // Consolidated from guest.js, guest-enhanced.js, and inline scripts

  document.addEventListener("DOMContentLoaded", function () {
    initializeGuestPortal();
  });

  function initializeGuestPortal() {
    try {
      setupBootstrapComponents();
    } catch (error) {
      console.error("Guest: Bootstrap setup failed:", error);
    }

    try {
      setupMobileMenu();
    } catch (error) {
      console.error("Guest: Mobile menu setup failed:", error);
    }

    try {
      enhanceForms();
    } catch (error) {
      console.error("Guest: Form enhancement failed:", error);
    }

    try {
      setupSectionNavigation();
    } catch (error) {
      console.error("Guest: Section navigation setup failed:", error);
    }

    try {
      setupBookingForms();
    } catch (error) {
      console.error("Guest: Booking forms setup failed:", error);
    }
    try {
      setupCardFiltering();
      console.log("Guest: Card filtering initialized");
    } catch (error) {
      console.error("Guest: Card filtering setup failed:", error);
    }

    try {
      initializeReceiptGeneration();
      console.log("Guest: Receipt generation initialized");
    } catch (error) {
      console.error("Guest: Receipt generation failed:", error);
    }

    try {
      loadItems();
      console.log("Guest: Items loading started");
    } catch (error) {
      console.error("Guest: Items loading failed:", error);
    }

    try {
      addKeyboardNavigation();
      console.log("Guest: Keyboard navigation added");
    } catch (error) {
      console.error("Guest: Keyboard navigation failed:", error);
    }

    try {
      setupInteractiveOverview();
      console.log("Guest: Interactive overview initialized");
    } catch (error) {
      console.error("Guest: Interactive overview failed:", error);
    }

    try {
      initializeChatSystem();
      console.log("Guest: Chat system initialized");
    } catch (error) {
      console.warn("Guest: Chat system initialization failed:", error);
    }

    try {
      initializeStarRating();
      console.log("Guest: Star rating initialized");
    } catch (error) {
      console.error("Guest: Star rating failed:", error);
    }

    // Calendar initialization moved to the availability section script.
    // It will initialize itself when the section becomes visible.
    console.log(
      "Guest: Calendar initialization delegated to availability section.",
    );

    console.log("Guest: Portal initialization complete");
  }

  // Initialize Bootstrap Components
  function setupBootstrapComponents() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]'),
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]'),
    );
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize modals
    var modalList = [].slice.call(document.querySelectorAll(".modal"));
    modalList.map(function (modalEl) {
      return new bootstrap.Modal(modalEl);
    });
  }

  // Mobile Menu Setup
  function setupMobileMenu() {
    const toggleBtn = document.querySelector(".mobile-menu-toggle");
    const sidebar = document.querySelector(".sidebar-guest");

    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("open");

        // Update icon
        const icon = this.querySelector("i");
        if (sidebar.classList.contains("open")) {
          icon.className = "fas fa-times";
        } else {
          icon.className = "fas fa-bars";
        }
      });

      // Close sidebar when clicking outside on mobile
      document.addEventListener("click", function (e) {
        if (
          window.innerWidth <= 992 &&
          !sidebar.contains(e.target) &&
          !toggleBtn.contains(e.target) &&
          sidebar.classList.contains("open")
        ) {
          sidebar.classList.remove("open");
          toggleBtn.querySelector("i").className = "fas fa-bars";
        }
      });
    }
  }

  // Section Navigation (from inline script)

</script>