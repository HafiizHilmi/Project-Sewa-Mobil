# Implementation Plan — Redesign Frontend Admin + Perbaikan Performa

> **Status dokumen:** rencana saja. Tidak ada kode admin yang diubah saat dokumen ini dibuat.
> **Ruang lingkup:** `admin/` (index, pages, actions, assets) dan `include/db_config.php`.
> **Acuan visual:** design system pelanggan yang sudah ada (`public/assets/css/app.css`) — token, komponen, dan motion layer `public/assets/js/ui.js`.
> **Tanggal audit:** hasil pengukuran pada instalasi lokal (Apache 82, PHP 8.2, MariaDB 10.4).

---

## 1. Ringkasan Eksekutif

Panel admin saat ini punya dua masalah yang saling memperparah:

1. **Arsitektur render:** keenam halaman admin dirender sekaligus dalam satu request (single-page via Alpine), termasuk seluruh query dan seluruh HTML-nya. Setiap request mengeluarkan **91 query** dan **280 KB HTML**.
2. **Polling yang rusak:** notifikasi pesanan masuk memanggil endpoint tiap 5 detik, tetapi endpoint itu mengembalikan **HTML, bukan JSON** — jadi fitur real-time-nya secara fungsional mati sambil tetap membebani server.

Ditambah beban aset (50 foto armada tanpa `loading="lazy"`, Tailwind CDN 398 KB, Alpine CDN 6,9 detik), hasilnya: **DOMContentLoaded 2.799 ms, load 3.861 ms** untuk halaman yang isinya baru satu layar.

Rencana ini membagi pekerjaan jadi **6 fase**: (0) perbaikan bug kritis & keamanan, (1) pemisahan data per halaman, (2) sistem notifikasi baru dengan animasi, (3) redesign visual mengikuti style user, (4) motion layer, (5) verifikasi & dokumentasi.

---

## 2. Temuan Bug (dengan bukti ukur)

### B1 — BLOCKER: endpoint notifikasi mengembalikan HTML, bukan JSON

**Bukti:**
```
GET admin/index.php?page=dashboard&ajax_alerts=1
  status         : 200
  content-type   : text/html; charset=UTF-8     ← seharusnya application/json
  panjang body   : 5.762 byte
  awal body      : "<!DOCTYPE html>\n<html lang=\"id\">…"
  JSON.parse()   : GAGAL — Unexpected token '<'
  ekor body      : "<b>Warning</b>: Cannot modify header information -
                    headers already sent by (output started at
                    admin/pages/sidebar.php:57) in admin/pages/dashboard.php on line 7"
```

**Akar masalah:** `admin/pages/dashboard.php` baris 5–31 memanggil `header('Content-Type: application/json')` **setelah** `admin/index.php` sudah mencetak HTML. Urutan include di `admin/index.php` baris 178–190: `sidebar.php` → `header.php` → lalu `dashboard.php`. Output sidebar sudah dikirim lebih dulu, sehingga `header()` gagal dan seluruh HTML ikut terkirim.

**Dampak:** `fetch(...).then(res => res.json())` di `dashboard.php:450` selalu melempar error → blok `.catch` hanya `console.error`. **Notifikasi pesanan baru tidak pernah muncul, suara notifikasi tidak pernah berbunyi**, sementara browser tetap mengirim 1 request / 5 detik.

**Perbaikan:** endpoint JSON dipindah ke file terpisah (`admin/api/alerts.php`) yang:
- tidak meng-include view apa pun,
- dipanggil sebelum ada output (`ob_start()` + `ob_clean()` sebagai pengaman),
- mengirim `Content-Type: application/json` + `Cache-Control: no-store`,
- memakai `json_encode([...], JSON_UNESCAPED_UNICODE)` dan `exit`.

---

### B2 — BLOCKER: 91 query per request, 39 di antaranya untuk polling

