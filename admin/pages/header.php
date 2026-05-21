<!-- ====================================================
     HEADER (Top Bar)
     Berisi: Tombol hamburger, judul halaman, tombol aksi
===================================================== -->
<header class="flex-shrink-0 bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 px-5 lg:px-6 py-3 flex items-center justify-between gap-3 z-10">
  <div class="flex items-center gap-3 min-w-0">
    <button @click="sidebarOpen = true"
            class="lg:hidden w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 flex items-center justify-center text-slate-500 dark:text-slate-400 flex-shrink-0 transition-colors">
      <i class="fa-solid fa-bars text-sm"></i>
    </button>
    <div>
      <h1 class="text-base font-bold text-slate-800 dark:text-white leading-tight" x-text="pageTitle"></h1>
      <p class="text-xs text-slate-400 font-medium hidden sm:block" x-text="pageSubtitle"></p>
    </div>
  </div>
  <div class="flex items-center gap-2 flex-shrink-0">
    <button class="relative w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 flex items-center justify-center transition-colors">
      <i class="fa-solid fa-bell text-slate-500 dark:text-slate-400 text-sm"></i>
      <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 border-2 border-white dark:border-slate-800"></span>
    </button>
    <button x-show="activePage === 'customers' || activePage === 'dashboard'"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors">
      <i class="fa-solid fa-download text-xs"></i>
      <span class="hidden sm:inline">Export CSV</span>
    </button>
    <button x-show="activePage === 'cars'" @click="openAddCarModal()"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors">
      <i class="fa-solid fa-plus text-xs"></i>
      <span class="hidden sm:inline">Tambah Mobil</span>
    </button>
  </div>
</header>
