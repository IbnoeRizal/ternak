<?php
session_start();
// Hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';

// Ambil semua data kandang
$kandang_list = $koneksi->query("SELECT * FROM kandang ORDER BY nama_kandang");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kandang</title>
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
                    <h1 class="h3 fw-bold">Manajemen Kandang</h1>
                    <button type="button" class="btn-custom" onclick="openModal()">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Kandang
                    </button>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Kandang</th>
                                        <th>Lokasi</th>
                                        <th>Kapasitas</th>
                                        <th>Terisi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($kandang_list->num_rows > 0): ?>
                                        <?php while($row = $kandang_list->fetch_assoc()): 
                                            // Hitung jumlah kambing di kandang ini
                                            $stmt_count = $koneksi->prepare("SELECT COUNT(id) as jumlah FROM kambing WHERE id_kandang = ?");
                                            $stmt_count->bind_param("i", $row['id']);
                                            $stmt_count->execute();
                                            $terisi = $stmt_count->get_result()->fetch_assoc()['jumlah'];
                                        ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($row['nama_kandang']); ?></td>
                                                <td><?= htmlspecialchars($row['lokasi']); ?></td>
                                                <td><?= htmlspecialchars($row['kapasitas']); ?> Ekor</td>
                                                <td><?= $terisi; ?> Ekor</td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick='openModal(<?= json_encode($row); ?>)'>
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">Belum ada data kandang.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="kandangModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="kandangForm" action="../actions/master_action.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Form Kandang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction">
                        <input type="hidden" name="entity" value="kandang">
                        <input type="hidden" name="id" id="kandangId">
                        <div class="mb-3"><label class="form-label">Nama Kandang</label><input type="text" name="nama_kandang" id="nama_kandang" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Lokasi</label><input type="text" name="lokasi" id="lokasi" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Kapasitas (Ekor)</label><input type="number" name="kapasitas" id="kapasitas" class="form-control" required></div>
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
    <script>
        const kandangModal = new bootstrap.Modal(document.getElementById('kandangModal'));
        function openModal(data = null) {
            const form = document.getElementById('kandangForm');
            form.reset();
            if (data) {
                document.getElementById('modalTitle').innerText = 'Edit Kandang';
                document.getElementById('formAction').value = 'update';
                document.getElementById('kandangId').value = data.id;
                document.getElementById('nama_kandang').value = data.nama_kandang;
                document.getElementById('lokasi').value = data.lokasi;
                document.getElementById('kapasitas').value = data.kapasitas;
            } else {
                document.getElementById('modalTitle').innerText = 'Tambah Kandang Baru';
                document.getElementById('formAction').value = 'create';
                document.getElementById('kandangId').value = '';
            }
            kandangModal.show();
        }
    </script>
</body>
</html>