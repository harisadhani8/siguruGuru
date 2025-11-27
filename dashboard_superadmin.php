<?php
require_once 'includes/auth.php';
auth_role('super admin');
$_SESSION['page_title'] = "Dashboard Super Admin";
require_once 'includes/db.php';

$tgl_ini = date('Y-m-d');

$sql_users = "SELECT COUNT(id) as total FROM users";
$total_users = $conn->query($sql_users)->fetch_assoc()['total'] ?? 0;

$sql_guru = "SELECT COUNT(id) as total FROM users WHERE role = 'guru' AND status = 'Aktif'";
$total_guru = $conn->query($sql_guru)->fetch_assoc()['total'] ?? 0;

$sql_hadir = "SELECT COUNT(id) as total 
              FROM absensi_harian 
              WHERE tanggal = ? AND status_kehadiran IN ('Hadir', 'Terlambat')";
$stmt_hadir = $conn->prepare($sql_hadir);
$stmt_hadir->bind_param("s", $tgl_ini);
$stmt_hadir->execute();
$guru_hadir = $stmt_hadir->get_result()->fetch_assoc()['total'] ?? 0;

$guru_tidak_hadir_input = 0;
$sql_tdk_hadir = "SELECT COUNT(id) as total 
                  FROM absensi_harian 
                  WHERE tanggal = ? AND status_kehadiran IN ('Izin', 'Sakit', 'Dinas Luar')";
$stmt_tdk = $conn->prepare($sql_tdk_hadir);
$stmt_tdk->bind_param("s", $tgl_ini);
$stmt_tdk->execute();
$guru_tidak_hadir_input = $stmt_tdk->get_result()->fetch_assoc()['total'] ?? 0;

$guru_belum_hadir = max(0, $total_guru - $guru_hadir); 

$sql_koreksi = "SELECT COUNT(id) as total FROM koreksi_absensi WHERE status = 'Diajukan'";
$koreksi_pending = $conn->query($sql_koreksi)->fetch_assoc()['total'] ?? 0;
?>

<?php include 'includes/header.php'; ?>

<div class="stat-container">
    <div class="stat-card">
        <h5>Total Pengguna</h5>
        <span class="stat-number"><?= $total_users; ?></span>
        <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 5px;">Akun terdaftar</div>
    </div>

    <div class="stat-card">
        <h5>Total Guru Aktif</h5>
        <span class="stat-number"><?= $total_guru; ?></span>
        <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 5px;">Data Master Guru</div>
    </div>

    <div class="stat-card" style="border-left: 5px solid var(--success);">
        <h5>Guru Hadir Hari Ini</h5>
        <span class="stat-number text-success"><?= $guru_hadir; ?></span>
        <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 5px;">Sudah Check-in</div>
    </div>

    <div class="stat-card" style="border-left: 5px solid var(--warning);">
        <h5>Koreksi Pending</h5>
        <span class="stat-number text-warning"><?= $koreksi_pending; ?></span>
        <div style="font-size: 0.85rem; color: var(--text-light); margin-top: 5px;">Perlu persetujuan</div>
    </div>
</div>

<div class="card-ui" style="margin-top: 2rem;">
    <h4>Selamat Datang, Super Admin!</h4>
    <p>Anda memiliki akses penuh ke sistem SiGuru. Gunakan menu di samping untuk:</p>
    <ul style="margin-left: 1.5rem; color: var(--text-color);">
        <li>Mengelola data master (Pengguna, Kelas, Mapel, Jadwal).</li>
        <li>Mencatat izin dinas luar guru.</li>
        <li>Melihat rekap laporan absensi harian/bulanan.</li>
        <li>Memproses pengajuan koreksi dari Ketua Kelas.</li>
    </ul>
</div>

<?php include 'includes/footer.php'; ?>