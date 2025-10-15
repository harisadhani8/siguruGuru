<?php
session_start();
// Memastikan hanya super admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super admin') {
    header('Location: index.php');
    exit();
}
?>
<?php require_once 'includes/header.php'; ?>

<div style="padding: 30px 25px;">
    <h4 class="fw-bold mb-3 text-start">Halo, <?= htmlspecialchars($_SESSION['user_nama']); ?></h4>
    <h5 class="fw-bold mb-3 text-start">Dashboard</h5>
</div>

<div class="content-wrapper form-view" style="padding-top: 0;">
    <div class="d-grid gap-2 mt-4">
        <a href="manage_users.php" class="btn btn-action role-button">Kelola Akun Pengguna</a>
        <a href="logout.php" class="btn btn-logout role-button">LOGOUT</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>