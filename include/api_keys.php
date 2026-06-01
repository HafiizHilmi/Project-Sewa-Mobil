<?php
// Simpan token Fonnte kamu di sini
define('FONNTE_TOKEN', 'UNr7QQkbrDeoRAgEe7bw');
=======
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

define('FONNTE_TOKEN', getenv('FONNTE_TOKEN') ?: 'NULL');
define('MIDTRANS_IS_PRODUCTION', filter_var(getenv('MIDTRANS_IS_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN));
define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY') ?: '');
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY') ?: '');
