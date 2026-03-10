<div class="report-section" id="revenueReportSection">
  <h5 class="border-bottom pb-2 mb-3">
    <i class="fas fa-money-bill-wave me-2"></i>Revenue Reports
  </h5>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-white bg-primary shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-uppercase mb-3"><i class="fas fa-coins me-2"></i>Total Revenue</h6>
          <h2 id="totalRevenue">₱0</h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-primary shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-uppercase mb-3"><i class="fas fa-calendar-alt me-2"></i>Monthly Average</h6>
          <h2 id="monthlyAvgRevenue">₱0</h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-primary shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-uppercase mb-3"><i class="fas fa-chart-line me-2"></i>Daily Average</h6>
          <h2 id="dailyAvgRevenue">₱0</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Revenue Trend</h6>
        </div>
        <div class="card-body">
          <canvas id="revenueTrendChart" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Revenue by Room Type</h6>
        </div>
        <div class="card-body">
          <canvas id="revenueByRoomTypeChart" height="200"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-table me-2"></i>Monthly Revenue Breakdown</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-sm table-hover" id="monthlyRevenueTable">
              <thead class="table-light sticky-top">
                <tr>
                  <th>Month</th>
                  <th>Revenue</th>
                  <th>Bookings</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
