<?php
require_once '../Config/database.php';
$checkStock = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'stock'");
if (mysqli_num_rows($checkStock) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN stock INT NOT NULL DEFAULT 1");
}

$checkIsType = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'is_type'");
if (mysqli_num_rows($checkIsType) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN is_type TINYINT(1) NOT NULL DEFAULT 0");
}

$checkTypeKey = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'type_key'");
if (mysqli_num_rows($checkTypeKey) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN type_key VARCHAR(255) NULL");
    mysqli_query($conn, "UPDATE cars SET type_key = CONCAT(make,'|',model,'|',year,'|',fuel_type,'|',engine_capacity,'|',seats,'|',transmission,'|',category,'|',price_per_day)");
}

$query = mysqli_query($conn, "
    SELECT 
        c.*, 
        b.full_name AS renter_name,
        b.start_date,
        b.end_date,
        b.pickup_location,
        b.return_location
    FROM cars c
    LEFT JOIN bookings b ON c.id = b.assigned_car_id AND b.status = 'confirmed'
    ORDER BY c.id DESC
");

$types_map = [];
while ($row = mysqli_fetch_assoc($query)) {
  $imagePath = !empty($row['image']) ? (filter_var($row['image'], FILTER_VALIDATE_URL) ? $row['image'] : '../public/assets/images/' . $row['image']) : null;

  $type_key = !empty($row['type_key'])
    ? $row['type_key']
    : implode('|', [
        $row['make'],
        $row['model'],
        $row['year'],
        $row['fuel_type'],
        $row['engine_capacity'],
        $row['seats'],
        $row['transmission'],
        $row['category'],
        $row['price_per_day']
      ]);

  if (!isset($types_map[$type_key])) {
    $types_map[$type_key] = [
      'type_key'   => $type_key,
      'id'         => $row['id'],
      'is_type'    => intval($row['is_type']),
      'name'       => trim($row['make'] . ' ' . $row['model'] . ' ' . $row['year']),
      'make'       => $row['make'],
      'model'      => $row['model'],
      'year'       => $row['year'],
      'category'   => $row['category'],
      'price'      => "Rp " . number_format($row['price_per_day'], 0, ',', '.'),
      'price_raw'  => $row['price_per_day'],
      'fuel'       => $row['fuel_type'],
      'transmission'=> $row['transmission'],
      'passengers' => $row['seats'],
      'engine'     => $row['engine_capacity'],
      'bgCls'      => $row['category'] == 'EV' ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500',
      'iconCls'    => $row['category'] == 'EV' ? 'text-emerald-500' : 'text-blue-500',
      'image'      => $imagePath,
      'children'   => []
    ];
  } elseif (intval($row['is_type']) === 1) {
    $types_map[$type_key]['id'] = $row['id'];
    $types_map[$type_key]['is_type'] = 1;
    $types_map[$type_key]['name'] = trim($row['make'] . ' ' . $row['model'] . ' ' . $row['year']);
    $types_map[$type_key]['make'] = $row['make'];
    $types_map[$type_key]['model'] = $row['model'];
    $types_map[$type_key]['year'] = $row['year'];
    $types_map[$type_key]['category'] = $row['category'];
    $types_map[$type_key]['price'] = "Rp " . number_format($row['price_per_day'], 0, ',', '.');
    $types_map[$type_key]['price_raw'] = $row['price_per_day'];
    $types_map[$type_key]['fuel'] = $row['fuel_type'];
    $types_map[$type_key]['transmission'] = $row['transmission'];
    $types_map[$type_key]['passengers'] = $row['seats'];
    $types_map[$type_key]['engine'] = $row['engine_capacity'];
    $types_map[$type_key]['bgCls'] = $row['category'] == 'EV' ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500';
    $types_map[$type_key]['iconCls'] = $row['category'] == 'EV' ? 'text-emerald-500' : 'text-blue-500';
    $types_map[$type_key]['image'] = $imagePath;
  }

  if (intval($row['is_type']) === 0) {
    $child = [
      'id'      => (int)$row['id'],
      'plate'   => $row['number_plate'],
      'chassis' => $row['chassis_number'],
      'status'  => ($row['available'] == 1) ? 'Tersedia' : 'Tersewa',
      'image'   => $imagePath,
      'latitude'=> $row['latitude'] ?? null,
      'longitude'=> $row['longitude'] ?? null,
      'renter'  => $row['renter_name'] ?? null,
      'start_date' => $row['start_date'] ? date('j M Y', strtotime($row['start_date'])) : null,
      'end_date'   => $row['end_date'] ? date('j M Y', strtotime($row['end_date'])) : null,
      'pickup'     => $row['pickup_location'] ?? null,
      'return'     => $row['return_location'] ?? null
    ];

    $types_map[$type_key]['children'][] = $child;
  }
}

$types_data = array_values($types_map);

$cars_data = [];
foreach ($types_data as $t) {
    $available_count = 0;
    foreach ($t['children'] as $c) {
        if ($c['status'] === 'Tersedia') {
            $available_count++;
        }
    }
    $t['stock'] = $available_count;
    $t['image'] = (!empty($t['image'])) ? $t['image'] : ((!empty($t['children'][0]['image'])) ? $t['children'][0]['image'] : null);
    $available = ($available_count > 0);
    $t['status'] = $available ? 'Tersedia' : 'Tersewa';
    $t['is_type'] = 1;
    $cars_data[] = $t;
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div x-data="carApp" 
     x-show="activePage === 'cars'"
     @open-add-car-modal.window="openEditCarModal()"
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
      <template x-for="car in filteredCars" :key="car.type_key">
        <div @click="viewCar(car)" class="car-row cursor-pointer bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3.5 flex items-center gap-3 sm:gap-4">

          <div class="w-20 sm:w-24 h-14 sm:h-16 rounded-xl flex items-center justify-center flex-shrink-0 relative overflow-hidden" :class="car.bgCls">
            <template x-if="car.image">
                <img :src="car.image" class="absolute inset-0 w-full h-full object-cover">
            </template>
            <template x-if="!car.image">
                <i class="fa-solid fa-car text-3xl sm:text-4xl" :class="car.iconCls"></i>
            </template>
          </div>

          <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-snug truncate" x-text="car.name"></p>
            <p class="text-xs text-slate-400 mt-0.5" x-text="car.stock + ' unit'" ></p>
            <div class="flex items-center gap-2 mt-1.5 sm:hidden">
              <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full" :class="carBadge(car.status)" x-text="car.status"></span>
              <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full" x-text="car.category"></span>
              <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">Stok <span x-text="car.stock"></span></span>
            </div>
          </div>

          <div class="hidden sm:block flex-shrink-0 w-28">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Status</p>
            <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full" :class="carBadge(car.status)" x-text="car.status"></span>
          </div>

          <div class="hidden sm:block flex-shrink-0 w-16">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Kategori</p>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="car.category"></p>
          </div>

          <div class="hidden sm:block flex-shrink-0 w-16">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Stok</p>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="car.stock"></p>
          </div>
          <div class="hidden md:block flex-shrink-0 w-32">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide mb-1">Harga/Hari</p>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="car.price"></p>
          </div>

          <div class="flex flex-col gap-1.5 flex-shrink-0">
            <button @click.stop="viewCar(car)" class="w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition-all hover:scale-105 shadow-sm">
              <i class="fa-solid fa-eye text-white text-xs"></i>
            </button>
            <button @click.stop="openEditCarModal(car, true)" title="Edit Tipe" class="w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition-all hover:scale-105 shadow-sm">
              <i class="fa-solid fa-pen text-white text-[10px]"></i>
            </button>
            <button @click.stop="openAddUnit(car)" title="Tambah Unit" class="w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 flex items-center justify-center transition-all hover:scale-105 shadow-sm">
              <i class="fa-solid fa-plus text-white text-[10px]"></i>
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
    <button @click="showCarDetail = false; selectedCar = null;" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-blue-600 mb-4 transition-colors bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-300 px-3 py-1.5 rounded-lg">
      <i class="fa-solid fa-arrow-left text-xs"></i>Kembali ke Daftar
    </button>

    <template x-if="selectedCar">
      <div class="page-fade">
        <div class="mb-5">
          <h2 class="text-2xl font-extrabold text-slate-800 dark:text-white leading-tight" x-text="selectedCar.name"></h2>
          <p class="text-sm text-slate-400 font-medium mt-1" x-text="selectedCar.plate || (selectedCar.children ? (selectedCar.children.length + ' unit') : '')"></p>

        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-4">
          <div class="space-y-4">
            
            <!-- Rental Info Block -->
            <template x-if="selectedCar.status === 'Tersewa' && selectedCar.is_type === 0 && selectedCar.renter">
              <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-2xl p-4 space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-blue-100 dark:border-blue-900/50">
                  <h4 class="text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wide">Informasi Sewa</h4>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs">
                  <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Penyewa</span>
                    <span class="font-bold text-slate-800 dark:text-white" x-text="selectedCar.renter"></span>
                  </div>
                  <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Durasi Sewa</span>
                    <span class="font-bold text-slate-800 dark:text-white" x-text="selectedCar.start_date + ' s/d ' + selectedCar.end_date"></span>
                  </div>
                  <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Lokasi Pengambilan</span>
                    <span class="font-bold text-slate-800 dark:text-white" x-text="selectedCar.pickup"></span>
                  </div>
                  <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Lokasi Pengembalian</span>
                    <span class="font-bold text-slate-800 dark:text-white" x-text="selectedCar.return || 'Sama dengan lokasi pengambilan'"></span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Child Unit -->
            <template x-if="selectedCar.is_type === 0">
              <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="h-[210px] flex flex-col items-center justify-center gap-3 relative" :class="selectedCar.bgCls">
                  <template x-if="selectedCar.image">
                      <img :src="selectedCar.image" class="absolute inset-0 w-full h-full object-cover">
                  </template>
                  <template x-if="!selectedCar.image">
                      <div class="flex flex-col items-center">
                          <i class="fa-solid fa-car text-[76px] opacity-60" :class="selectedCar.iconCls"></i>
                          <span class="text-xs font-semibold opacity-75 mt-2" :class="selectedCar.iconCls" x-text="selectedCar.name"></span>
                      </div>
                  </template>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 border-t border-slate-100 dark:border-slate-700 px-5 py-2.5">
                  <p class="text-xs text-slate-500">Nomor Rangka:&nbsp;<span class="font-semibold text-slate-700 dark:text-slate-200" x-text="selectedCar.chassis"></span></p>
                </div>
              </div>
            </template>

            <!-- Parent Tipe Mobil -->
            <template x-if="selectedCar.is_type === 1">
              <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between border-b dark:border-slate-700 pb-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-3xs">
                      <i class="fa-solid fa-images text-lg"></i>
                    </div>
                    <div>
                      <h3 class="text-sm font-bold text-slate-800 dark:text-white">Detail Unit Mobil</h3>
                      <p class="text-[11px] text-slate-400 font-medium mt-0.5">Semua unit untuk tipe kendaraan ini</p>
                    </div>
                  </div>
                  <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-100/50 dark:border-blue-900/30" x-text="selectedCar.children ? (selectedCar.children.length + ' Unit') : '0 Unit'"></span>
                </div>

                <!-- Grid Foto -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                  <template x-for="(c, index) in selectedCar.children" :key="c.id">
                    <div class="relative group bg-slate-50/50 dark:bg-slate-900/20 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700/80 hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 flex flex-col">
                      
                      <!-- Foto Unit -->
                      <div class="h-36 w-full relative overflow-hidden bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                        <template x-if="c.image">
                          <img :src="c.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </template>
                        <template x-if="!c.image">
                          <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 p-4">
                            <i class="fa-solid fa-car text-4xl mb-2 opacity-50"></i>
                            <span class="text-[10px] font-bold tracking-wider uppercase opacity-65">Tanpa Foto</span>
                          </div>
                        </template>
                        
                        <!-- Status -->
                        <div class="absolute top-2.5 right-2.5">
                          <span class="text-[9px] font-extrabold px-2.5 py-0.5 rounded-full shadow-3xs uppercase tracking-wide border" 
                                :class="c.status === 'Tersedia' 
                                  ? 'bg-emerald-500 text-white border-emerald-400' 
                                  : 'bg-orange-500 text-white border-orange-400'" 
                                x-text="c.status"></span>
                        </div>
                      </div>

                      <!-- Detail Unit -->
                      <div class="p-4 flex-1 flex flex-col justify-between bg-white dark:bg-slate-800">
                        <div>
                          <!-- Plat -->
                          <div class="flex justify-between items-center gap-2 mb-2">
                            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 bg-slate-100 dark:bg-slate-700 px-2.5 py-1 rounded-md font-mono tracking-wider uppercase border border-slate-200/50 dark:border-slate-600 shadow-3xs" x-text="c.plate"></span>
                          </div>
                          
                          <!-- Rangka -->
                          <p class="text-[10px] text-slate-400 font-medium" x-show="c.chassis">
                            No. Rangka: <span class="font-mono text-slate-600 dark:text-slate-300 font-semibold" x-text="c.chassis.substring(0, 10) + '...'"></span>
                          </p>

                          <!-- Info Sewa -->
                          <template x-if="c.status === 'Tersewa' && c.renter">
                            <div class="mt-3 text-[10px] text-slate-600 dark:text-slate-300 bg-orange-50/40 dark:bg-orange-950/10 border border-orange-100/50 dark:border-orange-900/30 rounded-xl p-2.5 space-y-1">
                              <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-medium">Penyewa:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-200 truncate max-w-[100px]" x-text="c.renter"></span>
                              </div>
                              <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-medium">Durasi Sewa:</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200 text-[9.5px]" x-text="c.start_date + ' - ' + c.end_date"></span>
                              </div>
                            </div>
                          </template>
                        </div>

                        <!-- Action -->
                        <div class="flex gap-2 mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/80">
                          <button type="button" @click="viewChild(c, selectedCar)" class="flex-1 text-[10.5px] font-bold py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-xl transition duration-200 text-center flex items-center justify-center gap-1.5 active:scale-97">
                            <i class="fa-solid fa-satellite-dish text-[10px]"></i> Detail & Map
                          </button>
                          <button type="button" @click="openEditCarModal({...c, make: selectedCar.make, model: selectedCar.model, year: selectedCar.year, category: selectedCar.category, price_raw: selectedCar.price_raw, fuel: selectedCar.fuel, engine: selectedCar.engine, passengers: selectedCar.passengers, transmission: selectedCar.transmission, type_key: selectedCar.type_key}, false)" class="text-[10.5px] font-bold py-2 px-3 bg-slate-50 hover:bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 rounded-xl transition duration-200 flex items-center justify-center gap-1 active:scale-97">
                            <i class="fa-solid fa-pen text-[9px]"></i> Edit
                          </button>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </template>

            <template x-if="selectedCar.is_type === 0">
              <div class="space-y-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden relative" style="height: 240px;">
                <div class="w-full h-full"
                     x-init="$nextTick(() => {
                        let lat = selectedCar.latitude || -7.1186; 
                        let lng = selectedCar.longitude || 112.4155;
                        let map = L.map($el).setView([lat, lng], 15);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OSM' }).addTo(map);

                        let carMarker = L.marker([lat, lng]).addTo(map);
                        carMarker.bindPopup(`<b>${selectedCar.name}</b><br>${selectedCar.plate}`).openPopup();

                        setTimeout(() => { map.invalidateSize(); }, 200);
                     })">
                </div>

                <div class="absolute top-3 right-3 z-[1000] flex items-center gap-1.5 bg-white dark:bg-slate-800 rounded-full px-3 py-1.5 shadow-md border border-slate-100 dark:border-slate-700">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">GPS Aktif</span>
                </div>
              </div> 
              
              <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-4">
                <div class="flex items-center gap-2 mb-3">
                  <i class="fa-solid fa-satellite-dish text-blue-500 text-xs"></i>
                  <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wide">Kontrol</h4>
                </div>
                  
                <!-- Telemetri Real-Time -->
                <div class="grid grid-cols-2 gap-3 mb-4 text-left">
                  <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100/50 dark:border-slate-700/50 p-3 rounded-xl">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Status Mobil</span>
                    <span class="font-bold text-xs text-slate-700 dark:text-slate-200 block mt-0.5">N/A</span>
                  </div>
                  <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100/50 dark:border-slate-700/50 p-3 rounded-xl">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Kecepatan</span>
                    <span class="font-bold text-xs text-slate-700 dark:text-slate-200 block mt-0.5">N/A</span>
                  </div>
                  <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100/50 dark:border-slate-700/50 p-3 rounded-xl col-span-2">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">GPS Terakhir Diperbarui</span>
                    <span class="font-bold text-xs text-slate-700 dark:text-slate-200 block mt-0.5">N/A</span>
                  </div>
                  <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100/50 dark:border-slate-700/50 p-3 rounded-xl col-span-2">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Odometer</span>
                    <span class="font-bold text-xs text-slate-700 dark:text-slate-200 block mt-0.5">N/A</span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <a :href="'triggers.php?type=alarm&id=' + selectedCar.id" class="flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-4 py-3 rounded-xl transition-all shadow-sm active:scale-95">
                    <i class="fa-solid fa-bell"></i> Bunyikan Alarm
                  </a>
                  <a :href="'triggers.php?type=mesin_mati&id=' + selectedCar.id" class="flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-3 rounded-xl transition-all shadow-sm active:scale-95">
                    <i class="fa-solid fa-power-off"></i> Matikan Mesin
                  </a>
                </div>
              </div>
            </div>
            </template>
            </div>

          </div>
        </div>
      </div>
    </template>
  </div>

  <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div @click="showEditModal = false" class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    
    <form x-ref="carForm" action="car_action.php" method="POST" enctype="multipart/form-data" class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden" style="max-height:92vh;">
      
      <input type="hidden" name="id" x-model="editForm.id">
      <input type="hidden" name="is_type" x-bind:value="editForm.is_type ? 1 : 0" x-model="editForm.is_type">
      <input type="hidden" name="type_key" x-model="editForm.type_key">

      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white" x-text="editModalTitle"></h2>
        <button type="button" @click="showEditModal = false" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 flex items-center justify-center"><i class="fa-solid fa-xmark text-slate-500 dark:text-slate-400 text-xs"></i></button>
      </div>

      <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Foto Mobil</label>
          <div class="upload-zone rounded-xl p-4 flex flex-col items-center justify-center gap-2 relative overflow-hidden h-32 border-2 border-dashed border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            
            <template x-if="imagePreview">
                <img :src="imagePreview" class="absolute inset-0 w-full h-full object-cover z-10 opacity-90">
            </template>
            
            <div class="z-0 flex flex-col items-center">
                <i class="fa-solid fa-cloud-arrow-up text-blue-400 text-2xl mb-1"></i>
                <p class="text-xs text-slate-500 text-center font-medium bg-white/70 dark:bg-slate-800/70 px-2 py-0.5 rounded">Klik atau seret foto ke sini</p>
            </div>
            
            <input type="file" name="foto_mobil" accept="image/*" @change="fileChosen" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
          </div>
        </div>

          <div class="grid grid-cols-2 gap-3">
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Merk</label><input type="text" name="make" x-model="editForm.make" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-blue-100"/></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Model</label><input type="text" name="model" x-model="editForm.model" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tahun</label><input type="text" name="year" x-model="editForm.year" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="editForm.is_type === 0"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Plat Nomor</label><input type="text" name="plate" x-model="editForm.plate" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="editForm.is_type === 0"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">No Rangka</label><input type="text" name="chassis" x-model="editForm.chassis" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kategori</label><select name="category" x-model="editForm.category" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"><option value="MPV">MPV</option><option value="SUV">SUV</option><option value="Sedan">Sedan</option><option value="EV">EV</option></select></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Harga Sewa / Hari</label><input type="number" name="price" x-model="editForm.price" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis BBM</label><input type="text" name="fuel" x-model="editForm.fuel" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas Mesin</label><input type="text" name="engine" x-model="editForm.engine" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
            <div x-show="!editForm.onlyUnit"><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Kapasitas Penumpang</label><input type="number" name="passengers" x-model="editForm.passengers" class="w-full border dark:border-slate-600 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white dark:bg-slate-700 dark:text-white"/></div>
          </div>
        <div x-show="!editForm.onlyUnit">
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Transmisi</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2"><input type="radio" name="transmission" x-model="editForm.transmission" value="Manual" class="accent-blue-600"> <span class="text-xs">Manual</span></label>
            <label class="flex items-center gap-2"><input type="radio" name="transmission" x-model="editForm.transmission" value="Matic" class="accent-blue-600"> <span class="text-xs">Matic</span></label>
          </div>
        </div>
        <input type="hidden" name="save_car" value="1">
      </div>

      <div class="flex items-center justify-between px-5 py-3 border-t dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
        <button type="button" @click="deleteCar(editForm.id, editForm.type_key, editForm.is_type)" x-show="editForm.id" class="text-red-600 text-xs font-bold px-3 py-2 bg-red-50 rounded-lg">Hapus Kendaraan</button>
        <div x-show="!editForm.id"></div>

        <div class="flex gap-2">
          <button type="button" @click="showEditModal = false" class="text-slate-600 text-xs font-bold px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">Batal</button>
          <button type="button" @click="submitForm()" class="text-white text-xs font-bold px-4 py-2 bg-blue-600 rounded-lg">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('carApp', () => ({
        showCarDetail: false,
        showEditModal: false,
        editModalTitle: '',
        carFilter: 'All',
        selectedCar: null,
        imagePreview: null, 
        
        cars: <?php echo json_encode($cars_data); ?>,
        
        editForm: { id: '', is_type: 0, type_key: '', make: '', model: '', year: '', plate: '', chassis: '', category: 'MPV', price: '', fuel: '', engine: '', passengers: '', stock: 1, transmission: 'Manual', onlyUnit: false },

        init() {
            if (sessionStorage.getItem('carActionSuccess')) {
                let msg = sessionStorage.getItem('carActionSuccess');
                this.$root.activePage = 'cars';
                setTimeout(() => { alert(msg); }, 300);
                sessionStorage.removeItem('carActionSuccess');
            }
            const params = new URLSearchParams(window.location.search);
            const typeKey = params.get('open_type_key');
            if (typeKey) {
                this.$root.activePage = 'cars';
                const found = this.cars.find(c => c.type_key === typeKey);
                if (found) {
                    this.selectedCar = found;
                    this.showCarDetail = true;
                }
                history.replaceState(null, '', window.location.pathname);
            }
        },

        get filteredCars() {
            if (this.carFilter === 'All') return this.cars;
            return this.cars.filter(c => c.category === this.carFilter);
        },

        viewCar(car) {
            this.selectedCar = car;
            this.showCarDetail = true;
        },

        openEditCarModal(car = null, isType = false) {
            if (car && isType) {
            this.editForm = {
              id: car.id || '',
              is_type: 1,
              type_key: car.type_key || '',
              make: car.make || '',
              model: car.model || '',
              year: car.year || '',
              plate: '',
              chassis: '',
              category: car.category || 'MPV',
              price: car.price_raw || '',
              fuel: car.fuel || '',
              engine: car.engine || '',
              passengers: car.passengers || '',
              stock: car.stock || 0,
              transmission: car.transmission || 'Manual'
            };
            this.editModalTitle = 'Edit Tipe Mobil';
            this.imagePreview = car.image || null;
            this.editForm.onlyUnit = false;
          } else if (car) {
            // Edit individual unit (child)
            this.editForm = { id: car.id, is_type: 0, type_key: car.type_key || '', make: car.make || '', model: car.model || '', year: car.year || '', plate: car.plate || '', chassis: car.chassis || '', category: car.category || 'MPV', price: car.price_raw || '', fuel: car.fuel || '', engine: car.engine || '', passengers: car.passengers || '', stock: car.stock || 1, transmission: car.transmission || 'Manual', onlyUnit: true };
            this.editModalTitle = 'Edit Unit Kendaraan';
            this.imagePreview = car.image || null;
          } else {
            this.editForm = { id: '', is_type: 1, type_key: '', make: '', model: '', year: '', plate: '', chassis: '', category: 'MPV', price: '', fuel: '', engine: '', passengers: '', stock: 0, transmission: 'Manual', onlyUnit: false };
            this.editModalTitle = 'Tambah Tipe Mobil Baru';
            this.imagePreview = null; // Jika tambah baru, kosongkan foto
          }
          this.showEditModal = true;
        },

        openAddUnit(type) {
          this.editForm = { id: '', is_type: 0, type_key: type.type_key || '', make: type.make || '', model: type.model || '', year: type.year || '', plate: '', chassis: '', category: type.category || 'MPV', price: type.price_raw || '', fuel: type.fuel || '', engine: type.engine || '', passengers: type.passengers || '', stock: 1, transmission: type.transmission || 'Manual', onlyUnit: true };
          this.editModalTitle = 'Tambah Unit untuk ' + (type.name || 'Tipe Mobil');
          this.imagePreview = null;
          this.showEditModal = true;
        },

        viewChild(child, parent) {
          this.selectedCar = {
            ...child,
            is_type: 0,
            name: `${parent?.make || child.make || ''} ${parent?.model || child.model || ''}`.trim(),
            make: parent?.make || child.make || '',
            model: parent?.model || child.model || '',
            year: parent?.year || child.year || '',
            category: parent?.category || child.category || 'MPV',
            price_raw: parent?.price_raw || child.price_raw || '',
            fuel: parent?.fuel || child.fuel || '',
            engine: parent?.engine || child.engine || '',
            passengers: parent?.passengers || child.passengers || '',
            transmission: parent?.transmission || child.transmission || 'Manual',
            type_key: parent?.type_key || child.type_key || ''
          };
          this.showCarDetail = true;
        },

        fileChosen(event) {
            let file = event.target.files[0];
            if (file) {
                this.imagePreview = URL.createObjectURL(file);
            }
        },

        submitForm() {
            let isEdit = this.editForm.id !== '';
            let confirmMsg = isEdit ? 'Apakah Anda yakin mau menyimpan perubahan pada kendaraan ini?' : 'Apakah Anda yakin mau menambahkan kendaraan baru ini?';
            
            if (confirm(confirmMsg)) {
                // 1. Ubah tombol jadi mode loading agar user tidak klik 2 kali
                let submitBtn = this.$refs.carForm.querySelector('button.bg-blue-600');
                if (submitBtn) {
                    submitBtn.innerText = "Memproses...";
                    submitBtn.disabled = true;
                    submitBtn.classList.add("opacity-70", "cursor-wait");
                }

                // 2. Ambil seluruh data input & file foto
                let formData = new FormData(this.$refs.carForm);
                
                // 3. Kirim data via jalur belakang (AJAX) agar layar tidak blank
                fetch('car_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    // Munculkan notif sukses
                    alert(isEdit ? 'Data kendaraan berhasil diperbarui!' : 'Kendaraan baru berhasil ditambahkan!');
                    // Muat ulang halaman secara halus
                    window.location.href = 'index.php?page=cars';
                })
                .catch(() => {
                    alert('Terjadi kesalahan saat memproses data!');
                    // Kembalikan tombol ke semula jika gagal
                    if (submitBtn) {
                        submitBtn.innerText = "Simpan";
                        submitBtn.disabled = false;
                        submitBtn.classList.remove("opacity-70", "cursor-wait");
                    }
                });
            }
        },

        // Ganti kedua fungsi deleteCar lama dengan fungsi tunggal ini di dalam Alpine.data
        deleteCar(id, typeKey = '', isType = 0) {
            let confirmMsg = 'Apakah Anda yakin mau menghapus kendaraan ini? Data yang dihapus tidak dapat dikembalikan.';
            if (isType == 1 && typeKey) {
                confirmMsg = 'PERINGATAN: Anda akan menghapus Tipe Induk ini BESERTA SEMUA UNIT MOBIL di dalamnya. Lanjutkan?';
            }
            
            if (confirm(confirmMsg)) {
                // Tampilkan indikator loading agar user tahu proses sedang berjalan
                document.body.style.cursor = 'wait';
                
                let url = 'car_action.php?delete_id=' + id;
                if (isType == 1 && typeKey) {
                    url += '&delete_is_type=1&delete_type_key=' + encodeURIComponent(typeKey);
                }
                
                // Kirim permintaan hapus ke server
                fetch(url)
                .then(response => {
                    document.body.style.cursor = 'default';
                    // Paksa pemuatan ulang halaman setelah penghapusan berhasil
                    window.location.href = 'index.php?page=cars';
                })
                .catch(error => {
                    document.body.style.cursor = 'default';
                    alert('Terjadi kesalahan saat menghapus data.');
                });
            }
        },

        // INI FUNGSI DELETE YANG SUDAH DIPERBAIKI (Tunggal & Efektif)
        deleteCar(id, typeKey = '', isType = 0) {
            let confirmMsg = 'Apakah Anda yakin mau menghapus kendaraan ini? Data yang dihapus tidak dapat dikembalikan.';
            if (isType == 1 && typeKey) {
                confirmMsg = 'PERINGATAN: Anda akan menghapus Tipe Induk ini BESERTA SEMUA UNIT MOBIL di dalamnya. Lanjutkan?';
            }
            
            if (confirm(confirmMsg)) {
                let url = 'car_action.php?delete_id=' + id;
                if (isType == 1 && typeKey) {
                    url += '&delete_is_type=1&delete_type_key=' + encodeURIComponent(typeKey);
                }
                // Langsung redirect untuk refresh halaman
                window.location.href = url;
            }
        },
        
        carBadge(status) {
            if (status === 'Tersedia') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
            if (status === 'Tersewa') return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        }
    }))
})
</script>