<?php

class HomepageController {

    public function index() {
        // Ambil konfigurasi database kamu
        require_once __DIR__ . '/../../../include/db_config.php';
        
        $pdo = getPDO();
        if (!$pdo) {
            die("Gagal terhubung ke database.");
        }

        // Ambil semua data mobil dari database
        $stmt = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
        $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kirim data $cars ke view HomePage.php
        require_once __DIR__ . '/views/HomePage.php';
    }
    
}