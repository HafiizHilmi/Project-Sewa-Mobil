---
name: rental-mobil-backend
description: Dokumentasi backend lengkap untuk Sistem Informasi Rental Mobil berbasis PHP Native OOP dengan arsitektur HMVC Modular. Gunakan skill ini saat mengerjakan backend sistem rental mobil, mencakup: skema database (ERD 8 tabel), routing lengkap semua modul, logika bisnis tiap Controller, method Model beserta query SQL-nya, sistem anti-collision booking, RBAC, keamanan (CSRF, XSS, SQL Injection), dan konvensi kode. Trigger skill ini setiap kali ada pertanyaan atau tugas yang berkaitan dengan modul Auth, Inventory, Booking, Verification, Maintenance, Payment, Tracking, Notification, atau struktur file project rental mobil ini.
---

# Backend Documentation — Sistem Informasi Rental Mobil

> \*\*Arsitektur:\*\* PHP Native OOP · HMVC Modular · PDO MySQL  
> \*\*Pendekatan:\*\* Backend-Driven Development  
> \*\*Versi Dokumen:\*\* 1.0

---

## Daftar Isi

1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Konfigurasi & Environment](#2-konfigurasi--environment)
3. [Front Controller & Routing](#3-front-controller--routing)
4. [Skema Database (ERD)](#4-skema-database-erd)
5. [Modul Auth](#5-modul-auth)
6. [Modul Dashboard](#6-modul-dashboard)
7. [Modul Inventory](#7-modul-inventory)
8. [Modul Booking](#8-modul-booking)
9. [Modul Verification](#9-modul-verification)
10. [Modul Maintenance](#10-modul-maintenance)
11. [Modul PaymentDummy](#11-modul-paymentdummy)
12. [Modul TrackingDummy](#12-modul-trackingdummy)
13. [Modul NotificationDummy](#13-modul-notificationdummy)
14. [Keamanan & Validasi Global](#14-keamanan--validasi-global)
15. [Konvensi & Standar Kode](#15-konvensi--standar-kode)

---

## 1. Arsitektur Sistem

### 1.1 Pola Arsitektur yang Diterapkan

| Pola | Implementasi |
|------|-------------|
| **HMVC Modular** | Setiap fitur dikemas dalam modul mandiri di `/app/modules/{NamaModul}/` dengan Controller, Model, dan Views sendiri |
| **Front Controller** | Semua request HTTP masuk ke `public/index.php`, lalu di-dispatch ke modul yang sesuai |
| **Separation of Concerns** | View tidak boleh query DB langsung; semua logika bisnis di Controller, semua akses data di Model |
| **RBAC** | Validasi hak akses (`admin` / `user`) dilakukan di `\_\_construct()` setiap Controller |
| **Anti-Collision** | Model Booking memiliki method khusus untuk mendeteksi overlap tanggal sebelum data disimpan |

### 1.2 Alur Request

```

Browser

&#x20; │

&#x20; ▼

public/.htaccess        ← Redirect semua request non-aset ke index.php

&#x20; │

&#x20; ▼

public/index.php        ← Front Controller

&#x20; ├── session\_start()

&#x20; ├── Sanitasi input global ($\_GET, $\_POST, $\_FILES)

&#x20; ├── Parse URL → tentukan \[modul] \& \[aksi]

&#x20; └── Dispatch ke Controller yang sesuai

&#x20;       │

&#x20;       ▼

&#x20;   Controller          ← Validasi RBAC di \_\_construct()

&#x20;       ├── Panggil method Model (query DB via PDO)

&#x20;       └── Load View dengan data hasil Model

```

### 1.3 Struktur URL

Format URL yang digunakan:

```

https://domain.com/{modul}/{aksi}/{parameter\_opsional}

```

Contoh:
- `GET /inventory/list` → halaman katalog mobil
- `GET /booking/checkout/5` → form checkout untuk mobil ID 5
- `POST /auth/login` → proses login
- `GET /admin/dashboard` → statistik admin

---

## 2. Konfigurasi & Environment

### 2.1 File `.env`

File ini **tidak boleh** masuk ke repositori Git (sudah di-ignore via `.gitignore`).

```

DB\_HOST=localhost

DB\_PORT=3306

DB\_NAME=rental\_mobil

DB\_USER=root

DB\_PASS=

APP\_ENV=development

APP\_URL=http://localhost/rental-mobil/public

```

### 2.2 `app/config/database.php`

Bertanggung jawab untuk:
- Membaca variabel dari file `.env`
- Membuat koneksi PDO tunggal (Singleton pattern)
- Mengatur PDO error mode ke `ERRMODE\_EXCEPTION`
- Mengatur `ATTR\_DEFAULT\_FETCH\_MODE` ke `FETCH\_ASSOC`
- Melempar Exception jika koneksi gagal

**Kelas:** `Database`
**Method utama:** `getConnection() : PDO` (static, Singleton)

### 2.3 `public/.htaccess`

Aturan yang harus ada:
- Aktifkan `mod\_rewrite`
- Cegah akses langsung ke folder `/app`
- Redirect semua request yang bukan file/folder nyata ke `index.php`
- Blokir akses ke `.env`

---

## 3. Front Controller & Routing

### 3.1 `public/index.php` — Tanggung Jawab

1. **Inisialisasi session** — `session\_start()` dengan konfigurasi aman (HttpOnly, SameSite)
2. **Sanitasi input global** — semua `$\_GET` dan `$\_POST` di-strip tags dan di-trim
3. **Parse URL** — ambil segmen path dari `$\_SERVER\['REQUEST\_URI']`
4. **Routing** — map segmen pertama URL ke nama modul, segmen kedua ke nama method Controller
5. **Dispatch** — instansiasi Controller yang sesuai dan panggil method-nya
6. **Fallback** — jika modul/aksi tidak ditemukan, redirect ke halaman 404

### 3.2 Tabel Routing Lengkap

#### Auth Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/auth/login` | `AuthController@showLogin` | Public |
| POST | `/auth/login` | `AuthController@processLogin` | Public |
| GET | `/auth/register` | `AuthController@showRegister` | Public |
| POST | `/auth/register` | `AuthController@processRegister` | Public |
| GET | `/auth/logout` | `AuthController@logout` | Auth |

#### Dashboard Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/dashboard` | `DashboardController@index` | Admin |

#### Inventory Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/inventory/list` | `CarController@adminList` | Admin |
| GET | `/inventory/create` | `CarController@showCreate` | Admin |
| POST | `/inventory/create` | `CarController@processCreate` | Admin |
| GET | `/inventory/edit/{id}` | `CarController@showEdit` | Admin |
| POST | `/inventory/edit/{id}` | `CarController@processEdit` | Admin |
| POST | `/inventory/delete/{id}` | `CarController@delete` | Admin |
| GET | `/catalog` | `CarController@userCatalog` | User |
| GET | `/catalog/detail/{id}` | `CarController@detail` | User |

#### Booking Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/booking/checkout/{car\_id}` | `BookingController@showCheckout` | User |
| POST | `/booking/checkout` | `BookingController@processCheckout` | User |
| GET | `/booking/history` | `BookingController@userHistory` | User |
| GET | `/booking/orders` | `BookingController@adminOrders` | Admin |
| POST | `/booking/update-status/{id}` | `BookingController@updateStatus` | Admin |
| GET | `/booking/availability/{car\_id}` | `BookingController@getAvailability` | User |

#### Verification Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/verify/upload` | `VerifyController@showUpload` | User |
| POST | `/verify/upload` | `VerifyController@processUpload` | User |
| GET | `/verify/approval` | `VerifyController@adminApproval` | Admin |
| POST | `/verify/approve/{id}` | `VerifyController@approve` | Admin |
| POST | `/verify/reject/{id}` | `VerifyController@reject` | Admin |

#### Maintenance Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/maintenance/schedule` | `MaintenanceController@showSchedule` | Admin |
| POST | `/maintenance/create` | `MaintenanceController@create` | Admin |
| POST | `/maintenance/delete/{id}` | `MaintenanceController@delete` | Admin |

#### Payment Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/payment/simulate/{booking\_id}` | `PaymentController@showSimulate` | User |
| POST | `/payment/process/{booking\_id}` | `PaymentController@processPayment` | User |
| GET | `/payment/invoice/{booking\_id}` | `PaymentController@showInvoice` | User |

#### Tracking Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/tracking/map` | `TrackingController@adminMap` | Admin |
| GET | `/tracking/log/{car\_id}` | `TrackingController@travelLog` | Admin |
| GET | `/tracking/user/{booking\_id}` | `TrackingController@userTracking` | User (aktif) |
| POST | `/tracking/update` | `TrackingController@updateCoordinate` | Admin |

#### Notification Routes
| Method | URL | Controller@Method | Akses |
|--------|-----|-------------------|-------|
| GET | `/notification/popup/{booking\_id}` | `NotificationController@showPopup` | User |

---

## 4. Skema Database (ERD)

### 4.1 Daftar Tabel

| Tabel | Modul | Keterangan |
|-------|-------|------------|
| `users` | Auth | Data akun pengguna dan admin |
| `cars` | Inventory | Data armada kendaraan |
| `car\_categories` | Inventory | Kategori kendaraan (SUV, MPV, dll.) |
| `bookings` | Booking | Data transaksi penyewaan |
| `documents` | Verification | Berkas KTP/SIM yang diunggah |
| `maintenance\_schedules` | Maintenance | Jadwal servis kendaraan |
| `payments` | PaymentDummy | Data simulasi pembayaran |
| `car\_coordinates` | TrackingDummy | Koordinat terkini tiap kendaraan |
| `travel\_logs` | TrackingDummy | Log histori perjalanan |

---

### 4.2 Detail Skema Tabel

#### Tabel `users`
```sql

CREATE TABLE users (

&#x20;   id            INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   name          VARCHAR(100)  NOT NULL,

&#x20;   email         VARCHAR(150)  NOT NULL UNIQUE,

&#x20;   password      VARCHAR(255)  NOT NULL,          -- bcrypt hash

&#x20;   phone         VARCHAR(20)   NULL,

&#x20;   role          ENUM('admin', 'user') NOT NULL DEFAULT 'user',

&#x20;   is\_verified   TINYINT(1)    NOT NULL DEFAULT 0, -- dokumen ID sudah diverifikasi admin

&#x20;   created\_at    TIMESTAMP     NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   updated\_at    TIMESTAMP     NOT NULL DEFAULT CURRENT\_TIMESTAMP ON UPDATE CURRENT\_TIMESTAMP

);

```

#### Tabel `car\_categories`
```sql

CREATE TABLE car\_categories (

&#x20;   id    INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   name  VARCHAR(50) NOT NULL UNIQUE  -- 'SUV', 'MPV', 'Sedan', 'City Car', dll.

);

```

#### Tabel `cars`
```sql

CREATE TABLE cars (

&#x20;   id              INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   category\_id     INT UNSIGNED NOT NULL,

&#x20;   brand           VARCHAR(50)  NOT NULL,

&#x20;   model           VARCHAR(100) NOT NULL,

&#x20;   year            YEAR         NOT NULL,

&#x20;   license\_plate   VARCHAR(20)  NOT NULL UNIQUE,

&#x20;   transmission    ENUM('manual', 'automatic') NOT NULL,

&#x20;   capacity        TINYINT UNSIGNED NOT NULL,       -- jumlah penumpang

&#x20;   price\_per\_day   DECIMAL(10,2) NOT NULL,

&#x20;   description     TEXT          NULL,

&#x20;   image\_path      VARCHAR(255)  NULL,              -- path relatif dari /public/uploads/

&#x20;   status          ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',

&#x20;   created\_at      TIMESTAMP    NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   updated\_at      TIMESTAMP    NOT NULL DEFAULT CURRENT\_TIMESTAMP ON UPDATE CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (category\_id) REFERENCES car\_categories(id) ON DELETE RESTRICT

);

```

#### Tabel `bookings`
```sql

CREATE TABLE bookings (

&#x20;   id              INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   user\_id         INT UNSIGNED NOT NULL,

&#x20;   car\_id          INT UNSIGNED NOT NULL,

&#x20;   start\_date      DATE         NOT NULL,

&#x20;   end\_date        DATE         NOT NULL,

&#x20;   total\_days      INT UNSIGNED NOT NULL,

&#x20;   total\_price     DECIMAL(12,2) NOT NULL,

&#x20;   pickup\_location VARCHAR(255) NULL,

&#x20;   status          ENUM('pending', 'booked', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',

&#x20;   notes           TEXT         NULL,

&#x20;   created\_at      TIMESTAMP    NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   updated\_at      TIMESTAMP    NOT NULL DEFAULT CURRENT\_TIMESTAMP ON UPDATE CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (user\_id) REFERENCES users(id)  ON DELETE CASCADE,

&#x20;   FOREIGN KEY (car\_id)  REFERENCES cars(id)   ON DELETE RESTRICT

);

```

> \*\*Catatan Anti-Collision:\*\* Sebelum INSERT, Model harus menjalankan query deteksi overlap:  
> Cari booking dengan `car\_id` yang sama, status bukan `cancelled`, dan rentang tanggalnya \*\*beririsan\*\* dengan tanggal yang diminta. Jika ada hasil → tolak booking.

#### Tabel `documents`
```sql

CREATE TABLE documents (

&#x20;   id           INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   user\_id      INT UNSIGNED NOT NULL UNIQUE,       -- satu user, satu set dokumen

&#x20;   ktp\_path     VARCHAR(255) NULL,

&#x20;   sim\_path     VARCHAR(255) NULL,

&#x20;   status       ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',

&#x20;   rejection\_reason VARCHAR(255) NULL,

&#x20;   reviewed\_at  TIMESTAMP NULL,

&#x20;   created\_at   TIMESTAMP NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   updated\_at   TIMESTAMP NOT NULL DEFAULT CURRENT\_TIMESTAMP ON UPDATE CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (user\_id) REFERENCES users(id) ON DELETE CASCADE

);

```

#### Tabel `maintenance\_schedules`
```sql

CREATE TABLE maintenance\_schedules (

&#x20;   id          INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   car\_id      INT UNSIGNED NOT NULL,

&#x20;   start\_date  DATE         NOT NULL,

&#x20;   end\_date    DATE         NOT NULL,

&#x20;   description VARCHAR(255) NULL,

&#x20;   created\_at  TIMESTAMP    NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (car\_id) REFERENCES cars(id) ON DELETE CASCADE

);

```

> \*\*Blokir Otomatis:\*\* Saat admin membuat jadwal maintenance, status mobil di tabel `cars` harus di-UPDATE menjadi `maintenance` jika tanggal hari ini termasuk dalam rentang. Saat maintenance selesai (melewati `end\_date`), status kembali ke `available`.

#### Tabel `payments`
```sql

CREATE TABLE payments (

&#x20;   id              INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   booking\_id      INT UNSIGNED NOT NULL UNIQUE,

&#x20;   payment\_method  ENUM('virtual\_account', 'qris', 'credit\_card') NOT NULL,

&#x20;   amount          DECIMAL(12,2) NOT NULL,

&#x20;   status          ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',

&#x20;   paid\_at         TIMESTAMP NULL,

&#x20;   created\_at      TIMESTAMP NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (booking\_id) REFERENCES bookings(id) ON DELETE CASCADE

);

```

#### Tabel `car\_coordinates`
```sql

CREATE TABLE car\_coordinates (

&#x20;   id         INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   car\_id     INT UNSIGNED NOT NULL UNIQUE,   -- satu baris per mobil (upsert)

&#x20;   latitude   DECIMAL(10,8) NOT NULL,

&#x20;   longitude  DECIMAL(11,8) NOT NULL,

&#x20;   updated\_at TIMESTAMP NOT NULL DEFAULT CURRENT\_TIMESTAMP ON UPDATE CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (car\_id) REFERENCES cars(id) ON DELETE CASCADE

);

```

#### Tabel `travel\_logs`
```sql

CREATE TABLE travel\_logs (

&#x20;   id          INT UNSIGNED AUTO\_INCREMENT PRIMARY KEY,

&#x20;   car\_id      INT UNSIGNED NOT NULL,

&#x20;   booking\_id  INT UNSIGNED NULL,

&#x20;   latitude    DECIMAL(10,8) NOT NULL,

&#x20;   longitude   DECIMAL(11,8) NOT NULL,

&#x20;   logged\_at   TIMESTAMP NOT NULL DEFAULT CURRENT\_TIMESTAMP,

&#x20;   FOREIGN KEY (car\_id)     REFERENCES cars(id)     ON DELETE CASCADE,

&#x20;   FOREIGN KEY (booking\_id) REFERENCES bookings(id) ON DELETE SET NULL

);

```

### 4.3 Relasi Antar Tabel (ERD Ringkas)

```

car\_categories ──< cars >──────────── maintenance\_schedules

&#x20;                   │

&#x20;                   └──< bookings >── payments

&#x20;                         │

&#x20;                   users ┘

&#x20;                     │

&#x20;                   documents



cars >── car\_coordinates

cars >── travel\_logs >── bookings

```

**Keterangan:**
- `>──` = One-to-Many (satu ke banyak)
- `──<` = Many-to-One

---

## 5. Modul Auth

**Lokasi:** `/app/modules/Auth/`

### 5.1 AuthModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `findByEmail(string $email)` | `SELECT \* FROM users WHERE email = ?` | `array\\|false` |
| `createUser(array $data)` | `INSERT INTO users (name, email, password, phone)` | `int` (lastInsertId) |
| `getUserById(int $id)` | `SELECT \* FROM users WHERE id = ?` | `array\\|false` |

### 5.2 AuthController — Logika Bisnis

#### `processLogin()`
1. Ambil `email` dan `password` dari `$\_POST`
2. Validasi: email tidak kosong, format email valid, password tidak kosong
3. Panggil `AuthModel::findByEmail($email)`
4. Jika user tidak ditemukan → redirect balik dengan pesan error
5. Verifikasi password dengan `password\_verify($input, $hash)`
6. Jika salah → redirect balik dengan pesan error (jangan sebutkan mana yang salah)
7. Jika benar → set session: `$\_SESSION\['user\_id']`, `$\_SESSION\['user\_role']`, `$\_SESSION\['user\_name']`
8. Redirect berdasarkan role: Admin → `/dashboard`, User → `/catalog`

#### `processRegister()`
1. Ambil `name`, `email`, `password`, `password\_confirm`, `phone` dari `$\_POST`
2. Validasi:
   - Semua field wajib tidak kosong
   - Format email valid
   - Password minimal 8 karakter
   - `password` === `password\_confirm`
3. Cek duplikasi email via `AuthModel::findByEmail()`
4. Hash password dengan `password\_hash($password, PASSWORD\_BCRYPT)`
5. Simpan via `AuthModel::createUser()`
6. Redirect ke halaman login dengan pesan sukses

#### `logout()`
1. Unset semua session: `session\_unset()`
2. Destroy session: `session\_destroy()`
3. Redirect ke `/auth/login`

### 5.3 RBAC Helper

Setiap Controller memiliki method `requireRole(string $role)` yang dipanggil di `\_\_construct()`. Logikanya:
- Cek apakah `$\_SESSION\['user\_id']` ada
- Jika tidak → redirect ke `/auth/login`
- Cek apakah `$\_SESSION\['user\_role']` sesuai dengan `$role` yang diminta
- Jika tidak sesuai → redirect ke halaman 403 atau halaman utama

---

## 6. Modul Dashboard

**Lokasi:** `/app/modules/Dashboard/`
**Akses:** Admin only

### 6.1 DashboardModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `getTotalRevenue()` | `SELECT SUM(amount) FROM payments WHERE status = 'paid'` | `float` |
| `getRevenueByMonth(int $year)` | `SELECT MONTH(paid\_at), SUM(amount) FROM payments WHERE YEAR(paid\_at) = ? AND status='paid' GROUP BY MONTH(paid\_at)` | `array` |
| `getMostRentedCars(int $limit)` | `SELECT car\_id, COUNT(\*) as total FROM bookings GROUP BY car\_id ORDER BY total DESC LIMIT ?` (JOIN dengan tabel `cars`) | `array` |
| `getTotalActiveBookings()` | `SELECT COUNT(\*) FROM bookings WHERE status = 'active'` | `int` |
| `getTotalCars()` | `SELECT COUNT(\*) FROM cars` | `int` |
| `getTotalUsers()` | `SELECT COUNT(\*) FROM users WHERE role = 'user'` | `int` |
| `getPendingVerifications()` | `SELECT COUNT(\*) FROM documents WHERE status = 'pending'` | `int` |

### 6.2 DashboardController — Logika Bisnis

#### `index()`
1. Panggil semua method DashboardModel
2. Kirim data ke View `admin\_stats.php` dalam bentuk array asosiatif
3. Data yang dikirim ke View: `total\_revenue`, `revenue\_chart` (array per bulan), `most\_rented` (array), `stats` (ringkasan)

---

## 7. Modul Inventory

**Lokasi:** `/app/modules/Inventory/`

### 7.1 CarModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `getAllCars(array $filters)` | SELECT dengan WHERE dinamis berdasarkan filter kategori, transmisi, kapasitas, rentang harga | `array` |
| `getCarById(int $id)` | SELECT dengan JOIN ke `car\_categories` | `array\\|false` |
| `createCar(array $data)` | INSERT ke tabel `cars` | `int` (lastInsertId) |
| `updateCar(int $id, array $data)` | UPDATE tabel `cars` | `bool` |
| `deleteCar(int $id)` | DELETE dari tabel `cars` (cek dulu tidak ada booking aktif) | `bool` |
| `getAllCategories()` | `SELECT \* FROM car\_categories` | `array` |
| `getUnavailableDates(int $car\_id)` | Gabungan tanggal dari `bookings` (status bukan cancelled) dan `maintenance\_schedules` untuk car_id tertentu | `array` |

### 7.2 CarController — Logika Bisnis

#### `processCreate()` (Admin)
1. Validasi input: brand, model, year, license_plate, transmission, capacity, price_per_day wajib diisi
2. Validasi `price\_per\_day` harus numerik dan positif
3. Proses upload foto:
   - Cek ekstensi file: hanya jpg, jpeg, png, webp
   - Cek ukuran file: maksimal 2MB
   - Generate nama file unik: `uniqid('car\_') . '.' . $ext`
   - Simpan ke `/public/uploads/cars/`
4. Simpan data via `CarModel::createCar()`
5. Redirect ke halaman daftar admin dengan pesan sukses

#### `processEdit()` (Admin)
1. Ambil data lama via `CarModel::getCarById()`
2. Validasi input yang berubah
3. Jika ada file foto baru, proses upload dan hapus foto lama
4. Update via `CarModel::updateCar()`

#### `delete()` (Admin)
1. Cek apakah ada booking dengan status `active` atau `booked` untuk mobil ini
2. Jika ada → tolak penghapusan, tampilkan error
3. Jika tidak ada → hapus via `CarModel::deleteCar()`, hapus juga file foto dari server

#### `userCatalog()` (User)
1. Ambil filter dari `$\_GET`: `category`, `min\_price`, `max\_price`, `transmission`, `capacity`, `search`
2. Kirim filter ke `CarModel::getAllCars($filters)`
3. Tampilkan hasil ke View `user\_catalog.php`

---

## 8. Modul Booking

**Lokasi:** `/app/modules/Booking/`

### 8.1 BookingModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `checkCollision(int $car\_id, string $start, string $end)` | Cek overlap di `bookings` dan `maintenance\_schedules` | `bool` (true = ada konflik) |
| `createBooking(array $data)` | INSERT ke tabel `bookings` | `int` (lastInsertId) |
| `getBookingById(int $id)` | SELECT dengan JOIN ke `users` dan `cars` | `array\\|false` |
| `getBookingsByUser(int $user\_id)` | SELECT riwayat booking satu user, ORDER BY created_at DESC | `array` |
| `getAllBookings(array $filters)` | SELECT semua booking untuk admin, support filter status | `array` |
| `updateStatus(int $id, string $status)` | UPDATE status booking, UPDATE status mobil jika perlu | `bool` |
| `getUnavailableDateRanges(int $car\_id)` | Kembalikan array `\[{start, end}]` untuk kalender FullCalendar | `array` |

### 8.2 Logika Anti-Collision (`checkCollision`)

Kondisi overlap yang harus dideteksi. Dua rentang `\[A\_start, A\_end]` dan `\[B\_start, B\_end]` dianggap **beririsan** jika:

```

A\_start <= B\_end AND A\_end >= B\_start

```

Query SQL yang digunakan:

```sql

\-- Cek di tabel bookings

SELECT id FROM bookings

WHERE car\_id = :car\_id

&#x20; AND status NOT IN ('cancelled', 'completed')

&#x20; AND start\_date <= :end\_date

&#x20; AND end\_date   >= :start\_date



\-- Cek di tabel maintenance\_schedules

SELECT id FROM maintenance\_schedules

WHERE car\_id = :car\_id

&#x20; AND start\_date <= :end\_date

&#x20; AND end\_date   >= :start\_date

```

Jika salah satu query menghasilkan baris → ada konflik → booking ditolak.

### 8.3 BookingController — Logika Bisnis

#### `showCheckout()` (User)
1. Cek apakah user sudah memiliki dokumen dengan status `approved`; jika belum → redirect ke `/verify/upload` dengan pesan peringatan
2. Ambil data mobil via `CarModel::getCarById($car\_id)`
3. Ambil daftar tanggal tidak tersedia via `BookingModel::getUnavailableDateRanges($car\_id)`
4. Kirim data ke View untuk dipakai FullCalendar

#### `processCheckout()` (User)
1. Ambil `car\_id`, `start\_date`, `end\_date`, `pickup\_location`, `notes` dari `$\_POST`
2. Validasi format tanggal (Y-m-d)
3. Validasi `start\_date` tidak di masa lalu
4. Validasi `start\_date` < `end\_date`
5. Hitung `total\_days` = selisih hari
6. Ambil `price\_per\_day` dari DB, hitung `total\_price`
7. Jalankan `BookingModel::checkCollision()` → jika konflik, redirect balik dengan error
8. Simpan booking via `BookingModel::createBooking()` dengan status `pending`
9. Redirect ke `/payment/simulate/{booking\_id}`

#### `updateStatus()` (Admin)
1. Ambil status baru dari `$\_POST`
2. Validasi transisi status yang diizinkan:
   - `pending` → `booked` atau `cancelled`
   - `booked` → `active` atau `cancelled`
   - `active` → `completed`
3. Jika status menjadi `active` → UPDATE status mobil di tabel `cars` menjadi `rented`
4. Jika status menjadi `completed` atau `cancelled` → UPDATE status mobil menjadi `available`
5. Simpan via `BookingModel::updateStatus()`

---

## 9. Modul Verification

**Lokasi:** `/app/modules/Verification/`

### 9.1 VerifyModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `getDocumentByUser(int $user\_id)` | `SELECT \* FROM documents WHERE user\_id = ?` | `array\\|false` |
| `createOrUpdateDocument(int $user\_id, array $paths)` | INSERT atau UPDATE (UPSERT) data dokumen | `bool` |
| `getAllPendingDocuments()` | SELECT JOIN dengan tabel `users`, WHERE `status = 'pending'` | `array` |
| `updateDocumentStatus(int $id, string $status, string $reason)` | UPDATE status dan `rejection\_reason`, UPDATE `users.is\_verified` | `bool` |

### 9.2 VerifyController — Logika Bisnis

#### `processUpload()` (User)
1. Cek apakah file KTP dan/atau SIM ada di `$\_FILES`
2. Validasi tiap file:
   - Ekstensi: jpg, jpeg, png, pdf
   - Ukuran: maksimal 5MB
   - Cek `$\_FILES\['file']\['error']` tidak ada error upload
3. Generate nama file: `{user\_id}\_ktp\_{timestamp}.jpg`
4. Simpan ke `/public/uploads/documents/`
5. Simpan path via `VerifyModel::createOrUpdateDocument()`
6. Set status kembali ke `pending` jika sebelumnya `rejected`

#### `approve()` (Admin)
1. Ambil dokumen via `VerifyModel::getDocumentByUser()`
2. Update status menjadi `approved` via `VerifyModel::updateDocumentStatus()`
3. Update `users.is\_verified = 1`

#### `reject()` (Admin)
1. Ambil alasan penolakan dari `$\_POST\['reason']`
2. Update status menjadi `rejected`, simpan `rejection\_reason`
3. Update `users.is\_verified = 0`

---

## 10. Modul Maintenance

**Lokasi:** `/app/modules/Maintenance/`

### 10.1 MaintenanceModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `getAllSchedules()` | SELECT JOIN dengan `cars` ORDER BY `start\_date` ASC | `array` |
| `getSchedulesByCar(int $car\_id)` | SELECT WHERE `car\_id = ?` | `array` |
| `createSchedule(array $data)` | INSERT ke `maintenance\_schedules`, update status mobil jika perlu | `int` |
| `deleteSchedule(int $id)` | DELETE jadwal, update status mobil kembali `available` jika tidak ada jadwal lain | `bool` |
| `checkAndUpdateCarStatuses()` | Cek semua jadwal terhadap tanggal hari ini, update status mobil secara batch | `void` |

### 10.2 MaintenanceController — Logika Bisnis

#### `create()` (Admin)
1. Validasi input: `car\_id`, `start\_date`, `end\_date`, `description`
2. Validasi `start\_date` tidak lebih dari `end\_date`
3. Cek apakah ada booking aktif (`booked` / `active`) di rentang tanggal tersebut
4. Jika ada booking → tolak, tampilkan pesan konflik
5. Simpan jadwal via `MaintenanceModel::createSchedule()`
6. Jika tanggal hari ini masuk dalam rentang → UPDATE status mobil menjadi `maintenance`

#### `delete()` (Admin)
1. Hapus jadwal via `MaintenanceModel::deleteSchedule()`
2. Setelah hapus, cek apakah masih ada jadwal maintenance aktif lain untuk mobil ini
3. Jika tidak ada → UPDATE status mobil kembali ke `available`

---

## 11. Modul PaymentDummy

**Lokasi:** `/app/modules/PaymentDummy/`

### 11.1 PaymentModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `getPaymentByBooking(int $booking\_id)` | `SELECT \* FROM payments WHERE booking\_id = ?` | `array\\|false` |
| `createPayment(int $booking\_id, string $method, float $amount)` | INSERT ke tabel `payments` dengan status `pending` | `int` |
| `markAsPaid(int $id)` | UPDATE `status = 'paid'`, `paid\_at = NOW()` | `bool` |
| `getInvoiceData(int $booking\_id)` | SELECT JOIN `payments`, `bookings`, `users`, `cars` | `array\\|false` |

### 11.2 PaymentController — Logika Bisnis

#### `showSimulate()` (User)
1. Ambil data booking via `BookingModel::getBookingById($booking\_id)`
2. Pastikan `booking.user\_id` === `$\_SESSION\['user\_id']` (cegah akses booking orang lain)
3. Jika belum ada record payment, buat dulu via `PaymentModel::createPayment()`
4. Kirim data ke View: total tagihan, pilihan metode pembayaran, nomor VA dummy yang di-generate

#### `processPayment()` (User)
1. Ambil `payment\_method` dari `$\_POST`
2. Validasi metode: harus salah satu dari `virtual\_account`, `qris`, `credit\_card`
3. Update record payment: set `payment\_method` dan status tetap `pending` (simulasi menunggu konfirmasi)
4. Untuk keperluan demo: langsung panggil `PaymentModel::markAsPaid()` setelah 1-2 detik (sleep simulasi)
5. Update status booking dari `pending` → `booked` via `BookingModel::updateStatus()`
6. Redirect ke `/payment/invoice/{booking\_id}`

#### `showInvoice()` (User)
1. Ambil data lengkap via `PaymentModel::getInvoiceData($booking\_id)`
2. Pastikan data milik user yang sedang login
3. Kirim ke View `e\_invoice.php` untuk ditampilkan dan bisa di-download sebagai PDF (via browser print atau library JS)

### 11.3 Generate Nomor VA Dummy

Nomor Virtual Account dummy di-generate di Controller (bukan disimpan ke DB secara permanen):

```

Format: {kode\_bank\_3digit} + {user\_id\_4digit\_padded} + {booking\_id\_5digit\_padded}

Contoh: BCA-00012-00023 → 70800120000023

```

---

## 12. Modul TrackingDummy

**Lokasi:** `/app/modules/TrackingDummy/`

### 12.1 TrackingModel — Method

| Method | Query/Logika | Return |
|--------|-------------|--------|
| `getAllCoordinates()` | `SELECT cc.\*, c.brand, c.model, c.license\_plate FROM car\_coordinates cc JOIN cars c` | `array` |
| `getCoordinatesByCar(int $car\_id)` | SELECT untuk satu mobil | `array\\|false` |
| `upsertCoordinate(int $car\_id, float $lat, float $lng)` | INSERT ... ON DUPLICATE KEY UPDATE | `bool` |
| `getTravelLog(int $car\_id, int $limit)` | SELECT dari `travel\_logs` WHERE `car\_id = ?` ORDER BY `logged\_at` DESC LIMIT ? | `array` |
| `addTravelLog(int $car\_id, int $booking\_id, float $lat, float $lng)` | INSERT ke `travel\_logs` | `bool` |
| `getActiveBookingForUser(int $user\_id)` | SELECT booking dengan `user\_id = ?` dan `status = 'active'` | `array\\|false` |

### 12.2 TrackingController — Logika Bisnis

#### `adminMap()` (Admin)
1. Ambil semua koordinat via `TrackingModel::getAllCoordinates()`
2. Encode sebagai JSON untuk dikonsumsi Leaflet.js di View
3. Kirim ke View `admin\_map.php`

#### `userTracking()` (User)
1. Cek apakah booking ini milik user yang login
2. Cek apakah status booking adalah `active`
3. Jika bukan `active` → tampilkan pesan "Tracking hanya tersedia saat sewa aktif"
4. Ambil koordinat mobil via `TrackingModel::getCoordinatesByCar($car\_id)`
5. Kirim ke View

#### `updateCoordinate()` (Admin)
1. Validasi input: `car\_id`, `latitude`, `longitude`
2. Validasi range: latitude -90 s.d. 90, longitude -180 s.d. 180
3. Simpan via `TrackingModel::upsertCoordinate()`
4. Catat juga ke `travel\_logs` via `TrackingModel::addTravelLog()`

---

## 13. Modul NotificationDummy

**Lokasi:** `/app/modules/NotificationDummy/`

### 13.1 NotificationController — Logika Bisnis

Modul ini bersifat **dummy** (simulasi) dan tidak memiliki Model karena tidak ada tabel database tersendiri.

#### `showPopup()` (User)
1. Ambil `booking\_id` dari parameter URL
2. Ambil data booking via `BookingModel::getBookingById($booking\_id)`
3. Tentukan pesan notifikasi berdasarkan status booking:
   - `pending` → "Pesanan kamu sedang menunggu konfirmasi pembayaran."
   - `booked` → "Pembayaran berhasil! Pesanan kamu telah dikonfirmasi."
   - `active` → "Selamat, masa sewa kamu sedang berjalan."
   - `completed` → "Sewa selesai. Terima kasih sudah menggunakan layanan kami!"
   - `cancelled` → "Pesanan kamu telah dibatalkan."
4. Kirim pesan ke View `popup\_dummy.php` untuk ditampilkan via SweetAlert2

> \*\*Catatan:\*\* Untuk simulasi WhatsApp API, View cukup menampilkan modal yang meniru tampilan notifikasi WhatsApp. Tidak ada request ke API nyata.

---

## 14. Keamanan & Validasi Global

### 14.1 Proteksi yang Wajib Diterapkan

| Ancaman | Mitigasi |
|---------|----------|
| **SQL Injection** | Selalu gunakan PDO Prepared Statements dengan parameter binding (`:param` atau `?`). DILARANG konkatenasi string langsung ke query |
| **XSS (Cross-Site Scripting)** | Semua output ke HTML di-escape dengan `htmlspecialchars($var, ENT\_QUOTES, 'UTF-8')` |
| **CSRF** | Setiap form POST menyertakan token CSRF tersembunyi. Token di-generate saat session dimulai dan divalidasi sebelum proses POST |
| **Session Hijacking** | Regenerate session ID setelah login: `session\_regenerate\_id(true)`. Set cookie dengan `HttpOnly` dan `SameSite=Strict` |
| **Unauthorized Access** | Method `requireRole()` di `\_\_construct()` setiap Controller |
| **Directory Traversal** | Validasi path upload, gunakan `basename()` saat membaca nama file dari input |
| **Unrestricted File Upload** | Validasi ekstensi dan MIME type. Simpan di luar document root atau gunakan whitelist ekstensi |

### 14.2 Validasi Input — Aturan Umum

- Semua input dari `$\_GET`, `$\_POST`, `$\_FILES` dianggap **tidak terpercaya**
- Gunakan filter PHP bawaan: `filter\_var($email, FILTER\_VALIDATE\_EMAIL)`, `filter\_var($price, FILTER\_VALIDATE\_FLOAT)`
- Tanggal divalidasi dengan `DateTime::createFromFormat('Y-m-d', $date)`
- Integer ID divalidasi dengan `is\_numeric()` dan `(int)` casting
- Pesan error validasi TIDAK boleh mengekspos detail teknis (nama tabel, nama kolom, dsb.)

### 14.3 CSRF Token Flow

```

1\. Session dimulai → generate token: $\_SESSION\['csrf\_token'] = bin2hex(random\_bytes(32))

2\. View menyertakan: <input type="hidden" name="csrf\_token" value="<?= $\_SESSION\['csrf\_token'] ?>">

3\. Controller memvalidasi sebelum proses POST:

&#x20;  if ($\_POST\['csrf\_token'] !== $\_SESSION\['csrf\_token']) → reject \& redirect

4\. Setelah validasi sukses, regenerate token baru untuk request berikutnya

```

### 14.4 Upload File — Checklist

- [ ] Cek `$\_FILES\['file']\['error'] === UPLOAD\_ERR\_OK`
- [ ] Validasi ekstensi file dari nama file (whitelist)
- [ ] Validasi MIME type dengan `finfo\_file()` (jangan percaya `$\_FILES\['type']`)
- [ ] Batasi ukuran file (cek `$\_FILES\['file']\['size']`)
- [ ] Generate nama file baru yang unik (jangan pakai nama asli dari user)
- [ ] Simpan ke direktori di luar `/app` (gunakan `/public/uploads/` yang sudah di-ignore `.gitignore`)

---

## 15. Konvensi & Standar Kode

### 15.1 Penamaan

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Kelas (Controller, Model) | PascalCase | `BookingController`, `CarModel` |
| Method | camelCase | `processCheckout()`, `getAllCars()` |
| Variabel | camelCase | `$totalPrice`, `$carId` |
| Tabel DB | snake_case (plural) | `bookings`, `car\_categories` |
| Kolom DB | snake_case | `start\_date`, `price\_per\_day` |
| File View | snake_case | `user\_checkout.php`, `admin\_orders.php` |

### 15.2 Struktur Method Controller (Standar)

Setiap method Controller mengikuti urutan:
1. Validasi RBAC (sudah di `\_\_construct`, tapi bisa double-check di sini jika perlu)
2. Ambil & sanitasi input
3. Validasi bisnis
4. Panggil Model
5. Redirect atau load View

### 15.3 Struktur Method Model (Standar)

Setiap method Model mengikuti pola:
1. Siapkan SQL query
2. Prepare statement via PDO
3. Bind parameter
4. Execute
5. Fetch dan return hasil
6. Semua dalam blok `try-catch (PDOException $e)` — log error, jangan tampilkan ke user

### 15.4 Error Handling

- Error database di-log ke file (misal `/app/logs/error.log`), TIDAK ditampilkan ke browser
- Di environment `production`, matikan `display\_errors` dan aktifkan `log\_errors` di `php.ini`
- Redirect dengan flash message (simpan di `$\_SESSION\['flash']`) untuk memberi tahu user tentang sukses/gagal

### 15.5 Flash Message

Konvensi untuk pesan satu kali yang ditampilkan setelah redirect:

```

Set:  $\_SESSION\['flash'] = \['type' => 'success'|'error'|'warning', 'message' => '...']

Read: Di View, cek dan tampilkan $\_SESSION\['flash'], lalu unset setelah ditampilkan

```

---

*Dokumen ini merupakan spesifikasi backend dan panduan pengembangan. Setiap perubahan signifikan pada skema database atau logika bisnis harus diperbarui di dokumen ini.*