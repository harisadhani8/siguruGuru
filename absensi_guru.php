<?php
require_once 'includes/auth.php';
auth_role(['guru', 'admin']);
$_SESSION['page_title'] = "Home Absensi Guru";
require_once 'includes/db.php';

if (!isset($_SESSION['user_nip'])) {
    include 'includes/header.php';
    echo '<div class="card-ui"><div class="notif notif-danger">Error: Akun Anda tidak memiliki NIP. Hubungi Admin.</div></div>';
    include 'includes/footer.php';
    exit();
}

$guru_nip = $_SESSION['user_nip'];
$hari_ini = date('l');
$tgl_ini = date('Y-m-d');
$waktu_sekarang = date('H:i:s');
$notif_msg = '';
$notif_type = '';

if (isset($_GET['action']) && isset($_GET['jadwal_id'])) {
    $jadwal_id = $_GET['jadwal_id'];

    $stmt_cek = $conn->prepare("SELECT id, jam_mulai FROM jadwal_mengajar WHERE id = ? AND guru_nip = ? AND hari = ?");
    $stmt_cek->bind_param("iss", $jadwal_id, $guru_nip, $hari_ini);
    $stmt_cek->execute();
    $jadwal_valid = $stmt_cek->get_result()->fetch_assoc();

    if (!$jadwal_valid) {
        $notif_msg = "Jadwal tidak valid atau bukan milik Anda.";
        $notif_type = "danger";
    } else {
        $stmt_log = $conn->prepare("SELECT * FROM absensi_log WHERE jadwal_id = ? AND tanggal = ?");
        $stmt_log->bind_param("is", $jadwal_id, $tgl_ini);
        $stmt_log->execute();
        $log = $stmt_log->get_result()->fetch_assoc();

        if ($_GET['action'] == 'masuk') {
            if ($log) {
                $notif_msg = "Anda sudah Check-In kelas ini.";
                $notif_type = "warning";
            } else {
                $status_kelas = ($waktu_sekarang > $jadwal_valid['jam_mulai']) ? 'Terlambat' : 'Tepat Waktu';
                $stmt_masuk = $conn->prepare("INSERT INTO absensi_log (jadwal_id, guru_nip, tanggal, jam_masuk, status) VALUES (?, ?, ?, NOW(), ?)");
                $stmt_masuk->bind_param("isss", $jadwal_id, $guru_nip, $tgl_ini, $status_kelas);

                $stmt_cek_harian = $conn->prepare("SELECT id FROM absensi_harian WHERE guru_nip = ? AND tanggal = ?");
                $stmt_cek_harian->bind_param("ss", $guru_nip, $tgl_ini);
                $stmt_cek_harian->execute();
                if ($stmt_cek_harian->get_result()->num_rows == 0) {
                    $status_harian = ($waktu_sekarang > '07:00:00') ? 'Terlambat' : 'Hadir';
                    $stmt_auto_absen = $conn->prepare("INSERT INTO absensi_harian (guru_nip, tanggal, jam_datang, status_kehadiran) VALUES (?, ?, NOW(), ?)");
                    $stmt_auto_absen->bind_param("sss", $guru_nip, $tgl_ini, $status_harian);
                    $stmt_auto_absen->execute();
                }

                if ($stmt_masuk->execute()) {
                    header('Location: absensi_guru.php?notif=success_in');
                    exit();
                }
            }
        } elseif ($_GET['action'] == 'keluar') {
            if ($log && empty($log['jam_keluar'])) {
                $stmt_keluar = $conn->prepare("UPDATE absensi_log SET jam_keluar = NOW(), status = 'Selesai' WHERE id = ?");
                $stmt_keluar->bind_param("i", $log['id']);
                if ($stmt_keluar->execute()) {
                    header('Location: absensi_guru.php?notif=success_out');
                    exit();
                }
            }
        }
    }
}

