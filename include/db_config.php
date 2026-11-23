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
        $pdo = $GLOBALS['pdo'];

        // Migrasi kolom tabel cars
        $carCols = [
            'stock'           => "ALTER TABLE cars ADD COLUMN stock INT NOT NULL DEFAULT 1",
            'is_type'         => "ALTER TABLE cars ADD COLUMN is_type TINYINT(1) NOT NULL DEFAULT 0",
            'type_key'        => "ALTER TABLE cars ADD COLUMN type_key VARCHAR(255) NULL",
            'number_plate'    => "ALTER TABLE cars ADD COLUMN number_plate VARCHAR(50) NULL",
            'chassis_number'  => "ALTER TABLE cars ADD COLUMN chassis_number VARCHAR(255) NULL",
            'engine_capacity' => "ALTER TABLE cars ADD COLUMN engine_capacity VARCHAR(50) NULL",
            'transmission'    => "ALTER TABLE cars ADD COLUMN transmission VARCHAR(50) DEFAULT 'Manual'",
        ];
        foreach ($carCols as $col => $sql) {
            $stmt = $pdo->query("SHOW COLUMNS FROM cars LIKE '$col'");
            if ($stmt && $stmt->rowCount() === 0) {
                $pdo->exec($sql);
            }
        }
        try {
            $pdo->exec("ALTER TABLE cars MODIFY COLUMN chassis_number VARCHAR(255) NULL");
        } catch (Exception $e) {
            // Abaikan jika tidak didukung
        }
        // Seed armada dari db/cars.json bila tabel cars masih kosong (instalasi baru)
        require_once __DIR__ . '/../db/seed_cars.php';
        if ((int) $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn() === 0) {
            seed_cars($pdo);
        }

        $pdo->exec("UPDATE cars SET type_key = CONCAT(COALESCE(make,''),'|',COALESCE(model,''),'|',COALESCE(year,''),'|',COALESCE(fuel_type,''),'|',COALESCE(engine_capacity,''),'|',COALESCE(seats,''),'|',COALESCE(transmission,'Manual'),'|',COALESCE(category,''),'|',COALESCE(price_per_day,'')) WHERE type_key IS NULL OR type_key = ''");

        // Migrasi kolom tabel bookings
        $bookingCols = [
            'assigned_car_id'    => "ALTER TABLE bookings ADD COLUMN assigned_car_id INT UNSIGNED NULL",
            'additional_cost'    => "ALTER TABLE bookings ADD COLUMN additional_cost DECIMAL(12, 2) DEFAULT 0.00",
            'damage_image'       => "ALTER TABLE bookings ADD COLUMN damage_image VARCHAR(255) NULL",
            'damage_description' => "ALTER TABLE bookings ADD COLUMN damage_description TEXT NULL",
            'payment_token'      => "ALTER TABLE bookings ADD COLUMN payment_token VARCHAR(255) DEFAULT NULL",
            'payment_url'        => "ALTER TABLE bookings ADD COLUMN payment_url VARCHAR(255) DEFAULT NULL",
            'payment_expiry'     => "ALTER TABLE bookings ADD COLUMN payment_expiry TIMESTAMP NULL DEFAULT NULL",
            'midtrans_order_id'  => "ALTER TABLE bookings ADD COLUMN midtrans_order_id VARCHAR(100) DEFAULT NULL",
        ];
        foreach ($bookingCols as $col => $sql) {
            $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE '$col'");
            if ($stmt && $stmt->rowCount() === 0) {
                $pdo->exec($sql);
            }
        }

        // Migrasi kolom tabel users
        $userCols = [
            'reject_reason' => "ALTER TABLE users ADD COLUMN reject_reason TEXT NULL DEFAULT NULL",
        ];
        foreach ($userCols as $col => $sql) {
            $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE '$col'");
            if ($stmt && $stmt->rowCount() === 0) {
                $pdo->exec($sql);
            }
        }

        // Pastikan tabel admins ada
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nama` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `role` enum('superuser','staff') NOT NULL DEFAULT 'staff',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seeding admin default jika belum ada
        $checkAdmin = $pdo->query("SELECT COUNT(*) FROM admins WHERE email = 'ahmad@sewamobilsby.id'");
        if ($checkAdmin && $checkAdmin->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO admins (nama, email, password, role) VALUES ('Ahmad Fauzi', 'ahmad@sewamobilsby.id', 'admin123', 'superuser')");
        }

        // Pastikan tabel blacklisted_locations ada
        $pdo->exec("CREATE TABLE IF NOT EXISTS `blacklisted_locations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `location_name` VARCHAR(150) NOT NULL,
            `latitude` DECIMAL(10, 8) DEFAULT NULL,
            `longitude` DECIMAL(11, 8) DEFAULT NULL,
            `radius` INT DEFAULT 5000,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
} catch (Exception $e) {
    // Abaikan jika migrasi gagal atau tabel belum dibuat
}

function getPDO() {
    return $GLOBALS['pdo'] ?? null;
}

?>
