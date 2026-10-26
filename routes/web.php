<?php

use App\Domain\Auth\Permissions;
use App\Http\Controllers\Admin;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Member;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

/*
|--------------------------------------------------------------------------
| Customer membership portal  (/member, guard: customer)
|--------------------------------------------------------------------------
*/
Route::prefix('member')->name('member.')->group(function () {
    // Guest
    Route::get('register', [Member\RegisterController::class, 'create'])->name('register');
    Route::post('register', [Member\RegisterController::class, 'store'])->name('register.store');
    Route::get('login', [Member\LoginController::class, 'create'])->name('login');
    Route::post('login', [Member\LoginController::class, 'store'])->name('login.store');
    Route::get('forgot-password', [Member\LoginController::class, 'forgot'])->name('forgot');

    // Authenticated member
    Route::middleware('auth:customer')->group(function () {
        Route::post('logout', [Member\LoginController::class, 'destroy'])->name('logout');
        Route::get('dashboard', [Member\DashboardController::class, 'index'])->name('dashboard');
        Route::get('transactions', [Member\TransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/{transaction}', [Member\TransactionController::class, 'show'])->name('transactions.show');
        Route::get('points', [Member\PointController::class, 'index'])->name('points');
        Route::get('profile', [Member\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [Member\ProfileController::class, 'update'])->name('profile.update');
        Route::get('security', [Member\SecurityController::class, 'edit'])->name('security');
        Route::put('security/password', [Member\SecurityController::class, 'updatePassword'])->name('security.password');
        Route::put('security/pin', [Member\SecurityController::class, 'updatePin'])->name('security.pin');
    });
});

/*
|--------------------------------------------------------------------------
| Admin panel  (/admin, guard: web)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest
    Route::get('login', [Admin\LoginController::class, 'create'])->name('login');
    Route::post('login', [Admin\LoginController::class, 'store'])->name('login.store');

    // Authenticated admin
    Route::middleware('auth:web')->group(function () {
        Route::post('logout', [Admin\LoginController::class, 'destroy'])->name('logout');
        Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Customers
        Route::middleware('permission:' . Permissions::CUSTOMERS_VIEW)->group(function () {
            Route::get('customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{customer}', [Admin\CustomerController::class, 'show'])->name('customers.show');
        });
        Route::middleware('permission:' . Permissions::CUSTOMERS_MANAGE)->group(function () {
            Route::get('customers-create/new', [Admin\CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [Admin\CustomerController::class, 'store'])->name('customers.store');
            Route::get('customers/{customer}/edit', [Admin\CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [Admin\CustomerController::class, 'update'])->name('customers.update');
            Route::patch('customers/{customer}/status', [Admin\CustomerController::class, 'toggleStatus'])->name('customers.status');
        });

        // Point adjustments
        Route::post('customers/{customer}/adjust', [Admin\PointAdjustmentController::class, 'store'])
            ->middleware('permission:' . Permissions::POINTS_ADJUST)->name('points.adjust');

        // Transactions
        Route::middleware('permission:' . Permissions::TRANSACTIONS_VIEW)->group(function () {
            Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/{transaction}', [Admin\TransactionController::class, 'show'])->name('transactions.show');
        });
        Route::middleware('permission:' . Permissions::TRANSACTIONS_CREATE)->group(function () {
            Route::get('transactions-create/new', [Admin\TransactionController::class, 'create'])->name('transactions.create');
            Route::post('transactions', [Admin\TransactionController::class, 'store'])->name('transactions.store');
            Route::get('api/customers/search', [Admin\TransactionController::class, 'searchCustomers'])->name('customers.search');
            Route::post('api/transactions/preview', [Admin\TransactionController::class, 'preview'])->name('transactions.preview');
        });
        Route::post('transactions/{transaction}/reverse', [Admin\TransactionController::class, 'reverse'])
            ->middleware('permission:' . Permissions::TRANSACTIONS_REVERSE)->name('transactions.reverse');

        // Birthdays
        Route::middleware('permission:' . Permissions::BIRTHDAYS_VIEW)->group(function () {
            Route::get('birthdays', [Admin\BirthdayController::class, 'index'])->name('birthdays.index');
            Route::post('birthdays/{customer}/followup', [Admin\BirthdayController::class, 'markContacted'])->name('birthdays.followup');
        });

        // Reports + export
        Route::middleware('permission:' . Permissions::REPORTS_VIEW)->group(function () {
            Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export/{type}', [Admin\ReportController::class, 'export'])->name('reports.export');
        });

        // Audit log
        Route::get('audit-logs', [Admin\AuditLogController::class, 'index'])
            ->middleware('permission:' . Permissions::AUDIT_VIEW)->name('audit.index');

        // Settings
        Route::middleware('permission:' . Permissions::SETTINGS_MANAGE)->group(function () {
            Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        });

        // Admin user management
        Route::middleware('permission:' . Permissions::USERS_MANAGE)->group(function () {
            Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
            Route::get('users-create/new', [Admin\UserController::class, 'create'])->name('users.create');
            Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
            Route::patch('users/{user}/status', [Admin\UserController::class, 'toggleStatus'])->name('users.status');
        });
    });
});
