<?php
/**
 * Check if Enhanced Admin Management database migration has been run
 */

require_once __DIR__ . '/database/db_connect.php';

echo "<!DOCTYPE html><html><head><title>Migration Status Check</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".status{padding:10px;margin:10px 0;border-radius:5px;}";
echo ".success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}";
echo ".error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}";
echo ".warning{background:#fff3cd;color:#856404;border:1px solid #ffeeba;}";
echo ".info{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;}";
echo "table{width:100%;border-collapse:collapse;background:white;margin:20px 0;}";
echo "th,td{padding:12px;text-align:left;border:1px solid #ddd;}";
echo "th{background:#007bff;color:white;}";
echo "tr:nth-child(even){background:#f8f9fa;}";
echo ".btn{display:inline-block;padding:10px 20px;margin:10px 0;background:#007bff;color:white;text-decoration:none;border-radius:5px;}";
echo ".btn:hover{background:#0056b3;}</style></head><body>";

echo "<h1>🔍 Enhanced Admin Management - Migration Status Check</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check required tables
$required_tables = [
    'admins' => 'Main admin users table',
    'admin_activity_log' => 'Audit trail logging',
    'admin_permissions' => 'Available permissions',
    'role_permissions' => 'Default role permissions',
    'admin_custom_permissions' => 'Custom per-admin permissions',
    'admin_sessions' => 'Session tracking'
];

echo "<h2>📊 Table Status</h2>";
echo "<table><thead><tr><th>Table Name</th><th>Description</th><th>Status</th><th>Row Count</th></tr></thead><tbody>";

$missing_tables = [];
foreach ($required_tables as $table => $description) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = $result && $result->num_rows > 0;
    
    $count = 0;
    if ($exists) {
        $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
        if ($count_result) {
            $count_row = $count_result->fetch_assoc();
            $count = $count_row['cnt'];
        }
    }
    
    $status_class = $exists ? 'success' : 'error';
    $status_text = $exists ? '✅ EXISTS' : '❌ MISSING';
    
    if (!$exists) {
        $missing_tables[] = $table;
    }
    
    echo "<tr>";
    echo "<td><strong>$table</strong></td>";
    echo "<td>$description</td>";
    echo "<td class='$status_class'>$status_text</td>";
    echo "<td>" . ($exists ? number_format($count) : 'N/A') . "</td>";
    echo "</tr>";
}
echo "</tbody></table>";

// Check required columns in admins table
echo "<h2>🔧 Admins Table Columns</h2>";

$required_columns = [
    'last_activity' => 'datetime',
    'full_name' => 'varchar(100)',
    'phone_number' => 'varchar(20)',
    'modified_by' => 'int',
    'failed_login_attempts' => 'int'
];

$result = $conn->query("SHOW COLUMNS FROM admins");
$existing_columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
}

echo "<table><thead><tr><th>Column Name</th><th>Expected Type</th><th>Status</th></tr></thead><tbody>";
$missing_columns = [];
foreach ($required_columns as $column => $type) {
    $exists = in_array($column, $existing_columns);
    $status_class = $exists ? 'success' : 'error';
    $status_text = $exists ? '✅ EXISTS' : '❌ MISSING';
    
    if (!$exists) {
        $missing_columns[] = $column;
    }
    
    echo "<tr>";
    echo "<td><strong>$column</strong></td>";
    echo "<td><code>$type</code></td>";
    echo "<td class='$status_class'>$status_text</td>";
    echo "</tr>";
}
echo "</tbody></table>";

// Overall status
echo "<h2>📋 Migration Status Summary</h2>";

if (empty($missing_tables) && empty($missing_columns)) {
    echo "<div class='status success'>";
    echo "<h3>✅ MIGRATION COMPLETE!</h3>";
    echo "<p>All required tables and columns are present. Your enhanced admin management system is ready to use!</p>";
    echo "<p><a href='dashboard.php' class='btn'>Go to Dashboard</a></p>";
    echo "</div>";
} else {
    echo "<div class='status error'>";
    echo "<h3>❌ MIGRATION REQUIRED</h3>";
    echo "<p>The database migration has NOT been run yet. The enhanced features will not work until you run the migration SQL.</p>";
    
    if (!empty($missing_tables)) {
        echo "<p><strong>Missing Tables:</strong> " . implode(', ', $missing_tables) . "</p>";
    }
    if (!empty($missing_columns)) {
        echo "<p><strong>Missing Columns in admins table:</strong> " . implode(', ', $missing_columns) . "</p>";
    }
    
    echo "</div>";
    
    echo "<div class='status warning'>";
    echo "<h3>🚀 How to Run Migration</h3>";
    echo "<ol>";
    echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Select database: <strong>barcie_db</strong></li>";
    echo "<li>Click the <strong>SQL</strong> tab</li>";
    echo "<li>Open file: <code>database/migrations/2025_12_12_enhanced_admin_management.sql</code></li>";
    echo "<li>Copy the entire SQL content</li>";
    echo "<li>Paste it into the SQL window</li>";
    echo "<li>Click <strong>Go</strong> button</li>";
    echo "<li>Refresh this page to verify</li>";
    echo "</ol>";
    echo "</div>";
}

// File check
echo "<h2>📁 Required Files Status</h2>";
$required_files = [
    'database/migrations/2025_12_12_enhanced_admin_management.sql' => 'Migration SQL',
    'database/modules/audit_trail.php' => 'Audit Trail Module',
    'database/modules/permissions_manager.php' => 'Permissions Manager',
    'api/admin_management_enhanced.php' => 'Enhanced API',
    'api/admin_heartbeat.php' => 'Heartbeat API',
    'components/dashboard/sections/admin_management_enhanced.php' => 'Enhanced UI Component',
    'components/dashboard/sections/modals/add_admin_modal_enhanced.php' => 'Add Admin Modal',
    'assets/js/admin-management-enhanced.js' => 'Enhanced JavaScript',
    'assets/css/admin-online-status.css' => 'Online Status CSS'
];

echo "<table><thead><tr><th>File Path</th><th>Description</th><th>Status</th></tr></thead><tbody>";
foreach ($required_files as $file => $desc) {
    $full_path = __DIR__ . '/' . $file;
    $exists = file_exists($full_path);
    $status_class = $exists ? 'success' : 'error';
    $status_text = $exists ? '✅ EXISTS' : '❌ MISSING';
    
    echo "<tr>";
    echo "<td><code>$file</code></td>";
    echo "<td>$desc</td>";
    echo "<td class='$status_class'>$status_text</td>";
    echo "</tr>";
}
echo "</tbody></table>";

echo "<div class='status info'>";
echo "<p><strong>💡 Tip:</strong> After running the migration, click <a href='dashboard.php'>Go to Dashboard</a> and navigate to <strong>Manage Roles</strong> to see all the new features!</p>";
echo "</div>";

echo "<p style='margin-top:20px;'><a href='check_migration_status.php' class='btn'>🔄 Refresh Status</a></p>";

echo "</body></html>";
?>
