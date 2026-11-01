@extends('layouts.app')
@section('title', 'Change Password')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="color:var(--clr-grey-800);font-weight:500;">Change Password</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Change Password</h1>
    <p class="hmis-page-header__subtitle">You must set a new password before continuing.</p>
  </div>
</div>

<div style="max-width:480px;">
  <div class="hmis-alert hmis-alert--warning" style="margin-bottom:var(--space-5);">
    <i class="bi bi-shield-lock-fill"></i>
    <div>Your account requires a password change. Please choose a strong password.</div>
  </div>

  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title">Set New Password</h2>
    </div>
    <div class="hmis-card__body">
      @if($errors->any())
        <div class="hmis-alert hmis-alert--danger" style="margin-bottom:var(--space-4);">
          <i class="bi bi-exclamation-circle-fill"></i>
          <div>{{ $errors->first() }}</div>
        </div>
      @endif

      <form method="POST" action="{{ route('password.change') }}">
        @csrf
        @method('PUT')

        <div class="hmis-form-group">
          <label for="password" class="hmis-label">
            New Password <span class="required">*</span>
          </label>
          <input type="password" id="password" name="password"
                 class="hmis-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                 autocomplete="new-password" required>
          <span class="hmis-hint">
            Min. 10 characters, with uppercase, lowercase, number and symbol.
          </span>
        </div>

        <div class="hmis-form-group">
          <label for="password_confirmation" class="hmis-label">
            Confirm Password <span class="required">*</span>
          </label>
          <input type="password" id="password_confirmation" name="password_confirmation"
                 class="hmis-input" autocomplete="new-password" required>
        </div>

        <div style="display:flex;gap:var(--space-3);margin-top:var(--space-5);">
          <button type="submit" class="btn-hmis-primary">
            <i class="bi bi-shield-check"></i> Update Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection