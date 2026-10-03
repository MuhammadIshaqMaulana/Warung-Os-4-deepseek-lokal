# Warung OS

Aplikasi pencatatan warung digital untuk UMKM (warung makan, toko kelontong, usaha rumahan): penjualan, manajemen stok, pembukuan sederhana, dan pembayaran QRIS.

Dibangun dengan **Laravel 13 + PostgreSQL**, berjalan di lokal (Windows/macOS/Linux) maupun di produksi **Vercel + Neon (Postgres)**.

> Sumber kebutuhan lengkap ada di [`PRD.txt`](PRD.txt).

---

## Daftar Isi

- [Fitur](#fitur)
- [Tech Stack](#tech-stack)
- [Persyaratan](#persyaratan)
- [Menjalankan Secara Lokal](#menjalankan-secara-lokal)
- [Alur Transaksi & Status](#alur-transaksi--status)
- [Konfigurasi QRIS (Placeholder)](#konfigurasi-qris-placeholder)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Deployment ke Vercel + Neon](#deployment-ke-vercel--neon)
- [Testing & Kualitas Kode](#testing--kualitas-kode)
- [Batasan & Catatan Penting](#batasan--catatan-penting)
- [Struktur Direktori](#struktur-direktori)

---

## Fitur

- **Auth** — login, register, reset password, verifikasi email (Laravel Breeze).
- **Dashboard** — omzet & laba hari ini (Asia/Jakarta), jumlah transaksi, banner pembayaran QRIS tertunda, daftar stok menipis, aksi cepat (kasir, produk, laporan).
- **Produk** — CRUD produk, kategori dinamis, harga beli/jual, soft delete.
- **Kasir (transaksi)** — keranjang multi-item, pilih kategori cepat, metode bayar **Tunai / QRIS**, cek stok cukup, cegah double-submit.
- **Stok otomatis** — stok dipotong saat transaksi dibuat (sebagai *reserve*); bila QRIS gagal/batal/kedaluwarsa stok dikembalikan, bila lunas status saja yang berubah. Semua dalam satu transaksi DB (`SELECT ... FOR UPDATE`) agar tidak stok minus.
- **Struk digital** — halaman struk per transaksi: QR QRIS, hitung mundur masa berlaku, polling status otomatis.
- **QRIS** — payload QR statis (placeholder), masa berlaku default 15 menit, webhook terverifikasi signature.
- **Inventaris** — riwayat stok (masuk/keluar/penyesuaian), restock cepat, notifikasi stok menipis.
- **Laporan** — filter rentang tanggal: omzet, laba, jumlah transaksi, produk terlaris, pecahan metode bayar.
- **UX** — bahasa Indonesia, responsif mobile (Tailwind), performa ringan.

---

## Tech Stack

| Bagian | Teknologi |
| --- | --- |
| Backend | Laravel 13 (PHP ^8.3), Eloquent ORM, Blade |
| Auth | Laravel Breeze |
| Frontend | Tailwind CSS 3 + Alpine.js + Vite 8 |
| Database | PostgreSQL (Neon di produksi), SQLite in-memory untuk test |
| Server | `php artisan serve` (lokal), Vercel PHP runtime `vercel-php@0.7.2` (produksi) |
| Test | PHPUnit 12, Pint (code style) |

---

## Persyaratan

- PHP **8.3**+ dengan ekstensi: `pdo_pgsql` (untuk Neon/PostgreSQL), `pdo_sqlite` (untuk test), `openssl`, `mbstring`
- [Composer](https://getcomposer.org)
- Node.js 18+ dan npm
- PostgreSQL (opsional untuk lokal — default `.env.example` memakai SQLite)

---

## Menjalankan Secara Lokal

### Cara cepat (satu perintah)

```bash
composer setup    # install dependency, salin .env, key:generate, migrate, npm install, npm run build
composer dev      # jalankan server + queue + log (pail) + vite sekaligus
```

### Cara manual (PowerShell / Windows)

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Buka <http://127.0.0.1:8000> (akan diarahkan ke halaman login).

### Data awal (seeder)

```bash
php artisan db:seed --class=AdminUserSeeder    # akun demo: a@a.com / admin123 (sudah verified)
php artisan db:seed --class=ProductSeeder      # 25 produk contoh (butuh akun a@a.com)
```

Bisa juga `php artisan migrate --seed` — menjalankan `DatabaseSeeder` dan membuat akun `test@example.com` / `password`.

### Memakai PostgreSQL lokal

Ubah `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=warungos
DB_USERNAME=postgres
DB_PASSWORD=
```

### Perintah yang sering dipakai

```bash
php artisan serve            # server lokal
npm run dev                  # vite dev (hot reload)
npm run build                # build aset produksi (hasilnya di public/build — wajib di-commit)
php artisan migrate           # jalankan migrasi
php artisan route:list        # daftar route
php artisan qris:expire       # paksa kedaluwarsa pembayaran QRIS (lokal/VM, bukan Vercel)
```

---

## Alur Transaksi & Status

Status transaksi disimpan di tabel `transactions` (`method`, `status`, `external_id`, `paid_at`, `expires_at`) dan disinkronkan ke `transaction_details`.

```
TUNAI   : dibuat → LUNAS   (stok dipotong saat transaksi dibuat)

QRIS    : dibuat → MENUNGGU BAYAR   (stok sudah dipotong / di-reserve, ada countdown)
             ├─ lunas (tombol "Bayar Sekarang (Simulasi Gateway)" / webhook) → LUNAS  (stok tetap)
             ├─ gagal / dibatalkan ("Batalkan Transaksi")                   → GAGAL  (stok kembali)
             └─ lewat masa berlaku (15 menit)                                → GAGAL  (stok kembali)

GAGAL   : "Coba Bayar Lagi" (retry) → MENUNGGU BAYAR, external_id & masa berlaku baru,
                                      stok direserve ulang bila sempat dikembalikan
```

Label status pada UI: `LUNAS`, `MENUNGGU BAYAR`, `GAGAL`.

**Kedaluwarsa** dipicu dua cara:
- `php artisan qris:expire` (dijadwalkan tiap menit lewat `routes/console.php`) — hanya untuk lingkungan dengan cron/scheduler;
- pembersihan **lazy** saat user membuka dashboard, riwayat transaksi, struk, atau endpoint polling status → inilah yang dipakai di Vercel (lihat [deployment](#deployment-ke-vercel--neon)).

---

## Konfigurasi QRIS (Placeholder)

Gateway pembayaran **belum tersambung** — aplikasi memakai placeholder:

- QR berisi **payload QRIS statis** milik warung (`QRIS_MERCHANT_PAYLOAD`, default payload contoh). Ganti dengan payload asli dari bank/gateway Anda.
- Gambar QR dibuat via `api.qrserver.com` (fallback: teks payload) sehingga tidak butuh library tambahan.
- Pembayaran bisa ditandai lunas lewat tombol **“Bayar Sekarang (Simulasi Gateway)”** di halaman struk, atau lewat webhook gateway.
- `QRIS_ENDPOINT` disediakan untuk keperluan ping status, tetapi belum dipakai (belum ada gateway).

### Webhook gateway

```
POST https://<domain-anda>/webhooks/qris
Content-Type: application/json
X-Signature: <HMAC-SHA256(raw body, QRIS_WEBHOOK_SECRET)>
```

Body yang dipahami:

```json
{ "external_id": "TRX-...", "status": "paid" }
```

| Field | Nilai yang diproses | Efek |
| --- | --- | --- |
| `external_id` (atau `order_id`) | id unik transaksi | dicocokkan ke `transactions.external_id` |
| `status` | `paid` | transaksi → **LUNAS** (stok sudah dipotong sejak transaksi dibuat) |
| | `failed` / `expired` / `cancel` | transaksi → **GAGAL**, stok dikembalikan |
| | lainnya | diabaikan (`ignored`) |

Respon: `200` sukses (termasuk penanda `duplicate` bila callback ganda — idempotent), `401` signature salah, `404` transaksi tidak ada, `422` `external_id` kosong, `503` `QRIS_WEBHOOK_SECRET` dikosongkan.

CSRF dikecualikan untuk `webhooks/*` (lihat `bootstrap/app.php`).

---

## Konfigurasi Environment

Salin `.env.example` → `.env`. Variasi penting:

| Variabel | Keterangan | Default |
| --- | --- | --- |
| `APP_KEY` | kunci enkripsi (wajib ada) | — |
| `APP_URL` | URL publik; di Vercel isi domain asli + `https://` | `http://localhost` |
| `APP_LOCALE` / `APP_TIMEZONE` | bahasa & zona waktu (omzet harian mengikuti ini) | `id` / `Asia/Jakarta` |
| `DB_*` | koneksi database (atau `DB_URL` untuk Neon) | sqlite |
| `SESSION_DRIVER` | lokal `database`, Vercel **`cookie`** | `database` |
| `CACHE_STORE` | lokal `database`, Vercel **`array`** | `database` |
| `QRIS_MERCHANT_PAYLOAD` | payload QRIS statis warung | payload contoh |
| `QRIS_WEBHOOK_SECRET` | secret verifikasi `X-Signature` | `warungos-placeholder-secret` |
| `QRIS_EXPIRY_MINUTES` | masa berlaku pembayaran QRIS | `15` |
| `LOW_STOCK_THRESHOLD` | batas stok menipis | `5` |

Simpan daftar nilai produksi di **password manager** atau langsung di *Environment Variables* Vercel — **jangan pernah disimpan di repository**.

---

## Deployment ke Vercel + Neon

Konfigurasi yang sudah ada:

- `vercel.json` — semua request diarahkan ke `api/index.php` (runtime `vercel-php@0.7.2`), aset statis di `/build/*`.
- `api/index.php` — penanganan runtime Vercel: storage (log, cache, view) dipindah ke `/tmp` karena filesystem read-only.
- `AppServiceProvider` — memaksa HTTPS ketika `APP_ENV=production` / header `X-Forwarded-Proto: https` / env `VERCEL`.
- `.vercelignore` — tidak meng-*upload* `tests`, `node_modules`, `vendor`, dll; sebaliknya `app/`, `config/`, `database/migrations`, `public/build` ikut ter-deploy.

### Langkah deploy

1. **Set env variables di dashboard Vercel** (Project → Settings → Environment Variables). Minimal:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://<domain-anda>
   APP_LOCALE=id
   APP_TIMEZONE=Asia/Jakarta

   DB_CONNECTION=pgsql
   DB_HOST=ep-xxxxx-pooler.<region>.aws.neon.tech
   DB_PORT=5432
   DB_DATABASE=neondb
   DB_USERNAME=<user>
   DB_PASSWORD=<password>
   DB_SSLMODE=require

   SESSION_DRIVER=cookie
   CACHE_STORE=array
   FILESYSTEM_DISK=public

   QRIS_MERCHANT_PAYLOAD=
   QRIS_WEBHOOK_SECRET=<rahasia-panjang>
   QRIS_EXPIRY_MINUTES=15
   LOW_STOCK_THRESHOLD=5
   ```
   `APP_KEY` — buat baru dengan `php artisan key:generate --show`, simpan di password manager dan di Vercel. Jangan commit nilai key.
2. **Jalankan migrasi ke Neon** — Vercel **tidak** menjalankan migrasi otomatis. Dari komputer lokal:
   ```powershell
   $env:DB_URL="postgresql://<user>:<pass>@ep-xxxxx.<region>.aws.neon.tech/neondb?sslmode=require"
   php artisan migrate --force
   ```
   Pakai hostname **tanpa `-pooler`** (unpooled) untuk migrasi; runtime aplikasi memakai hostname pooler.
3. **Build aset sebelum push** — `npm run build`, lalu commit folder `public/build` (Vite manifest + CSS/JS). Tanpa ini aset 404.
4. **Deploy** — push ke `main` (Git integration) atau `vercel --prod`.
5. **Setelah live**: buka `https://<domain>/register` untuk membuat akun pemilik warung.

### Catatan khusus Vercel (serverless)

| Topik | Kenyataan | Solusi di aplikasi |
| --- | --- | --- |
| Scheduler/cron | Tidak ada — `schedule:run` tidak pernah jalan | Kedaluwarsa QRIS dibersihkan **lazy** saat dashboard/riwayat/struk/polling dibuka |
| Filesystem read-only | Hanya `/tmp` yang bisa ditulis | Log & cache view diarahkan ke `/tmp` (`api/index.php`) |
| Instance bersifat sementara | `array` cache & `cookie` session tidak lintas-instance | Tidak ada data penting di cache/session selain flash & preferensi |
| `qris:expire` | Tidak dapat dijadwalkan | Opsional: panggil via cron eksternal (mis. Vercel Cron) bila ingin expiry tanpa perlu user membuka halaman |

Untuk Vercel Cron opsional, tambahkan `vercel.json` → `"crons": [{ "path": "/cron/qris-expire", "schedule": "*/5 * * * *" }]` dan buat route proteksi yang memanggil `php artisan qris:expire` (belum tersedia saat ini).

---

## Testing & Kualitas Kode

```bash
composer test          # atau: php artisan test
vendor/bin/pint        # format kode (gaya Laravel)
```

- **64 test / 236 assertion** — mencakup alur produk, transaksi kasir & QRIS, webhook (signature, idempoten), laporan, inventaris, dan render halaman.
- Test memakai **SQLite in-memory** (`phpunit.xml`), jadi tidak menyentuh database produksi.

---

## Batasan & Catatan Penting

1. **QRIS masih simulasi.** Belum terhubung ke Midtrans/Xendit/dll. Untuk produksi: isi `QRIS_MERCHANT_PAYLOAD`, set `QRIS_WEBHOOK_SECRET` identik di aplikasi & gateway, lalu daftarkan webhook `https://<domain>/webhooks/qris`.
2. **Verifikasi email saat register.** Route dashboard memerlukan `verified`; dengan `MAIL_MAILER=log` link verifikasi ada di `storage/logs/laravel.log`. Untuk pengguna nyata, pertimbangkan set `email_verified_at` otomatis atau matikan middleware `verified`.
3. **Akun demo** `a@a.com` / `admin123` dibuat oleh `AdminUserSeeder` dan sudah verified — hapus/ganti sebelum publik.
4. **Jangan commit kredensial.** `.env`, `NEONDB.txt`, `env*.txt`, dan `*.credentials.txt` sudah masuk `.gitignore` **dan** `.vercelignore`. Jika suatu saat secret pernah ter-commit di repo publik: **rotate** kredensial tersebut (password Neon, `APP_KEY`, `QRIS_WEBHOOK_SECRET`), hapus dari tracking, lalu bersihkan history Git (`git filter-repo --invert-paths --path <file> --force` + force push).
5. **Stok konsisten** — reserve → potong → kembalikan dilakukan di dalam satu transaksi DB; aman untuk penggunaan bersamaan.

---

## Struktur Direktori

```
app/
  Console/Commands/          # qris:expire
  Http/Controllers/          # Dashboard, Product, Transaction, Report, Stock, Webhook QRIS, Auth
  Models/                    # Product, Transaction, TransactionDetail, StockLog, User
  Services/                  # TransactionService (reserve/paid/cancel/retry/expire), QrisService
bootstrap/app.php            # routing + CSRF exception webhooks/*
config/qris.php              # konfigurasi placeholder QRIS
database/migrations/         # skema + migrasi kolom pembayaran
resources/views/             # dashboard, products, transactions (kasir/struk), reports, stocks
routes/web.php               # route utama (auth + modul)
routes/console.php           # jadwal qris:expire
tests/Feature/               # 64 test
api/index.php                # entry point Vercel
vercel.json                  # konfigurasi deploy Vercel
.env.example                 # template environment
PRD.txt                      # Product Requirements Document
```

---

## Lisensi

MIT — silakan dikembangkan untuk kebutuhan warung Anda.
