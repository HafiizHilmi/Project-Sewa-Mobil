<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../../include/db_config.php';
$pdo = getPDO();

$booking = $_SESSION['booking_success'] ?? null;
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : ($booking['id'] ?? null);

$car = null;
if ($bookingId && $pdo) {
    $stmtBooking = $pdo->prepare(
        "SELECT b.*, c.make, c.model, c.year, c.category, c.fuel_type, c.seats, COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), c.image) AS image FROM bookings b LEFT JOIN cars c ON c.id = b.car_id WHERE b.id = :id LIMIT 1"
    );
    $stmtBooking->execute(['id' => $bookingId]);
    $bookingRow = $stmtBooking->fetch(PDO::FETCH_ASSOC);

    if ($bookingRow) {
        $booking = $bookingRow;
        $car = $bookingRow;
    }
}

$status = strtolower($booking['status'] ?? '');
$allowedStatuses = ['approved', 'success', 'confirmed'];

if (!$booking || !in_array($status, $allowedStatuses, true)) {
    if ($status === 'pending' || $status === '') {
        header('Location: index.php?module=Booking&action=pending&booking_id=' . ($bookingId ?? '')); 
        exit;
    }

    if (in_array($status, ['rejected', 'cancelled'], true)) {
        header('Location: index.php?module=Booking&action=rejected&booking_id=' . ($bookingId ?? '')); 
        exit;
    }

    header('Location: index.php?module=Homepage&action=index');
    exit;
}

if (isset($_SESSION['booking_success'])) {
    unset($_SESSION['booking_success']);
}

