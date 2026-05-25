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
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-visible">
    <!-- Header Tabel (Desktop) -->
    <div class="hidden md:grid px-5 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-[11px] font-bold uppercase tracking-wider rounded-t-2xl"
         style="grid-template-columns: 48px 40px 1fr 150px 110px 90px 120px 110px 50px; gap: 10px;">
      <div class="flex items-center">
        <input type="checkbox" :checked="allCustSelected" @change="toggleSelectAllCust()"
               class="rounded accent-white w-3.5 h-3.5 cursor-pointer focus:ring-0 focus:outline-none"/>
      </div>
      <div>No</div><div>Pelanggan</div><div>No HP</div><div>Kota</div><div class="text-center">Total Pesan</div><div>Terakhir Pesan</div><div>Status</div><div class="text-right">Aksi</div>
    </div>

    <div>
      <template x-for="(c, idx) in filteredCustomers" :key="c.id">
        <div>
          <div x-show="idx > 0" class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>

          <!-- Desktop Row -->
          <div @click="openCustomerDetail(c)" class="cust-row hidden md:grid px-5 py-3.5 items-center dark:hover:bg-slate-700/30"
               style="grid-template-columns: 48px 40px 1fr 150px 110px 90px 120px 110px 50px; gap: 10px;">
            <div @click.stop>
              <input type="checkbox" :value="c.id" x-model="selectedCustIds"
                     class="rounded accent-blue-600 w-3.5 h-3.5 cursor-pointer focus:ring-0 focus:outline-none"/>
            </div>
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
            <div class="flex justify-end relative" @click.stop x-data="{ open: false }">
              <button @click="open = !open" class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-sm"></i>
              </button>
              <div x-show="open" @click.outside="open = false" 
                   x-transition:enter="transition ease-out duration-100"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-75"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   class="absolute right-0 top-8 z-30 w-44 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-xl py-1.5 focus:outline-none" x-cloak>
                <button type="button" @click="openEditCustModal(c); open = false;" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center gap-2">
                  <i class="fa-solid fa-pen text-slate-400 text-[10px]"></i> Edit Data
                </button>
                <form action="customer_action.php" method="POST" class="m-0 p-0" :onsubmit="c.status === 'Suspended' ? 'return confirm(\'Aktifkan kembali pelanggan ini?\')' : 'return confirm(\'Apakah Anda yakin ingin men-suspend pelanggan ini?\')'">
                  <input type="hidden" name="user_id" :value="c.id">
                  <input type="hidden" name="action" :value="c.status === 'Suspended' ? 'unsuspend' : 'suspend'">
                  <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold flex items-center gap-2" :class="c.status === 'Suspended' ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/20'">
                    <i class="fa-solid" :class="c.status === 'Suspended' ? 'fa-circle-check text-emerald-500' : 'fa-ban text-amber-500'"></i> 
                    <span x-text="c.status === 'Suspended' ? 'Aktifkan Akun' : 'Suspend'"></span>
                  </button>
                </form>
                <div class="h-[1px] bg-slate-100 dark:bg-slate-700 my-1"></div>
                <form action="customer_action.php" method="POST" class="m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini? Semua data terkait pesanan juga akan terpengaruh.')">
                  <input type="hidden" name="user_id" :value="c.id">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 flex items-center gap-2">
                    <i class="fa-solid fa-trash text-red-500 text-[10px]"></i> Hapus
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Mobile Row -->
          <div @click="openCustomerDetail(c)" class="cust-row md:hidden flex items-center justify-between gap-3 px-4 py-3.5 dark:hover:bg-slate-700/30">
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold shadow-sm" :style="'background: ' + c.avatarColor">
                <span x-text="c.name.charAt(0)"></span>
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="c.name"></p>
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full flex-shrink-0 ml-2"
                        :class="{
                          'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : c.status === 'Aktif',
                          'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : c.status === 'Baru',
                          'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : c.status === 'Nonaktif',
                          'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : c.status === 'Suspended',
                        }" x-text="c.status"></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5 truncate" x-text="c.phone + ' · ' + c.city"></p>
              </div>
            </div>
            <div class="flex justify-end relative" @click.stop x-data="{ open: false }">
              <button @click="open = !open" class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-ellipsis-vertical text-slate-400 text-sm"></i>
              </button>
              <div x-show="open" @click.outside="open = false" 
                   x-transition:enter="transition ease-out duration-100"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-75"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   class="absolute right-0 top-8 z-30 w-44 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-xl py-1.5 focus:outline-none" x-cloak>
                <button type="button" @click="openEditCustModal(c); open = false;" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 flex items-center gap-2">
                  <i class="fa-solid fa-pen text-slate-400 text-[10px]"></i> Edit Data
                </button>
                <form action="customer_action.php" method="POST" class="m-0 p-0" :onsubmit="c.status === 'Suspended' ? 'return confirm(\'Aktifkan kembali pelanggan ini?\')' : 'return confirm(\'Apakah Anda yakin ingin men-suspend pelanggan ini?\')'">
                  <input type="hidden" name="user_id" :value="c.id">
                  <input type="hidden" name="action" :value="c.status === 'Suspended' ? 'unsuspend' : 'suspend'">
                  <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold flex items-center gap-2" :class="c.status === 'Suspended' ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/20'">
                    <i class="fa-solid" :class="c.status === 'Suspended' ? 'fa-circle-check text-emerald-500' : 'fa-ban text-amber-500'"></i> 
                    <span x-text="c.status === 'Suspended' ? 'Aktifkan Akun' : 'Suspend'"></span>
                  </button>
                </form>
                <div class="h-[1px] bg-slate-100 dark:bg-slate-700 my-1"></div>
                <form action="customer_action.php" method="POST" class="m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini? Semua data terkait pesanan juga akan terpengaruh.')">
                  <input type="hidden" name="user_id" :value="c.id">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 flex items-center gap-2">
                    <i class="fa-solid fa-trash text-red-500 text-[10px]"></i> Hapus
                  </button>
                </form>
              </div>
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

  <!-- Floating Bulk Actions Bar -->
  <div x-show="selectedCustIds.length > 0"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 translate-y-10"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 translate-y-10"
       class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-white dark:bg-slate-800 text-slate-800 dark:text-white backdrop-blur-md px-6 py-3.5 rounded-2xl shadow-2xl flex items-center gap-6 border border-slate-200 dark:border-slate-700" x-cloak>
    <div class="flex items-center gap-2">
      <p class="text-xs font-semibold tracking-wide"><span class="text-blue-600 dark:text-blue-400 font-extrabold" x-text="selectedCustIds.length"></span> Pelanggan Terpilih</p>
    </div>
    
    <div class="h-4 w-[1px] bg-slate-200 dark:bg-slate-700"></div>
    
    <div class="flex items-center gap-2">
      <!-- Bulk Suspend Button -->
      <form action="customer_action.php" method="POST" class="m-0 p-0" 
            @submit="return confirm('Apakah Anda yakin ingin men-suspend ' + selectedCustIds.length + ' pelanggan terpilih?\nSemua status akun mereka akan diubah menjadi Suspended.')">
        <input type="hidden" name="action" value="bulk_suspend">
        <input type="hidden" name="user_ids" :value="selectedCustIds.join(',')">
        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-xs font-bold flex items-center gap-1.5 transition-all shadow-md shadow-amber-900/10 text-white">
          <i class="fa-solid fa-ban text-[10px]"></i> Suspend semua
        </button>
      </form>
      
      <!-- Bulk Delete Button -->
      <form action="customer_action.php" method="POST" class="m-0 p-0" 
            @submit="return confirm('PERINGATAN KRITIS: Anda akan menghapus ' + selectedCustIds.length + ' pelanggan secara permanen!\nSemua data pemesanan terkait juga akan terpengaruh.\nApakah Anda benar-benar yakin?')">
        <input type="hidden" name="action" value="bulk_delete">
        <input type="hidden" name="user_ids" :value="selectedCustIds.join(',')">
        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold flex items-center gap-1.5 transition-all shadow-md shadow-rose-900/10 text-white">
          <i class="fa-solid fa-trash text-[10px]"></i> Hapus Semua
        </button>
      </form>
      
      <button type="button" @click="selectedCustIds = []" class="px-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white transition-colors">
        Batal
      </button>
    </div>
  </div>
