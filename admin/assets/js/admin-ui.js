/**
 * admin-ui.js — Toast, polling notifikasi, page transitions, dan motion layer.
 * Tidak ada dependency CDN — semua vanilla JS.
 * @version 2.0.0
 */

// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 1: TOAST NOTIFICATION SYSTEM
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  'use strict';

  /* Preferensi reduced motion */
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Durasi otomatis per varian (ms) */
  const AUTO_CLOSE = {
    success: 3500,
    info: 4000,
    warn: 5000,
    error: 5000,
    danger: 5000,
    order: 6000
  };

  /* Micro-badge icon SVG & judul default per varian */
  const VARIANT_CONFIG = {
    success: {
      iconSvg: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`,
      defaultTitle: 'Berhasil'
    },
    error: {
      iconSvg: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>`,
      defaultTitle: 'Terjadi Kesalahan'
    },
    danger: {
      iconSvg: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>`,
      defaultTitle: 'Terjadi Kesalahan'
    },
    warn: {
      iconSvg: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
      defaultTitle: 'Peringatan'
    },
    info: {
      iconSvg: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>`,
      defaultTitle: 'Informasi'
    },
    order: {
      iconSvg: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>`,
      defaultTitle: 'Pesanan Baru'
    }
  };

  /* Container toast */
  let _container = null;
  function getContainer() {
    if (_container) return _container;
    _container = document.getElementById('toast-container');
    if (_container) return _container;
    _container = document.createElement('div');
    _container.id = 'toast-container';
    document.body.appendChild(_container);
    return _container;
  }

  /* Anti-duplikasi cache */
  const _lastToastMap = new Map();

  /**
   * Format teks toast agar rapi, ringkas, dan profesional.
   */
  function formatToastContent(variant, title, body) {
    let cleanTitle = typeof title === 'string' ? title.trim() : '';
    let cleanBody = typeof body === 'string' ? body.trim() : '';

    // Jika hanya diberikan 1 teks sebagai parameter title
    if (!cleanBody && cleanTitle) {
      if (/^error koneksi:\s*/i.test(cleanTitle)) {
        const detail = cleanTitle.replace(/^error koneksi:\s*/i, '');
        return {
          title: 'Koneksi Perangkat Gagal',
          body: (detail.includes('Failed to connect') || detail.includes('Timeout'))
            ? 'Kendaraan offline atau tidak dapat dijangkau di jaringan lokal.'
            : detail
        };
      }
      if (/^berhasil!?\s*/i.test(cleanTitle)) {
        return {
          title: 'Berhasil',
          body: cleanTitle.replace(/^berhasil!?\s*/i, '') || cleanTitle
        };
      }
      if (/^gagal!?\s*/i.test(cleanTitle)) {
        return {
          title: 'Gagal',
          body: cleanTitle.replace(/^gagal!?\s*/i, '') || cleanTitle
        };
      }
      const cfg = VARIANT_CONFIG[variant] || VARIANT_CONFIG.info;
      if (cleanTitle.length > 30) {
        return { title: cfg.defaultTitle, body: cleanTitle };
      }
      return { title: cleanTitle, body: '' };
    }

    // Jika title dan body sama-sama diberikan
    if (/^error koneksi:\s*/i.test(cleanBody)) {
      const detail = cleanBody.replace(/^error koneksi:\s*/i, '');
      return {
        title: cleanTitle === 'Gagal' ? 'Koneksi Perangkat Gagal' : cleanTitle,
        body: (detail.includes('Failed to connect') || detail.includes('Timeout'))
          ? 'Kendaraan offline atau tidak dapat dijangkau di jaringan lokal.'
          : detail
      };
    }

    return { title: cleanTitle, body: cleanBody };
  }

  /**
   * Tampilkan toast notifikasi modern & minimalis.
   * @param {string} variant - 'success'|'info'|'warn'|'error'|'danger'|'order'
   * @param {string} title   - Judul pesan
   * @param {string} [body]  - Isi pesan opsional
   * @param {object} [opts]  - { duration, undoFn }
   * @returns {HTMLElement} Elemen toast
   */
  window.toast = function (variant, title, body = '', opts = {}) {
    variant = VARIANT_CONFIG[variant] ? variant : 'info';
    const cfg = VARIANT_CONFIG[variant];
    const duration = opts.duration ?? (AUTO_CLOSE[variant] || 4000);
    const content = formatToastContent(variant, title, body);

    const toastKey = `${variant}:${content.title}:${content.body}`;
    const now = Date.now();
    const container = getContainer();

    // Pencegahan duplikasi toast dalam interval 2 detik
    const existing = container.querySelector(`[data-toast-key="${CSS.escape(toastKey)}"]`);
    if (existing && (now - (_lastToastMap.get(toastKey) || 0) < 2500)) {
      _lastToastMap.set(toastKey, now);
      existing.classList.remove('toast-pulse');
      void existing.offsetWidth;
      existing.classList.add('toast-pulse');
      if (existing._resetTimer) existing._resetTimer();
      return existing;
    }
    _lastToastMap.set(toastKey, now);

    // Batasi maksimal 3 toast sekaligus
    if (container.children.length >= 3) {
      const oldest = container.firstElementChild;
      if (oldest) dismiss(oldest);
    }

    // Card Container
    const el = document.createElement('div');
    el.className = `toast-item toast-${variant}`;
    el.setAttribute('data-toast-key', toastKey);
    el.setAttribute('role', (variant === 'error' || variant === 'danger') ? 'alert' : 'status');
    el.setAttribute('aria-live', (variant === 'error' || variant === 'danger') ? 'assertive' : 'polite');

    // Micro-badge Icon
    const iconWrap = document.createElement('div');
    iconWrap.className = 'toast-icon-wrap';
    iconWrap.innerHTML = cfg.iconSvg;

    // Content Wrap
    const contentWrap = document.createElement('div');
    contentWrap.className = 'toast-content';

    const titleEl = document.createElement('h4');
    titleEl.className = 'toast-title';
    titleEl.textContent = content.title;
    contentWrap.appendChild(titleEl);

    if (content.body) {
      const bodyEl = document.createElement('p');
      bodyEl.className = 'toast-body';
      bodyEl.textContent = content.body;
      contentWrap.appendChild(bodyEl);
    }

    if (opts.undoFn) {
      const undoBtn = document.createElement('button');
      undoBtn.type = 'button';
      undoBtn.textContent = 'Batalkan';
      undoBtn.className = 'mt-2 inline-flex items-center text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline';
      undoBtn.addEventListener('click', () => {
        opts.undoFn();
        dismiss(el);
      });
      contentWrap.appendChild(undoBtn);
    }

    // Close Button (Minimalist SVG)
    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast-close-btn';
    closeBtn.setAttribute('aria-label', 'Tutup notifikasi');
    closeBtn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>`;
    closeBtn.addEventListener('click', () => dismiss(el));

    el.appendChild(iconWrap);
    el.appendChild(contentWrap);
    el.appendChild(closeBtn);

    // Smooth Progress Bar
    let progressEl = null;
    if (duration > 0 && !prefersReduced) {
      progressEl = document.createElement('div');
      progressEl.className = 'toast-progress';
      progressEl.style.transition = `transform ${duration}ms linear`;
      el.appendChild(progressEl);
    }

    container.appendChild(el);

    // Entrance Animation
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        el.classList.add('toast-visible');
        if (progressEl) {
          requestAnimationFrame(() => {
            progressEl.style.transform = 'scaleX(0)';
          });
        }
      });
    });

    // Auto Dismiss & Hover Pause
    let autoTimer = null;
    let remainingTime = duration;
    let timerStart = Date.now();

    const startTimer = (ms) => {
      if (ms <= 0) return;
      timerStart = Date.now();
      remainingTime = ms;
      autoTimer = setTimeout(() => dismiss(el), ms);
    };

    el._resetTimer = () => {
      if (autoTimer) clearTimeout(autoTimer);
      if (progressEl) {
        progressEl.style.transition = 'none';
        progressEl.style.transform = 'scaleX(1)';
        void progressEl.offsetWidth;
        progressEl.style.transition = `transform ${duration}ms linear`;
        progressEl.style.transform = 'scaleX(0)';
      }
      startTimer(duration);
    };

    if (duration > 0) {
      startTimer(duration);
    }

    // Pause on Hover
    el.addEventListener('mouseenter', () => {
      if (autoTimer) {
        clearTimeout(autoTimer);
        const elapsed = Date.now() - timerStart;
        remainingTime = Math.max(1200, remainingTime - elapsed);
      }
      if (progressEl) progressEl.style.transitionDuration = '0ms';
    });

    el.addEventListener('mouseleave', () => {
      if (duration > 0) {
        if (progressEl) {
          progressEl.style.transition = `transform ${remainingTime}ms linear`;
          progressEl.style.transform = 'scaleX(0)';
        }
        startTimer(remainingTime);
      }
    });

    return el;
  };

  function dismiss(el) {
    if (!el || el._isDismissing) return;
    el._isDismissing = true;
    el.classList.remove('toast-visible');
    el.classList.add('toast-hiding');
    setTimeout(() => {
      if (el.parentNode) el.remove();
    }, 220);
  }

  /* Shortcut helpers */
  window.toastSuccess = (title, body, opts) => window.toast('success', title, body, opts);
  window.toastError   = (title, body, opts) => window.toast('error',   title, body, opts);
  window.toastWarn    = (title, body, opts) => window.toast('warn',    title, body, opts);
  window.toastInfo    = (title, body, opts) => window.toast('info',    title, body, opts);
  window.toastOrder   = (title, body, opts) => window.toast('order',   title, body, opts);

  /* Non-blocking modern confirmation modal */
  window.confirmModal = function (title, message, onConfirm, opts = {}) {
    const backdrop = document.createElement('div');
    backdrop.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm';

    const box = document.createElement('div');
    box.className = 'w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 p-6 flex flex-col gap-4 text-left';

    box.innerHTML = `
      <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 text-base">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="text-sm font-bold text-slate-800 dark:text-white">${title || 'Konfirmasi Tindakan'}</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">${message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?'}</p>
        </div>
      </div>
      <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-700/80">
        <button type="button" class="btn-cancel px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          ${opts.cancelText || 'Batal'}
        </button>
        <button type="button" class="btn-ok px-4 py-2 rounded-xl text-xs font-bold text-white ${opts.danger !== false ? 'bg-rose-600 hover:bg-rose-700' : 'bg-blue-600 hover:bg-blue-700'} transition-all shadow-sm active:scale-95">
          ${opts.confirmText || 'Lanjutkan'}
        </button>
      </div>
    `;

    backdrop.appendChild(box);
    document.body.appendChild(backdrop);

    const closeDialog = () => {
      backdrop.style.opacity = '0';
      backdrop.style.transition = 'opacity .15s ease';
      setTimeout(() => backdrop.remove(), 160);
    };

    box.querySelector('.btn-cancel').addEventListener('click', closeDialog);
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) closeDialog();
    });
    box.querySelector('.btn-ok').addEventListener('click', () => {
      closeDialog();
      if (typeof onConfirm === 'function') onConfirm();
    });
  };

  window.confirmSubmit = function (e, message, opts) {
    if (e && e.preventDefault) e.preventDefault();
    const form = e.target.closest('form');
    if (!form) return false;
    window.confirmModal('Konfirmasi', message, () => {
      form.submit();
    }, opts);
    return false;
  };

})();


// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 2: FLASH MESSAGE (dari PHP Session)
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  /* SERVER_FLASH disuntikkan oleh PHP di index.php */
  if (window.SERVER_FLASH && window.SERVER_FLASH.msg) {
    const { msg, type } = window.SERVER_FLASH;
    const variant = { info: 'info', success: 'success', error: 'error', danger: 'error', warn: 'warn' }[type] || 'info';
    window.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => window.toast(variant, msg), 250);
    });
  }
})();


// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 3: POLLING NOTIFIKASI PESANAN (setiap 25 detik)
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  let lastMaxId = 0;
  let soundUnlocked = false;
  const POLL_INTERVAL = 25000;
  let pollTimer = null;

  /* Buka kunci audio setelah interaksi pertama pengguna */
  function unlockAudio() {
    if (soundUnlocked) return;
    const audio = document.getElementById('notifSound');
    if (audio) {
      audio.play().then(() => { audio.pause(); audio.currentTime = 0; soundUnlocked = true; }).catch(() => {});
    }
    soundUnlocked = true;
  }
  document.addEventListener('click', unlockAudio, { once: true });
  document.addEventListener('keydown', unlockAudio, { once: true });

  function playNotifSound() {
    if (!soundUnlocked) return;
    const audio = document.getElementById('notifSound');
    if (audio) {
      audio.currentTime = 0;
      audio.play().catch(() => {});
    }
  }

  function updateBellBadge(count) {
    const badge = document.getElementById('bell-badge');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count > 9 ? '9+' : count;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  }

  async function doPoll() {
    if (document.hidden) return; // Jangan poll saat tab background
    try {
      const res = await fetch('api/alerts.php', { cache: 'no-cache' });
      if (!res.ok) return;
      const data = await res.json();
      if (!data.ok) return;

      /* Deteksi pesanan baru */
      if (lastMaxId > 0 && data.max_id > lastMaxId) {
        const newOrder = data.items[0];
        if (newOrder) {
          window.toastOrder(
            '🛎 Pesanan Baru Masuk!',
            newOrder.desc,
            { duration: 8000 }
          );
          playNotifSound();
        }
      }
      lastMaxId = data.max_id;
      updateBellBadge(data.pending);
    } catch (e) { /* Gagal diam-diam */ }
  }

  /* Mulai polling setelah halaman siap */
  document.addEventListener('DOMContentLoaded', () => {
    doPoll(); // Poll pertama langsung
    pollTimer = setInterval(doPoll, POLL_INTERVAL);
  });

  /* Pause saat tab disembunyikan, resume saat kembali */
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) doPoll();
  });
})();


// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 4: RIPPLE EFFECT (tombol)
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  const rippleStyle = document.createElement('style');
  rippleStyle.textContent = `
    .btn-ripple { position: relative; overflow: hidden; }
    .btn-ripple .ripple {
      position: absolute; border-radius: 50%;
      transform: scale(0); animation: rippleAnim .4s linear;
      background: rgba(255,255,255,.35); pointer-events: none;
    }
    @keyframes rippleAnim { to { transform: scale(4); opacity: 0; } }
    @media (prefers-reduced-motion: reduce) { .btn-ripple .ripple { animation: none; } }
  `;
  document.head.appendChild(rippleStyle);

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-ripple, button[class*="bg-blue"], button[class*="bg-red"]');
    if (!btn) return;
    const r = document.createElement('span');
    r.className = 'ripple';
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px`;
    btn.appendChild(r);
    setTimeout(() => r.remove(), 450);
  });
})();


// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 5: SCROLL-TRIGGERED STAT COUNTER (dashboard)
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  function animateCounter(el) {
    const raw = el.textContent.replace(/\./g, '').replace(/,/g, '').trim();
    const target = parseFloat(raw);
    if (isNaN(target) || target === 0) return;
    const isCurrency = el.textContent.includes('Rp') || el.closest('[data-currency]');
    const dur = 1200;
    const start = performance.now();
    function step(now) {
      const pct = Math.min((now - start) / dur, 1);
      const eased = 1 - Math.pow(1 - pct, 3); // ease-out cubic
      const val = Math.round(eased * target);
      el.textContent = new Intl.NumberFormat('id-ID').format(val);
      if (pct < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.querySelectorAll('.stat-num').forEach(animateCounter);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    document.querySelectorAll('.stat-card').forEach(card => observer.observe(card));
  });
})();


// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 6: SKELETON LOADER
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  const skeletonStyle = document.createElement('style');
  skeletonStyle.textContent = `
    .skeleton {
      background: linear-gradient(90deg, var(--surface-2,#f1f5f9) 25%, var(--surface-1,#e2e8f0) 50%, var(--surface-2,#f1f5f9) 75%);
      background-size: 200% 100%;
      animation: skeletonShimmer 1.4s infinite;
      border-radius: 6px;
    }
    @keyframes skeletonShimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
    @media (prefers-reduced-motion: reduce) { .skeleton { animation: none; } }
  `;
  document.head.appendChild(skeletonStyle);
})();


// ═══════════════════════════════════════════════════════════════════════════════
// SECTION 7: PAGE TRANSITION (antara navigasi halaman)
// ═══════════════════════════════════════════════════════════════════════════════

(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const transStyle = document.createElement('style');
  transStyle.textContent = `
    .page-enter { animation: pageEnter 260ms cubic-bezier(.16,1,.3,1) forwards; }
    @keyframes pageEnter {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  `;
  document.head.appendChild(transStyle);

  /* Trigger page-enter saat Alpine mengubah activePage */
  document.addEventListener('alpine:init', () => {
    Alpine.effect(() => {
      // Will be triggered when activePage changes in Alpine
    });
  });
})();
