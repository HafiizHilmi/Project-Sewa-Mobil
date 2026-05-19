<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Berhasil - SewaMobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="flex flex-col items-center justify-center py-10 px-4 relative">

    <div class="z-10 flex flex-col items-center text-center mb-6">
        <div class="w-16 h-16 bg-blue-700 text-white rounded-full flex items-center justify-center text-3xl shadow-lg shadow-blue-700/40 mb-4">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2 tracking-tight">Reservasi Berhasil!</h1>
        <p class="text-gray-600 text-sm max-w-md leading-relaxed">Perjalanan Anda bersama kami akan segera dimulai. Instruksi pengambilan kendaraan telah dikirimkan ke email Anda.</p>
    </div>

    <div class="z-10 flex flex-col lg:flex-row gap-5 max-w-4xl w-full">
        
        <div class="bg-white rounded-3xl p-6 shadow-xl border border-white/50 flex-1 relative overflow-hidden">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <p class="text-[0.65rem] font-bold text-blue-600 tracking-wider uppercase mb-1">Order ID</p>
                    <h2 class="text-xl font-black text-gray-900">#RESRV-8821</h2>
                    <div class="barcode mt-2 opacity-80"></div>
                    <p class="text-[0.6rem] text-gray-400 mt-1 tracking-widest">8821004928173645</p>
                </div>
                <div class="bg-blue-600 text-white text-[0.7rem] font-semibold px-3 py-1.5 rounded-full flex items-center gap-1">
                    <i class="bi bi-shield-check"></i> Verified Safety
                </div>
            </div>

            <div class="h-36 w-full mb-5 rounded-2xl overflow-hidden bg-gray-100 flex items-center justify-center">
                <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Toyota Avanza" class="w-full h-full object-cover">
            </div>

            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900">Toyota Avanza 2024</h3>
                <p class="text-xs text-gray-500 mt-0.5">MPV • Bensin • 7 Penumpang</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-4">
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-calendar2-range text-blue-600"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Rentang Waktu Sewa</p>
                        <p class="text-xs font-bold text-gray-900">13 April 2026 <span class="text-gray-400 mx-1">→</span> 15 April 2026</p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-gray-200"></div>
                
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-geo-alt-fill text-blue-600"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Lokasi Pengambilan</p>
                        <p class="text-xs font-semibold text-gray-900">Soekarno Hatta T3 International Airport</p>
                    </div>
                </div>
                
                <div class="flex gap-3 items-start">
                    <div class="mt-0.5 w-6 flex justify-center"><i class="bi bi-geo-fill text-blue-600"></i></div>
                    <div>
                        <p class="text-[0.65rem] text-gray-500 font-bold uppercase tracking-wider mb-0.5">Lokasi Pengembalian</p>
                        <p class="text-xs font-semibold text-gray-900">Sama dengan lokasi pengambilan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-xl border border-white/50 w-full lg:w-[300px] flex flex-col">
            <h3 class="text-base font-bold text-gray-900 mb-5">Ringkasan Pembayaran</h3>
            
            <div class="space-y-4 flex-grow">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Metode Bayar</span>
                    <span class="font-bold text-gray-900">Transfer Bank</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Harga Sewa (3 Hari)</span>
                    <span class="font-bold text-gray-900">Rp 1.050.000</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Biaya Supir</span>
                    <span class="font-bold text-gray-900">Rp 0</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Pajak (10%)</span>
                    <span class="font-bold text-gray-900">Rp 105.000</span>
                </div>
                
                <hr class="border-gray-100 my-4">
                
                <div class="text-right">
                    <p class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Dibayar</p>
                    <p class="text-2xl font-black text-blue-600 tracking-tight">Rp 1.155.000</p>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3 mt-5 flex gap-3 items-start">
                <i class="bi bi-envelope-paper-fill text-yellow-500 mt-0.5"></i>
                <p class="text-[0.65rem] font-medium text-yellow-800 leading-relaxed">Cek kotak masuk email Anda untuk instruksi pengambilan dan detail kontak supir (jika ada).</p>
            </div>
        </div>
        
    </div>

    <div class="z-10 flex flex-col sm:flex-row gap-3 mt-8 w-full max-w-md justify-center">
        <a href="index.php?module=Homepage&action=index" class="bg-blue-700 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-blue-800 transition text-center text-sm">
            Lihat Riwayat Pesanan
        </a>
        <a href="index.php?module=Homepage&action=index" class="bg-white text-blue-700 border-2 border-blue-700 px-6 py-3 rounded-full font-bold hover:bg-blue-50 transition text-center text-sm">
            Kembali ke Beranda
        </a>
    </div>

</body>
</html>