if (isset($_POST['submit_manual'])) {
    $status = $_POST['status_harian']; 
    $keterangan = $_POST['keterangan_harian'];

    $stmt_cek = $conn->prepare("SELECT id FROM absensi_harian WHERE guru_nip = ? AND tanggal = ?");
    $stmt_cek->bind_param("ss", $guru_nip, $tgl_ini);
    $stmt_cek->execute();
    if ($stmt_cek->get_result()->num_rows > 0) {
        $notif_msg = "Anda sudah tercatat absen hari ini.";
        $notif_type = "warning";
    } else {
        $jam_datang = ($status == 'Hadir') ? date('H:i:s') : NULL;
        $stmt_ins = $conn->prepare("INSERT INTO absensi_harian (guru_nip, tanggal, jam_datang, status_kehadiran, keterangan) VALUES (?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("sssss", $guru_nip, $tgl_ini, $jam_datang, $status, $keterangan);
        if ($stmt_ins->execute()) {
            $notif_msg = "Status kehadiran berhasil disimpan.";
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

$stmt_status = $conn->prepare("SELECT status_kehadiran, jam_datang FROM absensi_harian WHERE guru_nip = ? AND tanggal = ?");
$stmt_status->bind_param("ss", $guru_nip, $tgl_ini);
$stmt_status->execute();
$status_hari_ini = $stmt_status->get_result()->fetch_assoc();
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>
<?php if (isset($_GET['notif']) && $_GET['notif'] == 'success_in'): ?>
    <div class="notif notif-success notif-autohide">Berhasil Masuk Kelas (Otomatis Absen Hadir).</div>
<?php endif; ?>

<div class="card-ui mb-3" style="border-left: 5px solid var(--primary-color);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Status Hari Ini: <?= date('l, d M Y'); ?></h5>
            <?php if ($status_hari_ini): ?>
                <span class="badge bg-success" style="font-size:1rem;"><?= htmlspecialchars($status_hari_ini['status_kehadiran']); ?></span>
                <span class="text-muted ms-2">Jam: <?= date('H:i', strtotime($status_hari_ini['jam_datang'] ?? 'now')); ?></span>
            <?php else: ?>
                <span class="badge bg-secondary" style="font-size:1rem;">Belum Absen</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($result_jadwal->num_rows > 0): ?>
    <div class="card-ui">
        <h4>Jadwal Mengajar</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Status Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($j = $result_jadwal->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($j['jam_mulai'])); ?> - <?= date('H:i', strtotime($j['jam_selesai'])); ?></td>
                            <td><?= htmlspecialchars($j['nama_tingkat_numerik'] . ' ' . $j['singkatan_jurusan'] . ' ' . $j['rombel']); ?></td>
                            <td><?= htmlspecialchars($j['nama_mapel']); ?></td>
                            <td>
                                <?php
                                if ($j['jam_masuk']) echo '<span class="text-success">Masuk: ' . date('H:i', strtotime($j['jam_masuk'])) . '</span>';
                                if ($j['jam_keluar']) echo '<br><span class="text-primary">Selesai: ' . date('H:i', strtotime($j['jam_keluar'])) . '</span>';
                                if (!$j['jam_masuk']) echo $j['status'] ?? 'Belum Mulai';
                                ?>
                            </td>
                            <td>
                                <?php if (empty($j['status']) || $j['status'] == 'Belum Mulai'): ?>
                                    <a href="absensi_guru.php?action=masuk&jadwal_id=<?= $j['id']; ?>" class="btn btn-success btn-sm">Masuk Kelas</a>
                                <?php elseif ($j['status'] == 'Tepat Waktu' || $j['status'] == 'Terlambat'): ?>
                                    <?php if (empty($j['jam_keluar'])): ?>
                                        <a href="absensi_guru.php?action=keluar&jadwal_id=<?= $j['id']; ?>" class="btn btn-danger btn-sm">Selesai Kelas</a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Selesai</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">Anda tidak memiliki jadwal mengajar hari ini.</div>
<?php endif; ?>

<?php if (!$status_hari_ini): ?>
    <div class="card-ui mt-4" style="max-width: 600px;">
        <h4>Konfirmasi Kehadiran Lainnya</h4>
        <p class="text-muted small">Gunakan ini jika Anda tidak mengajar hari ini (Hadir) atau berhalangan (Izin/Sakit).</p>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Status Kehadiran</label>
                <select name="status_harian" class="form-select" required>
                    <option value="Hadir">Hadir (Tanpa Jadwal Mengajar)</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Keterangan (Opsional untuk Hadir)</label>
                <textarea name="keterangan_harian" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" name="submit_manual" class="btn btn-primary">Kirim Konfirmasi</button>
        </form>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>