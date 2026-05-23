<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaMobil - Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Tailwind handles font */
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="index.php?module=Homepage&action=index" class="text-blue-700 font-extrabold text-2xl tracking-tight no-underline">Sewa<span class="text-gray-900">Mobil</span></a>
            <div class="flex items-center gap-4">   
                <a href="index.php?module=Homepage&action=index" class="text-gray-600 hover:text-blue-600 font-medium transition no-underline">Beranda</a>
                <a href="index.php?module=Auth&action=logout" class="text-red-500 hover:text-red-700 font-medium transition no-underline">Keluar</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 space-y-8 py-10">
        <header class="space-y-2 mb-8">
            <h1 class="text-4xl font-bold tracking-tight">Profil Saya</h1>
            <p class="text-gray-600">Kelola informasi data diri dan pantau aktivitas penyewaan Anda.</p>
        </header>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6">
                <p><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6">
                <p><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php 
            $status = $user['verification_status'] ?? 'unverified';
            $name = htmlspecialchars($user['name'] ?? '');
            $email = htmlspecialchars($user['email'] ?? '');
            $phone = htmlspecialchars($user['phone'] ?? '');
            $initial = strtoupper(substr($name, 0, 1));
        ?>

        <!-- Banners -->
        <?php if ($status === 'pending'): ?>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg flex items-center gap-3 w-full">
                <i class="bi bi-info-circle-fill text-yellow-500 text-lg flex-shrink-0"></i>
                <div class="text-sm font-medium m-0">Akun Anda sedang diverifikasi oleh admin, silakan tunggu. Pengecekan biasanya memakan waktu maksimal 1x24 jam.</div>
            </div>
        <?php elseif ($status === 'verified'): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg flex items-center gap-3 w-full">
                <i class="bi bi-check-circle-fill text-green-500 text-lg flex-shrink-0"></i>
                <div class="text-sm font-medium m-0">Akun Anda Telah Terverifikasi! Seluruh fitur sewa mobil kini aktif dan siap digunakan.</div>
            </div>
        <?php elseif ($status === 'rejected'): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg flex items-center gap-3 w-full">
                <i class="bi bi-x-circle-fill text-red-500 text-lg flex-shrink-0"></i>
                <div class="text-sm font-medium m-0">Verifikasi ditolak. Dokumen tidak valid atau kurang jelas. Mohon unggah ulang KTP dan SIM Anda.</div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: User Info -->
            <div class="col-span-1 space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-sm font-bold text-gray-700 mb-6 flex items-center gap-2">
                        <i class="bi bi-person-fill text-blue-500"></i> Informasi Akun
                    </h2>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold shrink-0">
                            <?= $initial ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg"><?= $name ?></h3>
                            <p class="text-gray-500 text-sm mb-1"><?= $email ?></p>
                            
                            <?php if ($status === 'unverified'): ?>
                                <span class="inline-block bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-md">⚪ BELUM VERIFIKASI</span>
                            <?php elseif ($status === 'pending'): ?>
                                <span class="inline-block bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded-md">⏳ PENDING</span>
                            <?php elseif ($status === 'verified'): ?>
                                <span class="inline-block bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-md">🟢 TERVERIFIKASI</span>
                            <?php elseif ($status === 'rejected'): ?>
                                <span class="inline-block bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-md">🔴 REJECTED</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Nomor HP</p>
                            <p class="font-medium text-gray-800"><?= $phone ?: '-' ?></p>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-gray-100 pt-6">
                        <button class="w-full text-center py-2.5 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition">Edit Profil</button>
                        <button class="w-full text-center py-2.5 rounded-lg border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition">Ubah Password</button>
                    </div>
                </div>

                <!-- Resubmit Form for Rejected Status -->
                <?php if ($status === 'rejected'): ?>
                <div class="bg-red-50 rounded-2xl p-6 shadow-sm border border-red-100">
                    <h2 class="text-sm font-bold text-red-600 mb-4 flex items-center gap-2">
                        <i class="bi bi-arrow-repeat"></i> Unggah Ulang Dokumen
                    </h2>
                    <form action="index.php?module=Profile&action=upload" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-gray-800">Foto KTP <span class="text-red-500">*</span></label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 hover:bg-gray-50 transition text-center relative cursor-pointer overflow-hidden group bg-white">
                                <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this, 'ktp_resubmit_preview', 'ktp_resubmit_text', 'ktp_resubmit_icon')">
                                <div class="relative z-0 pointer-events-none">
                                    <i id="ktp_resubmit_icon" class="bi bi-person-vcard text-2xl text-gray-400 mb-1 block"></i>
                                    <span id="ktp_resubmit_text" class="text-xs text-gray-600 font-medium block">Klik/seret KTP ke sini</span>
                                    <img id="ktp_resubmit_preview" class="hidden mt-2 mx-auto max-h-20 object-contain rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-gray-800">Foto SIM <span class="text-red-500">*</span></label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 hover:bg-gray-50 transition text-center relative cursor-pointer overflow-hidden group bg-white">
                                <input type="file" name="sim_file" accept=".jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this, 'sim_resubmit_preview', 'sim_resubmit_text', 'sim_resubmit_icon')">
                                <div class="relative z-0 pointer-events-none">
                                    <i id="sim_resubmit_icon" class="bi bi-card-heading text-2xl text-gray-400 mb-1 block"></i>
                                    <span id="sim_resubmit_text" class="text-xs text-gray-600 font-medium block">Klik/seret SIM ke sini</span>
                                    <img id="sim_resubmit_preview" class="hidden mt-2 mx-auto max-h-20 object-contain rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-700 transition mt-2">
                            Kirim Ulang
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Dynamic Content -->
            <div class="col-span-1 lg:col-span-2">
                
                <?php if ($status === 'unverified'): ?>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 h-full">
                        <h2 class="text-sm font-bold text-gray-700 mb-6 flex items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-blue-500"></i> Verifikasi Identitas
                        </h2>
                        
                        <div class="mb-8">
                            <p class="text-gray-600 mb-2">Silakan unggah foto KTP dan SIM Anda agar bisa menyewa mobil.</p>
                            <p class="text-sm text-gray-500">Format yang didukung: JPG, JPEG, PNG. Maksimal ukuran 2MB.</p>
                        </div>

                        <form action="index.php?module=Profile&action=upload" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-xl">
                            <div class="space-y-2">
                                <label class="block font-semibold text-gray-800">Unggah KTP <span class="text-red-500">*</span></label>
                                <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 hover:bg-gray-50 transition text-center cursor-pointer overflow-hidden group">
                                    <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this, 'ktp_preview', 'ktp_text', 'ktp_icon')">
                                    <div class="relative z-0 pointer-events-none">
                                        <i id="ktp_icon" class="bi bi-person-vcard text-3xl text-gray-400 mb-2 block"></i>
                                        <span id="ktp_text" class="text-gray-600 font-medium block">Klik atau seret file KTP ke sini</span>
                                        <img id="ktp_preview" class="hidden mt-3 mx-auto max-h-32 object-contain rounded-lg shadow-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block font-semibold text-gray-800">Unggah SIM <span class="text-red-500">*</span></label>
                                <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 hover:bg-gray-50 transition text-center cursor-pointer overflow-hidden group">
                                    <input type="file" name="sim_file" accept=".jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this, 'sim_preview', 'sim_text', 'sim_icon')">
                                    <div class="relative z-0 pointer-events-none">
                                        <i id="sim_icon" class="bi bi-card-heading text-3xl text-gray-400 mb-2 block"></i>
                                        <span id="sim_text" class="text-gray-600 font-medium block">Klik atau seret file SIM ke sini</span>
                                        <img id="sim_preview" class="hidden mt-3 mx-auto max-h-32 object-contain rounded-lg shadow-sm">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
                                <i class="bi bi-cloud-arrow-up-fill"></i> Unggah Dokumen
                            </button>
                        </form>
                    </div>
                
                <?php else: ?>
                    <!-- Pending, Verified, Rejected will show Dokumen and Riwayat Penyewaan -->
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 mb-8">
                        <h2 class="text-sm font-bold text-gray-700 mb-6 flex items-center gap-2">
                            <i class="bi bi-person-vcard text-blue-500"></i> Dokumen Identitas Saya
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">Foto KTP</p>
                                <div class="border rounded-xl p-2 bg-gray-50 aspect-video flex items-center justify-center overflow-hidden shadow-inner">
                                    <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['ktp_file'] ?? '') ?>" class="max-w-full max-h-full object-contain" alt="KTP">
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">Foto SIM</p>
                                <div class="border rounded-xl p-2 bg-gray-50 aspect-video flex items-center justify-center overflow-hidden shadow-inner">
                                    <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['sim_file'] ?? '') ?>" class="max-w-full max-h-full object-contain" alt="SIM">
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        $historyCardStyle = 'bg-white border border-gray-100';
                        if ($status === 'pending') {
                            $historyCardStyle = 'bg-amber-50/30 border-2 border-amber-400 transition-all duration-300';
                        } elseif ($status === 'rejected') {
                            $historyCardStyle = 'bg-red-50/30 border-2 border-red-500 transition-all duration-300';
                        }
                    ?>
                    <div class="<?= $historyCardStyle ?> rounded-2xl p-8 shadow-sm h-full">
                        <h2 class="text-sm font-bold text-gray-700 mb-6 flex items-center gap-2">
                            <i class="bi bi-clock-history text-blue-500"></i> Riwayat Penyewaan Mobil
                        </h2>
                        
                        <div class="text-center py-10">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-3xl">
                                <i class="bi bi-car-front"></i>
                            </div>
                            <p class="text-gray-800 font-medium mb-1">Belum ada transaksi penyewaan.</p>
                            <p class="text-gray-500 text-sm mb-8">Yuk, cari armada terbaikmu dan mulai perjalanan!</p>

                            <?php if ($status === 'verified'): ?>
                                <a href="index.php?module=Homepage&action=index" class="inline-block bg-blue-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-blue-700 transition no-underline">
                                    Cari & Sewa Mobil
                                </a>
                            <?php else: ?>
                                <div class="max-w-sm mx-auto bg-gray-50 rounded-xl p-4 border border-gray-200 text-left flex gap-3">
                                    <i class="bi bi-lock-fill text-gray-400 text-xl mt-0.5"></i>
                                    <div>
                                        <h4 class="font-bold text-gray-700">Fitur Sewa Terkunci</h4>
                                        <p class="text-sm text-gray-500">Akun Anda harus terverifikasi terlebih dahulu sebelum dapat menyewa mobil.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../../../include/footer.html'; ?>
    
    <script>
        function previewImage(input, previewId, textId, iconId) {
            const preview = document.getElementById(previewId);
            const text = document.getElementById(textId);
            const icon = document.getElementById(iconId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    text.textContent = 'File terpilih: ' + input.files[0].name;
                    text.classList.add('text-sm', 'text-blue-600', 'font-bold');
                    icon.classList.replace('text-gray-400', 'text-blue-500');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.classList.add('hidden');
                text.textContent = textId === 'ktp_text' ? 'Klik atau seret file KTP ke sini' : 'Klik atau seret file SIM ke sini';
                text.classList.remove('text-sm', 'text-blue-600', 'font-bold');
                icon.classList.replace('text-blue-500', 'text-gray-400');
            }
        }
    </script>
</body>
</html>
