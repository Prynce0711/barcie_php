(function () {
  function initReportsModule() {
    if (!window.ReportsModule || !window.ReportsModule.actions) {
      console.error("Reports modules are not fully loaded.");
      return;
    }

    const actions = window.ReportsModule.actions;

    window.generateReport = actions.generateReport;
    window.exportReportPDF = actions.exportReportPDF;
    window.exportReportExcel = actions.exportReportExcel;

    actions.initReports();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initReportsModule);
  } else {
    initReportsModule();
  }
})();
