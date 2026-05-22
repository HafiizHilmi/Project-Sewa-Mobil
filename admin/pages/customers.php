<!-- ====================================================
     PAGE: CUSTOMERS
     Berisi: Search, filter status, tabel pelanggan,
             drawer detail pelanggan, modal tambah admin
===================================================== -->
<div x-show="activePage === 'customers'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

  <!-- Search & Filter -->
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

  <!-- Tabel Pelanggan -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <!-- Header Tabel (Desktop) -->
    <div class="hidden md:grid px-5 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-[11px] font-bold uppercase tracking-wider"
         style="grid-template-columns: 48px 40px 1fr 150px 110px 90px 120px 110px 50px; gap: 10px;">
      <div class="flex items-center"><input type="checkbox" class="rounded accent-white w-3.5 h-3.5 cursor-pointer"/></div>
      <div>No</div><div>Pelanggan</div><div>No HP</div><div>Kota</div><div class="text-center">Total Pesan</div><div>Terakhir Pesan</div><div>Status</div><div class="text-right">Aksi</div>
    </div>

    <div>
      <template x-for="(c, idx) in filteredCustomers" :key="c.id">
        <div>
          <div x-show="idx > 0" class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>

          <!-- Desktop Row -->
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

          <!-- Mobile Row -->
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

  <!-- Footer Info -->
  <div class="flex items-center justify-between mt-4">
    <p class="text-xs text-slate-400">Menampilkan <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="filteredCustomers.length"></span> dari <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="customers.length"></span> pelanggan</p>
  </div>
</div>

<!-- ── Drawer Detail Pelanggan (Slide dari kanan) ── -->
<div x-show="showCustomerDetail" @click="showCustomerDetail = false" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm" x-transition.opacity></div>
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

<!-- ── Modal Tambah Admin ── -->
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
