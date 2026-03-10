<!-- Report Filters Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
          <i class="fas fa-chart-bar me-2"></i>Reports & Analytics
        </h5>
        <small class="opacity-75">Generate comprehensive reports and insights</small>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label for="reportStartDate" class="form-label fw-semibold">
              <i class="fas fa-calendar-start me-1"></i>Start Date
            </label>
            <input type="date" class="form-control" id="reportStartDate" value="<?php echo date('Y-m-01'); ?>">
          </div>
          <div class="col-md-3">
            <label for="reportEndDate" class="form-label fw-semibold">
              <i class="fas fa-calendar-end me-1"></i>End Date
            </label>
            <input type="date" class="form-control" id="reportEndDate" value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-md-3">
            <label for="reportRoomType" class="form-label fw-semibold">
              <i class="fas fa-door-open me-1"></i>Room Type
            </label>
            <select class="form-select" id="reportRoomType">
              <option value="">All Room Types</option>
              <option value="PENTHOUSE">PENTHOUSE</option>
              <option value="SUITE">SUITE</option>
              <option value="TRIPLE">TRIPLE</option>
              <option value="TWIN">TWIN</option>
              <option value="SINGLE">SINGLE</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="reportType" class="form-label fw-semibold">
              <i class="fas fa-file-alt me-1"></i>Report Type
            </label>
            <select class="form-select" id="reportType">
              <option value="overview">Overview</option>
              <option value="booking">Booking Reports</option>
              <option value="occupancy">Occupancy Reports</option>
              <option value="revenue">Revenue Reports</option>
              <option value="guest">Guest Reports</option>
              <option value="room">Room Reports</option>
            </select>
          </div>
          <div class="col-12">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" onclick="generateReport()">
                <i class="fas fa-sync me-1"></i>Generate Report
              </button>
              <button type="button" class="btn btn-success" onclick="exportReportPDF()">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
              </button>
              <button type="button" class="btn btn-info" onclick="exportReportExcel()">
                <i class="fas fa-file-excel me-1"></i>Export Excel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
