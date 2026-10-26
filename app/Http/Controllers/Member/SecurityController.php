<?php

namespace App\Http\Controllers\Member;

use App\Domain\Auth\PinHasher;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function edit(): View
    {
        return view('member.security', [
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
        ]);

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $customer->password = $request->input('password'); // hashed by cast
        $customer->save();

        AuditLogger::log('customer.password_change', [
            'actor_type' => 'customer',
            'actor_id' => $customer->id,
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
        ]);

        return redirect()->route('member.security')->with('success', 'Password berhasil diubah.');
    }

    public function updatePin(Request $request, PinHasher $pinHasher): RedirectResponse
    {
        $request->validate([
            'current_pin' => ['required', 'digits:6'],
            'pin' => ['required', 'digits:6', 'confirmed'],
        ], [
            'pin.confirmed' => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        if ($customer->isPinLocked()) {
            $minutes = now()->diffInMinutes($customer->pin_locked_until) + 1;
            throw ValidationException::withMessages([
                'current_pin' => "PIN terkunci sementara. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        if (! $pinHasher->check($request->input('current_pin'), $customer->pin_hash)) {
            $this->registerFailedPinAttempt($customer);
            throw ValidationException::withMessages([
                'current_pin' => 'PIN saat ini salah.',
            ]);
        }

        // Success: reset counters and store the new PIN.
        $customer->pin_hash = $pinHasher->make($request->input('pin'));
        $customer->pin_attempts = 0;
        $customer->pin_locked_until = null;
        $customer->save();

        AuditLogger::log('customer.pin_change', [
            'actor_type' => 'customer',
            'actor_id' => $customer->id,
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
        ]);

        return redirect()->route('member.security')->with('success', 'PIN berhasil diubah.');
    }

    private function registerFailedPinAttempt(Customer $customer): void
    {
        $max = (int) config('pin.max_attempts', 5);
        $lockMinutes = (int) config('pin.lock_minutes', 15);

        $customer->pin_attempts = $customer->pin_attempts + 1;

        if ($customer->pin_attempts >= $max) {
            $customer->pin_locked_until = now()->addMinutes($lockMinutes);
            $customer->pin_attempts = 0;
        }

        $customer->save();
    }
}
