<?php
require_once 'includes/auth.php';
auth_role('super admin');
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
$page_title = $id ? "Edit Tingkat" : "Tambah Tingkat Baru";
$_SESSION['page_title'] = $page_title;

$nama_tingkat = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_tingkat = $_POST['nama_tingkat'];

    if (empty($id)) { 
        $sql = "INSERT INTO tingkat (nama_tingkat) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nama_tingkat);
    } else { 
        $sql = "UPDATE tingkat SET nama_tingkat = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nama_tingkat, $id);
    }

    if ($stmt->execute()) {
        header('Location: manage_tingkat.php?notif=2');
        exit();
    }
}

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM tingkat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $nama_tingkat = $data['nama_tingkat'];
}
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui" style="max-width: 600px; margin: 0 auto;">
    <h4><?= $page_title; ?></h4>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nama Tingkat</label>
            <input type="text" class="form-control" name="nama_tingkat" value="<?= htmlspecialchars($nama_tingkat); ?>" placeholder="Contoh: X, XI, atau XII" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="manage_tingkat.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>