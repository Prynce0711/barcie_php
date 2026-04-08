<div class="report-section" id="bookingReportSection">
  <h5 class="border-bottom pb-2 mb-3">
    <i class="fas fa-calendar-check me-2"></i>Booking Reports
  </h5>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Daily Bookings</h6>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tbody id="dailyBookingsTable">
              <tr><td colspan="2" class="text-center text-muted">No data available</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Monthly Bookings</h6>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tbody id="monthlyBookingsTable">
              <tr><td colspan="2" class="text-center text-muted">No data available</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Booking Status Breakdown</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="bookingStatusTable">
              <thead class="table-light">
                <tr>
                  <th>Status</th>
                  <th>Count</th>
                  <th>Percentage</th>
                  <th>Revenue</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="4" class="text-center text-muted">No data available</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-source me-2"></i>Booking Sources</h6>
        </div>
        <div class="card-body">
          <canvas id="bookingSourcesChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
