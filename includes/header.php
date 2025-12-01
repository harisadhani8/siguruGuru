<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiGuru - Sistem Informasi Kehadiran Guru</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-header">
                <img src="includes/logo.png" alt="Logo SiGuru" class="sidebar-logo logo-fix">
                <span class="sidebar-title"></span>
            </div>

            <ul class="sidebar-menu">
                <?php $role = $_SESSION['user_role'] ?? ''; ?>

                <?php if ($role === 'super admin'): ?>
                    <li><a href="dashboard_superadmin.php">Dashboard</a></li>
                    <li><a href="manage_users.php">Master Data Pengguna</a></li>
                    <li><a href="manage_mapel.php">Master Mata Pelajaran</a></li>
                    <li><a href="manage_jadwal.php">Master Jadwal Mengajar</a></li>
                    <li><a href="dinas_luar.php">Input Dinas Luar</a></li>
                    <li><a href="laporan_absensi.php">Rekap Absensi</a></li>
                    <li><a href="lihat_koreksi.php">Pengajuan Koreksi</a></li>
                <?php endif; ?>

                <?php if ($role === 'admin'): ?>
                    <li><a href="dashboard_admin.php">Dashboard Admin</a></li>
                    <li><a href="laporan_absensi.php">Rekap Absensi</a></li>
                    <li><a href="lihat_koreksi.php">Pengajuan Koreksi</a></li>
                <?php endif; ?>

                <?php if ($role === 'guru'): ?>
                    <li><a href="absensi_guru.php">Home / Absensi</a></li>
                <?php endif; ?>

                <?php if ($role === 'ketua kelas'): ?>
                    <li><a href="dashboard_ketua.php">Dashboard Kelas</a></li>
                    <li><a href="form_koreksi.php">Ajukan Koreksi</a></li>
                <?php endif; ?>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </nav>

        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <button id="mobile-menu-toggle" class="mobile-menu-toggle" title="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="header-title-text"><?= htmlspecialchars($_SESSION['page_title'] ?? 'Dashboard'); ?></span>
                </div>

                <div class="header-user-info">
                    <span>Halo, <strong><?= htmlspecialchars($_SESSION['user_nama'] ?? 'Tamu'); ?></strong></span>

                    <?php if (!empty($_SESSION['user_foto'])): ?>
                        <img src="uploads/foto_profil/<?= htmlspecialchars($_SESSION['user_foto']); ?>" alt="Foto" class="header-profile-pic">
                    <?php else: ?>
                        <div class="header-profile-pic" style="background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; color: #555;">
                            <?= strtoupper(substr($_SESSION['user_nama'] ?? 'U', 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <button id="theme-toggle" class="theme-toggle" title="Toggle theme">
                        <span class="moon-icon"><i class="fas fa-moon"></i></span>
                        <span class="sun-icon"><i class="fas fa-sun"></i></span>
                    </button>
                </div>
            </header>

            <div class="page-content">