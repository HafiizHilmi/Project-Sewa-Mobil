<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaMobil - Profil Saya</title>
    <script>
        // Inline script to force light mode as default on first load
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* =============================================
           BASE: Identik dengan halaman Admin
           bg-slate-900 = #0f172a  (page background)
           bg-slate-800 = #1e293b  (sidebar & cards)
           bg-slate-700 = #334155  (inputs, hover)
           border-slate-700 = #334155
        ============================================= */

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f8fafc; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-track { background: #0f172a; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }

        /* Sidebar nav links */
        .sidebar-link { transition: all 0.15s; border-left: 3px solid transparent; }
        .sidebar-link.active { border-left-color: #2563eb; background: rgba(37,99,235,.08); color: #2563eb !important; font-weight: 700; }
        .sidebar-link.active i { color: #2563eb !important; }
        .sidebar-link:not(.active):hover { background: rgba(100,116,139,.07); }
        .dark .sidebar-link { color: #94a3b8; }
        .dark .sidebar-link.active { border-left-color: #3b82f6; background: rgba(37,99,235,.14); color: #60a5fa !important; }
        .dark .sidebar-link.active i { color: #60a5fa !important; }
        .dark .sidebar-link:not(.active):hover { background: rgba(148,163,184,.07); color: #f1f5f9; }

        /* Tab panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; animation: fadeSlide .22s ease; }
        @keyframes fadeSlide { from { opacity:0; transform:translateY(8px) } to { opacity:1; transform:translateY(0) } }

        /* Avatar */
        .avatar-ring { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }

        /* Booking card */
        .booking-card { transition: box-shadow .18s, transform .18s; }
        .booking-card:hover { box-shadow: 0 8px 24px -4px rgba(0,0,0,.08); transform: translateY(-1px); }
        .dark .booking-card { background-color: #1e293b !important; border-color: #334155 !important; }
        .dark .booking-card:hover { box-shadow: 0 8px 24px -4px rgba(0,0,0,.4); }

        /* Input bg-slate-50 override for dark mode */
        html.dark .bg-slate-50 { background-color: #0f172a !important; }

        /* Mobile sidebar */
        @media (max-width:1023px) {
            #sidebar { transform:translateX(-100%); transition:transform .3s ease; position:fixed; top:0; left:0; height:100%; z-index:50; width:280px; }
            #sidebar.open { transform:translateX(0); }
            #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:40; }
            #sidebar-overlay.show { display:block; }
        }

        /* =============================================
           DARK MODE — CSS langsung, bukan Tailwind JIT
           Sama persis dengan warna dark mode admin
        ============================================= */

        /* Page background */
        html.dark body { background-color: #0f172a !important; color: #f1f5f9 !important; }

        /* Navbar */
        html.dark nav { background-color: #1e293b !important; border-color: #334155 !important; }

        /* Sidebar */
        html.dark aside#sidebar { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark aside#sidebar > div { border-color: #334155 !important; }

        /* All cards/panels (bg-white) */
        html.dark .bg-white { background-color: #1e293b !important; }

        /* Stat mini cards in sidebar */
        html.dark .sidebar-stat-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important; border-color: #334155 !important; }

        /* Card borders */
        html.dark .border-slate-100,
        html.dark .border-slate-200 { border-color: #334155 !important; }

        /* Dividers */
        html.dark .divide-y > * + * { border-color: #334155 !important; }
        html.dark .border-b { border-color: #334155 !important; }

        /* Text colors */
        html.dark .text-slate-800,
        html.dark .text-slate-900 { color: #f1f5f9 !important; }
        html.dark .text-slate-700 { color: #cbd5e1 !important; }
        html.dark .text-slate-600 { color: #94a3b8 !important; }
        html.dark .text-slate-500 { color: #94a3b8 !important; }
        html.dark .text-slate-400 { color: #64748b !important; }

        /* Inputs */
        html.dark input[type="text"],
        html.dark input[type="email"],
        html.dark input[type="password"] {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html.dark input::placeholder { color: #475569 !important; }

        /* Icon colors in dark */
        html.dark .text-slate-400 i { color: #64748b !important; }

        /* Stat icon backgrounds */
        html.dark .bg-blue-100 { background-color: rgba(37,99,235,.2) !important; }
        html.dark .bg-emerald-100 { background-color: rgba(16,185,129,.2) !important; }
        html.dark .bg-violet-100 { background-color: rgba(124,58,237,.2) !important; }

        /* Sidebar stat mini cards */
        html.dark .bg-blue-50 { background-color: rgba(37,99,235,.15) !important; border-color: rgba(37,99,235,.2) !important; }

        /* Riwayat image strip background */
        html.dark .bg-slate-100 { background-color: #334155 !important; }

        /* Plate number badge */
        html.dark .bg-slate-100.border-slate-300 { background-color: #1e293b !important; border-color: #475569 !important; color: #cbd5e1 !important; }

        /* Tips password box */
        html.dark .bg-blue-50.border-blue-100 { background-color: rgba(37,99,235,.12) !important; border-color: rgba(59,130,246,.2) !important; color: #93c5fd !important; }

        /* Lock card (dokumen identitas) */
        html.dark .dm-lock { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .dm-lock-icon { background-color: rgba(37,99,235,.2) !important; }
        html.dark .dm-lock p.text-sm { color: #f1f5f9 !important; }
        html.dark .dm-lock p.text-slate-500 { color: #94a3b8 !important; }

        /* Unlocked docs card */
        html.dark .dm-unlocked { background-color: #1e293b !important; border-color: #334155 !important; }
        html.dark .dm-doc-img { background-color: #0f172a !important; border-color: #334155 !important; }

        /* Hover on items in riwayat penyewaan preview list */
        html.dark .hover\:bg-gray-50\/60:hover { background-color: rgba(148,163,184,.06) !important; }

        /* Flash banners */
        html.dark .bg-yellow-50 { background-color: rgba(234,179,8,.08) !important; border-color: rgba(234,179,8,.2) !important; }
        html.dark .bg-green-50  { background-color: rgba(16,185,129,.08) !important; border-color: rgba(16,185,129,.2) !important; }
        html.dark .bg-red-50    { background-color: rgba(239,68,68,.08) !important; border-color: rgba(239,68,68,.2) !important; }
        html.dark .text-yellow-800 { color: #fde68a !important; }
        html.dark .text-green-800  { color: #6ee7b7 !important; }
        html.dark .text-red-800    { color: #fca5a5 !important; }

        /* Upload zones */
        html.dark .border-dashed { border-color: #475569 !important; }
        html.dark .border-dashed:hover { border-color: #3b82f6 !important; background-color: rgba(37,99,235,.06) !important; }

        /* Rejected step-by-step workflow */
        #rejected-upload-form { animation: fadeSlide .28s ease; }
        html.dark #rejected-info-card { background-color: #1e293b !important; border-color: rgba(239,68,68,.25) !important; }
        html.dark #rejected-upload-form .bg-white { background-color: #1e293b !important; }

        /* Rejected card */
        html.dark .bg-red-50.rounded-2xl { background-color: rgba(239,68,68,.08) !important; border-color: rgba(239,68,68,.2) !important; }
        html.dark .bg-red-100 { background-color: rgba(239,68,68,.2) !important; }
    </style>

</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen dark:bg-slate-900 dark:text-slate-100 transition-colors duration-200">

<?php
    $status  = $user['verification_status'] ?? 'unverified';
    $name    = htmlspecialchars($user['name'] ?? '');
    $email   = htmlspecialchars($user['email'] ?? '');
    $phone   = htmlspecialchars($user['phone'] ?? '');
    $initial = strtoupper(substr($name, 0, 1));

    // Booking stats
    $totalBookings    = count($bookings);
    $activeBookings   = count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed'));
    $completedBookings= count(array_filter($bookings, fn($b) => $b['status'] === 'completed'));
?>

<nav class="bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 sticky top-0 z-30 shadow-sm transition-colors w-full">
    <div class="w-full px-4 sm:px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 transition">
                <i class="bi bi-list text-xl"></i>
            </button>
            <a href="index.php?module=Homepage&action=index" class="font-extrabold text-xl tracking-tight no-underline">
                <span class="text-blue-600">Sewa</span><span class="text-slate-900 dark:text-white">Mobil</span>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <!-- Theme Toggle Button -->
            <button id="theme-toggle" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-slate-700 dark:hover:bg-slate-700 transition-all duration-200 mr-1" title="Toggle Theme">
                <i id="theme-toggle-icon" class="bi bi-moon-fill text-lg"></i>
            </button>
            <a href="index.php?module=Homepage&action=index" class="hidden sm:inline-flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-all duration-200 no-underline px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">
                <i class="bi bi-house-fill"></i> Beranda
            </a>
            <a href="index.php?module=Auth&action=logout" class="inline-flex items-center gap-1.5 text-sm text-red-500 hover:text-red-700 font-medium transition-all duration-200 no-underline px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10">
                <i class="bi bi-box-arrow-right"></i> <span class="hidden sm:inline">Keluar</span>
            </a>
        </div>
    </div>
</nav>

<div id="sidebar-overlay"></div>

<div class="w-full flex min-h-[calc(100vh-57px)]">

    <aside id="sidebar" class="w-72 lg:w-64 xl:w-72 bg-white dark:bg-slate-800 border-r border-slate-100 dark:border-slate-700 flex flex-col lg:fixed lg:left-0 lg:top-[57px] lg:bottom-0 lg:z-20 overflow-y-auto custom-scroll transition-colors">
        
        <div class="p-5 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="avatar-ring w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold flex-shrink-0 shadow-md">
                    <?= $initial ?>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-slate-800 dark:text-white text-sm truncate"><?= $name ?></p>
                    <p class="text-slate-500 dark:text-slate-400 text-xs truncate"><?= $email ?></p>
                    <?php if ($status === 'verified'): ?>
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md mt-0.5">
                            <i class="bi bi-patch-check-fill text-[9px]"></i> Terverifikasi
                        </span>
                    <?php elseif ($status === 'pending'): ?>
                        <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md mt-0.5">
                            <i class="bi bi-hourglass-split text-[9px]"></i> Pending
                        </span>
                    <?php elseif ($status === 'rejected'): ?>
                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md mt-0.5">
                            <i class="bi bi-x-circle-fill text-[9px]"></i> Ditolak
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-bold px-1.5 py-0.5 rounded-md mt-0.5">
                            <i class="bi bi-circle text-[9px]"></i> Belum Verifikasi
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-2.5 text-center border border-blue-100 dark:border-blue-800/30">
                    <p class="text-xl font-extrabold text-blue-600 dark:text-blue-400"><?= $totalBookings ?></p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Total Sewa</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-2.5 text-center border border-blue-100 dark:border-blue-800/30">
                    <p class="text-xl font-extrabold text-emerald-600 dark:text-emerald-450"><?= $activeBookings ?></p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Sedang Berjalan</p>
                </div>
            </div>
        </div>

        <nav class="p-3 flex-1 space-y-0.5">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 pt-1 pb-2">Menu Utama</p>

            <a href="#" class="sidebar-link active flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm no-underline" data-tab="dashboard">
                <i class="bi bi-grid-1x2-fill text-blue-400 text-base w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 no-underline" data-tab="riwayat">
                <i class="bi bi-clock-history text-slate-400 dark:text-slate-500 text-base w-5 text-center"></i>
                <span>Riwayat Penyewaan</span>
                <?php if ($totalBookings > 0): ?>
                    <span class="ml-auto bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full dark:bg-blue-900/40 dark:text-blue-300"><?= $totalBookings ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 no-underline" data-tab="dokumen">
                <i class="bi bi-person-vcard text-slate-400 dark:text-slate-500 text-base w-5 text-center"></i>
                <span>Dokumen Identitas</span>
                <?php if ($status === 'pending'): ?>
                    <span class="ml-auto w-2 h-2 bg-yellow-400 rounded-full"></span>
                <?php elseif ($status === 'rejected'): ?>
                    <span class="ml-auto w-2 h-2 bg-red-500 rounded-full"></span>
                <?php endif; ?>
            </a>

            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 pt-4 pb-2">Pengaturan Akun</p>

            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 no-underline" data-tab="editprofil">
                <i class="bi bi-pencil-square text-slate-400 dark:text-slate-500 text-base w-5 text-center"></i>
                <span>Edit Profil</span>
            </a>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 no-underline" data-tab="ubahpassword">
                <i class="bi bi-shield-lock text-slate-400 dark:text-slate-500 text-base w-5 text-center"></i>
                <span>Ubah Password</span>
            </a>

            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 pt-4 pb-2">Dukungan</p>
            <a href="#" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 dark:text-slate-300 no-underline" data-tab="bantuan">
                <i class="bi bi-question-circle text-slate-400 dark:text-slate-500 text-base w-5 text-center"></i>
                <span>Pusat Bantuan</span>
            </a>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700 mt-2">
                <a href="index.php?module=Auth&action=logout" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 no-underline transition-all duration-200 font-medium">
                    <i class="bi bi-box-arrow-right text-base w-5 text-center"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 min-w-0 p-4 sm:p-6 xl:p-8 space-y-6 lg:ml-64 xl:ml-72">

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900 text-green-800 dark:text-green-300 p-4 rounded-xl flex items-center gap-3 transition-colors">
                <i class="bi bi-check-circle-fill text-green-500 flex-shrink-0"></i>
                <p class="text-sm font-medium m-0"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 text-red-800 dark:text-red-300 p-4 rounded-xl flex items-center gap-3 transition-colors">
                <i class="bi bi-x-circle-fill text-red-500 flex-shrink-0"></i>
                <p class="text-sm font-medium m-0"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if ($status === 'pending'): ?>
            <div class="bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-200 dark:border-yellow-900 text-yellow-800 dark:text-yellow-300 p-4 rounded-xl flex items-center gap-3 transition-colors">
                <i class="bi bi-hourglass-split text-yellow-500 text-lg flex-shrink-0"></i>
                <p class="text-sm font-medium m-0">Akun Anda sedang diverifikasi oleh admin. Pengecekan biasanya memakan waktu maksimal 1x24 jam.</p>
            </div>
        <?php elseif ($status === 'verified'): ?>
            <div class="bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900 text-green-800 dark:text-green-300 p-4 rounded-xl flex items-center gap-3 transition-colors">
                <i class="bi bi-patch-check-fill text-green-500 text-lg flex-shrink-0"></i>
                <p class="text-sm font-medium m-0">Akun Anda Telah Terverifikasi! Seluruh fitur sewa mobil kini aktif dan siap digunakan.</p>
            </div>
        <?php elseif ($status === 'rejected'): ?>
            <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900 text-red-800 dark:text-red-300 p-4 rounded-xl flex items-center gap-3 transition-colors">
                <i class="bi bi-x-octagon-fill text-red-500 text-lg flex-shrink-0"></i>
                <p class="text-sm font-medium m-0">Verifikasi ditolak. Dokumen tidak valid atau kurang jelas. Mohon unggah ulang KTP dan SIM Anda di menu <strong>Dokumen Identitas</strong>.</p>
            </div>
        <?php endif; ?>

        <div class="tab-panel active" id="tab-dashboard">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dashboard Aktivitas</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Ringkasan aktivitas dan riwayat penyewaan Anda.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors">
                    <div class="w-11 h-11 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 text-xl flex-shrink-0">
                        <i class="bi bi-car-front-fill"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $totalBookings ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Sewa</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors">
                    <div class="w-11 h-11 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl flex-shrink-0">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $activeBookings ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sedang Berjalan</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center gap-4 col-span-2 sm:col-span-1 transition-colors">
                    <div class="w-11 h-11 bg-violet-100 dark:bg-violet-900/30 rounded-xl flex items-center justify-center text-violet-600 dark:text-violet-400 text-xl flex-shrink-0">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $completedBookings ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Selesai</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-50 dark:border-slate-700 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="bi bi-clock-history text-blue-500"></i> Riwayat Penyewaan Terkini
                    </h2>
                    <?php if (count($bookings) > 3): ?>
                        <a href="#" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold no-underline" data-tab-link="riwayat">Lihat semua →</a>
                    <?php endif; ?>
                </div>

                <?php $recentBookings = array_slice($bookings, 0, 3); ?>
                <?php if (!empty($recentBookings)): ?>
                    <div class="divide-y divide-gray-50">
                        <?php foreach ($recentBookings as $booking): ?>
                            <?php
                                $carName  = htmlspecialchars(trim($booking['make'] . ' ' . $booking['model']));
                                $start    = date('d M Y', strtotime($booking['start_date']));
                                $end      = date('d M Y', strtotime($booking['end_date']));
                                $total    = number_format($booking['total_price'], 0, ',', '.');
                                $statusVal= $booking['status'];
                                $image    = htmlspecialchars($booking['image'] ?: 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop');

                                $statusLabel = 'Menunggu';
                                $statusClass = 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                                $dotClass    = 'bg-yellow-400';
                                if ($statusVal === 'confirmed') {
                                    $statusLabel = 'Aktif';
                                    $statusClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                    $dotClass    = 'bg-blue-500';
                                } elseif ($statusVal === 'completed') {
                                    $statusLabel = 'Selesai';
                                    $statusClass = 'bg-green-50 text-green-700 border border-green-200';
                                    $dotClass    = 'bg-green-500';
                                } elseif ($statusVal === 'cancelled') {
                                    $statusLabel = 'Dibatalkan';
                                    $statusClass = 'bg-red-50 text-red-700 border border-red-200';
                                    $dotClass    = 'bg-red-400';
                                }
                            ?>
                            <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50/60 transition">
                                <div class="w-16 h-11 rounded-lg bg-slate-100 dark:bg-slate-700 flex-shrink-0 overflow-hidden">
                                    <img src="<?= $image ?>" alt="<?= $carName ?>" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-800 dark:text-white text-sm truncate"><?= $carName ?></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><i class="bi bi-calendar3 text-blue-400 mr-1"></i><?= $start ?> – <?= $end ?></p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="inline-flex items-center gap-1 text-[10.5px] font-bold px-2 py-1 rounded-full <?= $statusClass ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span>
                                        <?= $statusLabel ?>
                                    </span>
                                    <p class="text-sm font-extrabold text-blue-600 mt-1">Rp<?= $total ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="py-12 text-center">
                        <i class="bi bi-car-front text-slate-300 dark:text-slate-600 text-5xl block mb-3"></i>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada riwayat penyewaan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-panel" id="tab-riwayat">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Riwayat Penyewaan</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Semua transaksi penyewaan mobil Anda.</p>
            </div>

            <?php if (!empty($bookings)): ?>
                <div class="space-y-4">
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                            $carName  = htmlspecialchars(trim($booking['make'] . ' ' . $booking['model']));
                            $start    = date('d M Y', strtotime($booking['start_date']));
                            $end      = date('d M Y', strtotime($booking['end_date']));
                            $total    = number_format($booking['total_price'], 0, ',', '.');
                            $statusVal= $booking['status'];
                            $image    = htmlspecialchars($booking['image'] ?: 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop');

                            $statusLabel = 'Menunggu Persetujuan';
                            $statusClass = 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                            $dotClass    = 'bg-yellow-400';
                            $sisaHariText= '';
                            
                            if ($statusVal === 'confirmed') {
                                $statusLabel = 'Disetujui & Aktif';
                                $statusClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                $dotClass    = 'bg-blue-500';
                                
                                $now = new DateTime();
                                $endDateObj = new DateTime($booking['end_date']);
                                if ($now < $endDateObj) {
                                    $diff = $now->diff($endDateObj);
                                    $sisaHari = $diff->days;
                                    $sisaHariText = "Sisa Waktu: " . ($sisaHari == 0 ? "Hari ini" : "$sisaHari hari lagi");
                                } else {
                                    $sisaHariText = "Masa sewa habis";
                                }
                            } elseif ($statusVal === 'completed') {
                                $statusLabel = 'Selesai';
                                $statusClass = 'bg-green-50 text-green-700 border border-green-200';
                                $dotClass    = 'bg-green-500';
                            } elseif ($statusVal === 'cancelled') {
                                $statusLabel = 'Dibatalkan';
                                $statusClass = 'bg-red-50 text-red-700 border border-red-200';
                                $dotClass    = 'bg-red-400';
                            }
                        ?>
                        <div class="booking-card bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                            <div class="flex flex-col sm:flex-row gap-0">
                                <div class="sm:w-36 h-32 sm:h-auto bg-slate-100 dark:bg-slate-700 flex-shrink-0 overflow-hidden">
                                    <img src="<?= $image ?>" alt="<?= $carName ?>" class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1 p-4 sm:p-5 flex flex-col sm:flex-row gap-4">
                                    <div class="flex-1 space-y-2.5">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div>
                                                <h3 class="font-extrabold text-slate-800 dark:text-white text-base leading-tight"><?= $carName ?></h3>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                    <?= htmlspecialchars($booking['category'] ?? '') ?> · <?= htmlspecialchars($booking['fuel_type'] ?? '') ?>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center gap-1 text-[10.5px] font-bold px-2.5 py-1 rounded-full <?= $statusClass ?>">
                                                    <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span>
                                                    <?= $statusLabel ?>
                                                </span>
                                                <?php if (!empty($sisaHariText)): ?>
                                                    <p class="text-[10px] font-bold text-blue-600 mt-1 animate-pulse"><i class="bi bi-clock-history mr-0.5"></i><?= $sisaHariText ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 xs:grid-cols-2 gap-x-6 gap-y-3 text-xs border-t border-slate-100 dark:border-slate-700 pt-2 mt-2">
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-0.5">ID Transaksi</span>
                                                <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                    <i class="bi bi-receipt text-indigo-500 mr-1"></i>#TRX-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-0.5">Tanggal Transaksi</span>
                                                <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                    <i class="bi bi-calendar-check text-green-500 mr-1"></i><?= date('d M Y H:i', strtotime($booking['created_at'])) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Durasi Sewa</span>
                                                <span class="font-semibold text-slate-700 dark:text-slate-200">
                                                    <i class="bi bi-calendar-range text-blue-500 mr-1"></i><?= $start ?> – <?= $end ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Lokasi Pengambilan</span>
                                                <span class="font-semibold text-slate-700 dark:text-slate-200 truncate block" title="<?= htmlspecialchars($booking['pickup_location']) ?>">
                                                    <i class="bi bi-geo-alt-fill text-red-500 mr-1"></i><?= htmlspecialchars($booking['pickup_location']) ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($booking['return_location'])): ?>
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Lokasi Pengembalian</span>
                                                <span class="font-semibold text-slate-700 dark:text-slate-200 truncate block" title="<?= htmlspecialchars($booking['return_location']) ?>">
                                                    <i class="bi bi-arrow-left-right text-blue-500 mr-1"></i><?= htmlspecialchars($booking['return_location']) ?>
                                                </span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($booking['assigned_plate'])): ?>
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Plat Nomor</span>
                                                <span class="inline-block bg-slate-100 border border-slate-300 px-2 py-0.5 rounded text-[10.5px] font-black text-slate-800 tracking-widest font-mono uppercase">
                                                    <?= htmlspecialchars($booking['assigned_plate']) ?>
                                                </span>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($statusVal === 'completed' && ($booking['additional_cost'] > 0 || !empty($booking['damage_description']))): ?>
                                            <div class="bg-red-50 border border-red-100 rounded-xl p-3 space-y-1.5 mt-1">
                                                <p class="text-[10.5px] font-bold text-red-700 uppercase tracking-wide flex items-center gap-1.5">
                                                    <i class="bi bi-exclamation-octagon-fill"></i> Detail Kerusakan / Biaya Tambahan
                                                </p>
                                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs">
                                                    <?php if ($booking['additional_cost'] > 0): ?>
                                                        <span class="text-slate-600 dark:text-slate-300">Denda: <strong class="text-red-600">Rp<?= number_format($booking['additional_cost'], 0, ',', '.') ?></strong></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($booking['damage_description'])): ?>
                                                        <span class="text-slate-600 dark:text-slate-300">Keterangan: <em class="text-slate-700 dark:text-slate-200"><?= htmlspecialchars($booking['damage_description']) ?></em></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($booking['damage_image'])): ?>
                                                        <a href="assets/images/damages/<?= htmlspecialchars($booking['damage_image']) ?>" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold no-underline">
                                                            <i class="bi bi-image"></i> Lihat Bukti Foto
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-start border-t sm:border-t-0 sm:border-l border-slate-100 dark:border-slate-700 pt-3 sm:pt-0 sm:pl-5 sm:min-w-[120px]">
                                        <div class="text-left sm:text-right">
                                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Harga</span>
                                            <span class="text-lg font-extrabold text-blue-600 block">Rp<?= $total ?></span>
                                            <?php if ($booking['additional_cost'] > 0): ?>
                                                <span class="text-[10px] text-red-500 font-medium block">
                                                    +Rp<?= number_format($booking['additional_cost'], 0, ',', '.') ?> denda
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm py-16 text-center">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400 dark:text-slate-500 text-3xl">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <p class="text-slate-700 dark:text-slate-200 font-bold mb-1">Belum ada transaksi penyewaan.</p>
                    <p class="text-slate-400 dark:text-slate-500 text-sm mb-6">Yuk, temukan armada terbaikmu dan mulai perjalanan!</p>
                    <?php if ($status === 'verified'): ?>
                        <a href="index.php?module=Homepage&action=index" class="inline-block bg-blue-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-blue-700 transition-all duration-200 no-underline text-sm">
                            <i class="bi bi-search mr-1.5"></i>Cari & Sewa Mobil
                        </a>
                    <?php else: ?>
                        <div class="max-w-xs mx-auto bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-200 dark:border-slate-600 text-left flex gap-3">
                            <i class="bi bi-lock-fill text-slate-400 dark:text-slate-500 text-xl mt-0.5 flex-shrink-0"></i>
                            <div>
                                <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Fitur Sewa Terkunci</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Verifikasi akun Anda terlebih dahulu sebelum menyewa mobil.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-panel" id="tab-dokumen">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Dokumen Identitas</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">KTP dan SIM untuk verifikasi identitas Anda.</p>
            </div>

            <?php if ($status === 'unverified'): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8 max-w-xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                            <i class="bi bi-shield-lock text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-800 dark:text-white">Verifikasi Identitas</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Unggah KTP & SIM untuk mulai menyewa</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Format: JPG, JPEG, PNG. Maks. 2MB per file.</p>

                    <form action="index.php?module=Profile&action=upload" method="POST" enctype="multipart/form-data" class="space-y-5">
                        <div class="space-y-2">
                            <label class="block font-semibold text-sm text-slate-800 dark:text-white">Foto KTP <span class="text-red-500">*</span></label>
                            <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-500 rounded-xl p-6 hover:border-blue-400 hover:bg-blue-50/30 transition-all duration-200 text-center cursor-pointer overflow-hidden group">
                                <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this, 'ktp_preview', 'ktp_text', 'ktp_icon')">
                                <div class="relative z-0 pointer-events-none">
                                    <i id="ktp_icon" class="bi bi-person-vcard text-3xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                    <span id="ktp_text" class="text-slate-500 dark:text-slate-400 text-sm font-medium block">Klik atau seret file KTP ke sini</span>
                                    <img id="ktp_preview" class="hidden mt-3 mx-auto max-h-32 object-contain rounded-lg shadow">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block font-semibold text-sm text-slate-800 dark:text-white">Foto SIM <span class="text-red-500">*</span></label>
                            <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-500 rounded-xl p-6 hover:border-blue-400 hover:bg-blue-50/30 transition-all duration-200 text-center cursor-pointer overflow-hidden group">
                                <input type="file" name="sim_file" accept=".jpg,.jpeg,.png" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this, 'sim_preview', 'sim_text', 'sim_icon')">
                                <div class="relative z-0 pointer-events-none">
                                    <i id="sim_icon" class="bi bi-card-heading text-3xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                    <span id="sim_text" class="text-slate-500 dark:text-slate-400 text-sm font-medium block">Klik atau seret file SIM ke sini</span>
                                    <img id="sim_preview" class="hidden mt-3 mx-auto max-h-32 object-contain rounded-lg shadow">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Unggah Dokumen
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <?php if (!$docsUnlocked && $status !== 'rejected'): ?>
                    <div class="dm-lock bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8 mb-6 text-center max-w-md mx-auto">
                        <div class="dm-lock-icon w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 mx-auto mb-4">
                            <i class="bi bi-lock-fill text-3xl"></i>
                        </div>
                        <h2 class="font-bold text-slate-800 dark:text-white text-lg mb-2">Keamanan Dokumen</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Silakan isi password untuk mengakses dokumen identitas Anda lebih lanjut.</p>
                        
                        <form action="index.php?module=Profile&action=verifyDocsPassword" method="POST" class="space-y-4">
                            <div class="relative">
                                <input type="password" name="password" id="doc_password" required
                                       class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50 text-center"
                                       placeholder="Masukkan Password Akun Anda">
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:text-slate-300" onclick="togglePwd('doc_password', this)">
                                    <i class="bi bi-eye text-base"></i>
                                </button>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-all duration-200 text-sm">
                                <i class="bi bi-unlock-fill mr-1"></i> Buka Dokumen
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <?php if ($status !== 'rejected'): ?>
                        <div class="dm-unlocked bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8 mb-6 relative">
                            <!-- Indikator Unlocked -->
                            <div class="absolute top-4 right-4 text-green-500 flex items-center gap-1 text-xs font-bold bg-green-50 px-2 py-1 rounded-md border border-green-100">
                                <i class="bi bi-unlock-fill"></i> Terbuka
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                                        <i class="bi bi-person-vcard-fill text-blue-500"></i> Foto KTP Anda
                                    </p>
                                    <div class="dm-doc-img border border-slate-200 dark:border-slate-600 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50 aspect-video flex items-center justify-center overflow-hidden shadow-inner">
                                        <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['ktp_file'] ?? '') ?>" class="max-w-full max-h-full object-contain" alt="KTP">
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                                        <i class="bi bi-card-heading text-blue-500"></i> Foto SIM Anda
                                    </p>
                                    <div class="dm-doc-img border border-slate-200 dark:border-slate-600 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50 aspect-video flex items-center justify-center overflow-hidden shadow-inner">
                                        <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['sim_file'] ?? '') ?>" class="max-w-full max-h-full object-contain" alt="SIM">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($status === 'rejected'): ?>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            
                            <!-- KIRI: Foto Dokumen Lama -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8">
                                <h3 class="font-bold text-slate-800 dark:text-white text-base mb-4 flex items-center gap-2">
                                    <i class="bi bi-file-earmark-image text-slate-400"></i> Dokumen yang Ditolak
                                </h3>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                                            <i class="bi bi-person-vcard-fill text-blue-500"></i> Foto KTP Lama
                                        </p>
                                        <div class="dm-doc-img border border-slate-200 dark:border-slate-600 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50 aspect-video flex items-center justify-center overflow-hidden shadow-inner">
                                            <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['ktp_file'] ?? '') ?>" class="max-w-full max-h-full object-contain" alt="KTP Lama">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                                            <i class="bi bi-card-heading text-blue-500"></i> Foto SIM Lama
                                        </p>
                                        <div class="dm-doc-img border border-slate-200 dark:border-slate-600 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50 aspect-video flex items-center justify-center overflow-hidden shadow-inner">
                                            <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['sim_file'] ?? '') ?>" class="max-w-full max-h-full object-contain" alt="SIM Lama">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KANAN: Workflow Rejection -->
                            <div>
                                <!-- ===== STEP 1: Info Penolakan ===== -->
                                <div id="rejected-info-card" class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200 dark:border-red-900/60 shadow-sm overflow-hidden">
                        <!-- Header merah -->
                        <div class="bg-red-600 px-6 py-5 flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-x-circle-fill text-white text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="font-extrabold text-white text-base leading-tight">Verifikasi Dokumen Ditolak</h2>
                                <p class="text-red-200 text-xs mt-0.5">Dokumen Anda memerlukan perbaikan sebelum diproses ulang</p>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8 space-y-5">
                            <!-- Alasan Penolakan -->
                            <div class="bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900/40 rounded-xl p-4 flex gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 text-base mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-sm font-bold text-red-700 dark:text-red-400 mb-1">Alasan Penolakan</p>
                                    <p class="text-sm text-red-600 dark:text-red-300 leading-relaxed">
                                        <strong>Alasan Penolakan:</strong> <?php echo htmlspecialchars($user['reject_reason'] ?? 'Tidak ada alasan spesifik.'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Tips Perbaikan -->
                            <div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                                    <i class="bi bi-lightbulb-fill text-amber-500"></i> Tips agar dokumen diterima
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div class="flex items-start gap-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700">
                                        <i class="bi bi-camera-fill text-blue-500 text-base mt-0.5 flex-shrink-0"></i>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">Foto dengan pencahayaan terang, hindari bayangan di atas dokumen</p>
                                    </div>
                                    <div class="flex items-start gap-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700">
                                        <i class="bi bi-aspect-ratio-fill text-blue-500 text-base mt-0.5 flex-shrink-0"></i>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">Pastikan seluruh bagian dokumen terlihat penuh, tidak terpotong</p>
                                    </div>
                                    <div class="flex items-start gap-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700">
                                        <i class="bi bi-eye-fill text-blue-500 text-base mt-0.5 flex-shrink-0"></i>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">Teks dan foto wajah pada dokumen harus terbaca dengan jelas</p>
                                    </div>
                                    <div class="flex items-start gap-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700">
                                        <i class="bi bi-calendar-check-fill text-blue-500 text-base mt-0.5 flex-shrink-0"></i>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">Pastikan KTP dan SIM yang diunggah masih berlaku dan tidak kadaluarsa</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Jaminan Keamanan -->
                            <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 rounded-xl p-4 flex gap-3">
                                <i class="bi bi-shield-lock-fill text-blue-500 text-base mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-sm font-bold text-blue-700 dark:text-blue-400 mb-0.5">Keamanan Data Terjamin</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-300 leading-relaxed">Dokumen identitas Anda disimpan terenkripsi dan hanya digunakan untuk keperluan verifikasi. Data tidak akan dibagikan kepada pihak ketiga mana pun.</p>
                                </div>
                            </div>

                            <!-- Tombol CTA -->
                            <button
                                id="btn-show-upload-form"
                                onclick="showRejectedUploadForm()"
                                class="w-full bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold py-3.5 rounded-xl transition-all duration-200 flex items-center justify-center gap-2.5 text-sm shadow-sm hover:shadow-md group">
                                <i class="bi bi-arrow-repeat text-base group-hover:rotate-180 transition-transform duration-300"></i>
                                Perbaiki &amp; Unggah Ulang Dokumen
                                <i class="bi bi-chevron-right text-xs opacity-70"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ===== STEP 2: Form Unggah Ulang (tersembunyi, muncul saat tombol diklik) ===== -->
                    <div id="rejected-upload-form" class="hidden">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden"
                             style="animation: none;">

                            <!-- Header form -->
                            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-6 py-5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                        <i class="bi bi-cloud-arrow-up-fill text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h2 class="font-extrabold text-white text-sm leading-tight">Unggah Ulang Dokumen</h2>
                                        <p class="text-slate-300 text-xs mt-0.5">Format: JPG, JPEG, PNG · Maks. 2MB per file</p>
                                    </div>
                                </div>
                                <!-- Tombol kembali ke info -->
                                <button type="button" onclick="hideRejectedUploadForm()"
                                    class="flex items-center gap-1.5 text-xs text-slate-300 hover:text-white bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg transition-all duration-200 font-medium">
                                    <i class="bi bi-arrow-left text-xs"></i> Kembali
                                </button>
                            </div>

                            <div class="p-6 sm:p-8">
                                <!-- Step indicator -->
                                <div class="flex items-center gap-2 mb-6">
                                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-[10px] font-bold">1</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 line-through">Lihat Informasi Penolakan</span>
                                    <i class="bi bi-chevron-right text-slate-300 dark:text-slate-600 text-[10px]"></i>
                                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-bold">2</span>
                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-bold">Unggah Dokumen Baru</span>
                                </div>

                                <form action="index.php?module=Profile&action=upload" method="POST" enctype="multipart/form-data" class="space-y-5">
                                    <!-- Upload KTP -->
                                    <div class="space-y-2">
                                        <label class="block font-semibold text-sm text-slate-800 dark:text-white flex items-center gap-1.5">
                                            <i class="bi bi-person-vcard-fill text-blue-500"></i>
                                            Foto KTP Baru <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-500 rounded-xl p-6 hover:border-blue-400 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all duration-200 text-center cursor-pointer overflow-hidden group">
                                            <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png" required
                                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                   onchange="previewImage(this, 'ktp_resubmit_preview', 'ktp_resubmit_text', 'ktp_resubmit_icon')">
                                            <div class="relative z-0 pointer-events-none">
                                                <i id="ktp_resubmit_icon" class="bi bi-person-vcard text-3xl text-slate-300 dark:text-slate-600 mb-2 block group-hover:text-blue-400 transition-colors duration-200"></i>
                                                <span id="ktp_resubmit_text" class="text-slate-500 dark:text-slate-400 text-sm font-medium block">Klik atau seret file KTP ke sini</span>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">JPG, JPEG, PNG — maks. 2MB</p>
                                                <img id="ktp_resubmit_preview" class="hidden mt-3 mx-auto max-h-32 object-contain rounded-lg shadow">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Upload SIM -->
                                    <div class="space-y-2">
                                        <label class="block font-semibold text-sm text-slate-800 dark:text-white flex items-center gap-1.5">
                                            <i class="bi bi-card-heading text-blue-500"></i>
                                            Foto SIM Baru <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-500 rounded-xl p-6 hover:border-blue-400 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all duration-200 text-center cursor-pointer overflow-hidden group">
                                            <input type="file" name="sim_file" accept=".jpg,.jpeg,.png" required
                                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                   onchange="previewImage(this, 'sim_resubmit_preview', 'sim_resubmit_text', 'sim_resubmit_icon')">
                                            <div class="relative z-0 pointer-events-none">
                                                <i id="sim_resubmit_icon" class="bi bi-card-heading text-3xl text-slate-300 dark:text-slate-600 mb-2 block group-hover:text-blue-400 transition-colors duration-200"></i>
                                                <span id="sim_resubmit_text" class="text-slate-500 dark:text-slate-400 text-sm font-medium block">Klik atau seret file SIM ke sini</span>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">JPG, JPEG, PNG — maks. 2MB</p>
                                                <img id="sim_resubmit_preview" class="hidden mt-3 mx-auto max-h-32 object-contain rounded-lg shadow">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-sm hover:shadow-md">
                                        <i class="bi bi-cloud-arrow-up-fill"></i> Kirim Ulang Dokumen
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                            </div>
                        </div>
                <?php endif; // end of if ($status === 'rejected') ?>
            <?php endif; // end of if (!$docsUnlocked) ?>
        <?php endif; // end of if ($status === 'unverified') ?>
        </div>

        <div class="tab-panel" id="tab-editprofil">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Edit Profil</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Perbarui informasi akun Anda.</p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8 max-w-xl">
                <form action="index.php?module=Profile&action=update" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="<?= $name ?>" 
                               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50"
                               placeholder="Nama lengkap Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Email</label>
                        <input type="email" name="email" value="<?= $email ?>"
                               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50"
                               placeholder="Alamat email">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nomor HP</label>
                        <input type="text" name="phone" value="<?= $phone ?>"
                               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50"
                               placeholder="Nomor telepon">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-all duration-200 text-sm">
                        <i class="bi bi-check2 mr-1.5"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <div class="tab-panel" id="tab-ubahpassword">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Ubah Password</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Ganti kata sandi akun Anda secara berkala untuk keamanan.</p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-6 sm:p-8 max-w-xl">
                <form action="index.php?module=Profile&action=changePassword" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Password Lama</label>
                        <div class="relative">
                            <input type="password" name="old_password" id="old_password"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50"
                                   placeholder="Password saat ini">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:text-slate-300" onclick="togglePwd('old_password', this)">
                                <i class="bi bi-eye text-base"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Password Baru</label>
                        <div class="relative">
                            <input type="password" name="new_password" id="new_password"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50"
                                   placeholder="Password baru (min. 8 karakter)">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:text-slate-300" onclick="togglePwd('new_password', this)">
                                <i class="bi bi-eye text-base"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 dark:bg-slate-900/50"
                                   placeholder="Ulangi password baru">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:text-slate-300" onclick="togglePwd('confirm_password', this)">
                                <i class="bi bi-eye text-base"></i>
                            </button>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700 space-y-0.5">
                        <p class="font-semibold mb-1">Tips keamanan:</p>
                        <p>• Gunakan minimal 8 karakter</p>
                        <p>• Kombinasikan huruf besar, kecil, angka & simbol</p>
                        <p>• Jangan gunakan password yang sama di layanan lain</p>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-all duration-200 text-sm">
                        <i class="bi bi-shield-check mr-1.5"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>

        <div class="tab-panel" id="tab-bantuan">
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pusat Bantuan</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Temukan jawaban atau hubungi dukungan admin jika mengalami kendala.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <h2 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-2">
                        <i class="bi bi-info-circle text-blue-500"></i> Pertanyaan yang Sering Diajukan (FAQ)
                    </h2>
                    
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm space-y-2">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Bagaimana cara memverifikasi akun?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Buka tab <strong class="text-slate-700 dark:text-slate-200">Dokumen Identitas</strong> di sidebar, kemudian unggah foto KTP dan SIM Anda yang masih berlaku. Admin akan memeriksa pengajuan maksimal dalam 1x24 jam.</p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm space-y-2">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Kenapa status verifikasi saya ditolak?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Penolakan biasanya terjadi karena foto dokumen buram, terpotong, atau masa berlakunya habis. Anda bisa mengunggah ulang dokumen yang lebih jelas pada tab Dokumen Identitas.</p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm space-y-2">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Apakah mobil boleh dibawa ke luar kota?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Boleh. Seluruh armada kami dapat digunakan untuk perjalanan dalam maupun luar kota. Namun, jangan ke madura, sama medan gais wkwk</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-2">
                        <i class="bi bi-chat-left-dots text-blue-500"></i> Hubungi Kami
                    </h2>
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-md space-y-4">
                        <div>
                            <h3 class="font-bold text-lg">Butuh Bantuan Lebih Lanjut?</h3>
                            <p class="text-xs text-blue-100 mt-1 leading-relaxed">Customer Service kami siap melayani kendala transaksi, klaim denda, atau pertanyaan seputar armada mobil selama 24/7.</p>
                        </div>
                        
<a href="https://mail.google.com/mail/?view=cm&fs=1&to=swambilsby@gmail.com&su=Butuh%20Bantuan%20SewaMobil&body=Halo%20Admin%20SewaMobil,%20saya%20butuh%20bantuan%20terkait%20akun%20saya.%20Berikut%20detailnya:%0A%0ANama:%20%0AKendala:%20" 
   target="_blank" 
   class="flex items-center justify-center gap-2 w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-xl shadow transition-all duration-200 no-underline text-sm">
    <i class="bi bi-envelope-fill text-lg"></i> Hubungi via Gmail
</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // ==================== REJECTED DOCS STEP WORKFLOW ====================
    function showRejectedUploadForm() {
        const infoCard   = document.getElementById('rejected-info-card');
        const uploadForm = document.getElementById('rejected-upload-form');
        if (!infoCard || !uploadForm) return;

        // Fade out info card
        infoCard.style.transition = 'opacity .2s ease, transform .2s ease';
        infoCard.style.opacity = '0';
        infoCard.style.transform = 'translateY(-6px)';

        setTimeout(() => {
            infoCard.classList.add('hidden');
            infoCard.style.opacity = '';
            infoCard.style.transform = '';

            uploadForm.classList.remove('hidden');
            // Trigger reflow for animation
            void uploadForm.offsetWidth;
            uploadForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 200);
    }

    function hideRejectedUploadForm() {
        const infoCard   = document.getElementById('rejected-info-card');
        const uploadForm = document.getElementById('rejected-upload-form');
        if (!infoCard || !uploadForm) return;

        uploadForm.style.transition = 'opacity .2s ease, transform .2s ease';
        uploadForm.style.opacity = '0';
        uploadForm.style.transform = 'translateY(-6px)';

        setTimeout(() => {
            uploadForm.classList.add('hidden');
            uploadForm.style.opacity = '';
            uploadForm.style.transform = '';

            infoCard.classList.remove('hidden');
            void infoCard.offsetWidth;
            infoCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 200);
    }

    // ==================== TAB NAVIGATION ====================
    const sidebarLinks = document.querySelectorAll('[data-tab]');
    const tabPanels    = document.querySelectorAll('.tab-panel');

    function activateTab(tabId) {
        sidebarLinks.forEach(l => l.classList.remove('active'));
        tabPanels.forEach(p => p.classList.remove('active'));

        const link  = document.querySelector(`[data-tab="${tabId}"]`);
        const panel = document.getElementById(`tab-${tabId}`);
        if (link)  link.classList.add('active');
        if (panel) panel.classList.add('active');

        // Close mobile sidebar
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('show');

        // Persist to URL hash
        history.replaceState(null, '', `#${tabId}`);
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            activateTab(link.dataset.tab);
        });
    });

    // "Lihat semua" links
    document.querySelectorAll('[data-tab-link]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            activateTab(el.dataset.tabLink);
        });
    });

    // Init from URL hash
    const hash = location.hash.replace('#', '');
    if (hash && document.getElementById(`tab-${hash}`)) {
        activateTab(hash);
    }

    // ==================== MOBILE SIDEBAR ====================
    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebar-overlay');
    const toggleBtn      = document.getElementById('sidebar-toggle');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    });

    // ==================== IMAGE PREVIEW ====================
    function previewImage(input, previewId, textId, iconId) {
        const preview = document.getElementById(previewId);
        const text    = document.getElementById(textId);
        const icon    = document.getElementById(iconId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                text.textContent = 'File: ' + input.files[0].name;
                text.classList.add('text-blue-600', 'font-bold', 'text-xs');
                if (icon) icon.style.color = '#3b82f6';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.classList.add('hidden');
        }
    }

    // ==================== PASSWORD TOGGLE ====================
    function togglePwd(fieldId, btn) {
        const field = document.getElementById(fieldId);
        const icon  = btn.querySelector('i');
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'bi bi-eye-slash text-base';
        } else {
            field.type = 'password';
            icon.className = 'bi bi-eye text-base';
        }
    }

    // ==================== THEME TOGGLE ====================
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeToggleIcon = document.getElementById('theme-toggle-icon');

    function updateThemeIcon() {
        if (document.documentElement.classList.contains('dark')) {
            themeToggleIcon.classList.replace('bi-moon-fill', 'bi-sun-fill');
        } else {
            themeToggleIcon.classList.replace('bi-sun-fill', 'bi-moon-fill');
        }
    }

    if (themeToggleBtn) {
        updateThemeIcon();
        themeToggleBtn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            if (document.documentElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
            updateThemeIcon();
        });
    }
</script>
</body>
</html>