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
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM business_settings WHERE setting_key IN ('fonnte_token', 'midtrans_is_production', 'midtrans_server_key', 'midtrans_client_key', 'telegram_bot_token', 'telegram_chat_id')");
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
$telegramBotToken = isset($dbSettings['telegram_bot_token']) && $dbSettings['telegram_bot_token'] !== '' ? $dbSettings['telegram_bot_token'] : getenv('TELEGRAM_BOT_TOKEN');
$telegramChatId = isset($dbSettings['telegram_chat_id']) && $dbSettings['telegram_chat_id'] !== '' ? $dbSettings['telegram_chat_id'] : getenv('TELEGRAM_CHAT_ID');

define('FONNTE_TOKEN', $fonnteToken ?: 'NULL');
define('MIDTRANS_IS_PRODUCTION', $midtransIsProd);
define('MIDTRANS_SERVER_KEY', $midtransServer ?: '');
define('MIDTRANS_CLIENT_KEY', $midtransClient ?: '');
define('TELEGRAM_BOT_TOKEN', $telegramBotToken ?: '');
define('TELEGRAM_CHAT_ID', $telegramChatId ?: '');

if (!function_exists('sendTelegramNotification')) {
    function sendTelegramNotification($message) {
        $botToken = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
        $chatId = defined('TELEGRAM_CHAT_ID') ? TELEGRAM_CHAT_ID : '';
        
        if (empty($botToken) || empty($chatId)) {
            return false;
        }
        
        $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
        
        $postData = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}