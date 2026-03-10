(function () {
  window.ReportsModule = window.ReportsModule || {};

  const utils = window.ReportsModule.utils;
  const charts = window.ReportsModule.charts;

  const updaters = {
    updateSummaryCards(summary) {
      const bookingsEl = document.getElementById("totalBookingsCount");
      const revenueEl = document.getElementById("totalRevenueAmount");
      const occupancyEl = document.getElementById("occupancyRate");
      const guestsEl = document.getElementById("totalGuestsCount");

      if (bookingsEl)
        bookingsEl.textContent = utils.formatNumber(
          summary.total_bookings || 0,
        );
      if (revenueEl)
        revenueEl.textContent = utils.formatCurrency(
          summary.total_revenue || 0,
        );
      if (occupancyEl) {
        const rate = parseFloat(summary.occupancy_rate || 0);
        occupancyEl.textContent = rate.toFixed(2) + "%";
        const progressBar = document.getElementById("occupancyProgressBar");
        if (progressBar) progressBar.style.width = Math.min(rate, 100) + "%";
      }
      if (guestsEl)
        guestsEl.textContent = utils.formatNumber(summary.total_guests || 0);
    },

    calculateOverviewSummary(data) {
      let totalBookings = 0;
      if (data.booking_reports && data.booking_reports.monthly_bookings) {
        totalBookings = data.booking_reports.monthly_bookings.reduce(
          (sum, item) => sum + parseInt(item.count || 0, 10),
          0,
        );
      }

      updaters.updateSummaryCards({
        total_bookings: totalBookings,
        total_revenue: data.revenue_reports
          ? data.revenue_reports.total_revenue || 0
          : 0,
        total_guests: data.guest_reports
          ? data.guest_reports.total_guests || 0
          : 0,
        occupancy_rate: data.occupancy_reports
          ? data.occupancy_reports.avg_occupancy_rate || 0
          : 0,
      });
    },

    updateBookingReports(data) {
      const dailyTable = document.getElementById("dailyBookingsTable");
      if (dailyTable && data.daily_bookings) {
        dailyTable.innerHTML = "";
        data.daily_bookings.slice(0, 5).forEach((item) => {
          dailyTable.innerHTML += `<tr><td>${utils.formatDate(item.date)}</td><td><strong>${item.count}</strong> bookings</td></tr>`;
        });
      }

      const monthlyTable = document.getElementById("monthlyBookingsTable");
      if (monthlyTable && data.monthly_bookings) {
        monthlyTable.innerHTML = "";
        data.monthly_bookings.forEach((item) => {
          monthlyTable.innerHTML += `<tr><td>${item.month}</td><td><strong>${item.count}</strong> bookings</td></tr>`;
        });
      }

      const statusTable = document.getElementById("bookingStatusTable");
      if (statusTable && data.status_breakdown) {
        const tbody = statusTable.querySelector("tbody");
        if (tbody) {
          tbody.innerHTML = "";
          const total = data.status_breakdown.reduce(
            (sum, item) => sum + parseInt(item.count, 10),
            0,
          );
          data.status_breakdown.forEach((item) => {
            const percentage =
              total > 0 ? ((item.count / total) * 100).toFixed(1) : 0;
            tbody.innerHTML += `<tr><td>${utils.formatStatus(item.status)}</td><td>${utils.formatNumber(item.count)}</td><td>${percentage}%</td><td>${utils.formatCurrency(item.revenue)}</td></tr>`;
          });
        }
      }

      if (data.booking_trends)
        charts.renderBookingTrendsChart(data.booking_trends);
      if (data.status_breakdown)
        charts.renderBookingStatusChart(data.status_breakdown);
      if (data.booking_sources)
        charts.renderBookingSourcesChart(data.booking_sources);
    },

    updateOccupancyReports(data) {
      const avgEl = document.getElementById("avgOccupancyRate");
      const peakEl = document.getElementById("peakOccupancyDate");
      const occupiedEl = document.getElementById("currentlyOccupied");
      if (avgEl) avgEl.textContent = (data.avg_occupancy_rate || 0) + "%";
      if (peakEl && data.peak_occupancy)
        peakEl.textContent = utils.formatDate(data.peak_occupancy.date);
      if (occupiedEl) occupiedEl.textContent = data.currently_occupied || 0;

      const statusTable = document.getElementById("roomStatusTable");
      if (statusTable && data.room_status) {
        const tbody = statusTable.querySelector("tbody");
        if (tbody) {
          tbody.innerHTML = "";
          const total = data.room_status.reduce(
            (sum, item) => sum + parseInt(item.count, 10),
            0,
          );
          data.room_status.forEach((item) => {
            const percentage =
              total > 0 ? ((item.count / total) * 100).toFixed(1) : 0;
            tbody.innerHTML += `<tr><td>${item.status}</td><td>${utils.formatNumber(item.count)}</td><td>${percentage}%</td></tr>`;
          });
        }
      }

      if (data.daily_occupancy)
        charts.renderOccupancyTrendChart(data.daily_occupancy);
    },

    updateRevenueReports(data) {
      const totalEl = document.getElementById("totalRevenue");
      const monthEl = document.getElementById("monthlyAvgRevenue");
      const dayEl = document.getElementById("dailyAvgRevenue");
      if (totalEl)
        totalEl.textContent = utils.formatCurrency(data.total_revenue || 0);
      if (monthEl)
        monthEl.textContent = utils.formatCurrency(data.monthly_average || 0);
      if (dayEl)
        dayEl.textContent = utils.formatCurrency(data.daily_average || 0);

      const monthlyTable = document.getElementById("monthlyRevenueTable");
      if (monthlyTable && data.monthly_revenue) {
        const tbody = monthlyTable.querySelector("tbody");
        if (tbody) {
          tbody.innerHTML = "";
          data.monthly_revenue.forEach((item) => {
            tbody.innerHTML += `<tr><td>${item.month_name || item.month}</td><td>${utils.formatCurrency(item.revenue)}</td><td>${utils.formatNumber(item.bookings)}</td></tr>`;
          });
        }
      }

      if (data.daily_revenue)
        charts.renderRevenueTrendChart(data.daily_revenue);
      if (data.revenue_by_room_type)
        charts.renderRevenueByRoomTypeChart(data.revenue_by_room_type);
    },

    updateGuestReports(data) {
      const totalEl = document.getElementById("totalGuests");
      const stayEl = document.getElementById("avgStayLength");
      if (totalEl)
        totalEl.textContent = utils.formatNumber(data.total_guests || 0);
      if (stayEl) stayEl.textContent = (data.avg_stay_length || 0) + " days";
      if (data.guest_trends) charts.renderGuestTrendsChart(data.guest_trends);
    },

    updateRoomReports(data) {
      const mostTable = document.getElementById("mostBookedRoomsTable");
      if (mostTable && data.most_booked) {
        const tbody = mostTable.querySelector("tbody");
        if (tbody) {
          tbody.innerHTML = "";
          data.most_booked.forEach((room) => {
            tbody.innerHTML += `<tr><td>${room.room_number}</td><td>${room.type}</td><td>${utils.formatNumber(room.bookings)}</td><td>${utils.formatCurrency(room.revenue)}</td></tr>`;
          });
        }
      }

      const leastTable = document.getElementById("leastBookedRoomsTable");
      if (leastTable && data.least_booked) {
        const tbody = leastTable.querySelector("tbody");
        if (tbody) {
          tbody.innerHTML = "";
          data.least_booked.forEach((room) => {
            tbody.innerHTML += `<tr><td>${room.room_number}</td><td>${room.type}</td><td>${utils.formatNumber(room.bookings)}</td><td>${utils.formatCurrency(room.revenue)}</td></tr>`;
          });
        }
      }

      const distTable = document.getElementById("roomTypeDistTable");
      if (distTable && data.room_type_distribution) {
        const tbody = distTable.querySelector("tbody");
        if (tbody) {
          tbody.innerHTML = "";
          data.room_type_distribution.forEach((type) => {
            tbody.innerHTML += `<tr><td>${type.type}</td><td>${utils.formatNumber(type.total_rooms)}</td><td>${utils.formatNumber(type.available)}</td><td>${utils.formatNumber(type.occupied)}</td></tr>`;
          });
        }
      }

      if (data.room_performance)
        charts.renderRoomPerformanceChart(data.room_performance);
      if (data.room_type_distribution)
        charts.renderRoomStatusPieChart(data.room_type_distribution);
    },

    updateAllReports(data) {
      document.getElementById("bookingReportSection").style.display = "block";
      document.getElementById("occupancyReportSection").style.display = "block";
      document.getElementById("revenueReportSection").style.display = "block";
      document.getElementById("guestReportSection").style.display = "block";
      document.getElementById("roomReportSection").style.display = "block";

      if (data.booking_reports)
        updaters.updateBookingReports(data.booking_reports);
      if (data.occupancy_reports)
        updaters.updateOccupancyReports(data.occupancy_reports);
      if (data.revenue_reports)
        updaters.updateRevenueReports(data.revenue_reports);
      if (data.guest_reports) updaters.updateGuestReports(data.guest_reports);
      if (data.room_reports) updaters.updateRoomReports(data.room_reports);
    },
  };

  window.ReportsModule.updaters = updaters;
})();
