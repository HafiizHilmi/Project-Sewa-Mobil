<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../../include/db_config.php';
$pdo = getPDO();

$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;

if (!$bookingId || !$pdo) {
    header('Location: index.php?module=Homepage&action=index');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT b.*, c.make, c.model, c.year, COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), c.image) AS image FROM bookings b LEFT JOIN cars c ON c.id = b.car_id WHERE b.id = :id LIMIT 1"
);
$stmt->execute(['id' => $bookingId]);
$bookingRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bookingRow) {
    header('Location: index.php?module=Homepage&action=index');
    exit;
}

$status = strtolower($bookingRow['status'] ?? '');

if (!in_array($status, ['rejected', 'cancelled'], true)) {
    if (in_array($status, ['approved', 'success', 'confirmed'], true)) {
        header('Location: index.php?module=Booking&action=success&booking_id=' . $bookingId);
        exit;
    }
    if ($status === 'pending') {
        header('Location: index.php?module=Booking&action=pending&booking_id=' . $bookingId);
        exit;
    }
    header('Location: index.php?module=Homepage&action=index');
    exit;
}

$orderId = $bookingRow['id'];
$carName = htmlspecialchars(trim(($bookingRow['make'] ?? '') . ' ' . ($bookingRow['model'] ?? '') . ' ' . ($bookingRow['year'] ?? '')));
$rejectReason = trim($bookingRow['reject_reason'] ?? '');
$rejectReason = $rejectReason !== '' ? $rejectReason : 'Tidak ada alasan spesifik yang diberikan oleh admin.';
$rawImg = $bookingRow['image'] ?? '';
$carImage = !empty($rawImg) ? (filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : 'assets/images/' . $rawImg) : 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop';
$carImage = htmlspecialchars($carImage);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Ditolak - SewaMobil</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body {
            min-height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            margin: 0;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }
        html.dark body {
            background: linear-gradient(135deg, #0f172a 0%, #0b1120 100%);
            color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-12 transition-colors duration-200">
    <div class="w-full max-w-5xl">
        <div class="rounded-[2rem] overflow-hidden shadow-2xl ring-1 ring-black/5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.95fr] gap-0">
                <div class="p-10 lg:p-14">
                    <div class="inline-flex items-center gap-3 rounded-full bg-red-50 dark:bg-red-900/30 px-4 py-2 mb-6 text-red-700 dark:text-red-200 text-sm font-semibold">
                        <i class="bi bi-x-octagon-fill"></i>
                        <span>Pesanan Ditolak</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 dark:text-white leading-tight">Maaf, pesanan Anda ditolak oleh Admin.</h1>
                    <p class="mt-5 text-slate-600 dark:text-slate-300 text-base leading-8 max-w-2xl">
                        Kami telah meninjau pesanan Anda dan sayangnya pesanan tersebut tidak dapat diproses. Silakan pilih armada lain atau coba kembali setelah memperbaiki informasi pemesanan.
                    </p>
                    <div class="mt-8 space-y-4">
                        <div class="rounded-3xl border border-red-100 dark:border-red-900/70 bg-red-50 dark:bg-red-950/80 p-5">
                            <p class="text-sm font-semibold text-red-700 dark:text-red-300">Alasan penolakan</p>
                            <p class="mt-3 text-sm text-slate-700 dark:text-slate-200 leading-relaxed"><?= htmlspecialchars($rejectReason) ?></p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-5">
                            <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500 dark:text-slate-400 mb-2">Nomor Pesanan</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">#RESRV-<?= htmlspecialchars($orderId) ?></p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Mobil: <?= $carName ?></p>
                        </div>
                    </div>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <a href="index.php?module=Homepage&action=index" class="inline-flex items-center justify-center gap-2 rounded-3xl bg-red-600 text-white px-6 py-3 text-sm font-semibold shadow-lg shadow-red-600/10 hover:bg-red-700 transition">
                            <i class="bi bi-car-front-fill"></i>
                            Kembali ke Katalog Mobil
                        </a>
                        <a href="index.php?module=Profile&action=index#riwayat" class="inline-flex items-center justify-center gap-2 rounded-3xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-6 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <i class="bi bi-clock-history"></i>
                            Cek Riwayat Pesanan
                        </a>
                    </div>
                </div>
                <div class="relative bg-red-950/95 px-8 py-10 sm:px-10 sm:py-16 text-white">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-700/15 via-transparent to-slate-950/10"></div>
                    <div class="relative z-10">
                        <div class="rounded-[2rem] overflow-hidden bg-slate-950/95 border border-red-900/40 shadow-2xl shadow-slate-900/20 p-5">
                            <img src="<?= $carImage ?>" alt="<?= $carName ?>" class="w-full h-56 object-cover rounded-3xl shadow-inner">
                        </div>
                        <div class="mt-7 rounded-3xl border border-red-900/40 bg-red-950/95 p-6">
                            <h2 class="text-xl font-bold">Saran berikutnya</h2>
                            <p class="mt-3 text-sm text-slate-300 leading-relaxed">Coba pilih jenis mobil yang lain atau atur ulang tanggal sewa agar pesanan memiliki peluang disetujui.</p>
                            <div class="mt-5 grid gap-3 text-sm text-slate-300">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                                    <span>Periksa kembali alamat pengambilan dan pengembalian.</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                                    <span>Pastikan informasi kontak valid dan dapat dihubungi.</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                                    <span>Hubungi admin jika Anda membutuhkan bantuan lebih lanjut.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
