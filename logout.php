<?php
session_start();

session_unset();
session_destroy();

session_start();
$_SESSION['flash_success'] = 'Anda berhasil keluar.';

header('Location: index.php?module=Auth&action=login');
exit;
?>