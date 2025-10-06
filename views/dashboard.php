<?php
session_start();
// Jika tidak ada sesi user_id, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';

// Ambil data sesi untuk personalisasi
$id_pengguna = $_SESSION['user_id'];
$nama_pengguna = $_SESSION['nama'];
$peran_pengguna = $_SESSION['peran'];

// =================================================================
// LOGIKA PENGAMBILAN DATA SESUAI PERAN
// =================================================================

$data = [];

if ($peran_pengguna == 'admin') {
    // Data untuk Admin
    $q_total_kambing = mysqli_query($koneksi, "SELECT COUNT(id) AS total FROM kambing WHERE status NOT IN ('mati', 'dijual')");
    $data['total_kambing'] = mysqli_fetch_assoc($q_total_kambing)['total'] ?? 0;

    $q_total_pengguna = mysqli_query($koneksi, "SELECT COUNT(id) AS total FROM pengguna WHERE peran != 'admin'");
    $data['total_pengguna'] = mysqli_fetch_assoc($q_total_pengguna)['total'] ?? 0;

    $q_utang_pribadi = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM keuangan WHERE sumber_dana = 'pribadi' AND status_pembayaran = 'belum_diganti'");
    $data['utang_pribadi'] = mysqli_fetch_assoc($q_utang_pribadi)['total'] ?? 0;

    // Data untuk Grafik Ras
    $q_ras = mysqli_query($koneksi, "SELECT r.nama_ras, COUNT(k.id) AS jumlah FROM kambing k JOIN ras r ON k.id_ras = r.id GROUP BY r.nama_ras");
    $data['ras_chart'] = [];
    while ($row = mysqli_fetch_assoc($q_ras)) {
        $data['ras_chart'][] = $row;
    }

} elseif ($peran_pengguna == 'peternak' || $peran_pengguna == 'penitip') {
    // Data untuk Peternak & Penitip
    $q_kambing_saya = mysqli_query($koneksi, "SELECT COUNT(id) AS total FROM kambing WHERE id_pemilik = $id_pengguna AND status NOT IN ('mati', 'dijual')");
    $data['kambing_saya'] = mysqli_fetch_assoc($q_kambing_saya)['total'] ?? 0;

    $q_kambing_bunting = mysqli_query($koneksi, "SELECT COUNT(id) AS total FROM kambing WHERE id_pemilik = $id_pengguna AND status = 'bunting'");
    $data['kambing_bunting'] = mysqli_fetch_assoc($q_kambing_bunting)['total'] ?? 0;

    $q_total_biaya = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM keuangan WHERE id_pengguna = $id_pengguna AND jenis = 'pengeluaran'");
    $data['total_biaya'] = mysqli_fetch_assoc($q_total_biaya)['total'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kandang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Gaya spesifik untuk halaman dashboard */
        body {
            background-color: #f4f7f6; /* Latar belakang netral */
        }
        .main-content {
            padding: 1.5rem;
        }
        .stat-card {
            background-color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.08);
        }
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .stat-title {
            font-weight: 600;
            color: #5a6a85;
            font-size: 0.9rem;
        }
        .stat-icon {
            font-size: 1.8rem;
            padding: 12px;
            border-radius: 12px;
            color: white;
        }
        .stat-body .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        .stat-body .stat-description {
            color: #94a3b8;
            font-size: 0.8rem;
        }
        .quick-action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .quick-action-card a {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: none;
        }
        .quick-action-card a:hover {
            background-color: rgba(255,255,255,0.3);
        }
        #rasChart {
            max-height: 350px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content flex-grow-1">
            <div class="mb-4">
                <h1 class="h3 fw-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($nama_pengguna); ?>!</h1>
                <p class="text-muted">Ini adalah ringkasan aktivitas di kandang Anda hari ini.</p>
            </div>

            <div class="row">
                <?php if ($peran_pengguna == 'admin'): ?>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Kambing Aktif</span>
                            <div class="stat-icon" style="background-color: #e3f2fd; color: #1e88e5;"><i class="bi bi-ui-checks-grid"></i></div>
                        </div>
                        <div class="stat-body">
                            <h2 class="stat-number counter"><?php echo $data['total_kambing']; ?></h2>
                            <p class="stat-description">Ekor di seluruh peternakan</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Pengguna</span>
                            <div class="stat-icon" style="background-color: #e8f5e9; color: #43a047;"><i class="bi bi-people-fill"></i></div>
                        </div>
                        <div class="stat-body">
                            <h2 class="stat-number counter"><?php echo $data['total_pengguna']; ?></h2>
                            <p class="stat-description">Peternak & Penitip</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Dana Pribadi Belum Diganti</span>
                            <div class="stat-icon" style="background-color: #fff3e0; color: #fb8c00;"><i class="bi bi-wallet2"></i></div>
                        </div>
                        <div class="stat-body">
                            <h2 class="stat-number">Rp <span class="counter"><?php echo number_format($data['utang_pribadi'], 0, ',', '.'); ?></span></h2>
                            <p class="stat-description">Dari semua transaksi</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="stat-card quick-action-card text-center">
                        <h5 class="fw-bold">Aksi Cepat</h5>
                        <p class="small opacity-75">Akses menu penting dengan satu klik.</p>
                        <div class="d-grid gap-2">
                            <a href="kambing_form.php" class="btn"><i class="bi bi-plus-circle me-2"></i>Tambah Kambing</a>
                            <a href="keuangan_form.php" class="btn"><i class="bi bi-cash-coin me-2"></i>Catat Transaksi</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($peran_pengguna == 'peternak' || $peran_pengguna == 'penitip'): ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Kambing Anda</span>
                            <div class="stat-icon" style="background-color: #e3f2fd; color: #1e88e5;"><i class="bi bi-house-heart-fill"></i></div>
                        </div>
                        <div class="stat-body">
                            <h2 class="stat-number counter"><?php echo $data['kambing_saya']; ?></h2>
                            <p class="stat-description">Ekor yang Anda kelola/titipkan</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Kambing Bunting</span>
                            <div class="stat-icon" style="background-color: #fce4ec; color: #ec407a;"><i class="bi bi-heart-pulse-fill"></i></div>
                        </div>
                        <div class="stat-body">
                            <h2 class="stat-number counter"><?php echo $data['kambing_bunting']; ?></h2>
                            <p class="stat-description">Menunggu kelahiran</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Biaya Anda</span>
                            <div class="stat-icon" style="background-color: #e8f5e9; color: #43a047;"><i class="bi bi-receipt-cutoff"></i></div>
                        </div>
                        <div class="stat-body">
                           <h2 class="stat-number">Rp <span class="counter"><?php echo number_format($data['total_biaya'], 0, ',', '.'); ?></span></h2>
                            <p class="stat-description">Total pengeluaran tercatat</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($peran_pengguna == 'admin'): ?>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <h5 class="card-title-custom"><i class="bi bi-pie-chart-fill me-2"></i>Komposisi Ras Kambing</h5>
                        </div>
                        <div class="card-body-custom">
                            <canvas id="rasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Animasi Counter
        const counters = document.querySelectorAll('.counter');

        const animateCounter = (counter) => {
            const target = +counter.innerText.replace(/\./g, '');
            counter.innerText = '0';
            
            const updateCount = setInterval(() => {
                const current = +counter.innerText;

                if (current >= target) {
                    counter.innerText = target.toLocaleString('id-ID');
                    clearInterval(updateCount);
                    return;
                }
                    
                counter.innerText = Math.ceil(current + (target - current)/3);
            },10)
        };
        
        counters.forEach( i => animateCounter(i));

        // Grafik untuk Admin
        <?php if ($peran_pengguna == 'admin' && !empty($data['ras_chart'])): ?>
        const ctx = document.getElementById('rasChart').getContext('2d');
        const rasData = <?php echo json_encode($data['ras_chart']); ?>;
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: rasData.map(item => item.nama_ras),
                datasets: [{
                    label: 'Jumlah Kambing',
                    data: rasData.map(item => item.jumlah),
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(78, 205, 196, 0.8)',
                        'rgba(255, 184, 98, 0.8)',
                        'rgba(255, 107, 107, 0.8)',
                        'rgba(141, 107, 255, 0.8)',
                        'rgba(68, 204, 136, 0.8)'
                    ],
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed + ' ekor';
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>