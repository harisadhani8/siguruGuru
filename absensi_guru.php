<?php
require_once 'includes/auth.php';
auth_role(['guru', 'admin']);
$_SESSION['page_title'] = "Home Absensi Guru";
require_once 'includes/db.php';

if (!isset($_SESSION['user_nip'])) {
    include 'includes/header.php';
    echo '<div class="card-ui"><div class="notif notif-danger">Error: Akun Anda tidak memiliki NIP yang terdaftar. Hubungi Admin.</div></div>';
    include 'includes/footer.php';
    exit();
}

$guru_nip = $_SESSION['user_nip'];
$hari_ini = date('l');
$tgl_ini = date('Y-m-d');
$notif_msg = '';
$notif_type = '';

if (isset($_GET['action']) && isset($_GET['jadwal_id'])) {
    $jadwal_id = $_GET['jadwal_id'];
    $stmt_cek = $conn->prepare("SELECT id FROM jadwal_mengajar WHERE id = ? AND guru_nip = ? AND hari = ?");
    $stmt_cek->bind_param("iss", $jadwal_id, $guru_nip, $hari_ini);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows == 0) {
        $notif_msg = "Aksi dibatalkan. Jadwal tidak valid.";
        $notif_type = "danger";
    } else {
        $stmt_log = $conn->prepare("SELECT * FROM absensi_log WHERE jadwal_id = ? AND tanggal = ?");
        $stmt_log->bind_param("is", $jadwal_id, $tgl_ini);
        $stmt_log->execute();
        $log = $stmt_log->get_result()->fetch_assoc();
        if ($_GET['action'] == 'masuk') {
            if ($log) {
                $notif_msg = "Anda sudah tercatat absen untuk jadwal ini.";
                $notif_type = "warning";
            } else {
                $jam_mulai_jadwal = $conn->query("SELECT jam_mulai FROM jadwal_mengajar WHERE id = $jadwal_id")->fetch_row()[0];
                $status = (date('H:i:s') > $jam_mulai_jadwal) ? 'Terlambat' : 'Hadir';
                $stmt_masuk = $conn->prepare("INSERT INTO absensi_log (jadwal_id, guru_nip, tanggal, jam_masuk, status) VALUES (?, ?, ?, NOW(), ?)");
                $stmt_masuk->bind_param("isss", $jadwal_id, $guru_nip, $tgl_ini, $status);
                if ($stmt_masuk->execute()) {
                    header('Location: absensi_guru.php?notif=13');
                    exit();
                }
            }
        } elseif ($_GET['action'] == 'keluar') {
            if ($log && empty($log['jam_keluar'])) {
                $stmt_keluar = $conn->prepare("UPDATE absensi_log SET jam_keluar = NOW() WHERE id = ?");
                $stmt_keluar->bind_param("i", $log['id']);
                if ($stmt_keluar->execute()) {
                    $notif_msg = "Jam Keluar berhasil dicatat.";
                    $notif_type = "success";
                }
            } else {
                $notif_msg = "Aksi tidak valid.";
                $notif_type = "warning";
            }
        }
    }
}
if (isset($_POST['submit_izin'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $status = $_POST['status'];
    $keterangan = $_POST['keterangan_izin'];
    $stmt_log = $conn->prepare("SELECT * FROM absensi_log WHERE jadwal_id = ? AND tanggal = ?");
    $stmt_log->bind_param("is", $jadwal_id, $tgl_ini);
    $stmt_log->execute();
    if ($stmt_log->get_result()->num_rows > 0) {
        $notif_msg = "Anda sudah tercatat absen untuk jadwal ini.";
        $notif_type = "warning";
    } else {
        $stmt_izin = $conn->prepare("INSERT INTO absensi_log (jadwal_id, guru_nip, tanggal, status, keterangan_izin) VALUES (?, ?, ?, ?, ?)");
        $stmt_izin->bind_param("issss", $jadwal_id, $guru_nip, $tgl_ini, $status, $keterangan);
        if ($stmt_izin->execute()) {
            $notif_msg = "Pengajuan $status berhasil dikirim.";
            $notif_type = "success";
        }
    }
}

$sql_jadwal = "SELECT j.*, 
               (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
               ju.singkatan_jurusan, k.rombel, m.nama_mapel, a.jam_masuk, a.jam_keluar, a.status 
               FROM jadwal_mengajar j
               JOIN kelas k ON j.kelas_id = k.id
               JOIN tingkat t ON k.tingkat_id = t.id
               JOIN jurusan ju ON k.jurusan_id = ju.id
               JOIN mata_pelajaran m ON j.mapel_id = m.id
               LEFT JOIN absensi_log a ON j.id = a.jadwal_id AND a.tanggal = ?
               WHERE j.guru_nip = ? AND j.hari = ?
               ORDER BY j.jam_mulai ASC";
$stmt = $conn->prepare($sql_jadwal);
$stmt->bind_param("sss", $tgl_ini, $guru_nip, $hari_ini);
$stmt->execute();
$result_jadwal = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>
<?php if (isset($_GET['notif']) && $_GET['notif'] == 13): ?>
    <div class="notif notif-success notif-autohide">Absensi 'Jam Masuk' berhasil dicatat.</div>
<?php endif; ?>

<div class="card-ui">
    <h4>Jadwal Mengajar Anda Hari Ini (<?= $hari_ini, ', ', $tgl_ini; ?>)</h4>
    <table class="table" style="margin-top: 1rem;">
        <thead>
            <tr>
                <th>Jam</th>
                <th>Kelas</th>
                <th>Mata Pelajaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_jadwal->num_rows > 0): ?>
                <?php mysqli_data_seek($result_jadwal, 0); ?>
                <?php while ($j = $result_jadwal->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('H:i', strtotime($j['jam_mulai'])); ?> - <?= date('H:i', strtotime($j['jam_selesai'])); ?></td>
                        <td><?= htmlspecialchars($j['nama_tingkat_numerik'] . ' ' . $j['singkatan_jurusan'] . ' ' . $j['rombel']); ?></td>
                        <td><?= htmlspecialchars($j['nama_mapel']); ?></td>
                        <td>
                            <?php
                            if ($j['jam_masuk']) echo "Masuk: " . date('H:i', strtotime($j['jam_masuk']));
                            if ($j['jam_keluar']) echo "<br>Keluar: " . date('H:i', strtotime($j['jam_keluar']));
                            if (!$j['jam_masuk']) echo $j['status'] ?? 'Belum Absen';
                            ?>
                        </td>
                        <td>
                            <?php if (empty($j['status'])): ?>
                                <a href="absensi_guru.php?action=masuk&jadwal_id=<?= $j['id']; ?>" class="btn btn-success">Jam Masuk</a>
                            <?php elseif ($j['status'] == 'Hadir' || $j['status'] == 'Terlambat'): ?>
                                <?php if (empty($j['jam_keluar'])): ?>
                                    <a href="absensi_guru.php?action=keluar&jadwal_id=<?= $j['id']; ?>" class="btn btn-danger">Jam Keluar</a>
                                <?php else: ?><span class="btn btn-secondary">Selesai</span><?php endif; ?>
                            <?php else: ?><span class="btn btn-secondary">Tercatat</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada jadwal mengajar hari ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($_SESSION['user_role'] == 'guru'): ?>
    <div class="card-ui" style="margin-top: 1.5rem; max-width: 600px;">
        <h4>Formulir Tidak Hadir (Izin / Sakit)</h4>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Pilih Jadwal</label>
                <select name="jadwal_id" class="form-select" required>
                    <option value="" disabled selected>-- Pilih Jadwal --</option>
                    <?php mysqli_data_seek($result_jadwal, 0); ?>
                    <?php while ($j = $result_jadwal->fetch_assoc()): ?>
                        <option value="<?= $j['id']; ?>">
                            <?= date('H:i', strtotime($j['jam_mulai'])); ?> |
                            <?= htmlspecialchars($j['nama_tingkat_numerik'] . ' ' . $j['singkatan_jurusan'] . ' ' . $j['rombel']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <select name="status" class="form-select" required>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Alasan/Keterangan Tambahan</label>
                <textarea name="keterangan_izin" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" name="submit_izin" class="btn btn-primary">Kirim Pengajuan</button>
        </form>
    </div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>