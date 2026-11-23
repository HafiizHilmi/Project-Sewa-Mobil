/**
 * SewaMobil — lapisan gerak (motion layer).
 * Semua efek di sini progresif: kalau JS mati atau pengguna minta gerak minimal,
 * halaman tetap berfungsi penuh.
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ------------------------------------------------------------------ *
     * 1. Indikator geser pada nav utama (pill mengikuti link aktif/hover)
     * ------------------------------------------------------------------ */
    function initNavPill() {
        var nav = document.querySelector('.nav-links');
        if (!nav) return;
        var pill = nav.querySelector('.nav-pill');
        if (!pill) {
            pill = document.createElement('span');
            pill.className = 'nav-pill';
            nav.appendChild(pill);
        }
        var links = Array.prototype.slice.call(nav.querySelectorAll('.nav-link'));

        function moveTo(link) {
            if (!link) {
                pill.classList.remove('is-visible');
                return;
            }
            var navBox = nav.getBoundingClientRect();
            var box = link.getBoundingClientRect();
            pill.style.width = box.width + 'px';
            pill.style.transform = 'translateX(' + (box.left - navBox.left) + 'px)';
            pill.classList.add('is-visible');
        }

        function activeLink() {
            return nav.querySelector('.nav-link.is-active');
        }

        links.forEach(function (link) {
            link.addEventListener('mouseenter', function () { moveTo(link); });
            link.addEventListener('focus', function () { moveTo(link); });
        });
        nav.addEventListener('mouseleave', function () { moveTo(activeLink()); });

        var current = activeLink();
        if (current) {
            // Tunggu font selesai dimuat supaya lebar teks sudah final.
            requestAnimationFrame(function () { moveTo(current); });
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function () { moveTo(activeLink()); });
            }
        }
        window.addEventListener('resize', function () { moveTo(activeLink()); });
        window.addEventListener('load', function () { moveTo(activeLink()); });
    }

    /* ------------------------------------------------------------------ *
     * 2. Navbar: bayangan saat digulir + drawer mobile
     * ------------------------------------------------------------------ */
    function initNavScroll() {
        var nav = document.querySelector('.site-nav');
        if (!nav) return;
        var ticking = false;
        function update() {
            nav.classList.toggle('is-scrolled', window.scrollY > 8);
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(update);
        }, { passive: true });
        update();
    }

    function initDrawer() {
        var btn = document.getElementById('navToggle');
        var drawer = document.getElementById('navDrawer');
        if (!btn || !drawer) return;

        function setOpen(open) {
            drawer.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.innerHTML = open ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-list"></i>';
        }

        btn.addEventListener('click', function () {
            setOpen(!drawer.classList.contains('is-open'));
        });
        drawer.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') setOpen(false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
                setOpen(false);
                btn.focus();
            }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth > 900) setOpen(false);
        });
    }

    /* ------------------------------------------------------------------ *
     * 3. Umpan balik tekan: ripple pada kontrol
     * ------------------------------------------------------------------ */
    var RIPPLE_SELECTOR = '.btn, .chip, .car-cta, .icon-btn, .dash-link, .addon-card, .theme-option';

    function spawnRipple(target, x, y) {
        if (reduceMotion) return;
        target.classList.add('ripple-host');
        var box = target.getBoundingClientRect();
        var size = Math.max(box.width, box.height);
        var el = document.createElement('span');
        el.className = 'ripple';
        el.style.width = el.style.height = size + 'px';
        el.style.left = (x - box.left - size / 2) + 'px';
        el.style.top = (y - box.top - size / 2) + 'px';
        target.appendChild(el);
        setTimeout(function () { el.remove(); }, 640);
    }

    function initRipple() {
        document.addEventListener('pointerdown', function (e) {
            var target = e.target.closest(RIPPLE_SELECTOR);
            if (!target || target.disabled) return;
            spawnRipple(target, e.clientX, e.clientY);
        }, { passive: true });
    }

    /* ------------------------------------------------------------------ *
     * 4. Reveal saat elemen masuk viewport
     * ------------------------------------------------------------------ */
    function initReveal() {
        var items = document.querySelectorAll('[data-reveal]');
        if (!items.length) return;

        if (reduceMotion || !('IntersectionObserver' in window)) {
            items.forEach(function (el) { el.classList.add('is-revealed'); });
            return;
        }

        // Beri jeda bertingkat untuk elemen yang berdekatan.
        var groups = new Map();
        items.forEach(function (el) {
            var parent = el.parentElement;
            var index = groups.get(parent) || 0;
            el.style.setProperty('--reveal-delay', Math.min(index, 6) * 55 + 'ms');
            groups.set(parent, index + 1);
        });

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                io.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

        items.forEach(function (el) { io.observe(el); });
    }

    /* ------------------------------------------------------------------ *
     * 5. Progress bar saat pindah halaman
     * ------------------------------------------------------------------ */
    function initRouteProgress() {
        if (reduceMotion) return;
        var bar = document.createElement('div');
        bar.className = 'route-progress';
        document.body.appendChild(bar);

        document.addEventListener('click', function (e) {
            var link = e.target.closest('a');
            if (!link) return;
            var href = link.getAttribute('href') || '';
            if (!href || href.charAt(0) === '#' || link.target === '_blank' ||
                href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0 ||
                link.hasAttribute('download') || e.metaKey || e.ctrlKey || e.shiftKey) return;
            if (link.hostname && link.hostname !== window.location.hostname) return;
            bar.classList.remove('is-done');
            bar.classList.add('is-loading');
        });

        window.addEventListener('pageshow', function () {
            bar.classList.remove('is-loading');
            bar.classList.add('is-done');
        });
    }

    /* ------------------------------------------------------------------ *
     * 6. Hitung naik untuk angka statistik
     * ------------------------------------------------------------------ */
    function initCounters() {
        var targets = document.querySelectorAll('[data-count]');
        if (!targets.length) return;

        function animate(el) {
            var final = parseInt(el.getAttribute('data-count'), 10) || 0;
            if (reduceMotion || final === 0) { el.textContent = final; return; }
            var start = performance.now();
            var duration = 700;
            function step(now) {
                var p = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(final * eased);
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        if (!('IntersectionObserver' in window)) {
            targets.forEach(animate);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                animate(entry.target);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.4 });
        targets.forEach(function (el) { io.observe(el); });
    }

    /* ------------------------------------------------------------------ *
     * 7. Transisi halus saat filter/tab mengubah isi (dipanggil manual)
     * ------------------------------------------------------------------ */
    window.fadeSwap = function (container, mutate) {
        if (!container) { mutate(); return; }
        if (reduceMotion) { mutate(); return; }
        container.style.transition = 'opacity 140ms ease, transform 140ms ease';
        container.style.opacity = '0';
        container.style.transform = 'translateY(4px)';
        setTimeout(function () {
            mutate();
            container.style.transition = 'opacity 240ms cubic-bezier(.16,1,.3,1), transform 240ms cubic-bezier(.16,1,.3,1)';
            container.style.opacity = '1';
            container.style.transform = 'none';
        }, 140);
    };

    /* ------------------------------------------------------------------ *
     * Boot
     * ------------------------------------------------------------------ */
    function boot() {
        initNavPill();
        initNavScroll();
        initDrawer();
        initRipple();
        initReveal();
        initRouteProgress();
        initCounters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();