<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Kendaraan - SewaMobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* KALENDER CSS FIX */
        .date-cell {
            cursor: pointer;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            font-size: 0.875rem;
            color: #4b5563;
        }
        .date-cell:hover:not(.empty-cell):not(.bg-gray-100) {
            background-color: #f3f4f6;
            border-radius: 9999px;
        }
        
        .date-cell.selected {
            color: white;
            font-weight: 600;
        }
        .date-cell.selected::before {
            content: '';
            position: absolute;
            top: 2px;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 36px;
            background-color: #004aad;
            border-radius: 50%;
            z-index: -1;
        }
        
        .date-cell.in-range {
            color: #1e3a8a;
        }
        .date-cell.in-range::before {
            content: '';
            position: absolute;
            top: 2px;
            bottom: 2px;
            left: 0;
            right: 0;
            background-color: #bfdbfe;
            z-index: -2;
        }

        .date-cell.start-date::after {
            content: '';
            position: absolute;
            top: 2px;
            bottom: 2px;
            left: 50%;
            right: 0;
            background-color: #bfdbfe;
            z-index: -3;
        }
        
        .date-cell.end-date::after {
            content: '';
            position: absolute;
            top: 2px;
            bottom: 2px;
            left: 0;
            right: 50%;
            background-color: #bfdbfe;
            z-index: -3;
        }

        .input-floating-label {
            position: relative;
            padding: 12px 16px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
        }
        .input-floating-label label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 2px;
        }
        .input-floating-label input {
            width: 100%;
            background: transparent;
            border: none;
            font-size: 0.875rem;
            color: #1f2937;
            outline: none;
        }
        .input-floating-label input::placeholder {
            color: #9ca3af;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-white border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between relative">
            <a href="index.php" class="text-gray-800 hover:text-blue-600 font-semibold absolute left-6 flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h1 class="text-lg font-bold w-full text-center">Reservasi Kendaraan</h1>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h2 class="text-xl font-bold">Detail Lokasi</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-floating-label flex items-center justify-between">
                            <div class="w-full">
                                <label>Lokasi Pengambilan</label>
                                <input type="text" id="input-pickup" placeholder="Contoh: Soekarno Hatta T3..." autocomplete="off">
                            </div>
                            <i class="bi bi-crosshair text-gray-400"></i>
                        </div>
                        <div class="input-floating-label">
                            <label>Lokasi Pengembalian</label>
                            <input type="text" placeholder="Sama dengan lokasi pengambilan" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h2 class="text-xl font-bold">Detail Personal</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-floating-label">
                            <label>Nama Lengkap</label>
                            <input type="text" placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="input-floating-label">
                            <label>Alamat Email</label>
                            <input type="email" placeholder="contoh@email.com">
                        </div>
                        <div class="input-floating-label">
                            <label>Nomor HP</label>
                            <input type="tel" placeholder="+62 812-xxxx-xxxx">
                        </div>
                        <div class="input-floating-label">
                            <label>Alamat Lengkap</label>
                            <input type="text" placeholder="Jl. Veteran...">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold leading-tight">Pilih Tanggal</h2>
                                <p class="text-sm text-gray-500">Cek ketersediaan kendaraan.</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-full px-4 py-2 min-w-[200px]">
                            <button class="text-gray-600 hover:text-blue-600" id="prev-month"><i class="bi bi-chevron-left"></i></button>
                            <span class="font-semibold text-sm" id="current-month-year"></span>
                            <button class="text-gray-600 hover:text-blue-600" id="next-month"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 text-center mb-2 font-medium text-gray-500 text-sm">
                        <div class="pb-2">Min</div>
                        <div class="pb-2">Sen</div>
                        <div class="pb-2">Sel</div>
                        <div class="pb-2">Rab</div>
                        <div class="pb-2">Kam</div>
                        <div class="pb-2">Jum</div>
                        <div class="pb-2">Sab</div>
                    </div>
                    <div class="grid grid-cols-7 text-center" id="calendar-grid"></div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg">
                            <i class="bi bi-nut-fill"></i>
                        </div>
                        <h2 class="text-xl font-bold">Pilih Layanan Tambahan</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="addon-card border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-300 transition" data-price="100000" onclick="selectAddon(this)">
                            <h3 class="font-bold text-sm">Dengan Driver</h3>
                            <p class="text-xs text-gray-500 mb-4">Sewa mobil menggunakan supir.</p>
                            <p class="text-xs font-semibold text-gray-500">+Rp 100.000 /hari</p>
                        </div>
                        <div class="addon-card border-2 border-blue-600 bg-blue-50/30 rounded-xl p-4 cursor-pointer transition active-addon" data-price="0" onclick="selectAddon(this)">
                            <h3 class="font-bold text-sm">Tanpa Supir</h3>
                            <p class="text-xs text-gray-500 mb-4">Sewa mobil tanpa menggunakan supir.</p>
                            <p class="text-xs font-semibold text-gray-500">+Rp 0 /hari</p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                    <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1629897048514-3dd74143275d?q=80&w=600&auto=format&fit=crop" alt="Car" class="w-full h-full object-cover">
                    </div>
                    
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-1">Toyota Avanza 2024</h3>
                        <p class="text-sm text-gray-500 mb-6">MPV • Bensin • 7 Penumpang</p>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex gap-4">
                                <i class="bi bi-calendar3 mt-0.5 text-gray-600"></i>
                                <div>
                                    <p class="text-sm font-medium" id="summary-dates">Belum pilih tanggal</p>
                                    <p class="text-xs text-gray-400" id="summary-days">0 Hari</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <i class="bi bi-geo-alt mt-0.5 text-gray-600"></i>
                                <div>
                                    <p class="text-sm font-medium" id="summary-location">Lokasi belum ditentukan</p>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="border-gray-100 mb-4">
                        
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Harga Sewa <span id="label-sewa"></span></span>
                                <span class="font-medium" id="summary-base-price">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-sm text-blue-600 hidden" id="row-driver">
                                <span>Biaya Supir</span>
                                <span class="font-medium" id="summary-driver">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Pajak (10%)</span>
                                <span class="font-medium" id="summary-tax">Rp 0</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-xl font-bold">Total</span>
                            <span class="text-xl font-bold text-blue-600" id="summary-total">Rp 0</span>
                        </div>
                        
                        <form action="index.php?module=Booking&action=process" method="POST">
                            <button type="button" class="w-full bg-blue-700 text-white text-center py-3 rounded-xl font-semibold hover:bg-blue-800 transition">
                                Konfirmasi Pesanan <i class="bi bi-arrow-right ml-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>

        const CAR_BASE_PRICE = 350000; 
        let driverPrice = 0;
        let totalDays = 0;

        const sumLocation = document.getElementById('summary-location');
        const sumDates = document.getElementById('summary-dates');
        const sumDays = document.getElementById('summary-days');
        const sumBasePrice = document.getElementById('summary-base-price');
        const sumDriver = document.getElementById('summary-driver');
        const rowDriver = document.getElementById('row-driver');
        const sumTax = document.getElementById('summary-tax');
        const sumTotal = document.getElementById('summary-total');
        const labelSewa = document.getElementById('label-sewa');

        document.getElementById('input-pickup').addEventListener('input', function(e) {
            sumLocation.textContent = e.target.value || 'Lokasi belum ditentukan';
        });

        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        };

        function calculateTotal() {
            let baseTotal = CAR_BASE_PRICE * (totalDays === 0 ? 1 : totalDays); 
            let driverTotal = driverPrice * (totalDays === 0 ? 1 : totalDays);
            let subtotal = baseTotal + driverTotal;
            let tax = subtotal * 0.1; // Pajak 10%
            let grandTotal = subtotal + tax;

            labelSewa.textContent = `(${totalDays === 0 ? 1 : totalDays} Hari)`;
            sumBasePrice.textContent = formatRupiah(baseTotal);
            sumTax.textContent = formatRupiah(tax);
            sumTotal.textContent = formatRupiah(grandTotal);

            if(driverPrice > 0) {
                rowDriver.classList.remove('hidden');
                sumDriver.textContent = formatRupiah(driverTotal);
            } else {
                rowDriver.classList.add('hidden');
            }
        }

        function selectAddon(selectedElement) {
            document.querySelectorAll('.addon-card').forEach(card => {
                card.className = 'addon-card border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-blue-300 transition';
            });
            selectedElement.className = 'addon-card border-2 border-blue-600 bg-blue-50/30 rounded-xl p-4 cursor-pointer transition active-addon';

            driverPrice = parseInt(selectedElement.getAttribute('data-price'));
            calculateTotal();
        }
        let currentDate = new Date(); 
        let currentMonth = currentDate.getMonth(); 
        let currentYear = currentDate.getFullYear();
        
        let startDate = null;
        let endDate = null;
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        const calendarGrid = document.getElementById('calendar-grid');

        function generateCalendarGrid(month, year) {
            calendarGrid.innerHTML = ''; 
            const firstDayOfMonth = new Date(year, month, 1).getDay(); 
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            document.getElementById('current-month-year').textContent = monthNames[month] + " " + year;
            
            for (let i = 0; i < firstDayOfMonth; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'date-cell empty-cell';
                calendarGrid.appendChild(emptyCell);
            }
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dateCell = document.createElement('div');
                dateCell.className = 'date-cell';
                
                const formattedMonth = (month + 1).toString().padStart(2, '0');
                const formattedDay = i.toString().padStart(2, '0');
                const fullDateStr = `${year}-${formattedMonth}-${formattedDay}`;
                
                dateCell.setAttribute('data-date', fullDateStr);
                dateCell.textContent = i;
                
                dateCell.addEventListener('click', handleDateClick);
                calendarGrid.appendChild(dateCell);
            }
            renderCalendar(); 
        }

        function renderCalendar() {
            const allDateCells = calendarGrid.querySelectorAll('.date-cell:not(.empty-cell)');
            
            allDateCells.forEach(cell => {
                const dateStr = cell.getAttribute('data-date');
                cell.classList.remove('selected', 'start-date', 'end-date', 'in-range');

                if (dateStr === startDate) {
                    cell.classList.add('selected', 'start-date');
                    if (!endDate) cell.classList.remove('start-date'); 
                }
                if (dateStr === endDate) cell.classList.add('selected', 'end-date');
                if (startDate && endDate && dateStr > startDate && dateStr < endDate) cell.classList.add('in-range');
            });
            updateSummaryDates();
        }

        function handleDateClick(event) {
            const val = event.currentTarget.getAttribute('data-date');
            if (!startDate || (startDate && endDate)) {
                startDate = val; endDate = null;
            } else if (val === startDate) {
                startDate = null;
            } else if (val < startDate) {
                endDate = startDate; startDate = val;
            } else {
                endDate = val;
            }
            renderCalendar();
        }
        
        function updateSummaryDates() {
            if (startDate && endDate) {
                const startObj = new Date(startDate);
                const endObj = new Date(endDate);
                
                const diffTime = Math.abs(endObj - startObj);
                totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                
                sumDates.textContent = `${startObj.getDate()} ${monthNames[startObj.getMonth()]} - ${endObj.getDate()} ${monthNames[endObj.getMonth()]} ${endObj.getFullYear()}`;
                sumDays.textContent = `${totalDays} Hari Terpilih`;
            } else if (startDate) {
                totalDays = 1; 
                const startObj = new Date(startDate);
                sumDates.textContent = `${startObj.getDate()} ${monthNames[startObj.getMonth()]} ${startObj.getFullYear()}`;
                sumDays.textContent = "1 Hari Terpilih";
            } else {
                totalDays = 0;
                sumDates.textContent = "Belum pilih tanggal";
                sumDays.textContent = "0 Hari";
            }
            calculateTotal();
        }

        document.getElementById('prev-month').addEventListener('click', () => {
            currentMonth--; if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            generateCalendarGrid(currentMonth, currentYear);
        });

        document.getElementById('next-month').addEventListener('click', () => {
            currentMonth++; if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            generateCalendarGrid(currentMonth, currentYear);
        });

        // Initialize UI
        generateCalendarGrid(currentMonth, currentYear);
        calculateTotal();
    </script>
</body>
</html>