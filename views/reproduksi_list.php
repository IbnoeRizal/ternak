<?php
session_start();
// Hanya peternak atau admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['peran'], ['peternak', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

// Query untuk mengambil data reproduksi
$sql = "SELECT r.*, betina.nama as nama_betina, jantan.nama as nama_jantan 
        FROM reproduksi r 
        JOIN kambing betina ON r.id_betina = betina.id 
        JOIN kambing jantan ON r.id_jantan = jantan.id";

if ($peran_pengguna == 'peternak') {
    $sql .= " WHERE betina.id_pemilik = ?";
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
    <title>Manajemen Reproduksi</title>
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
                    <h1 class="h3 fw-bold">Manajemen Reproduksi</h1>
                    <a href="reproduksi_form.php" class="btn-custom">
                        <i class="bi bi-heart-pulse-fill me-2"></i>Catat Perkawinan
                    </a>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal Kawin</th>
                                        <th>Induk Betina</th>
                                        <th>Pejantan</th>
                                        <th>Status</th>
                                        <th>Perkiraan Lahir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($row['tanggal_kawin'])); ?></td>
                                                <td><?= htmlspecialchars($row['nama_betina']); ?></td>
                                                <td><?= htmlspecialchars($row['nama_jantan']); ?></td>
                                                <td>
                                                    <?php if ($row['bunting'] == 1): ?>
                                                        <span class="badge bg-success">Bunting</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Proses</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $row['perkiraan_lahir'] ? date('d M Y', strtotime($row['perkiraan_lahir'])) : '-'; ?></td>
                                                <td>
                                                    <a href="kelahiran_form.php?reproduksi_id=<?= $row['id']; ?>" class="btn btn-sm btn-info">Catat Lahir</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">Belum ada data reproduksi.</td></tr>
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