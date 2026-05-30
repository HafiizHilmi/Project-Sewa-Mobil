<?php
require_once '../include/db_config.php';
$pdo = getPDO();
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'");
echo json_encode(['count' => $stmt->fetchColumn()]);
?>