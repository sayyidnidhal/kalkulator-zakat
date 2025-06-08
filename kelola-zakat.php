<?php
require_once 'system/session-handler.php';
require_admin_login();

require_once 'system/db-connect.php';

// Data untuk Header
$current_page = basename($_SERVER['PHP_SELF']);
$user_name_display = get_current_user_name() ?? 'Admin';
$dashboard_link = 'dashboard-admin.php';
$profile_link = 'profil-pengguna.php';

$data_pembayaran = [];
// --- Query SQL untuk mengambil data pembayaran, termasuk username ---
$sql = "SELECT p.pembayaran_id, p.user_id, u.username, COALESCE(u.nama_lengkap, p.nama_pembayar) AS nama_final_pembayar, p.tanggal_bayar, p.jenis_zakat_display, p.nominal_bayar, p.bukti_bayar, p.status_verifikasi, p.keterangan_verifikasi
        FROM pembayaran_zakat p 
        LEFT JOIN users u ON p.user_id = u.user_id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data_pembayaran[] = $row;
    }
} else {
    // error_log("Tidak ada data pembayaran atau query error: " . $conn->error);
}

function formatRupiah($angka) {
    if (!is_numeric($angka)) { return htmlspecialchars($angka); }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Logika untuk menampilkan pesan feedback dari proses di backend
$feedback_message = '';
if (isset($_GET['status']) && isset($_GET['msg'])) {
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
    <title>Kelola Pembayaran Zakat</title>
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
                'verified-yes': '#10B981', 
                'verified-no': '#EF4444',  
                'verified-pending': '#F59E0B', 
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
                <h1 class="text-3xl font-bold text-primary-orange">Kelola Pembayaran Zakat</h1>
            </div>
            
            <?php echo $feedback_message; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-table-border">
                    <thead class="bg-table-header-bg">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Detail Pembayaran</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Nama Pembayar</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Jenis & Nominal</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Bukti</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Status Verifikasi</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm border-b border-table-border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (empty($data_pembayaran)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Tidak ada data pembayaran.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data_pembayaran as $pembayaran): ?>
                                <tr class="hover:bg-light-orange-bg">
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm">
                                        <span class="font-bold"><?php echo htmlspecialchars($pembayaran['pembayaran_id']); ?></span>
                                        <!-- Perubahan di sini: menghapus format jam (H:i) -->
                                        <span class="block text-xs text-gray-500"><?php echo date('d M Y', strtotime($pembayaran['tanggal_bayar'])); ?></span>
                                    </td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm">
                                        <?php echo htmlspecialchars($pembayaran['nama_final_pembayar'] ?? '-'); ?>
                                        <!-- Perubahan di sini: menampilkan username sebagai teks biasa, bukan link -->
                                        <span class="text-xs text-gray-500 block">
                                            (@<?php echo htmlspecialchars($pembayaran['username'] ?? 'non-terdaftar'); ?>)
                                        </span>
                                    </td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm">
                                        <span class="font-medium"><?php echo htmlspecialchars($pembayaran['jenis_zakat_display']); ?></span>
                                        <span class="block text-primary-orange"><?php echo formatRupiah($pembayaran['nominal_bayar']); ?></span>
                                    </td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <a href="uploads/bukti-pembayaran/<?php echo htmlspecialchars($pembayaran['bukti_bayar']); ?>" target="_blank" class="text-primary-orange hover:underline">Lihat</a>
                                    </td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full 
                                            <?php 
                                                $status_ver = $pembayaran['status_verifikasi'] ?? 'PENDING';
                                                if ($status_ver === 'YA') echo 'bg-green-100 text-verified-yes';
                                                elseif ($status_ver === 'TIDAK') echo 'bg-red-100 text-verified-no';
                                                else echo 'bg-yellow-100 text-verified-pending'; 
                                            ?>">
                                            <?php echo $status_ver === 'YA' ? 'Terverifikasi' : $status_ver; ?>
                                        </span>
                                        <?php if ($status_ver === 'TIDAK' && !empty($pembayaran['keterangan_verifikasi'])): ?>
                                            <p class="text-xs text-red-600 mt-1 italic" title="<?php echo htmlspecialchars($pembayaran['keterangan_verifikasi']); ?>">
                                                Ket: <?php echo htmlspecialchars(substr($pembayaran['keterangan_verifikasi'], 0, 50)); ?>...
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <?php if ($status_ver === 'PENDING' || $status_ver === 'TIDAK'): ?>
                                            <a href="system/proses-kelola-zakat.php?action=verifikasi&status=YA&id=<?php echo htmlspecialchars($pembayaran['pembayaran_id']); ?>" onclick="return confirm('Anda yakin ingin memverifikasi pembayaran ini?')" class="text-green-600 hover:text-green-900 mr-2" title="Setujui Verifikasi">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($status_ver === 'PENDING' || $status_ver === 'YA'): ?>
                                            <button onclick="showTolakModal('<?php echo htmlspecialchars($pembayaran['pembayaran_id']); ?>')" class="text-yellow-600 hover:text-yellow-900 mr-2" title="Tolak Verifikasi">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="hapusPembayaran('<?php echo htmlspecialchars($pembayaran['pembayaran_id']); ?>')" class="text-red-600 hover:text-red-900" title="Hapus">
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

    <div id="tolakModal" class="modal-overlay">
        <div class="modal-content">
            <h3 class="text-xl font-semibold mb-4 text-dark-text">Tolak Verifikasi Pembayaran</h3>
            <p class="text-sm text-slate-600 mb-4">Mohon berikan alasan penolakan verifikasi.</p>
            <form id="formTolakVerifikasi" action="system/proses-kelola-zakat.php" method="POST">
                <input type="hidden" name="action" value="verifikasi">
                <input type="hidden" name="status" value="TIDAK">
                <input type="hidden" id="tolak_pembayaran_id" name="id_pembayaran">
                
                <div class="mb-6">
                    <label for="keterangan_verifikasi" class="block text-sm font-medium text-dark-text mb-1">Alasan Penolakan</label>
                    <textarea id="keterangan_verifikasi" name="keterangan" rows="3" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Contoh: Bukti pembayaran tidak jelas/tidak sesuai" required></textarea>
                </div>

                <div class="flex justify-end items-center space-x-3">
                    <button type="button" onclick="closeTolakModal()" class="btn-reset py-2 px-4 rounded-md font-semibold shadow-md">Batal</button>
                    <button type="submit" class="btn-primary py-2 px-4 rounded-md font-semibold shadow-md">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">© <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
        const tolakModal = document.getElementById('tolakModal');
        const tolakPembayaranIdInput = document.getElementById('tolak_pembayaran_id');

        function showTolakModal(id) {
            if (tolakModal && tolakPembayaranIdInput) {
                tolakPembayaranIdInput.value = id;
                tolakModal.classList.add('active');
            }
        }

        function closeTolakModal() {
            if (tolakModal) {
                tolakModal.classList.remove('active');
            }
        }

        function hapusPembayaran(id) {
            if (confirm(`Anda yakin ingin menghapus pembayaran dengan ID: ${id}? Operasi ini tidak dapat dibatalkan.`)) {
                window.location.href = `system/proses-kelola-zakat.php?action=hapus&id=${id}`;
            }
        }
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
