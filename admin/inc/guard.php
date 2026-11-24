<?php
/**
 * Guard — Keamanan Backend Admin Panel
 * Semua endpoint admin WAJIB include file ini di baris pertama setelah session_start().
 */

/**
 * Pastikan admin sudah login; jika belum, kembalikan 403 JSON lalu exit.
 */
function require_admin(bool $redirectOnHtml = false): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['admin_id'])) {
        if ($redirectOnHtml) {
            header("Location: login.php");
            exit();
        }
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized — sesi admin tidak ditemukan.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Hanya izinkan superuser; staff akan dapat 403.
 */
function require_superuser(): void {
    require_admin();
    if (($_SESSION['admin_role'] ?? '') !== 'superuser') {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Hanya Superuser yang diizinkan.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Generate CSRF token per-session dan simpan di $_SESSION.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifikasi CSRF token dari POST atau header X-CSRF-Token.
 * Panggil ini di action endpoint yang menerima POST mutation.
 */
function verify_csrf(): void {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'CSRF token tidak valid.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Set flash message untuk redirect berikutnya.
 */
function flash(string $msg, string $type = 'info'): void {
    $_SESSION['flash_msg'] = $msg;
    $_SESSION['flash_type'] = $type;
}

/**
 * Ambil dan hapus flash message; kembalikan array ['msg','type'] atau null.
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash_msg'])) {
        $data = ['msg' => $_SESSION['flash_msg'], 'type' => $_SESSION['flash_type'] ?? 'info'];
        unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
        return $data;
    }
    return null;
}
