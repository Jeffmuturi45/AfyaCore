@extends('layouts.app')
@section('title', $encounter->encounter_number)

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('encounters.index') }}" style="color:var(--clr-green);">Encounters</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">{{ $encounter->encounter_number }}</li>
@endsection

@section('content')
@php
  $badges = [
    'registered'=>'grey','triaged'=>'blue','in_queue'=>'blue',
    'in_progress'=>'amber','consultation_complete'=>'green',
    'pre_auth_pending'=>'amber','pre_auth_approved'=>'green',
    'billed'=>'purple','closed'=>'green','void'=>'red',
  ];
@endphp

{{-- Patient Header --}}
<div class="hmis-patient-header">
  <div class="hmis-patient-header__avatar"><i class="bi bi-person-fill"></i></div>
  <div style="flex:1;">
    <div class="hmis-patient-header__name">{{ $encounter->patient->full_name }}</div>
    <div class="hmis-patient-header__meta">
      <span><i class="bi bi-credit-card"></i> {{ $branchRecord?->mrn ?? '—' }}</span>
      @if($encounter->patient->date_of_birth)
        <span><i class="bi bi-calendar3"></i> {{ $encounter->patient->age }}</span>
      @endif
      @if($encounter->patient->gender)
        <span><i class="bi bi-gender-ambiguous"></i> {{ ucfirst($encounter->patient->gender) }}</span>
      @endif
      <span><i class="bi bi-telephone"></i> {{ $encounter->patient->phone_primary ?? '—' }}</span>
    </div>
  </div>
  <div style="text-align:right;">
    <span class="hmis-badge hmis-badge--{{ $badges[$encounter->status] ?? 'grey' }}" style="font-size:var(--font-size-sm);padding:4px 12px;">
      {{ ucfirst(str_replace('_',' ',$encounter->status)) }}
    </span>
    <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);margin-top:var(--space-1);">
      {{ $encounter->encounter_number }}
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-5);">
  {{-- Main clinical area --}}
  <div>
    {{-- Triage (OPD only) --}}
    @if($encounter->isOpd())
      <div class="hmis-card" style="margin-bottom:var(--space-4);">
        <div class="hmis-card__header">
          <h2 class="hmis-card__title"><i class="bi bi-thermometer-half text-green"></i> Triage Vitals</h2>
          @if(!$encounter->triageRecord && in_array($encounter->status, ['registered']))
            <a href="{{ route('triage.create', $encounter) }}" class="btn-hmis-primary" style="padding:4px 12px;font-size:var(--font-size-xs);">
              <i class="bi bi-plus"></i> Record Vitals
            </a>
          @endif
        </div>
        <div class="hmis-card__body">
          @if($encounter->triageRecord)
            @php $t = $encounter->triageRecord; @endphp
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:var(--space-4);">
              @foreach([
                ['Temp','bi-thermometer-half',$t->temperature_c ? $t->temperature_c.'°C' : '—'],
                ['BP','bi-heart-pulse',$t->bp ?? '—'],
                ['Pulse','bi-activity',$t->pulse_bpm ? $t->pulse_bpm.' bpm' : '—'],
                ['SpO₂','bi-lungs',$t->spo2_percent ? $t->spo2_percent.'%' : '—'],
                ['Weight','bi-speedometer',$t->weight_kg ? $t->weight_kg.' kg' : '—'],
                ['Height','bi-rulers',$t->height_cm ? $t->height_cm.' cm' : '—'],
              ] as [$label,$icon,$value])
                <div style="text-align:center;padding:var(--space-3);background:var(--clr-grey-100);border-radius:var(--radius-md);">
                  <i class="bi {{ $icon }}" style="color:var(--clr-green);font-size:1.2rem;"></i>
                  <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);margin:var(--space-1) 0;">{{ $label }}</div>
                  <div style="font-weight:var(--font-weight-semibold);font-size:var(--font-size-sm);">{{ $value }}</div>
                </div>
              @endforeach
            </div>
            <div style="margin-top:var(--space-3);font-size:var(--font-size-xs);color:var(--clr-grey-500);">
              Recorded by {{ $t->recordedBy?->name }} at {{ $t->recorded_at->format('H:i, d M Y') }}
            </div>
          @else
            <div class="hmis-empty" style="padding:var(--space-5);">
              <i class="bi bi-thermometer"></i>
              <div class="hmis-empty__title">Vitals not recorded</div>
            </div>
          @endif
        </div>
      </div>
    @endif

    {{-- Clinical Notes / Eye Clinic Notes --}}
    <div class="hmis-card" style="margin-bottom:var(--space-4);">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">
          <i class="bi bi-{{ $encounter->isEyeClinic() ? 'eye' : 'journal-medical' }} text-green"></i>
          {{ $encounter->isEyeClinic() ? 'Eye Clinic Notes' : 'Clinical Notes' }}
        </h2>
        @if(in_array($encounter->status, ['in_progress','in_queue','triaged']))
          <a href="{{ $encounter->isEyeClinic() ? route('eye-clinic.notes.edit', $encounter) : route('consultations.edit', $encounter) }}"
             class="btn-hmis-primary" style="padding:4px 12px;font-size:var(--font-size-xs);">
            <i class="bi bi-pencil"></i> {{ $encounter->currentNote() ? 'Edit Notes' : 'Start Consultation' }}
          </a>
        @endif
      </div>
      <div class="hmis-card__body">
        @if($encounter->isOpd() && $encounter->currentNote())
          @php $note = $encounter->currentNote(); @endphp
          @foreach([
            'Presenting Complaints' => $note->presenting_complaints,
            'History of Illness'    => $note->history_of_illness,
            'Examination Findings'  => $note->examination_findings,
            'Diagnosis'             => $note->diagnosis,
            'Treatment Plan'        => $note->treatment_plan,
            'Follow-up'             => $note->follow_up_instructions,
          ] as $label => $value)
            @if($value)
              <div style="margin-bottom:var(--space-4);">
                <div style="font-size:var(--font-size-xs);font-weight:var(--font-weight-semibold);text-transform:uppercase;letter-spacing:.05em;color:var(--clr-grey-500);margin-bottom:var(--space-2);">{{ $label }}</div>
                <div style="font-size:var(--font-size-sm);line-height:1.7;">{{ $value }}</div>
              </div>
            @endif
          @endforeach
          @if($note->is_finalized)
            <div class="hmis-alert hmis-alert--success" style="margin-top:var(--space-4);">
              <i class="bi bi-check-circle-fill"></i>
              <div>Notes finalized by {{ $note->authoredBy?->name }} at {{ $note->finalized_at?->format('H:i, d M Y') }}</div>
            </div>
          @endif
        @else
          <div class="hmis-empty" style="padding:var(--space-5);">
            <i class="bi bi-journal-medical"></i>
            <div class="hmis-empty__title">No notes yet</div>
            <p>Consultation notes will appear here.</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Sidebar info --}}
  <div>
    {{-- Status timeline --}}
    <div class="hmis-card" style="margin-bottom:var(--space-4);">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Timeline</h2>
      </div>
      <div class="hmis-card__body" style="padding:var(--space-4);">
        @foreach($encounter->statusLog->sortBy('changed_at') as $log)
          <div style="display:flex;gap:var(--space-3);margin-bottom:var(--space-3);">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--clr-green);margin-top:5px;flex-shrink:0;"></div>
            <div>
              <div style="font-size:var(--font-size-xs);font-weight:var(--font-weight-semibold);">
                {{ ucfirst(str_replace('_',' ',$log->to_status)) }}
              </div>
              <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">
                {{ $log->changedBy?->name }} &middot; {{ $log->changed_at->format('H:i') }}
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Encounter details --}}
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title">Details</h2>
      </div>
      <div class="hmis-card__body">
        @foreach([
          'Visit Date'      => $encounter->visit_date->format('d M Y'),
          'Type'            => ucfirst(str_replace('_',' ',$encounter->encounter_type)),
          'Arrival'         => $encounter->arrival_time->format('H:i'),
          'Created By'      => $encounter->createdBy?->name,
          'Assigned To'     => $encounter->assignedTo?->name ?? 'Unassigned',
        ] as $label => $value)
          <div style="margin-bottom:var(--space-3);">
            <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">{{ $label }}</div>
            <div style="font-size:var(--font-size-sm);font-weight:var(--font-weight-medium);">{{ $value }}</div>
          </div>
        @endforeach

        @if($encounter->chief_complaint)
          <hr class="divider">
          <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);margin-bottom:var(--space-2);">Chief Complaint</div>
          <div style="font-size:var(--font-size-sm);">{{ $encounter->chief_complaint }}</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection