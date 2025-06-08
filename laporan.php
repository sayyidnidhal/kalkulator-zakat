<?php
require_once 'system/session-handler.php';
require_admin_login();

require_once 'system/db-connect.php';

// --- BAGIAN LOGIKA HEADER DINAMIS ---
$current_page = basename($_SERVER['PHP_SELF']);
$user_name_display = get_current_user_name() ?? 'Admin';
$dashboard_link = 'dashboard-admin.php';
$profile_link = 'profil-pengguna.php';
$admin_nama_lengkap_ttd = get_current_user_name() ?? 'Nama Admin'; 

// --- BAGIAN LOGIKA FILTER ---
$periode_filter = $_GET['periode'] ?? 'bulanan'; 
$jenis_zakat_filter = $_GET['jenis_zakat'] ?? 'semua'; 

$now = new DateTime();
$bulan_terpilih = $_GET['bulan'] ?? $now->format('n');
$tahun_bulanan_terpilih = $_GET['tahun_bulanan'] ?? $now->format('Y');
$tahun_tahunan_terpilih = $_GET['tahun_tahunan'] ?? $now->format('Y');
$start_date_input = $_GET['start_date'] ?? '';
$end_date_input = $_GET['end_date'] ?? '';

$start_date = '';
$end_date = '';

switch ($periode_filter) {
    case 'hari_ini':
        $start_date = $now->format('Y-m-d');
        $end_date = $now->format('Y-m-d');
        break;
    case 'tahunan':
        $start_date = $tahun_tahunan_terpilih . '-01-01';
        $end_date = $tahun_tahunan_terpilih . '-12-31';
        break;
    case 'custom':
        $start_date = $start_date_input;
        $end_date = $end_date_input;
        break;
    case 'bulanan':
    default:
        $start_date = date('Y-m-01', strtotime("{$tahun_bulanan_terpilih}-{$bulan_terpilih}-01"));
        $end_date = date('Y-m-t', strtotime("{$tahun_bulanan_terpilih}-{$bulan_terpilih}-01"));
        break;
}

// --- FUNGSI HELPER & JUDUL ---
$bulan_indonesia = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

function generatePeriodeTitle($periode_filter, $start_date, $end_date, $bulan_indonesia, $bulan_terpilih, $tahun_bulanan_terpilih, $tahun_tahunan_terpilih) {
    if (empty($start_date) || empty($end_date)) return "PILIH PERIODE LAPORAN";
    
    $start_dt_obj = date_create($start_date);
    $end_dt_obj = date_create($end_date);

    switch ($periode_filter) {
        case 'hari_ini':
            $bulan = $bulan_indonesia[date_format($start_dt_obj, 'n')];
            return "PERIODE " . date_format($start_dt_obj, 'd') . " " . $bulan . " " . date_format($start_dt_obj, 'Y');
        case 'bulanan':
            $bulan = $bulan_indonesia[(int)$bulan_terpilih];
            return "BULAN " . strtoupper($bulan) . " TAHUN " . $tahun_bulanan_terpilih;
        case 'tahunan':
            return "TAHUN " . $tahun_tahunan_terpilih;
        case 'custom':
            if ($start_date === $end_date) {
                 $bulan = $bulan_indonesia[date_format($start_dt_obj, 'n')];
                 return "PERIODE " . date_format($start_dt_obj, 'd') . " " . $bulan . " " . date_format($start_dt_obj, 'Y');
            }
            $bulan_mulai = $bulan_indonesia[date_format($start_dt_obj, 'n')];
            $bulan_selesai = $bulan_indonesia[date_format($end_dt_obj, 'n')];
            return "PERIODE " . date_format($start_dt_obj, 'd') . " " . $bulan_mulai . " " . date_format($start_dt_obj, 'Y') . " - " . date_format($end_dt_obj, 'd') . " " . $bulan_selesai . " " . date_format($end_dt_obj, 'Y');
        default:
            return "PERIODE LAPORAN";
    }
}
$periode_title = generatePeriodeTitle($periode_filter, $start_date, $end_date, $bulan_indonesia, $bulan_terpilih, $tahun_bulanan_terpilih, $tahun_tahunan_terpilih);

function getTanggalCetak($bulan_indonesia) {
    $tanggal = date('d');
    $bulan = $bulan_indonesia[date('n')];
    $tahun = date('Y');
    return "$tanggal $bulan $tahun";
}

// --- PENGAMBILAN DATA DARI DATABASE ---
$laporan_data_fitrah = [];
$laporan_data_mal = [];
$laporan_data_penyaluran = [];
$total_fitrah = 0;
$total_mal = 0;
$total_penyaluran = 0;
$total_penerimaan = 0;

