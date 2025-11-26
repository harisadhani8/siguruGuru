<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'auth.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nama = $_SESSION['user_nama'];
$user_role = $_SESSION['user_role'];
$user_foto = $_SESSION['user_foto'] ?? '';

if (!empty($user_foto) && file_exists('uploads/profil/' . $user_foto)) {
    $foto_profil_path = 'uploads/profil/' . $user_foto;
} else {
    $foto_profil_path = 'uploads/profil/default.png';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiGuru - Sistem Informasi Kehadiran Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="app-body">
    <div class="overlay" id="overlay"></div>

    <div class="wrapper">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="includes/logo.png" alt="Logo SiGuru" class="sidebar-logo logo-fix">
                <span class="sidebar-title"></span>
            </div>

            <ul class="sidebar-menu">
                <?php if ($user_role === 'super admin'): ?>
                    
                    <li><a href="dashboard_superadmin.php" class="sidebar-link"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                    <li><a href="manage_users.php" class="sidebar-link"><i class="fas fa-users me-2"></i>Data Pengguna</a></li>
                    <li><a href="manage_kelas.php" class="sidebar-link"><i class="fas fa-school me-2"></i>Data Kelas</a></li>
                    <li><a href="manage_jurusan.php" class="sidebar-link"><i class="fas fa-graduation-cap me-2"></i>Data Jurusan</a></li>
                    <li><a href="manage_mapel.php" class="sidebar-link"><i class="fas fa-book me-2"></i>Data Mapel</a></li>
                    <li><a href="manage_jadwal.php" class="sidebar-link"><i class="fas fa-calendar-alt me-2"></i>Jadwal Mengajar</a></li>
                    <li><a href="dinas_luar.php" class="sidebar-link"><i class="fas fa-briefcase me-2"></i>Input Dinas Luar</a></li>
                    <li><a href="laporan_absensi.php" class="sidebar-link"><i class="fas fa-file-alt me-2"></i>Rekap Absensi</a></li>
                    <li><a href="lihat_koreksi.php" class="sidebar-link"><i class="fas fa-check-double me-2"></i>Pengajuan Koreksi</a></li>
                <?php endif; ?>

                <?php if ($user_role === 'admin'): ?>
                    <li><a href="dashboard_admin.php" class="sidebar-link"><i class="fas fa-home me-2"></i>Dashboard Admin</a></li>
                    <li><a href="laporan_absensi.php" class="sidebar-link"><i class="fas fa-file-alt me-2"></i>Laporan Absensi</a></li>
                    <li><a href="lihat_koreksi.php" class="sidebar-link"><i class="fas fa-check-double me-2"></i>Pengajuan Koreksi</a></li>
                    <li><a href="absensi_guru.php" class="sidebar-link"><i class="fas fa-user-clock me-2"></i>Absensi (Piket)</a></li>
                <?php endif; ?>

                <?php if ($user_role === 'guru'): ?>
                    <li><a href="absensi_guru.php" class="sidebar-link"><i class="fas fa-calendar-check me-2"></i>Home / Absensi</a></li>
                <?php endif; ?>

                <?php if ($user_role === 'ketua kelas'): ?>
                    <li><a href="dashboard_ketua.php" class="sidebar-link"><i class="fas fa-chalkboard me-2"></i>Dashboard Kelas</a></li>
                    <li><a href="form_koreksi.php" class="sidebar-link"><i class="fas fa-edit me-2"></i>Ajukan Koreksi</a></li>
                <?php endif; ?>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php" class="btn btn-danger w-100">Logout</a>
            </div>
        </nav>

        <div class="main-content" id="main-content">
            <header class="header">
                <div class="header-left">
                    <button id="mobile-menu-toggle" class="mobile-menu-toggle" title="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span style="font-weight: 600; font-size: 1.1rem;"><?= htmlspecialchars($_SESSION['page_title'] ?? 'Dashboard'); ?></span>
                </div>

                <div class="header-user-info">
                    <span>Halo, <strong><?= htmlspecialchars($user_nama); ?></strong></span>

                    <img src="<?= $foto_profil_path; ?>" alt="Foto" class="header-profile-pic">

                    <button id="theme-toggle" class="theme-toggle" title="Toggle theme">
                        <span class="moon-icon"><i class="fas fa-moon"></i></span>
                        <span class="sun-icon"><i class="fas fa-sun"></i></span>
                    </button>
                </div>
            </header>

            <div class="page-content">