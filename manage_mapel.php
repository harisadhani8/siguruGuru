<?php
require_once 'includes/auth.php';
auth_role('super admin');
$_SESSION['page_title'] = "Mata Pelajaran";
require_once 'includes/db.php';

$notif_msg = '';
$notif_type = '';
$is_edit_mode = false;
$mapel_data = ['id' => '', 'nama_mapel' => ''];

if (isset($_POST['submit_mapel'])) {
    $id = $_POST['id'];
    $nama_mapel = $_POST['nama_mapel'];
    if (empty($nama_mapel)) {
        $notif_msg = "Nama mata pelajaran tidak boleh kosong.";
        $notif_type = "danger";
    } else {
        if (empty($id)) { 
            $stmt = $conn->prepare("INSERT INTO mata_pelajaran (nama_mapel) VALUES (?)");
            $stmt->bind_param("s", $nama_mapel);
        } else { 
            $stmt = $conn->prepare("UPDATE mata_pelajaran SET nama_mapel = ? WHERE id = ?");
            $stmt->bind_param("si", $nama_mapel, $id);
        }

        if ($stmt->execute()) {
            $notif_msg = "Data mata pelajaran berhasil disimpan.";
            $notif_type = "success";
        } else {
            $notif_msg = "Gagal menyimpan. Kemungkinan nama mapel sudah ada.";
            $notif_type = "danger";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) {
        $notif_msg = "Data berhasil dihapus.";
        $notif_type = "success";
    } else {
        $notif_msg = "Gagal menghapus. Data mungkin terkait dengan jadwal.";
        $notif_type = "danger";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_edit = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM mata_pelajaran WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $mapel_data = $stmt->get_result()->fetch_assoc();
    $is_edit_mode = true;
}

$result = $conn->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel ASC");
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui" style="max-width: 600px; margin-bottom: 2rem;">
    <h4><?= $is_edit_mode ? 'Edit' : 'Tambah'; ?> Mata Pelajaran</h4>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($mapel_data['id']); ?>">
        <div class="form-group">
            <label class="form-label">Nama Mata Pelajaran</label>
            <input type="text" class="form-control" name="nama_mapel" value="<?= htmlspecialchars($mapel_data['nama_mapel']); ?>" required>
        </div>
        <button type="submit" name="submit_mapel" class="btn btn-primary">Simpan</button>
        <?php if ($is_edit_mode): ?>
            <a href="manage_mapel.php" class="btn btn-secondary">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card-ui">
    <h4>Daftar Mata Pelajaran</h4>
    <table class="table" style="margin-top: 1rem;">
        <thead>
            <tr>
                <th>Nama Mata Pelajaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_mapel']); ?></td>
                    <td>
                        <a href="manage_mapel.php?action=edit&id=<?= $row['id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="manage_mapel.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-danger btn-hapus">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>