<?php
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
    </style>
</head>
<body>

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
                                    <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
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

    <footer class="bg-white py-4 border-top mt-auto">
        <div class="container">
            <div class="row align-items-center flex-column flex-md-row text-center text-md-start">
                <div class="col-md-3 mb-3 mb-md-0">
                    <a class="fw-bold text-brand fs-5 text-decoration-none" href="#">SewaMobil</a>
                </div>
                <div class="col-md-6 mb-3 mb-md-0 text-center">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted text-decoration-none" style="font-size: 0.8rem;">Privacy Policy</a></li>
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted text-decoration-none" style="font-size: 0.8rem;">Terms of Service</a></li>
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted text-decoration-none" style="font-size: 0.8rem;">Safety Standards</a></li>
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted text-decoration-none" style="font-size: 0.8rem;">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end text-muted" style="font-size: 0.75rem;">
                    &copy; 2026 sewamobil. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>