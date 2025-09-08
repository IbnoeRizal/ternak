<?php
session_start();
// Hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once '../config/koneksi.php';

// Inisialisasi variabel
$pengguna = [
    'id' => '', 'nama' => '', 'email' => '', 'password' => '',
    'peran' => 'peternak', 'nomor_telepon' => '', 'alamat' => ''
];
$is_edit = false;
$page_title = "Tambah Pengguna Baru";

// Cek apakah ini mode edit
if (isset($_GET['id'])) {
    $is_edit = true;
    $id_pengguna = $_GET['id'];
    $page_title = "Edit Data Pengguna";

    $stmt = $koneksi->prepare("SELECT * FROM pengguna WHERE id = ?");
    $stmt->bind_param("i", $id_pengguna);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $pengguna = $result->fetch_assoc();
    } else {
        header('Location: pengguna_list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 fw-bold"><?= $page_title; ?></h1>
                    <a href="pengguna_list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <form action="../actions/pengguna_action.php" method="POST">
                            <input type="hidden" name="action" value="<?= $is_edit ? 'update' : 'create'; ?>">
                            <?php if ($is_edit): ?>
                                <input type="hidden" name="id" value="<?= $pengguna['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($pengguna['nama']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Alamat Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($pengguna['email']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" <?= !$is_edit ? 'required' : ''; ?>>
                                    <?php if ($is_edit): ?>
                                        <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="peran" class="form-label">Peran (Role)</label>
                                    <select class="form-select" id="peran" name="peran" required>
                                        <option value="peternak" <?= ($pengguna['peran'] == 'peternak') ? 'selected' : ''; ?>>Peternak</option>
                                        <option value="penitip" <?= ($pengguna['peran'] == 'penitip') ? 'selected' : ''; ?>>Penitip</option>
                                        <option value="admin" <?= ($pengguna['peran'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($pengguna['nomor_telepon']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($pengguna['alamat']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn-custom">
                                    <i class="bi bi-save-fill me-2"></i>Simpan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>