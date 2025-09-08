<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['peternak', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

$sql = "SELECT rk.*, k.nama as nama_kambing, k.kode_tag 
        FROM riwayat_kesehatan rk 
        JOIN kambing k ON rk.id_kambing = k.id";

if ($peran_pengguna == 'peternak') {
    $sql .= " WHERE k.id_pemilik = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_pengguna);
} else {
    $stmt = $koneksi->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold">Riwayat Kesehatan</h1>
                    <a href="kesehatan_form.php" class="btn-custom">
                        <i class="bi bi-file-earmark-medical-fill me-2"></i>Catat Pemeriksaan
                    </a>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal Periksa</th>
                                        <th>Kambing</th>
                                        <th>Kondisi</th>
                                        <th>Penanganan</th>
                                        <th>Obat Diberikan</th>
                                        <th>Jadwal Berikutnya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($row['tanggal_periksa'])); ?></td>
                                                <td><?= htmlspecialchars($row['nama_kambing'] . ' (' . $row['kode_tag'] . ')'); ?></td>
                                                <td><?= htmlspecialchars($row['kondisi']); ?></td>
                                                <td><?= htmlspecialchars($row['penanganan']); ?></td>
                                                <td><?= htmlspecialchars($row['obat_yang_diberikan']); ?></td>
                                                <td><?= $row['jadwal_periksa_berikutnya'] ? date('d M Y', strtotime($row['jadwal_periksa_berikutnya'])) : '-'; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">Belum ada riwayat kesehatan.</td></tr>
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