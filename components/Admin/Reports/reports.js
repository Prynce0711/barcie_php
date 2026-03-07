/**
 * Reports and Analytics JavaScript
 * Handles data fetching, chart rendering, and interactivity for reports
 */

// Chart instances
let bookingTrendsChart = null;
let bookingStatusChart = null;
let bookingSourcesChart = null;
let occupancyTrendChart = null;
let revenueTrendChart = null;
let revenueByRoomTypeChart = null;
let guestTrendsChart = null;
let roomPerformanceChart = null;
let roomStatusPieChart = null;

/**
 * Initialize reports section
 */
function initReports() {
  console.log("Initializing Reports module...");

  // Check if Chart.js is loaded
  if (typeof Chart === "undefined") {
    console.error("Chart.js is not loaded! Charts will not render.");
    return;
  }

  console.log("Chart.js version:", Chart.version);

  // Auto-load overview report on page load
  if (typeof generateReport === "function") {
    console.log("Auto-generating report...");
    generateReport();
  } else {
    console.error("generateReport function not found");
  }
}

/**
 * Generate report based on selected filters
 */
function generateReport() {
  const startDate = document.getElementById("reportStartDate")?.value;
  const endDate = document.getElementById("reportEndDate")?.value;
  const roomType = document.getElementById("reportRoomType")?.value;
  const reportType = document.getElementById("reportType")?.value || "overview";

  // Validate dates
  if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
    showToast("Start date cannot be after end date", "error");
    return;
  }

  // Show loading
  showReportLoading(true);

  // Build query parameters
  const params = new URLSearchParams({
    start_date: startDate,
    end_date: endDate,
    room_type: roomType,
    report_type: reportType,
  });

  // Fetch report data
  fetch(`api/reports_data.php?${params.toString()}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateReportTitle(reportType);
        updateReportData(data.data, reportType);
        showReportLoading(false);
      } else {
        throw new Error(data.error || "Failed to generate report");
      }
    })
    .catch((error) => {
      console.error("Error generating report:", error);
      showToast("Failed to generate report: " + error.message, "error");
      showReportLoading(false);
    });
}

/**
 * Update report title badge
 */
function updateReportTitle(reportType) {
  const reportTitle = document.getElementById("reportTitle");
  if (reportTitle) {
    const titles = {
      overview: "Overview Report",
      booking: "Booking Reports",
      occupancy: "Occupancy Reports",
      revenue: "Revenue Reports",
      guest: "Guest Reports",
      room: "Room Reports",
    };
    reportTitle.textContent = titles[reportType] || "Report";
  }
}

/**
 * Show/hide loading spinner
 */
function showReportLoading(show) {
  const loading = document.getElementById("reportLoading");
  const content = document.getElementById("reportContent");

  if (loading && content) {
    loading.style.display = show ? "block" : "none";
    content.style.display = show ? "none" : "block";
  }
}

/**
 * Update report data and visualizations
 */
function updateReportData(data, reportType) {
  // Update summary cards
  if (data.summary) {
    updateSummaryCards(data.summary);
  } else if (reportType === "overview" && data.booking_reports) {
    // Calculate summary from overview data
    calculateOverviewSummary(data);
  }

  // Show/hide report sections based on report type
  const sections = [
    "bookingReportSection",
    "occupancyReportSection",
    "revenueReportSection",
    "guestReportSection",
    "roomReportSection",
  ];
  sections.forEach((sectionId) => {
    const section = document.getElementById(sectionId);
    if (section) {
      section.style.display = "none";
    }
  });

  // Update specific report sections
  switch (reportType) {
    case "overview":
      updateAllReports(data);
      break;
    case "booking":
      document.getElementById("bookingReportSection").style.display = "block";
      updateBookingReports(data);
      break;
    case "occupancy":
      document.getElementById("occupancyReportSection").style.display = "block";
      updateOccupancyReports(data);
      break;
    case "revenue":
      document.getElementById("revenueReportSection").style.display = "block";
      updateRevenueReports(data);
      break;
    case "guest":
      document.getElementById("guestReportSection").style.display = "block";
      updateGuestReports(data);
      break;
    case "room":
      document.getElementById("roomReportSection").style.display = "block";
      updateRoomReports(data);
      break;
  }
}

/**
 * Update summary cards
 */
function updateSummaryCards(summary) {
  console.log("Updating summary cards:", summary);

  if (document.getElementById("totalBookingsCount")) {
    document.getElementById("totalBookingsCount").textContent = formatNumber(
      summary.total_bookings || 0,
    );
  }
  if (document.getElementById("totalRevenueAmount")) {
    document.getElementById("totalRevenueAmount").textContent = formatCurrency(
      summary.total_revenue || 0,
    );
  }
  if (document.getElementById("occupancyRate")) {
    const rate = parseFloat(summary.occupancy_rate || 0);
    document.getElementById("occupancyRate").textContent =
      rate.toFixed(2) + "%";

    // Update progress bar
    const progressBar = document.getElementById("occupancyProgressBar");
    if (progressBar) {
      progressBar.style.width = Math.min(rate, 100) + "%";
    }
  }
  if (document.getElementById("totalGuestsCount")) {
    document.getElementById("totalGuestsCount").textContent = formatNumber(
      summary.total_guests || 0,
    );
  }
}

/**
 * Calculate and update summary from overview data
 */
function calculateOverviewSummary(data) {
  let totalBookings = 0;
  let totalRevenue = 0;
  let totalGuests = 0;
  let occupancyRate = 0;

  if (data.booking_reports && data.booking_reports.monthly_bookings) {
    totalBookings = data.booking_reports.monthly_bookings.reduce(
      (sum, item) => sum + parseInt(item.count || 0),
      0,
    );
  }

  if (data.revenue_reports) {
    totalRevenue = data.revenue_reports.total_revenue || 0;
  }

  if (data.guest_reports) {
    totalGuests = data.guest_reports.total_guests || 0;
  }

  if (data.occupancy_reports) {
    occupancyRate = data.occupancy_reports.avg_occupancy_rate || 0;
  }

  updateSummaryCards({
    total_bookings: totalBookings,
    total_revenue: totalRevenue,
    total_guests: totalGuests,
    occupancy_rate: occupancyRate,
  });
}

/**
 * Update all reports for overview
 */
function updateAllReports(data) {
  document.getElementById("bookingReportSection").style.display = "block";
  document.getElementById("occupancyReportSection").style.display = "block";
  document.getElementById("revenueReportSection").style.display = "block";
  document.getElementById("guestReportSection").style.display = "block";
  document.getElementById("roomReportSection").style.display = "block";

  if (data.booking_reports) updateBookingReports(data.booking_reports);
  if (data.occupancy_reports) updateOccupancyReports(data.occupancy_reports);
  if (data.revenue_reports) updateRevenueReports(data.revenue_reports);
  if (data.guest_reports) updateGuestReports(data.guest_reports);
  if (data.room_reports) updateRoomReports(data.room_reports);
}

/**
 * Update booking reports
 */
function updateBookingReports(data) {
  // Daily bookings table
  const dailyTable = document.getElementById("dailyBookingsTable");
  if (dailyTable && data.daily_bookings) {
    dailyTable.innerHTML = "";
    data.daily_bookings.slice(0, 5).forEach((item) => {
      dailyTable.innerHTML += `<tr>
                <td>${formatDate(item.date)}</td>
                <td><strong>${item.count}</strong> bookings</td>
            </tr>`;
    });
  }

  // Monthly bookings table
  const monthlyTable = document.getElementById("monthlyBookingsTable");
  if (monthlyTable && data.monthly_bookings) {
    monthlyTable.innerHTML = "";
    data.monthly_bookings.forEach((item) => {
      monthlyTable.innerHTML += `<tr>
                <td>${item.month}</td>
                <td><strong>${item.count}</strong> bookings</td>
            </tr>`;
    });
  }

  // Booking status breakdown
  const statusTable = document.getElementById("bookingStatusTable");
  if (statusTable && data.status_breakdown) {
    const tbody = statusTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      const total = data.status_breakdown.reduce(
        (sum, item) => sum + parseInt(item.count),
        0,
      );
      data.status_breakdown.forEach((item) => {
        const percentage =
          total > 0 ? ((item.count / total) * 100).toFixed(1) : 0;
        tbody.innerHTML += `<tr>
                    <td>${formatStatus(item.status)}</td>
                    <td>${formatNumber(item.count)}</td>
                    <td>${percentage}%</td>
                    <td>${formatCurrency(item.revenue)}</td>
                </tr>`;
      });
    }
  }

  // Booking trends chart
  if (data.booking_trends) {
    renderBookingTrendsChart(data.booking_trends);
  }

  // Booking status chart
  if (data.status_breakdown) {
    renderBookingStatusChart(data.status_breakdown);
  }

  // Booking sources chart
  if (data.booking_sources) {
    renderBookingSourcesChart(data.booking_sources);
  }
}

/**
 * Update occupancy reports
 */
function updateOccupancyReports(data) {
  // Average occupancy rate
  if (document.getElementById("avgOccupancyRate")) {
    document.getElementById("avgOccupancyRate").textContent =
      (data.avg_occupancy_rate || 0) + "%";
  }

  // Peak occupancy date
  if (document.getElementById("peakOccupancyDate") && data.peak_occupancy) {
    document.getElementById("peakOccupancyDate").textContent = formatDate(
      data.peak_occupancy.date,
    );
  }

  // Currently occupied
  if (document.getElementById("currentlyOccupied")) {
    document.getElementById("currentlyOccupied").textContent =
      data.currently_occupied || 0;
  }

  // Room status table
  const statusTable = document.getElementById("roomStatusTable");
  if (statusTable && data.room_status) {
    const tbody = statusTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      const total = data.room_status.reduce(
        (sum, item) => sum + parseInt(item.count),
        0,
      );
      data.room_status.forEach((item) => {
        const percentage =
          total > 0 ? ((item.count / total) * 100).toFixed(1) : 0;
        tbody.innerHTML += `<tr>
                    <td>${item.status}</td>
                    <td>${formatNumber(item.count)}</td>
                    <td>${percentage}%</td>
                </tr>`;
      });
    }
  }

  // Occupancy trend chart
  if (data.daily_occupancy) {
    renderOccupancyTrendChart(data.daily_occupancy);
  }
}

/**
 * Update revenue reports
 */
function updateRevenueReports(data) {
  // Revenue summary
  if (document.getElementById("totalRevenue")) {
    document.getElementById("totalRevenue").textContent = formatCurrency(
      data.total_revenue || 0,
    );
  }
  if (document.getElementById("monthlyAvgRevenue")) {
    document.getElementById("monthlyAvgRevenue").textContent = formatCurrency(
      data.monthly_average || 0,
    );
  }
  if (document.getElementById("dailyAvgRevenue")) {
    document.getElementById("dailyAvgRevenue").textContent = formatCurrency(
      data.daily_average || 0,
    );
  }

  // Monthly revenue table
  const monthlyTable = document.getElementById("monthlyRevenueTable");
  if (monthlyTable && data.monthly_revenue) {
    const tbody = monthlyTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      data.monthly_revenue.forEach((item) => {
        tbody.innerHTML += `<tr>
                    <td>${item.month_name || item.month}</td>
                    <td>${formatCurrency(item.revenue)}</td>
                    <td>${formatNumber(item.bookings)}</td>
                </tr>`;
      });
    }
  }

  // Revenue trend chart
  if (data.daily_revenue) {
    renderRevenueTrendChart(data.daily_revenue);
  }

  // Revenue by room type chart
  if (data.revenue_by_room_type) {
    renderRevenueByRoomTypeChart(data.revenue_by_room_type);
  }
}

/**
 * Update guest reports
 */
function updateGuestReports(data) {
  // Guest stats
  if (document.getElementById("totalGuests")) {
    document.getElementById("totalGuests").textContent = formatNumber(
      data.total_guests || 0,
    );
  }
  if (document.getElementById("avgStayLength")) {
    document.getElementById("avgStayLength").textContent =
      (data.avg_stay_length || 0) + " days";
  }
  if (document.getElementById("returnGuests")) {
    document.getElementById("returnGuests").textContent = formatNumber(
      data.return_guests || 0,
    );
  }

  // Top guests table
  const topGuestsTable = document.getElementById("topGuestsTable");
  if (topGuestsTable && data.top_guests) {
    const tbody = topGuestsTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      data.top_guests.forEach((guest, index) => {
        tbody.innerHTML += `<tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(guest.user_name)}</td>
                    <td>${escapeHtml(guest.user_email)}</td>
                    <td>${formatNumber(guest.total_bookings)}</td>
                    <td>${formatCurrency(guest.total_spent)}</td>
                </tr>`;
      });
    }
  }

  // Guest trends chart
  if (data.guest_trends) {
    renderGuestTrendsChart(data.guest_trends);
  }
}

/**
 * Update room reports
 */
function updateRoomReports(data) {
  // Most booked rooms
  const mostBookedTable = document.getElementById("mostBookedRoomsTable");
  if (mostBookedTable && data.most_booked) {
    const tbody = mostBookedTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      data.most_booked.forEach((room) => {
        tbody.innerHTML += `<tr>
                    <td>${room.room_number}</td>
                    <td>${room.type}</td>
                    <td>${formatNumber(room.bookings)}</td>
                    <td>${formatCurrency(room.revenue)}</td>
                </tr>`;
      });
    }
  }

  // Least booked rooms
  const leastBookedTable = document.getElementById("leastBookedRoomsTable");
  if (leastBookedTable && data.least_booked) {
    const tbody = leastBookedTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      data.least_booked.forEach((room) => {
        tbody.innerHTML += `<tr>
                    <td>${room.room_number}</td>
                    <td>${room.type}</td>
                    <td>${formatNumber(room.bookings)}</td>
                    <td>${formatCurrency(room.revenue)}</td>
                </tr>`;
      });
    }
  }

  // Room type distribution table
  const distTable = document.getElementById("roomTypeDistTable");
  if (distTable && data.room_type_distribution) {
    const tbody = distTable.querySelector("tbody");
    if (tbody) {
      tbody.innerHTML = "";
      data.room_type_distribution.forEach((type) => {
        tbody.innerHTML += `<tr>
                    <td>${type.type}</td>
                    <td>${formatNumber(type.total_rooms)}</td>
                    <td>${formatNumber(type.available)}</td>
                    <td>${formatNumber(type.occupied)}</td>
                </tr>`;
      });
    }
  }

  // Room performance chart
  if (data.room_performance) {
    renderRoomPerformanceChart(data.room_performance);
  }

  // Room status pie chart
  if (data.room_type_distribution) {
    renderRoomStatusPieChart(data.room_type_distribution);
  }
}

/**
 * Render booking trends chart
 */
function renderBookingTrendsChart(data) {
  const ctx = document.getElementById("bookingTrendsChart");
  if (!ctx) {
    console.warn("Booking trends chart canvas not found");
    return;
  }

  if (bookingTrendsChart) {
    bookingTrendsChart.destroy();
  }

  // Handle empty data
  if (!data || data.length === 0) {
    data = [{ date: new Date().toISOString().split("T")[0], count: 0 }];
  }

  bookingTrendsChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: data.map((item) => formatDate(item.date)),
      datasets: [
        {
          label: "Bookings",
          data: data.map((item) => item.count),
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
    },
  });
}

/**
 * Render booking status chart
 */
function renderBookingStatusChart(data) {
  const ctx = document.getElementById("bookingStatusChart");
  if (!ctx) return;

  if (bookingStatusChart) {
    bookingStatusChart.destroy();
  }

  bookingStatusChart = new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: data.map((item) => formatStatus(item.status)),
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
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

/**
 * Render booking sources chart
 */
function renderBookingSourcesChart(data) {
  const ctx = document.getElementById("bookingSourcesChart");
  if (!ctx) return;

  if (bookingSourcesChart) {
    bookingSourcesChart.destroy();
  }

  bookingSourcesChart = new Chart(ctx, {
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

/**
 * Render occupancy trend chart
 */
function renderOccupancyTrendChart(data) {
  const ctx = document.getElementById("occupancyTrendChart");
  if (!ctx) return;

  if (occupancyTrendChart) {
    occupancyTrendChart.destroy();
  }

  occupancyTrendChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: data.map((item) => formatDate(item.date)),
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          ticks: {
            callback: function (value) {
              return value + "%";
            },
          },
        },
      },
    },
  });
}

/**
 * Render revenue trend chart
 */
function renderRevenueTrendChart(data) {
  const ctx = document.getElementById("revenueTrendChart");
  if (!ctx) return;

  if (revenueTrendChart) {
    revenueTrendChart.destroy();
  }

  revenueTrendChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.map((item) => formatDate(item.date)),
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return "₱" + value.toLocaleString();
            },
          },
        },
      },
    },
  });
}

/**
 * Render revenue by room type chart
 */
function renderRevenueByRoomTypeChart(data) {
  const ctx = document.getElementById("revenueByRoomTypeChart");
  if (!ctx) return;

  if (revenueByRoomTypeChart) {
    revenueByRoomTypeChart.destroy();
  }

  revenueByRoomTypeChart = new Chart(ctx, {
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
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

/**
 * Render guest trends chart
 */
function renderGuestTrendsChart(data) {
  const ctx = document.getElementById("guestTrendsChart");
  if (!ctx) return;

  if (guestTrendsChart) {
    guestTrendsChart.destroy();
  }

  guestTrendsChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: data.map((item) => formatDate(item.date)),
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
    },
  });
}

/**
 * Render room performance chart
 */
function renderRoomPerformanceChart(data) {
  const ctx = document.getElementById("roomPerformanceChart");
  if (!ctx) return;

  if (roomPerformanceChart) {
    roomPerformanceChart.destroy();
  }

  roomPerformanceChart = new Chart(ctx, {
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
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
        },
      },
    },
  });
}

/**
 * Render room status pie chart
 */
function renderRoomStatusPieChart(data) {
  const ctx = document.getElementById("roomStatusPieChart");
  if (!ctx) return;

  if (roomStatusPieChart) {
    roomStatusPieChart.destroy();
  }

  const chartData = data.map((item) => ({
    label: item.type,
    value: parseInt(item.occupied),
  }));

  roomStatusPieChart = new Chart(ctx, {
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
      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

/**
 * Export report to PDF
 */
function exportReportPDF() {
  const startDate = document.getElementById("reportStartDate")?.value;
  const endDate = document.getElementById("reportEndDate")?.value;
  const roomType = document.getElementById("reportRoomType")?.value;
  const reportType = document.getElementById("reportType")?.value || "overview";

  const params = new URLSearchParams({
    start_date: startDate,
    end_date: endDate,
    room_type: roomType,
    report_type: reportType,
  });

  window.open(`api/export_report_pdf.php?${params.toString()}`, "_blank");
  showToast("Generating PDF report...", "info");
}

/**
 * Export report to Excel
 */
function exportReportExcel() {
  const startDate = document.getElementById("reportStartDate")?.value;
  const endDate = document.getElementById("reportEndDate")?.value;
  const roomType = document.getElementById("reportRoomType")?.value;
  const reportType = document.getElementById("reportType")?.value || "overview";

  const params = new URLSearchParams({
    start_date: startDate,
    end_date: endDate,
    room_type: roomType,
    report_type: reportType,
  });

  window.location.href = `api/export_report_excel.php?${params.toString()}`;
  showToast("Generating Excel report...", "info");
}

/**
 * Utility: Format number
 */
function formatNumber(num) {
  return parseInt(num || 0).toLocaleString();
}

/**
 * Utility: Format currency
 */
function formatCurrency(amount) {
  return (
    "₱" +
    parseFloat(amount || 0).toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
}

/**
 * Utility: Format date
 */
function formatDate(dateStr) {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}

/**
 * Utility: Format status
 */
function formatStatus(status) {
  return status
    .split("_")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

/**
 * Utility: Escape HTML
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Show toast notification (assumes popup-manager.js is loaded)
 */
function showToast(message, type = "info") {
  // Check if external toast function exists (not this one)
  if (
    typeof window.showToast === "function" &&
    window.showToast !== showToast
  ) {
    window.showToast(message, type);
  } else {
    // Fallback: log to console
    console.log(`Toast [${type}]: ${message}`);
  }
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initReports);
} else {
  initReports();
}
