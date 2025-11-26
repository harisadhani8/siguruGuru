<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

$total_guru_result = $conn->query("SELECT COUNT(id) as total FROM users WHERE role = 'guru'");
$total_guru = $total_guru_result->fetch_assoc()['total'];
$hadir_hari_ini_result = $conn->query("SELECT COUNT(id) as total FROM absensi WHERE keterangan = 'Hadir' AND tanggal = CURDATE()");
$hadir_hari_ini = $hadir_hari_ini_result->fetch_assoc()['total'];
$tidak_hadir_hari_ini_result = $conn->query("SELECT COUNT(id) as total FROM absensi WHERE keterangan != 'Hadir' AND tanggal = CURDATE()");
$tidak_hadir_hari_ini = $tidak_hadir_hari_ini_result->fetch_assoc()['total'];
$koreksi_pending_result = $conn->query("SELECT COUNT(id) as total FROM koreksi_absensi WHERE status = 'Diajukan'");
$koreksi_pending = $koreksi_pending_result->fetch_assoc()['total'];
?>
<?php require_once 'includes/header.php'; ?>

<div class="dashboard-layout">
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="assets/logo.png" alt="SiGuru Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard_admin.php" class="<?= ($current_page == 'dashboard_admin.php') ? 'active' : ''; ?>">Dashboard Analitik</a></li>
            <li><a href="laporan_absensi.php" class="<?= ($current_page == 'laporan_absensi.php') ? 'active' : ''; ?>">Lihat Laporan Absensi</a></li>
            <li><a href="lihat_koreksi.php" class="<?= ($current_page == 'lihat_koreksi.php') ? 'active' : ''; ?>">Lihat Pengajuan Koreksi</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">LOGOUT</a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <header class="main-header">
            <div class="header-title">Dashboard Admin</div>
            <div class="header-user">
                <button id="theme-toggle-btn" class="theme-toggle">🌙</button>
                <div class="profile-icon"><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)); ?></div>
                <span><?= htmlspecialchars($_SESSION['user_nama']); ?></span>
            </div>
        </header>

        <main class="main-content-area">
            <h5 class="content-title">DASHBOARD ANALITIK</h5>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-title">Guru Hadir Hari Ini</div>
                    <div class="stat-value"><?= $hadir_hari_ini; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Guru Tidak Hadir</div>
                    <div class="stat-value"><?= $tidak_hadir_hari_ini; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Total Guru</div>
                    <div class="stat-value"><?= $total_guru; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Koreksi Pending</div>
                    <div class="stat-value"><?= $koreksi_pending; ?></div>
                </div>
            </div>

            <footer>@2025 Mas Haris</footer>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>