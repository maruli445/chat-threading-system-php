<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'chat_threading_db');
define('DB_USER', 'root'); // Change this to your database username
define('DB_PASS', ''); // Change this to your database password
define('DB_CHARSET', 'utf8mb4');

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Helper functions
function getDB() {
    return Database::getInstance()->getConnection();
}

// Session management for current user
function getCurrentUser() {
    if (!isset($_SESSION['current_user_id'])) {
        // Default to user 1 if no user is set
        $_SESSION['current_user_id'] = 1;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['current_user_id']]);
    return $stmt->fetch();
}

function setCurrentUser($userId) {
    $_SESSION['current_user_id'] = $userId;
}
?>
