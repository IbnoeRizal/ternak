<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';

$id_pengguna = $_SESSION['user_id'];
$peran_pengguna = $_SESSION['peran'];

// Ambil semua transaksi
$where_clauses = [];
$params = [];
$types = '';

$sql = "SELECT k.*, kat.nama AS nama_kategori 
        FROM keuangan k 
        LEFT JOIN kategori_keuangan kat ON k.id_kategori = kat.id";

$sql .= " ORDER BY k.tanggal_transaksi DESC, k.id DESC";
$result = $koneksi->query($sql);

// Hitung total pemasukan, pengeluaran, dan utang pribadi user ini
$total_pemasukan = 0;
$total_pengeluaran = 0;
$total_utang_pribadi = 0;

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    if ($row['jenis'] == 'pemasukan') {
        $total_pemasukan += $row['jumlah'];
    } else {
        $total_pengeluaran += $row['jumlah'];
    }
    if (
        $row['id_pengguna'] == $id_pengguna &&
        $row['sumber_dana'] == 'pribadi' &&
        $row['status_pembayaran'] == 'belum_diganti'
    ) {
        $total_utang_pribadi += $row['jumlah'];
    }
}
$saldo_akhir = $total_pemasukan - $total_pengeluaran;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Keuangan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        /* Mobile responsive cards */
        .transaction-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }
        
        /* Modal styling for image preview */
        .modal-img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .transaction-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .transaction-card .card-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .transaction-card .card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .transaction-card .card-row .label {
            font-weight: 500;
            color: #666;
            min-width: 80px;
        }
        
        .transaction-card .card-actions {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 8px;
        }
        
        /* Show cards on mobile, hide table */
        @media (max-width: 768px) {
            .desktop-table {
                display: none !important;
            }
            .transaction-card {
                display: block !important;
            }
        }
        
        /* Hide cards on desktop, show table */
        @media (min-width: 769px) {
            .transaction-card {
                display: none !important;
            }
            .desktop-table {
                display: block !important;
            }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content flex-grow-1">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold">Buku Kas Keuangan</h1>
                <a href="keuangan_form.php" class="btn-custom">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Transaksi
                </a>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card-custom p-3">
                        <h6 class="text-muted mb-1">Total Pemasukan</h6>
                        <h4 class="fw-bold text-success">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3">
                        <h6 class="text-muted mb-1">Total Pengeluaran</h6>
                        <h4 class="fw-bold text-danger">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3">
                        <h6 class="text-muted mb-1">Saldo Akhir</h6>
                        <h4 class="fw-bold text-primary">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Utang -->
            <div class="alert alert-warning">
                <strong>Total Utang Pribadi Anda:</strong> Rp <?= number_format($total_utang_pribadi, 0, ',', '.') ?>
            </div>

            <!-- Desktop Table View -->
            <div class="card-custom desktop-table">
                <div class="card-body-custom">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Sumber</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($data) > 0): ?>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                                            <td>
                                                <?php if ($row['jenis'] == 'pemasukan'): ?>
                                                    <span class="fw-bold text-success">+ Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold text-danger">- Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $row['sumber_dana'] == 'pribadi' ? 'bg-warning' : 'bg-info' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $row['sumber_dana'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $row['status_pembayaran'] == 'lunas' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $row['status_pembayaran'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($peran_pengguna == 'admin'): ?>
                                                    <!-- Admin dapat edit dan hapus semua transaksi -->
                                                    <a href="keuangan_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary btn-edit me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                    <a href="../actions/keuangan_action.php?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete me-1" onclick="return confirm('Hapus transaksi ini?')" title="Hapus"><i class="bi bi-trash"></i></a>
                                                    <?php if ($row['status_pembayaran'] == 'belum_diganti' && $row['sumber_dana'] == 'pribadi'): ?>
                                                        <form method="post" action="../actions/keuangan_action.php" style="display:inline;">
                                                            <input type="hidden" name="aksi" value="lunas">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success me-1" onclick="return confirm('Tandai transaksi ini sudah dilunasi?')" title="Tandai Lunas"><i class="bi bi-check2-circle"></i></button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php elseif ($peran_pengguna == 'peternak' && $row['id_pengguna'] == $id_pengguna): ?>
                                                    <!-- Peternak hanya dapat edit dan hapus transaksi sendiri -->
                                                    <a href="keuangan_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary btn-edit me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                                    <a href="../actions/keuangan_action.php?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete me-1" onclick="return confirm('Hapus transaksi ini?')" title="Hapus"><i class="bi bi-trash"></i></a>
                                                    <?php if ($row['status_pembayaran'] == 'belum_diganti' && $row['sumber_dana'] == 'pribadi'): ?>
                                                        <form method="post" action="../actions/keuangan_action.php" style="display:inline;">
                                                            <input type="hidden" name="aksi" value="lunas">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success me-1" onclick="return confirm('Tandai transaksi ini sudah dilunasi?')" title="Tandai Lunas"><i class="bi bi-check2-circle"></i></button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <!-- Tombol lihat bukti untuk semua peran jika ada bukti -->
                                                <?php if (!empty($row['bukti'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#buktiModal<?= $row['id'] ?>" title="Lihat Bukti">
                                                        <i class="bi bi-image"></i>
                                                    </button>
                                                    
                                                    <!-- Modal untuk menampilkan bukti -->
                                                    <div class="modal fade" id="buktiModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="buktiModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="buktiModalLabel<?= $row['id'] ?>">
                                                                        Bukti Transaksi - <?= htmlspecialchars($row['deskripsi']) ?>
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="uploads/bukti/<?= htmlspecialchars($row['bukti']) ?>" 
                                                                         alt="Bukti Transaksi" 
                                                                         class="modal-img"
                                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPgogIDx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2Yjc0ODMiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIwLjNlbSI+R2FtYmFyIFRpZGFrIERpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.alt='Gambar tidak ditemukan';">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($peran_pengguna == 'penitip' || ($peran_pengguna == 'peternak' && $row['id_pengguna'] != $id_pengguna)): ?>
                                                    <?php if (empty($row['bukti'])): ?>
                                                        <em class="text-muted">-</em>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">Belum ada data transaksi.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-cards">
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                        <div class="transaction-card">
                            <div class="card-header">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($row['deskripsi']) ?></h6>
                                    <small class="text-muted"><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></small>
                                </div>
                                <div class="text-end">
                                    <?php if ($row['jenis'] == 'pemasukan'): ?>
                                        <span class="fw-bold text-success">+ Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold text-danger">- Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="card-row">
                                    <span class="label">Kategori:</span>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                                </div>
                                
                                <div class="card-row">
                                    <span class="label">Sumber:</span>
                                    <span class="badge <?= $row['sumber_dana'] == 'pribadi' ? 'bg-warning' : 'bg-info' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $row['sumber_dana'])) ?>
                                    </span>
                                </div>
                                
                                <div class="card-row">
                                    <span class="label">Status:</span>
                                    <span class="badge <?= $row['status_pembayaran'] == 'lunas' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $row['status_pembayaran'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php 
                            $showActions = false;
                            if ($peran_pengguna == 'admin' || ($peran_pengguna == 'peternak' && $row['id_pengguna'] == $id_pengguna)) {
                                $showActions = true;
                            }
                            ?>
                            
                            <?php if ($showActions || !empty($row['bukti'])): ?>
                                <div class="card-actions">
                                    <?php if ($showActions): ?>
                                        <a href="keuangan_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary btn-edit">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="../actions/keuangan_action.php?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" onclick="return confirm('Hapus transaksi ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                        <?php if ($row['status_pembayaran'] == 'belum_diganti' && $row['sumber_dana'] == 'pribadi'): ?>
                                            <form method="post" action="../actions/keuangan_action.php" style="display:inline;">
                                                <input type="hidden" name="aksi" value="lunas">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Tandai transaksi ini sudah dilunasi?')">
                                                    <i class="bi bi-check2-circle"></i> Lunas
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol lihat bukti untuk semua peran jika ada bukti -->
                                    <?php if (!empty($row['bukti'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#buktiModalMobile<?= $row['id'] ?>">
                                            <i class="bi bi-image"></i> Lihat Bukti
                                        </button>
                                        
                                        <!-- Modal untuk menampilkan bukti (mobile) -->
                                        <div class="modal fade" id="buktiModalMobile<?= $row['id'] ?>" tabindex="-1" aria-labelledby="buktiModalMobileLabel<?= $row['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="buktiModalMobileLabel<?= $row['id'] ?>">
                                                            Bukti Transaksi - <?= htmlspecialchars($row['deskripsi']) ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="../uploads/bukti/<?= htmlspecialchars($row['bukti']) ?>" 
                                                             alt="Bukti Transaksi" 
                                                             class="modal-img"
                                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y4ZjlmYSIvPgogIDx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2Yjc0ODMiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIwLjNlbSI+R2FtYmFyIFRpZGFrIERpdGVtdWthbjwvdGV4dD4KPC9zdmc+'; this.alt='Gambar tidak ditemukan';">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="transaction-card">
                        <div class="text-center text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">Belum ada data transaksi.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>