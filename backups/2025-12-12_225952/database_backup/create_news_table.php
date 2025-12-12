<?php
/**
 * Database migration to create news_updates table
 * Run this file once to create the table structure
 */

require_once __DIR__ . '/db_connect.php';

// Create news_updates table
$sql = "CREATE TABLE IF NOT EXISTS news_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    author VARCHAR(100) DEFAULT 'Admin',
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_date DATE DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_published_date (published_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'news_updates' created successfully or already exists.\n";
    
    // Insert sample data
    $sample_sql = "INSERT INTO news_updates (title, content, author, status, published_date) 
                   SELECT * FROM (SELECT 
                       'Welcome to Barcie Hotel Management System' as title,
                       'We are excited to announce the launch of our new hotel management system. Experience seamless booking and exceptional service!' as content,
                       'Admin' as author,
                       'published' as status,
                       CURDATE() as published_date
                   ) AS tmp
                   WHERE NOT EXISTS (
                       SELECT id FROM news_updates WHERE title = 'Welcome to Barcie Hotel Management System'
                   ) LIMIT 1";
    
    if ($conn->query($sample_sql) === TRUE) {
        echo "✅ Sample news added successfully.\n";
    }
} else {
    echo "❌ Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
