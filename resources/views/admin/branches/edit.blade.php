@extends('layouts.app')
@section('title', 'Edit Branch')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li><a href="{{ route('admin.branches.index') }}" style="color:var(--clr-green);">Branches</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>{{ $branch->name }}</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Edit Branch — {{ $branch->name }}</h1>
</div>

<div style="max-width:600px;">
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-building text-green"></i> Branch Details</h2>
      <span class="hmis-badge hmis-badge--grey font-mono">{{ $branch->code }}</span>
    </div>
    <div class="hmis-card__body">
      <form method="POST" action="{{ route('admin.branches.update', $branch) }}" novalidate>
        @csrf @method('PUT')

        <div class="hmis-form-group">
          <label class="hmis-label">Branch Name <span class="required">*</span></label>
          <input type="text" name="name" class="hmis-input" value="{{ old('name', $branch->name) }}" required>
          @error('name')<span class="hmis-error">{{ $message }}</span>@enderror
        </div>

        <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--sp-4);">
          <i class="bi bi-info-circle-fill"></i>
          <div>Branch code and type cannot be changed after creation as they affect existing records.</div>
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Address</label>
          <textarea name="address" class="hmis-input" rows="2">{{ old('address', $branch->address) }}</textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4);">
          <div class="hmis-form-group">
            <label class="hmis-label">Phone</label>
            <input type="tel" name="phone" class="hmis-input" value="{{ old('phone', $branch->phone) }}">
          </div>
          <div class="hmis-form-group">
            <label class="hmis-label">Email</label>
            <input type="email" name="email" class="hmis-input" value="{{ old('email', $branch->email) }}">
          </div>
        </div>

        <div class="hmis-form-group">
          <label style="display:flex;align-items:center;gap:var(--sp-2);cursor:pointer;">
            <input type="checkbox" name="is_active" value="1" style="accent-color:var(--clr-green);" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
            <span class="hmis-label" style="margin:0;">Branch is active</span>
          </label>
        </div>

        <div style="display:flex;gap:var(--sp-3);">
          <button type="submit" class="btn-hmis-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
          <a href="{{ route('admin.branches.index') }}" class="btn-hmis-secondary"><i class="bi bi-x"></i> Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection