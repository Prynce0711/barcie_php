<?php
// Simple debug page for live server
// Access control: allow localhost without auth; require HTTP Basic Auth for remote access.
// Credentials can be provided via environment variables DEBUG_USER and DEBUG_PASS or via
// an optional file 'debug_secret.php' that returns an array: ['user'=>'...','pass'=>'...'].
header('Content-Type: text/html; charset=utf-8');

// Helper: check if request is from localhost
function is_local_request() {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($remote, ['127.0.0.1', '::1', 'localhost']);
}

// Load credentials if present
$debugUser = getenv('DEBUG_USER') ?: null;
$debugPass = getenv('DEBUG_PASS') ?: null;
if (file_exists(__DIR__ . '/debug_secret.php')) {
    $creds = include __DIR__ . '/debug_secret.php';
    if (is_array($creds)) {
        $debugUser = $debugUser ?: ($creds['user'] ?? $debugUser);
        $debugPass = $debugPass ?: ($creds['pass'] ?? $debugPass);
    }
}

// If not local, enforce basic auth when credentials are set; otherwise deny.
if (!is_local_request()) {
    if ($debugUser && $debugPass) {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Debug Page"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h1>401 Unauthorized</h1><p>Authentication required to access this debug page.</p>';
            exit;
        }

        $providedUser = $_SERVER['PHP_AUTH_USER'];
        $providedPass = $_SERVER['PHP_AUTH_PW'] ?? '';

        if (!hash_equals($debugUser, $providedUser) || !hash_equals($debugPass, $providedPass)) {
            header('HTTP/1.0 403 Forbidden');
            echo '<h1>403 Forbidden</h1><p>Invalid credentials.</p>';
            exit;
        }
    } else {
        // No credentials configured and request is remote -> deny
        header('HTTP/1.0 403 Forbidden');
        echo '<h1>403 Forbidden</h1><p>Debug page is restricted. Configure DEBUG_USER/DEBUG_PASS or create debug_secret.php to enable remote access.</p>';
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>BarCIE Debug - Live Server</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #4CAF50; }
        .error { border-left: 4px solid #f44336; }
        .warning { border-left: 4px solid #ff9800; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border-radius: 4px; }
        h1 { color: #333; }
        h2 { color: #555; font-size: 18px; margin-top: 0; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; color: white; font-weight: bold; }
        .status.ok { background: #4CAF50; }
        .status.fail { background: #f44336; }
    </style>
</head>
<body>
    <h1>🔍 BarCIE Live Server Debug</h1>
    <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown'; ?></p>

    <!-- Test 1: PHP Version -->
    <div class="test <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'warning'; ?>">
        <h2>✓ PHP Version</h2>
        <p><strong>Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Status:</strong> 
            <span class="status <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'fail'; ?>">
                <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'OK' : 'TOO OLD'; ?>
            </span>
        </p>
    </div>

    <!-- Test 2: Database Connection -->
    <div class="test <?php
        try {
            include 'database/db_connect.php';
            echo ($conn && !$conn->connect_error) ? 'success' : 'error';
        } catch (Exception $e) {
            echo 'error';
        }
    ?>">
        <h2>🗄️ Database Connection</h2>
        <?php
        try {
            include 'database/db_connect.php';
            if ($conn && !$conn->connect_error) {
                echo '<p><span class="status ok">CONNECTED</span></p>';
                echo '<p><strong>Database:</strong> ' . ($conn->query("SELECT DATABASE()")->fetch_row()[0] ?? 'Unknown') . '</p>';
            } else {
                echo '<p><span class="status fail">FAILED</span></p>';
                echo '<p><strong>Error:</strong> ' . ($conn->connect_error ?? 'Unknown error') . '</p>';
            }
        } catch (Exception $e) {
            echo '<p><span class="status fail">EXCEPTION</span></p>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- Test 3: Tables Check -->
    <div class="test <?php
        try {
            if (isset($conn) && !$conn->connect_error) {
                $tables = ['items', 'bookings', 'feedback'];
                $all_exist = true;
                foreach ($tables as $table) {
                    $check = $conn->query("SHOW TABLES LIKE '$table'");
                    if (!$check || $check->num_rows == 0) {
                        $all_exist = false;
                        break;
                    }
                }
                echo $all_exist ? 'success' : 'error';
            } else {
                echo 'error';
            }
        } catch (Exception $e) {
            echo 'error';
        }
    ?>">
        <h2>📋 Database Tables</h2>
        <?php
        if (isset($conn) && !$conn->connect_error) {
            $tables = ['items', 'bookings', 'feedback'];
            foreach ($tables as $table) {
                $check = $conn->query("SHOW TABLES LIKE '$table'");
                $exists = $check && $check->num_rows > 0;
                
                echo '<p><strong>' . ucfirst($table) . ':</strong> ';
                echo '<span class="status ' . ($exists ? 'ok' : 'fail') . '">';
                echo $exists ? 'EXISTS' : 'MISSING';
                echo '</span>';
                
                if ($exists) {
                    $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
                    echo ' (' . $count . ' records)';
                }
                echo '</p>';
            }
        } else {
            echo '<p><span class="status fail">Cannot check - database not connected</span></p>';
        }
        ?>
    </div>

    <!-- Test 4: API Endpoints -->
    <div class="test">
        <h2>🌐 API Endpoints</h2>
        <p>Testing API endpoints...</p>
        
        <div id="api-tests">
            <p>⏳ Loading...</p>
        </div>
    </div>

    <script>
        // Test API endpoints via JavaScript
        async function testAPIs() {
            const container = document.getElementById('api-tests');
            container.innerHTML = '';
            
            const endpoints = [
                { name: 'Debug Connection', url: 'database/user_auth.php?action=debug_connection' },
                { name: 'Fetch Items', url: 'database/user_auth.php?action=fetch_items' },
                { name: 'Fetch Availability', url: 'database/user_auth.php?action=fetch_guest_availability' },
                { name: 'Get Receipt Number', url: 'database/user_auth.php?action=get_receipt_no' }
            ];
            
            for (const endpoint of endpoints) {
                const div = document.createElement('div');
                div.style.marginBottom = '10px';
                
                try {
                    const response = await fetch(endpoint.url);
                    const data = await response.json();
                    
                    const status = response.ok ? 'ok' : 'fail';
                    div.innerHTML = `
                        <strong>${endpoint.name}:</strong> 
                        <span class="status ${status}">${response.status} ${response.statusText}</span>
                        <br><small>Response: ${JSON.stringify(data).substring(0, 100)}...</small>
                    `;
                } catch (error) {
                    div.innerHTML = `
                        <strong>${endpoint.name}:</strong> 
                        <span class="status fail">ERROR</span>
                        <br><small>Error: ${error.message}</small>
                    `;
                }
                
                container.appendChild(div);
            }
        }
        
        testAPIs();
    </script>

    <!-- Test 5: Files Check -->
    
        <!-- Test 4.5: Current File Context -->
        <div class="test">
                <h2>📄 Current File Context</h2>
                <p>Showing the active file (<code>dashboard.php</code>) excerpt currently open in the editor.</p>
                <?php
                // Embed the dashboard.php excerpt as a safe, display-only block.
                $dashboard_excerpt = <<<'DASH'
<?= "<body>" ?>
/* Lines 208-242 omitted */
    <div class="main-content">
        <div class="container-fluid px-2" style="max-width: 100%;">
            /* Lines 244-1012 omitted */

            <section id="rooms" class="content-section">
                <!-- Rooms & Facilities Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-white">
                                <div class="text-center">
                                    <h2 class="mb-1"><i class="fas fa-building me-2"></i>Rooms & Facilities Management</h2>
                                    <p class="mb-0 opacity-75">Manage your property inventory and amenities</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-3"><i class="fas fa-filter me-2 text-primary"></i>Filter & Search</h5>
                                        <div class="btn-group w-100 item-filters" role="group" aria-label="Type filter">
                                            <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-all" value="all"
                                                checked>
                                            <label class="btn btn-outline-primary" for="filter-all">
                                                <i class="fas fa-list me-1"></i>All
                                                <span class="badge bg-primary ms-1 type-count" data-type="all">0</span>
                                            </label>

                                            <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-room"
                                                value="room">
                                            <label class="btn btn-outline-primary" for="filter-room">
                                                <i class="fas fa-bed me-1"></i>Rooms
                                                <span class="badge bg-primary ms-1 type-count" data-type="room">0</span>
                                            </label>

                                            <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-facility"
                                                value="facility">
                                            <label class="btn btn-outline-primary" for="filter-facility">
                                                <i class="fas fa-building me-1"></i>Facilities
                                                <span class="badge bg-primary ms-1 type-count" data-type="facility">0</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Search Items</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="searchItems"
                                                    placeholder="Search by name, room number, or description...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> /* Items Grid */
                <div class="row" id="items-container">
                    <?php
                    $res = $conn->query("SELECT * FROM items ORDER BY item_type, created_at DESC");
                    while ($item = $res->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6 mb-4 item-card" data-type="<?= $item['item_type'] ?>"
                            data-searchable="<?= strtolower($item['name'] . ' ' . $item['room_number'] . ' ' . $item['description']) ?>">
                            <div class="card border-0 shadow-sm h-100 hover-lift">
                                <!-- Item Image -->
                                <div class="position-relative">
                                    <?php if ($item['image'] && file_exists($item['image'])): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" class="card-img-top"
                                            style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <?php else: ?>
                                        <div class="card-img-top d-flex align-items-center justify-content-center"
                                            style="height: 200px; background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
                                            <i
                                                class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Type Badge -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span
                                            class="badge <?= $item['item_type'] === 'room' ? 'bg-primary' : ($item['item_type'] === 'facility' ? 'bg-success' : 'bg-info') ?> px-3 py-2">
                                            <i
                                                class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> me-1"></i>
                                            <?= ucfirst($item['item_type']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Item Details -->
                                <div class="card-body d-flex flex-column">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-2"><?= htmlspecialchars($item['name']) ?></h5>

                                        <?php if ($item['room_number']): ?>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-door-open me-1"></i>Room #<?= htmlspecialchars($item['room_number']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <p class="card-text text-muted small mb-3"><?= htmlspecialchars($item['description']) ?></p>

                                        <div class="row text-center mb-3">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h6 class="text-primary mb-1">₱<?= number_format($item['price']) ?></h6>
                                                    <small class="text-muted"><?= $item['item_type'] === 'room' ? 'per night' : 'per day' ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h6 class="text-success mb-1"><?= $item['capacity'] ?></h6>
                                                <small class="text-muted"><?= $item['item_type'] === 'room' ? 'guests' : 'people' ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary flex-fill edit-toggle-btn"
                                            data-item-id="<?= $item['id'] ?>">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?= $item['id'] ?>">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>

                                    <!-- Hidden Edit Form -->
                                    <div class="edit-form-container mt-3" id="editForm<?= $item['id'] ?>" style="display: none;">
                                        <form method="POST" enctype="multipart/form-data" class="border-top pt-3">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="old_image" value="<?= $item['image'] ?>">

                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" name="name"
                                                        value="<?= htmlspecialchars($item['name']) ?>" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Type</label>
                                                    <select name="item_type" class="form-select">
                                                        <option value="room" <?= $item['item_type'] == 'room' ? 'selected' : '' ?>>Room</option>
                                                        <option value="facility" <?= $item['item_type'] == 'facility' ? 'selected' : '' ?>>Facility
                                                        </option>
                                                        <option value="amenities" <?= $item['item_type'] == 'amenities' ? 'selected' : '' ?>>Amenities
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Room Number</label>
                                                    <input type="text" class="form-control" name="room_number"
                                                        value="<?= htmlspecialchars($item['room_number']) ?>">
                                                </div>

                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description"
                                                        rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Capacity</label>
                                                    <input type="number" class="form-control" name="capacity" value="<?= $item['capacity'] ?>"
                                                        required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Price (₱)</label>
                                                    <input type="number" class="form-control" name="price" value="<?= $item['price'] ?>" required>
                                                </div>

                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Change Image</label>
                                                    <input type="file" class="form-control" name="image" accept="image/*">
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary flex-fill">
                                                    <i class="fas fa-save me-1"></i>Update
                                                </button>
                                                <button type="button" class="btn btn-secondary edit-cancel-btn" data-item-id="<?= $item['id'] ?>">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Confirmation Modal -->
                        <div class="modal fade" id="deleteModal<?= $item['id'] ?>" data-bs-backdrop="false" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete <strong><?= htmlspecialchars($item['name']) ?></strong>?</p>
                                        <p class="text-muted small">This action cannot be undone.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>


                <!-- Add Item Modal -->
                <div class="modal fade" id="addItemModal" data-bs-backdrop="false" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-plus me-2"></i>Add New Room / Facility / Amenities
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="add_item" value="1">

                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Type <span class="text-danger">*</span></label>
                                            <select name="item_type" class="form-select" required>
                                                <option value="">Select Type</option>
                                                <option value="room">Room</option>
                                                <option value="facility">Facility</option>

                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Room Number</label>
                                            <input type="text" class="form-control" name="room_number" placeholder="Optional">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3"
                                                placeholder="Brief description of the room or facility"></textarea>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="capacity" min="1" required>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="price" min="0" step="1" required>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label">Image</label>
                                            <input type="file" class="form-control" name="image" accept="image/*">
                                            <div class="form-text">Optional: Upload an image for this room or facility</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus me-1"></i>Add Item
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </section>
/* Lines 1311-1928 omitted */
DASH;

                echo '<pre style="max-height:300px; overflow:auto; background:#fafafa; padding:12px; border-radius:6px;">' . htmlspecialchars($dashboard_excerpt) . '</pre>';
                ?>
        </div>
    <div class="test">
        <h2>📁 Critical Files</h2>
        <?php
        $files = [
            'database/db_connect.php' => 'Database Config',
            'database/user_auth.php' => 'API Handler',
            'assets/js/guest-bootstrap.js' => 'Guest JS',
            'Guest.php' => 'Guest Portal'
        ];
        
        foreach ($files as $file => $desc) {
            $exists = file_exists($file);
            echo '<p><strong>' . $desc . ':</strong> ';
            echo '<span class="status ' . ($exists ? 'ok' : 'fail') . '">';
            echo $exists ? 'FOUND' : 'MISSING';
            echo '</span>';
            echo ' <small>' . $file . '</small>';
            echo '</p>';
        }
        ?>
    </div>

    <!-- Test 6: PHP Extensions -->
    <div class="test">
        <h2>🔌 PHP Extensions</h2>
        <?php
        $extensions = ['mysqli', 'json', 'mbstring', 'curl'];
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            echo '<p><strong>' . $ext . ':</strong> ';
            echo '<span class="status ' . ($loaded ? 'ok' : 'fail') . '">';
            echo $loaded ? 'LOADED' : 'MISSING';
            echo '</span></p>';
        }
        ?>
    </div>

    <?php
    if (isset($conn) && !$conn->connect_error) {
        $conn->close();
    }
    ?>
</body>
</html>
