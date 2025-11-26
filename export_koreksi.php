<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
require_once 'includes/db.php';

$filename = "export_koreksi_" . date('Ymd') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID Koreksi', 'Tgl Pengajuan', 'Tgl Absen', 'Nama Pengaju', 'Kelas', 'Nama Guru', 'Status Baru', 'Alasan', 'Status Koreksi']);

$sql = "SELECT k.id, k.created_at, k.tanggal, kk.nama as nama_ketua, c.nama_kelas, u.nama as nama_guru, k.keterangan_baru, k.alasan, k.status
        FROM koreksi_absensi k
        JOIN jadwal_mengajar j ON k.jadwal_id = j.id
        JOIN users u ON j.guru_nip = u.nip
        JOIN kelas c ON j.kelas_id = c.id
        JOIN users kk ON k.diajukan_oleh_nisn = kk.nisn
        ORDER BY k.created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
