@extends('layouts.app')

@section('title', 'Payments')

@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
  <li><span class="breadcrumb-sep"><i class="bi bi-chevron-right"></i></span></li>
  <li>Finance</li>
  <li><span class="breadcrumb-sep"><i class="bi bi-chevron-right"></i></span></li>
  <li>Payments</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="hmis-page-header">
  <div>
    <div class="hmis-page-header__title">
      <i class="bi bi-cash-coin text-green" style="font-size:1.5rem; vertical-align:middle; margin-right:var(--sp-2);"></i>
      Payments
    </div>
    <div class="hmis-page-header__subtitle">
      All received payments for {{ auth()->user()->branch?->name }}
    </div>
  </div>
  <div style="display:flex; gap:var(--sp-3); align-items:center; flex-wrap:wrap;">
    <a href="{{ route('billing.index') }}" class="btn-hmis-secondary">
      <i class="bi bi-receipt"></i> View Invoices
    </a>
  </div>
</div>

{{-- Summary Metrics --}}
@php
  $totalToday      = $payments->getCollection()->where('confirmed_at', '>=', today())->sum('amount');
  $countToday      = $payments->getCollection()->where('confirmed_at', '>=', today())->count();
  $methodBreakdown = $payments->getCollection()->groupBy('payment_method');
@endphp

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:var(--sp-4); margin-bottom:var(--sp-6);">

  <div class="hmis-metric hmis-metric--green">
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <div class="hmis-metric__label">Today's Collections</div>
        <div class="hmis-metric__value">KES {{ number_format($totalToday, 2) }}</div>
        <div style="font-size:var(--fs-xs); color:var(--clr-grey-500); margin-top:var(--sp-2);">
          {{ $countToday }} payment{{ $countToday !== 1 ? 's' : '' }} today
        </div>
      </div>
      <div class="hmis-metric__icon hmis-metric__icon--green">
        <i class="bi bi-graph-up-arrow"></i>
      </div>
    </div>
  </div>

  <div class="hmis-metric hmis-metric--blue">
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <div class="hmis-metric__label">This Page Total</div>
        <div class="hmis-metric__value">KES {{ number_format($payments->getCollection()->sum('amount'), 2) }}</div>
        <div style="font-size:var(--fs-xs); color:var(--clr-grey-500); margin-top:var(--sp-2);">
          {{ $payments->total() }} total records
        </div>
      </div>
      <div class="hmis-metric__icon hmis-metric__icon--blue">
        <i class="bi bi-wallet2"></i>
      </div>
    </div>
  </div>

  <div class="hmis-metric hmis-metric--amber">
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <div class="hmis-metric__label">Cash Received</div>
        <div class="hmis-metric__value">
          KES {{ number_format($methodBreakdown->get('cash', collect())->sum('amount'), 2) }}
        </div>
        <div style="font-size:var(--fs-xs); color:var(--clr-grey-500); margin-top:var(--sp-2);">
          {{ $methodBreakdown->get('cash', collect())->count() }} transactions
        </div>
      </div>
      <div class="hmis-metric__icon hmis-metric__icon--amber">
        <i class="bi bi-cash-coin"></i>
      </div>
    </div>
  </div>

  <div class="hmis-metric hmis-metric--purple">
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <div class="hmis-metric__label">M-Pesa Received</div>
        <div class="hmis-metric__value">
          KES {{ number_format($methodBreakdown->get('mpesa', collect())->sum('amount'), 2) }}
        </div>
        <div style="font-size:var(--fs-xs); color:var(--clr-grey-500); margin-top:var(--sp-2);">
          {{ $methodBreakdown->get('mpesa', collect())->count() }} transactions
        </div>
      </div>
      <div class="hmis-metric__icon hmis-metric__icon--purple">
        <i class="bi bi-phone"></i>
      </div>
    </div>
  </div>

</div>

