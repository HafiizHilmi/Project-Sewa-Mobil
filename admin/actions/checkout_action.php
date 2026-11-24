<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/guard.php';
require_admin();
// Ambil data input dari penyewa (contoh kota asal, tujuan, dan lokasi jemput)
$asal_pelanggan = $_POST['kota_asal'] ?? '';   
$tujuan_penyewa = $_POST['kota_tujuan'] ?? ''; 
$lokasi_ambil   = $_POST['lokasi_pickup'] ?? '';

// 1. Ambil seluruh data lokasi blacklist dari database
$stmtBL = $pdo->query("SELECT location_name FROM blacklisted_locations");
$blacklists = $stmtBL->fetchAll(PDO::FETCH_COLUMN);

$is_blacklisted = false;
$blocked_reason = "";

// 2. Cek apakah ada kata kunci blacklist dalam input penyewa
foreach ($blacklists as $b_area) {
    // Ubah ke huruf kecil biar pencocokannya tidak sensitif huruf besar/kecil
    $b_area = strtolower(trim($b_area));
    
    if (strpos(strtolower($asal_pelanggan), $b_area) !== false) {
        $is_blacklisted = true;
        $blocked_reason = "Asal pelanggan berada di wilayah tidak terjangkau.";
        break;
    }
    if (strpos(strtolower($tujuan_penyewa), $b_area) !== false) {
        $is_blacklisted = true;
        $blocked_reason = "Tujuan perjalanan berada di wilayah tidak terjangkau.";
        break;
    }
    if (strpos(strtolower($lokasi_ambil), $b_area) !== false) {
        $is_blacklisted = true;
        $blocked_reason = "Lokasi pengambilan mobil berada di wilayah tidak terjangkau.";
        break;
    }
}

// 3. Jika masuk blacklist, langsung tolak pesanan
if ($is_blacklisted) {
    $_SESSION['flash_msg'] = "Maaf, pesanan tidak dapat diproses: " . $blocked_reason;
    $_SESSION['flash_type'] = "error";
    header("Location: ../index.php?page=orders");
    exit();
}

// Jika lolos blacklist, lanjutkan ke proses simpan pesanan ke database
?>