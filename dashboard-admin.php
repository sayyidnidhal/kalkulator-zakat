<?php
require_once 'system/session-handler.php';
require_admin_login(); 

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = true; 
$user_name_display = get_current_user_name();
if (!$user_name_display && isset($_SESSION['user_nama'])) { 
    $user_name_display = $_SESSION['user_nama'];
} elseif (!$user_name_display) {
    $user_name_display = 'Admin';
}
$dashboard_link = 'dashboard-admin.php'; 
$profile_link = 'profil-pengguna.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kalkulator Zakat</title>
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
                
                <?php if ($current_page !== 'profil-pengguna.php'): // Tombol profil hanya jika tidak di halaman profil ?>
                     <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                <?php endif; ?>
                <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name_display); ?>!</span>
                <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center p-6">
        <div class="admin-dashboard-container bg-white p-8 md:p-12 rounded-xl shadow-xl w-full max-w-lg text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-primary-orange mb-10">
                Dashboard Admin
            </h1>
            
            <div class="space-y-6">
                <a href="kelola-zakat.php" class="dashboard-button bg-action-orange hover:bg-primary-orange text-white font-semibold py-4 px-6 rounded-lg shadow-md block text-lg transition duration-150 ease-in-out">
                    Kelola Pembayaran Zakat
                </a>
                <a href="kelola-penyaluran.php" class="dashboard-button bg-action-orange hover:bg-primary-orange text-white font-semibold py-4 px-6 rounded-lg shadow-md block text-lg transition duration-150 ease-in-out">
                    Kelola Penyaluran Zakat
                </a>
                 <a href="kelola-pengguna.php" class="dashboard-button bg-action-orange hover:bg-primary-orange text-white font-semibold py-4 px-6 rounded-lg shadow-md block text-lg transition duration-150 ease-in-out">
                    Kelola Pengguna
                </a>
                 <a href="edit-nisab.php" class="dashboard-button bg-orange-500 hover:bg-orange-600 text-white font-semibold py-4 px-6 rounded-lg shadow-md block text-lg transition duration-150 ease-in-out">
                    Pembaruan Nisab
                </a>
                <a href="laporan.php" class="dashboard-button bg-gray-500 hover:bg-gray-600 text-white font-semibold py-4 px-6 rounded-lg shadow-md block text-lg transition duration-150 ease-in-out">
                    Lihat Laporan
                </a>
            </div>
        </div>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">&copy; <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>
