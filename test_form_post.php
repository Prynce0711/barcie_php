<?php
// Test form POST data capture
session_start();
require_once 'database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>Add-ons Specifically:</h2>";
    echo "<pre>";
    if (isset($_POST['addons'])) {
        print_r($_POST['addons']);
    } else {
        echo "No 'addons' key in POST data!";
    }
    echo "</pre>";
    
    echo "<h2>FILES Data:</h2>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    die();
}

// Fetch one item for testing
$item = $conn->query("SELECT * FROM items LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Add-ons Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Test Add-ons Form Submission</h1>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= $item['id'] ?>">
        
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($item['name']) ?>">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Add-ons Test</label>
            <div id="addonsContainer">
                <!-- Add a test addon -->
                <div class="card p-3 mb-2">
                    <h6>Test Add-on 1</h6>
                    <input type="text" name="addons[name][]" class="form-control mb-2" placeholder="Add-on name" value="Extra Towels">
                    <input type="text" name="addons[price][]" class="form-control mb-2" placeholder="Price" value="₱500.00">
                    <select name="addons[type][]" class="form-select">
                        <option value="Per Event">Per Event</option>
                        <option value="Per Day" selected>Per Day</option>
                        <option value="Per Night">Per Night</option>
                    </select>
                </div>
                
                <div class="card p-3 mb-2">
                    <h6>Test Add-on 2</h6>
                    <input type="text" name="addons[name][]" class="form-control mb-2" placeholder="Add-on name" value="Breakfast">
                    <input type="text" name="addons[price][]" class="form-control mb-2" placeholder="Price" value="₱300.00">
                    <select name="addons[type][]" class="form-select">
                        <option value="Per Event">Per Event</option>
                        <option value="Per Day">Per Day</option>
                        <option value="Per Night" selected>Per Night</option>
                    </select>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit Test Form</button>
    </form>
</div>
</body>
</html>
