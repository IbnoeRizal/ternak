<?php
session_start();
// Hanya penitip yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'penitip') {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

$id_penitip = $_SESSION['user_id'];

// Ambil semua data pengeluaran yang terkait dengan penitip ini
$sql = "SELECT k.*, kat.nama AS nama_kategori
        FROM keuangan k
        JOIN kategori_keuangan kat ON k.id_kategori = kat.id
        WHERE k.id_pengguna = ? AND k.jenis = 'pengeluaran'
        ORDER BY k.tanggal_transaksi DESC";
        
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_penitip);
$stmt->execute();
$result = $stmt->get_result();

// Hitung total tagihan
$total_tagihan = 0;
mysqli_data_seek($result, 0);
while ($row = $result->fetch_assoc()) {
    $total_tagihan += $row['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan & Biaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <div class="mb-4">
                    <h1 class="h3 fw-bold">Tagihan & Biaya Perawatan</h1>
                    <p class="text-muted">Rincian semua biaya yang terkait dengan penitipan kambing Anda.</p>
                </div>
                
                <div class="card-custom mb-4">
                    <div class="card-body-custom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Semua Tagihan</h6>
                            <h3 class="fw-bold text-danger mb-0">Rp <?= number_format($total_tagihan, 0, ',', '.'); ?></h3>
                        </div>
                        <i class="bi bi-receipt-cutoff text-danger" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                         <h5 class="fw-bold mb-3">Rincian Tagihan</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Deskripsi Biaya</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php mysqli_data_seek($result, 0); ?>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                                <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']); ?></span></td>
                                                <td class="fw-bold">Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada tagihan yang tercatat.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>