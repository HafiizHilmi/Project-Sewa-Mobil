<?php
// ==========================================
// DATA AGREGASI DASBOR EKSEKUTIF
// ==========================================

// 1. Data Armada & Stok
$stockStmt = $pdo->query("SELECT IFNULL(SUM(CASE WHEN IFNULL(is_type,0)=0 THEN 1 ELSE 0 END), 0) AS total_cars, IFNULL(SUM(CASE WHEN IFNULL(is_type,0)=0 THEN stock ELSE 0 END), 0) AS total_stock, IFNULL(SUM(CASE WHEN available = 1 AND IFNULL(is_type,0)=0 THEN stock ELSE 0 END), 0) AS available_stock FROM cars");
$stockStats = $stockStmt->fetch(PDO::FETCH_ASSOC);
$totalStock = (int)($stockStats['total_stock'] ?? 0);
$totalCars = (int)($stockStats['total_cars'] ?? 0);
$availableStock = (int)($stockStats['available_stock'] ?? 0);
$activeRentals = max(0, $totalStock - $availableStock);
$rentedPct = $totalStock > 0 ? min(100, (int)round(($activeRentals / $totalStock) * 100)) : 0;
$availPct = 100 - $rentedPct;

// 2. Data Pelanggan & Omzet
$statsStmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_cust,
        (SELECT COUNT(*) FROM users WHERE role = 'user' AND verification_status = 'verified') AS verified_cust,
        (SELECT IFNULL(SUM(total_price + IFNULL(additional_cost, 0)), 0) FROM bookings WHERE status = 'completed') AS total_revenue,
        (SELECT COUNT(*) FROM bookings WHERE status = 'completed') AS total_completed_bookings
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
$totalCust = (int)($stats['total_cust'] ?? 0);
$verifiedCust = (int)($stats['verified_cust'] ?? 0);
$totalRevenue = (float)($stats['total_revenue'] ?? 0);
$totalCompletedBookings = (int)($stats['total_completed_bookings'] ?? 0);
$verifiedPct = $totalCust > 0 ? (int)round(($verifiedCust / $totalCust) * 100) : 100;

// 3. Kalkulasi Grafik Tren (Harian, Mingguan, Bulanan)
function getSvgY($val, $max) {
    if ($max == 0) $max = 1; 
    return number_format(180 - (($val / $max) * 145), 2, '.', '');
}

$numPoints = 12; 
$pointSpacing = 95; 
$svgWidth = 45 + (($numPoints - 1) * $pointSpacing) + 45; 

$mCounts = [];
$mStmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 MONTH) GROUP BY ym");
while ($r = $mStmt->fetch(PDO::FETCH_ASSOC)) {
    $mCounts[$r['ym']] = (int)$r['c'];
}

$dCounts = [];
$dStmt = $pdo->query("SELECT DATE(created_at) as dt, COUNT(*) as c FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 95 DAY) GROUP BY dt");
while ($r = $dStmt->fetch(PDO::FETCH_ASSOC)) {
    $dCounts[$r['dt']] = (int)$r['c'];
}

// Data bulanan
$chartMonths = [];
$chartValues = [];
$maxValue = 10; 
for ($i = $numPoints - 1; $i >= 0; $i--) {
    $monthStr = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months")); 
    $count = $mCounts[$monthStr] ?? 0;
    if ($count > $maxValue) $maxValue = ceil($count / 10) * 10; 
    $chartMonths[] = $monthLabel;
    $chartValues[] = $count;
}

// Data mingguan
$weeklyLabels = [];
$weeklyValues = [];
$maxWeekValue = 10;
for ($i = $numPoints - 1; $i >= 0; $i--) {
    $startOfWeek = date('Y-m-d', strtotime("-$i weeks -".date('w')." days"));
    $endOfWeek = date('Y-m-d', strtotime("-$i weeks +".(6-date('w'))." days"));
    $weekLabel = date('d M', strtotime($startOfWeek)); 
    $countW = 0;
    $currDate = $startOfWeek;
    while ($currDate <= $endOfWeek) {
        $countW += ($dCounts[$currDate] ?? 0);
        $currDate = date('Y-m-d', strtotime("$currDate +1 day"));
    }
    if ($countW > $maxWeekValue) $maxWeekValue = ceil($countW / 10) * 10;
    $weeklyLabels[] = $weekLabel;
    $weeklyValues[] = $countW;
}

// Data harian
$dailyLabels = [];
$dailyValues = [];
$maxDailyValue = 10;
for ($i = $numPoints - 1; $i >= 0; $i--) {
    $dayStr = date('Y-m-d', strtotime("-$i days"));
    $dayLabel = date('d M', strtotime("-$i days")); 
    $countD = $dCounts[$dayStr] ?? 0;
    if ($countD > $maxDailyValue) $maxDailyValue = ceil($countD / 10) * 10;
    $dailyLabels[] = $dayLabel;
    $dailyValues[] = $countD;
}

