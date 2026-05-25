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
         style="grid-template-columns: 115px 1fr 1fr 165px 110px 120px 80px; gap: 12px;">
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

          <div @click="openOrderModal(order)" class="order-row px-5 py-4 dark:hover:bg-slate-700/30 cursor-pointer">

            <div class="hidden md:grid items-center" style="grid-template-columns: 115px 1fr 1fr 165px 110px 120px 80px; gap: 12px;">
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
                  <template x-if="order.assignedPlate">
                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                      Plat: <span class="bg-emerald-50 dark:bg-emerald-950/30 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800" x-text="order.assignedPlate"></span>
                    </p>
                  </template>
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
              
              <div class="flex justify-end items-center gap-1.5" @click.stop>
                <template x-if="order.status === 'Pending'">
                  <div class="flex items-center gap-1.5">
                    <form action="order_action.php" method="POST" class="m-0 p-0 flex">
                      <input type="hidden" name="booking_id" :value="order.id">
                      <input type="hidden" name="action" value="accept">
                      <button type="submit" title="Terima Pesanan" class="w-7 h-7 rounded-lg flex items-center justify-center text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors">
                        <i class="fa-solid fa-check text-xs"></i>
                      </button>
                    </form>
                    <form action="order_action.php" method="POST" class="m-0 p-0 flex">
                      <input type="hidden" name="booking_id" :value="order.id">
                      <input type="hidden" name="action" value="reject">
                      <button type="submit" title="Tolak Pesanan" class="w-7 h-7 rounded-lg flex items-center justify-center text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:text-rose-400 dark:hover:bg-rose-900/30 transition-colors">
                        <i class="fa-solid fa-xmark text-xs"></i>
                      </button>
                    </form>
                  </div>
                </template>
                <template x-if="order.status === 'Confirmed'">
                  <div class="flex items-center gap-1.5">
                    <button type="button" @click="openCompleteModal(order)" title="Selesaikan Transaksi (Success)" class="w-7 h-7 rounded-lg flex items-center justify-center text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/20 dark:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors">
                      <i class="fa-solid fa-circle-check text-xs"></i>
                    </button>
                    <form action="order_action.php" method="POST" class="m-0 p-0 flex">
                      <input type="hidden" name="booking_id" :value="order.id">
                      <input type="hidden" name="action" value="reject">
                      <button type="submit" title="Batalkan/Tolak Pesanan" class="w-7 h-7 rounded-lg flex items-center justify-center text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:text-rose-400 dark:hover:bg-rose-900/30 transition-colors">
                        <i class="fa-solid fa-xmark text-xs"></i>
                      </button>
                    </form>
                  </div>
                </template>
                <template x-if="order.status !== 'Pending' && order.status !== 'Confirmed'">
                  <span class="text-[10px] text-slate-400 font-semibold uppercase px-2">Selesai</span>
                </template>
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
                <template x-if="order.assignedPlate">
                  <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                    Plat: <span class="bg-emerald-50 dark:bg-emerald-950/30 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800" x-text="order.assignedPlate"></span>
                  </p>
                </template>
                <div class="flex items-center justify-between mt-1.5">
                  <p class="text-xs text-slate-500" x-text="order.startDate + ' – ' + order.endDate"></p>
                  <p class="text-xs font-bold text-slate-800 dark:text-white">Rp <span x-text="order.totalFormatted"></span></p>
                </div>
                
                <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700" @click.stop>
                  <template x-if="order.status === 'Pending'">
                    <div class="flex items-center gap-2">
                      <form action="order_action.php" method="POST" class="m-0 p-0">
                        <input type="hidden" name="booking_id" :value="order.id">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="px-3 py-1 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg flex items-center gap-1">
                          <i class="fa-solid fa-check"></i> Terima
                        </button>
                      </form>
                      <form action="order_action.php" method="POST" class="m-0 p-0">
                        <input type="hidden" name="booking_id" :value="order.id">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="px-3 py-1 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg flex items-center gap-1">
                          <i class="fa-solid fa-xmark"></i> Tolak
                        </button>
                      </form>
                    </div>
                  </template>
                  <template x-if="order.status === 'Confirmed'">
                    <div class="flex items-center gap-2">
                      <button type="button" @click="openCompleteModal(order)" class="px-3 py-1 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg flex items-center gap-1">
                        <i class="fa-solid fa-circle-check"></i> Selesai
                      </button>
                      <form action="order_action.php" method="POST" class="m-0 p-0">
                        <input type="hidden" name="booking_id" :value="order.id">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="px-3 py-1 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg flex items-center gap-1">
                          <i class="fa-solid fa-xmark"></i> Batalkan
                        </button>
                      </form>
                    </div>
                  </template>
                  <template x-if="order.status !== 'Pending' && order.status !== 'Confirmed'">
                    <span class="text-xs text-slate-400 font-semibold uppercase py-1">Selesai</span>
                  </template>
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
        <div class="bg-blue-600 rounded-xl p-4 flex justify-between items-center text-white mb-4">
          <p class="text-sm font-semibold">Total Pembayaran</p>
          <p class="text-base font-extrabold" x-text="'Rp ' + selectedOrder.totalFormatted"></p>
        </div>

        <!-- Laporan kerusakan kalo ada -->
        <template x-if="selectedOrder.status === 'Completed' && (selectedOrder.additionalCost > 0 || selectedOrder.damageDescription)">
          <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-xl p-4 mb-4 text-rose-800 dark:text-rose-300">
            <p class="text-[10px] font-bold uppercase mb-2">Laporan Pengembalian (Kerusakan/Denda)</p>
            <template x-if="selectedOrder.additionalCost > 0">
              <p class="text-xs font-semibold">Biaya Tambahan: Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedOrder.additionalCost)"></span></p>
            </template>
            <template x-if="selectedOrder.damageDescription">
              <p class="text-xs mt-1.5"><span class="font-semibold">Deskripsi:</span> <span x-text="selectedOrder.damageDescription"></span></p>
            </template>
            <template x-if="selectedOrder.damageImage">
              <div class="mt-2.5 rounded-lg overflow-hidden border border-rose-200 dark:border-rose-800">
                <img :src="'../public/assets/images/damages/' + selectedOrder.damageImage" class="w-full h-auto object-cover">
              </div>
            </template>
          </div>
        </template>

        <button @click="showOrderModal = false" class="w-full bg-slate-200 dark:bg-slate-700 py-2.5 rounded-xl font-semibold dark:text-white text-sm">Tutup</button>
      </div>
    </template>
  </div>
