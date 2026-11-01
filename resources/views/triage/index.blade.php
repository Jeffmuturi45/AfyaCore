@extends('layouts.app')
@section('title', 'Triage & Assignment')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>Triage & Assignment</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Triage & Assignment</h1>
    <p class="hmis-page-header__subtitle">{{ $waiting->count() }} patient(s) waiting — {{ today()->format('d M Y') }}</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--sp-5);">
  {{-- Waiting patients --}}
  <div>
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title"><i class="bi bi-clock-history text-green"></i> Waiting for Triage</h2>
        <span class="hmis-badge hmis-badge--{{ $waiting->count() > 0 ? 'amber' : 'green' }}">
          {{ $waiting->count() }} waiting
        </span>
      </div>
      @if($waiting->isEmpty())
        <div class="hmis-card__body">
          <div class="hmis-empty">
            <i class="bi bi-check-circle"></i>
            <div class="hmis-empty__title">All clear!</div>
            <p>No patients waiting for triage.</p>
          </div>
        </div>
      @else
        <div style="padding:var(--sp-4);display:flex;flex-direction:column;gap:var(--sp-3);">
          @foreach($waiting as $i => $enc)
            <div class="hmis-queue-card" style="text-decoration:none;color:inherit;cursor:default;">
              <div class="hmis-queue-card__number">{{ $i + 1 }}</div>
              <div style="flex:1;">
                <div class="hmis-queue-card__name">{{ $enc->patient->full_name }}</div>
                <div class="hmis-queue-card__meta">
                  Arrived {{ $enc->arrival_time->format('H:i') }}
                  @if($enc->chief_complaint) &mdash; {{ Str::limit($enc->chief_complaint, 40) }}@endif
                </div>
              </div>
              <a href="{{ route('triage.create', $enc) }}" class="btn-hmis-primary" style="padding:6px 14px;font-size:var(--fs-xs);">
                <i class="bi bi-thermometer-half"></i>
                {{ $enc->encounter_type === 'eye_clinic' ? 'Assign' : 'Triage & Assign' }}
              </a>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  {{-- Doctor queues --}}
  <div>
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title"><i class="bi bi-person-badge-fill text-green"></i> Clinician Queues</h2>
      </div>
      <div class="hmis-card__body" style="padding:var(--sp-4);">
        @forelse($doctors as $doc)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--sp-3);border-radius:var(--r-md);border:1px solid var(--border);margin-bottom:var(--sp-3);background:var(--clr-grey-50);">
            <div style="display:flex;align-items:center;gap:var(--sp-3);">
              <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--clr-green),var(--clr-green-mid));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:var(--fs-xs);font-weight:800;color:#fff;">
                {{ strtoupper(substr($doc->name, 0, 2)) }}
              </div>
              <div>
                <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);">{{ $doc->name }}</div>
                <div style="font-size:var(--fs-xs);color:var(--clr-grey-500);">{{ config('hmis.roles.'.$doc->role) }}</div>
              </div>
            </div>
            <div style="text-align:center;">
              <div style="font-size:var(--fs-xl);font-weight:var(--fw-black);color:{{ $doc->queue_count > 5 ? 'var(--clr-danger)' : ($doc->queue_count > 2 ? 'var(--clr-warning)' : 'var(--clr-green)') }};">
                {{ $doc->queue_count }}
              </div>
              <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);">in queue</div>
            </div>
          </div>
        @empty
          <div class="hmis-empty" style="padding:var(--sp-5);">
            <i class="bi bi-person-x"></i>
            <div class="hmis-empty__title">No clinicians found</div>
            <p>Add doctors/optometrists to this branch first.</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection