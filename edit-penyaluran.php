<?php
require_once 'system/session-handler.php';
require_admin_login();

require_once 'system/db-connect.php';

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

$id_penyaluran_edit = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;
$data_penyaluran_edit = null;
$error_message = '';
$feedback_message = '';

// Ambil pesan dari sesi jika ada (setelah redirect dari proses-edit-penyaluran.php)
if (isset($_SESSION['penyaluran_message'])) {
    $feedback_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Berhasil!</strong> '.htmlspecialchars($_SESSION['penyaluran_message']).'</div>';
    unset($_SESSION['penyaluran_message']);
} elseif (isset($_SESSION['penyaluran_error'])) {
    $feedback_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Gagal!</strong> '.htmlspecialchars($_SESSION['penyaluran_error']).'</div>';
    unset($_SESSION['penyaluran_error']);
}


if ($id_penyaluran_edit) {
    $stmt = $conn->prepare("SELECT penyaluran_id, id_pembayaran_terkait, deskripsi_penyaluran, tanggal_penyaluran, nominal_penyaluran, dokumentasi FROM penyaluran_zakat WHERE penyaluran_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $id_penyaluran_edit); // Asumsi penyaluran_id bisa VARCHAR jika bukan INT AUTO_INCREMENT
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $data_penyaluran_edit = $result->fetch_assoc();
            // Ganti nama kolom 'dokumentasi' menjadi 'dokumentasi_lama' untuk form
            if (isset($data_penyaluran_edit['dokumentasi'])) {
                $data_penyaluran_edit['dokumentasi_lama'] = $data_penyaluran_edit['dokumentasi'];
            }
        } else {
            $error_message = "Data penyaluran dengan ID " . htmlspecialchars($id_penyaluran_edit) . " tidak ditemukan.";
        }
        $stmt->close();
    } else {
        $error_message = "Gagal menyiapkan query untuk mengambil data penyaluran: " . $conn->error;
        error_log("Error prepare statement edit penyaluran: " . $conn->error);
    }
} else {
    $error_message = "ID Penyaluran tidak disediakan untuk diedit.";
}

// Jika data tidak ditemukan dan ada error message, set default agar form tidak error
if (!$data_penyaluran_edit && !empty($error_message)) {
     $data_penyaluran_edit = [
        'penyaluran_id' => $id_penyaluran_edit ?? 'ERROR',
        'id_pembayaran_terkait' => '',
        'deskripsi_penyaluran' => '',
        'tanggal_penyaluran' => date('Y-m-d'),
        'nominal_penyaluran' => '',
        'dokumentasi_lama' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Penyaluran Zakat</title>
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
                <h1 class="text-3xl font-bold text-primary-orange">Edit Data Penyaluran Zakat</h1>
                <?php if ($data_penyaluran_edit && $data_penyaluran_edit['penyaluran_id'] !== 'ERROR'): ?>
                    <p class="text-slate-600 mt-1">ID Penyaluran: <?php echo htmlspecialchars($data_penyaluran_edit['penyaluran_id']); ?></p>
                <?php endif; ?>
            </div>

            <?php echo $feedback_message; ?>
            <?php if (!empty($error_message) && (!$data_penyaluran_edit || $data_penyaluran_edit['penyaluran_id'] === 'ERROR')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                     <p class="mt-2"><a href="kelola-penyaluran.php" class="text-sm text-red-700 hover:text-red-900 underline">Kembali ke Daftar Penyaluran</a></p>
                </div>
            <?php endif; ?>

            <?php if ($data_penyaluran_edit && $data_penyaluran_edit['penyaluran_id'] !== 'ERROR'): ?>
                <form id="formEditPenyaluran" action="system/proses-edit-penyaluran.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_penyaluran_asli" value="<?php echo htmlspecialchars($data_penyaluran_edit['penyaluran_id']); ?>">
                    
                    <div class="mb-6">
                        <label for="id_pembayaran_terkait" class="block text-sm font-medium text-dark-text mb-1">ID Pembayaran Terkait (Opsional)</label>
                        <input type="text" id="id_pembayaran_terkait" name="id_pembayaran_terkait" value="<?php echo htmlspecialchars($data_penyaluran_edit['id_pembayaran_terkait'] ?? ''); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Contoh: ZKT001">
                    </div>

                    <div class="mb-6">
                        <label for="deskripsi_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Deskripsi Penyaluran</label>
                        <textarea id="deskripsi_penyaluran" name="deskripsi_penyaluran" rows="4" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Jelaskan detail penyaluran..." required><?php echo htmlspecialchars($data_penyaluran_edit['deskripsi_penyaluran']); ?></textarea>
                    </div>

                    <div class="mb-6">
                        <label for="tanggal_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Tanggal Penyaluran</label>
                        <input type="date" id="tanggal_penyaluran" name="tanggal_penyaluran" value="<?php echo htmlspecialchars($data_penyaluran_edit['tanggal_penyaluran']); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" required>
                    </div>
                     <div class="mb-6">
                        <label for="nominal_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Nominal Penyaluran (Rp) (Opsional)</label>
                        <input type="number" step="0.01" id="nominal_penyaluran" name="nominal_penyaluran" value="<?php echo htmlspecialchars($data_penyaluran_edit['nominal_penyaluran'] ?? ''); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan nominal jika ada">
                    </div>

                    <div class="mb-8">
                        <label for="dokumentasi_penyaluran" class="block text-sm font-medium text-dark-text mb-1">Dokumentasi Penyaluran Baru (Opsional)</label>
                        <input type="file" id="dokumentasi_penyaluran" name="dokumentasi_penyaluran" class="form-input w-full text-sm text-slate-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-primary-orange file:text-white
                            hover:file:bg-action-orange">
                        <p class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah dokumentasi. Format: JPG, PNG, PDF. Maksimal 5MB.</p>
                        <?php if (!empty($data_penyaluran_edit['dokumentasi_lama'])): ?>
                            <p class="text-sm text-slate-600 mt-2">Dokumentasi saat ini: 
                                <a href="uploads/dokumentasi-penyaluran/<?php echo htmlspecialchars($data_penyaluran_edit['dokumentasi_lama']); ?>" target="_blank" class="text-primary-orange hover:underline">
                                    <?php echo htmlspecialchars($data_penyaluran_edit['dokumentasi_lama']); ?>
                                </a>
                            </p>
                            <input type="hidden" name="dokumentasi_lama" value="<?php echo htmlspecialchars($data_penyaluran_edit['dokumentasi_lama']); ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4">
                        <a href="kelola-penyaluran.php" class="btn-reset py-3 px-6 rounded-md font-semibold shadow-md">Batal</a>
                        <button type="submit" class="btn-primary py-3 px-6 rounded-md font-semibold shadow-md text-lg">
                            Update Penyaluran
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
