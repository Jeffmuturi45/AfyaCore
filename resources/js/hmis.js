// ============================================================
// AFYA CORE HMIS — Global JS
// ============================================================

// Toast notification system
window.HmisToast = {
  container: null,

  init() {
    this.container = document.getElementById('hmis-toast-container');
  },

  show(type, title, message, duration = 5000) {
    if (!this.container) this.init();
    if (!this.container) return;

    const icons = {
      success: 'bi-check-circle-fill',
      error:   'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
      info:    'bi-info-circle-fill',
    };

    const toast = document.createElement('div');
    toast.className = `hmis-toast hmis-toast--${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    toast.innerHTML = `
      <i class="bi ${icons[type] || icons.info} hmis-toast__icon" aria-hidden="true"></i>
      <div class="hmis-toast__body">
        <div class="hmis-toast__title">${this._esc(title)}</div>
        ${message ? `<div class="hmis-toast__msg">${this._esc(message)}</div>` : ''}
      </div>
      <button class="hmis-toast__close" aria-label="Dismiss notification">
        <i class="bi bi-x" aria-hidden="true"></i>
      </button>
    `;

    toast.querySelector('.hmis-toast__close').addEventListener('click', () => {
      this._remove(toast);
    });

    this.container.appendChild(toast);

    if (duration > 0) {
      setTimeout(() => this._remove(toast), duration);
    }
  },

  success(title, msg)  { this.show('success', title, msg); },
  error(title, msg)    { this.show('error',   title, msg); },
  warning(title, msg)  { this.show('warning', title, msg); },
  info(title, msg)     { this.show('info',    title, msg); },

  _remove(el) {
    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    el.style.transition = 'opacity .2s, transform .2s';
    setTimeout(() => el.remove(), 200);
  },

  _esc(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  },
};

// Connectivity indicator
window.HmisConnectivity = {
  check() {
    const el = document.getElementById('hmis-connectivity');
    if (!el) return;
    if (navigator.onLine) {
      el.className = 'hmis-connectivity hmis-connectivity--online';
      el.querySelector('.hmis-connectivity__dot').title = 'Connected';
      el.querySelector('.hmis-connectivity__label').textContent = 'Online';
    } else {
      el.className = 'hmis-connectivity hmis-connectivity--offline';
      el.querySelector('.hmis-connectivity__label').textContent = 'Offline';
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  HmisToast.init();
  HmisConnectivity.check();
  window.addEventListener('online',  () => HmisConnectivity.check());
  window.addEventListener('offline', () => HmisConnectivity.check());

  // Auto-show flash toasts from server
  const flash = document.getElementById('hmis-flash-data');
  if (flash) {
    const type    = flash.dataset.type;
    const title   = flash.dataset.title;
    const message = flash.dataset.message;
    if (type && title) HmisToast.show(type, title, message);
  }
});