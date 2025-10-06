-- Chat Messages Table for Admin-Guest Communication
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    sender_type ENUM('admin', 'guest') NOT NULL,
    receiver_id INT NOT NULL,
    receiver_type ENUM('admin', 'guest') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_sender (sender_id, sender_type),
    INDEX idx_receiver (receiver_id, receiver_type),
    INDEX idx_conversation (sender_id, sender_type, receiver_id, receiver_type),
    INDEX idx_created_at (created_at),
    INDEX idx_unread (is_read, receiver_id, receiver_type)
);

-- Chat Conversations Table for easier conversation management
CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    guest_id INT NOT NULL,
    last_message_id INT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    admin_unread_count INT DEFAULT 0,
    guest_unread_count INT DEFAULT 0,
    
    UNIQUE KEY unique_conversation (admin_id, guest_id),
    FOREIGN KEY (last_message_id) REFERENCES chat_messages(id) ON DELETE SET NULL,
    INDEX idx_last_activity (last_activity),
    INDEX idx_admin_id (admin_id),
    INDEX idx_guest_id (guest_id)
);