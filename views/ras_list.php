<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
require_once '../config/koneksi.php';
$ras_list = $koneksi->query("SELECT * FROM ras ORDER BY nama_ras");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ras</title>
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
                    <h1 class="h3 fw-bold">Manajemen Ras Kambing</h1>
                    <button type="button" class="btn-custom" onclick="openModal()">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Ras
                    </button>
                </div>

                 <div class="card-custom">
                    <div class="card-body-custom">
                        <table class="table table-hover">
                            <thead><tr><th>Nama Ras</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php if ($ras_list->num_rows > 0): ?>
                                    <?php while($row = $ras_list->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($row['nama_ras']); ?></td>
                                            <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick='openModal(<?= json_encode($row); ?>)'>Edit</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">Belum ada data ras.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="rasModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="rasForm" action="../actions/master_action.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Form Ras</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction">
                        <input type="hidden" name="entity" value="ras">
                        <input type="hidden" name="id" id="rasId">
                        <div class="mb-3"><label class="form-label">Nama Ras</label><input type="text" name="nama_ras" id="nama_ras" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea></div>
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
        const rasModal = new bootstrap.Modal(document.getElementById('rasModal'));
        const modalTitle = document.getElementById('modalTitle');
        const rasForm = document.getElementById('rasForm');
        const formAction = document.getElementById('formAction');
        const rasId = document.getElementById('rasId');
        const namaRas = document.getElementById('nama_ras');
        const deskripsi = document.getElementById('deskripsi');

        function openModal(data = null) {
            rasForm.reset();
            if (data) {
                // Edit mode
                modalTitle.innerText = 'Edit Ras';
                formAction.value = 'update';
                rasId.value = data.id;
                namaRas.value = data.nama_ras;
                deskripsi.value = data.deskripsi;
            } else {
                // Create mode
                modalTitle.innerText = 'Tambah Ras Baru';
                formAction.value = 'create';
                rasId.value = '';
            }
            rasModal.show();
        }
    </script>
</body>
</html>