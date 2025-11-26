SET
  FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `absensi_log`;

DROP TABLE IF EXISTS `jadwal_mengajar`;

DROP TABLE IF EXISTS `koreksi_absensi`;

DROP TABLE IF EXISTS `mata_pelajaran`;

DROP TABLE IF EXISTS `kelas`;

DROP TABLE IF EXISTS `jurusan`;

DROP TABLE IF EXISTS `tingkat`;

DROP TABLE IF EXISTS `users`;

CREATE TABLE
  `tingkat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_tingkat` VARCHAR(5) NOT NULL UNIQUE COMMENT 'Cth: X, XI, XII'
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `jurusan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_jurusan` VARCHAR(100) NOT NULL,
    `singkatan_jurusan` VARCHAR(10) NOT NULL UNIQUE COMMENT 'Cth: RPL, DKV, TP'
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `kelas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tingkat_id` INT NOT NULL,
    `jurusan_id` INT NOT NULL,
    `rombel` VARCHAR(5) NOT NULL COMMENT 'Cth: A, B, C, 1, 2',
    UNIQUE KEY `kelas_unik` (`tingkat_id`, `jurusan_id`, `rombel`),
    FOREIGN KEY (`tingkat_id`) REFERENCES `tingkat` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `nip` VARCHAR(18) CHARACTER
    SET
      utf8mb4 COLLATE utf8mb4_general_ci NULL UNIQUE,
      `nisn` VARCHAR(10) CHARACTER
    SET
      utf8mb4 COLLATE utf8mb4_general_ci NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `role` ENUM ('super admin', 'admin', 'guru', 'ketua kelas') NOT NULL,
      `role_guru` VARCHAR(100) NULL COMMENT 'Cth: Guru Matematika',
      `kelas_id` INT NULL COMMENT 'Hanya untuk Ketua Kelas',
      `status` ENUM ('Aktif', 'Non-Aktif') NOT NULL DEFAULT 'Aktif',
      -- POIN 2 (Detail Pengguna) --
      `jenis_kelamin` ENUM ('Laki-laki', 'Perempuan') NULL,
      `alamat` TEXT NULL,
      `foto` VARCHAR(255) NULL COMMENT 'Nama file foto profil',
      FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `mata_pelajaran` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_mapel` VARCHAR(100) NOT NULL UNIQUE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `jadwal_mengajar` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `guru_nip` VARCHAR(18) CHARACTER
    SET
      utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `kelas_id` INT NOT NULL,
      `mapel_id` INT NOT NULL,
      `hari` ENUM (
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
      ) NOT NULL,
      `jam_mulai` TIME NOT NULL,
      `jam_selesai` TIME NOT NULL,
      FOREIGN KEY (`guru_nip`) REFERENCES `users` (`nip`) ON DELETE CASCADE,
      FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `absensi_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jadwal_id` INT NULL,
    `guru_nip` VARCHAR(18) CHARACTER
    SET
      utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `tanggal` DATE NOT NULL,
      `jam_masuk` DATETIME NULL,
      `jam_keluar` DATETIME NULL,
      `status` ENUM (
        'Hadir',
        'Izin',
        'Sakit',
        'Terlambat',
        'Absen',
        'Dinas Luar'
      ) NOT NULL,
      `keterangan_izin` TEXT NULL,
      `file_bukti` VARCHAR(255) NULL,
      UNIQUE KEY `absen_ganda` (`guru_nip`, `tanggal`, `jadwal_id`),
      KEY `jadwal_id` (`jadwal_id`),
      FOREIGN KEY (`guru_nip`) REFERENCES `users` (`nip`) ON DELETE CASCADE,
      FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE
  `koreksi_absensi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jadwal_id` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `keterangan_baru` ENUM ('Hadir', 'Izin', 'Sakit', 'Absen') NOT NULL,
    `alasan` TEXT NOT NULL,
    `status` ENUM ('Diajukan', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Diajukan',
    `diajukan_oleh_nisn` VARCHAR(10) CHARACTER
    SET
      utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
      `diproses_oleh_id` INT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`id`) ON DELETE CASCADE,
      FOREIGN KEY (`diajukan_oleh_nisn`) REFERENCES `users` (`nisn`) ON DELETE CASCADE,
      FOREIGN KEY (`diproses_oleh_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
  `tingkat` (`id`, `nama_tingkat`)
VALUES
  (1, 'X'),
  (2, 'XI'),
  (3, 'XII');

INSERT INTO
  `jurusan` (`id`, `nama_jurusan`, `singkatan_jurusan`)
VALUES
  (1, 'Rekayasa Perangkat Lunak', 'RPL'),
  (2, 'Teknik Pengelasan', 'TP'),
  (3, 'Desain Komunikasi Visual', 'DKV'),
  (4, 'Teknik Kendaraan Ringan Otomotif', 'TKRO');

INSERT INTO
  `kelas` (`id`, `tingkat_id`, `jurusan_id`, `rombel`)
VALUES
  (1, 2, 1, 'B'), 
  (2, 2, 2, 'A'), 
  (3, 3, 3, 'B'), 
  (4, 1, 4, 'A'), 
  (5, 1, 4, 'B');

INSERT INTO
  `users` (
    `id`,
    `nama`,
    `nip`,
    `nisn`,
    `password`,
    `role`,
    `role_guru`,
    `kelas_id`,
    `status`,
    `jenis_kelamin`,
    `alamat`,
    `foto`
  )
VALUES
  (
    1,
    'Super Admin',
    '111111111111111111',
    NULL,
    '$2y$10$E.F3gRJ25iT..2i.3c.p5O5G.F0N4gE/N3gH.e.2L.g3i.1u.5K.e',
    'super admin',
    'Waka Kurikulum',
    NULL,
    'Aktif',
    'Laki-laki',
    'Jl. Admin',
    NULL
  ),
  (
    2,
    'Admin Piket',
    '222222222222222222',
    NULL,
    '$2y$10$E.F3gRJ25iT..2i.3c.p5O5G.F0N4gE/N3gH.e.2L.g3i.1u.5K.e',
    'admin',
    'Staf Piket',
    NULL,
    'Aktif',
    'Perempuan',
    'Jl. Piket',
    NULL
  ),
  (
    3,
    'Haris Ramadhani',
    '333333333333333333',
    NULL,
    '$2y$10$E.F3gRJ25iT..2i.3c.p5O5G.F0N4gE/N3gH.e.2L.g3i.1u.5K.e',
    'guru',
    'Guru RPL',
    NULL,
    'Aktif',
    'Laki-laki',
    'Jl. Koding',
    NULL
  ),
  (
    4,
    'Bu Teti',
    '444444444444444444',
    NULL,
    '$2y$10$E.F3gRJ25iT..2i.3c.p5O5G.F0N4gE/N3gH.e.2L.g3i.1u.5K.e',
    'guru',
    'Guru Matematika',
    NULL,
    'Aktif',
    'Perempuan',
    'Jl. Angka',
    NULL
  ),
  (
    5,
    'Ketua RPL B',
    NULL,
    '1000000001',
    '$2y$10$E.F3gRJ25iT..2i.3c.p5O5G.F0N4gE/N3gH.e.2L.g3i.1u.5K.e',
    'ketua kelas',
    'Siswa',
    1,
    'Aktif',
    'Laki-laki',
    'Jl. Siswa 1',
    NULL
  ),
  (
    6,
    'Ketua TP A',
    NULL,
    '1000000002',
    '$2y$10$E.F3gRJ25iT..2i.3c.p5O5G.F0N4gE/N3gH.e.2L.g3i.1u.5K.e',
    'ketua kelas',
    'Siswa',
    2,
    'Aktif',
    'Perempuan',
    'Jl. Siswa 2',
    NULL
  );

INSERT INTO
  `mata_pelajaran` (`id`, `nama_mapel`)
VALUES
  (1, 'Pemrograman Web'),
  (2, 'Matematika');

INSERT INTO
  `jadwal_mengajar` (
    `id`,
    `guru_nip`,
    `kelas_id`,
    `mapel_id`,
    `hari`,
    `jam_mulai`,
    `jam_selesai`
  )
VALUES
  (
    1,
    '333333333333333333',
    1,
    1,
    'Tuesday',
    '07:30:00',
    '10:00:00'
  ),
  (
    2,
    '444444444444444444',
    1,
    2,
    'Tuesday',
    '10:00:00',
    '12:00:00'
  );

SET
  FOREIGN_KEY_CHECKS = 1;