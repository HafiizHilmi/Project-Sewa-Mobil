<?php
$actionType = isset($_GET['type']) ? $_GET['type'] : '';

$deviceId = "eb1cb6f7-feb6-4266-9e54-8937e3590b6d";

$webhookIdentifier = "";
$pesanSukses = "";

if ($actionType === 'alarm') {
    $webhookIdentifier = "nyalakan_alarm";
    $pesanSukses = "Sinyal alarm berhasil dikirim ke kendaraan!";
} elseif ($actionType === 'mesin_mati') {
    $webhookIdentifier = "matikan_mesin";
    $pesanSukses = "Sistem kelistrikan kendaraan berhasil dimatikan!";
}

if ($webhookIdentifier !== "") {
    $url = "https://trigger.macrodroid.com/{$deviceId}/{$webhookIdentifier}";
    
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
