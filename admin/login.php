<?php
session_start();

// 1. Hubungkan ke database beralaskan file config/database.php milikmu
// Cek otomatis posisi folder config agar tidak terjadi error path
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
} else if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
} else {
    // Jika tidak ketemu, gunakan konfigurasi yang kamu siapkan sebagai fallback
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "project_sewa_mobil";
    $conn = mysqli_connect($host, $user, $password, $database);
}

if (!$conn) {
    die("Koneksi ke database gagal! Periksa kembali file config/database.php kamu.");
}

// 2. FITUR OTOMATIS: Membuat tabel 'admins' jika belum ada di database kamu
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('superuser','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Memasukkan akun default Superuser secara otomatis jika baris data belum ada
$checkAdmin = mysqli_query($conn, "SELECT * FROM admins WHERE email = 'ahmad@sewamobilsby.id'");
if (mysqli_num_rows($checkAdmin) == 0) {
    mysqli_query($conn, "INSERT INTO admins (nama, email, password, role) VALUES ('Ahmad Fauzi', 'ahmad@sewamobilsby.id', 'admin123', 'superuser')");
}

// 3. Eksekusi Form Login saat tombol diklik
$error = null;
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM admins WHERE email = '$email'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Cek kecocokan password dengan teks biasa (plain text)
        if ($password === $data['password']) {
            $_SESSION['admin_id'] = $data['id'];
            $_SESSION['admin_nama'] = $data['nama'];
            $_SESSION['admin_role'] = $data['role']; // 'superuser' atau 'staff'
            
            // Berhasil login! Langsung lempar ke index.php
            header("Location: index.php");
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Email tidak ditemukan di sistem!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sewa Mobil SBY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 min-h-screen flex items-center justify-center p-4 transition-colors duration-200">

    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700 p-8">
        
        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                Sewa <span class="text-blue-600">Mobil</span> SBY
            </h1>
            <p class="text-xs text-slate-400 dark:text-slate-400 mt-2 font-medium">Panel Administrasi Log In</p>
        </div>

        <?php if($error): ?>
            <div class="bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 p-3.5 rounded-xl text-xs mb-6 flex items-center border border-rose-100 dark:border-rose-900/30 font-semibold">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 focus:border-blue-500 transition-all placeholder-slate-400"
                    placeholder="ahmad@sewamobilsby.id">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-2.5 text-xs bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 focus:border-blue-500 transition-all placeholder-slate-400"
                    placeholder="••••••••">
            </div>

            <button type="submit" name="login" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-3 rounded-xl transition-colors mt-2 shadow-md shadow-blue-200 dark:shadow-none">
                Masuk Dashboard
            </button>
        </form>

        <div class="mt-8 text-center text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
            &copy; 2026 Sewa Mobil SBY
        </div>
    </div>

</body>
</html>