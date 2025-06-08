<?php
// system/proses-update-profil.php
require_once 'db-connect.php';
require_once 'session-handler.php';

require_login(); // Pengguna harus login untuk update profil
$user_id_session = get_current_user_id(); 

$message = "";
$success = false;
$redirect_status = 'error'; // Default status untuk redirect

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pengguna_form = isset($_POST['id_pengguna']) ? intval($_POST['id_pengguna']) : null;
    $nama_lengkap = trim($conn->real_escape_string($_POST['nama_lengkap'] ?? ''));
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $nomor_hp = trim($conn->real_escape_string($_POST['nomor_hp'] ?? ''));
    
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password_baru = $_POST['konfirmasi_password_baru'] ?? '';

    // Validasi bahwa pengguna yang login yang mengupdate profilnya sendiri
    if ($user_id_session !== $id_pengguna_form) {
        $message = "Error otentikasi:  tidak diizinkan mengubah profil ini.";
        // header('Location: ../profil-pengguna.php?status=auth_error&msg=' . urlencode($message)); // Sebaiknya jangan redirect dari sini jika error kritis
        // exit;

        session_destroy();
        header('Location: ../login.php?status=auth_error');
        exit;
    }

    if (empty($nama_lengkap) || empty($email) || empty($nomor_hp)) {
        $message = "Nama lengkap, email, dan nomor HP wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid.";
    } else {
        // Cek apakah email baru (jika diubah) sudah digunakan oleh pengguna lain
        // $stmt_check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        // if ($stmt_check_email) {
        //     $stmt_check_email->bind_param("si", $email, $user_id_session);
        //     $stmt_check_email->execute();
        //     $result_check_email = $stmt_check_email->get_result();
        //     if ($result_check_email->num_rows > 0) {
        //         $message = "Email sudah digunakan oleh pengguna lain.";
        //     }
        //     $stmt_check_email->close();
        // } else {
        //     $message = "Gagal menyiapkan statement cek email: " . $conn->error;
        // }

        // Jika tidak ada error dari cek email, lanjutkan
        if (empty($message)) {
            $stmt_update_user = $conn->prepare("UPDATE users SET nama_lengkap = ?, email = ?, nomor_hp = ? WHERE user_id = ?");
            if ($stmt_update_user) {
                $stmt_update_user->bind_param("sssi", $nama_lengkap, $email, $nomor_hp, $user_id_session);
                
                if ($stmt_update_user->execute()) {
                    $success = true; // Setidaknya update data dasar berhasil
                    $message = "Profil berhasil diperbarui.";
                    $_SESSION['user_nama'] = $nama_lengkap; // Update nama di sesi
                    $redirect_status = 'success';
                } else {
                    $message = "Gagal memperbarui profil: " . $stmt_update_user->error;
                }
                $stmt_update_user->close();
            } else {
                 $message = "Gagal menyiapkan statement update profil: " . $conn->error;
            }

            // Proses perubahan password jika diisi dan update profil dasar berhasil atau diizinkan lanjut
            if (!empty($password_lama) && !empty($password_baru)) {
                if ($password_baru !== $konfirmasi_password_baru) {
                    $message = "Konfirmasi password baru tidak cocok.";
                    $success = false; 
                    $redirect_status = 'pwd_mismatch';
                } elseif (strlen($password_baru) < 6) {
                    $message = "Password baru minimal harus 6 karakter.";
                    $success = false;
                    $redirect_status = 'pwd_len_error';
                } else {
                    // Verifikasi password lama
                    $stmt_pass = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                    if ($stmt_pass) {
                        $stmt_pass->bind_param("i", $user_id_session);
                        $stmt_pass->execute();
                        $result_pass = $stmt_pass->get_result();
                        if ($result_pass->num_rows === 1) {
                            $user_db = $result_pass->fetch_assoc();
                            if (password_verify($password_lama, $user_db['password'])) {
                                // Password lama cocok, hash password baru dan update
                                $hashed_password_baru = password_hash($password_baru, PASSWORD_BCRYPT);
                                $stmt_update_pass = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                                if ($stmt_update_pass) {
                                    $stmt_update_pass->bind_param("si", $hashed_password_baru, $user_id_session);
                                    if ($stmt_update_pass->execute()) {
                                        $message = $success ? $message . " Password juga berhasil diperbarui." : "Password berhasil diperbarui.";
                                        $success = true; // Pastikan status sukses jika password update berhasil
                                        $redirect_status = 'success';
                                    } else {
                                        $message = "Gagal memperbarui password: " . $stmt_update_pass->error;
                                        $success = false;
                                        $redirect_status = 'error';
                                    }
                                    $stmt_update_pass->close();
                                } else {
                                     $message = "Gagal menyiapkan statement update password: " . $conn->error;
                                     $success = false;
                                     $redirect_status = 'error';
                                }
                            } else {
                                $message = "Password saat ini salah.";
                                $success = false;
                                $redirect_status = 'pwd_error';
                            }
                        }
                        $stmt_pass->close();
                    } else {
                        $message = "Gagal menyiapkan statement verifikasi password: " . $conn->error;
                        $success = false;
                        $redirect_status = 'error';
                    }
                }
            } elseif (!empty($password_lama) && empty($password_baru)) {
                // Jika password lama diisi tapi password baru tidak, itu mungkin error input pengguna
                $message = "Password baru tidak boleh kosong jika ingin mengubah password.";
                $success = false;
                $redirect_status = 'pwd_new_empty';
            }
        }
    }
} else {
    header('Location: ../profil-pengguna.php');
    exit;
}

$conn->close();

// Arahkan kembali ke halaman profil dengan pesan status
$_SESSION['update_profil_msg'] = $message; // Simpan pesan ke sesi
$_SESSION['update_profil_status'] = $success ? 'success' : $redirect_status;

header('Location: ../profil-pengguna.php?status=' . $redirect_status . '&msg=' . urlencode($message));
exit;
?>
