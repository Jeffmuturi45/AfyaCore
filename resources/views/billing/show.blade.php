@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('billing.index') }}" style="color:var(--clr-green);">Billing</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">{{ $invoice->invoice_number }}</li>
@endsection
@section('content')
@php
  $statusBadges = ['draft'=>'grey','issued'=>'blue','partially_paid'=>'amber','paid'=>'green','written_off'=>'red'];
@endphp

<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">{{ $invoice->invoice_number }}</h1>
    <p class="hmis-page-header__subtitle">{{ $invoice->patient->full_name }} &mdash; {{ $invoice->created_at->format('d M Y H:i') }}</p>
  </div>
  <span class="hmis-badge hmis-badge--{{ $statusBadges[$invoice->status] ?? 'grey' }}" style="font-size:var(--font-size-base);padding:6px 16px;">
    {{ ucfirst(str_replace('_',' ',$invoice->status)) }}
  </span>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-5);">
  <div>
    {{-- Items --}}
    <div class="hmis-card" style="margin-bottom:var(--space-4);">
      <div class="hmis-card__header"><h2 class="hmis-card__title">Invoice Items</h2></div>
      <div class="hmis-table-wrap" style="border:none;box-shadow:none;border-radius:0;">
        <table class="hmis-table">
          <thead>
            <tr><th>Description</th><th>Type</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Total</th></tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $item)
              <tr>
                <td>{{ $item->description }}</td>
                <td><span class="hmis-badge hmis-badge--grey">{{ ucfirst($item->item_type) }}</span></td>
                <td>{{ $item->quantity }}</td>
                <td>KSh {{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '—' }}</td>
                <td><strong>KSh {{ number_format($item->line_total, 2) }}</strong></td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:var(--clr-grey-100);">
              <td colspan="5" style="text-align:right;font-weight:600;padding:var(--space-3) var(--space-4);">Total</td>
              <td style="font-weight:700;font-size:var(--font-size-lg);padding:var(--space-3) var(--space-4);">KSh {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Payment history --}}
    @if($invoice->payments->isNotEmpty())
      <div class="hmis-card">
        <div class="hmis-card__header"><h2 class="hmis-card__title">Payment History</h2></div>
        <div class="hmis-table-wrap" style="border:none;box-shadow:none;border-radius:0;">
          <table class="hmis-table">
            <thead>
              <tr><th>Reference</th><th>Method</th><th>Amount</th><th>Status</th><th>Time</th></tr>
            </thead>
            <tbody>
              @foreach($invoice->payments as $pay)
                <tr>
                  <td style="font-family:monospace;font-size:var(--font-size-xs);">{{ $pay->payment_reference }}</td>
                  <td>{{ ucfirst($pay->payment_method) }}</td>
                  <td><strong>KSh {{ number_format($pay->amount, 2) }}</strong></td>
                  <td><span class="hmis-badge hmis-badge--{{ $pay->status==='confirmed'?'green':'grey' }}">{{ ucfirst($pay->status) }}</span></td>
                  <td style="font-size:var(--font-size-xs);color:var(--clr-grey-500);">{{ $pay->confirmed_at?->format('H:i d M Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>

  {{-- Summary + Payment --}}
  <div>
    <div class="hmis-card" style="margin-bottom:var(--space-4);">
      <div class="hmis-card__header"><h2 class="hmis-card__title">Summary</h2></div>
      <div class="hmis-card__body">
        @foreach([
          'Subtotal'    => 'KSh ' . number_format($invoice->subtotal, 2),
          'Discount'    => 'KSh ' . number_format($invoice->discount_amount, 2),
          'Total'       => 'KSh ' . number_format($invoice->total_amount, 2),
          'Paid'        => 'KSh ' . number_format($invoice->amount_paid, 2),
        ] as $label => $value)
          <div style="display:flex;justify-content:space-between;margin-bottom:var(--space-3);font-size:var(--font-size-sm);">
            <span style="color:var(--clr-grey-600);">{{ $label }}</span>
            <span style="font-weight:var(--font-weight-semibold);">{{ $value }}</span>
          </div>
        @endforeach
        <hr class="divider">
        <div style="display:flex;justify-content:space-between;font-size:var(--font-size-lg);">
          <span style="font-weight:700;">Balance Due</span>
          <span style="font-weight:700;color:{{ $invoice->balance_due > 0 ? 'var(--clr-danger)' : 'var(--clr-green)' }};">
            KSh {{ number_format($invoice->balance_due, 2) }}
          </span>
        </div>
      </div>
    </div>

    {{-- Receive payment --}}
    @if($invoice->balance_due > 0)
      <div class="hmis-card">
        <div class="hmis-card__header"><h2 class="hmis-card__title"><i class="bi bi-cash-coin text-green"></i> Receive Payment</h2></div>
        <div class="hmis-card__body">
          <form method="POST" action="{{ route('billing.payment.store', $invoice) }}" novalidate>
            @csrf
            @if($errors->any())
              <div class="hmis-alert hmis-alert--danger" style="margin-bottom:var(--space-4);">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ $errors->first() }}</div>
              </div>
            @endif

            <div class="hmis-form-group">
              <label class="hmis-label">Payment Method <span class="required">*</span></label>
              <select name="payment_method" class="hmis-select" required>
                @foreach(['cash'=>'Cash','mpesa'=>'M-Pesa','card'=>'Card','insurance'=>'Insurance','waiver'=>'Waiver'] as $v=>$l)
                  <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
              </select>
            </div>

            <div class="hmis-form-group">
              <label class="hmis-label">Amount (KSh) <span class="required">*</span></label>
              <input type="number" step="0.01" name="amount"
                     class="hmis-input" value="{{ $invoice->balance_due }}" required>
            </div>

            <button type="submit" class="btn-hmis-primary" style="width:100%;justify-content:center;">
              <i class="bi bi-check-circle"></i> Confirm Payment
            </button>
          </form>
        </div>
      </div>
    @else
      <div class="hmis-alert hmis-alert--success">
        <i class="bi bi-check-circle-fill"></i>
        <div><strong>Fully paid.</strong> No balance outstanding.</div>
      </div>
    @endif
  </div>
</div>
@endsection