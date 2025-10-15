<?php require_once 'includes/header.php'; ?>

<div class="content-wrapper form-view">

    <div class="dashboard-card">
        <div class="logo-container">
            <img src="Logo_SMK_Negeri_1_Kota_Bekasi.png" alt="Logo Sekolah">
        </div>
        <div class="d-grid gap-2">
            <a href="login.php?role=super admin" class="btn role-button">SUPER ADMIN</a>
            <a href="login.php?role=admin" class="btn role-button">ADMIN</a>
            <a href="login.php?role=guru" class="btn role-button">GURU</a>
            <a href="login.php?role=ketua kelas" class="btn role-button">KETUA KELAS</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>