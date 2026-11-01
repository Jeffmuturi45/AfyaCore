<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = 'login.'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            AuditService::log('auth.rate_limited', null, [], [], 'failure');
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);
            if ($user) {
                $user->increment('failed_login_count');
                if ($user->failed_login_count >= config('hmis.security.max_failed_logins')) {
                    $user->update(['locked_until' => now()->addMinutes(config('hmis.security.lockout_minutes'))]);
                }
            }
            AuditService::log('auth.failed', null, [], ['email' => $request->email], 'failure');
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => 'Your account has been deactivated. Contact your administrator.']);
        }

        if ($user->isLockedOut()) {
            throw ValidationException::withMessages(['email' => 'Account is temporarily locked due to too many failed attempts.']);
        }

        RateLimiter::clear($key);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update([
            'failed_login_count' => 0,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        AuditService::log('auth.login', $user);

        if ($user->force_password_change) {
            return redirect()->route('password.change')
                ->with('toast', ['type' => 'warning', 'title' => 'Password change required', 'message' => 'Please set a new password before continuing.']);
        }

        return redirect()->intended(route('dashboard'))
            ->with('toast', ['type' => 'success', 'title' => 'Welcome back', 'message' => "Signed in as {$user->name}"]);
    }

    public function logout(Request $request)
    {
        AuditService::log('auth.logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('toast', ['type' => 'info', 'title' => 'Signed out', 'message' => 'You have been signed out securely.']);
    }
}
