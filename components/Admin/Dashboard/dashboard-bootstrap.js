(function () {
  var basePath = "Components/Admin/Dashboard/modules/";
  var modules = [
    "core-navigation-sections.js",
    "core-init-bootstrap.js",
    "core-section-display-calendar.js",
    "ui-forms-theme-tables.js",
    "ui-sorting-toast-items.js",
    "items-filters-edit-toggle.js",
    "items-booking-forms-receipts.js",
    "charts-core.js",
    "dashboard-data-rooms-search.js",
    "dashboard-exports-feedback-data.js",
    "feedback-insights-actions.js",
    "calendar-room-navigation.js",
    "booking-confirm-and-status.js",
    "booking-discount-details.js",
    "booking-filters-global-debug.js",
  ];

  for (var i = 0; i < modules.length; i++) {
    var tag =
      '<script src="' +
      basePath +
      modules[i] +
      '" onerror="console.error(\'Failed loading module: ' +
      modules[i] +
      "')\"><\\/script>";
    document.write(tag);
  }
})();
