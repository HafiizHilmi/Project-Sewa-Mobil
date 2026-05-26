<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaMobil - Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .car-grid > div.car-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1 1 auto;
        }

        .car-title {
            word-break: break-word; 
            min-height: 3rem; 
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
    <?php $cars = $cars ?? []; ?>

    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 relative flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center justify-between w-full md:w-auto">
                <span class="text-blue-700 font-extrabold text-2xl tracking-tight">Sewa<span class="text-gray-900">Mobil</span></span>
            </div>
            
            <div class="w-full md:w-[450px] lg:w-[550px] md:absolute md:left-1/2 md:-translate-x-1/2 order-last md:order-none z-10">
                <form action="index.php" method="GET" class="relative">
                    <input type="hidden" name="module" value="Homepage">
                    <input type="hidden" name="action" value="index">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="bi bi-search"></i>    
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Cari mobil, ukuran, kategori..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-gray-100 rounded-full border-none text-sm sm:text-base focus:ring-2 focus:ring-blue-100 placeholder:text-gray-500">
                </form>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?module=Profile&action=index" class="flex items-center gap-2 group cursor-pointer no-underline overflow-hidden">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-50 group-hover:bg-blue-100 transition flex items-center justify-center text-blue-600 shrink-0">
                        <i class="bi bi-person-circle text-lg sm:text-xl"></i>
                    </div>
                    <span class="font-medium text-gray-800 group-hover:text-blue-600 text-sm sm:text-base transition truncate max-w-[120px]">
                        Halo, <?= htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]) ?>
                    </span>
                </a>
                <?php else: ?>
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 shrink-0">
                        <i class="bi bi-person-circle text-lg sm:text-xl"></i>
                    </div>
                    <span class="font-medium text-sm sm:text-base">Halo, Tamu</span>
                </div>
                <?php endif; ?>
                
                <div class="border-l border-gray-300 pl-3 ml-1 shrink-0">
                    <a href="index.php?module=Auth&action=logout" class="flex items-center gap-1 text-red-500 hover:text-red-700 font-medium transition text-sm no-underline">
                        <i class="bi bi-box-arrow-right text-lg"></i> 
                        <span class="hidden sm:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow w-full max-w-6xl mx-auto px-4 md:px-6 pt-10 pb-40 space-y-10 md:space-y-12">
        
        <?php if(isset($_SESSION['user_id']) && isset($verification_status) && in_array($verification_status, ['unverified', 'rejected'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl shadow-sm flex items-start gap-3 mb-6" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-xl mt-0.5"></i>
            <div>
                <strong class="block mb-1 font-bold">Identitas Belum Lengkap!</strong>
                <p class="text-sm">Identitas Anda belum lengkap atau ditolak. Silakan <a href="index.php?module=Profile&action=index" class="underline font-semibold hover:text-red-600">lengkapi di sini</a> untuk menyewa mobil.</p>
            </div>
        </div>
        <?php endif; ?>

        <header class="space-y-3">
            <h1 class="text-4xl md:text-5xl font-bold tracking-tighter">Cari yang terbaik</h1>
            <p class="text-lg md:text-xl text-gray-600">Pilih mobil terbaik yang kami sediakan untukmu.</p>
        </header>

        <section class="flex flex-wrap items-center gap-3">
            <button class="bg-blue-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-blue-700 transition">Semua Mobil</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">SUV</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">MPV</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">Sedan</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">EV</button>
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6 car-grid mb-16">
            <?php if (!empty($_GET['search'])): ?>
                <div class="col-span-full rounded-xl bg-blue-50 border border-blue-100 px-5 py-3 text-sm text-blue-700">
                    Menampilkan <?= count($cars ?? []) ?> hasil untuk "<?= htmlspecialchars($_GET['search']) ?>".
                </div>
            <?php endif; ?>

            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $car): ?>
                    <?php 
                        $carName = htmlspecialchars(trim($car['make'] . ' ' . $car['model'] . ' ' . $car['year'])); 
                        $carCategory = htmlspecialchars($car['category'] ?: 'Lainnya'); 
                        $carFuel = htmlspecialchars($car['fuel_type'] ?: '-'); 
                        $carSeats = htmlspecialchars($car['seats'] ?: '-'); 
                        $carPrice = number_format($car['price_per_day'], 0, ',', '.'); 
                        
                        // Perbaikan Logic Gambar
                        $rawImg = $car['image'] ?? '';
                        if (!empty($rawImg)) {
                            $carImage = filter_var($rawImg, FILTER_VALIDATE_URL) ? $rawImg : 'assets/images/' . $rawImg;
                        } else {
                            $carImage = 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop';
                        }
                    ?>
                    
                    <div class="bg-white rounded-3xl p-5 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300 car-card">
                        <img src="<?= htmlspecialchars($carImage) ?>" alt="<?= $carName ?>" class="w-full h-40 object-cover rounded-2xl mb-4">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex-1 pr-1">
                                    <h3 class="text-lg font-bold tracking-tight car-title"><?= $carName ?></h3>
                                    <p class="text-sm text-gray-500 font-medium"><?= $carCategory ?></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-lg font-bold text-blue-700">Rp<?= $carPrice ?> <span class="text-xs font-normal text-gray-500 block">/hari</span></p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 mb-6">
                                <span class="bg-gray-100 text-gray-600 px-2.5 py-1.5 rounded-full text-xs font-medium"><?= $carFuel ?></span>
                                <span class="bg-gray-100 text-gray-600 px-2.5 py-1.5 rounded-full text-xs font-medium"><?= $carSeats ?> Kursi</span>
                            </div>
                        </div>
                        <a href="index.php?module=Booking&action=checkout&car_id=<?= htmlspecialchars($car['id']) ?>" class="block w-full text-center bg-blue-600 text-white py-3 rounded-xl font-semibold text-sm hover:bg-blue-700 transition no-underline">Sewa Sekarang</a>
                    </div>
                <?php endforeach; ?>
                
                <div id="empty-state-js" class="col-span-full p-12 bg-white rounded-3xl border border-gray-200 text-center" style="display: none;">
                    <i class="bi bi-car-front text-5xl text-gray-300 mb-3 block"></i>
                    <p class="text-xl font-semibold mb-2">Tidak ada produk</p>
                    <p class="text-gray-500">Mohon maaf, mobil untuk kategori ini sedang kosong.</p>
                </div>

            <?php else: ?>
                <div class="col-span-full p-12 bg-white rounded-3xl border border-gray-200 text-center">
                    <i class="bi bi-search text-5xl text-gray-300 mb-3 block"></i>
                    <p class="text-xl font-semibold mb-2">Tidak ada produk</p>
                    <p class="text-gray-500">Coba kata kunci pencarian lain atau kembali lagi nanti.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . '/../../../../include/footer.html'; ?>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const filterButtons = document.querySelectorAll("section.flex-wrap button");
        const carCards = document.querySelectorAll("section.car-grid > div.car-card");
        const emptyStateJs = document.getElementById("empty-state-js");

        filterButtons.forEach(button => {
            button.addEventListener("click", () => {
                filterButtons.forEach(btn => {
                    btn.className = "bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition";
                });
                
                button.className = "bg-blue-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-blue-700 transition";

                const filterText = button.textContent.trim().toUpperCase();
                let visibleCount = 0;

                carCards.forEach(card => {
                    if (filterText === "SEMUA MOBIL") {
                        card.style.display = "";
                        visibleCount++;
                    } else {
                        const categoryEl = card.querySelector("p.text-gray-500.font-medium");
                        if (categoryEl && categoryEl.textContent.trim().toUpperCase() === filterText) {
                            card.style.display = "";
                            visibleCount++;
                        } else {
                            card.style.display = "none";
                        }
                    }
                });

                if (emptyStateJs) {
                    if (visibleCount === 0) {
                        emptyStateJs.style.display = "block";
                    } else {
                        emptyStateJs.style.display = "none";
                    }
                }
            });
        });
    });
    </script>
</body>
</html>