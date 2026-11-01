@extends('layouts.app')
@section('title', $title)
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="color:var(--clr-grey-800);font-weight:500;">{{ $title }}</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">{{ $title }}</h1>
</div>
<div class="hmis-card">
  <div class="hmis-card__body">
    <div class="hmis-empty">
      <i class="bi bi-tools"></i>
      <div class="hmis-empty__title">Coming in Phase 4</div>
      <p>This module is being built. The foundation is in place.</p>
    </div>
  </div>
</div>
@endsection