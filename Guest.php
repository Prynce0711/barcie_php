<?php
session_start();
include __DIR__ . '/database/db_connect.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include __DIR__ . '/components/guest/head.php'; ?>
  <script>
    // expose minimal globals used by guest-bootstrap.js if needed
    window.BARCIE_GUEST = { userId: <?php echo json_encode($user_id); ?> };
  </script>
</head>
<body>
  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Mobile Sidebar Overlay -->
  <div class="sidebar-overlay" onclick="closeSidebar()"></div>

  <!-- Sidebar -->
  <?php include __DIR__ . '/components/guest/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container-fluid">
  <?php include __DIR__ . '/components/guest/sections/overview.php'; ?>
  <?php include __DIR__ . '/components/guest/sections/availability.php'; ?>
  <?php include __DIR__ . '/components/guest/sections/rooms.php'; ?>
  <?php include __DIR__ . '/components/guest/sections/booking.php'; ?>
  <?php include __DIR__ . '/components/guest/sections/feedback.php'; ?>
    </div>
  </main>

  <!-- Chatbot -->
  <?php include __DIR__ . '/components/guest/sections/chatbot.php'; ?>

  <!-- Footer -->
  <?php include __DIR__ . '/components/guest/footer.php'; ?>
</body>
</html>