<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../../include/db_config.php';
require_once __DIR__ . '/../../../../include/api_keys.php';
$pdo = getPDO();

$booking = $_SESSION['booking_pending'] ?? null;
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : ($booking['id'] ?? null);

if (!$bookingId || !$pdo) {
    header('Location: index.php?module=Homepage&action=index');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT b.*, c.make, c.model, c.year, c.category, c.fuel_type, c.seats, COALESCE((SELECT p.image FROM cars p WHERE p.type_key = c.type_key AND p.is_type = 1 AND p.image != '' LIMIT 1), c.image) AS image FROM bookings b LEFT JOIN cars c ON c.id = b.car_id WHERE b.id = :id LIMIT 1"
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

function rupiah($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

$orderId = $bookingRow['id'];
$startDate = $bookingRow['start_date'];
$endDate = $bookingRow['end_date'];
$rentalDays = 3;
if ($bookingRow['start_date'] && $bookingRow['end_date']) {
    $start = date_create($bookingRow['start_date']);
    $end = date_create($bookingRow['end_date']);
    $interval = date_diff($start, $end);
    $rentalDays = $interval->days + 1;
}
$driverText = ($bookingRow['addon_driver'] ?? 0) ? 'Rp 100.000' : 'Rp 0';
$totalPrice = $bookingRow['total_price'] ?? 1155000;
$pickupLocation = $bookingRow['pickup_location'] ?? '';
$returnLocation = $bookingRow['return_location'] ?: 'Sama dengan lokasi pengambilan';
$startLabel = date('j F Y', strtotime($startDate));
$endLabel = date('j F Y', strtotime($endDate));
$baseSubtotal = round($totalPrice / 1.1);
$taxAmount = round($totalPrice - $baseSubtotal);

$carName = htmlspecialchars(trim(($bookingRow['make'] ?? '') . ' ' . ($bookingRow['model'] ?? '') . ' ' . ($bookingRow['year'] ?? '')));
$rawImg = $bookingRow['image'] ?? '';
$carImage = !empty($rawImg) ? (filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : 'assets/images/' . $rawImg) : 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop';
$carImage = htmlspecialchars($carImage);

$serverKey = MIDTRANS_SERVER_KEY;
$isProduction = defined('MIDTRANS_IS_PRODUCTION') ? MIDTRANS_IS_PRODUCTION : false;
$methodLabel = $isProduction ? 'Midtrans' : 'Midtrans Sandbox';
$snapScriptUrl = $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
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
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
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
    <!-- Midtrans Snap JS -->
    <script type="text/javascript" src="<?= $snapScriptUrl ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
</head>
<body class="flex flex-col items-center justify-center py-10 px-4 relative transition-colors duration-200">
 
    <div class="z-10 flex flex-col items-center text-center mb-6">
        <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-3xl shadow-lg shadow-blue-500/40 mb-4 animate-pulse">
            <i class="bi bi-credit-card-2-front"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2 tracking-tight">Selesaikan Pembayaran Anda!</h1>
        <p class="text-gray-600 dark:text-slate-400 text-sm max-w-md leading-relaxed">Pesanan Anda berhasil dikirim. Silakan selesaikan pembayaran Anda via <?= $methodLabel ?> sebelum batas waktu pembayaran berakhir.</p>
    </div>
 
    <!-- TAMPILKAN ERROR MIDTRANS JIKA ADA -->
    <?php if (isset($_SESSION['midtrans_error'])): ?>
        <div class="z-10 w-full max-w-4xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-2xl p-4 mb-5 flex gap-3 items-start text-left shadow-sm">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 dark:text-rose-500 mt-0.5 flex-shrink-0 text-lg"></i>
            <div>
                <h4 class="text-sm font-bold text-rose-800 dark:text-rose-300">Gagal Menghubungkan ke Payment Gateway</h4>
                <p class="text-xs text-rose-700 dark:text-rose-450 leading-relaxed mt-1 font-mono bg-white/50 dark:bg-black/30 p-2 rounded border border-rose-100 dark:border-rose-950/50">
                    <?= htmlspecialchars($_SESSION['midtrans_error']) ?>
                </p>
                <p class="text-[0.65rem] text-rose-650 dark:text-rose-500 font-semibold mt-2">
                    Tip: Periksa kembali pengaturan **SERVER_KEY** dan **CLIENT_KEY** di file <code class="bg-rose-100 dark:bg-rose-950 px-1 py-0.5 rounded text-[0.6rem]">include/api_keys.php</code>. Pastikan tipe key cocok (Production key untuk mode Production, Sandbox key untuk mode Sandbox).
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="z-10 flex flex-col lg:flex-row gap-5 max-w-4xl w-full">
        
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-white/50 dark:border-slate-800 flex-1 relative overflow-hidden">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <p class="text-[0.65rem] font-bold text-amber-600 dark:text-amber-400 tracking-wider uppercase mb-1">Order ID</p>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white">#RESRV-<?= htmlspecialchars($orderId) ?></h2>
                    
                    <div class="mt-4">
                        <p class="text-[0.65rem] font-bold text-red-500 dark:text-red-400 tracking-wider uppercase mb-0.5">Sisa Waktu Pembayaran</p>
                        <div id="payment-countdown-timer" class="text-3xl font-black text-amber-500 tracking-tight">15:00</div>
                    </div>
                </div>
                <div class="bg-blue-600 text-white text-[0.7rem] font-semibold px-3 py-1.5 rounded-full flex items-center gap-1">
                    <i class="bi bi-clock-history"></i> Awaiting Payment
                </div>
            </div>
 
            <div class="h-36 w-full mb-5 rounded-2xl overflow-hidden bg-gray-100 dark:bg-slate-800 flex items-center justify-center">
                <img src="<?= $carImage ?>" alt="<?= $carName ?>" class="w-full h-full object-cover">
            </div>
 
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= $carName ?></h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($bookingRow['category'] ?? 'MPV') ?> • <?= htmlspecialchars($bookingRow['fuel_type'] ?? 'Bensin') ?> • <?= htmlspecialchars($bookingRow['seats'] ?? '7') ?> Penumpang</p>
            </div>
 
            <div class="bg-gray-50 dark:bg-slate-950 rounded-xl p-4 border border-gray-100 dark:border-slate-800 space-y-4">
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-calendar2-range text-blue-600 dark:text-blue-450"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mb-0.5">Rentang Waktu Sewa</p>
                        <p class="text-xs font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($startLabel) ?> <span class="text-gray-400 dark:text-slate-500 mx-1">→</span> <?= htmlspecialchars($endLabel) ?></p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-gray-200 dark:bg-slate-800"></div>
                
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-geo-alt-fill text-blue-600 dark:text-blue-450"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mb-0.5">Lokasi Pengambilan</p>
                        <p class="text-xs font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($pickupLocation) ?></p>
                    </div>
                </div>
                
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-geo-fill text-blue-600 dark:text-blue-450"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider mb-0.5">Lokasi Pengembalian</p>
                        <p class="text-xs font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($returnLocation) ?></p>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-white/50 dark:border-slate-800 w-full lg:w-[300px] flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-5">Estimasi Pembayaran</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 dark:text-slate-400">Metode Bayar</span>
                        <span class="font-bold text-gray-900 dark:text-white"><?= $methodLabel ?></span>
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
                    
                    <div class="text-right mb-4">
                        <p class="text-[0.65rem] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Total Pembayaran</p>
                        <p class="text-2xl font-black text-blue-600 dark:text-blue-450 tracking-tight"><?= rupiah($totalPrice) ?></p>
                    </div>
                </div>
            </div>
 
            <div>
                <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/50 rounded-xl p-3 flex gap-3 items-start text-left mb-4">
                    <i class="bi bi-info-circle-fill text-blue-600 dark:text-blue-450 mt-0.5 flex-shrink-0"></i>
                    <p class="text-[0.65rem] font-medium text-blue-800 dark:text-blue-300 leading-relaxed">Pembayaran instan dan otomatis terverifikasi secara real-time melalui jaringan <?= $methodLabel ?>.</p>
                </div>
                
                <button id="pay-button" class="w-full bg-gradient-to-r from-blue-600 to-indigo-650 hover:from-blue-750 hover:to-indigo-750 text-white py-3 rounded-full font-bold shadow-lg shadow-blue-600/30 transition duration-200 text-center text-xs flex items-center justify-center gap-2 animate-bounce">
                    <i class="bi bi-wallet2"></i> BAYAR SEKARANG
                </button>
            </div>
        </div>
        
    </div>
 
    <div class="z-10 flex flex-col sm:flex-row gap-3 mt-8 w-full max-w-md justify-center">
        <a href="index.php?module=Profile&action=index#riwayat" class="bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-slate-800 px-6 py-3 rounded-full font-bold shadow-sm hover:bg-gray-50 dark:hover:bg-slate-800 transition text-center text-sm">
            Lihat Riwayat Pesanan
        </a>
        <a href="index.php?module=Homepage&action=index" class="bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-gray-200 dark:border-slate-800 px-6 py-3 rounded-full font-bold hover:bg-gray-50 dark:hover:bg-slate-800 transition text-center text-sm">
            Kembali ke Beranda
        </a>
    </div>
 
    <script>
        // --- REAL-TIME COUNTDOWN TIMER ---
        const expiryTime = new Date("<?= str_replace(' ', 'T', $bookingRow['payment_expiry'] ?? '') ?>").getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = expiryTime - now;
            
            const timerElement = document.getElementById('payment-countdown-timer');
            if (!timerElement) return;
            
            if (distance < 0) {
                timerElement.innerHTML = "WAKTU HABIS";
                timerElement.classList.add('text-red-500');
                // Otomatis refresh agar BookingController.php memproses pembatalan di server
                window.location.reload();
                return;
            }
            
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const minStr = String(minutes).padStart(2, '0');
            const secStr = String(seconds).padStart(2, '0');
            
            timerElement.innerHTML = `${minStr}:${secStr}`;
            
            if (minutes < 3) {
                timerElement.classList.remove('text-amber-500', 'text-gray-900', 'dark:text-white');
                timerElement.classList.add('text-red-500', 'animate-pulse');
            } else {
                timerElement.classList.remove('text-red-500', 'animate-pulse');
                timerElement.classList.add('text-amber-500');
            }
        }
        
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // --- INSTANT CALLBACK MIDTRANS SNAP POPUP ---
        const paymentToken = "<?= $bookingRow['payment_token'] ?? '' ?>";
        const bookingId = "<?= $bookingId ?>";
        
        document.getElementById('pay-button').addEventListener('click', function() {
            if (!paymentToken) {
                alert('Token pembayaran tidak ditemukan atau telah kedaluwarsa.');
                return;
            }
            window.snap.pay(paymentToken, {
                onSuccess: function(result){
                    window.location.href = 'index.php?module=Booking&action=success&booking_id=' + bookingId;
                },
                onPending: function(result){
                    console.log('Payment pending:', result);
                },
                onError: function(result){
                    window.location.href = 'index.php?module=Booking&action=rejected&booking_id=' + bookingId;
                },
                onClose: function(){
                    console.log('Customer closed the payment popup');
                }
            });
        });

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
 
        updateGlobalNavbarIcon();
    </script>
</body>
</html>
