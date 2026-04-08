function setupMobileMenu() {
  const toggleBtn = document.querySelector(".mobile-menu-toggle");
  const sidebar = document.querySelector(".sidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("open");

      const icon = this.querySelector("i");
      if (sidebar.classList.contains("open")) {
        icon.className = "fas fa-times";
      } else {
        icon.className = "fas fa-bars";
      }
    });

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

function setupSectionNavigation() {
  console.log("Setting up section navigation...");

  const lastSectionId =
    localStorage.getItem("activeSection") || "dashboard-section";

  console.log("Last active section:", lastSectionId);

  const SECTION_ALIASES = {
    "Pencil-Bookings": "pencil-bookings-section",
  };

  function resolveSectionId(id) {
    if (!id) return id;
    return SECTION_ALIASES[id] || id;
  }

  const navLinks = document.querySelectorAll(".nav-link-custom");
  console.log("Found navigation links:", navLinks.length);

  if (navLinks.length === 0) {
    console.error(
      "âŒ NO NAVIGATION LINKS FOUND! Sidebar may not be loaded yet.",
    );
    return;
  }

  navLinks.forEach((link, index) => {
    const sectionId = link.getAttribute("data-section");
    console.log("  Setting up link " + (index + 1) + ": " + sectionId);

    link.addEventListener("click", function (e) {
      e.preventDefault();
      const clickedSectionId = this.getAttribute("data-section");
      console.log("Navigation clicked:", clickedSectionId);
      const resolved =
        typeof resolveSectionId === "function"
          ? resolveSectionId(clickedSectionId)
          : clickedSectionId;
      showSection(resolved);

      document
        .querySelectorAll(".nav-link-custom")
        .forEach((l) => l.classList.remove("active"));
      this.classList.add("active");

      localStorage.setItem("activeSection", clickedSectionId);
    });
  });

  const initialActiveLink = document.querySelector(
    `.nav-link-custom[data-section="${lastSectionId}"]`,
  );
  if (initialActiveLink) {
    document
      .querySelectorAll(".nav-link-custom")
      .forEach((l) => l.classList.remove("active"));
    initialActiveLink.classList.add("active");
  }

  console.log("Initial section display...");
  showSection(lastSectionId);

  const initialHash = window.location.hash.substring(1);
  if (initialHash === "Pencil-Bookings") {
    window.location.hash = "pencil-bookings-section";
  }

  window.addEventListener("hashchange", function () {
    const hash = window.location.hash.substring(1);
    if (hash) {
      console.log("Hash changed to:", hash);
      const resolved =
        typeof resolveSectionId === "function" ? resolveSectionId(hash) : hash;
      showSection(resolved);

      document
        .querySelectorAll(".nav-link-custom, .nav-link")
        .forEach((l) => l.classList.remove("active"));
      const activeLink = document.querySelector(
        `.nav-link-custom[data-section="${hash}"], .nav-link[data-section="${hash}"], .nav-link-custom[data-section="${resolved}"], .nav-link[data-section="${resolved}"]`,
      );
      if (activeLink) {
        activeLink.classList.add("active");
      }
    }
  });

  console.log("âœ… Section navigation setup complete");
}
