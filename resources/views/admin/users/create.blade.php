@extends('layouts.app')
@section('title', 'Create User')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('admin.users.index') }}" style="color:var(--clr-green);">Users</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Create User</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Create New User</h1>
</div>

<div style="max-width:600px;">
  <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--space-5);">
    <i class="bi bi-info-circle-fill"></i>
    <div>The user will be required to change their password on first login.</div>
  </div>

  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title">User Details</h2>
    </div>
    <div class="hmis-card__body">
      @if($errors->any())
        <div class="hmis-alert hmis-alert--danger" style="margin-bottom:var(--space-4);">
          <i class="bi bi-exclamation-circle-fill"></i>
          <div>{{ $errors->first() }}</div>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
        @csrf

        <div class="hmis-form-group">
          <label class="hmis-label">Full Name <span class="required">*</span></label>
          <input type="text" name="name" class="hmis-input {{ $errors->has('name')?'is-invalid':'' }}"
                 value="{{ old('name') }}" required>
          @error('name')<span class="hmis-error">{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Email Address <span class="required">*</span></label>
          <input type="email" name="email" class="hmis-input {{ $errors->has('email')?'is-invalid':'' }}"
                 value="{{ old('email') }}" required>
          @error('email')<span class="hmis-error">{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Phone</label>
          <input type="tel" name="phone" class="hmis-input" value="{{ old('phone') }}">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">Branch <span class="required">*</span></label>
            <select name="branch_id" class="hmis-select {{ $errors->has('branch_id')?'is-invalid':'' }}" required>
              <option value="">— Select Branch —</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id')==$branch->id?'selected':'' }}>
                  {{ $branch->name }} ({{ $branch->code }})
                </option>
              @endforeach
            </select>
            @error('branch_id')<span class="hmis-error">{{ $message }}</span>@enderror
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label">Role <span class="required">*</span></label>
            <select name="role" class="hmis-select {{ $errors->has('role')?'is-invalid':'' }}" required>
              <option value="">— Select Role —</option>
              @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ old('role')==$value?'selected':'' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('role')<span class="hmis-error">{{ $message }}</span>@enderror
          </div>
        </div>

        <hr class="divider">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">Password <span class="required">*</span></label>
            <input type="password" name="password"
                   class="hmis-input {{ $errors->has('password')?'is-invalid':'' }}" required>
            <span class="hmis-hint">Min 10 chars, mixed case, number and symbol.</span>
            @error('password')<span class="hmis-error">{{ $message }}</span>@enderror
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label">Confirm Password <span class="required">*</span></label>
            <input type="password" name="password_confirmation" class="hmis-input" required>
          </div>
        </div>

        <div style="display:flex;gap:var(--space-3);margin-top:var(--space-4);">
          <button type="submit" class="btn-hmis-primary">
            <i class="bi bi-person-check-fill"></i> Create User
          </button>
          <a href="{{ route('admin.users.index') }}" class="btn-hmis-secondary">
            <i class="bi bi-x"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection