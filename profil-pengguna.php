<?php
require_once 'system/session-handler.php';
require_login(); // Pengguna harus login untuk mengakses halaman ini

require_once 'system/db-connect.php';

$current_user_id_session = get_current_user_id();
$current_user_role = $_SESSION['role'] ?? 'user';
$current_user_name_session = get_current_user_name(); // Untuk header

$profile_user_id_to_load = $current_user_id_session; // Default ke profil sendiri
$page_title = "Profil Saya";
$can_edit_profile = true; // Pengguna selalu bisa edit profil sendiri

// Jika admin mencoba melihat profil pengguna lain
if ($current_user_role === 'admin' && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $requested_user_id = intval($_GET['user_id']);
    if ($requested_user_id !== $current_user_id_session) {
        $profile_user_id_to_load = $requested_user_id;
        $page_title = "Profil Pengguna";
        // Admin tidak bisa edit password pengguna lain, hanya data dasar jika diizinkan
        // Untuk saat ini, admin hanya bisa melihat profil pengguna lain, bukan mengeditnya melalui form ini.
        // Jika admin ingin edit data user lain, perlu form/mekanisme terpisah di kelola-pengguna.php
        $can_edit_profile = false; // Admin tidak edit user lain via form ini
    }
}


$user_data = null;
$error_fetch_user = '';

if ($profile_user_id_to_load) {
    $stmt = $conn->prepare("SELECT user_id, nama_lengkap, username, email, nomor_hp, role FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $profile_user_id_to_load);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            if ($is_admin_viewing_other = ($profile_user_id_to_load !== $current_user_id_session)) {
                 $page_title = "Profil Pengguna: " . htmlspecialchars($user_data['username']);
            }
        } else {
            $error_fetch_user = "Data pengguna dengan ID ".htmlspecialchars($profile_user_id_to_load)." tidak ditemukan.";
        }
        $stmt->close();
    } else {
        $error_fetch_user = "Gagal menyiapkan query: " . $conn->error;
    }
} else {
    $error_fetch_user = "ID Pengguna tidak valid untuk dimuat.";
}

$update_message = '';
// Ambil pesan dari sesi jika ada (setelah redirect dari proses-update-profil.php)
if (isset($_SESSION['update_profil_msg']) && isset($_SESSION['update_profil_status'])) {
    $msg_text = htmlspecialchars($_SESSION['update_profil_msg']);
    $msg_status_class = $_SESSION['update_profil_status'] === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    $msg_title = $_SESSION['update_profil_status'] === 'success' ? 'Berhasil!' : 'Gagal!';
    $update_message = '<div class="'.$msg_status_class.' px-4 py-3 rounded relative mb-6" role="alert"><strong class="font-bold">'.$msg_title.'</strong> <span class="block sm:inline">'.$msg_text.'</span></div>';
    unset($_SESSION['update_profil_msg']);
    unset($_SESSION['update_profil_status']);
}

$current_page = basename($_SERVER['PHP_SELF']); // Untuk logika header
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Kalkulator Zakat</title>
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
                
                <a href="<?php echo ($current_user_role === 'admin') ? 'dashboard-admin.php' : 'dashboard.php'; ?>" class="text-sm font-medium text-dark-text hover:text-primary-orange transition-colors">
                    Dashboard
                </a>
                <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($current_user_name_session ?? 'Pengguna'); ?>!</span>
                <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        <div class="max-w-xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary-orange"><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <?php echo $update_message; ?>
            <?php if (!empty($error_fetch_user)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_fetch_user); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($user_data): ?>
            <form id="formProfilPengguna" action="system/proses-update-profil.php" method="POST">
                <input type="hidden" name="id_pengguna" value="<?php echo htmlspecialchars($user_data['user_id']); ?>">

                <div class="mb-6">
                    <label for="nama_lengkap" class="block text-sm font-medium text-dark-text mb-1">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" <?php echo !$can_edit_profile ? 'readonly class="bg-gray-100"' : 'required'; ?>>
                </div>

                <div class="mb-6">
                    <label for="username" class="block text-sm font-medium text-dark-text mb-1">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" class="form-input w-full p-3 border rounded-md shadow-sm bg-gray-100" readonly>
                    <p class="text-xs text-slate-500 mt-1">Username tidak dapat diubah.</p>
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-dark-text mb-1">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" <?php echo !$can_edit_profile ? 'readonly class="bg-gray-100"' : 'required'; ?>>
                </div>

                <div class="mb-6">
                    <label for="nomor_hp" class="block text-sm font-medium text-dark-text mb-1">Nomor HP/WhatsApp</label>
                    <input type="tel" id="nomor_hp" name="nomor_hp" value="<?php echo htmlspecialchars($user_data['nomor_hp']); ?>" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Contoh: 081234567890" <?php echo !$can_edit_profile ? 'readonly class="bg-gray-100"' : 'required pattern="[0-9]{10,15}"'; ?>>
                </div>
                
                <?php if ($can_edit_profile): // Hanya tampilkan form ubah password jika bisa edit profil sendiri ?>
                <hr class="my-8">
                <h2 class="text-xl font-semibold text-action-orange mb-4">Ubah Password (Opsional)</h2>
                 <div class="mb-6">
                    <label for="password_lama" class="block text-sm font-medium text-dark-text mb-1">Password Saat Ini</label>
                    <input type="password" id="password_lama" name="password_lama" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Masukkan password Anda saat ini">
                    <p class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengubah password.</p>
                </div>
                <div class="mb-6">
                    <label for="password_baru" class="block text-sm font-medium text-dark-text mb-1">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Minimal 6 karakter">
                </div>
                <div class="mb-8">
                    <label for="konfirmasi_password_baru" class="block text-sm font-medium text-dark-text mb-1">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password_baru" name="konfirmasi_password_baru" class="form-input w-full p-3 border rounded-md shadow-sm" placeholder="Ulangi password baru">
                </div>
                <?php endif; ?>
                
                <div class="flex items-center justify-end space-x-4 mt-10">
                     <a href="<?php echo ($current_user_role === 'admin' && !$is_admin_viewing_other) ? 'dashboard-admin.php' : 'dashboard.php'; ?>" class="btn-reset py-3 px-6 rounded-md font-semibold shadow-md">Kembali</a>
                    <?php if ($can_edit_profile): ?>
                    <button type="submit" class="btn-primary py-3 px-6 rounded-md font-semibold shadow-md text-lg">
                        Simpan Perubahan
                    </button>
                    <?php endif; ?>
                </div>
            </form>
            <?php elseif(empty($error_fetch_user)): ?>
                <p class="text-center text-slate-600">Tidak dapat memuat data profil.</p>
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
