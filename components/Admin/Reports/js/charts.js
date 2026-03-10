(function () {
  window.ReportsModule = window.ReportsModule || {};

  const state = window.ReportsModule.state;
  const utils = window.ReportsModule.utils;

  const charts = {
    renderBookingTrendsChart(data) {
      const ctx = document.getElementById("bookingTrendsChart");
      if (!ctx) return;

      let chartData = data;
      if (!chartData || chartData.length === 0) {
        chartData = [
          { date: new Date().toISOString().split("T")[0], count: 0 },
        ];
      }

      state.replaceChart(
        "bookingTrendsChart",
        new Chart(ctx, {
          type: "line",
          data: {
            labels: chartData.map((item) => utils.formatDate(item.date)),
            datasets: [
              {
                label: "Bookings",
                data: chartData.map((item) => item.count),
                borderColor: "rgb(75, 192, 192)",
                backgroundColor: "rgba(75, 192, 192, 0.1)",
                tension: 0.4,
                fill: true,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
          },
        }),
      );
    },

    renderBookingStatusChart(data) {
      const ctx = document.getElementById("bookingStatusChart");
      if (!ctx) return;

      state.replaceChart(
        "bookingStatusChart",
        new Chart(ctx, {
          type: "doughnut",
          data: {
            labels: data.map((item) => utils.formatStatus(item.status)),
            datasets: [
              {
                data: data.map((item) => item.count),
                backgroundColor: [
                  "rgb(40, 167, 69)",
                  "rgb(255, 193, 7)",
                  "rgb(220, 53, 69)",
                  "rgb(108, 117, 125)",
                  "rgb(23, 162, 184)",
                ],
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: "bottom" } },
          },
        }),
      );
    },

    renderBookingSourcesChart(data) {
      const ctx = document.getElementById("bookingSourcesChart");
      if (!ctx) return;

      state.replaceChart(
        "bookingSourcesChart",
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: data.map((item) => item.source),
            datasets: [
              {
                label: "Bookings",
                data: data.map((item) => item.count),
                backgroundColor: "rgba(54, 162, 235, 0.8)",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
          },
        }),
      );
    },

    renderOccupancyTrendChart(data) {
      const ctx = document.getElementById("occupancyTrendChart");
      if (!ctx) return;

      state.replaceChart(
        "occupancyTrendChart",
        new Chart(ctx, {
          type: "line",
          data: {
            labels: data.map((item) => utils.formatDate(item.date)),
            datasets: [
              {
                label: "Occupancy Rate (%)",
                data: data.map((item) => item.occupancy_rate),
                borderColor: "rgb(255, 159, 64)",
                backgroundColor: "rgba(255, 159, 64, 0.1)",
                tension: 0.4,
                fill: true,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
              y: {
                beginAtZero: true,
                max: 100,
                ticks: { callback: (value) => value + "%" },
              },
            },
          },
        }),
      );
    },

    renderRevenueTrendChart(data) {
      const ctx = document.getElementById("revenueTrendChart");
      if (!ctx) return;

      state.replaceChart(
        "revenueTrendChart",
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: data.map((item) => utils.formatDate(item.date)),
            datasets: [
              {
                label: "Revenue",
                data: data.map((item) => item.revenue),
                backgroundColor: "rgba(40, 167, 69, 0.8)",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: (value) => "\u20b1" + value.toLocaleString(),
                },
              },
            },
          },
        }),
      );
    },

    renderRevenueByRoomTypeChart(data) {
      const ctx = document.getElementById("revenueByRoomTypeChart");
      if (!ctx) return;

      state.replaceChart(
        "revenueByRoomTypeChart",
        new Chart(ctx, {
          type: "pie",
          data: {
            labels: data.map((item) => item.type),
            datasets: [
              {
                data: data.map((item) => item.revenue),
                backgroundColor: [
                  "rgb(54, 162, 235)",
                  "rgb(255, 99, 132)",
                  "rgb(255, 205, 86)",
                  "rgb(75, 192, 192)",
                  "rgb(153, 102, 255)",
                ],
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: "bottom" } },
          },
        }),
      );
    },

    renderGuestTrendsChart(data) {
      const ctx = document.getElementById("guestTrendsChart");
      if (!ctx) return;

      state.replaceChart(
        "guestTrendsChart",
        new Chart(ctx, {
          type: "line",
          data: {
            labels: data.map((item) => utils.formatDate(item.date)),
            datasets: [
              {
                label: "Guests",
                data: data.map((item) => item.guests),
                borderColor: "rgb(153, 102, 255)",
                backgroundColor: "rgba(153, 102, 255, 0.1)",
                tension: 0.4,
                fill: true,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
          },
        }),
      );
    },

    renderRoomPerformanceChart(data) {
      const ctx = document.getElementById("roomPerformanceChart");
      if (!ctx) return;

      state.replaceChart(
        "roomPerformanceChart",
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: data.map((item) => item.room_number),
            datasets: [
              {
                label: "Bookings",
                data: data.map((item) => item.bookings),
                backgroundColor: "rgba(54, 162, 235, 0.8)",
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
          },
        }),
      );
    },

    renderRoomStatusPieChart(data) {
      const ctx = document.getElementById("roomStatusPieChart");
      if (!ctx) return;

      const chartData = data.map((item) => ({
        label: item.type,
        value: parseInt(item.occupied, 10),
      }));

      state.replaceChart(
        "roomStatusPieChart",
        new Chart(ctx, {
          type: "pie",
          data: {
            labels: chartData.map((item) => item.label),
            datasets: [
              {
                data: chartData.map((item) => item.value),
                backgroundColor: [
                  "rgb(255, 99, 132)",
                  "rgb(54, 162, 235)",
                  "rgb(255, 205, 86)",
                  "rgb(75, 192, 192)",
                  "rgb(153, 102, 255)",
                ],
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: "bottom" } },
          },
        }),
      );
    },
  };

  window.ReportsModule.charts = charts;
})();
