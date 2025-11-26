<?php
require_once 'includes/auth.php';
auth_role('super admin');
$_SESSION['page_title'] = "Master Data Tingkat";
require_once 'includes/db.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
    $stmt_cek = $conn->prepare("SELECT COUNT(*) as total FROM kelas WHERE tingkat_id = ?");
    $stmt_cek->bind_param("i", $id_hapus);
    $stmt_cek->execute();
    $hasil_cek = $stmt_cek->get_result()->fetch_assoc();

    if ($hasil_cek['total'] > 0) {
        header('Location: manage_tingkat.php?notif=3');
    } else {
        $stmt_hapus = $conn->prepare("DELETE FROM tingkat WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_hapus);
        if ($stmt_hapus->execute()) {
            header('Location: manage_tingkat.php?notif=1');
            exit(); // Berhasil hapus
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['notif'])): ?>
    <?php if ($_GET['notif'] == 1): ?>
        <div class="notif notif-success notif-autohide">Data tingkat berhasil dihapus.</div>
    <?php elseif ($_GET['notif'] == 2): ?>
        <div class="notif notif-success notif-autohide">Data tingkat berhasil disimpan.</div>
    <?php elseif ($_GET['notif'] == 3): ?>
        <div class="notif notif-danger">Gagal menghapus: Tingkat ini sedang digunakan oleh data kelas.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="card-ui">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4>Daftar Tingkat</h4>
        <a href="edit_tingkat.php" class="btn btn-primary">Tambah Tingkat Baru</a>
    </div>

    <div style="overflow-x: auto; margin-top: 1.5rem; max-width: 500px;">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Tingkat (Contoh: X, XI)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM tingkat ORDER BY nama_tingkat");
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_tingkat']); ?></td>
                            <td>
                                <a href="edit_tingkat.php?id=<?= $row['id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="manage_tingkat.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-danger btn-hapus">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center;">Belum ada data tingkat.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>