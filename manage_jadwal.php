<?php
require_once 'includes/auth.php';
auth_role('super admin');
require_once 'includes/db.php';
$_SESSION['page_title'] = "Master Jadwal Mengajar";

$filter_kelas = $_GET['filter_kelas'] ?? '';

$gurus = $conn->query("SELECT nip, nama FROM users WHERE role='guru' ORDER BY nama");
$kelases = $conn->query("SELECT k.id, t.nama_tingkat, j.singkatan_jurusan, k.rombel FROM kelas k JOIN tingkat t ON k.tingkat_id=t.id JOIN jurusan j ON k.jurusan_id=j.id ORDER BY t.nama_tingkat, j.singkatan_jurusan, k.rombel");
$mapels = $conn->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel");

$jadwal = null;
if ($filter_kelas) {
    $sql = "SELECT j.*, u.nama as guru, m.nama_mapel, t.nama_tingkat, ju.singkatan_jurusan, k.rombel 
            FROM jadwal_mengajar j 
            JOIN users u ON j.guru_nip=u.nip 
            JOIN mata_pelajaran m ON j.mapel_id=m.id 
            JOIN kelas k ON j.kelas_id=k.id 
            JOIN tingkat t ON k.tingkat_id=t.id 
            JOIN jurusan ju ON k.jurusan_id=ju.id 
            WHERE j.kelas_id = ?
            ORDER BY FIELD(j.hari, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), j.jam_mulai";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $filter_kelas);
    $stmt->execute();
    $jadwal = $stmt->get_result();
}
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui">
    <h4>Kelola Jadwal Pelajaran</h4>

    <form method="GET" style="margin-bottom: 20px; background: #f0f0f0; padding: 15px; border-radius: 8px;">
        <label class="form-label">Pilih Kelas untuk Mengelola Jadwal:</label>
        <div style="display: flex; gap: 10px;">
            <select name="filter_kelas" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                <?php
                mysqli_data_seek($kelases, 0);
                while ($k = $kelases->fetch_assoc()):
                ?>
                    <option value="<?= $k['id'] ?>" <?= $filter_kelas == $k['id'] ? 'selected' : '' ?>>
                        <?= $k['nama_tingkat'] . ' ' . $k['singkatan_jurusan'] . ' ' . $k['rombel'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <a href="manage_jadwal.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <?php if ($filter_kelas): ?>
        <h5>Tambah Jadwal untuk Kelas Terpilih</h5>
        <form method="POST" class="row g-3 mb-4">
            <input type="hidden" name="kelas" value="<?= $filter_kelas; ?>">
            <div class="col-12"><button type="submit" name="add" class="btn btn-primary w-100">Simpan ke Jadwal</button></div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Mapel</th>
                    <th>Guru</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jadwal && $jadwal->num_rows > 0): while ($r = $jadwal->fetch_assoc()): ?>
                        <tr>
                            <td><?= $r['hari'] ?></td>
                            <td><?= substr($r['jam_mulai'], 0, 5) . ' - ' . substr($r['jam_selesai'], 0, 5) ?></td>
                            <td><?= $r['nama_mapel'] ?></td>
                            <td><?= $r['guru'] ?></td>
                            <td><a href="?del=<?= $r['id'] ?>&filter_kelas=<?= $filter_kelas ?>" class="btn btn-sm btn-danger">Hapus</a></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Belum ada jadwal di kelas ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-3 text-end">
            <a href="?reset_kelas=<?= $filter_kelas ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus SEMUA jadwal kelas ini?')">Hapus Semua Jadwal Kelas Ini</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Silakan pilih kelas terlebih dahulu di atas untuk melihat dan mengedit jadwal.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>