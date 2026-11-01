@extends('layouts.app')
@section('title', 'System Settings')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li class="breadcrumb-sep">/</li>
  <li>Settings</li>
@endsection
@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title">System Settings</h1>
</div>

<div style="max-width:640px;display:flex;flex-direction:column;gap:var(--sp-5);">

  {{-- Paystack --}}
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title">
        <i class="bi bi-credit-card-2-front-fill text-green"></i> Paystack Integration
      </h2>
      <span class="hmis-badge hmis-badge--{{ config('services.paystack.secret_key') ? 'green' : 'amber' }}">
        {{ config('services.paystack.secret_key') ? 'Configured' : 'Not configured' }}
      </span>
    </div>
    <div class="hmis-card__body">
      <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--sp-5);">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          These keys are stored in your <code>.env</code> file and used for M-Pesa and card payments.
          Get your keys from <a href="https://dashboard.paystack.com" target="_blank">Paystack Dashboard</a>.
        </div>
      </div>

      <form method="POST" action="{{ route('admin.settings.paystack') }}" novalidate>
        @csrf

        <div class="hmis-form-group">
          <label class="hmis-label">Public Key <span class="required">*</span></label>
          <input type="text" name="paystack_public_key" class="hmis-input"
                 value="{{ old('paystack_public_key', config('services.paystack.public_key')) }}"
                 placeholder="pk_test_xxxxxxxxxxxx" required>
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Secret Key <span class="required">*</span></label>
          <input type="password" name="paystack_secret_key" class="hmis-input"
                 placeholder="{{ config('services.paystack.secret_key') ? '••••••••••••••••' : 'sk_test_xxxxxxxxxxxx' }}"
                 required>
          <span class="hmis-hint">Never share your secret key. It is stored encrypted.</span>
        </div>

        <div class="hmis-form-group">
          <label class="hmis-label">Webhook URL</label>
          <input type="url" name="paystack_webhook_url" class="hmis-input"
                 value="{{ old('paystack_webhook_url', config('services.paystack.webhook_url')) }}"
                 placeholder="https://yourdomain.com/api/paystack/webhook">
          <span class="hmis-hint">Set this in your Paystack dashboard to confirm M-Pesa payments.</span>
        </div>

        <button type="submit" class="btn-hmis-primary">
          <i class="bi bi-save"></i> Save Paystack Settings
        </button>
      </form>
    </div>
  </div>

  {{-- System info --}}
  <div class="hmis-card">
    <div class="hmis-card__header">
      <h2 class="hmis-card__title"><i class="bi bi-info-circle text-green"></i> System Information</h2>
    </div>
    <div class="hmis-card__body">
      @foreach([
        'HMIS Version'    => config('hmis.version'),
        'Laravel Version' => app()->version(),
        'PHP Version'     => PHP_VERSION,
        'Database'        => 'PostgreSQL',
        'Environment'     => app()->environment(),
      ] as $label => $value)
        <div style="display:flex;justify-content:space-between;padding:var(--sp-2) 0;border-bottom:1px solid var(--border);font-size:var(--fs-sm);">
          <span style="color:var(--clr-grey-500);">{{ $label }}</span>
          <span style="font-weight:var(--fw-semibold);font-family:var(--font-mono);">{{ $value }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection