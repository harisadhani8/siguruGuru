<?php
require_once 'includes/auth.php';
auth_role('super admin'); 
$_SESSION['page_title'] = "Master Data Kelas";
require_once 'includes/db.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM kelas WHERE id = ?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) {
        header('Location: manage_kelas.php?notif=1');
        exit();
    }
}

$sql = "SELECT k.*, t.nama_tingkat, j.nama_jurusan, j.singkatan_jurusan
        FROM kelas k
        LEFT JOIN tingkat t ON k.tingkat_id = t.id
        LEFT JOIN jurusan j ON k.jurusan_id = j.id
        ORDER BY t.nama_tingkat, j.nama_jurusan, k.rombel";
$result = $conn->query($sql);
?>
<?php include 'includes/header.php'; ?>

<?php if (isset($_GET['notif']) && $_GET['notif'] == 1): ?>
    <div class="notif notif-success notif-autohide">Data kelas berhasil dihapus.</div>
<?php endif; ?>
<?php if (isset($_GET['notif']) && $_GET['notif'] == 2): ?>
    <div class="notif notif-success notif-autohide">Data kelas berhasil disimpan.</div>
<?php endif; ?>

<div class="card-ui">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4>Daftar Kelas</h4>
        <a href="edit_kelas.php" class="btn btn-primary">Tambah Kelas Baru</a>
    </div>

    <div style="overflow-x: auto; margin-top: 1.5rem;">
        <table class="table">
            <thead>
                <tr>
                    <th>Tingkat</th>
                    <th>Jurusan</th>
                    <th>Rombel</th>
                    <th>Nama Kelas (Lengkap)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $tingkat_nama = $row['nama_tingkat'] ?? '-';
                        $jurusan_nama = $row['nama_jurusan'] ?? '-';
                        $jurusan_singkatan = $row['singkatan_jurusan'] ?? '-';
                        $rombel = $row['rombel'] ?? '';

                        $tingkat_angka = $tingkat_nama;
                        if ($tingkat_nama == 'X') $tingkat_angka = '10';
                        elseif ($tingkat_nama == 'XI') $tingkat_angka = '11';
                        elseif ($tingkat_nama == 'XII') $tingkat_angka = '12';

                        $nama_kelas_lengkap = $tingkat_angka . ' ' . $jurusan_singkatan . ' ' . $rombel;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($tingkat_nama); ?></td>
                            <td><?= htmlspecialchars($jurusan_nama); ?></td>
                            <td><?= htmlspecialchars($rombel); ?></td>
                            <td><strong><?= htmlspecialchars($nama_kelas_lengkap); ?></strong></td>
                            <td>
                                <a href="edit_kelas.php?id=<?= $row['id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="manage_kelas.php?action=delete&id=<?= $row['id']; ?>" class="btn btn-danger btn-hapus">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Belum ada data kelas. Silakan tambah baru.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>