<?php
session_start();
require_once __DIR__ . '/../../include/db_config.php';
$pdo = getPDO();

// Pastikan hanya menerima request POST
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
        
        // Arahkan kembali ke halaman verifikasi dan beri notifikasi
        echo "<script>
                alert('Berhasil: Status verifikasi user berhasil diubah menjadi " . strtoupper($action) . ".');
                window.location.href = '../index.php?page=verifications';
              </script>";
        exit;
    } catch (PDOException $e) {
        $errorMsg = addslashes($e->getMessage());
        echo "<script>
                alert('Terjadi kesalahan pada sistem database: " . $errorMsg . "');
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
