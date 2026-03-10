<div class="report-section" id="guestReportSection">
  <h5 class="border-bottom pb-2 mb-3">
    <i class="fas fa-users me-2"></i>Guest Reports
  </h5>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card text-center border-primary shadow-sm">
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-users me-2"></i>Total Guests</h6>
          <h2 class="text-primary" id="totalGuests">0</h2>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-center border-primary shadow-sm">
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-calendar-days me-2"></i>Average Stay Length</h6>
          <h2 class="text-primary" id="avgStayLength">0 days</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary bg-opacity-10">
          <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Guest Arrival Trends</h6>
        </div>
        <div class="card-body">
          <canvas id="guestTrendsChart" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
