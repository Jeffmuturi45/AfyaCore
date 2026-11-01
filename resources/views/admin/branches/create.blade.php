@extends('layouts.app')
@section('title', 'Create Branch')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li><a href="{{ route('admin.branches.index') }}" style="color:var(--clr-green);">Branches</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>Create Branch</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Create New Branch</h1>
</div>

<div style="max-width:600px;">
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-building-add text-green"></i> Branch Details</h2>
    </div>
    <div class="hmis-card__body">
      @if($errors->any())
        <div class="hmis-alert hmis-alert--danger" style="margin-bottom:var(--sp-4);">
          <i class="bi bi-exclamation-circle-fill"></i>
          <div>{{ $errors->first() }}</div>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.branches.store') }}" novalidate>
        @csrf

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--sp-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">Branch Name <span class="required">*</span></label>
            <input type="text" name="name" class="hmis-input {{ $errors->has('name')?'is-invalid':'' }}"
                   value="{{ old('name') }}" placeholder="e.g. Prime Opticals" required>
            @error('name')<span class="hmis-error">{{ $message }}</span>@enderror
          </div>

          <div class="hmis-form-group">
            <label class="hmis-label">Branch Code <span class="required">*</span></label>
            <input type="text" name="code" class="hmis-input {{ $errors->has('code')?'is-invalid':'' }}"
                   value="{{ old('code') }}" placeholder="NYR" maxlength="10"
                   style="text-transform:uppercase;"
                   oninput="this.value=this.value.toUpperCase()" required>
            <span class="hmis-hint">Uppercase letters/numbers only. Used in MRN prefix.</span>
            @error('code')<span class="hmis-error">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Branch Type <span class="required">*</span></label>
          <select name="branch_type" class="hmis-select {{ $errors->has('branch_type')?'is-invalid':'' }}" required>
            <option value="">— Select type —</option>
            <option value="opd" {{ old('branch_type')=='opd'?'selected':'' }}>OPD (General Outpatient)</option>
            <option value="eye_clinic" {{ old('branch_type')=='eye_clinic'?'selected':'' }}>Eye Clinic</option>
            <option value="both" {{ old('branch_type')=='both'?'selected':'' }}>Both OPD + Eye Clinic</option>
          </select>
          <span class="hmis-hint">This determines which modules are activated for this branch.</span>
          @error('branch_type')<span class="hmis-error">{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Address</label>
          <textarea name="address" class="hmis-input" rows="2" placeholder="Physical address or location">{{ old('address') }}</textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">Phone</label>
            <input type="tel" name="phone" class="hmis-input" value="{{ old('phone') }}" placeholder="07XX XXX XXX">
          </div>
          <div class="hmis-form-group">
            <label class="hmis-label">Email</label>
            <input type="email" name="email" class="hmis-input" value="{{ old('email') }}">
          </div>
        </div>

        <div style="display:flex;gap:var(--sp-3);margin-top:var(--sp-4);">
          <button type="submit" class="btn-hmis-primary">
            <i class="bi bi-building-add"></i> Create Branch
          </button>
          <a href="{{ route('admin.branches.index') }}" class="btn-hmis-secondary">
            <i class="bi bi-x"></i> Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection