<?php
/**
 * Navigasi utama pelanggan. Butuh include/site.php.
 * Variabel opsional: $navActive ('katalog' | 'landing' | 'profil')
 *
 * Motion: indikator pill di .nav-links digerakkan public/assets/js/ui.js,
 * jadi sorotan menu bergeser mulus mengikuti hover/aktif.
 */
require_once __DIR__ . '/site.php';

$site = site_settings();
$navActive = $navActive ?? '';
$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? trim((string) ($_SESSION['user_name'] ?? 'Pengguna')) : '';
?>
<div class="route-progress" aria-hidden="true"></div>
<header class="site-nav">
    <div class="nav-inner">
        <a class="brand" href="index.php?module=Homepage&action=index" aria-label="<?= htmlspecialchars($site['name']) ?>">
            <?= site_brand($site['name']) ?>
        </a>

        <nav class="nav-links" aria-label="Navigasi utama">
            <span class="nav-pill" aria-hidden="true"></span>
            <a class="nav-link <?= $navActive === 'landing' ? 'is-active' : '' ?>" href="index.php?module=Landing&action=index">Beranda</a>
            <a class="nav-link <?= $navActive === 'katalog' ? 'is-active' : '' ?>" href="index.php?module=Homepage&action=index">Katalog</a>
            <a class="nav-link" href="index.php?module=Landing&action=index#cara-sewa">Cara sewa</a>
            <?php if ($loggedIn): ?>
                <a class="nav-link <?= $navActive === 'profil' ? 'is-active' : '' ?>" href="index.php?module=Profile&action=index">Pesanan saya</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <button class="icon-btn" type="button" onclick="toggleGlobalTheme()" aria-label="Ganti tema terang/gelap" title="Ganti tema">
                <i class="bi bi-moon-fill" id="global-theme-icon"></i>
            </button>

            <?php if ($loggedIn): ?>
                <a class="nav-user" href="index.php?module=Profile&action=index">
                    <span class="avatar"><?= htmlspecialchars(site_initials($userName)) ?></span>
                    <span><?= htmlspecialchars(explode(' ', $userName)[0]) ?></span>
                </a>
                <a class="icon-btn" href="index.php?module=Auth&action=logout" aria-label="Keluar" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            <?php else: ?>
                <a class="btn btn--ghost btn--sm" href="index.php?module=Auth&action=login">Masuk</a>
                <a class="btn btn--primary btn--sm" href="index.php?module=Auth&action=register">Daftar</a>
            <?php endif; ?>

            <button class="icon-btn nav-toggle" type="button" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navDrawer">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>

    <div class="nav-drawer" id="navDrawer">
        <a href="index.php?module=Landing&action=index">Beranda</a>
        <a href="index.php?module=Homepage&action=index">Katalog mobil</a>
        <a href="index.php?module=Landing&action=index#cara-sewa">Cara sewa</a>
        <?php if ($loggedIn): ?>
            <a href="index.php?module=Profile&action=index">Pesanan saya</a>
            <a href="index.php?module=Auth&action=logout">Keluar</a>
        <?php else: ?>
            <a href="index.php?module=Auth&action=login">Masuk</a>
            <a href="index.php?module=Auth&action=register">Daftar akun</a>
        <?php endif; ?>
    </div>
</header>

<script>
    // Tema: disimpan di localStorage, sinkron antar-tab.
    window.toggleGlobalTheme = window.toggleGlobalTheme || function () {
        const isDark = document.documentElement.classList.toggle('dark');
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        document.dispatchEvent(new CustomEvent('themechange', { detail: { dark: isDark } }));
    };

    // Ikon tema mengikuti kondisi awal.
    window.syncThemeIcon = window.syncThemeIcon || function () {
        const icon = document.getElementById('global-theme-icon');
        if (!icon) return;
        const dark = document.documentElement.classList.contains('dark');
        icon.className = dark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        icon.style.color = dark ? 'var(--warn)' : '';
    };
    document.addEventListener('DOMContentLoaded', syncThemeIcon);

    // Sinkron antar-tab.
    window.addEventListener('storage', function (e) {
        if (e.key !== 'theme') return;
        const dark = e.newValue === 'dark';
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        if (typeof syncThemeIcon === 'function') syncThemeIcon();
    });
</script>
<script src="assets/js/ui.js" defer></script>