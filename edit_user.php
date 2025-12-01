<?php
require_once 'includes/auth.php';
auth_role('super admin');
require_once 'includes/db.php';

$is_edit_mode = false;
$user_data = ['id' => '', 'nama' => '', 'nip' => '', 'nisn' => '', 'role' => 'guru', 'role_guru' => '', 'kelas_id' => '', 'status' => 'Aktif', 'jenis_kelamin' => '', 'alamat' => '', 'foto' => ''];
$notif_msg = '';
$notif_type = 'danger';

if (isset($_GET['id'])) {
    $is_edit_mode = true;
    $_SESSION['page_title'] = "Edit Pengguna";
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $data_fetch = $stmt->get_result()->fetch_assoc();
    if ($data_fetch) $user_data = $data_fetch;
} else {
    $_SESSION['page_title'] = "Tambah Pengguna Baru";
}

$sql_kelas = "SELECT k.id, t.nama_tingkat, j.singkatan_jurusan, k.rombel 
              FROM kelas k
              JOIN tingkat t ON k.tingkat_id = t.id
              JOIN jurusan j ON k.jurusan_id = j.id
              ORDER BY t.nama_tingkat, j.singkatan_jurusan, k.rombel";
$result_kelas = $conn->query($sql_kelas);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $role_guru = $_POST['role_guru'];
    $kelas_id = ($_POST['role'] == 'ketua kelas' && !empty($_POST['kelas_id'])) ? $_POST['kelas_id'] : null;
    $status = $_POST['status'];
    $password = $_POST['password'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $foto_nama = $user_data['foto'];

    $nip = null;
    $nisn = null;
    $validasi_ok = true;

    if ($role == 'super admin' || $role == 'admin' || $role == 'guru') {
        $nip = $_POST['nip'];
        if (empty($nip) || strlen($nip) != 18) {
            $validasi_ok = false;
            $notif_msg = "Gagal! Role $role wajib memiliki NIP 18 digit.";
        }
    } else if ($role == 'ketua kelas') {
        $nisn = $_POST['nisn'];
        if (empty($nisn) || strlen($nisn) != 10) {
            $validasi_ok = false;
            $notif_msg = "Gagal! Role Ketua Kelas wajib memiliki NISN 10 digit.";
        }
        if (empty($kelas_id)) {
            $validasi_ok = false;
            $notif_msg = "Gagal! Role Ketua Kelas wajib memilih Asal Kelas.";
        }
    }

    if ($validasi_ok && isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/foto_profil/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $foto_nama = uniqid() . '.' . $extension;
        $target_file = $target_dir . $foto_nama;
        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $validasi_ok = false;
            $notif_msg = "Gagal mengupload file foto.";
        } else {
            if ($is_edit_mode && !empty($user_data['foto']) && file_exists($target_dir . $user_data['foto'])) {
                unlink($target_dir . $user_data['foto']);
            }
        }
    }

    if ($validasi_ok) {
        $current_id = empty($id) ? 0 : $id;
        $sql_cek_duplikat = "SELECT id FROM users WHERE ((nip = ? AND ? != '') OR (nisn = ? AND ? != '')) AND id != ?";
        $stmt_cek = $conn->prepare($sql_cek_duplikat);
        $stmt_cek->bind_param("ssssi", $nip, $nip, $nisn, $nisn, $current_id);
        $stmt_cek->execute();

        if ($stmt_cek->get_result()->num_rows > 0) {
            $notif_msg = "Gagal! NIP atau NISN sudah digunakan oleh pengguna lain.";
        } else {
            if (!empty($id)) { 
                if (!empty($password)) {
                    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET nama=?, nip=?, nisn=?, role=?, role_guru=?, kelas_id=?, status=?, password=?, jenis_kelamin=?, alamat=?, foto=? WHERE id=?");
                    $stmt->bind_param("sssssississsi", $nama, $nip, $nisn, $role, $role_guru, $kelas_id, $status, $hashed_pass, $jenis_kelamin, $alamat, $foto_nama, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET nama=?, nip=?, nisn=?, role=?, role_guru=?, kelas_id=?, status=?, jenis_kelamin=?, alamat=?, foto=? WHERE id=?");
                    $stmt->bind_param("sssssissssi", $nama, $nip, $nisn, $role, $role_guru, $kelas_id, $status, $jenis_kelamin, $alamat, $foto_nama, $id);
                }
            } else { 
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (nama, nip, nisn, role, role_guru, kelas_id, status, password, jenis_kelamin, alamat, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssissssss", $nama, $nip, $nisn, $role, $role_guru, $kelas_id, $status, $hashed_pass, $jenis_kelamin, $alamat, $foto_nama);
            }

            if ($stmt->execute()) {
                header('Location: manage_users.php?notif=12');
                exit();
            } else {
                $notif_msg = "Error Database: " . $stmt->error;
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui" style="max-width: 800px; margin: 0 auto;">
    <h4><?= $_SESSION['page_title']; ?></h4>

    <?php if (!empty($notif_msg)) echo '<div class="notif notif-' . $notif_type . '">' . $notif_msg . '</div>'; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($user_data['id']); ?>">

        <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user_data['nama']); ?>" required></div>
        <div class="form-group">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" id="roleSelect" required>
                <option value="super admin" <?= $user_data['role'] == 'super admin' ? 'selected' : ''; ?>>Super Admin</option>
                <option value="admin" <?= $user_data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="guru" <?= $user_data['role'] == 'guru' ? 'selected' : ''; ?>>Guru</option>
                <option value="ketua kelas" <?= $user_data['role'] == 'ketua kelas' ? 'selected' : ''; ?>>Ketua Kelas</option>
            </select>
        </div>

        <div class="form-group"><label class="form-label">Jenis Kelamin</label><select class="form-select" name="jenis_kelamin">
                <option value="" <?= $user_data['jenis_kelamin'] == '' ? 'selected' : ''; ?>>-- Pilih --</option>
                <option value="Laki-laki" <?= $user_data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="Perempuan" <?= $user_data['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
            </select></div>
        <div class="form-group"><label class="form-label">Alamat</label><textarea class="form-control" name="alamat" rows="3"><?= htmlspecialchars($user_data['alamat']); ?></textarea></div>
        <div class="form-group"><label class="form-label">Foto Profil</label><input type="file" class="form-control" name="foto" accept="image/jpeg, image/png"></div>

        <div class="form-group" id="nipGroup"><label class="form-label">NIP (18 Digit)</label><input type="text" class="form-control" name="nip" value="<?= htmlspecialchars($user_data['nip']); ?>" maxlength="18"></div>
        <div class="form-group" id="nisnGroup"><label class="form-label">NISN (10 Digit)</label><input type="text" class="form-control" name="nisn" value="<?= htmlspecialchars($user_data['nisn']); ?>" maxlength="10"></div>
        <div class="form-group" id="roleGuruGroup"><label class="form-label">Detail Role</label><input type="text" class="form-control" name="role_guru" value="<?= htmlspecialchars($user_data['role_guru']); ?>"></div>

        <div class="form-group" id="kelasSelectGroup">
            <label class="form-label">Asal Kelas</label>
            <select class="form-select" name="kelas_id">
                <option value="">-- Pilih Kelas --</option>
                <?php if ($result_kelas): ?>
                    <?php while ($k = $result_kelas->fetch_assoc()): ?>
                        <option value="<?= $k['id']; ?>" <?= $user_data['kelas_id'] == $k['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($k['nama_tingkat'] . ' ' . $k['singkatan_jurusan'] . ' ' . $k['rombel']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group"><label class="form-label">Status</label><select class="form-select" name="status" required>
                <option value="Aktif" <?= $user_data['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                <option value="Non-Aktif" <?= $user_data['status'] == 'Non-Aktif' ? 'selected' : ''; ?>>Non-Aktif</option>
            </select></div>
        <div class="form-group"><label class="form-label">Password</label><input type="password" class="form-control" name="password" <?= $is_edit_mode ? '' : 'required'; ?>></div>

        <button type="submit" class="btn btn-primary">Simpan Data</button>
        <a href="manage_users.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
    function toggleRoleFields() {
        const role = document.getElementById('roleSelect').value;
        const nipGroup = document.getElementById('nipGroup');
        const nisnGroup = document.getElementById('nisnGroup');
        const kelasSelectGroup = document.getElementById('kelasSelectGroup');
        const roleGuruGroup = document.getElementById('roleGuruGroup');
        if (role === 'ketua kelas') {
            nipGroup.style.display = 'none';
            roleGuruGroup.style.display = 'none';
            nisnGroup.style.display = 'block';
            kelasSelectGroup.style.display = 'block';
        } else {
            nipGroup.style.display = 'block';
            roleGuruGroup.style.display = 'block';
            nisnGroup.style.display = 'none';
            kelasSelectGroup.style.display = 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', toggleRoleFields);
    document.getElementById('roleSelect').addEventListener('change', toggleRoleFields);
</script>
<?php include 'includes/footer.php'; ?>