<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

// Query dasar
$sql = "SELECT k.*, r.nama_ras, kd.nama_kandang, p.nama AS nama_pemilik 
        FROM kambing k
        LEFT JOIN ras r ON k.id_ras = r.id
        LEFT JOIN kandang kd ON k.id_kandang = kd.id
        LEFT JOIN pengguna p ON k.id_pemilik = p.id";

if ($peran_pengguna == 'peternak' || $peran_pengguna == 'penitip') {
    $sql .= " WHERE k.id_pemilik = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_pengguna);
} else {
    $stmt = $koneksi->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kambing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .foto-kambing {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }
        .table-action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 768px) {
            .table-responsive {
                display: none;
            }
            .card-mobile {
                display: block;
            }
        }
        @media (min-width: 769px) {
            .card-mobile {
                display: none;
            }
        }
        .card-mobile .card {
            margin-bottom: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            background-color: #fff;
        }
        .card-mobile .badge {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold">Manajemen Kambing</h1>
                    <a href="kambing_form.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Kambing
                    </a>
                </div>

                <?php if (isset($_SESSION['pesan'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['pesan']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['pesan']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- TABEL DESKTOP -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Foto</th>
                                <th>Kode Tag</th>
                                <th>Nama</th>
                                <th>Ras</th>
                                <th>JK</th>
                                <th>Status</th>
                                <?php if ($peran_pengguna == 'admin') echo '<th>Pemilik</th>'; ?>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($rows): ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['foto']) && file_exists("../uploads/kambing/" . $row['foto'])): ?>
                                            <img src="../uploads/kambing/<?= htmlspecialchars($row['foto']); ?>" class="foto-kambing" alt="Foto Kambing">
                                        <?php else: ?>
                                            <span class="text-muted">Tidak Ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_tag']); ?></span></td>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['nama_ras']); ?></td>
                                    <td><?= ucfirst($row['jenis_kelamin']); ?></td>
                                    <td><span class="badge bg-primary"><?= ucfirst($row['status']); ?></span></td>
                                    <?php if ($peran_pengguna == 'admin') echo '<td>' . htmlspecialchars($row['nama_pemilik']) . '</td>'; ?>
                                    <td>
                                        <a href="kambing_detail.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm table-action-btn"><i class="bi bi-eye"></i></a>
                                        <a href="kambing_form.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm table-action-btn"><i class="bi bi-pencil"></i></a>
                                        <form action="../actions/kambing_action.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm table-action-btn"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="<?= ($peran_pengguna == 'admin') ? '8' : '7'; ?>" class="text-center">Tidak ada data.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- KARTU MOBILE -->
                <div class="card-mobile">
                    <?php foreach ($rows as $row): ?>
                        <div class="card">
                            <?php if (!empty($row['foto']) && file_exists("../uploads/kambing/" . $row['foto'])): ?>
                                <img src="../uploads/kambing/<?= htmlspecialchars($row['foto']); ?>" class="foto-kambing mb-2" alt="Foto Kambing">
                            <?php else: ?>
                                <div class="text-muted">Tidak ada foto</div>
                            <?php endif; ?>

                            <p><strong>Kode Tag:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($row['kode_tag']); ?></span></p>
                            <p><strong>Nama:</strong> <?= htmlspecialchars($row['nama']); ?></p>
                            <p><strong>Ras:</strong> <?= htmlspecialchars($row['nama_ras']); ?></p>
                            <p><strong>JK:</strong> <?= ucfirst($row['jenis_kelamin']); ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-primary"><?= ucfirst($row['status']); ?></span></p>
                            <?php if ($peran_pengguna == 'admin'): ?>
                                <p><strong>Pemilik:</strong> <?= htmlspecialchars($row['nama_pemilik']); ?></p>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <a href="kambing_detail.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                <a href="kambing_form.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                <form action="../actions/kambing_action.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
