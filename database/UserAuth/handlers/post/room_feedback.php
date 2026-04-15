<?php
/* ---------------------------
   SUBMIT ROOM FEEDBACK
   --------------------------- */
if ($action === 'room_feedback') {
    $room_id = (int) ($_POST['room_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

    // Google Sign-In data
    $google_id = trim($_POST['google_id'] ?? '');
    $google_email = trim($_POST['google_email'] ?? '');

    // Get guest name from Google Sign-In (hidden field populated by JS)
    $guest_name = trim($_POST['guest_name'] ?? '');

    // If Google Sign-In is used, extract name from userGoogleName
    if (empty($guest_name) && !empty($google_email)) {
        // Try to get name from the form data or use email prefix
        $guest_name = explode('@', $google_email)[0];
    }

    // If anonymous is checked, show only first 2 letters of name
    if ($is_anonymous && !empty($guest_name)) {
        $guest_name = substr($guest_name, 0, 2) . '**';
    }

    // Validation
    if ($room_id <= 0) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
        } else {
            handleResponse("Invalid room ID.", false, '../index.php?view=guest#rooms');
        }
        exit();
    }

    if ($rating < 1 || $rating > 5) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => 'Please select a star rating (1-5)']);
        } else {
            handleResponse("Please select a star rating.", false, '../index.php?view=guest#rooms');
        }
        exit();
    }

    // Require Google Sign-In
    if (empty($google_id)) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => 'Please sign in with Google to submit your review.']);
        } else {
            handleResponse('Please sign in with Google to submit your review.', false, '../index.php?view=guest#rooms');
        }
        exit();
    }

    // Insert room feedback (no approval required)
    $stmt = $conn->prepare("INSERT INTO feedback (room_id, rating, message, feedback_name, is_anonymous, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissi", $room_id, $rating, $comment, $guest_name, $is_anonymous);

    if ($stmt->execute()) {
        // Update average rating and review count in items table (include all reviews)
        $update_stmt = $conn->prepare("\n            UPDATE items \n            SET average_rating = (\n                SELECT ROUND(AVG(rating), 2) \n                FROM feedback \n                WHERE room_id = ? AND rating IS NOT NULL\n            ),\n            total_reviews = (\n                SELECT COUNT(*) \n                FROM feedback \n                WHERE room_id = ?\n            )\n            WHERE id = ?\n        ");
        $update_stmt->bind_param("iii", $room_id, $room_id, $room_id);
        $update_stmt->execute();
        $update_stmt->close();

        if ($is_ajax) {
            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your review! Your feedback helps others make better decisions.'
            ]);
        } else {
            handleResponse("Thank you for your review!", true, '../index.php?view=guest#rooms');
        }
    } else {
        error_log("Room feedback submission error: " . $stmt->error);
        if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => 'Failed to submit review. Please try again.']);
        } else {
            handleResponse("Error submitting review. Please try again.", false, '../index.php?view=guest#rooms');
        }
    }
    $stmt->close();
    exit();
}


