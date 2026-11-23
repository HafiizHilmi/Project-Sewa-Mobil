<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../../../include/db_config.php';
    $pdo = getPDO();
}
$stmtBL = $pdo->query("SELECT * FROM blacklisted_locations");
$geofences = $stmtBL ? $stmtBL->fetchAll(PDO::FETCH_ASSOC) : [];

// Ambil data pemesanan sukses untuk hitung stok tanggal berdasarkan type_key dari tipe mobil ini
$bookedRanges = [];
$totalStock = 1;
if (!empty($car['type_key'])) {
    // 1. Hitung total unit fisik yang terdaftar untuk tipe mobil ini
    $stmtStock = $pdo->prepare("
        SELECT COUNT(*) 
        FROM cars 
        WHERE type_key = :type_key 
          AND is_type = 0
    ");
    $stmtStock->execute(['type_key' => $car['type_key']]);
    $totalStock = intval($stmtStock->fetchColumn());
    if ($totalStock < 1) {
        $totalStock = 1; // Fallback minimal 1
    }

    // 2. Ambil semua rentang tanggal pemesanan terkonfirmasi untuk semua mobil dengan type_key yang sama
    $stmtBooked = $pdo->prepare("
        SELECT b.start_date, b.end_date 
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE c.type_key = :type_key 
          AND b.status = 'confirmed'
    ");
    $stmtBooked->execute(['type_key' => $car['type_key']]);
    $bookedRanges = $stmtBooked->fetchAll(PDO::FETCH_ASSOC);
} else if (isset($car['id'])) {
    // Fallback lama jika type_key kosong
    $stmtBooked = $pdo->prepare("
        SELECT start_date, end_date 
        FROM bookings 
        WHERE car_id = :car_id 
          AND status = 'confirmed'
    ");
    $stmtBooked->execute(['car_id' => $car['id']]);
    $bookedRanges = $stmtBooked->fetchAll(PDO::FETCH_ASSOC);
    $totalStock = intval($car['stock'] ?? 1);
}
$carStock = $totalStock;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SewaMobil - Reservasi Kendaraan</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <link rel="stylesheet" href="assets/css/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
</head>
<body>
    <?php $navActive = 'katalog'; include __DIR__ . '/../../../../include/nav.php'; ?>

    <div class="container" style="padding-top:22px">
        <a href="index.php?module=Homepage&action=index" class="tiny" style="display:inline-flex;align-items:center;gap:6px;font-weight:650;color:var(--ink-2)">
            <i class="bi bi-arrow-left"></i> Kembali ke katalog
        </a>
        <div class="booking-head">
            <h1>Reservasi kendaraan</h1>
            <p>Isi data, pilih tanggal, lalu konfirmasi. Stok otomatis dicek per tanggal.</p>
        </div>
    </div>

    <main class="container" style="padding-bottom:64px">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="notice notice--danger" id="flash-alert" role="alert" style="margin:18px 0;align-items:flex-start">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span style="flex:1"><?= htmlspecialchars($_SESSION['flash']) ?></span>
                <button type="button" onclick="document.getElementById('flash-alert').remove()" aria-label="Tutup"
                        style="background:none;border:0;color:inherit;line-height:1"><i class="bi bi-x-lg"></i></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="booking-grid">

            <div class="u-stack">

                <section class="panel">
                    <div class="panel-head">
                        <span class="panel-step">1</span>
                        <div>
                            <h2>Detail lokasi</h2>
                            <p>Ketik nama tempat, pilih dari saran agar titik lokasi tersimpan.</p>
                        </div>
                    </div>

                    <div id="geofence-warning-box" class="notice notice--danger hidden" style="margin-bottom:16px">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span id="geofence-warning-text"></span>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field" style="position:relative">
                            <label for="input-pickup">Lokasi pengambilan</label>
                            <input class="input" type="text" id="input-pickup" name="pickup_location" form="booking-form"
                                   placeholder="Contoh: Bandara Juanda T1" autocomplete="off">
                            <div id="dropdown-pickup" class="suggest hidden"></div>
                        </div>
                        <div class="field" style="position:relative">
                            <label for="input-return">Lokasi pengembalian</label>
                            <input class="input" type="text" id="input-return" name="return_location" form="booking-form"
                                   placeholder="Kosongkan bila sama" autocomplete="off">
                            <div id="dropdown-return" class="suggest hidden"></div>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <span class="panel-step">2</span>
                        <div>
                            <h2>Detail personal</h2>
                            <p>Data ini dipakai untuk kontrak sewa dan notifikasi WhatsApp.</p>
                        </div>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="full_name">Nama lengkap</label>
                            <input class="input" type="text" id="full_name" name="full_name" form="booking-form" placeholder="Sesuai KTP">
                        </div>
                        <div class="field">
                            <label for="email">Alamat email</label>
                            <input class="input" type="email" id="email" name="email" form="booking-form" placeholder="nama@email.com">
                        </div>
                        <div class="field">
                            <label for="phone">Nomor HP</label>
                            <input class="input" type="tel" id="phone" name="phone" form="booking-form" placeholder="0812xxxxxxx">
                        </div>
                        <div class="field" style="position:relative">
                            <label for="input-address">Alamat lengkap</label>
                            <input class="input" type="text" id="input-address" name="address" form="booking-form"
                                   placeholder="Jalan, nomor, kota" autocomplete="off">
                            <div id="dropdown-address" class="suggest hidden"></div>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="calendar-head">
                        <div class="panel-head" style="margin-bottom:0">
                            <span class="panel-step">3</span>
                            <div>
                                <h2>Pilih tanggal</h2>
                                <p>Tanggal yang dicoret berarti stok tipe ini sudah habis.</p>
                            </div>
                        </div>
                        <div class="calendar-nav">
                            <button type="button" class="icon-btn" id="prev-month" aria-label="Bulan sebelumnya"><i class="bi bi-chevron-left"></i></button>
                            <span id="current-month-year" style="font-size:.86rem;font-weight:700;min-width:120px;text-align:center"></span>
                            <button type="button" class="icon-btn" id="next-month" aria-label="Bulan berikutnya"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>

                    <div class="calendar-dow">
                        <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                    </div>
                    <div class="calendar-grid" id="calendar-grid"></div>

                    <div class="calendar-legend">
                        <span><i class="legend-free"></i> Tersedia</span>
                        <span><i class="legend-selected"></i> Tanggal dipilih</span>
                        <span><i class="legend-full"></i> Stok habis</span>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <span class="panel-step">4</span>
                        <div>
                            <h2>Layanan tambahan</h2>
                            <p>Supir ditambahkan per hari sewa.</p>
                        </div>
                    </div>

                    <div class="addon-grid">
                        <div class="addon-card" data-price="100000" onclick="selectAddon(this)" role="button" tabindex="0">
                            <h3>Dengan driver</h3>
                            <p>Mobil datang lengkap dengan supir.</p>
                            <span class="addon-price">+Rp 100.000 /hari</span>
                        </div>
                        <div class="addon-card is-active" data-price="0" onclick="selectAddon(this)" role="button" tabindex="0">
                            <h3>Lepas kunci</h3>
                            <p>Mobil disewa tanpa supir.</p>
                            <span class="addon-price">Tanpa biaya tambahan</span>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="booking-aside">
                <div class="card summary-car">
                    <?php
                        $rawImg = $car['image'] ?? '';
                        $carImage = car_image_url($rawImg);
                    ?>
                    <img src="<?= htmlspecialchars($carImage) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">

                    <div class="summary-body">
                        <div class="car-eyebrow" style="margin-bottom:6px">
                            <span><?= htmlspecialchars($car['category'] ?: 'Lainnya') ?></span>
                            <span><?= (int) $carStock ?> unit</span>
                        </div>
                        <h3><?= htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']) ?></h3>
                        <p class="summary-meta">
                            <?= htmlspecialchars($car['fuel_type'] ?: '-') ?> &middot; <?= htmlspecialchars($car['seats'] ?: '-') ?> kursi
                            &middot; <?= htmlspecialchars($car['transmission'] ?: 'Manual') ?>
                        </p>

                        <div class="summary-list">
                            <div class="summary-item">
                                <i class="bi bi-calendar3"></i>
                                <div>
                                    <b id="summary-dates">Belum pilih tanggal</b>
                                    <span id="summary-days">0 hari</span>
                                </div>
                            </div>
                            <div class="summary-item">
                                <i class="bi bi-geo-alt"></i>
                                <div>
                                    <b id="summary-pickup" title="Lokasi belum ditentukan" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:230px">Lokasi belum ditentukan</b>
                                    <span>Pengambilan</span>
                                </div>
                            </div>
                            <div class="summary-item">
                                <i class="bi bi-geo"></i>
                                <div>
                                    <b id="summary-return" title="Sama dengan lokasi pengambilan" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:230px">Sama dengan lokasi pengambilan</b>
                                    <span>Pengembalian</span>
                                </div>
                            </div>
                        </div>

                        <div class="summary-rows">
                            <div class="summary-row">
                                <span>Harga sewa <span id="label-sewa" class="tiny"></span></span>
                                <b id="summary-base-price">Rp 0</b>
                            </div>
                            <div class="summary-row hidden" id="row-driver">
                                <span>Biaya driver</span>
                                <b id="summary-driver">Rp 0</b>
                            </div>
                            <div class="summary-row">
                                <span>Pajak 10%</span>
                                <b id="summary-tax">Rp 0</b>
                            </div>
                        </div>

                        <div class="summary-total">
                            <span>Total</span>
                            <b id="summary-total">Rp 0</b>
                        </div>

                        <form action="index.php?module=Booking&action=process" method="POST" id="booking-form" class="summary-cta">
                            <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">
                            <input type="hidden" name="addon_driver" id="addon-driver-input" value="0">
                            <input type="hidden" name="start_date" id="start-date-input" value="">
                            <input type="hidden" name="end_date" id="end-date-input" value="">
                            <input type="hidden" name="total_price" id="total-price-input" value="0">

                            <input type="hidden" name="pickup_lat" id="pickup-lat-input" value="">
                            <input type="hidden" name="pickup_lon" id="pickup-lon-input" value="">
                            <input type="hidden" name="return_lat" id="return-lat-input" value="">
                            <input type="hidden" name="return_lon" id="return-lon-input" value="">
                            <input type="hidden" name="address_lat" id="address-lat-input" value="">
                            <input type="hidden" name="address_lon" id="address-lon-input" value="">

                            <button type="submit" id="submitBooking" class="btn btn--primary btn--block btn--lg" disabled>
                                Konfirmasi pesanan <i class="bi bi-arrow-right"></i>
                            </button>
                            <p class="tiny u-mt-8" id="submitHint" style="text-align:center">Pilih tanggal sewa untuk melanjutkan.</p>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        const CAR_BASE_PRICE = <?= intval($car['price_per_day']) ?>; 
        const bookedRanges = <?= json_encode($bookedRanges) ?>;
        const carStock = <?= $carStock ?>;
        let driverPrice = 0;
        let totalDays = 0;

        const sumPickup = document.getElementById('summary-pickup');
        const sumReturn = document.getElementById('summary-return');
        const sumDates = document.getElementById('summary-dates');
        const sumDays = document.getElementById('summary-days');
        const sumBasePrice = document.getElementById('summary-base-price');
        const sumDriver = document.getElementById('summary-driver');
        const rowDriver = document.getElementById('row-driver');
        const sumTax = document.getElementById('summary-tax');
        const sumTotal = document.getElementById('summary-total');
        const labelSewa = document.getElementById('label-sewa');
        const addonDriverInput = document.getElementById('addon-driver-input');
        const startDateInput = document.getElementById('start-date-input');
        const endDateInput = document.getElementById('end-date-input');
        const totalPriceInput = document.getElementById('total-price-input');
        const bookingForm = document.getElementById('booking-form');

        document.getElementById('input-pickup').addEventListener('input', function(e) {
            sumPickup.textContent = e.target.value || 'Lokasi belum ditentukan';
            sumPickup.title = e.target.value || 'Lokasi belum ditentukan';
            if (!e.target.value.trim()) {
                document.getElementById('pickup-lat-input').value = "";
                document.getElementById('pickup-lon-input').value = "";
            }
            if (typeof checkGeofencesRealtime === 'function') checkGeofencesRealtime();
        });

        document.getElementById('input-return').addEventListener('input', function(e) {
            sumReturn.textContent = e.target.value || 'Sama dengan lokasi pengambilan';
            sumReturn.title = e.target.value || 'Sama dengan lokasi pengambilan';
            if (!e.target.value.trim()) {
                document.getElementById('return-lat-input').value = "";
                document.getElementById('return-lon-input').value = "";
            }
            if (typeof checkGeofencesRealtime === 'function') checkGeofencesRealtime();
        });

        document.getElementById('input-address').addEventListener('input', function(e) {
            if (!e.target.value.trim()) {
                document.getElementById('address-lat-input').value = "";
                document.getElementById('address-lon-input').value = "";
            }
            if (typeof checkGeofencesRealtime === 'function') checkGeofencesRealtime();
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
            totalPriceInput.value = grandTotal.toFixed(2);

            if (driverPrice > 0) {
                rowDriver.classList.remove('hidden');
                sumDriver.textContent = formatRupiah(driverTotal);
            } else {
                rowDriver.classList.add('hidden');
            }
        }

        function selectAddon(selectedElement) {
            document.querySelectorAll('.addon-card').forEach(card => {
                card.className = 'addon-card';
            });
            selectedElement.className = 'addon-card is-active';
            
            driverPrice = parseInt(selectedElement.getAttribute('data-price'));
            addonDriverInput.value = driverPrice > 0 ? '1' : '0';
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
            
            // Disable prev-month button if we are in the current month or earlier
            const prevMonthBtn = document.getElementById('prev-month');
            const todayDate = new Date();
            const currentRealMonth = todayDate.getMonth();
            const currentRealYear = todayDate.getFullYear();
            
            if (year < currentRealYear || (year === currentRealYear && month <= currentRealMonth)) {
                prevMonthBtn.classList.add('is-disabled');
            } else {
                prevMonthBtn.classList.remove('is-disabled');
            }
            
            for (let i = 0; i < firstDayOfMonth; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'date-cell empty-cell';
                calendarGrid.appendChild(emptyCell);
            }
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dateCell = document.createElement('div');
                dateCell.className = 'date-cell';
                
                const formattedMonth = (month + 1).toString().padStart(2, '0');
                const formattedDay = i.toString().padStart(2, '0');
                const fullDateStr = `${year}-${formattedMonth}-${formattedDay}`;
                
                dateCell.setAttribute('data-date', fullDateStr);
                dateCell.textContent = i;
                
                // Hitung jumlah booking yang menutup tanggal ini
                let activeBookingsCount = 0;
                for (const range of bookedRanges) {
                    if (fullDateStr >= range.start_date && fullDateStr <= range.end_date) {
                        activeBookingsCount++;
                    }
                }
                
                const cellDate = new Date(year, month, i);
                if (cellDate < today) {
                    dateCell.classList.add('is-booked');
                } else if (activeBookingsCount >= carStock) {
                    // Jika stok habis (warna abu-abu disabled)
                    dateCell.classList.add('is-booked');
                    dateCell.title = "Stok Mobil Habis pada Tanggal Ini";
                } else {
                    dateCell.addEventListener('click', handleDateClick);
                }
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

        function isDateOutOfStock(dateStr) {
            let activeBookingsCount = 0;
            for (const range of bookedRanges) {
                if (dateStr >= range.start_date && dateStr <= range.end_date) {
                    activeBookingsCount++;
                }
            }
            return activeBookingsCount >= carStock;
        }

        function handleDateClick(event) {
            const val = event.currentTarget.getAttribute('data-date');
            
            if (!startDate || (startDate && endDate)) {
                if (isDateOutOfStock(val)) {
                    alert('Tanggal ini sudah habis dipesan.');
                    return;
                }
                startDate = val; endDate = null;
            } else if (val === startDate) {
                startDate = null;
            } else if (val < startDate) {
                // Cek apakah ada tanggal out of stock di antara val dan startDate
                let hasOutOfStock = false;
                let cur = new Date(val);
                let end = new Date(startDate);
                while (cur <= end) {
                    let curStr = cur.toISOString().split('T')[0];
                    if (isDateOutOfStock(curStr)) {
                        hasOutOfStock = true;
                        break;
                    }
                    cur.setDate(cur.getDate() + 1);
                }
                
                if (hasOutOfStock) {
                    alert('Rentang tanggal yang Anda pilih mengandung tanggal yang sudah habis dipesan.');
                    return;
                }
                
                endDate = startDate; startDate = val;
            } else {
                // Cek apakah ada tanggal out of stock di antara startDate dan val
                let hasOutOfStock = false;
                let cur = new Date(startDate);
                let end = new Date(val);
                while (cur <= end) {
                    let curStr = cur.toISOString().split('T')[0];
                    if (isDateOutOfStock(curStr)) {
                        hasOutOfStock = true;
                        break;
                    }
                    cur.setDate(cur.getDate() + 1);
                }
                
                if (hasOutOfStock) {
                    alert('Rentang tanggal yang Anda pilih mengandung tanggal yang sudah habis dipesan.');
                    return;
                }
                
                endDate = val;
            }
            renderCalendar();
        }
        
        function updateSummaryDates() {
            if (startDate && endDate) {
                const startObj = new Date(startDate);
                const endObj = new Date(endDate);
                
                // Hitung selisih hari
                const diffTime = Math.abs(endObj - startObj);
                totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
                
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

            startDateInput.value = startDate || '';
            endDateInput.value = endDate || '';

            const submitBtn = document.getElementById('submitBooking');
            const submitHint = document.getElementById('submitHint');
            const ready = Boolean(startDate && endDate);
            if (submitBtn) submitBtn.disabled = !ready;
            if (submitHint) submitHint.textContent = ready
                ? 'Tanggal siap. Lengkapi data lalu konfirmasi.'
                : 'Pilih tanggal sewa untuk melanjutkan.';
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

        generateCalendarGrid(currentMonth, currentYear);
        calculateTotal();

        // ==================== AUTOCOMPLETE NOMINATIM SYSTEM ====================
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function createSuggestionItem(place, index, activeIndex) {
            const mainText = place.name || place.display_name.split(',')[0];
            const secondaryText = place.display_name.replace(mainText + ', ', '') || '';
            const activeClass = index === activeIndex ? 'is-active' : '';
            return `
                <div class="suggest-item suggestion-item ${activeClass}" data-index="${index}">
                    <i class="bi bi-geo-alt-fill"></i>
                    <div style="min-width:0">
                        <b>${escapeHtml(mainText)}</b>
                        <span>${escapeHtml(secondaryText)}</span>
                    </div>
                </div>
            `;
        }

        function showLoading(dropdown) {
            dropdown.innerHTML = `
                <div class="suggest-empty">
                    <i class="bi bi-hourglass-split"></i> Mencari lokasi…
                </div>
            `;
            dropdown.classList.remove('hidden');
        }

        function showNoResults(dropdown) {
            dropdown.innerHTML = `
                <div class="suggest-empty">
                    <i class="bi bi-geo-alt"></i> Lokasi tidak ditemukan
                </div>
            `;
            dropdown.classList.remove('hidden');
        }

        function initAutocomplete(inputId, dropdownId, onSelectCallback = null) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            let debounceTimer;
            let suggestions = [];
            let activeIndex = -1;

            function closeDropdown() {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                activeIndex = -1;
            }

            input.addEventListener('input', function() {
                const query = input.value.trim();
                clearTimeout(debounceTimer);
                
                if (query.length < 3) {
                    closeDropdown();
                    return;
                }

                debounceTimer = setTimeout(() => {
                    showLoading(dropdown);
                    
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1&countrycodes=id`;
                    
                    fetch(url, {
                        headers: {
                            'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        suggestions = data;
                        if (suggestions.length === 0) {
                            showNoResults(dropdown);
                            return;
                        }
                        renderSuggestions();
                    })
                    .catch(error => {
                        console.error('Error fetching autocomplete:', error);
                        closeDropdown();
                    });
                }, 350);
            });

            function renderSuggestions() {
                dropdown.innerHTML = suggestions.map((place, idx) => 
                    createSuggestionItem(place, idx, activeIndex)
                ).join('');
                dropdown.classList.remove('hidden');

                dropdown.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const index = parseInt(item.getAttribute('data-index'));
                        selectSuggestion(index);
                    });
                });
            }

            function selectSuggestion(index) {
                if (index >= 0 && index < suggestions.length) {
                    const selected = suggestions[index];
                    input.value = selected.display_name;
                    closeDropdown();
                    if (onSelectCallback) {
                        onSelectCallback(selected);
                    }
                }
            }

            input.addEventListener('keydown', function(e) {
                if (dropdown.classList.contains('hidden') || suggestions.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % suggestions.length;
                    renderSuggestions();
                    scrollActiveIntoView();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + suggestions.length) % suggestions.length;
                    renderSuggestions();
                    scrollActiveIntoView();
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && activeIndex < suggestions.length) {
                        e.preventDefault();
                        selectSuggestion(activeIndex);
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            function scrollActiveIntoView() {
                const activeItem = dropdown.querySelector(`.suggestion-item[data-index="${activeIndex}"]`);
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            }

            document.addEventListener('click', function(e) {
                if (e.target !== input && !dropdown.contains(e.target)) {
                    closeDropdown();
                }
            });
        }

        // ==================== REAL-TIME GEOFENCE CHECKER ====================
        const geofences = <?= json_encode($geofences) ?>;
        let isGeofenceBlocked = false;

        function haversine(lat1, lon1, lat2, lon2) {
            const earthRadius = 6371000; // in meters
            const latDelta = (lat2 - lat1) * Math.PI / 180;
            const lonDelta = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(latDelta / 2) * Math.sin(latDelta / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(lonDelta / 2) * Math.sin(lonDelta / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return earthRadius * c;
        }

        function checkGeofencesRealtime() {
            const pickupLat = parseFloat(document.getElementById('pickup-lat-input').value) || null;
            const pickupLon = parseFloat(document.getElementById('pickup-lon-input').value) || null;
            const returnLat = parseFloat(document.getElementById('return-lat-input').value) || null;
            const returnLon = parseFloat(document.getElementById('return-lon-input').value) || null;
            const addressLat = parseFloat(document.getElementById('address-lat-input').value) || null;
            const addressLon = parseFloat(document.getElementById('address-lon-input').value) || null;

            // Ambil teks yang diinput secara manual (ubah ke huruf kecil untuk perbandingan case-insensitive)
            const pickupText = document.getElementById('input-pickup').value.trim().toLowerCase();
            const returnText = document.getElementById('input-return').value.trim().toLowerCase();
            const addressText = document.getElementById('input-address').value.trim().toLowerCase();

            isGeofenceBlocked = false;
            let blockReason = "";

            if (!geofences || geofences.length === 0) return;

            for (const gf of geofences) {
                const gfLat = parseFloat(gf.latitude);
                const gfLng = parseFloat(gf.longitude);
                const gfRad = parseInt(gf.radius);
                const gfName = gf.location_name ? gf.location_name.trim().toLowerCase() : "";

                // A. Pengecekan berbasis Pencocokan Teks (Manual Input)
                if (gfName !== "") {
                    // Bersihkan nama lokasi dari suffix angka dan kata "point"
                    const cleanGfName = gfName.replace(/\s*\d+$/, '').replace(/\s*point\s*$/, '').trim();
                    
                    // Pecah menjadi bagian-bagian kata kunci (misal: "ntt - ntb" -> ["ntt", "ntb"])
                    const parts = cleanGfName.split(/[\s\-\,]+/).filter(part => part.length >= 3);
                    if (cleanGfName.length >= 3 && !parts.includes(cleanGfName)) {
                        parts.push(cleanGfName);
                    }

                    // Cek apakah ada bagian dari nama lokasi blacklist yang terkandung dalam input user
                    let isTextMatched = false;
                    for (const part of parts) {
                        if (pickupText.includes(part) || returnText.includes(part) || addressText.includes(part)) {
                            isTextMatched = true;
                            break;
                        }
                    }

                    if (isTextMatched) {
                        isGeofenceBlocked = true;
                        blockReason = `Lokasi Anda berada di wilayah tidak terjangkau (${gf.location_name}).`;
                        break;
                    }
                }

                // B. Pengecekan berbasis Radius Geofence (Auto-suggest terpilih)
                // 1. Cek Lokasi Pengambilan
                if (pickupLat && pickupLon) {
                    const dist = haversine(pickupLat, pickupLon, gfLat, gfLng);
                    if (dist <= gfRad) {
                        isGeofenceBlocked = true;
                        blockReason = `Lokasi pengambilan kendaraan Anda berada di wilayah tidak terjangkau (${gf.location_name}).`;
                        break;
                    }
                }

                // 2. Cek Lokasi Pengembalian
                if (returnLat && returnLon) {
                    const dist = haversine(returnLat, returnLon, gfLat, gfLng);
                    if (dist <= gfRad) {
                        isGeofenceBlocked = true;
                        blockReason = `Lokasi pengembalian kendaraan Anda berada di wilayah tidak terjangkau (${gf.location_name}).`;
                        break;
                    }
                }

                // 3. Cek Alamat Lengkap
                if (addressLat && addressLon) {
                    const dist = haversine(addressLat, addressLon, gfLat, gfLng);
                    if (dist <= gfRad) {
                        isGeofenceBlocked = true;
                        blockReason = `Alamat lengkap Anda berada di wilayah tidak terjangkau (${gf.location_name}).`;
                        break;
                    }
                }
            }

            const warningBox = document.getElementById('geofence-warning-box');
            const warningText = document.getElementById('geofence-warning-text');
            if (isGeofenceBlocked) {
                warningText.textContent = blockReason;
                warningBox.classList.remove('hidden');
            } else {
                warningBox.classList.add('hidden');
                warningText.textContent = "";
            }
        }

        // Initialize Auto-suggest
        initAutocomplete('input-pickup', 'dropdown-pickup', function(place) {
            const val = place.display_name;
            sumPickup.textContent = val || 'Lokasi belum ditentukan';
            sumPickup.title = val || 'Lokasi belum ditentukan';
            document.getElementById('pickup-lat-input').value = place.lat;
            document.getElementById('pickup-lon-input').value = place.lon;
            checkGeofencesRealtime();
        });
        initAutocomplete('input-return', 'dropdown-return', function(place) {
            const val = place.display_name;
            sumReturn.textContent = val || 'Sama dengan lokasi pengambilan';
            sumReturn.title = val || 'Sama dengan lokasi pengambilan';
            document.getElementById('return-lat-input').value = place.lat;
            document.getElementById('return-lon-input').value = place.lon;
            checkGeofencesRealtime();
        });
        initAutocomplete('input-address', 'dropdown-address', function(place) {
            document.getElementById('address-lat-input').value = place.lat;
            document.getElementById('address-lon-input').value = place.lon;
            checkGeofencesRealtime();
        });

        bookingForm.addEventListener('submit', function(e) {
            if (!startDate || !endDate) {
                alert('Pilih tanggal mulai dan selesai sewa terlebih dahulu.');
                e.preventDefault();
                return;
            }

            if (sumPickup.textContent === 'Lokasi belum ditentukan') {
                alert('Masukkan lokasi pengambilan.');
                e.preventDefault();
                return;
            }

            if (isGeofenceBlocked) {
                alert('Pemesanan tidak dapat dilanjutkan karena lokasi yang dipilih berada di wilayah tidak terjangkau.');
                e.preventDefault();
                return;
            }
        });

        // ==================== GLOBAL THEME SYSTEM ====================
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