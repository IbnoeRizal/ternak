<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';

// Inisialisasi variabel
$kambing = [
    'id' => '', 'id_pemilik' => '', 'id_kandang' => '', 'kode_tag' => '', 'nama' => '', 
    'jenis_kelamin' => 'jantan', 'id_ras' => '', 'tanggal_lahir' => '', 'status' => 'sehat', 
    'asal' => 'pembelian', 'harga_beli' => '', 'id_induk' => '', 'id_pejantan' => '', 'foto' => ''
];
$is_edit = false;
$page_title = "Tambah Kambing Baru";

// Mode edit
if (isset($_GET['id'])) {
    $is_edit = true;
    $id_kambing = $_GET['id'];
    $page_title = "Edit Data Kambing";

    $stmt = $koneksi->prepare("SELECT * FROM kambing WHERE id = ?");
    $stmt->bind_param("i", $id_kambing);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $kambing = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Data kambing tidak ditemukan.";
        header('Location: kambing_list.php');
        exit;
    }
}

// Ambil data dropdown
$ras_list = $koneksi->query("SELECT id, nama_ras FROM ras ORDER BY nama_ras");
$kandang_list = $koneksi->query("SELECT id, nama_kandang FROM kandang ORDER BY nama_kandang");
$pemilik_list = $koneksi->query("SELECT id, nama FROM pengguna WHERE peran IN ('peternak', 'penitip') ORDER BY nama");
$induk_list = $koneksi->query("SELECT id, nama, kode_tag FROM kambing WHERE jenis_kelamin = 'betina' ORDER BY nama");
$pejantan_list = $koneksi->query("SELECT id, nama, kode_tag FROM kambing WHERE jenis_kelamin = 'jantan' ORDER BY nama");

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
                    <a href="kambing_list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>

                <div class="card-custom">
                    <div class="card-body-custom">
                        <form action="../actions/kambing_action.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?= $is_edit ? 'update' : 'create'; ?>">
                            <?php if ($is_edit): ?>
                                <input type="hidden" name="id" value="<?= $kambing['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Kambing</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($kambing['nama']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kode_tag" class="form-label">Kode Tag (Eartag)</label>
                                    <input type="text" class="form-control" id="kode_tag" name="kode_tag" value="<?= htmlspecialchars($kambing['kode_tag']); ?>" required>
                                </div>

                                <?php if ($_SESSION['peran'] == 'admin'): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="id_pemilik" class="form-label">Pemilik</label>
                                    <select class="form-select" id="id_pemilik" name="id_pemilik" required>
                                        <option value="">Pilih Pemilik...</option>
                                        <?php while($p = $pemilik_list->fetch_assoc()): ?>
                                            <option value="<?= $p['id']; ?>" <?= ($kambing['id_pemilik'] == $p['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($p['nama']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <div class="col-md-6 mb-3">
                                    <label for="id_ras" class="form-label">Ras</label>
                                    <select class="form-select" id="id_ras" name="id_ras" required>
                                        <option value="">Pilih Ras...</option>
                                        <?php while($r = $ras_list->fetch_assoc()): ?>
                                            <option value="<?= $r['id']; ?>" <?= ($kambing['id_ras'] == $r['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($r['nama_ras']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="jantan" <?= ($kambing['jenis_kelamin'] == 'jantan') ? 'selected' : ''; ?>>Jantan</option>
                                        <option value="betina" <?= ($kambing['jenis_kelamin'] == 'betina') ? 'selected' : ''; ?>>Betina</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= htmlspecialchars($kambing['tanggal_lahir']); ?>" required>
                                </div>

                                <hr class="my-3">

                                <div class="col-md-6 mb-3">
                                    <label for="id_induk" class="form-label">Induk (Betina)</label>
                                    <select class="form-select" id="id_induk" name="id_induk">
                                        <option value="">Tidak Diketahui</option>
                                        <?php while($i = $induk_list->fetch_assoc()): ?>
                                            <option value="<?= $i['id']; ?>" <?= ($kambing['id_induk'] == $i['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($i['nama'] . ' - ' . $i['kode_tag']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_pejantan" class="form-label">Pejantan</label>
                                    <select class="form-select" id="id_pejantan" name="id_pejantan">
                                        <option value="">Tidak Diketahui</option>
                                        <?php while($j = $pejantan_list->fetch_assoc()): ?>
                                            <option value="<?= $j['id']; ?>" <?= ($kambing['id_pejantan'] == $j['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($j['nama'] . ' - ' . $j['kode_tag']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_kandang" class="form-label">Kandang</label>
                                    <select class="form-select" id="id_kandang" name="id_kandang" required>
                                        <option value="">Pilih Kandang...</option>
                                        <?php while($k = $kandang_list->fetch_assoc()): ?>
                                            <option value="<?= $k['id']; ?>" <?= ($kambing['id_kandang'] == $k['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($k['nama_kandang']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="sehat" <?= ($kambing['status'] == 'sehat') ? 'selected' : ''; ?>>Sehat</option>
                                        <option value="sakit" <?= ($kambing['status'] == 'sakit') ? 'selected' : ''; ?>>Sakit</option>
                                        <option value="bunting" <?= ($kambing['status'] == 'bunting') ? 'selected' : ''; ?>>Bunting</option>
                                        <option value="dijual" <?= ($kambing['status'] == 'dijual') ? 'selected' : ''; ?>>Dijual</option>
                                        <option value="mati" <?= ($kambing['status'] == 'mati') ? 'selected' : ''; ?>>Mati</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="asal" class="form-label">Asal</label>
                                    <select class="form-select" id="asal" name="asal" required>
                                        <option value="pembelian" <?= ($kambing['asal'] == 'pembelian') ? 'selected' : ''; ?>>Pembelian</option>
                                        <option value="kelahiran" <?= ($kambing['asal'] == 'kelahiran') ? 'selected' : ''; ?>>Kelahiran</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="harga_beli" class="form-label">Harga Beli (Rp)</label>
                                    <input type="number" class="form-control" id="harga_beli" name="harga_beli" value="<?= htmlspecialchars($kambing['harga_beli']); ?>">
                                </div>

                                <!-- Tambahan Upload Foto -->
                                <div class="col-md-6 mb-3">
                                    <label for="foto" class="form-label">Foto Kambing</label>
                                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" <?= $is_edit ? '' : 'required'; ?>>
                                    <?php if ($is_edit && !empty($kambing['foto'])): ?>
                                        <div class="mt-2">
                                            <p class="mb-1">Foto Saat Ini:</p>
                                            <img src="../uploads/kambing/<?= htmlspecialchars($kambing['foto']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn-custom">
                                    <i class="bi bi-save me-2"></i>Simpan Data
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
