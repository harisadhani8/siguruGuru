<?php
require_once 'includes/auth.php';
auth_role('admin');
$_SESSION['page_title'] = "Dashboard Admin";
require_once 'includes/db.php';

$tgl_ini = date('Y-m-d');

// Hitung Guru
$sql_guru = "SELECT COUNT(id) as total FROM users WHERE role = 'guru' AND status = 'Aktif'";
$total_guru = $conn->query($sql_guru)->fetch_assoc()['total'] ?? 0;

// Guru Hadir (absensi_harian)
$sql_hadir = "SELECT COUNT(id) as total 
              FROM absensi_harian 
              WHERE tanggal = ? AND status_kehadiran IN ('Hadir', 'Terlambat')";
$stmt_hadir = $conn->prepare($sql_hadir);
$stmt_hadir->bind_param("s", $tgl_ini);
$stmt_hadir->execute();
$guru_hadir = $stmt_hadir->get_result()->fetch_assoc()['total'] ?? 0;

// Guru Belum Hadir
$guru_belum_hadir = max(0, $total_guru - $guru_hadir);

// Koreksi Pending
$sql_koreksi = "SELECT COUNT(id) as total FROM koreksi_absensi WHERE status = 'Diajukan'";
$koreksi_pending = $conn->query($sql_koreksi)->fetch_assoc()['total'] ?? 0;

// Data Guru Tidak Hadir (Izin/Sakit/Dinas)
$sql_list_tdk = "SELECT u.nama, a.status_kehadiran, a.keterangan 
                 FROM absensi_harian a
                 JOIN users u ON a.guru_nip = u.nip
                 WHERE a.tanggal = ? AND a.status_kehadiran IN ('Izin', 'Sakit', 'Dinas Luar')
                 ORDER BY u.nama ASC";
$stmt_list = $conn->prepare($sql_list_tdk);
$stmt_list->bind_param("s", $tgl_ini);
$stmt_list->execute();
$result_tidak_hadir = $stmt_list->get_result();
?>
<meta http-equiv="refresh" content="30">
<?php include 'includes/header.php'; ?>

<div class="card-ui" style="margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h4 style="margin: 0;">Statistik Ringkas</h4>
        <a href="export_dashboard.php" class="btn btn-success" style="display: flex; align-items: center; gap: 8px;">
            Export Data
        </a>
    </div>
</div>

<div class="stat-container">
    <div class="stat-card">
        <h5>Total Guru</h5>
        <span class="stat-number"><?= $total_guru; ?></span>
    </div>

    <div class="stat-card" style="border-left: 5px solid var(--success);">
        <h5>Guru Hadir</h5>
        <span class="stat-number text-success"><?= $guru_hadir; ?></span>
    </div>

    <div class="stat-card" style="border-left: 5px solid var(--danger);">
        <h5>Belum Hadir/Absen</h5>
        <span class="stat-number text-danger"><?= $guru_belum_hadir; ?></span>
    </div>

    <div class="stat-card" style="border-left: 5px solid var(--warning);">
        <h5>Koreksi Pending</h5>
        <span class="stat-number text-warning"><?= $koreksi_pending; ?></span>
    </div>
</div>

<div class="card-ui" style="margin-top: 2rem;">
    <h4>Daftar Guru Tidak Hadir Hari Ini (Izin/Sakit)</h4>
    <div class="table-responsive">
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
                            <td>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status_kehadiran']); ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['keterangan']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">Belum ada data guru izin/sakit hari ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>