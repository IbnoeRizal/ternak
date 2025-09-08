<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

// Data Ringkasan Keuangan
$keuangan_summary = $koneksi->query("SELECT 
    SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END) as total_pemasukan, 
    SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END) as total_pengeluaran 
    FROM keuangan")->fetch_assoc();
$saldo = $keuangan_summary['total_pemasukan'] - $keuangan_summary['total_pengeluaran'];

// Data Penjualan
$penjualan_summary = $koneksi->query("SELECT COUNT(id) as jumlah_terjual, SUM(harga_jual) as total_penjualan FROM penjualan")->fetch_assoc();

// Data untuk Grafik Pengeluaran per Kategori
$expense_by_cat_query = $koneksi->query("SELECT kat.nama, SUM(k.jumlah) as total 
                                        FROM keuangan k 
                                        JOIN kategori_keuangan kat ON k.id_kategori = kat.id 
                                        WHERE k.jenis = 'pengeluaran' 
                                        GROUP BY kat.nama ORDER BY total DESC");
$expense_data = [];
while ($row = $expense_by_cat_query->fetch_assoc()) {
    $expense_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analitik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <div class="mb-4">
                    <h1 class="h3 fw-bold">Laporan & Analitik</h1>
                    <p class="text-muted">Ringkasan data operasional dan keuangan peternakan.</p>
                </div>

                <div class="row">
                    <div class="col-md-4"><div class="card-custom p-3"><h6 class="text-muted mb-1">Total Pemasukan</h6><h4 class="fw-bold text-success">Rp <?= number_format($keuangan_summary['total_pemasukan'], 0, ',', '.'); ?></h4></div></div>
                    <div class="col-md-4"><div class="card-custom p-3"><h6 class="text-muted mb-1">Total Pengeluaran</h6><h4 class="fw-bold text-danger">Rp <?= number_format($keuangan_summary['total_pengeluaran'], 0, ',', '.'); ?></h4></div></div>
                    <div class="col-md-4"><div class="card-custom p-3"><h6 class="text-muted mb-1">Profit/Rugi</h6><h4 class="fw-bold text-primary">Rp <?= number_format($saldo, 0, ',', '.'); ?></h4></div></div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-8">
                        <div class="card-custom h-100">
                            <div class="card-body-custom">
                                <h5 class="fw-bold">Pengeluaran per Kategori</h5>
                                <canvas id="expenseChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                         <div class="card-custom h-100">
                            <div class="card-body-custom">
                                <h5 class="fw-bold mb-3">Statistik Penjualan</h5>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Kambing Terjual</span>
                                    <span class="fw-bold fs-5"><?= $penjualan_summary['jumlah_terjual'] ?> Ekor</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Total Penjualan</span>
                                    <span class="fw-bold fs-5 text-success">Rp <?= number_format($penjualan_summary['total_penjualan'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const expenseData = <?= json_encode($expense_data); ?>;
        new Chart(document.getElementById('expenseChart'), {
            type: 'bar',
            data: {
                labels: expenseData.map(item => item.nama),
                datasets: [{
                    label: 'Total Pengeluaran (Rp)',
                    data: expenseData.map(item => item.total),
                    backgroundColor: '#667eea'
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>