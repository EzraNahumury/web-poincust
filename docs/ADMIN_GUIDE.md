# Panduan Admin & Kasir

## Masuk
Buka `/admin/login`, masuk dengan username/email + password yang diberikan super
admin. Akun yang nonaktif tidak dapat masuk. Semua tindakan penting tercatat di
Audit Log.

## Peran
- **Super Admin**: semua akses, termasuk pembatalan transaksi, penyesuaian poin,
  pengaturan program poin, kelola admin, audit log.
- **Admin / Kasir**: customer, catat transaksi, ulang tahun, laporan.

## Mencatat transaksi (kasir)
1. **Transaksi → Tambah Transaksi** (atau dari halaman customer: tombol **+ Transaksi**).
2. Cari customer (nama/nomor member/WhatsApp), pilih. Saldo poin tampil.
3. Isi **Total belanja** dan **Diskon transaksi** (bila ada).
4. Bila customer menukar poin, isi **Poin ditukar**. Panel kanan menampilkan
   estimasi (poin didapat, diskon, total bayar, saldo akhir).
5. Periksa, lalu **Simpan Transaksi**. Server menghitung ulang semua nilai.
   > Estimasi di layar hanya perkiraan; nilai final dari server yang berlaku.

## Membatalkan transaksi (super admin)
Buka detail transaksi → **Batalkan Transaksi**, isi alasan. Sistem membuat
reversal (poin dikembalikan sesuai aturan), transaksi asli tetap tersimpan.
Transaksi yang sudah dibatalkan tidak bisa dibatalkan lagi.

## Penyesuaian poin (super admin)
Halaman detail customer → **Penyesuaian poin**: pilih Tambah/Kurangi, jumlah, dan
**alasan** (wajib). Tercatat di ledger & audit. Pengurangan tidak boleh melebihi saldo.

## Ulang tahun & WhatsApp
**Ulang Tahun**: daftar yang berulang tahun hari ini & beberapa hari ke depan
(zona waktu Asia/Jakarta). Tombol **WhatsApp** membuka chat dengan pesan siap kirim
— **admin tetap menekan kirim sendiri**. Setelah benar-benar menghubungi, klik
**Tandai dihubungi**. Membuka WhatsApp ≠ pesan terkirim.

## Laporan & ekspor
**Laporan**: pilih periode, lihat ringkasan, ekspor CSV (transaksi, mutasi poin,
customer). Ekspor tidak memuat password/PIN dan aman dari formula injection.

## Pengaturan program poin (super admin)
**Pengaturan**: ubah rupiah per poin & nilai tukar per poin. Berlaku untuk
transaksi baru saja.

## Kelola admin (super admin)
**Admin & Role**: tambah/edit admin, atur role & status. Anda tidak dapat
menonaktifkan atau mengubah role akun sendiri.

## Keamanan
- Password & PIN customer disimpan ter-hash; PIN tidak pernah bisa dilihat admin.
- Jangan membagikan kredensial admin. Logout setelah selesai.
