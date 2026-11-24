# Project Sewa Mobil

Sistem Informasi Penyewaan Mobil berbasis **PHP Native** dengan arsitektur **MVC (Model-View-Controller) Kustom** yang dirancang secara modern, responsif, dan aman. Aplikasi ini menyediakan solusi lengkap baik untuk pelanggan (pencarian, verifikasi dokumen, booking, pembayaran terintegrasi) maupun untuk administrator (manajemen mobil, verifikasi dokumen, kelola pesanan, statistik bisnis).

---

## Tech Stack & Integrasi

Berikut adalah teknologi dan layanan pihak ketiga yang digunakan dalam proyek ini:

*   **Core Backend:** PHP 8.x (Native dengan MVC kustom)
*   **Database:** MySQL / MariaDB (Interaksi via PDO & MySQLi)
*   **Frontend:** HTML5, CSS3, JavaScript (ES6)
*   **Design System:** `public/assets/css/app.css` — token warna/tipografi/komponen (tanpa framework, tanpa gradien dekoratif, mendukung mode terang & gelap)
*   **Ikon & Font:** Bootstrap Icons 1.11, Google Fonts (Plus Jakarta Sans)
*   **Data Armada:** `db/cars.json` (sumber tunggal 50 mobil) + seeder `db/seed_cars.php`
*   **Integrasi Midtrans:** Payment Gateway Sandbox untuk pemrosesan pembayaran real-time.
*   **Integrasi Fonnte:** Layanan API WhatsApp untuk notifikasi pemesanan & verifikasi otomatis ke pelanggan.
*   **Gambar Dinamis:** Unsplash Source API.

---

## Fitur Utama

### Portal Pelanggan (Customer Portal)
1.  **Landing Page:**
    *   Hero minimalis dengan statistik armada langsung dari database.
    *   Enam mobil paling sering disewa + alur sewa tiga langkah.
2.  **Sistem Autentikasi:**
    *   Registrasi dan Login pelanggan.
    *   Proteksi menggunakan token **CSRF (Cross-Site Request Forgery)**.
    *   Enkripsi password menggunakan `password_hash()`.
3.  **Verifikasi Identitas Pengguna:**
    *   Pengunggahan berkas KTP dan SIM secara langsung melalui halaman profil pelanggan.
    *   Status verifikasi *real-time*: `unverified`, `pending`, `verified`, atau `rejected`.
4.  **Halaman Utama (Homepage) & Filtrasi:**
    *   Katalog **50 tipe mobil / 84 unit** dengan foto asli per model.
    *   Pencarian mobil.
    *   Filter kategori (MPV, SUV, Sedan, Hatchback, EV, Luxury), transmisi, batas harga, dan urutan harga; jumlah hasil dihitung langsung di klien.
5.  **Pemesanan & Sewa Mobil (Booking System):**
    *   Formulir input tanggal mulai dan berakhir, lokasi penjemputan/pengembalian, alamat, serta opsi tambahan sopir (*add-on driver*).
    *   Kalkulasi otomatis total harga sewa secara *real-time* berdasarkan durasi sewa dan tarif harian mobil.
    *   Pelanggan harus terverifikasi (`verified`) sebelum dapat menyewa mobil untuk meminimalkan risiko keamanan bisnis.
6.  **Gateway Pembayaran Sandbox (Midtrans):**
    *   Integrasi pembayaran online dengan status pemesanan terupdate secara otomatis (Pending, Sukses, Gagal/Ditolak).
7.  **Riwayat Pesanan & Profil:**
    *   Melihat daftar seluruh transaksi yang pernah dilakukan beserta status terkininya (`pending`, `confirmed`, `completed`, `cancelled`).

### Panel Administrator (Admin Portal v2.0)
1.  **Arsitektur & Performa:**
    *   **Per-Page Data Separation (Router):** Menggunakan router PHP selektif (`admin/index.php?page=...`). Query dan data hanya dimuat untuk halaman yang aktif, memangkas ukuran DOM dan HTML payload hingga <90 KB per halaman.
    *   **0 Ketergantungan CDN Eksternal:** Framework Tailwind dan Alpine.js di-vendor secara lokal di `admin/assets/vendor/` untuk kedaulatan data dan kestabilan tanpa koneksi internet luar.
    *   **Optimasi Agregasi Database:** Grafik analitik pesanan (Harian, Mingguan, Bulanan) menggunakan query agregasi tunggal (turun dari 36 query menjadi 2 query).
    *   **Sistem Migrasi Terpusat:** `admin/install/migrate.php` dengan schema versioning, mengeliminasi pengecekan `SHOW COLUMNS` berulang pada setiap request.
