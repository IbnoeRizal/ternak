<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';
$stok_list = $koneksi->query("SELECT * FROM stok_barang ORDER BY jenis, nama_barang");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok</title>
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
                    <h1 class="h3 fw-bold">Stok Pakan & Obat</h1>
                    <button type="button" class="btn-custom" data-bs-toggle="modal" data-bs-target="#stokModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Stok
                    </button>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <table class="table table-hover">
                            <thead><tr><th>Nama Barang</th><th>Jenis</th><th>Jumlah Stok</th><th>Satuan</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php if ($stok_list->num_rows > 0): ?>
                                    <?php while($row = $stok_list->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                                            <td><span class="badge bg-secondary"><?= ucfirst($row['jenis']); ?></span></td>
                                            <td><span class="fw-bold fs-5"><?= htmlspecialchars($row['jumlah_stok']); ?></span></td>
                                            <td><?= htmlspecialchars($row['satuan']); ?></td>
                                            <td></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">Belum ada data stok.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="stokModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../actions/master_action.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Stok Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_stok"> <input type="hidden" name="entity" value="stok_barang">
                        <div class="mb-3"><label class="form-label">Nama Barang</label><input type="text" name="nama_barang" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Jenis</label><select name="jenis" class="form-select"><option value="pakan">Pakan</option><option value="obat">Obat</option><option value="vitamin">Vitamin</option><option value="lainnya">Lainnya</option></select></div>
                        <div class="row">
                            <div class="col-6"><label class="form-label">Jumlah</label><input type="number" step="0.01" name="jumlah_stok" class="form-control" required></div>
                            <div class="col-6"><label class="form-label">Satuan</label><input type="text" name="satuan" class="form-control" placeholder="Contoh: kg, botol, tablet" required></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>