{{-- Payments Table --}}
<div class="hmis-card">
  <div class="hmis-card__header">
    <div class="hmis-card__title">
      <i class="bi bi-list-check text-green"></i>
      Payment Transactions
      <span class="hmis-badge hmis-badge--green" style="margin-left:var(--sp-2);">
        {{ $payments->total() }}
      </span>
    </div>
    <div style="display:flex; gap:var(--sp-2); align-items:center;">
      <span style="font-size:var(--fs-xs); color:var(--clr-grey-400);">
        Page {{ $payments->currentPage() }} of {{ $payments->lastPage() }}
      </span>
    </div>
  </div>

  @if($payments->isEmpty())
    <div class="hmis-empty">
      <i class="bi bi-cash-stack"></i>
      <div class="hmis-empty__title">No payments recorded yet</div>
      <p>Payments will appear here once invoices are settled.</p>
    </div>
  @else
    <div class="hmis-table-wrap" style="border-radius:0; border:none; box-shadow:none;">
      <table class="hmis-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Reference</th>
            <th>Patient</th>
            <th>Invoice</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Received By</th>
            <th>Date & Time</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payments as $index => $payment)
            @php
              $pName = $payment->patient
                ? trim(($payment->patient->first_name ?? '') . ' ' . ($payment->patient->middle_name ?? '') . ' ' . ($payment->patient->last_name ?? ''))
                : null;
              $pInitials = $payment->patient
                ? strtoupper(substr($payment->patient->first_name ?? '?', 0, 1) . substr($payment->patient->last_name ?? '?', 0, 1))
                : '??';
              $methodColors = ['cash' => 'green', 'mpesa' => 'purple', 'card' => 'blue', 'insurance' => 'teal', 'waiver' => 'amber'];
              $mc = $methodColors[$payment->payment_method] ?? 'grey';
              $statusMap = [
                'confirmed' => ['badge' => 'green',  'icon' => 'bi-check-circle-fill'],
                'initiated' => ['badge' => 'amber',  'icon' => 'bi-hourglass-split'],
                'failed'    => ['badge' => 'red',    'icon' => 'bi-x-circle-fill'],
                'reversed'  => ['badge' => 'grey',   'icon' => 'bi-arrow-counterclockwise'],
              ];
              $s = $statusMap[$payment->status] ?? ['badge' => 'grey', 'icon' => 'bi-dash'];
            @endphp
            <tr>

              <td>
                <span style="font-size:var(--fs-xs); color:var(--clr-grey-400); font-weight:var(--fw-medium);">
                  {{ $payments->firstItem() + $index }}
                </span>
              </td>

              <td>
                <span class="font-mono" style="font-size:var(--fs-xs); background:var(--clr-grey-100); padding:2px 7px; border-radius:var(--r-sm); color:var(--clr-charcoal); font-weight:var(--fw-semibold); letter-spacing:.03em;">
                  {{ $payment->payment_reference }}
                </span>
              </td>

              <td>
                @if($pName)
                  <div style="display:flex; align-items:center; gap:var(--sp-2);">
                    <div style="width:30px; height:30px; border-radius:var(--r-pill); background:linear-gradient(135deg,var(--clr-green),var(--clr-green-mid)); display:flex; align-items:center; justify-content:center; font-size:var(--fs-xs); font-weight:var(--fw-black); color:#fff; flex-shrink:0;">
                      {{ $pInitials }}
                    </div>
                    <div>
                      <div style="font-weight:var(--fw-semibold); color:var(--clr-charcoal); font-size:var(--fs-sm);">
                        {{ $pName }}
                      </div>
                      @if($payment->patient->global_patient_id ?? null)
                        <div style="font-size:var(--fs-xs); color:var(--clr-grey-400);">
                          {{ $payment->patient->global_patient_id }}
                        </div>
                      @endif
                    </div>
                  </div>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                @if($payment->invoice)
                  <a href="{{ route('billing.show', $payment->invoice) }}"
                     class="font-mono"
                     style="font-size:var(--fs-xs); font-weight:var(--fw-semibold); color:var(--clr-green); text-decoration:none;">
                    {{ $payment->invoice->invoice_number }}
                  </a>
                  @if($payment->invoice->balance_due > 0)
                    <div style="font-size:var(--fs-xs); color:var(--clr-warning); margin-top:2px;">
                      Bal: KES {{ number_format($payment->invoice->balance_due, 2) }}
                    </div>
                  @else
                    <div style="font-size:var(--fs-xs); color:var(--clr-green); margin-top:2px;">
                      <i class="bi bi-check-circle-fill"></i> Paid
                    </div>
                  @endif
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                <span class="hmis-badge hmis-badge--{{ $mc }} hmis-badge--dot">
                  <i class="bi {{ $payment->methodIcon() }}"></i>
                  {{ $payment->methodLabel() }}
                </span>
              </td>

              <td>
                <div style="font-size:var(--fs-base); font-weight:var(--fw-black); color:var(--clr-charcoal); letter-spacing:-.01em;">
                  KES {{ number_format($payment->amount, 2) }}
                </div>
              </td>

              <td>
                <span class="hmis-badge hmis-badge--{{ $s['badge'] }}">
                  <i class="bi {{ $s['icon'] }}"></i>
                  {{ $payment->statusLabel() }}
                </span>
              </td>

              <td>
                @if($payment->receivedBy)
                  <div style="font-size:var(--fs-sm); font-weight:var(--fw-medium); color:var(--clr-grey-700);">
                    {{ $payment->receivedBy->name }}
                  </div>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              <td>
                <div style="font-size:var(--fs-sm); color:var(--clr-charcoal); font-weight:var(--fw-medium);">
                  {{ $payment->confirmed_at?->format('d M Y') ?? $payment->created_at->format('d M Y') }}
                </div>
                <div style="font-size:var(--fs-xs); color:var(--clr-grey-400);">
                  {{ $payment->confirmed_at?->format('H:i') ?? $payment->created_at->format('H:i') }}
                </div>
              </td>

              <td>
                <div style="display:flex; align-items:center; justify-content:flex-end; gap:var(--sp-1);">

                  {{-- View Invoice --}}
                  <a href="{{ route('billing.show', $payment->invoice_id) }}"
                     class="btn-hmis-ghost"
                     style="padding:var(--sp-1) var(--sp-2); font-size:var(--fs-xs);"
                     title="View Invoice">
                    <i class="bi bi-receipt"></i>
                  </a>

                  {{-- Print Receipt --}}
                  @if($payment->status === 'confirmed')
                    <a href="{{ route('payments.receipt', $payment) }}"
                       class="btn-hmis-ghost"
                       style="padding:var(--sp-1) var(--sp-2); font-size:var(--fs-xs); color:var(--clr-green);"
                       title="Print Receipt"
                       target="_blank">
                      <i class="bi bi-printer-fill"></i>
                    </a>
                  @endif

                  {{-- Reverse (branch admin only) --}}
                  @if($payment->status === 'confirmed' && auth()->user()->isBranchAdmin())
                    <button
                      type="button"
                      class="btn-hmis-ghost"
                      style="padding:var(--sp-1) var(--sp-2); font-size:var(--fs-xs); color:var(--clr-danger);"
                      title="Reverse Payment"
                      onclick="openReverseModal({{ $payment->id }}, '{{ $payment->payment_reference }}', '{{ number_format($payment->amount, 2) }}')">
                      <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                  @endif

                </div>
              </td>

            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($payments->hasPages())
      <div style="padding:var(--sp-4) var(--sp-5); border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:var(--sp-3);">
        <div style="font-size:var(--fs-xs); color:var(--clr-grey-500);">
          Showing <strong>{{ $payments->firstItem() }}</strong>–<strong>{{ $payments->lastItem() }}</strong>
          of <strong>{{ $payments->total() }}</strong> payments
        </div>
        <div style="display:flex; gap:var(--sp-1); align-items:center;">

          @if($payments->onFirstPage())
            <span class="btn-hmis-secondary" style="opacity:.4; cursor:not-allowed; padding:var(--sp-2) var(--sp-3); font-size:var(--fs-xs);">
              <i class="bi bi-chevron-left"></i> Prev
            </span>
          @else
            <a href="{{ $payments->previousPageUrl() }}" class="btn-hmis-secondary" style="padding:var(--sp-2) var(--sp-3); font-size:var(--fs-xs);">
              <i class="bi bi-chevron-left"></i> Prev
            </a>
          @endif

          @foreach($payments->getUrlRange(max(1, $payments->currentPage()-2), min($payments->lastPage(), $payments->currentPage()+2)) as $page => $url)
            @if($page == $payments->currentPage())
              <span class="btn-hmis-primary" style="padding:var(--sp-2) var(--sp-3); font-size:var(--fs-xs); min-width:36px; justify-content:center;">
                {{ $page }}
              </span>
            @else
              <a href="{{ $url }}" class="btn-hmis-secondary" style="padding:var(--sp-2) var(--sp-3); font-size:var(--fs-xs); min-width:36px; justify-content:center;">
                {{ $page }}
              </a>
            @endif
          @endforeach

          @if($payments->hasMorePages())
            <a href="{{ $payments->nextPageUrl() }}" class="btn-hmis-secondary" style="padding:var(--sp-2) var(--sp-3); font-size:var(--fs-xs);">
              Next <i class="bi bi-chevron-right"></i>
            </a>
          @else
            <span class="btn-hmis-secondary" style="opacity:.4; cursor:not-allowed; padding:var(--sp-2) var(--sp-3); font-size:var(--fs-xs);">
              Next <i class="bi bi-chevron-right"></i>
            </span>
          @endif

        </div>
      </div>
    @endif

  @endif
