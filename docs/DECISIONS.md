# Keputusan Bisnis, Asumsi & Batasan

Keputusan yang diambil saat implementasi karena spesifikasi memberi keleluasaan
atau perlu dipastikan. Ubah bila kebijakan toko berbeda.

## Keputusan & asumsi

1. **Dasar perhitungan poin** = `gross_amount − transaction_discount` (eligible),
   dihitung **sebelum** diskon penukaran poin. (README §3.1 memperbolehkan ini
   sebagai konfigurasi; nilai disimpan per transaksi.)
2. **1 nomor WhatsApp = 1 akun.** Nomor dinormalisasi ke format `62…` lalu diberi
   unique constraint. Jika toko ingin berbagi nomor antar keluarga, hapus unique
   index pada `customers.whatsapp_number` dan sesuaikan validasi.
3. **Username** dinormalisasi ke huruf kecil; unik setelah normalisasi.
4. **Ulang tahun 29 Februari** ditampilkan pada **28 Februari** di tahun non-kabisat.
5. **PIN awal** untuk customer yang didaftarkan admin diisi bersama customer di
   kasir, lalu customer disarankan menggantinya sendiri. Setelah tersimpan, PIN
   (hash) tidak pernah dapat dilihat admin.
6. **Pemulihan password** tidak lewat email/WhatsApp otomatis — melalui bantuan
   admin terkontrol (halaman `/member/forgot-password` menjelaskan alurnya).
7. **Reversal** tidak membuat baris transaksi baru; transaksi asli ditandai
   `reversed` + ditulis baris `REVERSAL` di ledger yang mengacu transaksi asli.
8. **Saldo negatif** boleh terjadi bila pembatalan menarik poin yang sudah
   dipakai. Riwayat tidak dihapus; saldo negatif ditandai untuk peninjauan
   (dashboard customer detail + `points:reconcile`).
9. **PIN hashing**: Argon2id bila tersedia di build PHP, selain itu bcrypt
   (keduanya aman). Lihat `config/pin.php`.
10. **Role awal**: `super_admin` dan `admin` (kasir). Struktur permission siap
    untuk menambah role tanpa menulis ulang sistem.

## Batasan yang diketahui

- **Uji konkurensi otomatis**: proteksi balapan diterapkan via
  `SELECT … FOR UPDATE` (kunci baris customer) + DB transaction + `idempotency_key`.
  Simulasi dua request benar-benar paralel sulit dilakukan di PHP CLI single-proc
  pada Windows, sehingga tidak ada test paralel otomatis. Jaminan ada pada level
  kode (`TransactionService`, `LedgerService`) + unique `idempotency_key`.
  Idempotensi (double-submit) diuji otomatis.
- **Ekspor** tersedia dalam CSV (dengan proteksi formula injection). XLSX/PDF
  belum diimplementasikan (README menyebut CSV sebagai prioritas awal).
- **Notifikasi real-time** tidak ada; data termutakhir saat halaman dibuka/di-refresh.
- **WhatsApp** hanya tautan `wa.me` manual — tanpa API/bot/OTP/otomasi.

## Perlu dikonfirmasi pemilik toko

- Apakah diskon penukaran poin boleh membuat `final_amount` = 0? (Saat ini boleh,
  selama `redemption_discount ≤ eligible_amount`.)
- Kebijakan kedaluwarsa poin (tipe ledger `EXPIRED` sudah disiapkan, belum ada job).
- Apakah kasir (`admin`) boleh melakukan reversal? Saat ini hanya `super_admin`
  (ubah via permission `transactions.reverse` pada role).
