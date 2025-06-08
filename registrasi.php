<?php
require_once 'system/session-handler.php';

if (is_user_logged_in() || is_admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$reg_error = '';
$form_data = $_SESSION['form_data'] ?? []; // Ambil data form jika ada dari sesi
unset($_SESSION['form_data']); // Hapus setelah diambil

if (isset($_SESSION['reg_error'])) {
    $reg_error = $_SESSION['reg_error'];
    unset($_SESSION['reg_error']);
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Kalkulator Zakat</title>
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
                Buat Akun Baru
            </h1>

            <?php if (!empty($reg_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Registrasi Gagal!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($reg_error); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'error' && isset($_SESSION['reg_error_msg'])): ?>
                 <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['reg_error_msg']); unset($_SESSION['reg_error_msg']);?></span>
                </div>
            <?php endif; ?>


            <form id="registrationForm" action="system/proses-registrasi.php" method="POST">
                <div class="mb-4">
                    <label for="nama_lengkap" class="block text-sm font-medium text-dark-text mb-1">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan nama lengkap Anda" required value="<?php echo htmlspecialchars($form_data['nama_lengkap'] ?? ''); ?>">
                </div>
                <div class="mb-4">
                    <label for="username_reg" class="block text-sm font-medium text-dark-text mb-1">Username</label>
                    <input type="text" id="username_reg" name="username" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Pilih username" required value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>">
                </div>
                <div class="mb-4">
                    <label for="email_reg" class="block text-sm font-medium text-dark-text mb-1">Email</label>
                    <input type="email" id="email_reg" name="email" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan alamat email Anda" required value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                </div>
                <div class="mb-4">
                    <label for="nomor_hp" class="block text-sm font-medium text-dark-text mb-1">Nomor HP/WhatsApp</label>
                    <input type="tel" id="nomor_hp" name="nomor_hp" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Contoh: 081234567890" required pattern="[0-9]{10,15}" value="<?php echo htmlspecialchars($form_data['nomor_hp'] ?? ''); ?>">
                    <p class="text-xs text-slate-500 mt-1">Masukkan 10-15 digit angka.</p>
                </div>
                <div class="mb-4">
                    <label for="password_reg" class="block text-sm font-medium text-dark-text mb-1">Password</label>
                    <input type="password" id="password_reg" name="password" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Buat password (minimal 6 karakter)" required minlength="6">
                </div>
                <div class="mb-6">
                    <label for="konfirmasi_password" class="block text-sm font-medium text-dark-text mb-1">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="login-input w-full p-3 border rounded-md shadow-sm" placeholder="Ulangi password" required>
                </div>
                
                <button type="submit" class="btn-primary w-full py-3 px-4 rounded-md font-semibold shadow-md text-lg mt-6">
                    Daftar
                </button>
                <p class="text-sm text-center text-slate-600 mt-6">
                    Sudah punya akun? <a href="login.php" class="text-primary-orange hover:text-action-orange font-semibold">Login di sini</a>
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
            
            const registrationForm = document.getElementById('registrationForm');
            if(registrationForm) {
                registrationForm.addEventListener('submit', function(event) {
                    const password = document.getElementById('password_reg').value;
                    const confirmPassword = document.getElementById('konfirmasi_password').value;
                    const nomorHp = document.getElementById('nomor_hp').value;

                    if (password !== confirmPassword) {
                        alert('Password dan Konfirmasi Password tidak cocok!');
                        event.preventDefault(); 
                        return false;
                    }

                    const nomorHpPattern = /^[0-9]{10,15}$/;
                    if (!nomorHpPattern.test(nomorHp)) {
                        alert('Format Nomor HP/WhatsApp tidak valid. Harap masukkan 10-15 digit angka.');
                        event.preventDefault();
                        return false;
                    }
                    
                    if (password.length < 6) {
                        alert('Password minimal harus 6 karakter.');
                        event.preventDefault();
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>
