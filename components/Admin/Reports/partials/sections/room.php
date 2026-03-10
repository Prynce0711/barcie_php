<div class="report-section" id="roomReportSection">
  <h5 class="border-bottom pb-2 mb-3">
    <i class="fas fa-door-open me-2"></i>Room Reports
  </h5>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-star me-2"></i>Most Booked Rooms</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover" id="mostBookedRoomsTable">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Type</th>
                  <th>Bookings</th>
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

    <div class="col-md-6">
      <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Least Booked Rooms</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover" id="leastBookedRoomsTable">
              <thead>
                <tr>
                  <th>Room</th>
                  <th>Type</th>
                  <th>Bookings</th>
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
          <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Room Performance Comparison</h6>
        </div>
        <div class="card-body">
          <canvas id="roomPerformanceChart" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-circle-info me-2"></i>Current Room Status</h6>
        </div>
        <div class="card-body">
          <canvas id="roomStatusPieChart" height="200"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Room Type Distribution</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="roomTypeDistTable">
              <thead class="table-light">
                <tr>
                  <th>Room Type</th>
                  <th>Total Rooms</th>
                  <th>Available</th>
                  <th>Occupied</th>
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
</div>
