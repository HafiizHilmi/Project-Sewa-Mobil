<div x-show="activePage === 'settings'"
     x-data="{ showEditAdmin: false, showChangePass: false, editForm: {id:'', nama:'', email:'', role:''} }"
     x-init="
        const setDefaultTab = () => {
            const params = new URLSearchParams(window.location.search);
            // Cek apakah ada request tab spesifik di URL (misal: &tab=blacklist)
            if (params.has('tab')) {
                activeTab = params.get('tab');
            } else if ('<?= $role ?>' === 'superuser') {
                activeTab = 'admin';
            } else {
                activeTab = 'preferences';
            }
        };
        // Set tab saat halaman pertama dimuat
        if (activePage === 'settings') setDefaultTab();
        
        // Pantau jika user berpindah menu dari sidebar
        $watch('activePage', value => { 
            if (value === 'settings') setDefaultTab(); 
        });
     "
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

  <div class="flex items-center border-b border-slate-200 dark:border-slate-700 mb-6 gap-1">
    
    <?php if (isset($role) && $role === 'superuser'): ?>
      <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'" class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap">
        Admin Management
      </button>

      <button @click="activeTab = 'business'" :class="activeTab === 'business' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'" class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap">
        Business Settings
      </button>

      <button @click="activeTab = 'blacklist'" :class="activeTab === 'blacklist' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'" class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap">
        Blacklist Area
      </button>
    <?php endif; ?>

    <button @click="activeTab = 'preferences'" :class="activeTab === 'preferences' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'" class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap">
      Preferences
    </button>
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
      <div class="hidden sm:grid px-5 py-3.5 bg-gradient-to-r from-slate-700 to-slate-800 text-slate-200 text-[11px] font-bold uppercase tracking-wider" style="grid-template-columns: 1fr 180px 110px 140px 80px; gap: 12px;">
        <div>Admin</div><div>Email</div><div>Role</div><div>Ditambahkan</div><div class="text-right">Aksi</div>
      </div>
      
      <?php
      $stmtAdmins = $pdo->query("SELECT * FROM admins ORDER BY id DESC");
      $adminList = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);
      
      $index = 0;
      foreach ($adminList as $admin):
          $index++;
          $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
          $color = $colors[$admin['id'] % count($colors)];
          
          $inisial = strtoupper(substr($admin['nama'], 0, 1));
          $isSuper = ($admin['role'] === 'superuser');
          $roleText = $isSuper ? 'Super Admin' : 'Staff';
          $roleClass = $isSuper ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
      ?>
        <div>
          <?php if($index > 1): ?>
          <div class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>
          <?php endif; ?>
          
          <div class="tbl-row hidden sm:grid px-5 py-4 items-center dark:hover:bg-slate-700/30" style="grid-template-columns: 1fr 180px 110px 140px 80px; gap: 12px;">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background: <?= $color ?>">
                <span><?= $inisial ?></span>
              </div>
              <div><p class="text-sm font-bold text-slate-800 dark:text-slate-100"><?= htmlspecialchars($admin['nama']) ?></p></div>
            </div>
            
            <div class="text-xs text-slate-500 dark:text-slate-400 truncate"><?= htmlspecialchars($admin['email']) ?></div>
            <div><span class="text-[11px] font-bold px-2.5 py-1 rounded-md <?= $roleClass ?>"><?= $roleText ?></span></div>
            <div class="text-xs text-slate-400 font-medium"><?= date('d M Y', strtotime($admin['created_at'])) ?></div>
            
            <div class="flex justify-end gap-1.5">
              <button @click="editForm.id = '<?= $admin['id'] ?>'; editForm.nama = '<?= addslashes($admin['nama']) ?>'; editForm.email = '<?= addslashes($admin['email']) ?>'; editForm.role = '<?= $admin['role'] ?>'; showEditAdmin = true;" 
                      title="Edit Admin/Staff" class="w-7 h-7 rounded-lg flex items-center justify-center text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                <i class="fa-solid fa-pen text-[11px]"></i>
              </button>
              
              <?php if(!$isSuper): ?>
              <form action="admin_action.php" method="POST" class="m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun staff ini?');">
                  <input type="hidden" name="action" value="delete_admin">
                  <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                  <button type="submit" title="Hapus Staff" class="w-7 h-7 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                  </button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div x-show="activeTab === 'business'" class="page-fade">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center"><i class="fa-solid fa-building text-blue-500 text-sm"></i></div>
        <div><h2 class="text-sm font-bold text-slate-800 dark:text-white">Informasi Bisnis</h2><p class="text-xs text-slate-400">Konfigurasi data utama perusahaan</p></div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Perusahaan / Toko</label>
          <input type="text" value="Sewa Mobil SBY" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 focus:outline-none focus:border-blue-400"/>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">No Telp / WhatsApp</label>
          <input type="text" value="08123456789" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 focus:outline-none focus:border-blue-400"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Sosial Media (Instagram/Facebook)</label>
          <input type="text" value="@sewamobilsby" placeholder="Contoh: @sewamobilsby" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 focus:outline-none focus:border-blue-400"/>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Alamat Perusahaan</label>
          <textarea rows="3" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 focus:outline-none focus:border-blue-400 resize-none">Jl. Ketintang Baru, Surabaya, Jawa Timur</textarea>
        </div>
      </div>
      
      <button class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
        <i class="fa-solid fa-floppy-disk text-xs"></i>Save Changes
      </button>
    </div>
  </div>

  <?php
  // Buat/Sesuaikan tabel blacklist untuk mendukung geofencing
  $pdo->exec("CREATE TABLE IF NOT EXISTS blacklisted_locations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      location_name VARCHAR(150) NOT NULL,
      latitude DECIMAL(10, 8) DEFAULT NULL,
      longitude DECIMAL(11, 8) DEFAULT NULL,
      radius INT DEFAULT 5000,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  // Pastikan kolom geofence ada jika tabel sudah pernah dibuat sebelumnya
  try {
      $pdo->exec("ALTER TABLE blacklisted_locations ADD COLUMN latitude DECIMAL(10,8) DEFAULT NULL");
  } catch (Exception $e) {}
  try {
      $pdo->exec("ALTER TABLE blacklisted_locations ADD COLUMN longitude DECIMAL(11,8) DEFAULT NULL");
  } catch (Exception $e) {}
  try {
      $pdo->exec("ALTER TABLE blacklisted_locations ADD COLUMN radius INT DEFAULT 5000");
  } catch (Exception $e) {}

  $stmtBL = $pdo->query("SELECT * FROM blacklisted_locations ORDER BY id DESC");
  $blacklists = $stmtBL->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <div x-show="activeTab === 'blacklist'"
       x-data="{ showAddForm: false, selectedLat: '', selectedLng: '', selectedRadius: 5000, selectedName: '' }"
       x-init="
          $watch('activeTab', value => {
              if (value === 'blacklist') {
                  setTimeout(initGeofenceMap, 200);
              }
          });
          if (activeTab === 'blacklist') {
              setTimeout(initGeofenceMap, 200);
          }
       "
       class="page-fade">
       
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
      <div>
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Geofencing Blacklist Area</h2>
        <p class="text-xs text-slate-400 mt-0.5">Blokir otomatis transaksi sewa dari/ke zona merah rawan</p>
      </div>
      <div class="text-[11px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 px-3 py-1.5 rounded-xl border dark:border-slate-700 flex items-center gap-1.5 font-medium shrink-0">
        <i class="fa-solid fa-circle-info text-blue-500"></i> Klik lokasi di peta untuk membuat Geofence baru
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- MAP CONTAINER WITH LOCATION SEARCH BAR -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-3.5 relative overflow-visible flex flex-col gap-3">
        <!-- Search bar input -->
        <div class="relative z-30">
          <div class="relative flex items-center">
            <input type="text" id="map-search-input" placeholder="Cari wilayah/kota untuk di-blacklist (Cth: Pati, Surabaya, Jakarta)..." autocomplete="off" class="w-full border dark:border-slate-600 rounded-xl pl-10 pr-10 py-2.5 text-xs bg-slate-50 dark:bg-slate-700 dark:text-white outline-none focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-inner"/>
            <div class="absolute left-3.5 text-slate-400"><i class="fa-solid fa-magnifying-glass text-xs"></i></div>
            <button type="button" id="map-search-clear" class="absolute right-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hidden"><i class="fa-solid fa-circle-xmark text-xs"></i></button>
          </div>
          <!-- Search Dropdown Suggestions -->
          <div id="map-search-results" class="absolute left-0 right-0 top-[calc(100%+4px)] z-40 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-60 overflow-y-auto hidden"></div>
        </div>
        <div id="geofence-map" class="w-full rounded-xl z-10" style="height: 440px; min-height: 380px;"></div>
      </div>
      
      <!-- CONTROL PANEL -->
      <div class="lg:col-span-1 flex flex-col gap-4">
        <!-- FORM TAMBAH GEOFENCE -->
        <div x-show="showAddForm" x-transition.opacity class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400"><i class="fa-solid fa-plus text-xs"></i></div>
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Geofence Baru</h3>
          </div>
          
          <form action="admin_action.php" method="POST" id="geofence-form">
            <input type="hidden" name="action" value="add_blacklist">
            <input type="hidden" name="latitude" x-model="selectedLat">
            <input type="hidden" name="longitude" x-model="selectedLng">
            
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Wilayah / Keterangan</label>
                <input type="text" name="location_name" x-model="selectedName" required placeholder="Cth: Zona Rawan Pati" class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-xs bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-red-500"/>
              </div>
              
              <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5 flex justify-between">
                  <span>Radius Geofence</span>
                  <span class="text-red-500 font-bold" x-text="selectedRadius >= 1000 ? (selectedRadius/1000).toFixed(1) + ' km' : selectedRadius + ' meter'"></span>
                </label>
                <input type="range" name="radius" min="500" max="1000000" step="500" x-model="selectedRadius" @input="updateTempGeofenceCircle(selectedRadius)" class="w-full h-1 bg-slate-100 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-red-600"/>
                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                  <span>500 m</span><span>500 km</span><span>1000 km</span>
                </div>
              </div>
              
              <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 text-[10px] text-slate-500 dark:text-slate-400 space-y-1">
                <div class="flex justify-between"><span>Latitude:</span><span class="font-mono text-slate-700 dark:text-slate-300" x-text="parseFloat(selectedLat || 0).toFixed(6)"></span></div>
                <div class="flex justify-between"><span>Longitude:</span><span class="font-mono text-slate-700 dark:text-slate-300" x-text="parseFloat(selectedLng || 0).toFixed(6)"></span></div>
              </div>
              
              <div class="flex gap-2 pt-2">
                <button type="button" @click="cancelAddGeofence()" class="flex-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border dark:border-slate-600 px-4 py-2.5 rounded-xl transition-colors hover:bg-slate-50">Batal</button>
                <button type="submit" class="flex-1 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-4 py-2.5 rounded-xl transition-colors">Simpan</button>
              </div>
            </div>
          </form>
        </div>
        
        <!-- LIST GEOFENCE TERDAFTAR -->
        <div x-show="!showAddForm" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5 flex-1 flex flex-col min-h-[300px]">
          <div class="flex items-center justify-between mb-4 shrink-0">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400"><i class="fa-solid fa-ban text-xs"></i></div>
              <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Geofence Aktif</h3>
            </div>
            <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-2 py-0.5 rounded-md" x-text="'Total: ' + <?= count($blacklists) ?>"></span>
          </div>
          
          <div class="space-y-3 overflow-y-auto max-h-[320px] pr-1 flex-1 custom-scrollbar">
            <?php if (count($blacklists) > 0): ?>
              <?php foreach ($blacklists as $bl): ?>
                <div class="border border-slate-100 dark:border-slate-700/80 rounded-xl p-3 hover:border-slate-200 dark:hover:border-slate-600 hover:shadow-sm transition-all group">
                  <div class="flex justify-between items-start">
                    <div class="min-w-0 flex-1">
                      <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate"><?= htmlspecialchars($bl['location_name']) ?></h4>
                      <p class="text-[10px] text-slate-400 mt-1">Radius: <span class="font-semibold text-slate-600 dark:text-slate-300"><?= $bl['radius'] >= 1000000 ? '1000 km' : ($bl['radius'] >= 1000 ? ($bl['radius']/1000) . ' km' : $bl['radius'] . ' m') ?></span></p>
                      <?php if($bl['latitude'] && $bl['longitude']): ?>
                        <p class="text-[9px] text-slate-400 font-mono mt-0.5"><?= number_format($bl['latitude'], 4) ?>, <?= number_format($bl['longitude'], 4) ?></p>
                      <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-1 shrink-0 ml-2">
                      <button onclick="zoomToGeofence(<?= $bl['latitude'] ?>, <?= $bl['longitude'] ?>, <?= $bl['radius'] ?>)" title="Pusatkan Peta" class="w-7 h-7 rounded-lg flex items-center justify-center text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                        <i class="fa-solid fa-crosshairs text-[10px]"></i>
                      </button>
                      
                      <form action="admin_action.php" method="POST" class="m-0 p-0" onsubmit="return confirm('Hapus geofence ini?');">
                        <input type="hidden" name="action" value="delete_blacklist">
                        <input type="hidden" name="id" value="<?= $bl['id'] ?>">
                        <button type="submit" title="Hapus Blacklist" class="w-7 h-7 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                          <i class="fa-solid fa-trash-can text-[10px]"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="p-8 text-center text-slate-400 text-xs flex-1 flex flex-col items-center justify-center">
                <i class="fa-solid fa-map-location-dot text-2xl text-slate-300 dark:text-slate-600 mb-2"></i>
                <span>Belum ada geofence terdaftar.</span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div x-show="activeTab === 'preferences'" class="page-fade">
    
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Keamanan & Profil</h2>
        <p class="text-xs text-slate-400 mt-0.5">Atur keamanan akun kamu</p>
      </div>
      <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3.5 min-w-0">
          <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-lock text-slate-500 text-sm"></i></div>
          <div>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Ganti Password</p>
            <p class="text-xs text-slate-400 mt-0.5">Perbarui password akun untuk menjaga keamanan</p>
          </div>
        </div>
        <button @click="showChangePass = true" class="text-xs font-bold text-slate-700 dark:text-white bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 px-4 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors shadow-sm">Ubah Password</button>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Preferensi Tampilan</h2>
      </div>
      <div class="divide-y divide-slate-100 dark:divide-slate-700">
        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-envelope text-blue-500 text-sm"></i></div>
            <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Email Notifications</p><p class="text-xs text-slate-400 mt-0.5">Terima notifikasi pesanan dan pembayaran via email</p></div>
          </div>
          <div @click="emailNotifs = !emailNotifs" class="toggle-track ml-4 flex-shrink-0" :class="emailNotifs ? 'on' : ''"><div class="toggle-thumb"></div></div>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-bell text-orange-400 text-sm"></i></div>
            <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Push Notifications</p><p class="text-xs text-slate-400 mt-0.5">Notifikasi real-time di browser</p></div>
          </div>
          <div @click="pushNotifs = !pushNotifs" class="toggle-track ml-4 flex-shrink-0" :class="pushNotifs ? 'on' : ''"><div class="toggle-thumb"></div></div>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors" :class="darkMode ? 'bg-indigo-900/30' : 'bg-indigo-50'"><i class="fa-solid text-indigo-500 text-sm" :class="darkMode ? 'fa-moon' : 'fa-sun'"></i></div>
            <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Dark Mode Theme</p><p class="text-xs text-slate-400 mt-0.5">Mode gelap untuk penggunaan di lingkungan redup</p></div>
          </div>
          <div @click="darkMode = !darkMode" class="toggle-track ml-4 flex-shrink-0" :class="darkMode ? 'on' : ''"><div class="toggle-thumb"></div></div>
        </div>

        <div class="flex items-center justify-between px-6 py-4">
          <div class="flex items-center gap-3.5 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-rotate text-emerald-500 text-sm"></i></div>
            <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Auto Refresh Data</p><p class="text-xs text-slate-400 mt-0.5">Perbarui data dashboard setiap 5 menit otomatis</p></div>
          </div>
          <div @click="autoRefresh = !autoRefresh" class="toggle-track ml-4 flex-shrink-0" :class="autoRefresh ? 'on' : ''"><div class="toggle-thumb"></div></div>
        </div>
      </div>
    </div>
  </div>

  <div x-show="showAddAdminModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <div @click="showAddAdminModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
    <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col p-5" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
      <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Tambah Admin Baru</h2>
      <form action="admin_action.php" method="POST">
          <input type="hidden" name="action" value="add_admin">
          <div class="space-y-4 mb-5">
            <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Lengkap</label><input type="text" name="nama" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/></div>
            <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Email</label><input type="email" name="email" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/></div>
            <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Password</label><input type="password" name="password" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/></div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Role Akses</label>
              <select name="role" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500">
                  <option value="staff">Staff (Akses Terbatas)</option><option value="superuser">Super Admin (Akses Penuh)</option>
              </select>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="showAddAdminModal = false" class="flex-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border dark:border-slate-600 px-4 py-2.5 rounded-xl transition-colors hover:bg-slate-50">Batal</button>
            <button type="submit" class="flex-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition-colors">Simpan</button>
          </div>
      </form>
    </div>
  </div>

  <div x-show="showEditAdmin" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <div @click="showEditAdmin = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
    <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md flex flex-col p-5" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
      <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Edit Data Admin</h2>
      <form action="admin_action.php" method="POST">
          <input type="hidden" name="action" value="edit_admin">
          <input type="hidden" name="id" x-model="editForm.id">
          
          <div class="space-y-4 mb-5">
            <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Nama Lengkap</label><input type="text" name="nama" x-model="editForm.nama" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/></div>
            <div><label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Email</label><input type="email" name="email" x-model="editForm.email" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/></div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Password Baru (Opsional)</label>
              <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Role Akses</label>
              <select name="role" x-model="editForm.role" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500">
                  <option value="staff">Staff (Akses Terbatas)</option><option value="superuser">Super Admin (Akses Penuh)</option>
              </select>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="showEditAdmin = false" class="flex-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border dark:border-slate-600 px-4 py-2.5 rounded-xl transition-colors hover:bg-slate-50">Batal</button>
            <button type="submit" class="flex-1 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2.5 rounded-xl transition-colors">Simpan Perubahan</button>
          </div>
      </form>
    </div>
  </div>

  <div x-show="showChangePass" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <div @click="showChangePass = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity></div>
    <div class="modal-box relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm flex flex-col p-5" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
      <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-4">Ganti Password</h2>
      <form action="admin_action.php" method="POST">
          <input type="hidden" name="action" value="change_password">
          <div class="space-y-4 mb-5">
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Password Lama</label>
              <input type="password" name="old_password" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Password Baru</label>
              <input type="password" name="new_password" required class="w-full border dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-700 dark:text-white outline-none focus:border-blue-500"/>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="showChangePass = false" class="flex-1 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border dark:border-slate-600 px-4 py-2.5 rounded-xl transition-colors hover:bg-slate-50">Batal</button>
            <button type="submit" class="flex-1 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 rounded-xl transition-colors">Perbarui</button>
          </div>
      </form>
    </div>
  </div>

  <!-- Dynamic Leaflet Loader and Geofencing JS Script -->
  <script>
      // Load Leaflet dynamically if not loaded
      if (typeof L === 'undefined') {
          const link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
          link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
          link.crossOrigin = '';
          document.head.appendChild(link);

          const script = document.createElement('script');
          script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
          script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
          script.crossOrigin = '';
          document.head.appendChild(script);
      }

      let map, tempMarker, tempCircle;
      let geofenceCircles = {};
      let mapInitialized = false;

      function initGeofenceMap() {
          if (typeof L === 'undefined') {
              // Wait until Leaflet is fully loaded in head
              setTimeout(initGeofenceMap, 100);
              return;
          }

          if (mapInitialized) {
              if (map) {
                  map.invalidateSize();
              }
              return;
          }

          const mapContainer = document.getElementById('geofence-map');
          if (!mapContainer) return;

          // Inisialisasi Map, Default Center Surabaya (-7.2575, 112.7521)
          map = L.map('geofence-map').setView([-7.2575, 112.7521], 11);

          // Pilih Tile Layer berdasarkan tema Dark/Light
          const isDark = document.documentElement.classList.contains('dark');
          const tileUrl = isDark 
              ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
              : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
              
          L.tileLayer(tileUrl, {
              attribution: '&copy; OpenStreetMap &copy; CARTO'
          }).addTo(map);

          // Render Geofence terdaftar dari Database
          const geofencesData = <?= json_encode($blacklists) ?>;
          
          geofencesData.forEach(bl => {
              if (bl.latitude && bl.longitude) {
                  const lat = parseFloat(bl.latitude);
                  const lng = parseFloat(bl.longitude);
                  const rad = parseInt(bl.radius);
                  
                  // Buat Circle Merah
                  const circle = L.circle([lat, lng], {
                      color: '#ef4444',
                      fillColor: '#f87171',
                      fillOpacity: 0.2,
                      weight: 2,
                      radius: rad
                  }).addTo(map);

                  // Detail content untuk popup
                  const popupContent = `
                      <div class="p-2 text-slate-800 dark:text-slate-100 font-sans" style="min-width: 160px; color: #1e293b;">
                          <h4 class="font-bold text-xs mb-1" style="margin:0; color: #0f172a;">${bl.location_name}</h4>
                          <p class="text-[10px] text-slate-500" style="margin:0 0 8px 0;">Radius: ${rad >= 1000000 ? '1000 km' : (rad >= 1000 ? (rad/1000) + ' km' : rad + ' m')}</p>
                          <form action="admin_action.php" method="POST" onsubmit="return confirm('Hapus geofence ini?');" style="margin:0;">
                              <input type="hidden" name="action" value="delete_blacklist">
                              <input type="hidden" name="id" value="${bl.id}">
                              <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold py-1.5 px-2 rounded-lg transition-colors border-0 cursor-pointer">Hapus Geofence</button>
                          </form>
                      </div>
                  `;

                  circle.bindPopup(popupContent);
                  geofenceCircles[bl.id] = { circle, lat, lng, rad };
              }
          });

          // Event listener saat peta diklik
          map.on('click', function(e) {
              const lat = e.latlng.lat;
              const lng = e.latlng.lng;
              
              const blacklistTab = document.querySelector('[x-show="activeTab === \'blacklist\'"]');
              if (blacklistTab) {
                  const alpineData = Alpine.$data(blacklistTab);
                  
                  alpineData.selectedLat = lat;
                  alpineData.selectedLng = lng;
                  alpineData.showAddForm = true;
                  
                  // Gambar Marker & Circle sementara
                  if (tempMarker) map.removeLayer(tempMarker);
                  if (tempCircle) map.removeLayer(tempCircle);

                  // Buat marker yang bisa digeser (draggable)
                  tempMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
                  tempCircle = L.circle([lat, lng], {
                      color: '#3b82f6',
                      fillColor: '#60a5fa',
                      fillOpacity: 0.15,
                      weight: 2,
                      radius: alpineData.selectedRadius
                  }).addTo(map);

                  // Sinkronisasi posisi marker saat digeser
                  tempMarker.on('drag', function(evt) {
                      const newLatLng = evt.latlng;
                      if (tempCircle) {
                          tempCircle.setLatLng(newLatLng);
                      }
                      alpineData.selectedLat = newLatLng.lat;
                      alpineData.selectedLng = newLatLng.lng;
                  });

                  map.panTo([lat, lng]);
              }
          });

          // --- KODE AUTOCOMPLETE / SEARCH MAP DENGAN NOMINATIM ---
          const searchInput = document.getElementById('map-search-input');
          const searchClear = document.getElementById('map-search-clear');
          const searchResults = document.getElementById('map-search-results');
          
          if (searchInput && searchResults) {
              let debounceTimeout;
              
              searchInput.addEventListener('input', function() {
                  const query = searchInput.value.trim();
                  
                  if (query.length > 0) {
                      if (searchClear) searchClear.classList.remove('hidden');
                  } else {
                      if (searchClear) searchClear.classList.add('hidden');
                      searchResults.classList.add('hidden');
                      searchResults.innerHTML = '';
                      return;
                  }
                  
                  clearTimeout(debounceTimeout);
                  debounceTimeout = setTimeout(() => {
                      // Tampilkan spinner loading di dropdown
                      searchResults.innerHTML = `
                          <div class="p-3 text-center text-xs text-slate-400">
                              <i class="fa-solid fa-spinner fa-spin mr-1.5 text-blue-500"></i>Mencari lokasi...
                          </div>
                      `;
                      searchResults.classList.remove('hidden');
                      
                      const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1&countrycodes=id`;
                      
                      fetch(url, {
                          headers: {
                              'Accept-Language': 'id-ID,id;q=0.9',
                              'User-Agent': 'SewaMobil-Geofence-Admin'
                          }
                      })
                      .then(res => res.json())
                      .then(data => {
                          if (!data || data.length === 0) {
                              searchResults.innerHTML = `
                                  <div class="p-3 text-center text-xs text-slate-400">
                                      <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-500"></i>Lokasi tidak ditemukan
                                  </div>
                              `;
                              return;
                          }
                          
                          let html = '';
                          data.forEach((place, idx) => {
                              // Potong display_name agar rapi
                              const mainText = place.name || place.display_name.split(',')[0];
                              const secondaryText = place.display_name.replace(mainText + ', ', '');
                              
                              html += `
                                  <div class="p-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 border-b last:border-0 border-slate-100 dark:border-slate-800 cursor-pointer transition-colors" 
                                       onclick="selectSearchedLocation(${place.lat}, ${place.lon}, '${escapeJs(mainText)}', '${escapeJs(place.display_name)}')">
                                      <div class="text-[11px] font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-location-dot mr-1.5 text-red-500 text-[10px]"></i>${escapeHtml(mainText)}</div>
                                      <div class="text-[9px] text-slate-400 mt-0.5 truncate pl-3">${escapeHtml(secondaryText)}</div>
                                  </div>
                              `;
                          });
                          searchResults.innerHTML = html;
                      })
                      .catch(err => {
                          searchResults.innerHTML = `
                              <div class="p-3 text-center text-xs text-red-500">
                                  Gagal memuat data pencarian
                              </div>
                          `;
                      });
                  }, 400);
              });
              
              if (searchClear) {
                  searchClear.addEventListener('click', function() {
                      searchInput.value = '';
                      searchClear.classList.add('hidden');
                      searchResults.classList.add('hidden');
                      searchResults.innerHTML = '';
                  });
              }
              
              // Tutup dropdown jika klik di luar
              document.addEventListener('click', function(e) {
                  if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                      searchResults.classList.add('hidden');
                  }
              });
          }

          mapInitialized = true;
      }

      function escapeHtml(text) {
          if (!text) return '';
          return text
              .replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
      }

      function escapeJs(text) {
          if (!text) return '';
          return text
              .replace(/\\/g, '\\\\')
              .replace(/'/g, "\\'")
              .replace(/"/g, '\\"');
      }

      function selectSearchedLocation(lat, lon, mainName, fullName) {
          const blacklistTab = document.querySelector('[x-show="activeTab === \'blacklist\'"]');
          if (blacklistTab && map) {
              const alpineData = Alpine.$data(blacklistTab);
              
              alpineData.selectedLat = lat;
              alpineData.selectedLng = lon;
              alpineData.selectedName = mainName;
              alpineData.showAddForm = true;
              
              // Gambar Marker & Circle sementara
              if (tempMarker) map.removeLayer(tempMarker);
              if (tempCircle) map.removeLayer(tempCircle);

              tempMarker = L.marker([lat, lon], { draggable: true }).addTo(map);
              tempCircle = L.circle([lat, lon], {
                  color: '#3b82f6',
                  fillColor: '#60a5fa',
                  fillOpacity: 0.15,
                  weight: 2,
                  radius: alpineData.selectedRadius
              }).addTo(map);

              // Sinkronisasi posisi marker saat digeser
              tempMarker.on('drag', function(evt) {
                  const newLatLng = evt.latlng;
                  if (tempCircle) {
                      tempCircle.setLatLng(newLatLng);
                  }
                  alpineData.selectedLat = newLatLng.lat;
                  alpineData.selectedLng = newLatLng.lng;
              });

              // Centering Map dengan zoom yang pas
              map.setView([lat, lon], 12);
              
              // Sembunyikan dropdown saran
              const searchResults = document.getElementById('map-search-results');
              if (searchResults) searchResults.classList.add('hidden');
          }
      }

      function updateTempGeofenceCircle(radius) {
          if (tempCircle && map) {
              tempCircle.setRadius(parseInt(radius));
          }
      }

      function cancelAddGeofence() {
          const blacklistTab = document.querySelector('[x-show="activeTab === \'blacklist\'"]');
          if (blacklistTab) {
              const alpineData = Alpine.$data(blacklistTab);
              alpineData.showAddForm = false;
              alpineData.selectedName = '';
              alpineData.selectedLat = '';
              alpineData.selectedLng = '';
              
              if (tempMarker) { map.removeLayer(tempMarker); tempMarker = null; }
              if (tempCircle) { map.removeLayer(tempCircle); tempCircle = null; }
          }
      }

      function zoomToGeofence(lat, lng, radius) {
          if (map) {
              map.setView([lat, lng], 13);
              Object.values(geofenceCircles).forEach(item => {
                  if (item.lat === lat && item.lng === lng) {
                      setTimeout(() => {
                          item.circle.openPopup();
                      }, 300);
                  }
              });
          }
      }
  </script>
</div>