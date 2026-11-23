<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../../../include/site.php';
$site = site_settings();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — <?= htmlspecialchars($site['name']) ?></title>
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            var dark = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <?php $navActive = ''; include __DIR__ . '/../../../../include/nav.php'; ?>

    <?php if ($flashSuccess): ?>
        <div class="flash" id="flashOk"><?= htmlspecialchars($flashSuccess) ?></div>
        <script>
            setTimeout(function () {
                var el = document.getElementById('flashOk');
                if (!el) return;
                el.style.opacity = '0';
                el.style.transform = 'translate(-50%, -12px)';
                setTimeout(function () { el.remove(); }, 400);
            }, 3200);
        </script>
    <?php endif; ?>

    <main class="auth-wrap">
        <div class="auth-shell">
            <div class="auth-form">
                <span class="eyebrow">Akun pelanggan</span>
                <h1 style="margin-top:12px">Masuk</h1>
                <p>Lanjutkan pemesanan, pantau status sewa, dan kelola dokumenmu.</p>

                <?php if ($flash): ?>
                    <div class="notice notice--danger u-mt-24" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span><?= htmlspecialchars($flash) ?></span>
                    </div>
                <?php endif; ?>

                <form action="index.php?module=Auth&action=processLogin" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="field">
                        <label for="email">Alamat email</label>
                        <input class="input" type="email" id="email" name="email" placeholder="nama@email.com" autocomplete="email" required>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block btn--lg">
                        Masuk <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <p class="auth-switch">
                    Belum punya akun? <a href="index.php?module=Auth&action=register">Daftar sekarang</a>
                </p>
            </div>

            <aside class="auth-aside">
                <img src="assets/images/cars/mitsubishi-pajero-sport-2023.jpg" alt="Armada <?= htmlspecialchars($site['name']) ?>">
                <div class="auth-aside-text">
                    <p>Tiga langkah: pilih mobil, verifikasi dokumen, lalu bayar.</p>
                    <span><?= htmlspecialchars($site['address']) ?></span>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>