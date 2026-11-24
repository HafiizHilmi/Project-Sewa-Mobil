<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/inc/guard.php';
require_admin(true);

$role = $_SESSION['admin_role'] ?? 'staff';

require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

// ── Router Halaman ──────────────────────────────────────────────────────────
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'orders', 'cars', 'customers', 'verifications', 'settings'];
if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}

// ── Data Fetching Selektif (Hanya load query untuk halaman yang aktif) ──────
$verifications = [];
$orders = [];
$customers = [];

if ($page === 'verifications' || $page === 'dashboard') {
    $stmt = $pdo->query("
        SELECT id, name, email, verification_status, ktp_file, sim_file, created_at 
        FROM users 
        WHERE verification_status != 'unverified' 
        ORDER BY FIELD(verification_status, 'pending') DESC, id DESC
    ");
    $verifications = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

if ($page === 'orders') {
    $stmtOrders = $pdo->query("
        SELECT 
            b.id,
            b.full_name,
            b.email,
            b.phone,
            b.start_date,
            b.end_date,
            b.total_price,
            b.status,
            b.created_at,
            b.assigned_car_id,
            b.additional_cost,
            b.damage_image,
            b.damage_description,
            b.pickup_location,
            b.return_location,
            c.make,
            c.model,
            c.category,
            c.fuel_type,
            ac.number_plate AS assigned_plate
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        LEFT JOIN cars ac ON b.assigned_car_id = ac.id
        ORDER BY b.id DESC
    ");
    $orders = $stmtOrders ? $stmtOrders->fetchAll(PDO::FETCH_ASSOC) : [];
}

if ($page === 'customers') {
    $stmtCustomers = $pdo->query("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.phone,
            u.verification_status,
            u.created_at,
            COUNT(b.id) AS total_orders,
            MAX(b.start_date) AS last_order,
            MAX(b.address) AS address,
            COALESCE(SUM(COALESCE(b.total_price, 0) + COALESCE(b.additional_cost, 0)), 0) AS total_spent,
            COALESCE(SUM(CASE WHEN b.additional_cost > 0 OR b.damage_image IS NOT NULL OR (b.damage_description IS NOT NULL AND b.damage_description != '') THEN 1 ELSE 0 END), 0) AS total_damaged,
            GROUP_CONCAT(DISTINCT CONCAT(c.make, ' ', c.model, '::', COALESCE(ac.number_plate, 'Pending')) SEPARATOR '|') AS rented_cars
        FROM users u
        LEFT JOIN bookings b ON u.id = b.user_id
        LEFT JOIN cars c ON b.car_id = c.id
        LEFT JOIN cars ac ON b.assigned_car_id = ac.id
        WHERE u.role = 'user'
        GROUP BY u.id
        ORDER BY u.id DESC
    ");
    $customers = $stmtCustomers ? $stmtCustomers->fetchAll(PDO::FETCH_ASSOC) : [];
}

$flashData = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SewaMobil - Admin Panel</title>
  <meta name="description" content="Sistem Admin Dashboard SewaMobil — Customers, Settings, dan manajemen armada."/>

  <!-- Anti-FOUC Theme Initializer: Terapkan tema instan sebelum rendering untuk mencegah glitch saat ganti halaman di sidebar -->
  <script>
    (function () {
      var saved = localStorage.getItem('admin_theme');
      var isDark = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
      if (isDark) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    })();
  </script>

  <!-- Local Vendored Frameworks (0 CDN dependencies) -->
  <script src="assets/vendor/tailwind/tailwind.min.js"></script>
  <script defer src="assets/vendor/alpine/alpine.min.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css"/>

  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
        }
      }
    }
  </script>

  <link rel="stylesheet" href="assets/css/style.css"/>
  <link rel="stylesheet" href="assets/css/admin.css"/>
</head>

<body x-data="appData()" 
      x-cloak 
      class="font-sans antialiased bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

<?php if ($flashData): ?>
<script>
  window.SERVER_FLASH = <?= json_encode($flashData, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php endif; ?>

<div class="flex h-screen overflow-hidden">

  <div x-show="sidebarOpen"
       @click="sidebarOpen = false"
       class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
  </div>

  <?php include 'pages/sidebar.php'; ?>

  <div class="flex-1 flex flex-col overflow-hidden">

    <?php include 'pages/header.php'; ?>

    <main class="flex-1 overflow-hidden relative">
      <?php 
        $pageFile = __DIR__ . "/pages/{$page}.php";
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            include __DIR__ . "/pages/dashboard.php";
        }
      ?>
    </main>

  </div>
</div>

<script>
  window.SERVER_VERIFICATIONS = <?= json_encode($verifications, JSON_UNESCAPED_UNICODE) ?>;
  window.SERVER_ORDERS = <?= json_encode($orders, JSON_UNESCAPED_UNICODE) ?>;
  window.SERVER_CUSTOMERS = <?= json_encode($customers, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/js/admin-ui.js?v=<?= time() ?>"></script>
<script src="assets/js/app.js?v=<?= time() ?>"></script>

</body>
</html>
