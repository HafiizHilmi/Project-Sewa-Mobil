<?php
require_once __DIR__ . '/../../../../include/site.php';

if (!isset($user) || !is_array($user)) {
    header('Location: index.php?module=Auth&action=login');
    exit;
}

$site    = site_settings();
$status  = $user['verification_status'] ?? 'unverified';
$name    = htmlspecialchars($user['name'] ?? '');
$email   = htmlspecialchars($user['email'] ?? '');
$phone   = htmlspecialchars($user['phone'] ?? '');
$initial = site_initials($user['name'] ?? 'Pengguna');

$bookings = $bookings ?? [];
$totalBookings     = count($bookings);
$activeBookings    = count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed'));
$pendingBookings   = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));
$completedBookings = count(array_filter($bookings, fn($b) => $b['status'] === 'completed'));
$recentBookings    = array_slice($bookings, 0, 3);

/** Label + kelas pill status pesanan. */
$statusMeta = function (string $s, bool $long = false): array {
    return match ($s) {
        'confirmed' => [$long ? 'Berjalan' : 'Aktif', 'status--active'],
        'completed' => ['Selesai', 'status--done'],
        'cancelled' => [$long ? 'Dibatalkan' : 'Batal', 'status--cancel'],
        default     => [$long ? 'Menunggu pembayaran' : 'Menunggu', 'status--pending'],
    };
};

$statusBadge = match ($status) {
    'verified' => ['Terverifikasi', 'badge--ok', 'bi-patch-check-fill'],
    'pending'  => ['Menunggu verifikasi', 'badge--warn', 'bi-hourglass-split'],
    'rejected' => ['Ditolak', 'badge--danger', 'bi-x-circle-fill'],
    default    => ['Belum verifikasi', '', 'bi-circle'],
};

