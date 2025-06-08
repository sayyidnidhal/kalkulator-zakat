<?php
// system/proses-kelola-zakat.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_admin_login(); 

$action = $_GET['action'] ?? ($_POST['action'] ?? null);
$id_pembayaran = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id_pembayaran']) ? intval($_POST['id_pembayaran']) : null);
$current_admin_id = get_current_user_id(); 

$message = "";
$success = false;

if (empty($action) || empty($id_pembayaran)) {
    $_SESSION['kelola_zakat_error'] = "Aksi atau ID pembayaran tidak valid.";
    header('Location: ../kelola-zakat.php');
    exit;
}

if ($action === 'verifikasi') {
    $status_verifikasi_baru = $_GET['status'] ?? ($_POST['status'] ?? null);
    $keterangan = isset($_POST['keterangan']) ? trim($conn->real_escape_string($_POST['keterangan'])) : null;
    
    if ($status_verifikasi_baru !== 'YA' && $status_verifikasi_baru !== 'TIDAK') {
        $message = "Status verifikasi tidak valid.";
    } elseif ($status_verifikasi_baru === 'TIDAK' && empty($keterangan)) {
        $message = "Alasan penolakan verifikasi wajib diisi.";
    } else {
        // Jika status 'YA', keterangan akan di-set NULL
        if ($status_verifikasi_baru === 'YA') {
            $keterangan = null;
        }

        $stmt = $conn->prepare("UPDATE pembayaran_zakat SET status_verifikasi = ?, keterangan_verifikasi = ?, verified_by = ?, verified_at = NOW() WHERE pembayaran_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssii", $status_verifikasi_baru, $keterangan, $current_admin_id, $id_pembayaran);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = true;
                    $message = "Status pembayaran ID " . htmlspecialchars($id_pembayaran) . " berhasil diubah menjadi " . htmlspecialchars($status_verifikasi_baru) . ".";
                } else {
                    $message = "Pembayaran ID " . htmlspecialchars($id_pembayaran) . " tidak ditemukan atau status sudah sama.";
                }
            } else {
                $message = "Gagal mengubah status verifikasi: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Gagal menyiapkan statement verifikasi: " . $conn->error;
        }
    }

} elseif ($action === 'hapus') {
    // Ambil nama file bukti bayar sebelum menghapus record
    $bukti_file = null;
    $stmt_get_bukti = $conn->prepare("SELECT bukti_bayar FROM pembayaran_zakat WHERE pembayaran_id = ?");
    if($stmt_get_bukti){
        $stmt_get_bukti->bind_param("i", $id_pembayaran);
        $stmt_get_bukti->execute();
        $result_bukti = $stmt_get_bukti->get_result();
        if($result_bukti->num_rows > 0) {
            $row_bukti = $result_bukti->fetch_assoc();
            $bukti_file = $row_bukti['bukti_bayar'];
        }
        $stmt_get_bukti->close();
    }

    $stmt_delete = $conn->prepare("DELETE FROM pembayaran_zakat WHERE pembayaran_id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_pembayaran);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $success = true;
                $message = "Pembayaran ID " . htmlspecialchars($id_pembayaran) . " berhasil dihapus.";
                if ($bukti_file && file_exists("../uploads/bukti-pembayaran/" . $bukti_file)) {
                    unlink("../uploads/bukti-pembayaran/" . $bukti_file);
                }
            } else {
                $message = "Pembayaran ID " . htmlspecialchars($id_pembayaran) . " tidak ditemukan.";
            }
        } else {
            $message = "Gagal menghapus pembayaran: " . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $message = "Gagal menyiapkan statement hapus: " . $conn->error;
    }

} else {
    $message = "Aksi tidak dikenal.";
}

$conn->close();

if ($success) {
    $_SESSION['kelola_zakat_message'] = $message;
    header('Location: ../kelola-zakat.php?status=success');
} else {
    $_SESSION['kelola_zakat_error'] = $message;
    header('Location: ../kelola-zakat.php?status=error');
}
exit;
?>
