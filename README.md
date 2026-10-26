# Poin Member Avasport

Website membership & loyalty point untuk Avasport. Customer mengumpulkan poin
dari belanja di toko dan menukarnya jadi diskon; admin/kasir mengelola customer,
mencatat transaksi, dan memantau program poin.

Spesifikasi lengkap ada di [`docs/SPEC.md`](docs/SPEC.md).

## Teknologi

- **Laravel 11** (PHP 8.2+)
- **MySQL / MariaDB** (InnoDB, `utf8mb4`)
- **Blade + Tailwind CSS 3** (di-build dengan Vite)
- Session-based auth, dua guard terpisah (`web` untuk admin, `customer` untuk member)
- PIN member di-hash dengan **Argon2id** (fallback bcrypt)

## Fitur

**Customer (portal membership):** landing, registrasi, login, dashboard (saldo
poin + estimasi diskon), riwayat transaksi + detail, riwayat mutasi poin, profil,
ubah password & PIN.

**Admin (panel operasional):** dashboard ringkasan, data customer
(cari/filter/pagination), detail customer, catat transaksi (dengan estimasi poin
langsung), detail transaksi, pembatalan (reversal), penyesuaian poin, pengingat
ulang tahun + tautan WhatsApp manual, laporan + ekspor CSV, audit log, pengaturan
program poin, manajemen admin & role (RBAC).

## Prasyarat

- PHP **8.2+** dengan ekstensi: `pdo_mysql, mbstring, openssl, tokenizer, ctype,
  json, bcmath, fileinfo, curl, zip`
- Composer 2
- MySQL/MariaDB
- Node.js 18+ & npm (untuk build aset)

## Setup lokal

```bash
# 1. Dependency
composer install
npm install && npm run build

# 2. Environment
cp .env.example .env
php artisan key:generate
# edit .env -> DB_DATABASE / DB_USERNAME / DB_PASSWORD

# 3. Buat database (sekali), mis. di MySQL:
#    CREATE DATABASE avasport CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 4. Migrasi + seed aman (role, permission, settings)
php artisan migrate --seed

# 5. Buat admin pertama (aman, password diminta tersembunyi)
php artisan admin:create --role=super_admin

# 6. Jalankan
php artisan serve
```
Buka `http://127.0.0.1:8000` (landing), `/admin/login`, `/member/register`.

> **Windows:** `PHP_CLI_SERVER_WORKERS` harus `1` di `.env` (sudah diset) —
> server bawaan PHP tidak bisa fork worker di Windows.

### Catatan mesin dev ini (Windows + XAMPP)

PHP 8.2 portable sudah disiapkan di `./.toolchain/php82` (XAMPP global masih PHP
7.3 yang terlalu lama). Wrapper tersedia:

```powershell
.\.toolchain\php.cmd        # = php
.\.toolchain\composer.cmd   # = composer (pakai PHP 8.2)
.\.toolchain\art.cmd        # = php artisan
```

- Database: MariaDB dari XAMPP (`C:\xampp7\mysql`). DB dev `avasport`, DB test `avasport_test` (user `root`, tanpa password).
- Start MariaDB: `C:\xampp7\mysql\bin\mysqld.exe --defaults-file=C:\xampp7\mysql\bin\my.ini` (atau XAMPP Control Panel).
- `.toolchain/` tidak di-commit (ada di `.gitignore`).

## Menjalankan test

```bash
php artisan test
# atau: vendor/bin/phpunit
```
Test memakai database `avasport_test` (lihat `phpunit.xml`). Buat DB tersebut dulu.

**Hasil aktual** (PHP 8.2.35, MariaDB 10.4): **63 passed (126 assertions)** —
unit (rumus poin), registrasi/login, otorisasi/IDOR, transaksi, penukaran,
reversal, konsistensi ledger, PIN lockout, ulang tahun (incl. 29 Feb), ekspor CSV.

## Struktur penting

```
app/
  Domain/
    Points/     PointsCalculator, PointsConfig, LedgerService,
                TransactionService, ReversalService, AdjustmentService (+exceptions)
    Auth/       PinHasher, Permissions
    Birthday/   BirthdayService
    Membership/ CustomerRegistrar
  Models/       User, Customer, Role, Permission, Transaction, PointLedger,
                AuditLog, BirthdayFollowup, Setting
  Http/
    Controllers/{Member,Admin}  + LandingController
    Requests/{Member,Admin}
    Middleware/CheckPermission.php
  Support/      WhatsApp, AuditLogger, CsvExporter, helpers.php
  Console/Commands/  admin:create, points:reconcile
database/migrations|seeders|factories
resources/views/{landing, member/*, admin/*, components/*}
routes/web.php
tests/{Unit,Feature}
docs/  SPEC, POINTS, DATABASE, DECISIONS, DEPLOYMENT_HOSTINGER, ADMIN_GUIDE
```

## Aturan bisnis inti

`Rp10.000 = 1 poin` (floor), `1 poin = Rp500` diskon. Backend selalu menghitung
ulang. Detail: [`docs/POINTS.md`](docs/POINTS.md).

## Keamanan (ringkas)

Password & PIN hanya di-hash; PIN tak pernah ditampilkan ke admin. CSRF, escaping
XSS Blade, query via Eloquent, throttle login + PIN lockout, otorisasi di server
(bukan sekadar sembunyi tombol), IDOR dicegah, transaksi DB + row lock +
idempotency untuk integritas poin, audit log, ekspor aman dari formula injection.

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [docs/SPEC.md](docs/SPEC.md) | Spesifikasi kebutuhan asli |
| [docs/POINTS.md](docs/POINTS.md) | Aturan perhitungan poin |
| [docs/DATABASE.md](docs/DATABASE.md) | Skema database |
| [docs/DECISIONS.md](docs/DECISIONS.md) | Keputusan bisnis, asumsi, batasan |
| [docs/DEPLOYMENT_HOSTINGER.md](docs/DEPLOYMENT_HOSTINGER.md) | Deployment Hostinger |
| [docs/ADMIN_GUIDE.md](docs/ADMIN_GUIDE.md) | Panduan admin & kasir |

## Perintah berguna

```bash
php artisan admin:create          # buat admin (aman)
php artisan points:reconcile      # cek saldo cache vs ledger
php artisan points:reconcile --fix
php artisan migrate:fresh --seed  # reset skema + seed (DEV saja)
```
