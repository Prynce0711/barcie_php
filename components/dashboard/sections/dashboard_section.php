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
                  <div class="flex-grow-1">
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
                  <div class="flex-grow-1">
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
            <div class="card h-100 border-0 shadow-sm hover-lift" style="border-left: 4px solid #ffc107 !important; transition: all 0.3s ease;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="flex-grow-1">
                    <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Guest Satisfaction</p>
                    <h2 class="mb-0 fw-bold" style="color: #ffc107; font-size: 2.5rem;">
                      <?php echo number_format($feedback_stats['avg_rating'], 1); ?>
                    </h2>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 56px; height: 56px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">
                    <i class="fas fa-star fa-lg text-white"></i>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= round($feedback_stats['avg_rating']) ? 'text-warning' : 'text-muted'; ?> me-1"></i>
                  <?php endfor; ?>
                  <span class="text-muted small ms-1">out of 5.0</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card h-100 border-0 shadow-sm hover-lift" style="border-left: 4px solid #5cb85c !important; transition: all 0.3s ease;">
              <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div class="flex-grow-1">
                    <p class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Total Reviews</p>
                    <h2 class="mb-0 fw-bold" style="color: #5cb85c; font-size: 2.5rem;"><?php echo $feedback_stats['total_feedback']; ?></h2>
                  </div>
                  <div class="rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 56px; height: 56px; background: linear-gradient(135deg, #5cb85c 0%, #4cae4c 100%); box-shadow: 0 4px 12px rgba(92, 184, 92, 0.3);">
                    <i class="fas fa-comments fa-lg text-white"></i>
                  </div>
                </div>
                <div class="d-flex align-items-center text-muted small">
                  <i class="fas fa-thumbs-up me-2" style="color: #5cb85c;"></i><?php echo $feedback_stats['five_star']; ?> five-star reviews
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

          <!-- Status Distribution -->
          <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e9ecef;">
                <h5 class="mb-0 fw-bold" style="color: #2a5298;">
                  <i class="fas fa-chart-pie me-2"></i>Booking Status
                </h5>
                <small class="text-muted">Current distribution</small>
              </div>
              <div class="card-body p-4">
                <div style="height: 200px;" class="mb-3">
                  <canvas id="statusChart" width="100%" height="200"></canvas>
                </div>
                <div class="status-legend">
                  <?php
                  $total_for_percentage = $total_bookings > 0 ? $total_bookings : 1;
                  $status_colors = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'confirmed' => 'success',
                    'checked_in' => 'info',
                    'checked_out' => 'secondary',
                    'cancelled' => 'danger',
                    'rejected' => 'danger'
                  ];

                  foreach ($status_distribution as $status => $count):
                    if ($count > 0):
                      $percentage = round(($count / $total_for_percentage) * 100, 1);
                      $color_class = $status_colors[$status] ?? 'secondary';
                      $display_name = ucfirst(str_replace('_', ' ', $status));
                      ?>
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                          <div class="legend-dot bg-<?php echo $color_class; ?> me-2"></div>
                          <small><?php echo $display_name; ?></small>
                        </div>
                        <small class="text-muted fw-bold"><?php echo $percentage; ?>%</small>
                      </div>
                      <?php
                    endif;
                  endforeach;

                  if ($total_bookings == 0):
                    ?>
                    <div class="text-center text-muted">
                      <small>No bookings data</small>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Guest Satisfaction & Recent Activity -->
        <div class="row g-4">
          <!-- Guest Satisfaction Detailed -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e9ecef;">
                <h5 class="mb-0 fw-bold" style="color: #2a5298;">
                  <i class="fas fa-chart-bar me-2"></i>Guest Satisfaction Analysis
                </h5>
                <small class="text-muted">Rating breakdown & insights</small>
              </div>
              <div class="card-body p-4">
                <div class="text-center mb-4">
                  <?php
                  $avg_rating = $feedback_stats['avg_rating'];
                  if ($avg_rating >= 4.5) {
                    $status = 'Excellent';
                    $color = 'success';
                    $icon = 'fa-trophy';
                  } elseif ($avg_rating >= 4.0) {
                    $status = 'Very Good';
                    $color = 'info';
                    $icon = 'fa-thumbs-up';
                  } elseif ($avg_rating >= 3.5) {
                    $status = 'Good';
                    $color = 'warning';
                    $icon = 'fa-star';
                  } elseif ($avg_rating >= 3.0) {
                    $status = 'Average';
                    $color = 'secondary';
                    $icon = 'fa-minus-circle';
                  } else {
                    $status = 'Needs Improvement';
                    $color = 'danger';
                    $icon = 'fa-exclamation-triangle';
                  }
                  ?>
                  <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle" 
                       style="width: 90px; height: 90px; background: linear-gradient(135deg, <?php echo $color === 'success' ? '#5cb85c 0%, #4cae4c 100%' : ($color === 'info' ? '#4a90e2 0%, #357abd 100%' : '#ffc107 0%, #ff9800 100%'); ?>); box-shadow: 0 6px 20px rgba(<?php echo $color === 'success' ? '92, 184, 92' : ($color === 'info' ? '74, 144, 226' : '255, 193, 7'); ?>, 0.4);">
                    <i class="fas <?php echo $icon; ?> text-white" style="font-size: 2.5rem;"></i>
                  </div>
                  <h4 class="mb-2 fw-bold" style="color: <?php echo $color === 'success' ? '#5cb85c' : ($color === 'info' ? '#4a90e2' : '#ffc107'); ?>;"><?php echo $status; ?></h4>
                  <div class="mb-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>" style="font-size: 1.25rem;"></i>
                    <?php endfor; ?>
                  </div>
                  <h2 class="mb-1 fw-bold" style="color: #2a5298; font-size: 2.5rem;"><?php echo number_format($feedback_stats['avg_rating'], 1); ?><span style="font-size: 1.5rem; color: #6c757d;">/5.0</span></h2>
                  <p class="text-muted mb-0">Based on <strong><?php echo $feedback_stats['total_feedback']; ?></strong> guest reviews</p>
                </div>

                <!-- Rating Distribution -->
                <div class="mt-4 pt-3" style="border-top: 2px solid #e9ecef;">
                  <h6 class="text-muted text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Rating Breakdown</h6>
                  <?php
                  $total_reviews = $feedback_stats['total_feedback'];
                  $ratings = [
                    5 => ['count' => $feedback_stats['five_star'], 'color' => '#5cb85c'],
                    4 => ['count' => $feedback_stats['four_star'], 'color' => '#4a90e2'],
                    3 => ['count' => $feedback_stats['three_star'], 'color' => '#ffc107'],
                    2 => ['count' => $feedback_stats['two_star'], 'color' => '#ff9800'],
                    1 => ['count' => $feedback_stats['one_star'], 'color' => '#dc3545']
                  ];
                  foreach ($ratings as $star => $data):
                    $percentage = $total_reviews > 0 ? ($data['count'] / $total_reviews * 100) : 0;
                    ?>
                    <div class="d-flex align-items-center mb-3">
                      <div class="me-3" style="width: 30px;">
                        <span class="fw-bold" style="color: #2a5298;"><?php echo $star; ?></span>
                        <i class="fas fa-star text-warning ms-1"></i>
                      </div>
                      <div class="flex-grow-1 me-3">
                        <div class="progress" style="height: 10px; border-radius: 10px; background-color: #e9ecef;">
                          <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, <?php echo $data['color']; ?> 0%, <?php echo $data['color']; ?>dd 100%); border-radius: 10px;"></div>
                        </div>
                      </div>
                      <span class="fw-semibold" style="width: 80px; color: #6c757d;">
                        <?php echo $data['count']; ?> <small class="text-muted">(<?php echo number_format($percentage, 0); ?>%)</small>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity Feed -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white py-3" style="border-bottom: 2px solid #e9ecef;">
                <div class="row align-items-center">
                  <div class="col">
                    <h5 class="mb-0 fw-bold" style="color: #2a5298;">
                      <i class="fas fa-clock me-2"></i>Recent Activity
                    </h5>
                    <small class="text-muted">Latest bookings & updates</small>
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-sm shadow-sm" onclick="refreshRecentActivities(this)" style="background-color: #f8f9fa; border: 1px solid #e9ecef; color: #2a5298;">
                      <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body p-0">
                <div id="recent-activities-container" class="activity-timeline" style="max-height: 400px; overflow-y: auto;">
                  <?php
                  // Embed the recent activities fragment for initial render while keeping
                  // it fetchable via AJAX. The fragment file will detect EMBED_RECENT_ACTIVITY
                  // and avoid sending headers/exiting when embedded.
                  define('EMBED_RECENT_ACTIVITY', true);
                  include __DIR__ . '/recent_activities_fragment.php';
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
        // Compute base path so asset and fragment URLs work when app is hosted in a subdirectory.
        $barcie_base_path = dirname($_SERVER['SCRIPT_NAME']);
        if ($barcie_base_path === '/' || $barcie_base_path === '\\') $barcie_base_path = '';
        $barcie_base_path = rtrim($barcie_base_path, '/\\');
        ?>
        <script>
          // Expose base path for the external recent-activities script.
          window.BARCIE_BASE_PATH = '<?php echo $barcie_base_path; ?>';
        </script>
        <script src="<?php echo ($barcie_base_path ? $barcie_base_path : ''); ?>/js/dashboard/recent-activities.js"></script>