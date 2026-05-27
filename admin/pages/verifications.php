<!-- ====================================================
     PAGE: VERIFICATIONS
     Berisi: List user yang perlu diverifikasi, drawer detail dengan foto KTP/SIM
===================================================== -->
<div x-show="activePage === 'verifications'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     class="absolute inset-0 overflow-y-auto px-5 lg:px-6 py-5">

  <!-- Search & Filter -->
  <div class="flex items-center gap-3 flex-wrap mb-5">
    <div class="flex items-center gap-2 flex-wrap">
      <template x-for="s in ['Semua','Pending','Verified','Rejected']" :key="s">
        <button @click="verifyFilter = s"
                :class="verifyFilter === s
                  ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                  : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-blue-300'"
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition-all"
                x-text="s">
        </button>
      </template>
    </div>
  </div>

  <!-- Tabel Verifikasi -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <!-- Header Tabel (Desktop) -->
    <div class="hidden md:grid px-5 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-[11px] font-bold uppercase tracking-wider"
         style="grid-template-columns: 48px 1fr 150px 150px 110px 50px; gap: 10px;">
      <div>No</div><div>Pelanggan</div><div>Tanggal Upload</div><div>Status</div><div>Aksi</div>
    </div>

    <div>
      <template x-for="(v, idx) in filteredVerifications" :key="v.id">
        <div>
          <div x-show="idx > 0" class="border-t border-slate-100 dark:border-slate-700 mx-5"></div>

          <!-- Desktop Row -->
          <div class="hidden md:grid px-5 py-3.5 items-center dark:hover:bg-slate-700/30"
               style="grid-template-columns: 48px 1fr 150px 150px 110px 50px; gap: 10px;">
            <div class="text-sm font-semibold text-slate-500 dark:text-slate-400" x-text="idx + 1"></div>
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold shadow-sm bg-blue-500">
                <span x-text="v.name.charAt(0)"></span>
              </div>
              <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate" x-text="v.name"></p>
                <p class="text-[10px] text-blue-500 truncate" x-text="v.email"></p>
              </div>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium" x-text="v.created_at"></div>
            <div>
              <span class="inline-block text-[11px] font-bold px-3 py-1 rounded-full uppercase"
                    :class="{
                      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : v.verification_status === 'pending',
                      'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : v.verification_status === 'verified',
                      'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : v.verification_status === 'rejected',
                    }" x-text="v.verification_status"></span>
            </div>
            <div>
              <button @click="openVerificationDetail(v)" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-1.5 px-3 rounded-lg transition-colors">Lihat Dokumen</button>
            </div>
          </div>

          <!-- Mobile Row -->
          <div class="md:hidden flex flex-col gap-3 px-4 py-3.5 dark:hover:bg-slate-700/30">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold shadow-sm bg-blue-500">
                  <span x-text="v.name.charAt(0)"></span>
                </div>
                <div>
                  <p class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="v.name"></p>
                  <p class="text-xs text-slate-400" x-text="v.email"></p>
                </div>
              </div>
              <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase"
                    :class="{'bg-yellow-100 text-yellow-700': v.verification_status === 'pending', 'bg-green-100 text-green-700': v.verification_status === 'verified', 'bg-red-100 text-red-600': v.verification_status === 'rejected'}" x-text="v.verification_status"></span>
            </div>
            <button @click="openVerificationDetail(v)" class="w-full text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-lg transition-colors">Lihat Dokumen</button>
          </div>
        </div>
      </template>

      <div x-show="filteredVerifications.length === 0" class="py-16 text-center">
        <i class="fa-solid fa-id-card text-4xl text-slate-200 dark:text-slate-700 mb-3 block"></i>
        <p class="text-sm text-slate-400 font-medium">Tidak ada data verifikasi ditemukan</p>
      </div>
    </div>
  </div>
</div>

