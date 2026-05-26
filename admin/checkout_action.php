<?php
// Misal ini adalah data yang diinput oleh penyewa saat memesan mobil:
$asal_pelanggan = $_POST['kota_asal'];        // Cth: "Surabaya"
$tujuan_penyewa = $_POST['kota_tujuan'];      // Cth: "Bangkalan"
$lokasi_ambil   = $_POST['lokasi_pickup'];    // Cth: "Pelabuhan Kamal"

// 1. Ambil semua data blacklist dari database
$stmtBL = $pdo->query("SELECT location_name FROM blacklisted_locations");
$blacklists = $stmtBL->fetchAll(PDO::FETCH_COLUMN);

$is_blacklisted = false;
$blocked_reason = "";

// 2. Loop dan periksa apakah ada kata blacklist di dalam input penyewa
foreach ($blacklists as $b_area) {
    // Ubah ke huruf kecil semua agar pengecekan tidak sensitif huruf besar/kecil
    $b_area = strtolower(trim($b_area));
    
    if (strpos(strtolower($asal_pelanggan), $b_area) !== false) {
        $is_blacklisted = true;
        $blocked_reason = "Asal pelanggan berada di zona blacklist: " . ucwords($b_area);
        break;
    }
    if (strpos(strtolower($tujuan_penyewa), $b_area) !== false) {
        $is_blacklisted = true;
        $blocked_reason = "Tujuan perjalanan berada di zona blacklist: " . ucwords($b_area);
        break;
    }
    if (strpos(strtolower($lokasi_ambil), $b_area) !== false) {
        $is_blacklisted = true;
        $blocked_reason = "Lokasi pengambilan mobil berada di zona blacklist: " . ucwords($b_area);
        break;
    }
}

// 3. Jika terkena blacklist, tolak pesanan
if ($is_blacklisted) {
    // Kembalikan user ke halaman sebelumnya dengan pesan error
    echo "<script>
            alert('Maaf, pesanan Anda tidak dapat diproses. {$blocked_reason}');
            window.history.back();
          </script>";
    exit();
}

// JIKA LOLOS BLACKLIST, LANJUTKAN PROSES SIMPAN PESANAN (INSERT INTO bookings ...)
// ...
// ...
?>