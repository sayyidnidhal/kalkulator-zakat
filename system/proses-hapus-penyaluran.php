<?php
// system/proses-hapus-penyaluran.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_admin_login();

$id_penyaluran = $_GET['id'] ?? null;
$message = "";
$success = false;

if (!$id_penyaluran) {
    $_SESSION['penyaluran_error'] = "ID penyaluran tidak valid atau tidak ditemukan.";
    header('Location: ../kelola-penyaluran.php?status=error');
    exit;
}

// Ambil nama file dokumentasi sebelum menghapus record
$doc_file = null;
$stmt_get_doc = $conn->prepare("SELECT dokumentasi FROM penyaluran_zakat WHERE penyaluran_id = ?");
if($stmt_get_doc){
    $stmt_get_doc->bind_param("s", $id_penyaluran); // Asumsi ID bisa string jika UUID
    $stmt_get_doc->execute();
    $result_doc = $stmt_get_doc->get_result();
    if($result_doc->num_rows > 0) {
        $row_doc = $result_doc->fetch_assoc();
        $doc_file = $row_doc['dokumentasi'];
    }
    $stmt_get_doc->close();
} else {
    $_SESSION['penyaluran_error'] = "Gagal menyiapkan query untuk mengambil dokumentasi: " . $conn->error;
    header('Location: ../kelola-penyaluran.php?status=error');
    exit;
}


$stmt_delete = $conn->prepare("DELETE FROM penyaluran_zakat WHERE penyaluran_id = ?");
if ($stmt_delete) {
    $stmt_delete->bind_param("s", $id_penyaluran); // Asumsi ID bisa string
    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $success = true;
            $message = "Data penyaluran ID " . htmlspecialchars($id_penyaluran) . " berhasil dihapus.";
            // Hapus file fisik dari server
            if ($doc_file && file_exists("../uploads/dokumentasi-penyaluran/" . $doc_file)) {
                unlink("../uploads/dokumentasi-penyaluran/" . $doc_file);
            }
        } else {
            $message = "Data penyaluran ID " . htmlspecialchars($id_penyaluran) . " tidak ditemukan.";
        }
    } else {
        $message = "Gagal menghapus data penyaluran: " . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    $message = "Gagal menyiapkan statement hapus: " . $conn->error;
}

$conn->close();

if ($success) {
    $_SESSION['penyaluran_message'] = $message;
    header('Location: ../kelola-penyaluran.php?status=success');
} else {
    $_SESSION['penyaluran_error'] = $message;
    header('Location: ../kelola-penyaluran.php?status=error');
}
exit;
?>
