<?php

class HomepageController
{

    public function index()
    {
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

        // Satu baris = satu tipe mobil (stok unit digabung per type_key).
        $baseSelect = "SELECT MAX(c.id) AS id, c.type_key, c.make, c.model, c.year, c.category,
                c.fuel_type, c.engine_capacity, c.seats, c.price_per_day,
                COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), MAX(c.image)) AS image,
                SUM(c.stock) AS stock,
                GROUP_CONCAT(DISTINCT c.transmission SEPARATOR ', ') AS transmission
            FROM cars c
            WHERE c.is_type = 0 AND c.stock > 0";

        if ($search !== '') {
            $stmt = $pdo->prepare($baseSelect . " AND (c.make LIKE :search1 OR c.model LIKE :search2 OR c.category LIKE :search3 OR c.fuel_type LIKE :search4 OR c.year LIKE :search5)
                GROUP BY c.type_key ORDER BY MIN(c.price_per_day) ASC");
            $stmt->execute([
                'search1' => "%$search%",
                'search2' => "%$search%",
                'search3' => "%$search%",
                'search4' => "%$search%",
                'search5' => "%$search%",
            ]);
        } else {
            $stmt = $pdo->query($baseSelect . " GROUP BY c.type_key ORDER BY MIN(c.price_per_day) ASC");
        }

        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Data pendukung filter
        $categories = $pdo->query("SELECT category, COUNT(DISTINCT type_key) AS total FROM cars WHERE is_type = 0 AND stock > 0 GROUP BY category ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
        $maxPrice = (int) $pdo->query("SELECT COALESCE(MAX(price_per_day), 1000000) FROM cars WHERE is_type = 0 AND stock > 0")->fetchColumn();
        $minPrice = (int) $pdo->query("SELECT COALESCE(MIN(price_per_day), 100000) FROM cars WHERE is_type = 0 AND stock > 0")->fetchColumn();
        $totalTypes = (int) $pdo->query("SELECT COUNT(DISTINCT type_key) FROM cars WHERE is_type = 0 AND stock > 0")->fetchColumn();

        require_once __DIR__ . '/views/HomePage.php';
    }

}