function rupiah($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

$orderId = $booking['id'] ?? '8821';
$startDate = $booking['start_date'] ?? '2026-04-13';
$endDate = $booking['end_date'] ?? '2026-04-15';
$rentalDays = 3;
if ($booking && $booking['start_date'] && $booking['end_date']) {
    $start = date_create($booking['start_date']);
    $end = date_create($booking['end_date']);
    $interval = date_diff($start, $end);
    $rentalDays = $interval->days + 1;
}
$driverText = ($booking['addon_driver'] ?? 0) ? 'Rp 100.000' : 'Rp 0';
$totalPrice = $booking['total_price'] ?? 1155000;
$pickupLocation = $booking['pickup_location'] ?? 'Soekarno Hatta T3 International Airport';
$returnLocation = $booking['return_location'] ?? 'Sama dengan lokasi pengambilan';
$startLabel = date('j F Y', strtotime($startDate));
$endLabel = date('j F Y', strtotime($endDate));
$baseSubtotal = round($totalPrice / 1.1);
$taxAmount = round($totalPrice - $baseSubtotal);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - SewaMobil</title>
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
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            min-height: 100vh;
        }
        .barcode {
            height: 35px;
            width: 140px;
            background: repeating-linear-gradient(
                90deg,
                #1f2937,
                #1f2937 2px,
                transparent 2px,
                transparent 4px,
                #1f2937 4px,
                #1f2937 7px,
                transparent 7px,
                transparent 10px,
                #1f2937 10px,
                #1f2937 11px,
                transparent 11px,
                transparent 14px
            );
        }
        html.dark body {
            background: linear-gradient(135deg, #090d1f 0%, #030712 100%) !important;
            color: #f1f5f9 !important;
        }
        html.dark .barcode {
            background: repeating-linear-gradient(
                90deg,
                #f8fafc,
                #f8fafc 2px,
                transparent 2px,
                transparent 4px,
                #f8fafc 4px,
                #f8fafc 7px,
                transparent 7px,
                transparent 10px,
                #f8fafc 10px,
                #f8fafc 11px,
                transparent 11px,
                transparent 14px
            ) !important;
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center py-10 px-4 relative transition-colors duration-200">

    <!-- Floating theme toggle removed: theme is managed from Profile page -->

    <div class="z-10 flex flex-col items-center text-center mb-6">
        <div class="w-16 h-16 bg-blue-700 text-white rounded-full flex items-center justify-center text-3xl shadow-lg shadow-blue-700/40 mb-4">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2 tracking-tight">Reservasi Berhasil!</h1>
        <p class="text-gray-600 dark:text-slate-400 text-sm max-w-md leading-relaxed">Perjalanan Anda bersama kami akan segera dimulai. Instruksi pengambilan kendaraan telah dikirimkan ke email Anda.</p>
    </div>

    <div class="z-10 flex flex-col lg:flex-row gap-5 max-w-4xl w-full">
        
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-white/50 dark:border-slate-800 flex-1 relative overflow-hidden">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <p class="text-[0.65rem] font-bold text-blue-600 dark:text-blue-400 tracking-wider uppercase mb-1">Order ID</p>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white">#RESRV-<?= htmlspecialchars($orderId) ?></h2>
                    <div class="barcode mt-2 opacity-80"></div>
                    <p class="text-[0.6rem] text-gray-400 dark:text-slate-500 mt-1 tracking-widest"><?php echo htmlspecialchars($orderId . '004928173645'); ?></p>
                </div>
                <div class="bg-blue-600 dark:bg-blue-700 text-white text-[0.7rem] font-semibold px-3 py-1.5 rounded-full flex items-center gap-1">
                    <i class="bi bi-shield-check"></i> Verified Safety
                </div>
            </div>

            <div class="h-36 w-full mb-5 rounded-2xl overflow-hidden bg-gray-100 dark:bg-slate-800 flex items-center justify-center">
                <?php 
                    // Perbaikan Path Gambar
                    $rawImg = ($car && $car['image']) ? $car['image'] : '';
                    $carImage = !empty($rawImg) ? (filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : 'assets/images/' . $rawImg) : 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop';
                ?>
                <img src="<?= htmlspecialchars($carImage) ?>" alt="<?= htmlspecialchars($car ? ($car['make'] . ' ' . $car['model']) : 'Toyota Avanza') ?>" class="w-full h-full object-cover">
            </div>

            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($car ? ($car['make'] . ' ' . $car['model'] . ' ' . $car['year']) : 'Toyota Avanza 2024') ?></h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($car ? $car['category'] : 'MPV') ?> • <?= htmlspecialchars($car ? $car['fuel_type'] : 'Bensin') ?> • <?= htmlspecialchars($car ? $car['seats'] : '7') ?> Penumpang</p>
            </div>

            <div class="bg-gray-50 dark:bg-slate-950 rounded-xl p-4 border border-gray-100 dark:border-slate-800 space-y-4">
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-calendar2-range text-blue-600 dark:text-blue-400"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mb-0.5">Rentang Waktu Sewa</p>
                        <p class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($startLabel) ?> <span class="text-gray-400 dark:text-slate-500 mx-1">→</span> <?= htmlspecialchars($endLabel) ?></p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-gray-200 dark:bg-slate-800"></div>
                
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-geo-alt-fill text-blue-600 dark:text-blue-400"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mb-0.5">Lokasi Pengambilan</p>
                        <p class="text-xs font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($pickupLocation) ?></p>
                    </div>
                </div>
                
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-geo-fill text-blue-600 dark:text-blue-400"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mb-0.5">Lokasi Pengembalian</p>
                        <p class="text-xs font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($returnLocation) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-white/50 dark:border-slate-800 w-full lg:w-[300px] flex flex-col">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5">Ringkasan Pembayaran</h3>
            
            <div class="space-y-4 flex-grow">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-slate-400">Metode Bayar</span>
                    <span class="font-bold text-gray-900 dark:text-white">Transfer Bank</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-slate-400">Harga Sewa (<?= htmlspecialchars($rentalDays) ?> Hari)</span>
                    <span class="font-bold text-gray-900 dark:text-white"><?= rupiah($baseSubtotal) ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-slate-400">Biaya Supir</span>
                    <span class="font-bold text-gray-900 dark:text-white"><?= $driverText ?></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500 dark:text-slate-400">Pajak (10%)</span>
                    <span class="font-bold text-gray-900 dark:text-white"><?= rupiah($taxAmount) ?></span>
                </div>
                
                <hr class="border-gray-100 dark:border-slate-800 my-4">
                
                <div class="text-right">
                    <p class="text-[0.65rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Dibayar</p>
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight"><?= rupiah($totalPrice) ?></p>
                </div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-100 dark:border-yellow-900/50 rounded-xl p-3 mt-5 flex gap-3 items-start">
                <i class="bi bi-envelope-paper-fill text-yellow-500 dark:text-yellow-400 mt-0.5"></i>
                <p class="text-[0.65rem] font-medium text-yellow-800 dark:text-yellow-300 leading-relaxed">Cek kotak masuk email Anda untuk instruksi pengambilan dan detail kontak supir (jika ada).</p>
            </div>
        </div>
        
    </div>

    <div class="z-10 flex flex-col sm:flex-row gap-3 mt-8 w-full max-w-md justify-center">
        <a href="index.php?module=Homepage&action=index" class="bg-blue-700 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-blue-800 transition text-center text-sm">
            Lihat Riwayat Pesanan
        </a>
        <a href="index.php?module=Homepage&action=index" class="bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-400 border-2 border-blue-700 dark:border-blue-500 px-6 py-3 rounded-full font-bold hover:bg-blue-50 dark:hover:bg-slate-800 transition text-center text-sm">
            Kembali ke Beranda
        </a>
    </div>

    <script>
        function updateGlobalNavbarIcon() {
            const themeIcon = document.getElementById('global-theme-icon');
            if (themeIcon) {
                if (document.documentElement.classList.contains('dark')) {
                    themeIcon.className = 'bi bi-sun-fill text-lg';
                } else {
                    themeIcon.className = 'bi bi-moon-fill text-lg';
                }
            }
        }

        function toggleGlobalTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
            updateGlobalNavbarIcon();
        }

        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                if (e.newValue === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                }
                updateGlobalNavbarIcon();
            }
        });

        // Initialize theme icon on load
        updateGlobalNavbarIcon();
    </script>
</body>
</html>