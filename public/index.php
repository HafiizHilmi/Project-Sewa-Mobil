<?php
<<<<<<< HEAD

session_start();

$module = isset($_GET['module']) ? ucfirst($_GET['module']) : 'Inventory';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

=======
// public/index.php

// 1. Mulai session (Penting untuk fitur Login & Notifikasi nantinya)
session_start();

// 2. Tangkap parameter URL (?module=... &action=...)
// Jika URL kosong (hanya localhost/.../public/), maka arahkan ke Modul Inventory, Action index
$module = isset($_GET['module']) ? ucfirst($_GET['module']) : 'Inventory';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// 3. Pemetaan Nama Controller (Routing Map)
// Karena nama foldernya 'Inventory' tapi nama filenya 'CarController', kita petakan di sini:
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
$controllers = [
    'Inventory' => 'CarController',
    'Auth'      => 'AuthController',
    'Booking'   => 'BookingController',
    'Dashboard' => 'DashboardController',
    'Verification'=> 'VerifyController'
];

<<<<<<< HEAD
$controllerName = isset($controllers[$module]) ? $controllers[$module] : $module . 'Controller';

$controllerFile = __DIR__ . "/../app/modules/{$module}/{$controllerName}.php";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        if (method_exists($controller, $action)) {
            $controller->$action();
            
        } else {
=======
// Cari nama controller yang bener, kalo gaada di list, pake format default (NamaModul + Controller)
$controllerName = isset($controllers[$module]) ? $controllers[$module] : $module . 'Controller';

// 4. Bentuk jalur (path) menuju file Controller di folder /app/modules/
// __DIR__ adalah lokasi folder 'public' saat ini, abis itu mundur selangkah pake '/../'
$controllerFile = __DIR__ . "/../app/modules/{$module}/{$controllerName}.php";

// 5. Eksekusi File
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Cek apakah Class ada di dalam file tsb
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        // Cek apakah action ada di dalam class tsb
        if (method_exists($controller, $action)) {
            // Jalanin fungsinya
            $controller->$action();
        } else {
            // error kalo fungsinya belum/salah
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
            echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                    <h2 style='color:#e11d48;'>Error 404: Halaman Tidak Ditemukan</h2>
                    <p>Fungsi <b>{$action}()</b> tidak ditemukan di dalam <b>{$controllerName}</b>.</p>
                  </div>";
        }
    } else {
        echo "<h2 style='color:red; text-align:center;'>Error: Class {$controllerName} tidak ada di dalam filenya!</h2>";
    }
} else {
<<<<<<< HEAD
=======
    // error kalo file folder blm dibuat
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h2 style='color:#e11d48;'>Error 404: Modul Tidak Ditemukan</h2>
            <p>Sistem mencari file di path ini: <br><code style='background:#eee; padding:5px;'>{$controllerFile}</code></p>
            <p>Pastikan nama folder dan file sudah benar.</p>
          </div>";
}