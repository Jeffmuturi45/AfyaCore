@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')
  <li><i class="bi bi-grid-fill" style="color:var(--clr-green);"></i></li>
  <li class="breadcrumb-sep">/</li>
  <li>Dashboard</li>
@endsection

@section('content')

@php $isSuperAdmin = auth()->user()->isSuperAdmin(); @endphp

{{-- SUPER ADMIN: show setup if no branches yet --}}
@if($isSuperAdmin && $branchCount === 0)
  <div style="max-width:700px;margin:0 auto;padding:var(--sp-8) 0;">
    <div style="text-align:center;margin-bottom:var(--sp-8);">
      <div style="width:72px;height:72px;background:linear-gradient(135deg,var(--clr-green),var(--clr-green-mid));border-radius:var(--r-xl);display:inline-flex;align-items:center;justify-content:center;margin-bottom:var(--sp-4);box-shadow:var(--sh-green);">
        <i class="bi bi-heart-pulse-fill" style="font-size:2rem;color:#fff;"></i>
      </div>
      <h1 style="font-size:var(--fs-2xl);font-weight:var(--fw-black);color:var(--clr-charcoal);letter-spacing:-.02em;">Welcome to Afya Core HMIS</h1>
      <p style="color:var(--clr-grey-500);margin-top:var(--sp-2);">Let's get your system set up. Start by creating your branches.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--sp-4);">
      <a href="{{ route('admin.branches.create') }}" class="hmis-setup-card">
        <i class="bi bi-building-add"></i>
        <div class="hmis-setup-card__title">Create Branch</div>
        <div class="hmis-setup-card__desc">Set up your clinic locations</div>
      </a>
      <a href="{{ route('admin.users.create') }}" class="hmis-setup-card" style="{{ $branchCount === 0 ? 'opacity:.4;pointer-events:none;' : '' }}">
        <i class="bi bi-person-plus-fill"></i>
        <div class="hmis-setup-card__title">Add Users</div>
        <div class="hmis-setup-card__desc">Create doctors, nurses, staff</div>
      </a>
      <a href="{{ route('admin.settings') }}" class="hmis-setup-card" style="{{ $branchCount === 0 ? 'opacity:.4;pointer-events:none;' : '' }}">
        <i class="bi bi-gear-fill"></i>
        <div class="hmis-setup-card__title">Configure</div>
        <div class="hmis-setup-card__desc">Paystack keys and settings</div>
      </a>
    </div>
  </div>

@elseif($isSuperAdmin)
  {{-- Super admin normal dashboard --}}
  <div class="hmis-page-header">
    <div>
      <h1 class="hmis-page-header__title">System Overview</h1>
      <p class="hmis-page-header__subtitle">{{ now()->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('admin.branches.create') }}" class="btn-hmis-primary">
      <i class="bi bi-building-add"></i> Add Branch
    </a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-6);">
    <div class="hmis-metric hmis-metric--green">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <div class="hmis-metric__label">Branches</div>
          <div class="hmis-metric__value">{{ $branchCount }}</div>
        </div>
        <div class="hmis-metric__icon hmis-metric__icon--green"><i class="bi bi-building"></i></div>
      </div>
    </div>
    <div class="hmis-metric hmis-metric--blue">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <div class="hmis-metric__label">System Users</div>
          <div class="hmis-metric__value">{{ $userCount }}</div>
        </div>
        <div class="hmis-metric__icon hmis-metric__icon--blue"><i class="bi bi-people-fill"></i></div>
      </div>
    </div>
    <div class="hmis-metric hmis-metric--amber">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <div class="hmis-metric__label">Total Patients</div>
          <div class="hmis-metric__value">{{ $patientCount }}</div>
        </div>
        <div class="hmis-metric__icon hmis-metric__icon--amber"><i class="bi bi-person-heart"></i></div>
      </div>
    </div>
    <div class="hmis-metric hmis-metric--purple">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <div class="hmis-metric__label">Today's Encounters</div>
          <div class="hmis-metric__value">{{ $todayEncounters }}</div>
        </div>
        <div class="hmis-metric__icon hmis-metric__icon--purple"><i class="bi bi-clipboard2-pulse-fill"></i></div>
      </div>
    </div>
  </div>

  {{-- Branch cards --}}
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-building text-green"></i> Branches</h2>
      <a href="{{ route('admin.branches.index') }}" class="btn-hmis-ghost" style="font-size:var(--fs-xs);">View all <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="hmis-table-wrap" style="border:none;box-shadow:none;border-radius:0;">
      <table class="hmis-table">
        <thead><tr><th>Branch</th><th>Code</th><th>Type</th><th>Users</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach($branches as $b)
            <tr>
              <td><strong>{{ $b->name }}</strong></td>
              <td><span class="font-mono hmis-badge hmis-badge--grey">{{ $b->code }}</span></td>
              <td><span class="hmis-badge hmis-badge--blue">{{ ucfirst(str_replace('_',' ',$b->branch_type)) }}</span></td>
              <td>{{ $b->users_count }}</td>
              <td><span class="hmis-badge hmis-badge--{{ $b->is_active ? 'green' : 'red' }}">{{ $b->is_active ? 'Active' : 'Inactive' }}</span></td>
              <td><a href="{{ route('admin.branches.edit', $b) }}" class="btn-hmis-ghost" style="padding:4px 8px;font-size:var(--fs-xs);"><i class="bi bi-pencil"></i></a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

