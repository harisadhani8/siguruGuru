<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super admin'])) {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

$query = "SELECT u.nama, a.tanggal, a.keterangan, a.catatan 
          FROM absensi a
          JOIN users u ON a.user_id = u.id
          ORDER BY a.tanggal DESC, u.nama ASC";
$laporan = $conn->query($query);
?>
<?php require_once 'includes/header.php'; ?>

<div class="dashboard-layout">
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="assets/logo.png" alt="SiGuru Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <?php if ($_SESSION['user_role'] == 'super admin'): ?>
                <li><a href="dashboard_superadmin.php">Dashboard Analitik</a></li>
                <li><a href="manage_users.php">Kelola Pengguna</a></li>
                <li><a href="laporan_absensi.php" class="active">Laporan Absensi</a></li>
                <li><a href="lihat_koreksi.php">Pengajuan Koreksi</a></li>
            <?php else: ?>
                <li><a href="dashboard_admin.php">Dashboard Analitik</a></li>
                <li><a href="laporan_absensi.php" class="active">Lihat Laporan Absensi</a></li>
                <li><a href="lihat_koreksi.php">Lihat Pengajuan Koreksi</a></li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">LOGOUT</a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <header class="main-header">
            <div class="header-title">Laporan Absensi</div>
            <div class="header-user">
                <button id="theme-toggle-btn" class="theme-toggle">🌙</button>
                <div class="profile-icon"><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)); ?></div>
                <span><?= htmlspecialchars($_SESSION['user_nama']); ?></span>
            </div>
        </header>

        <main class="main-content-area">
            <h5 class="content-title">LAPORAN ABSENSI</h5>

            <div class="table-responsive dashboard-card" style="text-align: left;">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Guru</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Catatan Tambahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($laporan->num_rows > 0): ?>
                            <?php while ($row = $laporan->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']); ?></td>
                                    <td><?= htmlspecialchars($row['catatan']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data absensi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <footer>@2025 Mas Haris</footer>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>