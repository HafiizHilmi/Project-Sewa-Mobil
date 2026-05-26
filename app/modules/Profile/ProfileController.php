<?php

class ProfileController {

    public function index() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ambil riwayat pemesanan/penyewaan untuk user ini
        $stmtBookings = $pdo->prepare("
            SELECT 
                b.id,
                b.pickup_location,
                b.return_location,
                b.start_date,
                b.end_date,
                b.total_price,
                b.status,
                b.created_at,
                b.additional_cost,
                b.damage_image,
                b.damage_description,
                c.make,
                c.model,
                c.category,
                c.fuel_type,
                c.price_per_day,
                COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), c.image) AS image,
                ac.number_plate AS assigned_plate
            FROM bookings b
            JOIN cars c ON b.car_id = c.id
            LEFT JOIN cars ac ON b.assigned_car_id = ac.id
            WHERE b.user_id = :user_id
            ORDER BY b.id DESC
        ");
        $stmtBookings->execute(['user_id' => $_SESSION['user_id']]);
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        $docsUnlocked = isset($_SESSION['docs_unlocked']) && $_SESSION['docs_unlocked'] === true;

        require_once __DIR__ . '/views/index.php';
    }

    public function upload() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=Profile&action=index');
            exit;
        }

        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();

        $uploadDir = __DIR__ . '/../../../data_sensitive/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedExts = ['jpg', 'jpeg', 'png'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $ktpName = null;
        $simName = null;
        $error = null;

        if (isset($_FILES['ktp_file']) && $_FILES['ktp_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['ktp_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                $error = "Ekstensi KTP tidak valid. Gunakan JPG, JPEG, atau PNG.";
            } elseif ($_FILES['ktp_file']['size'] > $maxSize) {
                $error = "Ukuran file KTP maksimal 2MB.";
            } else {
                $ktpName = 'ktp_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($_FILES['ktp_file']['tmp_name'], $uploadDir . $ktpName)) {
                    $error = "Gagal memindahkan file KTP.";
                }
            }
        } else {
            $error = "KTP wajib diunggah.";
        }

        if (!$error && isset($_FILES['sim_file']) && $_FILES['sim_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['sim_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                $error = "Ekstensi SIM tidak valid. Gunakan JPG, JPEG, atau PNG.";
            } elseif ($_FILES['sim_file']['size'] > $maxSize) {
                $error = "Ukuran file SIM maksimal 2MB.";
            } else {
                $simName = 'sim_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($_FILES['sim_file']['tmp_name'], $uploadDir . $simName)) {
                    $error = "Gagal memindahkan file SIM.";
                }
            }
        } elseif (!$error) {
            $error = "SIM wajib diunggah.";
        }

        if ($error) {
            $_SESSION['flash_error'] = $error;
        } else {
            $stmt = $pdo->prepare("UPDATE users SET verification_status = 'pending', ktp_file = :ktp, sim_file = :sim WHERE id = :id");
            if ($stmt->execute([
                'ktp' => $ktpName,
                'sim' => $simName,
                'id' => $_SESSION['user_id']
            ])) {
                $_SESSION['flash_success'] = "Dokumen berhasil diunggah. Menunggu verifikasi admin.";
            } else {
                $_SESSION['flash_error'] = "Terjadi kesalahan pada saat menyimpan ke database.";
            }
        }

        header('Location: index.php?module=Profile&action=index');
        exit;
    }

    public function verifyDocsPassword() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=Profile&action=index');
            exit;
        }

        require_once __DIR__ . '/../../../include/db_config.php';
        $pdo = getPDO();

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $password = $_POST['password'] ?? '';
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['docs_unlocked'] = true;
            $_SESSION['flash_success'] = "Dokumen identitas berhasil dibuka.";
        } else {
            $_SESSION['flash_error'] = "Password salah! Tidak dapat mengakses dokumen.";
        }

        header('Location: index.php?module=Profile&action=index#dokumen');
        exit;
    }
}
