<?php /* migrated from Components/Guest/js/guest-navigation.js */ ?>
<script>
  function showSection(sectionId, button, saveToStorage = true) {
    console.log(
      "Guest: Attempting to show section:",
      sectionId,
      "saveToStorage:",
      saveToStorage,
    );

    // Validate sectionId
    if (!sectionId) {
      console.error("Guest: No section ID provided");
      return false;
    }

    // Hide all sections
    const allSections = document.querySelectorAll(".content-section");
    console.log("Guest: Found", allSections.length, "total sections");

    allSections.forEach((sec) => {
      sec.classList.remove("active");
      sec.style.display = "none";
    });

    // Show target section
    const section = document.getElementById(sectionId);
    if (section) {
      section.classList.add("active");
      section.style.display = "block";
      section.style.opacity = "1";

      // Add smooth animation
      section.style.animation = "fadeInUp 0.4s ease";

      console.log("Guest: Section successfully displayed:", sectionId);

      // Save current section to sessionStorage (only valid sections and only when user navigates)
      if (saveToStorage) {
        const validSections = [
          "overview",
          "availability",
          "rooms",
          "booking",
          "feedback",
        ];
        if (validSections.includes(sectionId)) {
          sessionStorage.setItem("guestCurrentSection", sectionId);
          console.log("Guest: Saved section to sessionStorage:", sectionId);
        } else {
          console.warn(
            "Guest: Not saving invalid section to sessionStorage:",
            sectionId,
          );
        }
      } else {
        console.log(
          "Guest: Not saving section to sessionStorage (programmatic navigation):",
          sectionId,
        );
      }

      // Scroll to top of section
      section.scrollIntoView({ behavior: "smooth", block: "start" });
    } else {
      console.error("Guest: Section element not found:", sectionId);

      // Try to find any section as fallback
      const availableSections = Array.from(allSections).map((sec) => sec.id);
      console.log("Guest: Available sections:", availableSections);
      return false;
    }

    // Update navigation buttons
    const sidebarButtons = document.querySelectorAll(".sidebar-guest button");
    sidebarButtons.forEach((btn) => btn.classList.remove("active"));

    if (button) {
      button.classList.add("active");
      console.log("Guest: Navigation button activated");
    } else {
      // Try to find the button by section name (new sidebar markup uses data-section)
      const matchingButton = document.querySelector(
        `.sidebar-guest button[data-section="${sectionId}"]`,
      );
      if (matchingButton) {
        matchingButton.classList.add("active");
        console.log("Guest: Found and activated matching button");
      }
    }

    // Close mobile menu if open
    const sidebar = document.querySelector(".sidebar-guest");
    const toggleBtn = document.querySelector(".mobile-menu-toggle");
    if (
      window.innerWidth <= 992 &&
      sidebar &&
      sidebar.classList.contains("open")
    ) {
      sidebar.classList.remove("open");
      if (toggleBtn) {
        toggleBtn.querySelector("i").className = "fas fa-bars";
      }
    }

    return true;
  }

  // Setup Section Navigation
  function setupSectionNavigation() {
    console.log("Guest: Setting up section navigation");

    // Check if sections exist
    const sections = document.querySelectorAll(".content-section");
    console.log("Guest: Found", sections.length, "sections");

    if (sections.length === 0) {
      console.error("Guest: No content sections found!");
      return;
    }

    // Check which section should be active on page load
    // First, remove any existing active class from sections
    sections.forEach((sec, index) => {
      sec.classList.remove("active");
      sec.style.display = "none";
      console.log(`Guest: Section ${index + 1} (${sec.id}): reset`);
    });

    // Determine which section to show
    setTimeout(() => {
      let sectionToShow = "overview";
      const hashSection = window.location.hash.substring(1);
      const savedSection = sessionStorage.getItem("guestCurrentSection");

      // Valid section IDs
      const validSections = [
        "overview",
        "availability",
        "rooms",
        "booking",
        "feedback",
      ];

      console.log("Guest: Hash section:", hashSection);
      console.log("Guest: Saved section:", savedSection);

      if (
        hashSection &&
        validSections.includes(hashSection) &&
        document.getElementById(hashSection)
      ) {
        sectionToShow = hashSection;
        console.log("Guest: Using hash section from URL:", sectionToShow);
        const preservedUrl = window.location.pathname + (window.location.search || "");
        history.replaceState(null, "", preservedUrl);
      } else if (
        savedSection &&
        validSections.includes(savedSection) &&
        document.getElementById(savedSection)
      ) {
        sectionToShow = savedSection;
        console.log("Guest: Restoring saved section:", sectionToShow);
      } else {
        console.log("Guest: Using default section: overview");
        sectionToShow = "overview";
      }

      console.log("Guest: Final section to show:", sectionToShow);

      const targetButton = document.querySelector(
        `.sidebar-guest button[data-section="${sectionToShow}"]`,
      );
      console.log(
        `Guest: Target button for ${sectionToShow} found:`,
        !!targetButton,
      );

      const targetSection = document.getElementById(sectionToShow);
      if (targetSection) {
        console.log("Guest: Showing section:", sectionToShow);
        // Don't save to sessionStorage on initial page load (we're just restoring)
        showSection(sectionToShow, targetButton, false);
      } else {
        console.warn("Guest: Target section not found, showing overview");
        const overviewSection = document.getElementById("overview");
        const overviewButton = document.querySelector(
          ".sidebar-guest button[data-section='overview']",
        );
        if (overviewSection) {
          showSection("overview", overviewButton, false);
        } else {
          const firstSection = sections[0];
          if (firstSection) {
            showSection(firstSection.id, null, false);
          }
        }
      }
    }, 200);
  }

  // Booking Form Toggle (from guest.js)

</script>