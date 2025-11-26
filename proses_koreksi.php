<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
require_once 'includes/db.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $koreksi_id = $_GET['id'];
    $status_baru = $_GET['status'];
    $catatan_admin = null; 

    if ($status_baru == 'Ditolak') {
        if (empty($_GET['alasan_admin'])) {
            header('Location: lihat_koreksi.php?notif=20'); 
            exit();
        }
        $catatan_admin = $_GET['alasan_admin'];

        $stmt = $conn->prepare("UPDATE koreksi_absensi SET status = ?, catatan_admin = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status_baru, $catatan_admin, $koreksi_id);
    } else if ($status_baru == 'Disetujui') {
        $stmt = $conn->prepare("UPDATE koreksi_absensi SET status = ?, catatan_admin = NULL WHERE id = ?"); 
        $stmt->bind_param("si", $status_baru, $koreksi_id);

        
    } else {
        header('Location: lihat_koreksi.php');
        exit();
    }

    if ($stmt->execute()) {
        header('Location: lihat_koreksi.php?notif=19'); 
    } else {
        header('Location: lihat_koreksi.php?notif=21'); 
    }
} else {
    header('Location: lihat_koreksi.php');
}
