<?php
// Nyalakan session jika belum aktif untuk cek role
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengaman backend: Tolak akses jika bukan superuser
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superuser') {
    http_response_code(403);
    die("Akses Ditolak! Hanya Superuser yang diizinkan untuk menambah, mengedit, atau menghapus kendaraan.");
}

// Load koneksi database
require_once '../../Config/database.php';

$checkIsType = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'is_type'");
if (mysqli_num_rows($checkIsType) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN is_type TINYINT(1) NOT NULL DEFAULT 0");
}

$checkTypeKey = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'type_key'");
if (mysqli_num_rows($checkTypeKey) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN type_key VARCHAR(255) NULL");
    mysqli_query($conn, "UPDATE cars SET type_key = CONCAT(make,'|',model,'|',year,'|',fuel_type,'|',engine_capacity,'|',seats,'|',transmission,'|',category,'|',price_per_day)");
}

$checkChassis = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'chassis_number'");
if ($checkChassis && mysqli_num_rows($checkChassis) > 0) {
    $colChassis = mysqli_fetch_assoc($checkChassis);
    if (isset($colChassis['Type']) && strpos(strtolower($colChassis['Type']), 'varchar(100)') !== false) {
        mysqli_query($conn, "ALTER TABLE cars MODIFY COLUMN chassis_number VARCHAR(255) NULL");
    }
}

