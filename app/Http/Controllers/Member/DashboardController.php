<?php

namespace App\Http\Controllers\Member;

use App\Domain\Points\PointsConfig;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PointsConfig $points): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $calc = $points->calculator();

        return view('member.dashboard', [
            'customer' => $customer,
            'balance' => $customer->points_balance,
            'estimatedDiscount' => $calc->estimatedDiscount($customer->points_balance),
            'redeemValue' => $calc->redeemValuePerPoint(),
            'transactionCount' => $customer->transactions()->completed()->count(),
            'recentTransactions' => $customer->transactions()->latest('transaction_date')->take(5)->get(),
            'recentLedger' => $customer->ledgerEntries()->with('transaction')->take(6)->get(),
            'lastTransaction' => $customer->transactions()->latest('transaction_date')->first(),
        ]);
    }
}
