<!-- Charts Row -->
<div class="row mb-4 g-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-lg h-100">
      <div class="card-header border-0 bg-white pt-4 pb-3">
        <div class="d-flex align-items-center">
          <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
            <i class="fas fa-chart-line"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold">Booking Trends</h6>
            <small class="text-muted">Daily booking patterns</small>
          </div>
        </div>
      </div>
      <div class="card-body pt-0">
        <canvas id="bookingTrendsChart" height="250"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-0 shadow-lg h-100">
      <div class="card-header border-0 bg-white pt-4 pb-3">
        <div class="d-flex align-items-center">
          <div class="icon-circle bg-success bg-opacity-10 text-success me-3">
            <i class="fas fa-chart-pie"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold">Booking Status Distribution</h6>
            <small class="text-muted">Status breakdown</small>
          </div>
        </div>
      </div>
      <div class="card-body pt-0">
        <canvas id="bookingStatusChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>
