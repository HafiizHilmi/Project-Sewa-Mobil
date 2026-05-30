<?php
$stockStmt = $pdo->query("SELECT IFNULL(SUM(CASE WHEN IFNULL(is_type,0)=0 THEN 1 ELSE 0 END), 0) AS total_cars, IFNULL(SUM(CASE WHEN IFNULL(is_type,0)=0 THEN stock ELSE 0 END), 0) AS total_stock, IFNULL(SUM(CASE WHEN available = 1 AND IFNULL(is_type,0)=0 THEN stock ELSE 0 END), 0) AS available_stock FROM cars");
$stockStats = $stockStmt->fetch(PDO::FETCH_ASSOC);
$totalStock = $stockStats['total_stock'] ?? 0;
$totalCars = $stockStats['total_cars'] ?? 0;
$availableStock = $stockStats['available_stock'] ?? 0;

$statsStmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_cust,
        (SELECT IFNULL(SUM(total_price + IFNULL(additional_cost, 0)), 0) FROM bookings WHERE status = 'completed') AS total_revenue
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
$totalCust = $stats['total_cust'] ?? 0;
$totalRevenue = $stats['total_revenue'] ?? 0;


// ====================================================================================
// FITUR BARU: KALKULASI GRAFIK DYNAMIC (Membaca jumlah penyewaan dari tabel bookings)
// ====================================================================================

// 1. Dapatkan Data 5 Bulan Terakhir
$chartMonths = [];
$chartValues = [];
$maxValue = 10; // Default max Y axis agar grafiknya proporsional

for ($i = 4; $i >= 0; $i--) {
    $monthStr = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months")); // Contoh: Jan, Feb, Mar
    
    // Hitung berapa banyak penyewaan di bulan tersebut
    $stmtChart = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmtChart->execute([$monthStr]);
    $count = (int)$stmtChart->fetchColumn();
    
    if ($count > $maxValue) {
        $maxValue = ceil($count / 10) * 10; // Bulatkan ke kelipatan 10 terdekat
    }
    
    $chartMonths[] = $monthLabel;
    $chartValues[] = $count;
}

// 2. Dapatkan Data 5 Minggu Terakhir
$weeklyLabels = [];
$weeklyValues = [];
$maxWeekValue = 10;

for ($i = 4; $i >= 0; $i--) {
    $startOfWeek = date('Y-m-d', strtotime("-$i weeks -".date('w')." days"));
    $endOfWeek = date('Y-m-d', strtotime("-$i weeks +".(6-date('w'))." days"));
    $weekLabel = date('d M', strtotime($startOfWeek)); // Contoh: 12 Apr
    
    $stmtW = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?");
    $stmtW->execute([$startOfWeek, $endOfWeek]);
    $countW = (int)$stmtW->fetchColumn();
    
    if ($countW > $maxWeekValue) {
        $maxWeekValue = ceil($countW / 10) * 10;
    }
    
    $weeklyLabels[] = $weekLabel;
    $weeklyValues[] = $countW;
}

// 3. Algoritma Pembentuk Garis Melengkung (Bezier Curve) untuk Bulan
$xCoords = [100, 232, 365, 497, 630]; // Titik pasti sumbu X pada SVG kamu
$pathD = "M " . $xCoords[0] . "," . (150 - (($chartValues[0] / $maxValue) * 130));
for ($i = 1; $i < 5; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = 150 - (($chartValues[$i-1] / $maxValue) * 130);
    $currX = $xCoords[$i];
    $currY = 150 - (($chartValues[$i] / $maxValue) * 130);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathD .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY"; // Melengkung mulus
}
$bgPathD = $pathD . " L 630,150 L 100,150 Z";
$circleY = 150 - (($chartValues[4] / $maxValue) * 130);
$step = $maxValue / 4;

// 4. Algoritma Pembentuk Garis Melengkung (Bezier Curve) untuk Minggu
$pathDW = "M " . $xCoords[0] . "," . (150 - (($weeklyValues[0] / $maxWeekValue) * 130));
for ($i = 1; $i < 5; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = 150 - (($weeklyValues[$i-1] / $maxWeekValue) * 130);
    $currX = $xCoords[$i];
    $currY = 150 - (($weeklyValues[$i] / $maxWeekValue) * 130);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathDW .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY";
}
$bgPathDW = $pathDW . " L 630,150 L 100,150 Z";
$circleYW = 150 - (($weeklyValues[4] / $maxWeekValue) * 130);
$stepW = $maxWeekValue / 4;
// ====================================================================================

?>

