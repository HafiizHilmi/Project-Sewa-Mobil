<?php
// PHP logic tetap sama, tidak ada yang berubah
$appName = "SewaMobil SBY";
$appAddress = "Surabaya, Jawa Timur, Indonesia";
$appEmail = "admin@sewamobil.com";
$appPhone = "+62 812 3456 7890";

if (isset($pdo)) {
    try {
        $stmtSettings = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmtSettings->fetch(PDO::FETCH_ASSOC)) {
            if ($row['setting_key'] === 'app_name') $appName = $row['setting_value'];
            if ($row['setting_key'] === 'company_address') $appAddress = $row['setting_value'];
            if ($row['setting_key'] === 'company_email') $appEmail = $row['setting_value'];
            if ($row['setting_key'] === 'company_phone') $appPhone = $row['setting_value'];
        }
    } catch (Exception $e) {}
}
?>

<aside id="sidebar" :class="sidebarOpen ? '' : 'sb-hidden'"
  class="w-[200px] min-w-[200px] bg-white dark:bg-slate-800 border-r border-slate-100 dark:border-slate-700 flex flex-col h-full overflow-y-auto flex-shrink-0 transition-all">

  <div class="px-4 pt-5 pb-4">
    <span class="font-extrabold tracking-tight">
      <a href="index.php?module=Homepage&action=index" class="no-underline flex items-center">
        <?php
            // Ukuran font logo dikecilkan dari text-2xl menjadi text-lg
            if (stripos($appName, 'sewa') === 0) {
                $restOfName = substr($appName, 4);
                echo '<span class="text-blue-700 dark:text-blue-500 text-lg">Sewa<span class="text-gray-900 dark:text-white">' . htmlspecialchars($restOfName) . '</span></span>';
            } else {
                echo '<span class="text-blue-700 dark:text-blue-500 text-lg">' . htmlspecialchars($appName) . '</span>';
            }
        ?>
      </a>
    </span>
  </div>
  <div class="mx-4 border-t border-slate-100 dark:border-slate-700"></div>

  <div class="relative px-3 py-3">
    <button @click="showProfileMenu = !showProfileMenu"
      class="flex items-center gap-2.5 w-full rounded-xl px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-left group">

      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-700 flex items-center justify-center flex-shrink-0 shadow-sm">
        <span class="text-white text-[10px] font-bold">
          <?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)) ?>
        </span>
      </div>

      <div class="min-w-0 flex-1">
        <p class="text-xs font-bold text-slate-800 dark:text-white truncate">
          <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?>
        </p>
        <p class="text-[9px] text-slate-400 font-medium capitalize">
          <?= htmlspecialchars($_SESSION['admin_role'] ?? 'Staff') ?>
        </p>
      </div>
      <i class="fa-solid fa-chevron-down text-slate-400 text-[9px] transition-transform" :class="showProfileMenu ? 'rotate-180' : ''"></i>
    </button>
    
    <div x-show="showProfileMenu" @click.away="showProfileMenu = false" x-transition
      class="absolute left-3 right-3 mt-1 z-30 bg-white dark:bg-slate-750 rounded-xl shadow-xl border border-slate-100 dark:border-slate-600 overflow-hidden">
      <div class="p-1.5">
        <a href="logout.php" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-[11px] font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </div>
  </div>

  <div class="mx-4 border-t border-slate-100 dark:border-slate-700 mb-2"></div>
  <p class="px-4 pb-1 text-[9px] font-bold text-slate-400 uppercase tracking-widest">Menu</p>

  <nav class="flex-1 px-2 space-y-0.5 pb-4">
    <template x-for="item in navItems" :key="item.key">
      <div>
        <div x-show="item.key === 'settings'" class="pt-2 pb-1">
          <p class="px-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest">Lainnya</p>
        </div>
        <button @click="setPage(item.key)"
          :class="activePage === item.key ? 'active font-semibold text-blue-600' : 'text-slate-500 dark:text-slate-400 font-medium'"
          class="nav-link w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs text-left transition-colors">
          <i class="w-3 text-[13px]" :class="[item.icon, activePage === item.key ? 'text-blue-600' : 'text-slate-400 dark:text-slate-500']"></i>
          <span x-text="item.label"></span>
        </button>
      </div>
    </template>
  </nav>

  <div class="mx-3 mb-4 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
    <p class="text-[10px] font-bold text-blue-600">
        <?php
            if (stripos($appName, 'sewa') === 0) {
                $restOfName = substr($appName, 4);
                echo '<span class="text-blue-700">Sewa<span class="text-slate-600 dark:text-slate-300">' . htmlspecialchars($restOfName) . '</span></span>';
            } else {
                echo htmlspecialchars($appName);
            }
        ?>
    </p>
    <p class="text-[9px] text-blue-400 mt-0.5 opacity-80">v2.0.0 · 2026</p>
  </div>
</aside> 