<?php
// system/proses-kelola-pengguna.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_admin_login();
$admin_id = get_current_user_id();

$action = $_GET['action'] ?? null;
$user_id_target = isset($_GET['id']) ? intval($_GET['id']) : null;

$message = "Aksi tidak dikenal atau ID pengguna tidak ada.";
$success = false;

if ($action && $user_id_target) {
    // Keamanan: Admin tidak bisa mengelola dirinya sendiri atau admin lain
    $target_role = '';
    $stmt_check_role = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    if ($stmt_check_role) {
        $stmt_check_role->bind_param("i", $user_id_target);
        $stmt_check_role->execute();
        $result_role = $stmt_check_role->get_result();
        if ($result_role->num_rows > 0) {
            $target_role = $result_role->fetch_assoc()['role'];
        }
        $stmt_check_role->close();
    }

    if ($target_role === 'admin') {
        $message = "Aksi tidak diizinkan. Admin tidak dapat mengelola akun admin lain.";
    } else {
        switch ($action) {
            case 'activate':
                // Mengubah status akun menjadi ACTIVE
                $stmt = $conn->prepare("UPDATE users SET status_akun = 'ACTIVE' WHERE user_id = ? AND role = 'user'");
                if ($stmt) {
                    $stmt->bind_param("i", $user_id_target);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $success = true;
                        $message = "Pengguna dengan ID " . htmlspecialchars($user_id_target) . " berhasil diaktifkan.";
                    } else {
                        $message = "Gagal mengaktifkan pengguna atau pengguna sudah aktif.";
                    }
                    $stmt->close();
                } else {
                    $message = "Gagal menyiapkan statement aktivasi: " . $conn->error;
                }
                break;

            case 'suspend':
                // Mengubah status akun menjadi SUSPENDED
                $stmt = $conn->prepare("UPDATE users SET status_akun = 'SUSPENDED' WHERE user_id = ? AND role = 'user'");
                if ($stmt) {
                    $stmt->bind_param("i", $user_id_target);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $success = true;
                        $message = "Pengguna dengan ID " . htmlspecialchars($user_id_target) . " berhasil ditangguhkan (suspended).";
                    } else {
                        $message = "Gagal menangguhkan pengguna atau pengguna sudah ditangguhkan.";
                    }
                    $stmt->close();
                } else {
                    $message = "Gagal menyiapkan statement suspend: " . $conn->error;
                }
                break;

            case 'hapus':
                // Menghapus pengguna. Relasi di pembayaran_zakat akan menjadi NULL karena ON DELETE SET NULL.
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'user'");
                if ($stmt) {
                    $stmt->bind_param("i", $user_id_target);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $success = true;
                        $message = "Pengguna dengan ID " . htmlspecialchars($user_id_target) . " berhasil dihapus.";
                    } else {
                        $message = "Gagal menghapus pengguna atau pengguna tidak ditemukan.";
                    }
                    $stmt->close();
                } else {
                    $message = "Gagal menyiapkan statement hapus: " . $conn->error;
                }
                break;

            default:
                $message = "Aksi '" . htmlspecialchars($action) . "' tidak valid.";
                break;
        }
    }
}

$conn->close();

// Set pesan di sesi dan redirect
if ($success) {
    $_SESSION['kelola_pengguna_msg'] = $message;
} else {
    $_SESSION['kelola_pengguna_error'] = $message;
}

header('Location: ../kelola-pengguna.php');
exit;
?>
