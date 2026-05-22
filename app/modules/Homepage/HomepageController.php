<?php

class HomepageController {

    public function index() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();
        if (!$pdo) {
            die("Gagal terhubung ke database.");
        }

        $verification_status = 'unverified';
        if (isset($_SESSION['user_id'])) {
            $stmtUser = $pdo->prepare("SELECT verification_status FROM users WHERE id = :id");
            $stmtUser->execute(['id' => $_SESSION['user_id']]);
            if ($row = $stmtUser->fetch(PDO::FETCH_ASSOC)) {
                $verification_status = $row['verification_status'];
            }
        }

        // Ambil semua data mobil dari database
        $stmt = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kirim data $cars ke view HomePage.php
        require_once __DIR__ . '/views/HomePage.php';
    }
    
}