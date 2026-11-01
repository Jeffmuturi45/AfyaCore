@php
  $patient  = $payment->invoice->patient ?? $payment->patient;
  $invoice  = $payment->invoice;
  $branch   = $payment->branch;
  $receiver = $payment->receivedBy;
  $patientName = $patient
    ? trim(($patient->first_name ?? '') . ' ' . ($patient->middle_name ?? '') . ' ' . ($patient->last_name ?? ''))
    : 'Unknown Patient';
  $initials = $patient
    ? strtoupper(substr($patient->first_name ?? '?', 0, 1) . substr($patient->last_name ?? '?', 0, 1))
    : '??';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt — {{ $payment->payment_reference }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --clr-green:        #006600;
      --clr-green-dark:   #004d00;
      --clr-green-mid:    #008800;
      --clr-green-light:  #e8f5e8;
      --clr-charcoal:     #1a1a2e;
      --clr-grey-100:     #f5f5f5;
      --clr-grey-200:     #eeeeee;
      --clr-grey-300:     #e0e0e0;
      --clr-grey-400:     #bdbdbd;
      --clr-grey-500:     #9e9e9e;
      --clr-grey-600:     #757575;
      --clr-grey-800:     #424242;
      --clr-grey-900:     #212121;
      --font-base:        'Inter', system-ui, sans-serif;
      --font-mono:        'JetBrains Mono', monospace;
      --paper-width:      360px;
      --paper-tooth:      6px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      font-family: var(--font-base);
      background: #f0f2f5;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 2rem 1rem 4rem;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Toolbar ── */
    .toolbar {
      width: var(--paper-width);
      max-width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
      gap: 0.75rem;
    }

    .toolbar__brand {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.8125rem;
      font-weight: 700;
      color: var(--clr-charcoal);
      text-decoration: none;
    }

    .toolbar__brand-icon {
      width: 28px; height: 28px;
      background: linear-gradient(135deg, var(--clr-green), var(--clr-green-mid));
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 0.75rem;
    }

    .toolbar__actions { display: flex; gap: 0.5rem; }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      padding: 0.4rem 0.875rem;
      border-radius: 8px;
      font-size: 0.8125rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      text-decoration: none;
      transition: all .15s ease;
      font-family: var(--font-base);
    }

    .btn--primary {
      background: linear-gradient(135deg, var(--clr-green), var(--clr-green-mid));
      color: #fff;
      box-shadow: 0 2px 8px rgba(0,102,0,.3);
    }
    .btn--primary:hover {
      background: linear-gradient(135deg, var(--clr-green-dark), var(--clr-green));
      transform: translateY(-1px);
    }

    .btn--secondary {
      background: #fff;
      color: var(--clr-grey-800);
      border: 1.5px solid var(--clr-grey-300);
    }
    .btn--secondary:hover { background: var(--clr-grey-100); }

    /* ── Printer shell ── */
    .printer {
      width: var(--paper-width);
      max-width: 100%;
    }

    .printer__machine {
      background: linear-gradient(160deg, #2d2d2d 0%, #1a1a1a 100%);
      border-radius: 18px;
      padding: 0.875rem;
      box-shadow:
        0 20px 40px rgba(0,0,0,.35),
        0 6px 14px rgba(0,0,0,.2),
        inset 0 1px 0 rgba(255,255,255,.12),
        inset 0 -1px 0 rgba(0,0,0,.4);
      position: relative;
      /* subtle plastic texture via gradient */
      background-image:
        radial-gradient(ellipse at 30% 20%, rgba(255,255,255,.04) 0%, transparent 60%),
        linear-gradient(160deg, #2d2d2d 0%, #1a1a1a 100%);
    }

    /* Screws */
    .printer__machine::before,
    .printer__machine::after {
      content: '';
      position: absolute;
      top: 1.1rem;
      width: 7px; height: 7px;
      background: radial-gradient(circle at 35% 35%, #555, #222);
      border-radius: 50%;
      box-shadow: inset 0 1px 2px rgba(0,0,0,.6);
    }
    .printer__machine::before { left: 1.1rem; }
    .printer__machine::after  { right: 1.1rem; }

    /* Screen */
    .printer__screen {
      background: #0a1628;
      border-radius: 10px;
      padding: 0.875rem 1rem;
      border: 1px solid #111;
      box-shadow:
        inset 0 0 20px rgba(0,0,0,.5),
        inset 0 0 4px rgba(0,255,0,.04);
      position: relative;
      overflow: hidden;
    }

    /* CRT scanline effect */
    .printer__screen::after {
      content: '';
      position: absolute;
      inset: 0;
      background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(0,0,0,.08) 2px,
        rgba(0,0,0,.08) 4px
      );
      pointer-events: none;
    }

    .screen__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.625rem;
    }

    .screen__logo {
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    .screen__logo-icon {
      width: 20px; height: 20px;
      background: linear-gradient(135deg, var(--clr-green), var(--clr-green-mid));
      border-radius: 4px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.6rem;
      color: #fff;
      box-shadow: 0 2px 6px rgba(0,102,0,.5);
    }

    .screen__brand {
      font-size: 0.6875rem;
      font-weight: 700;
      color: rgba(255,255,255,.8);
      letter-spacing: .02em;
    }

    .screen__branch {
      font-size: 0.625rem;
      color: rgba(255,255,255,.35);
    }

    .screen__row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 0.5rem;
      margin-bottom: 0.375rem;
    }

    .screen__label {
      font-size: 0.625rem;
      color: rgba(255,255,255,.35);
      text-transform: uppercase;
      letter-spacing: .08em;
      font-family: var(--font-mono);
    }

    .screen__value {
      font-size: 0.75rem;
      font-weight: 600;
      color: rgba(255,255,255,.75);
      font-family: var(--font-mono);
    }

    .screen__total {
      margin-top: 0.625rem;
      padding-top: 0.625rem;
      border-top: 1px solid rgba(255,255,255,.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .screen__total-label {
      font-size: 0.6875rem;
      color: rgba(255,255,255,.4);
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    .screen__total-value {
      font-size: 1.125rem;
      font-weight: 800;
      color: #6ee06e;
      font-family: var(--font-mono);
      letter-spacing: -.01em;
    }

    /* Status row */
    .screen__status {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      margin-top: 0.5rem;
    }

    .screen__status-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #6ee06e;
      animation: pulse-dot 2s ease infinite;
      flex-shrink: 0;
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(1.4); }
    }

    .screen__status-text {
      font-size: 0.625rem;
      color: #6ee06e;
      font-weight: 600;
      font-family: var(--font-mono);
      text-transform: uppercase;
      letter-spacing: .1em;
    }

    /* Slot */
    .printer__slot {
      height: 6px;
      background: #111;
      border-radius: 3px;
      margin: 0.625rem 1.5rem 0;
      box-shadow: inset 0 2px 4px rgba(0,0,0,.8);
    }

    /* ── Paper ── */
    .printer__output {
      margin-top: -4px;
      padding: 0 1.5rem;
      position: relative;
      z-index: 10;
    }

    /* Top shadow that makes paper look like it's coming from slot */
    .printer__output::before {
      content: '';
      position: absolute;
      top: 0;
      left: 1.5rem; right: 1.5rem;
      height: 8px;
      background: rgba(0,0,0,.18);
      filter: blur(4px);
      z-index: 20;
    }

    .receipt-paper {
      background: #fefefe;
      /* Subtle lined paper texture */
      background-image:
        repeating-linear-gradient(
          0deg,
          transparent,
          transparent 23px,
          rgba(0,0,0,.025) 23px,
          rgba(0,0,0,.025) 24px
        );
      font-family: var(--font-mono);
      padding: 1.5rem 1.25rem 2.5rem;
      position: relative;
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
      /* Torn bottom edge via clip-path teeth */
      clip-path: polygon(
        0 0, 100% 0, 100% calc(100% - 5px),
        97.5% 100%, 95% calc(100% - 5px),
        92.5% 100%, 90% calc(100% - 5px),
        87.5% 100%, 85% calc(100% - 5px),
        82.5% 100%, 80% calc(100% - 5px),
        77.5% 100%, 75% calc(100% - 5px),
        72.5% 100%, 70% calc(100% - 5px),
        67.5% 100%, 65% calc(100% - 5px),
        62.5% 100%, 60% calc(100% - 5px),
        57.5% 100%, 55% calc(100% - 5px),
        52.5% 100%, 50% calc(100% - 5px),
        47.5% 100%, 45% calc(100% - 5px),
        42.5% 100%, 40% calc(100% - 5px),
        37.5% 100%, 35% calc(100% - 5px),
        32.5% 100%, 30% calc(100% - 5px),
        27.5% 100%, 25% calc(100% - 5px),
        22.5% 100%, 20% calc(100% - 5px),
        17.5% 100%, 15% calc(100% - 5px),
        12.5% 100%, 10% calc(100% - 5px),
        7.5% 100%, 5% calc(100% - 5px),
        2.5% 100%, 0 calc(100% - 5px)
      );
    }

    /* ── Receipt content ── */
    .r-header {
      text-align: center;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px dashed var(--clr-grey-300);
    }

    .r-logo {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, var(--clr-green), var(--clr-green-mid));
      border-radius: 8px;
      display: inline-flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 1rem;
      margin-bottom: 0.5rem;
      box-shadow: 0 3px 10px rgba(0,102,0,.3);
    }

    .r-clinic-name {
      font-size: 0.875rem;
      font-weight: 700;
      color: var(--clr-charcoal);
      letter-spacing: .01em;
      font-family: var(--font-base);
      margin-bottom: 0.125rem;
    }

    .r-clinic-sub {
      font-size: 0.625rem;
      color: var(--clr-grey-500);
      text-transform: uppercase;
      letter-spacing: .1em;
    }

    .r-ref {
      margin-top: 0.625rem;
      display: inline-block;
      background: var(--clr-green-light);
      color: var(--clr-green-dark);
      padding: 2px 10px;
      border-radius: 999px;
      font-size: 0.6875rem;
      font-weight: 700;
      letter-spacing: .04em;
    }

    .r-section-title {
      font-size: 0.5625rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .12em;
      color: var(--clr-grey-400);
      margin-bottom: 0.375rem;
      margin-top: 0.875rem;
    }

    .r-row {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      gap: 0.5rem;
      font-size: 0.6875rem;
      margin-bottom: 0.25rem;
    }

    .r-row__label { color: var(--clr-grey-600); flex-shrink: 0; }
    .r-row__value { color: var(--clr-grey-900); font-weight: 600; text-align: right; word-break: break-word; }

    .r-divider {
      border: none;
      border-top: 1px dashed var(--clr-grey-300);
      margin: 0.75rem 0;
    }

    /* Patient avatar row */
    .r-patient {
      display: flex;
      align-items: center;
      gap: 0.625rem;
      background: var(--clr-grey-100);
      border-radius: 8px;
      padding: 0.5rem 0.625rem;
      margin: 0.625rem 0;
    }

    .r-patient__avatar {
      width: 28px; height: 28px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--clr-green), var(--clr-green-mid));
      display: flex; align-items: center; justify-content: center;
      font-size: 0.625rem;
      font-weight: 800;
      color: #fff;
      flex-shrink: 0;
      font-family: var(--font-base);
    }

    .r-patient__name {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--clr-charcoal);
      font-family: var(--font-base);
      line-height: 1.2;
    }

    .r-patient__meta {
      font-size: 0.5625rem;
      color: var(--clr-grey-500);
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    /* Line items */
    .r-items {
      width: 100%;
      font-size: 0.6875rem;
    }

    .r-items th {
      font-size: 0.5625rem;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: var(--clr-grey-400);
      font-weight: 700;
      padding-bottom: 0.25rem;
      border-bottom: 1px solid var(--clr-grey-200);
    }

    .r-items th:last-child,
    .r-items td:last-child { text-align: right; }

    .r-items td {
      padding: 0.25rem 0;
      color: var(--clr-grey-800);
      border-bottom: 1px solid rgba(0,0,0,.04);
      font-size: 0.6875rem;
    }

    .r-items tbody tr:last-child td { border-bottom: none; }

    /* Totals block */
    .r-totals { margin-top: 0.5rem; }

    .r-total-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.6875rem;
      margin-bottom: 0.2rem;
      color: var(--clr-grey-600);
    }

    .r-total-row--grand {
      font-size: 0.9375rem;
      font-weight: 800;
      color: var(--clr-charcoal);
      border-top: 2px solid var(--clr-grey-900);
      padding-top: 0.375rem;
      margin-top: 0.375rem;
      font-family: var(--font-base);
    }

    .r-total-row--paid {
      font-size: 0.8125rem;
      font-weight: 700;
      color: var(--clr-green);
      margin-top: 0.25rem;
    }

    .r-total-row--balance {
      font-size: 0.75rem;
      font-weight: 600;
      color: #f57c00;
    }

    /* Method badge */
    .r-method {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: var(--clr-green-light);
      color: var(--clr-green-dark);
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 0.625rem;
      font-weight: 700;
      letter-spacing: .04em;
      font-family: var(--font-base);
    }

    /* Status stamp */
    .r-stamp {
      text-align: center;
      margin: 0.875rem 0 0.5rem;
    }

    .r-stamp__inner {
      display: inline-block;
      border: 2.5px solid var(--clr-green);
      color: var(--clr-green);
      border-radius: 6px;
      padding: 3px 14px;
      font-size: 0.75rem;
      font-weight: 800;
      letter-spacing: .15em;
      text-transform: uppercase;
      font-family: var(--font-base);
      transform: rotate(-2deg);
      opacity: .8;
    }

    /* Footer */
    .r-footer {
      text-align: center;
      margin-top: 1rem;
      padding-top: 0.75rem;
      border-top: 1px dashed var(--clr-grey-300);
    }

    .r-footer p {
      font-size: 0.5625rem;
      color: var(--clr-grey-400);
      letter-spacing: .04em;
      line-height: 1.6;
      font-family: var(--font-base);
    }

    .r-footer__barcode {
      display: flex;
      align-items: flex-end;
      justify-content: center;
      gap: 1px;
      margin: 0.625rem auto 0.375rem;
      height: 28px;
    }

    .r-footer__barcode span {
      display: block;
      background: var(--clr-grey-800);
      border-radius: 1px;
      width: 2px;
    }

    /* ── Print styles ── */
    @media print {
      html, body {
        background: #fff;
        padding: 0;
        display: block;
      }

      .toolbar { display: none !important; }

      .printer__machine {
        background: #fff !important;
        box-shadow: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
      }

      .printer__machine::before,
      .printer__machine::after { display: none; }

      .printer__screen { display: none !important; }
      .printer__slot   { display: none !important; }

      .printer__output {
        padding: 0;
        margin: 0;
      }

      .printer__output::before { display: none; }

      .receipt-paper {
        box-shadow: none;
        clip-path: none;
        padding: 0;
        background-image: none;
        width: 80mm;
        margin: 0 auto;
      }

      @page {
        size: 80mm auto;
        margin: 8mm;
      }
    }
  </style>
</head>
<body>

{{-- Toolbar (hidden on print) --}}
<div class="toolbar">
  <a href="{{ route('payments.index') }}" class="toolbar__brand">
    <div class="toolbar__brand-icon">&#10084;</div>
    Afya Core
  </a>
  <div class="toolbar__actions">
    <a href="{{ route('billing.show', $invoice) }}" class="btn btn--secondary">
      ← Invoice
    </a>
    <button onclick="window.print()" class="btn btn--primary">
      &#128438; Print Receipt
    </button>
  </div>
</div>

{{-- Printer shell --}}
<div class="printer">
  <div class="printer__machine">

    {{-- Screen --}}
    <div class="printer__screen">
      <div class="screen__header">
        <div class="screen__logo">
          <div class="screen__logo-icon">&#10084;</div>
          <div>
            <div class="screen__brand">Afya Core HMIS</div>
            <div class="screen__branch">{{ $branch?->name ?? 'Branch' }}</div>
          </div>
        </div>
      </div>

      <div class="screen__row">
        <span class="screen__label">Reference</span>
        <span class="screen__value">{{ $payment->payment_reference }}</span>
      </div>
      <div class="screen__row">
        <span class="screen__label">Patient</span>
        <span class="screen__value">{{ $patientName }}</span>
      </div>
      <div class="screen__row">
        <span class="screen__label">Invoice</span>
        <span class="screen__value">{{ $invoice?->invoice_number }}</span>
      </div>

      <div class="screen__total">
        <span class="screen__total-label">Amount Paid</span>
        <span class="screen__total-value">KES {{ number_format($payment->amount, 2) }}</span>
      </div>

      <div class="screen__status">
        <span class="screen__status-dot"></span>
        <span class="screen__status-text">
          @if($payment->status === 'confirmed') Payment Confirmed
          @elseif($payment->status === 'initiated') Processing
          @else {{ ucfirst($payment->status) }}
          @endif
        </span>
      </div>
    </div>

    {{-- Paper slot --}}
    <div class="printer__slot"></div>
  </div>

  {{-- Paper output --}}
  <div class="printer__output">
    <div class="receipt-paper">

      {{-- Clinic header --}}
      <div class="r-header">
        <div class="r-logo">&#10084;</div>
        <div class="r-clinic-name">{{ $branch?->name ?? config('app.name') }}</div>
        <div class="r-clinic-sub">Payment Receipt</div>
        <div class="r-ref">{{ $payment->payment_reference }}</div>
      </div>

      {{-- Patient --}}
      <div class="r-section-title">Patient</div>
      <div class="r-patient">
        <div class="r-patient__avatar">{{ $initials }}</div>
        <div>
          <div class="r-patient__name">{{ $patientName }}</div>
          @if($patient?->global_patient_id)
            <div class="r-patient__meta">ID: {{ $patient->global_patient_id }}</div>
          @endif
        </div>
      </div>

      {{-- Payment details --}}
      <div class="r-section-title">Payment Details</div>
      <div class="r-row">
        <span class="r-row__label">Date</span>
        <span class="r-row__value">
          {{ ($payment->confirmed_at ?? $payment->created_at)->format('d M Y, H:i') }}
        </span>
      </div>
      <div class="r-row">
        <span class="r-row__label">Invoice #</span>
        <span class="r-row__value">{{ $invoice?->invoice_number }}</span>
      </div>
      <div class="r-row">
        <span class="r-row__label">Method</span>
        <span class="r-row__value">
          <span class="r-method">{{ $payment->methodLabel() }}</span>
        </span>
      </div>
      @if($payment->reference)
        <div class="r-row">
          <span class="r-row__label">Ref Code</span>
          <span class="r-row__value">{{ $payment->reference }}</span>
        </div>
      @endif
      <div class="r-row">
        <span class="r-row__label">Received by</span>
        <span class="r-row__value">{{ $receiver?->name ?? '—' }}</span>
      </div>

      {{-- Line items --}}
      @if($invoice && $invoice->items && $invoice->items->count())
        <hr class="r-divider">
        <div class="r-section-title">Items</div>
        <table class="r-items">
          <thead>
            <tr>
              <th style="text-align:left;">Description</th>
              <th style="text-align:right;">Qty</th>
              <th style="text-align:right;">Amount</th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoice->items as $item)
              <tr>
                <td>{{ $item->description ?? $item->name }}</td>
                <td style="text-align:right;">{{ $item->quantity ?? 1 }}</td>
                <td style="text-align:right;">{{ number_format($item->amount ?? $item->total, 2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

      {{-- Totals --}}
      <hr class="r-divider">
      <div class="r-totals">
        @if($invoice)
          <div class="r-total-row">
            <span>Subtotal</span>
            <span>KES {{ number_format($invoice->subtotal, 2) }}</span>
          </div>
          @if($invoice->discount_amount > 0)
            <div class="r-total-row">
              <span>Discount</span>
              <span>- KES {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
          @endif
          @if($invoice->tax_amount > 0)
            <div class="r-total-row">
              <span>Tax</span>
              <span>KES {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
          @endif
          <div class="r-total-row r-total-row--grand">
            <span>Invoice Total</span>
            <span>KES {{ number_format($invoice->total_amount, 2) }}</span>
          </div>
          <div class="r-total-row r-total-row--paid">
            <span>Paid Now</span>
            <span>KES {{ number_format($payment->amount, 2) }}</span>
          </div>
          @php
            $balance = $invoice->total_amount - ($invoice->amount_paid);
          @endphp
          @if($balance > 0)
            <div class="r-total-row r-total-row--balance">
              <span>Balance Due</span>
              <span>KES {{ number_format($balance, 2) }}</span>
            </div>
          @else
            <div class="r-total-row" style="color: var(--clr-green); font-weight:700;">
              <span>Balance Due</span>
              <span>KES 0.00 ✓</span>
            </div>
          @endif
        @else
          <div class="r-total-row r-total-row--grand">
            <span>Amount Paid</span>
            <span>KES {{ number_format($payment->amount, 2) }}</span>
          </div>
        @endif
      </div>

      {{-- Confirmed stamp --}}
      @if($payment->status === 'confirmed')
        <div class="r-stamp">
          <div class="r-stamp__inner">&#10003; Confirmed</div>
        </div>
      @endif

      {{-- Barcode-style decoration --}}
      <div class="r-footer__barcode" aria-hidden="true">
        @php
          $heights = [18,24,20,28,16,24,18,28,20,16,24,18,28,20,24,16,28,18,24,20,16,28,24,18,20];
        @endphp
        @foreach($heights as $h)
          <span style="height:{{ $h }}px;"></span>
        @endforeach
      </div>

      {{-- Footer --}}
      <div class="r-footer">
        <p>{{ $payment->payment_reference }}</p>
        <p style="margin-top:4px;">
          {{ config('app.name', 'Afya Core HMIS') }} &bull;
          {{ $branch?->name }} &bull;
          Printed {{ now()->format('d M Y H:i') }}
        </p>
        <p style="margin-top:6px;">Thank you for choosing our services.</p>
        <p>Please keep this receipt for your records.</p>
      </div>

    </div>{{-- /.receipt-paper --}}
  </div>{{-- /.printer__output --}}
</div>{{-- /.printer --}}

</body>
</html>