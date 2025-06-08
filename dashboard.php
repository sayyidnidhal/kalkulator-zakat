<?php
require_once 'system/session-handler.php';
require_login(); 
if(is_admin_logged_in()){ 
    header('Location: dashboard-admin.php');
    exit;
}

require_once 'system/db-connect.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = true; 
$user_name_display = get_current_user_name();
if (!$user_name_display) {
    $user_name_display = 'Pengguna';
}
$dashboard_link = 'dashboard.php'; 
$profile_link = 'profil-pengguna.php'; 


$id_pengguna_login = get_current_user_id();
$riwayat_pembayaran_user = [];
$total_zakat_terverifikasi = 0;
$user_pembayaran_ids = []; // Array untuk menampung ID pembayaran pengguna

if ($id_pengguna_login) {
    // 1. Ambil semua riwayat pembayaran dan ID-nya
    $stmt_pembayaran = $conn->prepare("SELECT pembayaran_id, tanggal_bayar, jenis_zakat_display, nominal_bayar, bukti_bayar, status_verifikasi, keterangan_verifikasi FROM pembayaran_zakat WHERE user_id = ? ORDER BY created_at DESC");
    if ($stmt_pembayaran) {
        $stmt_pembayaran->bind_param("i", $id_pengguna_login);
        $stmt_pembayaran->execute();
        $result_pembayaran = $stmt_pembayaran->get_result();
        while ($row = $result_pembayaran->fetch_assoc()) {
            $riwayat_pembayaran_user[] = $row;
            $user_pembayaran_ids[] = $row['pembayaran_id']; // Kumpulkan ID pembayaran
            if ($row['status_verifikasi'] === 'YA' && is_numeric($row['nominal_bayar'])) {
                $total_zakat_terverifikasi += (float)$row['nominal_bayar'];
            }
        }
        $stmt_pembayaran->close();
    }
}

// 2. Ambil info penyaluran HANYA JIKA terkait dengan pembayaran pengguna
$info_penyaluran = [];
if (!empty($user_pembayaran_ids)) {
    // Buat placeholder '?' sebanyak jumlah ID pembayaran
    $placeholders = implode(',', array_fill(0, count($user_pembayaran_ids), '?'));
    // Buat string tipe data untuk bind_param (misal: 'ssi' untuk string, string, integer)
    $types = str_repeat('s', count($user_pembayaran_ids));
    
    $sql_penyaluran = "SELECT penyaluran_id, deskripsi_penyaluran, tanggal_penyaluran, dokumentasi 
                       FROM penyaluran_zakat 
                       WHERE id_pembayaran_terkait IN ($placeholders) 
                       ORDER BY tanggal_penyaluran DESC";
                       
    $stmt_penyaluran = $conn->prepare($sql_penyaluran);
    if ($stmt_penyaluran) {
        // Bind semua ID pembayaran ke placeholder
        $stmt_penyaluran->bind_param($types, ...$user_pembayaran_ids);
        $stmt_penyaluran->execute();
        $result_penyaluran = $stmt_penyaluran->get_result();
        while ($row = $result_penyaluran->fetch_assoc()) {
            $info_penyaluran[] = $row;
        }
        $stmt_penyaluran->close();
    }
}


function formatRupiah($angka) {
    if (!is_numeric($angka)) {
        return htmlspecialchars($angka);
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna - Kalkulator Zakat</title>
    <link rel="icon" href="assets/logo_lazismu.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                'primary-orange': '#F97316',
                'action-orange': '#F57F17',
                'light-orange-bg': '#FFF7ED',
                'dark-text': '#374151',
                'light-bg': '#F9FAFB', 
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
                <a href="index.php" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Menu Utama</a>
                <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                <span class="hidden md:inline text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name_display); ?>!</span>
                <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-dark-text">Dashboard Saya</h1>
            <p class="text-slate-500 mt-1">Selamat datang kembali, <?php echo htmlspecialchars($user_name_display); ?>!</p>
        </div>

        <!-- Ringkasan & Aksi Utama -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl shadow-lg flex flex-col justify-between">
                <div>
                    <div class="flex items-center opacity-80">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p class="text-lg font-medium">Total Zakat Terverifikasi</p>
                    </div>
                    <p class="text-4xl lg:text-5xl font-bold mt-2"><?php echo formatRupiah($total_zakat_terverifikasi); ?></p>
                </div>
                <p class="text-xs opacity-70 mt-4">Total semua pembayaran zakat Anda yang telah disetujui oleh admin.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg flex flex-col items-center justify-center text-center">
                 <i class="fas fa-calculator text-4xl text-primary-orange mb-4"></i>
                <h3 class="text-xl font-semibold text-dark-text">Hitung Zakat Baru</h3>
                <p class="text-sm text-slate-500 mt-1 mb-4">Mulai hitung kewajiban zakat Anda sekarang.</p>
                <a href="index.php#selection-box" class="btn-primary w-full py-3 px-6 rounded-md font-semibold shadow-md">
                    Mulai Hitung
                </a>
            </div>
        </div>

        <!-- Tabel Riwayat Pembayaran -->
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg mb-8">
            <h2 class="text-2xl font-semibold text-dark-text mb-6">Riwayat Pembayaran Zakat Saya</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-table-header-bg">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID Pembayaran</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Tanggal Bayar</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Jenis Zakat</th>
                            <th class="text-right py-3 px-4 uppercase font-semibold text-sm">Nominal</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Bukti</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (empty($riwayat_pembayaran_user)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-6 text-slate-500">
                                    <i class="fas fa-folder-open text-3xl mb-2"></i>
                                    <p>Anda belum memiliki riwayat pembayaran.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayat_pembayaran_user as $pembayaran): ?>
                                <tr>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm font-mono"><?php echo htmlspecialchars($pembayaran['pembayaran_id']); ?></td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm"><?php echo date('d M Y', strtotime($pembayaran['tanggal_bayar'])); ?></td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm"><?php echo htmlspecialchars($pembayaran['jenis_zakat_display']); ?></td>
                                    <td class="text-right py-3 px-4 border-b border-table-border text-sm font-medium"><?php echo formatRupiah($pembayaran['nominal_bayar']); ?></td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <?php if (!empty($pembayaran['bukti_bayar'])): ?>
                                        <a href="uploads/bukti-pembayaran/<?php echo htmlspecialchars($pembayaran['bukti_bayar']); ?>" target="_blank" class="text-primary-orange hover:underline">Lihat</a>
                                        <?php else: echo "-"; endif; ?>
                                    </td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full 
                                            <?php 
                                                $status_verifikasi = $pembayaran['status_verifikasi'] ?? 'PENDING';
                                                if ($status_verifikasi === 'YA') echo 'bg-green-100 text-verified-yes';
                                                elseif ($status_verifikasi === 'TIDAK') echo 'bg-red-100 text-verified-no';
                                                else echo 'bg-yellow-100 text-verified-pending'; 
                                            ?>">
                                            <?php echo $status_verifikasi === 'YA' ? 'Terverifikasi' : $status_verifikasi; ?>
                                        </span>
                                        <?php if ($status_verifikasi === 'TIDAK' && !empty($pembayaran['keterangan_verifikasi'])): ?>
                                            <p class="text-xs text-red-600 mt-1 italic" title="<?php echo htmlspecialchars($pembayaran['keterangan_verifikasi']); ?>">
                                                Ket: <?php echo htmlspecialchars(substr($pembayaran['keterangan_verifikasi'], 0, 30)); ?>...
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white p-6 md:p-8 rounded-xl shadow-lg">
            <h2 class="text-2xl font-semibold text-dark-text mb-6">Informasi Penyaluran Zakat Saya</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-table-header-bg">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Tanggal Penyaluran</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Deskripsi Penyaluran</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Dokumentasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (empty($info_penyaluran)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-6 text-slate-500">
                                    <i class="fas fa-bullhorn text-3xl mb-2"></i>
                                    <p>Belum ada informasi penyaluran yang terkait dengan zakat Anda.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($info_penyaluran as $penyaluran): ?>
                                <tr>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm"><?php echo date('d M Y', strtotime($penyaluran['tanggal_penyaluran'])); ?></td>
                                    <td class="text-left py-3 px-4 border-b border-table-border text-sm max-w-md"><?php echo htmlspecialchars($penyaluran['deskripsi_penyaluran']); ?></td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <?php if (!empty($penyaluran['dokumentasi'])): ?>
                                            <a href="uploads/dokumentasi-penyaluran/<?php echo htmlspecialchars($penyaluran['dokumentasi']); ?>" target="_blank" class="text-primary-orange hover:underline">Lihat</a>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">&copy; <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
