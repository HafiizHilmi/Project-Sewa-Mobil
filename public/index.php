<?php

session_start();

$module = isset($_GET['module']) ? ucfirst($_GET['module']) : 'Landing';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

$controllers = [    
    'Landing' => 'LandingController',
    'Auth'      => 'AuthController',
    'Booking'   => 'BookingController',
    'Dashboard' => 'DashboardController',
    'Verification'=> 'VerifyController',
    'Homepage' => 'HomePageController'
];

$controllerName = isset($controllers[$module]) ? $controllers[$module] : $module . 'Controller';

$controllerFile = __DIR__ . "/../app/modules/{$module}/{$controllerName}.php";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        if (method_exists($controller, $action)) {
            $controller->$action();
            
        } else {
            echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                    <h2 style='color:#e11d48;'>Error 404: Halaman Tidak Ditemukan</h2>
                    <p>Fungsi <b>{$action}()</b> tidak ditemukan di dalam <b>{$controllerName}</b>.</p>
                  </div>";
        }
    } else {
        echo "<h2 style='color:red; text-align:center;'>Error: Class {$controllerName} tidak ada di dalam filenya!</h2>";
    }
} else {
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h2 style='color:#e11d48;'>Error 404: Modul Tidak Ditemukan</h2>
            <p>Sistem mencari file di path ini: <br><code style='background:#eee; padding:5px;'>{$controllerFile}</code></p>
            <p>Pastikan nama folder dan file sudah benar.</p>
          </div>";
}