**Bukti (dihitung dari `SHOW GLOBAL STATUS LIKE 'Questions'` sebelum/sesudah request):**

| Endpoint | Query per request |
|---|---|
| `?page=dashboard` | **91** |
| `?page=orders` | **91** |
| `?page=dashboard&ajax_alerts=1` | **39** |
| `admin/login.php` | 3 |

**Akar masalah:** `admin/index.php` baris 185–190 meng-include **keenam** halaman sekaligus. Karena `dashboard.php` ikut dieksekusi meskipun `page=orders`, query dashboard (termasuk 36 query chart) selalu jalan.

`admin/pages/dashboard.php` baris 69–120 menjalankan query di dalam loop:

```php
for ($i = $numPoints - 1; $i >= 0; $i--) {          // 12 iterasi
    $stmtChart = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(created_at,'%Y-%m') = ?");
    $stmtChart->execute([$monthStr]);               // 12 query
}
// diulang untuk mingguan (12) dan harian (12) → 36 query chart
```

Ditambah `admin/index.php` baris 14–46: **12 perintah migrasi** (`SHOW COLUMNS` ×4 + `ALTER TABLE` ×8) yang dieksekusi **setiap request**.

**Perbaikan:**
1. Chart: ganti 36 query menjadi **1 query agregat** (`GROUP BY DATE_FORMAT(...)` untuk 3 rentang, atau 3 query total), lalu hitung label di PHP dari array hasil.
2. Migrasi: pindahkan ke `admin/install/migrate.php` yang dijalankan manual/CLI, dan simpan penanda versi skema (mis. tabel `schema_migrations` atau `settings['schema_version']`). Request normal **tidak** boleh menjalankan `ALTER TABLE`.
3. Include halaman: hanya include file sesuai `?page=` (lihat Fase 1).

**Target:** ≤ 12 query untuk dashboard, ≤ 6 query untuk halaman lain.

---

### B3 — TINGGI: enam halaman dirender sekaligus (280 KB HTML, 2.964 node DOM)

**Bukti:**
```
?page=dashboard   → 280 KB, 2.964 node, 12 elemen .fixed.inset-0 (modal)
?page=orders      → 280 KB (identik)
?page=customers   → 280 KB
?page=cars        → 280 KB
?page=verifications → 280 KB
?page=settings    → 280 KB
```

Semua panel memakai `x-show="activePage === '...'"` — jadi markup, query, dan modal semuanya ada di DOM sejak awal. Ini juga membuat `x-cloak` di `admin/index.php:154` tidak efektif (0 elemen `[x-cloak]` terdeteksi setelah Alpine boot).

**Perbaikan:** render **satu halaman per request**:

```
admin/index.php?page=cars  →  hanya include pages/cars.php
```

Navigasi sidebar menjadi link biasa (bukan `x-show`), dengan transisi halaman singkat. Modal yang tidak dipakai halaman itu tidak ikut dikirim.

**Target:** ≤ 90 KB per halaman, ≤ 1.200 node DOM.

---

### B4 — TINGGI: 50 foto armada dimuat sekaligus (9,5 MB) tanpa lazy loading

**Bukti:**
```
resource gambar : 50 file
total transfer  : 9.507 KB
<img> di admin/pages/cars.php : 4
atribut loading="lazy"        : 0
```

Foto dimuat lewat `<img :src="car.image">` di dalam `x-for` (cars.php:182–183), jadi semua 50 gambar diminta saat halaman dibuka — padahal hanya 3–6 kartu yang terlihat.

**Perbaikan:**
- `loading="lazy"` + `decoding="async"` pada semua `<img>` di `x-for`.
- Thumbnail: buat versi 320 px (`public/assets/images/cars/thumbs/`) lewat script sekali jalan, pakai di kartu; versi penuh hanya di modal detail.
- Tambahkan `width`/`height` agar tidak ada layout shift.

**Target:** transfer gambar awal ≤ 600 KB.

---

