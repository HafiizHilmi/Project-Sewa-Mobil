<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inc/guard.php';
require_admin();

$file = $_GET['file'] ?? '';
if (!$file) {
    http_response_code(400);
    exit("File parameter missing.");
}

// Mencegah directory traversal
$cleanFilename = basename($file);
$path = __DIR__ . '/../data_sensitive/uploads/' . $cleanFilename;

if (file_exists($path) && is_file($path)) {
    $mime = mime_content_type($path) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: private, max-age=3600');
    readfile($path);
    exit;
} else {
    http_response_code(404);
    echo "File not found.";
    exit;
}
