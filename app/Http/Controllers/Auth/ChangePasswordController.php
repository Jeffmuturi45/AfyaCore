<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required', 'confirmed',
                Password::min(10)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = auth()->user();

        // Prevent reuse of current password
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'New password must be different from your current password.'
            ]);
        }

        $user->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        AuditService::log('auth.password_changed', $user);

        return redirect()->route('dashboard')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Password updated',
                'message' => 'Your password has been changed successfully.',
            ]);
    }
}