### B5 — TINGGI: dependensi CDN memblokir render

**Bukti (ukur langsung dari mesin ini):**

| Aset | Ukuran | Waktu |
|---|---|---|
| `cdn.tailwindcss.com` (JIT, render-blocking) | 398 KB | 563 ms |
| `alpinejs@3.14.1/dist/cdn.min.js` | 44 KB | **6.901 ms** |
| `fonts.googleapis.com` | 1 KB | 785 ms |

Total `load` = 3.861 ms, dan itu terjadi **sebelum** halaman bisa interaktif karena Alpine `defer` menunggu unduhan selesai.

**Perbaikan (pilih salah satu, rekomendasi A):**
- **A. Lepas Tailwind CDN.** Pindahkan ke CSS statis: pakai token + komponen dari `public/assets/css/app.css` (sudah ada `.card`, `.btn`, `.badge`, `.panel`, `.status`, dll). Ini sekaligus menyelesaikan Fase 3 (redesign), karena style user memang tidak memakai Tailwind.
- **B. Kalau tetap butuh utility:** build Tailwind sekali (`npx tailwindcss -o admin/assets/css/tailwind.min.css --minify`), simpan hasilnya di repo, hapus script CDN.
- **Alpine:** vendor lokal (`admin/assets/vendor/alpine/alpine.min.js`, ~15 KB gzip) seperti yang sudah dilakukan untuk Font Awesome.
- **Font:** `preconnect` sudah ada; tambahkan `font-display: swap` (sudah dari Google) dan subset `latin`.

**Target:** tidak ada request ke CDN; `load` ≤ 900 ms.

---

### B6 — SEDANG: migrasi skema dijalankan tiap request

**Bukti:** `admin/index.php` — 8 `ALTER TABLE`, 4 `SHOW COLUMNS`. `admin/pages/cars.php:3–17` — 3 `SHOW COLUMNS` + potensi `ALTER TABLE` + `UPDATE` massal. `include/db_config.php:48–92` — 17 `SHOW COLUMNS` per include.

**Dampak:** setiap request membayar biaya metadata MySQL. Di data kecil masih terasa (≈40% dari total query), di produksi bisa jadi sumber lock.

**Perbaikan:** satu file migrasi idempoten + penanda versi. Request normal hanya `SELECT setting_value FROM settings WHERE setting_key='schema_version'`.

---

### B7 — SEDANG: `alert()` / `confirm()` native, tidak ada sistem notifikasi

**Bukti:**
```
alert()   : 4 pemakaian (cars.php ×3, app.js ×1)
confirm() : 12 pemakaian (cars.php ×2, customers.php ×6, dashboard.php ×1, settings.php ×3)
toast/notify : 0 implementasi
flash_msg : dikirim sebagai alert() via setTimeout di admin/index.php:157–165
```

**Dampak:** `alert()` memblokir main thread dan menghentikan animasi; tidak ada feedback visual yang konsisten; tidak bisa di-queue.

**Perbaikan:** satu komponen toast (lihat §4) menggantikan seluruh `alert()` dan `confirm()` destruktif.

---

### B8 — SEDANG: atribut `class` ganda (markup tidak valid)

**Bukti:** `admin/pages/dashboard.php:427`

```html
<circle class="chart-gradient" … class="transition-all duration-300 group-hover:stroke-[4px] dark:fill-slate-800"/>
```

Atribut `class` kedua diabaikan browser, sehingga animasi hover titik chart tidak pernah aktif. Ada 3 kejadian serupa di `dashboard.php` (baris 427, 430, 431).

**Perbaikan:** gabungkan menjadi satu atribut `class`.

---

### B9 — SEDANG: JSON armada di-inline ke dalam halaman

**Bukti:** `admin/pages/cars.php:559` → `cars: <?php echo json_encode($cars_data); ?>` — blok `<script>` 177 baris (mulai baris 546).

**Dampak:** payload HTML membengkak, data tidak bisa di-cache, dan setiap perubahan filter tidak bisa memakai endpoint ringan.

**Perbaikan:** `GET admin/api/cars.php` (JSON, ~10 KB) yang dipanggil Alpine saat halaman dibuka. Halaman HTML jadi jauh lebih kecil.

---

### B10 — KRITIS (keamanan): password admin disimpan & dibandingkan plaintext

**Bukti:** `admin/login.php:53` → `if ($password === $data['password'])`. Seeder di `include/db_config.php:108` dan `db/sewa_mobil.sql:185` menyimpan `admin123` apa adanya.

**Perbaikan:** `password_hash()` / `password_verify()`, plus migrasi hash untuk baris lama (`UPDATE admins SET password = <hash> WHERE password NOT LIKE '$2y$%'` dijalankan sekali).

---

### B11 — KRITIS (keamanan): file KTP/SIM bisa diakses tanpa login

**Bukti:** `admin/serve_file.php` — tidak ada pengecekan `$_SESSION['admin_id']`, hanya `basename()` lalu `readfile()`.

**Dampak:** siapa pun yang tahu/ menebak nama file bisa mengunduh KTP & SIM pelanggan.

**Perbaikan:** wajib sesi admin + verifikasi role, kirim `X-Content-Type-Options: nosniff`, dan batasi MIME yang diizinkan.

---

### B12 — SEDANG (keamanan): endpoint tanpa guard

| File | Guard sesi | Catatan |
|---|---|---|
| `admin/actions/*.php` (6 file) | hanya `admin_action.php` | 5 file lain bisa dipanggil tanpa login |
| `admin/get_pending_count.php` | tidak ada | juga **dead code** (0 pemanggil) |
| `admin/triggers.php` | tidak ada | memicu webhook kendaraan |
| `admin/serve_file.php` | tidak ada | lihat B11 |

Tidak ada satu pun action yang memakai token CSRF.

**Perbaikan:** satu file `admin/inc/guard.php` yang di-require paling atas setiap action:
```php
require_admin();          // cek $_SESSION['admin_id'], kirim 403 JSON bila gagal
verify_csrf();            // token per-sesi, wajib pada POST
```

---

### B13 — RENDAH: index database belum ada untuk query dashboard

**Bukti:** `SHOW INDEX FROM bookings` → hanya `PRIMARY` dan `car_id`. Tidak ada index pada `status`, `created_at`, `user_id`.

**Dampak:** query chart (`DATE_FORMAT(created_at,...)`) dan `WHERE status='pending'` akan jadi full scan saat data bertambah.

**Perbaikan:**
```sql
CREATE INDEX idx_bookings_status_created ON bookings (status, created_at);
CREATE INDEX idx_bookings_created        ON bookings (created_at);
CREATE INDEX idx_bookings_user           ON bookings (user_id);
```

---

### B14 — RENDAH: polling 5 detik per tab

**Bukti:** `admin/pages/dashboard.php:448` → `setInterval(…, 5000)`.

**Dampak:** 720 request/jam per tab admin yang terbuka. Dengan B1 (selalu gagal) beban itu sia-sia total.

**Perbaikan:** naikkan interval ke 20–30 detik, jeda otomatis saat tab tidak aktif (`document.visibilityState`), dan hentikan polling saat modal/panel tertutup. Idealnya pakai SSE (`admin/api/stream.php`) — opsional, bisa fase lanjutan.

---

### B15 — RENDAH: `get_pending_count.php` tidak dipakai

0 referensi di seluruh repo. Hapus atau jadikan bagian dari `admin/api/alerts.php`.

---

## 3. Target Redesign — Admin Mengikuti Style User

Prinsip yang sudah dipakai di sisi pelanggan dan akan diadopsi admin:

| Aspek | Sekarang (admin) | Target |
|---|---|---|
| Framework CSS | Tailwind CDN 398 KB | CSS statis `admin/assets/css/admin.css` berisi token yang sama dengan `app.css` |
| Palet | `slate` + `blue-600`, banyak varian (`indigo`, `violet`, `teal`, `orange`, `rose`) | satu aksen (`--accent: #1246e4`), status pakai `--ok/--warn/--danger` |
| Radius | campur `rounded-lg/xl/2xl` | token `--r-sm/md/lg/xl` |
| Elevasi | `shadow-sm/md/xl/2xl` acak | `--shadow-1/2/3` (garis 1px + bayangan tipis) |
| Tipografi | Plus Jakarta Sans (tetap) | sama, skala `--display/.lead/.tiny` |
| Ikon | Font Awesome | **tetap** Font Awesome (sudah lokal) — jangan campur dua set |
| Dark mode | `class="dark"` + `dark:` utility | `html.dark` + variabel CSS |
| Komponen | inline utility | `.card`, `.panel`, `.btn`, `.badge`, `.status`, `.table`, `.drawer`, `.toast` |

### 3.1 Struktur layout baru

```
┌──────────────────────────────────────────────────────────┐
│  rail 248px (sticky)  │  topbar 64px (full-bleed)        │
│  - brand              ├──────────────────────────────────┤
│  - grup menu          │  page head (judul + aksi)        │
│  - status badge       │  ────────────────────────────    │
│  - profil + logout    │  konten halaman (1 halaman saja)  │
└──────────────────────────────────────────────────────────┘
```

- Rail: sticky di desktop, drawer di mobile (pola sama dengan `.dash-side` pelanggan).
- Topbar: full-bleed ke pojok kiri-kanan (menyusul perbaikan navbar pelanggan), berisi breadcrumb + pencarian global + ikon notifikasi (dengan badge) + toggle tema + avatar.
- Tidak ada lagi `x-show` untuk 6 panel.

### 3.2 Peta komponen per halaman

| Halaman | Komponen baru | Komponen lama yang dibuang |
|---|---|---|
| Dashboard | `.stat-grid` (4 kartu), `.chart-card` (1 query), `.feed` notifikasi | `dashAlerts` hardcoded, SVG 36-query |
| Orders | `.table` + `.status` pill, `.drawer` detail | modal besar 12 elemen `fixed inset-0` |
| Cars | `.card-grid` armada + thumb 320px, `.drawer` form | JSON inline 177 baris |
| Customers | `.table` + bulk action bar `.bulkbar` | 6 `confirm()` |
| Verifications | `.review` 2 kolom (dokumen + aksi) | — |
| Settings | `.tabs` + `.panel` per grup | 3 modal terpisah |

---

## 4. Motion & Animasi Notifikasi (spesifikasi)

Semua nilai di bawah masuk ke `admin/assets/css/admin.css` sebagai token, dan `admin/assets/js/admin-ui.js` sebagai perilaku. Prinsipnya sama dengan `ui.js` pelanggan: **fail-safe** (konten tidak pernah tertinggal transparan).

### 4.1 Token

```css
--dur-1: 130ms;   /* tekan */
--dur-2: 220ms;   /* hover, warna */
--dur-3: 380ms;   /* panel, drawer */
--dur-4: 620ms;   /* reveal */
--ease-out:    cubic-bezier(.16, 1, .3, 1);
--ease-spring: cubic-bezier(.34, 1.56, .64, 1);
```

### 4.2 Toast (pengganti `alert()`)

