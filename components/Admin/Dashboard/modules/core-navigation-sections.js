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

  function sectionExists(sectionId) {
    return !!(sectionId && document.getElementById(sectionId));
  }

  const navLinks = document.querySelectorAll(
    ".sidebar .nav-link, .sidebar .nav-link-custom",
  );
  console.log("Found navigation links:", navLinks.length);

  if (navLinks.length === 0) {
    console.error(
      "âŒ NO NAVIGATION LINKS FOUND! Sidebar may not be loaded yet.",
    );
    return;
  }

  function getSectionIdFromLink(link) {
    const dataSection = link.getAttribute("data-section");
    if (dataSection) {
      return dataSection;
    }

    const href = (link.getAttribute("href") || "").trim();
    if (href.charAt(0) === "#" && href.length > 1) {
      return href.substring(1);
    }

    return "";
  }

  navLinks.forEach((link, index) => {
    const sectionId = getSectionIdFromLink(link);
    const isDropdownToggle = link.classList.contains("dropdown-toggle");
    console.log(
      "  Setting up link " +
        (index + 1) +
        ": " +
        (sectionId || "(no section)") +
        (isDropdownToggle ? " [dropdown]" : ""),
    );

    if (!sectionId || isDropdownToggle) {
      return;
    }

    link.addEventListener("click", function (e) {
      e.preventDefault();
      const clickedSectionId = getSectionIdFromLink(this);
      if (!clickedSectionId) {
        return;
      }

      console.log("Navigation clicked:", clickedSectionId);
      const resolved =
        typeof resolveSectionId === "function"
          ? resolveSectionId(clickedSectionId)
          : clickedSectionId;
      showSection(resolved);

      document
        .querySelectorAll(".sidebar .nav-link, .sidebar .nav-link-custom")
        .forEach((l) => l.classList.remove("active"));
      this.classList.add("active");

      localStorage.setItem("activeSection", resolved || clickedSectionId);
    });
  });

  const initialHash = window.location.hash.substring(1);
  const resolvedHashSection = resolveSectionId(initialHash);
  const resolvedStoredSection = resolveSectionId(lastSectionId);
  const initialSectionId = sectionExists(resolvedHashSection)
    ? resolvedHashSection
    : sectionExists(resolvedStoredSection)
      ? resolvedStoredSection
      : "dashboard-section";

  const initialActiveLink = document.querySelector(
    `.sidebar .nav-link-custom[data-section="${initialSectionId}"], .sidebar .nav-link[data-section="${initialSectionId}"], .sidebar .nav-link-custom[href="#${initialSectionId}"], .sidebar .nav-link[href="#${initialSectionId}"]`,
  );
  if (initialActiveLink) {
    document
      .querySelectorAll(".sidebar .nav-link, .sidebar .nav-link-custom")
      .forEach((l) => l.classList.remove("active"));
    initialActiveLink.classList.add("active");
  }

  console.log("Initial section display...");
  showSection(initialSectionId);
  localStorage.setItem("activeSection", initialSectionId);

  if (
    initialHash &&
    resolvedHashSection &&
    initialHash !== resolvedHashSection
  ) {
    window.location.hash = resolvedHashSection;
  }

  window.addEventListener("hashchange", function () {
    const hash = window.location.hash.substring(1);
    if (hash) {
      console.log("Hash changed to:", hash);
      const resolved =
        typeof resolveSectionId === "function" ? resolveSectionId(hash) : hash;
      showSection(resolved);

      document
        .querySelectorAll(".sidebar .nav-link, .sidebar .nav-link-custom")
        .forEach((l) => l.classList.remove("active"));
      const activeLink = document.querySelector(
        `.sidebar .nav-link-custom[data-section="${hash}"], .sidebar .nav-link[data-section="${hash}"], .sidebar .nav-link-custom[data-section="${resolved}"], .sidebar .nav-link[data-section="${resolved}"], .sidebar .nav-link-custom[href="#${hash}"], .sidebar .nav-link[href="#${hash}"], .sidebar .nav-link-custom[href="#${resolved}"], .sidebar .nav-link[href="#${resolved}"]`,
      );
      if (activeLink) {
        activeLink.classList.add("active");
      }
    }
  });

  console.log("âœ… Section navigation setup complete");
}
