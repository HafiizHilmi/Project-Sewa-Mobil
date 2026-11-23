<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../../../include/site.php';
$site = site_settings();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar akun — <?= htmlspecialchars($site['name']) ?></title>
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

    <main class="auth-wrap">
        <div class="auth-shell">
            <div class="auth-form">
                <span class="eyebrow">Akun pelanggan</span>
                <h1 style="margin-top:12px">Daftar</h1>
                <p>Buat akun sekali, lalu unggah KTP &amp; SIM di halaman profil untuk mulai menyewa.</p>

                <?php if ($flash): ?>
                    <div class="notice notice--danger u-mt-24" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span><?= htmlspecialchars($flash) ?></span>
                    </div>
                <?php endif; ?>

                <form action="index.php?module=Auth&action=processRegister" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="field">
                        <label for="name">Nama lengkap</label>
                        <input class="input" type="text" id="name" name="name" placeholder="Sesuai KTP" autocomplete="name" required>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="email">Alamat email</label>
                            <input class="input" type="email" id="email" name="email" placeholder="nama@email.com" autocomplete="email" required>
                        </div>
                        <div class="field">
                            <label for="phone">Nomor HP</label>
                            <input class="input" type="tel" id="phone" name="phone" placeholder="0812xxxxxxx" autocomplete="tel" required>
                        </div>
                    </div>

                    <div class="form-grid form-grid--2">
                        <div class="field">
                            <label for="password">Password</label>
                            <input class="input" type="password" id="password" name="password" placeholder="Min. 6 karakter" autocomplete="new-password" required>
                        </div>
                        <div class="field">
                            <label for="password_confirm">Ulangi password</label>
                            <input class="input" type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block btn--lg">
                        Buat akun <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <p class="auth-switch">
                    Sudah punya akun? <a href="index.php?module=Auth&action=login">Masuk</a>
                </p>
            </div>

            <aside class="auth-aside">
                <img src="assets/images/cars/toyota-innova-zenix-2024.jpg" alt="Armada <?= htmlspecialchars($site['name']) ?>">
                <div class="auth-aside-text">
                    <p>Verifikasi KTP &amp; SIM cukup sekali, berlaku untuk semua pesanan berikutnya.</p>
                    <span><?= $site['email'] ?> &middot; <?= $site['phone'] ?></span>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>