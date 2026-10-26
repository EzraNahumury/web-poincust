# DOKUMENTASI SPESIFIKASI SISTEM
# POIN MEMBER AVASPORT

**Versi:** 1.0  
**Jenis aplikasi:** Website Membership dan Loyalty Point  
**Platform:** Web Customer dan Web Admin  
**Target hosting:** Hostinger  
**Database:** MySQL  
**Bahasa aplikasi:** Bahasa Indonesia  
**Status:** Spesifikasi pengembangan awal

---

# 1. LATAR BELAKANG

Avasport membutuhkan website untuk mengelola program poin pelanggan yang berbelanja di toko. Sistem ini memungkinkan customer memiliki akun member, mendapatkan poin dari transaksi belanja, dan mengecek saldo poin secara mandiri.

Admin atau kasir akan menggunakan halaman administrasi untuk mengelola data member, mencatat transaksi, memantau saldo poin, dan melihat pengingat ulang tahun customer.

Website dibagi menjadi dua area utama:

1. **Customer Website:** halaman yang digunakan customer untuk registrasi, login, dan melihat informasi membership.
2. **Admin Website:** halaman internal untuk mengelola customer, transaksi, poin, dan operasional program membership.

Sistem tidak memerlukan integrasi WhatsApp API. WhatsApp hanya digunakan oleh admin secara manual melalui tautan menuju chat customer.

# 2. TUJUAN SISTEM

Sistem harus mampu:

1. Mencatat dan mengelola data seluruh member Avasport.
2. Menghitung poin customer secara otomatis berdasarkan transaksi yang dicatat admin.
3. Memungkinkan customer melihat saldo dan riwayat poin melalui akun pribadi.
4. Menampilkan estimasi nilai diskon yang dapat diperoleh dari poin.
5. Menyediakan riwayat transaksi dan perubahan saldo yang dapat diaudit.
6. Menampilkan pengingat ulang tahun customer kepada admin.
7. Membantu admin menghubungi customer melalui WhatsApp secara manual.
8. Mengurangi kesalahan perhitungan poin dan mencegah manipulasi saldo.
9. Menyediakan pengelolaan data yang aman dan mudah digunakan.
10. Berjalan di lingkungan hosting PHP dan MySQL yang sesuai dengan Hostinger.

# 3. ATURAN BISNIS POIN

Aturan berikut merupakan ketentuan utama yang harus diterapkan secara konsisten di frontend, backend, dan database.

## 3.1 Perolehan poin

Setiap belanja sebesar Rp10.000 mendapatkan 1 poin.

Rumus:

`poin_diperoleh = floor(total_belanja_yang_memenuhi_syarat / 10000)`

Contoh:

| Total belanja yang memenuhi syarat | Poin |
|---:|---:|
| Rp9.000 | 0 |
| Rp10.000 | 1 |
| Rp19.999 | 1 |
| Rp20.000 | 2 |
| Rp75.000 | 7 |
| Rp100.000 | 10 |
| Rp275.000 | 27 |
| Rp1.000.000 | 100 |

Poin harus dibulatkan ke bawah. Jangan menggunakan pembulatan ke bilangan terdekat.

**Ketentuan dasar:** gunakan total belanja setelah diskon transaksi, tetapi sebelum potongan dari penukaran poin. Apabila kebijakan toko berbeda, jadikan dasar perhitungan tersebut sebagai konfigurasi yang dapat diubah melalui keputusan bisnis, bukan aturan yang tersebar di kode.

Jika customer menukar poin dalam sebuah transaksi, poin baru dihitung dari nilai belanja yang memenuhi syarat sebelum diskon penukaran poin, untuk mencegah perhitungan melingkar. Implementasi akhir wajib menampilkan nilai tersebut secara transparan di rincian transaksi.

## 3.2 Nilai penukaran poin

Satu poin bernilai Rp500 untuk diskon.

Rumus:

`nilai_diskon = jumlah_poin_ditukarkan × 500`

Contoh:

| Poin yang ditukarkan | Nilai diskon |
|---:|---:|
| 1 | Rp500 |
| 10 | Rp5.000 |
| 20 | Rp10.000 |
| 50 | Rp25.000 |
| 100 | Rp50.000 |
| 200 | Rp100.000 |

## 3.3 Validasi penukaran

Sistem wajib memastikan:

- Poin yang ditukarkan tidak melebihi saldo tersedia.
- Nilai diskon tidak melebihi total tagihan yang harus dibayar.
- Customer tidak dapat mengubah saldo poin melalui browser atau API publik.
- Penukaran poin harus menjadi bagian dari transaksi yang valid.
- Poin yang sudah ditukarkan tidak dapat digunakan kembali.
- Transaksi yang dibatalkan harus menghasilkan pembalikan poin yang sesuai.
- Saldo poin dan riwayat mutasinya harus konsisten.

Contoh: customer mempunyai 50 poin dan ingin menukarkan 100 poin. Sistem harus menolak transaksi tersebut.

## 3.4 Kebijakan yang harus dapat dikonfigurasi

Jika diperlukan, admin dengan izin yang sesuai dapat mengubah:

- Rupiah minimum per poin.
- Nilai rupiah per poin untuk penukaran.
- Apakah poin berlaku tanpa masa kedaluwarsa.
- Batas penukaran poin per transaksi.
- Ketentuan minimum transaksi.
- Aturan pembulatan dan dasar perhitungan poin.

Untuk implementasi awal, gunakan Rp10.000 per poin dan Rp500 per poin penukaran. Jangan membuat pengaturan konfigurasi terlalu kompleks apabila tidak dibutuhkan.

# 4. PEMBAGIAN SISTEM

## 4.1 Customer Website

Customer website harus mempunyai halaman berikut:

