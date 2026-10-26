<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Points\AdjustmentService;
use App\Domain\Points\PointsException;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointAdjustmentController extends Controller
{
    public function store(Request $request, Customer $customer, AdjustmentService $adjustments): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:credit,debit'],
            'points' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            if ($validated['type'] === 'credit') {
                $adjustments->credit($customer, $validated['points'], $validated['reason'], Auth::guard('web')->user());
            } else {
                $adjustments->debit($customer, $validated['points'], $validated['reason'], Auth::guard('web')->user());
            }
        } catch (PointsException $e) {
            return back()->withErrors(['points' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Penyesuaian poin berhasil dicatat di ledger.');
    }
}