if (!empty($start_date) && !empty($end_date)) {
    // Bangun query dasar untuk penerimaan zakat yang sudah terverifikasi
    $base_sql_penerimaan = "SELECT p.tanggal_bayar, p.jenis_zakat_display, p.nominal_bayar, COALESCE(u.username, p.nama_pembayar) AS nama_pengguna_pembayar 
                            FROM pembayaran_zakat p
                            LEFT JOIN users u ON p.user_id = u.user_id
                            WHERE p.status_verifikasi = 'YA' AND p.tanggal_bayar BETWEEN ? AND ?";

    // Ambil data Zakat Fitrah jika filter 'semua' atau 'fitrah'
    if ($jenis_zakat_filter === 'semua' || $jenis_zakat_filter === 'fitrah') {
        $sql_fitrah = $base_sql_penerimaan . " AND p.jenis_zakat_raw = 'zakat_fitrah' ORDER BY p.tanggal_bayar ASC";
        $stmt_fitrah = $conn->prepare($sql_fitrah);
        if ($stmt_fitrah) {
            $stmt_fitrah->bind_param("ss", $start_date, $end_date);
            $stmt_fitrah->execute();
            $result_fitrah = $stmt_fitrah->get_result();
            while ($row = $result_fitrah->fetch_assoc()) {
                if (is_numeric($row['nominal_bayar'])) {
                    $laporan_data_fitrah[] = $row;
                    $total_fitrah += (float)$row['nominal_bayar'];
                }
            }
            $stmt_fitrah->close();
        }
    }

    // Ambil data Zakat Mal jika filter 'semua' atau 'mal'
    if ($jenis_zakat_filter === 'semua' || $jenis_zakat_filter === 'mal') {
        $sql_mal = $base_sql_penerimaan . " AND p.jenis_zakat_raw != 'zakat_fitrah' ORDER BY p.tanggal_bayar ASC";
        $stmt_mal = $conn->prepare($sql_mal);
        if ($stmt_mal) {
            $stmt_mal->bind_param("ss", $start_date, $end_date);
            $stmt_mal->execute();
            $result_mal = $stmt_mal->get_result();
            while ($row = $result_mal->fetch_assoc()) {
                if (is_numeric($row['nominal_bayar'])) {
                    $laporan_data_mal[] = $row;
                    $total_mal += (float)$row['nominal_bayar'];
                }
            }
            $stmt_mal->close();
        }
    }
    
    // Hitung total penerimaan berdasarkan filter
    $total_penerimaan = $total_fitrah + $total_mal;

    // Ambil data Penyaluran Zakat (tidak terpengaruh filter jenis zakat)
    $stmt_penyaluran = $conn->prepare("SELECT tanggal_penyaluran, deskripsi_penyaluran, nominal_penyaluran FROM penyaluran_zakat WHERE tanggal_penyaluran BETWEEN ? AND ? ORDER BY tanggal_penyaluran ASC");
    if ($stmt_penyaluran) {
        $stmt_penyaluran->bind_param("ss", $start_date, $end_date);
        $stmt_penyaluran->execute();
        $result_penyaluran = $stmt_penyaluran->get_result();
        while ($row = $result_penyaluran->fetch_assoc()) {
            $laporan_data_penyaluran[] = $row;
            $total_penyaluran += (float)($row['nominal_penyaluran'] ?? 0);
        }
        $stmt_penyaluran->close();
    }
}

$saldo_akhir = $total_penerimaan - $total_penyaluran;

function formatRupiah($angka) {
    if (!is_numeric($angka)) { return htmlspecialchars($angka); }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Zakat</title>
    <link rel="icon" href="assets/logo_lazismu.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; }
            .no-print { display: none !important; }
            table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 20px; page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            th, td { border: 1px solid #666 !important; padding: 6px !important; font-size: 9pt !important; text-align: left; }
            th { background-color: #f2f2f2 !important; font-weight: bold; }
            .text-right { text-align: right !important; }
            h1, h2, h3, h4 { color: black !important; margin-bottom: 0.5rem; text-align: center; }
            .print-header { display: block !important; }
            .signature-area { display: block !important; page-break-inside: avoid; }
            footer { display: none; }
        }
        .print-header, .signature-area { display: none; } 
    </style>
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
        <div class="max-w-4xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-xl">
            <div class="text-center mb-8 no-print">
                <h1 class="text-3xl font-bold text-primary-orange">Laporan Zakat</h1>
            </div>

            <form method="GET" action="laporan.php" class="mb-8 p-4 bg-gray-50 rounded-lg border space-y-4 no-print">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="periode" class="block text-sm font-medium text-dark-text mb-1">Pilih Periode</label>
                        <select name="periode" id="periodeFilter" class="form-input w-full p-2.5 border rounded-md shadow-sm">
                            <option value="hari_ini" <?php echo $periode_filter === 'hari_ini' ? 'selected' : ''; ?>>Hari Ini</option>
                            <option value="bulanan" <?php echo $periode_filter === 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
                            <option value="tahunan" <?php echo $periode_filter === 'tahunan' ? 'selected' : ''; ?>>Tahunan</option>
                            <option value="custom" <?php echo $periode_filter === 'custom' ? 'selected' : ''; ?>>Pilih Tanggal</option>
                        </select>
                    </div>
                    
                    <div id="bulananInputs" class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bulan" class="block text-sm font-medium text-dark-text mb-1">Bulan</label>
                            <select name="bulan" id="bulan" class="form-input w-full p-2.5 border rounded-md shadow-sm">
                                <?php foreach ($bulan_indonesia as $nomor => $nama): ?>
                                    <option value="<?php echo $nomor; ?>" <?php echo $nomor == $bulan_terpilih ? 'selected' : ''; ?>><?php echo $nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div>
                            <label for="tahun_bulanan" class="block text-sm font-medium text-dark-text mb-1">Tahun</label>
                            <input type="number" id="tahun_bulanan" name="tahun_bulanan" value="<?php echo htmlspecialchars($tahun_bulanan_terpilih); ?>" class="form-input w-full p-2.5 border rounded-md shadow-sm" placeholder="YYYY">
                        </div>
                    </div>

                    <div id="tahunanInputs" class="col-span-1 md:col-span-2">
                         <label for="tahun_tahunan" class="block text-sm font-medium text-dark-text mb-1">Tahun</label>
                         <input type="number" id="tahun_tahunan" name="tahun_tahunan" value="<?php echo htmlspecialchars($tahun_tahunan_terpilih); ?>" class="form-input w-full p-2.5 border rounded-md shadow-sm" placeholder="YYYY">
                    </div>

                    <div id="customDateRange" class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-dark-text mb-1">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date_input); ?>" class="form-input w-full p-2.5 border rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-dark-text mb-1">Tanggal Selesai</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date_input); ?>" class="form-input w-full p-2.5 border rounded-md shadow-sm">
                        </div>
                    </div>
                    
                    <div>
                         <label for="jenis_zakat" class="block text-sm font-medium text-dark-text mb-1">Jenis Zakat</label>
                        <select name="jenis_zakat" id="jenis_zakat" class="form-input w-full p-2.5 border rounded-md shadow-sm">
                            <option value="semua" <?php echo $jenis_zakat_filter === 'semua' ? 'selected' : ''; ?>>Semua Zakat</option>
                            <option value="fitrah" <?php echo $jenis_zakat_filter === 'fitrah' ? 'selected' : ''; ?>>Hanya Zakat Fitrah</option>
                            <option value="mal" <?php echo $jenis_zakat_filter === 'mal' ? 'selected' : ''; ?>>Hanya Zakat Mal</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end items-center space-x-3 pt-4">
                    <button type="submit" class="btn-primary py-2.5 px-6 rounded-md font-semibold shadow-md w-full sm:w-auto">
                        Tampilkan
                    </button>
                     <button type="button" onclick="window.print()" class="btn-secondary py-2.5 px-6 rounded-md font-semibold shadow-md w-full sm:w-auto">
                        Cetak
                    </button>
                </div>
            </form>
            
            <div class="print-area">
                <div class="print-header mb-8">
                    <div class="flex justify-between items-center border-b-2 border-black pb-2">
                        <div class="text-left">
                            <h2 class="text-lg font-bold text-black">LAPORAN PEROLEHAN ZAKAT</h2>
                            <h2 class="text-lg font-bold text-black">KANTOR LAZISMU KALIMANTAN TENGAH</h2>
                        </div>
                        <div>
                            <img src="assets/logo_lazismu.png" alt="Logo Lazismu" class="h-16 w-auto">
                        </div>
                    </div>
                    <div class="text-center mt-2 border-b-4 border-black pb-2">
                         <h3 class="text-md font-semibold text-black uppercase"><?php echo htmlspecialchars($periode_title); ?></h3>
                    </div>
                </div>

                <?php if (!empty($start_date) && !empty($end_date)): ?>
                    <div class="text-center mb-6 no-print"> 
                        <h3 class="text-lg font-medium text-dark-text">Laporan Periode: <?php echo htmlspecialchars(date('d M Y', strtotime($start_date))); ?> s/d <?php echo htmlspecialchars(date('d M Y', strtotime($end_date))); ?></h3>
                    </div>

                    <section class="mb-8">
                        <h3 class="text-xl font-semibold text-action-orange mb-4">Rekapitulasi Penerimaan Zakat</h3>
                        
                        <?php if ($jenis_zakat_filter === 'semua' || $jenis_zakat_filter === 'fitrah'): ?>
                            <h4 class="text-lg font-medium text-dark-text mt-4 mb-2">Zakat Fitrah</h4>
                            <?php if (!empty($laporan_data_fitrah)): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-table-border text-sm">
                                    <thead class="bg-table-header-bg">
                                        <tr>
                                            <th class="text-left py-2 px-3 font-semibold">Tanggal</th>
                                            <th class="text-left py-2 px-3 font-semibold">Jenis Zakat</th>
                                            <th class="text-left py-2 px-3 font-semibold">Nama Pembayar/Pengguna</th>
                                            <th class="text-right py-2 px-3 font-semibold">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($laporan_data_fitrah as $penerimaan): ?>
                                            <tr>
                                                <td class="py-2 px-3 border-b"><?php echo date('d M Y', strtotime($penerimaan['tanggal_bayar'])); ?></td>
                                                <td class="py-2 px-3 border-b"><?php echo htmlspecialchars($penerimaan['jenis_zakat_display']); ?></td>
                                                <td class="py-2 px-3 border-b"><?php echo htmlspecialchars($penerimaan['nama_pengguna_pembayar'] ?? '-'); ?></td>
                                                <td class="text-right py-2 px-3 border-b"><?php echo formatRupiah($penerimaan['nominal_bayar']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right font-bold py-2 px-3 border-t-2 border-black">Subtotal Zakat Fitrah:</td>
                                            <td class="text-right font-bold py-2 px-3 border-t-2 border-black"><?php echo formatRupiah($total_fitrah); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php else: ?>
                                <p class="text-slate-600 italic">Tidak ada data penerimaan Zakat Fitrah pada periode ini.</p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($jenis_zakat_filter === 'semua' || $jenis_zakat_filter === 'mal'): ?>
                            <h4 class="text-lg font-medium text-dark-text mt-6 mb-2">Zakat Mal</h4>
                            <?php if (!empty($laporan_data_mal)): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-table-border text-sm">
                                    <thead class="bg-table-header-bg">
                                        <tr>
                                            <th class="text-left py-2 px-3 font-semibold">Tanggal</th>
                                            <th class="text-left py-2 px-3 font-semibold">Jenis Zakat</th>
                                            <th class="text-left py-2 px-3 font-semibold">Nama Pembayar/Pengguna</th>
                                            <th class="text-right py-2 px-3 font-semibold">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($laporan_data_mal as $penerimaan): ?>
                                            <tr>
                                                <td class="py-2 px-3 border-b"><?php echo date('d M Y', strtotime($penerimaan['tanggal_bayar'])); ?></td>
                                                <td class="py-2 px-3 border-b"><?php echo htmlspecialchars($penerimaan['jenis_zakat_display']); ?></td>
                                                <td class="py-2 px-3 border-b"><?php echo htmlspecialchars($penerimaan['nama_pengguna_pembayar'] ?? '-'); ?></td>
                                                <td class="text-right py-2 px-3 border-b"><?php echo formatRupiah($penerimaan['nominal_bayar']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right font-bold py-2 px-3 border-t-2 border-black">Subtotal Zakat Mal:</td>
                                            <td class="text-right font-bold py-2 px-3 border-t-2 border-black"><?php echo formatRupiah($total_mal); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php else: ?>
                                <p class="text-slate-600 italic">Tidak ada data penerimaan Zakat Mal pada periode ini.</p>
                            <?php endif; ?>
                        <?php endif; ?>

                    </section>

                    <section>
                        <h3 class="text-xl font-semibold text-action-orange mb-4">Rekapitulasi Penyaluran Zakat</h3>
                         <?php if (!empty($laporan_data_penyaluran)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-table-border text-sm">
                                <thead class="bg-table-header-bg">
                                    <tr>
                                        <th class="text-left py-2 px-3 font-semibold">Tanggal</th>
                                        <th class="text-left py-2 px-3 font-semibold">Deskripsi Penyaluran</th>
                                        <th class="text-right py-2 px-3 font-semibold">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan_data_penyaluran as $penyaluran): ?>
                                    <tr>
                                        <td class="py-2 px-3 border-b"><?php echo date('d M Y', strtotime($penyaluran['tanggal_penyaluran'])); ?></td>
                                        <td class="py-2 px-3 border-b"><?php echo htmlspecialchars($penyaluran['deskripsi_penyaluran']); ?></td>
                                        <td class="text-right py-2 px-3 border-b"><?php echo formatRupiah($penyaluran['nominal_penyaluran']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-right font-bold py-2 px-3 border-t-2 border-black">Total Penyaluran:</td>
                                        <td class="text-right font-bold py-2 px-3 border-t-2 border-black"><?php echo formatRupiah($total_penyaluran); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-slate-600 italic">Tidak ada data penyaluran pada periode ini.</p>
                        <?php endif; ?>
                    </section>
                    
                    <section class="mt-8 pt-4 border-t-2 border-dashed">
                        <h3 class="text-xl font-semibold text-dark-text mb-4">Ringkasan Keuangan Periode Ini</h3>
                        <div class="max-w-md mx-auto">
                            <table class="min-w-full">
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 pr-4 font-semibold">TOTAL DEBET (PENERIMAAN)</td>
                                        <td class="py-2 pr-4 text-right">:</td>
                                        <td class="py-2 pr-4 text-right font-semibold"><?php echo formatRupiah($total_penerimaan); ?></td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 pr-4 font-semibold">TOTAL KREDIT (PENYALURAN)</td>
                                        <td class="py-2 pr-4 text-right">:</td>
                                        <td class="py-2 pr-4 text-right font-semibold"><?php echo formatRupiah($total_penyaluran); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 pr-4 font-bold text-lg">SALDO AKHIR</td>
                                        <td class="py-2 pr-4 text-right font-bold text-lg">:</td>
                                        <td class="py-2 pr-4 text-right font-bold text-lg"><?php echo formatRupiah($saldo_akhir); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    
                    <div class="signature-area mt-20 pt-8">
                        <div class="flex justify-between text-center text-sm text-black">
                            <div class="w-2/5">
                                <p class="mb-20">Mengetahui,</p>
                                <p class="font-bold underline">Kurniawan, S.Pd</p>
                                <p>Direktur Eksekutif</p>
                            </div>
                            <div class="w-1/5">
                                <!-- Kolom tengah kosong -->
                            </div>
                            <div class="w-2/5">
                                <p>Palangka Raya, <?php echo getTanggalCetak($bulan_indonesia); ?></p>
                                <p class="mb-20">Dibuat oleh,</p>
                                <p class="font-bold underline"><?php echo htmlspecialchars($admin_nama_lengkap_ttd); ?></p>
                                <p>Staf Keuangan</p>
                            </div>
                        </div>
                    </div>

                    <?php elseif (isset($_GET['periode'])): ?>
                    <p class="text-center text-slate-600">Silakan pilih rentang tanggal yang valid untuk menampilkan laporan atau tidak ada data untuk periode yang dipilih.</p>
                <?php else: ?>
                    <p class="text-center text-slate-600">Silakan pilih filter di atas untuk menampilkan laporan.</p>
                <?php endif; ?>
            </div> 
        </div>
    </main>

    <footer class="bg-white text-center py-8 mt-12 border-t border-slate-200 no-print">
        <p class="text-sm text-slate-600">© <span id="currentYear"></span> Kalkulator Zakat. <a href="tentang-kami.php" class="text-primary-orange hover:underline">Tentang Kami</a></p>
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
        
        const periodeFilter = document.getElementById('periodeFilter');
        const bulananInputs = document.getElementById('bulananInputs');
        const tahunanInputs = document.getElementById('tahunanInputs');
        const customDateRange = document.getElementById('customDateRange');

        function toggleFilterInputs() {
            if (periodeFilter && customDateRange && bulananInputs && tahunanInputs) {
                // Sembunyikan semua input spesifik periode
                bulananInputs.style.display = 'none';
                tahunanInputs.style.display = 'none';
                customDateRange.style.display = 'none';

                // Tampilkan input yang sesuai dengan pilihan
                const selectedPeriode = periodeFilter.value;
                if (selectedPeriode === 'bulanan') {
                    bulananInputs.style.display = 'grid';
                } else if (selectedPeriode === 'tahunan') {
                    tahunanInputs.style.display = 'block';
                } else if (selectedPeriode === 'custom') {
                    customDateRange.style.display = 'grid';
                }
            }
        }
        
        if (periodeFilter) {
            periodeFilter.addEventListener('change', toggleFilterInputs);
            toggleFilterInputs();
        }
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>