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
            $stmt = $pdo->prepare("SELECT MAX(id) AS id, type_key, make, model, year, category, fuel_type, engine_capacity, seats, price_per_day, MAX(image) AS image, SUM(stock) AS stock, GROUP_CONCAT(DISTINCT transmission SEPARATOR ', ') AS transmission FROM cars WHERE available = 1 AND is_type = 0 AND stock > 0 AND (make LIKE :search1 OR model LIKE :search2 OR category LIKE :search3 OR fuel_type LIKE :search4 OR year LIKE :search5) GROUP BY type_key ORDER BY MAX(id) DESC");
            $stmt->execute([
                'search1' => "%$search%",
                'search2' => "%$search%",
                'search3' => "%$search%",
                'search4' => "%$search%",
                'search5' => "%$search%",
            ]);
        } else {
            $stmt = $pdo->query("SELECT MAX(id) AS id, type_key, make, model, year, category, fuel_type, engine_capacity, seats, price_per_day, MAX(image) AS image, SUM(stock) AS stock, GROUP_CONCAT(DISTINCT transmission SEPARATOR ', ') AS transmission FROM cars WHERE available = 1 AND is_type = 0 AND stock > 0 GROUP BY type_key ORDER BY MAX(id) DESC");
        }

        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/views/HomePage.php';
    }
    
}