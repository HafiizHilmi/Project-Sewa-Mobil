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
            $_SESSION['flash'] = "Lengkapi semua data booking terlebih dahulu.";
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

        // Cek apakah lokasi jemput/kembali masuk area blacklist (geofencing)
        $stmtBL = $pdo->query("SELECT * FROM blacklisted_locations");
        $geofences = $stmtBL->fetchAll(PDO::FETCH_ASSOC);

        $pickup_lat = isset($_POST['pickup_lat']) && $_POST['pickup_lat'] !== '' ? floatval($_POST['pickup_lat']) : null;
        $pickup_lon = isset($_POST['pickup_lon']) && $_POST['pickup_lon'] !== '' ? floatval($_POST['pickup_lon']) : null;

        $return_lat = isset($_POST['return_lat']) && $_POST['return_lat'] !== '' ? floatval($_POST['return_lat']) : null;
        $return_lon = isset($_POST['return_lon']) && $_POST['return_lon'] !== '' ? floatval($_POST['return_lon']) : null;

        $address_lat = isset($_POST['address_lat']) && $_POST['address_lat'] !== '' ? floatval($_POST['address_lat']) : null;
        $address_lon = isset($_POST['address_lon']) && $_POST['address_lon'] !== '' ? floatval($_POST['address_lon']) : null;

        $is_blocked = false;
        $block_reason = "";

        $pickup_location_lower = strtolower($pickup_location);
        $return_location_lower = strtolower($return_location);
        $address_lower = strtolower($address);

        $haversine = function($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371000; // dalam satuan meter
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
            $locationNameLower = strtolower(trim($locationName));

            // A. Cek kecocokan teks untuk input manual
            if ($locationNameLower !== '') {
                // Bersihkan angka dan kata "point" di bagian akhir nama lokasi
                $cleanGfName = preg_replace('/\s*\d+$/', '', $locationNameLower);
                $cleanGfName = preg_replace('/\s*point\s*$/', '', $cleanGfName);
                $cleanGfName = trim($cleanGfName);

                // Pecah jadi bagian kata kunci (contoh: "ntt - ntb" -> ["ntt", "ntb"])
                $parts = preg_split('/[\s\-,]+/', $cleanGfName);
                $parts = array_filter($parts, function($part) {
                    return strlen(trim($part)) >= 3;
                });
                $parts = array_map('trim', $parts);

                if (strlen($cleanGfName) >= 3 && !in_array($cleanGfName, $parts)) {
                    $parts[] = $cleanGfName;
                }

                // Cek apakah input user mengandung kata kunci blacklist
                $text_blocked = false;
                foreach ($parts as $part) {
                    if (strpos($pickup_location_lower, $part) !== false ||
                        strpos($return_location_lower, $part) !== false ||
                        strpos($address_lower, $part) !== false) {
                        $text_blocked = true;
                        break;
                    }
                }

                if ($text_blocked) {
                    $is_blocked = true;
                    $block_reason = "Lokasi yang Anda masukkan berada di wilayah tidak terjangkau ($locationName).";
                    break;
                }
            }

            // B. Cek radius geofencing berdasarkan koordinat
            // Validasi titik jemput
            if ($pickup_lat && $pickup_lon) {
                $dist = $haversine($pickup_lat, $pickup_lon, $gfLat, $gfLng);
                if ($dist <= $gfRad) {
                    $is_blocked = true;
                    $block_reason = "Lokasi pengambilan kendaraan Anda berada di wilayah tidak terjangkau ($locationName).";
                    break;
                }
            }

            // Validasi titik kembalikan
            if ($return_lat && $return_lon) {
                $dist = $haversine($return_lat, $return_lon, $gfLat, $gfLng);
                if ($dist <= $gfRad) {
                    $is_blocked = true;
                    $block_reason = "Lokasi pengembalian kendaraan Anda berada di wilayah tidak terjangkau ($locationName).";
                    break;
                }
            }

            // Validasi alamat lengkap user
            if ($address_lat && $address_lon) {
                $dist = $haversine($address_lat, $address_lon, $gfLat, $gfLng);
                if ($dist <= $gfRad) {
                    $is_blocked = true;
                    $block_reason = "Alamat lengkap Anda berada di wilayah tidak terjangkau ($locationName).";
                    break;
                }
            }
        }

        if ($is_blocked) {
            $_SESSION['flash'] = "Maaf, transaksi tidak dapat diproses karena: " . $block_reason;
            header('Location: index.php?module=Booking&action=checkout&car_id=' . $car_id);
            exit;
        }
        // Selesai cek geofencing

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

        // 1. Tambah kolom database baru kalau belum ada
        try {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_token VARCHAR(255) DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_url VARCHAR(255) DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_expiry TIMESTAMP NULL DEFAULT NULL");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN midtrans_order_id VARCHAR(100) DEFAULT NULL");
        } catch (Exception $e) {}

        // 2. Buat Snap Token Midtrans untuk pembayaran
        $payment_url = "";
        $snap = $this->getSnapToken($bookingId, $total_price, $full_name, $email, $phone);
        if ($snap) {
            $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $stmtUpdate = $pdo->prepare("UPDATE bookings SET payment_token = :token, payment_url = :url, payment_expiry = :expiry, midtrans_order_id = :midtrans_id WHERE id = :id");
            $stmtUpdate->execute([
                'token' => $snap['token'],
                'url' => $snap['url'],
                'expiry' => $expiryTime,
                'midtrans_id' => $snap['order_id'],
                'id' => $bookingId
            ]);
            $payment_url = $snap['url'];
        }

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

        // Ambil nama mobil lalu kirim notifikasi pembayaran tertunda
        $stmtCarDetails = $pdo->prepare("SELECT make, model FROM cars WHERE id = :id");
        $stmtCarDetails->execute(['id' => $car_id]);
        $carDetails = $stmtCarDetails->fetch(PDO::FETCH_ASSOC);
        $carName = trim(($carDetails['make'] ?? '') . ' ' . ($carDetails['model'] ?? ''));

        $msgWA = "Halo *$full_name*,\n\nTerima kasih telah memesan mobil *$carName* di SewaMobil SBY (Order ID: #$bookingId).\n\nSilakan selesaikan pembayaran via Midtrans menggunakan link berikut:\n$payment_url\n\nHarap lakukan pembayaran dalam waktu 15 menit sebelum pesanan otomatis dibatalkan.\n\nTerima kasih!";
        $this->sendNotification($phone, $email, "Menunggu Pembayaran - SewaMobil SBY", $msgWA);

        // Kirim notifikasi Telegram untuk pesanan baru masuk
        require_once __DIR__ . '/../../../include/api_keys.php';
        $totalFormatted = "Rp " . number_format($total_price, 0, ',', '.');
        $msgTelegram = "*Pesanan Baru Masuk!*\n\n"
                     . "*Order ID:* `#{$bookingId}`\n"
                     . "*Pelanggan:* {$full_name}\n"
                     . "*Mobil:* {$carName}\n"
                     . "*Total Sewa:* {$totalFormatted}\n"
                     . "*Status:* PENDING";
        sendTelegramNotification($msgTelegram);

        header('Location: index.php?module=Booking&action=pending&booking_id=' . $bookingId);
        exit;
    }

    public function success() {
        require_once __DIR__ . '/views/payment_success.php';
    }

    public function pending() {
        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();
        if ($pdo) {
            // 1. Beres-beres DB: Batalkan pesanan yang expired otomatis
            try {
                $pdo->exec("UPDATE bookings SET status = 'cancelled' WHERE status = 'pending' AND payment_expiry IS NOT NULL AND payment_expiry < NOW()");
            } catch (Exception $e) {}

            // 2. Cek status pembayaran ke Midtrans jika ada id transaksi
            $bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;
            if ($bookingId) {
                $stmt = $pdo->prepare("SELECT status, car_id, payment_token, midtrans_order_id, total_price, full_name, email, phone FROM bookings WHERE id = ? LIMIT 1");
                $stmt->execute([$bookingId]);
                $booking = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($booking && $booking['status'] === 'pending') {
                    // Bikin token pembayaran secara dinamis jika masih kosong
                    if (empty($booking['payment_token'])) {
                        $snap = $this->getSnapToken($bookingId, $booking['total_price'], $booking['full_name'], $booking['email'], $booking['phone']);
                        if ($snap) {
                            $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            $stmtUpdate = $pdo->prepare("UPDATE bookings SET payment_token = :token, payment_url = :url, payment_expiry = :expiry, midtrans_order_id = :midtrans_id WHERE id = :id");
                            $stmtUpdate->execute([
                                'token' => $snap['token'],
                                'url' => $snap['url'],
                                'expiry' => $expiryTime,
                                'midtrans_id' => $snap['order_id'],
                                'id' => $bookingId
                            ]);
                            $booking['payment_token'] = $snap['token'];
                            $booking['midtrans_order_id'] = $snap['order_id'];
                        }
                    }

                    // Tarik status terbaru dari Midtrans
                    if (!empty($booking['midtrans_order_id'])) {
                        $statusResult = $this->getMidtransStatus($booking['midtrans_order_id']);
                        if (isset($statusResult['transaction_status'])) {
                            $txStatus = $statusResult['transaction_status'];
                            if ($txStatus === 'settlement' || $txStatus === 'capture') {
                                // 1. Cari tipe mobil (mobil induk) yang dipesan
                                $stmtCar = $pdo->prepare("SELECT type_key, make, model FROM cars WHERE id = ?");
                                $stmtCar->execute([$booking['car_id']]);
                                $parentCar = $stmtCar->fetch(PDO::FETCH_ASSOC);
                                
                                $assigned_car_id = null;
                                $assigned_plate = 'TBA';
                                $carName = 'Mobil';
                                
                                if ($parentCar) {
                                    $carName = trim(($parentCar['make'] ?? '') . ' ' . ($parentCar['model'] ?? ''));
                                    // 2. Cari unit fisik mobil yang cocok dan tersedia
                                    $stmtChild = $pdo->prepare("SELECT id, number_plate FROM cars WHERE type_key = ? AND is_type = 0 AND available = 1 LIMIT 1");
                                    $stmtChild->execute([$parentCar['type_key']]);
                                    $assigned_car = $stmtChild->fetch(PDO::FETCH_ASSOC);
                                    if ($assigned_car) {
                                        $assigned_car_id = $assigned_car['id'];
                                        $assigned_plate = $assigned_car['number_plate'] ?? 'TBA';
                                    }
                                }

                                // 3. Konfirmasi booking dan pasangkan unit mobil
                                $stmtConfirm = $pdo->prepare("UPDATE bookings SET status = 'confirmed', assigned_car_id = ? WHERE id = ?");
                                $stmtConfirm->execute([$assigned_car_id, $bookingId]);

                                // 4. Tandai unit mobil tersebut jadi tidak tersedia
                                if ($assigned_car_id) {
                                    $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 0 WHERE id = ?");
                                    $stmtUpdateChild->execute([$assigned_car_id]);
                                }

                                // Kirim notifikasi pembayaran sukses beserta info plat nomor
                                $msgWA = "Halo *" . $booking['full_name'] . "*,\n\nPembayaran Anda untuk pesanan sewa mobil *$carName* (Order ID: #$bookingId) telah kami *TERIMA*.\n\nUnit kendaraan Anda telah dialokasikan dengan nomor plat: *" . $assigned_plate . "*.\n\nSilakan datang ke lokasi pengambilan sesuai jadwal. Terima kasih telah menyewa di SewaMobil SBY!";
                                $this->sendNotification($booking['phone'], $booking['email'], "Pembayaran Diterima - SewaMobil SBY", $msgWA);
                            } else if (in_array($txStatus, ['expire', 'cancel', 'deny'], true)) {
                                $stmtCancel = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
                                $stmtCancel->execute([$bookingId]);

                                // Kirim notifikasi Telegram untuk pesanan dibatalkan otomatis
                                require_once __DIR__ . '/../../../include/api_keys.php';
                                $msgTelegram = "❌ *Pesanan Dibatalkan Otomatis!*\n\n"
                                             . "*Order ID:* `#{$bookingId}`\n"
                                             . "*Pelanggan:* {$booking['full_name']}\n"
                                             . "*Alasan:* Transaksi Midtrans " . strtoupper($txStatus) . "\n"
                                             . "*Status:* CANCELLED";
                                sendTelegramNotification($msgTelegram);
                            }
                        }
                    }
                }
            }
        }
        require_once __DIR__ . '/views/payment_pending.php';
    }

    public function rejected() {
        require_once __DIR__ . '/views/payment_rejected.php';
    }

    // ==========================================
    // Generate Snap Token Pembayaran via Midtrans
    // ==========================================
    private function getSnapToken($bookingId, $totalPrice, $fullName, $email, $phone) {
        require_once __DIR__ . '/../../../include/api_keys.php';
        
        $serverKey = MIDTRANS_SERVER_KEY;
        $isProduction = defined('MIDTRANS_IS_PRODUCTION') ? MIDTRANS_IS_PRODUCTION : false;
        
        $url = $isProduction 
            ? "https://app.midtrans.com/snap/v1/transactions"
            : "https://app.sandbox.midtrans.com/snap/v1/transactions";
        
        $midtrans_order_id = 'RESRV-' . $bookingId . '-' . time();
        
        $params = [
            'transaction_details' => [
                'order_id' => $midtrans_order_id,
                'gross_amount' => (int) $totalPrice
            ],
            'customer_details' => [
                'first_name' => $fullName,
                'email' => $email,
                'phone' => $phone
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 15
            ]
        ];
        
        $json = json_encode($params);
        $payload = base64_encode($serverKey . ':');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $payload
        ]);
        
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            $_SESSION['midtrans_error'] = "cURL Error: " . $curl_error;
            return null;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['token'])) {
            unset($_SESSION['midtrans_error']);
            return [
                'token' => $result['token'],
                'url' => $result['redirect_url'],
                'order_id' => $midtrans_order_id
            ];
        }
        
        if (isset($result['error_messages'])) {
            $_SESSION['midtrans_error'] = implode(', ', $result['error_messages']);
        } else {
            $_SESSION['midtrans_error'] = "Respons tidak dikenal: " . $response;
        }
        
        return null;
    }

    // ==========================================
    // Tarik Status Transaksi Terbaru dari Midtrans
    // ==========================================
    private function getMidtransStatus($midtrans_order_id) {
        require_once __DIR__ . '/../../../include/api_keys.php';
        $serverKey = MIDTRANS_SERVER_KEY;
        $isProduction = defined('MIDTRANS_IS_PRODUCTION') ? MIDTRANS_IS_PRODUCTION : false;
        
        $url = $isProduction
            ? "https://api.midtrans.com/v2/" . $midtrans_order_id . "/status"
            : "https://api.sandbox.midtrans.com/v2/" . $midtrans_order_id . "/status";
        
        $payload = base64_encode($serverKey . ':');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . $payload
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    // ==========================================
    // Kirim Notifikasi ke User (Email & WhatsApp)
    // ==========================================
    private function sendNotification($phone, $email, $subject, $message) {
        // Ambil konfigurasi API keys
        require_once __DIR__ . '/../../../include/api_keys.php';

        // 1. Kirim notifikasi via email
        $headers = "From: no-reply@sewamobil.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        @mail($email, $subject, $message, $headers);

        // 2. Kirim notifikasi via WhatsApp Fonnte
        $token = FONNTE_TOKEN;
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 2, // Set timeout cepat biar loading web tetap kencang
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
          ),
          CURLOPT_HTTPHEADER => array(
            "Authorization: $token"
          ),
        ));
        @curl_exec($curl);
        @curl_close($curl);
    }
}