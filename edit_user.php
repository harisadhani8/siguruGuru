<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super admin') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

$is_edit = isset($_GET['id']) && !empty($_GET['id']);
$user_id = '';
$nama = '';
$role = '';
$page_title = 'Tambah Pengguna Baru';

if ($is_edit) {
    $user_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT nama, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $nama = $user['nama'];
        $role = $user['role'];
        $page_title = 'Edit Pengguna';
    } else {
        // Jika ID tidak ditemukan, alihkan
        header('Location: manage_users.php');
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_post = $_POST['nama'];
    $role_post = $_POST['role'];
    $password_post = $_POST['password'];

    if ($is_edit) {
        // Logika UPDATE
        $user_id_post = (int)$_POST['id'];
        if (!empty($password_post)) {
            // Jika password diisi, update password
            $hashed_password = password_hash($password_post, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nama = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama_post, $role_post, $hashed_password, $user_id_post);
        } else {
            // Jika password kosong, jangan update password
            $stmt = $conn->prepare("UPDATE users SET nama = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama_post, $role_post, $user_id_post);
        }
        $_SESSION['pesan_sukses'] = "Pengguna berhasil diperbarui.";
    } else {
        // Logika INSERT
        if (empty($password_post)) {
            $error = "Password wajib diisi untuk pengguna baru.";
        } else {
            $hashed_password = password_hash($password_post, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama_post, $hashed_password, $role_post);
            $_SESSION['pesan_sukses'] = "Pengguna baru berhasil ditambahkan.";
        }
    }

    if (empty($error) && $stmt->execute()) {
        header('Location: manage_users.php');
        exit();
    } else {
        $error = $error ?? "Terjadi kesalahan pada database.";
    }
}
?>
<?php require_once 'includes/header.php'; ?>
<div class="content-wrapper">
    <h5 class="fw-bold mb-3 text-start"><?= $page_title ?></h5>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($user_id); ?>">
        <?php endif; ?>
        <div class="dashboard-card">
            <div class="mb-3 text-start">
                <label for="nama" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control text-start" value="<?= htmlspecialchars($nama); ?>" required>
            </div>
            <div class="mb-3 text-start">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <select id="role" name="role" class="form-select form-control" required>
                    <option value="super admin" <?= ($role == 'super admin') ? 'selected' : '' ?>>Super Admin</option>
                    <option value="admin" <?= ($role == 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="guru" <?= ($role == 'guru') ? 'selected' : '' ?>>Guru</option>
                    <option value="ketua kelas" <?= ($role == 'ketua kelas') ? 'selected' : '' ?>>Ketua Kelas</option>
                </select>
            </div>
            <div class="mb-3 text-start">
                <label for="password" class="form-label">Password <?php if (!$is_edit) echo '<span class="text-danger">*</span>'; ?></label>
                <input type="password" id="password" name="password" class="form-control text-start" <?php if (!$is_edit) echo 'required'; ?>>
                <?php if ($is_edit): ?>
                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                <?php endif; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-submit mt-3">Simpan</button>
        <a href="manage_users.php" class="btn btn-secondary role-button mt-2">Batal</a>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>