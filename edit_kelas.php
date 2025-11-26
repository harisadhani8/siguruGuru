<?php
require_once 'includes/auth.php';
auth_role('super admin');
require_once 'includes/db.php';

$kelas_id = $_GET['id'] ?? null;
$page_title = $kelas_id ? "Edit Kelas" : "Tambah Kelas Baru";
$_SESSION['page_title'] = $page_title;

$kelas = ['tingkat_id' => '', 'jurusan_id' => '', 'rombel' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tingkat_id = $_POST['tingkat_id'];
    $jurusan_id = $_POST['jurusan_id'];
    $rombel = strtoupper($_POST['rombel']); 

    if (empty($kelas_id)) { 
        $sql = "INSERT INTO kelas (tingkat_id, jurusan_id, rombel) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $tingkat_id, $jurusan_id, $rombel);
    } else { 
        $sql = "UPDATE kelas SET tingkat_id = ?, jurusan_id = ?, rombel = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $tingkat_id, $jurusan_id, $rombel, $kelas_id);
    }

    if ($stmt->execute()) {
        header('Location: manage_kelas.php?notif=2');
        exit();
    } else {
        echo "<script>alert('Gagal: Kelas tersebut mungkin sudah ada.');</script>";
    }
}

if ($kelas_id) {
    $stmt = $conn->prepare("SELECT * FROM kelas WHERE id = ?");
    $stmt->bind_param("i", $kelas_id);
    $stmt->execute();
    $result_kelas_data = $stmt->get_result();
    if ($result_kelas_data->num_rows > 0) {
        $kelas = $result_kelas_data->fetch_assoc();
    } else {
        header('Location: manage_kelas.php');
        exit();
    }
}

$result_tingkat = $conn->query("SELECT * FROM tingkat ORDER BY id");
$result_jurusan = $conn->query("SELECT * FROM jurusan ORDER BY nama_jurusan");
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui" style="max-width: 600px; margin: 0 auto;">
    <h4><?= $page_title; ?></h4>
    <form method="POST">

        <div class="form-group">
            <label class="form-label">Tingkat</label>
            <select name="tingkat_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Tingkat --</option>
                <?php if ($result_tingkat): while ($row = $result_tingkat->fetch_assoc()): ?>
                        <option value="<?= $row['id']; ?>" <?= $kelas['tingkat_id'] == $row['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($row['nama_tingkat']); ?>
                        </option>
                <?php endwhile;
                endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Jurusan</label>
            <select name="jurusan_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Jurusan --</option>
                <?php if ($result_jurusan): while ($row = $result_jurusan->fetch_assoc()): ?>
                        <option value="<?= $row['id']; ?>" <?= $kelas['jurusan_id'] == $row['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($row['nama_jurusan']); ?> (<?= htmlspecialchars($row['singkatan_jurusan']); ?>)
                        </option>
                <?php endwhile;
                endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Rombel (Rombongan Belajar)</label>
            <input type="text" class="form-control" name="rombel" value="<?= htmlspecialchars($kelas['rombel']); ?>" placeholder="Contoh: A, B, atau 1, 2" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Kelas</button>
        <a href="manage_kelas.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>