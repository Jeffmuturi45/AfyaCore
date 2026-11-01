@extends('layouts.app')
@section('title', 'Encounters')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Encounters</li>
@endsection

@section('content')
@php
  $badges = [
    'registered'=>'grey','triaged'=>'blue','in_queue'=>'blue',
    'in_progress'=>'amber','consultation_complete'=>'green',
    'pre_auth_pending'=>'amber','billed'=>'purple',
    'closed'=>'green','void'=>'red',
  ];
@endphp

<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Encounters</h1>
    <p class="hmis-page-header__subtitle">{{ $encounters->total() }} encounter(s)</p>
  </div>
  <a href="{{ route('encounters.create') }}" class="btn-hmis-primary">
    <i class="bi bi-clipboard2-plus"></i> New Encounter
  </a>
</div>

{{-- Filters --}}
<div class="hmis-card" style="margin-bottom:var(--space-5);">
  <div class="hmis-card__body" style="padding:var(--space-4);">
    <form method="GET" style="display:flex;gap:var(--space-3);flex-wrap:wrap;align-items:flex-end;">
      <div>
        <label class="hmis-label" style="margin-bottom:var(--space-1);">Date</label>
        <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}" class="hmis-input" style="width:auto;">
      </div>
      <div>
        <label class="hmis-label" style="margin-bottom:var(--space-1);">Status</label>
        <select name="status" class="hmis-select" style="width:auto;">
          <option value="">All Statuses</option>
          @foreach(['registered','triaged','in_queue','in_progress','consultation_complete','billed','closed','void'] as $s)
            <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn-hmis-primary"><i class="bi bi-funnel"></i> Filter</button>
      <a href="{{ route('encounters.index') }}" class="btn-hmis-secondary"><i class="bi bi-x"></i> Reset</a>
    </form>
  </div>
</div>

<div class="hmis-table-wrap">
  <table class="hmis-table">
    <thead>
      <tr>
        <th>Encounter No.</th>
        <th>Patient</th>
        <th>Type</th>
        <th>Status</th>
        <th>Chief Complaint</th>
        <th>Assigned To</th>
        <th>Arrival</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($encounters as $enc)
        <tr>
          <td style="font-family:monospace;font-size:var(--font-size-xs);">{{ $enc->encounter_number }}</td>
          <td>
            <div style="font-weight:var(--font-weight-semibold);">{{ $enc->patient->full_name }}</div>
          </td>
          <td>
            <span class="hmis-badge {{ $enc->isEyeClinic() ? 'hmis-badge--blue' : 'hmis-badge--green' }}">
              {{ $enc->isEyeClinic() ? 'Eye Clinic' : 'OPD' }}
            </span>
          </td>
          <td>
            <span class="hmis-badge hmis-badge--{{ $badges[$enc->status] ?? 'grey' }}">
              {{ ucfirst(str_replace('_',' ',$enc->status)) }}
            </span>
          </td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            {{ $enc->chief_complaint ?? '—' }}
          </td>
          <td>{{ $enc->assignedTo?->name ?? '—' }}</td>
          <td style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">
            {{ $enc->arrival_time->format('H:i') }}
          </td>
          <td>
            <a href="{{ route('encounters.show', $enc) }}" class="btn-hmis-secondary" style="padding:4px 10px;font-size:var(--font-size-xs);">
              <i class="bi bi-eye"></i> Open
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8">
            <div class="hmis-empty">
              <i class="bi bi-clipboard2"></i>
              <div class="hmis-empty__title">No encounters found</div>
              <p>No encounters for the selected filters.</p>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($encounters->hasPages())
  <div style="margin-top:var(--space-4);">{{ $encounters->links() }}</div>
@endif
@endsection