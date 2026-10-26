# Skema Database

MySQL/MariaDB, engine InnoDB, charset `utf8mb4`. Semua nominal rupiah & poin
bertipe **integer** (tanpa floating point).

## Ringkasan tabel

| Tabel | Fungsi |
|---|---|
| `users` | Akun admin / kasir (guard `web`) |
| `roles`, `permissions`, `permission_role` | RBAC |
| `customers` | Akun member (guard `customer`) |
| `transactions` | Transaksi belanja + poin |
| `point_ledger` | Buku besar poin (immutable, sumber kebenaran saldo) |
| `audit_logs` | Jejak audit tindakan sensitif |
| `birthday_followups` | Status tindak lanjut ulang tahun |
| `settings` | Konfigurasi program poin |

## `users` (admin)
`id, name, username (unik), email (unik), password (hash), role_id → roles,
status (active|inactive), last_login_at, remember_token, timestamps`.

## `roles` / `permissions` / `permission_role`
Role: `super_admin` (semua izin, + bypass via `Gate::before`) dan `admin`
(subset operasional). Izin lihat `App\Domain\Auth\Permissions`.

## `customers` (member)
`id, member_number (unik), name, birth_date, whatsapp_number (unik, normalisasi
62…), username (unik, lowercase), email (nullable), password (hash),
pin_hash (Argon2id/bcrypt), pin_attempts, pin_locked_until, points_balance
(cache), status, timestamps, deleted_at (soft delete)`.
- Password & PIN hanya disimpan sebagai hash. PIN tidak pernah ditampilkan ke admin.

## `transactions`
`id, transaction_number (unik), customer_id → customers, created_by → users,
transaction_date, gross_amount, transaction_discount, eligible_amount,
points_redeemed, redemption_discount, points_earned, final_amount,
status (completed|reversed), notes, external_receipt_no,
reversed_transaction_id, reversal_reason, reversed_by, reversed_at,
idempotency_key (unik), timestamps`.

## `point_ledger` (immutable)
`id, customer_id → customers, transaction_id → transactions (nullable),
type (EARN|REDEEM|REVERSAL|ADJUSTMENT_CREDIT|ADJUSTMENT_DEBIT|EXPIRED),
points_delta (signed), balance_after, description, created_by → users,
reversal_of_id → point_ledger, idempotency_key (unik), created_at`.
- Baris tidak boleh di-update/-delete (dijaga di model `PointLedger`). Koreksi = baris baru.

## `audit_logs`
`id, actor_type (admin|customer|system), actor_id, action, entity_type,
entity_id, before_data (json), after_data (json), reason, ip_address, created_at`.
- Tidak pernah menyimpan password/PIN/token.

## `birthday_followups`
`id, customer_id → customers, birthday_year, status (pending|contacted),
followed_up_by → users, followed_up_at, notes, timestamps`.
- Unik `(customer_id, birthday_year)` — satu tindak lanjut per customer per tahun.

## `settings`
`id, key (unik), value, type (string|integer|boolean), updated_by → users, timestamps`.

## Relasi utama
```
customers 1───* transactions *───1 users (created_by)
customers 1───* point_ledger  *───0..1 transactions
transactions 1───* point_ledger
users *───1 roles *───* permissions
customers 1───* birthday_followups
```

## Migrations
`database/migrations/` — jalankan `php artisan migrate`. Seeder aman (role,
permission, settings): `php artisan db:seed`. Lihat juga
[DECISIONS.md](DECISIONS.md).
