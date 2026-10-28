# Deployment ke Hostinger

Aplikasi: Laravel 11 (PHP 8.2+), MySQL/MariaDB. Panduan untuk Hostinger shared
hosting (hPanel). Jangan menaruh `.env`, backup DB, atau file rahasia di folder publik.

## Deploy via koneksi GitHub (metode yang kamu pilih)

Repo: `https://github.com/EzraNahumury/web-poincust` (branch `main`). Aset
frontend (`public/build`) sudah ikut di repo, jadi **tidak perlu Node** di server.
`vendor/` dan `.env` **tidak** di repo — lihat langkah di bawah.

> **Penting (keterbatasan shared hosting):** Integrasi Git bawaan hPanel pada
> paket shared hanya menyalin repo ke `public_html` dan **tidak bisa** memindah
> document root. Laravel butuh document root di folder `public/`. Karena itu,
> deploy Laravel yang rapi & aman di Hostinger **memerlukan akses SSH**
> (umumnya paket **Business ke atas**) agar bisa membuat symlink
> `public_html → public/`. Tanpa SSH, menaruh seluruh aplikasi di `public_html`
> berisiko (file internal & `.env` bisa terekspos) dan tidak disarankan.

### Opsi A — Ada SSH (disarankan)

1. **hPanel → Git**: hubungkan repository, branch `main`, clone ke folder
   **di luar** `public_html`, mis. `~/avasport`. Aktifkan auto-deploy (webhook)
   bila mau setiap `git push` otomatis `git pull`.
2. Buka **SSH** lalu jalankan sekali:
   ```bash
   cd ~/avasport
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   # edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL=https://domain,
   #            DB_*, SESSION_SECURE_COOKIE=true
   php artisan migrate --force
   php artisan db:seed --force
   php artisan admin:create --role=super_admin
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
3. **Arahkan document root ke `public/`**: hapus `public_html` lalu symlink:
   ```bash
   rm -rf ~/public_html && ln -s ~/avasport/public ~/public_html
   # atau arahkan domain ke ~/avasport/public via hPanel bila tersedia
   ```
4. Setiap deploy berikutnya: `git pull` (atau webhook) lalu — bila ada dependency
   atau migrasi baru — ulangi `composer install --no-dev`, `migrate --force`,
   dan `php artisan optimize`. Aset sudah ikut repo (tak perlu `npm`).

### Opsi B — Tanpa SSH

Integrasi Git bawaan tidak cukup untuk Laravel (lihat catatan di atas). Pilihan:
- **Upgrade ke paket dengan SSH** (paling aman) lalu ikuti Opsi A, atau
- Minta saya meng-commit `vendor/` ke repo (agar `composer install` tak perlu di
  server) — **tetapi** masalah document root `public_html` belum teratasi tanpa
  kemampuan symlink/ubah root, jadi ini bukan solusi lengkap.

`APP_KEY` tetap wajib digenerate di server (`php artisan key:generate`), dan DB
harus dibuat di hPanel lalu migrasi dijalankan (SSH, atau impor skrip bila tak ada SSH).

---

## 0. Prasyarat di Hostinger

- **PHP 8.2 atau 8.3** (hPanel → PHP Configuration). Ekstensi aktif: `pdo_mysql`,
  `mbstring`, `openssl`, `tokenizer`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`.
- **Database MySQL/MariaDB** (hPanel → Databases).
- **Akses SSH** (disarankan; untuk Composer & artisan) atau Composer via hPanel.
- **HTTPS** (SSL gratis Hostinger).

## 1. Database

hPanel → MySQL Databases → buat database + user, catat nama DB, user, password,
host (biasanya `localhost`).

## 2. Upload kode

Via Git (SSH) atau File Manager. Letakkan proyek **di luar** `public_html`
(mis. `~/avasport`). Jangan upload `/vendor`, `/node_modules`, `.env`, `.toolchain`.

## 3. Document root → `public/`

Dua cara:
- **Preferred**: arahkan domain/document root ke `~/avasport/public` (hPanel → domain).
- **Alternatif** (jika root terkunci ke `public_html`): symlink
  `ln -s ~/avasport/public ~/public_html` atau pindahkan isi `public/` ke
  `public_html` dan sesuaikan `index.php` (`require __DIR__.'/../avasport/vendor/autoload.php'` dst).

## 4. Dependency produksi

```bash
cd ~/avasport
composer install --no-dev --optimize-autoloader
```

Build aset frontend: build di lokal (`npm run build`) lalu upload folder
`public/build/`. (Node sering tidak tersedia di shared hosting.)

## 5. Environment (`.env`)

```bash
cp .env.example .env
php artisan key:generate
```
Set minimal:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda
APP_TIMEZONE="Asia/Jakarta"
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
SESSION_SECURE_COOKIE=true
```

## 6. Migration & seeder aman

```bash
php artisan migrate --force
php artisan db:seed --force      # roles, permissions, settings (tanpa admin default)
```

## 7. Admin pertama (aman, tanpa password default)

```bash
php artisan admin:create --role=super_admin
# akan meminta password secara tersembunyi
```

## 8. Optimasi & permission

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R ug+rw storage bootstrap/cache
```

## 9. HTTPS

Aktifkan SSL di hPanel, paksa HTTPS (redirect). Pastikan `APP_URL` https dan
`SESSION_SECURE_COOKIE=true`.

## 10. Uji pasca-deploy

- Login customer & admin.
- Catat satu transaksi → cek poin & saldo di akun customer.
- Buka halaman ulang tahun & tautan WhatsApp.
- Ekspor CSV.

## 11. Backup & restore

```bash
# Backup
mysqldump -u USER -p DBNAME > backup_$(date +%F).sql
# Restore
mysql -u USER -p DBNAME < backup_YYYY-MM-DD.sql
```
Simpan backup di luar folder publik. Backup file `.env` secara terpisah & aman.

## 12. Rollback

- Kode: `git checkout <tag-stabil>` lalu `composer install --no-dev` + `php artisan migrate --force` (atau `migrate:rollback` bila perlu) + `config:cache`.
- DB: restore dari dump sebelum deploy. **Jangan** migrasi destruktif tanpa backup & konfirmasi.

## Troubleshooting

| Gejala | Penyebab umum |
|---|---|
| 500, layar putih | `APP_KEY` belum di-generate / permission `storage` |
| Aset/CSS tidak muncul | `public/build/` belum di-upload atau `APP_URL` salah |
| 419 Page Expired | Cookie/HTTPS/`SESSION_SECURE_COOKIE` tidak konsisten |
| DB connection refused | kredensial `.env` / `DB_HOST` salah |
| Perubahan config tak terbaca | jalankan ulang `php artisan config:cache` |
