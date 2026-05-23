<?php
// Pastikan path ke database benar
require_once '../Config/database.php';

// ==========================================
// [ PROSES SIMPAN / EDIT KENDARAAN ]
// ==========================================
if (isset($_POST['save_car'])) {
    $id           = $_POST['id'];
    $make         = $_POST['make']; 
    $plate        = $_POST['plate'];
    $chassis      = $_POST['chassis'];
    $category     = $_POST['category'];
    $price        = $_POST['price'];
    $fuel         = $_POST['fuel'];
    $engine       = $_POST['engine'];
    $passengers   = $_POST['passengers'];
    $transmission = $_POST['transmission'];
    
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
        $sql = "INSERT INTO cars (make, model, number_plate, frame_number, category, price_per_day, fuel_type, engine_capacity, seats, transmission, image) 
                VALUES ('$make', '', '$plate', '$chassis', '$category', '$price', '$fuel', '$engine', '$passengers', '$transmission', '$imageName')";
    } else {
        // [ MODE EDIT DATA ]
        if ($imageName != "") {
            // Jika user mengupload foto BARU saat edit
            $sql = "UPDATE cars SET make='$make', number_plate='$plate', frame_number='$chassis', category='$category', 
                    price_per_day='$price', fuel_type='$fuel', engine_capacity='$engine', seats='$passengers', 
                    transmission='$transmission', image='$imageName' WHERE id='$id'";
        } else {
            // Jika user TIDAK mengganti foto (hanya edit teks)
            $sql = "UPDATE cars SET make='$make', number_plate='$plate', frame_number='$chassis', category='$category', 
                    price_per_day='$price', fuel_type='$fuel', engine_capacity='$engine', seats='$passengers', 
                    transmission='$transmission' WHERE id='$id'";
        }
    }

    // Eksekusi query
    mysqli_query($conn, $sql);
    
    // Kembali ke halaman index (otomatis akan ditangani Alpine.js untuk menampilkan halaman cars)
    header("Location: index.php");
    exit();
}

// ==========================================
// [ PROSES HAPUS KENDARAAN ]
// ==========================================
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    // (Opsional) Hapus file gambar fisik dari folder agar tidak menumpuk
    $query = mysqli_query($conn, "SELECT image FROM cars WHERE id='$id'");
    if($row = mysqli_fetch_assoc($query)) {
        if(!empty($row['image'])) {
            $imgPath = "../public/assets/images/" . $row['image'];
            if(file_exists($imgPath)) {
                unlink($imgPath); // Menghapus file gambar
            }
        }
    }

    // Hapus data dari database
    mysqli_query($conn, "DELETE FROM cars WHERE id='$id'");
    
    header("Location: index.php");
    exit();
}
?>