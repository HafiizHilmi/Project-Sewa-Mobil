<?php
session_start();
require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_msg'] = "Akses ditolak.";
    header("Location: index.php?page=orders");
    exit();
}

// ==============================================================
// FUNGSI PENGIRIM NOTIFIKASI (EMAIL & WHATSAPP)
// ==============================================================
function sendNotifAlert($phone, $email, $subject, $message) {
    // Panggil file rahasia
    require_once __DIR__ . '/../include/api_keys.php';

    // 1. Kirim Email
    $headers = "From: no-reply@sewamobil.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($email, $subject, $message, $headers);

    // 2. Kirim WhatsApp (Fonnte)
    $token = FONNTE_TOKEN; // <-- Ambil token dari file api_keys.php
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 2,
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
// ==============================================================


$booking_id = $_POST['booking_id'] ?? null;
$action = $_POST['action'] ?? null; // 'accept' or 'reject'

if ($booking_id && in_array($action, ['accept', 'reject', 'complete'])) {
    try {
        $pdo->beginTransaction();

        // Mengambil data pelanggan (email, no HP, dan nama) agar bisa dikirimi pesan
        $stmtGet = $pdo->prepare("SELECT status, car_id, full_name, email, phone FROM bookings WHERE id = :id");
        $stmtGet->execute([':id' => $booking_id]);
        $booking = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("Pemesanan tidak ditemukan.");
        }

        $currentStatus = $booking['status'];
        $car_id = $booking['car_id'];

        // Mengambil nama mobil untuk dicantumkan di pesan WA
        $stmtCar = $pdo->prepare("SELECT type_key, make, model FROM cars WHERE id = :id");
        $stmtCar->execute([':id' => $car_id]);
        $car = $stmtCar->fetch(PDO::FETCH_ASSOC);
        $carName = trim(($car['make'] ?? '') . ' ' . ($car['model'] ?? ''));

        if ($action === 'accept') {
            if ($currentStatus !== 'pending') {
                throw new Exception("Pemesanan ini tidak dapat diterima karena statusnya bukan pending.");
            }

            // 1. Cari mobil anak (is_type=0) yang sejenis (type_key sama) yang tersedia
            $stmtChild = $pdo->prepare("SELECT id FROM cars WHERE type_key = :type_key AND is_type = 0 AND available = 1 LIMIT 1");
            $stmtChild->execute([':type_key' => $car['type_key']]);
            $assigned_car = $stmtChild->fetch(PDO::FETCH_ASSOC);

            if (!$assigned_car) {
                throw new Exception("Tidak ada unit tersedia untuk jenis mobil ini.");
            }

            $assigned_car_id = $assigned_car['id'];

            // 2. Update status booking menjadi confirmed & simpan assigned_car_id
            $stmtUpdate = $pdo->prepare("UPDATE bookings SET status = 'confirmed', assigned_car_id = :assigned_id WHERE id = :id");
            $stmtUpdate->execute([':assigned_id' => $assigned_car_id, ':id' => $booking_id]);

            // 3. Ubah status available mobil tersebut menjadi 0 (tersewa)
            $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 0 WHERE id = :child_id");
            $stmtUpdateChild->execute([':child_id' => $assigned_car_id]);

            // --- EKSEKUSI NOTIFIKASI APPROVED (DISETUJUI) ---
            $msgWA = "Halo *{$booking['full_name']}*,\n\nKabar baik! Pesanan sewa mobil *$carName* Anda (Order ID: #$booking_id) telah *DISETUJUI* oleh Admin.\n\nSilakan cek status di Profil Anda dan segera siapkan pembayaran agar unit dapat segera kami serahkan.\n\nTerima kasih!";
            sendNotifAlert($booking['phone'], $booking['email'], "Pesanan Disetujui - SewaMobil SBY", $msgWA);
            // ------------------------------------------------

        } elseif ($action === 'reject') {
            if ($currentStatus === 'completed') {
                throw new Exception("Pemesanan sudah selesai dan tidak dapat dibatalkan.");
            }

            // Jika status sebelumnya confirmed, kembalikan mobil ke available
            if ($currentStatus === 'confirmed') {
                $stmtGetAssigned = $pdo->prepare("SELECT assigned_car_id FROM bookings WHERE id = :id");
                $stmtGetAssigned->execute([':id' => $booking_id]);
                $assigned_car_id = $stmtGetAssigned->fetchColumn();

                if ($assigned_car_id) {
                    $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 1 WHERE id = :child_id");
                    $stmtUpdateChild->execute([':child_id' => $assigned_car_id]);
                }
            }

            // Update status booking menjadi cancelled/rejected
            $stmtUpdate = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id");
            $stmtUpdate->execute([':id' => $booking_id]);

            // --- EKSEKUSI NOTIFIKASI REJECTED (DITOLAK) ---
            $msgWA = "Halo *{$booking['full_name']}*,\n\nMohon maaf, pesanan sewa mobil *$carName* Anda (Order ID: #$booking_id) harus kami *TOLAK/BATALKAN* untuk saat ini karena satu dan lain hal.\n\nSilakan hubungi Admin untuk informasi lebih lanjut atau lakukan pemesanan ulang dengan jadwal/unit yang berbeda.\n\nTerima kasih.";
            sendNotifAlert($booking['phone'], $booking['email'], "Pesanan Dibatalkan - SewaMobil SBY", $msgWA);
            // ----------------------------------------------

        } elseif ($action === 'complete') {
            if ($currentStatus !== 'confirmed') {
                throw new Exception("Pemesanan ini tidak dapat diselesaikan.");
            }

            $additional_cost = floatval($_POST['additional_cost'] ?? 0);
            $damage_description = trim($_POST['damage_description'] ?? '');
            $damage_image = null;

            if (isset($_FILES['foto_kerusakan']) && $_FILES['foto_kerusakan']['error'] == 0) {
                $target_dir = "../public/assets/images/damages/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $damage_image = time() . "_" . basename($_FILES["foto_kerusakan"]["name"]);
                move_uploaded_file($_FILES["foto_kerusakan"]["tmp_name"], $target_dir . $damage_image);
            }

            $stmtGetAssigned = $pdo->prepare("SELECT assigned_car_id FROM bookings WHERE id = :id");
            $stmtGetAssigned->execute([':id' => $booking_id]);
            $assigned_car_id = $stmtGetAssigned->fetchColumn();

            $stmtUpdateStatus = $pdo->prepare("
                UPDATE bookings 
                SET status = 'completed', 
                    additional_cost = :additional_cost, 
                    damage_image = :damage_image, 
                    damage_description = :damage_description 
                WHERE id = :id
            ");
            $stmtUpdateStatus->execute([
                ':additional_cost' => $additional_cost,
                ':damage_image' => $damage_image,
                ':damage_description' => $damage_description,
                ':id' => $booking_id
            ]);

            if ($assigned_car_id) {
                $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 1 WHERE id = :child_id");
                $stmtUpdateChild->execute([':child_id' => $assigned_car_id]);
            }
        }

        $pdo->commit();

        $_SESSION['flash_msg'] = "Berhasil: Status pesanan berhasil diubah.";
        header("Location: index.php?page=orders");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash_msg'] = "Terjadi kesalahan: " . addslashes($e->getMessage());
        header("Location: index.php?page=orders");
        exit();
    }
} else {
    $_SESSION['flash_msg'] = "Aksi tidak valid.";
    header("Location: index.php?page=orders");
    exit();
}
?>