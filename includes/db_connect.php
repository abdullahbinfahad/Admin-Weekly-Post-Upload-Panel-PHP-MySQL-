<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'abdullah_public_note');
define('DB_USER', 'abdullah_wp63');
define('DB_PASS', 'QazWsxEdc@@11');

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Create PDO connection
            $this->connection = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            // Create tables if not exists
            $this->createTables();
            
        } catch (PDOException $e) {
            // Log error securely
            error_log("Database connection failed: " . $e->getMessage());
            
            // User-friendly message
            header("HTTP/1.1 500 Internal Server Error");
            exit("We're experiencing technical difficulties. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
    
    private function createTables() {
        // Create posts table if not exists
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                category VARCHAR(100) NOT NULL,
                content_type ENUM('text', 'presentation', 'excel', 'video', 'homework') NOT NULL,
                content TEXT,
                file_path VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                week_number INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Create subscribers table if not exists
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('active', 'unsubscribed') DEFAULT 'active'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Create admin users table if not exists
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'editor') DEFAULT 'editor',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Create default admin user if not exists
        $this->createDefaultAdmin();
    }
    
    private function createDefaultAdmin() {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM admin_users");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $username = 'admin';
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            
            $stmt = $this->connection->prepare("
                INSERT INTO admin_users (username, password_hash, role) 
                VALUES (?, ?, 'admin')
            ");
            $stmt->execute([$username, $password]);
        }
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Test connection
try {
    $db = Database::getInstance();
    // Uncomment for debugging:
    // echo "Database connection successful!";
} catch (Exception $e) {
    die("Critical database error: " . $e->getMessage());
}
?>