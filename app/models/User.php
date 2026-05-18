<?php

require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Cek apakah email sudah terdaftar
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Register User
    public function register($name, $email, $phone, $password) {
        // Hash password untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Default role: user (bisa juga 'admin' nantinya)
        $role = 'user';

        $query = "INSERT INTO " . $this->table . " (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login User
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                return $user; // Return data user jika sukses
            }
        }
        return false; // Gagal login
    }
}
?>
