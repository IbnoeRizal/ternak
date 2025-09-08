<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
    header('Location: ../views/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['entity'])) {
    
    $entity = $_POST['entity'];
    $action = $_POST['action'];
    $redirect_url = "../views/{$entity}_list.php";

    switch ($entity) {
        // --- LOGIKA UNTUK ENTITAS 'RAS' ---
        case 'ras':
            if ($action == 'create') {
                $stmt = $koneksi->prepare("INSERT INTO ras (nama_ras, deskripsi) VALUES (?, ?)");
                $stmt->bind_param("ss", $_POST['nama_ras'], $_POST['deskripsi']);
                $_SESSION['pesan'] = "Data ras berhasil ditambahkan.";
            } elseif ($action == 'update') {
                $stmt = $koneksi->prepare("UPDATE ras SET nama_ras=?, deskripsi=? WHERE id=?");
                $stmt->bind_param("ssi", $_POST['nama_ras'], $_POST['deskripsi'], $_POST['id']);
                $_SESSION['pesan'] = "Data ras berhasil diperbarui.";
            } elseif ($action == 'delete') {
                $stmt = $koneksi->prepare("DELETE FROM ras WHERE id=?");
                $stmt->bind_param("i", $_POST['id']);
                $_SESSION['pesan'] = "Data ras berhasil dihapus.";
            }
            break;
            
        // --- LOGIKA UNTUK ENTITAS 'KANDANG' ---
        case 'kandang':
            if ($action == 'create') {
                $stmt = $koneksi->prepare("INSERT INTO kandang (nama_kandang, lokasi, kapasitas) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $_POST['nama_kandang'], $_POST['lokasi'], $_POST['kapasitas']);
                $_SESSION['pesan'] = "Data kandang berhasil ditambahkan.";
            } elseif ($action == 'update') {
                $stmt = $koneksi->prepare("UPDATE kandang SET nama_kandang=?, lokasi=?, kapasitas=? WHERE id=?");
                $stmt->bind_param("ssii", $_POST['nama_kandang'], $_POST['lokasi'], $_POST['kapasitas'], $_POST['id']);
                 $_SESSION['pesan'] = "Data kandang berhasil diperbarui.";
            } elseif ($action == 'delete') {
                $stmt = $koneksi->prepare("DELETE FROM kandang WHERE id=?");
                $stmt->bind_param("i", $_POST['id']);
                 $_SESSION['pesan'] = "Data kandang berhasil dihapus.";
            }
            break;
    }

    // Eksekusi query jika stmt sudah disiapkan
    if (isset($stmt)) {
        if (!$stmt->execute()) {
            $_SESSION['error'] = "Terjadi kesalahan: " . $stmt->error;
            // Hapus pesan sukses jika ada error
            unset($_SESSION['pesan']);
        }
        $stmt->close();
    }
} else {
    // Jika tidak ada entity, redirect ke dashboard
    $redirect_url = '../views/dashboard.php';
}

header("Location: $redirect_url");
exit;
?>