<?php
/**
 * migrate.php — One-time database schema migration runner.
 * Dijalankan oleh db_config.php dan admin/index.php.
 * Hanya mengeksekusi jika SCHEMA_VERSION di database lebih rendah dari target.
 */

if (!function_exists('getPDO')) {
    require_once __DIR__ . '/../../include/db_config.php';
}
$pdo = getPDO();
if (!$pdo) return;

// ── Versi schema target ─────────────────────────────────────────────────────
define('SCHEMA_VERSION', '3');

// Buat tabel business_settings kalau belum ada
$pdo->exec("CREATE TABLE IF NOT EXISTS `business_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ambil versi schema yang sudah berjalan
$currentVersion = '0';
try {
    $sv = $pdo->query("SELECT setting_value FROM business_settings WHERE setting_key = 'schema_version' LIMIT 1");
    if ($sv) $currentVersion = $sv->fetchColumn() ?: '0';
} catch (Exception $e) {}

if ($currentVersion >= SCHEMA_VERSION) {
    return; // Sudah up-to-date, keluar langsung tanpa query berlebih
}

// ── Migration helper ─────────────────────────────────────────────────────────
function col_exists(PDO $pdo, string $table, string $col): bool {
    try {
        $r = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$col}'");
        return $r && $r->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function idx_exists(PDO $pdo, string $table, string $idx): bool {
    try {
        $r = $pdo->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$idx}'");
        return $r && $r->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function safe_exec(PDO $pdo, string $sql): void {
    try { $pdo->exec($sql); } catch (Exception $e) {}
}

// ── Inisialisasi Tabel Dasar ─────────────────────────────────────────────────
safe_exec($pdo, "CREATE TABLE IF NOT EXISTS `admins` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nama` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `role` enum('superuser','staff') NOT NULL DEFAULT 'staff',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

safe_exec($pdo, "CREATE TABLE IF NOT EXISTS `blacklisted_locations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `location_name` VARCHAR(150) NOT NULL,
    `latitude` DECIMAL(10, 8) DEFAULT NULL,
    `longitude` DECIMAL(11, 8) DEFAULT NULL,
    `radius` INT DEFAULT 5000,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Schema v1 — Kolom tambahan cars, bookings, users ──────────────────────────
if ($currentVersion < '1') {
    // cars
    if (!col_exists($pdo, 'cars', 'type_key'))        safe_exec($pdo, "ALTER TABLE cars ADD COLUMN type_key VARCHAR(255) NULL");
    if (!col_exists($pdo, 'cars', 'is_type'))         safe_exec($pdo, "ALTER TABLE cars ADD COLUMN is_type TINYINT(1) NOT NULL DEFAULT 0");
    if (!col_exists($pdo, 'cars', 'number_plate'))    safe_exec($pdo, "ALTER TABLE cars ADD COLUMN number_plate VARCHAR(50) NULL");
    if (!col_exists($pdo, 'cars', 'chassis_number'))  safe_exec($pdo, "ALTER TABLE cars ADD COLUMN chassis_number VARCHAR(255) NULL");
    if (!col_exists($pdo, 'cars', 'engine_capacity')) safe_exec($pdo, "ALTER TABLE cars ADD COLUMN engine_capacity VARCHAR(50) NULL");
    if (!col_exists($pdo, 'cars', 'transmission'))    safe_exec($pdo, "ALTER TABLE cars ADD COLUMN transmission VARCHAR(50) NULL DEFAULT 'Manual'");
    if (!col_exists($pdo, 'cars', 'available'))       safe_exec($pdo, "ALTER TABLE cars ADD COLUMN available TINYINT(1) NOT NULL DEFAULT 1");
    if (!col_exists($pdo, 'cars', 'stock'))           safe_exec($pdo, "ALTER TABLE cars ADD COLUMN stock INT NOT NULL DEFAULT 1");
    if (!col_exists($pdo, 'cars', 'webhook_alarm'))   safe_exec($pdo, "ALTER TABLE cars ADD COLUMN webhook_alarm VARCHAR(255) NULL");
    if (!col_exists($pdo, 'cars', 'webhook_mesin'))   safe_exec($pdo, "ALTER TABLE cars ADD COLUMN webhook_mesin VARCHAR(255) NULL");
    safe_exec($pdo, "ALTER TABLE cars MODIFY COLUMN chassis_number VARCHAR(255) NULL");

    // bookings
    if (!col_exists($pdo, 'bookings', 'assigned_car_id'))    safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN assigned_car_id INT UNSIGNED NULL");
    if (!col_exists($pdo, 'bookings', 'additional_cost'))    safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN additional_cost DECIMAL(12,2) DEFAULT 0.00");
    if (!col_exists($pdo, 'bookings', 'damage_image'))       safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN damage_image VARCHAR(255) NULL");
    if (!col_exists($pdo, 'bookings', 'damage_description')) safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN damage_description TEXT NULL");
    if (!col_exists($pdo, 'bookings', 'payment_token'))      safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN payment_token VARCHAR(255) DEFAULT NULL");
    if (!col_exists($pdo, 'bookings', 'payment_url'))        safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN payment_url VARCHAR(500) DEFAULT NULL");
    if (!col_exists($pdo, 'bookings', 'payment_expiry'))     safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN payment_expiry TIMESTAMP NULL DEFAULT NULL");
    if (!col_exists($pdo, 'bookings', 'midtrans_order_id'))  safe_exec($pdo, "ALTER TABLE bookings ADD COLUMN midtrans_order_id VARCHAR(100) DEFAULT NULL");

    // users
    if (!col_exists($pdo, 'users', 'reject_reason'))  safe_exec($pdo, "ALTER TABLE users ADD COLUMN reject_reason TEXT NULL");

    // Seed mobil bila kosong
    $seedFile = __DIR__ . '/../../db/seed_cars.php';
    if (file_exists($seedFile)) {
        require_once $seedFile;
        try {
            $carCount = (int)$pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
            if ($carCount === 0 && function_exists('seed_cars')) {
                seed_cars($pdo);
            }
        } catch (Exception $e) {}
    }

    // Update type_key
    safe_exec($pdo, "UPDATE cars SET type_key = CONCAT(COALESCE(make,''),'|',COALESCE(model,''),'|',COALESCE(year,''),'|',COALESCE(fuel_type,''),'|',COALESCE(engine_capacity,''),'|',COALESCE(seats,''),'|',COALESCE(transmission,'Manual'),'|',COALESCE(category,''),'|',COALESCE(price_per_day,'')) WHERE type_key IS NULL OR type_key = ''");
}

// ── Schema v2 — Indexes Performa ─────────────────────────────────────────────
if ($currentVersion < '2') {
    if (!idx_exists($pdo, 'bookings', 'idx_bookings_status_created'))
        safe_exec($pdo, "ALTER TABLE bookings ADD INDEX idx_bookings_status_created (status, created_at)");
    if (!idx_exists($pdo, 'bookings', 'idx_bookings_created'))
        safe_exec($pdo, "ALTER TABLE bookings ADD INDEX idx_bookings_created (created_at)");
    if (!idx_exists($pdo, 'bookings', 'idx_bookings_user'))
        safe_exec($pdo, "ALTER TABLE bookings ADD INDEX idx_bookings_user (user_id)");
    if (!idx_exists($pdo, 'cars', 'idx_cars_type_key'))
        safe_exec($pdo, "ALTER TABLE cars ADD INDEX idx_cars_type_key (type_key(100))");
    if (!idx_exists($pdo, 'cars', 'idx_cars_available'))
        safe_exec($pdo, "ALTER TABLE cars ADD INDEX idx_cars_available (available, is_type)");
}

// ── Schema v3 — Migrasi Bcrypt Hash Admin ────────────────────────────────────
if ($currentVersion < '3') {
    // Pastikan admin default ada dengan password hash
    $chkAdmin = $pdo->query("SELECT id, password FROM admins WHERE email = 'ahmad@sewamobilsby.id'")->fetch(PDO::FETCH_ASSOC);
    if (!$chkAdmin) {
        $defaultHash = password_hash('admin123', PASSWORD_BCRYPT);
        $ins = $pdo->prepare("INSERT INTO admins (nama, email, password, role) VALUES ('Ahmad Fauzi', 'ahmad@sewamobilsby.id', ?, 'superuser')");
        $ins->execute([$defaultHash]);
    }

    // Hash semua password admin yang masih plaintext
    $allAdmins = $pdo->query("SELECT id, password FROM admins")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allAdmins as $adm) {
        if (strpos($adm['password'], '$2y$') !== 0) {
            $hashed = password_hash($adm['password'], PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $upd->execute([$hashed, $adm['id']]);
        }
    }

    // Batalkan booking pending yang sudah kadaluarsa
    safe_exec($pdo, "UPDATE bookings SET status = 'cancelled' WHERE status = 'pending' AND payment_expiry IS NOT NULL AND payment_expiry < NOW()");
}

// ── Simpan Versi Schema Baru ──────────────────────────────────────────────────
$upsert = $pdo->prepare("INSERT INTO business_settings (setting_key, setting_value)
    VALUES ('schema_version', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
$upsert->execute([SCHEMA_VERSION, SCHEMA_VERSION]);
