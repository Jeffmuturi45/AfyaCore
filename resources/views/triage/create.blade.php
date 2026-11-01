@extends('layouts.app')
@section('title', 'Triage Patient')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li><a href="{{ route('triage.index') }}" style="color:var(--clr-green);">Triage</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>{{ $encounter->patient->full_name }}</li>
@endsection
@section('content')

<div class="hmis-patient-header">
  <div class="hmis-patient-header__avatar"><i class="bi bi-person-fill"></i></div>
  <div style="flex:1;">
    <div class="hmis-patient-header__name">{{ $encounter->patient->full_name }}</div>
    <div class="hmis-patient-header__meta">
      <span><i class="bi bi-credit-card"></i> {{ $branchRecord?->mrn ?? '—' }}</span>
      @if($encounter->patient->date_of_birth)<span><i class="bi bi-calendar3"></i> {{ $encounter->patient->age }}</span>@endif
      <span><i class="bi bi-clock"></i> Arrived {{ $encounter->arrival_time->format('H:i') }}</span>
      @if($encounter->chief_complaint)
        <span><i class="bi bi-chat-text"></i> {{ $encounter->chief_complaint }}</span>
      @endif
    </div>
  </div>
  <span class="hmis-badge hmis-badge--{{ $encounter->isEyeClinic() ? 'blue' : 'green' }}" style="font-size:var(--fs-sm);padding:6px 16px;">
    {{ $encounter->isEyeClinic() ? 'Eye Clinic' : 'OPD' }}
  </span>
</div>

<form method="POST" action="{{ route('triage.store', $encounter) }}" novalidate>
  @csrf

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--sp-5);">
    <div>
      @if($encounter->isOpd())
        {{-- Vitals --}}
        <div class="hmis-card" style="margin-bottom:var(--sp-4);">
          <div class="hmis-card__header">
            <h2 class="hmis-card__title"><i class="bi bi-thermometer-half text-green"></i> Vital Signs</h2>
          </div>
          <div class="hmis-card__body">
            <div class="hmis-vitals-grid">
              <div>
                <label class="hmis-label">Temperature (°C)</label>
                <input type="number" step="0.1" name="temperature_c" class="hmis-input {{ $errors->has('temperature_c')?'is-invalid':'' }}"
                       value="{{ old('temperature_c') }}" placeholder="37.0">
                @error('temperature_c')<span class="hmis-error">{{ $message }}</span>@enderror
              </div>
              <div>
                <label class="hmis-label">Systolic (mmHg)</label>
                <input type="number" name="bp_systolic" class="hmis-input" value="{{ old('bp_systolic') }}" placeholder="120">
              </div>
              <div>
                <label class="hmis-label">Diastolic (mmHg)</label>
                <input type="number" name="bp_diastolic" class="hmis-input" value="{{ old('bp_diastolic') }}" placeholder="80">
              </div>
              <div>
                <label class="hmis-label">Pulse (bpm)</label>
                <input type="number" name="pulse_bpm" class="hmis-input" value="{{ old('pulse_bpm') }}" placeholder="72">
              </div>
              <div>
                <label class="hmis-label">SpO₂ (%)</label>
                <input type="number" step="0.1" name="spo2_percent" class="hmis-input" value="{{ old('spo2_percent') }}" placeholder="98">
              </div>
              <div>
                <label class="hmis-label">Weight (kg)</label>
                <input type="number" step="0.1" name="weight_kg" class="hmis-input" value="{{ old('weight_kg') }}" placeholder="70">
              </div>
              <div>
                <label class="hmis-label">Height (cm)</label>
                <input type="number" step="0.1" name="height_cm" class="hmis-input" value="{{ old('height_cm') }}" placeholder="170">
              </div>
              <div>
                <label class="hmis-label">MUAC (cm)</label>
                <input type="number" step="0.1" name="muac_cm" class="hmis-input" value="{{ old('muac_cm') }}">
              </div>
            </div>
            <div class="hmis-form-group" style="margin-top:var(--sp-4);">
              <label class="hmis-label">Triage Notes</label>
              <textarea name="notes" class="hmis-input" rows="2" placeholder="Observations...">{{ old('notes') }}</textarea>
            </div>
          </div>
        </div>
      @else
        <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--sp-4);">
          <i class="bi bi-eye-fill"></i>
          <div>Eye clinic patients skip vitals triage. Assign directly to an optometrist below.</div>
        </div>
      @endif
    </div>

    {{-- Assign to doctor --}}
    <div>
      <div class="hmis-card" style="position:sticky;top:calc(var(--topbar-h) + var(--sp-4));">
        <div class="hmis-card__header">
          <h2 class="hmis-card__title"><i class="bi bi-person-badge-fill text-green"></i> Assign to Clinician</h2>
        </div>
        <div class="hmis-card__body">
          @if($errors->has('assigned_to'))
            <div class="hmis-alert hmis-alert--danger" style="margin-bottom:var(--sp-4);">
              <i class="bi bi-exclamation-circle-fill"></i>
              <div>{{ $errors->first('assigned_to') }}</div>
            </div>
          @endif

          <div style="display:flex;flex-direction:column;gap:var(--sp-3);margin-bottom:var(--sp-5);" x-data="{ selected: '' }">
            @forelse($doctors as $doc)
              <label style="cursor:pointer;">
                <input type="radio" name="assigned_to" value="{{ $doc->id }}"
                       x-model="selected"
                       style="position:absolute;opacity:0;"
                       {{ old('assigned_to') == $doc->id ? 'checked' : '' }}>
                <div :class="selected == '{{ $doc->id }}' ? 'border-color:var(--clr-green);background:var(--clr-green-xlight);' : ''"
                     style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-3);border-radius:var(--r-md);border:2px solid;border-color:{{ old('assigned_to') == $doc->id ? 'var(--clr-green)' : 'var(--border)' }};transition:all var(--t-fast);">
                  <div style="display:flex;align-items:center;gap:var(--sp-3);">
                    <div style="width:34px;height:34px;background:linear-gradient(135deg,var(--clr-green),var(--clr-green-mid));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:var(--fs-xs);font-weight:800;color:#fff;">
                      {{ strtoupper(substr($doc->name, 0, 2)) }}
                    </div>
                    <div>
                      <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);">{{ $doc->name }}</div>
                      <div style="font-size:var(--fs-xs);color:var(--clr-grey-500);">{{ config('hmis.roles.'.$doc->role) }}</div>
                    </div>
                  </div>
                  <div style="text-align:right;">
                    <div style="font-size:var(--fs-lg);font-weight:var(--fw-black);color:{{ $doc->queue_count > 5 ? 'var(--clr-danger)' : ($doc->queue_count > 2 ? 'var(--clr-warning)' : 'var(--clr-green)') }};">
                      {{ $doc->queue_count }}
                    </div>
                    <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);">in queue</div>
                  </div>
                </div>
              </label>
            @empty
              <div class="hmis-alert hmis-alert--warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>No clinicians available. Add doctors to this branch first.</div>
              </div>
            @endforelse
          </div>

          <button type="submit" class="btn-hmis-primary" style="width:100%;justify-content:center;padding:var(--sp-3);">
            <i class="bi bi-check-circle-fill"></i>
            {{ $encounter->isEyeClinic() ? 'Assign to Optometrist' : 'Save Vitals & Assign' }}
          </button>
          <a href="{{ route('triage.index') }}" class="btn-hmis-secondary" style="width:100%;justify-content:center;margin-top:var(--sp-3);">
            <i class="bi bi-x"></i> Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection