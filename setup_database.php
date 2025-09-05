<?php
// Database setup script
// Run this file once to create the database and tables

$host = 'localhost';
$username = 'root'; // Change this to your database username
$password = ''; // Change this to your database password
$database = 'chat_threading_db';

try {
    // Connect to MySQL server (without selecting database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setting up Chat Threading Database...</h2>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✓ Database '$database' created successfully</p>";
    
    // Select the database
    $pdo->exec("USE `$database`");
    
    // Create tables
    $tables = [
        'cases' => "
            CREATE TABLE IF NOT EXISTS cases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                status TINYINT DEFAULT 0 COMMENT '0=open, 1=completed',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
        
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                display_name VARCHAR(100) NOT NULL,
                avatar_color VARCHAR(7) DEFAULT '#3498db',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        
        'messages' => "
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
            )",
        
        'message_status' => "
            CREATE TABLE IF NOT EXISTS message_status (
                id INT AUTO_INCREMENT PRIMARY KEY,
                message_id INT NOT NULL,
                user_id INT NOT NULL,
                status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
                status_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_message_user (message_id, user_id)
            )"
    ];
    
    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        echo "<p>✓ Table '$tableName' created successfully</p>";
    }
    
    // Insert default users
    $pdo->exec("
        INSERT INTO users (username, display_name, avatar_color) VALUES 
        ('admin', 'Administrator', '#e74c3c'),
        ('user1', 'User 1', '#3498db'),
        ('user2', 'User 2', '#2ecc71'),
        ('user3', 'User 3', '#9b59b6'),
        ('user4', 'User 4', '#f39c12')
        ON DUPLICATE KEY UPDATE username=username
    ");
    echo "<p>✓ Default users created successfully</p>";
    
    // Insert sample cases
    $pdo->exec("
        INSERT INTO cases (title, status) VALUES 
        ('Sample Case 1 - Bug Report', 0),
        ('Sample Case 2 - Feature Request', 0),
        ('Sample Case 3 - Support Ticket', 1)
        ON DUPLICATE KEY UPDATE title=title
    ");
    echo "<p>✓ Sample cases created successfully</p>";
    
    echo "<h3 style='color: green;'>✓ Database setup completed successfully!</h3>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Update database credentials in <code>config.php</code> if needed</li>";
    echo "<li>Make sure the <code>uploads/</code> directory exists and is writable</li>";
    echo "<li>Visit <a href='index.php'>index.php</a> to start using the application</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Database setup failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Please check:</strong></p>";
    echo "<ul>";
    echo "<li>MySQL server is running</li>";
    echo "<li>Database credentials are correct</li>";
    echo "<li>User has permission to create databases</li>";
    echo "</ul>";
}
?>
