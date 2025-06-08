<?php
require_once 'system/session-handler.php';
require_admin_login(); // Pastikan hanya admin yang bisa akses

require_once 'system/db-connect.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = true; // Karena sudah di-require_admin_login
$user_name_display = get_current_user_name();
if (!$user_name_display && isset($_SESSION['user_nama'])) { // Fallback jika admin juga punya 'user_nama' di sesi user biasa
    $user_name_display = $_SESSION['user_nama'];
} elseif (!$user_name_display) {
    $user_name_display = 'Admin'; // Default jika tidak ada sesi
}
$dashboard_link = 'dashboard-admin.php';
$profile_link = 'profil-pengguna.php';


$data_penyaluran = [];
// Ambil data penyaluran dari database
$sql = "SELECT penyaluran_id, id_pembayaran_terkait, deskripsi_penyaluran, tanggal_penyaluran, nominal_penyaluran, dokumentasi 
        FROM penyaluran_zakat 
        ORDER BY tanggal_penyaluran DESC, created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_penyaluran[] = $row;
    }
} else {
    // Tidak ada data atau terjadi error, $data_penyaluran akan tetap kosong
    // error_log("Tidak ada data penyaluran atau query error: " . $conn->error);
}

$feedback_message = '';
// Menampilkan pesan sukses atau error dari sesi (setelah redirect dari proses_*.php)
if (isset($_SESSION['penyaluran_message'])) {
    $feedback_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Berhasil!</strong> '.htmlspecialchars($_SESSION['penyaluran_message']).'</div>';
    unset($_SESSION['penyaluran_message']);
} elseif (isset($_SESSION['penyaluran_error'])) {
    $feedback_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Gagal!</strong> '.htmlspecialchars($_SESSION['penyaluran_error']).'</div>';
    unset($_SESSION['penyaluran_error']);
}
// Juga bisa dari parameter GET jika proses-*.php redirect dengan GET
if (isset($_GET['status']) && isset($_GET['msg']) && empty($feedback_message) ) { // Hanya tampilkan jika belum ada dari session
    $msg_text = htmlspecialchars($_GET['msg']);
    if ($_GET['status'] === 'success') {
        $feedback_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Berhasil!</strong> '.$msg_text.'</div>';
    } else {
         $feedback_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Gagal!</strong> '.$msg_text.'</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyaluran Zakat</title>
    <link rel="icon" href="assets/logo_lazismu.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                'table-header-bg': '#F3F4F6', 
                'table-border': '#E5E7EB', 
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
        <div class="bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary-orange">Kelola Penyaluran Zakat</h1>
            </div>

            <?php echo $feedback_message; ?>

            <div class="mb-6 flex justify-end">
                <a href="tambah-penyaluran.php" class="btn-primary py-2 px-4 rounded-md font-semibold shadow-md flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Penyaluran
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-table-border">
                    <thead class="bg-table-header-bg">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">ID Penyaluran</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">ID Pembayaran Terkait</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Deskripsi Penyaluran</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Tanggal Penyaluran</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Dokumentasi</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (empty($data_penyaluran)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data penyaluran.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data_penyaluran as $penyaluran): ?>
                                <tr class="hover:bg-light-orange-bg">
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm"><?php echo htmlspecialchars($penyaluran['penyaluran_id']); ?></td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm"><?php echo htmlspecialchars($penyaluran['id_pembayaran_terkait'] ?? '-'); ?></td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm max-w-xs truncate" title="<?php echo htmlspecialchars($penyaluran['deskripsi_penyaluran']); ?>"><?php echo htmlspecialchars($penyaluran['deskripsi_penyaluran']); ?></td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm"><?php echo date('d M Y', strtotime($penyaluran['tanggal_penyaluran'])); ?></td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <?php if (!empty($penyaluran['dokumentasi'])): ?>
                                            <a href="uploads/dokumentasi-penyaluran/<?php echo htmlspecialchars($penyaluran['dokumentasi']); ?>" target="_blank" class="text-primary-orange hover:underline">Lihat</a>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <a href="edit-penyaluran.php?id=<?php echo htmlspecialchars($penyaluran['penyaluran_id']); ?>" class="text-blue-600 hover:text-blue-900 mr-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="hapusPenyaluran('<?php echo htmlspecialchars($penyaluran['penyaluran_id']); ?>')" class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">© <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        function hapusPenyaluran(id) {
            if (confirm(`Anda yakin ingin menghapus data penyaluran dengan ID: ${id}? Operasi ini tidak dapat dibatalkan.`)) {
                window.location.href = `system/proses-hapus-penyaluran.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
