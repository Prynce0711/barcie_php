(function () {
  window.ReportsModule = window.ReportsModule || {};

  const charts = {
    bookingTrendsChart: null,
    bookingStatusChart: null,
    bookingSourcesChart: null,
    occupancyTrendChart: null,
    revenueTrendChart: null,
    revenueByRoomTypeChart: null,
    guestTrendsChart: null,
    roomPerformanceChart: null,
    roomStatusPieChart: null,
  };

  function replaceChart(key, instance) {
    if (charts[key] && typeof charts[key].destroy === "function") {
      charts[key].destroy();
    }
    charts[key] = instance;
    return charts[key];
  }

  window.ReportsModule.state = {
    charts,
    replaceChart,
  };
})();
