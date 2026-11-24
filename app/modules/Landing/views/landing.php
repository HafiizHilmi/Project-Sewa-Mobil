<?php
require_once __DIR__ . '/../../../../include/site.php';
require_once __DIR__ . '/../../../../include/car_card.php';

$site = site_settings();
$cars = $cars ?? [];
$fleetStats = $fleetStats ?? ['types' => count($cars), 'units' => 0, 'categories' => 0];
$startPrice = $startPrice ?? null;

$heroCar = $cars[0] ?? null;
$heroImage = $heroCar ? car_image_url($heroCar['image'] ?? '') : 'assets/images/cars/toyota-innova-zenix-2024.jpg';
$heroName = $heroCar ? trim($heroCar['make'] . ' ' . $heroCar['model']) : 'Toyota Innova Zenix';
$heroPrice = $heroCar ? rupiah($heroCar['price_per_day']) : 'Rp 350.000';
$categoryCount = count(array_unique(array_map(fn($c) => strtolower((string) ($c['category'] ?? '')), $cars)));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        document.documentElement.classList.add('js');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site['name']) ?> — Sewa mobil di Surabaya</title>
    <meta name="description" content="Sewa mobil harian, mingguan, dan bulanan di Surabaya. <?= (int) $fleetStats['types'] ?> tipe armada, verifikasi cepat, harga transparan.">
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
    <?php $navActive = 'landing'; include __DIR__ . '/../../../../include/nav.php'; ?>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy" data-reveal>
                    <span class="eyebrow eyebrow--accent">Rental mobil &middot; Surabaya</span>
                    <h1 class="display">Sewa mobil<br>tanpa <em>ribet</em>.</h1>
                    <p class="lead">
                        <?= (int) $fleetStats['types'] ?> tipe mobil siap jalan, dari city car harian sampai
                        MPV keluarga. Pesan online, verifikasi dokumen sekali, kunci di tangan.
                    </p>
                    <div class="hero-actions">
                        <a class="btn btn--primary btn--lg" href="index.php?module=Homepage&action=index">
                            Lihat armada <i class="bi bi-arrow-right"></i>
                        </a>
                        <a class="btn btn--outline btn--lg" href="#cara-sewa">Cara sewa</a>
                    </div>
                    <div class="hero-facts">
                        <div class="fact"><b class="num" data-count="<?= (int) $fleetStats['types'] ?>">0</b><span>tipe mobil</span></div>
                        <div class="fact"><b class="num" data-count="<?= (int) $fleetStats['units'] ?>">0</b><span>unit terawat</span></div>
                        <div class="fact"><b class="num" data-count="<?= max(1, (int) ($fleetStats['categories'] ?? 0)) ?: 1 ?>">0</b><span>kategori</span></div>
                    </div>
                </div>

                <div class="hero-media" data-reveal>
                    <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($heroName) ?>" fetchpriority="high">
                    <div class="hero-tag">
                        <span class="dot" aria-hidden="true"></span>
                        <div>
                            <b>Mulai <?= htmlspecialchars($startPrice ? rupiah($startPrice) : $heroPrice) ?>/hari</b>
                            <span>Harga transparan &middot; bisa tambah driver</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section--line" id="armada">
            <div class="container">
                <div class="section-head" data-reveal>
                    <div>
                        <span class="eyebrow">Armada</span>
                        <h2 style="margin-top:10px">Yang paling sering disewa</h2>
                        <p>Harga sewa per hari. Pajak 10% dihitung saat checkout.</p>
                    </div>
                    <a class="btn btn--outline" href="index.php?module=Homepage&action=index">
                        Semua <?= (int) $fleetStats['types'] ?> mobil <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="car-grid">
                    <?php if (!empty($cars)): ?>
                        <?php foreach ($cars as $car) car_card($car); ?>
                    <?php else: ?>
                        <div class="empty">
                            <i class="bi bi-car-front"></i>
                            <h3>Belum ada armada tersedia</h3>
                            <p>Data mobil akan muncul di sini setelah admin menambahkannya.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section" id="cara-sewa">
            <div class="container">
                <div class="section-head" data-reveal>
                    <div>
                        <span class="eyebrow">Cara sewa</span>
                        <h2 style="margin-top:10px">Tiga langkah, satu kunci</h2>
                        <p>Semua dilakukan online. Tidak perlu telepon, tidak perlu datang dua kali.</p>
                    </div>
                </div>

                <div class="steps" data-reveal>
                    <div class="step">
                        <h3>Pilih mobil &amp; tanggal</h3>
                        <p>Tentukan unit, tanggal mulai, dan lokasi penjemputan. Kalender menampilkan tanggal yang masih kosong.</p>
                    </div>
                    <div class="step">
                        <h3>Verifikasi dokumen</h3>
                        <p>Unggah KTP dan SIM sekali di halaman profil. Setelah disetujui admin, kamu bisa memesan tanpa batas.</p>
                    </div>
                    <div class="step">
                        <h3>Bayar &amp; ambil mobil</h3>
                        <p>Pembayaran lewat Midtrans, status pesanan diperbarui otomatis. Tunjukkan bukti pesanan saat pengambilan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section--tight">
            <div class="container">
                <div class="cta-band" data-reveal>
                    <div>
                        <h2>Butuh mobil untuk besok pagi?</h2>
                        <p>Armada dan harga selalu terbaru di katalog. Pesan sekarang, konfirmasi langsung masuk ke halaman pesananmu.</p>
                    </div>
                    <a class="btn btn--primary btn--lg" href="index.php?module=Homepage&action=index">
                        Cari mobil <i class="bi bi-search"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../../../../include/footer.php'; ?>
</body>
</html>