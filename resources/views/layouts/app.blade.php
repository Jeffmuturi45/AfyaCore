<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — Afya Core HMIS</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @stack('styles')
</head>
<body>
<div class="hmis-layout" x-data="{ sidebarOpen: false }">

  {{-- SIDEBAR --}}
  <aside class="hmis-sidebar" :class="{ 'open': sidebarOpen }" id="hmis-sidebar" aria-label="Main navigation">

    <a href="{{ route('dashboard') }}" class="hmis-sidebar__brand">
      <div class="hmis-sidebar__brand-icon">
        <i class="bi bi-heart-pulse-fill"></i>
      </div>
      <div>
        <div class="hmis-sidebar__brand-name">Afya Core</div>
        <div class="hmis-sidebar__brand-sub">{{ auth()->user()->branch?->name ?? 'System Admin' }}</div>
      </div>
    </a>

    <nav class="hmis-sidebar__nav">

      <span class="hmis-nav-section">Main</span>
      <a href="{{ route('dashboard') }}" class="hmis-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-fill"></i> Dashboard
      </a>

      @if(!auth()->user()->isSuperAdmin())
        <span class="hmis-nav-section">Patients</span>
        <a href="{{ route('patients.index') }}" class="hmis-nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}">
          <i class="bi bi-people-fill"></i> Patients
        </a>
        <a href="{{ route('encounters.index') }}" class="hmis-nav-item {{ request()->routeIs('encounters.*') ? 'active' : '' }}">
          <i class="bi bi-clipboard2-pulse-fill"></i> Encounters
        </a>
      @endif

      @if(auth()->user()->branch?->hasModule('opd'))
        <span class="hmis-nav-section">OPD</span>
        @if(in_array(auth()->user()->role, ['nurse','branch_admin']))
          <a href="{{ route('triage.index') }}" class="hmis-nav-item {{ request()->routeIs('triage.*') ? 'active' : '' }}">
            <i class="bi bi-thermometer-half"></i> Triage & Assign
          </a>
        @endif
        @if(in_array(auth()->user()->role, ['branch_admin']))
          <a href="{{ route('consultations.index') }}" class="hmis-nav-item {{ request()->routeIs('consultations.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge-fill"></i> My Queue
          </a>
        @endif
        @if(in_array(auth()->user()->role, ['branch_admin']))
          <a href="{{ route('pharmacy.index') }}" class="hmis-nav-item {{ request()->routeIs('pharmacy.*') ? 'active' : '' }}">
            <i class="bi bi-capsule"></i> Pharmacy
          </a>
        @endif
      @endif

      @if(auth()->user()->branch?->hasModule('eye_clinic'))
        <span class="hmis-nav-section">Eye Clinic</span>
        @if(in_array(auth()->user()->role, ['nurse','branch_admin']))
          <a href="{{ route('triage.index') }}" class="hmis-nav-item {{ request()->routeIs('triage.*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i> Assign Queue
          </a>
        @endif
        @if(in_array(auth()->user()->role, ['optometrist','branch_admin']))
          <a href="{{ route('eye-clinic.index') }}" class="hmis-nav-item {{ request()->routeIs('eye-clinic.*') ? 'active' : '' }}">
            <i class="bi bi-eye-fill"></i> My Queue
          </a>
        @endif
      @endif

      @if(!auth()->user()->isSuperAdmin())
        <span class="hmis-nav-section">Finance</span>
        <a href="{{ route('billing.index') }}" class="hmis-nav-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
          <i class="bi bi-receipt"></i> Billing
        </a>
        <a href="{{ route('payments.index') }}" class="hmis-nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
          <i class="bi bi-cash-coin"></i> Payments
        </a>
        <span class="hmis-nav-section">Inventory</span>
        <a href="{{ route('inventory.index') }}" class="hmis-nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
          <i class="bi bi-box-seam-fill"></i> Stock
        </a>
      @endif

      @if(auth()->user()->isSuperAdmin() || auth()->user()->isBranchAdmin())
        <span class="hmis-nav-section">Administration</span>
        @if(auth()->user()->isSuperAdmin())
          <a href="{{ route('admin.branches.index') }}" class="hmis-nav-item {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Branches
          </a>
        @endif
        <a href="{{ route('admin.users.index') }}" class="hmis-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <i class="bi bi-person-gear"></i> Users
        </a>
        @if(auth()->user()->isSuperAdmin())
          <a href="{{ route('admin.settings') }}" class="hmis-nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
            <i class="bi bi-gear-fill"></i> Settings
          </a>
        @endif
        <a href="{{ route('admin.audit.index') }}" class="hmis-nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
          <i class="bi bi-journal-text"></i> Audit Log
        </a>
      @endif

    </nav>

    <div class="hmis-sidebar__footer">
      <div class="hmis-sidebar__user">
        <div class="hmis-sidebar__avatar">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <div style="overflow:hidden;flex:1;">
          <div class="hmis-sidebar__user-name">{{ auth()->user()->name }}</div>
          <div class="hmis-sidebar__user-role">
            {{ config('hmis.roles.' . auth()->user()->role, auth()->user()->role) }}
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="hmis-signout">
          <i class="bi bi-box-arrow-left"></i> Sign Out
        </button>
      </form>
    </div>
  </aside>

  {{-- MAIN --}}
  <div class="hmis-main">

    {{-- TOPBAR --}}
    <header class="hmis-topbar">
      <button class="btn-hmis-ghost d-lg-none" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle navigation" style="padding:var(--sp-2);">
        <i class="bi bi-list" style="font-size:1.3rem;"></i>
      </button>

      <div class="hmis-topbar__breadcrumb">
        <nav aria-label="Breadcrumb">
          <ol class="breadcrumb-list">
            @yield('breadcrumb')
          </ol>
        </nav>
      </div>

      <div class="hmis-topbar__actions">
        <div id="hmis-connectivity" class="hmis-connectivity hmis-connectivity--online">
          <span class="hmis-connectivity__dot"></span>
          <span class="hmis-connectivity__label">Online</span>
        </div>
        <span style="font-size:var(--fs-xs);color:var(--clr-grey-400);font-weight:var(--fw-medium);">
          {{ now()->format('D, d M Y') }}
        </span>
      </div>
    </header>

    {{-- CONTENT --}}
    <main class="hmis-content" id="main-content">
      @yield('content')
    </main>
  </div>

  {{-- Mobile overlay --}}
  <div x-show="sidebarOpen"
       @click="sidebarOpen = false"
       style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1039;backdrop-filter:blur(2px);"
       x-transition:enter="transition-opacity ease duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0">
  </div>
</div>

{{-- TOASTS --}}
<div id="hmis-toast-container" class="hmis-toast-container" aria-live="polite" aria-atomic="false"></div>
@if(session('toast'))
  <div id="hmis-flash-data" hidden
    data-type="{{ session('toast.type') }}"
    data-title="{{ session('toast.title') }}"
    data-message="{{ session('toast.message', '') }}">
  </div>
@endif

@stack('scripts')
</body>
</html>