</div>

{{-- ── Reverse Payment Modal (branch admin only) ── --}}
@if(auth()->user()->isBranchAdmin())
<div id="reverseModal"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); backdrop-filter:blur(4px); z-index:1050; align-items:center; justify-content:center; padding:var(--sp-4);"
     onclick="if(event.target===this) closeReverseModal()">
  <div class="hmis-modal" style="max-width:440px;">
    <div class="hmis-modal__header">
      <div class="hmis-modal__title" style="color:var(--clr-danger); display:flex; align-items:center; gap:var(--sp-2);">
        <i class="bi bi-arrow-counterclockwise"></i>
        Reverse Payment
      </div>
      <button type="button" class="btn-hmis-ghost" onclick="closeReverseModal()" style="padding:var(--sp-1);">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form id="reverseForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="hmis-modal__body">

        <div class="hmis-alert hmis-alert--warning" style="margin-bottom:var(--sp-4);">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <div>
            <strong>This action cannot be undone.</strong>
            Reversing this payment will restore the invoice balance and mark the payment as reversed.
          </div>
        </div>

        <div style="background:var(--clr-grey-50); border:1px solid var(--border); border-radius:var(--r-md); padding:var(--sp-4); margin-bottom:var(--sp-4);">
          <div style="display:flex; justify-content:space-between; font-size:var(--fs-sm); margin-bottom:var(--sp-2);">
            <span style="color:var(--clr-grey-600);">Reference</span>
            <span id="modal-ref" class="font-mono" style="font-weight:var(--fw-semibold); color:var(--clr-charcoal);"></span>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:var(--fs-sm);">
            <span style="color:var(--clr-grey-600);">Amount</span>
            <span id="modal-amount" style="font-weight:var(--fw-black); color:var(--clr-danger); font-size:var(--fs-base);"></span>
          </div>
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label" for="reverseReason">
            Reason for reversal <span class="required">*</span>
          </label>
          <textarea
            id="reverseReason"
            name="reason"
            class="hmis-textarea"
            rows="3"
            placeholder="e.g. Duplicate payment, customer request, data entry error…"
            required
            minlength="5"
            style="resize:vertical;"></textarea>
          <div class="hmis-hint">Minimum 5 characters. This will be recorded in the audit log.</div>
        </div>

      </div>
      <div class="hmis-modal__footer">
        <button type="button" class="btn-hmis-secondary" onclick="closeReverseModal()">
          Cancel
        </button>
        <button type="submit" class="btn-hmis-danger">
          <i class="bi bi-arrow-counterclockwise"></i> Confirm Reversal
        </button>
      </div>
    </form>
  </div>
</div>
@endif

@endsection

@push('scripts')
<script>
  function openReverseModal(paymentId, ref, amount) {
    document.getElementById('reverseForm').action = '/payments/' + paymentId;
    document.getElementById('modal-ref').textContent    = ref;
    document.getElementById('modal-amount').textContent = 'KES ' + amount;
    document.getElementById('reverseReason').value      = '';
    const modal = document.getElementById('reverseModal');
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('reverseReason').focus(), 100);
  }

  function closeReverseModal() {
    document.getElementById('reverseModal').style.display = 'none';
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeReverseModal();
  });
</script>
@endpush