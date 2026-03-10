<!-- Report Content Area -->
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Report</h6>
        <span class="badge bg-primary" id="reportTitle">Overview Report</span>
      </div>
      <div class="card-body">
        <div id="reportLoading" class="text-center py-5" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Generating report...</p>
        </div>

        <div id="reportContent">
          <?php include __DIR__ . '/sections/booking.php'; ?>
          <?php include __DIR__ . '/sections/occupancy.php'; ?>
          <?php include __DIR__ . '/sections/revenue.php'; ?>
          <?php include __DIR__ . '/sections/guest.php'; ?>
          <?php include __DIR__ . '/sections/room.php'; ?>
        </div>
      </div>
    </div>
  </div>
</div>
