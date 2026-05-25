<?php
// admin/admin_action.php
session_start();
require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 1. TAMBAH ADMIN
    if ($_POST['action'] === 'add_admin') {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') die("Akses Ditolak!");
        
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password']; 
        $role = $_POST['role'];

        $checkEmail = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->rowCount() > 0) {
            $_SESSION['flash_msg'] = "Gagal! Email tersebut sudah digunakan.";
            header("Location: index.php?page=settings");
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO admins (nama, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nama, $email, $password, $role])) {
            $_SESSION['flash_msg'] = "Berhasil! Admin/Staff baru ditambahkan.";
            header("Location: index.php?page=settings");
            exit();
        }
    }
    
    // 2. HAPUS ADMIN
    if ($_POST['action'] === 'delete_admin') {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') die("Akses Ditolak!");
        
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['flash_msg'] = "Akun staff berhasil dihapus!";
            header("Location: index.php?page=settings");
            exit();
        }
    }

    // 3. EDIT ADMIN
    if ($_POST['action'] === 'edit_admin') {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') die("Akses Ditolak!");

        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'];

        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE admins SET nama=?, email=?, role=?, password=? WHERE id=?");
            $stmt->execute([$nama, $email, $role, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET nama=?, email=?, role=? WHERE id=?");
            $stmt->execute([$nama, $email, $role, $id]);
        }
        
        $_SESSION['flash_msg'] = "Data admin berhasil diperbarui!";
        header("Location: index.php?page=settings");
        exit();
    }

    // 4. GANTI PASSWORD
    if ($_POST['action'] === 'change_password') {
        $admin_id = $_SESSION['admin_id'];
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch();

        if ($user && $old_pass === $user['password']) {
            $update = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $update->execute([$new_pass, $admin_id]);
            $_SESSION['flash_msg'] = "Password berhasil diubah!";
        } else {
            $_SESSION['flash_msg'] = "Gagal! Password lama yang Anda masukkan salah.";
        }
        header("Location: index.php?page=settings");
        exit();
    }
}
?>