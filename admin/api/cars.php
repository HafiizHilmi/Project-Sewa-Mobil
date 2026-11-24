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
            c.*, 
            b.full_name AS renter_name,
            b.start_date,
            b.end_date,
            b.pickup_location,
            b.return_location
        FROM cars c
        LEFT JOIN bookings b ON c.id = b.assigned_car_id AND b.status = 'confirmed'
        ORDER BY c.id DESC
    ");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'   => true,
        'data' => $cars,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
exit;
