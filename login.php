<?php
session_start();
$role = isset($_GET['role']) ? htmlspecialchars($_GET['role']) : '';
if (empty($role)) {
    header('Location: index.php');
    exit();
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="content-wrapper form-view">

    <div class="dashboard-card">
        <div class="logo-container">
            <img src="Logo_SMK_Negeri_1_Kota_Bekasi.png" alt="Logo Sekolah">
        </div>

        <h5 class="mb-4 text-uppercase"><?= $role; ?></h5>

        <form action="includes/auth.php" method="POST">
            <input type="hidden" name="role" value="<?= $role; ?>">
            <div class="mb-3"><input type="number" class="form-control" name="id" placeholder="ID" required></div>
            <div class="mb-3"><input type="text" class="form-control" name="nama" placeholder="NAMA" required></div>
            <div class="mb-3"><input type="password" class="form-control" name="password" placeholder="PASSWORD" required></div>
            <button type="submit" name="login" class="btn btn-submit">SUBMIT</button>
        </form>
    </div>
</div>

<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-icon error">❌</div>
                <p><?php if (isset($_SESSION['login_error'])) {
                        echo htmlspecialchars($_SESSION['login_error']);
                    } else {
                        echo "Data yang Anda input salah.<br>Silakan coba lagi.";
                    } ?></p>
            </div>
        </div>
    </div>
</div>
<?php if (isset($_SESSION['login_error'])): ?><script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
        });
    </script><?php unset($_SESSION['login_error']);
            endif; ?>
<?php require_once 'includes/footer.php'; ?>