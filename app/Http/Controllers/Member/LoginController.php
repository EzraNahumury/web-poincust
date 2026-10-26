<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function create(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('member.dashboard');
        }

        return view('member.auth.login');
    }

    public function forgot(): View
    {
        // Password recovery is admin-assisted and controlled (README §6).
        return view('member.auth.forgot');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = Customer::normalizeUsername((string) $request->input('username'));
        $throttleKey = 'member-login|' . $username . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credentials = ['username' => $username, 'password' => $request->input('password')];

        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        if (! $customer->isActive()) {
            Auth::guard('customer')->logout();
            throw ValidationException::withMessages([
                'username' => 'Akun ini tidak aktif. Silakan hubungi admin Avasport.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        AuditLogger::log('customer.login', [
            'actor_type' => 'customer',
            'actor_id' => $customer->id,
            'entity_type' => 'Customer',
            'entity_id' => $customer->id,
        ]);

        return redirect()->intended(route('member.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login')->with('success', 'Anda telah keluar.');
    }
}
