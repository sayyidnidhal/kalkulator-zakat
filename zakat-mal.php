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

$harga_emas_db = 1200000; // Default
$nishab_emas_rp = 0;
$nishab_emas_rp_bulanan = 0;
$nishab_pertanian_db = 653; // Default

$sql_emas = "SELECT nilai FROM nisab WHERE jenis_zakat = 'emas' LIMIT 1";
$result_emas = $conn->query($sql_emas);
if ($result_emas && $result_emas->num_rows > 0) {
    $row_emas = $result_emas->fetch_assoc();
    $harga_emas_db = (float)$row_emas['nilai'];
}
$nishab_emas_rp = 85 * $harga_emas_db;
$nishab_emas_rp_bulanan = $nishab_emas_rp / 12;

$sql_pertanian_kg = "SELECT nilai FROM nisab WHERE jenis_zakat = 'pertanian_kg' LIMIT 1";
$result_pertanian_kg = $conn->query($sql_pertanian_kg);
if ($result_pertanian_kg && $result_pertanian_kg->num_rows > 0) {
    $row_pertanian_kg = $result_pertanian_kg->fetch_assoc();
    $nishab_pertanian_db = (float)$row_pertanian_kg['nilai'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Zakat Mal</title>
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
                'tab-inactive-text': '#DD6B20', 
                'tab-inactive-hover-bg': '#FFF7ED', 
              }
            }
          }
        }
        // Menyimpan nilai dari PHP ke JavaScript
        const HARGA_EMAS_PER_GRAM_DB = <?php echo json_encode($harga_emas_db); ?>;
        const NISHAB_EMAS_TAHUNAN_RP = <?php echo json_encode($nishab_emas_rp); ?>;
        const NISHAB_EMAS_BULANAN_RP = <?php echo json_encode($nishab_emas_rp_bulanan); ?>;
        const NISHAB_PERTANIAN_KG_DB = <?php echo json_encode($nishab_pertanian_db); ?>;
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
        <div class="max-w-3xl mx-auto bg-white p-0 rounded-xl shadow-xl overflow-hidden">
            <div class="pt-6 px-6"> 
                <div class="flex justify-end mb-2"> 
                    <a href="index.php" class="text-sm text-primary-orange hover:text-action-orange">← Kembali ke Halaman Utama</a>
                </div>
                <h1 class="text-3xl font-bold text-primary-orange text-center">Zakat Mal</h1> 
            </div>
             <div class="p-4 bg-light-orange-bg border-y border-primary-orange/20 text-sm text-center mt-4">
                Harga Emas saat ini (untuk perhitungan nishab): <strong><?php echo 'Rp ' . number_format($harga_emas_db, 0, ',', '.'); ?>/gram</strong>.
                Nishab Zakat Mal (85gr Emas): <strong><?php echo 'Rp ' . number_format($nishab_emas_rp, 0, ',', '.'); ?></strong>.
            </div>
            <nav class="mal-tabs-nav mt-2 mb-0 border-b border-gray-200">
                <button data-tab="hartaSimpanan" class="mal-tab-button active">Simpanan & Investasi</button>
                <button data-tab="perniagaan" class="mal-tab-button">Perniagaan & Perusahaan</button>
                <button data-tab="pertanian" class="mal-tab-button">Pertanian</button>
                <button data-tab="peternakan" class="mal-tab-button">Peternakan</button>
                <button data-tab="pertambangan" class="mal-tab-button">Pertambangan</button>
                <button data-tab="rikaz" class="mal-tab-button">Rikaz</button>
                <button data-tab="profesi" class="mal-tab-button">Pendapatan & Jasa</button>
            </nav>
            <div class="p-6 md:p-8">
                <section id="hartaSimpananContent" class="mal-tab-content"></section>
                <section id="perniagaanContent" class="mal-tab-content hidden"></section>
                <section id="pertanianContent" class="mal-tab-content hidden"></section>
                <section id="peternakanContent" class="mal-tab-content hidden"></section>
                <section id="pertambanganContent" class="mal-tab-content hidden"></section>
                <section id="rikazContent" class="mal-tab-content hidden"></section>
                <section id="profesiContent" class="mal-tab-content hidden"></section>
            </div>
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
        // --- Fungsi Utilitas Global ---
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        }
        function getNumericValue(id) {
            const el = document.getElementById(id);
            return el ? (parseFloat(el.value) || 0) : 0;
        }
        
        // --- Logika Modal Notifikasi ---
        const modal = document.getElementById('customModal');
        const modalTitleEl = document.getElementById('modalTitle');
        const modalMessageEl = document.getElementById('modalMessage');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        function showModal(title, message) {
            if(modalTitleEl) modalTitleEl.textContent = title;
            if(modalMessageEl) modalMessageEl.textContent = message;
            if(modal) modal.classList.add('active');
        }
        function closeModal() {
            if(modal) modal.classList.remove('active');
        }
        if(modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
        if(modal) modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });

        // --- Logika Utama Halaman Zakat Mal ---
        document.addEventListener('DOMContentLoaded', function() {
            const malTabs = document.querySelectorAll('.mal-tab-button');
            const malTabContents = document.querySelectorAll('.mal-tab-content');

            malTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    malTabs.forEach(item => item.classList.remove('active'));
                    tab.classList.add('active');
                    const targetTab = tab.getAttribute('data-tab');
                    malTabContents.forEach(content => {
                        content.classList.toggle('hidden', content.id !== targetTab + 'Content');
                    });
                    // Panggil fungsi inisialisasi untuk tab yang aktif
                    const initFunctionName = `init${targetTab.charAt(0).toUpperCase() + targetTab.slice(1)}Tab`;
                    if (typeof window[initFunctionName] === 'function') {
                        window[initFunctionName](); 
                    }
                    // Update nishab display khusus untuk peternakan jika tabnya aktif
                    if (targetTab === 'peternakan' && typeof updateNishabPeternakanDisplay === 'function') {
                         updateNishabPeternakanDisplay();
                    }
                });
            });

            // --- Fungsi untuk Membuat Elemen Form Dinamis ---
            function createFormInput(labelText, inputId, inputType, placeholderText = "", defaultValue = "", helpText = "", additionalAttributes = {}) {
                const div = document.createElement('div');
                div.className = 'mb-5';
                const label = document.createElement('label');
                label.htmlFor = inputId;
                label.className = 'block text-sm font-medium text-dark-text mb-1';
                label.textContent = labelText;
                div.appendChild(label);
                const input = document.createElement('input');
                input.type = inputType;
                input.id = inputId;
                input.name = inputId; 
                input.className = 'form-input w-full p-2.5 border rounded-md shadow-sm';
                if (placeholderText) input.placeholder = placeholderText;
                if (defaultValue) input.value = defaultValue;
                Object.keys(additionalAttributes).forEach(key => {
                    input.setAttribute(key, additionalAttributes[key]);
                });
                div.appendChild(input);
                if (helpText) {
                    const pHelp = document.createElement('p');
                    pHelp.className = 'text-xs text-slate-500 mt-1';
                    pHelp.textContent = helpText;
                    div.appendChild(pHelp);
                }
                return div;
            }
            
            function createSelectInput(labelText, selectId, options, helpText = "") {
                const div = document.createElement('div');
                div.className = 'mb-5';
                const label = document.createElement('label');
                label.htmlFor = selectId;
                label.className = 'block text-sm font-medium text-dark-text mb-1';
                label.textContent = labelText;
                div.appendChild(label);
                const select = document.createElement('select');
                select.id = selectId;
                select.name = selectId;
                select.className = 'form-input w-full p-2.5 border rounded-md shadow-sm bg-white';
                options.forEach(opt => {
                    const optionEl = document.createElement('option');
                    optionEl.value = opt.value;
                    optionEl.textContent = opt.text;
                    select.appendChild(optionEl);
                });
                div.appendChild(select);
                if (helpText) {
                    const pHelp = document.createElement('p');
                    pHelp.className = 'text-xs text-slate-500 mt-1';
                    pHelp.textContent = helpText;
                    div.appendChild(pHelp);
                }
                return div;
            }

            function createFormButtons(calculateId, resetId, payId, zakatTypeForPayment, hidePaymentButton = false) {
                const divButtons = document.createElement('div');
                divButtons.className = 'flex gap-3 mt-8';
                const calcButton = document.createElement('button');
                calcButton.type = 'button';
                calcButton.id = calculateId;
                calcButton.className = 'btn-primary w-full py-3 px-4 rounded-md font-semibold shadow-md';
                calcButton.textContent = 'Hitung Zakat';
                divButtons.appendChild(calcButton);
                const resetButton = document.createElement('button');
                resetButton.type = 'reset'; 
                resetButton.id = resetId;
                resetButton.className = 'btn-reset w-1/3 py-3 px-4 rounded-md font-semibold shadow-md';
                resetButton.textContent = 'Reset';
                divButtons.appendChild(resetButton);
                
                const resultContainer = document.createElement('div');
                resultContainer.className = 'mt-8';
                let nishabInfoHTML = `<div id="nishabInfo${zakatTypeForPayment}" class="info-card p-4 rounded-md mb-4 text-sm">Nishab (85gr Emas): <strong id="nishabValue${zakatTypeForPayment}">${formatCurrency(NISHAB_EMAS_TAHUNAN_RP)}</strong></div>`;
                if (zakatTypeForPayment === 'Pertanian') {
                    nishabInfoHTML = `<div id="nishabInfo${zakatTypeForPayment}" class="info-card p-4 rounded-md mb-4 text-sm">Nishab: <strong>±${NISHAB_PERTANIAN_KG_DB} Kg</strong> hasil panen kering.</div>`;
                } else if (zakatTypeForPayment === 'Peternakan') {
                    nishabInfoHTML = `<div id="nishabInfo${zakatTypeForPayment}" class="info-card p-4 rounded-md mb-4 text-sm">Nishab: <span id="nishabValue${zakatTypeForPayment}">-</span></div>`;
                } else if (zakatTypeForPayment === 'Rikaz') {
                    nishabInfoHTML = ''; 
                } else if (zakatTypeForPayment === 'Profesi') {
                    nishabInfoHTML = `<div id="nishabInfo${zakatTypeForPayment}" class="info-card p-4 rounded-md mb-4 text-sm">Nishab (Bulanan/Tahunan): <strong id="nishabValue${zakatTypeForPayment}">${formatCurrency(NISHAB_EMAS_BULANAN_RP)} / ${formatCurrency(NISHAB_EMAS_TAHUNAN_RP)}</strong></div>`;
                }

                let zakatAmountClass = "text-3xl";
                let zakatAmountInitialText = "Rp 0";
                if (zakatTypeForPayment === 'Peternakan') {
                    zakatAmountClass = "text-2xl";
                    zakatAmountInitialText = "-";
                }

                let paymentButtonHTML = '';
                if (!hidePaymentButton) {
                    paymentButtonHTML = `<button type="button" id="${payId}" class="btn-secondary w-full py-3 px-4 rounded-md font-semibold shadow-md mt-4">Bayar Zakat</button>`;
                } else {
                    paymentButtonHTML = `<div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 text-yellow-700 text-sm rounded-md text-center">Untuk pembayaran zakat ${zakatTypeForPayment.toLowerCase()}, silakan hubungi kantor Lazismu terdekat.</div>`;
                }

                resultContainer.innerHTML = `
                    ${nishabInfoHTML}
                    <div id="result${zakatTypeForPayment}" class="result-card p-4 rounded-md text-center">
                        <p class="text-lg font-semibold">Zakat Anda:</p>
                        <p id="zakatAmount${zakatTypeForPayment}" class="${zakatAmountClass} font-bold text-primary-orange my-2">${zakatAmountInitialText}</p>
                        <p id="message${zakatTypeForPayment}" class="text-sm text-slate-600"></p>
                    </div>
                    ${paymentButtonHTML}
                `;
                 if (zakatTypeForPayment === 'Rikaz') {
                    resultContainer.querySelector(`#result${zakatTypeForPayment} p:first-child`).textContent = "Zakat Rikaz Anda (20%):";
                    const messageEl = resultContainer.querySelector(`#message${zakatTypeForPayment}`);
                    if(messageEl) messageEl.classList.add('hidden');
                }
                return { divButtons, resultContainer };
            }

            // --- Inisialisasi dan Logika untuk setiap Tab ---
            
            window.initHartaSimpananTab = function() { 
                const c = document.getElementById('hartaSimpananContent'); if (!c || c.innerHTML.trim() !== "") return; 
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Harta Simpanan & Investasi</h2><p class="text-sm text-slate-600 mb-4">Mencakup Emas, Perak, Uang Tunai, Tabungan, Deposito, Logam Mulia, Batu Mulia, dan Surat Berharga (Saham, Obligasi). Nishab 85 gram emas, haul 1 tahun, kadar 2.5%.</p><form id="formZakatSimpanan"></form>`;
                const f = c.querySelector('#formZakatSimpanan');
                f.appendChild(createFormInput('Total Nilai Harta Simpanan & Investasi (Rp)', 'simpananValue', 'number', 'Masukkan total nilai'));
                f.appendChild(createFormInput('Hutang Jatuh Tempo Terkait Harta Ini (Rp)', 'simpananDebts', 'number', 'Jika ada, yang mengurangi nilai zakat'));
                const { divButtons, resultContainer } = createFormButtons('calculateSimpananBtn', 'resetSimpananBtn', 'payZakatSimpananBtn', 'Simpanan');
                f.appendChild(divButtons); f.appendChild(resultContainer); attachSimpananListeners();
            };
            function attachSimpananListeners() { 
                const el = (id) => document.getElementById(id);
                const zaEl = el('zakatAmountSimpanan'), msgEl = el('messageSimpanan');
                if(el('calculateSimpananBtn')) el('calculateSimpananBtn').addEventListener('click', () => { const net = getNumericValue('simpananValue') - getNumericValue('simpananDebts'); if (net < 0) { if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = "Total aset bersih Anda negatif."; return; } let z = 0; if (net >= NISHAB_EMAS_TAHUNAN_RP) { z = 0.025 * net; if(msgEl) msgEl.textContent = "Anda wajib membayar zakat."; } else { if(msgEl) msgEl.textContent = "Harta Anda belum mencapai nishab."; } if(zaEl) zaEl.textContent = formatCurrency(z); });
                if(el('resetSimpananBtn')) el('resetSimpananBtn').addEventListener('click', () => { el('simpananValue').value = ""; el('simpananDebts').value = ""; if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = ""; });
                if(el('payZakatSimpananBtn')) el('payZakatSimpananBtn').addEventListener('click', function() { const j = parseFloat(zaEl.textContent.replace(/[^0-9]/g, '')); if (j > 0) { window.location.href = `bayar-zakat.php?jenis=harta_simpanan&jumlah=${j}`; } else { showModal("Perhitungan Belum Selesai", "Mohon hitung zakat Anda terlebih dahulu."); } });
            }

            window.initPerniagaanTab = function() { 
                const c = document.getElementById('perniagaanContent'); if (!c || c.innerHTML.trim() !== "") return;
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Perniagaan & Perusahaan</h2><p class="text-sm text-slate-600 mb-4">Aset yang diperdagangkan atau aset produktif perusahaan. Nishab 85 gram emas, haul 1 tahun, kadar 2.5%.</p><form id="formZakatPerniagaan"></form>`;
                const f = c.querySelector('#formZakatPerniagaan');
                f.appendChild(createFormInput('Modal Diputar + Keuntungan (Rp)', 'perniagaanAssets', 'number', 'Nilai barang dagangan, aset perusahaan, kas'));
                f.appendChild(createFormInput('Piutang Dagang Lancar (Rp)', 'perniagaanReceivables', 'number', 'Piutang dari penjualan yang diharapkan cair'));
                f.appendChild(createFormInput('Hutang Usaha Jatuh Tempo (Rp)', 'perniagaanDebts', 'number', 'Hutang terkait usaha yang harus dibayar'));
                const { divButtons, resultContainer } = createFormButtons('calculatePerniagaanBtn', 'resetPerniagaanBtn', 'payZakatPerniagaanBtn', 'Perniagaan');
                f.appendChild(divButtons); f.appendChild(resultContainer); attachPerniagaanListeners();
            };
            function attachPerniagaanListeners() { 
                const el = (id) => document.getElementById(id);
                const zaEl = el('zakatAmountPerniagaan'), msgEl = el('messagePerniagaan');
                if(el('calculatePerniagaanBtn')) el('calculatePerniagaanBtn').addEventListener('click', () => { const net = (getNumericValue('perniagaanAssets') + getNumericValue('perniagaanReceivables')) - getNumericValue('perniagaanDebts'); if (net < 0) { if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = "Total aset bersih Anda negatif."; return; } let z = 0; if (net >= NISHAB_EMAS_TAHUNAN_RP) { z = 0.025 * net; if(msgEl) msgEl.textContent = "Anda wajib membayar zakat."; } else { if(msgEl) msgEl.textContent = "Harta Anda belum mencapai nishab."; } if(zaEl) zaEl.textContent = formatCurrency(z); });
                if(el('resetPerniagaanBtn')) el('resetPerniagaanBtn').addEventListener('click', () => { el('perniagaanAssets').value = ""; el('perniagaanReceivables').value = ""; el('perniagaanDebts').value = ""; if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = ""; });
                if(el('payZakatPerniagaanBtn')) el('payZakatPerniagaanBtn').addEventListener('click', function() { const j = parseFloat(zaEl.textContent.replace(/[^0-9]/g, '')); if (j > 0) { window.location.href = `bayar-zakat.php?jenis=perniagaan&jumlah=${j}`; } else { showModal("Perhitungan Belum Selesai", "Mohon hitung zakat Anda terlebih dahulu."); } });
            }

            window.initPertanianTab = function() { 
                const c = document.getElementById('pertanianContent'); if (!c || c.innerHTML.trim() !== "") return;
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Pertanian, Perkebunan & Kehutanan</h2><p class="text-sm text-slate-600 mb-4">Nishab ${NISHAB_PERTANIAN_KG_DB} kg hasil panen kering setelah dibersihkan. Kadar 5% (dengan biaya irigasi) atau 10% (tanpa biaya irigasi signifikan/tadah hujan).</p><form id="formZakatPertanian"></form>`;
                const f = c.querySelector('#formZakatPertanian');
                f.appendChild(createFormInput('Jumlah Hasil Panen Kering (Kg)', 'pertanianHarvestKg', 'number', 'Contoh: 700'));
                f.appendChild(createFormInput('Harga Jual Hasil Panen per Kg (Rp)', 'pertanianPricePerKg', 'number', 'Contoh: 10000'));
                f.appendChild(createSelectInput('Jenis Pengairan', 'pertanianIrrigationType', [{value: 'dengan_biaya', text: 'Menggunakan Biaya Irigasi (Zakat 5%)'},{value: 'tanpa_biaya', text: 'Tadah Hujan/Tanpa Biaya Irigasi Signifikan (Zakat 10%)'}]));
                const { divButtons, resultContainer } = createFormButtons('calculatePertanianBtn', 'resetPertanianBtn', 'payZakatPertanianBtn', 'Pertanian', true); 
                f.appendChild(divButtons); f.appendChild(resultContainer); attachPertanianListeners();
            };
            function attachPertanianListeners() { 
                const el = (id) => document.getElementById(id);
                const zaEl = el('zakatAmountPertanian'), msgEl = el('messagePertanian');
                if(el('calculatePertanianBtn')) el('calculatePertanianBtn').addEventListener('click', () => { const hKg = getNumericValue('pertanianHarvestKg'), pKg = getNumericValue('pertanianPricePerKg'), iType = el('pertanianIrrigationType').value; const nKg = NISHAB_PERTANIAN_KG_DB; let zRate = (iType === 'dengan_biaya') ? 0.05 : 0.10; if (hKg < nKg) { if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = `Hasil panen Anda (${hKg} Kg) belum mencapai nishab (${nKg} Kg).`; return; } const z = (hKg * pKg) * zRate; if(zaEl) zaEl.textContent = formatCurrency(z); if(msgEl) msgEl.textContent = `Anda wajib membayar zakat sebesar ${zRate*100}% dari total nilai hasil panen.`; });
                if(el('resetPertanianBtn')) el('resetPertanianBtn').addEventListener('click', () => { el('pertanianHarvestKg').value = ""; el('pertanianPricePerKg').value = ""; el('pertanianIrrigationType').value = "dengan_biaya"; if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = ""; });
            }
            
            window.initPeternakanTab = function() { 
                const c = document.getElementById('peternakanContent'); if (!c || c.innerHTML.trim() !== "") return;
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Peternakan</h2><p class="text-sm text-slate-600 mb-4">Hewan digembalakan, mencapai haul, dan tidak dipekerjakan. Nishab dan kadar berbeda per jenis hewan.</p><form id="formZakatPeternakan"></form>`;
                const f = c.querySelector('#formZakatPeternakan');
                f.appendChild(createSelectInput('Jenis Hewan Ternak', 'peternakanAnimalType', [{value: 'kambing', text: 'Kambing/Domba'},{value: 'sapi', text: 'Sapi/Kerbau'},{value: 'unta', text: 'Unta'}]));
                f.appendChild(createFormInput('Jumlah Hewan yang Dimiliki (Ekor)', 'peternakanAnimalCount', 'number', 'Masukkan jumlah ekor'));
                const { divButtons, resultContainer } = createFormButtons('calculatePeternakanBtn', 'resetPeternakanBtn', 'payZakatPeternakanBtn', 'Peternakan', true); 
                f.appendChild(divButtons); f.appendChild(resultContainer); attachPeternakanListeners();
            };
            function attachPeternakanListeners() { 
                const el = (id) => document.getElementById(id);
                const typeEl = el('peternakanAnimalType'), nvEl = el('nishabValuePeternakan'), zaEl = el('zakatAmountPeternakan'), msgEl = el('messagePeternakan');
                window.updateNishabPeternakanDisplay = function() { if(!typeEl || !nvEl) return; const aType = typeEl.value; let nText = "-"; if (aType === 'kambing') nText = "40 ekor"; else if (aType === 'sapi') nText = "30 ekor"; else if (aType === 'unta') nText = "5 ekor"; nvEl.textContent = nText; }
                if(typeEl) typeEl.addEventListener('change', updateNishabPeternakanDisplay);
                if(el('calculatePeternakanBtn')) el('calculatePeternakanBtn').addEventListener('click', () => { const aType = typeEl.value, count = getNumericValue('peternakanAnimalCount'); let zDesc = "-", msg = ""; if (aType === 'kambing') { if (count < 40) { zDesc = "0 ekor"; msg = "Belum mencapai nishab (40 ekor)."; } else if (count <= 120) zDesc = "1 ekor kambing"; else if (count <= 200) zDesc = "2 ekor kambing"; else if (count <= 399) zDesc = "3 ekor kambing"; else zDesc = Math.floor(count / 100) + " ekor kambing"; if (count >= 40 && msg === "") msg = "Anda wajib membayar zakat ternak."; } else if (aType === 'sapi') { if (count < 30) { zDesc = "0 ekor"; msg = "Belum mencapai nishab (30 ekor)."; } else if (count <= 39) zDesc = "1 ekor Tabi' (sapi 1 thn)"; else if (count <= 59) zDesc = "1 ekor Musinnah (sapi 2 thn)"; else if (count <= 69) zDesc = "2 ekor Tabi'"; else if (count <= 79) zDesc = "1 ekor Musinnah & 1 ekor Tabi'"; else if (count <= 89) zDesc = "2 ekor Musinnah"; else if (count <= 99) zDesc = "3 ekor Tabi'"; else if (count <= 109) zDesc = "1 Musinnah & 2 Tabi'"; else if (count <= 119) zDesc = "2 ekor Musinnah & 1 ekor Tabi'"; else if (count >= 120) { let num = count; let musinnah = 0; let tabi = 0; if (num % 40 === 0 && num / 40 >=3) { musinnah = num / 40; } else if (num % 30 === 0 && num / 30 >= 4 && num === 120) { tabi = num / 30; } else { musinnah = Math.floor(num / 40); tabi = Math.floor((num % 40) / 30); if (musinnah === 0 && tabi === 0 && num >= 120) { if (count === 120) zDesc = "3 ekor Musinnah atau 4 ekor Tabi'"; else zDesc = "Konsultasikan dengan ahli"; } else { zDesc = ""; if (musinnah > 0) zDesc += `${musinnah} Musinnah `; if (tabi > 0) zDesc += `${musinnah > 0 ? "& " : ""}${tabi} Tabi'`; } } if (zDesc === "" && count >= 120 && !(count === 120)) zDesc = "Konsultasikan dengan ahli untuk jumlah ini."; else if (count === 120 && zDesc === "") zDesc = "3 ekor Musinnah atau 4 ekor Tabi'";} if (count >= 30 && msg === "") msg = "Anda wajib membayar zakat ternak."; } else if (aType === 'unta') { if (count < 5) { zDesc = "0 ekor"; msg = "Belum mencapai nishab (5 ekor)."; } else if (count <= 9) zDesc = "1 ekor kambing"; else if (count <= 14) zDesc = "2 ekor kambing"; else if (count <= 19) zDesc = "3 ekor kambing"; else if (count <= 24) zDesc = "4 ekor kambing"; else if (count <= 35) zDesc = "1 ekor Bintu Makhadh (unta betina 1 thn)"; else if (count <= 45) zDesc = "1 ekor Bintu Labun (unta betina 2 thn)"; else if (count <= 60) zDesc = "1 ekor Hiqqah (unta betina 3 thn)"; else if (count <= 75) zDesc = "1 ekor Jadza'ah (unta betina 4 thn)"; else if (count <= 90) zDesc = "2 ekor Bintu Labun"; else if (count <= 120) zDesc = "2 ekor Hiqqah"; else { zDesc = "Konsultasikan dengan ahli untuk jumlah di atas 120 ekor."; } if (count >= 5 && msg === "") msg = "Anda wajib membayar zakat ternak."; } if(zaEl) zaEl.textContent = zDesc; if(msgEl) msgEl.textContent = msg; });
                if(el('resetPeternakanBtn')) el('resetPeternakanBtn').addEventListener('click', () => { if(typeEl) typeEl.value = "kambing"; el('peternakanAnimalCount').value = ""; if(zaEl) zaEl.textContent = "-"; if(msgEl) msgEl.textContent = ""; updateNishabPeternakanDisplay(); });
            }

            window.initPertambanganTab = function() { 
                const c = document.getElementById('pertambanganContent'); if (!c || c.innerHTML.trim() !== "") return;
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Pertambangan</h2><p class="text-sm text-slate-600 mb-4">Nishab setara 85 gram emas, kadar 2.5% dari hasil bersih setelah biaya eksplorasi dan operasional, haul 1 tahun.</p><form id="formZakatPertambangan"></form>`;
                const f = c.querySelector('#formZakatPertambangan');
                f.appendChild(createFormInput('Total Hasil Tambang (Setelah 1 Tahun) (Rp)', 'pertambanganRevenue', 'number', 'Pendapatan kotor dari hasil tambang'));
                f.appendChild(createFormInput('Biaya Eksplorasi & Operasional (Rp)', 'pertambanganCosts', 'number', 'Total biaya terkait penambangan'));
                const { divButtons, resultContainer } = createFormButtons('calculatePertambanganBtn', 'resetPertambanganBtn', 'payZakatPertambanganBtn', 'Pertambangan');
                f.appendChild(divButtons); f.appendChild(resultContainer); attachPertambanganListeners();
            };
            function attachPertambanganListeners() { 
                const el = (id) => document.getElementById(id);
                const zaEl = el('zakatAmountPertambangan'), msgEl = el('messagePertambangan');
                if(el('calculatePertambanganBtn')) el('calculatePertambanganBtn').addEventListener('click', () => { const net = getNumericValue('pertambanganRevenue') - getNumericValue('pertambanganCosts'); if (net < 0) { if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = "Hasil bersih pertambangan Anda negatif."; return; } let z = 0; if (net >= NISHAB_EMAS_TAHUNAN_RP) { z = 0.025 * net; if(msgEl) msgEl.textContent = "Anda wajib membayar zakat."; } else { if(msgEl) msgEl.textContent = "Hasil bersih tambang Anda belum mencapai nishab."; } if(zaEl) zaEl.textContent = formatCurrency(z); });
                if(el('resetPertambanganBtn')) el('resetPertambanganBtn').addEventListener('click', () => { el('pertambanganRevenue').value = ""; el('pertambanganCosts').value = ""; if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = ""; });
                if(el('payZakatPertambanganBtn')) el('payZakatPertambanganBtn').addEventListener('click', function() { const j = parseFloat(zaEl.textContent.replace(/[^0-9]/g, '')); if (j > 0) { window.location.href = `bayar-zakat.php?jenis=pertambangan&jumlah=${j}`; } else { showModal("Perhitungan Belum Selesai", "Mohon hitung zakat Anda terlebih dahulu."); } });
            }

            window.initRikazTab = function() { 
                const c = document.getElementById('rikazContent'); if (!c || c.innerHTML.trim() !== "") return;
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Rikaz (Barang Temuan)</h2><p class="text-sm text-slate-600 mb-4">Tidak ada nishab & haul. Kadar zakat 20% dari nilai barang temuan (harta karun).</p><form id="formZakatRikaz"></form>`;
                const f = c.querySelector('#formZakatRikaz');
                f.appendChild(createFormInput('Nilai Barang Temuan (Harta Karun) (Rp)', 'rikazValue', 'number', 'Masukkan nilai total temuan'));
                const { divButtons, resultContainer } = createFormButtons('calculateRikazBtn', 'resetRikazBtn', 'payZakatRikazBtn', 'Rikaz');
                f.appendChild(divButtons); f.appendChild(resultContainer); attachRikazListeners();
            };
            function attachRikazListeners() { 
                const el = (id) => document.getElementById(id);
                const zaEl = el('zakatAmountRikaz');
                if(el('calculateRikazBtn')) el('calculateRikazBtn').addEventListener('click', () => { const val = getNumericValue('rikazValue'); if (val <= 0) { if(zaEl) zaEl.textContent = formatCurrency(0); showModal("Input Tidak Valid", "Nilai barang temuan harus lebih dari 0."); return; } const z = 0.20 * val; if(zaEl) zaEl.textContent = formatCurrency(z); });
                if(el('resetRikazBtn')) el('resetRikazBtn').addEventListener('click', () => { el('rikazValue').value = ""; if(zaEl) zaEl.textContent = formatCurrency(0); });
                if(el('payZakatRikazBtn')) el('payZakatRikazBtn').addEventListener('click', function() { const j = parseFloat(zaEl.textContent.replace(/[^0-9]/g, '')); if (j > 0) { window.location.href = `bayar-zakat.php?jenis=rikaz&jumlah=${j}`; } else { showModal("Perhitungan Belum Selesai", "Mohon hitung zakat Anda terlebih dahulu."); } });
            }

            window.initProfesiTab = function() { 
                const c = document.getElementById('profesiContent'); if (!c || c.innerHTML.trim() !== "") return;
                c.innerHTML = `<h2 class="text-2xl font-semibold text-dark-text mb-6">Pendapatan, Profesi & Jasa</h2><p class="text-sm text-slate-600 mb-4">Nishab 85 gram emas, kadar 2.5%. Dikeluarkan saat menerima jika mencapai nishab per penerimaan, atau dikumpulkan selama setahun (haul) jika tidak.</p><form id="formZakatProfesi"></form>`;
                const f = c.querySelector('#formZakatProfesi');
                f.appendChild(createFormInput('Total Pendapatan Diterima (Rp)', 'profesiIncome', 'number', 'Gaji, upah, honorarium'));
                f.appendChild(createSelectInput('Periode Perhitungan', 'profesiPeriod', [{value: 'bulanan', text: 'Bulanan'},{value: 'tahunan', text: 'Tahunan (Haul)'}]));
                f.appendChild(createFormInput('Hutang Pokok/Kebutuhan Mendesak (Rp)', 'profesiDebts', 'number', 'Pengurang jika dihitung tahunan', '', '(opsional, jika perhitungan tahunan)'));
                const { divButtons, resultContainer } = createFormButtons('calculateProfesiBtn', 'resetProfesiBtn', 'payZakatProfesiBtn', 'Profesi');
                f.appendChild(divButtons); f.appendChild(resultContainer); attachProfesiListeners();
            };
            function attachProfesiListeners() { 
                const el = (id) => document.getElementById(id);
                const zaEl = el('zakatAmountProfesi'), msgEl = el('messageProfesi');
                if(el('calculateProfesiBtn')) el('calculateProfesiBtn').addEventListener('click', () => { const income = getNumericValue('profesiIncome'), period = el('profesiPeriod').value, debts = getNumericValue('profesiDebts'); let nishabAcuan = (period === 'bulanan') ? NISHAB_EMAS_BULANAN_RP : NISHAB_EMAS_TAHUNAN_RP; let netIncome = income, z = 0, msg = ""; if (period === 'tahunan') { netIncome = income - debts; if (netIncome < 0) netIncome = 0; } if (netIncome >= nishabAcuan) { z = 0.025 * netIncome; msg = `Pendapatan ${period} Anda (setelah hutang jika tahunan) wajib dizakati.`; } else { msg = `Pendapatan ${period} Anda (setelah hutang jika tahunan) belum mencapai nishab.`; } if (netIncome < 0 && period === 'tahunan') { msg = "Pendapatan bersih tahunan Anda negatif."; } if(zaEl) zaEl.textContent = formatCurrency(z); if(msgEl) msgEl.textContent = msg; });
                if(el('resetProfesiBtn')) el('resetProfesiBtn').addEventListener('click', () => { el('profesiIncome').value = ""; el('profesiDebts').value = ""; el('profesiPeriod').value = "bulanan"; if(zaEl) zaEl.textContent = formatCurrency(0); if(msgEl) msgEl.textContent = ""; });
                if(el('payZakatProfesiBtn')) el('payZakatProfesiBtn').addEventListener('click', function() { const j = parseFloat(zaEl.textContent.replace(/[^0-9]/g, '')); if (j > 0) { window.location.href = `bayar-zakat.php?jenis=profesi&jumlah=${j}`; } else { showModal("Perhitungan Belum Selesai", "Mohon hitung zakat Anda terlebih dahulu."); } });
            }

            // Inisialisasi tab pertama saat halaman dimuat
            const activeTabOnInit = document.querySelector('.mal-tab-button.active');
            if (activeTabOnInit) {
                const initialTabName = activeTabOnInit.getAttribute('data-tab');
                const initFunctionName = `init${initialTabName.charAt(0).toUpperCase() + initialTabName.slice(1)}Tab`;
                if (typeof window[initFunctionName] === 'function') { window[initFunctionName](); }
                if (initialTabName === 'peternakan' && typeof updateNishabPeternakanDisplay === 'function') { updateNishabPeternakanDisplay(); }
            }
            const currentYearEl = document.getElementById('currentYear');
            if (currentYearEl) {
                currentYearEl.textContent = new Date().getFullYear();
            }
        });
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>
