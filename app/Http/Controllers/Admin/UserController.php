<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Canonical list of assignable roles.
     * Shared by create/edit views and validation rules.
     */
    private const ROLES = [
        'super_admin'  => 'Super Admin',
        'branch_admin' => 'Branch Admin',
        'receptionist' => 'Receptionist',
        'nurse'        => 'Nurse',
        'optometrist'  => 'Optometrist',
    ];

    public function index()
    {
        $users = User::with('branch')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $roles    = self::ROLES;

        return view('admin.users.create', compact('branches', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['boolean'],
        ]);

        if ($validated['role'] !== 'super_admin' && empty($validated['branch_id'])) {
            return back()
                ->withInput()
                ->withErrors(['branch_id' => 'A branch is required for non-super-admin users.']);
        }

        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $roles    = self::ROLES;

        return view('admin.users.edit', compact('user', 'branches', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(array_keys(self::ROLES))],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['boolean'],
        ]);

        if ($validated['role'] !== 'super_admin' && empty($validated['branch_id'])) {
            return back()
                ->withInput()
                ->withErrors(['branch_id' => 'A branch is required for non-super-admin users.']);
        }

        if ($user->id === auth()->id()
            && $user->role === 'super_admin'
            && $validated['role'] !== 'super_admin') {
            return back()
                ->withInput()
                ->withErrors(['role' => 'You cannot demote your own super admin account.']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === 'super_admin'
            && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last remaining super admin.');
        }

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password reset successfully. Please share the new password securely.');
    }
}