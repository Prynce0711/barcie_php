<?php
// Returns the recent activity HTML fragment for AJAX refresh
// This file is intended to be fetched via fetch() and returns the inner HTML

// Minimal bootstrap: connect to DB
// If included from another PHP file, define EMBED_RECENT_ACTIVITY before including.
// When accessed directly (e.g. fetch), behave as an endpoint and send headers.
require_once __DIR__ . '/../../../database/db_connect.php';

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$recent_activities = [];

// Fetch bookings (reservations) and show recent updates (creation, status changes)
$bookings_query = "SELECT 'booking' as activity_type,
          CASE
            WHEN b.updated_at IS NOT NULL AND b.updated_at > DATE_ADD(b.created_at, INTERVAL 1 MINUTE) THEN 
              CONCAT('Booking updated - ', UPPER(LEFT(b.status,1)), SUBSTRING(b.status,2), ' - ', i.name)
            ELSE CONCAT('New booking for ', i.name, CASE WHEN i.room_number IS NOT NULL THEN CONCAT(' (Room #', i.room_number, ')') ELSE '' END)
          END as activity_title,
          CONCAT('Guest: ', b.guest_name,
            ' - Check-in: ', DATE_FORMAT(b.check_in_date, '%b %d, %Y'),
            ' - Status: ', UPPER(LEFT(b.status,1)), SUBSTRING(b.status,2)) as activity_details,
          b.status as activity_status,
          GREATEST(COALESCE(b.updated_at, b.created_at), COALESCE(b.payment_verified_at, '1970-01-01 00:00:00')) as activity_time,
          'fa-calendar-check' as activity_icon,
          CASE 
            WHEN b.status = 'confirmed' OR b.status = 'approved' THEN 'success'
            WHEN b.status = 'pending' THEN 'warning'
            WHEN b.status = 'cancelled' THEN 'danger'
            ELSE 'primary'
          END as activity_color
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
          ";

// Fetch pencil bookings
$pencil_query = "SELECT 'pencil' as activity_type,
                        CASE
                          WHEN pb.updated_at IS NOT NULL AND pb.updated_at > DATE_ADD(pb.created_at, INTERVAL 1 MINUTE) THEN
                            CONCAT('Pencil booking updated - ', UPPER(SUBSTRING(pb.status, 1, 1)), SUBSTRING(pb.status, 2), ' - ', i.name)
                          ELSE 
                            CONCAT('New pencil booking for ', i.name,
                                   CASE WHEN i.room_number IS NOT NULL THEN CONCAT(' (Room #', i.room_number, ')') ELSE '' END)
                        END as activity_title,
                        CONCAT('Guest: ', pb.guest_name, 
                               ' - Date: ', DATE_FORMAT(pb.checkin, '%b %d, %Y'),
                               ' - Status: ', UPPER(SUBSTRING(pb.status, 1, 1)), SUBSTRING(pb.status, 2)) as activity_details,
                        pb.status as activity_status,
                        GREATEST(COALESCE(pb.updated_at, pb.created_at), pb.created_at) as activity_time,
                        'fa-pencil-alt' as activity_icon,
                        CASE 
                          WHEN pb.status = 'confirmed' OR pb.status = 'converted' THEN 'success'
                          WHEN pb.status = 'pending' THEN 'warning'
                          WHEN pb.status = 'cancelled' THEN 'danger'
                          ELSE 'info'
                        END as activity_color
                 FROM pencil_bookings pb
                 LEFT JOIN items i ON pb.room_id = i.id
                           ";

// Fetch feedback
$feedback_query = "SELECT 'feedback' as activity_type,
                          CONCAT('New feedback received - Rating: ', rating, '/5') as activity_title,
                          CONCAT(SUBSTRING(comments, 1, 100), CASE WHEN LENGTH(comments) > 100 THEN '...' ELSE '' END) as activity_details,
                          'received' as activity_status,
                          created_at as activity_time,
                          'fa-comment-dots' as activity_icon,
                          'info' as activity_color
                   FROM feedback
                         ";

