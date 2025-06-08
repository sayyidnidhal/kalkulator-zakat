<?php
require_once 'system/session-handler.php';
require_admin_login(); 

require_once 'system/db-connect.php'; 

// Data untuk Header
$current_page = basename($_SERVER['PHP_SELF']);
$user_name_display = get_current_user_name() ?? 'Admin';
$dashboard_link = 'dashboard-admin.php';
$profile_link = 'profil-pengguna.php';

$data_nisab = [];
// Query diubah untuk tidak mengambil jenis 'perak' dan 'pertanian_kg'
$sql = "SELECT zakat_id, jenis_zakat, deskripsi, nilai, satuan FROM nisab WHERE jenis_zakat NOT IN ('perak', 'pertanian_kg') ORDER BY zakat_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_nisab[] = $row;
    }
}

// Logika untuk menampilkan pesan feedback...
$update_message = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
    $msg_text = htmlspecialchars($_GET['msg']);
    if ($_GET['status'] === 'success') {
        $update_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Berhasil!</strong> '.$msg_text.'</div>';
    } else {
         $update_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Gagal!</strong> '.$msg_text.'</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Nilai Nishab - Admin</title>
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
                
                <?php if ($current_page !== 'dashboard-admin.php'): ?>
                    <a href="<?php echo $dashboard_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Dashboard Admin</a>
                <?php endif; ?>
                <?php if ($current_page === 'dashboard-admin.php' && $current_page !== 'profil-pengguna.php'): ?>
                     <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                <?php endif; ?>
                <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name_display); ?>!</span>
                <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        <div class="max-w-2xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary-orange">Pembaruan Nilai Nisab Zakat</h1>
            </div>

            <?php echo $update_message; ?>

            <?php if (empty($data_nisab)): ?>
                <p class="text-center text-red-600">Data nilai nisab tidak ditemukan atau gagal dimuat dari database.</p>
            <?php else: ?>
                <form id="formEditNishab" action="system/proses-edit-nisab.php" method="POST">
                    <?php foreach ($data_nisab as $item): ?>
                    <div class="mb-6 p-4 border rounded-md bg-slate-50">
                        <label for="nilai_<?php echo htmlspecialchars($item['zakat_id']); ?>" class="block text-sm font-medium text-dark-text mb-1">
                            <?php echo htmlspecialchars($item['deskripsi']); ?> 
                            <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($item['jenis_zakat']); ?>)</span>
                        </label>
                        <div class="flex items-center">
                            <input type="number" step="0.01" id="nilai_<?php echo htmlspecialchars($item['zakat_id']); ?>" name="nilai[<?php echo htmlspecialchars($item['zakat_id']); ?>]" value="<?php echo htmlspecialchars(number_format($item['nilai'], 2, '.', '')); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan nilai" required>
                            <?php if (!empty($item['satuan'])): ?>
                                <span class="ml-2 text-slate-500 text-sm">/ <?php echo htmlspecialchars($item['satuan']); ?></span>
                            <?php endif; ?>
                        </div>
                         <?php if ($item['jenis_zakat'] === 'emas'): ?>
                            <p class="text-xs text-slate-500 mt-1">Digunakan untuk nishab Zakat Mal, Profesi, dll (85 gram emas).</p>
                        <?php elseif (strpos($item['jenis_zakat'], 'beras') === 0): ?>
                            <p class="text-xs text-slate-500 mt-1">Digunakan untuk pilihan harga beras pada Zakat Fitrah.</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-10">
                        <button type="submit" class="btn-primary w-full py-3 px-4 rounded-md font-semibold shadow-md text-lg">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
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
<?php if(isset($conn)) $conn->close(); ?>
