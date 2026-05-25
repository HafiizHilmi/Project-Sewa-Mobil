<?php
session_start();
require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses ditolak.");
}

$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null;

if (in_array($action, ['bulk_suspend', 'bulk_delete'])) {
    try {
        $user_ids_str = $_POST['user_ids'] ?? '';
        if (empty($user_ids_str)) {
            throw new Exception("Tidak ada pelanggan yang dipilih.");
        }
        
        $ids = array_filter(array_map('intval', explode(',', $user_ids_str)));
        if (empty($ids)) {
            throw new Exception("ID pelanggan tidak valid.");
        }

        if ($action === 'bulk_suspend') {
            $in_query = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE users SET verification_status = 'rejected' WHERE id IN ($in_query) AND role = 'user'");
            $stmt->execute($ids);

            echo "<script>
                    alert('Berhasil men-suspend " . count($ids) . " pelanggan.');
                    window.location.href = 'index.php?page=customers';
                  </script>";
            exit;
        } elseif ($action === 'bulk_delete') {
            $in_query = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($in_query) AND role = 'user'");
            $stmt->execute($ids);

            echo "<script>
                    alert('Berhasil menghapus " . count($ids) . " pelanggan secara permanen.');
                    window.location.href = 'index.php?page=customers';
                  </script>";
            exit;
        }
    } catch (Exception $e) {
        echo "<script>
                alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
        exit;
    }
}

if ($user_id && in_array($action, ['edit', 'suspend', 'unsuspend', 'delete'])) {
    try {
        if ($action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if ($name === '' || $email === '' || $phone === '') {
                throw new Exception("Lengkapi semua kolom input.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email tidak valid.");
            }

            $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':id' => $user_id
            ]);

            echo "<script>
                    alert('Data pelanggan berhasil diubah.');
                    window.location.href = 'index.php?page=customers';
                  </script>";
            exit;

        } elseif ($action === 'suspend') {
            // Set verification_status to rejected for suspend
            $stmt = $pdo->prepare("UPDATE users SET verification_status = 'rejected' WHERE id = :id");
            $stmt->execute([':id' => $user_id]);

            echo "<script>
                    alert('Pelanggan berhasil di-suspend.');
                    window.location.href = 'index.php?page=customers';
                  </script>";
            exit;

        } elseif ($action === 'unsuspend') {
            // Set verification_status back to verified
            $stmt = $pdo->prepare("UPDATE users SET verification_status = 'verified' WHERE id = :id");
            $stmt->execute([':id' => $user_id]);

            echo "<script>
                    alert('Akun pelanggan berhasil diaktifkan kembali.');
                    window.location.href = 'index.php?page=customers';
                  </script>";
            exit;

        } elseif ($action === 'delete') {
            // Delete customer from users table
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'user'");
            $stmt->execute([':id' => $user_id]);

            echo "<script>
                    alert('Pelanggan berhasil dihapus.');
                    window.location.href = 'index.php?page=customers';
                  </script>";
            exit;
        }

    } catch (Exception $e) {
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
