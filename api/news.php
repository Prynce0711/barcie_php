<?php
/**
 * API endpoint for News & Updates CRUD operations
 * Handles: Create, Read, Update, Delete news items
 */

session_start();
require_once __DIR__ . '/../database/db_connect.php';

// Set JSON header
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Public endpoint - no authentication required
if ($action === 'fetch_published') {
    fetchPublishedNews($conn);
    exit;
}

// Check if admin is logged in for protected endpoints
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

switch ($action) {
    case 'fetch':
        fetchNews($conn);
        break;
    
    case 'fetch_single':
        fetchSingleNews($conn);
        break;
    
    case 'create':
        createNews($conn);
        break;
    
    case 'update':
        updateNews($conn);
        break;
    
    case 'delete':
        deleteNews($conn);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();

/**
 * Fetch all news items (admin)
 */
function fetchNews($conn) {
    $status = $_GET['status'] ?? 'all';
    
    $sql = "SELECT * FROM news_updates";
    if ($status !== 'all') {
        $sql .= " WHERE status = ?";
    }
    $sql .= " ORDER BY published_date DESC, created_at DESC";
    
    if ($status !== 'all') {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    $news = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $news]);
}

/**
 * Fetch single news item
 */
function fetchSingleNews($conn) {
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM news_updates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $news = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $news]);
    } else {
        echo json_encode(['success' => false, 'message' => 'News not found']);
    }
}

/**
 * Fetch published news (public - for landing page)
 */
function fetchPublishedNews($conn) {
    $limit = $_GET['limit'] ?? 6;
    
    $stmt = $conn->prepare("SELECT * FROM news_updates WHERE status = 'published' 
                           ORDER BY published_date DESC, created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $news = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $news]);
}

/**
 * Create new news item
 */
function createNews($conn) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'Admin');
    $status = $_POST['status'] ?? 'published';
    $published_date = $_POST['published_date'] ?? date('Y-m-d');
    
    // Validate required fields
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        return;
    }
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_path = handleImageUpload($_FILES['image']);
        if ($image_path === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            return;
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO news_updates (title, content, image_path, author, status, published_date) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $content, $image_path, $author, $status, $published_date);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'News created successfully',
            'id' => $conn->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create news']);
    }
}

/**
 * Update existing news item
 */
function updateNews($conn) {
    $id = $_POST['news_id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'Admin');
    $status = $_POST['status'] ?? 'published';
    $published_date = $_POST['published_date'] ?? date('Y-m-d');
    
    // Validate
    if (empty($id) || empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    // Get current image path
    $current_image = null;
    $stmt = $conn->prepare("SELECT image_path FROM news_updates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $current_image = $row['image_path'];
    }
    
    // Handle image upload
    $image_path = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $new_image = handleImageUpload($_FILES['image']);
        if ($new_image !== false) {
            // Delete old image if exists
            if ($current_image && file_exists(__DIR__ . '/../' . $current_image)) {
                unlink(__DIR__ . '/../' . $current_image);
            }
            $image_path = $new_image;
        }
    }
    
    // Check if image should be removed
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($current_image && file_exists(__DIR__ . '/../' . $current_image)) {
            unlink(__DIR__ . '/../' . $current_image);
        }
        $image_path = null;
    }
    
    // Update database
    $stmt = $conn->prepare("UPDATE news_updates SET title = ?, content = ?, image_path = ?, 
                           author = ?, status = ?, published_date = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $title, $content, $image_path, $author, $status, $published_date, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'News updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update news']);
    }
}

/**
 * Delete news item
 */
function deleteNews($conn) {
    $id = $_POST['id'] ?? $_GET['id'] ?? 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid news ID']);
        return;
    }
    
    // Get image path before deleting
    $stmt = $conn->prepare("SELECT image_path FROM news_updates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $image_path = $row['image_path'];
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM news_updates WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete image file if exists
            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                unlink(__DIR__ . '/../' . $image_path);
            }
            echo json_encode(['success' => true, 'message' => 'News deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete news']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'News not found']);
    }
}

/**
 * Handle image upload
 */
function handleImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/news/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'news_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/news/' . $filename;
    }
    
    return false;
}
?>
