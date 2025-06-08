<?php
// system/proses-edit-penyaluran.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_admin_login(); // Pastikan hanya admin yang bisa akses
// $admin_id = get_current_user_id(); // Bisa digunakan untuk logging atau created_by jika diperlukan

$message = "";
$success = false;
$id_penyaluran_asli_redirect = ''; // Untuk redirect jika error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_penyaluran_asli = trim($conn->real_escape_string($_POST['id_penyaluran_asli'] ?? ''));
    $id_penyaluran_asli_redirect = $id_penyaluran_asli; // Simpan untuk redirect jika error

    $id_pembayaran_terkait_input = trim($conn->real_escape_string($_POST['id_pembayaran_terkait'] ?? ''));
    $id_pembayaran_terkait = !empty($id_pembayaran_terkait_input) ? $id_pembayaran_terkait_input : NULL;
    
    $deskripsi_penyaluran = trim($conn->real_escape_string($_POST['deskripsi_penyaluran'] ?? ''));
    $tanggal_penyaluran = $_POST['tanggal_penyaluran'] ?? '';
    
    $nominal_penyaluran_input = trim($_POST['nominal_penyaluran'] ?? '');
    $nominal_penyaluran = !empty($nominal_penyaluran_input) ? floatval($nominal_penyaluran_input) : NULL;
    
    $dokumentasi_lama = $_POST['dokumentasi_lama'] ?? null;
    $dokumentasi_filename_update = $dokumentasi_lama; // Default ke file lama

    if (empty($id_penyaluran_asli) || empty($deskripsi_penyaluran) || empty($tanggal_penyaluran)) {
        $message = "ID Penyaluran, Deskripsi, dan Tanggal Penyaluran wajib diisi.";
    } else {
        // Proses upload file dokumentasi baru jika ada
        if (!empty($_FILES['dokumentasi_penyaluran']['name'])) {
            $target_dir = "../uploads/dokumentasi-penyaluran/";
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true) && !is_dir($target_dir)) {
                     $message = "Gagal membuat direktori upload.";
                     // Langsung redirect dengan error karena ini masalah server
                     $_SESSION['penyaluran_error'] = $message;
                     header('Location: ../edit-penyaluran.php?id=' . urlencode($id_penyaluran_asli_redirect) . '&status=error_upload_dir');
                     exit;
                }
            }

            $original_filename_doc_new = basename($_FILES["dokumentasi_penyaluran"]["name"]);
            $docFileType_new = strtolower(pathinfo($original_filename_doc_new, PATHINFO_EXTENSION));
            $new_filename_base = "doc_" . $id_penyaluran_asli . "_" . time(); // Menambahkan ID penyaluran dan timestamp
            $dokumentasi_filename_update = $new_filename_base . "." . $docFileType_new;
            $target_file_doc_new = $target_dir . $dokumentasi_filename_update;
            $uploadOkDocNew = 1;

            $allowed_types_doc = ['jpg', 'jpeg', 'png', 'pdf'];
            if(!in_array($docFileType_new, $allowed_types_doc)) {
                $message = "Maaf, hanya file JPG, JPEG, PNG & PDF yang diizinkan untuk dokumentasi baru.";
                $uploadOkDocNew = 0;
            }
            if ($_FILES["dokumentasi_penyaluran"]["size"] > 5000000) { // 5MB
                $message = "Maaf, ukuran file dokumentasi baru Anda terlalu besar (maks 5MB).";
                $uploadOkDocNew = 0;
            }

            if ($uploadOkDocNew == 1) {
                if (move_uploaded_file($_FILES["dokumentasi_penyaluran"]["tmp_name"], $target_file_doc_new)) {
                    // File baru berhasil diupload, hapus file lama jika ada dan berbeda
                    if ($dokumentasi_lama && $dokumentasi_lama !== $dokumentasi_filename_update && file_exists($target_dir . $dokumentasi_lama)) {
                        unlink($target_dir . $dokumentasi_lama);
                    }
                } else {
                    $message = "Maaf, terjadi error saat mengupload file dokumentasi baru Anda.";
                    $uploadOkDocNew = 0; 
                    $dokumentasi_filename_update = $dokumentasi_lama; // Gagal upload, kembali ke file lama
                }
            } else {
                 $dokumentasi_filename_update = $dokumentasi_lama; // Gagal validasi, kembali ke file lama
            }
        }

        // Lanjutkan hanya jika tidak ada error dari proses upload (jika ada file baru)
        if (empty($message) || (!empty($_FILES['dokumentasi_penyaluran']['name']) && $uploadOkDocNew == 1) || empty($_FILES['dokumentasi_penyaluran']['name'])) {
            // Update data di database
            $stmt = $conn->prepare("UPDATE penyaluran_zakat SET id_pembayaran_terkait = ?, deskripsi_penyaluran = ?, tanggal_penyaluran = ?, nominal_penyaluran = ?, dokumentasi = ? WHERE penyaluran_id = ?");
            
            if ($stmt) {
                // Bind parameter: s (string), d (double/decimal), i (integer)
                // Jika $nominal_penyaluran adalah NULL, mysqli akan menanganinya dengan benar jika tipe 'd' digunakan
                $stmt->bind_param("sssdsi", $id_pembayaran_terkait, $deskripsi_penyaluran, $tanggal_penyaluran, $nominal_penyaluran, $dokumentasi_filename_update, $id_penyaluran_asli);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $success = true;
                        $message = "Data penyaluran ID " . htmlspecialchars($id_penyaluran_asli) . " berhasil diperbarui.";
                    } else {
                        // Tidak ada baris yang terpengaruh, bisa jadi data sama atau ID tidak ditemukan (meskipun seharusnya ditemukan)
                        $success = true; // Anggap sukses jika tidak ada error, meskipun tidak ada perubahan
                        $message = "Tidak ada perubahan data atau data penyaluran ID " . htmlspecialchars($id_penyaluran_asli) . " tidak memerlukan pembaruan.";
                    }
                } else {
                    $message = "Gagal memperbarui data penyaluran: " . $stmt->error;
                    error_log("Error update penyaluran: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $message = "Gagal menyiapkan statement update: " . $conn->error;
                error_log("Error prepare statement update penyaluran: " . $conn->error);
            }
        }
    }
} else {
    // Jika bukan metode POST, arahkan ke halaman kelola
    $_SESSION['penyaluran_error'] = "Akses tidak sah.";
    header('Location: ../kelola-penyaluran.php'); 
    exit;
}

$conn->close();

if ($success) {
    $_SESSION['penyaluran_message'] = $message;
    header('Location: ../kelola-penyaluran.php?status=success');
} else {
    $_SESSION['penyaluran_error'] = $message;
    // Redirect kembali ke form edit dengan ID agar pengguna bisa memperbaiki
    header('Location: ../edit-penyaluran.php?id=' . urlencode($id_penyaluran_asli_redirect) . '&status=error');
}
exit;
?>