@else
  {{-- Branch staff dashboard --}}
  @php $branch = auth()->user()->branch; @endphp
  <div class="hmis-page-header">
    <div>
      <h1 class="hmis-page-header__title">
        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
      </h1>
      <p class="hmis-page-header__subtitle">{{ $branch->name }} &mdash; {{ now()->format('l, d F Y') }}</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-6);">
    <div class="hmis-metric hmis-metric--green">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div><div class="hmis-metric__label">Today's Patients</div><div class="hmis-metric__value">{{ $todayPatients }}</div></div>
        <div class="hmis-metric__icon hmis-metric__icon--green"><i class="bi bi-people-fill"></i></div>
      </div>
    </div>
    <div class="hmis-metric hmis-metric--blue">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div><div class="hmis-metric__label">Open Encounters</div><div class="hmis-metric__value">{{ $openEncounters }}</div></div>
        <div class="hmis-metric__icon hmis-metric__icon--blue"><i class="bi bi-clipboard2-pulse-fill"></i></div>
      </div>
    </div>
    <div class="hmis-metric hmis-metric--amber">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div><div class="hmis-metric__label">Pending Payments</div><div class="hmis-metric__value">{{ $pendingPayments }}</div></div>
        <div class="hmis-metric__icon hmis-metric__icon--amber"><i class="bi bi-cash-coin"></i></div>
      </div>
    </div>
    <div class="hmis-metric hmis-metric--red">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div><div class="hmis-metric__label">Waiting Triage</div><div class="hmis-metric__value">{{ $waitingTriage }}</div></div>
        <div class="hmis-metric__icon hmis-metric__icon--red"><i class="bi bi-thermometer-half"></i></div>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--sp-5);">
    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title"><i class="bi bi-lightning-charge-fill text-green"></i> Quick Actions</h2>
      </div>
      <div class="hmis-card__body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
          @if(in_array(auth()->user()->role, ['receptionist','branch_admin']))
            <a href="{{ route('patients.create') }}" class="hmis-queue-card">
              <div class="hmis-queue-card__number"><i class="bi bi-person-plus-fill"></i></div>
              <div><div class="hmis-queue-card__name">Register Patient</div><div class="hmis-queue-card__meta">New patient registration</div></div>
            </a>
            <a href="{{ route('encounters.create') }}" class="hmis-queue-card">
              <div class="hmis-queue-card__number"><i class="bi bi-clipboard2-plus"></i></div>
              <div><div class="hmis-queue-card__name">New Encounter</div><div class="hmis-queue-card__meta">Walk-in or scheduled visit</div></div>
            </a>
          @endif
          @if(in_array(auth()->user()->role, ['nurse','branch_admin']))
            <a href="{{ route('triage.index') }}" class="hmis-queue-card">
              <div class="hmis-queue-card__number"><i class="bi bi-thermometer-half"></i></div>
              <div><div class="hmis-queue-card__name">Triage Queue</div><div class="hmis-queue-card__meta">{{ $waitingTriage }} waiting</div></div>
            </a>
          @endif
          @if(in_array(auth()->user()->role, ['branch_admin','optometrist']))
            <a href="{{ $branch->isEyeClinic() ? route('eye-clinic.index') : route('consultations.index') }}" class="hmis-queue-card">
              <div class="hmis-queue-card__number"><i class="bi bi-person-badge-fill"></i></div>
              <div><div class="hmis-queue-card__name">My Queue</div><div class="hmis-queue-card__meta">Assigned patients</div></div>
            </a>
          @endif
          @if(in_array(auth()->user()->role, ['receptionist','branch_admin','optometrist']))
            <a href="{{ route('billing.pos') }}" class="hmis-queue-card">
              <div class="hmis-queue-card__number"><i class="bi bi-cart-fill"></i></div>
              <div><div class="hmis-queue-card__name">POS Sale</div><div class="hmis-queue-card__meta">Walk-in purchase</div></div>
            </a>
          @endif
        </div>
      </div>
    </div>

    <div class="hmis-card">
      <div class="hmis-card__header">
        <h2 class="hmis-card__title"><i class="bi bi-building text-green"></i> Branch Info</h2>
      </div>
      <div class="hmis-card__body">
        @foreach([
          'Branch'    => $branch->name,
          'Code'      => $branch->code,
          'Type'      => ucfirst(str_replace('_',' ',$branch->branch_type)),
          'Your Role' => config('hmis.roles.' . auth()->user()->role),
        ] as $label => $value)
          <div style="margin-bottom:var(--sp-4);">
            <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);margin-bottom:2px;text-transform:uppercase;letter-spacing:.06em;">{{ $label }}</div>
            <div style="font-weight:var(--fw-semibold);font-size:var(--fs-sm);">{{ $value }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
@endif

@endsection