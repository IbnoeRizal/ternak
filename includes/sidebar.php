<?php
// Pastikan sesi dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil peran pengguna dari sesi, default ke 'tamu' jika tidak ada
$peran = $_SESSION['peran'] ?? 'tamu';
$nama_pengguna = $_SESSION['nama'] ?? 'Pengguna';

// Inisialisasi menu dan ikon
$menu = [];
$icons = [];

// Definisikan menu berdasarkan peran pengguna
switch ($peran) {
    case 'admin':
        $menu = [
            'Dashboard'         => '../views/dashboard.php',
            'Manajemen Kambing' => '../views/kambing_list.php',
            'Manajemen Pengguna'=> '../views/pengguna_list.php',
            'Data Master'       => [
                'Kandang'   => '../views/kandang_list.php',
                'Ras'       => '../views/ras_list.php',
                'Stok Pakan & Obat' => '../views/stok_list.php'
            ],
            'Keuangan'          => '../views/keuangan_list.php',
            'Laporan'           => '../views/laporan.php',
        ];
        $icons = [
            'Dashboard'         => 'bi-speedometer2',
            'Manajemen Kambing' => 'bi-ui-checks-grid',
            'Manajemen Pengguna'=> 'bi-people',
            'Data Master'       => 'bi-archive',
            'Keuangan'          => 'bi-cash-coin',
            'Laporan'           => 'bi-file-earmark-bar-graph',
        ];
        break;
    case 'peternak':
        $menu = [
            'Dashboard'         => '../views/dashboard.php',
            'Kambing Saya'      => '../views/kambing_list.php',
            'Reproduksi'        => '../views/reproduksi_list.php',
            'Riwayat Kesehatan' => '../views/kesehatan_list.php',
            'Riwayat Pakan'     => '../views/pakan_list.php',
            'Keuangan'  => '../views/keuangan_list.php',
        ];
        $icons = [
            'Dashboard'         => 'bi-speedometer2',
            'Kambing Saya'      => 'bi-house-heart',
            'Reproduksi'        => 'bi-heart-pulse',
            'Riwayat Kesehatan' => 'bi-shield-plus',
            'Riwayat Pakan'     => 'bi-basket3',
            'Keuangan'  => 'bi-wallet2',
        ];
        break;
    case 'penitip':
        $menu = [
            'Dashboard'         => '../views/dashboard.php',
            'Kambing Titipan'   => '../views/kambing_list.php',
            'Riwayat Perawatan' => '../views/perawatan_list.php',
            'Tagihan & Biaya'   => '../views/tagihan_list.php',
        ];
        $icons = [
            'Dashboard'         => 'bi-speedometer2',
            'Kambing Titipan'   => 'bi-box-seam',
            'Riwayat Perawatan' => 'bi-clock-history',
            'Tagihan & Biaya'   => 'bi-receipt',
        ];
        break;
    default: // Untuk tamu atau jika sesi tidak ada
        $menu = [ 'Login' => '../index.php' ];
        $icons = [ 'Login' => 'bi-box-arrow-in-right' ];
        break;
}

