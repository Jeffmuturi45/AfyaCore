@extends('layouts.app')
@section('title', $patient->full_name)

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('patients.index') }}" style="color:var(--clr-green);">Patients</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">{{ $patient->full_name }}</li>
@endsection

@section('content')
{{-- Patient Header --}}
<div class="hmis-patient-header">
  <div class="hmis-patient-header__avatar">
    <i class="bi bi-person-fill"></i>
  </div>
  <div style="flex:1;">
    <div class="hmis-patient-header__name">{{ $patient->full_name }}</div>
    <div class="hmis-patient-header__meta">
      <span><i class="bi bi-credit-card"></i> {{ $branchRecord?->mrn ?? 'No MRN' }}</span>
      <span><i class="bi bi-globe"></i> {{ $patient->global_patient_id }}</span>
      @if($patient->date_of_birth)
        <span><i class="bi bi-calendar3"></i> {{ $patient->date_of_birth->format('d M Y') }} ({{ $patient->age }})</span>
      @endif
      @if($patient->gender)
        <span><i class="bi bi-gender-ambiguous"></i> {{ ucfirst($patient->gender) }}</span>
      @endif
      @if($patient->phone_primary)
        <span><i class="bi bi-telephone"></i> {{ $patient->phone_primary }}</span>
      @endif
    </div>
  </div>
  <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
    <a href="{{ route('encounters.create', ['patient_id' => $patient->id]) }}" class="btn-hmis-primary">
      <i class="bi bi-clipboard2-plus"></i> New Encounter
    </a>
    <a href="{{ route('patients.edit', $patient) }}" class="btn-hmis-secondary">
      <i class="bi bi-pencil"></i> Edit
    </a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--space-5);">
  {{-- Patient details --}}
  <div>
    <div class="hmis-card" style="margin-bottom:var(--space-4);">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Patient Details</h2>
      </div>
      <div class="hmis-card__body">
        @foreach([
          'National ID'   => $patient->national_id,
          'Phone'         => $patient->phone_primary,
          'Email'         => $patient->email,
          'County'        => $patient->county,
          'Nationality'   => $patient->nationality,
        ] as $label => $value)
          <div style="margin-bottom:var(--space-3);">
            <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);margin-bottom:2px;">{{ $label }}</div>
            <div style="font-size:var(--font-size-sm);">{{ $value ?? '—' }}</div>
          </div>
        @endforeach
        <hr class="divider">
        <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">
          Registered {{ $patient->created_at->format('d M Y') }}
        </div>
      </div>
    </div>

    @if($patient->next_of_kin_name)
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Next of Kin</h2>
      </div>
      <div class="hmis-card__body">
        <div style="font-weight:var(--font-weight-semibold);">{{ $patient->next_of_kin_name }}</div>
        <div style="font-size:var(--font-size-sm);color:var(--clr-grey-600);">{{ $patient->next_of_kin_relation }}</div>
        <div style="font-size:var(--font-size-sm);">{{ $patient->next_of_kin_phone }}</div>
      </div>
    </div>
    @endif
  </div>

  {{-- Encounters --}}
  <div>
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Encounter History</h2>
        <a href="{{ route('encounters.create', ['patient_id' => $patient->id]) }}" class="btn-hmis-primary" style="padding:4px 12px;font-size:var(--font-size-xs);">
          <i class="bi bi-plus"></i> New
        </a>
      </div>
      @if($encounters->isEmpty())
        <div class="hmis-card__body">
          <div class="hmis-empty">
            <i class="bi bi-clipboard2"></i>
            <div class="hmis-empty__title">No encounters yet</div>
          </div>
        </div>
      @else
        <div class="hmis-table-wrap" style="border:none;box-shadow:none;border-radius:0;">
          <table class="hmis-table">
            <thead>
              <tr><th>Date</th><th>Type</th><th>Status</th><th>Clinician</th><th></th></tr>
            </thead>
            <tbody>
              @foreach($encounters as $enc)
                <tr>
                  <td>{{ $enc->visit_date->format('d M Y') }}</td>
                  <td>{{ ucfirst(str_replace('_',' ',$enc->encounter_type)) }}</td>
                  <td>
                    @php
                      $badges = [
                        'registered'=>'grey','triaged'=>'blue','in_queue'=>'blue',
                        'in_progress'=>'amber','consultation_complete'=>'green',
                        'billed'=>'purple','closed'=>'green','void'=>'red',
                      ];
                    @endphp
                    <span class="hmis-badge hmis-badge--{{ $badges[$enc->status] ?? 'grey' }}">
                      {{ ucfirst(str_replace('_',' ',$enc->status)) }}
                    </span>
                  </td>
                  <td>{{ $enc->assignedTo?->name ?? '—' }}</td>
                  <td>
                    <a href="{{ route('encounters.show', $enc) }}" class="btn-hmis-secondary" style="padding:4px 10px;font-size:var(--font-size-xs);">
                      <i class="bi bi-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection