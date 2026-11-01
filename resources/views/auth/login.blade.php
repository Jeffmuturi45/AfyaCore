<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — Afya Core HMIS</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green:       #006600;
      --green-mid:   #008800;
      --green-dark:  #004d00;
      --green-light: #e8f5e8;
      --charcoal:    #0f172a;
      --slate:       #1e293b;
      --slate-mid:   #334155;
      --grey-300:    #e0e0e0;
      --grey-400:    #bdbdbd;
      --grey-500:    #9e9e9e;
      --grey-600:    #757575;
      --danger:      #d32f2f;
      --danger-lt:   #ffebee;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', system-ui, sans-serif;
      -webkit-font-smoothing: antialiased;
    }

    body {
      display: flex;
      min-height: 100vh;
      background: var(--charcoal);
      overflow: hidden;
    }

    /* ── Left panel ── */
    .panel-left {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 3rem 2rem;
      position: relative;
      background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0d2010 100%);
      overflow: hidden;
    }

    /* Animated background orbs */
    .panel-left::before {
      content: '';
      position: absolute;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(0,102,0,.18) 0%, transparent 70%);
      top: -150px; left: -150px;
      animation: orb1 12s ease-in-out infinite;
    }

    .panel-left::after {
      content: '';
      position: absolute;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(0,136,0,.12) 0%, transparent 70%);
      bottom: -100px; right: -100px;
      animation: orb2 15s ease-in-out infinite;
    }

    @keyframes orb1 {
      0%, 100% { transform: translate(0, 0) scale(1); }
      33%       { transform: translate(60px, 40px) scale(1.1); }
      66%       { transform: translate(-30px, 60px) scale(.95); }
    }

    @keyframes orb2 {
      0%, 100% { transform: translate(0, 0) scale(1); }
      50%       { transform: translate(-40px, -30px) scale(1.15); }
    }

    /* Grid dot pattern */
    .panel-left__grid {
      position: absolute;
      inset: 0;
      background-image:
        radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
      background-size: 32px 32px;
      pointer-events: none;
    }

    .panel-left__content {
      position: relative;
      z-index: 10;
      text-align: center;
      max-width: 420px;
    }

    .brand-logo {
      width: 72px; height: 72px;
      background: linear-gradient(135deg, var(--green), var(--green-mid));
      border-radius: 20px;
      display: inline-flex; align-items: center; justify-content: center;
      font-size: 2rem;
      color: #fff;
      margin-bottom: 1.5rem;
      box-shadow:
        0 0 0 1px rgba(255,255,255,.1),
        0 8px 32px rgba(0,102,0,.4),
        0 0 80px rgba(0,102,0,.15);
      animation: logo-pulse 3s ease-in-out infinite;
    }

    @keyframes logo-pulse {
      0%, 100% { box-shadow: 0 0 0 1px rgba(255,255,255,.1), 0 8px 32px rgba(0,102,0,.4), 0 0 80px rgba(0,102,0,.15); }
      50%       { box-shadow: 0 0 0 1px rgba(255,255,255,.15), 0 8px 40px rgba(0,102,0,.5), 0 0 120px rgba(0,102,0,.25); }
    }

    .brand-name {
      font-size: 2rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.03em;
      line-height: 1.1;
      margin-bottom: .5rem;
    }

    .brand-name span { color: #6ee06e; }

    .brand-tagline {
      font-size: .875rem;
      color: rgba(255,255,255,.45);
      letter-spacing: .04em;
      text-transform: uppercase;
      margin-bottom: 3rem;
    }

    /* Feature pills */
    .features {
      display: flex;
      flex-direction: column;
      gap: .75rem;
      text-align: left;
    }

    .feature {
      display: flex;
      align-items: center;
      gap: .875rem;
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.07);
      border-radius: 12px;
      padding: .875rem 1.125rem;
      backdrop-filter: blur(8px);
      transition: all .2s ease;
    }

    .feature:hover {
      background: rgba(255,255,255,.07);
      border-color: rgba(0,136,0,.3);
      transform: translateX(4px);
    }

    .feature__icon {
      width: 36px; height: 36px;
      border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      font-size: .9rem;
      flex-shrink: 0;
    }

    .feature__icon--green  { background: rgba(0,102,0,.3);  color: #6ee06e; }
    .feature__icon--blue   { background: rgba(2,136,209,.2); color: #4fc3f7; }
    .feature__icon--purple { background: rgba(123,31,162,.2);color: #ce93d8; }
    .feature__icon--amber  { background: rgba(245,124,0,.2); color: #ffcc80; }

    .feature__text {
      flex: 1;
    }

    .feature__title {
      font-size: .8125rem;
      font-weight: 600;
      color: rgba(255,255,255,.85);
      margin-bottom: 1px;
    }

    .feature__desc {
      font-size: .6875rem;
      color: rgba(255,255,255,.35);
    }

    /* ── Right panel (form) ── */
    .panel-right {
      width: 480px;
      flex-shrink: 0;
      background: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 3rem 2.5rem;
      position: relative;
      overflow-y: auto;
    }

    /* Subtle top accent */
    .panel-right::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--green), var(--green-mid), #6ee06e);
    }

    .form-header {
      margin-bottom: 2rem;
    }

    .form-header__eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .375rem;
      background: var(--green-light);
      color: var(--green-dark);
      padding: 3px 10px;
      border-radius: 999px;
      font-size: .6875rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      margin-bottom: 1rem;
    }

    .form-header__eyebrow::before {
      content: '';
      width: 5px; height: 5px;
      border-radius: 50%;
      background: var(--green);
      animation: pulse-dot 2s ease infinite;
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(1.4); }
    }

    .form-header__title {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--charcoal);
      letter-spacing: -.03em;
      line-height: 1.2;
      margin-bottom: .375rem;
    }

    .form-header__sub {
      font-size: .875rem;
      color: var(--grey-500);
    }

    /* Form fields */
    .field {
      margin-bottom: 1.25rem;
    }

    .field__label {
      display: block;
      font-size: .8125rem;
      font-weight: 600;
      color: #374151;
      margin-bottom: .5rem;
    }

    .field__label .req { color: var(--danger); margin-left: 2px; }

    .field__wrap {
      position: relative;
    }

    .field__icon {
      position: absolute;
      left: .875rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--grey-400);
      font-size: .9rem;
      pointer-events: none;
      transition: color .15s ease;
    }

    .field__input {
      display: block;
      width: 100%;
      padding: .75rem .875rem .75rem 2.625rem;
      font-size: .9375rem;
      font-family: inherit;
      color: var(--charcoal);
      background: #f9fafb;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      outline: none;
      transition: all .15s ease;
      -webkit-appearance: none;
    }

    .field__input:focus {
      background: #fff;
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(0,102,0,.12);
    }

    .field__input:focus + .field__icon,
    .field__wrap:focus-within .field__icon {
      color: var(--green);
    }

    .field__input.is-invalid {
      border-color: var(--danger);
      box-shadow: 0 0 0 3px rgba(211,47,47,.1);
    }

    /* Password toggle */
    .field__toggle {
      position: absolute;
      right: .875rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--grey-400);
      cursor: pointer;
      font-size: .9rem;
      padding: 0;
      display: flex;
      align-items: center;
      transition: color .15s ease;
    }

    .field__toggle:hover { color: var(--charcoal); }

    /* Error */
    .field__error {
      display: flex;
      align-items: center;
      gap: .25rem;
      font-size: .75rem;
      color: var(--danger);
      margin-top: .375rem;
    }

    /* Remember + forgot row */
    .form-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }

    .checkbox-wrap {
      display: flex;
      align-items: center;
      gap: .5rem;
      cursor: pointer;
    }

    .checkbox-wrap input[type="checkbox"] {
      width: 16px; height: 16px;
      border: 1.5px solid var(--grey-300);
      border-radius: 4px;
      accent-color: var(--green);
      cursor: pointer;
    }

    .checkbox-wrap span {
      font-size: .8125rem;
      color: var(--grey-600);
      font-weight: 500;
    }

    /* Submit button */
    .btn-signin {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      width: 100%;
      padding: .875rem;
      background: linear-gradient(135deg, var(--green), var(--green-mid));
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: .9375rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      letter-spacing: .01em;
      box-shadow: 0 4px 16px rgba(0,102,0,.35);
      transition: all .2s ease;
      position: relative;
      overflow: hidden;
    }

    .btn-signin::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.1), transparent);
      opacity: 0;
      transition: opacity .2s ease;
    }

    .btn-signin:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 24px rgba(0,102,0,.45);
    }

    .btn-signin:hover::before { opacity: 1; }
    .btn-signin:active { transform: translateY(0); }

    /* Global error alert */
    .alert-error {
      display: flex;
      align-items: flex-start;
      gap: .75rem;
      background: var(--danger-lt);
      border: 1px solid #ef9a9a;
      border-radius: 10px;
      padding: .875rem 1rem;
      margin-bottom: 1.25rem;
      font-size: .8125rem;
      color: #b71c1c;
      animation: shake .4s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%       { transform: translateX(-6px); }
      40%       { transform: translateX(6px); }
      60%       { transform: translateX(-4px); }
      80%       { transform: translateX(4px); }
    }

    .alert-error i { flex-shrink: 0; margin-top: 1px; font-size: 1rem; }

    /* Footer */
    .form-footer {
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid #f1f5f9;
      text-align: center;
    }

    .form-footer p {
      font-size: .75rem;
      color: var(--grey-400);
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      body { overflow: auto; }
      .panel-left { display: none; }
      .panel-right {
        width: 100%;
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>

  {{-- ── Left Panel ── --}}
  <div class="panel-left">
    <div class="panel-left__grid"></div>
    <div class="panel-left__content">

      <div class="brand-logo">
        <i class="bi bi-heart-pulse-fill"></i>
      </div>

      <div class="brand-name">Afya <span>Core</span></div>
      <div class="brand-tagline">Health Management Information System</div>

      <div class="features">
        <div class="feature">
          <div class="feature__icon feature__icon--green">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="feature__text">
            <div class="feature__title">Patient Management</div>
            <div class="feature__desc">Complete records, history & demographics</div>
          </div>
        </div>
        <div class="feature">
          <div class="feature__icon feature__icon--blue">
            <i class="bi bi-clipboard2-pulse-fill"></i>
          </div>
          <div class="feature__text">
            <div class="feature__title">Clinical Workflows</div>
            <div class="feature__desc">Triage, consultations & eye clinic</div>
          </div>
        </div>
        <div class="feature">
          <div class="feature__icon feature__icon--amber">
            <i class="bi bi-receipt"></i>
          </div>
          <div class="feature__text">
            <div class="feature__title">Billing & Payments</div>
            <div class="feature__desc">Invoices, receipts & payment tracking</div>
          </div>
        </div>
        <div class="feature">
          <div class="feature__icon feature__icon--purple">
            <i class="bi bi-bar-chart-fill"></i>
          </div>
          <div class="feature__text">
            <div class="feature__title">Multi-Branch Support</div>
            <div class="feature__desc">Centralised admin across all locations</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- ── Right Panel (Form) ── --}}
  <div class="panel-right">

    <div class="form-header">
      <div class="form-header__eyebrow">Secure Access</div>
      <div class="form-header__title">Welcome back</div>
      <div class="form-header__sub">Sign in to continue to Afya Core</div>
    </div>

    {{-- Global error --}}
    @if($errors->any())
      <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <div>{{ $errors->first() }}</div>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" autocomplete="on">
      @csrf

      {{-- Email --}}
      <div class="field">
        <label class="field__label" for="email">
          Email address <span class="req">*</span>
        </label>
        <div class="field__wrap">
          <input
            id="email"
            type="email"
            name="email"
            class="field__input {{ $errors->has('email') ? 'is-invalid' : '' }}"
            value="{{ old('email') }}"
            placeholder="you@clinic.com"
            autocomplete="email"
            autofocus
            required>
          <i class="bi bi-envelope field__icon"></i>
        </div>
        @error('email')
          <div class="field__error">
            <i class="bi bi-exclamation-circle"></i> {{ $message }}
          </div>
        @enderror
      </div>

      {{-- Password --}}
      <div class="field">
        <label class="field__label" for="password">
          Password <span class="req">*</span>
        </label>
        <div class="field__wrap">
          <input
            id="password"
            type="password"
            name="password"
            class="field__input {{ $errors->has('password') ? 'is-invalid' : '' }}"
            placeholder="••••••••"
            autocomplete="current-password"
            required>
          <i class="bi bi-lock field__icon"></i>
          <button type="button" class="field__toggle" onclick="togglePassword()" id="toggleBtn" tabindex="-1" aria-label="Show password">
            <i class="bi bi-eye" id="toggleIcon"></i>
          </button>
        </div>
        @error('password')
          <div class="field__error">
            <i class="bi bi-exclamation-circle"></i> {{ $message }}
          </div>
        @enderror
      </div>

      {{-- Remember me --}}
      <div class="form-row">
        <label class="checkbox-wrap">
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
          <span>Remember me</span>
        </label>
      </div>

      <button type="submit" class="btn-signin">
        <i class="bi bi-box-arrow-in-right"></i>
        Sign In
      </button>

    </form>

    <div class="form-footer">
      <p>© {{ date('Y') }} Afya Core HMIS — All rights reserved</p>
    </div>

  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon  = document.getElementById('toggleIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }
  </script>

</body>
</html>