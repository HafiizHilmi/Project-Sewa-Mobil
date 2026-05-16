<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>SewaMobil - Landing</title>
=======
    <title>SewaMobil - Mudah dan Cepat</title>
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1f2937;
        }
        .text-brand { color: #004aad; }
        .bg-brand { background-color: #004aad; }
        .btn-brand {
            background-color: #004aad;
            color: white;
            border: none;
        }
        .btn-brand:hover {
            background-color: #003680;
            color: white;
        }
        .bg-light-blue { background-color: #f5f7fc; }
        .text-muted-custom { color: #6b7280; font-size: 0.875rem; }
        
<<<<<<< HEAD
=======
        /* Card Tweaks */
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
        .card-custom {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        }
        .card-custom:hover { transform: translateY(-5px); }
        .badge-custom {
            background-color: #f3f4f6;
            color: #4b5563;
            font-weight: 500;
            padding: 0.4em 0.8em;
        }
        
<<<<<<< HEAD
=======
        /* Icons */
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
        .icon-circle {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
<<<<<<< HEAD

=======
        /* Avatar Testi */
>>>>>>> eac36ec1b71714b1858d82a988947fabe27583c6
        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #eef2ff;
            color: #004aad;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .navbar {
            box-shadow: 0 4px 20px #00000011;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-brand fs-4" href="#">SewaMobil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link fw-semibold text-dark" href="#">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-brand rounded-pill px-4 fw-semibold" href="#">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container py-lg-5">
            <div class="row align-items-center flex-column-reverse flex-lg-row">
                <div class="col-lg-6 pe-lg-5 mt-4 mt-lg-0">
                    <h1 class="fw-bold mb-3" style="font-size: 3rem; line-height: 1.2;">
                        Rental Mobil di Surabaya dengan <span class="text-brand">Mudah</span> dan <span class="text-brand">Cepat</span>
                    </h1>
                    <p class="text-muted-custom fs-6 mb-4 col-lg-8">
                        Tersedia berbagai mobil untuk mencukupi kebutuhan mu sehari-hari.
                    </p>
                    <a href="#" class="btn btn-brand rounded-pill px-4 py-2 fw-semibold">Lihat Katalog</a>
                </div>
                <div class="col-lg-6">
                    <img src="../public/assets/images/avanza_b1242_dfr.jpeg" alt="Avanza" class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover" style="height: 350px;">
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light-blue">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Mobil Terlaris</h3>
                    <p class="text-muted-custom mb-0">Mobil dengan penyewaan terbanyak bulan ini.</p>
                </div>
                <a href="#" class="text-brand text-decoration-none fw-semibold">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-custom p-3 h-100">
                        <img src="../public/assets/images/avanza_b1242_dfr.jpeg" class="card-img-top rounded-3 mb-3 object-fit-cover" alt="Avanza" style="height: 180px;">
                        <div class="card-body p-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0">Toyota Avanza 2024</h6>
                                <div class="text-end">
                                    <span class="fw-bold mb-0 d-block">Rp350.000 <span class="text-muted fw-normal" style="font-size: 0.75rem;">/hari</span></span>
                                </div>
                            </div>
                            <p class="text-muted-custom" style="font-size: 0.75rem;">MPV</p>
                            
                            <div class="mb-4">
                                <span class="badge badge-custom rounded-pill me-1">Bensin</span>
                                <span class="badge badge-custom rounded-pill">7 Penumpang</span>
                            </div>
                            
                            <a href="#" class="btn btn-brand w-100 rounded-3 fw-semibold">Sewa Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-3 h-100">
                        <img src="../public/assets/images/avanza_b1242_dfr.jpeg" class="card-img-top rounded-3 mb-3 object-fit-cover" alt="Avanza" style="height: 180px;">
                        <div class="card-body p-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0">Toyota Avanza 2024</h6>
                                <div class="text-end">
                                    <span class="fw-bold mb-0 d-block">Rp350.000 <span class="text-muted fw-normal" style="font-size: 0.75rem;">/hari</span></span>
                                </div>
                            </div>
                            <p class="text-muted-custom" style="font-size: 0.75rem;">MPV</p>
                            
                            <div class="mb-4">
                                <span class="badge badge-custom rounded-pill me-1">Bensin</span>
                                <span class="badge badge-custom rounded-pill">7 Penumpang</span>
                            </div>
                            
                            <a href="#" class="btn btn-brand w-100 rounded-3 fw-semibold">Sewa Sekarang</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom p-3 h-100">
                        <img src="../public/assets/images/avanza_b1242_dfr.jpeg" class="card-img-top rounded-3 mb-3 object-fit-cover" alt="Avanza" style="height: 180px;">
                        <div class="card-body p-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0">Toyota Avanza 2024</h6>
                                <div class="text-end">
                                    <span class="fw-bold mb-0 d-block">Rp350.000 <span class="text-muted fw-normal" style="font-size: 0.75rem;">/hari</span></span>
                                </div>
                            </div>
                            <p class="text-muted-custom" style="font-size: 0.75rem;">MPV</p>
                            
                            <div class="mb-4">
                                <span class="badge badge-custom rounded-pill me-1">Bensin</span>
                                <span class="badge badge-custom rounded-pill">7 Penumpang</span>
                            </div>
                            
                            <a href="#" class="btn btn-brand w-100 rounded-3 fw-semibold">Sewa Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h3 class="fw-bold">Kenapa memilih <span class="text-brand">SewaMobil</span></h3>
                <p class="text-muted-custom">SewaMobil menyediakan berbagai mobil yang dapat memenuhi kebutuhan penyewa secara lengkap.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="mx-auto bg-brand text-white rounded-circle icon-circle mb-3">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <h6 class="fw-bold" style="font-size: 0.9rem;">Berbagai Pilihan Tipe Mobil</h6>
                        <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">Anda dapat memilih berbagai tipe mobil yang selaras dengan keinginan Anda.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="mx-auto bg-brand text-white rounded-circle icon-circle mb-3">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h6 class="fw-bold" style="font-size: 0.9rem;">CS 24/7</h6>
                        <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">CS Kami siap membantu Anda kapanpun di manapun.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="mx-auto bg-brand text-white rounded-circle icon-circle mb-3">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h6 class="fw-bold" style="font-size: 0.9rem;">Harga Bervariasi</h6>
                        <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">Kami memiliki berbagai kategori mobil yang bisa dipilih sesuai dengan budget anda.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="mx-auto bg-brand text-white rounded-circle icon-circle mb-3">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h6 class="fw-bold" style="font-size: 0.9rem;">Keamanan Terjamin</h6>
                        <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">Kami melakukan pengecekan sebelum mengirimkan mobil yang akan disewa.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light-blue">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h3 class="fw-bold">Testimoni Penyewa</h3>
                <p class="text-muted-custom">Apa kata mereka tentang rental mobil kami</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-custom h-100 p-4 d-flex flex-column">
                        <p class="text-dark mb-4 flex-grow-1" style="font-size: 0.8rem; line-height: 1.6;">
                            "Rental mobil paling gacor di Surabaya. Adminnya sangat ramah, ketika mobil memiliki kendala langsung ditangani dengan cepat. Interior mobil juga bersih seperti mobil baru, wanginya sangat semerbak. Dapat bonus air minum botolan sama tissue 1 pack untuk menemani saat kita sedang berkendara."
                        </p>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">WB</div>
                            <div>
                                <h6 class="fw-bold mb-0" style="font-size: 0.85rem;">Windah Basudara</h6>
                                <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">Content-Creator</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom h-100 p-4 d-flex flex-column">
                        <p class="text-dark mb-4 flex-grow-1" style="font-size: 0.8rem; line-height: 1.6;">
                            "Rental mobil paling gacor di Surabaya. Adminnya sangat ramah, ketika mobil memiliki kendala langsung ditangani dengan cepat. Interior mobil juga bersih seperti mobil baru, wanginya sangat semerbak. Dapat bonus air minum botolan sama tissue 1 pack untuk menemani saat kita sedang berkendara."
                        </p>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">WB</div>
                            <div>
                                <h6 class="fw-bold mb-0" style="font-size: 0.85rem;">Windah Basudara</h6>
                                <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">Content-Creator</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom h-100 p-4 d-flex flex-column">
                        <p class="text-dark mb-4 flex-grow-1" style="font-size: 0.8rem; line-height: 1.6;">
                            "Rental mobil paling gacor di Surabaya. Adminnya sangat ramah, ketika mobil memiliki kendala langsung ditangani dengan cepat. Interior mobil juga bersih seperti mobil baru, wanginya sangat semerbak. Dapat bonus air minum botolan sama tissue 1 pack untuk menemani saat kita sedang berkendara."
                        </p>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">WB</div>
                            <div>
                                <h6 class="fw-bold mb-0" style="font-size: 0.85rem;">Windah Basudara</h6>
                                <p class="text-muted-custom mb-0" style="font-size: 0.7rem;">Content-Creator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white py-4 border-top">
        <div class="container">
            <div class="row align-items-center flex-column flex-md-row">
                <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                    <a class="fw-bold text-brand fs-5 text-decoration-none" href="#">SewaMobil</a>
                </div>
                <div class="col-md-6 text-center mb-3 mb-md-0">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted-custom text-decoration-none hover-dark">Privacy Policy</a></li>
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted-custom text-decoration-none hover-dark">Terms of Service</a></li>
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted-custom text-decoration-none hover-dark">Safety Standards</a></li>
                        <li class="list-inline-item mx-2"><a href="#" class="text-muted-custom text-decoration-none hover-dark">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-3 text-center text-md-end text-muted-custom" style="font-size: 0.75rem;">
                    &copy; 2026 sewamobil. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>