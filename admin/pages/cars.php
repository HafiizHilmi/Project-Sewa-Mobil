<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div x-show="activePage === 'cars'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto">

  <!-- ── Daftar Mobil ── -->
  <div x-show="!showCarDetail" class="px-5 lg:px-6 py-5">

    <!-- Filter Kategori -->
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

    <!-- List Kendaraan -->
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

  <!-- ── Detail Kendaraan ── -->
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
            <!-- Foto Kendaraan -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
              <div class="h-[210px] flex flex-col items-center justify-center gap-3" :class="selectedCar.bgCls">
                <i class="fa-solid fa-car text-[76px] opacity-60" :class="selectedCar.iconCls"></i>
                <span class="text-xs font-semibold opacity-75" :class="selectedCar.iconCls" x-text="selectedCar.name + ' ' + selectedCar.year"></span>
              </div>
              <div class="bg-slate-50 dark:bg-slate-700/50 border-t border-slate-100 dark:border-slate-700 px-5 py-2.5">
                <p class="text-xs text-slate-500">Nomor Rangka:&nbsp;<span class="font-semibold text-slate-700 dark:text-slate-200" x-text="selectedCar.chassis"></span></p>
              </div>
            </div>

            <!-- GPS Map Mock -->
            <div class="space-y-4">
              
              <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden relative" style="height: 240px;">
                <div class="w-full h-full"
                     x-init="$nextTick(() => {
                        let lat = selectedCar.latitude || -7.1186; 
                        let lng = selectedCar.longitude || 112.4155;
                        let map = L.map($el).setView([lat, lng], 15);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OSM'
                        }).addTo(map);

                        let carMarker = L.marker([lat, lng]).addTo(map);
                        carMarker.bindPopup(`<b>${selectedCar.name}</b><br>${selectedCar.plate}`).openPopup();

                        setTimeout(() => { map.invalidateSize(); }, 200);
                     })">
                </div>

                <div class="absolute top-3 right-3 z-[1000] flex items-center gap-1.5 bg-white dark:bg-slate-800 rounded-full px-3 py-1.5 shadow-md border border-slate-100 dark:border-slate-700">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">Telemetri GPS Aktif</span>
                </div>
              </div> <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-4">
                <div class="flex items-center gap-2 mb-3">
                  <i class="fa-solid fa-satellite-dish text-blue-500 text-xs"></i>
                  <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Sistem Kontrol Jarak Jauh (IoT)</h4>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <a :href="'triggers.php?type=alarm&id=' + selectedCar.id"
                     class="flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-3 rounded-xl transition-all shadow-sm active:scale-95">
                    <i class="fa-solid fa-bell"></i> Bunyikan Alarm
                  </a>

                  <a :href="'triggers.php?type=mesin_mati&id=' + selectedCar.id"
                     class="flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-3 rounded-xl transition-all shadow-sm active:scale-95">
                    <i class="fa-solid fa-power-off"></i> Matikan Mesin
                  </a>
                </div>
              </div>

            </div>

            <!-- Spesifikasi -->
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

            <!-- Penyewa -->
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
          </div>
        </div>
      </div>
    </template>
  </div>
</div>

<!-- ── Modal Edit / Tambah Kendaraan ── -->
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