| Properti | Nilai |
|---|---|
| Posisi | kanan atas, `top: 16px; right: 16px` (desktop), bawah tengah di mobile |
| Masuk | `translateX(24px) scale(.96)` + `opacity 0` → `0 / 1 / 1`, `--dur-3`, `--ease-spring` |
| Keluar | `translateX(12px)` + `opacity 0`, `--dur-2`, `--ease-out` |
| Tumpukan | maks 4, jarak 10px, yang lama bergeser dengan transisi `transform` (bukan `top`) |
| Progress | garis 2px di bawah toast menyusut `linear` selama durasi tampil |
| Durasi | sukses 3,5 s · info 4 s · error 6 s (tidak auto-close) |
| Hover | pause timer |
| Aksi | tombol "Batalkan" untuk aksi destruktif (menggantikan `confirm()`) |
| A11y | `role="status"` / `role="alert"`, `aria-live="polite"` / `"assertive"` |
| Reduced motion | muncul/hilang tanpa transform, hanya fade 120 ms |

### 4.3 Notifikasi pesanan masuk (yang tadinya rusak)

Alur baru:
1. `admin/api/alerts.php` mengembalikan JSON valid.
2. `admin-ui.js` polling 25 s, **berhenti saat `document.hidden`**, lanjut saat `visibilitychange`.
3. Bila `max(id)` naik: tampilkan toast `pesanan` (varian khusus, ikon lonceng, tanpa auto-close), badge di topbar berdenyut (`pulse` 1,8 s ×3 lalu diam), dan baris feed baru masuk dengan animasi `slide-in` + highlight latar yang memudar 1,2 s.
4. Suara notifikasi hanya diputar setelah interaksi pertama pengguna (patuh autoplay policy) — disimpan di `localStorage`.

### 4.4 Motion lain yang diseragamkan

| Trigger | Efek |
|---|---|
| Tekan tombol/menu/kartu | `scale(.97)`, `--dur-1` + ripple halus |
| Pindah halaman (sidebar) | konten lama `opacity .4 + translateY(4px)` 120 ms → halaman baru `fade+rise` 260 ms |
| Buka drawer/modal | backdrop fade 200 ms + panel `translateY(12px) scale(.99)` → normal, `--ease-out` |
| Baris tabel baru | `slide-in` 240 ms + latar `--accent-soft` memudar 1,2 s |
| Ganti status pesanan | pill berubah dengan cross-fade 180 ms, bukan ganti warna mendadak |
| Chart muncul | `stroke-dashoffset` 900 ms (sudah ada, pertahankan tapi pemicu saat terlihat) |
| Counter statistik | hitung naik 700 ms saat masuk viewport |
| Skeleton loading | shimmer 1,4 s saat fetch API (menggantikan layar kosong) |

Semua diblok oleh `@media (prefers-reduced-motion: reduce)`.

---

## 5. Rencana Kerja per Fase

### Fase 0 — Stabilisasi & Keamanan (tanpa perubahan visual)
**Tujuan:** hilangkan bug yang membuat fitur mati dan menutup lubang keamanan.

| # | Pekerjaan | File |
|---|---|---|
| 0.1 | Buat `admin/api/alerts.php` (JSON bersih, `ob_start`, guard sesi) | baru |
| 0.2 | Hapus blok `ajax_alerts` dari `dashboard.php` (baris 5–31) | `pages/dashboard.php` |
| 0.3 | Pindahkan 12 migrasi dari `index.php` ke `admin/install/migrate.php` + penanda versi | `index.php`, baru |
| 0.4 | Buat `admin/inc/guard.php` (`require_admin`, `verify_csrf`), pasang di 6 action + `serve_file.php` + `triggers.php` | baru, 8 file |
| 0.5 | Hash password admin + migrasi hash lama | `login.php`, `actions/admin_action.php`, `db/sewa_mobil.sql` |
| 0.6 | Hapus `get_pending_count.php` | — |
| 0.7 | Perbaiki `class` ganda di chart (3 titik) | `pages/dashboard.php:427,430,431` |

**Kriteria selesai:** `curl` ke `alerts.php` mengembalikan `application/json` + array valid; action tanpa sesi → 403; login admin memakai `password_verify`.

---

### Fase 1 — Pemisahan Data per Halaman
**Tujuan:** satu halaman = satu request, query minimal.

