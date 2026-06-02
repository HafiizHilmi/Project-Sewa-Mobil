<?php
session_start();
$file = $_GET['file'] ?? '';
if (!$file) exit;

$path = __DIR__ . '/../data_sensitive/uploads/' . basename($file);

if (file_exists($path)) {
    $mime = mime_content_type($path);
    header('Content-Type: ' . $mime);
    readfile($path);
} else {
    http_response_code(404);
}
