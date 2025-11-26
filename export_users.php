<?php
require_once 'includes/auth.php';
auth_role('super admin');
require_once 'includes/db.php';

$filename = "export_pengguna_" . date('Ymd') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Nama', 'NIP', 'NISN', 'Role', 'Detail Role', 'ID Kelas', 'Status']);

$sql = "SELECT id, nama, nip, nisn, role, role_guru, kelas_id, status FROM users ORDER BY nama ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