| # | Pekerjaan | Target |
|---|---|---|
| 1.1 | Router sederhana di `index.php`: `match($page)` → include satu file saja | 1 halaman/request |
| 1.2 | Chart dashboard: 36 query → 3 query agregat | ≤ 12 query total |
| 1.3 | `admin/api/cars.php`, `admin/api/orders.php`, `admin/api/customers.php` (JSON) | HTML turun < 90 KB |
| 1.4 | Sidebar jadi `<a href="?page=…">`, hapus `x-show` panel | — |
| 1.5 | Tambah index DB (B13) | query < 30 ms |

**Kriteria selesai:** `?page=orders` ≤ 90 KB dan ≤ 6 query; tidak ada query chart saat membuka halaman lain.

---

### Fase 2 — Sistem Notifikasi
**Tujuan:** notifikasi benar-benar berfungsi dan terasa halus.

| # | Pekerjaan |
|---|---|
| 2.1 | `admin-ui.js`: modul `toast()` (varian success/info/warn/error/order) |
| 2.2 | Ganti 4 `alert()` → `toast()` |
| 2.3 | Ganti 12 `confirm()` destruktif → toast dengan tombol "Batalkan" (atau dialog non-blocking) |
| 2.4 | `flash_msg` dari PHP → payload JSON → toast saat load |
| 2.5 | Polling 25 s + pause saat tab hidden + badge topbar |
| 2.6 | Suara notifikasi setelah interaksi pertama |

**Kriteria selesai:** pesanan baru (insert manual ke DB) memunculkan toast < 25 s tanpa error console; `fetch` alert mengembalikan JSON; 0 `alert()`/`confirm()` tersisa.

---

### Fase 3 — Redesign Visual
**Tujuan:** admin terasa satu keluarga dengan sisi pelanggan.

| # | Pekerjaan |
|---|---|
| 3.1 | Buat `admin/assets/css/admin.css`: token (warna/tipe/radius/shadow/motion) disalin dari `app.css` |
| 3.2 | Layout shell baru: rail 248px + topbar full-bleed |
| 3.3 | Port komponen: `.card`, `.panel`, `.btn`, `.badge`, `.status`, `.table`, `.drawer`, `.field` |
| 3.4 | Tulis ulang 6 halaman memakai komponen itu; buang utility Tailwind |
| 3.5 | Hapus `<script src="cdn.tailwindcss.com">`, vendor Alpine lokal |
| 3.6 | Thumbnail armada 320px + `loading="lazy"` |

**Kriteria selesai:** 0 request CDN; screenshot tiap halaman konsisten dengan halaman pelanggan (warna, radius, tipografi).

---

### Fase 4 — Motion Layer
| # | Pekerjaan |
|---|---|
| 4.1 | `admin-ui.js`: nav rail indicator, page transition, ripple, reveal, counter |
| 4.2 | Skeleton loading untuk semua fetch |
| 4.3 | Audit `prefers-reduced-motion` |
| 4.4 | Uji "tab background": pastikan tidak ada elemen tertinggal transparan (bug yang sudah ditemukan di sisi pelanggan) |

---

### Fase 5 — Verifikasi & Dokumentasi
| # | Pekerjaan |
|---|---|
| 5.1 | Ukur ulang semua metrik (tabel §6) |
| 5.2 | Uji regresi: CRUD mobil, ubah status order, approve/reject verifikasi, semua tab settings |
| 5.3 | Uji keamanan: action tanpa sesi, `serve_file` tanpa sesi, CSRF |
| 5.4 | Update `README.md` (struktur admin, cara migrasi, cara build CSS) |
| 5.5 | Screenshot before/after per halaman |

---

## 6. Metrik Keberhasilan

