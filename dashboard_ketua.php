<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketua kelas') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

// Ambil data absensi
$absensi_terbaru = $conn->query("SELECT u.nama FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.keterangan = 'Hadir' AND a.tanggal = CURDATE() LIMIT 3");
$tidak_hadir = $conn->query("SELECT u.nama, a.keterangan FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.keterangan != 'Hadir' AND a.tanggal = CURDATE() LIMIT 3");
?>
<?php require_once 'includes/header.php'; ?>

<div style="padding: 30px 25px;">
    <h4 class="fw-bold mb-3 text-start">Halo, <?= htmlspecialchars($_SESSION['user_nama']); ?></h4>
    <h5 class="fw-bold mb-3 text-start">Dashboard</h5>
</div>

<div class="content-wrapper form-view" style="padding-top: 0;">
    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success"><?= $_SESSION['pesan_sukses']; ?></div>
    <?php unset($_SESSION['pesan_sukses']);
    endif; ?>

    <?php if (isset($_SESSION['pesan_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['pesan_error']; ?></div>
    <?php unset($_SESSION['pesan_error']);
    endif; ?>

    <div class="dashboard-card">
        <h5>Absensi Terbaru</h5>
        <?php if ($absensi_terbaru->num_rows > 0): ?>
            <?php while ($row = $absensi_terbaru->fetch_assoc()): ?>
                <p>[<?= htmlspecialchars($row['nama']); ?>]</p>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada yang absen hari ini.</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <h5>Tidak Hadir Hari Ini</h5>
        <?php if ($tidak_hadir->num_rows > 0): ?>
            <?php while ($row = $tidak_hadir->fetch_assoc()): ?>
                <p>[<?= htmlspecialchars($row['nama']); ?> - <?= htmlspecialchars($row['keterangan']); ?>]</p>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Semua guru hadir hari ini.</p>
        <?php endif; ?>
    </div>

    <div class="d-grid gap-2 mt-4">
        <a href="form_koreksi.php" class="btn btn-action role-button">Ajukan Koreksi</a>
        <a href="logout.php" class="btn btn-logout role-button">LOGOUT</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>