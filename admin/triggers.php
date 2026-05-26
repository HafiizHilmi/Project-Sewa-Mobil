<?php
$actionType = isset($_GET['type']) ? $_GET['type'] : '';
$carId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$deviceId = "";
$webhookIdentifier = "";
$pesanSukses = "";

if ($carId > 0) {
    require_once __DIR__ . '/../include/db_config.php';
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT chassis_number FROM cars WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $carId]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($car && !empty($car['chassis_number'])) {
            $deviceId = trim($car['chassis_number']);
        }
    }
}

// Fallback jika tidak ditemukan di DB
if (empty($deviceId)) {
    $deviceId = "DEVICE_ID_MD"; 
}

if ($actionType === 'alarm') {
    $webhookIdentifier = "nyalakan_alarm";
    $pesanSukses = "Sinyal alarm berhasil dikirim ke kendaraan!";
} elseif ($actionType === 'mesin_mati') {
    $webhookIdentifier = "matikan_mesin";
    $pesanSukses = "Sistem kelistrikan kendaraan berhasil dimatikan!";
}

if ($webhookIdentifier !== "") {
    // Gunakan urlencode agar mencegah error malformed input jika ada spasi pada ID
    $encodedDevice = urlencode($deviceId);
    $url = "https://trigger.macrodroid.com/{$encodedDevice}/{$webhookIdentifier}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    curl_exec($ch);
    
    if(curl_errno($ch)){
        $error_msg = curl_error($ch);
        echo "<script>alert('Error Koneksi: {$error_msg}'); window.history.back();</script>";
        curl_close($ch);
        exit;
    }
    curl_close($ch);

    echo "<script>
            alert('{$pesanSukses}');
            window.history.back();
          </script>";
} else {
    echo "<script>
            alert('Perintah tidak dikenali.');
            window.history.back();
          </script>";
}
?>
