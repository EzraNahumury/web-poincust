<?php

namespace App\Http\Controllers\Member;

use App\Domain\Points\PointsConfig;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PointController extends Controller
{
    public function index(PointsConfig $points): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $entries = $customer->ledgerEntries()->with('transaction')->paginate(15);

        return view('member.points', [
            'entries' => $entries,
            'balance' => $customer->points_balance,
            'estimatedDiscount' => $points->calculator()->estimatedDiscount($customer->points_balance),
        ]);
    }
}
