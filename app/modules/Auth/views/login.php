<?php 
// Buka session dan buat keamanan CSRF (Wajib ditaruh paling atas sebelum HTML)
if (session_status() !== PHP_SESSION_ACTIVE) session_start(); 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SewaMobil</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .text-brand { color: #004aad; }
        .bg-brand { background-color: #004aad; }
        .btn-brand { background-color: #004aad; color: white; }
        .btn-brand:hover { background-color: #003680; color: white; }
        
        .auth-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .form-control-custom {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        .form-control-custom:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 74, 173, 0.1);
            border-color: #004aad;
            background-color: #ffffff;
        }
        .image-placeholder {
            background-color: #e2e8f0;
            min-height: 100%;
        }
        html.dark body {
            background-color: #020617;
            color: #f8fafc;
        }
        html.dark .navbar {
            background-color: #0f172a !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        }
        html.dark .navbar-brand,
        html.dark .text-brand { color: #93c5fd !important; }
        html.dark .auth-card { background-color: #111827 !important; }
        html.dark .form-control-custom { background-color: #0f172a !important; border-color: #334155 !important; color: #f8fafc; }
        html.dark .form-control-custom:focus { background-color: #111827 !important; box-shadow: 0 0 0 0.2rem rgba(56,189,248,0.2); border-color: #3b82f6 !important; }
        html.dark .btn-brand { background-color: #2563eb !important; }
        html.dark .btn-brand:hover { background-color: #1d4ed8 !important; }
        html.dark .text-muted { color: #94a3b8 !important; }
        html.dark .image-placeholder { background-color: #111827 !important; }
    </style>
</head>
<body>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div id="alert-sukses" class="alert alert-success text-center fw-semibold shadow-sm" 
             style="position: fixed; top: 0; left: 0; width: 100%; z-index: 9999; border-radius: 0; margin: 0; transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const alertSukses = document.getElementById("alert-sukses");
                if (alertSukses) {
                    setTimeout(function() {
                        // Animasi transparan dan meluncur naik ke atas
                        alertSukses.style.opacity = "0";
                        alertSukses.style.transform = "translateY(-100%)";
                        
                        setTimeout(function() {
                            alertSukses.style.display = "none";
                        }, 500);
                    }, 3000); // Hilang otomatis dalam 3 detik
                }
            });
        </script>
    <?php endif; ?>
    <nav class="navbar navbar-light bg-white py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-brand fs-4" href="index.php">SewaMobil</a>
        </div>
    </nav>

    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card auth-card">
                        <div class="row g-0">
                            <div class="col-md-6 p-5">
                                <h3 class="fw-bold mb-1">Selamat Datang</h3>
                                <p class="text-muted mb-4" style="font-size: 0.9rem;">Masuk untuk melanjutkan.</p>
                                
                                <?php if (!empty($_SESSION['flash'])): ?>
                                    <div class="alert alert-danger" style="font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                                        <?= htmlspecialchars($_SESSION['flash']) ?>
                                    </div>
                                    <?php unset($_SESSION['flash']); ?>
                                <?php endif; ?>
                                <form action="index.php?module=Auth&action=processLogin" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" style="font-size: 0.9rem;">Alamat Email</label>
                                        <input type="email" name="email" class="form-control form-control-custom" placeholder="Masukkan email anda" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold" style="font-size: 0.9rem;">Password</label>
                                        <input type="password" name="password" class="form-control form-control-custom" placeholder="Masukkan password anda" required>
                                    </div>
                                    <button type="submit" class="btn btn-brand w-100 py-2 mb-3 fw-semibold rounded-3">Masuk <span aria-hidden="true">&rarr;</span></button>
                                </form>
                                
                                <div class="text-center mt-2">
                                    <span class="text-muted" style="font-size: 0.85rem;">Belum mempunyai akun? 
                                        <a href="index.php?module=Auth&action=register" class="text-brand text-decoration-none fw-semibold">Buat disini</a>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center image-placeholder">
                                <h2 class="fw-bold text-dark tracking-wide">INI GAMBAR</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>