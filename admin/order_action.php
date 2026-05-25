<?php
session_start();
require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ditolak.");
}

$booking_id = $_POST['booking_id'] ?? null;
$action = $_POST['action'] ?? null; // 'accept' or 'reject'

if ($booking_id && in_array($action, ['accept', 'reject', 'complete'])) {
    try {
        $pdo->beginTransaction();

        // 1. Get the current booking status and car_id
        $stmtGet = $pdo->prepare("SELECT status, car_id FROM bookings WHERE id = :id");
        $stmtGet->execute([':id' => $booking_id]);
        $booking = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("Pemesanan tidak ditemukan.");
        }

        $currentStatus = $booking['status'];
        $car_id = $booking['car_id'];

        // 2. Get the type_key of the car
        $stmtCar = $pdo->prepare("SELECT type_key FROM cars WHERE id = :id");
        $stmtCar->execute([':id' => $car_id]);
        $car = $stmtCar->fetch(PDO::FETCH_ASSOC);
        $type_key = $car ? $car['type_key'] : null;

        if ($action === 'accept') {
            $child_id = null;
            // If it wasn't already confirmed, decrement stock
            if ($currentStatus !== 'confirmed' && $type_key) {
                // Find one available child unit (is_type = 0, available = 1)
                $stmtChild = $pdo->prepare("SELECT id FROM cars WHERE type_key = :type_key AND is_type = 0 AND available = 1 LIMIT 1");
                $stmtChild->execute([':type_key' => $type_key]);
                $child = $stmtChild->fetch(PDO::FETCH_ASSOC);

                if ($child) {
                    $child_id = $child['id'];
                    // Update child available to 0
                    $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 0 WHERE id = :child_id");
                    $stmtUpdateChild->execute([':child_id' => $child_id]);
                }
            }

            // Update status to confirmed and set assigned_car_id
            $stmtUpdateStatus = $pdo->prepare("UPDATE bookings SET status = 'confirmed'" . ($child_id ? ", assigned_car_id = :assigned_car_id" : "") . " WHERE id = :id");
            $params = [':id' => $booking_id];
            if ($child_id) {
                $params[':assigned_car_id'] = $child_id;
            }
            $stmtUpdateStatus->execute($params);

        } elseif ($action === 'reject') {
            // Find if there is an assigned car
            $stmtGetAssigned = $pdo->prepare("SELECT assigned_car_id FROM bookings WHERE id = :id");
            $stmtGetAssigned->execute([':id' => $booking_id]);
            $assigned_row = $stmtGetAssigned->fetch(PDO::FETCH_ASSOC);
            $assigned_car_id = $assigned_row ? $assigned_row['assigned_car_id'] : null;

            // Update status to cancelled and clear assigned_car_id
            $stmtUpdateStatus = $pdo->prepare("UPDATE bookings SET status = 'cancelled', assigned_car_id = NULL WHERE id = :id");
            $stmtUpdateStatus->execute([':id' => $booking_id]);

            // If there was an assigned car, set it to available = 1
            if ($assigned_car_id) {
                $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 1 WHERE id = :child_id");
                $stmtUpdateChild->execute([':child_id' => $assigned_car_id]);
            }
        } elseif ($action === 'complete') {
            $additional_cost = 0.00;
            $damage_image = null;
            $damage_description = null;

            $skip_damage = $_POST['skip_damage'] ?? 0;
            if (!$skip_damage) {
                $additional_cost = floatval($_POST['additional_cost'] ?? 0);
                $damage_description = trim($_POST['damage_description'] ?? '');

                // Handle damage image upload
                if (isset($_FILES['foto_kerusakan']) && $_FILES['foto_kerusakan']['error'] == 0) {
                    $target_dir = "../public/assets/images/damages/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $damage_image = time() . "_" . basename($_FILES["foto_kerusakan"]["name"]);
                    $target_file = $target_dir . $damage_image;
                    move_uploaded_file($_FILES["foto_kerusakan"]["tmp_name"], $target_file);
                }
            }

            // Find if there is an assigned car
            $stmtGetAssigned = $pdo->prepare("SELECT assigned_car_id FROM bookings WHERE id = :id");
            $stmtGetAssigned->execute([':id' => $booking_id]);
            $assigned_row = $stmtGetAssigned->fetch(PDO::FETCH_ASSOC);
            $assigned_car_id = $assigned_row ? $assigned_row['assigned_car_id'] : null;

            // Update booking status to completed and store damage info
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

            // Release the assigned car back to available = 1
            if ($assigned_car_id) {
                $stmtUpdateChild = $pdo->prepare("UPDATE cars SET available = 1 WHERE id = :child_id");
                $stmtUpdateChild->execute([':child_id' => $assigned_car_id]);
            }
        }

        $pdo->commit();

        echo "<script>
                alert('Berhasil: Status pesanan berhasil diubah.');
                window.location.href = 'index.php';
              </script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>
                alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('Data tidak valid.');
            window.history.back();
          </script>";
    exit;
}
