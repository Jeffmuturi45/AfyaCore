@extends('layouts.app')
@section('title', 'Edit User')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('admin.users.index') }}" style="color:var(--clr-green);">Users</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">{{ $user->name }}</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Edit User — {{ $user->name }}</h1>
</div>

<div style="max-width:600px;display:flex;flex-direction:column;gap:var(--space-5);">

  {{-- Edit details --}}
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title">User Details</h2>
    </div>
    <div class="hmis-card__body">
      <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
        @csrf @method('PUT')

        <div class="hmis-form-group">
          <label class="hmis-label">Full Name <span class="required">*</span></label>
          <input type="text" name="name" class="hmis-input" value="{{ old('name', $user->name) }}" required>
          @error('name')<span class="hmis-error">{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Email <span class="required">*</span></label>
          <input type="email" name="email" class="hmis-input" value="{{ old('email', $user->email) }}" required>
          @error('email')<span class="hmis-error">{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Phone</label>
          <input type="tel" name="phone" class="hmis-input" value="{{ old('phone', $user->phone) }}">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">Branch <span class="required">*</span></label>
            <select name="branch_id" class="hmis-select" required>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id)==$branch->id?'selected':'' }}>
                  {{ $branch->name }} ({{ $branch->code }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label">Role <span class="required">*</span></label>
            <select name="role" class="hmis-select" required>
              @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ old('role', $user->role)==$value?'selected':'' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="hmis-form-group">
          <label style="display:flex;align-items:center;gap:var(--space-2);cursor:pointer;font-size:var(--font-size-sm);">
            <input type="checkbox" name="is_active" value="1" style="accent-color:var(--clr-green);"
                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
            Account is active
          </label>
        </div>

        <div style="display:flex;gap:var(--space-3);">
          <button type="submit" class="btn-hmis-primary">
            <i class="bi bi-check-lg"></i> Save Changes
          </button>
          <a href="{{ route('admin.users.index') }}" class="btn-hmis-secondary">
            <i class="bi bi-x"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Reset password --}}
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-key text-green"></i> Reset Password</h2>
    </div>
    <div class="hmis-card__body">
      <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" novalidate>
        @csrf @method('PATCH')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">New Password <span class="required">*</span></label>
            <input type="password" name="password" class="hmis-input" required>
            <span class="hmis-hint">Min 10 chars, mixed case, number and symbol.</span>
          </div>
          <div class="hmis-form-group">
            <label class="hmis-label">Confirm Password <span class="required">*</span></label>
            <input type="password" name="password_confirmation" class="hmis-input" required>
          </div>
        </div>

        <button type="submit" class="btn-hmis-danger"
                onclick="return confirm('Reset password for {{ $user->name }}?')">
          <i class="bi bi-key-fill"></i> Reset Password
        </button>
      </form>
    </div>
  </div>
</div>
@endsection