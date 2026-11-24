<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inc/guard.php';
require_admin(true);

$actionType = isset($_GET['type']) ? $_GET['type'] : '';
$carId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$rawTarget = "";
$webhookIdentifier = "";
$pesanSukses = "";

if ($carId > 0) {
    require_once __DIR__ . '/../include/db_config.php';
    $pdo = getPDO();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT chassis_number, webhook_alarm, webhook_mesin FROM cars WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $carId]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($car) {
            if ($actionType === 'alarm') {
                if (!empty($car['webhook_alarm'])) {
                    $rawTarget = trim($car['webhook_alarm']);
                } elseif (!empty($car['webhook_mesin'])) {
                    $rawTarget = trim($car['webhook_mesin']);
                } elseif (!empty($car['chassis_number'])) {
                    $rawTarget = trim($car['chassis_number']);
                }
            } elseif ($actionType === 'mesin_mati') {
                if (!empty($car['webhook_mesin'])) {
                    $rawTarget = trim($car['webhook_mesin']);
                } elseif (!empty($car['webhook_alarm'])) {
                    $rawTarget = trim($car['webhook_alarm']);
                } elseif (!empty($car['chassis_number'])) {
                    $rawTarget = trim($car['chassis_number']);
                }
            }
        }
    }
}

// Gunakan target cadangan jika tidak ada di database
if (empty($rawTarget)) {
    $rawTarget = "DEVICE_ID_MD"; 
}

if ($actionType === 'alarm') {
    $webhookIdentifier = "nyalakan_alarm";
    $pesanSukses = "Sinyal alarm berhasil dikirim ke kendaraan!";
} elseif ($actionType === 'mesin_mati') {
    $webhookIdentifier = "matikan_mesin";
    $pesanSukses = "Sistem kelistrikan kendaraan berhasil dimatikan!";
}

if ($webhookIdentifier !== "") {
    // Selesaikan URL target (mendukung ID MacroDroid atau Link URL langsung)
    $url = resolveTriggerUrl($rawTarget, $webhookIdentifier);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $curlErr = curl_errno($ch);
    $error_msg = $curlErr ? curl_error($ch) : '';
    curl_close($ch);

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
           || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($curlErr) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => "Error Koneksi: " . $error_msg,
                'target_url' => $url
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $_SESSION['flash_msg'] = "Error Koneksi: " . $error_msg;
        $_SESSION['flash_type'] = "error";
        header("Location: index.php?page=cars");
        exit;
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $pesanSukses,
            'target_url' => $url
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $_SESSION['flash_msg'] = $pesanSukses;
    $_SESSION['flash_type'] = "success";
    header("Location: index.php?page=cars");
    exit;
} else {
    $_SESSION['flash_msg'] = "Perintah tidak dikenali.";
    $_SESSION['flash_type'] = "warn";
    header("Location: index.php?page=cars");
    exit;
}

/**
 * Menyelesaikan URL target pengiriman sinyal trigger.
 * Mendukung:
 * 1. MacroDroid Device ID (misal: "c7b60517-..." atau kode biasa) -> https://trigger.macrodroid.com/{device_id}/{action}
 * 2. URL langsung (misal: "http://192.168.11.12:8080/nyalakan_alarm" atau "http://192.168.11.12:8080")
 * 3. IP address tanpa http (misal: "192.168.11.12:8080/nyalakan_alarm")
 * 4. URL dengan placeholder {action} (misal: "http://192.168.11.12:8080/trigger?action={action}")
 * 5. Multiple URL dipisah koma/garis tegak/baris baru (misal: "http://.../alarm | http://.../matikan_mesin")
 */