</div>

<!-- (Success) -->
<div x-show="showCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
  <div @click="showCompleteModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
  <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col p-6 overflow-hidden" style="max-height: 90vh;" x-transition>
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
      <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> Selesaikan Transaksi Sewa
      </h3>
      <button @click="showCompleteModal = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 flex items-center justify-center">
        <i class="fa-solid fa-xmark text-slate-500 dark:text-slate-400 text-xs"></i>
      </button>
    </div>

    <form action="order_action.php" method="POST" enctype="multipart/form-data" class="space-y-4 overflow-y-auto flex-1 pr-1">
      <input type="hidden" name="booking_id" :value="completingOrder ? completingOrder.id : ' '">
      <input type="hidden" name="action" value="complete">

      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 text-xs text-blue-700 dark:text-blue-300">
        <span class="font-bold">Info Pesanan:</span>
        <p class="mt-1" x-text="completingOrder ? ('Customer: ' + completingOrder.customer.name + ' (' + completingOrder.car.name + ')') : ''"></p>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tambahan Biaya (Denda/Kerusakan)</label>
        <div class="relative">
          <span class="absolute left-3 top-2 text-xs text-slate-400 font-bold">Rp</span>
          <input type="number" name="additional_cost" placeholder="0" class="w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-700 dark:text-white rounded-lg pl-9 pr-3 py-2 text-xs outline-none focus:border-blue-500">
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Isi jika ada denda keterlambatan atau biaya kerusakan.</p>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Foto Kerusakan (Optional)</label>
        <input type="file" name="foto_kerusakan" accept="image/*" class="w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-1.5 text-xs outline-none focus:border-blue-500">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Detail Kerusakan / Catatan</label>
        <textarea name="damage_description" placeholder="Deskripsikan kerusakan jika ada..." rows="3" class="w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-700 dark:text-white rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500 resize-none"></textarea>
      </div>

      <div class="flex flex-col gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5 shadow-sm">
           Simpan & Selesaikan
        </button>
        <button type="button" @click="skipComplete(completingOrder.id)" class="w-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-white py-2.5 rounded-xl font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
           Lewati
        </button>
      </div>
    </form>
  </div>
</div>