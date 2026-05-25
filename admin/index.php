<?php
session_start();

// --- KUNCI HALAMAN UTAMA ADMIN ---
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['admin_role'] ?? 'staff'; // Ambil role hak akses
// ---------------------------------

require_once __DIR__ . '/../include/db_config.php';
$pdo = getPDO();

// Ensure assigned_car_id column exists in bookings table
$checkAssigned = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'assigned_car_id'");
if ($checkAssigned->rowCount() === 0) {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN assigned_car_id INT UNSIGNED NULL");
}

// Ensure additional columns exist in bookings table
$checkAdditional = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'additional_cost'");
if ($checkAdditional->rowCount() === 0) {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN additional_cost DECIMAL(12, 2) DEFAULT 0.00");
}
$checkDamageImg = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'damage_image'");
if ($checkDamageImg->rowCount() === 0) {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN damage_image VARCHAR(255) NULL");
}
$checkDamageDesc = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'damage_description'");
if ($checkDamageDesc->rowCount() === 0) {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN damage_description TEXT NULL");
}

// Ambil data user yang memiliki status verifikasi selain unverified (pending, verified, rejected)
$stmt = $pdo->query("SELECT id, name, email, verification_status, ktp_file, sim_file, created_at FROM users WHERE verification_status != 'unverified' ORDER BY FIELD(verification_status, 'pending') DESC, id DESC");
$verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data bookings/orders
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
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

// Ambil data pelanggan (customers)
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
$customers = $stmtCustomers->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sewa Mobil SBY — Admin Panel</title>
  <meta name="description" content="Sistem Admin Dashboard Sewa Mobil SBY — Customers, Settings, dan manajemen armada."/>

  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

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
</head>

<body x-data="appData()" x-cloak class="font-sans antialiased bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

<div class="flex h-screen overflow-hidden">

  <!-- Overlay Mobile Sidebar -->
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
      <?php include 'pages/dashboard.php'; ?>
      <?php include 'pages/cars.php'; ?>
      <?php include 'pages/orders.php'; ?>
      <?php include 'pages/customers.php'; ?>
      <?php include 'pages/verifications.php'; ?>
      <?php include 'pages/settings.php'; ?>
    </main>

  </div>
</div>

<script>
  window.SERVER_VERIFICATIONS = <?= json_encode($verifications) ?>;
  window.SERVER_ORDERS = <?= json_encode($orders) ?>;
  window.SERVER_CUSTOMERS = <?= json_encode($customers) ?>;
</script>
<script src="assets/js/app.js"></script>

</body>
</html>
