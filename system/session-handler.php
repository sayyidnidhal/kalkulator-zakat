<?php
// system/session-handler.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definisikan base URL aplikasi Anda. Sesuaikan jika perlu.
// Jika aplikasi Anda ada di root web server (misal, http://localhost/), maka BASE_URL = '/'
// Jika aplikasi Anda ada di subfolder (misal, http://localhost/kalkulator_zakat/), maka BASE_URL = '/kalkulator_zakat/'
// Pastikan diakhiri dengan slash '/'
define('BASE_URL', '/kalkulator_zakat/'); // <--- SESUAIKAN INI JIKA PERLU

/**
 * Memeriksa apakah pengguna (user biasa) sudah login.
 * @return bool True jika sudah login, false jika belum.
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Memeriksa apakah admin sudah login.
 * @return bool True jika sudah login, false jika belum.
 */
function is_admin_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Mendapatkan ID pengguna yang sedang login.
 * @return int|null ID pengguna atau null jika tidak login.
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Mendapatkan nama pengguna yang sedang login.
 * @return string|null Nama pengguna atau null jika tidak login.
 */
function get_current_user_name() {
    return $_SESSION['user_nama'] ?? null;
}

/**
 * Mengarahkan pengguna ke halaman login jika belum login.
 * Menyimpan URL tujuan saat ini untuk redirect setelah login.
 */
function require_login() {
    if (!is_user_logged_in() && !is_admin_logged_in()) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        // Gunakan $_SERVER['REQUEST_URI'] untuk mendapatkan path dan query string
        // Jangan gunakan $_SERVER['PHP_SELF'] karena itu tidak termasuk query string
        $current_path_and_query = $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_url'] = $current_path_and_query; // Simpan path relatif dari root web
        
        // Menggunakan BASE_URL untuk path yang lebih pasti
        header('Location: ' . BASE_URL . 'login.php?pesan=harus_login'); 
        exit;
    }
}

/**
 * Mengarahkan admin ke halaman login admin jika belum login.
 * Menyimpan URL tujuan saat ini untuk redirect setelah login.
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $current_path_and_query = $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_url'] = $current_path_and_query;

        header('Location: ' . BASE_URL . 'login.php?pesan=admin_harus_login'); 
        exit;
    }
}

?>
