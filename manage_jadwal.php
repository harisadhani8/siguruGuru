<?php
require_once 'includes/auth.php';
auth_role('super admin');
$_SESSION['page_title'] = "Jadwal Mengajar";
require_once 'includes/db.php';

$notif_msg = '';
$notif_type = '';
$is_edit_mode = false;
$jadwal_data = ['id' => '', 'guru_nip' => '', 'kelas_id' => '', 'mapel_id' => '', 'hari' => '', 'jam_mulai' => '', 'jam_selesai' => ''];

if (isset($_POST['submit_jadwal'])) {
    $id = $_POST['id'];
    $guru_nip = $_POST['guru_nip'];
    $kelas_id = $_POST['kelas_id'];
    $mapel_id = $_POST['mapel_id'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO jadwal_mengajar (guru_nip, kelas_id, mapel_id, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisss", $guru_nip, $kelas_id, $mapel_id, $hari, $jam_mulai, $jam_selesai);
    } else {
        $stmt = $conn->prepare("UPDATE jadwal_mengajar SET guru_nip=?, kelas_id=?, mapel_id=?, hari=?, jam_mulai=?, jam_selesai=? WHERE id=?");
        $stmt->bind_param("siisssi", $guru_nip, $kelas_id, $mapel_id, $hari, $jam_mulai, $jam_selesai, $id);
    }
    if ($stmt->execute()) {
        $notif_msg = "Jadwal berhasil disimpan.";
        $notif_type = "success";
    } else {
        $notif_msg = "Gagal menyimpan: " . $stmt->error;
        $notif_type = "danger";
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM jadwal_mengajar WHERE id = ?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) {
        $notif_msg = "Jadwal berhasil dihapus.";
        $notif_type = "success";
    } else {
        $notif_msg = "Gagal menghapus.";
        $notif_type = "danger";
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_edit = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM jadwal_mengajar WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $jadwal_data = $stmt->get_result()->fetch_assoc();
    $is_edit_mode = true;
}

$result_guru = $conn->query("SELECT nip, nama FROM users WHERE role = 'guru' ORDER BY nama ASC");
$result_kelas = $conn->query("SELECT k.id, 
                             (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
                             j.singkatan_jurusan, k.rombel 
                             FROM kelas k
                             JOIN tingkat t ON k.tingkat_id = t.id
                             JOIN jurusan j ON k.jurusan_id = j.id
                             ORDER BY t.nama_tingkat, j.singkatan_jurusan, k.rombel");
$result_mapel = $conn->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel ASC");
$hari_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

$sql_jadwal = "SELECT j.*, u.nama as nama_guru, 
               (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
               ju.singkatan_jurusan, k.rombel, m.nama_mapel
               FROM jadwal_mengajar j
               JOIN users u ON j.guru_nip = u.nip
               JOIN kelas k ON j.kelas_id = k.id
               JOIN tingkat t ON k.tingkat_id = t.id
               JOIN jurusan ju ON k.jurusan_id = ju.id
               JOIN mata_pelajaran m ON j.mapel_id = m.id
               ORDER BY j.hari, j.jam_mulai";
$result_jadwal = $conn->query($sql_jadwal);
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui" style="margin-bottom: 2rem;">
    <h4><?= $is_edit_mode ? 'Edit' : 'Tambah'; ?> Jadwal Mengajar</h4>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($jadwal_data['id']); ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Guru</label>
                <select name="guru_nip" class="form-select" required>
                    <?php while ($g = $result_guru->fetch_assoc()): ?>
                        <option value="<?= $g['nip']; ?>" <?= $jadwal_data['guru_nip'] == $g['nip'] ? 'selected' : ''; ?>><?= htmlspecialchars($g['nama']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <?php while ($k = $result_kelas->fetch_assoc()): ?>
                        <option value="<?= $k['id']; ?>" <?= $jadwal_data['kelas_id'] == $k['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($k['nama_tingkat_numerik'] . ' ' . $k['singkatan_jurusan'] . ' ' . $k['rombel']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Mata Pelajaran</label>
                <select name="mapel_id" class="form-select" required>
                    <?php while ($m = $result_mapel->fetch_assoc()): ?>
                        <option value="<?= $m['id']; ?>" <?= $jadwal_data['mapel_id'] == $m['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($m['nama_mapel']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Hari</label>
                <select name="hari" class="form-select" required>
                    <?php foreach ($hari_list as $hari): ?>
                        <option value="<?= $hari; ?>" <?= $jadwal_data['hari'] == $hari ? 'selected' : ''; ?>><?= $hari; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Jam Mulai</label>
                <input type="time" class="form-control" name="jam_mulai" value="<?= htmlspecialchars($jadwal_data['jam_mulai']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Jam Selesai</label>
                <input type="time" class="form-control" name="jam_selesai" value="<?= htmlspecialchars($jadwal_data['jam_selesai']); ?>" required>
            </div>
        </div>
        <button type="submit" name="submit_jadwal" class="btn btn-primary">Simpan Jadwal</button>
        <?php if ($is_edit_mode): ?>
            <a href="manage_jadwal.php" class="btn btn-secondary">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card-ui">
    <h4>Daftar Jadwal Mengajar</h4>
    <div style="overflow-x: auto;">
        <table class="table" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Guru</th>
                    <th>Kelas</th>
                    <th>Mapel</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($j = $result_jadwal->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($j['hari']); ?></td>
                        <td><?= date('H:i', strtotime($j['jam_mulai'])); ?> - <?= date('H:i', strtotime($j['jam_selesai'])); ?></td>
                        <td><?= htmlspecialchars($j['nama_guru']); ?></td>
                        <td>
                            <?= htmlspecialchars($j['nama_tingkat_numerik'] . ' ' . $j['singkatan_jurusan'] . ' ' . $j['rombel']); ?>
                        </td>
                        <td><?= htmlspecialchars($j['nama_mapel']); ?></td>
                        <td>
                            <a href="manage_jadwal.php?action=edit&id=<?= $j['id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="manage_jadwal.php?action=delete&id=<?= $j['id']; ?>" class="btn btn-danger btn-hapus">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>