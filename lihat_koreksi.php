<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super admin'])) {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    // ... (Logika PHP untuk 'setujui' dan 'tolak' tetap sama) ...
    header('Location: lihat_koreksi.php');
    exit();
}

$query = "SELECT k.id, u.nama as nama_guru, a.tanggal, a.keterangan as ket_awal, k.alasan_koreksi, k.status 
          FROM koreksi_absensi k
          JOIN absensi a ON k.absensi_id = a.id
          JOIN users u ON a.user_id = u.id
          ORDER BY k.tanggal_pengajuan DESC";
$result_koreksi = $conn->query($query);
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
                <li><a href="laporan_absensi.php">Laporan Absensi</a></li>
                <li><a href="lihat_koreksi.php" class="active">Pengajuan Koreksi</a></li>
            <?php else: ?>
                <li><a href="dashboard_admin.php">Dashboard Analitik</a></li>
                <li><a href="laporan_absensi.php">Lihat Laporan Absensi</a></li>
                <li><a href="lihat_koreksi.php" class="active">Lihat Pengajuan Koreksi</a></li>
            <?php endif; ?>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">LOGOUT</a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <header class="main-header">
            <div class="header-title">Lihat Pengajuan Koreksi</div>
            <div class="header-user">
                <button id="theme-toggle-btn" class="theme-toggle">🌙</button>
                <div class="profile-icon"><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)); ?></div>
                <span><?= htmlspecialchars($_SESSION['user_nama']); ?></span>
            </div>
        </header>

        <main class="main-content-area">
            <h5 class="content-title">LIHAT PENGAJUAN KOREKSI</h5>

            <div class="table-responsive dashboard-card" style="text-align: left;">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Guru</th>
                            <th>Tanggal</th>
                            <th>Ket. Awal</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_koreksi->num_rows > 0): ?>
                            <?php while ($row = $result_koreksi->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?= htmlspecialchars($row['ket_awal']); ?></td>
                                    <td><?= htmlspecialchars($row['alasan_koreksi']); ?></td>
                                    <td><span class="badge <?php if ($row['status'] == 'Disetujui') echo 'bg-success';
                                                            elseif ($row['status'] == 'Ditolak') echo 'bg-danger';
                                                            else echo 'bg-warning'; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                                    <td>
                                        <?php if ($row['status'] == 'Diajukan'): ?>
                                            <a href="lihat_koreksi.php?aksi=setujui&id=<?= $row['id']; ?>" class="btn btn-sm btn-success">✓</a>
                                            <a href="lihat_koreksi.php?aksi=tolak&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger">✗</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada pengajuan koreksi.</td>
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