<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
require_once 'includes/db.php';

$tgl_hari_ini = date('Y-m-d');
$total_guru = $conn->query("SELECT COUNT(id) FROM users WHERE role='guru'")->fetch_row()[0];
$total_hadir = $conn->query("SELECT COUNT(DISTINCT guru_nip) FROM absensi_log WHERE tanggal = '$tgl_hari_ini' AND status IN ('Hadir', 'Terlambat')")->fetch_row()[0];
$total_absen = $conn->query("SELECT COUNT(DISTINCT guru_nip) FROM absensi_log WHERE tanggal = '$tgl_ini' AND status IN ('Izin', 'Sakit', 'Absen', 'Dinas Luar')")->fetch_row()[0];

$filename = "export_dashboard_" . date('Ymd') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, ['Data', 'Jumlah']);
fputcsv($output, ['Total Guru', $total_guru]);
fputcsv($output, ['Guru Hadir Hari Ini', $total_hadir]);
fputcsv($output, ['Guru Tidak Hadir Hari Ini', $total_absen]);
fclose($output);
exit();
