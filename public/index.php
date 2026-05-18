<?php
session_start();

// 1. Sanitasi input global
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];
$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];

// 2. Load dependensi utama
// require_once __DIR__ . '/../app/config/Database.php'; // Aktifkan jika butuh instance database langsung

// 3. Parse URL
// Mengambil base path (misal: /PHP/Project-Sewa-Mobil/public/)
$basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$requestUri = $_SERVER['REQUEST_URI'];

// Hilangkan basePath dari requestUri untuk mendapatkan routing yang bersih
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Pisahkan URI dari query string
$uriParts = explode('?', $requestUri);
$path = trim($uriParts[0], '/');
$segments = explode('/', $path);

// 4. Default Routing (Fallback)
$moduleName = 'Dashboard';
$controllerName = 'DashboardController';
$action = 'index';
$param = null;

// Routing logic sederhana
if (!empty($segments[0])) {
    $route = $segments[0];
    
    // Pemetaan custom berdasarkan spesifikasi backend.md
    switch (strtolower($route)) {
        case 'auth':
            $moduleName = 'Auth';
            $controllerName = 'AuthController';
            $action = !empty($segments[1]) ? $segments[1] : 'showLogin';
            break;
            
        case 'dashboard':
            $moduleName = 'Dashboard';
            $controllerName = 'DashboardController';
            $action = !empty($segments[1]) ? $segments[1] : 'index';
            break;
            
        case 'inventory':
            $moduleName = 'Inventory';
            $controllerName = 'CarController';
            $action = !empty($segments[1]) ? $segments[1] : 'adminList';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'catalog':
            $moduleName = 'Inventory';
            $controllerName = 'CarController';
            $action = !empty($segments[1]) ? $segments[1] : 'userCatalog';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'booking':
            $moduleName = 'Booking';
            $controllerName = 'BookingController';
            $action = !empty($segments[1]) ? $segments[1] : 'userHistory';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'verify':
            $moduleName = 'Verification';
            $controllerName = 'VerifyController';
            $action = !empty($segments[1]) ? $segments[1] : 'showUpload';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'maintenance':
            $moduleName = 'Maintenance';
            $controllerName = 'MaintenanceController';
            $action = !empty($segments[1]) ? $segments[1] : 'showSchedule';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'payment':
            $moduleName = 'PaymentDummy';
            $controllerName = 'PaymentController';
            $action = !empty($segments[1]) ? $segments[1] : 'showSimulate';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'tracking':
            $moduleName = 'TrackingDummy';
            $controllerName = 'TrackingController';
            $action = !empty($segments[1]) ? $segments[1] : 'adminMap';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        case 'notification':
            $moduleName = 'NotificationDummy';
            $controllerName = 'NotificationController';
            $action = !empty($segments[1]) ? $segments[1] : 'showPopup';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
            
        default:
            // Fallback ke struktur konvensi default
            $moduleName = ucfirst($segments[0]);
            $controllerName = $moduleName . 'Controller';
            $action = !empty($segments[1]) ? $segments[1] : 'index';
            $param = !empty($segments[2]) ? $segments[2] : null;
            break;
    }
}

// 5. Dispatch
$controllerFile = __DIR__ . "/../app/modules/{$moduleName}/{$controllerName}.php";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        // Pengecekan HTTP Method untuk membedakan GET/POST (contoh showLogin vs processLogin)
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $actualAction = $action;
        
        // Coba periksa metode yang lebih spesifik berdasarkan HTTP Method jika ada
        if ($httpMethod === 'POST' && method_exists($controller, 'process' . ucfirst($action))) {
            $actualAction = 'process' . ucfirst($action);
        } elseif ($httpMethod === 'GET' && method_exists($controller, 'show' . ucfirst($action))) {
            $actualAction = 'show' . ucfirst($action);
        }
        
        if (method_exists($controller, $actualAction)) {
            if ($param !== null) {
                $controller->$actualAction($param);
            } else {
                $controller->$actualAction();
            }
        } else {
            http_response_code(404);
            echo "<h2 style='color:#e11d48; text-align:center;'>Error 404: Method '{$actualAction}()' tidak ditemukan di '{$controllerName}'.</h2>";
        }
    } else {
        http_response_code(500);
        echo "<h2 style='color:#e11d48; text-align:center;'>Error 500: Class '{$controllerName}' tidak didefinisikan.</h2>";
    }
} else {
    http_response_code(404);
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h2 style='color:#e11d48;'>Error 404: Modul Tidak Ditemukan</h2>
            <p>Sistem mencari file controller di path ini: <br><code style='background:#eee; padding:5px;'>{$controllerFile}</code></p>
          </div>";
}
?>