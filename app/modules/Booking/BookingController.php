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
            header('Location: index.php?module=Booking&action=checkout');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = 'Email tidak valid.';
            header('Location: index.php?module=Booking&action=checkout');
            exit;
        }

        $pdo = getPDO();
        if (!$pdo) {
            $_SESSION['flash'] = 'Database connection error.';
            header('Location: index.php?module=Booking&action=checkout');
            exit;
        }

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

        $_SESSION['booking_success'] = [
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

        header('Location: index.php?module=Booking&action=success');
        exit;
    }

    public function success() {
        require_once __DIR__ . '/views/payment_success.php';
    }
}

