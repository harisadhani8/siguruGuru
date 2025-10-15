<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ketua kelas') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

$query_absensi = "SELECT a.id, u.nama, a.tanggal, a.keterangan FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.keterangan != 'Hadir' ORDER BY a.tanggal DESC, u.nama ASC";
$result_absensi = $conn->query($query_absensi);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $absensi_id = $_POST['absensi_id'];
    $alasan_koreksi = $_POST['alasan_koreksi'];
    $user_id_ketua = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO koreksi_absensi (absensi_id, alasan_koreksi, diajukan_oleh) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $absensi_id, $alasan_koreksi, $user_id_ketua);

    if ($stmt->execute()) {
        $_SESSION['pesan_sukses'] = "Koreksi berhasil diajukan.";
    } else {
        $_SESSION['pesan_error'] = "Gagal mengajukan koreksi.";
    }
    $stmt->close();
    header("Location: dashboard_ketua.php");
    exit();
}
?>
<?php require_once 'includes/header.php'; ?>
<div style="padding: 30px 25px;">
    <h5 class="fw-bold mb-3 text-start">Ajukan Koreksi Absensi</h5>
</div>

<div class="content-wrapper form-view" style="padding-top: 0;">
    <form action="form_koreksi.php" method="POST">
        <div class="dashboard-card">
            <div class="mb-3 text-start">
                <label for="absensi_id" class="form-label">Pilih Absensi Guru <span class="text-danger">*</span></label>
                <select id="absensi_id" name="absensi_id" class="form-select form-control" required>
                    <option value="">-- Pilih Guru & Tanggal --</option>
                    <?php while ($row = $result_absensi->fetch_assoc()): ?>
                        <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['nama']); ?> | <?= date('d M Y', strtotime($row['tanggal'])); ?> (<?= htmlspecialchars($row['keterangan']); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3 text-start">
                <label for="alasan_koreksi" class="form-label">Alasan Koreksi <span class="text-danger">*</span></label>
                <textarea class="form-control" id="alasan_koreksi" name="alasan_koreksi" placeholder="Contoh: Sebenarnya Guru A hadir menggantikan Guru B" required></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-submit mt-3">KIRIM PENGAJUAN</button>
        <a href="dashboard_ketua.php" class="btn btn-secondary role-button mt-2">KEMBALI KE DASHBOARD</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>