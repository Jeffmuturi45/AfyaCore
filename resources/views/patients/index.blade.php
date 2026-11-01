@extends('layouts.app')
@section('title', 'Patients')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Patients</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Patients</h1>
    <p class="hmis-page-header__subtitle">{{ $patients->total() }} patient(s) registered at this branch</p>
  </div>
  <a href="{{ route('patients.create') }}" class="btn-hmis-primary">
    <i class="bi bi-person-plus-fill"></i> Register Patient
  </a>
</div>

{{-- Search --}}
<div class="hmis-card" style="margin-bottom:var(--space-5);">
  <div class="hmis-card__body" style="padding:var(--space-4);">
    <form method="GET" action="{{ route('patients.index') }}">
      <div style="display:flex;gap:var(--space-3);">
        <input type="text" name="search" value="{{ request('search') }}"
               class="hmis-input" style="max-width:400px;"
               placeholder="Search by name, phone, ID, MRN...">
        <button type="submit" class="btn-hmis-primary">
          <i class="bi bi-search"></i> Search
        </button>
        @if(request('search'))
          <a href="{{ route('patients.index') }}" class="btn-hmis-secondary">
            <i class="bi bi-x"></i> Clear
          </a>
        @endif
      </div>
    </form>
  </div>
</div>

{{-- Table --}}
<div class="hmis-table-wrap">
  <table class="hmis-table">
    <thead>
      <tr>
        <th>MRN</th>
        <th>Patient Name</th>
        <th>Gender</th>
        <th>Date of Birth</th>
        <th>Phone</th>
        <th>National ID</th>
        <th>Registered</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($patients as $patient)
        @php $br = $patient->branchRecords->first(); @endphp
        <tr>
          <td>
            <span style="font-family:monospace;font-size:var(--font-size-xs);background:var(--clr-grey-100);padding:2px 6px;border-radius:4px;">
              {{ $br?->mrn ?? '—' }}
            </span>
          </td>
          <td>
            <div style="font-weight:var(--font-weight-semibold);">{{ $patient->full_name }}</div>
            <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">{{ $patient->global_patient_id }}</div>
          </td>
          <td>{{ ucfirst($patient->gender ?? '—') }}</td>
          <td>
            {{ $patient->date_of_birth?->format('d M Y') ?? '—' }}
            @if($patient->date_of_birth)
              <div style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">{{ $patient->age }}</div>
            @endif
          </td>
          <td>{{ $patient->phone_primary ?? '—' }}</td>
          <td>{{ $patient->national_id ?? '—' }}</td>
          <td style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">
            {{ $patient->created_at->format('d M Y') }}
          </td>
          <td>
            <a href="{{ route('patients.show', $patient) }}" class="btn-hmis-secondary" style="padding:4px 10px;font-size:var(--font-size-xs);">
              <i class="bi bi-eye"></i> View
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8">
            <div class="hmis-empty">
              <i class="bi bi-people"></i>
              <div class="hmis-empty__title">No patients found</div>
              <p>{{ request('search') ? 'Try a different search term.' : 'Register the first patient to get started.' }}</p>
              <a href="{{ route('patients.create') }}" class="btn-hmis-primary" style="margin-top:var(--space-3);">
                <i class="bi bi-person-plus-fill"></i> Register Patient
              </a>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($patients->hasPages())
  <div style="margin-top:var(--space-4);">
    {{ $patients->links() }}
  </div>
@endif
@endsection