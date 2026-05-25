<?php
// admin/admin_action.php
session_start();
require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // -------------------------------------------------------------------
    // 1. TAMBAH ADMIN (Hanya Superuser)
    // -------------------------------------------------------------------
    if ($_POST['action'] === 'add_admin') {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') die("Akses Ditolak!");
        
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password']; 
        $role = $_POST['role'];

        $checkEmail = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->rowCount() > 0) {
            echo "<script>alert('Gagal! Email tersebut sudah digunakan.'); window.history.back();</script>";
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO admins (nama, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nama, $email, $password, $role])) {
            echo "<script>alert('Berhasil! Admin/Staff baru ditambahkan.'); window.location.href='index.php';</script>";
        }
        exit();
    }
    
    // -------------------------------------------------------------------
    // 2. HAPUS ADMIN (Hanya Superuser)
    // -------------------------------------------------------------------
    if ($_POST['action'] === 'delete_admin') {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') die("Akses Ditolak!");
        
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo "<script>alert('Akun staff berhasil dihapus!'); window.location.href='index.php?page=settings';</script>";
        }
        exit();
    }

    // -------------------------------------------------------------------
    // 3. EDIT ADMIN (Hanya Superuser)
    // -------------------------------------------------------------------
    if ($_POST['action'] === 'edit_admin') {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') die("Akses Ditolak!");

        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password']; // Opsional

        // Cek jika password diisi, maka update password juga
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE admins SET nama=?, email=?, role=?, password=? WHERE id=?");
            $stmt->execute([$nama, $email, $role, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET nama=?, email=?, role=? WHERE id=?");
            $stmt->execute([$nama, $email, $role, $id]);
        }
        
        echo "<script>alert('Data admin berhasil diperbarui!'); window.location.href='index.php?page=settings';</script>";
        exit();
    }

    // -------------------------------------------------------------------
    // 4. GANTI PASSWORD (Untuk Semua Role yang sedang Login)
    // -------------------------------------------------------------------
    if ($_POST['action'] === 'change_password') {
        $admin_id = $_SESSION['admin_id'];
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        // Ambil password lama dari database
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch();

        // Validasi kecocokan password lama
        if ($user && $old_pass === $user['password']) {
            $update = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->execute([$new_pass, $admin_id]);
            echo "<script>alert('Password berhasil diubah!'); window.location.href='index.php?page=settings';</script>";
        } else {
            echo "<script>alert('Gagal! Password lama yang Anda masukkan salah.'); window.history.back();</script>";
        }
        exit();
    }
}
?>