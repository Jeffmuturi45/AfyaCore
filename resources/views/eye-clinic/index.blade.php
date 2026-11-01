@extends('layouts.app')
@section('title', 'Eye Clinic')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Eye Clinic</li>
@endsection
@section('content')
@php
  $badges = ['registered'=>'grey','in_progress'=>'amber','consultation_complete'=>'green','billed'=>'purple','closed'=>'green','void'=>'red'];
@endphp
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Eye Clinic — Today</h1>
  <a href="{{ route('encounters.create') }}" class="btn-hmis-primary">
    <i class="bi bi-clipboard2-plus"></i> New Encounter
  </a>
</div>
<div class="hmis-table-wrap">
  <table class="hmis-table">
    <thead>
      <tr><th>Patient</th><th>MRN</th><th>Arrival</th><th>Status</th><th>Complaint</th><th></th></tr>
    </thead>
    <tbody>
      @forelse($encounters as $enc)
        <tr>
          <td><strong>{{ $enc->patient->full_name }}</strong></td>
          <td style="font-family:monospace;font-size:var(--font-size-xs);">{{ $enc->patient->mrn($enc->branch_id) }}</td>
          <td>{{ $enc->arrival_time->format('H:i') }}</td>
          <td><span class="hmis-badge hmis-badge--{{ $badges[$enc->status] ?? 'grey' }}">{{ ucfirst(str_replace('_',' ',$enc->status)) }}</span></td>
          <td>{{ $enc->chief_complaint ?? '—' }}</td>
          <td>
            <a href="{{ route('eye-clinic.notes.edit', $enc) }}" class="btn-hmis-primary" style="padding:4px 12px;font-size:var(--font-size-xs);">
              <i class="bi bi-eye"></i> Open
            </a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6"><div class="hmis-empty"><i class="bi bi-eye"></i><div class="hmis-empty__title">No eye clinic encounters today</div></div></td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection