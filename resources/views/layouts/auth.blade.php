<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Login') — Afya Core HMIS</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="hmis-auth-body">

  {{-- Subtle background pattern --}}
  <div class="hmis-auth-bg" aria-hidden="true"></div>

  <div class="hmis-auth-wrap">

    {{-- Brand mark --}}
    <div class="hmis-auth-brand">
      <div class="hmis-auth-brand__icon">
        <i class="bi bi-heart-pulse-fill"></i>
      </div>
      <div>
        <div class="hmis-auth-brand__name">Afya Core HMIS</div>
        <div class="hmis-auth-brand__sub">Health Management Information System</div>
      </div>
    </div>

    {{-- Card --}}
    <div class="hmis-card hmis-auth-card">
      <div class="hmis-card__body">
        @yield('content')
      </div>
    </div>

    <p class="hmis-auth-footer">
      &copy; {{ date('Y') }} Afya Core HMIS &mdash; All rights reserved
    </p>
  </div>

  {{-- Toast --}}
  <div id="hmis-toast-container" class="hmis-toast-container" aria-live="polite"></div>
  @if(session('toast'))
    <div id="hmis-flash-data" hidden
      data-type="{{ session('toast.type') }}"
      data-title="{{ session('toast.title') }}"
      data-message="{{ session('toast.message') }}">
    </div>
  @endif

  <style>
    /* ── Auth layout ──────────────────────────────────────────── */
    .hmis-auth-body {
      background: var(--surface-body);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--sp-4);
      position: relative;
    }

    /* Very subtle dot-grid background */
    .hmis-auth-bg {
      position: fixed;
      inset: 0;
      background-image: radial-gradient(circle, rgba(0,102,0,.07) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
      z-index: 0;
    }

    .hmis-auth-wrap {
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
      /* Entrance animation */
      animation: authSlideUp .4s cubic-bezier(.34,1.56,.64,1) both;
    }

    @keyframes authSlideUp {
      from { opacity: 0; transform: translateY(24px) scale(.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Brand header */
    .hmis-auth-brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--sp-3);
      margin-bottom: var(--sp-6);
      text-align: left;
    }

    .hmis-auth-brand__icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, var(--clr-green), var(--clr-green-mid));
      border-radius: var(--r-lg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      color: #fff;
      flex-shrink: 0;
      box-shadow: var(--sh-green);
    }

    .hmis-auth-brand__name {
      font-size: var(--fs-lg);
      font-weight: var(--fw-black);
      color: var(--clr-charcoal);
      letter-spacing: -.02em;
      line-height: 1.2;
    }

    .hmis-auth-brand__sub {
      font-size: var(--fs-xs);
      color: var(--clr-grey-500);
      margin-top: 2px;
    }

    /* Card overrides for auth */
    .hmis-auth-card {
      border-radius: var(--r-xl);
      box-shadow: var(--sh-lg);
      border: 1px solid var(--clr-grey-200);
    }

    .hmis-auth-card .hmis-card__body {
      padding: var(--sp-6);
    }

    /* Footer */
    .hmis-auth-footer {
      text-align: center;
      margin-top: var(--sp-5);
      font-size: var(--fs-xs);
      color: var(--clr-grey-400);
    }

    /* Mobile */
    @media (max-width: 480px) {
      .hmis-auth-brand { flex-direction: column; text-align: center; }
      .hmis-auth-card .hmis-card__body { padding: var(--sp-5); }
    }
  </style>

</body>
</html>