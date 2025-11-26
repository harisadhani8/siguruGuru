<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
$_SESSION['page_title'] = "Pengajuan Koreksi";
require_once 'includes/db.php';

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

$sql = "SELECT k.*, u_guru.nama as nama_guru, u_ketua.nama as nama_ketua, 
        (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
        ju.singkatan_jurusan, kls.rombel, m.nama_mapel, 
        a.status as status_awal
        FROM koreksi_absensi k
        LEFT JOIN jadwal_mengajar j ON k.jadwal_id = j.id
        LEFT JOIN users u_guru ON j.guru_nip = u_guru.nip
        LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
        LEFT JOIN users u_ketua ON k.diajukan_oleh_nisn = u_ketua.nisn
        LEFT JOIN kelas kls ON j.kelas_id = kls.id
        LEFT JOIN tingkat t ON kls.tingkat_id = t.id
        LEFT JOIN jurusan ju ON kls.jurusan_id = ju.id
        LEFT JOIN absensi_log a ON k.jadwal_id = a.jadwal_id AND k.tanggal = a.tanggal
        ORDER BY k.status ASC, k.tanggal DESC";

$result = $conn->query($sql);
if (!$result) {
    die("Error SQL: " . $conn->error);
}
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui">
    <h4>Daftar Pengajuan Koreksi Absensi</h4>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table class="table">
            <thead>
                <tr>
                    <th>Diajukan Oleh</th>
                    <th>Tgl Koreksi</th>
                    <th>Guru / Mapel</th>
                    <th>Status Awal</th>
                    <th>Diajukan Menjadi</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row['nama_ketua'] ?? 'N/A'); ?><br>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($row['nama_tingkat_numerik'] . ' ' . $row['singkatan_jurusan'] . ' ' . $row['rombel']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td><?= htmlspecialchars($row['nama_guru'] ?? 'N/A'); ?><br>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($row['nama_mapel'] ?? 'N/A'); ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['status_awal'] ?? 'Belum Absen'); ?></td>
                            <td><?= htmlspecialchars($row['keterangan_baru']); ?></td>
                            <td><?= htmlspecialchars($row['alasan']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td>
                                <?php if ($row['status'] == 'Diajukan'): ?>
                                    <a href="proses_koreksi.php?id=<?= $row['id']; ?>&status=Disetujui" class="btn btn-success" style="margin-bottom: 5px;">Setujui</a>

                                    <button class="btn btn-danger btn-tolak" data-id_koreksi="<?= $row['id']; ?>">Tolak</button>

                                <?php else: ?>
                                    <span class="btn btn-secondary">Diproses</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Belum ada pengajuan koreksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allBtnTolak = document.querySelectorAll('.btn-tolak');

        allBtnTolak.forEach(button => {
            button.addEventListener('click', function() {
                const idKoreksi = this.getAttribute('data-id_koreksi');

                const alasan = prompt("Harap masukkan alasan penolakan (wajib diisi):");

                if (alasan === null) {
                    return; 
                }

                if (alasan.trim() === "") {
                    alert("Alasan penolakan tidak boleh kosong.");
                } else {
                    window.location.href = `proses_koreksi.php?id=${idKoreksi}&status=Ditolak&alasan_admin=${encodeURIComponent(alasan)}`;
                }
            });
        });
    });
</script>
<?php include 'includes/footer.php'; ?>