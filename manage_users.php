<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super admin') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

if (isset($_GET['hapus'])) {
    // ... (Logika PHP untuk hapus user tetap sama) ...
    header('Location: manage_users.php');
    exit();
}
$users = $conn->query("SELECT * FROM users ORDER BY role, nama");
?>
<?php require_once 'includes/header.php'; ?>

<div class="dashboard-layout">
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="assets/logo.png" alt="SiGuru Logo" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard_superadmin.php">Dashboard Analitik</a></li>
            <li><a href="manage_users.php" class="active">Kelola Pengguna</a></li>
            <li><a href="laporan_absensi.php">Laporan Absensi</a></li>
            <li><a href="lihat_koreksi.php">Pengajuan Koreksi</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout-sidebar">LOGOUT</a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <header class="main-header">
            <div class="header-title">Kelola Akun Pengguna</div>
            <div class="header-user">
                <button id="theme-toggle-btn" class="theme-toggle">🌙</button>
                <div class="profile-icon"><?= strtoupper(substr($_SESSION['user_nama'], 0, 1)); ?></div>
                <span><?= htmlspecialchars($_SESSION['user_nama']); ?></span>
            </div>
        </header>

        <main class="main-content-area">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="content-title" style="margin-bottom: 0;">KELOLA PENGGUNA</h5>
                <a href="edit_user.php" class="btn btn-success">Tambah Pengguna Baru</a>
            </div>

            <div class="table-responsive dashboard-card" style="text-align: left;">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['role']); ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage_users.php?hapus=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin?');">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <footer>@2025 Mas Haris</footer>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>