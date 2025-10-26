<?php
/**
 * Admin Booking Update Email Debug Tool
 * This tool helps diagnose issues with admin booking status update emails
 * 
 * Access: http://localhost/barcie_php/src/database/debug_admin_booking.php
 */

// Start session to check admin status
session_start();

// Include database connection
require_once 'db_connect.php';

// Load vendor autoload for PHPMailer
$vendor_path = __DIR__ . '/../../vendor/autoload.php';
$vendor_available = file_exists($vendor_path);
if ($vendor_available) {
    require_once $vendor_path;
}

// Import PHPMailer classes at top level
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Booking Update Email Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .debug-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .debug-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .debug-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .debug-body {
            padding: 25px;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin: 5px;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-info { background: #d1ecf1; color: #0c5460; }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .test-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
        }
        .info-table {
            width: 100%;
            margin: 15px 0;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .info-table td:first-child {
            font-weight: 600;
            width: 30%;
            color: #495057;
        }
        .log-entry {
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }
        .log-success { background: #d4edda; border-left: 4px solid #28a745; }
        .log-error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .log-info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="debug-container">
        <!-- Header -->
        <div class="debug-card">
            <div class="debug-header">
                <h2><i class="fas fa-bug me-2"></i>Admin Booking Update Email Debug Tool</h2>
                <p class="mb-0 opacity-75">Diagnose email sending issues for booking status updates</p>
            </div>
        </div>

        <!-- Admin Session Check -->
        <div class="debug-card">
            <div class="debug-header">
                <h5><i class="fas fa-user-shield me-2"></i>Admin Session Status</h5>
            </div>
            <div class="debug-body">
                <?php if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <span class="status-badge status-success">
                        <i class="fas fa-check-circle"></i> Admin Logged In
                    </span>
                    <p class="mt-3 mb-0">
                        <strong>Admin Username:</strong> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'N/A') ?>
                    </p>
                <?php else: ?>
                    <span class="status-badge status-error">
                        <i class="fas fa-times-circle"></i> Not Logged In as Admin
                    </span>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You must be logged in as admin to update bookings. 
                        <a href="../../dashboard.php" class="alert-link">Login here</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Environment Check -->
        <div class="debug-card">
            <div class="debug-header">
                <h5><i class="fas fa-cog me-2"></i>Environment Configuration</h5>
            </div>
            <div class="debug-body">
                <table class="info-table">
                    <tr>
                        <td><i class="fas fa-folder me-2"></i>Vendor Folder</td>
                        <td>
                            <?php if ($vendor_available): ?>
                                <span class="status-badge status-success">
                                    <i class="fas fa-check"></i> Available
                                </span>
                                <div class="code-block mt-2"><?= htmlspecialchars($vendor_path) ?></div>
                            <?php else: ?>
                                <span class="status-badge status-error">
                                    <i class="fas fa-times"></i> Not Found
                                </span>
                                <div class="alert alert-danger mt-2">
                                    PHPMailer not available. Run: <code>composer install</code>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-envelope me-2"></i>PHPMailer Class</td>
                        <td>
                            <?php if (class_exists('PHPMailer\PHPMailer\PHPMailer')): ?>
                                <span class="status-badge status-success">
                                    <i class="fas fa-check"></i> Loaded
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-error">
                                    <i class="fas fa-times"></i> Not Loaded
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-file me-2"></i>Mail Config</td>
                        <td>
                            <?php 
                            $mail_config_path = __DIR__ . '/mail_config.php';
                            if (file_exists($mail_config_path)): 
                            ?>
                                <span class="status-badge status-success">
                                    <i class="fas fa-check"></i> Found
                                </span>
                                <?php 
                                $mail_config = include $mail_config_path;
                                if (is_array($mail_config)):
                                ?>
                                    <table class="info-table mt-2">
                                        <tr>
                                            <td>SMTP Host</td>
                                            <td><?= htmlspecialchars($mail_config['host'] ?? 'Not Set') ?></td>
                                        </tr>
                                        <tr>
                                            <td>SMTP Port</td>
                                            <td><?= htmlspecialchars($mail_config['port'] ?? 'Not Set') ?></td>
                                        </tr>
                                        <tr>
                                            <td>SMTP Secure</td>
                                            <td><?= htmlspecialchars($mail_config['secure'] ?? 'Not Set') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Username</td>
                                            <td>
                                                <?php 
                                                $username = $mail_config['username'] ?? '';
                                                echo !empty($username) ? 
                                                    '<span class="status-badge status-success">Set</span> ' . htmlspecialchars($username) : 
                                                    '<span class="status-badge status-error">Empty</span>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Password</td>
                                            <td>
                                                <?php 
                                                $password = $mail_config['password'] ?? '';
                                                echo !empty($password) ? 
                                                    '<span class="status-badge status-success">Set</span> (Hidden)' : 
                                                    '<span class="status-badge status-error">Empty</span>';
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="status-badge status-error">
                                    <i class="fas fa-times"></i> Not Found
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="debug-card">
            <div class="debug-header">
                <h5><i class="fas fa-calendar-alt me-2"></i>Recent Bookings (Last 10)</h5>
            </div>
            <div class="debug-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Guest Info</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $bookings_query = "SELECT id, status, details, created_at FROM bookings ORDER BY created_at DESC LIMIT 10";
                            $bookings_result = $conn->query($bookings_query);
                            
                            if ($bookings_result && $bookings_result->num_rows > 0):
                                while ($booking = $bookings_result->fetch_assoc()):
                                    // Extract guest info
                                    $guest_name = 'Unknown';
                                    $guest_email = 'Not found';
                                    
                                    if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
                                        $guest_name = trim($matches[1]);
                                    }
                                    if (preg_match('/Email:\s*([^|]+)/', $booking['details'], $matches)) {
                                        $guest_email = trim($matches[1]);
                                    }
                                    
                                    $status_colors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'approved' => 'info',
                                        'rejected' => 'danger',
                                        'checked_in' => 'primary',
                                        'checked_out' => 'secondary',
                                        'cancelled' => 'dark'
                                    ];
                                    $badge_color = $status_colors[$booking['status']] ?? 'secondary';
                            ?>
                                    <tr>
                                        <td><strong>#<?= $booking['id'] ?></strong></td>
                                        <td>
                                            <strong><?= htmlspecialchars($guest_name) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($guest_email) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $badge_color ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= date('M j, Y H:i', strtotime($booking['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="showDetails(<?= $booking['id'] ?>, '<?= htmlspecialchars(addslashes($booking['details'])) ?>')">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="testEmailUpdate(<?= $booking['id'] ?>, '<?= htmlspecialchars($guest_email) ?>')">
                                                <i class="fas fa-envelope"></i> Test Email
                                            </button>
                                        </td>
                                    </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No bookings found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Manual Email Test -->
        <div class="debug-card">
            <div class="debug-header">
                <h5><i class="fas fa-paper-plane me-2"></i>Manual Email Test</h5>
            </div>
            <div class="debug-body">
                <div class="test-section">
                    <h6>Test Admin Update Email</h6>
                    <p class="text-muted">Send a test admin booking update email to verify the email system works</p>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="test_action" value="send_test_email">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Recipient Email</label>
                                <input type="email" name="test_email" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['test_email'] ?? 'pc.clemente11@gmail.com') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Action Type</label>
                                <select name="test_action_type" class="form-select">
                                    <option value="approve">Approve Booking</option>
                                    <option value="reject">Reject Booking</option>
                                    <option value="checkin">Check In</option>
                                    <option value="checkout">Check Out</option>
                                    <option value="cancel">Cancel Booking</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php
                // Handle test email sending
                if (isset($_POST['test_action']) && $_POST['test_action'] === 'send_test_email' && $vendor_available):
                    $test_email = $_POST['test_email'] ?? '';
                    $test_action_type = $_POST['test_action_type'] ?? 'approve';
                    
                    // Load mail config
                    $mail_config = include __DIR__ . '/mail_config.php';
                    
                    $mail = new PHPMailer(true);
                    
                    echo '<div class="alert alert-info mt-3">';
                    echo '<h6><i class="fas fa-info-circle me-2"></i>Email Test Results</h6>';
                    echo '<div class="log-entry log-info">Starting email test...</div>';
                    
                    try {
                        // Server settings
                        $mail->SMTPDebug = 2; // Enable verbose debug output
                        $mail->isSMTP();
                        $mail->Host       = $mail_config['host'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $mail_config['username'];
                        $mail->Password   = $mail_config['password'];
                        $mail->SMTPSecure = $mail_config['secure'];
                        $mail->Port       = $mail_config['port'];

                        echo '<div class="log-entry log-info">SMTP Host: ' . htmlspecialchars($mail_config['host']) . ':' . $mail_config['port'] . '</div>';
                        echo '<div class="log-entry log-info">SMTP User: ' . htmlspecialchars($mail_config['username']) . '</div>';
                        
                        // Recipients
                        $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
                        $mail->addAddress($test_email);
                        
                        // Action-specific content
                        $action_messages = [
                            'approve' => ['subject' => 'Booking Approved', 'status' => 'confirmed', 'message' => 'Your booking has been approved!'],
                            'reject' => ['subject' => 'Booking Rejected', 'status' => 'rejected', 'message' => 'Unfortunately, your booking could not be approved.'],
                            'checkin' => ['subject' => 'Check-in Confirmed', 'status' => 'checked_in', 'message' => 'You have been checked in successfully!'],
                            'checkout' => ['subject' => 'Check-out Completed', 'status' => 'checked_out', 'message' => 'Thank you for staying with us!'],
                            'cancel' => ['subject' => 'Booking Cancelled', 'status' => 'cancelled', 'message' => 'Your booking has been cancelled.']
                        ];
                        
                        $action_info = $action_messages[$test_action_type];
                        
                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = '[TEST] ' . $action_info['subject'] . ' - BKG-TEST-001';
                        
                        $emailBody = '
                        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
                                <h1 style="color: white; margin: 0;">Booking Update - TEST EMAIL</h1>
                            </div>
                            <div style="padding: 30px; background: #f8f9fa;">
                                <h2 style="color: #333;">Hello, Test Guest!</h2>
                                <p style="color: #666; font-size: 16px; line-height: 1.6;">
                                    ' . $action_info['message'] . '
                                </p>
                                <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
                                    <p><strong>Receipt Number:</strong> BKG-TEST-001</p>
                                    <p><strong>New Status:</strong> <span style="color: #667eea;">' . ucfirst($action_info['status']) . '</span></p>
                                    <p><strong>Action:</strong> ' . ucfirst($test_action_type) . '</p>
                                </div>
                                <p style="color: #999; font-size: 14px; margin-top: 30px;">
                                    This is a TEST email from the admin booking debug tool.
                                </p>
                            </div>
                        </div>';
                        
                        $mail->Body = $emailBody;
                        
                        // Capture debug output
                        ob_start();
                        $result = $mail->send();
                        $debug_output = ob_get_clean();
                        
                        if ($result) {
                            echo '<div class="log-entry log-success"><i class="fas fa-check-circle me-2"></i>Email sent successfully!</div>';
                        }
                        
                        echo '<div class="code-block mt-3"><pre>' . htmlspecialchars($debug_output) . '</pre></div>';
                        
                    } catch (Exception $e) {
                        echo '<div class="log-entry log-error"><i class="fas fa-exclamation-circle me-2"></i>Email failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        echo '<div class="code-block mt-3"><pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre></div>';
                    }
                    
                    echo '</div>';
                endif;
                ?>
            </div>
        </div>

        <!-- Apache Error Logs -->
        <div class="debug-card">
            <div class="debug-header">
                <h5><i class="fas fa-file-alt me-2"></i>Recent Apache Error Logs (Email Related)</h5>
            </div>
            <div class="debug-body">
                <?php
                $log_file = 'C:/xampp/apache/logs/error.log';
                
                if (file_exists($log_file)):
                    $logs = file($log_file);
                    $email_logs = array_filter($logs, function($line) {
                        return stripos($line, 'ADMIN UPDATE EMAIL') !== false || 
                               stripos($line, 'BOOKING EMAIL') !== false ||
                               stripos($line, 'DISCOUNT UPDATE EMAIL') !== false ||
                               stripos($line, 'email') !== false;
                    });
                    
                    $recent_logs = array_slice($email_logs, -20); // Last 20 email-related logs
                    
                    if (!empty($recent_logs)):
                ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($recent_logs as $log): 
                                $log_class = 'log-info';
                                if (stripos($log, 'SUCCESS') !== false) $log_class = 'log-success';
                                if (stripos($log, 'FAILED') !== false || stripos($log, 'ERROR') !== false) $log_class = 'log-error';
                            ?>
                                <div class="log-entry <?= $log_class ?>">
                                    <?= htmlspecialchars($log) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                <?php 
                    else:
                        echo '<p class="text-muted">No email-related logs found in recent entries.</p>';
                    endif;
                else:
                ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Apache error log not found at: <code><?= htmlspecialchars($log_file) ?></code>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Flow Diagram -->
        <div class="debug-card">
            <div class="debug-header">
                <h5><i class="fas fa-sitemap me-2"></i>Email Flow Diagram</h5>
            </div>
            <div class="debug-body">
                <div class="code-block">
                    <pre style="margin: 0;">
<strong>Admin Booking Update Flow:</strong>

1. Admin clicks button (Approve/Reject/Check In/Check Out/Cancel)
   ↓
2. JavaScript: bookings-section.js → updateBookingStatus()
   - Maps status to action (e.g., 'approved' → 'approve')
   - Creates form with:
     * action = 'admin_update_booking'
     * booking_id = [ID]
     * admin_action = [approve|reject|checkin|checkout|cancel]
   ↓
3. Form submits to: src/database/user_auth.php
   ↓
4. PHP receives POST data
   - Checks admin session
   - Validates action and booking_id
   ↓
5. Updates booking status in database
   ↓
6. Extracts guest email from booking details (regex)
   ↓
7. Calls send_smtp_mail() with:
   - Guest email
   - Email subject (action-specific)
   - HTML email body
   ↓
8. PHPMailer sends via Gmail SMTP
   ↓
9. Logs result to Apache error.log:
   "ADMIN UPDATE EMAIL - Result: SUCCESS/FAILED"
   ↓
10. Redirects back to dashboard with success message

<strong>Common Failure Points:</strong>
❌ Admin not logged in → Access denied
❌ Wrong action name → Code block not executed
❌ No guest email in details → Email skipped
❌ Vendor folder missing → PHPMailer unavailable
❌ SMTP credentials wrong → Connection failed
❌ Gmail blocking → Check App Password & 2FA
                    </pre>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetails(bookingId, details) {
            alert('Booking #' + bookingId + '\n\nDetails:\n' + details);
        }

        function testEmailUpdate(bookingId, guestEmail) {
            if (!guestEmail || guestEmail === 'Not found') {
                alert('No email address found for this booking.');
                return;
            }
            
            const confirmed = confirm(
                'Test email update for Booking #' + bookingId + '\n\n' +
                'This will send a test approval email to:\n' + guestEmail + '\n\n' +
                'Continue?'
            );
            
            if (confirmed) {
                // Scroll to manual test section and pre-fill
                document.querySelector('input[name="test_email"]').value = guestEmail;
                document.querySelector('input[name="test_email"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
                document.querySelector('input[name="test_email"]').focus();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
