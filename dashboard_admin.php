<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

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

<div style="padding: 30px 25px;">
    <h4 class="fw-bold mb-3 text-start">Halo, <?= htmlspecialchars($_SESSION['user_nama']); ?></h4>
    <h5 class="fw-bold mb-3 text-start">Dashboard</h5>
</div>

<div class="content-wrapper full-width" style="padding-top: 0;">


    <div class="d-grid gap-2 mt-4">
        <a href="laporan_absensi.php" class="btn btn-action role-button">Lihat Laporan Absensi</a>
        <a href="lihat_koreksi.php" class="btn btn-action role-button">Lihat Pengajuan Koreksi</a>
        <a href="logout.php" class="btn btn-logout role-button">LOGOUT</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>