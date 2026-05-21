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

  <style>
    [x-cloak] { display: none !important; }

    /* Sidebar nav active/hover */
    .nav-link {
      border-left: 3px solid transparent;
      transition: background .15s, color .15s;
    }
    .nav-link:hover:not(.active) {
      background: rgba(100,116,139,.07);
    }
    .nav-link.active {
      border-left-color: #2563eb;
      background: rgba(37,99,235,.08);
      color: #2563eb !important;
    }
    .nav-link.active i { color: #2563eb !important; }

    /* Dark nav hover */
    .dark .nav-link:hover:not(.active) { background: rgba(148,163,184,.07); }
    .dark .nav-link.active { background: rgba(37,99,235,.14); }

    /* Stat card hover */
    .stat-card { transition: transform .2s, box-shadow .2s; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.09); }

    /* Table row hover */
    .tbl-row, .order-row, .car-row { transition: background .12s, box-shadow .18s, transform .18s; cursor: pointer;}
    .tbl-row:hover, .order-row:hover { background: #f8fafc; }
    .car-row:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-1px); }
    .dark .tbl-row:hover, .dark .order-row:hover { background: rgba(148,163,184,.06); }

    /* Slide-over drawer */
    .drawer-enter  { transition: transform .3s cubic-bezier(.22,1,.36,1); }
    .drawer-leave  { transition: transform .25s cubic-bezier(.55,0,1,.45); }

    /* Toggle switch */
    .toggle-track {
      width: 44px; height: 24px;
      background: #e2e8f0;
      border-radius: 9999px;
      transition: background .2s;
      position: relative; cursor: pointer; flex-shrink: 0;
    }
    .toggle-track.on { background: #2563eb; }
    .dark .toggle-track { background: #475569; }
    .dark .toggle-track.on { background: #2563eb; }
    .toggle-thumb {
      position: absolute;
      top: 3px; left: 3px;
      width: 18px; height: 18px;
      background: #fff;
      border-radius: 9999px;
      box-shadow: 0 1px 4px rgba(0,0,0,.2);
      transition: transform .2s cubic-bezier(.22,1,.36,1);
    }
    .toggle-track.on .toggle-thumb { transform: translateX(20px); }

    /* Chart line animation */
    .chart-path { stroke-dasharray: 950; stroke-dashoffset: 950; animation: cDraw 1.8s ease forwards .3s; }
    @keyframes cDraw { to { stroke-dashoffset: 0; } }

    /* Modal animation */
    .modal-box { animation: mIn .22s ease both; }
    .modal-backdrop { animation: mBd .2s ease both; }
    @keyframes mIn { from { opacity:0; transform:translateY(18px) } to { opacity:1; transform:translateY(0) } }
    @keyframes mBd { from { opacity:0 } to { opacity:1 } }

    /* Customer card */
    .cust-row { cursor: pointer; transition: background .12s, box-shadow .15s; }
    .cust-row:hover { background: #f0f7ff; box-shadow: 0 2px 12px rgba(37,99,235,.07); }
    .dark .cust-row:hover { background: rgba(37,99,235,.08); }

    /* Upload zone */
    .upload-zone { border: 2px dashed #cbd5e1; transition: border-color .15s, background .15s; cursor: pointer; }
    .upload-zone:hover { border-color: #3b82f6; background: rgba(239,246,255,.5); }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }
    .dark ::-webkit-scrollbar-thumb { background: #475569; }

    /* Mobile sidebar */
    #sidebar { transition: transform .25s cubic-bezier(.22,1,.36,1); }
    @media (max-width:1023px) {
      #sidebar { position:fixed; top:0; left:0; bottom:0; z-index:50; width:240px !important; box-shadow:4px 0 24px rgba(0,0,0,.12); }
      #sidebar.sb-hidden { transform: translateX(-100%); }
    }
    @media (min-width:1024px) { #sidebar { transform: translateX(0) !important; } }

    /* Page transition */
    .page-fade {
      animation: pfIn .2s ease both;
    }
    @keyframes pfIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

    /* Progress fill */
    .prog-fill { background: linear-gradient(90deg,#3b82f6,#1d4ed8); border-radius:9999px; height:100%; transition:width .7s ease; }
  </style>
</head>

<body x-data="appData()" x-cloak class="font-sans antialiased bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200">

<div class="flex h-screen overflow-hidden">

  <div x-show="sidebarOpen"
       @click="sidebarOpen = false"
       class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
  </div>

  <aside id="sidebar"
         :class="sidebarOpen ? '' : 'sb-hidden'"
         class="w-[230px] min-w-[230px] bg-white dark:bg-slate-800 border-r border-slate-100 dark:border-slate-700 flex flex-col h-full overflow-y-auto flex-shrink-0">

    <div class="px-5 pt-6 pb-5">
      <span class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight">
        Sewa <span class="text-blue-600">Mobil SBY</span>
      </span>
    </div>
    <div class="mx-5 border-t border-slate-100 dark:border-slate-700"></div>

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

    <div class="mx-4 mb-5 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
      <p class="text-xs font-semibold text-blue-600">Sewa Mobil SBY</p>
      <p class="text-[10px] text-blue-400 mt-0.5">v2.0.0 · 2025</p>
    </div>
  </aside>

  <div class="flex-1 flex flex-col overflow-hidden">

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

    <main class="flex-1 overflow-hidden relative">

      <div x-show="activePage === 'dashboard'"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           class="absolute inset-0 overflow-y-auto px-5 lg:px-7 py-6 space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
          <div class="stat-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Mobil</p>
                <p class="text-4xl font-extrabold text-slate-800 dark:text-white">15</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                <i class="fa-solid fa-car text-blue-500 text-lg"></i>
              </div>
            </div>
            <div class="flex items-center gap-1.5 pt-3 border-t border-slate-50 dark:border-slate-700">
              <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 text-xs font-semibold px-2 py-0.5 rounded-full"><i class="fa-solid fa-arrow-up text-[9px]"></i>+1</span>
              <span class="text-xs text-slate-400">this month</span>
            </div>
          </div>
          <div class="stat-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Pelanggan Aktif</p>
                <p class="text-4xl font-extrabold text-slate-800 dark:text-white">223</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center">
                <i class="fa-solid fa-user-group text-orange-400 text-lg"></i>
              </div>
            </div>
            <div class="flex items-center gap-1.5 pt-3 border-t border-slate-50 dark:border-slate-700">
              <span class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 text-xs font-semibold px-2 py-0.5 rounded-full"><i class="fa-solid fa-arrow-trend-up text-[9px]"></i>+8.4%</span>
              <span class="text-xs text-slate-400">vs last week</span>
            </div>
          </div>
          <div class="stat-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 sm:col-span-2 xl:col-span-1">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Total Keuntungan</p>
                <p class="text-2xl font-extrabold text-slate-800 dark:text-white">Rp 32.000.000</p>
              </div>
              <div class="w-11 h-11 rounded-2xl bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center">
                <i class="fa-solid fa-wallet text-yellow-500 text-lg"></i>
              </div>
            </div>
            <div class="flex items-center gap-1.5 pt-3 border-t border-slate-50 dark:border-slate-700">
              <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 text-xs font-semibold px-2 py-0.5 rounded-full"><i class="fa-solid fa-arrow-trend-up text-[9px]"></i>+14.2%</span>
              <span class="text-xs text-slate-400">Year on Year</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-4">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Rental Trends</h2>
                <p class="text-xs text-slate-400 mt-0.5">Performa bulanan armada kamu</p>
              </div>
              <div class="flex items-center bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5">
                <button @click="chartMode='weekly'" :class="chartMode==='weekly' ? 'bg-slate-800 dark:bg-slate-600 text-white' : 'text-slate-500 dark:text-slate-400'" class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all">Weekly</button>
                <button @click="chartMode='monthly'" :class="chartMode==='monthly' ? 'bg-slate-800 dark:bg-slate-600 text-white' : 'text-slate-500 dark:text-slate-400'" class="text-xs font-semibold px-3 py-1.5 rounded-md transition-all">Monthly</button>
              </div>
            </div>
            <svg viewBox="0 0 660 180" class="w-full h-auto">
              <defs>
                <linearGradient id="g1" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#3b82f6" stop-opacity=".12"/>
                  <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <line x1="36" y1="10" x2="650" y2="10" stroke="#f1f5f9" stroke-width="1.2"/>
              <line x1="36" y1="50" x2="650" y2="50" stroke="#f1f5f9" stroke-width="1.2"/>
              <line x1="36" y1="90" x2="650" y2="90" stroke="#f1f5f9" stroke-width="1.2"/>
              <line x1="36" y1="130" x2="650" y2="130" stroke="#f1f5f9" stroke-width="1.2"/>
              <text x="30" y="14" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">10k</text>
              <text x="30" y="54" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">8k</text>
              <text x="30" y="94" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">6k</text>
              <text x="30" y="134" font-size="10" fill="#94a3b8" text-anchor="end" font-family="Plus Jakarta Sans,sans-serif">4k</text>
              <path d="M 100,120 C 160,100 185,70 215,55 C 245,40 280,110 340,38 C 400,100 445,75 510,48 C 550,33 590,28 630,20 L 630,150 L 100,150 Z" fill="url(#g1)"/>
              <path class="chart-path" d="M 100,120 C 160,100 185,70 215,55 C 245,40 280,110 340,38 C 400,100 445,75 510,48 C 550,33 590,28 630,20"
                    fill="none" stroke="#3b82f6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
              <circle cx="340" cy="38" r="5" fill="#3b82f6" stroke="#fff" stroke-width="2.5"/>
              <text x="100" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif">Jan</text>
              <text x="215" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif">Feb</text>
              <text x="340" y="172" font-size="10" fill="#1e293b" text-anchor="middle" font-weight="700" font-family="Plus Jakarta Sans,sans-serif" class="dark:fill-slate-200">Apr</text>
              <text x="510" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif">May</text>
              <text x="630" y="172" font-size="10" fill="#94a3b8" text-anchor="middle" font-family="Plus Jakarta Sans,sans-serif">Jun</text>
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
      
      <div x-show="activePage === 'cars'"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           class="absolute inset-0 overflow-y-auto">

        <div x-show="!showCarDetail" class="px-5 lg:px-6 py-5">

          <div class="flex items-center gap-2 flex-wrap mb-5">
            <template x-for="f in ['All','MPV','SUV','Sedan','EV']" :key="f">
              <button @click="carFilter = f"
                      :class="carFilter === f
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                        : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-blue-300'"
                      class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-all"
                      x-text="f">
              </button>
            </template>
          </div>

          <div class="space-y-3">
            <template x-for="car in filteredCars" :key="car.id">
              <div class="car-row bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3.5 flex items-center gap-3 sm:gap-4">

                <div class="w-20 sm:w-24 h-14 sm:h-16 rounded-xl flex items-center justify-center flex-shrink-0" :class="car.bgCls">
                  <i class="fa-solid fa-car text-3xl sm:text-4xl" :class="car.iconCls"></i>
                </div>

                <div class="flex-1 min-w-0">
                  <p class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-snug truncate" x-text="car.name"></p>
                  <p class="text-xs text-slate-400 mt-0.5" x-text="car.plate"></p>
                  <div class="flex items-center gap-2 mt-1.5 sm:hidden">
                    <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full"
                          :class="carBadge(car.status)" x-text="car.status"></span>
                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full" x-text="car.category"></span>
                  </div>
                </div>

                <div class="hidden sm:block flex-shrink-0 w-28">
                  <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Status</p>
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full"
                        :class="carBadge(car.status)" x-text="car.status"></span>
                </div>

                <div class="hidden sm:block flex-shrink-0 w-16">
                  <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Kategori</p>
                  <p class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="car.category"></p>
                </div>

                <div class="hidden md:block flex-shrink-0 w-32">
                  <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Harga/Hari</p>
                  <p class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="car.price"></p>
                </div>

                <div class="flex flex-col gap-1.5 flex-shrink-0">
                  <button @click="viewCar(car)"
                          class="w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition-all hover:scale-105 shadow-sm">
                    <i class="fa-solid fa-eye text-white text-xs"></i>
                  </button>
                  <button @click="openEditCarModal(car)"
                          class="w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition-all hover:scale-105 shadow-sm">
                    <i class="fa-solid fa-pen text-white text-[10px]"></i>
                  </button>
                </div>

              </div>
            </template>

            <div x-show="filteredCars.length === 0" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-10 text-center">
              <i class="fa-solid fa-car-burst text-4xl text-slate-200 dark:text-slate-700 mb-3 block"></i>
              <p class="text-sm text-slate-400 font-medium">Tidak ada kendaraan untuk kategori ini</p>
            </div>
          </div>
        </div>

        <div x-show="showCarDetail" class="px-5 lg:px-6 py-5">
          <button @click="showCarDetail = false; selectedCar = null;"
                  class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-blue-600 mb-4 transition-colors bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-300 px-3 py-1.5 rounded-lg">
            <i class="fa-solid fa-arrow-left text-xs"></i>Kembali ke Daftar
          </button>

          <template x-if="selectedCar">
            <div class="page-fade">
              <div class="mb-5">
                <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white leading-tight" x-text="selectedCar.name + ' ' + selectedCar.year"></h2>
                <p class="text-sm text-slate-400 font-medium mt-1" x-text="selectedCar.plate"></p>
              </div>

              <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-4">
                <div class="space-y-4">
                  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="h-[210px] flex flex-col items-center justify-center gap-3" :class="selectedCar.bgCls">
                      <i class="fa-solid fa-car text-[76px] opacity-60" :class="selectedCar.iconCls"></i>
                      <span class="text-xs font-semibold opacity-75" :class="selectedCar.iconCls" x-text="selectedCar.name + ' ' + selectedCar.year"></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-700/50 border-t border-slate-100 dark:border-slate-700 px-5 py-2.5">
                      <p class="text-xs text-slate-500">Nomor Rangka:&nbsp;<span class="font-semibold text-slate-700 dark:text-slate-200" x-text="selectedCar.chassis"></span></p>
                    </div>
                  </div>

                  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden relative" style="height:190px;">
                    <div class="absolute inset-0" style="background:#e8f0e8;background-image:linear-gradient(rgba(160,185,160,.45) 1px,transparent 1px),linear-gradient(90deg,rgba(160,185,160,.45) 1px,transparent 1px);background-size:36px 36px;">
                      <div class="absolute" style="left:0;right:0;top:38%;height:18px;background:rgba(255,255,255,.85)"></div>
                      <div class="absolute" style="left:0;right:0;top:68%;height:14px;background:rgba(255,255,255,.85)"></div>
                      <div class="absolute" style="top:0;bottom:0;left:33%;width:18px;background:rgba(255,255,255,.85)"></div>
                      <div class="absolute" style="top:0;bottom:0;left:63%;width:14px;background:rgba(255,255,255,.85)"></div>
                    </div>
                    <div class="absolute top-3 left-3 z-10 flex items-center gap-1.5 bg-white rounded-full px-3 py-1.5 shadow-sm border border-slate-100">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                      <span class="text-xs font-semibold text-slate-700">GPS Aktif</span>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center z-10">
                      <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center shadow-xl border-[3px] border-white">
                          <i class="fa-solid fa-car text-white text-xs"></i>
                        </div>
                        <div class="absolute -inset-2 rounded-full border-2 border-blue-400 opacity-30 animate-ping"></div>
                      </div>
                    </div>
                    <button class="absolute bottom-3 right-3 z-10 flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                      <i class="fa-solid fa-rotate-right text-[10px]"></i>Refresh Lokasi
                    </button>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                    <div class="flex items-center gap-2.5 mb-4">
                      <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-circle-info text-blue-500 text-xs"></i>
                      </div>
                      <h3 class="text-sm font-bold text-slate-800 dark:text-white">Spesifikasi Kendaraan</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                      <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Tahun</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="selectedCar.year"></p>
                      </div>
                      <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Bahan Bakar</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white leading-tight" x-text="selectedCar.fuel"></p>
                      </div>
                      <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Transmisi</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="selectedCar.transmission"></p>
                      </div>
                      <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Penumpang</p>
                        <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="selectedCar.passengers"></p>
                      </div>
                    </div>
                    <div class="border-t border-slate-100 dark:border-slate-700 pt-3">
                      <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Harga Sewa</p>
                      <p class="text-base font-extrabold text-blue-600" x-text="selectedCar.price + ' / hari'"></p>
                    </div>
                  </div>

                  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center">
                          <i class="fa-solid fa-user text-orange-400 text-xs"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Penyewa Mobil</h3>
                      </div>
                      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :class="selectedCar.renter === '-' ? 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'"
                            x-text="selectedCar.renter === '-' ? 'Tidak Ada' : 'Aktif'"></span>
                    </div>
                    
                    <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl px-3.5 py-3 mb-4 cursor-pointer transition-colors">
                      <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center flex-shrink-0">
                          <i class="fa-solid fa-user text-white text-xs"></i>
                        </div>
                        <div>
                          <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="selectedCar.renter"></p>
                          <p class="text-[10px] text-slate-400 mt-0.5">Penyewa Aktif</p>
                        </div>
                      </div>
                      <i class="fa-solid fa-chevron-right text-slate-300 dark:text-slate-500 text-xs"></i>
                    </div>
                    
                    <div>
                      <div class="flex items-center justify-between mb-1.5">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Durasi Rental</p>
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full" x-text="selectedCar.rentalLeft"></span>
                      </div>
                      <div class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden mb-2">
                        <div class="prog-fill" :style="'width:' + selectedCar.rentalPct + '%'"></div>
                      </div>
                      <div class="flex justify-between">
                        <p class="text-[10px] text-slate-400 font-medium" x-text="selectedCar.rentalStart"></p>
                        <p class="text-[10px] text-slate-400 font-medium" x-text="selectedCar.rentalEnd"></p>
                      </div>
                    </div>
                  </div>

                </div></div>
            </div>
          </template>
        </div>
      </div>
      
      <div x-show="activePage === 'orders'"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

        <div class="flex items-start sm:items-center justify-between flex-wrap gap-3 mb-5">
          <div class="flex items-center gap-2 flex-wrap">
            <template x-for="tab in ['All Orders','Pending','Confirmed','Completed']" :key="tab">
              <button @click="orderFilter = tab"
                      :class="orderFilter === tab
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                        : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-blue-300'"
                      class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-all"
                      x-text="tab">
              </button>
            </template>
          </div>
          <div class="flex items-center gap-2">
            <button class="flex items-center gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
              <i class="fa-regular fa-calendar text-slate-400 text-xs"></i>
              Apr 26 – May 2 2026
            </button>
            <button class="w-8 h-8 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg flex items-center justify-center text-slate-500 transition-colors">
              <i class="fa-solid fa-sliders text-xs"></i>
            </button>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
          <div class="hidden md:grid px-5 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-[11px] font-bold uppercase tracking-wider"
               style="grid-template-columns: 115px 1fr 1fr 165px 110px 120px 48px; gap: 12px;">
            <div>ID Order</div>
            <div>Pelanggan</div>
            <div>Detail Mobil</div>
            <div>Waktu Sewa</div>
            <div>Total</div>
            <div>Status</div>
            <div class="text-right">Aksi</div>
          </div>

          <div>
            <template x-for="(order, idx) in filteredOrders" :key="order.idLine2">
              <div>
                <div x-show="idx > 0" class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>
                
                <div @click="openOrderModal(order)" class="order-row px-5 py-4 dark:hover:bg-slate-700/30">
                  
                  <div class="hidden md:grid items-center" style="grid-template-columns: 115px 1fr 1fr 165px 110px 120px 48px; gap: 12px;">
                    <div>
                      <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-snug" x-text="order.idLine1"></p>
                      <p class="text-xs font-bold text-slate-800 dark:text-slate-100" x-text="order.idLine2"></p>
                    </div>
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                      </div>
                      <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-snug" x-text="order.customer.name"></p>
                        <p class="text-[10px] text-blue-500 font-medium truncate" x-text="order.customer.email"></p>
                        <p class="text-[10px] text-slate-400" x-text="order.customer.phone"></p>
                      </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                      <div class="w-14 h-10 rounded-lg flex items-center justify-center flex-shrink-0" :class="order.car.thumbBg">
                        <i class="fa-solid fa-car text-xl" :class="order.car.thumbColor"></i>
                      </div>
                      <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-snug truncate" x-text="order.car.name"></p>
                        <p class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-0.5">
                          <span x-text="order.car.category"></span><span class="mx-0.5">•</span><span x-text="order.car.fuel"></span>
                        </p>
                      </div>
                    </div>
                    <div class="space-y-2">
                      <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-plane-departure text-slate-400 text-[10px] w-3.5"></i>
                        <span class="text-[11px] text-slate-600 dark:text-slate-300 font-medium" x-text="order.startDate"></span>
                      </div>
                      <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-plane-arrival text-slate-400 text-[10px] w-3.5"></i>
                        <span class="text-[11px] text-slate-600 dark:text-slate-300 font-medium" x-text="order.endDate"></span>
                      </div>
                    </div>
                    <div>
                      <p class="text-[10px] text-slate-400 font-medium">Rp</p>
                      <p class="text-xs font-bold text-slate-800 dark:text-slate-100" x-text="order.totalFormatted"></p>
                    </div>
                    <div>
                      <span class="inline-block text-[11px] font-bold px-3 py-1 rounded-full"
                            :class="{
                              'bg-blue-600 text-white' : order.status === 'Confirmed',
                              'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400': order.status === 'Pending',
                              'bg-emerald-500 text-white' : order.status === 'Completed',
                            }"
                            x-text="order.status">
                      </span>
                    </div>
                    <div class="flex justify-end" @click.stop>
                      <button class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-sm"></i>
                      </button>
                    </div>
                  </div>

                  <div class="md:hidden flex items-start gap-3">
                    <div class="w-12 h-10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" :class="order.car.thumbBg">
                      <i class="fa-solid fa-car text-lg" :class="order.car.thumbColor"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between mb-0.5">
                        <p class="text-xs font-bold text-slate-500" x-text="order.idLine1 + ' ' + order.idLine2"></p>
                        <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full"
                              :class="{
                                'bg-blue-600 text-white' : order.status === 'Confirmed',
                                'bg-orange-100 text-orange-600': order.status === 'Pending',
                                'bg-emerald-500 text-white' : order.status === 'Completed',
                              }" x-text="order.status"></span>
                      </div>
                      <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="order.customer.name"></p>
                      <p class="text-xs text-slate-400 mt-0.5" x-text="order.car.name + ' · ' + order.car.category"></p>
                      <div class="flex items-center justify-between mt-1.5">
                        <p class="text-xs text-slate-500" x-text="order.startDate + ' – ' + order.endDate"></p>
                        <p class="text-xs font-bold text-slate-800 dark:text-white">Rp <span x-text="order.totalFormatted"></span></p>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </template>

            <div x-show="filteredOrders.length === 0" class="py-16 text-center">
              <i class="fa-solid fa-inbox text-4xl text-slate-200 dark:text-slate-700 mb-3 block"></i>
              <p class="text-sm text-slate-400 font-medium">Tidak ada pesanan dengan status ini</p>
            </div>
          </div>
        </div>
        
        <div class="flex items-center justify-between mt-4">
          <p class="text-xs text-slate-400">
            Menampilkan <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="filteredOrders.length"></span>
            dari <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="orders.length"></span> pesanan
          </p>
        </div>
      </div>
      
      <div x-show="activePage === 'customers'"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

        <div class="flex items-center gap-3 flex-wrap mb-5">
          <div class="relative flex-1 min-w-[200px] max-w-xs">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            <input x-model="custSearch" type="text" placeholder="Cari Pelanggan..."
                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-800 focus:border-blue-400 transition-all"/>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <template x-for="s in ['Semua','Aktif','Nonaktif','Suspended']" :key="s">
              <button @click="custStatus = s"
                      :class="custStatus === s
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                        : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-blue-300'"
                      class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition-all"
                      x-text="s">
              </button>
            </template>
          </div>
          <button class="w-9 h-9 flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-500 dark:text-slate-400 hover:border-slate-300 transition-colors">
            <i class="fa-solid fa-sliders text-sm"></i>
          </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
          <div class="hidden md:grid px-5 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-[11px] font-bold uppercase tracking-wider"
               style="grid-template-columns: 48px 40px 1fr 150px 110px 90px 120px 110px 50px; gap: 10px;">
            <div class="flex items-center"><input type="checkbox" class="rounded accent-white w-3.5 h-3.5 cursor-pointer"/></div>
            <div>No</div><div>Pelanggan</div><div>No HP</div><div>Kota</div><div class="text-center">Total Pesan</div><div>Terakhir Pesan</div><div>Status</div><div class="text-right">Aksi</div>
          </div>

          <div>
            <template x-for="(c, idx) in filteredCustomers" :key="c.id">
              <div>
                <div x-show="idx > 0" class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>
                <div @click="openCustomerDetail(c)" class="cust-row hidden md:grid px-5 py-3.5 items-center dark:hover:bg-slate-700/30"
                     style="grid-template-columns: 48px 40px 1fr 150px 110px 90px 120px 110px 50px; gap: 10px;">
                  <div @click.stop><input type="checkbox" class="rounded accent-blue-600 w-3.5 h-3.5 cursor-pointer"/></div>
                  <div class="text-sm font-semibold text-slate-500 dark:text-slate-400" x-text="idx + 1"></div>
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold shadow-sm" :style="'background: ' + c.avatarColor">
                      <span x-text="c.name.charAt(0)"></span>
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="c.name"></p>
                      <p class="text-[10px] text-blue-500 truncate" x-text="c.email"></p>
                    </div>
                  </div>
                  <div class="text-sm text-slate-600 dark:text-slate-300 font-medium" x-text="c.phone"></div>
                  <div class="text-sm text-slate-600 dark:text-slate-300 font-medium" x-text="c.city"></div>
                  <div class="text-sm font-bold text-slate-700 dark:text-slate-200 text-center" x-text="c.totalOrders"></div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="c.lastOrder"></div>
                  <div>
                    <span class="inline-block text-[11px] font-bold px-3 py-1 rounded-full"
                          :class="{
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : c.status === 'Aktif',
                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : c.status === 'Baru',
                            'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : c.status === 'Nonaktif',
                            'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : c.status === 'Suspended',
                          }" x-text="c.status"></span>
                  </div>
                  <div class="flex justify-end" @click.stop>
                    <button class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                      <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-sm"></i>
                    </button>
                  </div>
                </div>
                <div @click="openCustomerDetail(c)" class="cust-row md:hidden flex items-center gap-3 px-4 py-3.5 dark:hover:bg-slate-700/30">
                  <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold shadow-sm" :style="'background: ' + c.avatarColor">
                    <span x-text="c.name.charAt(0)"></span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="c.name"></p>
                      <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full flex-shrink-0 ml-2"
                            :class="{'bg-emerald-100 text-emerald-700': c.status === 'Aktif', 'bg-blue-100 text-blue-700': c.status === 'Baru', 'bg-slate-100 text-slate-500': c.status === 'Nonaktif', 'bg-red-100 text-red-600': c.status === 'Suspended'}" x-text="c.status"></span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="c.phone + ' · ' + c.city"></p>
                  </div>
                </div>
              </div>
            </template>
            <div x-show="filteredCustomers.length === 0" class="py-16 text-center">
              <i class="fa-solid fa-users text-4xl text-slate-200 dark:text-slate-700 mb-3 block"></i>
              <p class="text-sm text-slate-400 font-medium">Tidak ada pelanggan ditemukan</p>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-between mt-4">
          <p class="text-xs text-slate-400">Menampilkan <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="filteredCustomers.length"></span> dari <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="customers.length"></span> pelanggan</p>
        </div>
      </div>
      
      <div x-show="activePage === 'settings'"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
           class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

        <div class="flex items-center border-b border-slate-200 dark:border-slate-700 mb-6 gap-1">
          <template x-for="tab in settingsTabs" :key="tab.key">
            <button @click="activeTab = tab.key"
                    :class="activeTab === tab.key ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'"
                    class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap" x-text="tab.label">
            </button>
          </template>
        </div>

        <div x-show="activeTab === 'admin'" class="page-fade">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-sm font-bold text-slate-800 dark:text-white">Daftar Admin</h2>
              <p class="text-xs text-slate-400 mt-0.5">Kelola akses pengguna sistem</p>
            </div>
            <button @click="showAddAdminModal = true" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors">
              <i class="fa-solid fa-plus text-xs"></i>Tambah Admin
            </button>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="hidden sm:grid px-5 py-3.5 bg-gradient-to-r from-slate-700 to-slate-800 text-slate-200 text-[11px] font-bold uppercase tracking-wider" style="grid-template-columns: 1fr 180px 110px 140px 48px; gap: 12px;">
              <div>Admin</div><div>Email</div><div>Role</div><div>Ditambahkan</div><div></div>
            </div>
            <template x-for="(admin, i) in admins" :key="admin.id">
              <div>
                <div x-show="i > 0" class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>
                <div class="tbl-row hidden sm:grid px-5 py-4 items-center dark:hover:bg-slate-700/30" style="grid-template-columns: 1fr 180px 110px 140px 48px; gap: 12px;">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="'background: ' + admin.color">
                      <span x-text="admin.name.charAt(0)"></span>
                    </div>
                    <div><p class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="admin.name"></p></div>
                  </div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 truncate" x-text="admin.email"></div>
                  <div>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-md" :class="admin.role === 'Super Admin' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'" x-text="admin.role"></span>
                  </div>
                  <div class="text-xs text-slate-400 font-medium" x-text="admin.addedAt"></div>
                  <div class="flex justify-end">
                    <button x-show="admin.role !== 'Super Admin'" class="w-7 h-7 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                      <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div x-show="activeTab === 'business'" class="page-fade">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center"><i class="fa-solid fa-building text-blue-500 text-sm"></i></div>
              <div><h2 class="text-sm font-bold text-slate-800 dark:text-white">Informasi Bisnis</h2><p class="text-xs text-slate-400">Konfigurasi data utama perusahaan</p></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Toko</label><input type="text" value="Sewa Mobil SBY" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 focus:outline-none focus:border-blue-400"/></div>
              <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nomor Telepon CS</label><input type="text" value="08123456789" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 focus:outline-none focus:border-blue-400"/></div>
            </div>
            <button class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
              <i class="fa-solid fa-floppy-disk text-xs"></i>Save Changes
            </button>
          </div>
        </div>

        <div x-show="activeTab === 'preferences'" class="page-fade">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
              <h2 class="text-sm font-bold text-slate-800 dark:text-white">Preferensi Tampilan</h2>
              <p class="text-xs text-slate-400 mt-0.5">Atur pengalaman menggunakan dashboard</p>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">

              <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-envelope text-blue-500 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Email Notifications</p>
                    <p class="text-xs text-slate-400 mt-0.5">Terima notifikasi pesanan dan pembayaran via email</p>
                  </div>
                </div>
                <div @click="emailNotifs = !emailNotifs" class="toggle-track ml-4 flex-shrink-0" :class="emailNotifs ? 'on' : ''">
                  <div class="toggle-thumb"></div>
                </div>
              </div>

              <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-bell text-orange-400 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Push Notifications</p>
                    <p class="text-xs text-slate-400 mt-0.5">Notifikasi real-time di browser</p>
                  </div>
                </div>
                <div @click="pushNotifs = !pushNotifs" class="toggle-track ml-4 flex-shrink-0" :class="pushNotifs ? 'on' : ''">
                  <div class="toggle-thumb"></div>
                </div>
              </div>

              <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors" :class="darkMode ? 'bg-indigo-900/30' : 'bg-indigo-50'">
                    <i class="fa-solid text-indigo-500 text-sm" :class="darkMode ? 'fa-moon' : 'fa-sun'"></i>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Dark Mode Theme</p>
                    <p class="text-xs text-slate-400 mt-0.5">Mode gelap untuk penggunaan di lingkungan redup</p>
                  </div>
                </div>
                <div @click="darkMode = !darkMode" class="toggle-track ml-4 flex-shrink-0" :class="darkMode ? 'on' : ''">
                  <div class="toggle-thumb"></div>
                </div>
              </div>

              <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3.5 min-w-0">
                  <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-rotate text-emerald-500 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Auto Refresh Data</p>
                    <p class="text-xs text-slate-400 mt-0.5">Perbarui data dashboard setiap 5 menit otomatis</p>
                  </div>
                </div>
                <div @click="autoRefresh = !autoRefresh" class="toggle-track ml-4 flex-shrink-0" :class="autoRefresh ? 'on' : ''">
                  <div class="toggle-thumb"></div>
                </div>
              </div>

            </div>
          </div>
        </div></div></main>
  </div>
</div><div x-show="showCustomerDetail" @click="showCustomerDetail = false" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm" x-transition.opacity></div>
<div x-show="showCustomerDetail" class="fixed right-0 top-0 bottom-0 z-50 w-full max-w-sm bg-white dark:bg-slate-800 shadow-2xl flex flex-col overflow-hidden"
     x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
     x-transition:leave="transform transition ease-in duration-250" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
  <template x-if="selectedCustomer">
    <div class="flex flex-col h-full">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Detail Pelanggan</h2>
        <button @click="showCustomerDetail = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400"><i class="fa-solid fa-xmark text-xs"></i></button>
      </div>
      <div class="flex-1 overflow-y-auto">
        <div class="px-5 py-5 flex items-center gap-4 border-b border-slate-100 dark:border-slate-700">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-extrabold shadow-lg" :style="'background: ' + selectedCustomer.avatarColor">
            <span x-text="selectedCustomer.name.charAt(0)"></span>
          </div>
          <div>
            <p class="text-lg font-extrabold text-slate-800 dark:text-white" x-text="selectedCustomer.name"></p>
            <p class="text-xs text-slate-400 mt-0.5" x-text="selectedCustomer.address"></p>
          </div>
        </div>
        <div class="px-5 py-4 space-y-4">
          <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Informasi Pribadi</p>
            <div class="space-y-2.5">
              <div class="flex items-center gap-3"><div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center"><i class="fa-solid fa-phone text-slate-400 text-[10px]"></i></div><div><p class="text-[10px] text-slate-400">No. HP</p><p class="text-xs font-bold dark:text-slate-100" x-text="selectedCustomer.phone"></p></div></div>
              <div class="flex items-center gap-3"><div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center"><i class="fa-solid fa-envelope text-slate-400 text-[10px]"></i></div><div class="min-w-0"><p class="text-[10px] text-slate-400">Email</p><p class="text-xs font-bold text-blue-500 truncate" x-text="selectedCustomer.email"></p></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
</div>

<div x-show="showAddAdminModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
  <div @click="showAddAdminModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
  <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col p-5">
    <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Tambah Admin Baru</h2>
    <div class="space-y-4 mb-5">
      <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Lengkap</label><input type="text" class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white"/></div>
      <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Email</label><input type="email" class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white"/></div>
    </div>
    <div class="flex gap-2">
      <button @click="showAddAdminModal = false" class="flex-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border dark:border-slate-600 px-4 py-2.5 rounded-xl">Batal</button>
      <button @click="showAddAdminModal = false" class="flex-1 text-xs font-semibold text-white bg-blue-600 px-4 py-2.5 rounded-xl">Simpan</button>
    </div>
  </div>
</div>

<div x-show="showOrderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
  <div @click="showOrderModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
  <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col p-5">
    <template x-if="selectedOrder">
      <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Detail Pesanan <span x-text="selectedOrder.idLine2"></span></h2>
        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 mb-4">
          <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Pelanggan</p>
          <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="selectedOrder.customer.name"></p>
          <p class="text-xs text-slate-500 mt-1" x-text="selectedOrder.car.name"></p>
        </div>
        <div class="bg-blue-600 rounded-xl p-4 flex justify-between items-center text-white">
          <p class="text-sm font-semibold">Total Pembayaran</p><p class="text-base font-extrabold" x-text="'Rp ' + selectedOrder.totalFormatted"></p>
        </div>
        <button @click="showOrderModal = false" class="w-full mt-4 bg-slate-200 dark:bg-slate-700 py-2.5 rounded-xl font-semibold dark:text-white text-sm">Tutup</button>
      </div>
    </template>
  </div>
</div>

<div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
  <div @click="showEditModal = false" class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
  <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden" style="max-height:92vh;">
    
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
      <h2 class="text-sm font-bold text-slate-800 dark:text-white" x-text="editModalTitle"></h2>
      <button @click="showEditModal = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 flex items-center justify-center"><i class="fa-solid fa-xmark text-slate-500 dark:text-slate-400 text-xs"></i></button>
    </div>

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Foto Mobil</label>
        <div class="upload-zone rounded-xl p-4 flex flex-col items-center gap-2">
          <i class="fa-solid fa-cloud-arrow-up text-blue-400 text-base"></i>
          <p class="text-xs text-slate-500 text-center">Klik atau seret foto ke sini</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Merk Mobil</label><input type="text" x-model="editForm.name" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-blue-100"/></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Plat Nomor</label><input type="text" x-model="editForm.plate" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">No Rangka</label><input type="text" x-model="editForm.chassis" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kategori</label><select x-model="editForm.category" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"><option value="MPV">MPV</option><option value="SUV">SUV</option><option value="Sedan">Sedan</option><option value="EV">EV</option></select></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Harga Sewa / Hari</label><input type="number" x-model="editForm.price" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis BBM</label><input type="text" x-model="editForm.fuel" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas Mesin</label><input type="text" x-model="editForm.engine" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
        <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas Penumpang</label><input type="number" x-model="editForm.passengers" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Transmisi</label>
        <div class="flex gap-4">
          <label class="flex items-center gap-2"><input type="radio" x-model="editForm.transmission" value="Manual" class="accent-blue-600"> <span class="text-xs">Manual</span></label>
          <label class="flex items-center gap-2"><input type="radio" x-model="editForm.transmission" value="Matic" class="accent-blue-600"> <span class="text-xs">Matic</span></label>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-between px-5 py-3 border-t dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
      <button @click="deleteCar()" class="text-red-600 text-xs font-bold px-3 py-2 bg-red-50 rounded-lg">Hapus Kendaraan</button>
      <div class="flex gap-2">
        <button @click="showEditModal = false" class="text-slate-600 text-xs font-bold px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">Batal</button>
        <button @click="saveCar()" class="text-white text-xs font-bold px-4 py-2 bg-blue-600 rounded-lg">Simpan</button>
      </div>
    </div>
  </div>
</div>


<script>
function appData() {
  return {
    /* ── Core State ── */
    activePage: 'dashboard',
    sidebarOpen: false,
    showProfileMenu: false,
    darkMode: false,
    chartMode: 'monthly',

    /* ── Init ── */
    init() {
      this.$watch('darkMode', val => {
        document.documentElement.classList.toggle('dark', val);
      });
    },

    /* ── Navigation ── */
    navItems: [
      { key: 'dashboard', label: 'Dashboard',      icon: 'fa-solid fa-gauge-high' },
      { key: 'cars',      label: 'Car Management', icon: 'fa-solid fa-car'        },
      { key: 'orders',    label: 'Orders',         icon: 'fa-solid fa-clipboard-list'},
      { key: 'customers', label: 'Customers',      icon: 'fa-solid fa-users'      },
      { key: 'settings',  label: 'Settings',       icon: 'fa-solid fa-gear'       },
    ],

    setPage(p) {
      this.activePage = p;
      this.sidebarOpen = false;
      this.showCarDetail = false;
    },

    get pageTitle() {
      return { dashboard:'Dashboard', cars:'Car Management', orders:'Orders', customers:'Customers', settings:'Settings' }[this.activePage];
    },
    get pageSubtitle() {
      return {
        dashboard: 'Apa yang terjadi pada rental kamu hari ini',
        cars: 'Kelola armada kendaraan rental kamu',
        orders: 'Pantau dan kelola semua pesanan rental',
        customers: 'Data pelanggan setia SewaMobil',
        settings: 'Konfigurasi panel administrasi'
      }[this.activePage];
    },

    /* ── Dashboard ── */
    dashAlerts: [
      { icon:'fa-car', bg:'bg-orange-50 dark:bg-orange-900/20', color:'text-orange-400', title:'Vehicle Maintenance Due', desc:'Toyota Avanza 2024 requires scheduled service.', time:'2 hours ago' },
      { icon:'fa-user-plus', bg:'bg-blue-50 dark:bg-blue-900/20', color:'text-blue-500', title:'New Corporate Account', desc:'PT Sukses completed onboarding process.', time:'5 hours ago' },
    ],

    /* ── Customers ── */
    custSearch: '',
    custStatus: 'Semua',
    selectedCustomer: null,
    showCustomerDetail: false,
    customers: [
      { id:1, name:'Sucipto Haryono', email:'sucipto12@gmail.com', phone:'081234567890', city:'Surabaya', status:'Aktif', totalOrders:12, lastOrder:'2025-04-27', avatarColor:'#3b82f6', address:'Jl. Basuki Rahmat No. 12, Surabaya' },
      { id:2, name:'Dewi Rahmawati', email:'dewi.rahma@gmail.com', phone:'082345678901', city:'Surabaya', status:'Baru', totalOrders:2, lastOrder:'2025-04-27', avatarColor:'#8b5cf6', address:'Jl. Pemuda No. 88, Surabaya' },
      { id:3, name:'Budi Santoso', email:'budi.s89@gmail.com', phone:'083456789012', city:'Gresik', status:'Nonaktif', totalOrders:7, lastOrder:'2025-04-27', avatarColor:'#64748b', address:'Jl. Pahlawan No. 5, Gresik' },
    ],
    get filteredCustomers() {
      return this.customers.filter(c => {
        const mSrch = !this.custSearch || c.name.toLowerCase().includes(this.custSearch.toLowerCase());
        const mStat = this.custStatus === 'Semua' || c.status === this.custStatus;
        return mSrch && mStat;
      });
    },
    openCustomerDetail(c) {
      this.selectedCustomer = c;
      this.showCustomerDetail = true;
    },

    /* ── Settings / Admin ── */
    activeTab: 'preferences',
    showAddAdminModal: false,
    emailNotifs: true,
    pushNotifs: false,
    autoRefresh: true,
    settingsTabs: [
      { key: 'admin', label: 'Admin Management' },
      { key: 'business', label: 'Business Settings' },
      { key: 'preferences', label: 'Preferences' },
    ],
    admins: [
      { id:1, name:'Ahmad Fauzi', email:'ahmad@sewamobilsby.id', role:'Super Admin', addedAt:'1 Jan 2025', color:'#3b82f6' },
      { id:2, name:'Dewi Rahma', email:'dewi@sewamobilsby.id', role:'Staff', addedAt:'10 Feb 2025', color:'#8b5cf6' },
    ],

    /* ── Cars ── */
    carFilter: 'All',
    showCarDetail: false,
    selectedCar: null,
    showEditModal: false,
    editingCarId: null,
    editModalTitle: '',
    editForm: {},
    cars: [
      { id:1, name:'Toyota Avanza', year:'2024', plate:'B 1242 DFR', category:'MPV', status:'Tersedia', price:'Rp 350.000', fuel:'Bensin - Pertalite', transmission:'Manual', passengers:'7', chassis:'MHFAB8GM4N4001234', renter:'Windah Basudara', rentalStart:'18 Mei 2025', rentalEnd:'22 Mei 2025', rentalPct:60, rentalLeft:'2 Hari Lagi', iconCls:'text-blue-400', bgCls:'bg-blue-50 dark:bg-blue-900/30' },
      { id:2, name:'Toyota Kijang Innova', year:'2023', plate:'B 1562 DYF', category:'MPV', status:'Tersewa', price:'Rp 550.000', fuel:'Bensin - Pertamax', transmission:'Matic', passengers:'8', chassis:'MHFAG8GM4N4005678', renter:'Budi Santoso', rentalStart:'15 Mei 2025', rentalEnd:'25 Mei 2025', rentalPct:50, rentalLeft:'5 Hari Lagi', iconCls:'text-indigo-400', bgCls:'bg-indigo-50 dark:bg-indigo-900/30' },
      { id:3, name:'Mercedes Benz E300', year:'2023', plate:'B 84 IK', category:'Sedan', status:'Perbaikan', price:'Rp 2.000.000', fuel:'Bensin Turbo', transmission:'Matic', passengers:'5', chassis:'WDD2130561A012345', renter:'-', rentalStart:'-', rentalEnd:'-', rentalPct:0, rentalLeft:'-', iconCls:'text-slate-400', bgCls:'bg-slate-100 dark:bg-slate-700/50' },
      { id:4, name:'BYD M6', year:'2024', plate:'B 1238', category:'EV', status:'Tersedia', price:'Rp 550.000', fuel:'Listrik (EV)', transmission:'Matic', passengers:'7', chassis:'LGXCE4CB8N6023456', renter:'-', rentalStart:'-', rentalEnd:'-', rentalPct:0, rentalLeft:'-', iconCls:'text-teal-400', bgCls:'bg-teal-50 dark:bg-teal-900/30' },
    ],
    get filteredCars() {
      if (this.carFilter === 'All') return this.cars;
      return this.cars.filter(c => c.category === this.carFilter);
    },
    carBadge(status) {
      return { 'Tersedia': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'Tersewa': 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400', 'Perbaikan': 'bg-red-100 text-red-500 dark:bg-red-900/30 dark:text-red-400' }[status] || 'bg-slate-100 text-slate-500';
    },
    viewCar(car) {
      this.selectedCar = car;
      this.showCarDetail = true;
    },
    openEditCarModal(car) {
      this.editingCarId = car.id;
      this.editModalTitle = 'Edit Data Kendaraan';
      this.editForm = {
        name: car.name,
        plate: car.plate,
        chassis: car.chassis,
        category: car.category,
        price: car.price.replace(/[^\d]/g,''),
        fuel: car.fuel,
        engine: car.engine,
        passengers: parseInt(car.passengers),
        transmission: car.transmission
      };
      this.showEditModal = true;
    },
    openAddCarModal() {
      this.editingCarId = null;
      this.editModalTitle = 'Tambah Kendaraan Baru';
      this.editForm = { name:'', plate:'', chassis:'', category:'', price:'', fuel:'', engine:'', passengers:'', transmission:'' };
      this.showEditModal = true;
    },
    saveCar() {
      this.showEditModal = false;
      alert(this.editingCarId ? 'Perubahan disimpan (Demo)' : 'Kendaraan baru ditambahkan (Demo)');
    },

    /* ── Orders ── */
    orderFilter: 'All Orders',
    showOrderModal: false,
    selectedOrder: null,
    orders: [
      { idLine1:'#202604', idLine2:'278301', customer:{ name:'Sucipto', email:'sucipto12@gmail.com', phone:'081234567890' }, car:{ name:'Toyota Avanza', category:'MPV', fuel:'Bensin', thumbBg:'bg-blue-50 dark:bg-blue-900/30', thumbColor:'text-blue-400' }, startDate:'Apr 27, 10.00 WIB', endDate:'Apr 30, 10.00 WIB', totalFormatted:'1.050.000', status:'Confirmed' },
      { idLine1:'#202604', idLine2:'278302', customer:{ name:'Budi Santoso', email:'budi.s@gmail.com', phone:'082345678901' }, car:{ name:'Toyota Kijang Innova', category:'MPV', fuel:'Bensin', thumbBg:'bg-indigo-50 dark:bg-indigo-900/30', thumbColor:'text-indigo-400' }, startDate:'Apr 27, 09.00 WIB', endDate:'Apr 30, 09.00 WIB', totalFormatted:'2.750.000', status:'Pending' },
      { idLine1:'#202604', idLine2:'278303', customer:{ name:'Dewi Rahma', email:'dewi.r@gmail.com', phone:'083456789012' }, car:{ name:'Toyota Fortuner VRZ', category:'SUV', fuel:'Solar', thumbBg:'bg-emerald-50 dark:bg-emerald-900/30', thumbColor:'text-emerald-400' }, startDate:'Apr 28, 08.00 WIB', endDate:'May 2, 08.00 WIB', totalFormatted:'3.500.000', status:'Completed' },
      { idLine1:'#202604', idLine2:'278304', customer:{ name:'Ahmad Fauzi', email:'ahmad.f@gmail.com', phone:'084567890123' }, car:{ name:'BYD M6', category:'EV', fuel:'Listrik', thumbBg:'bg-teal-50 dark:bg-teal-900/30', thumbColor:'text-teal-400' }, startDate:'Apr 29, 10.00 WIB', endDate:'May 1, 10.00 WIB', totalFormatted:'1.100.000', status:'Completed' },
    ],
    get filteredOrders() {
      if (this.orderFilter === 'All Orders') return this.orders;
      return this.orders.filter(o => o.status === this.orderFilter);
    },
    openOrderModal(order) {
      this.selectedOrder = order;
      this.showOrderModal = true;
    }

  };
}
</script>

</body>
</html>