<div x-show="activePage === 'dashboard'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-7 py-6 space-y-6">

  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    <div class="stat-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Stok Mobil</p>
          <p class="text-4xl font-extrabold text-slate-800 dark:text-white"><?= number_format($totalStock, 0, ',', '.') ?></p>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
          <i class="fa-solid fa-car text-blue-500 text-lg"></i>
        </div>
      </div>
      <div class="flex items-center gap-1.5 pt-3 border-t border-slate-50 dark:border-slate-700">
        <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 text-xs font-semibold px-2 py-0.5 rounded-full">
          <i class="fa-solid fa-warehouse text-[9px]"></i> Total unit: <?= number_format($totalCars, 0, ',', '.') ?>
        </span>
      </div>
    </div>
    <div class="stat-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Pelanggan Aktif</p>
          <p class="text-4xl font-extrabold text-slate-800 dark:text-white"><?= number_format($totalCust, 0, ',', '.') ?></p>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center">
          <i class="fa-solid fa-user-group text-orange-400 text-lg"></i>
        </div>
      </div>
      <div class="flex items-center gap-1.5 pt-3 border-t border-slate-50 dark:border-slate-700">
        <span class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 text-xs font-semibold px-2 py-0.5 rounded-full"><i class="fa-solid fa-users text-[9px]"></i> Total Pelanggan</span>
      </div>
    </div>
    <div class="stat-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 sm:col-span-2 xl:col-span-1">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Pendapatan</p>
          <p class="text-2xl font-extrabold text-slate-800 dark:text-white">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center">
          <i class="fa-solid fa-wallet text-yellow-500 text-lg"></i>
        </div>
      </div>
      <div class="flex items-center gap-1.5 pt-3 border-t border-slate-50 dark:border-slate-700">
        <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 text-xs font-semibold px-2 py-0.5 rounded-full"><i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> Total Omzet</span>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-4">
    <div x-data="{ chartMode: 'monthly' }" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
      
      <div class="flex items-center justify-between mb-5">
        <div>
          <h2 class="text-sm font-bold text-slate-800 dark:text-white">Rental Trends</h2>
          <p class="text-xs text-slate-400 mt-0.5">Performa penyewaan armada kamu</p>
        </div>
        <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5">
          <button @click="chartMode='weekly'" :class="chartMode==='weekly' ? 'bg-slate-800 dark:bg-slate-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'" class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all">Weekly</button>
          <button @click="chartMode='monthly'" :class="chartMode==='monthly' ? 'bg-slate-800 dark:bg-slate-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'" class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all">Monthly</button>
        </div>
      </div>

      <svg x-show="chartMode === 'monthly'" viewBox="0 0 660 180" class="w-full h-auto">
        <defs>
          <linearGradient id="g1" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#3b82f6" stop-opacity=".12"/>
            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
          </linearGradient>
        </defs>
        <line x1="36" y1="20" x2="650" y2="20" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="52.5" x2="650" y2="52.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="85" x2="650" y2="85" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="117.5" x2="650" y2="117.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="150" x2="650" y2="150" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        
        <text x="30" y="24" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue) ?></text>
        <text x="30" y="56.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue - $step) ?></text>
        <text x="30" y="89" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue - ($step * 2)) ?></text>
        <text x="30" y="121.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue - ($step * 3)) ?></text>
        <text x="30" y="154" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">0</text>
        
        <path d="<?= $bgPathD ?>" fill="url(#g1)"/>
        <path class="chart-path" d="<?= $pathD ?>" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        
        <circle cx="630" cy="<?= $circleY ?>" r="5" fill="#3b82f6" stroke="#fff" stroke-width="2.5" class="dark:stroke-slate-800"/>
        
        <text x="100" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $chartMonths[0] ?></text>
        <text x="232" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $chartMonths[1] ?></text>
        <text x="365" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $chartMonths[2] ?></text>
        <text x="497" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $chartMonths[3] ?></text>
        <text x="630" y="172" font-size="10" fill="#1e293b" text-anchor="middle" font-weight="700" font-family="Plus Jakarta Sans,sans-serif" class="dark:fill-slate-200"><?= $chartMonths[4] ?></text>
      </svg>

      <svg x-show="chartMode === 'weekly'" viewBox="0 0 660 180" class="w-full h-auto" style="display: none;">
        <defs>
          <linearGradient id="g2" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#10b981" stop-opacity=".12"/>
            <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
          </linearGradient>
        </defs>
        <line x1="36" y1="20" x2="650" y2="20" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="52.5" x2="650" y2="52.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="85" x2="650" y2="85" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="117.5" x2="650" y2="117.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        <line x1="36" y1="150" x2="650" y2="150" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
        
        <text x="30" y="24" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue) ?></text>
        <text x="30" y="56.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue - $stepW) ?></text>
        <text x="30" y="89" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue - ($stepW * 2)) ?></text>
        <text x="30" y="121.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue - ($stepW * 3)) ?></text>
        <text x="30" y="154" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">0</text>
        
        <path d="<?= $bgPathDW ?>" fill="url(#g2)"/>
        <path class="chart-path" d="<?= $pathDW ?>" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        
        <circle cx="630" cy="<?= $circleYW ?>" r="5" fill="#10b981" stroke="#fff" stroke-width="2.5" class="dark:stroke-slate-800"/>
        
        <text x="100" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $weeklyLabels[0] ?></text>
        <text x="232" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $weeklyLabels[1] ?></text>
        <text x="365" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $weeklyLabels[2] ?></text>
        <text x="497" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif"><?= $weeklyLabels[3] ?></text>
        <text x="630" y="172" font-size="10" fill="#1e293b" text-anchor="middle" font-weight="700" font-family="Plus Jakarta Sans,sans-serif" class="dark:fill-slate-200">Wk Ini</text>
      </svg>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 flex flex-col">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Recent Alerts</h2>
        <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">View All</a>
      </div>
      <div class="flex-1 space-y-0.5">
        <template x-for="(a, i) in dashAlerts" :key="i">
          <div>
            <div class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
              <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5" :class="a.bg">
                <i class="fa-solid text-xs" :class="[a.icon, a.color]"></i>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 leading-snug" x-text="a.title"></p>
                <p class="text-[11px] text-slate-400 mt-0.5 leading-snug" x-text="a.desc"></p>
                <p class="text-[10px] text-slate-300 dark:text-slate-500 mt-1 font-medium" x-text="a.time"></p>
              </div>
            </div>
            <div x-show="i < dashAlerts.length - 1" class="border-t border-slate-50 dark:border-slate-700 mx-2"></div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>