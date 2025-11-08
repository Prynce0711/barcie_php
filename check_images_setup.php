<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Images Feature - Database Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>📸 Room Images Feature - Database Check</h1>
        
        <?php
        require_once __DIR__ . '/database/db_connect.php';
        
        $checks = [];
        $overall_status = 'success';
        
        // Check 1: Database connection
        if ($conn->connect_error) {
            $checks[] = [
                'status' => 'error',
                'title' => 'Database Connection',
                'message' => 'Failed to connect: ' . $conn->connect_error
            ];
            $overall_status = 'error';
        } else {
            $checks[] = [
                'status' => 'success',
                'title' => 'Database Connection',
                'message' => 'Successfully connected to database'
            ];
        }
        
        // Check 2: Items table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'items'");
        if ($table_check && $table_check->num_rows > 0) {
            $checks[] = [
                'status' => 'success',
                'title' => 'Items Table',
                'message' => 'Items table exists'
            ];
        } else {
            $checks[] = [
                'status' => 'error',
                'title' => 'Items Table',
                'message' => 'Items table does not exist!'
            ];
            $overall_status = 'error';
        }
        
        // Check 3: Images column exists
        $column_check = $conn->query("SHOW COLUMNS FROM items LIKE 'images'");
        if ($column_check && $column_check->num_rows > 0) {
            $checks[] = [
                'status' => 'success',
                'title' => 'Images Column',
                'message' => 'Images column exists ✓'
            ];
            $migration_needed = false;
        } else {
            $checks[] = [
                'status' => 'warning',
                'title' => 'Images Column',
                'message' => 'Images column NOT found - Migration required!'
            ];
            $overall_status = $overall_status === 'error' ? 'error' : 'warning';
            $migration_needed = true;
        }
        
        // Check 4: Sample data
        $items_query = $conn->query("SELECT COUNT(*) as count FROM items");
        if ($items_query) {
            $count = $items_query->fetch_assoc()['count'];
            $checks[] = [
                'status' => 'info',
                'title' => 'Items Count',
                'message' => "Found $count room(s)/facility(s) in database"
            ];
        }
        
        // Check 5: Uploads directory
        $uploads_dir = __DIR__ . '/uploads/';
        if (is_dir($uploads_dir)) {
            if (is_writable($uploads_dir)) {
                $checks[] = [
                    'status' => 'success',
                    'title' => 'Uploads Directory',
                    'message' => 'Directory exists and is writable'
                ];
            } else {
                $checks[] = [
                    'status' => 'warning',
                    'title' => 'Uploads Directory',
                    'message' => 'Directory exists but is NOT writable'
                ];
                $overall_status = $overall_status === 'error' ? 'error' : 'warning';
            }
        } else {
            $checks[] = [
                'status' => 'warning',
                'title' => 'Uploads Directory',
                'message' => 'Directory does not exist (will be created on first upload)'
            ];
        }
        
        // Display checks
        foreach ($checks as $check) {
            echo "<div class='status {$check['status']}'>";
            echo "<strong>{$check['title']}:</strong> {$check['message']}";
            echo "</div>";
        }
        ?>
        
        <h2>🔧 Actions</h2>
        
        <?php if ($migration_needed): ?>
            <div class="status warning">
                <strong>⚠️ Migration Required</strong><br>
                You need to run the database migration to add the 'images' column.
            </div>
            <a href="database/add_images_column.php" class="btn btn-success">
                ▶️ Run Migration Now
            </a>
        <?php else: ?>
            <div class="status success">
                <strong>✅ Database Ready</strong><br>
                Your database is configured for multiple images feature!
            </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn">
            🏠 Go to Dashboard
        </a>
        
        <h2>📊 Database Schema</h2>
        
        <?php
        // Display table structure
        $columns = $conn->query("SHOW COLUMNS FROM items");
        if ($columns && $columns->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr></thead>";
            echo "<tbody>";
            while ($col = $columns->fetch_assoc()) {
                $highlight = $col['Field'] === 'images' ? ' style="background: #fff3cd;"' : '';
                echo "<tr$highlight>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
        
        <h2>📝 Sample Items</h2>
        
        <?php
        // Display sample items with images
        $sample_query = $conn->query("SELECT id, name, item_type, image, images FROM items LIMIT 5");
        if ($sample_query && $sample_query->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Single Image</th><th>Images Array</th></tr></thead>";
            echo "<tbody>";
            while ($item = $sample_query->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$item['id']}</td>";
                echo "<td>{$item['name']}</td>";
                echo "<td>" . ucfirst($item['item_type']) . "</td>";
                echo "<td>" . ($item['image'] ? "✓" : "✗") . "</td>";
                
                if (isset($item['images']) && !empty($item['images'])) {
                    $imgs = json_decode($item['images'], true);
                    $count = is_array($imgs) ? count($imgs) : 0;
                    echo "<td><span class='badge badge-success'>$count images</span></td>";
                } else {
                    echo "<td><span class='badge badge-danger'>None</span></td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='status info'>No items found in database.</div>";
        }
        
        $conn->close();
        ?>
        
        <h2>📚 Documentation</h2>
        <ul>
            <li><a href="IMAGES_QUICKSTART.md">Quick Start Guide</a> - How to use the features</li>
            <li><a href="IMAGES_UPGRADE.md">Upgrade Documentation</a> - Installation & troubleshooting</li>
            <li><a href="IMAGES_IMPLEMENTATION.md">Implementation Details</a> - Technical information</li>
        </ul>
        
    </div>
</body>
</html>