2.  **Keamanan & Proteksi (Backend Guard):**
    *   Semua aksi backend (`car_action`, `customer_action`, `order_action`, `verify_action`, `admin_action`, `triggers`) dan penyajian file sensitif (`serve_file.php`) diproteksi modul `admin/inc/guard.php` (`require_admin()` dan `require_superuser()`).
    *   Enkripsi kredensial admin menggunakan `password_hash()` (Bcrypt).
3.  **Sistem Notifikasi & Feedback UI (admin-ui.js):**
    *   Menghilangkan seluruh popup browser `alert()` dan `confirm()` native, digantikan oleh sistem `toast()` modern dengan animasi, bilah durasi otomatis, jeda saat *hover*, dan dukungan *reduced motion*.
    *   *Polling background* cerdas (25 detik) dengan auto-pause saat tab tidak aktif (*visibility API*) dan suara notifikasi pesanan masuk.
4.  **Kontrol Jarak Jauh & Alarm Delay Kendaraan:**
    *   Mendukung pengiriman sinyal alarm dan pematian mesin jarak jauh via Webhook (MacroDroid / HTTP).
    *   **Fitur Pengaturan Delay Alarm:** Durasi delay bunyi (0 detik, 5 detik, 10 detik, 30 detik, atau kustom detik) dapat dipilih langsung sebelum sinyal dikirimkan. MacroDroid di perangkat kendaraan dapat membaca parameter `{v=delay}` dan menambahkan aksi *"Wait Before Next Action"* sesuai nilai parameter tersebut.
6.  **Pengaturan Profil Bisnis:**
    *   Mengubah nama perusahaan, nomor kontak layanan, akun sosial media, dan alamat fisik kantor yang tersinkronisasi langsung ke sistem.

---

## Panduan Instalasi & Konfigurasi

Proyek ini dilengkapi dengan fitur **Auto-Setup Database** pada berkas `include/db_config.php`. Jika database Anda belum dibuat, sistem secara otomatis akan membuat database baru dan mengimpor tabel-tabel dari file SQL saat pertama kali aplikasi dijalankan.

### Langkah-langkah Memulai:

1.  **Clone Repositori:**
    ```bash
    git clone https://github.com/HafiizHilmi/Project-Sewa-Mobil.git
    cd Project-Sewa-Mobil
    ```

2.  **Pindahkan ke Web Root (XAMPP):**
    Pindahkan direktori proyek ini ke dalam folder `C:\xampp\htdocs\` (untuk pengguna Windows).

3.  **Konfigurasi Environment (`.env`):**
    Sesuaikan token Fonnte WhatsApp API dan Midtrans Sandbox Credentials pada file `.env` di root direktori jika Anda ingin mengaktifkan fitur notifikasi dan pembayaran asli:
    ```env
    # Fonnte WhatsApp API Token
    FONNTE_TOKEN="isi_token_fonnte_anda"

    # Midtrans Payment Gateway Configuration
    MIDTRANS_IS_PRODUCTION=false
    MIDTRANS_SERVER_KEY="isi_server_key_midtrans_anda"
    MIDTRANS_CLIENT_KEY="isi_client_key_midtrans_anda"
    ```

4.  **Konfigurasi Koneksi Database:**
    *   Pastikan MySQL di XAMPP Anda aktif.
    *   Buka berkas [db_config.php](file:///c:/xampp/htdocs/Project-Sewa-Mobil/include/db_config.php) dan sesuaikan username & password database Anda jika berbeda dari bawaan (`root` / `""`).
    *   Secara otomatis, ketika Anda memuat halaman web untuk pertama kali, sistem akan mendeteksi dan mengonfigurasi skema database dari `db/sewa_mobil.sql`.

5.  **Data Armada (50 mobil + foto):**
    *   Data armada ada di `db/cars.json` (merek, model, tahun, kategori, harga, nama file foto).
    *   Saat `include/db_config.php` mendeteksi tabel `cars` kosong, seeder otomatis mengisi 50 mobil dari JSON tersebut.
    *   Untuk menyegarkan data secara manual (upsert, id lama tidak berubah):
        ```bash
        php db/seed_cars.php
        ```
    *   Foto armada tersimpan di `public/assets/images/cars/`. Bila perlu mengambil ulang dari Wikimedia Commons:
        ```bash
        python db/fetch_car_images.py --force
        ```
        Sumber & atribusi tiap foto tercatat di `public/assets/images/cars/CREDITS.md`.

6.  **Akses Aplikasi:**
    *   **Customer Portal:** `http://localhost/Project-Sewa-Mobil/public/`
    *   **Admin Portal:** `http://localhost/Project-Sewa-Mobil/admin/`
    *   Catatan: konfigurasi Apache bawaan proyek ini berjalan di port **82** (`Listen 82`), sehingga URL menjadi `http://localhost:82/Project-Sewa-Mobil/public/`.

