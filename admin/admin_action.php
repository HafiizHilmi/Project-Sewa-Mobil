<?php
require_once '../Config/database.php';
session_start();

// Proteksi agar staff tidak bisa hapus
if ($_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak");
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id = $id AND role != 'admin'");
    header("Location: index.php?page=settings"); // Sesuaikan route-mu
}
?>