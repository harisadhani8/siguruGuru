<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super admin'])) {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

$query = "SELECT u.nama, a.tanggal, a.keterangan, a.catatan 
          FROM absensi a
          JOIN users u ON a.user_id = u.id
          ORDER BY a.tanggal DESC, u.nama ASC";
$laporan = $conn->query($query);
?>
<?php require_once 'includes/header.php'; ?>

<div class="header">
    <div class="header-brand">
        <img src="logo.png" alt="SiGuru Logo" class="header-app-logo">
    </div>
    <button id="theme-toggle-btn" class="theme-toggle">🌙</button>
</div>

<div style="padding: 30px 25px;">
    <h5 class="fw-bold mb-3 text-start">Laporan Detail Absensi Guru</h5>
</div>

<div class="content-wrapper full-width" style="padding-top: 0;">
    <div class="table-responsive">
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

    <?php $dashboard_link = ($_SESSION['user_role'] == 'super admin') ? 'dashboard_superadmin.php' : 'dashboard_admin.php'; ?>
    <a href="<?= $dashboard_link; ?>" class="btn btn-secondary role-button mt-3" style="max-width: 200px;">KEMBALI KE DASHBOARD</a>
</div>

<?php require_once 'includes/footer.php'; ?>