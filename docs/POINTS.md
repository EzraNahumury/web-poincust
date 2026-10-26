# Aturan Perhitungan Poin

Satu-satunya sumber kebenaran perhitungan poin ada di
`app/Domain/Points/PointsCalculator.php`. Frontend hanya menampilkan estimasi;
**backend selalu menghitung ulang** setiap nominal dan poin sebelum menyimpan.

## Nilai dasar (default)

| Parameter | Nilai default | Sumber |
|---|---|---|
| Rupiah per 1 poin | `Rp10.000` | `settings.points_rupiah_per_point` → fallback `config/points.php` |
| Nilai tukar per poin | `Rp500` | `settings.points_redeem_value` → fallback `config/points.php` |

Super admin dapat mengubah nilai ini di **Admin → Pengaturan**. Perubahan hanya
berlaku untuk transaksi baru; transaksi lama menyimpan nilainya sendiri.

## Perolehan poin (EARN)

```
eligible_amount = gross_amount - transaction_discount      (>= 0)
points_earned   = floor(eligible_amount / rupiah_per_point)
```

- Selalu dibulatkan **ke bawah** (`intdiv`, bilangan bulat — tanpa floating point).
- Dasar perhitungan = belanja setelah diskon transaksi, **sebelum** potongan penukaran poin (mencegah perhitungan melingkar).

| Eligible | Poin |
|---:|---:|
| Rp9.000 | 0 |
| Rp10.000 | 1 |
| Rp19.999 | 1 |
| Rp20.000 | 2 |
| Rp75.000 | 7 |
| Rp100.000 | 10 |
| Rp275.000 | 27 |
| Rp1.000.000 | 100 |

## Penukaran poin (REDEEM)

```
redemption_discount = points_redeemed * redeem_value_per_point
final_amount        = eligible_amount - redemption_discount
```

Validasi wajib (di backend):

1. `points_redeemed <= saldo poin customer` (baris customer dikunci `FOR UPDATE`).
2. `redemption_discount <= eligible_amount` (diskon tidak melebihi tagihan).
3. Customer tidak pernah menentukan sendiri jumlah poin yang diterima.

| Poin ditukar | Nilai diskon |
|---:|---:|
| 1 | Rp500 |
| 20 | Rp10.000 |
| 100 | Rp50.000 |

## Contoh transaksi dengan penukaran

```
Saldo awal           : 100 poin
Belanja (eligible)   : Rp275.000
Poin ditukar         : 20  → diskon Rp10.000
Total dibayar        : Rp265.000
Poin diperoleh       : floor(275.000 / 10.000) = 27
Saldo akhir          : 100 - 20 + 27 = 107 poin
```

## Estimasi diskon (dashboard customer)

```
estimasi_diskon = saldo_poin * redeem_value_per_point
```
Hanya indikatif — poin tidak dapat diuangkan.

## Integritas

- Uang & poin disimpan sebagai **integer** (rupiah & poin), tidak pernah float.
- Setiap transaksi + mutasi poin disimpan dalam **satu DB transaction**.
- Baris customer dikunci (`lockForUpdate`) agar transaksi bersamaan tidak double-spend.
- `idempotency_key` mencegah transaksi ganda akibat request dikirim ulang.
- Saldo ter-cache (`customers.points_balance`) selalu sama dengan `SUM(point_ledger.points_delta)`; verifikasi/rekonsiliasi: `php artisan points:reconcile [--fix]`.
