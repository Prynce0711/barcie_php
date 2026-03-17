window.AdminBadgeUtils =
  window.AdminBadgeUtils ||
  (function () {
    function normalize(value) {
      return String(value || "")
        .trim()
        .toLowerCase();
    }

    /**
     * Returns a consistent inline style string for admin badges.
     * No animations, no blinking — clean solid pills only.
     */
    function badgeInlineStyle(fontSize) {
      fontSize = fontSize || "0.65rem";
      return (
        "font-size: " +
        fontSize +
        "; " +
        "padding: 0.35rem 0.6rem; " +
        "border-radius: 0.75rem; " +
        "font-weight: 600; " +
        "letter-spacing: 0.02em; " +
        "white-space: nowrap; " +
        "animation: none;"
      );
    }

    /**
     * Returns a full badge HTML string.
     */
    function badgeHtml(label, bgClass, fontSize) {
      var style = badgeInlineStyle(fontSize);
      var escaped = String(label || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
      return (
        '<span class="badge ' +
        bgClass +
        '" style="' +
        style +
        '">' +
        escaped +
        "</span>"
      );
    }

    function roleLabel(role) {
      var map = {
        super_admin: "Super Admin",
        manager: "Manager",
        admin: "Admin",
        staff: "Staff",
      };
      var key = normalize(role);
      return map[key] || String(role || "Staff");
    }

    function roleBadgeClass(role) {
      var key = normalize(role);
      if (key === "super_admin") return "bg-danger";
      if (key === "manager") return "bg-warning text-dark";
      if (key === "staff") return "bg-secondary";
      return "bg-primary";
    }

    function accessBadgeClass() {
      return "bg-info";
    }

    function bookingStatusClass(status) {
      var map = {
        pending: "bg-warning",
        approved: "bg-success",
        confirmed: "bg-info",
        checked_in: "bg-primary",
        checked_out: "bg-secondary",
        cancelled: "bg-warning",
        rejected: "bg-danger",
        expired: "bg-secondary",
      };
      var key = normalize(status);
      return map[key] || "bg-secondary";
    }

    function discountStatusClass(status) {
      var map = {
        pending: "bg-warning",
        approved: "bg-success",
        rejected: "bg-danger",
        none: "bg-secondary",
      };
      var key = normalize(status);
      return map[key] || "bg-secondary";
    }

    function bookingType(type) {
      var key = normalize(type);
      if (key === "reservation" || key === "") {
        return { cls: "bg-primary", label: "Reserve" };
      }
      return { cls: "bg-warning", label: "Pencil" };
    }

    return {
      badgeInlineStyle: badgeInlineStyle,
      badgeHtml: badgeHtml,
      roleLabel: roleLabel,
      roleBadgeClass: roleBadgeClass,
      accessBadgeClass: accessBadgeClass,
      bookingStatusClass: bookingStatusClass,
      discountStatusClass: discountStatusClass,
      bookingType: bookingType,
    };
  })();
