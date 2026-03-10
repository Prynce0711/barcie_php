console.log("📱 mobile-enhancements.js loading...");

const isMobile = () => {
  return (
    window.innerWidth <= 768 ||
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent,
    )
  );
};

function enhancedToggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  const overlay = getOrCreateOverlay();
  const body = document.body;

  if (!sidebar) return;

  const isOpen =
    sidebar.classList.contains("show") || sidebar.classList.contains("open");

  if (isOpen) {
    sidebar.classList.remove("show", "open");
    overlay.classList.remove("show", "active");
    body.classList.remove("sidebar-open");
  } else {
    sidebar.classList.add("show", "open");
    overlay.classList.add("show", "active");
    body.classList.add("sidebar-open");
  }
}

function getOrCreateOverlay() {
  let overlay = document.querySelector(".sidebar-overlay");

  if (!overlay) {
    overlay = document.createElement("div");
    overlay.className = "sidebar-overlay";
    document.body.appendChild(overlay);
    overlay.addEventListener("click", enhancedToggleSidebar);
  }

  return overlay;
}

function addTouchFeedback() {
  const touchElements = document.querySelectorAll(
    ".btn, .card, .list-group-item, .nav-link",
  );

  touchElements.forEach((element) => {
    element.addEventListener("touchstart", function () {
      this.style.opacity = "0.7";
    });

    element.addEventListener("touchend", function () {
      setTimeout(() => {
        this.style.opacity = "1";
      }, 150);
    });

    element.addEventListener("touchcancel", function () {
      this.style.opacity = "1";
    });
  });
}

function enableImageSwipe() {
  const imageContainers = document.querySelectorAll(".image-slider-container");

  imageContainers.forEach((container) => {
    let touchStartX = 0;
    let touchEndX = 0;

    container.addEventListener(
      "touchstart",
      (e) => {
        touchStartX = e.changedTouches[0].screenX;
      },
      { passive: true },
    );

    container.addEventListener(
      "touchend",
      (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe(container);
      },
      { passive: true },
    );

    function handleSwipe(container) {
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;

      if (Math.abs(diff) > swipeThreshold) {
        const itemId = container.id.replace("imageCarousel", "");

        if (diff > 0) {
          if (typeof navigateImage === "function" && itemId) {
            navigateImage(itemId, 1);
          }
        } else {
          if (typeof navigateImage === "function" && itemId) {
            navigateImage(itemId, -1);
          }
        }
      }
    }
  });
}

function enablePullToRefresh() {
  let startY = 0;
  let pullDistance = 0;
  const threshold = 80;

  const mainContent = document.querySelector(".main-content");
  if (!mainContent) return;

  mainContent.addEventListener(
    "touchstart",
    (e) => {
      if (window.scrollY === 0) {
        startY = e.touches[0].pageY;
      }
    },
    { passive: true },
  );

  mainContent.addEventListener("touchmove", (e) => {
    if (window.scrollY === 0 && startY > 0) {
      pullDistance = e.touches[0].pageY - startY;

      if (pullDistance > 0 && pullDistance < threshold * 2) {
        e.preventDefault();
      }
    }
  });

  mainContent.addEventListener(
    "touchend",
    () => {
      if (pullDistance > threshold) {
        location.reload();
      }
      startY = 0;
      pullDistance = 0;
    },
    { passive: true },
  );
}

function optimizeTablesForMobile() {
  if (!isMobile()) return;

  const tables = document.querySelectorAll("table");

  tables.forEach((table) => {
    const headers = table.querySelectorAll("thead th");
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row) => {
      const cells = row.querySelectorAll("td");
      cells.forEach((cell, index) => {
        if (headers[index]) {
          cell.setAttribute("data-label", headers[index].textContent.trim());
        }
      });
    });
  });
}

function enhanceMobileFormInputs() {
  const inputs = document.querySelectorAll("input, select, textarea");

  inputs.forEach((input) => {
    if (input.name && input.name.includes("email")) {
      input.type = "email";
    }
    if (input.name && input.name.includes("tel")) {
      input.type = "tel";
    }
    if (
      (input.name && input.name.includes("number")) ||
      input.name.includes("price")
    ) {
      input.type = "number";
    }
  });
}

