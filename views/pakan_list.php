<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['peternak', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

$sql = "SELECT rp.*, k.nama as nama_kambing, k.kode_tag 
        FROM riwayat_pakan rp 
        JOIN kambing k ON rp.id_kambing = k.id";

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
    <title>Riwayat Pakan</title>
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
                    <h1 class="h3 fw-bold">Riwayat Pemberian Pakan</h1>
                    <a href="pakan_form.php" class="btn-custom">
                        <i class="bi bi-basket3-fill me-2"></i>Catat Pemberian Pakan
                    </a>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu Pakan</th>
                                        <th>Kambing</th>
                                        <th>Jenis Pakan</th>
                                        <th>Jumlah (kg)</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d M Y, H:i', strtotime($row['waktu_pakan'])); ?></td>
                                                <td><?= htmlspecialchars($row['nama_kambing'] . ' (' . $row['kode_tag'] . ')'); ?></td>
                                                <td><?= htmlspecialchars($row['jenis_pakan']); ?></td>
                                                <td class="fw-bold"><?= htmlspecialchars($row['jumlah_kg']); ?></td>
                                                <td><?= htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">Belum ada riwayat pemberian pakan.</td></tr>
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