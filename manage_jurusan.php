<?php
require_once 'includes/auth.php';
auth_role('super admin'); 
$_SESSION['page_title'] = "Master Data Jurusan";
require_once 'includes/db.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];

    $stmt_cek = $conn->prepare("SELECT COUNT(*) FROM kelas WHERE jurusan_id = ?");
    $stmt_cek->bind_param("i", $id_hapus);
    $stmt_cek->execute();
    $terpakai = $stmt_cek->get_result()->fetch_row()[0];

    if ($terpakai > 0) {
        header('Location: manage_jurusan.php?notif=error_delete');
        exit();
    } else {
        $stmt = $conn->prepare("DELETE FROM jurusan WHERE id = ?");
        $stmt->bind_param("i", $id_hapus);
        if ($stmt->execute()) {
            header('Location: manage_jurusan.php?notif=deleted');
            exit();
        }
    }
}

$sql = "SELECT * FROM jurusan ORDER BY nama_jurusan ASC";
$result = $conn->query($sql);
$i = 1;
?>
<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['notif'])): ?>
    <?php if ($_GET['notif'] == 'saved'): ?>
        <div class="notif notif-success notif-autohide">Data jurusan berhasil disimpan.</div>
    <?php elseif ($_GET['notif'] == 'deleted'): ?>
        <div class="notif notif-success notif-autohide">Data jurusan berhasil dihapus.</div>
    <?php elseif ($_GET['notif'] == 'error_delete'): ?>
        <div class="notif notif-danger">Gagal hapus! Jurusan ini masih digunakan oleh data Kelas.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="card-ui">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4>Daftar Jurusan SMK</h4>
        <a href="edit_jurusan.php" class="btn btn-primary">Tambah Jurusan Baru</a>
    </div>

    <div style="overflow-x: auto; margin-top: 1.5rem;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Jurusan Lengkap</th>
                    <th>Singkatan (Kode)</th>
                    <th style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($row['nama_jurusan']); ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($row['singkatan_jurusan']); ?></span></td>
                            <td>
                                <a href="edit_jurusan.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="manage_jurusan.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-danger btn-sm btn-hapus">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Belum ada data jurusan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>