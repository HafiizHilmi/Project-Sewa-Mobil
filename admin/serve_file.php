<?php
session_start();
// Harusnya cek session admin di sini
// if (!isset($_SESSION['admin_id'])) { exit; }

$file = $_GET['file'] ?? '';
if (!$file) exit;

// Path to data_sensitive
$path = __DIR__ . '/../data_sensitive/uploads/' . basename($file);

if (file_exists($path)) {
    $mime = mime_content_type($path);
    header('Content-Type: ' . $mime);
    readfile($path);
} else {
    http_response_code(404);
}
