<?php
// Pastikan path ke database benar
require_once '../Config/database.php';

$checkIsType = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'is_type'");
if (mysqli_num_rows($checkIsType) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN is_type TINYINT(1) NOT NULL DEFAULT 0");
}

$checkTypeKey = mysqli_query($conn, "SHOW COLUMNS FROM cars LIKE 'type_key'");
if (mysqli_num_rows($checkTypeKey) === 0) {
    mysqli_query($conn, "ALTER TABLE cars ADD COLUMN type_key VARCHAR(255) NULL");
    mysqli_query($conn, "UPDATE cars SET type_key = CONCAT(make,'|',model,'|',year,'|',fuel_type,'|',engine_capacity,'|',seats,'|',transmission,'|',category,'|',price_per_day)");
}

// ==========================================
// [ PROSES SIMPAN / EDIT KENDARAAN ]
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
    
    // Variabel untuk menampung nama file gambar
    $imageName = "";

    // Logika Upload Foto
    if (isset($_FILES['foto_mobil']) && $_FILES['foto_mobil']['error'] == 0) {
        // Path ke folder penyimpanan gambar kamu
        $target_dir = "../public/assets/images/";
        
        // Membuat nama file unik dengan timestamp agar tidak ada nama gambar yang bentrok
        $imageName = time() . "_" . basename($_FILES["foto_mobil"]["name"]);
        $target_file = $target_dir . $imageName;
        
        // Pindahkan file dari memori sementara ke folder tujuan
        move_uploaded_file($_FILES["foto_mobil"]["tmp_name"], $target_file);
    }

    if (empty($id)) {
        // [ MODE TAMBAH DATA BARU ]
        // Jika tidak ada gambar yang diupload, imageName akan kosong
        $sql = "INSERT INTO cars (make, model, year, number_plate, chassis_number, category, price_per_day, fuel_type, engine_capacity, seats, stock, transmission, image, is_type, type_key) 
            VALUES ('".mysqli_real_escape_string($conn,$make)."', '".mysqli_real_escape_string($conn,$model)."', '".mysqli_real_escape_string($conn,$year)."', '".mysqli_real_escape_string($conn,$plate)."', '".mysqli_real_escape_string($conn,$chassis)."', '".mysqli_real_escape_string($conn,$category)."', '".mysqli_real_escape_string($conn,$price)."', '".mysqli_real_escape_string($conn,$fuel)."', '".mysqli_real_escape_string($conn,$engine)."', '".mysqli_real_escape_string($conn,$passengers)."', '".mysqli_real_escape_string($conn,$stock)."', '".mysqli_real_escape_string($conn,$transmission)."', '".mysqli_real_escape_string($conn,$imageName)."', '".mysqli_real_escape_string($conn,$is_type)."', '".mysqli_real_escape_string($conn,$type_key)."')";
    } else {
        // [ MODE EDIT DATA ]
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
                // Jika user mengupload foto BARU saat edit
                $sql = "UPDATE cars SET make='".mysqli_real_escape_string($conn,$make)."', model='".mysqli_real_escape_string($conn,$model)."', year='".mysqli_real_escape_string($conn,$year)."', number_plate='".mysqli_real_escape_string($conn,$plate)."', chassis_number='".mysqli_real_escape_string($conn,$chassis)."', category='".mysqli_real_escape_string($conn,$category)."', 
                    price_per_day='".mysqli_real_escape_string($conn,$price)."', fuel_type='".mysqli_real_escape_string($conn,$fuel)."', engine_capacity='".mysqli_real_escape_string($conn,$engine)."', seats='".mysqli_real_escape_string($conn,$passengers)."', stock='".mysqli_real_escape_string($conn,$stock)."', 
                    transmission='".mysqli_real_escape_string($conn,$transmission)."', image='".mysqli_real_escape_string($conn,$imageName)."', type_key='".mysqli_real_escape_string($conn,$type_key)."' WHERE id='".mysqli_real_escape_string($conn,$id)."'";
            } else {
                // Jika user TIDAK mengganti foto (hanya edit teks)
                $sql = "UPDATE cars SET make='".mysqli_real_escape_string($conn,$make)."', model='".mysqli_real_escape_string($conn,$model)."', year='".mysqli_real_escape_string($conn,$year)."', number_plate='".mysqli_real_escape_string($conn,$plate)."', chassis_number='".mysqli_real_escape_string($conn,$chassis)."', category='".mysqli_real_escape_string($conn,$category)."', 
                    price_per_day='".mysqli_real_escape_string($conn,$price)."', fuel_type='".mysqli_real_escape_string($conn,$fuel)."', engine_capacity='".mysqli_real_escape_string($conn,$engine)."', seats='".mysqli_real_escape_string($conn,$passengers)."', stock='".mysqli_real_escape_string($conn,$stock)."', 
                    transmission='".mysqli_real_escape_string($conn,$transmission)."', type_key='".mysqli_real_escape_string($conn,$type_key)."' WHERE id='".mysqli_real_escape_string($conn,$id)."'";
            }
        }
    }

    // Eksekusi query
    mysqli_query($conn, $sql);
    
    session_start();
    $_SESSION['flash_msg'] = "Data kendaraan berhasil disimpan!";

    // KUNCI PERBAIKAN EDIT: Tambahkan page=cars SEBELUM open_type_key
    if (!empty($type_key)) {
        header("Location: index.php?page=cars");
    }
    exit();
}

