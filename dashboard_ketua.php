<?php
require_once 'includes/auth.php';
auth_role('ketua kelas');
require_once 'includes/db.php';

if (empty($_SESSION['kelas_id'])) {
    $_SESSION['page_title'] = "Error";
    include 'includes/header.php';
    echo '<div class="notif notif-danger">Akun Anda tidak terhubung ke kelas manapun. Hubungi Super Admin.</div>';
    include 'includes/footer.php';
    exit();
}
$kelas_id = $_SESSION['kelas_id'];

$stmt_kelas = $conn->prepare("SELECT 
                             (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
                             j.singkatan_jurusan, k.rombel 
                             FROM kelas k
                             JOIN tingkat t ON k.tingkat_id = t.id
                             JOIN jurusan j ON k.jurusan_id = j.id
                             WHERE k.id = ?");
$stmt_kelas->bind_param("i", $kelas_id);
$stmt_kelas->execute();
$kelas_info = $stmt_kelas->get_result()->fetch_assoc();

if (!$kelas_info) {
    $_SESSION['page_title'] = "Error";
    include 'includes/header.php';
    echo '<div class="notif notif-danger">Data kelas tidak ditemukan. Hubungi Super Admin.</div>';
    include 'includes/footer.php';
    exit();
}

$nama_kelas = $kelas_info['nama_tingkat_numerik'] . ' ' . $kelas_info['singkatan_jurusan'] . ' ' . $kelas_info['rombel'];
$_SESSION['page_title'] = "Dashboard Kelas: $nama_kelas";

$tgl_ini = date('Y-m-d');
$hari_ini = date('l');

$sql = "SELECT j.jam_mulai, m.nama_mapel, u.nama as nama_guru, a.status, a.keterangan_izin
        FROM jadwal_mengajar j
        JOIN users u ON j.guru_nip = u.nip
        JOIN mata_pelajaran m ON j.mapel_id = m.id
        LEFT JOIN absensi_log a ON j.id = a.jadwal_id AND a.tanggal = ?
        WHERE j.kelas_id = ? AND j.hari = ?
        ORDER BY j.jam_mulai ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $tgl_ini, $kelas_id, $hari_ini);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui">
    <h4>Status Guru Kelas Anda (<?= date('d M Y'); ?>)</h4>
    <table class="table" style="margin-top: 1rem;">
        <thead>
            <tr>
                <th>Jam</th>
                <th>Guru</th>
                <th>Mapel</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('H:i', strtotime($row['jam_mulai'])); ?></td>
                        <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                        <td><?= htmlspecialchars($row['nama_mapel']); ?></td>
                        <td>
                            <?php
                            $status = $row['status'] ?? 'Belum Absen';
                            echo htmlspecialchars($status);
                            if ($status == 'Izin' || $status == 'Sakit' || $status == 'Dinas Luar') {
                                echo " (" . htmlspecialchars($row['keterangan_izin']) . ")";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada jadwal untuk kelas Anda hari ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<meta http-equiv="refresh" content="20">
<?php include 'includes/footer.php'; ?>