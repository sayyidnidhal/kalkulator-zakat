<?php
require_once 'system/session-handler.php';
require_admin_login();
require_once 'system/db-connect.php';

// Data untuk Header
$current_page = basename($_SERVER['PHP_SELF']);
$user_name_display = get_current_user_name() ?? 'Admin';
$dashboard_link = 'dashboard-admin.php';
$profile_link = 'profil-pengguna.php';
$current_admin_id = get_current_user_id(); // ID admin yang sedang login

// Ambil semua pengguna dari database
$daftar_pengguna = [];
$sql = "SELECT user_id, nama_lengkap, username, email, role, status_akun FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $daftar_pengguna[] = $row;
    }
}

// Logika untuk menampilkan pesan feedback dari proses backend
$feedback_message = '';
if (isset($_SESSION['kelola_pengguna_msg'])) {
    $feedback_message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Berhasil!</strong> '.htmlspecialchars($_SESSION['kelola_pengguna_msg']).'</div>';
    unset($_SESSION['kelola_pengguna_msg']);
} elseif (isset($_SESSION['kelola_pengguna_error'])) {
    $feedback_message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">Gagal!</strong> '.htmlspecialchars($_SESSION['kelola_pengguna_error']).'</div>';
    unset($_SESSION['kelola_pengguna_error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin</title>
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
                <h1 class="text-3xl font-bold text-primary-orange">Kelola Pengguna</h1>
            </div>

            <?php echo $feedback_message; ?>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-table-border">
                    <thead class="bg-table-header-bg">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Lengkap</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Username</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Role</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Status Akun</th>
                            <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (empty($daftar_pengguna)): ?>
                            <tr><td colspan="7" class="text-center py-4">Tidak ada data pengguna.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_pengguna as $pengguna): ?>
                                <?php if ($pengguna['role'] !== 'admin'): // Tampilkan hanya jika bukan admin ?>
                                <tr class="hover:bg-light-orange-bg">
                                    <td class="py-3 px-4 border-b border-table-border text-sm"><?php echo $pengguna['user_id']; ?></td>
                                    <td class="py-3 px-4 border-b border-table-border text-sm"><?php echo htmlspecialchars($pengguna['nama_lengkap']); ?></td>
                                    <td class="py-3 px-4 border-b border-table-border text-sm"><?php echo htmlspecialchars($pengguna['username']); ?></td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm"><?php echo htmlspecialchars($pengguna['role']); ?></td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full 
                                            <?php 
                                                $status = $pengguna['status_akun'];
                                                if ($status === 'ACTIVE') echo 'bg-green-100 text-verified-yes';
                                                elseif ($status === 'SUSPENDED') echo 'bg-red-100 text-verified-no';
                                                else echo 'bg-yellow-100 text-verified-pending'; 
                                            ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td class="text-center py-3 px-4 border-b border-table-border text-sm">
                                        <?php if($pengguna['status_akun'] === 'PENDING'): ?>
                                            <a href="system/proses-kelola-pengguna.php?action=activate&id=<?php echo $pengguna['user_id']; ?>" class="text-green-600 hover:text-green-900 mr-2" title="Verifikasi & Aktifkan Akun">
                                                <i class="fas fa-user-check"></i>
                                            </a>
                                        <?php elseif($pengguna['status_akun'] === 'SUSPENDED'): ?>
                                            <a href="system/proses-kelola-pengguna.php?action=activate&id=<?php echo $pengguna['user_id']; ?>" class="text-green-600 hover:text-green-900 mr-2" title="Aktifkan Kembali Akun">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($pengguna['status_akun'] === 'ACTIVE'): ?>
                                            <button onclick="suspendUser('<?php echo $pengguna['user_id']; ?>')" class="text-yellow-600 hover:text-yellow-900 mr-2" title="Tangguhkan Akun">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="hapusUser('<?php echo $pengguna['user_id']; ?>')" class="text-red-600 hover:text-red-900" title="Hapus Pengguna">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endif; ?>
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
        
        function suspendUser(id) {
            if (confirm(`Anda yakin ingin menangguhkan (suspend) pengguna dengan ID: ${id}? Pengguna ini tidak akan bisa login.`)) {
                window.location.href = `system/proses-kelola-pengguna.php?action=suspend&id=${id}`;
            }
        }
        
        function hapusUser(id) {
            if (confirm(`PERINGATAN: Anda yakin ingin menghapus pengguna dengan ID: ${id}? Semua data pembayaran yang terkait akan kehilangan relasi pengguna. Operasi ini tidak dapat dibatalkan.`)) {
                window.location.href = `system/proses-kelola-pengguna.php?action=hapus&id=${id}`;
            }
        }
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
