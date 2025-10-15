<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super admin'])) {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $koreksi_id = (int)$_GET['id'];
    $aksi = $_GET['aksi'];

    if ($aksi == 'setujui') {
    } elseif ($aksi == 'tolak') {
    }

    header('Location: lihat_koreksi.php');
    exit();
}

?>
<?php require_once 'includes/header.php'; ?>

<div style="padding: 30px 25px;">
    <h5 class="fw-bold mb-3 text-start">Daftar Pengajuan Koreksi</h5>
</div>

<div class="content-wrapper full-width" style="padding-top: 0;">
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
        </table>
    </div>

    <?php $dashboard_link = ($_SESSION['user_role'] == 'super admin') ? 'dashboard_superadmin.php' : 'dashboard_admin.php'; ?>
    <a href="<?= $dashboard_link; ?>" class="btn btn-secondary role-button mt-3" style="max-width: 200px;">KEMBALI KE DASHBOARD</a>
</div>

<?php require_once 'includes/footer.php'; ?>