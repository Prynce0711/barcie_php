<?php
// Returns the recent activity HTML fragment for AJAX refresh
// This file is intended to be fetched via fetch() and returns the inner HTML

// Minimal bootstrap: connect to DB
// If included from another PHP file, define EMBED_RECENT_ACTIVITY before including.
// When accessed directly (e.g. fetch), behave as an endpoint and send headers.
require_once __DIR__ . '/../../../database/db_connect.php';

$recent_activities = [];
$recent_activity_result = $conn->query("SELECT b.type, b.details, b.created_at FROM bookings b ORDER BY b.created_at DESC LIMIT 8");
if ($recent_activity_result) {
  while ($row = $recent_activity_result->fetch_assoc()) {
    $recent_activities[] = $row;
  }
}

ob_start();
if (empty($recent_activities)):
?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
    <h6 class="text-muted">No Recent Activity</h6>
    <p class="small mb-0">New activities will appear here</p>
  </div>
<?php
else:
  foreach ($recent_activities as $index => $activity):
?>
  <div class="activity-item d-flex p-3 <?php echo $index < count($recent_activities) - 1 ? 'border-bottom' : ''; ?>">
    <div class="activity-icon me-3">
      <div class="icon-circle bg-primary bg-opacity-10 text-primary">
        <i class="fas fa-circle fa-xs"></i>
      </div>
    </div>
    <div class="flex-grow-1">
      <div class="activity-content">
        <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($activity['type']); ?></h6>
        <p class="text-muted small mb-1"><?php echo htmlspecialchars($activity['details']); ?></p>
        <div class="text-muted small">
          <i class="fas fa-user me-1"></i>Guest •
          <i class="fas fa-clock me-1"></i><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
        </div>
      </div>
    </div>
  </div>
<?php
  endforeach;
endif;
$fragment = ob_get_clean();

// If this file is requested directly (not embedded), send headers and exit after echo.
if (!defined('EMBED_RECENT_ACTIVITY')) {
  header('Content-Type: text/html; charset=utf-8');
  echo $fragment;
  exit;
}

// When embedded, simply echo the HTML fragment (caller may handle output buffering)
echo $fragment;
