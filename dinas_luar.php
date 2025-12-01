<?php
require_once 'includes/auth.php';
auth_role('super admin'); 
$_SESSION['page_title'] = "Input Dinas Luar";
require_once 'includes/db.php';

$notif_msg = '';
$notif_type = '';

if (isset($_POST['submit_dinas_luar'])) {
    $guru_nip = $_POST['guru_nip'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan_izin'];
    $file_bukti_nama = '';

    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
        $target_dir = "uploads/surat_tugas/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $file_bukti_nama = uniqid() . '-' . basename($_FILES["file_bukti"]["name"]);
        $target_file = $target_dir . $file_bukti_nama;

        if (!move_uploaded_file($_FILES["file_bukti"]["tmp_name"], $target_file)) {
            $notif_msg = "Gagal mengupload file bukti.";
            $notif_type = "danger";
        }
    }

    if (empty($notif_msg)) {
        $stmt_cek = $conn->prepare("SELECT id FROM absensi_log WHERE guru_nip = ? AND tanggal = ?");
        $stmt_cek->bind_param("ss", $guru_nip, $tanggal);
        $stmt_cek->execute();

        if ($stmt_cek->get_result()->num_rows > 0) {
            $notif_msg = "Gagal. Guru ini sudah memiliki catatan absensi pada tanggal tersebut.";
            $notif_type = "warning";
        } else {
            $status = "Dinas Luar";
            $stmt_insert = $conn->prepare("INSERT INTO absensi_log (guru_nip, tanggal, status, keterangan_izin, file_bukti) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $guru_nip, $tanggal, $status, $keterangan, $file_bukti_nama);
            if ($stmt_insert->execute()) {
                $notif_msg = "Berhasil mencatat Dinas Luar untuk guru tersebut.";
                $notif_type = "success";
            } else {
                $notif_msg = "Gagal menyimpan data: " . $stmt_insert->error;
                $notif_type = "danger";
            }
        }
    }
}

$result_guru = $conn->query("SELECT nip, nama, role_guru FROM users WHERE role = 'guru' OR role = 'admin' ORDER BY nama ASC");
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui" style="max-width: 600px; margin: 0 auto;">
    <h4>Input Keterangan Dinas Luar</h4>
    <p>Gunakan form ini untuk mencatat guru yang sedang Dinas Luar (Wajib dengan Surat Tugas).</p>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Pilih Guru</label>
            <select name="guru_nip" class="form-select" required>
                <option value="" disabled selected>-- Pilih Guru --</option>
                <?php while ($g = $result_guru->fetch_assoc()): ?>
                    <option value="<?= $g['nip']; ?>"><?= htmlspecialchars($g['nama']); ?> (<?= htmlspecialchars($g['role_guru']); ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Dinas Luar</label>
            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Keterangan/Nama Kegiatan</label>
            <textarea name="keterangan_izin" class="form-control" rows="3" required placeholder="Cth: Mengikuti MGMP di Dinas Pendidikan"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Upload Surat Tugas (Opsional)</label>
            <input type="file" class="form-control" name="file_bukti" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <button type="submit" name="submit_dinas_luar" class="btn btn-primary">Simpan Keterangan</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>