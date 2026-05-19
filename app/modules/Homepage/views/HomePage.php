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
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-1">
                <span class="text-blue-700 font-extrabold text-2xl tracking-tight">Sewa<span class="text-gray-900">Mobil</span></span>
            </div>
            
            <div class="relative w-full max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" placeholder="Cari mobil, lokasi..." class="w-full pl-10 pr-12 py-3 bg-gray-100 rounded-full border-none focus:ring-2 focus:ring-blue-100 placeholder:text-gray-500">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-gray-500 hover:text-gray-700">
                    <i class="bi bi-sliders h-5 w-5"></i>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                        <i class="bi bi-person-circle text-xl"></i>
                    </div><span class="font-medium">Halo, <?= isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]) : 'Tamu' ?></span><span class="font-medium">Halo, <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Tamu' ?></span>
                </div>
                
                <div class="border-l border-gray-300 pl-3 ml-1">
                    <a href="index.php?module=Auth&action=logout" class="flex items-center gap-1 text-red-500 hover:text-red-700 font-medium transition text-sm">
                        <i class="bi bi-box-arrow-right text-lg"></i> 
                        <span class="hidden md:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 space-y-10 py-10">
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

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-48 object-cover rounded-2xl mb-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">Toyota Avanza 2024</h3>
                        <p class="text-gray-500 font-medium">MPV</p>
                    </div>  
                    <div class="text-right">
                        <p class="text-xl font-bold text-blue-700">Rp350.000 <span class="text-sm font-normal text-gray-500">/hari</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">Bensin</span>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">7 Penumpang</span>
                </div>
                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition">Sewa Sekarang</button>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-48 object-cover rounded-2xl mb-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">Toyota Avanza 2024</h3>
                        <p class="text-gray-500 font-medium">MPV</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-blue-700">Rp350.000 <span class="text-sm font-normal text-gray-500">/hari</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">Bensin</span>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">7 Penumpang</span>
                </div>
                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition">Sewa Sekarang</button>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-48 object-cover rounded-2xl mb-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">Toyota Avanza 2024</h3>
                        <p class="text-gray-500 font-medium">MPV</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-blue-700">Rp350.000 <span class="text-sm font-normal text-gray-500">/hari</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">Bensin</span>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">7 Penumpang</span>
                </div>
                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition">Sewa Sekarang</button>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-48 object-cover rounded-2xl mb-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">Toyota Avanza 2024</h3>
                        <p class="text-gray-500 font-medium">MPV</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-blue-700">Rp350.000 <span class="text-sm font-normal text-gray-500">/hari</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">Bensin</span>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">7 Penumpang</span>
                </div>
                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition">Sewa Sekarang</button>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-48 object-cover rounded-2xl mb-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">Toyota Avanza 2024</h3>
                        <p class="text-gray-500 font-medium">MPV</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-blue-700">Rp350.000 <span class="text-sm font-normal text-gray-500">/hari</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">Bensin</span>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">7 Penumpang</span>
                </div>
                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition">Sewa Sekarang</button>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-2xl hover:border-gray-200 transition-all duration-300">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-48 object-cover rounded-2xl mb-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">Toyota Avanza 2024</h3>
                        <p class="text-gray-500 font-medium">MPV</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-blue-700">Rp350.000 <span class="text-sm font-normal text-gray-500">/hari</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-6">
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">Bensin</span>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium">7 Penumpang</span>
                </div>
                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition">Sewa Sekarang</button>
            </div>

        </section>
    </main>

    <?php include __DIR__ . '/../../../../include/footer.html'; ?>

</body>
</html>