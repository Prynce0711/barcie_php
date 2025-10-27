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
      <form class="attach-form" method="POST" enctype="multipart/form-data" data-item-id="<?php echo $it['id']; ?>">
        <input type="hidden" name="item_id" value="<?php echo $it['id']; ?>">
        <label>Upload image: <input type="file" name="image" accept="image/*" required></label>
        <button type="submit">Attach image</button>
        <div class="progress" style="display:none;margin-top:8px;"><progress value="0" max="100"></progress> <span class="pct">0%</span></div>
      </form>
    </div>
  <?php endforeach; endif; ?>

  <hr>
  <p>Manual option: you can also upload files directly into <code>/uploads/</code> and then use the SQL below to set the value, e.g.: <code>UPDATE items SET image='uploads/yourfile.jpg' WHERE id=123;</code></p>
</body>
<script>
// Chunked uploader for large files: sends multiple small POSTs to api/upload_chunk.php
document.addEventListener('DOMContentLoaded', function(){
  const forms = document.querySelectorAll('.attach-form');
  const serverMax = <?php echo json_encode(ini_get('upload_max_filesize')); ?>; // string like '2M'

  function parseSize(s){
    if (!s) return 0;
    s = s.toString().trim();
    const unit = s.slice(-1).toUpperCase();
    let num = parseFloat(s);
    if (unit === 'G') num *= 1024*1024*1024;
    else if (unit === 'M') num *= 1024*1024;
    else if (unit === 'K') num *= 1024;
    return Math.floor(num);
  }

  const maxChunk = Math.min(parseSize(serverMax) || (2*1024*1024), 1024*1024*1.5); // prefer 1.5MB chunks

  forms.forEach(form => {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const fileInput = form.querySelector('input[type=file]');
      if (!fileInput.files || fileInput.files.length === 0) return alert('Select a file');
      const f = fileInput.files[0];
      const itemId = form.dataset.itemId || form.querySelector('input[name=item_id]').value || '';
      const progressWrap = form.querySelector('.progress');
      const progressBar = progressWrap ? progressWrap.querySelector('progress') : null;
      const pct = progressWrap ? progressWrap.querySelector('.pct') : null;

      // If file is smaller than server max, submit classic POST via Fetch FormData to existing handler
      if (f.size <= maxChunk) {
        // fall back to normal form POST to same page to keep behavior consistent
        const fd = new FormData();
        fd.append('item_id', itemId);
        fd.append('image', f);
        fetch(window.location.href, {method:'POST', body: fd, credentials: 'same-origin'})
          .then(r => r.text())
          .then(txt => { alert('Upload completed (small file). Reload the page to see changes.'); location.reload(); })
          .catch(err => alert('Upload failed: ' + err));
        return;
      }

      // Chunked upload
      const uploadId = 'upl_' + Date.now() + '_' + Math.random().toString(36).slice(2,9);
      const totalChunks = Math.ceil(f.size / maxChunk);
      progressWrap.style.display = '';

      let current = 0;
      (function sendNext(){
        const start = current * maxChunk;
        const end = Math.min(start + maxChunk, f.size);
        const blob = f.slice(start, end);
        const fd = new FormData();
        fd.append('upload_id', uploadId);
        fd.append('filename', f.name);
        fd.append('chunk_index', current);
        fd.append('total_chunks', totalChunks);
        fd.append('item_id', itemId);
        fd.append('chunk', blob, f.name + '.part.' + current);

        fetch('/api/upload_chunk.php', {method:'POST', body: fd, credentials: 'same-origin'})
          .then(r => r.json())
          .then(json => {
            if (!json.ok) throw new Error(json.error || 'Upload error');
            const percent = Math.round(((current+1)/totalChunks)*100);
            if (progressBar) progressBar.value = percent;
            if (pct) pct.textContent = percent + '%';
            current++;
            if (current < totalChunks) sendNext();
            else {
              alert('Upload complete. Reloading to show updated image.');
              location.reload();
            }
          })
          .catch(err => { alert('Chunk upload failed: ' + err.message); });
      })();
    });
  });
});
</script>
</html>
