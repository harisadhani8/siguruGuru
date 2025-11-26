<?php
require_once 'includes/auth.php';
auth_role('super admin');
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
$page_title = $id ? "Edit Jurusan" : "Tambah Jurusan Baru";
$_SESSION['page_title'] = $page_title;

$data = ['nama_jurusan' => '', 'singkatan_jurusan' => ''];
$notif_msg = '';
$notif_type = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM jurusan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        header('Location: manage_jurusan.php');
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_jurusan = $_POST['nama_jurusan'];
    $singkatan_jurusan = strtoupper($_POST['singkatan_jurusan']); 

    try {
        if (empty($id)) { 
            $stmt = $conn->prepare("INSERT INTO jurusan (nama_jurusan, singkatan_jurusan) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_jurusan, $singkatan_jurusan);
        } else { 
            $stmt = $conn->prepare("UPDATE jurusan SET nama_jurusan = ?, singkatan_jurusan = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama_jurusan, $singkatan_jurusan, $id);
        }

        if ($stmt->execute()) {
            header('Location: manage_jurusan.php?notif=saved');
            exit();
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $notif_msg = "Gagal! Singkatan jurusan sudah ada.";
            $notif_type = "danger";
        } else {
            $notif_msg = "Error: " . $e->getMessage();
            $notif_type = "danger";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui" style="max-width: 600px; margin: 0 auto;">
    <h4><?= $page_title; ?></h4>

    <?php if (!empty($notif_msg)): ?>
        <div class="notif notif-<?= $notif_type; ?>"><?= $notif_msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nama Jurusan Lengkap</label>
            <input type="text" class="form-control" name="nama_jurusan" value="<?= htmlspecialchars($data['nama_jurusan']); ?>" placeholder="Contoh: Teknik Komputer dan Jaringan" required>
        </div>

        <div class="form-group">
            <label class="form-label">Singkatan Jurusan (Kode)</label>
            <input type="text" class="form-control" name="singkatan_jurusan" value="<?= htmlspecialchars($data['singkatan_jurusan']); ?>" placeholder="Contoh: TKJ" required>
            <div class="form-text">Gunakan singkatan umum yang dipakai di sekolah.</div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Simpan Data</button>
            <a href="manage_jurusan.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>