<?php

class LandingController
{
    public function index()
    {
        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();

        $cars = [];
        $fleetStats = ['types' => 0, 'units' => 0, 'categories' => 0];
        $startPrice = null;

        if ($pdo) {
            // Enam tipe paling sering disewa; satu baris = satu tipe mobil.
            $stmt = $pdo->query("
                SELECT
                    MAX(c.id) AS id,
                    c.type_key,
                    c.make,
                    c.model,
                    c.year,
                    c.category,
                    c.fuel_type,
                    c.seats,
                    c.price_per_day,
                    GROUP_CONCAT(DISTINCT c.transmission SEPARATOR ', ') AS transmission,
                    SUM(c.stock) AS stock,
                    COALESCE(
                        (SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1),
                        MAX(c.image)
                    ) AS image,
                    COUNT(b.id) AS rental_count
                FROM cars c
                LEFT JOIN bookings b ON b.car_id = c.id AND b.status IN ('confirmed', 'completed')
                WHERE c.is_type = 0 AND c.stock > 0
                GROUP BY c.type_key
                ORDER BY rental_count DESC, MIN(c.price_per_day) ASC
                LIMIT 6
            ");
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $fleetStats['types'] = (int) $pdo->query("SELECT COUNT(DISTINCT type_key) FROM cars WHERE is_type = 0 AND stock > 0")->fetchColumn();
            $fleetStats['units'] = (int) $pdo->query("SELECT COALESCE(SUM(stock), 0) FROM cars WHERE is_type = 0")->fetchColumn();
            $fleetStats['categories'] = (int) $pdo->query("SELECT COUNT(DISTINCT category) FROM cars WHERE is_type = 0 AND stock > 0")->fetchColumn();
            $startPrice = $pdo->query("SELECT MIN(price_per_day) FROM cars WHERE is_type = 0 AND stock > 0")->fetchColumn();
        }

        require_once __DIR__ . '/views/landing.php';
    }
}