1. Landing page membership Avasport.
2. Registrasi akun.
3. Login.
4. Lupa password dan pemulihan akun.
5. Dashboard customer.
6. Detail saldo poin.
7. Riwayat transaksi.
8. Riwayat mutasi poin.
9. Detail transaksi.
10. Profil dan pengaturan akun.
11. Halaman perubahan password.
12. Halaman keluar akun.

Semua informasi membership bersifat privat. Customer hanya boleh mengakses data miliknya sendiri.

## 4.2 Admin Website

Admin website sekurang-kurangnya mempunyai menu:

1. Dashboard.
2. Data customer.
3. Detail customer.
4. Transaksi.
5. Tambah transaksi.
6. Detail transaksi.
7. Riwayat poin.
8. Penyesuaian poin dengan alasan dan audit log.
9. Pengingat ulang tahun.
10. Pengaturan akun admin.
11. Pengelolaan pengguna dan hak akses.
12. Laporan dan ekspor data.
13. Pengaturan program poin.
14. Audit log aktivitas.

Pisahkan halaman admin dan customer dengan route, layout, middleware, dan otorisasi yang jelas.

# 5. REGISTRASI CUSTOMER

Customer dapat membuat akun melalui halaman registrasi.

## 5.1 Data yang wajib diisi

- Nama lengkap.
- Tanggal lahir.
- Nomor WhatsApp.
- Username.
- Password.
- Konfirmasi password.
- PIN member.
- Konfirmasi PIN member.
- Persetujuan terhadap syarat membership dan kebijakan privasi.

Email dapat disediakan sebagai kolom opsional atau ditambahkan sebagai persyaratan jika diperlukan untuk pemulihan akun.

## 5.2 Username

- Harus unik.
- Tidak boleh kosong.
- Tidak boleh menggunakan karakter yang tidak diizinkan.
- Harus dinormalisasi untuk mencegah duplikasi akibat perbedaan huruf besar dan kecil.
- Tampilkan pesan yang jelas apabila username sudah digunakan.

## 5.3 Password

- Digunakan untuk login.
- Jangan menyimpan password dalam bentuk teks biasa.
- Gunakan mekanisme hashing password yang aman dari framework.
- Terapkan validasi panjang dan kekuatan password.
- Gunakan mekanisme reset password yang aman.
- Batasi percobaan login yang gagal.

## 5.4 PIN member

PIN merupakan lapisan verifikasi tambahan, bukan pengganti password.

Ketentuan:

- Gunakan PIN numerik enam digit.
- Customer mengisi dan mengonfirmasi PIN saat registrasi.
- Jangan pernah menyimpan PIN dalam bentuk teks biasa.
- Simpan PIN dalam bentuk hash menggunakan mekanisme yang sesuai untuk rahasia berentropi rendah, misalnya Argon2id atau mekanisme password hashing yang aman.
- Batasi jumlah percobaan PIN yang salah.
- Berikan jeda atau penguncian sementara setelah beberapa kegagalan.
- Jangan menampilkan PIN di halaman admin.
- Jangan mengirimkan PIN melalui WhatsApp, log aplikasi, atau notifikasi.
- Sediakan proses penggantian PIN setelah autentikasi yang memadai.

PIN dapat digunakan sebagai verifikasi tambahan saat customer melakukan tindakan sensitif yang memang membutuhkan konfirmasi, atau saat melakukan verifikasi membership di toko sesuai alur yang ditentukan.

Jangan meminta PIN pada setiap halaman atau setiap kali dashboard dibuka karena dapat menurunkan kenyamanan penggunaan.

## 5.5 Nomor WhatsApp

- Simpan nomor dalam format yang konsisten.
- Validasi nomor sebelum disimpan.
- Untuk nomor Indonesia, normalisasikan ke format internasional, misalnya `6281234567890`.
- Pastikan nomor hanya bisa digunakan sesuai kebijakan duplikasi yang ditetapkan.
- Jangan menganggap nomor WhatsApp telah terverifikasi hanya karena customer memasukkannya.
- Tampilkan nomor hanya kepada pengguna yang mempunyai izin.
- Jangan menampilkan nomor lengkap di halaman publik.

## 5.6 Tanggal lahir

Tanggal lahir dipakai untuk menampilkan pengingat ulang tahun kepada admin.

- Validasi tanggal yang masuk akal.
- Gunakan zona waktu Asia/Jakarta untuk penentuan tanggal ulang tahun.
- Hindari menampilkan tanggal lahir lengkap kepada customer lain.
- Jangan menampilkan tanggal lahir di halaman publik.
- Batasi akses data tersebut untuk admin yang berwenang.

## 5.7 Pencegahan akun ganda

Sistem perlu memeriksa username, nomor WhatsApp, dan aturan duplikasi lain yang disepakati.

Jika satu nomor WhatsApp hanya boleh dimiliki satu akun, terapkan unique constraint setelah normalisasi nomor. Jika toko membutuhkan nomor yang dapat dipakai beberapa anggota keluarga, aturan ini harus dikonfirmasi sebelum implementasi.

# 6. LOGIN DAN KEAMANAN CUSTOMER

Customer login menggunakan username dan password.

Setelah login, sistem membuat sesi autentikasi yang aman. PIN tidak boleh digunakan sebagai satu-satunya kredensial login.

Fitur yang dibutuhkan:

- Login.
- Logout.
- Reset password.
- Perubahan password.
- Penggantian PIN.
- Pembatasan percobaan login.
- Penguncian sementara jika terdapat percobaan berulang.
- Pengelolaan sesi.
- Proteksi terhadap session fixation.
- Cookie sesi dengan atribut aman sesuai konfigurasi HTTPS.
- CSRF protection.
- Validasi input pada server.
- Pesan kesalahan yang tidak membocorkan informasi sensitif.

