/**
 * Motion One animations for admin dashboard.
 * Replaces CSS keyframe animations from dashboard-enhancements.css.
 * Requires: <script src="https://cdn.jsdelivr.net/npm/motion@latest/dist/motion.min.js"></script>
 */
(function () {
  "use strict";

  function initAnimations() {
    if (typeof Motion === "undefined" || !Motion.animate) {
      console.warn("[motion-animations] Motion library not loaded, skipping.");
      return;
    }

    var animate = Motion.animate;

    // 1. Footer shimmer bar (replaces .footer::before CSS animation)
    var footer = document.querySelector(".footer");
    if (footer) {
      var shimmerBar = document.createElement("div");
      shimmerBar.style.cssText =
        "position:absolute;top:0;left:0;right:0;height:3px;" +
        "background:linear-gradient(90deg,#3b82f6 0%,#2563eb 50%,#3b82f6 100%);" +
        "pointer-events:none;";
      footer.style.position = "relative";
      footer.prepend(shimmerBar);

      animate(
        shimmerBar,
        { opacity: [1, 0.7, 1] },
        { duration: 3, repeat: Infinity, easing: "ease-in-out" },
      );
    }

    // 2. Card hover lift
    document.querySelectorAll(".card").forEach(function (card) {
      card.addEventListener("mouseenter", function () {
        animate(
          card,
          { y: -2, boxShadow: "0 4px 16px rgba(0, 0, 0, 0.1)" },
          { duration: 0.3, easing: "ease-out" },
        );
      });
      card.addEventListener("mouseleave", function () {
        animate(
          card,
          { y: 0, boxShadow: "0 2px 8px rgba(0, 0, 0, 0.06)" },
          { duration: 0.3, easing: "ease-out" },
        );
      });
    });

    // 3. Button hover lift
    document.querySelectorAll(".btn").forEach(function (btn) {
      btn.addEventListener("mouseenter", function () {
        animate(
          btn,
          { y: -1, boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)" },
          { duration: 0.2, easing: "ease-out" },
        );
      });
      btn.addEventListener("mouseleave", function () {
        animate(
          btn,
          { y: 0, boxShadow: "0 1px 3px rgba(0, 0, 0, 0.1)" },
          { duration: 0.2, easing: "ease-out" },
        );
      });
    });

    // 4. Sidebar nav link hover slide
    document.querySelectorAll(".nav-link-custom").forEach(function (link) {
      link.addEventListener("mouseenter", function () {
        if (!link.classList.contains("active")) {
          animate(link, { x: 5 }, { duration: 0.2, easing: "ease-out" });
        }
      });
      link.addEventListener("mouseleave", function () {
        if (!link.classList.contains("active")) {
          animate(link, { x: 0 }, { duration: 0.2, easing: "ease-out" });
        }
      });
    });

    // 5. Mobile menu toggle scale
    var menuToggle = document.querySelector(".mobile-menu-toggle");
    if (menuToggle) {
      menuToggle.addEventListener("mouseenter", function () {
        animate(menuToggle, { scale: 1.05 }, { duration: 0.2 });
      });
      menuToggle.addEventListener("mouseleave", function () {
        animate(menuToggle, { scale: 1 }, { duration: 0.2 });
      });
    }

    // 6. Activity icon hover scale (delegated)
    var activityTimeline = document.querySelector(".activity-timeline");
    if (activityTimeline) {
      activityTimeline.addEventListener(
        "mouseenter",
        function (e) {
          var item = e.target.closest(".activity-item");
          if (item) {
            var icon = item.querySelector(".icon-circle");
            if (icon) {
              animate(icon, { scale: 1.1 }, { duration: 0.2 });
            }
          }
        },
        true,
      );
      activityTimeline.addEventListener(
        "mouseleave",
        function (e) {
          var item = e.target.closest(".activity-item");
          if (item) {
            var icon = item.querySelector(".icon-circle");
            if (icon) {
              animate(icon, { scale: 1 }, { duration: 0.2 });
            }
          }
        },
        true,
      );
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAnimations);
  } else {
    initAnimations();
  }
})();
