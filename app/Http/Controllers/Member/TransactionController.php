<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $transactions = $customer->transactions()
            ->latest('transaction_date')
            ->paginate(10);

        return view('member.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        // IDOR protection: members may only view their own transactions.
        abort_unless($transaction->customer_id === $customer->id, 404);

        $transaction->load(['ledgerEntries' => fn ($q) => $q->orderBy('id')]);

        return view('member.transactions.show', compact('transaction'));
    }
}