---

## Struktur Frontend Pelanggan

| Berkas | Fungsi |
| :--- | :--- |
| `public/assets/css/app.css` | Design system: token warna, tipografi, tombol, kartu, form, kalender, dark mode |
| `include/site.php` | Konteks bersama: setting bisnis, format rupiah, resolver path gambar |
| `include/nav.php` | Navigasi sticky + drawer mobile + tombol tema |
| `include/car_card.php` | Partial kartu mobil (dipakai landing & katalog) |
| `include/footer.php` | Footer bersama |
| `public/assets/js/ui.js` | Motion layer: pill nav, drawer, ripple, reveal saat scroll, counter, progress bar. Fail-safe: tidak ada konten yang disembunyikan bila JS/tab tidak aktif |
| `public/assets/vendor/bootstrap-icons/` | Bootstrap Icons 1.11.1 (CSS + woff2/woff) — di-vendor lokal agar ikon tetap tampil tanpa CDN |
| `admin/assets/vendor/fontawesome/` | Font Awesome 6.5 (CSS + webfonts) — di-vendor lokal untuk panel admin |
| `app/modules/Profile/views/index.php` | Area akun pelanggan (ringkasan, riwayat, dokumen, data pribadi, password, tampilan, bantuan) memakai komponen `.dash` / `.stat-card` / `.list-card` / `.booking-item` |
| `app/modules/*/views/*.php` | Halaman landing, katalog, auth, booking |

Halaman akun memakai pola **rail menu + konten**: rail 258px (sticky di desktop, menu chip berbalut baris di mobile), heading per panel, kartu statistik bernomor tabular, daftar pesanan bergaris rambut, dan status sebagai pill (`status--pending/active/done/cancel`). Seluruh form lama (unggah KTP/SIM, gerbang konfirmasi, ubah password, pemilih tema) tetap memakai endpoint yang sama; hanya lapisan tampilan dan helper JS kecil (tab, pratinjau file, toggle rincian) yang ditulis ulang.

Aturan gerak (motion) yang dipakai konsisten:

*   **Satu lapisan JS** (`ui.js`) untuk semua animasi; halaman tidak menyimpan skrip animasi sendiri.
*   **Umpan balik tekan**: setiap tombol/chip/kartu mengecil ~3% saat ditekan, plus ripple ringan.
*   **Navbar**: indikator pill bergeser mengikuti menu aktif/hover, bayangan muncul setelah scroll, drawer mobile membuka dengan transisi tinggi+opasitas.
*   **Reveal**: elemen di bawah lipatan muncul bertahap saat masuk viewport. Konten di atas lipatan tidak pernah disembunyikan, dan ada jaring pengaman 2,6 detik agar tidak ada konten tertinggal transparan.
*   **Hormat preferensi**: semua animasi mati otomatis pada `prefers-reduced-motion: reduce`.

Aturan tampilan yang dipakai konsisten di semua halaman pelanggan: satu aksen warna (biru), radius 16–30px, garis tipis 1px sebagai pemisah (bukan bayangan tebal), angka harga memakai *tabular numerals*, dan mode gelap mengikuti preferensi sistem atau pilihan tersimpan di `localStorage`.

---

## Kontributor Proyek

Proyek ini dikembangkan oleh tim kolaboratif berbakat berikut:

| Foto Profil | Nama Kontributor | Link Profil | Kontribusi Commits |
| :---: | :--- | :--- | :---: |
| <img src="https://github.com/bayuwirayudha13.png" width="55" height="55" style="border-radius:50%;" /> | **bayuwirayudha13** | [@bayuwirayudha13](https://github.com/bayuwirayudha13) | 70 commits |
| <img src="https://github.com/hafiizhilmi.png" width="55" height="55" style="border-radius:50%;" /> | **hafiizhilmi** (Hafiiz) | [@hafiizhilmi](https://github.com/hafiizhilmi) | 63 commits |
| <img src="https://github.com/RefaelTobing.png" width="55" height="55" style="border-radius:50%;" /> | **RefaelTobing** | [@RefaelTobing](https://github.com/RefaelTobing) | 20 commits |
| <img src="https://github.com/syarifatullahcev-cmyk.png" width="55" height="55" style="border-radius:50%;" /> | **syarifatullahcev-cmyk** | [@syarifatullahcev-cmyk](https://github.com/syarifatullahcev-cmyk) | 14 commits |

---

*Project-Sewa-Mobil dibuat untuk menyelesaikan Final Project Mata Kuliah Pemrograman Berbasis Web tahun ajaran 2025/2026.* 
