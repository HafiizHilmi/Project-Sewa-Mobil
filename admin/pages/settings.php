<!-- ====================================================
     PAGE: SETTINGS
     Berisi: Tab Admin Management, Business Settings,
             Preferences (dark mode, notifikasi, dll)
===================================================== -->
<div x-show="activePage === 'settings'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

  <!-- Tab Nav -->
  <div class="flex items-center border-b border-slate-200 dark:border-slate-700 mb-6 gap-1">
    <template x-for="tab in settingsTabs" :key="tab.key">
      <button @click="activeTab = tab.key"
              :class="activeTab === tab.key ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'"
              class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap" x-text="tab.label">
      </button>
    </template>
  </div>

  <!-- ── Tab: Admin Management ── -->
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

  <!-- ── Tab: Business Settings ── -->
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

  <!-- ── Tab: Preferences ── -->
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
  </div>

</div>
