<?php
require_once 'includes/auth.php';
auth_role(['super admin', 'admin']); 
require_once 'includes/db.php';

$current_role = $_SESSION['user_role'];
$user_id = $_GET['id'] ?? null;
$page_title = $user_id ? "Edit Pengguna" : "Tambah Pengguna Baru";
$_SESSION['page_title'] = $page_title;

$user = [
    'nama' => '',
    'nip' => '',
    'nisn' => '',
    'role' => '',
    'role_guru' => '',
    'kelas_id' => '',
    'status' => 'Aktif',
    'foto' => ''
];
$notif_msg = '';
$notif_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $role_guru = $_POST['role_guru'] ?? '';
    $status = $_POST['status'];
    $password = $_POST['password'];

    if ($current_role == 'admin' && !in_array($role, ['guru', 'ketua kelas'])) {
        $notif_msg = "Aksi tidak diizinkan. Anda tidak dapat membuat/mengedit pengguna dengan role '{$role}'.";
        $notif_type = 'danger';
    } else {
        $nip = NULL;
        $nisn = NULL;
        $kelas_id = NULL;

        if ($role == 'ketua kelas') {
            $nisn = $_POST['nisn'];
            $kelas_id = $_POST['kelas_id'];
        } else {
            $nip = $_POST['nip'];
        }

        try {
            if (empty($user_id)) {
                if (empty($password)) {
                    $notif_msg = "Password wajib diisi untuk pengguna baru.";
                    $notif_type = "danger";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO users (nama, nip, nisn, password, role, role_guru, kelas_id, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssis", $nama, $nip, $nisn, $hashed_password, $role, $role_guru, $kelas_id, $status);
                    $stmt->execute();
                    header('Location: manage_users.php?notif=12');
                    exit();
                }
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET nama=?, nip=?, nisn=?, password=?, role=?, role_guru=?, kelas_id=?, status=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssisi", $nama, $nip, $nisn, $hashed_password, $role, $role_guru, $kelas_id, $status, $user_id);
                } else {
                    $sql = "UPDATE users SET nama=?, nip=?, nisn=?, role=?, role_guru=?, kelas_id=?, status=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssisi", $nama, $nip, $nisn, $role, $role_guru, $kelas_id, $status, $user_id);
                }
                $stmt->execute();
                header('Location: manage_users.php?notif=12');
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $notif_msg = "Gagal! NIP atau NISN sudah terdaftar.";
                $notif_type = "danger";
            } else {
                $notif_msg = "Error DB: " . $e->getMessage();
                $notif_type = "danger";
            }
        }
    }
}

if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        header('Location: manage_users.php');
        exit();
    }

    if ($current_role == 'admin' && in_array($user['role'], ['admin', 'super admin'])) {
        include 'includes/header.php';
        echo '<div class="card-ui"><div class="notif notif-danger">Aksi tidak diizinkan. Anda tidak dapat mengedit pengguna ini.</div></div>';
        include 'includes/footer.php';
        exit();
    }
}

$sql_kelas = "SELECT k.id, 
              (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
              ju.singkatan_jurusan, k.rombel
              FROM kelas k
              LEFT JOIN tingkat t ON k.tingkat_id = t.id
              LEFT JOIN jurusan ju ON k.jurusan_id = ju.id
              ORDER BY nama_tingkat_numerik, singkatan_jurusan, rombel";
$result_kelas = $conn->query($sql_kelas);
?>
<?php include 'includes/header.php'; ?>

<?php if (!empty($notif_msg)): ?>
    <div class="notif notif-<?= $notif_type; ?> notif-autohide"><?= $notif_msg; ?></div>
<?php endif; ?>

<div class="card-ui" style="max-width: 700px; margin: 0 auto;">
    <h4><?= $page_title; ?></h4>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Role</label>
            <select name="role" id="role-select" class="form-select" required>
                <option value="" disabled selected>-- Pilih Role --</option>

                <?php if ($current_role == 'super admin'): ?>
                    <option value="super admin" <?= $user['role'] == 'super admin' ? 'selected' : ''; ?>>Super Admin</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <?php endif; ?>

                <option value="guru" <?= $user['role'] == 'guru' ? 'selected' : ''; ?>>Guru</option>
                <option value="ketua kelas" <?= $user['role'] == 'ketua kelas' ? 'selected' : ''; ?>>Ketua Kelas</option>
            </select>
        </div>

        <div class="form-group" id="grup-nip" style="display: none;">
            <label class="form-label">NIP</label>
            <input type="text" class="form-control" name="nip" value="<?= htmlspecialchars($user['nip']); ?>">
        </div>

        <div class="form-group" id="grup-nisn" style="display: none;">
            <label class="form-label">NISN</label>
            <input type="text" class="form-control" name="nisn" value="<?= htmlspecialchars($user['nisn']); ?>">
        </div>

        <div class="form-group" id="grup-role-guru" style="display: none;">
            <label class="form-label">Detail Jabatan/Role (Contoh: Guru Matematika, Waka Kurikulum)</label>
            <input type="text" class="form-control" name="role_guru" value="<?= htmlspecialchars($user['role_guru']); ?>">
        </div>

        <div class="form-group" id="grup-kelas" style="display: none;">
            <label class="form-label">Pilih Kelas</label>
            <select name="kelas_id" class="form-select">
                <option value="" disabled selected>-- Pilih Kelas --</option>
                <?php while ($k = $result_kelas->fetch_assoc()): ?>
                    <option value="<?= $k['id']; ?>" <?= $user['kelas_id'] == $k['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($k['nama_tingkat_numerik'] . ' ' . $k['singkatan_jurusan'] . ' ' . $k['rombel']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password">
            <?php if ($user_id): ?>
                <small class="form-text">Kosongkan jika tidak ingin mengubah password.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label">Status Akun</label>
            <select name="status" class="form-select" required>
                <option value="Aktif" <?= $user['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                <option value="Tidak Aktif" <?= $user['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
        <a href="manage_users.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role-select');
        const grupNIP = document.getElementById('grup-nip');
        const grupNISN = document.getElementById('grup-nisn');
        const grupKelas = document.getElementById('grup-kelas');
        const grupRoleGuru = document.getElementById('grup-role-guru');

        function toggleRoleFields() {
            const role = roleSelect.value;

            grupNIP.style.display = 'none';
            grupNISN.style.display = 'none';
            grupKelas.style.display = 'none';
            grupRoleGuru.style.display = 'none';

            if (role === 'guru' || role === 'admin' || role === 'super admin') {
                grupNIP.style.display = 'block';
                grupRoleGuru.style.display = 'block';
            } else if (role === 'ketua kelas') {
                grupNISN.style.display = 'block';
                grupKelas.style.display = 'block';
            }
        }

        roleSelect.addEventListener('change', toggleRoleFields);

        toggleRoleFields();
    });
</script>

<?php include 'includes/footer.php'; ?>