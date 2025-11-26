<?php
require_once 'includes/auth.php'; 
require_once 'includes/db.php';
$notif_msg = '';

redirect_user_to_dashboard();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier']; 
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE (nip = ? OR nisn = ?) AND status = 'Aktif'");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nip'] = $user['nip'];
            $_SESSION['user_nisn'] = $user['nisn'];
            $_SESSION['kelas_id'] = $user['kelas_id'];
            $_SESSION['user_foto'] = $user['foto']; 
            if ($user['role'] == 'super admin') header('Location: dashboard_superadmin.php');
            elseif ($user['role'] == 'admin') header('Location: dashboard_admin.php');
            elseif ($user['role'] == 'guru') header('Location: absensi_guru.php');
            elseif ($user['role'] == 'ketua kelas') header('Location: dashboard_ketua.php');
            else header('Location: logout.php');
            exit();
        } else {
            $notif_msg = "NIP/NISN atau Password salah!";
        }
    } else {
        $notif_msg = "NIP/NISN atau Password salah!";
    }
}
if (isset($_GET['error']) && $_GET['error'] == 1) $notif_msg = "NIP/NISN atau Password salah!";
if (isset($_GET['error']) && $_GET['error'] == 2) $notif_msg = "Anda harus login terlebih dahulu.";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SiGuru</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    <button id="theme-toggle" class="theme-toggle" title="Toggle theme" style="position: absolute; top: 1.5rem; right: 1.5rem; font-size: 1.5rem; z-index: 100;">
        <span class="moon-icon"><i class="fas fa-moon"></i></span>
        <span class="sun-icon"><i class="fas fa-sun"></i></span>
    </button>

    <div class="login-wrapper">
        <div class="login-box card-ui">

            <img src="includes/logo.png" alt="Logo SiGuru" class="login-logo logo-fix">
            <h3 style="text-align: center; margin-bottom: 1.5rem;">SiGuru - Sistem Informasi Kehadiran Guru</h3>

            <?php if (!empty($notif_msg)): ?>
                <div class="notif notif-danger"><?= $notif_msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">NIP / NISN</label>
                    <input type="text" class="form-control" name="identifier" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>