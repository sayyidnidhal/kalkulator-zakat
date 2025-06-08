<?php
// system/proses-logout.php
require_once 'session-handler.php'; // Memulai sesi untuk dihancurkan

// Hapus semua variabel sesi
$_SESSION = array();

// Jika diinginkan, hancurkan sesi juga.
// Ini akan menghancurkan sesi, bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Akhirnya, hancurkan sesi.
session_destroy();

header("Location: ../index.php?status=logout_success"); // Memberi feedback logout berhasil
exit;
?>
