<?php
/* ---------------------------
   SUBMIT FEEDBACK
   --------------------------- */
if ($action === 'submit_feedback' || $action === 'feedback') {
    // Get feedback data including name and email
    $message = trim($_POST['message'] ?? '');
    $rating = (int) ($_POST['rating'] ?? 0);
    $feedback_name = trim($_POST['feedback_name'] ?? '');
    $feedback_email = trim($_POST['feedback_email'] ?? '');

    function handleResponse($message, $success = true, $redirect = null)
    {

        if ($redirect) {
            echo "<script>
                alert('$message');
                window.location.href='$redirect';
              </script>";
            exit;
        }

        echo json_encode([
            "success" => $success,
            "message" => $message
        ]);
        exit;
    }

    if ($rating < 1 || $rating > 5) {
        handleResponse("Please select a star rating.", false, '../index.php?view=guest#feedback');
    }

    // Create feedback table if it doesn't exist
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rating INT NOT NULL DEFAULT 5,
            message TEXT,
            feedback_name VARCHAR(255) NULL,
            feedback_email VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_rating (rating),
            INDEX idx_created_at (created_at)
        )");

        // Add name and email columns if they don't exist
        $conn->query("ALTER TABLE feedback ADD COLUMN IF NOT EXISTS feedback_name VARCHAR(255) NULL");
        $conn->query("ALTER TABLE feedback ADD COLUMN IF NOT EXISTS feedback_email VARCHAR(255) NULL");
        $conn->query("ALTER TABLE feedback ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        // Check if rating column exists, add if missing
        $result = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
        if ($result && $result->num_rows == 0) {
            $conn->query("ALTER TABLE feedback ADD COLUMN rating INT NOT NULL DEFAULT 5");
        }

        // Remove user_id column if it exists
        $user_id_exists = $conn->query("SHOW COLUMNS FROM feedback LIKE 'user_id'");
        if ($user_id_exists && $user_id_exists->num_rows > 0) {
            $conn->query("ALTER TABLE feedback DROP COLUMN user_id");
        }

        // Try to add check constraint (ignore if already exists)
        try {
            $conn->query("ALTER TABLE feedback ADD CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5)");
        } catch (Exception $constraintError) {
            // Ignore constraint errors - it might already exist
        }
    } catch (Exception $e) {
        error_log("Error creating/updating feedback table: " . $e->getMessage());
    }

    $stmt = $conn->prepare("INSERT INTO feedback (rating, message, feedback_name, feedback_email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $rating, $message, $feedback_name, $feedback_email);

    if ($stmt->execute()) {
        handleResponse("Thank you for your " . $rating . "-star feedback" . ($feedback_name ? ", " . htmlspecialchars($feedback_name) : "") . "!", true, '../index.php?view=guest#feedback');
    } else {
        handleResponse("Error submitting feedback. Please try again.", false, '../index.php?view=guest#feedback');
        error_log("Feedback submission error: " . $stmt->error);
    }
    $stmt->close();
}


