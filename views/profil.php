<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$stmt = $koneksi->prepare("SELECT * FROM pengguna WHERE id = ?");
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$pengguna = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <h1 class="h3 fw-bold mb-4">Profil Saya</h1>

                <?php if (isset($_SESSION['pesan'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['pesan']; ?></div>
                    <?php unset($_SESSION['pesan']); ?>
                <?php endif; ?>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <h5 class="fw-bold mb-4">Informasi Akun</h5>
                        <form action="../actions/profil_action.php" method="POST">
                            <input type="hidden" name="action" value="update_profil">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($pengguna['nama']); ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pengguna['email']); ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Peran</label><input type="text" class="form-control" value="<?= ucfirst($pengguna['peran']); ?>" disabled></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Nomor Telepon</label><input type="text" name="nomor_telepon" class="form-control" value="<?= htmlspecialchars($pengguna['nomor_telepon']); ?>"></div>
                                <div class="col-12 mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($pengguna['alamat']); ?></textarea></div>
                            </div>
                            <button type="submit" class="btn-custom">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>