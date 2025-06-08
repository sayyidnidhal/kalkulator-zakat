<?php
// system/proses-tambah-penyaluran.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_admin_login(); // Hanya admin yang boleh akses
$admin_id = get_current_user_id(); // ID admin yang melakukan input

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ID Penyaluran akan di-generate otomatis oleh database (AUTO_INCREMENT)
    $id_pembayaran_terkait = trim($conn->real_escape_string($_POST['id_pembayaran_terkait'] ?? ''));
    if (empty($id_pembayaran_terkait)) {
        $id_pembayaran_terkait = NULL;
    }
    $deskripsi_penyaluran = trim($conn->real_escape_string($_POST['deskripsi_penyaluran'] ?? ''));
    $tanggal_penyaluran = $_POST['tanggal_penyaluran'] ?? '';
    $nominal_penyaluran_input = trim($_POST['nominal_penyaluran'] ?? '');
    $nominal_penyaluran = !empty($nominal_penyaluran_input) ? floatval($nominal_penyaluran_input) : NULL;
    
    $dokumentasi_filename = null;

    if (empty($deskripsi_penyaluran) || empty($tanggal_penyaluran)) {
        $message = "Deskripsi dan Tanggal Penyaluran wajib diisi.";
    } else {
        // Proses upload file dokumentasi jika ada
        if (!empty($_FILES['dokumentasi_penyaluran']['name'])) {
            $target_dir = "../uploads/dokumentasi-penyaluran/"; // Pastikan folder ini ada dan writable
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $original_filename_doc = basename($_FILES["dokumentasi_penyaluran"]["name"]);
            $docFileType = strtolower(pathinfo($original_filename_doc, PATHINFO_EXTENSION));
            $dokumentasi_filename = "doc_" . uniqid() . "." . $docFileType;
            $target_file_doc = $target_dir . $dokumentasi_filename;
            $uploadOkDoc = 1;

            $allowed_types_doc = ['jpg', 'jpeg', 'png', 'pdf'];
            if(!in_array($docFileType, $allowed_types_doc)) {
                $message = "Maaf, hanya file JPG, JPEG, PNG & PDF yang diizinkan untuk dokumentasi.";
                $uploadOkDoc = 0;
            }
            if ($_FILES["dokumentasi_penyaluran"]["size"] > 5000000) { // 5MB
                $message = "Maaf, ukuran file dokumentasi  terlalu besar (maks 5MB).";
                $uploadOkDoc = 0;
            }

            if ($uploadOkDoc == 1) {
                if (!move_uploaded_file($_FILES["dokumentasi_penyaluran"]["tmp_name"], $target_file_doc)) {
                    $message = "Maaf, terjadi error saat mengupload file dokumentasi .";
                    $uploadOkDoc = 0; // Ti gagal upload
                    $dokumentasi_filename = null; // Jangan simpan nama file jika upload gagal
                }
            } else {
                 $dokumentasi_filename = null; // Jangan simpan nama file jika upload tidak OK
            }
        }

        if (empty($message) || (!empty($_FILES['dokumentasi_penyaluran']['name']) && $uploadOkDoc == 1) || empty($_FILES['dokumentasi_penyaluran']['name'])) {
            // Lanjutkan simpan ke database jika tidak ada error upload atau tidak ada file yang diupload
            $stmt = $conn->prepare("INSERT INTO penyaluran_zakat (id_pembayaran_terkait, deskripsi_penyaluran, tanggal_penyaluran, nominal_penyaluran, dokumentasi, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {

                if ($nominal_penyaluran === NULL) {
                    $stmt->bind_param("sssisi", $id_pembayaran_terkait, $deskripsi_penyaluran, $tanggal_penyaluran, $nominal_penyaluran_null, $dokumentasi_filename, $admin_id);
                    $nominal_penyaluran_null = null; 
                    $nominal_penyaluran_to_save = $nominal_penyaluran ?? 0.00;
                     $stmt->bind_param("sssdsi", $id_pembayaran_terkait, $deskripsi_penyaluran, $tanggal_penyaluran, $nominal_penyaluran_to_save, $dokumentasi_filename, $admin_id);

                } else {
                    $stmt->bind_param("sssdsi", $id_pembayaran_terkait, $deskripsi_penyaluran, $tanggal_penyaluran, $nominal_penyaluran, $dokumentasi_filename, $admin_id);
                }


                if ($stmt->execute()) {
                    $success = true;
                    $_SESSION['penyaluran_message'] = "Data penyaluran berhasil ditambahkan.";
                    header("Location: ../kelola-penyaluran.php?status=success");
                    exit;
                } else {
                    $message = "Gagal menyimpan data penyaluran: " . $stmt->error;
                    // Hapus file jika gagal simpan ke DB
                    if ($dokumentasi_filename && file_exists($target_file_doc)) {
                        unlink($target_file_doc);
                    }
                }
                $stmt->close();
            } else {
                 $message = "Gagal menyiapkan statement database: " . $conn->error;
                 if ($dokumentasi_filename && file_exists($target_file_doc)) {
                    unlink($target_file_doc);
                }
            }
        }
    }
} else {
    header('Location: ../tambah-penyaluran.php');
    exit;
}

$conn->close();

if (!$success) {
    $_SESSION['penyaluran_error'] = $message;
    // Simpan data form ke sesi agar bisa diisi kembali (opsional)
    $_SESSION['form_data_penyaluran'] = $_POST;
    header('Location: ../tambah-penyaluran.php?status=error');
    exit;
}
?>