Jika pemulihan password tidak menggunakan email, jangan membuat mekanisme reset yang hanya mengandalkan nomor WhatsApp atau data tanggal lahir tanpa verifikasi tambahan. Sediakan alur bantuan admin yang terkontrol dan tercatat.

# 7. CUSTOMER DASHBOARD

Dashboard harus menjadi halaman utama setelah customer berhasil login.

## 7.1 Informasi yang ditampilkan

- Sapaan berdasarkan nama customer.
- Nomor atau identitas member.
- Total saldo poin.
- Estimasi nilai diskon yang dapat diperoleh.
- Jumlah transaksi.
- Transaksi terakhir.
- Riwayat mutasi poin terbaru.
- Informasi atau notifikasi terkait perubahan poin.
- Tombol untuk melihat seluruh riwayat transaksi.
- Tombol menuju halaman profil.

Contoh:

Nama: Andi

Saldo poin: 125 poin

Estimasi nilai diskon: Rp62.500

Total transaksi: 8 transaksi

Transaksi terakhir: Rp275.000

Nilai di atas hanya ilustrasi, bukan data produksi.

## 7.2 Perhitungan estimasi diskon

Estimasi diskon dihitung berdasarkan saldo poin yang tersedia:

`estimasi_diskon = saldo_poin_tersedia × 500`

Estimasi tersebut tidak boleh ditampilkan sebagai uang tunai atau saldo yang dapat ditarik. Poin hanya digunakan sesuai aturan program membership.

## 7.3 Notifikasi di website

Tidak diperlukan pengiriman notifikasi otomatis melalui WhatsApp.

Sebagai gantinya, website menampilkan informasi saat:

- Transaksi baru berhasil dicatat.
- Poin bertambah.
- Poin digunakan untuk penukaran.
- Terdapat pembalikan transaksi.
- Admin melakukan penyesuaian poin yang sah.

Notifikasi harus berasal dari data transaksi yang sudah berhasil disimpan, bukan dari perhitungan sementara di frontend.

Jika sistem tidak memakai WebSocket atau mekanisme real-time, halaman dapat memuat data terbaru ketika customer membuka dashboard atau melakukan refresh. Jangan mengklaim pembaruan real-time jika fitur tersebut belum diimplementasikan.

# 8. RIWAYAT TRANSAKSI CUSTOMER

Customer dapat melihat seluruh transaksi miliknya.

Informasi yang ditampilkan:

- Nomor transaksi.
- Tanggal dan waktu.
- Total belanja.
- Diskon transaksi.
- Poin yang diperoleh.
- Poin yang digunakan.
- Total pembayaran.
- Status transaksi.
- Detail mutasi poin yang berkaitan.

Gunakan pagination agar riwayat tetap ringan saat jumlah transaksi bertambah.

Customer tidak diperbolehkan mengubah atau menghapus transaksi. Jika menemukan kesalahan, customer dapat menghubungi admin.

# 9. ADMIN DASHBOARD

Dashboard admin menjadi pusat operasional program member.

## 9.1 Ringkasan

Tampilkan:

- Total customer terdaftar.
- Customer aktif.
- Jumlah transaksi hari ini.
- Total nominal transaksi hari ini.
- Total poin yang diberikan.
- Total poin yang ditukarkan.
- Customer berulang tahun hari ini.
- Customer yang akan berulang tahun dalam beberapa hari ke depan.
- Transaksi terbaru.
- Aktivitas admin terbaru.

Setiap angka harus berasal dari query database yang sesuai. Jangan menggunakan angka dummy pada dashboard produksi.

## 9.2 Data customer

Admin yang memiliki izin dapat:

- Melihat daftar customer.
- Mencari berdasarkan nama, username, nomor member, atau nomor WhatsApp.
- Melakukan filter dan pagination.
- Melihat profil customer.
- Melihat saldo poin.
- Melihat riwayat transaksi.
- Melihat riwayat mutasi poin.
- Memperbarui data yang diperbolehkan.
- Menonaktifkan akun jika diperlukan.
- Mengakses pengingat ulang tahun.

Data password dan PIN tidak boleh ditampilkan atau dapat dibaca oleh admin.

## 9.3 Detail customer

Halaman detail customer minimal menampilkan:

- Nama lengkap.
- Nomor member.
- Username.
- Nomor WhatsApp sesuai izin.
- Tanggal lahir sesuai izin.
- Tanggal registrasi.
- Status akun.
- Saldo poin tersedia.
- Ringkasan transaksi.
- Riwayat transaksi.
- Riwayat mutasi poin.
- Catatan aktivitas yang relevan.

Admin hanya dapat mengakses fungsi yang diizinkan oleh role-nya.

# 10. PENCATATAN TRANSAKSI OLEH ADMIN

Admin atau kasir harus dapat mencatat transaksi customer dengan alur yang sederhana.

## 10.1 Form transaksi

Field yang diperlukan:

- Customer/member.
- Nomor transaksi yang dibuat otomatis.
- Tanggal transaksi.
- Total belanja sebelum diskon.
- Diskon transaksi.
- Nilai belanja yang memenuhi syarat perolehan poin.
- Poin yang akan ditukarkan, jika ada.
- Nilai diskon dari penukaran poin.
- Total akhir yang dibayar.
- Catatan opsional.
- Admin/kasir yang mencatat transaksi.

Jika toko membutuhkan nomor struk eksternal, sediakan field opsional dengan aturan unik yang sesuai.

## 10.2 Alur transaksi

