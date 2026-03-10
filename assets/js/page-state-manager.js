(function () {
  "use strict";

  const STATE_KEY = "barcie_page_state";
  const ROLE_KEY = "barcie_user_role";

  const DEFAULT_SECTIONS = {
    admin: "dashboard",
    guest: "overview",
  };

  function savePageState(section, role) {
    try {
      const state = {
        section: section,
        role: role,
        timestamp: Date.now(),
        url: window.location.href,
      };
      sessionStorage.setItem(STATE_KEY, JSON.stringify(state));
      sessionStorage.setItem(ROLE_KEY, role);
    } catch (e) {
      console.warn("Failed to save page state:", e);
    }
  }
  function getPageState() {
    try {
      const stateJson = sessionStorage.getItem(STATE_KEY);
      if (stateJson) {
        return JSON.parse(stateJson);
      }
    } catch (e) {
      console.warn("Failed to get page state:", e);
    }
    return null;
  }
  function getUserRole() {
    return sessionStorage.getItem(ROLE_KEY) || null;
  }

  function clearPageState() {
    try {
      sessionStorage.removeItem(STATE_KEY);
      sessionStorage.removeItem(ROLE_KEY);
    } catch (e) {
      console.warn("Failed to clear page state:", e);
    }
  }

  function detectPageType() {
    const path = window.location.pathname;
    if (path.includes("dashboard.php")) return "admin";
    if (path.includes("Guest.php")) return "guest";
    if (path.includes("index.php") || path === "/" || /\/[^/]+\/?$/.test(path))
      return "landing";
    return "unknown";
  }

  function getCurrentSection() {
    const hash = window.location.hash.substring(1);
    if (hash) return hash;

    const activeSections = document.querySelectorAll(".content-section.active");
    if (activeSections.length > 0) {
      const activeSection = activeSections[0];
      const sectionId = activeSection.id || "";
      return sectionId.replace("-section", "");
    }

    return null;
  }

  function navigateToSection(sectionName, updateHash = true) {
    console.log("🧭 Navigating to section:", sectionName);
    if (updateHash) {
      window.location.hash = sectionName;
    }
    const possibleIds = [
      sectionName,
      sectionName + "-section",
      sectionName + "Section",
    ];

    let targetSection = null;
    for (const id of possibleIds) {
      targetSection = document.getElementById(id);
      if (targetSection) break;
    }

    if (!targetSection) {
      console.warn("Section not found:", sectionName);
      return false;
    }
    document.querySelectorAll(".content-section").forEach((section) => {
      section.classList.remove("active");
      section.style.display = "none";
    });

    targetSection.classList.add("active");
    targetSection.style.display = "block";

    updateSidebarNavigation(sectionName);
    const pageType = detectPageType();
    if (pageType === "admin" || pageType === "guest") {
      savePageState(sectionName, pageType);
    }

    return true;
  }

  function updateSidebarNavigation(sectionName) {
    document.querySelectorAll(".nav-link").forEach((link) => {
      link.classList.remove("active");
    });
    const matchingLink = document.querySelector(
      `.nav-link[href="#${sectionName}"]`,
    );
    if (matchingLink) {
      matchingLink.classList.add("active");
    }
  }

  function restorePageState() {
    const pageType = detectPageType();

    console.log("🔄 Page type detected:", pageType);

    if (pageType === "admin" || pageType === "guest") {
      const urlHash = window.location.hash.substring(1);

      if (urlHash) {
        console.log("📍 Using URL hash:", urlHash);
        navigateToSection(urlHash, false);
      } else {
        const savedState = getPageState();

        if (savedState && savedState.role === pageType) {
          console.log("💾 Restoring saved section:", savedState.section);
          navigateToSection(savedState.section, true);
        } else {
          e;
          const defaultSection = DEFAULT_SECTIONS[pageType];
          console.log("🏠 Using default section:", defaultSection);
          navigateToSection(defaultSection, true);
        }
      }
    }
  }

  function handleLoginSuccess(role) {
    console.log("✅ Login successful, role:", role);
    const defaultSection = DEFAULT_SECTIONS[role];
    savePageState(defaultSection, role);
    if (role === "admin") {
      window.location.href = "dashboard.php#" + defaultSection;
    } else if (role === "guest") {
      window.location.href = "Guest.php#" + defaultSection;
    }
  }

  function handleLogout() {
    console.log("👋 Logging out, clearing state");
    clearPageState();
    window.location.href = "index.php";
  }

  function initialize() {
    console.log("🚀 Page State Manager initialized");
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", restorePageState);
    } else {
      restorePageState();
    }

    window.addEventListener("hashchange", function () {
      const hash = window.location.hash.substring(1);
      if (hash) {
        navigateToSection(hash, false);
      }
    });

    document.addEventListener("click", function (e) {
      const navLink = e.target.closest('.nav-link[href^="#"]');
      if (navLink) {
        e.preventDefault();
        const sectionName = navLink.getAttribute("href").substring(1);
        navigateToSection(sectionName, true);
      }
    });

    setInterval(function () {
      const currentSection = getCurrentSection();
      const pageType = detectPageType();
      if (currentSection && (pageType === "admin" || pageType === "guest")) {
        savePageState(currentSection, pageType);
      }
    }, 5000);
  }

  window.BarcieStateManager = {
    navigate: navigateToSection,
    saveState: savePageState,
    getState: getPageState,
    clearState: clearPageState,
    handleLoginSuccess: handleLoginSuccess,
    handleLogout: handleLogout,
    getCurrentSection: getCurrentSection,
    getUserRole: getUserRole,
  };

  initialize();
})();