<!-- ── Drawer Detail Verifikasi (Slide dari kanan) ── -->
<div x-show="showVerificationDetail" @click="closeVerificationDetail()" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm" x-transition.opacity></div>
<div x-show="showVerificationDetail" class="fixed right-0 top-0 bottom-0 z-50 w-full max-w-2xl bg-white dark:bg-slate-800 shadow-2xl flex flex-col overflow-hidden"
     x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
     x-transition:leave="transform transition ease-in duration-250" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
  <template x-if="selectedVerification">
    <div class="flex flex-col h-full">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Review Dokumen Identitas</h2>
        <button @click="closeVerificationDetail()" class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400"><i class="fa-solid fa-xmark text-xs"></i></button>
      </div>
      
      <div class="flex-1 overflow-y-auto p-5 space-y-6">
        <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
          <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
             <span x-text="selectedVerification.name.charAt(0)"></span>
          </div>
          <div>
             <h3 class="font-bold text-lg text-slate-800" x-text="selectedVerification.name"></h3>
             <p class="text-sm text-slate-500" x-text="selectedVerification.email"></p>
          </div>
          <div class="ml-auto">
             <span class="inline-block text-xs font-bold px-3 py-1 rounded-full uppercase"
                    :class="{
                      'bg-yellow-100 text-yellow-700' : selectedVerification.verification_status === 'pending',
                      'bg-green-100 text-green-700' : selectedVerification.verification_status === 'verified',
                      'bg-red-100 text-red-600' : selectedVerification.verification_status === 'rejected',
                    }" x-text="selectedVerification.verification_status"></span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <p class="text-sm font-bold text-slate-700 mb-2">Dokumen KTP</p>
                <div class="border rounded-xl p-2 bg-slate-50 aspect-video flex items-center justify-center overflow-hidden">
                    <img :src="'serve_file.php?file=' + selectedVerification.ktp_file" class="max-w-full max-h-full object-contain hover:scale-110 transition cursor-zoom-in" alt="KTP">
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-700 mb-2">Dokumen SIM</p>
                <div class="border rounded-xl p-2 bg-slate-50 aspect-video flex items-center justify-center overflow-hidden">
                    <img :src="'serve_file.php?file=' + selectedVerification.sim_file" class="max-w-full max-h-full object-contain hover:scale-110 transition cursor-zoom-in" alt="SIM">
                </div>
            </div>
        </div>

        <div x-show="selectedVerification.verification_status === 'pending'" class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold text-blue-800 mb-1">Tindakan Diperlukan</p>
                <p class="text-xs text-blue-600 mb-4">Pastikan KTP dan SIM asli, jelas terbaca, dan nama sesuai dengan data yang terdaftar.</p>
                <div x-data="{ action: '' }" class="w-full">
                    <div class="flex gap-2">
                        <button type="button" @click="action = 'verified'" :class="action === 'verified' ? 'ring-2 ring-offset-1 ring-green-500 bg-green-700' : 'bg-green-600 hover:bg-green-700'" class="text-white font-bold py-2 px-4 rounded-lg text-xs transition-all">
                            <i class="fa-solid fa-check mr-1"></i> Approve
                        </button>
                        <button type="button" @click="action = 'rejected'" :class="action === 'rejected' ? 'ring-2 ring-offset-1 ring-red-500 bg-red-700' : 'bg-red-600 hover:bg-red-700'" class="text-white font-bold py-2 px-4 rounded-lg text-xs transition-all">
                            <i class="fa-solid fa-xmark mr-1"></i> Reject
                        </button>
                    </div>

                    <form method="POST" action="verify_action.php" x-show="action !== ''" x-transition.opacity class="mt-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
                        <input type="hidden" name="user_id" :value="selectedVerification.id">
                        <input type="hidden" name="action" :value="action">
                        
                        <div x-show="action === 'rejected'" class="mb-4">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="reject_reason" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" rows="3" placeholder="Contoh: Foto KTP buram dan terpotong..." :required="action === 'rejected'"></textarea>
                            <p class="text-[10px] text-slate-500 mt-1.5"><i class="fa-solid fa-circle-info mr-1"></i>Alasan ini akan otomatis muncul di profil pelanggan.</p>
                        </div>

                        <button type="submit" class="w-full text-white font-bold py-2.5 px-4 rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 shadow-sm" :class="action === 'verified' ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'">
                            <i class="fa-solid" :class="action === 'verified' ? 'fa-check-circle' : 'fa-paper-plane'"></i>
                            <span x-text="action === 'verified' ? 'Konfirmasi Approve Akun' : 'Kirim Penolakan'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

      </div>
    </div>
  </template>
</div>
