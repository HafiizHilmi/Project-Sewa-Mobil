<?php

class Database {
    private $conn;

    public function __construct() {
        // Parse .env file
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $host = $env['DB_HOST'] ?? 'localhost';
            $dbname = $env['DB_NAME'] ?? 'sewamobil';
            $user = $env['DB_USER'] ?? 'root';
            $pass = $env['DB_PASS'] ?? '';
            $port = $env['DB_PORT'] ?? '3306';
        } else {
            $host = 'localhost';
            $dbname = 'sewamobil';
            $user = 'root';
            $pass = '';
            $port = '3306';
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch(PDOException $e) {
            // Dalam mode production, log error alih-alih menampilkannya
            error_log("Connection failed: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
