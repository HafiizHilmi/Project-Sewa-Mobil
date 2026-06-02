<?php
// Muat variabel lingkungan dari berkas .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            
            if (preg_match('/^"(.*)"$/', $value, $matches) || preg_match('/^\'(.*)\'$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
// Ambil pengaturan dari basis data jika terhubung
$dbSettings = [];
try {
    if (function_exists('getPDO')) {
        $pdo = getPDO();
        if ($pdo) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM business_settings WHERE setting_key IN ('fonnte_token', 'midtrans_is_production', 'midtrans_server_key', 'midtrans_client_key')");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $dbSettings[$row['setting_key']] = $row['setting_value'];
                }
            }
        }
    }
} catch (Exception $e) {
    // Abaikan jika tabel belum dibuat atau database bermasalah
}

$fonnteToken = isset($dbSettings['fonnte_token']) && $dbSettings['fonnte_token'] !== '' ? $dbSettings['fonnte_token'] : getenv('FONNTE_TOKEN');
$midtransIsProd = isset($dbSettings['midtrans_is_production']) ? filter_var($dbSettings['midtrans_is_production'], FILTER_VALIDATE_BOOLEAN) : filter_var(getenv('MIDTRANS_IS_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN);
$midtransServer = isset($dbSettings['midtrans_server_key']) && $dbSettings['midtrans_server_key'] !== '' ? $dbSettings['midtrans_server_key'] : getenv('MIDTRANS_SERVER_KEY');
$midtransClient = isset($dbSettings['midtrans_client_key']) && $dbSettings['midtrans_client_key'] !== '' ? $dbSettings['midtrans_client_key'] : getenv('MIDTRANS_CLIENT_KEY');

define('FONNTE_TOKEN', $fonnteToken ?: 'NULL');
define('MIDTRANS_IS_PRODUCTION', $midtransIsProd);
define('MIDTRANS_SERVER_KEY', $midtransServer ?: '');
define('MIDTRANS_CLIENT_KEY', $midtransClient ?: '');

define('FONNTE_TOKEN', getenv('UNr7QQkbrDeoRAgEe7bw') ?: 'NULL');
define('MIDTRANS_IS_PRODUCTION', filter_var(getenv('MIDTRANS_IS_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN));
define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY') ?: '');
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY') ?: '');

