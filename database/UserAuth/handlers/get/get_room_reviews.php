<?php
/* ---------------------------
   GET ROOM REVIEWS
   --------------------------- */
if (isset($_GET['action']) && $_GET['action'] === 'get_room_reviews') {
    $room_id = (int) ($_GET['room_id'] ?? 0);

    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
        exit();
    }

    $stmt = $conn->prepare("
        SELECT 
            id,
            rating,
            message as comment,
            feedback_name as guest_name,
            is_anonymous,
            created_at
        FROM feedback 
        WHERE room_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    echo json_encode(['success' => true, 'reviews' => $reviews]);
    $stmt->close();
    exit();
}


