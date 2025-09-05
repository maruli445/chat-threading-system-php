-- Database setup for Chat Threading System
-- Create database (run this first)
CREATE DATABASE IF NOT EXISTS chat_threading_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chat_threading_db;

-- Cases table
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    status TINYINT DEFAULT 0 COMMENT '0=open, 1=completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table (simple user system)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    avatar_color VARCHAR(7) DEFAULT '#3498db',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messages table with threading support
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_message_id INT NULL COMMENT 'NULL for root messages, ID for replies',
    content TEXT NOT NULL,
    image_path VARCHAR(500) NULL,
    message_type ENUM('text', 'image', 'system') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_case_id (case_id),
    INDEX idx_parent_message (parent_message_id),
    INDEX idx_created_at (created_at)
);

-- Message status table (for read receipts, etc.)
CREATE TABLE IF NOT EXISTS message_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
    status_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_message_user (message_id, user_id)
);

-- Insert default user
INSERT INTO users (username, display_name, avatar_color) VALUES 
('admin', 'Administrator', '#e74c3c'),
('user1', 'User 1', '#3498db'),
('user2', 'User 2', '#2ecc71')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample cases (optional)
INSERT INTO cases (title, status) VALUES 
('Sample Case 1', 0),
('Sample Case 2', 0),
('Completed Case', 1)
ON DUPLICATE KEY UPDATE title=title;
