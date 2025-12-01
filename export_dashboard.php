<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
require_once 'includes/db.php';

$tgl_hari_ini = date('Y-m-d');

$total_guru = $conn->query("SELECT COUNT(id) FROM users WHERE role='guru' AND status='Aktif'")->fetch_row()[0];

$stmt_hadir = $conn->prepare("SELECT COUNT(id) FROM absensi_harian WHERE tanggal = ? AND status_kehadiran IN ('Hadir', 'Terlambat')");
$stmt_hadir->bind_param("s", $tgl_hari_ini);
$stmt_hadir->execute();
$total_hadir = $stmt_hadir->get_result()->fetch_row()[0];

$stmt_absen = $conn->prepare("SELECT COUNT(id) FROM absensi_harian WHERE tanggal = ? AND status_kehadiran IN ('Izin', 'Sakit', 'Dinas Luar', 'Alpha')");
$stmt_absen->bind_param("s", $tgl_hari_ini);
$stmt_absen->execute();
$total_absen_input = $stmt_absen->get_result()->fetch_row()[0];

$total_tidak_hadir_real = $total_guru - $total_hadir;

$filename = "export_dashboard_" . date('Ymd') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Kategori Data', 'Jumlah']);

fputcsv($output, ['Total Guru Aktif', $total_guru]);
fputcsv($output, ['Guru Hadir Hari Ini', $total_hadir]);
fputcsv($output, ['Guru Belum Hadir / Izin / Sakit', $total_tidak_hadir_real]);

fclose($output);
exit();
?>