<?php
require_once 'includes/auth.php';
auth_role('ketua kelas');
$_SESSION['page_title'] = "Ajukan Koreksi";
require_once 'includes/db.php';

if (empty($_SESSION['kelas_id']) || empty($_SESSION['user_nisn'])) {
    include 'includes/header.php';
    echo '<div class="card-ui"><div class="notif notif-danger">Sesi Anda tidak valid (NISN atau Kelas ID tidak ditemukan). Harap login ulang.</div></div>';
    include 'includes/footer.php';
    exit();
}
$kelas_id = $_SESSION['kelas_id'];
$nisn = $_SESSION['user_nisn'];
$notif_msg = '';
$notif_type = '';

if (isset($_POST['submit_koreksi'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan_baru'];
    $alasan = $_POST['alasan'];

    $stmt_cek = $conn->prepare("SELECT id FROM koreksi_absensi WHERE jadwal_id = ? AND tanggal = ? AND status = 'Diajukan'");
    $stmt_cek->bind_param("is", $jadwal_id, $tanggal);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows > 0) {
        $notif_msg = "Koreksi untuk jadwal & tanggal ini sudah pernah diajukan dan masih pending.";
        $notif_type = "warning";
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO koreksi_absensi (jadwal_id, tanggal, keterangan_baru, alasan, diajukan_oleh_nisn) VALUES (?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("issss", $jadwal_id, $tanggal, $keterangan, $alasan, $nisn);
        if ($stmt_ins->execute()) {
            $notif_msg = "Pengajuan koreksi berhasil dikirim.";
            $notif_type = "success";
        } else {
            $notif_msg = "Gagal mengirim: " . $stmt_ins->error;
            $notif_type = "danger";
        }
    }
}

$sql_jadwal = "SELECT j.id, j.hari, u.nama, m.nama_mapel 
               FROM jadwal_mengajar j
               JOIN users u ON j.guru_nip = u.nip
               JOIN mata_pelajaran m ON j.mapel_id = m.id
               WHERE j.kelas_id = ? ORDER BY j.hari, j.jam_mulai";
$stmt = $conn->prepare($sql_jadwal);
$stmt->bind_param("i", $kelas_id);
$stmt->execute();
$result_jadwal = $stmt->get_result();

$sql_riwayat = "SELECT k.*, u.nama as nama_guru, m.nama_mapel, k.catatan_admin
                FROM koreksi_absensi k
                LEFT JOIN jadwal_mengajar j ON k.jadwal_id = j.id
                LEFT JOIN users u ON j.guru_nip = u.nip
                LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
                WHERE k.diajukan_oleh_nisn = ? 
                ORDER BY k.tanggal DESC, k.id DESC";
$stmt_riwayat = $conn->prepare($sql_riwayat);
$stmt_riwayat->bind_param("s", $nisn);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();

?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui" style="max-width: 600px; margin: 0 auto;">
    <h4>Formulir Koreksi Absensi</h4>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Tanggal Absensi</label>
            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Pilih Jadwal Guru</label>
            <select name="jadwal_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Jadwal --</option>
                <?php if ($result_jadwal->num_rows > 0): ?>
                    <?php while ($j = $result_jadwal->fetch_assoc()): ?>
                        <option value="<?= $j['id']; ?>">(<?= $j['hari']; ?>) <?= htmlspecialchars($j['nama']); ?> - <?= htmlspecialchars($j['nama_mapel']); ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="" disabled>Tidak ada jadwal untuk kelas ini.</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Keterangan Baru</label>
            <select name="keterangan_baru" class="form-select" required>
                <option value="Hadir">Hadir</option>
                <option value="Izin">Izin</option>
                <option value="Sakit">Sakit</option>
                <option value="Absen">Absen</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Alasan Koreksi</label>
            <textarea name="alasan" class="form-control" rows="3" required placeholder="Contoh: Sebenarnya guru hadir, digantikan oleh guru lain."></textarea>
        </div>
        <button type="submit" name="submit_koreksi" class="btn btn-primary">Kirim Pengajuan</button>
    </form>
</div>


<div class="card-ui" style="margin-top: 2rem;">
    <h4>Riwayat Pengajuan Koreksi Anda</h4>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Tgl Koreksi</th>
                    <th>Guru & Mapel</th>
                    <th>Perubahan</th>
                    <th>Alasan Anda</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_riwayat->num_rows > 0): ?>
                    <?php while ($row_riwayat = $result_riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row_riwayat['tanggal']); ?></td>
                            <td><?= htmlspecialchars($row_riwayat['nama_guru'] ?? 'N/A'); ?> (<?= htmlspecialchars($row_riwayat['nama_mapel'] ?? 'N/A'); ?>)</td>
                            <td><?= htmlspecialchars($row_riwayat['keterangan_baru']); ?></td>
                            <td><?= htmlspecialchars($row_riwayat['alasan']); ?></td>
                            <td><?= htmlspecialchars($row_riwayat['status']); ?></td>
                            <td>
                                <?php if ($row_riwayat['status'] == 'Ditolak'): ?>
                                    <span style="color: var(--danger); font-weight: 600;">
                                        <?= htmlspecialchars($row_riwayat['catatan_admin'] ?? 'Tidak ada catatan.'); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Anda belum pernah mengajukan koreksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>