// Hitung koordinat X grafik
$xCoords = [];
for ($i = 0; $i < $numPoints; $i++) {
    $xCoords[] = 45 + ($i * $pointSpacing); 
}

// Kurva bulanan
$pathD = "M " . $xCoords[0] . "," . getSvgY($chartValues[0], $maxValue);
for ($i = 1; $i < $numPoints; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = getSvgY($chartValues[$i-1], $maxValue);
    $currX = $xCoords[$i];
    $currY = getSvgY($chartValues[$i], $maxValue);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathD .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY"; 
}
$bgPathD = $pathD . " L " . end($xCoords) . ",180 L " . $xCoords[0] . ",180 Z";
$step = $maxValue / 4;

// Kurva mingguan
$pathDW = "M " . $xCoords[0] . "," . getSvgY($weeklyValues[0], $maxWeekValue);
for ($i = 1; $i < $numPoints; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = getSvgY($weeklyValues[$i-1], $maxWeekValue);
    $currX = $xCoords[$i];
    $currY = getSvgY($weeklyValues[$i], $maxWeekValue);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathDW .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY";
}
$bgPathDW = $pathDW . " L " . end($xCoords) . ",180 L " . $xCoords[0] . ",180 Z";
$stepW = $maxWeekValue / 4;

// Kurva harian
$pathDD = "M " . $xCoords[0] . "," . getSvgY($dailyValues[0], $maxDailyValue);
for ($i = 1; $i < $numPoints; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = getSvgY($dailyValues[$i-1], $maxDailyValue);
    $currX = $xCoords[$i];
    $currY = getSvgY($dailyValues[$i], $maxDailyValue);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathDD .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY";
}
$bgPathDD = $pathDD . " L " . end($xCoords) . ",180 L " . $xCoords[0] . ",180 Z";
$stepD = $maxDailyValue / 4;

// ==========================================
// 5 METRIK & CHART BARU (USER DESIGN SYSTEM)
// ==========================================

// CHART 1: Distribusi Kategori Armada (Donut Chart)
$catStmt = $pdo->query("
    SELECT category, COUNT(*) as models_count, IFNULL(SUM(stock), 0) as units_count 
    FROM cars 
    WHERE IFNULL(is_type, 0) = 0 
    GROUP BY category 
    ORDER BY units_count DESC
");
$categoryBreakdown = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$catColors = [
    'MPV'       => ['#3b82f6', 'bg-blue-500'],
    'SUV'       => ['#10b981', 'bg-emerald-500'],
    'Hatchback' => ['#f59e0b', 'bg-amber-500'],
    'Sedan'     => ['#8b5cf6', 'bg-purple-500'],
    'EV'        => ['#06b6d4', 'bg-cyan-500'],
    'Luxury'    => ['#ec4899', 'bg-pink-500'],
];

$donutRadius = 54;
$donutCircumference = 2 * M_PI * $donutRadius; // ~339.29
$donutSlices = [];
$accumulatedUnits = 0;
$totalUnitsInCat = max(1, array_sum(array_column($categoryBreakdown, 'units_count')));

foreach ($categoryBreakdown as $cat) {
    $cUnits = (int)$cat['units_count'];
    $pct = $cUnits / $totalUnitsInCat;
    $dashLength = $pct * $donutCircumference;
    $dashOffset = -($accumulatedUnits / $totalUnitsInCat) * $donutCircumference;
    $color = $catColors[$cat['category']][0] ?? '#64748b';
    
    $donutSlices[] = [
        'category'    => $cat['category'],
        'units'       => $cUnits,
        'models'      => (int)$cat['models_count'],
        'pct'         => round($pct * 100, 1),
        'color'       => $color,
        'dash_length' => number_format($dashLength, 2, '.', ''),
        'dash_offset' => number_format($dashOffset, 2, '.', ''),
        'circumference' => number_format($donutCircumference, 2, '.', '')
    ];
    $accumulatedUnits += $cUnits;
}

// CHART 2: Komposisi Finansial & Mode Layanan
$srvStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        IFNULL(SUM(CASE WHEN addon_driver = 0 THEN 1 ELSE 0 END), 0) as lepas_kunci_count,
        IFNULL(SUM(CASE WHEN addon_driver = 1 THEN 1 ELSE 0 END), 0) as driver_count,
        IFNULL(SUM(CASE WHEN addon_driver = 0 THEN total_price ELSE 0 END), 0) as lepas_kunci_rev,
        IFNULL(SUM(CASE WHEN addon_driver = 1 THEN total_price ELSE 0 END), 0) as driver_rev,
        IFNULL(SUM(additional_cost), 0) as additional_denda_rev
    FROM bookings
");
$srvStats = $srvStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_orders' => 0, 'lepas_kunci_count' => 0, 'driver_count' => 0,
    'lepas_kunci_rev' => 0, 'driver_rev' => 0, 'additional_denda_rev' => 0
];
$totalServiceOrders = max(1, (int)$srvStats['total_orders']);
$lepasKunciPct = round(((int)$srvStats['lepas_kunci_count'] / $totalServiceOrders) * 100);
$driverPct = 100 - $lepasKunciPct;

