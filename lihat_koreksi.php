<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super admin'])) {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

// --- BLOK LOGIKA PEMROSESAN AKSI ---
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $koreksi_id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi == 'setujui') {
        $stmt_update_koreksi = $conn->prepare("UPDATE koreksi_absensi SET status = 'Disetujui' WHERE id = ?");
        $stmt_update_koreksi->bind_param("i", $koreksi_id);
        $stmt_update_koreksi->execute();

        $stmt_get_absensi = $conn->prepare("SELECT absensi_id FROM koreksi_absensi WHERE id = ?");
        $stmt_get_absensi->bind_param("i", $koreksi_id);
        $stmt_get_absensi->execute();
        $absensi_id = $stmt_get_absensi->get_result()->fetch_assoc()['absensi_id'];

        if ($absensi_id) {
            $stmt_update_absensi = $conn->prepare("UPDATE absensi SET keterangan = 'Hadir', catatan = 'Dikoreksi oleh admin' WHERE id = ?");
            $stmt_update_absensi->bind_param("i", $absensi_id);
            $stmt_update_absensi->execute();
        }
    } elseif ($aksi == 'tolak') {
        $stmt_update_koreksi = $conn->prepare("UPDATE koreksi_absensi SET status = 'Ditolak' WHERE id = ?");
        $stmt_update_koreksi->bind_param("i", $koreksi_id);
        $stmt_update_koreksi->execute();
    }

    header('Location: lihat_koreksi.php');
    exit();
}
$query = "SELECT k.id, u.nama as nama_guru, a.tanggal, a.keterangan as ket_awal, k.alasan_koreksi, k.status 
          FROM koreksi_absensi k
          JOIN absensi a ON k.absensi_id = a.id
          JOIN users u ON a.user_id = u.id
          ORDER BY k.tanggal_pengajuan DESC";
$result_koreksi = $conn->query($query);
?>
<?php require_once 'includes/header.php'; ?>

<div style="padding: 30px 25px;">
    <h5 class="fw-bold mb-3 text-start">Daftar Pengajuan Koreksi</h5>
</div>

<div class="content-wrapper full-width" style="padding-top: 0;">
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Guru</th>
                    <th>Tanggal</th>
                    <th>Ket. Awal</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_koreksi->num_rows > 0): ?>
                    <?php while ($row = $result_koreksi->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_guru']); ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                            <td><?= htmlspecialchars($row['ket_awal']); ?></td>
                            <td><?= htmlspecialchars($row['alasan_koreksi']); ?></td>
                            <td><span class="badge <?php if ($row['status'] == 'Disetujui') echo 'bg-success';
                                                    elseif ($row['status'] == 'Ditolak') echo 'bg-danger';
                                                    else echo 'bg-warning'; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <?php if ($row['status'] == 'Diajukan'): ?>
                                    <a href="lihat_koreksi.php?aksi=setujui&id=<?= $row['id']; ?>" class="btn btn-sm btn-success">✓</a>
                                    <a href="lihat_koreksi.php?aksi=tolak&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger">✗</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada pengajuan koreksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php $dashboard_link = ($_SESSION['user_role'] == 'super admin') ? 'dashboard_superadmin.php' : 'dashboard_admin.php'; ?>
    <a href="<?= $dashboard_link; ?>" class="btn btn-secondary role-button mt-3" style="max-width: 200px;">KEMBALI KE DASHBOARD</a>
</div>

<?php require_once 'includes/footer.php'; ?>