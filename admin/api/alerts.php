<?php
/**
 * api/alerts.php — Endpoint notifikasi real-time (pesanan pending baru).
 * Dipanggil oleh admin-ui.js setiap 25 detik melalui Fetch API.
 */
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../admin/inc/guard.php';
require_admin();

ob_clean();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../../include/db_config.php';
$pdo = getPDO();

try {
    // Ambil max booking id untuk deteksi pesanan baru di client
    $maxId = (int)$pdo->query("SELECT COALESCE(MAX(id), 0) FROM bookings")->fetchColumn();

    // Ambil pesanan pending terbaru (maks 10)
    $stmt = $pdo->prepare("
        SELECT b.id, b.created_at, b.full_name, c.make, c.model
        FROM bookings b
        LEFT JOIN cars c ON b.car_id = c.id
        WHERE b.status = 'pending'
        ORDER BY b.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $carName = trim(($row['make'] ?? '') . ' ' . ($row['model'] ?? ''));
        $items[] = [
            'id'    => (int)$row['id'],
            'title' => 'Pesanan Masuk #' . $row['id'],
            'desc'  => ($row['full_name'] ?? 'Pelanggan') . ' ingin menyewa ' . ($carName ?: 'mobil'),
            'time'  => date('d M Y, H:i', strtotime($row['created_at'])),
        ];
    }

    echo json_encode([
        'ok'      => true,
        'max_id'  => $maxId,
        'pending' => count($items),
        'items'   => $items,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
exit;
