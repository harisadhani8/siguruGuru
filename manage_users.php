<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']);
$_SESSION['page_title'] = "Master Data Pengguna";
require_once 'includes/db.php';

$notif_msg = '';
$notif_type = '';
$current_role = $_SESSION['user_role'];

$search = $_GET['search'] ?? '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];

    $stmt_cek = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_cek->bind_param("i", $id_hapus);
    $stmt_cek->execute();
    $data_target = $stmt_cek->get_result()->fetch_assoc();

    if ($current_role == 'admin' && in_array($data_target['role'], ['super admin', 'admin'])) {
        $notif_msg = "Anda tidak memiliki hak akses untuk menghapus pengguna ini.";
        $notif_type = 'danger';
    } else {
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
        LEFT JOIN jurusan j ON k.jurusan_id = j.id
        WHERE 1=1";

if ($current_role == 'admin') {
    $sql .= " AND u.role IN ('guru', 'ketua kelas')";
}

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (u.nama LIKE ? OR u.nip LIKE ? OR u.nisn LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param; // Untuk nama
    $params[] = $search_param; // Untuk nip
    $params[] = $search_param; // Untuk nisn
    $types .= "sss";
}

$sql .= " ORDER BY u.nama ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
        <h4 style="margin: 0;">Daftar Pengguna Sistem</h4>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <form method="GET" style="display: flex; gap: 5px;">
                <input type="text" name="search" class="form-control" placeholder="Cari Nama / NIP..." value="<?= htmlspecialchars($search); ?>" style="width: 200px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                <?php if (!empty($search)): ?>
                    <a href="manage_users.php" class="btn btn-secondary" title="Reset Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
            <a href="export_users.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Export</a>
            <a href="edit_user.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</a>
        </div>
    </div>

    <div style="overflow-x: auto;">
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
                                    <small class="text-muted">NIP:</small><br><?= htmlspecialchars($row['nip']); ?>
                                <?php elseif (!empty($row['nisn'])): ?>
                                    <small class="text-muted">NISN:</small><br><?= htmlspecialchars($row['nisn']); ?>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['role']); ?></span></td>
                            <td>
                                <?php
                                if ($row['role'] == 'ketua kelas') {
                                    echo htmlspecialchars($row['nama_tingkat_numerik'] . ' ' . $row['singkatan_jurusan'] . ' ' . $row['rombel']);
                                } else {
                                    echo htmlspecialchars($row['role_guru']);
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'Aktif'): ?>
                                    <span class="text-success fw-bold">Aktif</span>
                                <?php else: ?>
                                    <span class="text-danger fw-bold">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="detail_user.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-secondary" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="edit_user.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="manage_users.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-sm btn-danger btn-hapus" title="Hapus"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">Data pengguna tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>