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
// FITUR BARU: KALKULASI GRAFIK DYNAMIC DENGAN FITUR SLIDE / SCROLL & TOOLTIP
// ====================================================================================

// Helper untuk format Y koordinat SVG (mencegah bug koma di locale Indonesia)
function getSvgY($val, $max) {
    return number_format(150 - (($val / $max) * 130), 2, '.', '');
}

$numPoints = 12; // Menarik 12 titik data ke belakang (1 tahun / 12 minggu)
$pointSpacing = 100; // Jarak antar titik diperlebar agar tidak sesak
$svgWidth = 40 + (($numPoints - 1) * $pointSpacing) + 40; // Total lebar SVG memanjang

// 1. Data Bulan
$chartMonths = [];
$chartValues = [];
$maxValue = 10; 

for ($i = $numPoints - 1; $i >= 0; $i--) {
    $monthStr = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months")); // Contoh: Jan 2026
    
    $stmtChart = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmtChart->execute([$monthStr]);
    $count = (int)$stmtChart->fetchColumn();
    
    if ($count > $maxValue) {
        $maxValue = ceil($count / 10) * 10; 
    }
    
    $chartMonths[] = $monthLabel;
    $chartValues[] = $count;
}

// 2. Data Minggu
$weeklyLabels = [];
$weeklyValues = [];
$maxWeekValue = 10;

