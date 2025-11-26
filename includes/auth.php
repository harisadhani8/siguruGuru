<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function redirect_user_to_dashboard()
{
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['user_role'];
        if ($role === 'super admin') header('Location: dashboard_superadmin.php');
        elseif ($role === 'admin') header('Location: dashboard_admin.php');
        elseif ($role === 'guru') header('Location: absensi_guru.php');
        elseif ($role === 'ketua kelas') header('Location: dashboard_ketua.php');
        else header('Location: logout.php'); 
        exit();
    }
}

function auth_login()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?error=2');
        exit();
    }
}

function auth_role($role_dizinkan)
{
    auth_login();
    $user_role = $_SESSION['user_role'] ?? '';

    $is_authorized = false;

    if (is_array($role_dizinkan)) {
        if (in_array($user_role, $role_dizinkan)) {
            $is_authorized = true;
        }
    } else {
        if ($user_role === $role_dizinkan) {
            $is_authorized = true;
        }
    }

    if (!$is_authorized) {
        redirect_user_to_dashboard();
    }
}
