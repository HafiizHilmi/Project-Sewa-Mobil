<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../inc/guard.php';
require_admin();

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../../include/db_config.php';
$pdo = getPDO();

try {
    $stmt = $pdo->query("
        SELECT 
            b.id,
            b.full_name,
            b.email,
            b.phone,
            b.start_date,
            b.end_date,
            b.total_price,
            b.status,
            b.created_at,
            b.assigned_car_id,
            b.additional_cost,
            b.damage_image,
            b.damage_description,
            b.pickup_location,
            b.return_location,
            c.make,
            c.model,
            c.category,
            c.fuel_type,
            ac.number_plate AS assigned_plate
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        LEFT JOIN cars ac ON b.assigned_car_id = ac.id
        ORDER BY b.id DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'   => true,
        'data' => $orders,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
exit;
