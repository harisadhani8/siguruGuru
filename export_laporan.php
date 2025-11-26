<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$sql = "SELECT 
            a.tanggal,
            j.jam_mulai, 
            j.jam_selesai,
            u.nama as nama_guru, 
            m.nama_mapel, 
            (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
            ju.singkatan_jurusan, 
            k.rombel, 
            a.jam_masuk, 
            a.jam_keluar, 
            a.status, 
            a.keterangan_izin, 
            a.file_bukti
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
if (!$stmt->execute()) {
    die("Error SQL saat export: " . $stmt->error);
}
$result = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Rekap_Absensi_' . $tgl_mulai . '_sd_' . $tgl_selesai . '.csv');
$output = fopen('php://output', 'w');

fputcsv($output, array(
    'Tanggal',
    'Jam Jadwal',
    'Nama Guru',
    'Mata Pelajaran',
    'Kelas',
    'Jam Masuk',
    'Jam Keluar',
    'Status',
    'Keterangan Izin',
    'File Bukti'
));

while ($row = $result->fetch_assoc()) {
    $nama_kelas = $row['nama_tingkat_numerik'] ?
        ($row['nama_tingkat_numerik'] . ' ' . $row['singkatan_jurusan'] . ' ' . $row['rombel']) :
        '';

    $jam_jadwal = ($row['jam_mulai'] && $row['jam_selesai']) ?
        (date('H:i', strtotime($row['jam_mulai'])) . ' - ' . date('H:i', strtotime($row['jam_selesai']))) :
        '';

    $jam_masuk = $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '';
    $jam_keluar = $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '';
    
    fputcsv($output, array(
        $row['tanggal'],
        $jam_jadwal,
        $row['nama_guru'],
        $row['nama_mapel'],
        $nama_kelas,
        $jam_masuk,
        $jam_keluar,
        $row['status'],
        $row['keterangan_izin'],
        $row['file_bukti']
    ));
}

fclose($output);
exit();
