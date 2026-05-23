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

        .car-grid > div {
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
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
            min-height: 4rem; 
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php $cars = $cars ?? []; ?>

    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-1">
                <span class="text-blue-700 font-extrabold text-2xl tracking-tight">Sewa<span class="text-gray-900">Mobil</span></span>
            </div>
            
            <div class="relative w-full max-w-xl">
                <form action="index.php" method="GET" class="relative">
                    <input type="hidden" name="module" value="Homepage">
                    <input type="hidden" name="action" value="index">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="bi bi-search"></i>    
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Cari mobil, ukuran, kategori..." class="w-full pl-10 pr-12 py-3 bg-gray-100 rounded-full border-none focus:ring-2 focus:ring-blue-100 placeholder:text-gray-500">
                </form>
            </div>

            <div class="flex items-center gap-3">
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?module=Profile&action=index" class="flex items-center gap-2 group cursor-pointer no-underline">
                    <div class="w-10 h-10 rounded-full bg-blue-50 group-hover:bg-blue-100 transition flex items-center justify-center text-blue-600 shrink-0">
                        <i class="bi bi-person-circle text-xl"></i>
                    </div>
                    <span class="font-medium text-gray-800 group-hover:text-blue-600 transition relative after:absolute after:bottom-0 after:left-0 after:h-[1px] after:w-0 after:bg-blue-600 after:transition-all after:duration-300 group-hover:after:w-full">Halo, <?= htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]) ?></span>
                </a>
                <?php else: ?>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <i class="bi bi-person-circle text-xl"></i>
                    </div>
                    <span class="font-medium">Halo, Tamu</span>
                </div>
                <?php endif; ?>
                
                <div class="border-l border-gray-300 pl-3 ml-1">
                    <a href="index.php?module=Auth&action=logout" class="flex items-center gap-1 text-red-500 hover:text-red-700 font-medium transition text-sm no-underline">
                        <i class="bi bi-box-arrow-right text-lg"></i> 
                        <span class="hidden md:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 space-y-10 py-10">
        
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
            <h1 class="text-5xl font-bold tracking-tighter">Cari yang terbaik</h1>
            <p class="text-xl text-gray-600">Pilih mobil terbaik yang kami sediakan untukmu.</p>
        </header>

        <section class="flex items-center gap-3">
            <button class="bg-blue-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-blue-700 transition">Semua Mobil</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">SUV</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">MPV</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">Sedan</button>
            <button class="bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition">EV</button>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 car-grid">
            <?php if (!empty($_GET['search'])): ?>
                <div class="col-span-full rounded-xl bg-blue-50 border border-blue-100 px-5 py-3 text-sm text-blue-700">
                    Menampilkan <?= count($cars ?? []) ?> hasil untuk "<?= htmlspecialchars($_GET['search']) ?>".
                </div>
            <?php endif; ?>
            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $car): ?>
                    <?php $carName = htmlspecialchars(trim($car['make'] . ' ' . $car['model'] . ' ' . $car['year'])); ?>
                    <?php $carCategory = htmlspecialchars($car['category'] ?: 'Lainnya'); ?>
                    <?php $carFuel = htmlspecialchars($car['fuel_type'] ?: '-'); ?>
                    <?php $carSeats = htmlspecialchars($car['seats'] ?: '-'); ?>
                    <?php $carPrice = number_format($car['price_per_day'], 0, ',', '.'); ?>
                    <?php $carImage = htmlspecialchars($car['image'] ?: 'https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop'); ?>
                    <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300 car-card">
                        <img src="<?= $carImage ?>" alt="<?= $carName ?>" class="w-full h-48 object-cover rounded-2xl mb-5">
                        <div class="card-body">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div>
                                    <h3 class="text-2xl font-bold tracking-tight car-title"><?= $carName ?></h3>
                                    <p class="text-gray-500 font-medium"><?= $carCategory ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-blue-700">Rp<?= $carPrice ?> <span class="text-sm font-normal text-gray-500">/hari</span></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mb-6">
                                <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium"><?= $carFuel ?></span>
                                <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium"><?= $carSeats ?> Penumpang</span>
                            </div>
                        </div>
                        <a href="index.php?module=Booking&action=checkout" class="block w-full text-center bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition no-underline">Sewa Sekarang</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full p-12 bg-white rounded-3xl border border-gray-200 text-center">
                    <p class="text-xl font-semibold mb-2">Tidak ada mobil yang ditemukan.</p>
                    <p class="text-gray-500">Coba kata kunci lain atau hapus teks pencarian.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . '/../../../../include/footer.html'; ?>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const filterButtons = document.querySelectorAll("section.flex.items-center.gap-3 button");
        const carCards = document.querySelectorAll("section.car-grid > div.car-card");

        filterButtons.forEach(button => {
            button.addEventListener("click", () => {
                
                filterButtons.forEach(btn => {
                    btn.className = "bg-white text-gray-800 px-6 py-3 rounded-full font-medium hover:bg-gray-100 border border-gray-200 transition";
                });
                
                button.className = "bg-blue-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-blue-700 transition";

                const filterText = button.textContent.trim().toUpperCase();

                carCards.forEach(card => {
                    if (filterText === "SEMUA MOBIL") {
                        card.style.display = "";
                    } else {
                        const categoryEl = card.querySelector("p.text-gray-500.font-medium");
                        if (categoryEl && categoryEl.textContent.trim().toUpperCase() === filterText) {
                            card.style.display = "";
                        } else {
                            card.style.display = "none";
                        }
                    }
                });
            });
        });
    });
    </script>
</body>
</html>