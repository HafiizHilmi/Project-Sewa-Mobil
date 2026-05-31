<?php

class BookingController {
    public function checkout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash'] = "Silakan login terlebih dahulu untuk menyewa mobil.";
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();
        if (!$pdo) {
            die("Database connection error.");
        }

        $stmt = $pdo->prepare("SELECT verification_status FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['verification_status'] !== 'verified') {
            $_SESSION['flash_error'] = "Sesuai kebijakan keamanan, Anda wajib melengkapi dan memverifikasi identitas (KTP & SIM) terlebih dahulu sebelum dapat melanjutkan transaksi sewa mobil.";
            header('Location: index.php?module=Profile&action=index');
            exit;
        }

        $car_id = intval($_GET['car_id'] ?? 1);
        $stmtCar = $pdo->prepare("SELECT c.*, COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), c.image) AS image FROM cars c WHERE c.id = :id AND c.available = 1");
        $stmtCar->execute(['id' => $car_id]);
        $car = $stmtCar->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            $stmtCarDefault = $pdo->query("SELECT c.*, COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), c.image) AS image FROM cars c WHERE c.available = 1 LIMIT 1");
            $car = $stmtCarDefault->fetch(PDO::FETCH_ASSOC);
            if (!$car) {
                die("Tidak ada mobil yang tersedia untuk disewa.");
            }
        }

        require_once __DIR__ . '/views/booking.php';
    }

    public function process() {
        require_once __DIR__ . '/../../../include/db_config.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=Booking&action=checkout');
            exit;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $pickup_location = trim($_POST['pickup_location'] ?? '');
        $return_location = trim($_POST['return_location'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $addon_driver = isset($_POST['addon_driver']) && $_POST['addon_driver'] === '1' ? 1 : 0;
        $car_id = intval($_POST['car_id'] ?? 1);
        $total_price = floatval($_POST['total_price'] ?? 0);

        if ($pickup_location === '' || $full_name === '' || $email === '' || $phone === '' || $address === '' || $start_date === '' || $end_date === '' || $total_price <= 0) {
            $_SESSION['flash'] = "Lengkapi semua data booking terlebih dahulu. Debug: pickup=$pickup_location, name=$full_name, email=$email, phone=$phone, address=$address, start=$start_date, end=$end_date, total=$total_price";
            header('Location: index.php?module=Booking&action=checkout&car_id=' . $car_id);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = 'Email tidak valid.';
            header('Location: index.php?module=Booking&action=checkout&car_id=' . $car_id);
            exit;
        }

        $pdo = getPDO();
        if (!$pdo) {
            $_SESSION['flash'] = 'Database connection error.';
            header('Location: index.php?module=Booking&action=checkout&car_id=' . $car_id);
            exit;
        }

        // ----------------- GEOFENCING BLACKLIST CHECK -----------------
        $stmtBL = $pdo->query("SELECT * FROM blacklisted_locations WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
        $geofences = $stmtBL->fetchAll(PDO::FETCH_ASSOC);

        $pickup_lat = isset($_POST['pickup_lat']) && $_POST['pickup_lat'] !== '' ? floatval($_POST['pickup_lat']) : null;
        $pickup_lon = isset($_POST['pickup_lon']) && $_POST['pickup_lon'] !== '' ? floatval($_POST['pickup_lon']) : null;

        $return_lat = isset($_POST['return_lat']) && $_POST['return_lat'] !== '' ? floatval($_POST['return_lat']) : null;
        $return_lon = isset($_POST['return_lon']) && $_POST['return_lon'] !== '' ? floatval($_POST['return_lon']) : null;

        $address_lat = isset($_POST['address_lat']) && $_POST['address_lat'] !== '' ? floatval($_POST['address_lat']) : null;
        $address_lon = isset($_POST['address_lon']) && $_POST['address_lon'] !== '' ? floatval($_POST['address_lon']) : null;

        $is_blocked = false;
        $block_reason = "";

        $haversine = function($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371000; // in meters
            $latDelta = deg2rad($lat2 - $lat1);
            $lonDelta = deg2rad($lon2 - $lon1);
            $a = sin($latDelta / 2) * sin($latDelta / 2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($lonDelta / 2) * sin($lonDelta / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            return $earthRadius * $c;
        };

        foreach ($geofences as $gf) {
            $gfLat = floatval($gf['latitude']);
            $gfLng = floatval($gf['longitude']);
            $gfRad = intval($gf['radius']);
            $locationName = $gf['location_name'];

            // 1. Cek Lokasi Pengambilan
            if ($pickup_lat && $pickup_lon) {
                $dist = $haversine($pickup_lat, $pickup_lon, $gfLat, $gfLng);
                if ($dist <= $gfRad) {
                    $is_blocked = true;
                    $block_reason = "Lokasi pengambilan kendaraan Anda berada di dalam zona blacklist: " . $locationName;
                    break;
                }
            }

            // 2. Cek Lokasi Pengembalian
            if ($return_lat && $return_lon) {
                $dist = $haversine($return_lat, $return_lon, $gfLat, $gfLng);
                if ($dist <= $gfRad) {
                    $is_blocked = true;
                    $block_reason = "Lokasi pengembalian kendaraan Anda berada di dalam zona blacklist: " . $locationName;
                    break;
                }
            }

            // 3. Cek Alamat Lengkap
            if ($address_lat && $address_lon) {
                $dist = $haversine($address_lat, $address_lon, $gfLat, $gfLng);
                if ($dist <= $gfRad) {
                    $is_blocked = true;
                    $block_reason = "Alamat lengkap Anda berada di dalam zona blacklist: " . $locationName;
                    break;
                }
            }
        }

        if ($is_blocked) {
            $_SESSION['flash'] = "Maaf, transaksi tidak dapat diproses karena: " . $block_reason;
            header('Location: index.php?module=Booking&action=checkout&car_id=' . $car_id);
            exit;
        }
        // -------------------------------------------------------------

        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, car_id, pickup_location, return_location, full_name, email, phone, address, start_date, end_date, addon_driver, total_price, status, created_at) VALUES (:user_id, :car_id, :pickup_location, :return_location, :full_name, :email, :phone, :address, :start_date, :end_date, :addon_driver, :total_price, :status, NOW())');

        $stmt->execute([
            'user_id' => $_SESSION['user_id'] ?? null,
            'car_id' => $car_id,
            'pickup_location' => $pickup_location,
            'return_location' => $return_location,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'addon_driver' => $addon_driver,
            'total_price' => $total_price,
            'status' => 'pending'
        ]);

        $bookingId = $pdo->lastInsertId();

        $_SESSION['booking_pending'] = [
            'id' => $bookingId,
            'full_name' => $full_name,
            'pickup_location' => $pickup_location,
            'return_location' => $return_location,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'addon_driver' => $addon_driver,
            'total_price' => $total_price,
            'car_id' => $car_id
        ];

        header('Location: index.php?module=Booking&action=pending&booking_id=' . $bookingId);
        exit;
    }

    public function success() {
        require_once __DIR__ . '/views/payment_success.php';
    }

    public function pending() {
        require_once __DIR__ . '/views/payment_pending.php';
    }

    public function rejected() {
        require_once __DIR__ . '/views/payment_rejected.php';
    }
}

