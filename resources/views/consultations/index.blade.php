@extends('layouts.app')
@section('title', 'Consultations')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Consultations</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">Doctor Queue — Today</h1>
</div>
<div class="hmis-table-wrap">
  <table class="hmis-table">
    <thead>
      <tr><th>Arrival</th><th>Patient</th><th>MRN</th><th>Complaint</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
      @forelse($encounters as $enc)
        <tr>
          <td>{{ $enc->arrival_time->format('H:i') }}</td>
          <td><strong>{{ $enc->patient->full_name }}</strong></td>
          <td style="font-family:monospace;font-size:var(--font-size-xs);">{{ $enc->patient->mrn($enc->branch_id) }}</td>
          <td>{{ $enc->chief_complaint ?? '—' }}</td>
          <td>
            <span class="hmis-badge {{ $enc->status==='in_progress' ? 'hmis-badge--amber' : 'hmis-badge--blue' }}">
              {{ ucfirst(str_replace('_',' ',$enc->status)) }}
            </span>
          </td>
          <td>
            <a href="{{ route('consultations.edit', $enc) }}" class="btn-hmis-primary" style="padding:4px 12px;font-size:var(--font-size-xs);">
              <i class="bi bi-person-badge-fill"></i> Consult
            </a>
          </td>
        </tr>
      @empty
        <tr><td colspan="6">
          <div class="hmis-empty">
            <i class="bi bi-check-circle"></i>
            <div class="hmis-empty__title">Queue is clear</div>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection