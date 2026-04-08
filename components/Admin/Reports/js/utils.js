(function () {
  window.ReportsModule = window.ReportsModule || {};

  const utils = {
    formatNumber(num) {
      return parseInt(num || 0, 10).toLocaleString();
    },

    formatCurrency(amount) {
      return (
        "\u20b1" +
        parseFloat(amount || 0).toLocaleString("en-US", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })
      );
    },

    formatDate(dateStr) {
      if (!dateStr) return "-";
      const date = new Date(dateStr);
      return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
      });
    },

    formatStatus(status) {
      return String(status || "")
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
    },

    escapeHtml(text) {
      const div = document.createElement("div");
      div.textContent = text;
      return div.innerHTML;
    },

    showToast(message, type = "info") {
      if (
        typeof window.showToast === "function" &&
        window.showToast !== utils.showToast
      ) {
        window.showToast(message, type);
      } else {
        console.log(`Toast [${type}]: ${message}`);
      }
    },
  };

  window.ReportsModule.utils = utils;
})();
