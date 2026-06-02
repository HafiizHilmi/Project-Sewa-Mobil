<?php

class LandingController {
    
    public function index() {
        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();
        $cars = [];
        if ($pdo) {
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
                    COALESCE(
                        (SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), 
                        MAX(c.image)
                    ) AS image, 
                    COUNT(b.id) AS rental_count 
                FROM cars c 
                LEFT JOIN bookings b ON b.car_id = c.id AND b.status IN ('confirmed', 'completed') 
                WHERE c.available = 1 AND c.is_type = 0 AND c.stock > 0 
                GROUP BY c.type_key 
                ORDER BY rental_count DESC, MIN(c.price_per_day) ASC 
                LIMIT 3
            ");
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require_once __DIR__ . '/views/landing.php';
    }
}