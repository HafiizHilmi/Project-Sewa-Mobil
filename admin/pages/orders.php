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
                <button class="w-7 h-7 rounded-lg flex items-center justify-center text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                  <i class="fa-solid fa-pen text-[10px]"></i>
                </button>
                <button class="w-7 h-7 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                  <i class="fa-solid fa-trash text-[10px]"></i>
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
                
                <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-slate-700" @click.stop>
                  <button class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-500 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors">
                    <i class="fa-solid fa-pen text-xs"></i>
                  </button>
                  <button class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-800 transition-colors">
                    <i class="fa-solid fa-trash text-xs"></i>
                  </button>
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
        <div class="bg-blue-600 rounded-xl p-4 flex justify-between items-center text-white">
          <p class="text-sm font-semibold">Total Pembayaran</p>
          <p class="text-base font-extrabold" x-text="'Rp ' + selectedOrder.totalFormatted"></p>
        </div>
        <button @click="showOrderModal = false" class="w-full mt-4 bg-slate-200 dark:bg-slate-700 py-2.5 rounded-xl font-semibold dark:text-white text-sm">Tutup</button>
      </div>
    </template>
  </div>
</div>