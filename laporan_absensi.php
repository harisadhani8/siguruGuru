<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
$_SESSION['page_title'] = "Rekap Absensi";
require_once 'includes/db.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$sql = "SELECT a.*, j.jam_mulai, u.nama as nama_guru, 
        (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
        ju.singkatan_jurusan, k.rombel, m.nama_mapel 
        FROM absensi_log a
        LEFT JOIN users u ON a.guru_nip = u.nip
        LEFT JOIN jadwal_mengajar j ON a.jadwal_id = j.id
        LEFT JOIN kelas k ON j.kelas_id = k.id
        LEFT JOIN tingkat t ON k.tingkat_id = t.id
        LEFT JOIN jurusan ju ON k.jurusan_id = ju.id
        LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
        WHERE a.tanggal BETWEEN ? AND ?
        ORDER BY a.tanggal DESC, u.nama ASC, j.jam_mulai ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui">
    <h4>Rekap Laporan Absensi</h4>

    <form method="GET" class="card-ui" style="background-color: var(--light-color); margin-top: 1rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai); ?>">
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="tgl_selesai" value="<?= htmlspecialchars($tgl_selesai); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="export_laporan.php?tgl_mulai=<?= $tgl_mulai; ?>&tgl_selesai=<?= $tgl_selesai; ?>" class="btn btn-success">Export Excel</a>
        </div>
    </form>

    <div style="overflow-x: auto; margin-top: 1rem;">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Guru</th>
                    <th>Status</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Keterangan</th>
                    <th>Bukti</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td><?= htmlspecialchars($row['nama_mapel'] ?? '-'); ?></td>
                            <td>
                                <?= $row['nama_tingkat_numerik'] ? htmlspecialchars($row['nama_tingkat_numerik'] . ' ' . $row['singkatan_jurusan'] . ' ' . $row['rombel']) : '-'; ?>
                            </td>
                            <td><?= $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                            <td><?= $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-'; ?></td>

                            <td><?= htmlspecialchars($row['keterangan_izin'] ?? ''); ?></td>
                            <td>
                                <?php if ($row['status'] == 'Dinas Luar' && !empty($row['file_bukti'])): ?>
                                    <a href="uploads/surat_tugas/<?= htmlspecialchars($row['file_bukti']); ?>" target="_blank">Lihat</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">Tidak ada data pada rentang tanggal ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>