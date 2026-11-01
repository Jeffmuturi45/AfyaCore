@extends('layouts.app')
@section('title', 'Branches')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>Branches</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Branches</h1>
    <p class="hmis-page-header__subtitle">{{ $branches->count() }} branch(es) configured</p>
  </div>
  <a href="{{ route('admin.branches.create') }}" class="btn-hmis-primary">
    <i class="bi bi-building-add"></i> Add Branch
  </a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:var(--sp-5);">
  @forelse($branches as $branch)
    <div class="hmis-card" style="overflow:hidden;">
      <div style="height:6px;background:linear-gradient(135deg,var(--clr-green),var(--clr-green-mid));"></div>
      <div class="hmis-card__body">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:var(--sp-4);">
          <div>
            <div style="font-size:var(--fs-xl);font-weight:var(--fw-black);color:var(--clr-charcoal);">{{ $branch->name }}</div>
            <div style="display:flex;gap:var(--sp-2);margin-top:var(--sp-2);">
              <span class="hmis-badge hmis-badge--grey font-mono">{{ $branch->code }}</span>
              <span class="hmis-badge hmis-badge--blue">{{ ucfirst(str_replace('_',' ',$branch->branch_type)) }}</span>
              <span class="hmis-badge hmis-badge--{{ $branch->is_active ? 'green' : 'red' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
          </div>
          <a href="{{ route('admin.branches.edit', $branch) }}" class="btn-hmis-ghost" style="padding:var(--sp-2);">
            <i class="bi bi-pencil"></i>
          </a>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
          @foreach([
            ['bi-geo-alt',$branch->address ?? 'Not set'],
            ['bi-telephone',$branch->phone ?? 'Not set'],
            ['bi-envelope',$branch->email ?? 'Not set'],
            ['bi-people',$branch->users_count . ' users'],
          ] as [$icon,$value])
            <div style="display:flex;align-items:center;gap:var(--sp-2);font-size:var(--fs-xs);color:var(--clr-grey-500);">
              <i class="bi {{ $icon }}"></i> {{ $value }}
            </div>
          @endforeach
        </div>

        <hr class="divider">
        <div style="font-size:var(--fs-xs);color:var(--clr-grey-400);">
          Modules: {{ $branch->modules->where('is_active',true)->pluck('module')->implode(', ') }}
        </div>
      </div>
    </div>
  @empty
    <div style="grid-column:1/-1;">
      <div class="hmis-empty">
        <i class="bi bi-building"></i>
        <div class="hmis-empty__title">No branches yet</div>
        <p>Create your first branch to get started.</p>
        <a href="{{ route('admin.branches.create') }}" class="btn-hmis-primary" style="margin-top:var(--sp-4);">
          <i class="bi bi-building-add"></i> Create First Branch
        </a>
      </div>
    </div>
  @endforelse
</div>
@endsection