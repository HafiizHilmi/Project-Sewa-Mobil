<div x-show="activePage === 'settings'"
     x-data="{ showEditAdmin: false, showChangePass: false, editForm: {id:'', nama:'', email:'', role:''} }"
     x-init="
        const setDefaultTab = () => {
            if ('<?= $role ?>' === 'superuser') activeTab = 'admin';
            else activeTab = 'preferences';
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
    
    <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superuser'): ?>
      <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'" class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap">
        Admin Management
      </button>

      <button @click="activeTab = 'business'" :class="activeTab === 'business' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700 dark:hover:text-slate-200 border-b-2 border-transparent'" class="pb-3 px-1 mr-5 text-sm transition-all whitespace-nowrap">
        Business Settings
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

</div>