<?php
// system/db-connect.php
// Berisi kode untuk membuat koneksi ke database MySQL  (kalkulator_zakat).

$host = "localhost"; // atau alamat host database 
$username = "root"; // ganti dengan username database 
$password = ""; // ganti dengan password database 
$database_name = "kalkulator_zakat";

// Membuat koneksi menggunakan mysqli
$conn = new mysqli($host, $username, $password, $database_name);

// Periksa koneksi
if ($conn->connect_error) {
    // Hentikan eksekusi dan tampilkan pesan error jika koneksi gagal.
    // Dalam produksi, sebaiknya log error ini dan tampilkan pesan yang lebih ramah pengguna.
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Mengatur karakter set ke utf8mb4 untuk mendukung berbagai karakter
if (!$conn->set_charset("utf8mb4")) {
}

// Koneksi berhasil. Variabel $conn sekarang bisa digunakan di file lain.
// Contoh: require_once 'system/db-connect.php';
//         $conn->query(...);
?>
