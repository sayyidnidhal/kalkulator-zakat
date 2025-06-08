<?php
require_once 'system/session-handler.php'; 
require_once 'system/db-connect.php'; 

$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = is_user_logged_in() || is_admin_logged_in();
$user_name = null;
$dashboard_link = '#'; 
$profile_link = 'profil-pengguna.php';

if ($is_logged_in) {
    $user_name = get_current_user_name();
    if (is_admin_logged_in()) {
        $dashboard_link = 'dashboard-admin.php';
    } else { 
        $dashboard_link = 'dashboard.php';
    }
}

$pilihan_beras = [];
$sql_beras = "SELECT jenis_zakat, deskripsi, nilai FROM nisab WHERE jenis_zakat LIKE 'beras_%' ORDER BY nilai ASC";
$result_beras = $conn->query($sql_beras);
if ($result_beras && $result_beras->num_rows > 0) {
    while($row = $result_beras->fetch_assoc()) {
        $pilihan_beras[] = $row;
    }
}
// $conn->close(); // Akan ditutup di akhir skrip
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Zakat Fitrah</title>
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
                'btn-reset-bg': '#F87171', 
                'btn-reset-hover-bg': '#EF4444', 
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
                    <?php if ($current_page !== 'dashboard.php' && $current_page !== 'dashboard-admin.php'): ?>
                        <a href="<?php echo $dashboard_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Dashboard</a>
                    <?php endif; ?>
                     <?php if (($current_page === 'dashboard.php' || $current_page === 'dashboard-admin.php') && $current_page !== 'profil-pengguna.php'): ?>
                         <a href="<?php echo $profile_link; ?>" class="text-sm text-dark-text hover:text-primary-orange transition-colors">Profil Saya</a>
                    <?php endif; ?>
                    <span class="text-sm text-dark-text">Halo, <?php echo htmlspecialchars($user_name ?? 'Pengguna'); ?>!</span>
                    <a href="system/proses-logout.php" class="menu-utama-button text-sm">Logout</a>
                <?php else: ?>
                     <?php if ($current_page !== 'login.php' && $current_page !== 'registrasi.php'): ?>
                        <a href="login.php" class="login-button">Login</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 md:px-6 py-8 md:py-12">
        <div class="max-w-2xl mx-auto bg-white p-6 md:p-8 rounded-xl shadow-xl">
            <div class="mb-6"> 
                <div class="flex justify-end mb-2"> 
                    <a href="index.php" class="text-sm text-primary-orange hover:text-action-orange">← Kembali ke Halaman Utama</a>
                </div>
                <h1 class="text-3xl font-bold text-primary-orange text-center">Zakat Fitrah</h1> 
            </div>

            <div class="mb-8 p-4 bg-light-orange-bg border border-primary-orange/30 rounded-lg text-sm text-dark-text leading-relaxed">
                <p>
                    Zakat Fitrah adalah zakat yang ditunaikan pada bulan Ramadhan hingga sebelum
                    dilaksanakannya sholat Ied pada tanggal 1 Syawwal. Zakat yang dikeluarkan adalah dalam
                    bentuk makanan pokok atau uang dengan nilai setara. Besar zakat yang dibayarkan adalah
                    2,5 kg beras. Jenis beras sesuai dengan yang biasa dikonsumsi oleh muzakki sehari-hari.
                </p>
            </div>
            <form id="formZakatFitrah">
                <div class="grid md:grid-cols-1 gap-6">
                    <div>
                        <div class="mb-5">
                            <label for="fitrahRiceType" class="block text-sm font-medium text-dark-text mb-1">Pilih Jenis/Harga Beras yang Dikonsumsi</label>
                            <select id="fitrahRiceType" name="fitrah_rice_price" class="form-input w-full p-2.5 border rounded-md shadow-sm bg-white">
                                <?php if (empty($pilihan_beras)): ?>
                                    <option value="0">Data harga beras tidak tersedia</option>
                                <?php else: ?>
                                    <?php foreach ($pilihan_beras as $beras): ?>
                                        <option value="<?php echo htmlspecialchars($beras['nilai']); ?>">
                                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $beras['jenis_zakat']))) . ' - Rp ' . number_format($beras['nilai'], 0, ',', '.'); ?>/Kg
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Zakat fitrah adalah 2.5 kg beras atau nilainya per jiwa.</p>
                        </div>
                        <div class="mb-5">
                            <label for="fitrahPeopleCount" class="block text-sm font-medium text-dark-text mb-1">Jumlah Jiwa</label>
                            <input type="number" id="fitrahPeopleCount" value="1" min="1" class="form-input w-full p-2.5 border rounded-md shadow-sm">
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" id="calculateFitrahBtn" class="btn-primary w-full py-3 px-4 rounded-md font-semibold shadow-md">Hitung Zakat</button>
                            <button type="reset" id="resetFitrahBtn" class="btn-reset w-1/3 py-3 px-4 rounded-md font-semibold shadow-md">Reset</button>
                        </div>
                    </div>
                    <div class="mt-6 md:mt-2">
                        <div id="resultFitrah" class="result-card p-4 rounded-md text-center">
                            <p class="text-lg font-semibold text-dark-text">Total Zakat Fitrah Anda:</p>
                            <p id="zakatAmountFitrah" class="text-3xl font-bold text-primary-orange my-1">Rp 0</p>
                            <p id="zakatAmountRice" class="text-lg text-slate-700 mb-2">(setara dengan 0 Kg Beras)</p>
                        </div>
                        <button type="button" id="payZakatFitrahBtn" class="btn-secondary w-full py-3 px-4 rounded-md font-semibold shadow-md mt-4">
                            Bayar Zakat Fitrah
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200">
        <p class="text-sm text-slate-600">© <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <div id="customModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modalTitle" class="text-xl font-semibold mb-4 text-dark-text"></h3>
            <p id="modalMessage" class="text-slate-600 mb-6"></p>
            <button id="modalCloseBtn" class="btn-primary w-full py-2 px-4 rounded-md font-semibold">Tutup</button>
        </div>
    </div>

    <script>
        function formatCurrency(amount) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount); }
        function getNumericValue(id) { const value = document.getElementById(id).value; return parseFloat(value) || 0; }
        const modal = document.getElementById('customModal');
        const modalTitleEl = document.getElementById('modalTitle');
        const modalMessageEl = document.getElementById('modalMessage');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        function showModal(title, message) { if (modal && modalTitleEl && modalMessageEl) { modalTitleEl.textContent = title; modalMessageEl.textContent = message; modal.classList.add('active'); } else { alert(title + "\n\n" + message); } }
        function closeModal() { if (modal) { modal.classList.remove('active'); } }
        if (modalCloseBtn) { modalCloseBtn.addEventListener('click', closeModal); }
        if (modal) { modal.addEventListener('click', (event) => { if (event.target === modal) { closeModal(); } }); }

        document.addEventListener('DOMContentLoaded', function() {
            const zakatAmountFitrahEl = document.getElementById('zakatAmountFitrah');
            const zakatAmountRiceEl = document.getElementById('zakatAmountRice'); 
            const fitrahRiceTypeEl = document.getElementById('fitrahRiceType'); 
            const fitrahPeopleCountEl = document.getElementById('fitrahPeopleCount');
            const calculateFitrahBtn = document.getElementById('calculateFitrahBtn');
            const resetFitrahBtn = document.getElementById('resetFitrahBtn');
            const payZakatFitrahBtn = document.getElementById('payZakatFitrahBtn');
            const currentYearEl = document.getElementById('currentYear');

            if (currentYearEl) { currentYearEl.textContent = new Date().getFullYear(); }
            if (calculateFitrahBtn) {
                calculateFitrahBtn.addEventListener('click', () => {
                    const ricePricePerKg = parseFloat(fitrahRiceTypeEl.value) || 0; 
                    const peopleCount = getNumericValue('fitrahPeopleCount');
                    if (peopleCount <= 0) { if(zakatAmountFitrahEl) zakatAmountFitrahEl.textContent = formatCurrency(0); if(zakatAmountRiceEl) zakatAmountRiceEl.textContent = "(setara dengan 0 Kg Beras)"; showModal("Input Tidak Valid", "Jumlah jiwa harus lebih dari 0."); return; }
                    if (ricePricePerKg <= 0) { if(zakatAmountFitrahEl) zakatAmountFitrahEl.textContent = formatCurrency(0); if(zakatAmountRiceEl) zakatAmountRiceEl.textContent = "(setara dengan 0 Kg Beras)"; showModal("Input Tidak Valid", "Harga beras tidak valid atau belum dipilih."); return; }
                    const ricePerPersonKg = 2.5; const totalRiceKg = peopleCount * ricePerPersonKg; const totalZakatFitrah = totalRiceKg * ricePricePerKg;
                    if(zakatAmountFitrahEl) zakatAmountFitrahEl.textContent = formatCurrency(totalZakatFitrah);
                    if(zakatAmountRiceEl) zakatAmountRiceEl.textContent = `(setara dengan ${totalRiceKg.toLocaleString('id-ID')} Kg Beras)`;
                });
            }
            if (resetFitrahBtn) {
                resetFitrahBtn.addEventListener('click', () => {
                     if(zakatAmountFitrahEl) zakatAmountFitrahEl.textContent = formatCurrency(0);
                     if(zakatAmountRiceEl) zakatAmountRiceEl.textContent = "(setara dengan 0 Kg Beras)";
                     if(fitrahRiceTypeEl && fitrahRiceTypeEl.options.length > 0) fitrahRiceTypeEl.selectedIndex = 0; 
                     if(fitrahPeopleCountEl) fitrahPeopleCountEl.value = "1";   
                });
            }
            if (payZakatFitrahBtn) {
                payZakatFitrahBtn.addEventListener('click', function() {
                    const jumlahJiwa = getNumericValue('fitrahPeopleCount'); 
                    const hargaBeras = parseFloat(fitrahRiceTypeEl.value) || 0;
                    if (jumlahJiwa <= 0 || hargaBeras <= 0) { showModal("Data Belum Lengkap", "Mohon hitung zakat terlebih dahulu dan pastikan jumlah jiwa serta jenis beras valid."); return; }
                    const totalBerasKg = jumlahJiwa * 2.5; const jumlahBayar = totalBerasKg * hargaBeras;
                    if (jumlahBayar > 0) { window.location.href = `bayar-zakat.php?jenis=zakat_fitrah&jumlah=${jumlahBayar}`; } 
                    else { showModal("Perhitungan Belum Selesai", "Mohon hitung zakat Anda terlebih dahulu sebelum melanjutkan ke pembayaran."); }
                });
            }
        });
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
