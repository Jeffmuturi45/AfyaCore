@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="hmis-content">
    <div class="hmis-page-header">
        <div>
            <h1 class="hmis-page-header__title">User Management</h1>
            <p class="hmis-page-header__subtitle">Manage system users, roles, and branch assignments</p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn-hmis-primary">
                <i class="bi bi-person-plus"></i> Add New User
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="hmis-alert hmis-alert--success" style="margin-bottom: var(--sp-4);">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="hmis-card">
        <div class="hmis-table-wrap">
            <table class="hmis-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Branch</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                {{-- SAFE NULL CHECK FIX --}}
                                @if ($user->branch)
                                    <span class="hmis-badge hmis-badge--blue">{{ $user->branch->code }}</span>
                                    <span style="font-size: var(--fs-xs); color: var(--clr-grey-600); margin-left: 4px;">
                                        {{ $user->branch->name }}
                                    </span>
                                @else
                                    <span class="hmis-badge hmis-badge--grey">No branch assigned</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $roleBadges = [
                                        'super_admin'  => 'red',
                                        'branch_admin' => 'purple',
                                        'receptionist' => 'blue',
                                        'nurse'        => 'green',
                                        'optometrist'  => 'amber',
                                    ];
                                    $badgeColor = $roleBadges[$user->role] ?? 'grey';
                                @endphp
                                <span class="hmis-badge hmis-badge--{{ $badgeColor }}">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="hmis-badge hmis-badge--green hmis-badge--dot">Active</span>
                                @else
                                    <span class="hmis-badge hmis-badge--red hmis-badge--dot">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-hmis-ghost" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-hmis-ghost" style="color: var(--clr-danger);" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="hmis-empty">
                                    <i class="bi bi-people"></i>
                                    <div class="hmis-empty__title">No users found</div>
                                    <p>Get started by adding your first system user.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div style="padding: var(--sp-4) var(--sp-5); border-top: 1px solid var(--border);">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection