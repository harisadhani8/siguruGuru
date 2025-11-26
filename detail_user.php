<?php
require_once 'includes/auth.php';
auth_role('super admin');
$_SESSION['page_title'] = "Detail Data Pengguna";
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: manage_users.php');
    exit();
}
$user_id = $_GET['id'];

$sql = "SELECT u.*, 
        (CASE t.nama_tingkat WHEN 'X' THEN '10' WHEN 'XI' THEN '11' WHEN 'XII' THEN '12' ELSE t.nama_tingkat END) AS nama_tingkat_numerik, 
        j.singkatan_jurusan, k.rombel, k.id as id_kelas
        FROM users u 
        LEFT JOIN kelas k ON u.kelas_id = k.id
        LEFT JOIN tingkat t ON k.tingkat_id = t.id
        LEFT JOIN jurusan j ON k.jurusan_id = j.id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: manage_users.php');
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="card-ui">
    <h4>Detail Pengguna: <?= htmlspecialchars($user['nama']); ?></h4>

    <?php if (!empty($user['foto'])): ?>
        <div style="margin: 1.5rem 0;">
            <img src="uploads/foto_profil/<?= htmlspecialchars($user['foto']); ?>" alt="Foto Profil" style="max-width: 200px; height: auto; border-radius: 8px; border: 3px solid var(--border-color);">
        </div>
    <?php else: ?>
        <p style="margin: 1.5rem 0; color: var(--text-light);">Pengguna ini tidak memiliki foto profil.</p>
    <?php endif; ?>
    <div style="overflow-x: auto;">
        <table class="table">
            <tbody>
                <tr>
                    <th style="width: 200px;">Nama Lengkap</th>
                    <td><?= htmlspecialchars($user['nama']); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?= htmlspecialchars($user['role']); ?></td>
                </tr>

                <?php if ($user['role'] == 'guru'): ?>
                    <tr>
                        <th>NIP</th>
                        <td><?= htmlspecialchars($user['nip'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Detail Role Guru</th>
                        <td><?= htmlspecialchars($user['role_guru'] ?? '-'); ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($user['role'] == 'ketua kelas'): ?>
                    <tr>
                        <th>NISN</th>
                        <td><?= htmlspecialchars($user['nisn'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td>
                            <?= htmlspecialchars($user['nama_tingkat_numerik'] . ' ' . $user['singkatan_jurusan'] . ' ' . $user['rombel']); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($user['role'] == 'admin' || $user['role'] == 'super admin'): ?>
                    <tr>
                        <th>NIP</th>
                        <td><?= htmlspecialchars($user['nip'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Jabatan</th>
                        <td><?= htmlspecialchars($user['role_guru'] ?? '-'); ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th>Status Akun</th>
                    <td><?= htmlspecialchars($user['status']); ?></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td><?= htmlspecialchars($user['jenis_kelamin'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td><?= htmlspecialchars($user['alamat'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>No. HP</th>
                    <td><?= htmlspecialchars($user['no_hp'] ?? '-'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <a href="manage_users.php" class="btn btn-secondary" style="margin-top: 1.5rem;">Kembali</a>
    <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn btn-primary" style="margin-top: 1.5rem;">Edit Pengguna Ini</a>
</div>

<?php include 'includes/footer.php'; ?>