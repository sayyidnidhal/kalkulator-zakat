<?php
// system/proses-registrasi.php
require_once 'db-connect.php';
require_once 'session-handler.php'; // Untuk memulai sesi jika ingin langsung login atau menyimpan pesan

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan sanitasi data dari form
    $nama_lengkap = trim($conn->real_escape_string($_POST['nama_lengkap'] ?? ''));
    $username = trim($conn->real_escape_string($_POST['username'] ?? ''));
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $nomor_hp = trim($conn->real_escape_string($_POST['nomor_hp'] ?? ''));
    $password = $_POST['password'] ?? ''; // Jangan trim password, akan di-hash
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi dasar
    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($nomor_hp) || empty($password) || empty($konfirmasi_password)) {
        $message = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal harus 6 karakter.";
    } elseif ($password !== $konfirmasi_password) {
        $message = "Password dan konfirmasi password tidak cocok.";
    } else {
        // Cek apakah username atau email sudah ada
        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $message = "Username atau Email sudah terdaftar.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT); 

                // Simpan ke database
                $stmt_insert = $conn->prepare("INSERT INTO users (nama_lengkap, username, email, password, nomor_hp, role) VALUES (?, ?, ?, ?, ?, 'user')");
                if ($stmt_insert) {
                    $stmt_insert->bind_param("sssss", $nama_lengkap, $username, $email, $hashed_password, $nomor_hp);

                    if ($stmt_insert->execute()) {
                        $success = true;
                        // Simpan data form ke sesi untuk ditampilkan kembali jika ada error, atau hapus jika sukses
                        if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);
                        $_SESSION['reg_success_message'] = "Registrasi berhasil! Silakan login.";
                        header("Location: ../login.php?status=reg_success");
                        exit;
                    } else {
                        $message = "Registrasi gagal. Silakan coba lagi. Error: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                     $message = "Gagal menyiapkan statement insert: " . $conn->error;
                }
            }
            $stmt_check->close();
        } else {
            $message = "Gagal menyiapkan statement check: " . $conn->error;
        }
    }

    if (!$success) {
        // Simpan data form ke sesi agar bisa diisi kembali di halaman registrasi
        $_SESSION['form_data'] = $_POST;
        $_SESSION['reg_error'] = $message;
        header('Location: ../registrasi.php?status=error');
        exit;
    }

} else {
    // Jika bukan metode POST, arahkan ke halaman registrasi
    header('Location: ../registrasi.php');
    exit;
}

$conn->close();
?>
