<?php

class AuthController {

    public function login() {
        require_once __DIR__ . '/views/login.php';
    }

    public function register() {
        require_once __DIR__ . '/views/register.php';
    }

    public function processLogin() {
        // Process login form securely using PDO
        require_once __DIR__ . '/../../../include/db_config.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        // CSRF check
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['flash'] = 'Invalid CSRF token.';
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['flash'] = 'Email dan password wajib diisi.';
            header('Location: index.php?module=Auth&action=login');
            exit;
        }

        $pdo = getPDO();
        if (!$pdo) {
            $_SESSION['flash'] = 'Database connection error.';
            header('Location: index.php?module=Auth&action=login');
            exit;
        }
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: index.php?module=Homepage&action=index');
            exit;
        } else {
            $_SESSION['flash'] = 'Email atau password salah.';
            header('Location: index.php?module=Auth&action=login');
            exit;
        }
    }

    public function processRegister() {
        // Process register form securely using PDO
        require_once __DIR__ . '/../../../include/db_config.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['flash'] = 'Invalid CSRF token.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($name === '' || $email === '' || $password === '' || $password_confirm === '') {
            $_SESSION['flash'] = 'Semua field wajib diisi.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = 'Email tidak valid.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        if ($password !== $password_confirm) {
            $_SESSION['flash'] = 'Password dan konfirmasi tidak cocok.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['flash'] = 'Password minimal 6 karakter.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        $pdo = getPDO();
        if (!$pdo) {
            $_SESSION['flash'] = 'Database connection error.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

        // Check existing email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = 'Email sudah terdaftar.';
            header('Location: index.php?module=Auth&action=register');
            exit;
        }

      
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare('INSERT INTO users (name, email, phone, password, role, created_at) VALUES (:name, :email, :phone, :password, :role, NOW())');


        $insert->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password_hash,
            'role' => 'user'
        ]);

        $newId = $pdo->lastInsertId();
        // ... baris kode redirect ...
            }
        }
        ?>