</div>

<!-- ── Drawer Detail Pelanggan (Slide dari kanan) ── -->
<div x-show="showCustomerDetail" @click="showCustomerDetail = false" class="fixed inset-0 z-40 bg-white/50 backdrop-blur-sm" x-transition.opacity></div>
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
          <!-- Informasi Pribadi -->
          <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Informasi Pribadi</p>
            <div class="space-y-2.5">
              <div class="flex items-center gap-3"><div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center"><i class="fa-solid fa-phone text-slate-400 text-[10px]"></i></div><div><p class="text-[10px] text-slate-400">No. HP</p><p class="text-xs font-bold dark:text-slate-100" x-text="selectedCustomer.phone"></p></div></div>
              <div class="flex items-center gap-3"><div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center"><i class="fa-solid fa-envelope text-slate-400 text-[10px]"></i></div><div class="min-w-0"><p class="text-[10px] text-slate-400">Email</p><p class="text-xs font-bold text-blue-500 truncate" x-text="selectedCustomer.email"></p></div></div>
            </div>
          </div>

          <!-- Statistik Rental -->
          <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Statistik Rental</p>
            <div class="space-y-2.5">
              <div class="flex items-center justify-between py-1.5 border-b border-dashed border-slate-200 dark:border-slate-700/60">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm"><i class="fa-solid fa-clipboard-list text-blue-500 text-[10px]"></i></div>
                  <span class="text-xs font-semibold text-slate-650 dark:text-slate-350">Total Pesanan</span>
                </div>
                <span class="text-xs font-extrabold text-slate-800 dark:text-white" x-text="selectedCustomer.totalOrders + ' Kali'"></span>
              </div>
              
              <div class="flex items-center justify-between py-1.5 border-b border-dashed border-slate-200 dark:border-slate-700/60">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm"><i class="fa-solid fa-money-bill-wave text-emerald-500 text-[10px]"></i></div>
                  <span class="text-xs font-semibold text-slate-650 dark:text-slate-350">Total Nominal Sewa</span>
                </div>
                <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + selectedCustomer.totalSpentFormatted"></span>
              </div>

              <div class="flex items-center justify-between py-1.5">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm"><i class="fa-solid fa-car-burst text-rose-500 text-[10px]"></i></div>
                  <span class="text-xs font-semibold text-slate-650 dark:text-slate-350">Mobil Rusak Disewa</span>
                </div>
                <span class="text-xs font-extrabold" :class="selectedCustomer.totalDamaged > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-200'" x-text="selectedCustomer.totalDamaged + ' Mobil'"></span>
              </div>
            </div>
          </div>

          <!-- Riwayat Kendaraan Sewa -->
          <div class="bg-slate-50 dark:bg-slate-700/40 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Kendaraan Tersewa</p>
            
            <!-- Empty State -->
            <div x-show="selectedCustomer.rentedCars.length === 0" class="py-4 text-center">
              <i class="fa-solid fa-car text-xl text-slate-350 dark:text-slate-600 mb-1.5 block"></i>
              <p class="text-[11px] text-slate-400 font-medium">Belum ada riwayat sewa kendaraan</p>
            </div>
            
            <!-- List State -->
            <div x-show="selectedCustomer.rentedCars.length > 0" class="space-y-2.5">
              <template x-for="(car, idx) in selectedCustomer.rentedCars" :key="idx">
                <div class="flex items-center justify-between py-1.5" :class="idx < selectedCustomer.rentedCars.length - 1 ? 'border-b border-dashed border-slate-200 dark:border-slate-700/60' : ''">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center flex-shrink-0 shadow-sm animate-pulse-subtle">
                      <i class="fa-solid fa-car text-blue-500 text-[10px]"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate" x-text="car.name"></span>
                  </div>
                  <span class="inline-block text-[10px] font-mono font-bold bg-slate-200/75 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded" x-text="car.plate"></span>
                </div>
              </template>
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

<!-- ── Modal Edit Customer ── -->
<div x-show="showEditCustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
  <div @click="showEditCustModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
  <form action="customer_action.php" method="POST" class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col p-5 m-0" x-transition>
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="user_id" :value="editingCust ? editingCust.id : ''">
    
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
        <i class="fa-solid fa-user-pen text-blue-500"></i> Edit Data Pelanggan
      </h2>
      <button type="button" @click="showEditCustModal = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <div class="space-y-4 mb-5">
      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Lengkap</label>
        <input type="text" name="name" :value="editingCust ? editingCust.name : ''" required
               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 focus:border-blue-500 transition-all"/>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Email</label>
        <input type="email" name="email" :value="editingCust ? editingCust.email : ''" required
               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 focus:border-blue-500 transition-all"/>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">No. HP</label>
        <input type="text" name="phone" :value="editingCust ? editingCust.phone : ''" required
               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 focus:border-blue-500 transition-all"/>
      </div>
    </div>
    
    <div class="flex gap-2">
      <button type="button" @click="showEditCustModal = false" 
              class="flex-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 px-4 py-2.5 rounded-xl transition-all">
        Batal
      </button>
      <button type="submit" 
              class="flex-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/20 px-4 py-2.5 rounded-xl transition-all">
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>