// ==========================================
// [ PROSES HAPUS KENDARAAN ]
// ==========================================
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_is_type = isset($_GET['delete_is_type']) ? intval($_GET['delete_is_type']) : 0;
    $delete_type_key = isset($_GET['delete_type_key']) ? mysqli_real_escape_string($conn, $_GET['delete_type_key']) : '';
    
    session_start();

    // JIKA YANG DIHAPUS ADALAH PARENT (Beserta semua anaknya)
    if ($delete_is_type === 1 && !empty($delete_type_key)) {
        
        // 1. Hapus semua file gambar fisik (milik parent maupun child)
        $queryImg = mysqli_query($conn, "SELECT image FROM cars WHERE type_key='$delete_type_key'");
        while($row = mysqli_fetch_assoc($queryImg)) {
            if(!empty($row['image'])) {
                $imgPath = "../public/assets/images/" . $row['image'];
                if(file_exists($imgPath)) { unlink($imgPath); }
            }
        }
        
        // 2. Hapus SEMUA mobil yang memiliki type_key tersebut dari database
        $hapus_berhasil = mysqli_query($conn, "DELETE FROM cars WHERE type_key='$delete_type_key'");
        
        if ($hapus_berhasil) {
            $_SESSION['flash_msg'] = "Tipe mobil beserta seluruh unitnya berhasil dihapus!";
        } else {
            $_SESSION['flash_msg'] = "Gagal menghapus: Pastikan mobil ini tidak sedang terikat dengan pesanan aktif.";
        }

    } 
    // JIKA YANG DIHAPUS HANYA SATU UNIT SAJA (Child)
    else {
        
        // Hapus file gambar fisiknya saja
        $queryImg = mysqli_query($conn, "SELECT image FROM cars WHERE id='$id'");
        if($row = mysqli_fetch_assoc($queryImg)) {
            if(!empty($row['image'])) {
                $imgPath = "../public/assets/images/" . $row['image'];
                if(file_exists($imgPath)) { unlink($imgPath); }
            }
        }

        // Hapus spesifik 1 data dari database
        $hapus_berhasil = mysqli_query($conn, "DELETE FROM cars WHERE id='$id'");
        
        if ($hapus_berhasil) {
            $_SESSION['flash_msg'] = "Unit kendaraan berhasil dihapus!";
        } else {
            $_SESSION['flash_msg'] = "Gagal menghapus: Mobil ini mungkin sedang digunakan dalam transaksi.";
        }
    }

    header("Location: index.php?page=cars");
    exit();
}
?>