<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: kambing_list.php');
    exit;
}
$id_kambing = $_GET['id'];

// Query utama untuk detail kambing
$stmt = $koneksi->prepare("SELECT k.*, r.nama_ras, kd.nama_kandang, p.nama AS nama_pemilik,
                          induk.nama AS nama_induk, pejantan.nama AS nama_pejantan
                          FROM kambing k
                          LEFT JOIN ras r ON k.id_ras = r.id
                          LEFT JOIN kandang kd ON k.id_kandang = kd.id
                          LEFT JOIN pengguna p ON k.id_pemilik = p.id
                          LEFT JOIN kambing induk ON k.id_induk = induk.id
                          LEFT JOIN kambing pejantan ON k.id_pejantan = pejantan.id
                          WHERE k.id = ?");
$stmt->bind_param("i", $id_kambing);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Data kambing tidak ditemukan.";
    header('Location: kambing_list.php');
    exit;
}
$kambing = $result->fetch_assoc();

// Hitung umur
$tgl_lahir = new DateTime($kambing['tanggal_lahir']);
$sekarang = new DateTime();
$umur = $sekarang->diff($tgl_lahir);

// Query untuk riwayat-riwayat
$riwayat_kesehatan = $koneksi->query("SELECT * FROM riwayat_kesehatan WHERE id_kambing = $id_kambing ORDER BY tanggal_periksa DESC");
$riwayat_pakan = $koneksi->query("SELECT * FROM riwayat_pakan WHERE id_kambing = $id_kambing ORDER BY waktu_pakan DESC");
$riwayat_berat = $koneksi->query("SELECT * FROM riwayat_berat WHERE id_kambing = $id_kambing ORDER BY tanggal_timbang DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kambing: <?= htmlspecialchars($kambing['nama']); ?></title>
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
                    <h1 class="h3 fw-bold">Detail Kambing</h1>
                    <div>
                        <a href="kambing_form.php?id=<?= $kambing['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil me-2"></i>Edit</a>
                        <a href="kambing_list.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card-custom mb-4">
                            <div class="card-body-custom text-center">
                                <img src="../uploads/kambing/<?= htmlspecialchars($kambing['foto']); ?>" 
                                    class="img-fluid rounded-circle mb-3 foto-thumbnail" 
                                    alt="Foto Kambing" 
                                    style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalFoto" 
                                    data-src="../uploads/kambing/<?= htmlspecialchars($kambing['foto']); ?>">
                                <h4 class="fw-bold"><?= htmlspecialchars($kambing['nama']); ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($kambing['kode_tag']); ?></p>
                                <span class="badge bg-primary fs-6"><?= ucfirst($kambing['status']); ?></span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between"><span>Jenis Kelamin:</span> <strong><?= ucfirst($kambing['jenis_kelamin']); ?></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Ras:</span> <strong><?= htmlspecialchars($kambing['nama_ras']); ?></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Umur:</span> <strong><?= $umur->y . ' th, ' . $umur->m . ' bln'; ?></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Kandang:</span> <strong><?= htmlspecialchars($kambing['nama_kandang']); ?></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><span>Pemilik:</span> <strong><?= htmlspecialchars($kambing['nama_pemilik']); ?></strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card-custom">
                            <div class="card-header-custom">
                                <ul class="nav nav-tabs card-header-tabs" id="detailTab" role="tablist">
                                    <li class="nav-item" role="presentation"><button class="nav-link active" id="profil-tab" data-bs-toggle="tab" data-bs-target="#profil" type="button" role="tab">Profil Lengkap</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="kesehatan-tab" data-bs-toggle="tab" data-bs-target="#kesehatan" type="button" role="tab">Kesehatan</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="pakan-tab" data-bs-toggle="tab" data-bs-target="#pakan" type="button" role="tab">Pakan</button></li>
                                    <li class="nav-item" role="presentation"><button class="nav-link" id="berat-tab" data-bs-toggle="tab" data-bs-target="#berat" type="button" role="tab">Berat</button></li>
                                </ul>
                            </div>
                            <div class="card-body-custom">
                                <div class="tab-content" id="detailTabContent">
                                    <div class="tab-pane fade show active" id="profil" role="tabpanel">
                                        <h5 class="fw-bold mb-3">Silsilah & Asal</h5>
                                        <dl class="row">
                                            <dt class="col-sm-3">Induk</dt>
                                            <dd class="col-sm-9"><?= htmlspecialchars($kambing['nama_induk'] ?? 'Tidak Diketahui'); ?></dd>
                                            <dt class="col-sm-3">Pejantan</dt>
                                            <dd class="col-sm-9"><?= htmlspecialchars($kambing['nama_pejantan'] ?? 'Tidak Diketahui'); ?></dd>
                                            <dt class="col-sm-3">Asal</dt>
                                            <dd class="col-sm-9"><?= ucfirst($kambing['asal']); ?></dd>
                                            <?php if ($kambing['asal'] == 'pembelian'): ?>
                                                <dt class="col-sm-3">Harga Beli</dt>
                                                <dd class="col-sm-9">Rp <?= number_format($kambing['harga_beli'], 0, ',', '.'); ?></dd>
                                            <?php endif; ?>
                                        </dl>
                                    </div>
                                    <div class="tab-pane fade" id="kesehatan" role="tabpanel">
                                        <h5 class="fw-bold mb-3">Riwayat Kesehatan</h5>
                                        <p>Tabel riwayat kesehatan akan ditampilkan di sini.</p>
                                    </div>
                                    <div class="tab-pane fade" id="pakan" role="tabpanel">
                                        <h5 class="fw-bold mb-3">Riwayat Pakan</h5>
                                        <p>Tabel riwayat pakan akan ditampilkan di sini.</p>
                                    </div>
                                    <div class="tab-pane fade" id="berat" role="tabpanel">
                                        <h5 class="fw-bold mb-3">Riwayat Berat Badan</h5>
                                        <p>Tabel dan grafik riwayat berat akan ditampilkan di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
        <div class="modal fade" id="modalFoto" tabindex="-1" aria-labelledby="modalFotoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-light">
      <div class="modal-body p-0">
        <img src="" id="previewFoto" class="w-100 rounded" style="max-height: 90vh; object-fit: contain;" alt="Foto Kambing Besar">
      </div>
    </div>
  </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const modalFoto = document.getElementById('modalFoto');
  const previewFoto = document.getElementById('previewFoto');
  const thumb = document.querySelector('.foto-thumbnail');

  thumb.addEventListener('click', function () {
      const imgSrc = this.getAttribute('data-src');
      previewFoto.src = imgSrc;
  });
</script>
</body>
</html>