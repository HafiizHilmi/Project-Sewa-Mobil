<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../../include/db_config.php';
$pdo = getPDO();

$booking = $_SESSION['booking_pending'] ?? null;
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : ($booking['id'] ?? null);

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

if (in_array($status, ['approved', 'success', 'confirmed'], true)) {
    header('Location: index.php?module=Booking&action=success&booking_id=' . $bookingId);
    exit;
}

if (in_array($status, ['rejected', 'cancelled'], true)) {
    header('Location: index.php?module=Booking&action=rejected&booking_id=' . $bookingId);
    exit;
}

$orderId = $bookingRow['id'];
$startLabel = date('j F Y', strtotime($bookingRow['start_date']));
$endLabel = date('j F Y', strtotime($bookingRow['end_date']));
$totalPrice = number_format($bookingRow['total_price'], 0, ',', '.');
$pickupLocation = htmlspecialchars($bookingRow['pickup_location']);
$returnLocation = htmlspecialchars($bookingRow['return_location'] ?: 'Sama dengan lokasi pengambilan');
$carName = htmlspecialchars(trim(($bookingRow['make'] ?? '') . ' ' . ($bookingRow['model'] ?? '') . ' ' . ($bookingRow['year'] ?? '')));
$rawImg = $bookingRow['image'] ?? '';
$carImage = !empty($rawImg) ? (filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : 'assets/images/' . $rawImg) : 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop';
$carImage = htmlspecialchars($carImage);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Terkirim - SewaMobil</title>
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
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        html.dark body {
            background: linear-gradient(135deg, #020617 0%, #0f172a 100%);
            color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-12 transition-colors duration-200">
    <div class="w-full max-w-5xl">
        <div class="rounded-[2rem] overflow-hidden shadow-2xl ring-1 ring-black/5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <div class="grid grid-cols-1 lg:grid-cols-[1.35fr_0.9fr] gap-0">
                <div class="p-10 lg:p-14">
                    <div class="inline-flex items-center gap-3 rounded-full bg-blue-50 dark:bg-blue-900/40 px-4 py-2 mb-6 text-blue-700 dark:text-blue-200 text-sm font-semibold">
                        <i class="bi bi-clock-history"></i>
                        <span>Status: Menunggu Verifikasi Admin</span>
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 dark:text-white leading-tight">Reservasi Berhasil Dikirim!</h1>
                    <p class="mt-5 text-slate-600 dark:text-slate-300 text-base leading-8 max-w-2xl">
                        Mohon tunggu, pesanan Anda sedang ditinjau dan menunggu persetujuan dari Admin. Anda dapat memantau status pesanan di halaman Riwayat Penyewaan.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <a href="index.php?module=Homepage&action=index" class="inline-flex items-center justify-center gap-2 rounded-3xl bg-blue-600 text-white px-6 py-3 text-sm font-semibold shadow-lg shadow-blue-600/10 hover:bg-blue-700 transition">
                            <i class="bi bi-house-door-fill"></i>
                            Halaman Utama
                        </a>
                        <a href="index.php?module=Profile&action=index#riwayat" class="inline-flex items-center justify-center gap-2 rounded-3xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-6 py-3 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <i class="bi bi-clock-history"></i>
                            Riwayat Penyewaan
                        </a>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-5">
                            <p class="text-xs uppercase font-semibold tracking-[0.2em] text-slate-500 dark:text-slate-400 mb-2">Nomor Pesanan</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">#RESRV-<?= htmlspecialchars($orderId) ?></p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-5">
                            <p class="text-xs uppercase font-semibold tracking-[0.2em] text-slate-500 dark:text-slate-400 mb-2">Mobil pilihan</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white"><?= $carName ?></p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= $startLabel ?> – <?= $endLabel ?></p>
                        </div>
                    </div>
                </div>
                <div class="relative bg-slate-900 dark:bg-slate-950 px-8 py-10 sm:px-10 sm:py-16 text-white">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-700/20 via-slate-900/10 to-slate-900/0"></div>
                    <div class="relative z-10">
                        <div class="rounded-[2rem] overflow-hidden bg-slate-950 border border-slate-800 shadow-2xl shadow-slate-900/20 p-5">
                            <img src="<?= $carImage ?>" alt="<?= $carName ?>" class="w-full h-56 object-cover rounded-3xl shadow-inner">
                        </div>
                        <div class="mt-7 rounded-3xl border border-slate-800 bg-slate-900/95 p-6">
                            <h2 class="text-xl font-bold">Rangkuman Pemesanan</h2>
                            <p class="mt-3 text-sm text-slate-400">Total harga akan ditampilkan setelah admin menyetujui pesanan Anda.</p>
                            <div class="mt-6 space-y-3 text-sm">
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Rentang Sewa</span>
                                    <span class="text-white"><?= $startLabel ?> – <?= $endLabel ?></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Lokasi Ambil</span>
                                    <span class="text-white text-right max-w-[140px] truncate"><?= $pickupLocation ?></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400">
                                    <span>Lokasi Antar</span>
                                    <span class="text-white text-right max-w-[140px] truncate"><?= $returnLocation ?></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-400 border-t border-slate-800 pt-3">
                                    <span class="font-semibold text-slate-200">Estimasi Pembayaran</span>
                                    <span class="font-semibold text-white">Rp<?= $totalPrice ?></span>
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
