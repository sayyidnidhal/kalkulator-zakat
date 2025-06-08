<?php
require_once 'system/session-handler.php';
require_login(); // Ini akan menangani redirect jika belum login, sambil membawa parameter URL asli

require_once 'system/db-connect.php'; 

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = true; // Karena require_login() akan exit jika tidak login
$is_admin = is_admin_logged_in(); 

// Langsung definisikan $nama_lengkap_pengguna berdasarkan sesi
$nama_lengkap_pengguna = get_current_user_name(); 
if (!$nama_lengkap_pengguna) { 
    // Fallback jika nama tidak ada di sesi (seharusnya tidak terjadi jika login benar)
    $nama_lengkap_pengguna = $is_admin ? 'Admin Terdaftar' : 'Pengguna Terdaftar';
}

$dashboard_link = $is_admin ? 'dashboard-admin.php' : 'dashboard.php';
$profile_link = 'profil-pengguna.php';
$id_pengguna_login = get_current_user_id(); // Ini adalah ID user yang sedang login

// Mengambil data dari URL parameter (ini akan ada jika redirect dari login berhasil membawa parameter asli)
$jenisZakat = isset($_GET['jenis']) ? htmlspecialchars($_GET['jenis']) : 'Tidak Diketahui';
$jumlahZakatParam = isset($_GET['jumlah']) ? $_GET['jumlah'] : '0'; 

$isPeternakan = (strpos(strtolower($jenisZakat), 'peternakan') !== false);
if ($isPeternakan && !is_numeric($jumlahZakatParam)) { 
    $jumlahZakatFormatted = htmlspecialchars($jumlahZakatParam); 
    $jumlahZakatValue = htmlspecialchars($jumlahZakatParam); 
} else {
    $jumlahZakatNumerik = floatval($jumlahZakatParam);
    $jumlahZakatFormatted = 'Rp ' . number_format($jumlahZakatNumerik, 0, ',', '.');
    $jumlahZakatValue = $jumlahZakatNumerik;
}

$jenisZakatDisplay = ucwords(str_replace('_', ' ', $jenisZakat));
if ($jenisZakatDisplay === 'Harta Simpanan') $jenisZakatDisplay = 'Harta Simpanan & Investasi';
elseif ($jenisZakatDisplay === 'Perniagaan') $jenisZakatDisplay = 'Perniagaan & Perusahaan';
elseif ($jenisZakatDisplay === 'Profesi') $jenisZakatDisplay = 'Pendapatan, Profesi & Jasa';
elseif ($jenisZakatDisplay === 'Zakat Fitrah') $jenisZakatDisplay = 'Zakat Fitrah';

$todayDate = date('Y-m-d'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran Zakat</title>
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
                'info-bg': '#FEFCE8', 
                'info-border': '#FACC15', 
                'info-text': '#713F12', 
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
                
                <?php // Tombol Dashboard akan selalu ada jika login, kecuali jika sudah di halaman dashboard itu sendiri ?>
                <?php if (($is_admin && $current_page !== 'dashboard-admin.php') || (!$is_admin && $current_page !== 'dashboard.php')): ?>
                    <a href="<?php echo $dashboard_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Dashboard</a>
                <?php endif; ?>

                <?php // Tombol Profil Saya hanya jika berada di halaman dashboard ?>
                 <?php if (($current_page === 'dashboard.php' || $current_page === 'dashboard-admin.php') && $current_page !== 'profil-pengguna.php'): ?>
                     <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                <?php endif; ?>
                <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($nama_lengkap_pengguna); // Menggunakan $nama_lengkap_pengguna ?>!</span>
                <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        <div class="max-w-xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8">
                 <a href="javascript:history.back()" class="text-sm text-primary-orange hover:text-action-orange mb-4 inline-block">&larr; Kembali ke Kalkulator</a>
                <h1 class="text-3xl font-bold text-primary-orange">Konfirmasi Pembayaran Zakat</h1>
            </div>

            <div class="payment-details mb-8">
                <div class="bg-light-orange-bg p-4 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-dark-text mb-2">Detail Pembayaran</h2>
                    <div class="flex justify-between mb-1">
                        <span class="text-slate-600">Jenis Zakat:</span>
                        <span class="font-medium text-dark-text"><?php echo $jenisZakatDisplay; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Jumlah Pembayaran:</span>
                        <span class="font-bold text-xl text-primary-orange"><?php echo $jumlahZakatFormatted; ?></span>
                    </div>
                </div>
            </div>

            <div class="rekening-tujuan mb-8 p-4 border border-info-border bg-info-bg rounded-lg text-info-text">
                <h3 class="text-lg font-semibold mb-2">Rekening Tujuan Transfer</h3>
                <p class="text-sm">Silakan lakukan transfer ke rekening berikut:</p>
                <ul class="list-disc list-inside mt-2 text-sm">
                    <li><strong>BSI (Bank Syariah Indonesia):</strong> 7099271738 <br>a.n Lazismu PWM Kalteng</li>
                </ul>
            </div>

            <form id="paymentConfirmationForm" action="system/proses-pembayaran.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="jenis_zakat_raw" value="<?php echo htmlspecialchars($jenisZakat); ?>">
                <input type="hidden" name="jenis_zakat_display" value="<?php echo htmlspecialchars($jenisZakatDisplay); ?>">
                <input type="hidden" name="jumlah_zakat" value="<?php echo htmlspecialchars($jumlahZakatValue); ?>">
                <input type="hidden" name="id_pengguna" value="<?php echo htmlspecialchars($id_pengguna_login); ?>">
                <input type="hidden" name="nama_pembayar" value="<?php echo htmlspecialchars($nama_lengkap_pengguna); // Menggunakan $nama_lengkap_pengguna ?>">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-dark-text mb-1">Nama Pembayar (Muzakki)</label>
                    <p class="form-input w-full p-3 border rounded-md shadow-sm bg-gray-100"><?php echo htmlspecialchars($nama_lengkap_pengguna); // Menggunakan $nama_lengkap_pengguna ?></p>
                </div>
                
                <div class="mb-6">
                    <label for="tgl_bayar" class="block text-sm font-medium text-dark-text mb-1">Tanggal Pembayaran</label>
                    <input type="date" id="tgl_bayar" name="tgl_bayar" value="<?php echo $todayDate; ?>" class="form-input w-full p-3 border rounded-md shadow-sm" required>
                </div>

                <div class="mb-8">
                    <label for="bukti_pembayaran" class="block text-sm font-medium text-dark-text mb-1">Unggah Bukti Pembayaran</label>
                    <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" class="form-input w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-primary-orange file:text-white
                        hover:file:bg-action-orange" required>
                    <p class="text-xs text-slate-500 mt-1">Format yang diterima: JPG, PNG, PDF. Maksimal 2MB.</p>
                </div>
                
                <button type="submit" class="btn-primary w-full py-3 px-4 rounded-md font-semibold shadow-md text-lg">
                    Konfirmasi Pembayaran
                </button>
            </form>
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

            const paymentForm = document.getElementById('paymentConfirmationForm');
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(event) {
                    const fileInput = document.getElementById('bukti_pembayaran');
                    if (!fileInput || fileInput.files.length === 0) {
                        alert('Mohon unggah bukti pembayaran Anda.');
                        event.preventDefault();
                        return;
                    }
                    const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                    if (fileSize > 2) {
                        alert('Ukuran file bukti pembayaran maksimal 2MB.');
                        event.preventDefault();
                        return;
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