// ==========================================
// Simpan atau edit data mobil
// ==========================================
if (isset($_POST['save_car'])) {
    $id           = isset($_POST['id']) ? $_POST['id'] : '';
    $make         = isset($_POST['make']) ? $_POST['make'] : '';
    $model        = isset($_POST['model']) ? $_POST['model'] : '';
    $year         = isset($_POST['year']) ? $_POST['year'] : '';
    $plate        = isset($_POST['plate']) ? $_POST['plate'] : '';
    $chassis      = isset($_POST['chassis']) ? $_POST['chassis'] : '';
    $category     = isset($_POST['category']) ? $_POST['category'] : '';
    $price        = isset($_POST['price']) ? $_POST['price'] : '';
    $fuel         = isset($_POST['fuel']) ? $_POST['fuel'] : '';
    $engine       = isset($_POST['engine']) ? $_POST['engine'] : '';
    $passengers   = isset($_POST['passengers']) ? $_POST['passengers'] : '';
    $stock        = isset($_POST['stock']) ? intval($_POST['stock']) : 1;
    if ($stock < 0) {
        $stock = 0;
    }
    $transmission = isset($_POST['transmission']) ? $_POST['transmission'] : 'Manual';
    $is_type      = isset($_POST['is_type']) ? intval($_POST['is_type']) : 0;
    $type_key     = isset($_POST['type_key']) ? $_POST['type_key'] : '';
    if ($is_type) {
        $stock = 0;
    }
    
    // Siapkan variabel untuk menampung nama file gambar
    $imageName = "";

    // Proses upload foto mobil
    if (isset($_FILES['foto_mobil']) && $_FILES['foto_mobil']['error'] == 0) {
        // Folder tujuan upload foto
        $target_dir = "../../public/assets/images/";
        
        // Bikin nama file gambar unik dengan timestamp biar tidak bentrok
        $imageName = time() . "_" . basename($_FILES["foto_mobil"]["name"]);
        $target_file = $target_dir . $imageName;
        
        // Pindahkan file foto dari memori sementara ke folder tujuan
        move_uploaded_file($_FILES["foto_mobil"]["tmp_name"], $target_file);
    }

    if (empty($id)) {
        // Mode tambah mobil baru
        // Gunakan string kosong jika tidak ada gambar yang diupload
        $sql = "INSERT INTO cars (make, model, year, number_plate, chassis_number, category, price_per_day, fuel_type, engine_capacity, seats, stock, transmission, image, is_type, type_key) 
            VALUES ('".mysqli_real_escape_string($conn,$make)."', '".mysqli_real_escape_string($conn,$model)."', '".mysqli_real_escape_string($conn,$year)."', '".mysqli_real_escape_string($conn,$plate)."', '".mysqli_real_escape_string($conn,$chassis)."', '".mysqli_real_escape_string($conn,$category)."', '".mysqli_real_escape_string($conn,$price)."', '".mysqli_real_escape_string($conn,$fuel)."', '".mysqli_real_escape_string($conn,$engine)."', '".mysqli_real_escape_string($conn,$passengers)."', '".mysqli_real_escape_string($conn,$stock)."', '".mysqli_real_escape_string($conn,$transmission)."', '".mysqli_real_escape_string($conn,$imageName)."', '".mysqli_real_escape_string($conn,$is_type)."', '".mysqli_real_escape_string($conn,$type_key)."')";
    } else {
        // Mode edit data mobil
        if ($is_type && !empty($id)) {
            $safe_type_key = mysqli_real_escape_string($conn, $type_key);
            $safe_id = mysqli_real_escape_string($conn, $id);
            $oldTypeKey = '';
            $oldTypeRes = mysqli_query($conn, "SELECT type_key FROM cars WHERE id='" . $safe_id . "' LIMIT 1");
            if ($oldTypeRes && mysqli_num_rows($oldTypeRes) > 0) {
                $oldTypeKey = mysqli_fetch_assoc($oldTypeRes)['type_key'];
            }

            $sql = "UPDATE cars SET make='".mysqli_real_escape_string($conn,$make)."', model='".mysqli_real_escape_string($conn,$model)."', year='".mysqli_real_escape_string($conn,$year)."', category='".mysqli_real_escape_string($conn,$category)."', price_per_day='".mysqli_real_escape_string($conn,$price)."', fuel_type='".mysqli_real_escape_string($conn,$fuel)."', engine_capacity='".mysqli_real_escape_string($conn,$engine)."', seats='".mysqli_real_escape_string($conn,$passengers)."', stock='0', transmission='".mysqli_real_escape_string($conn,$transmission)."', type_key='".$safe_type_key."'";
            if ($imageName != "") {
                $sql .= ", image='".mysqli_real_escape_string($conn,$imageName)."'";
            }
            $sql .= " WHERE id='".mysqli_real_escape_string($conn,$id)."'";

            if (!empty($oldTypeKey)) {
                $safeOldKey = mysqli_real_escape_string($conn, $oldTypeKey);
                $sqlChildren = "UPDATE cars SET make='".mysqli_real_escape_string($conn,$make)."', model='".mysqli_real_escape_string($conn,$model)."', year='".mysqli_real_escape_string($conn,$year)."', category='".mysqli_real_escape_string($conn,$category)."', price_per_day='".mysqli_real_escape_string($conn,$price)."', fuel_type='".mysqli_real_escape_string($conn,$fuel)."', engine_capacity='".mysqli_real_escape_string($conn,$engine)."', seats='".mysqli_real_escape_string($conn,$passengers)."', transmission='".mysqli_real_escape_string($conn,$transmission)."', type_key='".$safe_type_key."' WHERE type_key='".$safeOldKey."' AND is_type=0";
                mysqli_query($conn, $sqlChildren);
            }
        } else {
            if ($imageName != "") {
                // Edit data dan perbarui foto dengan yang baru
                $sql = "UPDATE cars SET make='".mysqli_real_escape_string($conn,$make)."', model='".mysqli_real_escape_string($conn,$model)."', year='".mysqli_real_escape_string($conn,$year)."', number_plate='".mysqli_real_escape_string($conn,$plate)."', chassis_number='".mysqli_real_escape_string($conn,$chassis)."', category='".mysqli_real_escape_string($conn,$category)."', 
                    price_per_day='".mysqli_real_escape_string($conn,$price)."', fuel_type='".mysqli_real_escape_string($conn,$fuel)."', engine_capacity='".mysqli_real_escape_string($conn,$engine)."', seats='".mysqli_real_escape_string($conn,$passengers)."', stock='".mysqli_real_escape_string($conn,$stock)."', 
                    transmission='".mysqli_real_escape_string($conn,$transmission)."', image='".mysqli_real_escape_string($conn,$imageName)."', type_key='".mysqli_real_escape_string($conn,$type_key)."' WHERE id='".mysqli_real_escape_string($conn,$id)."'";
            } else {
                // Edit data tanpa mengganti foto yang lama
                $sql = "UPDATE cars SET make='".mysqli_real_escape_string($conn,$make)."', model='".mysqli_real_escape_string($conn,$model)."', year='".mysqli_real_escape_string($conn,$year)."', number_plate='".mysqli_real_escape_string($conn,$plate)."', chassis_number='".mysqli_real_escape_string($conn,$chassis)."', category='".mysqli_real_escape_string($conn,$category)."', 
                    price_per_day='".mysqli_real_escape_string($conn,$price)."', fuel_type='".mysqli_real_escape_string($conn,$fuel)."', engine_capacity='".mysqli_real_escape_string($conn,$engine)."', seats='".mysqli_real_escape_string($conn,$passengers)."', stock='".mysqli_real_escape_string($conn,$stock)."', 
                    transmission='".mysqli_real_escape_string($conn,$transmission)."', type_key='".mysqli_real_escape_string($conn,$type_key)."' WHERE id='".mysqli_real_escape_string($conn,$id)."'";
            }
        }
    }

    // Eksekusi perubahan ke database
    mysqli_query($conn, $sql);
    
    $_SESSION['flash_msg'] = "Data kendaraan berhasil disimpan!";

    // Kembalikan ke halaman daftar mobil
    if (!empty($type_key)) {
        header("Location: ../index.php?page=cars");
    }
    exit();
}

