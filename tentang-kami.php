<?php
require_once 'system/session-handler.php'; 
// Tidak memerlukan db-connect.php karena halaman ini statis

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = is_user_logged_in() || is_admin_logged_in();
$user_name = null;
$dashboard_link = '#'; 
$profile_link = 'profil-pengguna.php';

if ($is_logged_in) {
    $user_name = get_current_user_name();
    if (is_admin_logged_in()) {
        $dashboard_link = 'dashboard-admin.php';
    } else { 
        $dashboard_link = 'dashboard.php';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Lazismu Kalteng</title>
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
                
                <?php if ($is_logged_in): ?>
                    <?php if ($current_page !== 'dashboard.php' && $current_page !== 'dashboard-admin.php'): ?>
                        <a href="<?php echo $dashboard_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Dashboard</a>
                    <?php endif; ?>
                     <?php if (($current_page === 'dashboard.php' || $current_page === 'dashboard-admin.php') && $current_page !== 'profil-pengguna.php'): ?>
                         <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                    <?php endif; ?>
                    <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name ?? 'Pengguna'); ?>!</span>
                    <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
                <?php else: ?>
                     <?php if ($current_page !== 'login.php' && $current_page !== 'registrasi.php'): ?>
                        <a href="login.php" class="login-button">Login</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        <div class="max-w-3xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-primary-orange">Tentang Lazismu Kalteng</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div>
                    <img src="assets/kantor-lazismu.png" alt="Foto Kantor Lazismu Kalteng" class="rounded-lg shadow-md w-full h-auto object-cover" onerror="this.alt='Foto Kantor Tidak Ditemukan'; this.src='https://placehold.co/600x400/F97316/FFFFFF?text=Foto+Kantor';">
                    <p class="text-xs text-center text-slate-500 mt-2">Kantor Lazismu Kalteng</p>
                </div>
                <div class="text-dark-text space-y-3">
                    <h2 class="text-2xl font-semibold text-action-orange">Kantor Lazismu Kalteng</h2>
                    <div>
                        <strong class="block">Alamat:</strong>
                        <p class="leading-relaxed">
                            Jl. RTA. Milono Km. 1,5 Palangka Raya Kalimantan Tengah 73111 (Komplek Perguruan Muhammadiyah Palangka Raya)
                        </p>
                    </div>
                    <div>
                        <strong class="block">Telp/WA:</strong> 
                        <a href="https://wa.me/6282255469065" target="_blank" class="text-primary-orange hover:underline">0822 5546 9065</a>
                    </div>
                    <div>
                        <strong class="block">Email:</strong> 
                        <a href="mailto:lazismukalteng@gmail.com" class="text-primary-orange hover:underline">lazismukalteng@gmail.com</a>
                    </div>
                     <div class="mt-4">
                        <h3 class="text-lg font-semibold text-action-orange mb-1">Jam Operasional (Contoh):</h3>
                        <ul class="list-disc list-inside ml-4 text-sm space-y-1">
                            <li>Senin - Jumat: 08:00 - 16:00 WIB</li>
                            <li>Sabtu: 08:00 - 12:00 WIB</li>
                            <li>Minggu & Hari Libur Nasional: Tutup</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-12">
                <h2 class="text-2xl font-semibold text-action-orange mb-4 text-center">Peta Lokasi</h2>
                <div class="aspect-w-16 aspect-h-9 rounded-lg shadow-md overflow-hidden">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d996.7058363609875!2d113.92071244142461!3d-2.220162865953065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dfcb33796fef707%3A0x78bdcbc8d7bcb60d!2sLAZISMU%20Central%20Kalimantan!5e0!3m2!1sen!2sid!4v1748869087903!5m2!1sen!2sid" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy"
                        title="Peta Lokasi Lazismu Kalteng"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                     <p class="text-xs text-center text-slate-500 mt-2">Ganti `src` iframe dengan embed code Google Maps yang benar.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">© <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>
