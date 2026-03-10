(function () {
  window.ReportsModule = window.ReportsModule || {};

  const utils = window.ReportsModule.utils;
  const updaters = window.ReportsModule.updaters;

  const actions = {
    initReports() {
      if (typeof Chart === "undefined") {
        console.error("Chart.js is not loaded! Charts will not render.");
        return;
      }
      actions.generateReport();
    },

    generateReport() {
      const startDate = document.getElementById("reportStartDate")?.value;
      const endDate = document.getElementById("reportEndDate")?.value;
      const roomType = document.getElementById("reportRoomType")?.value;
      const reportType =
        document.getElementById("reportType")?.value || "overview";

      if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        utils.showToast("Start date cannot be after end date", "error");
        return;
      }

      actions.showReportLoading(true);

      const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
        room_type: roomType,
        report_type: reportType,
      });

      fetch(`api/reports_data.php?${params.toString()}`)
        .then((response) => response.json())
        .then((data) => {
          if (!data.success) {
            throw new Error(data.error || "Failed to generate report");
          }

          actions.updateReportTitle(reportType);
          actions.updateReportData(data.data, reportType);
          actions.showReportLoading(false);
        })
        .catch((error) => {
          console.error("Error generating report:", error);
          utils.showToast(
            "Failed to generate report: " + error.message,
            "error",
          );
          actions.showReportLoading(false);
        });
    },

    updateReportTitle(reportType) {
      const reportTitle = document.getElementById("reportTitle");
      if (!reportTitle) return;

      const titles = {
        overview: "Overview Report",
        booking: "Booking Reports",
        occupancy: "Occupancy Reports",
        revenue: "Revenue Reports",
        guest: "Guest Reports",
        room: "Room Reports",
      };

      reportTitle.textContent = titles[reportType] || "Report";
    },

    showReportLoading(show) {
      const loading = document.getElementById("reportLoading");
      const content = document.getElementById("reportContent");
      if (!loading || !content) return;

      loading.style.display = show ? "block" : "none";
      content.style.display = show ? "none" : "block";
    },

    updateReportData(data, reportType) {
      if (data.summary) {
        updaters.updateSummaryCards(data.summary);
      } else if (reportType === "overview" && data.booking_reports) {
        updaters.calculateOverviewSummary(data);
      }

      [
        "bookingReportSection",
        "occupancyReportSection",
        "revenueReportSection",
        "guestReportSection",
        "roomReportSection",
      ].forEach((sectionId) => {
        const section = document.getElementById(sectionId);
        if (section) section.style.display = "none";
      });

      switch (reportType) {
        case "overview":
          updaters.updateAllReports(data);
          break;
        case "booking":
          document.getElementById("bookingReportSection").style.display =
            "block";
          updaters.updateBookingReports(data);
          break;
        case "occupancy":
          document.getElementById("occupancyReportSection").style.display =
            "block";
          updaters.updateOccupancyReports(data);
          break;
        case "revenue":
          document.getElementById("revenueReportSection").style.display =
            "block";
          updaters.updateRevenueReports(data);
          break;
        case "guest":
          document.getElementById("guestReportSection").style.display = "block";
          updaters.updateGuestReports(data);
          break;
        case "room":
          document.getElementById("roomReportSection").style.display = "block";
          updaters.updateRoomReports(data);
          break;
        default:
          break;
      }
    },

    exportReportPDF() {
      const params = new URLSearchParams({
        start_date: document.getElementById("reportStartDate")?.value,
        end_date: document.getElementById("reportEndDate")?.value,
        room_type: document.getElementById("reportRoomType")?.value,
        report_type: document.getElementById("reportType")?.value || "overview",
      });

      window.open(`api/export_report_pdf.php?${params.toString()}`, "_blank");
      utils.showToast("Generating PDF report...", "info");
    },

    exportReportExcel() {
      const params = new URLSearchParams({
        start_date: document.getElementById("reportStartDate")?.value,
        end_date: document.getElementById("reportEndDate")?.value,
        room_type: document.getElementById("reportRoomType")?.value,
        report_type: document.getElementById("reportType")?.value || "overview",
      });

      window.location.href = `api/export_report_excel.php?${params.toString()}`;
      utils.showToast("Generating Excel report...", "info");
    },
  };

  window.ReportsModule.actions = actions;
})();
