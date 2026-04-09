<?php
session_start();
include __DIR__ . '/database/db_connect.php';

$normalizeDir = static function (?string $path): string {
  if (!is_string($path) || $path === '') {
    return '';
  }

  $trimmed = rtrim($path, DIRECTORY_SEPARATOR);
  $real = realpath($trimmed);

  return $real !== false ? $real : $trimmed;
};

$resolveCaseInsensitivePath = static function (string $basePath, string $relativePath): string {
  $current = $basePath;
  $segments = explode(DIRECTORY_SEPARATOR, $relativePath);

  foreach ($segments as $segment) {
    if ($segment === '' || $segment === '.') {
      continue;
    }

    $direct = $current . DIRECTORY_SEPARATOR . $segment;
    if (file_exists($direct)) {
      $current = $direct;
      continue;
    }

    $entries = @scandir($current);
    if ($entries === false) {
      return '';
    }

    $matched = null;
    foreach ($entries as $entry) {
      if (strcasecmp($entry, $segment) === 0) {
        $matched = $entry;
        break;
      }
    }

    if ($matched === null) {
      return '';
    }

    $current = $current . DIRECTORY_SEPARATOR . $matched;
  }

  return $current;
};

$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
  ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
  : '';

$scriptDir = isset($_SERVER['SCRIPT_FILENAME'])
  ? dirname((string) $_SERVER['SCRIPT_FILENAME'])
  : '';

$componentRootCandidates = array_filter(array_unique([
  __DIR__ . DIRECTORY_SEPARATOR . 'Components',
  __DIR__ . DIRECTORY_SEPARATOR . 'components',
  $scriptDir !== '' ? rtrim($scriptDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Components' : '',
  $scriptDir !== '' ? rtrim($scriptDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'Components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'Components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'components' : '',
  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'Components',
  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'components',
]));

$componentRoot = '';
$fallbackComponentRoot = '';

foreach ($componentRootCandidates as $candidate) {
  $normalized = $normalizeDir($candidate);
  if ($normalized === '' || !is_dir($normalized)) {
    continue;
  }

  if ($fallbackComponentRoot === '') {
    $fallbackComponentRoot = $normalized;
  }

  if (
    is_file($normalized . DIRECTORY_SEPARATOR . 'Guest' . DIRECTORY_SEPARATOR . 'head.php') ||
    is_file($normalized . DIRECTORY_SEPARATOR . 'guest' . DIRECTORY_SEPARATOR . 'head.php')
  ) {
    $componentRoot = $normalized;
    break;
  }
}

if ($componentRoot === '') {
  $componentRoot = $fallbackComponentRoot !== '' ? $fallbackComponentRoot : (__DIR__ . DIRECTORY_SEPARATOR . 'Components');
}

$resolveComponentPath = static function (string $relativePath) use ($componentRoot, $resolveCaseInsensitivePath): string {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
  $fullPath = $componentRoot . DIRECTORY_SEPARATOR . $normalizedRelativePath;

  if (is_file($fullPath)) {
    return $fullPath;
  }

  $resolvedPath = $resolveCaseInsensitivePath($componentRoot, $normalizedRelativePath);
  if ($resolvedPath !== '' && is_file($resolvedPath)) {
    return $resolvedPath;
  }

  return '';
};

$getComponentPathOrLog = static function (string $relativePath) use ($resolveComponentPath): string {
  $path = $resolveComponentPath($relativePath);
  if ($path === '') {
    error_log('Missing component include in Guest.php: ' . $relativePath);
  }

  return $path;
};

$success = $error = "";

// Check for session messages
if (isset($_SESSION['feedback_success'])) {
  $success = $_SESSION['feedback_success'];
  unset($_SESSION['feedback_success']);
}
if (isset($_SESSION['feedback_error'])) {
  $error = $_SESSION['feedback_error'];
  unset($_SESSION['feedback_error']);
}

// Initialize feedback table if it doesn't exist
try {
  // First create the table without foreign key constraints
  $createTableQuery = "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        rating INT NOT NULL DEFAULT 5,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_rating (rating),
        INDEX idx_created_at (created_at)
    )";

  $conn->query($createTableQuery);

  // Check if rating column exists, add if missing
  $result = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
  if ($result && $result->num_rows == 0) {
    $conn->query("ALTER TABLE feedback ADD COLUMN rating INT NOT NULL DEFAULT 5 AFTER user_id");
  }

  // Add check constraint for rating if it doesn't exist
  $conn->query("ALTER TABLE feedback ADD CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5)");

} catch (Exception $e) {
  // Log error but don't stop execution
  error_log("Error initializing feedback table: " . $e->getMessage());
}

// Handle Feedback Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "feedback") {
  $message = trim($_POST['message'] ?? '');
  $rating = (int) ($_POST['rating'] ?? 0);

  if ($rating < 1 || $rating > 5) {
    $error = "Please select a star rating.";
  } else {
    try {
      // Ensure table exists before inserting
      $conn->query("CREATE TABLE IF NOT EXISTS feedback (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          rating INT NOT NULL DEFAULT 5,
          message TEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          INDEX idx_user_id (user_id),
          INDEX idx_rating (rating),
          INDEX idx_created_at (created_at)
      )");

      $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, message) VALUES (?, ?, ?)");
      $stmt->bind_param("iis", $user_id, $rating, $message);

      if ($stmt->execute()) {
        $success = "Thank you for your " . $rating . "-star feedback!";
      } else {
        $error = "Error submitting feedback. Please try again.";
      }
      $stmt->close();
    } catch (Exception $e) {
      $error = "Error submitting feedback. Please try again.";
      error_log("Feedback submission error: " . $e->getMessage());
    }
  }
}