1. Admin mencari customer.
2. Admin memilih customer yang benar.
3. Sistem menampilkan identitas singkat dan saldo poin terkini.
4. Admin memasukkan nilai transaksi.
5. Sistem menghitung estimasi poin dan penukaran secara otomatis.
6. Admin memeriksa rincian transaksi.
7. Admin mengonfirmasi penyimpanan.
8. Backend memvalidasi ulang semua angka.
9. Sistem menyimpan transaksi dan mutasi poin dalam satu database transaction.
10. Saldo poin terbaru dapat langsung dilihat di halaman customer.

Jangan menghitung poin hanya di browser. Backend harus menghitung ulang semua nilai sebelum menyimpan transaksi.

## 10.3 Contoh transaksi tanpa penukaran

Total belanja yang memenuhi syarat: Rp275.000.

Poin diperoleh: 27 poin.

Jika saldo awal 100 poin, saldo baru menjadi 127 poin.

## 10.4 Contoh transaksi dengan penukaran

Saldo awal: 100 poin.

Total belanja yang memenuhi syarat: Rp275.000.

Poin ditukarkan: 20 poin.

Diskon penukaran: Rp10.000.

Poin baru yang diperoleh: 27 poin.

Saldo akhir: 100 - 20 + 27 = 107 poin.

Nominal akhir yang dibayar harus dihitung sesuai total belanja, diskon transaksi, dan diskon penukaran yang valid.

## 10.5 Konsistensi transaksi

Semua proses berikut harus berhasil atau gagal secara bersama-sama:

- Pembuatan transaksi.
- Pencatatan poin yang diperoleh.
- Pencatatan poin yang ditukarkan.
- Pembaruan saldo atau perhitungan saldo.
- Pencatatan audit.

Gunakan transaksi database dan penguncian yang sesuai untuk mencegah dua transaksi bersamaan memakai poin yang sama.

Cegah pengiriman ulang form atau request yang sama membuat transaksi ganda. Gunakan idempotency mechanism atau perlindungan setara.

# 11. PEMBATALAN DAN KOREKSI TRANSAKSI

Transaksi yang telah berhasil disimpan tidak boleh dihapus permanen secara sembarangan.

Jika transaksi perlu dibatalkan:

1. Admin dengan izin yang sesuai membuka detail transaksi.
2. Sistem meminta alasan pembatalan.
3. Sistem memvalidasi bahwa transaksi belum dibatalkan.
4. Sistem membuat catatan pembalikan yang mengacu pada transaksi asli.
5. Sistem membalikkan poin yang diperoleh dari transaksi.
6. Sistem mengembalikan poin yang digunakan, sesuai aturan pembatalan.
7. Sistem mencatat admin, waktu, alasan, dan dampak perubahan.
8. Transaksi asli tetap tersedia untuk audit.

Jika pembatalan menyebabkan saldo tidak wajar, sistem tidak boleh menghapus riwayat. Gunakan saldo yang dihitung dari ledger atau mekanisme koreksi yang tercatat, serta tampilkan kondisi yang membutuhkan peninjauan admin.

# 12. RIWAYAT MUTASI POIN

Buat ledger atau buku besar poin sebagai sumber utama riwayat perolehan dan penggunaan poin.

Setiap perubahan poin harus memiliki catatan.

Jenis mutasi yang disarankan:

- `EARN`: poin diperoleh dari transaksi.
- `REDEEM`: poin digunakan untuk diskon.
- `REVERSAL`: pembalikan transaksi.
- `ADJUSTMENT_CREDIT`: penambahan poin manual yang sah.
- `ADJUSTMENT_DEBIT`: pengurangan poin manual yang sah.
- `EXPIRED`: poin kedaluwarsa, hanya jika kebijakan masa berlaku diterapkan.

Setiap catatan mutasi minimal mempunyai:

- Customer.
- Transaksi terkait jika ada.
- Jenis mutasi.
- Perubahan poin bertanda positif atau negatif.
- Saldo setelah mutasi jika mekanisme tersebut digunakan.
- Alasan.
- Admin yang melakukan tindakan jika relevan.
- Waktu pembuatan.

Jangan memperbolehkan admin mengedit angka mutasi lama secara langsung. Koreksi dilakukan melalui mutasi baru agar jejak audit tetap utuh.

Saldo dapat dihitung dari ledger atau dipelihara menggunakan saldo ter-cache yang diperbarui secara atomik. Jika menggunakan saldo ter-cache, sediakan cara untuk memeriksa dan merekonsiliasi saldo dengan ledger.

# 13. PENGINGAT ULANG TAHUN CUSTOMER

Sistem harus membantu admin mengetahui customer yang berulang tahun.

## 13.1 Dashboard ulang tahun

Tampilkan:

- Customer yang berulang tahun hari ini.
- Customer yang berulang tahun besok.
- Customer yang akan berulang tahun dalam tujuh hari ke depan.
- Nama customer.
- Nomor WhatsApp sesuai izin.
- Tanggal ulang tahun atau hari ulang tahun berikutnya.
- Tombol membuka WhatsApp.
- Status pengingat, misalnya belum ditindaklanjuti atau sudah ditindaklanjuti.

Daftar harus dapat difilter berdasarkan rentang tanggal dan dicari berdasarkan nama.

## 13.2 Perhitungan tanggal

Gunakan tanggal dan zona waktu Asia/Jakarta.

Pengingat dihitung berdasarkan tanggal dan bulan kelahiran, bukan tahun lahir.

Untuk customer yang lahir pada 29 Februari, tentukan aturan tampilan pada tahun nonkabisat secara konsisten, misalnya 28 Februari, dan dokumentasikan aturan tersebut.

## 13.3 WhatsApp manual

Sediakan tombol seperti:

- Buka WhatsApp.
- Tandai sudah dihubungi.
- Catatan tindak lanjut opsional.

Tautan menuju format `https://wa.me/<nomor>?text=<pesan-yang-di-encode>`.

