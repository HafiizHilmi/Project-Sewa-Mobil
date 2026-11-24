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
            u.id,
            u.name,
            u.email,
            u.phone,
            u.verification_status,
            u.created_at,
            COUNT(b.id) AS total_orders,
            MAX(b.start_date) AS last_order,
            MAX(b.address) AS address,
            COALESCE(SUM(COALESCE(b.total_price, 0) + COALESCE(b.additional_cost, 0)), 0) AS total_spent,
            COALESCE(SUM(CASE WHEN b.additional_cost > 0 OR b.damage_image IS NOT NULL OR (b.damage_description IS NOT NULL AND b.damage_description != '') THEN 1 ELSE 0 END), 0) AS total_damaged,
            GROUP_CONCAT(DISTINCT CONCAT(c.make, ' ', c.model, '::', COALESCE(ac.number_plate, 'Pending')) SEPARATOR '|') AS rented_cars
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id
        LEFT JOIN cars c ON b.car_id = c.id
        LEFT JOIN cars ac ON b.assigned_car_id = ac.id
        WHERE u.role = 'user'
        GROUP BY u.id
        ORDER BY u.id DESC
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'   => true,
        'data' => $customers,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
exit;
