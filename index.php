<?php
require_once 'system/session-handler.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = is_user_logged_in() || is_admin_logged_in();
$user_name = null;
$dashboard_link = '#'; 

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
    <title>Kalkulator Zakat</title>
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
                <?php if ($is_logged_in): ?>
                    <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name ?? 'Pengguna'); ?>!</span>
                    <a href="<?php echo $dashboard_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors font-medium">Dashboard Saya</a>
                    <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="login-button">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center p-6 mt-8">
        <h1 class="text-4xl font-bold text-primary-orange mb-10 text-center">
            Selamat Datang!
        </h1>
        <p class="arabic-text text-center mb-6 text-3xl">
        خُذْ مِنْ اَمْوَالِهِمْ صَدَقَةً تُطَهِّرُهُمْ وَتُزَكِّيْهِمْ بِهَا وَصَلِّ عَلَيْهِمْۗ اِنَّ صَلٰوتَكَ سَكَنٌ لَّهُمْۗ وَاللّٰهُ سَمِيْعٌ عَلِيْمٌ
        </p>
        <p class="text-lg text-dark-text mb-10 text-center leading-relaxed max-w-2xl">
        "Ambillah zakat dari harta mereka (guna) menyucikan dan membersihkan mereka, dan doakanlah mereka karena sesungguhnya doamu adalah ketenteraman bagi mereka. Allah Maha Mendengar lagi Maha Mengetahui."
        <span class="font-semibold block mt-1">Q.S. At Taubah (9): 103</span> 
        </p>
        <div id="selection-box" class="selection-box bg-white rounded-xl shadow-xl overflow-hidden">
            <h3 class="text-3xl md:text-4xl font-bold text-primary-orange py-8 px-6 text-center"> 
                Kalkulator Zakat
            </h3>
            <nav class="zakat-tabs-nav">
                <a href="zakat-fitrah.php" class="zakat-tab-button text-lg md:text-xl py-4 px-6"> 
                    Zakat Fitrah
                </a>
                <a href="zakat-mal.php" class="zakat-tab-button text-lg md:text-xl py-4 px-6"> 
                    Zakat Mal
                </a>
            </nav>
            <div class="p-6 text-center">
                <p class="text-sm text-slate-600">Pilih jenis zakat yang ingin Anda hitung.</p>
            </div>
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
