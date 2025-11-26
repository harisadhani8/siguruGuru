<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
$_SESSION['page_title'] = "Master Data Pengguna";
require_once 'includes/db.php';

$notif_msg = '';
$notif_type = '';
$current_role = $_SESSION['user_role'];

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) {
        header('Location: manage_users.php?notif=14');
        exit();
    } else {
        $notif_msg = "Gagal menghapus: " . $stmt->error;
        $notif_type = 'danger';
    }
}
if (isset($_GET['notif'])) {
    if ($_GET['notif'] == 12) {
        $notif_msg = "Data pengguna berhasil disimpan.";
        $notif_type = "success";
    }
    if ($_GET['notif'] == 14) {
        $notif_msg = "Data pengguna berhasil dihapus.";
        $notif_type = "success";
    }
}

$sql = "SELECT u.*, 
        (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
        j.singkatan_jurusan, k.rombel
        FROM users u 
        LEFT JOIN kelas k ON u.kelas_id = k.id
        LEFT JOIN tingkat t ON k.tingkat_id = t.id
        LEFT JOIN jurusan j ON k.jurusan_id = j.id";

if ($current_role == 'admin') {
    $sql .= " WHERE u.role IN ('guru', 'ketua kelas')";
}

$sql .= " ORDER BY u.nama ASC";

$result = $conn->query($sql);
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4>Daftar Pengguna Sistem</h4>
        <a href="edit_user.php" class="btn btn-primary">Tambah Pengguna Baru</a>
    </div>

    <div style="overflow-x: auto; margin-top: 1.5rem;">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP / NISN</th>
                    <th>Role</th>
                    <th>Detail Role / Kelas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row['nama']); ?>
                            </td>
                            <td>
                                <?php if (!empty($row['nip'])): ?>
                                    <span style="font-weight: 600;">NIP:</span> <?= htmlspecialchars($row['nip']); ?>
                                <?php elseif (!empty($row['nisn'])): ?>
                                    <span style="font-weight: 600;">NISN:</span> <?= htmlspecialchars($row['nisn']); ?>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['role']); ?></td>
                            <td>
                                <?php
                                if ($row['role'] == 'ketua kelas') {
                                    echo htmlspecialchars($row['nama_tingkat_numerik'] . ' ' . $row['singkatan_jurusan'] . ' ' . $row['rombel']);
                                } else {
                                    echo htmlspecialchars($row['role_guru']);
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td>
                                <a href="detail_user.php?id=<?= $row['id']; ?>" class="btn btn-secondary">Detail</a>
                                <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="manage_users.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-danger btn-hapus">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada data pengguna.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>