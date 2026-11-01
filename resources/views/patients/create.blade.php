@extends('layouts.app')
@section('title', 'Register Patient')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('patients.index') }}" style="color:var(--clr-green);">Patients</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Register Patient</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Register New Patient</h1>
    <p class="hmis-page-header__subtitle">{{ auth()->user()->branch->name }}</p>
  </div>
</div>

{{-- Duplicate warning --}}
@if(session('duplicates'))
  <div class="hmis-alert hmis-alert--warning" style="margin-bottom:var(--space-5);">
    <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;"></i>
    <div style="flex:1;">
      <strong>Possible duplicate patients found.</strong>
      Review below before continuing.
    </div>
  </div>

  <div class="hmis-card" style="margin-bottom:var(--space-5);">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title">Existing Records — Please Review</h2>
    </div>
    <div class="hmis-table-wrap" style="border:none;border-radius:0;box-shadow:none;">
      <table class="hmis-table">
        <thead>
          <tr><th>Name</th><th>DOB</th><th>Phone</th><th>National ID</th><th>MRN(s)</th><th></th></tr>
        </thead>
        <tbody>
          @foreach(session('duplicates') as $dup)
            <tr>
              <td><strong>{{ $dup->full_name }}</strong></td>
              <td>{{ $dup->date_of_birth?->format('d M Y') ?? '—' }}</td>
              <td>{{ $dup->phone_primary ?? '—' }}</td>
              <td>{{ $dup->national_id ?? '—' }}</td>
              <td>
                @foreach($dup->branchRecords as $br)
                  <span style="font-family:monospace;font-size:var(--font-size-xs);background:var(--clr-grey-100);padding:2px 6px;border-radius:4px;">{{ $br->mrn }}</span>
                @endforeach
              </td>
              <td>
                <a href="{{ route('patients.show', $dup) }}" target="_blank" class="btn-hmis-secondary" style="padding:4px 10px;font-size:var(--font-size-xs);">
                  <i class="bi bi-eye"></i> View
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

<form method="POST" action="{{ route('patients.store') }}" novalidate>
  @csrf

  @if(session('duplicates'))
    <input type="hidden" name="confirm_duplicate" value="1">
    <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--space-5);">
      <i class="bi bi-info-circle-fill"></i>
      <div>You are registering a <strong>new patient</strong> despite the possible matches above. This will be recorded.</div>
    </div>
  @endif

  {{-- Demographics --}}
  <div class="hmis-card" style="margin-bottom:var(--space-5);">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-person-fill text-green"></i> Demographics</h2>
    </div>
    <div class="hmis-card__body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);">
        <div class="hmis-form-group">
          <label for="first_name" class="hmis-label">First Name <span class="required">*</span></label>
          <input type="text" id="first_name" name="first_name"
                 class="hmis-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                 value="{{ old('first_name') }}" required>
          @error('first_name')<span class="hmis-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label for="middle_name" class="hmis-label">Middle Name</label>
          <input type="text" id="middle_name" name="middle_name"
                 class="hmis-input" value="{{ old('middle_name') }}">
        </div>

        <div class="hmis-form-group">
          <label for="last_name" class="hmis-label">Last Name <span class="required">*</span></label>
          <input type="text" id="last_name" name="last_name"
                 class="hmis-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                 value="{{ old('last_name') }}" required>
          @error('last_name')<span class="hmis-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</span>@enderror
        </div>

        <div class="hmis-form-group">
          <label for="date_of_birth" class="hmis-label">Date of Birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth"
                 class="hmis-input" value="{{ old('date_of_birth') }}">
        </div>

        <div class="hmis-form-group">
          <label for="gender" class="hmis-label">Gender</label>
          <select id="gender" name="gender" class="hmis-select">
            <option value="">— Select —</option>
            @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','unknown'=>'Unknown'] as $val=>$label)
              <option value="{{ $val }}" {{ old('gender') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="hmis-form-group">
          <label for="national_id" class="hmis-label">National ID / Passport</label>
          <input type="text" id="national_id" name="national_id"
                 class="hmis-input" value="{{ old('national_id') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- Contact --}}
  <div class="hmis-card" style="margin-bottom:var(--space-5);">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-telephone-fill text-green"></i> Contact Information</h2>
    </div>
    <div class="hmis-card__body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);">
        <div class="hmis-form-group">
          <label for="phone_primary" class="hmis-label">Primary Phone</label>
          <input type="tel" id="phone_primary" name="phone_primary"
                 class="hmis-input" value="{{ old('phone_primary') }}"
                 placeholder="07XX XXX XXX">
        </div>

        <div class="hmis-form-group">
          <label for="phone_secondary" class="hmis-label">Secondary Phone</label>
          <input type="tel" id="phone_secondary" name="phone_secondary"
                 class="hmis-input" value="{{ old('phone_secondary') }}">
        </div>

        <div class="hmis-form-group">
          <label for="email" class="hmis-label">Email</label>
          <input type="email" id="email" name="email"
                 class="hmis-input" value="{{ old('email') }}">
        </div>

        <div class="hmis-form-group">
          <label for="county" class="hmis-label">County</label>
          <input type="text" id="county" name="county"
                 class="hmis-input" value="{{ old('county') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- Next of Kin --}}
  <div class="hmis-card" style="margin-bottom:var(--space-5);">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-people-fill text-green"></i> Next of Kin</h2>
    </div>
    <div class="hmis-card__body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);">
        <div class="hmis-form-group">
          <label for="next_of_kin_name" class="hmis-label">Name</label>
          <input type="text" id="next_of_kin_name" name="next_of_kin_name"
                 class="hmis-input" value="{{ old('next_of_kin_name') }}">
        </div>

        <div class="hmis-form-group">
          <label for="next_of_kin_phone" class="hmis-label">Phone</label>
          <input type="tel" id="next_of_kin_phone" name="next_of_kin_phone"
                 class="hmis-input" value="{{ old('next_of_kin_phone') }}">
        </div>

        <div class="hmis-form-group">
          <label for="next_of_kin_relation" class="hmis-label">Relationship</label>
          <input type="text" id="next_of_kin_relation" name="next_of_kin_relation"
                 class="hmis-input" value="{{ old('next_of_kin_relation') }}"
                 placeholder="e.g. Spouse, Parent, Sibling">
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:var(--space-3);">
    <button type="submit" class="btn-hmis-primary">
      <i class="bi bi-person-check-fill"></i>
      {{ session('duplicates') ? 'Confirm & Register New Patient' : 'Register Patient' }}
    </button>
    <a href="{{ route('patients.index') }}" class="btn-hmis-secondary">
      <i class="bi bi-x"></i> Cancel
    </a>
  </div>
</form>
@endsection