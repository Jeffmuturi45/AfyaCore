@extends('layouts.app')
@section('title', 'New Encounter')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('encounters.index') }}" style="color:var(--clr-green);">Encounters</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">New Encounter</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">New Encounter</h1>
</div>

<div style="max-width:640px;">
  <form method="POST" action="{{ route('encounters.store') }}" novalidate>
    @csrf

    <div class="hmis-card" style="margin-bottom:var(--space-5);">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Patient</h2>
      </div>
      <div class="hmis-card__body">
        @if($patient)
          {{-- Pre-selected patient --}}
          <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--space-4);">
            <i class="bi bi-person-check-fill"></i>
            <div>
              <strong>{{ $patient->full_name }}</strong>
              &mdash; {{ $patient->mrn(auth()->user()->branch_id) }}
            </div>
          </div>
          <input type="hidden" name="patient_id" value="{{ $patient->id }}">
        @else
          <div class="hmis-form-group">
            <label for="patient_id" class="hmis-label">Select Patient <span class="required">*</span></label>
            <select id="patient_id" name="patient_id" class="hmis-select" required>
              <option value="">— Search and select patient —</option>
              @foreach($patients as $p)
                <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                  {{ $p->full_name }} — {{ $p->mrn(auth()->user()->branch_id) ?? $p->phone_primary }}
                </option>
              @endforeach
            </select>
            @error('patient_id')<span class="hmis-error">{{ $message }}</span>@enderror
            <span class="hmis-hint">Patient must be registered at this branch first.</span>
          </div>
        @endif
      </div>
    </div>

    <div class="hmis-card" style="margin-bottom:var(--space-5);">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Visit Details</h2>
      </div>
      <div class="hmis-card__body">
        <div class="hmis-form-group">
          <label for="chief_complaint" class="hmis-label">Chief Complaint / Reason for Visit</label>
          <textarea id="chief_complaint" name="chief_complaint"
                    class="hmis-input" rows="3"
                    placeholder="Brief description of why the patient is visiting...">{{ old('chief_complaint') }}</textarea>
        </div>

        <div class="hmis-form-group" x-data="{ isReferral: {{ old('is_referral_in') ? 'true' : 'false' }} }">
          <label style="display:flex;align-items:center;gap:var(--space-2);cursor:pointer;font-size:var(--font-size-sm);">
            <input type="checkbox" name="is_referral_in" value="1"
                   x-model="isReferral"
                   style="accent-color:var(--clr-green);">
            This is a referral from another facility
          </label>
          <div x-show="isReferral" x-transition style="margin-top:var(--space-3);">
            <input type="text" name="referral_source"
                   class="hmis-input" placeholder="Referring facility name"
                   value="{{ old('referral_source') }}">
          </div>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:var(--space-3);">
      <button type="submit" class="btn-hmis-primary">
        <i class="bi bi-clipboard2-check"></i> Create Encounter
      </button>
      <a href="{{ route('encounters.index') }}" class="btn-hmis-secondary">
        <i class="bi bi-x"></i> Cancel
      </a>
    </div>
  </form>
</div>
@endsection