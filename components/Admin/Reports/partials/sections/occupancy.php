<div class="report-section" id="occupancyReportSection">
  <h5 class="border-bottom pb-2 mb-3">
    <i class="fas fa-bed me-2"></i>Occupancy Reports
  </h5>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-center border-primary shadow-sm">
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-percentage me-2"></i>Average Occupancy Rate</h6>
          <h2 class="text-primary" id="avgOccupancyRate">0%</h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center border-primary shadow-sm">
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-calendar-check me-2"></i>Peak Occupancy Date</h6>
          <h2 class="text-primary" id="peakOccupancyDate">-</h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center border-primary shadow-sm">
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-door-closed me-2"></i>Rooms Currently Occupied</h6>
          <h2 class="text-primary" id="currentlyOccupied">0</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>Daily Occupancy Trend</h6>
        </div>
        <div class="card-body">
          <canvas id="occupancyTrendChart" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-list-check me-2"></i>Room Status Distribution</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="roomStatusTable">
              <thead class="table-light">
                <tr>
                  <th>Status</th>
                  <th>Count</th>
                  <th>Percentage</th>
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