Nomor harus berasal dari data customer yang telah dinormalisasi. Pesan harus di-URL-encode dengan benar.

Contoh pesan:

“Selamat ulang tahun, Kak [Nama]! 🎉 Semoga selalu sehat, bahagia, dan sukses. Terima kasih sudah menjadi bagian dari member Avasport!”

Tombol hanya membuka WhatsApp dengan pesan yang telah disiapkan. Admin tetap harus memeriksa dan menekan tombol kirim sendiri.

**Penting:** membuka tautan WhatsApp bukan bukti bahwa pesan sudah terkirim. Status tindak lanjut harus ditandai oleh admin secara manual.

Tidak diperlukan:

- WhatsApp Business API.
- OTP WhatsApp.
- Bot WhatsApp.
- Pengiriman pesan otomatis.
- Integrasi Baileys atau otomasi WhatsApp tidak resmi.

# 14. LAPORAN DAN EKSPOR

Sediakan laporan dasar untuk admin:

- Daftar customer.
- Transaksi per periode.
- Transaksi per customer.
- Poin yang diberikan per periode.
- Poin yang ditukarkan.
- Riwayat mutasi poin.
- Pembatalan dan koreksi.
- Customer yang berulang tahun.

Fitur ekspor CSV dapat dijadikan prioritas awal. XLSX atau PDF dapat ditambahkan jika dibutuhkan.

Pastikan ekspor menghormati hak akses dan filter yang sedang diterapkan.

Lindungi ekspor CSV dari formula injection. Jangan memasukkan data rahasia seperti password hash atau PIN hash.

# 15. ROLE DAN HAK AKSES

Implementasikan role-based access control.

Role minimal:

### Super Admin

- Mengelola akun admin.
- Mengatur role dan permission.
- Mengelola pengaturan program poin.
- Melihat dan mengelola semua customer.
- Mengakses transaksi, laporan, dan audit log.
- Melakukan tindakan koreksi sesuai kewenangan.

### Admin/Kasir

- Melihat customer sesuai izin.
- Mencatat transaksi.
- Melihat saldo dan riwayat poin.
- Mengakses pengingat ulang tahun.
- Membuka WhatsApp secara manual.
- Tidak dapat mengelola akun super admin atau mengubah pengaturan kritis tanpa izin.

Hak akses harus diperiksa di backend, bukan hanya dengan menyembunyikan tombol pada frontend.

Jika hanya ada satu jenis admin pada versi awal, tetap bangun struktur yang memungkinkan role tambahan tanpa harus mengulang seluruh sistem.

# 16. DESAIN DATABASE

Gunakan MySQL dengan foreign key, indeks, constraint, dan tipe data yang sesuai.

Tabel yang disarankan:

## 16.1 `customers`

Field minimum:

- `id`
- `member_number`
- `name`
- `birth_date`
- `whatsapp_number`
- `username`
- `email` nullable jika email tidak diwajibkan
- `password`
- `pin_hash`
- `status`
- `created_at`
- `updated_at`
- `deleted_at` jika menggunakan soft delete

Ketentuan:

- `member_number` unik.
- `username` unik setelah normalisasi.
- Nomor WhatsApp memiliki constraint sesuai kebijakan duplikasi.
- Password dan PIN hanya disimpan dalam bentuk hash.
- Data profil yang dihapus tidak boleh menghilangkan jejak transaksi atau audit.

## 16.2 `admins` atau tabel users terintegrasi

Gunakan tabel autentikasi yang sesuai dengan framework.

Field yang diperlukan:

- `id`
- `name`
- `username` atau email login
- `password`
- `role_id`
- `status`
- `last_login_at`
- `created_at`
- `updated_at`

Jika menggunakan tabel `users` bersama, autentikasi dan pemisahan role customer/admin harus dirancang dengan jelas. Jangan sampai akun customer dapat mengakses panel admin karena kesalahan middleware.

## 16.3 `roles` dan `permissions`

Field yang disarankan:

- `id`
- `name`
- `description`

Bila dibutuhkan, gunakan tabel penghubung untuk banyak permission per role.

## 16.4 `transactions`

Field minimum:

- `id`
- `transaction_number`
- `customer_id`
- `created_by`
- `transaction_date`
- `gross_amount`
- `transaction_discount`
- `eligible_amount`
- `points_redeemed`
- `redemption_discount`
- `points_earned`
- `final_amount`
- `status`
- `notes`
- `reversed_transaction_id` nullable jika digunakan untuk hubungan pembalikan
- `created_at`
- `updated_at`

Gunakan tipe integer yang sesuai untuk nominal rupiah. Jangan memakai floating-point untuk perhitungan uang.

Nomor transaksi harus unik.

Pastikan arti setiap kolom konsisten. Jangan mencampur diskon transaksi dan diskon penukaran poin.

## 16.5 `point_ledger`

Field minimum:

- `id`
- `customer_id`
- `transaction_id` nullable jika bukan mutasi transaksi
- `type`
- `points_delta`
- `description`
- `created_by` nullable untuk proses sistem
- `reversal_of_id` nullable
- `idempotency_key` jika diperlukan
- `created_at`

Ledger harus immutable secara operasional: catatan yang telah diposting tidak boleh diubah atau dihapus melalui alur bisnis normal.

## 16.6 `audit_logs`

Field minimum:

- `id`
- `actor_type`
- `actor_id`
- `action`
- `entity_type`
- `entity_id`
- `before_data` jika relevan
- `after_data` jika relevan
- `reason` jika relevan
- `ip_address` jika diperlukan dan sesuai kebijakan privasi
- `created_at`

Jangan menyimpan password, PIN, token, atau kredensial dalam audit log.

## 16.7 `birthday_followups`

Field minimum:

