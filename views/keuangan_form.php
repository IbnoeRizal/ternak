<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

// Inisialisasi variabel
$id = null;
$jenis = '';
$jumlah = '';
$sumber_dana = '';
$status_pembayaran = '';
$id_kategori = '';
$id_kambing = '';
$deskripsi = '';
$tanggal_transaksi = date('Y-m-d');
$bukti = '';
$mode = 'create';

// Jika mode edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $mode = 'update';
    
    // Ambil data transaksi untuk edit
    $stmt = $koneksi->prepare("SELECT * FROM keuangan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$data) {
        $_SESSION['error'] = "Transaksi tidak ditemukan.";
        header('Location: keuangan_list.php');
        exit;
    }
    
    // Validasi hak akses
    if ($peran_pengguna != 'admin' && $data['id_pengguna'] != $id_pengguna) {
        $_SESSION['error'] = "Anda tidak memiliki hak untuk mengubah transaksi ini.";
        header('Location: keuangan_list.php');
        exit;
    }
    
    // Isi variabel dengan data dari database
    $jenis = $data['jenis'];
    $jumlah = number_format($data['jumlah'], 0, ',', '.');
    $sumber_dana = $data['sumber_dana'];
    $status_pembayaran = $data['status_pembayaran'];
    $id_kategori = $data['id_kategori'];
    $id_kambing = $data['id_kambing'];
    $deskripsi = $data['deskripsi'];
    $tanggal_transaksi = $data['tanggal_transaksi'];
    $bukti = $data['bukti'];
}

// Ambil data kategori
$kategori_query = "SELECT * FROM kategori_keuangan ORDER BY nama";
$kategori_result = $koneksi->query($kategori_query);

// Ambil data kambing (jika diperlukan)
$kambing_query = "SELECT * FROM kambing ORDER BY nama";
$kambing_result = $koneksi->query($kambing_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $mode == 'create' ? 'Tambah' : 'Edit' ?> Transaksi Keuangan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .preview-container {
            position: relative;
            display: inline-block;
            max-width: 200px;
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
        }
        .file-input-container {
            position: relative;
            display: inline-block;
        }
        .file-input-label {
            display: inline-block;
            padding: 8px 16px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-input-label:hover {
            background: #e9ecef;
            border-color: #007bff;
        }
        .file-input-label i {
            font-size: 24px;
            color: #6c757d;
        }
        #bukti {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content flex-grow-1">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold"><?= $mode == 'create' ? 'Tambah' : 'Edit' ?> Transaksi Keuangan</h1>
                <a href="keuangan_list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['pesan'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card-custom">
                <div class="card-body-custom">
                    <form action="/actions/keuangan_action.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?= $mode ?>">
                        <?php if ($mode == 'update'): ?>
                            <input type="hidden" name="id" value="<?= $id ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis" class="form-label">Jenis Transaksi</label>
                                    <select class="form-select" id="jenis" name="jenis" required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="pemasukan" <?= $jenis == 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                                        <option value="pengeluaran" <?= $jenis == 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah" class="form-label">Jumlah</label>
                                    <input type="text" class="form-control" id="jumlah" name="jumlah" value="<?= htmlspecialchars($jumlah) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sumber_dana" class="form-label">Sumber Dana</label>
                                    <select class="form-select" id="sumber_dana" name="sumber_dana" required>
                                        <option value="">Pilih Sumber Dana</option>
                                        <option value="kas_peternakan" <?= $sumber_dana == 'kas_bersama' ? 'selected' : '' ?>>Kas Bersama</option>
                                        <option value="pribadi" <?= $sumber_dana == 'pribadi' ? 'selected' : '' ?>>Pribadi</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
                                    <select class="form-select" id="status_pembayaran" name="status_pembayaran" required>
                                        <option value="">Pilih Status</option>
                                        <option value="lunas" <?= $status_pembayaran == 'lunas' ? 'selected' : '' ?>>Lunas</option>
                                        <option value="belum_diganti" <?= $status_pembayaran == 'belum_diganti' ? 'selected' : '' ?>>Belum Diganti</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_kategori" class="form-label">Kategori</label>
                                    <select class="form-select" id="id_kategori" name="id_kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php while ($kategori = $kategori_result->fetch_assoc()): ?>
                                            <option value="<?= $kategori['id'] ?>" <?= $id_kategori == $kategori['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kategori['nama']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_kambing" class="form-label">Kambing (Opsional)</label>
                                    <select class="form-select" id="id_kambing" name="id_kambing">
                                        <option value="">Pilih Kambing</option>
                                        <?php while ($kambing = $kambing_result->fetch_assoc()): ?>
                                            <option value="<?= $kambing['id'] ?>" <?= $id_kambing == $kambing['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kambing['nama']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_transaksi" class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="<?= $tanggal_transaksi ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?= htmlspecialchars($deskripsi) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti Transaksi</label>
                            
                            <!-- Preview gambar yang sudah ada -->
                            <div id="current-image" <?= empty($bukti) ? 'style="display:none;"' : '' ?>>
                                <div class="preview-container mb-3">
                                    <img id="current-preview" src="uploads/bukti/<?= htmlspecialchars($bukti) ?>" class="preview-image" alt="Bukti Transaksi">
                                    <button type="button" class="remove-image" onclick="removeCurrentImage()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="hapus_bukti" name="hapus_bukti" value="0">
                            </div>

                            <!-- Input file untuk upload gambar baru -->
                            <div class="file-input-container">
                                <label for="bukti" class="file-input-label text-center">
                                    <i class="bi bi-cloud-upload d-block mb-2"></i>
                                    <span>Klik untuk pilih gambar</span>
                                    <small class="d-block text-muted mt-1">JPG, JPEG, PNG, GIF (Max: 5MB)</small>
                                </label>
                                <input type="file" id="bukti" name="bukti" accept="image/*" onchange="previewImage(this)">
                            </div>

                            <!-- Preview gambar baru -->
                            <div id="new-image-preview" style="display:none;">
                                <div class="preview-container mt-3">
                                    <img id="new-preview" class="preview-image" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeNewImage()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="keuangan_list.php" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i><?= $mode == 'create' ? 'Simpan' : 'Update' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Format input jumlah
    document.getElementById('jumlah').addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        this.value = new Intl.NumberFormat('id-ID').format(value);
    });

    // Preview gambar baru
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('new-preview').src = e.target.result;
                document.getElementById('new-image-preview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Remove gambar yang sudah ada
    function removeCurrentImage() {
        document.getElementById('current-image').style.display = 'none';
        document.getElementById('hapus_bukti').value = '1';
    }

    // Remove preview gambar baru
    function removeNewImage() {
        document.getElementById('bukti').value = '';
        document.getElementById('new-image-preview').style.display = 'none';
    }

    // Hide file input label when showing status payment for pribadi
    document.getElementById('sumber_dana').addEventListener('change', function() {
        const statusPayment = document.getElementById('status_pembayaran');
        if (this.value === 'pribadi') {
            statusPayment.value = 'belum_diganti';
        } else {
            statusPayment.value = 'lunas';
        }
    });
</script>
</body>
</html>