<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: views/dashboard.php");
    exit;
}

require_once __DIR__ . '/config/koneksi.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = "SELECT * FROM pengguna WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Perbandingan langsung (tanpa hash)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['peran'] = $user['peran'];
            header("Location: views/dashboard.php");
            exit;
        } else {
            $error = "Email atau password salah!";
        }
    } else {
        $error = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Manajemen Kandang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-body-content pt-5">
                <div class="text-center mb-4">
                    <h1 class="h3 fw-bold">Selamat Datang</h1>
                    <p class="text-muted">Silakan masuk untuk melanjutkan</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php" id="loginForm">
                    <div class="form-group mb-3">
                        <label>Email Pengguna</label>
                        <input type="email" name="email" class="form-control" required autofocus placeholder="Masukkan email Anda">
                    </div>
                    <div class="form-group mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Masuk
                    </button>
                </form>
            </div>
            <div class="login-footer text-center mt-4">
                <p><i class="bi bi-shield-check me-2"></i>© <?= date('Y') ?> Manajemen Kandang</p>
            </div>
        </div>
    </div>
</body>
</html>
