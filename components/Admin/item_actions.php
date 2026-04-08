<?php
try {
  $chk = $conn->query("SHOW COLUMNS FROM items LIKE 'addons'");
  if (!$chk || $chk->num_rows === 0) {
    $conn->query("ALTER TABLE items ADD COLUMN addons TEXT NULL");
  }
} catch (Exception $e) {

  error_log('Migration check failed: ' . $e->getMessage());
}


function log_addons_debug($msg) {
  $logDir = __DIR__ . '/../../../logs';
  if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
  }
  $file = $logDir . '/addons_debug.log';
  $time = date('Y-m-d H:i:s');
  $entry = "[{$time}] " . $msg . "\n";
  @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  $projectRoot = dirname(__DIR__, 2);
  $uploadDir = $projectRoot . '/uploads/items';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  $normalizeImagePath = function($path) {
    $path = str_replace('\\', '/', trim((string)$path));
    return ltrim($path, '/');
  };

  $resolveImageFullPath = function($storedPath) use ($projectRoot) {
    $normalized = str_replace('\\', '/', trim((string)$storedPath));
    $normalized = ltrim($normalized, '/');
    $primary = $projectRoot . '/' . $normalized;
    if (file_exists($primary)) {
      return $primary;
    }

    // Legacy fallback for previously stored absolute root uploads.
    $legacy = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\') . '/' . $normalized;
    if ($legacy && file_exists($legacy)) {
      return $legacy;
    }
    return $primary;
  };


  $allowedExt = ['jpg','jpeg','png','gif','webp'];
  $allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];
  $maxSize = 5 * 1024 * 1024; 

  $validateAndMove = function($tmpPath, $origName) use ($uploadDir, $allowedExt, $allowedMime, $maxSize) {
    if (!is_uploaded_file($tmpPath)) return false;
    if (filesize($tmpPath) > $maxSize) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMime)) return false;
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) return false;
    $newName = uniqid('it_') . '.' . $ext;
    $dest = $uploadDir . '/' . $newName;
    if (move_uploaded_file($tmpPath, $dest)) {
      return 'uploads/items/' . $newName;
    }
    return false;
  };

  if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $item_type = $conn->real_escape_string($_POST['item_type'] ?? 'room');
    $room_number = $conn->real_escape_string(trim($_POST['room_number'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $capacity = intval($_POST['capacity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

  
    $addonsList = [];
    $postAddonsStr = isset($_POST['addons']) ? print_r($_POST['addons'], true) : 'NOT SET';
    error_log('=== ADDONS DEBUG ===');
    error_log('POST addons isset: ' . (isset($_POST['addons']) ? 'YES' : 'NO'));
    error_log('POST addons data: ' . $postAddonsStr);
    log_addons_debug('POST addons isset: ' . (isset($_POST['addons']) ? 'YES' : 'NO'));
    log_addons_debug('POST addons data: ' . $postAddonsStr);

    if (!empty($_POST['addons']['name']) && is_array($_POST['addons']['name'])) {
      foreach ($_POST['addons']['name'] as $idx => $aname) {
        $aname = trim($aname);
        $aprice = isset($_POST['addons']['price'][$idx]) ? trim($_POST['addons']['price'][$idx]) : '';
        $aprice = str_replace([',','₱','$',' '], '', $aprice);
        $aprice = is_numeric($aprice) ? (float)$aprice : '';
        $atype = $_POST['addons']['type'][$idx] ?? 'Per Event';
        if ($aname !== '') $addonsList[] = ['name' => $aname, 'price' => $aprice, 'type' => $atype];
      }
    }
    error_log('Processed addonsList: ' . print_r($addonsList, true));
    log_addons_debug('Processed addonsList: ' . print_r($addonsList, true));
    error_log('===================');

    $existing = [];
    $q = $conn->query("SELECT images, image FROM items WHERE id = " . $id);
    if ($q && $q->num_rows) {
      $row = $q->fetch_assoc();
      if (!empty($row['images'])) {
        $decoded = json_decode($row['images'], true);
        if (is_array($decoded)) $existing = array_map($normalizeImagePath, $decoded);
      } elseif (!empty($row['image'])) {
        $existing = [$normalizeImagePath($row['image'])];
      }
    }

    if (empty($existing) && !empty($_POST['existing_images'])) {
      $decoded = json_decode($_POST['existing_images'], true);
      if (is_array($decoded)) $existing = array_map($normalizeImagePath, $decoded);
    }

    
    $removed = [];
    if (!empty($_POST['removed_images'])) {
      $parts = array_filter(array_map('trim', explode(',', $_POST['removed_images'])));
      $removed = array_map($normalizeImagePath, $parts);
    }

   
    if (!empty($_FILES['replace_images']) && isset($_FILES['replace_images']['name'][$id])) {
      foreach ($_FILES['replace_images']['name'][$id] as $idx => $origName) {
        if (empty($origName)) continue;
        $tmp = $_FILES['replace_images']['tmp_name'][$id][$idx];
        $moved = $validateAndMove($tmp, $origName);
        if ($moved) {
          if (isset($existing[$idx])) {
            $origPath = $normalizeImagePath($existing[$idx]);
            $existing[$idx] = $moved;
            if ($origPath && !in_array($origPath, $removed)) $removed[] = $origPath;
          } else {
            $existing[] = $moved;
          }
        }
      }
    }

  
    if (!empty($removed)) {
      foreach ($removed as $r) {
        $r = $normalizeImagePath($r);
        $existing = array_values(array_filter($existing, function($v) use ($r, $normalizeImagePath){ return $normalizeImagePath($v) !== $r; }));
        $full = $resolveImageFullPath($r);
        if (file_exists($full)) @unlink($full);
      }
    }

  
    if (!empty($_FILES['images']) && !empty($_FILES['images']['name'])) {
      foreach ($_FILES['images']['name'] as $i => $iname) {
        if (empty($iname)) continue;
        $tmp = $_FILES['images']['tmp_name'][$i];
        $moved = $validateAndMove($tmp, $iname);
        if ($moved) $existing[] = $moved;
      }
    }

    $existing = array_values(array_map($normalizeImagePath, $existing));
    $primaryImage = !empty($existing) ? $existing[0] : '';

    $addonsJson = $conn->real_escape_string(json_encode($addonsList));
    $imagesJson = $conn->real_escape_string(json_encode($existing));
    $primaryImageEsc = $conn->real_escape_string($primaryImage);

    $sql = "UPDATE items SET name = '$name', item_type = '$item_type', room_number = '$room_number', description = '$description', capacity = $capacity, price = $price, addons = '$addonsJson', image = '$primaryImageEsc', images = '$imagesJson' WHERE id = $id";
    if ($conn->query($sql) === false) error_log('Items update failed: ' . $conn->error);

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;

  } elseif ($action === 'create') {
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $item_type = $conn->real_escape_string($_POST['item_type'] ?? 'room');
    $room_number = $conn->real_escape_string(trim($_POST['room_number'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $capacity = intval($_POST['capacity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    $addonsList = [];
    if (!empty($_POST['addons']['name']) && is_array($_POST['addons']['name'])) {
      foreach ($_POST['addons']['name'] as $idx => $aname) {
        $aname = trim($aname);
        $aprice = isset($_POST['addons']['price'][$idx]) ? trim($_POST['addons']['price'][$idx]) : '';
        $aprice = str_replace([',','₱','$',' '], '', $aprice);
        $aprice = is_numeric($aprice) ? (float)$aprice : '';
        $atype = $_POST['addons']['type'][$idx] ?? 'Per Event';
        if ($aname !== '') $addonsList[] = ['name' => $aname, 'price' => $aprice, 'type' => $atype];
      }
    }

    $existing = [];
    if (!empty($_FILES['images']) && !empty($_FILES['images']['name'])) {
      foreach ($_FILES['images']['name'] as $i => $iname) {
        if (empty($iname)) continue;
        $tmp = $_FILES['images']['tmp_name'][$i];
        $moved = $validateAndMove($tmp, $iname);
        if ($moved) $existing[] = $moved;
      }
    }

    $existing = array_values(array_map($normalizeImagePath, $existing));
    $primaryImage = !empty($existing) ? $existing[0] : '';

    $addonsJson = $conn->real_escape_string(json_encode($addonsList));
    $imagesJson = $conn->real_escape_string(json_encode($existing));
    $primaryImageEsc = $conn->real_escape_string($primaryImage);
    $sql = "INSERT INTO items (name, item_type, room_number, description, capacity, price, addons, image, images, created_at) VALUES ('$name', '$item_type', '$room_number', '$description', $capacity, $price, '$addonsJson', '$primaryImageEsc', '$imagesJson', NOW())";
    if ($conn->query($sql) === false) error_log('Items insert failed: ' . $conn->error);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;

  } elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
      $q = $conn->query("SELECT images, image FROM items WHERE id = " . $id);
      if ($q && $q->num_rows) {
        $row = $q->fetch_assoc();
        $toDelete = [];
        if (!empty($row['images'])) {
          $decoded = json_decode($row['images'], true);
          if (is_array($decoded)) $toDelete = array_merge($toDelete, $decoded);
        } elseif (!empty($row['image'])) {
          $toDelete[] = $normalizeImagePath($row['image']);
        }
        foreach ($toDelete as $p) {
          $full = $resolveImageFullPath($p);
          if (file_exists($full)) @unlink($full); 
        }
      }
      $conn->query("DELETE FROM items WHERE id = $id");
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
  }
}

// If reached here without exit, something went wrong - redirect back