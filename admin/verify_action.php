<?php
session_start();
require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

// Pastikan hanya POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ditolak.");
}

$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null;
$reject_reason = $_POST['reject_reason'] ?? null;

if ($user_id && in_array($action, ['verified', 'rejected'])) {
    try {
        if ($action === 'rejected') {
            $stmt = $pdo->prepare("UPDATE users SET verification_status = :status, reject_reason = :reason WHERE id = :id");
            $stmt->execute([
                ':status' => $action,
                ':reason' => $reject_reason,
                ':id' => $user_id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET verification_status = :status, reject_reason = NULL WHERE id = :id");
            $stmt->execute([
                ':status' => $action,
                ':id' => $user_id
            ]);
        }
        
        // Redirect kembali ke index dengan notifikasi
        echo "<script>
                alert('Berhasil: Status verifikasi user berhasil diubah menjadi " . strtoupper($action) . ".');
                window.location.href = 'index.php';
              </script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>
                alert('Terjadi kesalahan pada sistem database.');
                window.history.back();
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('Data tidak valid.');
            window.history.back();
          </script>";
    exit;
}
