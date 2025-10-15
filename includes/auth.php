<?php
session_start();
require_once 'db.php';

function redirect_with_error($role, $message)
{
    $_SESSION['login_error'] = $message;
    header('Location: ../login.php?role=' . urlencode($role));
    exit();
}

if (isset($_POST['login'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND nama = ? AND role = ?");
    $stmt->bind_param("iss", $id, $nama, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        redirect_with_error($role, "Kombinasi ID, Nama, dan Role tidak ditemukan.");
    }

    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_success'] = true;

        // PETA REDIRECT (PERBAIKAN)
        $redirect_map = [
            'super admin' => '../dashboard_superadmin.php', // <-- TAMBAHKAN INI
            'admin' => '../dashboard_admin.php',
            'ketua kelas' => '../dashboard_ketua.php',
            'guru' => '../absensi_guru.php',
            // 'staff' sudah diganti menjadi 'super admin'
        ];

        header('Location: ' . ($redirect_map[$role] ?? '../index.php'));
        exit();
    } else {
        redirect_with_error($role, "Password yang Anda masukkan salah.");
    }
}
// Jika login gagal