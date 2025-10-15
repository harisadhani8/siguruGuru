<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['guru', 'staff'])) {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $catatan = $_POST['catatan'];

    $stmt = $conn->prepare("INSERT INTO absensi (user_id, tanggal, keterangan, catatan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $tanggal, $keterangan, $catatan);

    if ($stmt->execute()) {
        $_SESSION['absen_sukses'] = true;
    }
    $stmt->close();
    header("Location: absensi_guru.php");
    exit();
}
?>
<?php require_once 'includes/header.php'; ?>

<div style="padding: 30px 25px;">
    <h4 class="fw-bold mb-3 text-start">Halo, <?= htmlspecialchars($_SESSION['user_nama']); ?></h4>
    <h5 class="fw-bold mb-3 text-start">Form Absensi</h5>
</div>

<div class="content-wrapper form-view" style="padding-top: 0;">
    <form action="absensi_guru.php" method="POST">
        <div class="dashboard-card">
            <div class="mb-3 text-start">
                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                <select class="form-select form-control" name="keterangan" required>
                    <option value="">Pilih disini...</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Dinas Luar">Dinas Luar</option>
                </select>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Keterangan Khusus <span class="text-danger">*</span></label>
                <textarea class="form-control" name="catatan" placeholder="Jika Hadir, isi '-' atau alasan lain jika tidak hadir..." required></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-submit mt-3">KIRIM</button>
        <a href="logout.php" class="btn btn-logout role-button mt-2">LOGOUT</a>
    </form>
</div>

<div class="modal fade" id="absenSuksesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-icon success">✅</div>
                <p>Absensi berhasil dikirim.</p>
            </div>
        </div>
    </div>
</div>
<?php
$modal_to_show = '';
if (isset($_SESSION['login_success'])) {
    $modal_to_show = 'successModal';
    unset($_SESSION['login_success']);
}
if (isset($_SESSION['absen_sukses'])) {
    $modal_to_show = 'absenSuksesModal';
    unset($_SESSION['absen_sukses']);
}
if ($modal_to_show): ?><script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('<?= $modal_to_show ?>'));
            myModal.show();
            <?php if ($modal_to_show == 'absenSuksesModal'): ?>setTimeout(function() {
                window.location.href = 'index.php';
            }, 2000);
        <?php endif; ?>
        });
    </script><?php endif; ?>
<?php require_once 'includes/footer.php'; ?>