$fmtDate = fn(string $d): string => date('d M Y', strtotime($d));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        document.documentElement.classList.add('js');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun saya — <?= htmlspecialchars($site['name']) ?></title>
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            var dark = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <?php $navActive = 'profil'; include __DIR__ . '/../../../../include/nav.php'; ?>

    <div class="dash-layout">

        <aside class="dash-side">
            <div class="dash-user">
                <div class="dash-avatar"><?= htmlspecialchars($initial) ?></div>
                <div style="min-width:0">
                    <b><?= $name ?></b>
                    <span title="<?= $email ?>"><?= $email ?></span>
                    <span class="badge <?= $statusBadge[1] ?>"><i class="bi <?= $statusBadge[2] ?>"></i> <?= $statusBadge[0] ?></span>
                </div>
            </div>

            <nav class="dash-menu" aria-label="Menu akun">
                <a href="#dashboard" class="dash-link active" data-tab="dashboard"><i class="bi bi-grid-1x2"></i> Ringkasan</a>
                <a href="#riwayat" class="dash-link" data-tab="riwayat">
                    <i class="bi bi-clock-history"></i> Riwayat sewa
                    <?php if ($totalBookings > 0): ?><span class="count"><?= $totalBookings ?></span><?php endif; ?>
                </a>
                <a href="#dokumen" class="dash-link" data-tab="dokumen">
                    <i class="bi bi-person-vcard"></i> Dokumen identitas
                    <?php if (in_array($status, ['pending', 'rejected'], true)): ?><span class="dot"></span><?php endif; ?>
                </a>

                <span class="dash-menu-label">Pengaturan akun</span>
                <a href="#editprofil" class="dash-link" data-tab="editprofil"><i class="bi bi-pencil-square"></i> Data pribadi</a>
                <a href="#ubahpassword" class="dash-link" data-tab="ubahpassword"><i class="bi bi-shield-lock"></i> Password</a>
                <a href="#tema" class="dash-link" data-tab="tema"><i class="bi bi-palette"></i> Tampilan</a>
                <a href="#bantuan" class="dash-link" data-tab="bantuan"><i class="bi bi-question-circle"></i> Bantuan</a>
            </nav>

            <div class="dash-logout">
                <a href="index.php?module=Auth&action=logout" class="dash-link" style="color:var(--danger)">
                    <i class="bi bi-box-arrow-right" style="color:var(--danger)"></i> Keluar dari akun
                </a>
            </div>
        </aside>

        <main class="dash-main">
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="notice notice--ok alert-banner" style="margin-bottom:16px">
                    <i class="bi bi-check-circle-fill"></i>
                    <span style="flex:1"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    <button type="button" onclick="closeAlert(this)" aria-label="Tutup" style="background:none;border:0;color:inherit"><i class="bi bi-x-lg"></i></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="notice notice--danger alert-banner" style="margin-bottom:16px">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span style="flex:1"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    <button type="button" onclick="closeAlert(this)" aria-label="Tutup" style="background:none;border:0;color:inherit"><i class="bi bi-x-lg"></i></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if ($status === 'pending'): ?>
                <div class="notice notice--warn alert-banner" style="margin-bottom:16px">
                    <i class="bi bi-hourglass-split"></i>
                    <span style="flex:1">Dokumenmu sedang diperiksa admin. Biasanya selesai dalam 1×24 jam.</span>
                    <button type="button" onclick="closeAlert(this)" aria-label="Tutup" style="background:none;border:0;color:inherit"><i class="bi bi-x-lg"></i></button>
                </div>
            <?php elseif ($status === 'verified'): ?>
                <div class="notice notice--ok alert-banner" style="margin-bottom:16px">
                    <i class="bi bi-patch-check-fill"></i>
                    <span style="flex:1">Akun terverifikasi. Semua fitur sewa sudah aktif.</span>
                    <button type="button" onclick="closeAlert(this)" aria-label="Tutup" style="background:none;border:0;color:inherit"><i class="bi bi-x-lg"></i></button>
                </div>
            <?php elseif ($status === 'rejected'): ?>
                <div class="notice notice--danger alert-banner" style="margin-bottom:16px">
                    <i class="bi bi-x-octagon-fill"></i>
                    <span style="flex:1">Verifikasi ditolak. Unggah ulang KTP dan SIM di menu <b>Dokumen identitas</b>.</span>
                    <button type="button" onclick="closeAlert(this)" aria-label="Tutup" style="background:none;border:0;color:inherit"><i class="bi bi-x-lg"></i></button>
                </div>
            <?php endif; ?>

            <!-- ============================ DASHBOARD ============================ -->
            <section class="tab-panel active" id="tab-dashboard">
                <div class="dash-head">
                    <h1>Halo, <?= htmlspecialchars(explode(' ', trim($user['name'] ?? 'Pengguna'))[0]) ?></h1>
                    <p>Ringkasan akun dan aktivitas sewamu.</p>
                </div>

                <div class="stat-grid">
                    <div class="stat-card" data-reveal><b class="num" data-count="<?= $totalBookings ?>">0</b><span>Total sewa</span></div>
                    <div class="stat-card" data-reveal><b class="num" data-count="<?= $pendingBookings ?>">0</b><span>Menunggu bayar</span></div>
                    <div class="stat-card" data-reveal><b class="num" data-count="<?= $activeBookings ?>">0</b><span>Sedang berjalan</span></div>
                    <div class="stat-card" data-reveal><b class="num" data-count="<?= $completedBookings ?>">0</b><span>Selesai</span></div>
                </div>

                <div class="list-card" data-reveal>
                    <div class="list-head">
                        <h2>Penyewaan terbaru</h2>
                        <?php if ($totalBookings > 3): ?>
                            <a href="#riwayat" data-tab-link="riwayat">Lihat semua <i class="bi bi-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $booking): ?>
                            <?php [$sLabel, $sClass] = $statusMeta($booking['status']); ?>
                            <div class="list-row">
                                <div class="list-thumb">
                                    <img src="<?= htmlspecialchars(car_image_url($booking['image'] ?? '')) ?>" alt="" loading="lazy">
                                </div>
                                <div class="grow">
                                    <b><?= htmlspecialchars(trim($booking['make'] . ' ' . $booking['model'])) ?></b>
                                    <small><?= $fmtDate($booking['start_date']) ?> – <?= $fmtDate($booking['end_date']) ?> &middot; <?= htmlspecialchars($booking['pickup_location']) ?></small>
                                </div>
                                <div class="right">
                                    <span class="status <?= $sClass ?>"><?= $sLabel ?></span>
                                    <div class="price num"><?= rupiah($booking['total_price']) ?></div>
                                    <div class="row-action">
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <a class="btn btn--primary btn--sm" href="index.php?module=Booking&action=pending&booking_id=<?= (int) $booking['id'] ?>">Bayar</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty" style="border:0;border-radius:0;background:transparent">
                            <i class="bi bi-car-front"></i>
                            <h3>Belum ada penyewaan</h3>
                            <p>Pilih mobil di katalog, lalu pesan dengan verifikasi akun yang sudah aktif.</p>
                            <a class="btn btn--primary btn--sm u-mt-16" href="index.php?module=Homepage&action=index">Cari mobil</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- ============================= RIWAYAT ============================= -->
            <section class="tab-panel" id="tab-riwayat">
                <div class="dash-head">
                    <h1>Riwayat sewa</h1>
                    <p><?= $totalBookings ?> transaksi tercatat di akun ini.</p>
                </div>

                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        [$sLabel, $sClass] = $statusMeta($booking['status'], true);
                        $sisaHari = '';
                        if ($booking['status'] === 'confirmed') {
                            $now = new DateTime();
                            $end = new DateTime($booking['end_date']);
                            if ($now < $end) {
                                $sisaHari = 'Sisa ' . $now->diff($end)->days . ' hari';
                            } else {
                                $sisaHari = 'Masa sewa habis';
                            }
                        }
                        $carName = htmlspecialchars(trim($booking['make'] . ' ' . $booking['model']));
                        ?>
                        <article class="booking-item">
                            <div class="booking-media">
                                <img src="<?= htmlspecialchars(car_image_url($booking['image'] ?? '')) ?>" alt="<?= $carName ?>" loading="lazy">
                            </div>

                            <div style="min-width:0">
                                <h3><?= $carName ?></h3>
                                <p class="meta">
                                    #TRX-<?= str_pad((string) $booking['id'], 5, '0', STR_PAD_LEFT) ?>
                                    &middot; <?= htmlspecialchars($booking['category'] ?? '') ?>
                                    <?php if (!empty($booking['assigned_plate'])): ?>
                                        &middot; plat <?= htmlspecialchars($booking['assigned_plate']) ?>
                                    <?php endif; ?>
                                </p>

                                <p class="meta" style="margin-top:6px">
                                    <i class="bi bi-calendar3"></i> <?= $fmtDate($booking['start_date']) ?> – <?= $fmtDate($booking['end_date']) ?>
                                    &nbsp;·&nbsp; <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($booking['pickup_location']) ?>
                                    <?php if ($sisaHari): ?>&nbsp;·&nbsp; <b style="color:var(--accent)"><?= $sisaHari ?></b><?php endif; ?>
                                </p>

                                <button type="button" class="detail-toggle" onclick="toggleBookingDetail('detail-<?= (int) $booking['id'] ?>', this)">
                                    <span>Rincian</span> <i class="bi bi-chevron-down"></i>
                                </button>

                                <div id="detail-<?= (int) $booking['id'] ?>" class="hidden">
                                    <div class="kv">
                                        <div><span>Tanggal transaksi</span><b><?= date('d M Y H:i', strtotime($booking['created_at'])) ?></b></div>
                                        <?php if (!empty($booking['return_location'])): ?>
                                            <div><span>Pengembalian</span><b><?= htmlspecialchars($booking['return_location']) ?></b></div>
                                        <?php endif; ?>
                                        <div><span>Tambahan</span><b><?= $booking['addon_driver'] ? 'Dengan driver' : 'Lepas kunci' ?></b></div>
                                    </div>

                                    <?php if ($booking['status'] === 'completed' && ((float) $booking['additional_cost'] > 0 || !empty($booking['damage_description']))): ?>
                                        <div class="notice notice--danger" style="margin-top:14px">
                                            <i class="bi bi-exclamation-octagon-fill"></i>
                                            <span>
                                                <?php if ((float) $booking['additional_cost'] > 0): ?>
                                                    Biaya tambahan <b><?= rupiah($booking['additional_cost']) ?></b>.
                                                <?php endif; ?>
                                                <?php if (!empty($booking['damage_description'])): ?>
                                                    <?= htmlspecialchars($booking['damage_description']) ?>
                                                <?php endif; ?>
                                                <?php if (!empty($booking['damage_image'])): ?>
                                                    <a href="assets/images/damages/<?= htmlspecialchars($booking['damage_image']) ?>" target="_blank" rel="noopener" style="text-decoration:underline">Lihat bukti foto</a>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="booking-side">
                                <span class="status <?= $sClass ?>"><?= $sLabel ?></span>
                                <div>
                                    <span class="tiny" style="text-transform:uppercase;letter-spacing:.09em;font-weight:700">Total</span>
                                    <div class="price num"><?= rupiah($booking['total_price']) ?></div>
                                    <?php if ((float) $booking['additional_cost'] > 0): ?>
                                        <span class="tiny" style="color:var(--danger)">+ <?= rupiah($booking['additional_cost']) ?> denda</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a class="btn btn--primary btn--sm" href="index.php?module=Booking&action=pending&booking_id=<?= (int) $booking['id'] ?>">
                                        <i class="bi bi-wallet2"></i> Bayar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-card" data-reveal>
                        <div class="empty" style="border:0;background:transparent">
                            <i class="bi bi-car-front"></i>
                            <h3>Belum ada transaksi</h3>
                            <p>Setelah kamu menyelesaikan pembayaran, riwayat sewa muncul di sini.</p>
                            <a class="btn btn--primary btn--sm u-mt-16" href="index.php?module=Homepage&action=index">Cari mobil</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ============================= DOKUMEN ============================= -->
            <section class="tab-panel" id="tab-dokumen">
                <div class="dash-head">
                    <h1>Dokumen identitas</h1>
                    <p>KTP dan SIM dipakai sekali untuk verifikasi, lalu berlaku untuk semua pesanan.</p>
                </div>

                <?php if ($status === 'unverified'): ?>
                    <div class="panel" style="max-width:640px;margin-inline:auto">
                        <div class="panel-head">
                            <span class="panel-step">1</span>
                            <div>
                                <h2>Unggah dokumen</h2>
                                <p>JPG, JPEG, atau PNG. Maksimal 2 MB per file.</p>
                            </div>
                        </div>

                        <form action="index.php?module=Profile&action=upload" method="POST" enctype="multipart/form-data">
                            <div class="form-grid form-grid--2">
                                <div class="field">
                                    <label>Foto KTP</label>
                                    <label class="upload-zone">
                                        <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png" required
                                               onchange="previewImage(this, 'ktp_preview', 'ktp_text')">
                                        <i class="bi bi-person-vcard"></i>
                                        <span id="ktp_text">Pilih file KTP</span>
                                        <small>Klik atau seret ke sini</small>
                                        <img id="ktp_preview" class="hidden" alt="Pratinjau KTP">
                                    </label>
                                </div>

                                <div class="field">
                                    <label>Foto SIM</label>
                                    <label class="upload-zone">
                                        <input type="file" name="sim_file" accept=".jpg,.jpeg,.png" required
                                               onchange="previewImage(this, 'sim_preview', 'sim_text')">
                                        <i class="bi bi-card-heading"></i>
                                        <span id="sim_text">Pilih file SIM</span>
                                        <small>Klik atau seret ke sini</small>
                                        <img id="sim_preview" class="hidden" alt="Pratinjau SIM">
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn--primary btn--block u-mt-24">
                                <i class="bi bi-cloud-arrow-up"></i> Unggah dokumen
                            </button>
                        </form>
                    </div>

                <?php elseif (!$docsUnlocked && $status !== 'rejected'): ?>
                    <div class="panel" style="max-width:560px;margin-inline:auto">
                        <div class="panel-head">
                            <span class="panel-icon"><i class="bi bi-shield-check"></i></span>
                            <div>
                                <h2>Dokumen terkunci</h2>
                                <p>Masukkan password akun untuk melihat file yang sudah diunggah.</p>
                            </div>
                        </div>

                        <form action="index.php?module=Profile&action=verifyDocsPassword" method="POST" class="u-stack">
                            <div class="field" style="position:relative">
                                <label for="doc_password">Password akun</label>
                                <input class="input" type="password" name="password" id="doc_password" placeholder="Password kamu" required style="padding-right:44px">
                                <button type="button" onclick="togglePwd('doc_password', this)" aria-label="Tampilkan password"
                                        style="position:absolute;right:12px;bottom:12px;background:none;border:0;color:var(--ink-3)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <button type="submit" class="btn btn--primary btn--block"><i class="bi bi-unlock"></i> Buka dokumen</button>
                        </form>
                    </div>

                <?php else: ?>
                    <?php if ($status !== 'rejected'): ?>
                        <div class="panel">
                            <div class="u-between" style="margin-bottom:6px">
                                <div>
                                    <h2 style="font-size:1.05rem">File terunggah</h2>
                                    <p class="tiny" style="margin-top:2px">Status: <?= $statusBadge[0] ?></p>
                                </div>
                                <button type="button" class="btn btn--outline btn--sm" onclick="toggleDocImages('doc-images-verified', this)">
                                    <i class="bi bi-eye"></i> <span>Buka dokumen</span>
                                </button>
                            </div>

                            <div id="doc-images-verified" class="hidden">
                                <div class="form-grid form-grid--2 u-mt-16">
                                    <div>
                                        <p class="field-label" style="margin-bottom:8px">KTP</p>
                                        <div class="doc-frame">
                                            <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['ktp_file'] ?? '') ?>" alt="KTP">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="field-label" style="margin-bottom:8px">SIM</p>
                                        <div class="doc-frame">
                                            <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['sim_file'] ?? '') ?>" alt="SIM">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($status === 'rejected'): ?>
                        <div class="form-grid form-grid--2" style="align-items:start">

                            <div class="panel">
                                <div class="u-between" style="margin-bottom:10px">
                                    <h2 style="font-size:1rem">Dokumen yang ditolak</h2>
                                    <button type="button" class="btn btn--outline btn--sm" onclick="toggleDocImages('doc-images-rejected', this)">
                                        <i class="bi bi-eye"></i> <span>Buka</span>
                                    </button>
                                </div>

                                <div id="doc-images-rejected" class="hidden">
                                    <div class="u-stack">
                                        <div>
                                            <p class="field-label" style="margin-bottom:8px">KTP lama</p>
                                            <div class="doc-frame">
                                                <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['ktp_file'] ?? '') ?>" alt="KTP lama">
                                            </div>
                                        </div>
                                        <div>
                                            <p class="field-label" style="margin-bottom:8px">SIM lama</p>
                                            <div class="doc-frame">
                                                <img src="../admin/serve_file.php?file=<?= htmlspecialchars($user['sim_file'] ?? '') ?>" alt="SIM lama">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="panel" id="rejected-info-card">
                                    <div class="notice notice--danger" style="margin-bottom:18px">
                                        <i class="bi bi-x-circle-fill"></i>
                                        <div>
                                            <b>Verifikasi ditolak</b>
                                            <p style="margin-top:4px"><?= htmlspecialchars($user['reject_reason'] ?? 'Tidak ada alasan spesifik dari admin.') ?></p>
                                        </div>
                                    </div>

                                    <p class="field-label" style="margin-bottom:10px">Agar diterima</p>
                                    <div class="u-stack" style="font-size:.84rem;color:var(--ink-2)">
                                        <p><i class="bi bi-camera" style="color:var(--ink-3)"></i> Foto terang, tanpa bayangan yang menutupi dokumen.</p>
                                        <p><i class="bi bi-file-earmark" style="color:var(--ink-3)"></i> KTP dan SIM asli, bukan fotokopi.</p>
                                        <p><i class="bi bi-eye" style="color:var(--ink-3)"></i> Teks dan foto wajah terbaca jelas.</p>
                                        <p><i class="bi bi-calendar-check" style="color:var(--ink-3)"></i> Masa berlaku dokumen masih aktif.</p>
                                    </div>

                                    <button type="button" class="btn btn--primary btn--block u-mt-24" id="btn-show-upload-form" onclick="showRejectedUploadForm()">
                                        <i class="bi bi-arrow-repeat"></i> Unggah dokumen baru
                                    </button>
                                </div>

                                <div class="panel hidden" id="rejected-upload-form">
                                    <div class="u-between" style="margin-bottom:18px">
                                        <div>
                                            <h2 style="font-size:1rem">Unggah ulang dokumen</h2>
                                            <p class="tiny" style="margin-top:2px">JPG / JPEG / PNG · maks. 2 MB</p>
                                        </div>
                                        <button type="button" class="btn btn--ghost btn--sm" onclick="hideRejectedUploadForm()">
                                            <i class="bi bi-arrow-left"></i> Kembali
                                        </button>
                                    </div>

                                    <form action="index.php?module=Profile&action=upload" method="POST" enctype="multipart/form-data">
                                        <div class="field">
                                            <label>Foto KTP baru</label>
                                            <label class="upload-zone">
                                                <input type="file" name="ktp_file" accept=".jpg,.jpeg,.png" required
                                                       onchange="previewImage(this, 'ktp_resubmit_preview', 'ktp_resubmit_text')">
                                                <i class="bi bi-person-vcard"></i>
                                                <span id="ktp_resubmit_text">Pilih file KTP</span>
                                                <img id="ktp_resubmit_preview" class="hidden" alt="Pratinjau KTP">
                                            </label>
                                        </div>

                                        <div class="field u-mt-16">
                                            <label>Foto SIM baru</label>
                                            <label class="upload-zone">
                                                <input type="file" name="sim_file" accept=".jpg,.jpeg,.png" required
                                                       onchange="previewImage(this, 'sim_resubmit_preview', 'sim_resubmit_text')">
                                                <i class="bi bi-card-heading"></i>
                                                <span id="sim_resubmit_text">Pilih file SIM</span>
                                                <img id="sim_resubmit_preview" class="hidden" alt="Pratinjau SIM">
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn--primary btn--block u-mt-24">
                                            <i class="bi bi-cloud-arrow-up"></i> Kirim ulang dokumen
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <!-- =========================== EDIT PROFIL =========================== -->
            <section class="tab-panel" id="tab-editprofil">
                <div class="dash-head">
                    <h1>Data pribadi</h1>
                    <p>Perubahan langsung dipakai pada kontrak sewa berikutnya.</p>
                </div>

                <div class="panel" id="edit-profile-gate" style="max-width:640px;margin-inline:auto">
                    <div class="panel-head">
                        <span class="panel-icon"><i class="bi bi-shield-lock"></i></span>
                        <div>
                            <h2>Konfirmasi sebelum mengubah</h2>
                            <p>Nama, email, dan nomor HP harus sama dengan dokumen identitas.</p>
                        </div>
                    </div>

                    <div class="notice notice--warn" style="margin-bottom:18px">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Data yang tidak sesuai bisa membatalkan klaim asuransi atau kontrak sewa yang sedang berjalan.</span>
                    </div>

                    <div class="checkline" style="margin-bottom:18px">
                        <input type="checkbox" id="chk-edit-profile-gate" onchange="toggleGateButton('chk-edit-profile-gate', 'btn-edit-profile-gate')">
                        <label for="chk-edit-profile-gate">Saya sudah memeriksa dan data yang saya masukkan benar.</label>
                    </div>

                    <button type="button" id="btn-edit-profile-gate" class="btn btn--primary btn--block" disabled
                            onclick="passGate('edit-profile-gate', 'edit-profile-form-container')">
                        Lanjutkan <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <div class="panel hidden" id="edit-profile-form-container" style="max-width:640px;margin-inline:auto">
                    <form action="index.php?module=Profile&action=update" method="POST" class="u-stack">
                        <div class="field">
                            <label for="p_name">Nama lengkap</label>
                            <input class="input" type="text" id="p_name" name="name" value="<?= $name ?>" placeholder="Sesuai KTP">
                        </div>
                        <div class="field">
                            <label for="p_email">Email</label>
                            <input class="input" type="email" id="p_email" name="email" value="<?= $email ?>" placeholder="nama@email.com">
                        </div>
                        <div class="field">
                            <label for="p_phone">Nomor HP</label>
                            <input class="input" type="tel" id="p_phone" name="phone" value="<?= $phone ?>" placeholder="0812xxxxxxx">
                        </div>
                        <button type="submit" class="btn btn--primary btn--block"><i class="bi bi-check2"></i> Simpan perubahan</button>
                    </form>
                </div>
            </section>

            <!-- ============================ PASSWORD ============================ -->
            <section class="tab-panel" id="tab-ubahpassword">
                <div class="dash-head">
                    <h1>Password</h1>
                    <p>Ganti kata sandi secara berkala.</p>
                </div>

                <div class="panel" id="change-password-gate" style="max-width:640px;margin-inline:auto">
                    <div class="panel-head">
                        <span class="panel-icon"><i class="bi bi-key"></i></span>
                        <div>
                            <h2>Konfirmasi keamanan</h2>
                            <p>Mengganti password akan mengakhiri sesi login di perangkat lain.</p>
                        </div>
                    </div>

                    <div class="checkline" style="margin-bottom:18px">
                        <input type="checkbox" id="chk-change-password-gate" onchange="toggleGateButton('chk-change-password-gate', 'btn-change-password-gate')">
                        <label for="chk-change-password-gate">Saya memahami dan menyetujui perubahan kata sandi.</label>
                    </div>

                    <button type="button" id="btn-change-password-gate" class="btn btn--primary btn--block" disabled
                            onclick="passGate('change-password-gate', 'change-password-form-container')">
                        Lanjutkan <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <div class="panel hidden" id="change-password-form-container" style="max-width:640px;margin-inline:auto">
                    <form action="index.php?module=Profile&action=changePassword" method="POST" class="u-stack">
                        <?php foreach ([
                            ['old_password', 'Password lama', 'Password saat ini'],
                            ['new_password', 'Password baru', 'Minimal 8 karakter'],
                            ['confirm_password', 'Ulangi password baru', 'Ulangi password baru'],
                        ] as [$field, $label, $hint]): ?>
                            <div class="field">
                                <label for="<?= $field ?>"><?= $label ?></label>
                                <div style="position:relative">
                                    <input class="input" type="password" id="<?= $field ?>" name="<?= $field ?>" placeholder="<?= $hint ?>" style="padding-right:44px">
                                    <button type="button" onclick="togglePwd('<?= $field ?>', this)" aria-label="Tampilkan password"
                                            style="position:absolute;right:12px;top:12px;background:none;border:0;color:var(--ink-3)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <p class="field-hint">Minimal 8 karakter, campur huruf besar/kecil, angka, dan simbol.</p>
                        <button type="submit" class="btn btn--primary btn--block"><i class="bi bi-shield-check"></i> Ubah password</button>
                    </form>
                </div>
            </section>

            <!-- ============================== TEMA ============================== -->
            <section class="tab-panel" id="tab-tema">
                <div class="dash-head">
                    <h1>Tampilan</h1>
                    <p>Pilih mode terang atau gelap. Pilihan tersimpan di perangkat ini.</p>
                </div>

                <div class="form-grid form-grid--2" style="max-width:640px;margin-inline:auto">
                    <div class="theme-option theme-card" id="theme-card-light" role="button" tabindex="0" onclick="setThemeMode('light')">
                        <div class="theme-swatch">
                            <span></span><span style="width:78%"></span><span style="width:56%"></span>
                        </div>
                        <h3>Mode terang</h3>
                        <p class="theme-state">Pilih</p>
                    </div>

                    <div class="theme-option theme-card" id="theme-card-dark" role="button" tabindex="0" onclick="setThemeMode('dark')">
                        <div class="theme-swatch dark-swatch">
                            <span></span><span style="width:78%"></span><span style="width:56%"></span>
                        </div>
                        <h3>Mode gelap</h3>
                        <p class="theme-state">Pilih</p>
                    </div>
                </div>
            </section>

            <!-- ============================= BANTUAN ============================ -->
            <section class="tab-panel" id="tab-bantuan">
                <div class="dash-head">
                    <h1>Bantuan</h1>
                    <p>Jawaban singkat, atau hubungi admin kalau masih buntu.</p>
                </div>

                <div class="faq-list" style="display:flex;flex-direction:column;gap:14px;max-width:820px">
                    <div class="faq">
                        <h3>Bagaimana cara memverifikasi akun?</h3>
                        <p>Buka <b>Dokumen identitas</b>, unggah foto KTP dan SIM yang masih berlaku. Admin memeriksa maksimal 1×24 jam.</p>
                    </div>
                    <div class="faq">
                        <h3>Kenapa verifikasi saya ditolak?</h3>
                        <p>Biasanya karena foto buram, terpotong, atau masa berlaku habis. Unggah ulang lewat menu yang sama.</p>
                    </div>
                    <div class="faq">
                        <h3>Apakah mobil boleh dibawa ke luar kota?</h3>
                        <p>Boleh untuk perjalanan dalam maupun luar kota, kecuali wilayah yang masuk daftar blacklist sistem.</p>
                    </div>
                    <div class="faq">
                        <h3>Bagaimana kalau pembayaran kedaluwarsa?</h3>
                        <p>Pesanan berstatus menunggu akan otomatis dibatalkan setelah masa bayar habis. Silakan buat pesanan baru.</p>
                    </div>

                    <div class="panel" style="margin-top:8px">
                        <div class="panel-head" style="margin-bottom:14px">
                            <span class="panel-icon"><i class="bi bi-chat-dots"></i></span>
                            <div>
                                <h2>Hubungi admin</h2>
                                <p><?= htmlspecialchars($site['email']) ?> &middot; <?= htmlspecialchars($site['phone']) ?></p>
                            </div>
                        </div>
                        <a class="btn btn--outline btn--block" href="mailto:<?= htmlspecialchars($site['email']) ?>?subject=Bantuan%20akun%20<?= rawurlencode($site['name']) ?>">
                            <i class="bi bi-envelope"></i> Kirim email
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
    // ---------- Tab ----------
    const dashLinks = document.querySelectorAll('[data-tab]');
    const tabPanels = document.querySelectorAll('.tab-panel');

    function activateTab(tabId) {
        dashLinks.forEach(l => l.classList.toggle('active', l.dataset.tab === tabId));
        tabPanels.forEach(p => p.classList.toggle('active', p.id === 'tab-' + tabId));
        history.replaceState(null, '', '#' + tabId);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    dashLinks.forEach(link => link.addEventListener('click', e => {
        e.preventDefault();
        activateTab(link.dataset.tab);
    }));

    document.querySelectorAll('[data-tab-link]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            activateTab(el.dataset.tabLink);
        });
    });

    const initialHash = location.hash.replace('#', '');
    if (initialHash && document.getElementById('tab-' + initialHash)) {
        activateTab(initialHash);
    }

    // ---------- Notifikasi ----------
    function closeAlert(btn) {
        const box = btn.closest('.alert-banner');
        if (!box) return;
        box.style.transition = 'opacity .25s ease, transform .25s ease';
        box.style.opacity = '0';
        box.style.transform = 'translateY(-6px)';
        setTimeout(() => box.remove(), 260);
    }

    // ---------- Pratinjau file ----------
    function previewImage(input, previewId, textId) {
        const preview = document.getElementById(previewId);
        const text = document.getElementById(textId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (text) text.textContent = input.files[0].name;
            };
            reader.readAsDataURL(input.files[0]);
        } else if (preview) {
            preview.src = '';
            preview.classList.add('hidden');
        }
    }

    // ---------- Tampilkan password ----------
    function togglePwd(fieldId, btn) {
        const field = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (!field) return;
        const show = field.type === 'password';
        field.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    }

    // ---------- Detail booking ----------
    function toggleBookingDetail(detailId, btn) {
        const box = document.getElementById(detailId);
        if (!box) return;
        const label = btn.querySelector('span');
        const icon = btn.querySelector('i');
        const open = box.classList.contains('hidden');
        box.classList.toggle('hidden', !open);
        label.textContent = open ? 'Tutup rincian' : 'Rincian';
        icon.className = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
    }

    // ---------- Foto dokumen ----------
    function toggleDocImages(docId, btn) {
        const box = document.getElementById(docId);
        if (!box) return;
        const label = btn.querySelector('span');
        const icon = btn.querySelector('i');
        const open = box.classList.contains('hidden');
        box.classList.toggle('hidden', !open);
        if (label) label.textContent = open ? 'Tutup dokumen' : 'Buka dokumen';
        icon.className = open ? 'bi bi-eye-slash' : 'bi bi-eye';
    }

    // ---------- Alur unggah ulang ----------
    function showRejectedUploadForm() {
        const info = document.getElementById('rejected-info-card');
        const form = document.getElementById('rejected-upload-form');
        if (!info || !form) return;
        info.classList.add('hidden');
        form.classList.remove('hidden');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function hideRejectedUploadForm() {
        const info = document.getElementById('rejected-info-card');
        const form = document.getElementById('rejected-upload-form');
        if (!info || !form) return;
        form.classList.add('hidden');
        info.classList.remove('hidden');
        info.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ---------- Gerbang konfirmasi (countdown 5 detik) ----------
    const gateTimers = {};

    function toggleGateButton(checkboxId, buttonId) {
        const checkbox = document.getElementById(checkboxId);
        const button = document.getElementById(buttonId);
        if (!checkbox || !button) return;

        if (gateTimers[buttonId]) {
            clearInterval(gateTimers[buttonId]);
            delete gateTimers[buttonId];
        }

        const label = button.dataset.label || button.innerHTML;

        if (!checkbox.checked) {
            button.disabled = true;
            button.innerHTML = label;
            return;
        }

        let seconds = 3;
        button.disabled = true;
        button.innerHTML = 'Tunggu (' + seconds + 's)…';

        gateTimers[buttonId] = setInterval(() => {
            seconds--;
            if (seconds > 0) {
                button.innerHTML = 'Tunggu (' + seconds + 's)…';
                return;
            }
            clearInterval(gateTimers[buttonId]);
            delete gateTimers[buttonId];
            button.disabled = false;
            button.innerHTML = label;
        }, 1000);
    }

    function passGate(gateId, containerId) {
        const gate = document.getElementById(gateId);
        const container = document.getElementById(containerId);
        if (!gate || !container) return;
        gate.classList.add('hidden');
        container.classList.remove('hidden');
    }

    // ---------- Tema ----------
    function updateThemeCards() {
        const isDark = document.documentElement.classList.contains('dark');
        document.querySelectorAll('.theme-card').forEach(card => {
            const active = card.id === (isDark ? 'theme-card-dark' : 'theme-card-light');
            card.classList.toggle('is-active', active);
            const state = card.querySelector('.theme-state');
            if (state) state.innerHTML = active ? '<i class="bi bi-check2"></i> Sedang dipakai' : 'Pilih';
        });
    }

    function setThemeMode(mode) {
        const dark = mode === 'dark';
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
        updateThemeCards();
        if (typeof window.syncThemeIcon === 'function') window.syncThemeIcon();
    }

    window.addEventListener('storage', e => {
        if (e.key !== 'theme') return;
        document.documentElement.classList.toggle('dark', e.newValue === 'dark');
        document.documentElement.setAttribute('data-bs-theme', e.newValue === 'dark' ? 'dark' : 'light');
        updateThemeCards();
    });

    document.addEventListener('themechange', updateThemeCards);

    document.addEventListener('DOMContentLoaded', () => {
        updateThemeCards();

        // Simpan label awal tombol gate
        document.querySelectorAll('#btn-edit-profile-gate, #btn-change-password-gate').forEach(btn => {
            btn.dataset.label = btn.innerHTML;
        });
    });
    </script>
</body>
</html>