- `id`
- `customer_id`
- `birthday_year`
- `status`
- `followed_up_by`
- `followed_up_at`
- `notes`
- `created_at`
- `updated_at`

Gunakan constraint untuk mencegah pencatatan tindak lanjut ganda pada customer dan tahun yang sama, jika sesuai dengan alur bisnis.

## 16.8 `settings`

Field yang dapat digunakan:

- `key`
- `value`
- `updated_by`
- `updated_at`

Simpan konfigurasi program poin yang memang perlu diubah melalui panel admin. Validasi nilai sebelum diterapkan.

# 17. ARSITEKTUR TEKNIS

Claude harus memeriksa repository dan prototype terlebih dahulu sebelum menentukan struktur final.

Jika belum ada proyek yang berjalan, Laravel dengan MySQL merupakan pilihan yang disarankan karena mendukung autentikasi, validasi, middleware, migration, queue, dan deployment PHP.

Pilihan arsitektur awal:

- Backend: Laravel versi stabil yang kompatibel dengan PHP di hosting.
- Database: MySQL atau MariaDB yang didukung Hostinger.
- Frontend: Blade dan Tailwind CSS, atau pendekatan frontend lain yang paling cocok dengan prototype.
- Autentikasi: session-based authentication yang aman.
- Penyimpanan password: hashing bawaan framework.
- Penyimpanan PIN: hashing khusus untuk PIN.
- Validasi: server-side validation.
- Database: migration dan seeder.
- Testing: automated feature tests dan unit tests.

Jangan memaksakan stack baru jika prototype atau repository yang tersedia sudah menggunakan teknologi yang layak dan kompatibel. Lakukan audit terlebih dahulu.

Hindari ketergantungan pada layanan berbayar eksternal jika tidak dibutuhkan.

# 18. KEAMANAN APLIKASI

Implementasikan minimal:

- Password hashing yang aman.
- PIN hashing yang aman.
- Validasi server-side.
- CSRF protection.
- Proteksi XSS melalui escaping output.
- Query aman melalui ORM atau parameterized queries.
- Pembatasan login dan percobaan PIN.
- Session security.
- Role-based authorization.
- HTTPS pada produksi.
- Pembatasan akses halaman admin.
- Rate limiting untuk endpoint sensitif.
- Validasi ukuran dan tipe file jika unggahan ditambahkan.
- Audit log untuk tindakan sensitif.
- Proteksi IDOR agar customer tidak dapat melihat customer lain dengan mengganti ID.
- Pencegahan double submission dan transaksi ganda.
- Transaksi database untuk mutasi poin.
- Perlindungan ekspor dari formula injection.
- Backup database dan prosedur pemulihan.
- Penanganan error yang tidak menampilkan stack trace atau rahasia di produksi.

Jangan menyimpan secret, kredensial database, APP_KEY, atau password di repository publik.

# 19. PENGALAMAN PENGGUNA DAN DESAIN

Gunakan prototype yang diberikan sebagai referensi utama untuk arah desain.

Jika prototype belum tersedia, buat desain awal yang:

- Modern, bersih, dan profesional.
- Sesuai dengan identitas brand Avasport.
- Responsif pada desktop, tablet, dan mobile.
- Mudah dipakai kasir saat melayani customer.
- Memiliki hierarki informasi yang jelas.
- Memiliki loading state, empty state, error state, dan success state.
- Memiliki konfirmasi untuk tindakan penting.
- Menggunakan bahasa Indonesia yang natural.
- Tidak terlalu ramai atau menyerupai template dashboard generik tanpa penyesuaian.

Customer website harus terasa seperti portal membership, bukan panel administrasi.

Admin website boleh menggunakan layout operasional yang memprioritaskan kecepatan pencarian customer dan pencatatan transaksi.

Pastikan form memiliki validasi yang mudah dipahami. Jangan mengandalkan warna saja untuk menyampaikan status.

# 20. HALAMAN DAN ROUTE YANG DIREKOMENDASIKAN

Contoh struktur route; sesuaikan dengan framework dan konvensi repository.

## Customer

- `/member/register`
- `/member/login`
- `/member/forgot-password`
- `/member/dashboard`
- `/member/transactions`
- `/member/transactions/{id}`
- `/member/points`
- `/member/profile`
- `/member/security`

## Admin

- `/admin/login`
- `/admin/dashboard`
- `/admin/customers`
- `/admin/customers/{id}`
- `/admin/transactions`
- `/admin/transactions/create`
- `/admin/transactions/{id}`
- `/admin/birthdays`
- `/admin/reports`
- `/admin/settings`
- `/admin/users`
- `/admin/audit-logs`

Semua route harus menggunakan middleware autentikasi dan otorisasi yang sesuai.

Route di atas adalah rancangan awal, bukan keharusan untuk menggunakan path yang sama persis.

# 21. TESTING DAN KRITERIA PENERIMAAN

Claude harus membuat dan menjalankan pengujian untuk skenario berikut.

## Registrasi dan akun

- Registrasi berhasil dengan data valid.
- Username yang sama ditolak.
- Data tidak valid ditolak.
- Password dan PIN tidak disimpan sebagai teks biasa.
- Customer tidak bisa mengakses panel admin.
- Customer tidak bisa membaca profil customer lain.
- Percobaan PIN berulang dibatasi.

## Perhitungan poin

- Rp9.000 menghasilkan 0 poin.
- Rp10.000 menghasilkan 1 poin.
- Rp19.999 menghasilkan 1 poin.
- Rp20.000 menghasilkan 2 poin.
- Rp275.000 menghasilkan 27 poin.
- Nilai diskon 20 poin adalah Rp10.000.
- Penukaran melebihi saldo ditolak.
- Penukaran dengan nilai diskon melebihi batas transaksi ditolak.
- Poin tidak terpotong dua kali saat request dikirim ulang.