for ($i = $numPoints - 1; $i >= 0; $i--) {
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

// 3. Algoritma X Coordinate
$xCoords = [];
for ($i = 0; $i < $numPoints; $i++) {
    $xCoords[] = 40 + ($i * $pointSpacing); 
}

// 4. Algoritma Melengkung (Bulan)
$pathD = "M " . $xCoords[0] . "," . getSvgY($chartValues[0], $maxValue);
for ($i = 1; $i < $numPoints; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = getSvgY($chartValues[$i-1], $maxValue);
    $currX = $xCoords[$i];
    $currY = getSvgY($chartValues[$i], $maxValue);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathD .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY"; 
}
$bgPathD = $pathD . " L " . end($xCoords) . ",150 L " . $xCoords[0] . ",150 Z";
$circleY = getSvgY(end($chartValues), $maxValue);
$step = $maxValue / 4;

// 5. Algoritma Melengkung (Minggu)
$pathDW = "M " . $xCoords[0] . "," . getSvgY($weeklyValues[0], $maxWeekValue);
for ($i = 1; $i < $numPoints; $i++) {
    $prevX = $xCoords[$i-1];
    $prevY = getSvgY($weeklyValues[$i-1], $maxWeekValue);
    $currX = $xCoords[$i];
    $currY = getSvgY($weeklyValues[$i], $maxWeekValue);
    $cp1X = $prevX + ($currX - $prevX) / 2;
    $pathDW .= " C $cp1X,$prevY $cp1X,$currY $currX,$currY";
}
$bgPathDW = $pathDW . " L " . end($xCoords) . ",150 L " . $xCoords[0] . ",150 Z";
$circleYW = getSvgY(end($weeklyValues), $maxWeekValue);
$stepW = $maxWeekValue / 4;
// ====================================================================================

?>

<style>
/* Menyembunyikan scrollbar bawaan browser tapi fitur scroll tetap aktif */
.hide-scroll::-webkit-scrollbar { display: none; }
.hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

/* Animasi untuk menggambar garis kurva (tinta ekstra panjang 4000px agar tidak putus) */
.chart-path {
    stroke-dasharray: 4000;
    stroke-dashoffset: 4000;
    animation: drawPathAnim 2.5s ease-out forwards;
}

/* Animasi untuk memunculkan gradien warna latar secara perlahan */
.chart-gradient {
    opacity: 0;
    animation: fadeGradientAnim 2s ease-out 0.5s forwards;
}

@keyframes drawPathAnim {
    to { stroke-dashoffset: 0; }
}

@keyframes fadeGradientAnim {
    to { opacity: 1; }
}
</style>

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
    <div x-data="{ chartMode: 'monthly' }" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 flex flex-col">
      
      <div class="flex items-center justify-between mb-5 shrink-0">
        <div>
          <h2 class="text-sm font-bold text-slate-800 dark:text-white">Rental Trends</h2>
          <p class="text-xs text-slate-400 mt-0.5">Arahkan kursor ke titik untuk detail penyewaan</p>
        </div>
        <div class="flex items-center">
          
          <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5 mr-3">
            <button @click="$refs.chartScroll.scrollBy({left: -300, behavior: 'smooth'})" title="Geser Kiri" class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
              <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <button @click="$refs.chartScroll.scrollBy({left: 300, behavior: 'smooth'})" title="Geser Kanan" class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
              <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
          </div>
          
          <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5">
            <button @click="chartMode='weekly'" :class="chartMode==='weekly' ? 'bg-slate-800 dark:bg-slate-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'" class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all">Weekly</button>
            <button @click="chartMode='monthly'" :class="chartMode==='monthly' ? 'bg-slate-800 dark:bg-slate-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'" class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all">Monthly</button>
          </div>
        </div>
      </div>

      <div class="relative w-full h-[180px] bg-slate-50/50 dark:bg-slate-900/20 border border-slate-100 dark:border-slate-700 rounded-xl overflow-hidden mt-auto">
        
        <div class="absolute left-0 top-0 bottom-0 w-[45px] bg-gradient-to-r from-white via-white to-white/0 dark:from-slate-800 dark:via-slate-800 dark:to-slate-800/0 z-10 pointer-events-none">
          <svg x-show="chartMode === 'monthly'" viewBox="0 0 45 180" class="w-full h-full">
            <text x="32" y="24" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue) ?></text>
            <text x="32" y="56.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue - $step) ?></text>
            <text x="32" y="89" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue - ($step * 2)) ?></text>
            <text x="32" y="121.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxValue - ($step * 3)) ?></text>
            <text x="32" y="154" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">0</text>
          </svg>
          <svg x-show="chartMode === 'weekly'" viewBox="0 0 45 180" class="w-full h-full" style="display:none;">
            <text x="32" y="24" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue) ?></text>
            <text x="32" y="56.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue - $stepW) ?></text>
            <text x="32" y="89" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue - ($stepW * 2)) ?></text>
            <text x="32" y="121.5" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif"><?= round($maxWeekValue - ($stepW * 3)) ?></text>
            <text x="32" y="154" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">0</text>
          </svg>
        </div>

        <div x-ref="chartScroll" class="absolute inset-0 pl-[45px] overflow-x-auto hide-scroll" x-init="$nextTick(() => { $el.scrollLeft = $el.scrollWidth; })">
          
          <svg x-show="chartMode === 'monthly'" viewBox="0 0 <?= $svgWidth ?> 180" class="h-full" style="width: <?= $svgWidth ?>px; min-width: 100%;">
            <defs>
              <linearGradient id="g1" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#3b82f6" stop-opacity=".12"/>
                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <line x1="0" y1="20" x2="<?= $svgWidth ?>" y2="20" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="52.5" x2="<?= $svgWidth ?>" y2="52.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="85" x2="<?= $svgWidth ?>" y2="85" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="117.5" x2="<?= $svgWidth ?>" y2="117.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="150" x2="<?= $svgWidth ?>" y2="150" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            
            <path class="chart-gradient" d="<?= $bgPathD ?>" fill="url(#g1)"/>
            <path class="chart-path" d="<?= $pathD ?>" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            
            <?php foreach ($xCoords as $i => $x): ?>
                <text x="<?= $x ?>" y="172" font-size="10" fill="<?= $i == $numPoints - 1 ? '#1e293b' : '#94a3b8' ?>" font-weight="<?= $i == $numPoints - 1 ? '700' : '400' ?>" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif" class="<?= $i == $numPoints - 1 ? 'dark:fill-slate-200' : '' ?>"><?= $chartMonths[$i] ?></text>
            <?php endforeach; ?>

            <?php foreach ($xCoords as $i => $x): $y = getSvgY($chartValues[$i], $maxValue); ?>
                <g class="group cursor-pointer">
                    <circle cx="<?= $x ?>" cy="<?= $y ?>" r="15" fill="transparent"/>
                    
                    <circle class="chart-gradient" cx="<?= $x ?>" cy="<?= $y ?>" r="4" fill="#fff" stroke="#3b82f6" stroke-width="2.5" class="transition-all duration-300 group-hover:stroke-[4px] dark:fill-slate-800"/>
                    
                    <line x1="<?= $x ?>" y1="<?= $y + 5 ?>" x2="<?= $x ?>" y2="150" stroke="#cbd5e1" stroke-dasharray="3,3" stroke-width="1" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 dark:stroke-slate-600 pointer-events-none"/>
                    
                    <rect x="<?= $x - 22 ?>" y="<?= $y - 32 ?>" width="44" height="22" rx="4" fill="#1e293b" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 dark:fill-slate-700 pointer-events-none"/>
                    <text x="<?= $x ?>" y="<?= $y - 17 ?>" font-size="10" fill="#fff" text-anchor="middle" font-weight="bold" font-family="Plus Jakarta Sans,sans-serif" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"><?= $chartValues[$i] ?> mobil</text>
                </g>
            <?php endforeach; ?>
          </svg>

          <svg x-show="chartMode === 'weekly'" viewBox="0 0 <?= $svgWidth ?> 180" class="h-full" style="width: <?= $svgWidth ?>px; min-width: 100%; display: none;">
            <defs>
              <linearGradient id="g2" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#10b981" stop-opacity=".12"/>
                <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <line x1="0" y1="20" x2="<?= $svgWidth ?>" y2="20" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="52.5" x2="<?= $svgWidth ?>" y2="52.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="85" x2="<?= $svgWidth ?>" y2="85" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="117.5" x2="<?= $svgWidth ?>" y2="117.5" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            <line x1="0" y1="150" x2="<?= $svgWidth ?>" y2="150" stroke="#f1f5f9" stroke-width="1.2" class="dark:stroke-slate-700"/>
            
            <path class="chart-gradient" d="<?= $bgPathDW ?>" fill="url(#g2)"/>
            <path class="chart-path" d="<?= $pathDW ?>" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            
            <?php foreach ($xCoords as $i => $x): ?>
                <text x="<?= $x ?>" y="172" font-size="10" fill="<?= $i == $numPoints - 1 ? '#1e293b' : '#94a3b8' ?>" font-weight="<?= $i == $numPoints - 1 ? '700' : '400' ?>" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif" class="<?= $i == $numPoints - 1 ? 'dark:fill-slate-200' : '' ?>"><?= $i == $numPoints - 1 ? 'Minggu Ini' : $weeklyLabels[$i] ?></text>
            <?php endforeach; ?>

            <?php foreach ($xCoords as $i => $x): $y = getSvgY($weeklyValues[$i], $maxWeekValue); ?>
                <g class="group cursor-pointer">
                    <circle cx="<?= $x ?>" cy="<?= $y ?>" r="15" fill="transparent"/>
                    <circle class="chart-gradient" cx="<?= $x ?>" cy="<?= $y ?>" r="4" fill="#fff" stroke="#10b981" stroke-width="2.5" class="transition-all duration-300 group-hover:stroke-[4px] dark:fill-slate-800"/>
                    
                    <line x1="<?= $x ?>" y1="<?= $y + 5 ?>" x2="<?= $x ?>" y2="150" stroke="#cbd5e1" stroke-dasharray="3,3" stroke-width="1" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 dark:stroke-slate-600 pointer-events-none"/>
                    <rect x="<?= $x - 22 ?>" y="<?= $y - 32 ?>" width="44" height="22" rx="4" fill="#1e293b" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 dark:fill-slate-700 pointer-events-none"/>
                    <text x="<?= $x ?>" y="<?= $y - 17 ?>" font-size="10" fill="#fff" text-anchor="middle" font-weight="bold" font-family="Plus Jakarta Sans,sans-serif" class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"><?= $weeklyValues[$i] ?> mobil</text>
                </g>
            <?php endforeach; ?>
          </svg>
        </div>
      </div>
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