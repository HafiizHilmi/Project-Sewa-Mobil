<!-- ====================================================
     HEADER (Topbar 64px — Glassmorphic Surface)
     Mengadaptasi style user portal: rounded pill, glass backdrop, clean icon-btn
===================================================== -->
<header class="h-16 flex-shrink-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 px-5 lg:px-6 flex items-center justify-between gap-4 z-20 transition-colors">
  
  <!-- Kiri: Mobile trigger & Breadcrumb Pill -->
  <div class="flex items-center gap-3 min-w-0">
    <button @click="sidebarOpen = true"
      class="lg:hidden w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 flex-shrink-0 transition-colors"
      aria-label="Buka Menu">
      <i class="fa-solid fa-bars text-xs"></i>
    </button>

    <div class="flex items-center gap-2.5">
      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
        <span class="text-slate-400 dark:text-slate-500">Portal</span>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="text-blue-600 dark:text-blue-400 font-bold capitalize" x-text="activePage"></span>
      </div>
      <h1 class="hidden sm:inline-block text-sm font-extrabold text-slate-800 dark:text-white leading-tight tracking-tight" x-text="pageTitle"></h1>
    </div>
  </div>

  <!-- Tengah: Global search pill (style user catalog search) -->
  <div class="hidden md:flex items-center flex-1 max-w-md mx-4">
    <div class="relative w-full">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
      <input type="text" 
             placeholder="Cari transaksi, armada, pelanggan..." 
             class="w-full pl-9 pr-4 py-2 bg-slate-100/70 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/70 rounded-full text-xs text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
    </div>
  </div>

  <!-- Kanan: Aksi Cepat, Lonceng Notifikasi, Mode Gelap & Profil Pill -->
  <div class="flex items-center gap-2 flex-shrink-0">
    
    <!-- Tombol Khusus Halaman (Pill Style) -->
    <template x-if="activePage === 'cars'">
      <button @click="$dispatch('open-add-car-modal')" 
        class="hidden sm:inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-full transition-all shadow-sm shadow-blue-500/20 active:scale-95">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Tambah Mobil</span>
      </button>
    </template>

    <!-- Tombol Notifikasi Real-time (Icon-btn user style) -->
    <a href="index.php?page=orders" 
       title="Lihat pesanan pending"
       class="relative w-9 h-9 rounded-full bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-sm">
      <i class="fa-solid fa-bell text-xs"></i>
      <span id="bell-badge" 
            style="display: none;" 
            class="absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 bg-red-500 text-white text-[9.5px] font-bold rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-900 animate-pulse">
        0
      </span>
    </a>

    <!-- Toggle Dark Mode (Icon-btn user style) -->
    <button @click="darkMode = !darkMode" 
      title="Ubah tema tampilan"
      class="w-9 h-9 rounded-full bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200/80 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-sm">
      <i class="fa-solid" :class="darkMode ? 'fa-sun text-amber-400 text-xs' : 'fa-moon text-slate-500 text-xs'"></i>
    </button>

    <!-- Divider -->
    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block mx-1"></div>

    <!-- Profil Pill (Mengadaptasi .nav-user dari User Portal) -->
    <div class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-full border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-800/60 hover:border-slate-300 dark:hover:border-slate-600 transition-all select-none">
      <div class="w-7 h-7 rounded-full bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-extrabold flex-shrink-0">
        <?= strtoupper(substr($_SESSION['admin_nama'] ?? 'A', 0, 1)) ?>
      </div>
      <div class="hidden xl:block text-left leading-tight pr-1">
        <span class="block text-xs font-bold text-slate-800 dark:text-slate-100 truncate max-w-[110px]">
          <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?>
        </span>
        <span class="block text-[9.5px] font-bold text-blue-600 dark:text-blue-400 capitalize">
          <?= htmlspecialchars($_SESSION['admin_role'] ?? 'Staff') ?>
        </span>
      </div>
    </div>

  </div>
</header>
