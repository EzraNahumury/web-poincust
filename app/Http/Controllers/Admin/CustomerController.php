<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Membership\CustomerRegistrar;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerStoreRequest;
use App\Http\Requests\Admin\CustomerUpdateRequest;
use App\Models\Customer;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('member_number', 'like', "%{$search}%")
                        ->orWhere('whatsapp_number', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), fn ($q) => $q->where('status', $status))
            ->withCount('transactions')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(CustomerStoreRequest $request, CustomerRegistrar $registrar): RedirectResponse
    {
        $data = $request->validated();

        $customer = $registrar->register([
            'name' => $data['name'],
            'birth_date' => $data['birth_date'],
            'whatsapp_number' => $data['whatsapp_number'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'pin' => $data['pin'],
            'status' => $data['status'],
        ], 'admin', Auth::guard('web')->id());

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', "Customer {$customer->name} ({$customer->member_number}) berhasil dibuat.");
    }

    public function show(Customer $customer): View
    {
        $customer->loadCount('transactions');

        return view('admin.customers.show', [
            'customer' => $customer,
            'transactions' => $customer->transactions()->latest('transaction_date')->take(10)->get(),
            'ledger' => $customer->ledgerEntries()->with('transaction')->take(15)->get(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(CustomerUpdateRequest $request, Customer $customer): RedirectResponse
    {
        $before = $customer->only(['name', 'email', 'whatsapp_number', 'username', 'birth_date', 'status']);

        $customer->fill($request->validated())->save();

        AuditLogger::log('customer.update', [
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
            'before' => $before,
            'after' => $customer->only(['name', 'email', 'whatsapp_number', 'username', 'birth_date', 'status']),
        ]);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Data customer diperbarui.');
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $customer->status = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->save();

        AuditLogger::log('customer.status_change', [
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
            'after' => ['status' => $customer->status],
        ]);

        return back()->with('success', "Status customer diubah menjadi {$customer->status}.");
    }
}