// CHART 3: Distribusi Hari Pemesanan Teramai (Peak Demand Day of Week)
$dowCounts = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 1 => 0]; // Sen..Min
$dowStmt = $pdo->query("SELECT DAYOFWEEK(start_date) as dow, COUNT(*) as c FROM bookings GROUP BY dow");
while ($row = $dowStmt->fetch(PDO::FETCH_ASSOC)) {
    $dowCounts[(int)$row['dow']] = (int)$row['c'];
}
$dayNames = [
    2 => 'Sen',
    3 => 'Sel',
    4 => 'Rab',
    5 => 'Kam',
    6 => 'Jum',
    7 => 'Sab',
    1 => 'Min'
];
$maxDowCount = max(1, max(array_values($dowCounts)));

// CHART 4: Funnel Verifikasi & Rasio Keamanan Pelanggan
$vFunnelStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        IFNULL(SUM(CASE WHEN verification_status != 'unverified' THEN 1 ELSE 0 END), 0) as docs_uploaded,
        IFNULL(SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END), 0) as verified_count,
        IFNULL(SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
        IFNULL(SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END), 0) as rejected_count
    FROM users WHERE role = 'user'
");
$vFunnel = $vFunnelStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_users' => 1, 'docs_uploaded' => 0, 'verified_count' => 0, 'pending_count' => 0, 'rejected_count' => 0
];
$funnelTotal = max(1, (int)$vFunnel['total_users']);
$funnelUploadedPct = round(((int)$vFunnel['docs_uploaded'] / $funnelTotal) * 100);
$funnelVerifiedPct = round(((int)$vFunnel['verified_count'] / $funnelTotal) * 100);

