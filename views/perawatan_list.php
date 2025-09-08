<?php
session_start();
// Hanya penitip yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'penitip') {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

$id_penitip = $_SESSION['user_id'];
$riwayat_gabungan = [];

// 1. Ambil Riwayat Kesehatan
$stmt_kesehatan = $koneksi->prepare(
    "SELECT rk.tanggal_periksa as tanggal, 'Kesehatan' as jenis, k.nama as nama_kambing, rk.penanganan as deskripsi
     FROM riwayat_kesehatan rk
     JOIN kambing k ON rk.id_kambing = k.id
     WHERE k.id_pemilik = ?"
);
$stmt_kesehatan->bind_param("i", $id_penitip);
$stmt_kesehatan->execute();
$result_kesehatan = $stmt_kesehatan->get_result();
while ($row = $result_kesehatan->fetch_assoc()) {
    $riwayat_gabungan[] = $row;
}

// 2. Ambil Riwayat Pakan
$stmt_pakan = $koneksi->prepare(
    "SELECT rp.waktu_pakan as tanggal, 'Pakan' as jenis, k.nama as nama_kambing, 
            CONCAT(rp.jenis_pakan, ' (', rp.jumlah_kg, ' kg)') as deskripsi
     FROM riwayat_pakan rp
     JOIN kambing k ON rp.id_kambing = k.id
     WHERE k.id_pemilik = ?"
);
$stmt_pakan->bind_param("i", $id_penitip);
$stmt_pakan->execute();
$result_pakan = $stmt_pakan->get_result();
while ($row = $result_pakan->fetch_assoc()) {
    $riwayat_gabungan[] = $row;
}

// 3. Urutkan riwayat gabungan berdasarkan tanggal (terbaru dulu)
usort($riwayat_gabungan, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Perawatan Kambing</title>
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
                    <h1 class="h3 fw-bold">Riwayat Perawatan</h1>
                    <p class="text-muted">Semua catatan kesehatan dan pakan untuk kambing titipan Anda.</p>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal & Waktu</th>
                                        <th>Jenis Perawatan</th>
                                        <th>Nama Kambing</th>
                                        <th>Deskripsi / Penanganan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($riwayat_gabungan)): ?>
                                        <?php foreach ($riwayat_gabungan as $riwayat): ?>
                                            <tr>
                                                <td><?= date('d M Y, H:i', strtotime($riwayat['tanggal'])); ?></td>
                                                <td>
                                                    <?php if ($riwayat['jenis'] == 'Kesehatan'): ?>
                                                        <span class="badge bg-success"><i class="bi bi-shield-plus me-1"></i> <?= $riwayat['jenis']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><i class="bi bi-basket3 me-1"></i> <?= $riwayat['jenis']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($riwayat['nama_kambing']); ?></td>
                                                <td><?= htmlspecialchars($riwayat['deskripsi']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Belum ada riwayat perawatan untuk kambing Anda.</td>
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