// Fetch discount applications
$discount_query = "SELECT 'discount' as activity_type,
                          CONCAT('Discount application - ', 
                                 CASE 
                                   WHEN discount_status = 'approved' THEN 'Approved'
                                   WHEN discount_status = 'rejected' THEN 'Rejected'
                                   ELSE 'Pending'
                                 END) as activity_title,
                          CONCAT('Booking: BARCIE-', DATE_FORMAT(b.created_at, '%Y%m%d'), '-', LPAD(b.id, 4, '0')) as activity_details,
                          b.discount_status as activity_status,
                          b.updated_at as activity_time,
                          'fa-tag' as activity_icon,
                          CASE 
                            WHEN b.discount_status = 'approved' THEN 'success'
                            WHEN b.discount_status = 'rejected' THEN 'danger'
                            ELSE 'secondary'
                          END as activity_color
                   FROM bookings b
                   WHERE b.discount_status IS NOT NULL AND b.discount_status != 'none'
                   ";

        // Fetch admin news updates (create/publish/update)
        $news_query = "SELECT 'news' as activity_type,
                                CONCAT('News: ', COALESCE(NULLIF(n.status, ''), 'updated')) as activity_title,
                                CONCAT(n.title, ' - ', SUBSTRING(n.content, 1, 120)) as activity_details,
                                n.status as activity_status,
                                GREATEST(COALESCE(n.updated_at, n.published_date, n.created_at), n.published_date) as activity_time,
                                'fa-newspaper' as activity_icon,
                                CASE WHEN n.status = 'published' THEN 'success' ELSE 'secondary' END as activity_color
                         FROM news_updates n
                         ORDER BY activity_time DESC";

                      // Fetch payment verification events (verify/reject)
                      $payment_query = "SELECT 'payment' as activity_type,
                                CONCAT('Payment ',
                                  CASE WHEN b.payment_status = 'verified' THEN 'verified' WHEN b.payment_status = 'rejected' THEN 'rejected' ELSE 'updated' END,
                                  ' for ', i.name,
                                  CASE WHEN i.room_number IS NOT NULL THEN CONCAT(' #', i.room_number) ELSE '' END) as activity_title,
                                CONCAT('Guest: ', SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest:', -1), '|', 1),
                                  ' - Payment: ', COALESCE(b.payment_status, 'unknown')) as activity_details,
                                COALESCE(b.payment_status, '') as activity_status,
                                COALESCE(b.payment_verified_at, b.updated_at, b.created_at) as activity_time,
                                'fa-credit-card' as activity_icon,
                                CASE WHEN b.payment_status = 'verified' THEN 'success' WHEN b.payment_status = 'rejected' THEN 'danger' ELSE 'secondary' END as activity_color
                              FROM bookings b
                              LEFT JOIN items i ON b.room_id = i.id
                              WHERE b.payment_status IS NOT NULL
                              ";

// Combine all queries with UNION and get total count
$union_query = "($bookings_query) UNION ALL ($pencil_query) UNION ALL ($feedback_query) UNION ALL ($discount_query) UNION ALL ($payment_query) ORDER BY activity_time DESC";

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM ($union_query) as all_activities");
if (!$count_result) {
  error_log("RecentActivities COUNT query failed: " . $conn->error);
  $total_activities = 0;
} else {
  $total_activities = $count_result->fetch_assoc()['total'];
}
$total_pages = ceil($total_activities / $per_page);

// Get paginated results
$paginated_query = "$union_query LIMIT $per_page OFFSET $offset";
$recent_activity_result = $conn->query($paginated_query);
if (!$recent_activity_result) {
  error_log("RecentActivities paginated query failed: " . $conn->error);
} else {
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
    $icon = $activity['activity_icon'] ?? 'fa-circle';
    $color = $activity['activity_color'] ?? 'primary';
    $title = $activity['activity_title'] ?? 'Activity';
    $details = $activity['activity_details'] ?? '';
    $time = $activity['activity_time'] ?? '';
    
    // Status badge color
    $status = $activity['activity_status'] ?? '';
    $status_badge_color = 'secondary';
    if (in_array($status, ['approved', 'confirmed', 'checked_in'])) {
      $status_badge_color = 'success';
    } elseif ($status === 'pending') {
      $status_badge_color = 'warning';
    } elseif (in_array($status, ['rejected', 'cancelled'])) {
      $status_badge_color = 'danger';
    } elseif ($status === 'received') {
      $status_badge_color = 'info';
    }
?>
  <div class="activity-item d-flex p-3 <?php echo $index < count($recent_activities) - 1 ? 'border-bottom' : ''; ?>">
    <div class="activity-icon me-3">
      <div class="icon-circle bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?>" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
        <i class="fas <?php echo $icon; ?>"></i>
      </div>
    </div>
    <div class="flex-grow-1">
      <div class="activity-content">
        <h6 class="mb-1 text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($title); ?></h6>
        <p class="text-muted small mb-1"><?php echo htmlspecialchars($details); ?></p>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-<?php echo $status_badge_color; ?>" style="font-size: 0.65rem;">
            <?php echo ucfirst($status); ?>
          </span>
          <span class="text-muted small">
            <i class="fas fa-clock me-1"></i><?php echo date('M d, Y H:i', strtotime($time)); ?>
          </span>
        </div>
      </div>
    </div>
  </div>
<?php
  endforeach;
  
  // Pagination controls
  if ($total_pages > 1):
?>
  <div class="activity-pagination border-top pt-3 mt-2">
    <nav aria-label="Recent activities pagination">
      <ul class="pagination pagination-sm justify-content-center mb-0">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="#" onclick="loadRecentActivitiesPage(<?php echo $page - 1; ?>); return false;">
              <i class="fas fa-chevron-left"></i>
            </a>
          </li>
        <?php endif; ?>
        
        <?php
        // Show page numbers
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        for ($i = $start_page; $i <= $end_page; $i++):
        ?>
          <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
            <a class="page-link" href="#" onclick="loadRecentActivitiesPage(<?php echo $i; ?>); return false;">
              <?php echo $i; ?>
            </a>
          </li>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="#" onclick="loadRecentActivitiesPage(<?php echo $page + 1; ?>); return false;">
              <i class="fas fa-chevron-right"></i>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
    <p class="text-center text-muted small mb-0 mt-2">
      Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_activities; ?> activities)
    </p>
  </div>
<?php
  endif;
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
