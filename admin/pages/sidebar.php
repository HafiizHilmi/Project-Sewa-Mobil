<!-- ====================================================
     SIDEBAR
     Berisi: Logo, profil admin, navigasi menu
===================================================== -->
<aside id="sidebar"
       :class="sidebarOpen ? '' : 'sb-hidden'"
       class="w-[230px] min-w-[230px] bg-white dark:bg-slate-800 border-r border-slate-100 dark:border-slate-700 flex flex-col h-full overflow-y-auto flex-shrink-0">

  <!-- Logo -->
  <div class="px-5 pt-6 pb-5">
    <span class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight">
      Sewa <span class="text-blue-600">Mobil SBY</span>
    </span>
  </div>
  <div class="mx-5 border-t border-slate-100 dark:border-slate-700"></div>

  <!-- Profile Dropdown -->
  <div class="relative px-4 py-4">
    <button @click="showProfileMenu = !showProfileMenu"
            class="flex items-center gap-3 w-full rounded-xl px-2 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-left group">
      <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-700 flex items-center justify-center flex-shrink-0 shadow-sm">
        <i class="fa-solid fa-user text-white text-xs"></i>
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-sm font-bold text-slate-800 dark:text-white leading-snug">Admin</p>
        <p class="text-xs text-slate-400 dark:text-slate-400 font-medium">Super Admin</p>
      </div>
      <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200"
         :class="showProfileMenu ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="showProfileMenu"
         @click.away="showProfileMenu = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="absolute left-4 right-4 top-full mt-1 z-30 bg-white dark:bg-slate-750 rounded-xl shadow-xl border border-slate-100 dark:border-slate-600 overflow-hidden"
         style="top: calc(100% - 8px);">
      <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-600">
        <div class="flex items-center gap-2.5 mb-2.5">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-700 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-user text-white text-[10px]"></i>
          </div>
          <div>
            <p class="text-xs font-bold text-slate-800 dark:text-white">Admin</p>
            <p class="text-[10px] text-slate-400">Super Admin</p>
          </div>
        </div>
        <div class="flex items-center gap-2 mb-1.5">
          <i class="fa-solid fa-envelope text-slate-400 text-[10px] w-3"></i>
          <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">admin@sewamobilsby.id</p>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
          <p class="text-[10px] text-emerald-600 font-semibold">Aktif</p>
        </div>
      </div>
      <div class="p-1.5">
        <button class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors text-left">
          <i class="fa-solid fa-right-from-bracket text-sm"></i>
          Logout
        </button>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <div class="mx-5 border-t border-slate-100 dark:border-slate-700 mb-2"></div>
  <p class="px-5 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Menu</p>

  <nav class="flex-1 px-3 space-y-0.5 pb-4">
    <template x-for="item in navItems" :key="item.key">
      <div>
        <div x-show="item.key === 'settings'" class="pt-2 pb-1">
          <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Lainnya</p>
        </div>
        <button @click="setPage(item.key)"
                :class="activePage === item.key ? 'active font-semibold text-blue-600' : 'text-slate-500 dark:text-slate-400 font-medium'"
                class="nav-link w-full flex items-center gap-3 px-3 py-2.5 rounded-r-lg text-sm text-left transition-colors">
          <i class="w-4 text-sm" :class="[item.icon, activePage === item.key ? 'text-blue-600' : 'text-slate-400 dark:text-slate-500']"></i>
          <span x-text="item.label"></span>
        </button>
      </div>
    </template>
  </nav>

  <!-- Version Badge -->
  <div class="mx-4 mb-5 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
    <p class="text-xs font-semibold text-blue-600">Sewa Mobil SBY</p>
    <p class="text-[10px] text-blue-400 mt-0.5">v2.0.0 · 2025</p>
  </div>
</aside>
