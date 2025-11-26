<?php
require_once 'includes/auth.php';
cegah_login_ganda(); // Poin 8
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        header('Location: index.php?error=1');
        exit();
    }

    $sql = "SELECT * FROM users WHERE (nip = ? OR nisn = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'Non-Aktif') {
                header('Location: index.php?error=3');
                exit();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nip'] = $user['nip'];
            $_SESSION['user_nisn'] = $user['nisn'];
            $_SESSION['kelas_id'] = $user['kelas_id'];

            cegah_login_ganda(); 
        } else {
            header('Location: index.php?error=1');
            exit();
        }
    } else {
        header('Location: index.php?error=1');
        exit();
    }
}
