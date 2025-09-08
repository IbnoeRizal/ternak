<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

// Fungsi untuk upload gambar
function uploadBukti($file) {
    $target_dir = "../uploads/bukti/";
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // Generate nama file unik
    $unique_name = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_name;
    
    // Validasi file
    if ($file["size"] > 5000000) { // 5MB
        $_SESSION['error'] = "File terlalu besar. Maksimal 5MB.";
        return false;
    }
    
    // Validasi format file
    $allowed_formats = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowed_formats)) {
        $_SESSION['error'] = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        return false;
    }
    
    // Validasi apakah file benar-benar gambar
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "File bukan gambar yang valid.";
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $unique_name;
    } else {
        $_SESSION['error'] = "Gagal mengupload file.";
        return false;
    }
}

// Fungsi untuk menghapus file bukti
function hapusBukti($nama_file) {
    if (!empty($nama_file) && $nama_file != 'tidakada.jpg') {
        $file_path = "../uploads/bukti/" . $nama_file;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $jenis = $_POST['jenis'];
                $jumlah = str_replace(['.', ','], '', $_POST['jumlah']); // Hapus format ribuan
                $sumber_dana = $_POST['sumber_dana'];
                $status_pembayaran = $_POST['status_pembayaran'];
                $id_kategori = $_POST['id_kategori'];
                $id_kambing = !empty($_POST['id_kambing']) ? $_POST['id_kambing'] : NULL;
                $deskripsi = $_POST['deskripsi'];
                $tanggal_transaksi = $_POST['tanggal_transaksi'];
                $bukti = '';
                
                // Handle upload bukti
                if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
                    $upload_result = uploadBukti($_FILES['bukti']);
                    if ($upload_result) {
                        $bukti = $upload_result;
                    } else {
                        // Jika ada error upload, redirect dengan pesan error
                        header('Location: ../views/keuangan_form.php');
                        exit;
                    }
                }

                $stmt = $koneksi->prepare("INSERT INTO keuangan (id_pengguna, jenis, jumlah, sumber_dana, status_pembayaran, id_kategori, id_kambing, deskripsi, tanggal_transaksi, bukti) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isdssiisss", $id_pengguna, $jenis, $jumlah, $sumber_dana, $status_pembayaran, $id_kategori, $id_kambing, $deskripsi, $tanggal_transaksi, $bukti);
                
                if ($stmt->execute()) {
                    $_SESSION['pesan'] = "Transaksi berhasil dicatat.";
                } else {
                    $_SESSION['error'] = "Gagal mencatat transaksi: " . $stmt->error;
                    // Hapus file yang sudah diupload jika query gagal
                    if (!empty($bukti)) {
                        hapusBukti($bukti);
                    }
                }
                $stmt->close();
                break;
            
            case 'update':
                $id = $_POST['id'];
                $jenis = $_POST['jenis'];
                $jumlah = str_replace(['.', ','], '', $_POST['jumlah']);
                $sumber_dana = $_POST['sumber_dana'];
                $status_pembayaran = $_POST['status_pembayaran'];
                $id_kategori = $_POST['id_kategori'];
                $id_kambing = !empty($_POST['id_kambing']) ? $_POST['id_kambing'] : NULL;
                $deskripsi = $_POST['deskripsi'];
                $tanggal_transaksi = $_POST['tanggal_transaksi'];
                
                // Cek apakah user berhak mengupdate transaksi ini
                $check_stmt = $koneksi->prepare("SELECT id_pengguna, bukti FROM keuangan WHERE id = ?");
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $existing_data = $check_result->fetch_assoc();
                $check_stmt->close();
                
                if (!$existing_data) {
                    $_SESSION['error'] = "Transaksi tidak ditemukan.";
                    header('Location: ../views/keuangan_list.php');
                    exit;
                }
                
                // Validasi hak akses
                if ($peran_pengguna != 'admin' && $existing_data['id_pengguna'] != $id_pengguna) {
                    $_SESSION['error'] = "Anda tidak memiliki hak untuk mengubah transaksi ini.";
                    header('Location: ../views/keuangan_list.php');
                    exit;
                }
                
                $bukti = $existing_data['bukti'];
                
                // Handle upload bukti baru
                if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
                    $upload_result = uploadBukti($_FILES['bukti']);
                    if ($upload_result) {
                        // Hapus bukti lama jika ada
                        hapusBukti($existing_data['bukti']);
                        $bukti = $upload_result;
                    } else {
                        // Jika ada error upload, redirect dengan pesan error
                        header('Location: keuangan_form.php?id=' . $id);
                        exit;
                    }
                }
                
                // Handle hapus bukti
                if (isset($_POST['hapus_bukti']) && $_POST['hapus_bukti'] == '1') {
                    hapusBukti($existing_data['bukti']);
                    $bukti = '';
                }

                $stmt = $koneksi->prepare("UPDATE keuangan SET jenis=?, jumlah=?, sumber_dana=?, status_pembayaran=?, id_kategori=?, id_kambing=?, deskripsi=?, tanggal_transaksi=?, bukti=? WHERE id=?");
                $stmt->bind_param("sdssiisssi", $jenis, $jumlah, $sumber_dana, $status_pembayaran, $id_kategori, $id_kambing, $deskripsi, $tanggal_transaksi, $bukti, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['pesan'] = "Transaksi berhasil diperbarui.";
                } else {
                    $_SESSION['error'] = "Gagal memperbarui transaksi: " . $stmt->error;
                }
                $stmt->close();
                break;
        }
    } elseif (isset($_POST['aksi']) && $_POST['aksi'] == 'lunas') {
        // Handle tandai lunas
        $id = $_POST['id'];
        
        // Cek apakah user berhak mengubah status
        $check_stmt = $koneksi->prepare("SELECT id_pengguna FROM keuangan WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $existing_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if (!$existing_data) {
            $_SESSION['error'] = "Transaksi tidak ditemukan.";
            header('Location: ../views/keuangan_list.php');
            exit;
        }
        
        // Validasi hak akses
        if ($peran_pengguna != 'admin' && $existing_data['id_pengguna'] != $id_pengguna) {
            $_SESSION['error'] = "Anda tidak memiliki hak untuk mengubah status transaksi ini.";
            header('Location: ../views/keuangan_list.php');
            exit;
        }
        
        $stmt = $koneksi->prepare("UPDATE keuangan SET status_pembayaran = 'lunas' WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['pesan'] = "Status pembayaran berhasil diubah menjadi lunas.";
        } else {
            $_SESSION['error'] = "Gagal mengubah status pembayaran: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle GET requests (untuk delete)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Cek apakah user berhak menghapus transaksi ini
    $check_stmt = $koneksi->prepare("SELECT id_pengguna, bukti FROM keuangan WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $existing_data = $check_result->fetch_assoc();
    $check_stmt->close();
    
    if (!$existing_data) {
        $_SESSION['error'] = "Transaksi tidak ditemukan.";
        header('Location: ../views/keuangan_list.php');
        exit;
    }
    
    // Validasi hak akses
    if ($peran_pengguna != 'admin' && $existing_data['id_pengguna'] != $id_pengguna) {
        $_SESSION['error'] = "Anda tidak memiliki hak untuk menghapus transaksi ini.";
        header('Location: ../views/keuangan_list.php');
        exit;
    }
    
    // Hapus bukti transaksi jika ada
    hapusBukti($existing_data['bukti']);
    
    // Hapus transaksi
    $stmt = $koneksi->prepare("DELETE FROM keuangan WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['pesan'] = "Transaksi berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus transaksi: " . $stmt->error;
    }
    $stmt->close();
}

header('Location: ../views/keuangan_list.php');
exit;
?>