<?php
date_default_timezone_set('Asia/Jakarta');
$host = '127.0.0.1';
$db   = 'project_sewa_mobil';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    // Simpan di $GLOBALS agar bisa diakses walau di-include di dalam fungsi
    $GLOBALS['pdo'] = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Jika database belum dibuat, coba buat otomatis dari file skema SQL
    try {
        $dsnNoDb = "mysql:host=$host;charset=$charset";
        $tmpPdo = new PDO($dsnNoDb, $user, $pass, $options);

        // Buat database baru
        $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset COLLATE ${charset}_unicode_ci");

        // Coba muat berkas skema SQL
        $sqlFile = __DIR__ . '/../db/sewa_mobil.sql';
        if (is_file($sqlFile) && is_readable($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            // Jalankan query SQL untuk inisialisasi tabel
            $tmpPdo->exec($sql);
        }

        // Hubungkan kembali ke database yang baru dibuat
        $GLOBALS['pdo'] = new PDO($dsn, $user, $pass, $options);
    } catch (Exception $ex) {
        // Tampilkan error setup database
        throw new RuntimeException('DB connection/setup failed: ' . $ex->getMessage());
    }
}

try {
    if (isset($GLOBALS['pdo'])) {
        $stmt = $GLOBALS['pdo']->query("SHOW COLUMNS FROM cars LIKE 'stock'");
        if ($stmt && $stmt->rowCount() === 0) {
            $GLOBALS['pdo']->exec("ALTER TABLE cars ADD COLUMN stock INT NOT NULL DEFAULT 1");
        }
    }
} catch (PDOException $e) {
    // Abaikan jika tabel 'cars' belum dibuat atau migrasi gagal
}

function getPDO() {
    return $GLOBALS['pdo'] ?? null;
}

?>