function resolveTriggerUrl($rawTarget, $webhookIdentifier) {
    $raw = trim($rawTarget);
    if (empty($raw)) {
        $raw = 'DEVICE_ID_MD';
    }

    // Cek apakah ada multiple target yang dipisah pemisah (, ; | atau baris baru)
    $lines = preg_split('/[\r\n|,;]+/', $raw);
    $lines = array_values(array_filter(array_map('trim', $lines)));

    $chosen = $raw;
    $matchedSpecific = false;

    if (count($lines) > 1) {
        foreach ($lines as $line) {
            if ($webhookIdentifier === 'nyalakan_alarm' && preg_match('/alarm|nyalakan|sirene/i', $line)) {
                $chosen = $line;
                $matchedSpecific = true;
                break;
            } elseif ($webhookIdentifier === 'matikan_mesin' && preg_match('/mesin|mati|engine/i', $line)) {
                $chosen = $line;
                $matchedSpecific = true;
                break;
            }
        }
        if (!$matchedSpecific) {
            $chosen = ($webhookIdentifier === 'matikan_mesin' && isset($lines[1])) ? $lines[1] : $lines[0];
        }
    }

    // Bersihkan prefix label seperti "alarm=" atau "mesin: " jika ada
    $chosen = preg_replace('/^(alarm|mesin|nyalakan_alarm|matikan_mesin)\s*[:=]\s*/i', '', $chosen);

    // Cek apakah berupa URL atau IP address
    $isUrl = false;
    if (preg_match('/^https?:\/\//i', $chosen)) {
        $isUrl = true;
    } elseif (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})(:\d+)?(\/.*)?$/', $chosen)) {
        $chosen = 'http://' . $chosen;
        $isUrl = true;
    }

    if ($isUrl) {
        // Jika terdapat placeholder {action} atau {webhook}
        if (stripos($chosen, '{action}') !== false) {
            return str_ireplace('{action}', $webhookIdentifier, $chosen);
        }
        if (stripos($chosen, '{webhook}') !== false) {
            return str_ireplace('{webhook}', $webhookIdentifier, $chosen);
        }

        // Jika dipilih dari multiple target spesifik, URL sudah spesifik untuk aksinya
        if ($matchedSpecific) {
            return $chosen;
        }

        // Jika URL single dengan nama action tertentu di dalamnya
        if ($webhookIdentifier === 'nyalakan_alarm') {
            if (stripos($chosen, 'matikan_mesin') !== false) {
                return str_ireplace('matikan_mesin', 'nyalakan_alarm', $chosen);
            }
            if (stripos($chosen, 'mesin_mati') !== false) {
                return str_ireplace('mesin_mati', 'nyalakan_alarm', $chosen);
            }
            if (stripos($chosen, 'nyalakan_alarm') !== false) {
                return $chosen;
            }
        } elseif ($webhookIdentifier === 'matikan_mesin') {
            if (stripos($chosen, 'nyalakan_alarm') !== false) {
                return str_ireplace('nyalakan_alarm', 'matikan_mesin', $chosen);
            }
            if (stripos($chosen, 'bunyikan_alarm') !== false) {
                return str_ireplace('bunyikan_alarm', 'matikan_mesin', $chosen);
            }
            if (stripos($chosen, 'matikan_mesin') !== false) {
                return $chosen;
            }
        }

        // Cek jika ada kata alarm atau mesin dalam url
        if ($webhookIdentifier === 'nyalakan_alarm') {
            if (preg_match('/\b(mesin|mati|engine_off)\b/i', $chosen)) {
                return preg_replace('/\b(mesin|mati|engine_off)\b/i', 'nyalakan_alarm', $chosen);
            }
            if (preg_match('/\b(alarm|nyalakan)\b/i', $chosen)) {
                return $chosen;
            }
        } elseif ($webhookIdentifier === 'matikan_mesin') {
            if (preg_match('/\b(alarm|nyalakan|bunyikan)\b/i', $chosen)) {
                return preg_replace('/\b(alarm|nyalakan|bunyikan)\b/i', 'matikan_mesin', $chosen);
            }
            if (preg_match('/\b(mesin|mati)\b/i', $chosen)) {
                return $chosen;
            }
        }

        // Jika URL polos (misal http://192.168.11.12:8080 atau http://192.168.11.12:8080/)
        return rtrim($chosen, '/') . '/' . $webhookIdentifier;
    }

    // Jika bukan URL, maka merupakan Device ID MacroDroid biasa
    $encodedDevice = urlencode($chosen);
    return "https://trigger.macrodroid.com/{$encodedDevice}/{$webhookIdentifier}";
}
?>
