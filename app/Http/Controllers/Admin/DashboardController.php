<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Birthday\BirthdayService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\PointLedger;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(BirthdayService $birthdays): View
    {
        $todayStart = Carbon::now('Asia/Jakarta')->startOfDay();
        $todayEnd = Carbon::now('Asia/Jakarta')->endOfDay();

        $todayTransactions = Transaction::completed()
            ->whereBetween('transaction_date', [$todayStart, $todayEnd]);

        // Point movements today, from the ledger (source of truth).
        $ledgerToday = PointLedger::whereBetween('created_at', [$todayStart, $todayEnd]);

        return view('admin.dashboard', [
            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::active()->count(),
            'todayCount' => (clone $todayTransactions)->count(),
            'todayAmount' => (int) (clone $todayTransactions)->sum('final_amount'),
            'pointsEarnedToday' => (int) (clone $ledgerToday)->where('points_delta', '>', 0)
                ->whereIn('type', [PointLedger::TYPE_EARN, PointLedger::TYPE_ADJUSTMENT_CREDIT])->sum('points_delta'),
            'pointsRedeemedToday' => (int) abs((clone $ledgerToday)
                ->where('type', PointLedger::TYPE_REDEEM)->sum('points_delta')),
            'birthdaysToday' => $birthdays->today(),
            'birthdaysUpcoming' => $birthdays->upcoming(7),
            'recentTransactions' => Transaction::with('customer')->latest('transaction_date')->take(8)->get(),
            'recentActivity' => AuditLog::latest('created_at')->take(10)->get(),
        ]);
    }
}
