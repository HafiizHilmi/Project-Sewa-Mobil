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

        $search = trim($_GET['search'] ?? '');

        if ($search !== '') {
            $stmt = $pdo->prepare("SELECT *, COALESCE(stock, 0) AS stock FROM cars WHERE available = 1 AND stock > 0 AND (make LIKE :search1 OR model LIKE :search2 OR category LIKE :search3 OR fuel_type LIKE :search4) ORDER BY id DESC");
            $stmt->execute([
                'search1' => "%$search%",
                'search2' => "%$search%",
                'search3' => "%$search%",
                'search4' => "%$search%",
            ]);
        } else {
            $stmt = $pdo->query("SELECT *, COALESCE(stock, 0) AS stock FROM cars WHERE available = 1 AND stock > 0 ORDER BY id DESC");
        }

        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kirim data $cars ke view HomePage.php
        require_once __DIR__ . '/views/HomePage.php';
    }
    
}