function addScrollToTop() {
  const scrollBtn = document.createElement("button");
  scrollBtn.className = "scroll-to-top";
  scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
  scrollBtn.style.cssText = `
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    font-size: 18px;
    cursor: pointer;
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  `;

  document.body.appendChild(scrollBtn);

  window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
      scrollBtn.style.opacity = "1";
      scrollBtn.style.visibility = "visible";
    } else {
      scrollBtn.style.opacity = "0";
      scrollBtn.style.visibility = "hidden";
    }
  });

  scrollBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
}

function optimizeModalsForMobile() {
  if (!isMobile()) return;

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (
          node.classList &&
          node.classList.contains("modal") &&
          node.classList.contains("show")
        ) {
          const modalDialog = node.querySelector(".modal-dialog");
          if (modalDialog) {
            modalDialog.style.margin = "10px";
            modalDialog.style.maxWidth = "calc(100% - 20px)";
          }
        }
      });
    });
  });

  observer.observe(document.body, { childList: true, subtree: true });
}

function improveScrolling() {
  document.body.style.webkitOverflowScrolling = "touch";
  const modals = document.querySelectorAll(".modal");
  modals.forEach((modal) => {
    modal.style.webkitOverflowScrolling = "touch";
  });
}

function addHapticFeedback() {
  if (!("vibrate" in navigator)) return;

  const importantButtons = document.querySelectorAll(
    ".btn-primary, .btn-success, .btn-danger",
  );

  importantButtons.forEach((button) => {
    button.addEventListener("click", () => {
      navigator.vibrate(10);
    });
  });
}

function handleOrientationChange() {
  window.addEventListener("orientationchange", () => {
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.querySelector(".sidebar-overlay");

    if (sidebar && sidebar.classList.contains("show")) {
      sidebar.classList.remove("show", "open");
      if (overlay) overlay.classList.remove("show", "active");
      document.body.classList.remove("sidebar-open");
    }

    setTimeout(() => {
      window.dispatchEvent(new Event("resize"));
    }, 100);
  });
}

function preventDoubleTapZoom() {
  let lastTap = 0;

  const elements = document.querySelectorAll(".btn, .card, img");

  elements.forEach((element) => {
    element.addEventListener("touchend", (e) => {
      const currentTime = new Date().getTime();
      const tapLength = currentTime - lastTap;

      if (tapLength < 300 && tapLength > 0) {
        e.preventDefault();
      }

      lastTap = currentTime;
    });
  });
}

function initializeMobileEnhancements() {
  if (!isMobile()) {
    console.log("📱 Desktop detected, skipping mobile enhancements");
    return;
  }

  console.log("📱 Initializing mobile enhancements...");

  try {
    (function ensureSidebarClosedOnLoad() {
      try {
        const sidebar = document.querySelector(".sidebar");
        const overlay =
          document.querySelector(".sidebar-overlay") || getOrCreateOverlay();
        if (!sidebar) return;
        if (window.innerWidth <= 768) {
          sidebar.classList.remove("show", "open", "active");
          overlay.classList.remove("show", "active");
          document.body.classList.remove("sidebar-open");
          sidebar.style.transform =
            sidebar.style.transform || "translateX(-120%)";
        }
      } catch (e) {
        console.warn(
          "mobile-enhancements: ensureSidebarClosedOnLoad failed",
          e,
        );
      }
    })();

    window.toggleSidebar = enhancedToggleSidebar;
    addTouchFeedback();
    enableImageSwipe();
    optimizeTablesForMobile();
    enhanceMobileFormInputs();
    addScrollToTop();
    optimizeModalsForMobile();
    improveScrolling();
    addHapticFeedback();
    handleOrientationChange();
    preventDoubleTapZoom();

    getOrCreateOverlay();

    console.log("✅ Mobile enhancements initialized");
  } catch (error) {
    console.error("❌ Error initializing mobile enhancements:", error);
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeMobileEnhancements);
} else {
  initializeMobileEnhancements();
}

window.addEventListener("load", () => {
  setTimeout(initializeMobileEnhancements, 500);
});

window.mobileEnhancements = {
  isMobile,
  enhancedToggleSidebar,
  addTouchFeedback,
  enableImageSwipe,
  optimizeTablesForMobile,
};

console.log("✅ mobile-enhancements.js loaded");
