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
}
