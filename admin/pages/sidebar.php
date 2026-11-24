<?php
$appName = "SewaMobil SBY";
if (isset($pdo)) {
    try {
        $stmtSettings = $pdo->query("SELECT setting_key, setting_value FROM business_settings WHERE setting_key = 'app_name'");
        if ($stmtSettings && ($row = $stmtSettings->fetch(PDO::FETCH_ASSOC))) {
            $appName = $row['setting_value'];
        }
    } catch (Exception $e) {}
}
?>

<aside id="sidebar" :class="sidebarOpen ? '' : 'sb-hidden'"
  class="w-[250px] min-w-[250px] bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 flex flex-col h-full overflow-y-auto flex-shrink-0 transition-all select-none z-30">

  <!-- Brand / Logo Area (Mengadaptasi User Brand Style) -->
  <div class="px-5 pt-6 pb-4 flex items-center justify-between">
    <a href="../public/index.php" target="_blank" class="no-underline flex items-center gap-2.5 group" title="Buka Website Pelanggan">
      <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
        <i class="fa-solid fa-car-side text-sm"></i>
      </div>
      <div>
        <span class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">
          Sewa<span class="text-blue-600 dark:text-blue-500">Mobil</span>
        </span>
        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1">
          Admin Portal <i class="fa-solid fa-arrow-up-right-from-square text-[8px] opacity-60"></i>
        </span>
      </div>
    </a>
  </div>

  <!-- Profil Card (Mengadaptasi .dash-user dari User Portal) -->
  <div class="mx-3.5 mb-3 p-3 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-full bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0 border border-blue-500/20">
        <?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)) ?>
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">
          <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?>
        </p>
        <div class="flex items-center gap-1.5 mt-0.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-400 capitalize">
            <?= htmlspecialchars($_SESSION['admin_role'] ?? 'Staff') ?>
          </span>
        </div>
      </div>
      <a href="logout.php" title="Keluar dari sesi" class="w-7 h-7 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 text-slate-400 hover:text-rose-600 flex items-center justify-center transition-colors">
        <i class="fa-solid fa-right-from-bracket text-xs"></i>
      </a>
    </div>
  </div>

  <div class="mx-4 border-t border-slate-100 dark:border-slate-800 mb-3"></div>

  <!-- Navigasi Menu Bertingkat (Mengadaptasi .dash-menu / .nav-links) -->
  <nav class="flex-1 px-3 space-y-4 pb-4">
    
    <!-- Menu Utama -->
    <div>
      <p class="px-3 pb-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Utama</p>
      <div class="space-y-1">
        <a :href="'index.php?page=dashboard'"
           :class="activePage === 'dashboard' 
             ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' 
             : 'text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100/70 dark:hover:bg-slate-800/60'"
           class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all">
          <i class="w-4 text-[13px] text-center fa-solid fa-gauge-high" :class="activePage === 'dashboard' ? 'text-white' : 'text-slate-400'"></i>
          <span>Dashboard</span>
        </a>
        <a :href="'index.php?page=orders'"
           :class="activePage === 'orders' 
             ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' 
             : 'text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100/70 dark:hover:bg-slate-800/60'"
           class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all">
          <i class="w-4 text-[13px] text-center fa-solid fa-clipboard-list" :class="activePage === 'orders' ? 'text-white' : 'text-slate-400'"></i>
          <span>Pesanan Rental</span>
        </a>
      </div>
    </div>

    <!-- Manajemen Data -->
    <div>
      <p class="px-3 pb-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Manajemen</p>
      <div class="space-y-1">
        <a :href="'index.php?page=cars'"
           :class="activePage === 'cars' 
             ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' 
             : 'text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100/70 dark:hover:bg-slate-800/60'"
           class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all">
          <i class="w-4 text-[13px] text-center fa-solid fa-car" :class="activePage === 'cars' ? 'text-white' : 'text-slate-400'"></i>
          <span>Kelola Armada</span>
        </a>
        <a :href="'index.php?page=customers'"
           :class="activePage === 'customers' 
             ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' 
             : 'text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100/70 dark:hover:bg-slate-800/60'"
           class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all">
          <i class="w-4 text-[13px] text-center fa-solid fa-users" :class="activePage === 'customers' ? 'text-white' : 'text-slate-400'"></i>
          <span>Pelanggan</span>
        </a>
        <a :href="'index.php?page=verifications'"
           :class="activePage === 'verifications' 
             ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' 
             : 'text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100/70 dark:hover:bg-slate-800/60'"
           class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all">
          <i class="w-4 text-[13px] text-center fa-solid fa-id-card" :class="activePage === 'verifications' ? 'text-white' : 'text-slate-400'"></i>
          <span>Verifikasi KTP & SIM</span>
        </a>
      </div>
    </div>

    <!-- Sistem -->
    <div>
      <p class="px-3 pb-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sistem</p>
      <div class="space-y-1">
        <a :href="'index.php?page=settings'"
           :class="activePage === 'settings' 
             ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' 
             : 'text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100/70 dark:hover:bg-slate-800/60'"
           class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all">
          <i class="w-4 text-[13px] text-center fa-solid fa-gear" :class="activePage === 'settings' ? 'text-white' : 'text-slate-400'"></i>
          <span>Pengaturan</span>
        </a>
      </div>
    </div>

  </nav>

  <!-- Footer Info Sidebar (User Style Minimal Pill) -->
  <div class="p-3 border-t border-slate-100 dark:border-slate-800">
    <div class="px-3 py-2.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/40 flex items-center justify-between text-[11px] font-semibold text-slate-400 dark:text-slate-500">
      <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-slate-600 dark:text-slate-300 font-bold">Online</span>
      </div>
      <span class="text-[10px] font-mono opacity-80">v2.1</span>
    </div>
  </div>

</aside>
