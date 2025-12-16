<?php
// Reports and Analytics Section Template
// Comprehensive reporting for bookings, revenue, occupancy, and guests
?>

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
          <!-- Date Range Filter -->
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
          
          <!-- Room Type Filter -->
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
          
          <!-- Report Type -->
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
          
          <!-- Action Buttons -->
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

<!-- Summary Cards Row -->
<div class="row mb-4 g-4" id="reportSummaryCards">
  <div class="col-md-3">
    <div class="card border-0 shadow-lg h-100 stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-white-50 text-uppercase small fw-semibold mb-2 letter-spacing-1">Total Bookings</p>
            <h2 class="text-white fw-bold mb-0 display-6" id="totalBookingsCount">0</h2>
            <div class="progress mt-3" style="height: 4px; background: rgba(255,255,255,0.2);">
              <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
            </div>
          </div>
          <div class="icon-box">
            <i class="fas fa-calendar-check fa-2x text-white opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card border-0 shadow-lg h-100 stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-white-50 text-uppercase small fw-semibold mb-2 letter-spacing-1">Total Revenue</p>
            <h2 class="text-white fw-bold mb-0 display-6" id="totalRevenueAmount">₱0</h2>
            <div class="progress mt-3" style="height: 4px; background: rgba(255,255,255,0.2);">
              <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
            </div>
          </div>
          <div class="icon-box">
            <i class="fas fa-peso-sign fa-2x text-white opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card border-0 shadow-lg h-100 stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-white-50 text-uppercase small fw-semibold mb-2 letter-spacing-1">Occupancy Rate</p>
            <h2 class="text-white fw-bold mb-0 display-6" id="occupancyRate">0%</h2>
            <div class="progress mt-3" style="height: 4px; background: rgba(255,255,255,0.2);">
              <div class="progress-bar bg-white" role="progressbar" style="width: 0%" id="occupancyProgressBar"></div>
            </div>
          </div>
          <div class="icon-box">
            <i class="fas fa-chart-pie fa-2x text-white opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card border-0 shadow-lg h-100 stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-white-50 text-uppercase small fw-semibold mb-2 letter-spacing-1">Total Guests</p>
            <h2 class="text-white fw-bold mb-0 display-6" id="totalGuestsCount">0</h2>
            <div class="progress mt-3" style="height: 4px; background: rgba(255,255,255,0.2);">
              <div class="progress-bar bg-white" role="progressbar" style="width: 100%"></div>
            </div>
          </div>
          <div class="icon-box">
            <i class="fas fa-users fa-2x text-white opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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

<!-- Report Content Area -->
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Report</h6>
        <span class="badge bg-primary" id="reportTitle">Overview Report</span>
      </div>
      <div class="card-body">
        <!-- Loading Spinner -->
        <div id="reportLoading" class="text-center py-5" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Generating report...</p>
        </div>
        
        <!-- Report Content -->
        <div id="reportContent">
          <!-- 1. BOOKING REPORTS -->
          <div class="report-section" id="bookingReportSection">
            <h5 class="border-bottom pb-2 mb-3">
              <i class="fas fa-calendar-check me-2"></i>Booking Reports
            </h5>
            
            <!-- Daily/Monthly Stats -->
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
            
            <!-- Booking Status Breakdown -->
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
            
            <!-- Booking Sources -->
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
          
          <!-- 2. OCCUPANCY REPORTS -->
          <div class="report-section" id="occupancyReportSection">
            <h5 class="border-bottom pb-2 mb-3">
              <i class="fas fa-bed me-2"></i>Occupancy Reports
            </h5>
            
            <!-- Occupancy Rate Card -->
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
            
            <!-- Daily Occupancy Chart -->
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
            
            <!-- Room Status Distribution -->
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
          
          <!-- 3. REVENUE REPORTS -->
          <div class="report-section" id="revenueReportSection">
            <h5 class="border-bottom pb-2 mb-3">
              <i class="fas fa-money-bill-wave me-2"></i>Revenue Reports
            </h5>
            
            <!-- Revenue Summary Cards -->
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
            
            <!-- Revenue Trend Chart -->
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
            
            <!-- Revenue by Room Type -->
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
              
              <!-- Monthly/Yearly Revenue Table -->
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
          
          <!-- 4. GUEST REPORTS -->
          <div class="report-section" id="guestReportSection">
            <h5 class="border-bottom pb-2 mb-3">
              <i class="fas fa-users me-2"></i>Guest Reports
            </h5>
            
            <!-- Guest Stats Cards -->
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
            
            <!-- Guest Trends -->
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
          
          <!-- 5. ROOM REPORTS -->
          <div class="report-section" id="roomReportSection">
            <h5 class="border-bottom pb-2 mb-3">
              <i class="fas fa-door-open me-2"></i>Room Reports
            </h5>
            
            <!-- Most/Least Booked Rooms -->
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
            
            <!-- Room Performance Chart -->
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
            
            <!-- Room Status Overview -->
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
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.report-section {
  margin-bottom: 2rem;
  padding: 2rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 16px;
  border-left: 4px solid #667eea;
}

.report-section h5 {
  color: #2d3748;
  font-weight: 700;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
}

.card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 16px;
}

.stats-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
}

.sticky-top {
  position: sticky;
  top: 0;
  z-index: 10;
  background: white;
}

.shadow-lg {
  box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.bg-primary.bg-opacity-10 {
  background-color: rgba(102, 126, 234, 0.1) !important;
}

.bg-success.bg-opacity-10 {
  background-color: rgba(240, 147, 251, 0.1) !important;
}

.icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.icon-box {
  opacity: 0.9;
}

.letter-spacing-1 {
  letter-spacing: 0.5px;
}

.display-6 {
  font-size: 2rem;
}

@media (max-width: 768px) {
  .display-6 {
    font-size: 1.5rem;
  }
}

/* Chart containers */
canvas {
  max-height: 300px;
}

/* Loading animation */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

#reportLoading {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
