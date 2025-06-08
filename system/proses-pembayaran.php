<?php
// system/proses-pembayaran.php
require_once 'db-connect.php';
require_once 'session-handler.php'; 

// require_login(); // Sebaiknya pengguna sudah login untuk melakukan pembayaran

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan sanitasi data dari form
    $jenis_zakat_raw = $conn->real_escape_string($_POST['jenis_zakat_raw'] ?? 'Tidak Diketahui');
    $jenis_zakat_display = $conn->real_escape_string($_POST['jenis_zakat_display'] ?? 'Tidak Diketahui');
    $jumlah_zakat_input = $_POST['jumlah_zakat'] ?? '0'; // Bisa berupa angka atau deskripsi
    $id_pengguna_form = isset($_POST['id_pengguna']) ? $conn->real_escape_string($_POST['id_pengguna']) : null;
    $nama_pembayar = trim($conn->real_escape_string($_POST['nama_pembayar'] ?? ''));
    $tgl_bayar = $_POST['tgl_bayar'] ?? date('Y-m-d');
    
    // Tentukan user_id yang akan disimpan
    $user_id_to_save = get_current_user_id(); // Prioritaskan dari sesi
    if (empty($user_id_to_save) && !empty($id_pengguna_form)) {
        // Jika tidak ada sesi, dan ada input manual ID Pengguna (misal untuk pembayaran anonim yang ingin dikaitkan)
        //  mungkin perlu validasi lebih lanjut untuk $id_pengguna_form jika ini diizinkan
        $user_id_to_save = $id_pengguna_form; 
    }
    if (empty($user_id_to_save)) { // Jika masih kosong, set ke NULL
        $user_id_to_save = NULL;
    }


    $bukti_bayar_filename = null;

    // Validasi dasar
    if (empty($nama_pembayar) || empty($tgl_bayar)) {
        $message = "Nama pembayar dan tanggal bayar wajib diisi.";
    } elseif (empty($_FILES['bukti_pembayaran']['name'])) {
        $message = "Bukti pembayaran wajib diunggah.";
    } else {
        // Proses upload file bukti pembayaran
        $target_dir = "../uploads/bukti-pembayaran/"; // Pastikan folder ini ada dan writable
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $original_filename = basename($_FILES["bukti_pembayaran"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $bukti_bayar_filename = uniqid('bukti_', true) . "." . $imageFileType; // Buat nama file unik
        $target_file = $target_dir . $bukti_bayar_filename;
        $uploadOk = 1;

        // Cek apakah file adalah gambar atau PDF
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if(!in_array($imageFileType, $allowed_types)) {
            $message = "Maaf, hanya file JPG, JPEG, PNG & PDF yang diizinkan untuk bukti pembayaran.";
            $uploadOk = 0;
        }

        // Cek ukuran file (misal, maks 2MB)
        if ($_FILES["bukti_pembayaran"]["size"] > 2000000) { // 2MB
            $message = "Maaf, ukuran file  terlalu besar (maks 2MB).";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            // $message sudah diisi dengan error
        } else {
            if (move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
                // File berhasil diupload, simpan data ke database
                
                $stmt = $conn->prepare("INSERT INTO pembayaran_zakat (user_id, nama_pembayar, jenis_zakat_raw, jenis_zakat_display, nominal_bayar, tanggal_bayar, bukti_bayar, status_verifikasi) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING')");
                if ($stmt) {

                    if ($user_id_to_save === NULL) {

                        $stmt->bind_param("issssss", $user_id_to_save, $nama_pembayar, $jenis_zakat_raw, $jenis_zakat_display, $jumlah_zakat_input, $tgl_bayar, $bukti_bayar_filename);
                    } else {
                         $stmt->bind_param("issssss", $user_id_to_save, $nama_pembayar, $jenis_zakat_raw, $jenis_zakat_display, $jumlah_zakat_input, $tgl_bayar, $bukti_bayar_filename);
                    }


                    if ($stmt->execute()) {
                        $success = true;
                        $_SESSION['payment_success_message'] = "Konfirmasi pembayaran  telah berhasil dikirim. Mohon tunggu proses verifikasi.";
                        header("Location: ../dashboard.php?status=payment_success"); // Arahkan ke dashboard pengguna
                        exit;
                    } else {
                        $message = "Gagal menyimpan data pembayaran: " . $stmt->error;
                        // Hapus file jika gagal simpan ke DB
                        if (file_exists($target_file)) unlink($target_file);
                    }
                    $stmt->close();
                } else {
                    $message = "Gagal menyiapkan statement database: " . $conn->error;
                    if (file_exists($target_file)) unlink($target_file); // Hapus file jika statement gagal
                }
            } else {
                $message = "Maaf, terjadi error saat mengupload file .";
            }
        }
    }
} else {
    // Jika bukan metode POST, arahkan ke halaman index
    header('Location: ../index.php');
    exit;
}

$conn->close();

// Jika ada error, kembali ke halaman pembayaran dengan pesan
if (!$success) {
    $_SESSION['payment_error'] = $message;
    // Arahkan kembali ke halaman bayar-zakat.php dengan parameter GET agar data terisi kembali (jika diperlukan)

    header('Location: ../bayar-zakat.php?jenis='.urlencode($jenis_zakat_raw).'&jumlah='.urlencode($jumlah_zakat_input).'&status=error&msg='.urlencode($message));
    exit;
}
?>
