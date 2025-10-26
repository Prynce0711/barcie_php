<?php
/**
 * Admin Login Test & Debug Tool
 * This helps verify admin credentials and diagnose login issues
 * 
 * Access: http://localhost/barcie_php/src/database/test_admin_login.php
 */

session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin: 20px auto;
            max-width: 800px;
        }
        .test-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        .test-body {
            padding: 30px;
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
        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            font-family: monospace;
            font-size: 13px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="test-card">
        <div class="test-header">
            <h2><i class="fas fa-user-shield me-2"></i>Admin Login Test & Debug</h2>
            <p class="mb-0 opacity-75">Verify admin credentials and test login functionality</p>
        </div>
        <div class="test-body">
            
            <!-- Database Connection Status -->
            <div class="mb-4">
                <h5><i class="fas fa-database me-2"></i>Database Connection</h5>
                <?php if (!$conn->connect_error): ?>
                    <span class="status-badge status-success">
                        <i class="fas fa-check-circle"></i> Connected
                    </span>
                    <p class="mt-2 mb-0 text-muted">Successfully connected to database: <strong><?= $dbname ?></strong></p>
                <?php else: ?>
                    <span class="status-badge status-error">
                        <i class="fas fa-times-circle"></i> Failed
                    </span>
                    <div class="alert alert-danger mt-2">
                        Error: <?= htmlspecialchars($conn->connect_error) ?>
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <!-- Admin Accounts in Database -->
            <div class="mb-4">
                <h5><i class="fas fa-users-cog me-2"></i>Admin Accounts in Database</h5>
                <?php
                $result = $conn->query("SELECT id, username, password, created_at FROM admins ORDER BY id");
                
                if ($result && $result->num_rows > 0):
                ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Password (First 20 chars)</th>
                                    <th>Password Type</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($admin = $result->fetch_assoc()): 
                                    $password = $admin['password'];
                                    $isHashed = (strlen($password) == 60 && substr($password, 0, 4) === '$2y$');
                                    $passwordPreview = substr($password, 0, 20) . '...';
                                ?>
                                    <tr>
                                        <td><?= $admin['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($admin['username']) ?></strong></td>
                                        <td>
                                            <code><?= htmlspecialchars($passwordPreview) ?></code>
                                        </td>
                                        <td>
                                            <?php if ($isHashed): ?>
                                                <span class="status-badge status-success">Hashed (bcrypt)</span>
                                            <?php else: ?>
                                                <span class="status-badge status-warning">Plain Text</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $admin['created_at'] ?? 'N/A' ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No admin accounts found in database!
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <!-- Test Login Form -->
            <div class="mb-4">
                <h5><i class="fas fa-sign-in-alt me-2"></i>Test Login</h5>
                <p class="text-muted">Enter credentials to test the login system</p>
                
                <form method="POST" action="">
                    <input type="hidden" name="test_login" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="test_username" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['test_username'] ?? 'admin') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="text" name="test_password" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['test_password'] ?? '') ?>" 
                                   placeholder="Enter password to test" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-vial me-2"></i>Test Login
                            </button>
                        </div>
                    </div>
                </form>

                <?php
                // Handle test login
                if (isset($_POST['test_login'])):
                    $test_user = trim($_POST['test_username'] ?? '');
                    $test_pass = trim($_POST['test_password'] ?? '');
                    
                    echo '<div class="mt-4">';
                    echo '<h6>Test Results:</h6>';
                    
                    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
                    $stmt->bind_param("s", $test_user);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if ($stmt->num_rows > 0):
                        $stmt->bind_result($id, $stored_password);
                        $stmt->fetch();
                        
                        echo '<div class="alert alert-info">';
                        echo '<strong>✓ Username found:</strong> ' . htmlspecialchars($test_user) . '<br>';
                        echo '<strong>User ID:</strong> ' . $id . '<br>';
                        echo '<strong>Stored Password:</strong> <code>' . htmlspecialchars(substr($stored_password, 0, 30)) . '...</code><br>';
                        echo '</div>';
                        
                        // Check if password is hashed
                        $isHashed = (strlen($stored_password) == 60 && substr($stored_password, 0, 4) === '$2y$');
                        
                        if ($isHashed):
                            // Try password_verify for hashed passwords
                            if (password_verify($test_pass, $stored_password)):
                                echo '<div class="alert alert-success">';
                                echo '<i class="fas fa-check-circle me-2"></i><strong>✓ Password Correct!</strong> (Verified with password_verify)';
                                echo '</div>';
                                echo '<div class="alert alert-warning">';
                                echo '<strong>⚠️ Issue Found:</strong> Your admin_login.php is using plain text comparison (<code>===</code>), but passwords are hashed.<br>';
                                echo '<strong>Fix:</strong> Update admin_login.php to use <code>password_verify()</code>';
                                echo '</div>';
                            else:
                                echo '<div class="alert alert-danger">';
                                echo '<i class="fas fa-times-circle me-2"></i><strong>✗ Password Incorrect</strong> (Tested with password_verify)';
                                echo '</div>';
                            endif;
                        else:
                            // Plain text comparison
                            if ($test_pass === $stored_password):
                                echo '<div class="alert alert-success">';
                                echo '<i class="fas fa-check-circle me-2"></i><strong>✓ Password Correct!</strong> (Plain text match)';
                                echo '</div>';
                            else:
                                echo '<div class="alert alert-danger">';
                                echo '<i class="fas fa-times-circle me-2"></i><strong>✗ Password Incorrect</strong><br>';
                                echo 'You entered: <code>' . htmlspecialchars($test_pass) . '</code><br>';
                                echo 'Expected: <code>' . htmlspecialchars($stored_password) . '</code>';
                                echo '</div>';
                            endif;
                        endif;
                        
                    else:
                        echo '<div class="alert alert-danger">';
                        echo '<i class="fas fa-times-circle me-2"></i><strong>✗ Username not found:</strong> ' . htmlspecialchars($test_user);
                        echo '</div>';
                    endif;
                    
                    $stmt->close();
                    echo '</div>';
                endif;
                ?>
            </div>

            <hr>

            <!-- Current Session Status -->
            <div class="mb-4">
                <h5><i class="fas fa-clock me-2"></i>Current Session Status</h5>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                    <span class="status-badge status-success">
                        <i class="fas fa-check-circle"></i> Logged In
                    </span>
                    <div class="code-block mt-2">
                        <strong>Admin ID:</strong> <?= $_SESSION['admin_id'] ?? 'N/A' ?><br>
                        <strong>Username:</strong> <?= $_SESSION['admin_username'] ?? 'N/A' ?>
                    </div>
                    <a href="../../dashboard.php" class="btn btn-success mt-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                <?php else: ?>
                    <span class="status-badge status-warning">
                        <i class="fas fa-sign-out-alt"></i> Not Logged In
                    </span>
                    <p class="mt-2 mb-0 text-muted">No active admin session</p>
                <?php endif; ?>
            </div>

            <hr>

            <!-- Quick Links -->
            <div>
                <h5><i class="fas fa-link me-2"></i>Quick Links</h5>
                <a href="../../index.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-home me-2"></i>Home Page (Login Here)
                </a>
                <a href="../../dashboard.php" class="btn btn-outline-success me-2">
                    <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                </a>
                <a href="debug_admin_booking.php" class="btn btn-outline-info">
                    <i class="fas fa-bug me-2"></i>Email Debug Tool
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
