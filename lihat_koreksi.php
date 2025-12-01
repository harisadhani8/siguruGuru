<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
$_SESSION['page_title'] = "Pengajuan Koreksi";
require_once 'includes/db.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$notif_msg = '';
$notif_type = '';
if (isset($_GET['notif'])) {
    if ($_GET['notif'] == 19) {
        $notif_msg = "Aksi berhasil diproses.";
        $notif_type = "success";
    }
    if ($_GET['notif'] == 20) {
        $notif_msg = "Gagal: Alasan penolakan tidak boleh kosong.";
        $notif_type = "danger";
    }
    if ($_GET['notif'] == 21) {
        $notif_msg = "Error: Terjadi kesalahan database.";
        $notif_type = "danger";
    }
}

$sql = "SELECT k.*, u_guru.nama as nama_guru, m.nama_mapel, u_ketua.nama as nama_ketua, 
        (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
        ju.singkatan_jurusan, c.rombel, a.status as status_awal
        FROM koreksi_absensi k
        LEFT JOIN jadwal_mengajar j ON k.jadwal_id = j.id
        LEFT JOIN users u_guru ON j.guru_nip = u_guru.nip
        LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
        LEFT JOIN users u_ketua ON k.diajukan_oleh_nisn = u_ketua.nisn
        LEFT JOIN kelas c ON j.kelas_id = c.id
        LEFT JOIN tingkat t ON c.tingkat_id = t.id
        LEFT JOIN jurusan ju ON c.jurusan_id = ju.id
        LEFT JOIN absensi_log a ON k.jadwal_id = a.jadwal_id AND k.tanggal = a.tanggal
        WHERE k.tanggal BETWEEN ? AND ?
        ORDER BY k.status ASC, k.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$result = $stmt->get_result();
?>
<meta http-equiv="refresh" content="25">
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 1.5rem;">
        <h4 style="margin: 0;">Daftar Pengajuan Koreksi</h4>

        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">

            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: var(--text-light);">Dari</label>
                <input type="date" name="tgl_mulai" class="form-control" style="height: 38px; padding: 0.375rem 0.75rem;" value="<?= $tgl_mulai; ?>">
            </div>

            <div style="display: flex; flex-direction: column;">
                <label style="font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; color: var(--text-light);">Sampai</label>
                <input type="date" name="tgl_selesai" class="form-control" style="height: 38px; padding: 0.375rem 0.75rem;" value="<?= $tgl_selesai; ?>">
            </div>

            <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 15px; display: flex; align-items: center; justify-content: center;">
                Filter
            </button>

            <a href="export_koreksi.php?tgl_mulai=<?= $tgl_mulai; ?>&tgl_selesai=<?= $tgl_selesai; ?>" class="btn btn-success" style="height: 38px; padding: 0 15px; display: flex; align-items: center; justify-content: center;">Export Excel
            </a>
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Tgl Absen</th>
                    <th>Diajukan Oleh</th>
                    <th>Guru / Mapel / Kelas</th>
                    <th>Status Awal</th>
                    <th>Koreksi Menjadi</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td><?= htmlspecialchars($row['nama_ketua'] ?? 'N/A'); ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_guru'] ?? 'N/A'); ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['nama_mapel'] ?? 'N/A'); ?></small><br>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($row['nama_tingkat_numerik'] . ' ' . $row['singkatan_jurusan'] . ' ' . $row['rombel']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['status_awal'] ?? 'Belum Absen'); ?></td>
                            <td>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($row['keterangan_baru']); ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['alasan']); ?></td>
                            <td>
                                <?php
                                $bg_status = 'secondary';
                                if ($row['status'] == 'Diajukan') $bg_status = 'warning';
                                if ($row['status'] == 'Disetujui') $bg_status = 'success';
                                if ($row['status'] == 'Ditolak') $bg_status = 'danger';
                                ?>
                                <span class="badge bg-<?= $bg_status; ?>"><?= htmlspecialchars($row['status']); ?></span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'Diajukan'): ?>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="proses_koreksi.php?id=<?= $row['id']; ?>&aksi=setuju"
                                            class="btn btn-success btn-sm"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0;"
                                            title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm btn-tolak"
                                            data-id_koreksi="<?= $row['id']; ?>"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0;"
                                            title="Tolak">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.85rem;">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">Tidak ada pengajuan koreksi pada rentang tanggal ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            const btnTolak = e.target.closest('.btn-tolak');
            if (btnTolak) {
                e.preventDefault();
                const idKoreksi = btnTolak.getAttribute('data-id_koreksi');
                const alasan = prompt("Harap masukkan alasan penolakan (Wajib):");
                if (alasan !== null) {
                    if (alasan.trim() !== "") {
                        window.location.href = `proses_koreksi.php?id=${idKoreksi}&aksi=tolak&alasan_admin=${encodeURIComponent(alasan)}`;
                    } else {
                        alert("Alasan penolakan tidak boleh kosong.");
                    }
                }
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>