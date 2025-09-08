<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya admin atau peternak yang bisa mengakses
if (!isset($_SESSION['user_id']) || ($_SESSION['peran'] !== 'admin' && $_SESSION['peran'] !== 'peternak')) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    function handleFotoUpload($oldFoto = null) {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array(strtolower($ext), $allowed)) return false;

            $nama_baru = 'kambing_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $lokasi = '../uploads/kambing/' . $nama_baru;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $lokasi)) {
                // Hapus foto lama jika ada
                if ($oldFoto && $oldFoto !== 'tidakada.jpg' && file_exists('../uploads/kambing/' . $oldFoto)) {
                    unlink('../uploads/kambing/' . $oldFoto);
                }
                return $nama_baru;
            }
        }
        return $oldFoto ?? 'tidakada.jpg';
    }

    switch ($_POST['action']) {
        // --- CREATE ---
        case 'create':
            $id_pemilik     = $_SESSION['peran'] === 'admin' ? $_POST['id_pemilik'] : $_SESSION['user_id'];
            $id_kandang     = $_POST['id_kandang'];
            $kode_tag       = $_POST['kode_tag'];
            $nama           = $_POST['nama'];
            $jenis_kelamin  = $_POST['jenis_kelamin'];
            $id_ras         = $_POST['id_ras'];
            $tanggal_lahir  = $_POST['tanggal_lahir'];
            $status         = $_POST['status'];
            $asal           = $_POST['asal'];
            $harga_beli     = !empty($_POST['harga_beli']) ? $_POST['harga_beli'] : 0;
            $id_induk       = !empty($_POST['id_induk']) ? $_POST['id_induk'] : null;
            $id_pejantan    = !empty($_POST['id_pejantan']) ? $_POST['id_pejantan'] : null;

            $foto = handleFotoUpload();

            $stmt = $koneksi->prepare("INSERT INTO kambing (id_pemilik, id_kandang, kode_tag, nama, jenis_kelamin, id_ras, tanggal_lahir, status, asal, harga_beli, id_induk, id_pejantan, foto)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssisssdiss", $id_pemilik, $id_kandang, $kode_tag, $nama, $jenis_kelamin, $id_ras, $tanggal_lahir, $status, $asal, $harga_beli, $id_induk, $id_pejantan, $foto);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Data kambing berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menambahkan data: " . $stmt->error;
            }
            $stmt->close();
            break;

        // --- UPDATE ---
        case 'update':
            $id             = $_POST['id'];
            $id_pemilik     = $_SESSION['peran'] === 'admin' ? $_POST['id_pemilik'] : $_SESSION['user_id'];
            $id_kandang     = $_POST['id_kandang'];
            $kode_tag       = $_POST['kode_tag'];
            $nama           = $_POST['nama'];
            $jenis_kelamin  = $_POST['jenis_kelamin'];
            $id_ras         = $_POST['id_ras'];
            $tanggal_lahir  = $_POST['tanggal_lahir'];
            $status         = $_POST['status'];
            $asal           = $_POST['asal'];
            $harga_beli     = !empty($_POST['harga_beli']) ? $_POST['harga_beli'] : 0;
            $id_induk       = !empty($_POST['id_induk']) ? $_POST['id_induk'] : null;
            $id_pejantan    = !empty($_POST['id_pejantan']) ? $_POST['id_pejantan'] : null;

            // Ambil foto lama
            $stmt_old = $koneksi->prepare("SELECT foto FROM kambing WHERE id = ?");
            $stmt_old->bind_param("i", $id);
            $stmt_old->execute();
            $result = $stmt_old->get_result();
            $row_old = $result->fetch_assoc();
            $foto_lama = $row_old['foto'] ?? 'tidakada.jpg';
            $stmt_old->close();

            $foto = handleFotoUpload($foto_lama);

            $stmt = $koneksi->prepare("UPDATE kambing SET 
                id_pemilik = ?, id_kandang = ?, kode_tag = ?, nama = ?, jenis_kelamin = ?, 
                id_ras = ?, tanggal_lahir = ?, status = ?, asal = ?, harga_beli = ?, 
                id_induk = ?, id_pejantan = ?, foto = ?
                WHERE id = ?");
            $stmt->bind_param("iisssisssdissi", $id_pemilik, $id_kandang, $kode_tag, $nama, $jenis_kelamin, $id_ras,
                $tanggal_lahir, $status, $asal, $harga_beli, $id_induk, $id_pejantan, $foto, $id);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Data kambing berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
            break;

        // --- DELETE ---
        case 'delete':
            $id = $_POST['id'];

            // Ambil foto lama untuk dihapus
            $stmt_select = $koneksi->prepare("SELECT foto FROM kambing WHERE id = ?");
            $stmt_select->bind_param("i", $id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            $row = $result->fetch_assoc();
            if ($row && $row['foto'] != 'tidakada.jpg' && file_exists("../uploads/kambing/" . $row['foto'])) {
                unlink("../uploads/kambing/" . $row['foto']);
            }
            $stmt_select->close();

            // Hapus data
            $stmt = $koneksi->prepare("DELETE FROM kambing WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = "Data kambing berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data.";
            }
            $stmt->close();
            break;
    }
}

header('Location: ../views/kambing_list.php');
exit;