## Transaksi

- Transaksi valid berhasil disimpan.
- Poin dan transaksi konsisten.
- Transaksi gagal tidak meninggalkan mutasi parsial.
- Pembatalan membalikkan poin dengan benar.
- Transaksi yang sudah dibatalkan tidak dapat dibatalkan ulang.
- Riwayat transaksi tetap dapat diaudit.
- Transaksi bersamaan tidak menyebabkan saldo negatif atau penggunaan poin ganda.

## Customer dashboard

- Saldo sesuai dengan ledger.
- Riwayat hanya menampilkan transaksi milik customer yang login.
- Estimasi diskon sesuai saldo poin.
- Data terbaru muncul setelah transaksi berhasil dicatat.
- Status kosong, loading, dan error ditangani dengan baik.

## Ulang tahun

- Customer yang berulang tahun hari ini tampil dengan benar.
- Customer yang berulang tahun mendatang tampil dalam rentang yang ditentukan.
- Tahun lahir tidak memengaruhi pencocokan tanggal dan bulan.
- Tombol WhatsApp membuka nomor customer yang benar.
- Status tindak lanjut tidak mengklaim pesan terkirim otomatis.

## Keamanan dan otorisasi

- Pengguna tanpa login tidak dapat mengakses halaman privat.
- Customer tidak dapat mengubah saldo lewat request manual.
- Admin dengan role terbatas tidak dapat menjalankan tindakan super admin.
- Input berbahaya tidak dieksekusi sebagai kode.
- Informasi rahasia tidak muncul di log.
- Ekspor hanya memuat data yang diizinkan.

# 22. DEPLOYMENT HOSTINGER

Sebelum deployment, periksa:

- Versi PHP yang tersedia.
- Ekstensi PHP yang diperlukan.
- Dukungan database.
- Document root.
- Kemampuan menjalankan Composer.
- Ketersediaan terminal atau SSH.
- Konfigurasi HTTPS.
- Konfigurasi cron jika memang diperlukan.
- Lokasi penyimpanan file dan permission.

Panduan deployment harus mencakup:

1. Persiapan environment produksi.
2. Pembuatan database dan user database.
3. Pengaturan environment variables.
4. Instalasi dependency produksi.
5. Eksekusi migration.
6. Pembuatan akun admin pertama dengan cara aman.
7. Konfigurasi document root atau public directory.
8. Pengaturan permission direktori.
9. Aktivasi HTTPS.
10. Pengujian login customer dan admin.
11. Pengujian transaksi dan perhitungan poin.
12. Backup dan prosedur restore.
13. Penanganan error produksi.
14. Langkah rollback jika deployment bermasalah.

Jangan menaruh file `.env`, backup database, atau file rahasia di direktori publik.

Jangan mengandalkan seed akun admin default dengan password yang diketahui. Sediakan proses pembuatan admin pertama yang aman.

# 23. OUTPUT YANG WAJIB DIBUAT

Pada akhir pengerjaan, repository harus memiliki:

1. Aplikasi frontend dan backend yang berfungsi.
2. Migration database.
3. Seeder yang aman jika diperlukan.
4. Model dan relasi database.
5. Controller atau service layer.
6. Middleware autentikasi dan otorisasi.
7. Halaman customer.
8. Halaman admin.
9. Validasi form.
10. Perhitungan poin di backend.
11. Ledger dan audit log.
12. Pengingat ulang tahun.
13. Tautan WhatsApp manual.
14. Laporan dan ekspor dasar.
15. Automated tests.
16. File konfigurasi contoh tanpa secret.
17. README instalasi lokal.
18. Dokumentasi database.
19. Dokumentasi deployment Hostinger.
20. Panduan penggunaan untuk admin dan kasir.
21. Daftar keputusan bisnis yang masih perlu dikonfirmasi.
22. Ringkasan pengujian dan hasil aktual.

# 24. KEBIJAKAN IMPLEMENTASI

- Jangan mengisi dashboard produksi dengan data dummy.
- Jangan mengimplementasikan fitur yang terlihat berfungsi tetapi tidak tersambung ke backend.
- Jangan menghitung saldo poin hanya berdasarkan data frontend.
- Jangan menghapus riwayat transaksi demi memperbaiki saldo.
- Jangan menyimpan PIN atau password sebagai teks biasa.
- Jangan membuat integrasi WhatsApp otomatis.
- Jangan membuat fitur tambahan yang meningkatkan kompleksitas tanpa manfaat yang jelas.
- Jangan mengubah aturan bisnis utama tanpa mencatat alasan dan dampaknya.
- Jangan mengklaim pengujian berhasil jika belum dijalankan.
- Jangan menutupi keterbatasan hosting atau dependency.
- Jika ada persyaratan yang belum jelas, dokumentasikan asumsi dan ajukan pertanyaan hanya untuk keputusan yang benar-benar menghambat implementasi.

---

# MASTER PROMPT UNTUK CLAUDE

Kamu bertindak sebagai Senior Full-Stack Engineer, Software Architect, Database Engineer, Security Reviewer, QA Engineer, dan UI/UX Engineer.

Tugasmu adalah mengembangkan website Poin Member Avasport berdasarkan spesifikasi dokumen ini dan prototype yang akan saya berikan.

**Tujuanmu bukan hanya memberikan saran atau membuat mockup. Kamu harus mengaudit proyek, merancang implementasi, membangun fitur yang diperlukan, menguji hasilnya, dan menyiapkan dokumentasi agar aplikasi dapat dijalankan dan di-deploy.**

## Tahap 1 — Audit sebelum coding

Sebelum mengubah kode:

1. Periksa seluruh struktur repository.
2. Periksa teknologi, framework, dependency, database, dan konfigurasi yang sudah ada.
3. Pelajari prototype, screenshot, atau desain yang saya lampirkan.
4. Identifikasi fitur yang sudah tersedia dan yang belum ada.
5. Identifikasi masalah arsitektur, keamanan, UX, dan konsistensi data.
6. Cocokkan kondisi proyek dengan semua kebutuhan dalam dokumen ini.
7. Buat daftar requirement dengan status:
   - Sudah tersedia.
   - Perlu diperbaiki.
   - Belum tersedia.
   - Belum dapat dipastikan.
8. Identifikasi risiko yang bisa menyebabkan kehilangan data, saldo poin salah, atau akses tidak sah.
9. Tentukan stack terbaik berdasarkan kondisi proyek dan kompatibilitas Hostinger.

Jangan langsung mengganti framework atau membangun ulang proyek tanpa alasan yang kuat.

## Tahap 2 — Berpikir kritis dan membuat rencana

Sebelum implementasi, evaluasi:

- Apakah aturan poin konsisten?
- Apakah alur registrasi dan autentikasi aman?
- Apakah username, password, dan PIN memiliki fungsi yang jelas?
- Bagaimana mencegah penggunaan poin secara ganda?
- Bagaimana menjaga saldo tetap konsisten saat transaksi bersamaan?
- Bagaimana menangani pembatalan dan koreksi?
- Bagaimana membatasi akses customer dan admin?
- Bagaimana memastikan pengingat ulang tahun tidak salah?
- Apakah aplikasi dapat berjalan di Hostinger tanpa layanan tambahan yang tidak diperlukan?

Buat rencana implementasi yang berurutan. Jika menemukan risiko atau kekurangan spesifikasi, jelaskan dan berikan solusi sebelum menerapkannya.

## Tahap 3 — Implementasi bertahap

Kerjakan dalam urutan:

1. Fondasi aplikasi dan konfigurasi.
2. Database, migration, model, dan relasi.
3. Autentikasi customer dan admin.
4. Registrasi, password, dan PIN member.
5. Customer dashboard.
6. Data customer pada panel admin.
7. Transaksi dan perhitungan poin.
8. Ledger, pembatalan, dan audit log.
9. Riwayat transaksi customer.
10. Pengingat ulang tahun.
11. Tombol WhatsApp manual.
12. Laporan dan ekspor.
13. Keamanan, validasi, dan penanganan error.
14. Automated testing.
15. Dokumentasi dan deployment.

Gunakan struktur kode yang jelas dan mudah dipelihara. Hindari controller yang terlalu besar, duplikasi perhitungan poin, dan business logic yang tersebar di banyak tempat.

## Tahap 4 — UI/UX

Gunakan prototype sebagai acuan utama. Pertahankan identitas visual yang relevan dan perbaiki bagian yang bermasalah tanpa menghilangkan arah desainnya.

Pastikan:

- Customer website dan admin website memiliki tujuan visual berbeda.
- Desain responsif.
- Form registrasi mudah dipahami.
- Kasir dapat mencatat transaksi dengan cepat.
- Saldo poin terlihat jelas.
- Tabel dapat dicari dan difilter.
- Semua aksi memiliki feedback.
- Tidak ada tombol palsu yang tidak menjalankan fungsi.
- Data sensitif tidak ditampilkan tanpa kebutuhan.

## Tahap 5 — Pengujian dan audit akhir

Setelah implementasi:

1. Jalankan automated tests.
2. Jalankan test perhitungan poin.
3. Jalankan test autentikasi dan otorisasi.
4. Uji transaksi, penukaran, dan pembatalan.
5. Uji konsistensi ledger.
6. Uji halaman ulang tahun.
7. Periksa keamanan input dan endpoint.
8. Periksa tampilan desktop dan mobile.
9. Periksa error log.
10. Catat semua test yang gagal atau belum dapat dijalankan.

Jangan menandai fitur selesai hanya karena halaman dapat dibuka. Pastikan alur frontend, backend, dan database benar-benar terhubung.

## Tahap 6 — Dokumentasi

Buat README yang menjelaskan:

- Gambaran aplikasi.
- Teknologi yang digunakan.
- Prasyarat.
- Instalasi lokal.
- Konfigurasi `.env`.
- Pembuatan database.
- Migration dan seeder.
- Pembuatan admin pertama.
- Cara menjalankan aplikasi.
- Cara menjalankan tests.
- Struktur folder.
- Alur bisnis utama.
- Deployment ke Hostinger.
- Backup dan restore.
- Troubleshooting.
- Keamanan produksi.

Buat juga dokumen khusus untuk skema database, aturan perhitungan poin, dan prosedur deployment.

## Aturan kerja penting

- Audit dahulu, baru implementasi.
- Jangan berhenti pada analisis atau rencana jika repository dan lingkungan kerja memungkinkan coding langsung.
- Jangan menghapus fitur yang sudah berfungsi tanpa alasan.
- Jangan mengarang hasil pengujian.
- Jangan menyimpan secret ke repository.
- Jangan membuat integrasi WhatsApp API.
- Jangan menggunakan saldo poin dari frontend sebagai sumber kebenaran.
- Jangan menghapus transaksi yang sudah diposting.
- Utamakan keamanan, konsistensi data, dan kemudahan penggunaan.
- Jika prototype atau informasi penting belum tersedia, catat sebagai blocker atau asumsi yang perlu dikonfirmasi.
- Setelah setiap tahap, laporkan perubahan file, hasil pengujian, masalah yang tersisa, dan tahap berikutnya.

**Mulailah dengan audit repository dan prototype. Setelah itu, tampilkan temuan serta rencana implementasi yang terukur, kemudian lanjutkan membangun aplikasi secara bertahap.**

---

**Akhir dokumen spesifikasi.**