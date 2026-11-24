<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/guard.php';
require_admin();
require_once __DIR__ . '/../../include/db_config.php';
$pdo = getPDO();

// Pastikan hanya menerima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ditolak.");
}

$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null;
$reject_reason = $_POST['reject_reason'] ?? null;

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
       || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

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
        
        $msg = "Status verifikasi berhasil diubah menjadi " . strtoupper($action) . ".";
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'message' => $msg,
                'status' => $action,
                'user_id' => (int) $user_id
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Arahkan kembali ke halaman verifikasi dan beri notifikasi
        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = "success";
        header("Location: ../index.php?page=verifications");
        exit;
    } catch (PDOException $e) {
        $errMsg = "Terjadi kesalahan database: " . $e->getMessage();
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $errMsg], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash_msg'] = $errMsg;
        $_SESSION['flash_type'] = "error";
        header("Location: ../index.php?page=verifications");
        exit;
    }
} else {
    $errMsg = "Data verifikasi tidak valid.";
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $errMsg], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $_SESSION['flash_msg'] = $errMsg;
    $_SESSION['flash_type'] = "error";
    header("Location: ../index.php?page=verifications");
    exit;
}
