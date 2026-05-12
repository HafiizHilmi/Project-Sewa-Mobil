<?php 
$host= "localhost";
$user= "root";
$password= "";
$database= "db_rental_mobil";

// membuat koneksi
$conn = mysqli_connect($host, $user, $password, $database);

// cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

echo "Koneksi berhasil";
?>