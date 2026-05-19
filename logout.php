<?php
// Mulai session
session_start();

// Hancurkan semua data memori
session_unset();
session_destroy();

// Mulai ulang session khusus untuk bawa pesan sukses
session_start();
$_SESSION['flash_success'] = 'Anda berhasil keluar.';

// Arahkan langsung ke halaman login
header('Location: index.php?module=Auth&action=login');
exit;
?>