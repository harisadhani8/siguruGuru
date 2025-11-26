<?php
require_once 'includes/auth.php';
auth_role('admin'); 
$_SESSION['page_title'] = "Dashboard Admin";
require_once 'includes/db.php';

$tgl_hari_ini = date('Y-m-d');
$sql_tidak_hadir = "SELECT u.nama, a.status, a.keterangan_izin 
                    FROM absensi_log a
                    JOIN users u ON a.guru_nip = u.nip
                    WHERE a.tanggal = ? AND a.status IN ('Izin', 'Sakit', 'Absen', 'Dinas Luar')";
$stmt_tidak_hadir = $conn->prepare($sql_tidak_hadir);
$stmt_tidak_hadir->bind_param("s", $tgl_hari_ini);
$stmt_tidak_hadir->execute();
$result_tidak_hadir = $stmt_tidak_hadir->get_result();

// Stats
$total_guru = $conn->query("SELECT COUNT(id) FROM users WHERE role='guru'")->fetch_row()[0];
$total_hadir = $conn->query("SELECT COUNT(DISTINCT guru_nip) FROM absensi_log WHERE tanggal = '$tgl_hari_ini' AND status IN ('Hadir', 'Terlambat')")->fetch_row()[0];
$total_absen = $result_tidak_hadir->num_rows;
?>
<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['error']) && $_GET['error'] == 403) echo '<div class="notif notif-danger">Anda tidak punya hak akses.</div>'; ?>

<div class="card-ui">
    <h4>Statistik Hari Ini (<?= date('d M Y'); ?>)</h4>
    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <div class="card-ui" style="flex: 1; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 700;"><?= $total_guru; ?></div>
            <div>Total Guru</div>
        </div>
        <div class="card-ui" style="flex: 1; text-align: center; background-color: #d4edda;">
            <div style="font-size: 2.5rem; font-weight: 700;"><?= $total_hadir; ?></div>
            <div>Guru Hadir</div>
        </div>
        <div class="card-ui" style="flex: 1; text-align: center; background-color: #f8d7da;">
            <div style="font-size: 2.5rem; font-weight: 700;"><?= $total_absen; ?></div>
            <div>Guru Tidak Hadir</div>
        </div>
    </div>
</div>

<div class="card-ui">
    <h4>Daftar Guru Tidak Hadir Hari Ini (Poin 17)</h4>
    <table class="table" style="margin-top: 1rem;">
        <thead>
            <tr>
                <th>Nama Guru</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_tidak_hadir->num_rows > 0): ?>
                <?php while ($row = $result_tidak_hadir->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td><?= htmlspecialchars($row['keterangan_izin']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center;">Semua guru hadir atau belum ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>