// Mendapatkan nama file dari halaman yang sedang aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --sidebar-width: 280px;
        --sidebar-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --sidebar-text-color: rgba(255, 255, 255, 0.85);
        --sidebar-text-hover-color: white;
        --sidebar-item-hover-bg: rgba(255, 255, 255, 0.15);
        --sidebar-item-active-bg: rgba(255, 255, 255, 0.25);
    }

    /* -- Sidebar Layout -- */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        background: var(--sidebar-bg);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
        display: flex;
        flex-direction: column;
    }
    .main-content {
        transition: margin-left 0.3s ease-in-out;
        padding: 20px;
    }
    @media (min-width: 992px) {
        .main-content {
            margin-left: var(--sidebar-width);
        }
    }

    /* -- Sidebar Header -- */
    .sidebar-header {
        padding: 1.5rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .sidebar-brand {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        text-decoration: none;
    }
    .sidebar-brand i {
        margin-right: 10px;
        transition: transform 0.3s ease;
    }
    .sidebar-brand:hover i {
        transform: rotate(15deg);
    }

    /* -- Navigation Menu -- */
    .sidebar-nav {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    .nav-link {
        color: var(--sidebar-text-color);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        font-weight: 500;
        transition: all 0.2s ease;
        position: relative;
    }
    .nav-link i {
        margin-right: 1rem;
        font-size: 1.2rem;
        width: 20px;
    }
    .nav-link:hover {
        background: var(--sidebar-item-hover-bg);
        color: var(--sidebar-text-hover-color);
        transform: translateX(5px);
    }
    .nav-link.active {
        background: var(--sidebar-item-active-bg);
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .nav-link.active::before { /* Indikator aktif */
        content: '';
        position: absolute;
        left: 0;
        top: 15%;
        height: 70%;
        width: 4px;
        background: white;
        border-radius: 0 4px 4px 0;
    }
    
    /* -- Dropdown Menu di Sidebar -- */
    .sidebar-dropdown .dropdown-toggle::after {
        margin-left: auto;
        transition: transform 0.3s ease;
    }
    .sidebar-dropdown .dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(90deg);
    }
    .dropdown-menu {
        background-color: rgba(0,0,0,0.2);
        border: none;
        padding: 0;
        margin-top: 0.5rem;
    }
    .dropdown-item {
        color: var(--sidebar-text-color);
        padding: 0.6rem 1rem 0.6rem 3.2rem; /* Indentasi submenu */
        font-size: 0.9rem;
    }
    .dropdown-item:hover, .dropdown-item.active {
        background-color: var(--sidebar-item-hover-bg);
        color: var(--sidebar-text-hover-color);
    }


    /* -- User Profile Section -- */
    .sidebar-footer {
        padding: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    .user-profile a {
        color: white;
        text-decoration: none;
        padding: 0.75rem;
        border-radius: 10px;
        transition: background 0.2s ease;
    }
    .user-profile a:hover {
        background: var(--sidebar-item-hover-bg);
    }
    .user-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: #4A5568;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        margin-right: 10px;
    }
    .user-info h6 {
        margin: 0; font-size: 0.9rem; font-weight: 600;
    }
    .user-info span {
        font-size: 0.75rem; color: var(--sidebar-text-color);
    }
    
    /* -- Mobile & Responsive -- */
    .mobile-toggle-btn {
        display: none;
    }
    .sidebar-overlay {
        display: none;
    }
    
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(calc(-1 * var(--sidebar-width)));
            z-index: 1050;
        }
        .sidebar.show {
            transform: translateX(0);
        }
        .mobile-toggle-btn {
            display: block;
            position: fixed;
            top: 15px; left: 15px;
            z-index: 1051;
            background: var(--sidebar-bg);
            color: white;
            border: none;
            width: 45px; height: 45px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .sidebar-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
            pointer-events: auto;
        }
    }
</style>

<button class="mobile-toggle-btn" type="button" id="sidebarToggleBtn">
    <i class="bi bi-list fs-5"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../views/dashboard.php" class="sidebar-brand">
            <i class="bi bi-house-heart-fill"></i>
            <span>Kandang</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <?php foreach ($menu as $label => $linkOrSubmenu): ?>
                <?php if (is_array($linkOrSubmenu)): // Cek jika ini adalah dropdown/submenu ?>
                    <li class="nav-item sidebar-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="collapse" data-bs-target="#submenu-<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $label); ?>" aria-expanded="false">
                            <i class="bi <?php echo $icons[$label] ?? 'bi-archive'; ?>"></i>
                            <span><?php echo $label; ?></span>
                        </a>
                        <div class="collapse" id="submenu-<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $label); ?>">
                            <ul class="nav flex-column">
                                <?php foreach ($linkOrSubmenu as $subLabel => $subLink): ?>
                                    <li><a class="dropdown-item <?php echo ($current_page == basename($subLink)) ? 'active' : ''; ?>" href="<?php echo $subLink; ?>"><?php echo $subLabel; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php else: // Menu biasa ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == basename($linkOrSubmenu)) ? 'active' : ''; ?>" href="<?php echo $linkOrSubmenu; ?>">
                            <i class="bi <?php echo $icons[$label] ?? 'bi-circle'; ?>"></i>
                            <span><?php echo $label; ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <?php if ($peran !== 'tamu'): ?>
            <div class="user-profile dropdown">
                <a href="#" class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($nama_pengguna, 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <h6><?php echo htmlspecialchars($nama_pengguna); ?></h6>
                        <span><?php echo ucfirst($peran); ?></span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person me-2"></i> Profil</a></li>
                    <li><a class="dropdown-item" href="pengaturan.php"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }
});
</script>