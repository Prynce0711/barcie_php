<?php
// tools/fix_missing_images.php
// Admin utility: list items with NULL/empty image and allow uploading an image to associate with the item.
// Usage: place in project and visit as an admin while logged in. It will move uploaded file to uploads/ and update items.image

session_start();
require __DIR__ . '/../database/db_connect.php';

// simple admin check
if (!isset($_SESSION['admin_id'])) {
  echo "<p>Access denied. Please login as admin.</p>";
  exit;
}

function redirect_back($msg = null) {
  if ($msg) $_SESSION['fix_msg'] = $msg;
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
  $item_id = intval($_POST['item_id']);
  if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    redirect_back('No file uploaded or upload error.');
  }

  $file = $_FILES['image'];
  // Basic checks
  $allowed = ['jpg','jpeg','png','gif','webp'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed)) redirect_back('Invalid file extension.');

  // Ensure uploads dir exists
  $target_dir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
  if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
  if (!is_writable($target_dir)) redirect_back('Uploads directory is not writable.');

  // Move file
  $unique = time() . '_' . uniqid() . '.' . $ext;
  $target_path = $target_dir . $unique;
  if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    redirect_back('Failed to move uploaded file.');
  }
  chmod($target_path, 0644);

  // Update DB
  $rel = 'uploads/' . $unique;
  $stmt = $conn->prepare('UPDATE items SET image=? WHERE id=?');
  $stmt->bind_param('si', $rel, $item_id);
  if ($stmt->execute()) {
    redirect_back('Image attached and DB updated for item id ' . $item_id);
  } else {
    // remove uploaded file if DB update failed
    @unlink($target_path);
    redirect_back('DB update failed: ' . $stmt->error);
  }
}

// UI
$msg = $_SESSION['fix_msg'] ?? null;
unset($_SESSION['fix_msg']);

$items_res = $conn->query("SELECT id, name, item_type, room_number, image FROM items WHERE image IS NULL OR image = '' ORDER BY id ASC");
$items = [];
if ($items_res) while ($r = $items_res->fetch_assoc()) $items[] = $r;

?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Fix Missing Item Images</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;} .item{border:1px solid #ddd;padding:12px;margin:12px 0;} .msg{padding:10px;background:#efe;color:#060;border-radius:4px;margin-bottom:12px;} </style>
</head>
<body>
  <h1>Fix Missing Item Images</h1>
  <?php if ($msg): ?>
    <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <p>This page lists items with empty or NULL image values. Use the form to upload an image to attach to the item.</p>

  <?php if (empty($items)): ?>
    <p>No items found with missing images.</p>
  <?php else: foreach ($items as $it): ?>
    <div class="item">
      <h3><?php echo htmlspecialchars($it['name']); ?> (<?php echo htmlspecialchars($it['item_type']); ?>)</h3>
      <p>ID: <?php echo $it['id']; ?> | Room number: <?php echo htmlspecialchars($it['room_number']); ?></p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="item_id" value="<?php echo $it['id']; ?>">
        <label>Upload image: <input type="file" name="image" accept="image/*" required></label>
        <button type="submit">Attach image</button>
      </form>
    </div>
  <?php endforeach; endif; ?>

  <hr>
  <p>Manual option: you can also upload files directly into <code>/uploads/</code> and then use the SQL below to set the value, e.g.: <code>UPDATE items SET image='uploads/yourfile.jpg' WHERE id=123;</code></p>
</body>
</html>
