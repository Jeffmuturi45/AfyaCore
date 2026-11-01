@extends('layouts.app')
@section('title', 'Billing')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Billing</li>
@endsection
@section('content')
@php
  $statusBadges = ['draft'=>'grey','issued'=>'blue','partially_paid'=>'amber','paid'=>'green','written_off'=>'red','reversed'=>'red'];
@endphp
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Billing</h1>
    <p class="hmis-page-header__subtitle">{{ $invoices->total() }} invoice(s)</p>
  </div>
  <div style="display:flex;gap:var(--space-3);">
    <a href="{{ route('billing.pos') }}" class="btn-hmis-secondary">
      <i class="bi bi-cart-fill"></i> POS / Walk-in Sale
    </a>
  </div>
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
          <option value="">All</option>
          @foreach(['issued','partially_paid','paid','written_off'] as $s)
            <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn-hmis-primary"><i class="bi bi-funnel"></i> Filter</button>
      <a href="{{ route('billing.index') }}" class="btn-hmis-secondary"><i class="bi bi-x"></i> Reset</a>
    </form>
  </div>
</div>

<div class="hmis-table-wrap">
  <table class="hmis-table">
    <thead>
      <tr><th>Invoice No.</th><th>Patient</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Date</th><th></th></tr>
    </thead>
    <tbody>
      @forelse($invoices as $inv)
        <tr>
          <td style="font-family:monospace;font-size:var(--font-size-xs);">{{ $inv->invoice_number }}</td>
          <td><strong>{{ $inv->patient->full_name }}</strong></td>
          <td>KSh {{ number_format($inv->total_amount, 2) }}</td>
          <td>KSh {{ number_format($inv->amount_paid, 2) }}</td>
          <td>
            @if($inv->balance_due > 0)
              <span style="color:var(--clr-danger);font-weight:600;">KSh {{ number_format($inv->balance_due, 2) }}</span>
            @else
              <span style="color:var(--clr-green);font-weight:600;">Settled</span>
            @endif
          </td>
          <td><span class="hmis-badge hmis-badge--{{ $statusBadges[$inv->status] ?? 'grey' }}">{{ ucfirst(str_replace('_',' ',$inv->status)) }}</span></td>
          <td style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">{{ $inv->created_at->format('d M Y H:i') }}</td>
          <td>
            <a href="{{ route('billing.show', $inv) }}" class="btn-hmis-secondary" style="padding:4px 10px;font-size:var(--font-size-xs);">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
      @empty
        <tr><td colspan="8">
          <div class="hmis-empty">
            <i class="bi bi-receipt"></i>
            <div class="hmis-empty__title">No invoices found</div>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if($invoices->hasPages())
  <div style="margin-top:var(--space-4);">{{ $invoices->links() }}</div>
@endif
@endsection