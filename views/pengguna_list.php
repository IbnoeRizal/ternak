<?php
session_start();
// Hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: dashboard.php'); // Arahkan ke dashboard jika bukan admin
    exit;
}

require_once '../config/koneksi.php';

// Ambil semua data pengguna kecuali admin yang sedang login
$current_admin_id = $_SESSION['user_id'];
$sql = "SELECT id, nama, email, peran, nomor_telepon FROM pengguna WHERE id != ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $current_admin_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .table-action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold">Manajemen Pengguna</h1>
                    <a href="pengguna_form.php" class="btn-custom">
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna
                    </a>
                </div>

                <?php if (isset($_SESSION['pesan'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['pesan']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['pesan']); ?>
                <?php endif; ?>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Peran</th>
                                        <th>No. Telepon</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                                <td><?= htmlspecialchars($row['email']); ?></td>
                                                <td><span class="badge bg-info"><?= ucfirst($row['peran']); ?></span></td>
                                                <td><?= htmlspecialchars($row['nomor_telepon'] ?? '-'); ?></td>
                                                <td>
                                                    <a href="pengguna_form.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm table-action-btn" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form action="../actions/pengguna_action.php" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm table-action-btn" title="Hapus">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada data pengguna.</td>
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