// CHART 5: Top 5 Armada Paling Laris & Utilisasi
$topCarsStmt = $pdo->query("
    SELECT 
        c.id, c.make, c.model, c.category, c.price_per_day, c.image,
        COUNT(b.id) as total_booked,
        IFNULL(SUM(b.total_price), 0) as total_rev
    FROM cars c
    LEFT JOIN bookings b ON b.car_id = c.id
    WHERE IFNULL(c.is_type, 0) = 0
    GROUP BY c.id
    ORDER BY total_booked DESC, total_rev DESC, c.id ASC
    LIMIT 5
");
$topCars = $topCarsStmt->fetchAll(PDO::FETCH_ASSOC);
$maxBookedCar = max(1, max(array_column($topCars, 'total_booked') ?: [1]));

// Pesanan Pending Terkini
$alertsStmt = $pdo->query("
    SELECT b.id, b.created_at, b.full_name, c.make, c.model, b.total_price 
    FROM bookings b 
    LEFT JOIN cars c ON b.car_id = c.id 
    WHERE b.status = 'pending' 
    ORDER BY b.created_at DESC
");
$pendingOrders = $alertsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div x-show="activePage === 'dashboard'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-1.5"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-7 py-6 space-y-6">

  <!-- ====================================================
       1. EXECUTIVE STAT CARDS (4 KARTU METRIK UTAMA)
  ==================================================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- Card 1: Total Armada -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all duration-200 group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Armada</span>
        <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs group-hover:scale-105 transition-transform border border-blue-100 dark:border-blue-900/50">
          <i class="fa-solid fa-car-side"></i>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight"><?= $totalStock ?></span>
        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Unit (<?= $totalCars ?> Tipe)</span>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80">
        <div class="flex items-center justify-between text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1.5">
          <span>Tersedia: <strong class="text-emerald-600 dark:text-emerald-400"><?= $availableStock ?></strong></span>
          <span>Disewa: <strong class="text-amber-500"><?= $activeRentals ?></strong></span>
        </div>
        <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-1.5 rounded-full overflow-hidden flex">
          <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?= $availPct ?>%"></div>
          <div class="bg-amber-400 h-full transition-all duration-500" style="width: <?= $rentedPct ?>%"></div>
        </div>
      </div>
    </div>

    <!-- Card 2: Pelanggan Terdaftar -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all duration-200 group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Pelanggan Aktif</span>
        <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs group-hover:scale-105 transition-transform border border-indigo-100 dark:border-indigo-900/50">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight"><?= $totalCust ?></span>
        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">User</span>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
          <i class="fa-solid fa-check text-[9px]"></i> <?= $verifiedPct ?>% Terverifikasi
        </span>
        <a href="index.php?page=customers" class="text-[11px] text-blue-600 dark:text-blue-400 font-bold hover:underline">Kelola &rarr;</a>
      </div>
    </div>

    <!-- Card 3: Total Omzet Rental -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all duration-200 group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Omzet Rental</span>
        <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs group-hover:scale-105 transition-transform border border-emerald-100 dark:border-emerald-900/50">
          <i class="fa-solid fa-money-bill-wave"></i>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-1.5">
        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Rp</span>
        <span class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">
          <?= number_format($totalRevenue, 0, ',', '.') ?>
        </span>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] font-medium text-slate-500 dark:text-slate-400">
        <span>Transaksi Sukses:</span>
        <span class="font-bold text-slate-700 dark:text-slate-200"><?= $totalCompletedBookings ?> Sewa</span>
      </div>
    </div>

    <!-- Card 4: Pesanan Pending -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all duration-200 group">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Pesanan Pending</span>
        <div class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs group-hover:scale-105 transition-transform border border-amber-100 dark:border-amber-900/50">
          <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight"><?= count($pendingOrders) ?></span>
        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">Menunggu</span>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
        <?php if (count($pendingOrders) > 0): ?>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40 animate-pulse">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Perlu Tindakan
          </span>
          <a href="index.php?page=orders" class="text-[11px] text-blue-600 dark:text-blue-400 font-bold hover:underline">Review &rarr;</a>
        <?php else: ?>
          <span class="text-[11px] text-slate-400">Semua pesanan aman</span>
          <span class="text-emerald-500 text-xs"><i class="fa-solid fa-circle-check"></i></span>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ====================================================
       2. PRIMARY ANALYTICS ROW (GRID 8/12 + 4/12)
       Left: Tren Aktivitas Rental (Main Line/Area Chart)
       Right: CHART 1: Distribusi Kategori Armada (Donut Chart)
  ==================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5" x-data="{ chartTab: 'bulanan' }">

    <!-- Left 8/12: Main Trend Activity Chart -->
    <div class="lg:col-span-8 bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 lg:p-6 shadow-sm">
      <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
        <div>
          <h2 class="text-sm lg:text-base font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
            Tren Aktivitas Rental Armada
          </h2>
          <p class="text-xs text-slate-400 dark:text-slate-400 mt-0.5">Analisis intensitas penyewaan mobil berdasarkan rentang waktu</p>
        </div>

        <!-- Segmented Capsule Selector (Style User Portal) -->
        <div class="inline-flex items-center bg-slate-100/80 dark:bg-slate-900/60 p-1 rounded-full border border-slate-200/60 dark:border-slate-700/60">
          <button type="button" @click="chartTab = 'harian'" 
                  :class="chartTab === 'harian' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                  class="px-3 py-1 rounded-full text-xs font-semibold transition-all">
            Harian
          </button>
          <button type="button" @click="chartTab = 'mingguan'" 
                  :class="chartTab === 'mingguan' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                  class="px-3 py-1 rounded-full text-xs font-semibold transition-all">
            Mingguan
          </button>
          <button type="button" @click="chartTab = 'bulanan'" 
                  :class="chartTab === 'bulanan' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                  class="px-3 py-1 rounded-full text-xs font-semibold transition-all">
            Bulanan
          </button>
        </div>
      </div>

      <!-- Scrollable Interactive SVG Chart Container -->
      <div class="overflow-x-auto pt-4 pb-2" style="-webkit-overflow-scrolling: touch;">
        <div style="min-width: <?= $svgWidth ?>px;" class="relative">
          <svg viewBox="0 0 <?= $svgWidth ?> 220" class="w-full overflow-visible select-none" style="height: 220px;">
            <defs>
              <linearGradient id="chartGradBlue" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>
              </linearGradient>
              <linearGradient id="chartGradEmerald" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/>
                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
              </linearGradient>
              <linearGradient id="chartGradRose" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.25"/>
                <stop offset="100%" stop-color="#f43f5e" stop-opacity="0.0"/>
              </linearGradient>
            </defs>

            <!-- Grid Lines Horizontal -->
            <?php for ($g = 0; $g <= 4; $g++): ?>
              <?php $gridY = 180 - ($g * 36.25); ?>
              <line x1="45" y1="<?= $gridY ?>" x2="<?= end($xCoords) ?>" y2="<?= $gridY ?>"
                    stroke="currentColor" stroke-dasharray="4 4" class="text-slate-200 dark:text-slate-700/60" stroke-width="1"/>
            <?php endfor; ?>

            <!-- Tab Bulanan -->
            <g x-show="chartTab === 'bulanan'" x-transition.opacity>
              <path d="<?= $bgPathD ?>" fill="url(#chartGradBlue)"/>
              <path d="<?= $pathD ?>" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              <?php foreach ($chartValues as $idx => $val): ?>
                <?php $cx = $xCoords[$idx]; $cy = getSvgY($val, $maxValue); ?>
                <g class="cursor-pointer group">
                  <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="4.5" fill="#ffffff" stroke="#3b82f6" stroke-width="2.5" class="transition-transform group-hover:scale-150"/>
                  <text x="<?= $cx ?>" y="206" text-anchor="middle" class="text-[10px] font-semibold fill-slate-400 dark:fill-slate-500">
                    <?= $chartMonths[$idx] ?>
                  </text>
                  <text x="<?= $cx ?>" y="<?= $cy - 9 ?>" text-anchor="middle" class="text-[10.5px] font-extrabold fill-blue-600 dark:fill-blue-400 opacity-90">
                    <?= $val ?>
                  </text>
                </g>
              <?php endforeach; ?>
            </g>

            <!-- Tab Mingguan -->
            <g x-show="chartTab === 'mingguan'" x-transition.opacity>
              <path d="<?= $bgPathDW ?>" fill="url(#chartGradEmerald)"/>
              <path d="<?= $pathDW ?>" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              <?php foreach ($weeklyValues as $idx => $val): ?>
                <?php $cx = $xCoords[$idx]; $cy = getSvgY($val, $maxWeekValue); ?>
                <g class="cursor-pointer group">
                  <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="4.5" fill="#ffffff" stroke="#10b981" stroke-width="2.5" class="transition-transform group-hover:scale-150"/>
                  <text x="<?= $cx ?>" y="206" text-anchor="middle" class="text-[10px] font-semibold fill-slate-400 dark:fill-slate-500">
                    <?= $weeklyLabels[$idx] ?>
                  </text>
                  <text x="<?= $cx ?>" y="<?= $cy - 9 ?>" text-anchor="middle" class="text-[10.5px] font-extrabold fill-emerald-600 dark:fill-emerald-400 opacity-90">
                    <?= $val ?>
                  </text>
                </g>
              <?php endforeach; ?>
            </g>

            <!-- Tab Harian -->
            <g x-show="chartTab === 'harian'" x-transition.opacity>
              <path d="<?= $bgPathDD ?>" fill="url(#chartGradRose)"/>
              <path d="<?= $pathDD ?>" fill="none" stroke="#f43f5e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
              <?php foreach ($dailyValues as $idx => $val): ?>
                <?php $cx = $xCoords[$idx]; $cy = getSvgY($val, $maxDailyValue); ?>
                <g class="cursor-pointer group">
                  <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="4.5" fill="#ffffff" stroke="#f43f5e" stroke-width="2.5" class="transition-transform group-hover:scale-150"/>
                  <text x="<?= $cx ?>" y="206" text-anchor="middle" class="text-[10px] font-semibold fill-slate-400 dark:fill-slate-500">
                    <?= $dailyLabels[$idx] ?>
                  </text>
                  <text x="<?= $cx ?>" y="<?= $cy - 9 ?>" text-anchor="middle" class="text-[10.5px] font-extrabold fill-rose-600 dark:fill-rose-400 opacity-90">
                    <?= $val ?>
                  </text>
                </g>
              <?php endforeach; ?>
            </g>
          </svg>
        </div>
      </div>
    </div>

    <!-- Right 4/12: CHART 1: Distribusi Kategori Armada (Donut Chart) -->
    <div class="lg:col-span-4 bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 lg:p-6 shadow-sm flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            Kategori Armada
          </h2>
          <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
            <?= count($categoryBreakdown) ?> Tipe
          </span>
        </div>

        <!-- SVG Donut Chart with Center Text -->
        <div class="flex items-center justify-center my-4 relative">
          <svg width="150" height="150" viewBox="0 0 160 160" class="transform -rotate-90">
            <circle cx="80" cy="80" r="<?= $donutRadius ?>" fill="none" stroke="currentColor" stroke-width="18" class="text-slate-100 dark:text-slate-700/50" />
            <?php foreach ($donutSlices as $slice): ?>
              <circle cx="80" cy="80" r="<?= $donutRadius ?>" fill="none" 
                      stroke="<?= $slice['color'] ?>" stroke-width="18" 
                      stroke-dasharray="<?= $slice['dash_length'] ?> <?= $donutCircumference ?>" 
                      stroke-dashoffset="<?= $slice['dash_offset'] ?>"
                      class="transition-all duration-700 hover:opacity-80 cursor-pointer" />
            <?php endforeach; ?>
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none select-none text-center">
            <span class="text-xl font-black text-slate-800 dark:text-white leading-none"><?= $totalStock ?></span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Unit Total</span>
          </div>
        </div>
      </div>

      <!-- Category Legend Grid -->
      <div class="space-y-2 mt-2 pt-3 border-t border-slate-100 dark:border-slate-800">
        <?php foreach (array_slice($donutSlices, 0, 4) as $s): ?>
          <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?= $s['color'] ?>"></span>
              <span class="font-bold text-slate-700 dark:text-slate-200"><?= htmlspecialchars($s['category']) ?></span>
              <span class="text-[10px] text-slate-400">(<?= $s['models'] ?> model)</span>
            </div>
            <div class="flex items-center gap-2 font-mono">
              <span class="font-bold text-slate-800 dark:text-slate-100"><?= $s['units'] ?> unit</span>
              <span class="text-[10px] text-slate-400 font-semibold">(<?= $s['pct'] ?>%)</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <!-- ====================================================
       3. OPERATIONAL & FINANCIAL INTELLIGENCE (3 KOLOM 1:1:1)
       Col 1: CHART 2: Komposisi Finansial & Layanan
       Col 2: CHART 3: Distribusi Hari Pemesanan Teramai
       Col 3: CHART 4: Funnel Verifikasi & Keamanan
  ==================================================== -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

    <!-- CHART 2: Komposisi Layanan & Finansial -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center gap-2 uppercase tracking-wider">
            <i class="fa-solid fa-sliders text-blue-600"></i>
            Komposisi Layanan
          </h3>
          <span class="text-[10px] font-bold text-slate-400">Total <?= (int)$srvStats['total_orders'] ?> Sewa</span>
        </div>

        <p class="text-xs text-slate-400 mt-3">Perbandingan preferensi pelanggan antara rental lepas kunci vs dengan pengemudi:</p>

        <!-- Segmented Dual Progress Bar -->
        <div class="mt-4">
          <div class="flex justify-between text-xs font-bold mb-1.5">
            <span class="text-blue-600 dark:text-blue-400 flex items-center gap-1">
              <i class="fa-solid fa-key text-[10px]"></i> Lepas Kunci (<?= $lepasKunciPct ?>%)
            </span>
            <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
              <i class="fa-solid fa-user-tie text-[10px]"></i> + Driver (<?= $driverPct ?>%)
            </span>
          </div>
          <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2.5 rounded-full overflow-hidden flex">
            <div class="bg-blue-600 h-full transition-all duration-500" style="width: <?= $lepasKunciPct ?>%"></div>
            <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?= $driverPct ?>%"></div>
          </div>
        </div>
      </div>

      <!-- Breakdown Details -->
      <div class="space-y-2 mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
        <div class="flex items-center justify-between text-xs py-1">
          <span class="text-slate-500 dark:text-slate-400">Omzet Lepas Kunci:</span>
          <span class="font-extrabold text-slate-800 dark:text-slate-100">Rp <?= number_format((float)$srvStats['lepas_kunci_rev'], 0, ',', '.') ?></span>
        </div>
        <div class="flex items-center justify-between text-xs py-1">
          <span class="text-slate-500 dark:text-slate-400">Omzet Sewa + Driver:</span>
          <span class="font-extrabold text-slate-800 dark:text-slate-100">Rp <?= number_format((float)$srvStats['driver_rev'], 0, ',', '.') ?></span>
        </div>
        <div class="flex items-center justify-between text-xs py-1">
          <span class="text-slate-500 dark:text-slate-400">Denda / Biaya Tambahan:</span>
          <span class="font-extrabold text-amber-600 dark:text-amber-400">Rp <?= number_format((float)$srvStats['additional_denda_rev'], 0, ',', '.') ?></span>
        </div>
      </div>
    </div>

    <!-- CHART 3: Distribusi Hari Pemesanan Teramai -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center gap-2 uppercase tracking-wider">
            <i class="fa-solid fa-calendar-week text-emerald-500"></i>
            Hari Pemesanan Teramai
          </h3>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
            Siklus 7 Hari
          </span>
        </div>

        <p class="text-xs text-slate-400 mt-3">Frekuensi tanggal mulai rental dalam sepekan untuk estimasi kesiapan armada:</p>

        <!-- 7-Bar Column Visual -->
        <div class="flex items-end justify-between gap-1.5 h-36 mt-4 pt-4 px-1">
          <?php foreach ($dayNames as $dowKey => $dLabel): ?>
            <?php 
              $count = $dowCounts[$dowKey] ?? 0;
              $barHeight = max(8, round(($count / $maxDowCount) * 100));
              $isPeak = ($count === $maxDowCount && $count > 0);
            ?>
            <div class="flex-1 flex flex-col items-center gap-1.5 group cursor-pointer">
              <span class="text-[9.5px] font-extrabold <?= $isPeak ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' ?>">
                <?= $count ?>
              </span>
              <div class="w-full bg-slate-100 dark:bg-slate-700/50 rounded-t-lg h-24 flex items-end overflow-hidden">
                <div class="w-full rounded-t-lg transition-all duration-500 <?= $isPeak ? 'bg-gradient-to-t from-blue-600 to-cyan-400 shadow-sm' : 'bg-slate-300 dark:bg-slate-600 group-hover:bg-blue-400' ?>"
                     style="height: <?= $barHeight ?>%;"></div>
              </div>
              <span class="text-[10.5px] font-bold <?= $isPeak ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' ?>">
                <?= $dLabel ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
        <span>Kesiapan Staf:</span>
        <span class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1">
          <i class="fa-solid fa-chart-simple text-blue-500"></i> Puncak di Akhir Pekan
        </span>
      </div>
    </div>

    <!-- CHART 4: Funnel Verifikasi & Keamanan Pelanggan -->
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center gap-2 uppercase tracking-wider">
            <i class="fa-solid fa-shield-halved text-indigo-500"></i>
            Keamanan & Verifikasi
          </h3>
          <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check text-[9px]"></i> Aktif
          </span>
        </div>

        <p class="text-xs text-slate-400 mt-3">Funnel validasi berkas identitas pelanggan untuk kepatuhan keamanan rental:</p>

        <!-- Funnel Step Rows -->
        <div class="space-y-2.5 mt-4">
          <!-- Step 1: Registered -->
          <div>
            <div class="flex justify-between text-xs font-bold mb-1">
              <span class="text-slate-700 dark:text-slate-200">1. Terdaftar</span>
              <span class="text-slate-400"><?= (int)$vFunnel['total_users'] ?> User (100%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2 rounded-full overflow-hidden">
              <div class="bg-slate-400 dark:bg-slate-500 h-full w-full"></div>
            </div>
          </div>

          <!-- Step 2: Uploaded Docs -->
          <div>
            <div class="flex justify-between text-xs font-bold mb-1">
              <span class="text-slate-700 dark:text-slate-200">2. Unggah KTP & SIM</span>
              <span class="text-indigo-600 dark:text-indigo-400"><?= (int)$vFunnel['docs_uploaded'] ?> User (<?= $funnelUploadedPct ?>%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2 rounded-full overflow-hidden">
              <div class="bg-indigo-500 h-full transition-all duration-500" style="width: <?= $funnelUploadedPct ?>%"></div>
            </div>
          </div>

          <!-- Step 3: Verified -->
          <div>
            <div class="flex justify-between text-xs font-bold mb-1">
              <span class="text-emerald-600 dark:text-emerald-400">3. Terverifikasi</span>
              <span class="text-emerald-600 dark:text-emerald-400"><?= (int)$vFunnel['verified_count'] ?> User (<?= $funnelVerifiedPct ?>%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2 rounded-full overflow-hidden">
              <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?= $funnelVerifiedPct ?>%"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <span class="text-[11px] text-slate-500 dark:text-slate-400">Pending Review: <strong><?= (int)$vFunnel['pending_count'] ?></strong></span>
        <a href="index.php?page=verifications" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">Review Berkas &rarr;</a>
      </div>
    </div>

  </div>

  <!-- ====================================================
       4. FLEET PERFORMANCE & INCOMING QUEUE (7/12 & 5/12)
       Left: CHART 5: Top 5 Armada Paling Laris & Utilisasi
       Right: Incoming Orders Queue & Fleet Readiness Hub
  ==================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    <!-- Left 7/12: CHART 5: Top 5 Armada Paling Laris -->
    <div class="lg:col-span-7 bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 lg:p-6 shadow-sm">
      <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800">
        <div>
          <h3 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            Top 5 Armada Paling Laris & Utilisasi
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">Peringkat unit mobil berdasarkan frekuensi booking dan kontribusi pendapatan</p>
        </div>
        <a href="index.php?page=cars" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">Semua Armada &rarr;</a>
      </div>

      <div class="space-y-3 mt-4">
        <?php if (!empty($topCars)): ?>
          <?php 
            $rankMedals = [
              1 => ['bg' => 'bg-amber-500 text-white', 'icon' => '1'],
              2 => ['bg' => 'bg-slate-400 text-white', 'icon' => '2'],
              3 => ['bg' => 'bg-amber-700 text-white', 'icon' => '3'],
              4 => ['bg' => 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300', 'icon' => '4'],
              5 => ['bg' => 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300', 'icon' => '5']
            ];
          ?>
          <?php foreach ($topCars as $i => $car): ?>
            <?php 
              $rank = $i + 1;
              $badge = $rankMedals[$rank] ?? $rankMedals[5];
              $carPct = round(((int)$car['total_booked'] / $maxBookedCar) * 100);
            ?>
            <div class="p-3 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-center gap-3.5 hover:border-slate-200 dark:hover:border-slate-700 transition-all">
              <span class="w-6 h-6 rounded-full <?= $badge['bg'] ?> text-xs font-black flex items-center justify-center flex-shrink-0 shadow-sm">
                <?= $badge['icon'] ?>
              </span>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-xs font-extrabold text-slate-800 dark:text-slate-100 truncate">
                    <?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>
                  </p>
                  <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">
                    <?= (int)$car['total_booked'] ?>x disewa
                  </span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1">
                  <span>Kategori: <strong class="text-slate-600 dark:text-slate-300"><?= htmlspecialchars($car['category']) ?></strong></span>
                  <span>Omzet: <strong class="text-emerald-600 dark:text-emerald-400">Rp <?= number_format((float)$car['total_rev'], 0, ',', '.') ?></strong></span>
                </div>
                <div class="w-full bg-slate-200/80 dark:bg-slate-700/50 h-1.5 rounded-full overflow-hidden mt-2">
                  <div class="bg-blue-600 dark:bg-blue-500 h-full transition-all duration-500" style="width: <?= $carPct ?>%"></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="py-10 text-center text-slate-400">
            <i class="fa-solid fa-car text-3xl mb-2 opacity-40"></i>
            <p class="text-xs">Belum ada riwayat booking rental.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right 5/12: Incoming Orders Queue & Fleet Readiness Hub -->
    <div class="lg:col-span-5 space-y-5">
      
      <!-- Pesanan Masuk Terbaru -->
      <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center gap-2 uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            Pesanan Masuk Terbaru
          </h3>
          <a href="index.php?page=orders" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">Semua &rarr;</a>
        </div>

        <div class="space-y-2 mt-3.5 max-h-56 overflow-y-auto pr-1">
          <?php if (!empty($pendingOrders)): ?>
            <?php foreach (array_slice($pendingOrders, 0, 4) as $po): ?>
              <div class="p-2.5 rounded-xl bg-slate-50/80 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                  <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-xs flex items-center justify-center flex-shrink-0">
                    <?= strtoupper(substr($po['full_name'] ?? 'P', 0, 1)) ?>
                  </div>
                  <div class="min-w-0">
                    <p class="font-bold text-slate-800 dark:text-slate-100 truncate"><?= htmlspecialchars($po['full_name'] ?? 'Pelanggan') ?></p>
                    <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars(($po['make'] ?? '') . ' ' . ($po['model'] ?? '')) ?></p>
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="font-bold text-slate-800 dark:text-slate-200">Rp <?= number_format((float)($po['total_price'] ?? 0), 0, ',', '.') ?></p>
                  <a href="index.php?page=orders" class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold hover:underline">Review &rarr;</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="py-6 text-center text-slate-400 text-xs">
              <i class="fa-solid fa-clipboard-check text-2xl mb-1 text-slate-300 dark:text-slate-600 block"></i>
              Tidak ada antrean pesanan pending.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Kesiapan Armada & Quick Actions Hub -->
      <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
        <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider mb-3">
          Aksi Cepat Operasional
        </h3>
        <div class="grid grid-cols-2 gap-2">
          <a href="index.php?page=cars" class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 transition-all flex flex-col items-center justify-center text-center group">
            <i class="fa-solid fa-car text-blue-600 dark:text-blue-400 text-base mb-1 group-hover:scale-110 transition-transform"></i>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Kelola Armada</span>
            <span class="text-[10px] text-slate-400"><?= $totalCars ?> Model Mobil</span>
          </a>
          <a href="index.php?page=verifications" class="p-3 rounded-xl bg-slate-50/80 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700 transition-all flex flex-col items-center justify-center text-center group">
            <i class="fa-solid fa-id-card text-emerald-500 text-base mb-1 group-hover:scale-110 transition-transform"></i>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Verifikasi User</span>
            <span class="text-[10px] text-slate-400">KTP & SIM</span>
          </a>
        </div>
      </div>

    </div>

  </div>

</div>
