<?php
// system/proses-login.php
require_once 'db-connect.php';
require_once 'session-handler.php'; // Memulai sesi dan mendefinisikan BASE_URL

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($conn->real_escape_string($_POST['username'] ?? ''));
    $password_input = $_POST['password'] ?? '';

    if (empty($username_input) || empty($password_input)) {
        $_SESSION['login_error'] = "Username dan password wajib diisi.";
        header('Location: ' . BASE_URL . 'login.php'); // Gunakan BASE_URL
        exit;
    } else {
        $stmt = $conn->prepare("SELECT user_id, nama_lengkap, username, email, password, role FROM users WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $username_input, $username_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password_input, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_nama'] = $user['nama_lengkap'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    $redirect_url_stored = $_SESSION['redirect_url'] ?? null;
                    unset($_SESSION['redirect_url']); 

                    if ($redirect_url_stored) {
                        // Karena redirect_url_stored sudah berisi path dari root (misal /kalkulator_zakat/bayar-zakat.php?...)
                        // kita tidak perlu menambahkan BASE_URL lagi.
                        // Kita hanya perlu memastikan itu adalah path yang valid.
                        // Untuk keamanan, pastikan redirect_url_stored adalah path internal aplikasi Anda.
                        // Contoh validasi sederhana:
                        if (strpos($redirect_url_stored, BASE_URL) === 0 || strpos($redirect_url_stored, '/') === 0) {
                             header("Location: " . $redirect_url_stored);
                        } else {
                            // Jika tidak valid, arahkan ke dashboard default
                             header("Location: " . ($user['role'] === 'admin' ? BASE_URL . 'dashboard-admin.php' : BASE_URL . 'dashboard.php'));
                        }
                    } elseif ($user['role'] === 'admin') {
                        header("Location: " . BASE_URL . 'dashboard-admin.php');
                    } else {
                        header("Location: " . BASE_URL . 'dashboard.php');
                    }
                    exit;
                } else {
                    $message = "Username atau password salah.";
                }
            } else {
                $message = "Username atau password salah.";
            }
            $stmt->close();
        } else {
            $message = "Gagal menyiapkan statement login: " . $conn->error;
            error_log("Login statement prepare error: " . $conn->error);
        }
    }
} else {
    header('Location: ' . BASE_URL . 'login.php'); // Gunakan BASE_URL
    exit;
}

$conn->close();

if (!empty($message)) {
    $_SESSION['login_error'] = $message;
    header('Location: ' . BASE_URL . 'login.php'); // Gunakan BASE_URL
    exit;
}
?>
