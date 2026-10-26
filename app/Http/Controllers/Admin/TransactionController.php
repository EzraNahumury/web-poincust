<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Points\PointsConfig;
use App\Domain\Points\PointsException;
use App\Domain\Points\ReversalService;
use App\Domain\Points\TransactionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransactionStoreRequest;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $transactions = Transaction::query()
            ->with('customer')
            ->when($search !== '', fn ($q) => $q->whereHas('customer', function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })->orWhere('transaction_number', 'like', "%{$search}%"))
            ->when(in_array($status, ['completed', 'reversed'], true), fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('transaction_date', '<=', $to))
            ->latest('transaction_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'search', 'status', 'from', 'to'));
    }

    public function create(Request $request): View
    {
        // Preselect a customer when arriving from a customer page.
        $preselect = $request->filled('customer')
            ? Customer::active()->find($request->input('customer'))
            : null;

        return view('admin.transactions.create', [
            'preselect' => $preselect,
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(TransactionStoreRequest $request, TransactionService $service): RedirectResponse
    {
        try {
            $txn = $service->record([
                'customer_id' => $request->integer('customer_id'),
                'gross_amount' => $request->integer('gross_amount'),
                'transaction_discount' => $request->integer('transaction_discount'),
                'points_redeemed' => $request->integer('points_redeemed'),
                'created_by' => Auth::guard('web')->id(),
                'transaction_date' => $request->input('transaction_date'),
                'notes' => $request->input('notes'),
                'external_receipt_no' => $request->input('external_receipt_no'),
                'idempotency_key' => $request->input('idempotency_key'),
            ]);
        } catch (PointsException $e) {
            return back()->withErrors(['points_redeemed' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.transactions.show', $txn)
            ->with('success', "Transaksi {$txn->transaction_number} berhasil dicatat.");
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['customer', 'creator', 'reversedBy', 'ledgerEntries' => fn ($q) => $q->orderBy('id')]);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function reverse(Request $request, Transaction $transaction, ReversalService $reversals): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $reversals->reverse($transaction, Auth::guard('web')->user(), $validated['reason']);
        } catch (PointsException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibatalkan (reversal tercatat di ledger).');
    }

    /** AJAX: customer autocomplete for the transaction form. */
    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        $customers = Customer::active()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('member_number', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('whatsapp_number', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'member_number', 'name', 'username', 'points_balance']);

        return response()->json($customers->map(fn (Customer $c) => [
            'id' => $c->id,
            'member_number' => $c->member_number,
            'name' => $c->name,
            'username' => $c->username,
            'points_balance' => $c->points_balance,
        ]));
    }

    /** AJAX: non-authoritative preview of points for the cashier UI. */
    public function preview(Request $request, PointsConfig $points): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'gross_amount' => ['nullable', 'integer', 'min:0'],
            'transaction_discount' => ['nullable', 'integer', 'min:0'],
            'points_redeemed' => ['nullable', 'integer', 'min:0'],
        ]);

        $calc = $points->calculator();
        $gross = (int) ($data['gross_amount'] ?? 0);
        $discount = min((int) ($data['transaction_discount'] ?? 0), $gross);
        $eligible = max(0, $gross - $discount);
        $redeemed = (int) ($data['points_redeemed'] ?? 0);

        $balance = isset($data['customer_id'])
            ? (int) Customer::whereKey($data['customer_id'])->value('points_balance')
            : 0;

        $redeemed = min($redeemed, $balance); // UI clamp only; backend re-validates
        $redemptionDiscount = min($calc->redemptionValue($redeemed), $eligible);
        $earned = $calc->pointsEarned($eligible);
        $final = $eligible - $redemptionDiscount;

        return response()->json([
            'eligible_amount' => $eligible,
            'points_earned' => $earned,
            'redemption_discount' => $redemptionDiscount,
            'final_amount' => $final,
            'current_balance' => $balance,
            'balance_after' => $balance - $redeemed + $earned,
        ]);
    }
}
