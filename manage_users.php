<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super admin') {
    header('Location: index.php');
    exit();
}
require_once 'includes/db.php';

$pesan = '';

// Logika untuk menghapus user
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    // Jangan biarkan super admin menghapus dirinya sendiri
    if ($id_hapus == $_SESSION['user_id']) {
        $_SESSION['pesan_error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id_hapus);
        if ($stmt->execute()) {
            $_SESSION['pesan_sukses'] = "Pengguna berhasil dihapus.";
        } else {
            $_SESSION['pesan_error'] = "Gagal menghapus pengguna.";
        }
        $stmt->close();
    }
    header('Location: manage_users.php');
    exit();
}

if (isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success'>" . $_SESSION['pesan_sukses'] . "</div>";
    unset($_SESSION['pesan_sukses']);
}
if (isset($_SESSION['pesan_error'])) {
    $pesan = "<div class='alert alert-danger'>" . $_SESSION['pesan_error'] . "</div>";
    unset($_SESSION['pesan_error']);
}

// Ambil semua data user
$users = $conn->query("SELECT id, nama, role FROM users ORDER BY role, nama");
?>
<?php require_once 'includes/header.php'; ?>
<div class="content-wrapper full-width">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Kelola Akun Pengguna</h5>
        <a href="edit_user.php" class="btn btn-success">Tambah Pengguna Baru</a>
    </div>

    <?= $pesan; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['nama']); ?></td>
                        <td><?= htmlspecialchars($row['role']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <?php if ($row['id'] != $_SESSION['user_id']): // Tombol hapus tidak muncul untuk diri sendiri 
                            ?>
                                <a href="manage_users.php?hapus=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat diurungkan.');">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <a href="dashboard_superadmin.php" class="btn btn-secondary role-button mt-3" style="max-width: 200px;">KEMBALI</a>
</div>
<?php require_once 'includes/footer.php'; ?>