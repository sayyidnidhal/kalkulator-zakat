<?php
require_once 'system/session-handler.php';
require_admin_login(); // Pastikan hanya admin yang bisa akses halaman ini

require_once 'system/db-connect.php'; 

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = is_user_logged_in() || is_admin_logged_in(); 
$user_name = get_current_user_name();
$dashboard_link = 'dashboard-admin.php'; // Admin selalu ke dashboard admin
$profile_link = 'profil-pengguna.php'; 

$feedback_message = '';
if (isset($_SESSION['penyaluran_message'])) {
    $feedback_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Berhasil!</strong> '.htmlspecialchars($_SESSION['penyaluran_message']).'</div>';
    unset($_SESSION['penyaluran_message']);
} elseif (isset($_SESSION['penyaluran_error'])) {
    $feedback_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Gagal!</strong> '.htmlspecialchars($_SESSION['penyaluran_error']).'</div>';
    unset($_SESSION['penyaluran_error']);
}
$form_data = $_SESSION['form_data_penyaluran'] ?? []; // Untuk mengisi kembali form jika ada error
unset($_SESSION['form_data_penyaluran']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Penyaluran Zakat</title>
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
                
                <?php if ($is_logged_in): // Seharusnya selalu true karena require_admin_login ?>
                    <?php if ($current_page !== 'dashboard-admin.php'): ?>
                        <a href="<?php echo $dashboard_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Dashboard</a>
                    <?php endif; ?>
                     <?php if ($current_page === 'dashboard-admin.php' && $current_page !== 'profil-pengguna.php'): ?>
                         <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                    <?php endif; ?>
                    <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name ?? 'Admin'); ?>!</span>
                    <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        <div class="max-w-2xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary-orange">Tambah Data Penyaluran Zakat</h1>
            </div>

            <?php echo $feedback_message; ?>

            <form id="formTambahPenyaluran" action="system/proses-tambah-penyaluran.php" method="POST" enctype="multipart/form-data">
                <div class="mb-6">
                    <label for="id_penyaluran_display" class="block text-sm font-medium text-dark-text mb-1">ID Penyaluran</label>
                    <input type="text" id="id_penyaluran_display" name="id_penyaluran_display" class="form-input w-full p-3 border rounded-md shadow-sm bg-gray-100" placeholder="Akan di-generate otomatis oleh sistem" readonly>
                     <p class="text-xs text-slate-500 mt-1">ID akan dibuat otomatis oleh sistem saat disimpan.</p>
                </div>

                <div class="mb-6">
                    <label for="id_pembayaran_terkait" class="block text-sm font-medium text-dark-text mb-1">ID Pembayaran Terkait (Opsional)</label>
                    <input type="text" id="id_pembayaran_terkait" name="id_pembayaran_terkait" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Contoh: ZKT001" value="<?php echo htmlspecialchars($form_data['id_pembayaran_terkait'] ?? ''); ?>">
                </div>

                <div class="mb-6">
                    <label for="deskripsi_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Deskripsi Penyaluran</label>
                    <textarea id="deskripsi_penyaluran" name="deskripsi_penyaluran" rows="4" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Jelaskan detail penyaluran, kepada siapa, untuk apa, jumlah penerima, dll." required><?php echo htmlspecialchars($form_data['deskripsi_penyaluran'] ?? ''); ?></textarea>
                </div>

                <div class="mb-6">
                    <label for="tanggal_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Tanggal Penyaluran</label>
                    <input type="date" id="tanggal_penyaluran" name="tanggal_penyaluran" value="<?php echo htmlspecialchars($form_data['tanggal_penyaluran'] ?? date('Y-m-d')); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" required>
                </div>
                
                <div class="mb-6">
                    <label for="nominal_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Nominal Penyaluran (Rp) (Opsional)</label>
                    <input type="number" step="0.01" id="nominal_penyaluran" name="nominal_penyaluran" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan nominal jika ada" value="<?php echo htmlspecialchars($form_data['nominal_penyaluran'] ?? ''); ?>">
                </div>

                <div class="mb-8">
                    <label for="dokumentasi_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Dokumentasi Penyaluran (Foto/File)</label>
                    <input type="file" id="dokumentasi_penyaluran" name="dokumentasi_penyaluran" class="form-input w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-primary-orange file:text-white
                        hover:file:bg-action-orange">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, PDF. Maksimal 5MB.</p>
                </div>
                
                <div class="flex items-center justify-end space-x-4">
                    <a href="kelola-penyaluran.php" class="btn-reset py-3 px-6 rounded-md font-semibold shadow-md">Batal</a>
                    <button type="submit" class="btn-primary py-3 px-6 rounded-md font-semibold shadow-md text-lg">
                        Simpan Penyaluran
                    </button>
                </div>
            </form>
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
