<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: ../views/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    switch ($_POST['action']) {
        case 'create':
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $password = $_POST['password']; // Mengambil password sebagai teks biasa
            $peran = $_POST['peran'];
            $nomor_telepon = $_POST['nomor_telepon'];
            $alamat = $_POST['alamat'];

            // PERINGATAN: MENYIMPAN PASSWORD TANPA HASH!
            $stmt = $koneksi->prepare("INSERT INTO pengguna (nama, email, password, peran, nomor_telepon, alamat) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nama, $email, $password, $peran, $nomor_telepon, $alamat);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Pengguna baru berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan pengguna: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'update':
            $id = $_POST['id'];
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $peran = $_POST['peran'];
            $nomor_telepon = $_POST['nomor_telepon'];
            $alamat = $_POST['alamat'];
            $password = $_POST['password'];

            if (!empty($password)) {
                // Jika password baru diisi, update password
                // PERINGATAN: MENYIMPAN PASSWORD TANPA HASH!
                $stmt = $koneksi->prepare("UPDATE pengguna SET nama=?, email=?, peran=?, nomor_telepon=?, alamat=?, password=? WHERE id=?");
                $stmt->bind_param("ssssssi", $nama, $email, $peran, $nomor_telepon, $alamat, $password, $id);
            } else {
                // Jika password kosong, jangan update password
                $stmt = $koneksi->prepare("UPDATE pengguna SET nama=?, email=?, peran=?, nomor_telepon=?, alamat=? WHERE id=?");
                $stmt->bind_param("sssssi", $nama, $email, $peran, $nomor_telepon, $alamat, $id);
            }

            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Data pengguna berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
            break;

        case 'delete':
            $id = $_POST['id'];
            $stmt = $koneksi->prepare("DELETE FROM pengguna WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Pengguna berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus pengguna.";
            }
            $stmt->close();
            break;
    }
}

header('Location: ../views/pengguna_list.php');
exit;
?>