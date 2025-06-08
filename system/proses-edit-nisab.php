<?php
// system/proses-edit-nisab.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_admin_login(); // Pastikan hanya admin yang bisa akses

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nilai']) && is_array($_POST['nilai'])) {
        $all_updates_successful = true;
        
        // Mulai transaksi jika database  mendukung dan  ingin memastikan semua update berhasil atau tidak sama sekali
        // $conn->begin_transaction(); 

        foreach ($_POST['nilai'] as $zakat_id => $nilai_baru) {
            $zakat_id_sanitized = intval($zakat_id);
            // Validasi nilai baru, pastikan itu angka dan positif jika perlu
            if (!is_numeric($nilai_baru) || floatval($nilai_baru) < 0) {
                $message = "Nilai yang dimasukkan untuk ID " . $zakat_id_sanitized . " tidak valid.";
                $all_updates_successful = false;
                break; // Hentikan jika ada nilai tidak valid
            }
            $nilai_baru_sanitized = floatval($nilai_baru);

            $stmt = $conn->prepare("UPDATE nisab SET nilai = ? WHERE zakat_id = ?");
            if ($stmt) {
                $stmt->bind_param("di", $nilai_baru_sanitized, $zakat_id_sanitized);
                if (!$stmt->execute()) {
                    $all_updates_successful = false;
                    $message = "Gagal memperbarui nilai untuk ID " . $zakat_id_sanitized . ": " . $stmt->error;
                    // $conn->rollback(); // Batalkan transaksi jika ada error
                    $stmt->close();
                    break; 
                }
                $stmt->close();
            } else {
                $all_updates_successful = false;
                $message = "Gagal menyiapkan statement update untuk ID " . $zakat_id_sanitized . ": " . $conn->error;
                // $conn->rollback();
                break;
            }
        }

        if ($all_updates_successful) {
            // $conn->commit(); // Konfirmasi transaksi jika semua berhasil
            $success = true;
            $message = "Semua nilai nisab berhasil diperbarui.";
        } else {
            // Pesan error sudah diatur di dalam loop atau saat prepare statement gagal
            // Jika menggunakan transaksi dan terjadi rollback, $message sudah berisi errornya.
        }
        
    } else {
        $message = "Tidak ada data nilai yang dikirim atau format data salah.";
    }
} else {
    // Jika bukan metode POST, arahkan kembali ke halaman edit-nisab
    header('Location: ../edit-nisab.php');
    exit;
}

$conn->close();

// Arahkan kembali ke halaman edit-nisab dengan pesan status
if ($success) {
    $_SESSION['update_nisab_msg'] = $message;
    $_SESSION['update_nisab_status'] = 'success';
    header('Location: ../edit-nisab.php?status=success&msg=' . urlencode($message));
} else {
    $_SESSION['update_nisab_msg'] = $message;
    $_SESSION['update_nisab_status'] = 'error';
    header('Location: ../edit-nisab.php?status=error&msg=' . urlencode($message));
}
exit;
?>
