<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_pengguna = $_SESSION['user_id'];

    switch ($_POST['action']) {
        case 'update_profil':
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $nomor_telepon = $_POST['nomor_telepon'];
            $alamat = $_POST['alamat'];

            $stmt = $koneksi->prepare("UPDATE pengguna SET nama=?, email=?, nomor_telepon=?, alamat=? WHERE id=?");
            $stmt->bind_param("ssssi", $nama, $email, $nomor_telepon, $alamat, $id_pengguna);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Profil berhasil diperbarui.";
                $_SESSION['nama'] = $nama; // Update juga sesi nama
            } else {
                $_SESSION['error'] = "Gagal memperbarui profil.";
            }
            $stmt->close();
            break;

        case 'update_password':
            $password_lama = $_POST['password_lama'];
            $password_baru = $_POST['password_baru'];
            $konfirmasi_password = $_POST['konfirmasi_password'];

            // Cek apakah password baru dan konfirmasi cocok
            if ($password_baru !== $konfirmasi_password) {
                $_SESSION['error'] = "Password baru dan konfirmasi tidak cocok!";
                header('Location: ../views/profil.php');
                exit;
            }

            // Ambil password saat ini dari DB
            $stmt_select = $koneksi->prepare("SELECT password FROM pengguna WHERE id = ?");
            $stmt_select->bind_param("i", $id_pengguna);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $user = $result->fetch_assoc();
            $stmt_select->close();

            // PERINGATAN: Membandingkan password lama sebagai teks biasa!
            if ($user && $password_lama === $user['password']) {
                // Jika password lama cocok, update dengan password baru
                // PERINGATAN: Menyimpan password baru sebagai teks biasa!
                $stmt_update = $koneksi->prepare("UPDATE pengguna SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $password_baru, $id_pengguna);
                if ($stmt_update->execute()) {
                    $_SESSION['pesan'] = "Password berhasil diubah.";
                } else {
                    $_SESSION['error'] = "Gagal mengubah password.";
                }
                $stmt_update->close();
            } else {
                $_SESSION['error'] = "Password lama yang Anda masukkan salah.";
            }
            break;
    }
}

header('Location: ../views/profil.php');
exit;
?>