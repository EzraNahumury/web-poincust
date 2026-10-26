<?php

namespace App\Domain\Auth;

/**
 * Central catalogue of admin permissions. Used by the seeder, the `permission`
 * middleware and Blade @can checks so the names never drift apart.
 */
class Permissions
{
    public const CUSTOMERS_VIEW = 'customers.view';
    public const CUSTOMERS_MANAGE = 'customers.manage';
    public const TRANSACTIONS_VIEW = 'transactions.view';
    public const TRANSACTIONS_CREATE = 'transactions.create';
    public const TRANSACTIONS_REVERSE = 'transactions.reverse';
    public const POINTS_ADJUST = 'points.adjust';
    public const BIRTHDAYS_VIEW = 'birthdays.view';
    public const REPORTS_VIEW = 'reports.view';
    public const AUDIT_VIEW = 'audit.view';
    public const SETTINGS_MANAGE = 'settings.manage';
    public const USERS_MANAGE = 'users.manage';

    /** @return array<string,string> permission name => human description (id) */
    public static function all(): array
    {
        return [
            self::CUSTOMERS_VIEW => 'Melihat data customer',
            self::CUSTOMERS_MANAGE => 'Mengubah data & status customer',
            self::TRANSACTIONS_VIEW => 'Melihat transaksi',
            self::TRANSACTIONS_CREATE => 'Mencatat transaksi',
            self::TRANSACTIONS_REVERSE => 'Membatalkan transaksi (reversal)',
            self::POINTS_ADJUST => 'Penyesuaian poin manual',
            self::BIRTHDAYS_VIEW => 'Melihat pengingat ulang tahun',
            self::REPORTS_VIEW => 'Melihat & mengekspor laporan',
            self::AUDIT_VIEW => 'Melihat audit log',
            self::SETTINGS_MANAGE => 'Mengelola pengaturan program poin',
            self::USERS_MANAGE => 'Mengelola admin & hak akses',
        ];
    }

    /** Default permissions granted to the ordinary admin/cashier role. */
    public static function adminDefaults(): array
    {
        return [
            self::CUSTOMERS_VIEW,
            self::TRANSACTIONS_VIEW,
            self::TRANSACTIONS_CREATE,
            self::BIRTHDAYS_VIEW,
            self::REPORTS_VIEW,
        ];
    }
}