| Metrik | Sekarang (terukur) | Target |
|---|---|---|
| Query per request (dashboard) | 91 | ≤ 12 |
| Query per request (orders) | 91 | ≤ 6 |
| HTML per halaman | 280 KB | ≤ 90 KB |
| Node DOM | 2.964 | ≤ 1.200 |
| Transfer gambar awal | 9.507 KB | ≤ 600 KB |
| `DOMContentLoaded` | 2.799 ms | ≤ 700 ms |
| `load` | 3.861 ms | ≤ 900 ms |
| Request CDN | 3 (Tailwind, Alpine, Fonts) | 0 (font boleh tetap, sudah `preconnect`) |
| Notifikasi pesanan | **tidak pernah muncul** (JSON gagal) | muncul ≤ 25 s, JSON valid |
| `alert()` / `confirm()` | 4 / 12 | 0 / 0 |
| Endpoint tanpa guard | 9 | 0 |
| Password admin | plaintext | `password_hash` |

---

## 7. Risiko & Catatan

| Risiko | Mitigasi |
|---|---|
| Melepas Tailwind = banyak class harus dipetakan ulang (~4.900 baris) | Kerjakan per halaman; sediakan `admin.css` dengan utility pengganti yang paling sering dipakai (`.flex`, `.gap-2`, `.text-xs`) agar migrasi bertahap tidak merusak tampilan |
| Router baru mengubah URL behavior (`?page=`) | Pertahankan format URL yang sama; tambahkan redirect untuk hash lama |
| `verify_csrf` bisa mematahkan form lama | Terapkan bertahap: guard sesi dulu (Fase 0), CSRF setelah semua form diberi token |
| Migrasi hash password mengunci admin yang tidak tahu password lama | Hash ulang otomatis dari nilai plaintext yang ada (tetap bisa login dengan password sama), lalu paksa ganti |
| Polling tetap membebani bila banyak tab | Pause saat hidden + interval 25 s; opsi SSE di fase lanjutan |

**Di luar lingkup dokumen ini:** perubahan pada sisi pelanggan, integrasi Midtrans/Fonnte, dan desain ulang halaman login admin (hanya diperbaiki hashing-nya).

---

## 8. Urutan Eksekusi yang Disarankan

```
Fase 0  (blocker + keamanan)        ← wajib lebih dulu
   │
   ├─ Fase 1 (data per halaman)     ← menentukan besar kecilnya pekerjaan Fase 3
   │      │
   │      └─ Fase 3 (redesign)  ──┐
   │                              ├─ Fase 4 (motion)
   └─ Fase 2 (notifikasi) ────────┘
                                  └─ Fase 5 (verifikasi)
```

Fase 2 bisa jalan paralel dengan Fase 3 karena menyentuh file berbeda (`admin-ui.js` vs `admin.css` + pages), selama komponen toast disepakati lebih dulu.

---

## Lampiran A — Daftar File yang Akan Disentuh

**Baru:**
```
admin/api/alerts.php
admin/api/cars.php
admin/api/orders.php
admin/api/customers.php
admin/inc/guard.php
admin/install/migrate.php
admin/assets/css/admin.css
admin/assets/js/admin-ui.js
admin/assets/vendor/alpine/alpine.min.js
```

**Diubah:**
```
admin/index.php              (router, hapus migrasi, hapus Tailwind CDN)
admin/login.php              (password_verify)
admin/pages/dashboard.php    (hapus blok ajax, chart 3 query, fix class ganda)
admin/pages/cars.php         (hapus migrasi inline, JSON → API, lazy img)
admin/pages/{orders,customers,verifications,settings,header,sidebar}.php
admin/assets/js/app.js       (alert → toast, hapus data hardcoded)
admin/assets/css/style.css   (dipindah ke admin.css)
admin/serve_file.php         (guard sesi)
admin/triggers.php           (guard sesi)
admin/actions/*.php          (guard + CSRF)
include/db_config.php        (migrasi → penanda versi)
db/sewa_mobil.sql            (hash password, index baru)
README.md
```

**Dihapus:**
```
admin/get_pending_count.php  (dead code)
```