// Default values for guest access
$username = "Guest";
$email = "";
$user_id = 0; // Default guest user ID

// Handle pencil booking conversion data
$pencil_conversion_data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert_from_pencil'])) {
  $pencil_conversion_data = [
    'pencil_id' => $_POST['pencil_id'] ?? '',
    'room_id' => $_POST['room_id'] ?? '',
    'guest_name' => $_POST['guest_name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'contact_number' => $_POST['contact_number'] ?? '',
    'checkin' => $_POST['checkin'] ?? '',
    'checkout' => $_POST['checkout'] ?? '',
    'occupants' => $_POST['occupants'] ?? '',
    'company' => $_POST['company'] ?? '',
    'company_contact' => $_POST['company_contact'] ?? ''
  ];
}

$pencil_conversion_script = '';
$pencil_conversion_script_path = $resolveComponentPath('Guest/Booking/pencil-conversion.js');
if ($pencil_conversion_script_path !== '' && is_readable($pencil_conversion_script_path)) {
  $loaded_script = file_get_contents($pencil_conversion_script_path);
  if ($loaded_script !== false) {
    $pencil_conversion_script = $loaded_script;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php if (($componentPath = $getComponentPathOrLog('Guest/head.php')) !== '') {
    include $componentPath;
  } ?>
  <script>
    // expose minimal globals used by guest-bootstrap.js if needed
    window.BARCIE_GUEST = {
      userId: <?php echo json_encode($user_id); ?>,
      pencilConversion: <?php echo json_encode($pencil_conversion_data); ?>
    };
  </script>
  <!-- Pencil Conversion Handler -->
  <?php if ($pencil_conversion_script !== ''): ?>
    <script>
      <?php echo $pencil_conversion_script; ?>
    </script>
  <?php endif; ?>
</head>

<body class="flex min-h-screen flex-col overflow-x-hidden text-gray-800 transition-colors duration-500">
  <!-- Mobile Menu Toggle -->
  <button
    class="mobile-menu-toggle fixed top-5 left-5 z-[1002] w-[50px] h-[50px] rounded-full border-none text-white text-xl flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 active:scale-95 transition-all duration-300 md:hidden"
    style="background: linear-gradient(135deg, #3498db, #2980b9); box-shadow: 0 4px 15px rgba(52,152,219,0.3);"
    onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Mobile Sidebar Overlay -->
  <div class="sidebar-overlay fixed inset-0 bg-black/50 z-[1000] hidden transition-opacity duration-300"
    onclick="closeSidebar()"></div>

  <!-- Sidebar -->
  <?php if (($componentPath = $getComponentPathOrLog('Guest/Sidebar/sidebar.php')) !== '') {
    include $componentPath;
  } ?>

  <!-- Main Content -->
  <main
    class="main-content ml-[260px] p-[30px] pb-[120px] grow min-h-[calc(100vh-80px)] relative transition-all duration-300 max-md:ml-0 max-md:px-4 max-md:pb-[120px] max-[480px]:px-2.5">
    <div class="container-fluid">
      <?php if (($componentPath = $getComponentPathOrLog('Guest/Dashboard/overview.php')) !== '') {
        include $componentPath;
      } ?>
      <?php if (($componentPath = $getComponentPathOrLog('Guest/AvailabilityCalendar.php/availability.php')) !== '') {
        include $componentPath;
      } ?>
      <?php if (($componentPath = $getComponentPathOrLog('Guest/RoomsAndFacilities.php/rooms.php')) !== '') {
        include $componentPath;
      } ?>
      <?php if (($componentPath = $getComponentPathOrLog('Guest/Booking/booking.php')) !== '') {
        include $componentPath;
      } ?>
      <?php if (($componentPath = $getComponentPathOrLog('Guest/Feedback/feedback.php')) !== '') {
        include $componentPath;
      } ?>
    </div>
  </main>

  <!-- Chatbot -->
  <?php if (($componentPath = $getComponentPathOrLog('Guest/ChatBot/chatbot.php')) !== '') {
    include $componentPath;
  } ?>

  <!-- Footer -->
  <?php if (($componentPath = $getComponentPathOrLog('Guest/footer.php')) !== '') {
    include $componentPath;
  } ?>

  <?php if (($componentPath = $getComponentPathOrLog('Popup/ConfirmPopup.php')) !== '') {
    include $componentPath;
  } ?>
  <?php if (($componentPath = $getComponentPathOrLog('Popup/ErrorPopup.php')) !== '') {
    include $componentPath;
  } ?>
  <?php if (($componentPath = $getComponentPathOrLog('Popup/LoadingPopup.php')) !== '') {
    include $componentPath;
  } ?>
  <?php if (($componentPath = $getComponentPathOrLog('Popup/SuccessPopup.php')) !== '') {
    include $componentPath;
  } ?>
</body>

</html>