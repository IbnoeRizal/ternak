<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-fluid">
                <h1 class="h3 fw-bold mb-4">Pengaturan Akun</h1>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                 <?php if (isset($_SESSION['pesan'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['pesan']; ?></div>
                    <?php unset($_SESSION['pesan']); ?>
                <?php endif; ?>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <h5 class="fw-bold mb-4">Ubah Password</h5>
                        <p class="text-muted">Untuk keamanan, jangan bagikan password Anda kepada siapa pun.</p>
                        <form action="../actions/profil_action.php" method="POST">
                            <input type="hidden" name="action" value="update_password">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="password_lama" class="form-control" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password_baru" class="form-control" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn-custom">Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>