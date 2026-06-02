# Project Sewa Mobil

Sistem Informasi Penyewaan Mobil berbasis **PHP Native** dengan arsitektur **MVC (Model-View-Controller) Kustom** yang dirancang secara modern, responsif, dan aman. Aplikasi ini menyediakan solusi lengkap baik untuk pelanggan (pencarian, verifikasi dokumen, booking, pembayaran terintegrasi) maupun untuk administrator (manajemen mobil, verifikasi dokumen, kelola pesanan, statistik bisnis).

---

## Tech Stack & Integrasi

Berikut adalah teknologi dan layanan pihak ketiga yang digunakan dalam proyek ini:

*   **Core Backend:** PHP 8.x (Native dengan MVC kustom)
*   **Database:** MySQL / MariaDB (Interaksi via PDO & MySQLi)
*   **Frontend:** HTML5, CSS3, JavaScript (ES6)
*   **UI Framework & Styling:** Bootstrap 5.3.2
*   **Typography:** Google Fonts (Plus Jakarta Sans)
*   **Integrasi Midtrans:** Payment Gateway Sandbox untuk pemrosesan pembayaran real-time.
*   **Integrasi Fonnte:** Layanan API WhatsApp untuk notifikasi pemesanan & verifikasi otomatis ke pelanggan.
*   **Gambar Dinamis:** Unsplash Source API.

---

## Fitur Utama

### Portal Pelanggan (Customer Portal)
1.  **Landing Page:**
    *   Tampilan hero section.
    *   Menampilkan katalog mobil terbaru dengan detail harga sewa per hari.
2.  **Sistem Autentikasi:**
    *   Registrasi dan Login pelanggan.
    *   Proteksi menggunakan token **CSRF (Cross-Site Request Forgery)**.
    *   Enkripsi password menggunakan `password_hash()`.
3.  **Verifikasi Identitas Pengguna:**
    *   Pengunggahan berkas KTP dan SIM secara langsung melalui halaman profil pelanggan.
    *   Status verifikasi *real-time*: `unverified`, `pending`, `verified`, atau `rejected`.
4.  **Halaman Utama (Homepage) & Filtrasi:**
    *   Pencarian mobil secara dinamis.
    *   Filtering berdasarkan kategori (MPV, SUV, Sedan, EV), jenis bahan bakar (Bensin, Diesel, Listrik), dan kapasitas tempat duduk (seats).
5.  **Pemesanan & Sewa Mobil (Booking System):**
    *   Formulir input tanggal mulai dan berakhir, lokasi penjemputan/pengembalian, alamat, serta opsi tambahan sopir (*add-on driver*).
    *   Kalkulasi otomatis total harga sewa secara *real-time* berdasarkan durasi sewa dan tarif harian mobil.
    *   Pelanggan harus terverifikasi (`verified`) sebelum dapat menyewa mobil untuk meminimalkan risiko keamanan bisnis.
6.  **Gateway Pembayaran Sandbox (Midtrans):**
    *   Integrasi pembayaran online dengan status pemesanan terupdate secara otomatis (Pending, Sukses, Gagal/Ditolak).
7.  **Riwayat Pesanan & Profil:**
    *   Melihat daftar seluruh transaksi yang pernah dilakukan beserta status terkininya (`pending`, `confirmed`, `completed`, `cancelled`).

### Panel Administrator (Admin Portal)
1.  **Dashboard Utama:**
    *   Menampilkan statistik seperti total pesanan pending, jumlah pelanggan aktif, jumlah armada mobil, dan total transaksi sukses.
2.  **Manajemen Armada Mobil (CRUD):**
    *   Menambah, mengubah, menampilkan, dan menghapus data mobil (Merek, Model, Tahun, Kategori, Bahan Bakar, Kapasitas Kursi, Stok, Tarif Sewa, dan unggah Gambar).
3.  **Kelola Pelanggan (Customer Management):**
    *   Melihat daftar detail seluruh pelanggan yang terdaftar.
4.  **Tinjau & Verifikasi Dokumen:**
    *   Memeriksa berkas KTP & SIM yang diunggah oleh pelanggan.
    *   Tombol persetujuan (`approve`) atau penolakan (`reject`) status verifikasi pelanggan secara instan.
5.  **Manajemen Pesanan (Order Management):**
    *   Memantau pemesanan masuk dan mengubah status pesanan pelanggan (`pending`, `confirmed`, `completed`, `cancelled`).
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

5.  **Akses Aplikasi:**
    *   **Customer Portal:** `http://localhost/Project-Sewa-Mobil/public/`
    *   **Admin Portal:** `http://localhost/Project-Sewa-Mobil/admin/`

---

## Kontributor Proyek

Proyek ini dikembangkan oleh tim kolaboratif berbakat berikut:

| Foto Profil | Nama Kontributor | Tautan Profil | Kontribusi Commits |
| :---: | :--- | :--- | :---: |
| <img src="https://github.com/bayuwirayudha13.png" width="55" height="55" style="border-radius:50%;" /> | **bayuwirayudha13** | [@bayuwirayudha13](https://github.com/bayuwirayudha13) | 70 commits |
| <img src="https://github.com/hafiizhilmi.png" width="55" height="55" style="border-radius:50%;" /> | **hafiizhilmi** (Hafiiz) | [@hafiizhilmi](https://github.com/hafiizhilmi) | 63 commits |
| <img src="https://github.com/RefaelTobing.png" width="55" height="55" style="border-radius:50%;" /> | **RefaelTobing** | [@RefaelTobing](https://github.com/RefaelTobing) | 20 commits |
| <img src="https://github.com/syarifatullahcev-cmyk.png" width="55" height="55" style="border-radius:50%;" /> | **syarifatullahcev-cmyk** | [@syarifatullahcev-cmyk](https://github.com/syarifatullahcev-cmyk) | 14 commits |

---

*Project-Sewa-Mobil dibuat untuk menyelesaikan Final Project Mata Kuliah Pemrograman Berbasis Web tahun ajaran 2025/2026.* 
