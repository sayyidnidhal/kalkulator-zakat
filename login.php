<?php
require_once 'system/session-handler.php'; 

if (is_user_logged_in() || is_admin_logged_in()) {
    header('Location: dashboard.php'); // Atau dashboard-admin.php jika role admin
    exit;
}

$login_error = '';
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); 
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kalkulator Zakat</title>
    <link rel="icon" href="assets/logo_lazismu.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                'primary-orange': '#F97316', 
                'action-orange': '#F57F17',  
                'light-orange-bg': '#FFF7ED', 
                'dark-text': '#374151',    
                'light-bg': '#FFFFFF',     
                'form-input-border': '#D1D5DB', 
                'btn-reset-bg': '#F87171', 
                'btn-reset-hover-bg': '#EF4444', 
              }
            }
          }
        }
    </script>
</head>
<body class="bg-light-bg text-dark-text flex flex-col min-h-screen">

    <header class="bg-white shadow-md py-4 sticky top-0 z-50">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <div>
                <a href="index.php">
                    <img src="assets/logo_lazismu.png" class="h-10 md:h-12 w-auto" alt="Logo Lazismu" onerror="this.alt='Logo Tidak Ditemukan'; this.src='https://placehold.co/120x48/F97316/FFFFFF?text=LOGO';">
                </a>
            </div>
            <div class="flex items-center space-x-3 md:space-x-4">
                <?php if ($current_page !== 'index.php'): ?>
                    <a href="index.php" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Menu Utama</a>
                <?php endif; ?>
                </div>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center p-6">
        <div class="login-container bg-white p-8 md:p-12 rounded-xl shadow-xl w-full max-w-md">
            <h1 class="text-3xl font-bold text-primary-orange mb-8 text-center">
                Selamat Datang!
            </h1>

            <?php if (!empty($login_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Login Gagal!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($login_error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['status']) && $_GET['status'] === 'reg_success'): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Registrasi Berhasil!</strong>
                    <span class="block sm:inline">Silakan login dengan akun baru Anda.</span>
                </div>
            <?php endif; ?>
             <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'harus_login'): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline">Anda harus login untuk mengakses halaman tersebut.</span>
                </div>
            <?php endif; ?>


            <form id="loginForm" action="system/proses-login.php" method="POST">
                <div class="mb-6">
                    <label for="username" class="block text-sm font-medium text-dark-text mb-1">Username atau Email</label>
                    <input type="text" id="username" name="username" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan username atau email Anda" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-dark-text mb-1">Password</label>
                    <input type="password" id="password" name="password" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan password Anda" required>
                </div>
                <button type="submit" class="btn-primary w-full py-3 px-4 rounded-md font-semibold shadow-md text-lg mt-6">
                    Login
                </button>
                <p class="text-sm text-center text-slate-600 mt-6">
                    Belum punya akun? <a href="registrasi.php" class="text-primary-orange hover:text-action-orange font-semibold">Daftar di sini</a>
                </p>
            </form>
        </div>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">&copy; <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentYearEl = document.getElementById('currentYear');
            if (currentYearEl) {
                currentYearEl.textContent = new Date().getFullYear();
            }
        });
    </script>
</body>
</html>
