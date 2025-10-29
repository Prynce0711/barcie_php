<?php
// api/upload_chunk.php
// Accepts chunked uploads and assembles them into a single file in /uploads/
// Expected POST fields:
// - upload_id: client generated identifier for the upload session
// - filename: original filename
// - chunk_index: zero-based index of this chunk
// - total_chunks: total number of chunks
// - item_id: optional item id to update DB after assembly
// - chunk: file field containing this chunk

session_start();
require __DIR__ . '/../database/db_connect.php';

header('Content-Type: application/json');

// Basic admin check
if (!isset($_SESSION['admin_id'])) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'Access denied']);
  exit;
}

// Validate inputs
$upload_id = $_POST['upload_id'] ?? null;
$filename = $_POST['filename'] ?? null;
$chunk_index = isset($_POST['chunk_index']) ? intval($_POST['chunk_index']) : null;
$total_chunks = isset($_POST['total_chunks']) ? intval($_POST['total_chunks']) : null;
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : null;

if (!$upload_id || !$filename || $chunk_index === null || $total_chunks === null) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Missing parameters']);
  exit;
}

// Ensure uploads directories
$projectRoot = realpath(__DIR__ . '/..');
$uploadsDir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
$tmpDir = $uploadsDir . DIRECTORY_SEPARATOR . 'tmp';
if (!file_exists($uploadsDir)) mkdir($uploadsDir, 0755, true);
if (!file_exists($tmpDir)) mkdir($tmpDir, 0755, true);

$sessionTmp = $tmpDir . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_-]/', '_', $upload_id);
if (!file_exists($sessionTmp)) mkdir($sessionTmp, 0755, true);

// Save chunk
if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Missing chunk or upload error']);
  exit;
}

$chunkPath = $sessionTmp . DIRECTORY_SEPARATOR . 'chunk_' . $chunk_index;
if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Failed to save chunk']);
  exit;
}

// If last chunk, assemble
if ($chunk_index === $total_chunks - 1) {
  // sanitize extension
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $allowed = ['jpg','jpeg','png','gif','webp'];
  if (!in_array($ext, $allowed)) {
    // cleanup
    array_map('unlink', glob($sessionTmp . DIRECTORY_SEPARATOR . '*'));
    @rmdir($sessionTmp);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid file extension']);
    exit;
  }

  // Create final filename
  $unique = time() . '_' . uniqid() . '.' . $ext;
  $finalPath = $uploadsDir . DIRECTORY_SEPARATOR . $unique;

  // Open final file for writing
  $out = fopen($finalPath, 'wb');
  if (!$out) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to create final file']);
    exit;
  }

  // Append all chunks in order
  for ($i = 0; $i < $total_chunks; $i++) {
    $part = $sessionTmp . DIRECTORY_SEPARATOR . 'chunk_' . $i;
    if (!file_exists($part)) {
      fclose($out);
      @unlink($finalPath);
      http_response_code(500);
      echo json_encode(['ok' => false, 'error' => 'Missing chunk ' . $i]);
      exit;
    }
    $in = fopen($part, 'rb');
    stream_copy_to_stream($in, $out);
    fclose($in);
  }
  fclose($out);

  // Basic image check
  $image_info = @getimagesize($finalPath);
  if ($image_info === false) {
    @unlink($finalPath);
    array_map('unlink', glob($sessionTmp . DIRECTORY_SEPARATOR . '*'));
    @rmdir($sessionTmp);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Assembled file is not a valid image']);
    exit;
  }

  // Set permissions
  @chmod($finalPath, 0644);

  // Cleanup chunks
  array_map('unlink', glob($sessionTmp . DIRECTORY_SEPARATOR . '*'));
  @rmdir($sessionTmp);

  // If item_id provided, update DB
  $rel = 'uploads/' . $unique;
  if ($item_id) {
    $stmt = $conn->prepare('UPDATE items SET image=? WHERE id=?');
    $stmt->bind_param('si', $rel, $item_id);
    $ok = $stmt->execute();
    if (!$ok) {
      // return success for upload but DB failed
      echo json_encode(['ok' => true, 'path' => $rel, 'db' => false, 'db_error' => $stmt->error]);
      exit;
    }
  }

  echo json_encode(['ok' => true, 'path' => $rel]);
  exit;
}

// Not last chunk yet
echo json_encode(['ok' => true, 'chunk_saved' => $chunk_index]);
exit;

?>
