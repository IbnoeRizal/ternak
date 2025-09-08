<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/koneksi.php';
?>

<style>
/* Mobile Optimizations */
.main-content {
    margin-left: 0;
    padding: 15px;
    transition: margin-left 0.3s ease;
}

@media (min-width: 1025px) {
    .main-content {
        margin-left: 280px;
        padding: 30px;
    }
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.page-title {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.add-btn {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    color: white;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.add-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    color: white;
}

/* Search and Filter */
.search-filter-container {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-input {
    border: 2px solid #e9ecef;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.filter-select {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 10px 15px;
    font-size: 14px;
    background: white;
    transition: all 0.3s ease;
}

.filter-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

/* Desktop Table */
.desktop-table {
    display: block;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.desktop-table table {
    margin: 0;
    border: none;
}

.desktop-table th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #495057;
    font-weight: 600;
    padding: 15px;
    border: none;
    font-size: 14px;
}

.desktop-table td {
    padding: 15px;
    border: none;
    border-bottom: 1px solid #f8f9fa;
    vertical-align: middle;
}

.desktop-table tr:hover {
    background: #f8f9fa;
}

/* Mobile Cards */
.mobile-cards {
    display: none;
}

.kambing-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.kambing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.card-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.card-photo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 3px solid #e9ecef;
}

.card-photo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(45deg, #e9ecef, #f8f9fa);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    margin-right: 15px;
    font-size: 24px;
}

.card-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.card-subtitle {
    font-size: 14px;
    color: #6c757d;
    margin: 0;
}

.card-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.card-item {
    display: flex;
    flex-direction: column;
}

.card-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.card-value {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.card-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 15px;
    border-top: 1px solid #f8f9fa;
}

.btn-card {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-edit {
    background: linear-gradient(45deg, #ffc107, #ff8c00);
    color: white;
}

.btn-edit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    color: white;
}

.btn-delete {
    background: linear-gradient(45deg, #dc3545, #c82333);
    color: white;
}

.btn-delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    color: white;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-sehat {
    background: #d4edda;
    color: #155724;
}

.status-sakit {
    background: #f8d7da;
    color: #721c24;
}

.status-hamil {
    background: #fff3cd;
    color: #856404;
}

.status-menyusui {
    background: #cce5ff;
    color: #004085;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #dee2e6;
}

.empty-state h4 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #495057;
}

.empty-state p {
    font-size: 16px;
    margin-bottom: 30px;
}

/* Loading Animation */
.loading {
    text-align: center;
    padding: 40px;
}

.loading .spinner-border {
    width: 3rem;
    height: 3rem;
    color: #667eea;
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-content {
        padding: 10px;
    }
    
    .page-header {
        padding: 15px;
        flex-direction: column;
        gap: 15px;
    }
    
    .page-title {
        font-size: 20px;
        justify-content: center;
    }
    
    .add-btn {
        padding: 12px 20px;
        font-size: 14px;
        align-self: stretch;
        justify-content: center;
    }
    
    .search-filter-container {
        padding: 15px;
    }
    
    .desktop-table {
        display: none;
    }
    
    .mobile-cards {
        display: block;
    }
    
    .card-body {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .card-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-card {
        text-align: center;
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 5px;
    }
    
    .page-header {
        padding: 12px;
        border-radius: 10px;
    }
    
    .kambing-card {
        padding: 15px;
        border-radius: 10px;
    }
    
    .card-header {
        flex-direction: column;
        text-align: center;
    }
    
    .card-photo, .card-photo-placeholder {
        margin: 0 auto 10px;
    }
}
</style>

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1 class="page-title">
                <i class="bi bi-house-heart"></i>
                Data Kambing
            </h1>
            <?php if ($_SESSION['peran'] === 'admin' || $_SESSION['peran'] === 'peternak'): ?>
                <a href="kambing-tambah.php" class="add-btn">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Kambing
                </a>
            <?php endif; ?>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter-container">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="position-relative">
                        <input type="text" class="form-control search-input" id="searchKambing" placeholder="🔍 Cari nama kambing...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select filter-select" id="filterJenis">
                        <option value="">Semua Jenis</option>
                        <option value="Etawa">Etawa</option>
                        <option value="Boer">Boer</option>
                        <option value="Kacang">Kacang</option>
                        <option value="Saanen">Saanen</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select filter-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="sehat">Sehat</option>
                        <option value="sakit">Sakit</option>
                        <option value="hamil">Hamil</option>
                        <option value="menyusui">Menyusui</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="loading" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Memuat data kambing...</p>
        </div>

        <!-- Desktop Table -->
        <div class="desktop-table" id="desktopTable">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Usia</th>
                        <th>Status</th>
                        <th>Pemilik</th>
                        <?php if ($_SESSION['peran'] !== 'penitip'): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="mobile-cards" id="mobileCards">
            <!-- Cards akan diisi oleh JavaScript -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <i class="bi bi-search"></i>
            <h4>Tidak ada data kambing</h4>
            <p>Belum ada data kambing yang ditemukan sesuai kriteria pencarian.</p>
            <?php if ($_SESSION['peran'] === 'admin' || $_SESSION['peran'] === 'peternak'): ?>
                <a href="kambing-tambah.php" class="add-btn">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Kambing Pertama
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allKambing = [];
    let filteredKambing = [];
    
    // Fetch data kambing
    async function fetchKambing() {
        try {
            document.getElementById('loadingState').style.display = 'block';
            
            const response = await fetch('api/kambing.php');
            const data = await response.json();
            
            if (data.success) {
                allKambing = data.data;
                filteredKambing = [...allKambing];
                renderKambing();
            } else {
                console.error('Error fetching data:', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            document.getElementById('loadingState').style.display = 'none';
        }
    }
    
    // Render kambing data
    function renderKambing() {
        const tableBody = document.getElementById('tableBody');
        const mobileCards = document.getElementById('mobileCards');
        const emptyState = document.getElementById('emptyState');
        
        if (filteredKambing.length === 0) {
            tableBody.innerHTML = '';
            mobileCards.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        
        emptyState.style.display = 'none';
        
        // Render desktop table
        tableBody.innerHTML = filteredKambing.map(kambing => `
            <tr>
                <td>
                    ${kambing.foto ? 
                        `<img src="../uploads/kambing/${kambing.foto}" alt="foto" class="rounded-circle" width="50" height="50" style="object-fit: cover;">` : 
                        `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; color: #6c757d;">
                            <i class="bi bi-image" style="font-size: 20px;"></i>
                        </div>`
                    }
                </td>
                <td><strong>${escapeHtml(kambing.nama)}</strong></td>
                <td>${escapeHtml(kambing.jenis)}</td>
                <td>${kambing.usia} bulan</td>
                <td><span class="status-badge status-${kambing.status.toLowerCase()}">${capitalizeFirst(kambing.status)}</span></td>
                <td>${escapeHtml(kambing.pemilik)}</td>
                <?php if ($_SESSION['peran'] !== 'penitip'): ?>
                <td>
                    <div class="d-flex gap-2">
                        <a href="kambing-edit.php?id=${kambing.id}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button onclick="deleteKambing(${kambing.id}, '${escapeHtml(kambing.nama)}')" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        `).join('');
        
        // Render mobile cards
        mobileCards.innerHTML = filteredKambing.map(kambing => `
            <div class="kambing-card">
                <div class="card-header">
                    ${kambing.foto ? 
                        `<img src="../uploads/kambing/${kambing.foto}" alt="foto" class="card-photo">` : 
                        `<div class="card-photo-placeholder">
                            <i class="bi bi-image"></i>
                        </div>`
                    }
                    <div>
                        <h5 class="card-title">${escapeHtml(kambing.nama)}</h5>
                        <p class="card-subtitle">${escapeHtml(kambing.jenis)}</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-item">
                        <div class="card-label">Usia</div>
                        <div class="card-value">${kambing.usia} bulan</div>
                    </div>
                    <div class="card-item">
                        <div class="card-label">Status</div>
                        <div class="card-value">
                            <span class="status-badge status-${kambing.status.toLowerCase()}">${capitalizeFirst(kambing.status)}</span>
                        </div>
                    </div>
                    <div class="card-item">
                        <div class="card-label">Pemilik</div>
                        <div class="card-value">${escapeHtml(kambing.pemilik)}</div>
                    </div>
                </div>
                <?php if ($_SESSION['peran'] !== 'penitip'): ?>
                <div class="card-actions">
                    <a href="kambing-edit.php?id=${kambing.id}" class="btn-card btn-edit">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button onclick="deleteKambing(${kambing.id}, '${escapeHtml(kambing.nama)}')" class="btn-card btn-delete">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
                <?php endif; ?>
            </div>
        `).join('');
    }
    
    // Filter functions
    function filterKambing() {
        const searchTerm = document.getElementById('searchKambing').value.toLowerCase();
        const jenisFilter = document.getElementById('filterJenis').value;
        const statusFilter = document.getElementById('filterStatus').value;
        
        filteredKambing = allKambing.filter(kambing => {
            const matchesSearch = kambing.nama.toLowerCase().includes(searchTerm) || 
                                kambing.jenis.toLowerCase().includes(searchTerm) ||
                                kambing.pemilik.toLowerCase().includes(searchTerm);
            const matchesJenis = !jenisFilter || kambing.jenis === jenisFilter;
            const matchesStatus = !statusFilter || kambing.status === statusFilter;
            
            return matchesSearch && matchesJenis && matchesStatus;
        });
        
        renderKambing();
    }
    
    // Event listeners
    document.getElementById('searchKambing').addEventListener('input', filterKambing);
    document.getElementById('filterJenis').addEventListener('change', filterKambing);
    document.getElementById('filterStatus').addEventListener('change', filterKambing);
    
    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    // Delete function
    window.deleteKambing = function(id, nama) {
        if (confirm(`Apakah Anda yakin ingin menghapus kambing "${nama}"?`)) {
            fetch('api/kambing-delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchKambing(); // Refresh data
                    alert('Kambing berhasil dihapus');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data');
            });
        }
    };
    
    // Initialize
    fetchKambing();
});
</script>
