<!-- ====================================================
     PAGE: DASHBOARD
     Berisi: Stat cards, chart rental trends, recent alerts
===================================================== -->
<div x-show="activePage === 'dashboard'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-7 py-6 space-y-6">

  <!-- Stat Cards -->
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

  <!-- Chart + Alerts -->
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