// ==========================================
// Hapus data mobil
// ==========================================
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_is_type = isset($_GET['delete_is_type']) ? intval($_GET['delete_is_type']) : 0;
    $delete_type_key = isset($_GET['delete_type_key']) ? mysqli_real_escape_string($conn, $_GET['delete_type_key']) : '';

    // Jika tipe induk yang dihapus (hapus tipe beserta semua unit fisiknya)
    if ($delete_is_type === 1 && !empty($delete_type_key)) {
        
        // 1. Hapus semua file gambar fisik tipe induk & unit anaknya
        $queryImg = mysqli_query($conn, "SELECT image FROM cars WHERE type_key='$delete_type_key'");
        while($row = mysqli_fetch_assoc($queryImg)) {
            if(!empty($row['image'])) {
                $imgPath = "../../public/assets/images/" . $row['image'];
                if(file_exists($imgPath)) { unlink($imgPath); }
            }
        }
        
        // 2. Hapus semua data mobil dengan tipe tersebut dari database
        $hapus_berhasil = mysqli_query($conn, "DELETE FROM cars WHERE type_key='$delete_type_key'");
        
        if ($hapus_berhasil) {
            $_SESSION['flash_msg'] = "Tipe mobil beserta seluruh unitnya berhasil dihapus!";
        } else {
            $_SESSION['flash_msg'] = "Gagal menghapus: Pastikan mobil ini tidak sedang terikat dengan pesanan aktif.";
        }

    } 
    // Jika hanya satu unit fisik saja yang dihapus
    else {
        
        // Hapus file gambar fisik dari unit tersebut
        $queryImg = mysqli_query($conn, "SELECT image FROM cars WHERE id='$id'");
        if($row = mysqli_fetch_assoc($queryImg)) {
            if(!empty($row['image'])) {
                $imgPath = "../../public/assets/images/" . $row['image'];
                if(file_exists($imgPath)) { unlink($imgPath); }
            }
        }

        // Hapus unit fisik dari database
        $hapus_berhasil = mysqli_query($conn, "DELETE FROM cars WHERE id='$id'");
        
        if ($hapus_berhasil) {
            $_SESSION['flash_msg'] = "Unit kendaraan berhasil dihapus!";
        } else {
            $_SESSION['flash_msg'] = "Gagal menghapus: Mobil ini mungkin sedang digunakan dalam transaksi.";
        }
    }

    header("Location: ../index.php?page=cars");
    exit();
}
?>