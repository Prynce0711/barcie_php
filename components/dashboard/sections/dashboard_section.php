<?php
// Dashboard Section Template
// This section displays key performance metrics, analytics, and quick actions
?>

<!-- Dashboard Section Content -->

        <!-- Welcome Header -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-lg overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
              <div class="card-body p-4">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                      <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                           style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                        <i class="fas fa-tachometer-alt fa-2x text-white"></i>
                      </div>
                      <div>
                        <h3 class="text-white mb-1 fw-bold">Admin Dashboard</h3>
                        <p class="text-white-50 mb-0 small">BarCIE International Center Management</p>
                      </div>
                    </div>
                    <p class="text-white mb-2" style="font-size: 0.95rem;">
                      <i class="fas fa-chart-line me-2"></i>Real-time overview of your hotel operations and performance metrics
                    </p>
                    <div class="d-flex align-items-center text-white-50">
                      <i class="fas fa-clock me-2"></i>
                      <small>Last updated: <?php echo date('M d, Y - H:i'); ?></small>
                    </div>
                  </div>
                  <div class="col-md-4 text-center d-none d-md-block">
                    <div class="position-relative" style="opacity: 0.15;">
                      <i class="fas fa-hotel" style="font-size: 8rem; color: white;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Key Performance Metrics -->
        <div class="row g-4 mb-4">
          <div class="col-xl-3 col-lg-6">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="border-left: 4px solid #2a5298 !important; transition: all 0.3s ease;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="grow">
                    <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Total Inventory</p>
                    <h2 class="mb-0 fw-bold" style="color: #2a5298; font-size: 2.5rem;"><?php echo $total_rooms + $total_facilities; ?></h2>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 56px; height: 56px; background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%); box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);">
                    <i class="fas fa-building fa-lg text-white"></i>
                  </div>
                </div>
                <div class="d-flex align-items-center text-muted small">
                  <i class="fas fa-bed me-2" style="color: #2a5298;"></i><?php echo $total_rooms; ?> rooms
                  <span class="mx-2">•</span>
                  <i class="fas fa-warehouse me-2" style="color: #2a5298;"></i><?php echo $total_facilities; ?> facilities
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="border-left: 4px solid #4a90e2 !important; transition: all 0.3s ease;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="grow">
                    <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Active Bookings</p>
                    <h2 class="mb-0 fw-bold" style="color: #4a90e2; font-size: 2.5rem;"><?php echo $active_bookings; ?></h2>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 56px; height: 56px; background: linear-gradient(135deg, #4a90e2 0%, #2a5298 100%); box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);">
                    <i class="fas fa-calendar-check fa-lg text-white"></i>
                  </div>
                </div>
                <div class="d-flex align-items-center text-muted small">
                  <i class="fas fa-door-open me-2" style="color: #4a90e2;"></i>Currently occupied
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="border-left: 4px solid #28a745 !important; transition: all 0.3s ease;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="grow">
                    <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Total Bookings</p>
                    <h2 class="mb-0 fw-bold" style="color: #28a745; font-size: 2.5rem;"><?php echo $total_bookings; ?></h2>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 56px; height: 56px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                    <i class="fas fa-clipboard-list fa-lg text-white"></i>
                  </div>
                </div>
                <div class="d-flex align-items-center text-muted small">
                  <i class="fas fa-check-circle me-2" style="color: #28a745;"></i>All time
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="border-left: 4px solid #ffc107 !important; transition: all 0.3s ease;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="grow">
                    <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Pending</p>
                    <h2 class="mb-0 fw-bold" style="color: #ffc107; font-size: 2.5rem;"><?php echo isset($status_distribution['pending']) ? $status_distribution['pending'] : 0; ?></h2>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 56px; height: 56px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">
                    <i class="fas fa-hourglass-half fa-lg text-white"></i>
                  </div>
                </div>
                <div class="d-flex align-items-center text-muted small">
                  <i class="fas fa-clock me-2" style="color: #ffc107;"></i>Awaiting action
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Quick Actions removed -->

        <!-- Analytics Dashboard -->
        <div class="row g-4 mb-4">
          <!-- Booking Trends Chart -->
          <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e9ecef;">
                <div class="row align-items-center">
                  <div class="col">
                    <h5 class="mb-0 fw-bold" style="color: #2a5298;">
                      <i class="fas fa-chart-line me-2"></i>Booking Trends
                    </h5>
                    <small class="text-muted">Monthly performance overview</small>
                  </div>
                  <div class="col-auto">
                    <div class="btn-group btn-group-sm shadow-sm">
                      <button class="btn btn-outline-primary" type="button" onclick="refreshChart('7days')" style="border-color: #2a5298; color: #2a5298;">7 Days</button>
                      <button class="btn btn-outline-primary" type="button" onclick="refreshChart('30days')" style="border-color: #2a5298; color: #2a5298;">30 Days</button>
                      <button class="btn btn-primary active" type="button" onclick="refreshChart('12months')" style="background: #2a5298; border-color: #2a5298;">Year</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body p-4">
                <div style="height: 300px;">
                  <canvas id="bookingsChart" width="100%" height="300"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Booking Status Chart -->
          <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e9ecef;">
                <h5 class="mb-0 fw-bold" style="color: #2a5298;">
                  <i class="fas fa-chart-pie me-2"></i>Booking Status
                </h5>
                <small class="text-muted">Current distribution</small>
              </div>
              <div class="card-body p-4">
                <div style="height: 300px;">
                  <canvas id="statusChart" width="100%" height="300"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

