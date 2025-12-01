SET
  FOREIGN_KEY_CHECKS = 0;
SET
  SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
  time_zone = "+07:00";
DROP TABLE IF EXISTS `koreksi_absensi`;
DROP TABLE IF EXISTS `absensi_log`;
DROP TABLE IF EXISTS `absensi_harian`;
DROP TABLE IF EXISTS `jadwal_mengajar`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `kelas`;
DROP TABLE IF EXISTS `mata_pelajaran`;
DROP TABLE IF EXISTS `jurusan`;
DROP TABLE IF EXISTS `tingkat`;

CREATE TABLE
  `tingkat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_tingkat` VARCHAR(5) NOT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
  `tingkat` (`id`, `nama_tingkat`)
VALUES
  (1, 'X'),
  (2, 'XI'),
  (3, 'XII');

CREATE TABLE
  `jurusan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_jurusan` VARCHAR(100) NOT NULL,
    `singkatan_jurusan` VARCHAR(10) NOT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
  `jurusan` (`id`, `nama_jurusan`, `singkatan_jurusan`)
VALUES
  (1, 'Rekayasa Perangkat Lunak', 'RPL'),
  (2, 'Teknik Pengelasan', 'TP'),
  (3, 'Desain Komunikasi Visual', 'DKV'),
  (4, 'Teknik Kendaraan Ringan', 'TKRO');

CREATE TABLE
  `kelas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tingkat_id` INT NOT NULL,
    `jurusan_id` INT NOT NULL,
    `rombel` VARCHAR(5) NOT NULL,
    FOREIGN KEY (`tingkat_id`) REFERENCES `tingkat` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
  `kelas` (`id`, `tingkat_id`, `jurusan_id`, `rombel`)
VALUES
  (1, 2, 1, 'B'), 
  (2, 2, 2, 'A'), 
  (3, 3, 3, 'B');

CREATE TABLE
  `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `nip` VARCHAR(18) NULL UNIQUE, -- NIP untuk Guru/Admin
    `nisn` VARCHAR(10) NULL UNIQUE, -- NISN untuk Ketua Kelas
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM ('super admin', 'admin', 'guru', 'ketua kelas') NOT NULL,
    `role_guru` VARCHAR(100) NULL COMMENT 'Jabatan/Mapel',
    `kelas_id` INT NULL COMMENT 'Khusus Ketua Kelas',
    `status` ENUM ('Aktif', 'Non-Aktif') NOT NULL DEFAULT 'Aktif',
    `foto` VARCHAR(255) NULL,
    `jenis_kelamin` ENUM ('Laki-laki', 'Perempuan') NULL,
    `alamat` TEXT NULL,
    `no_handphone` VARCHAR(15) NULL,
    FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Password hash untuk '123' adalah: $2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK
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
    `alamat`
  )
VALUES
  (
    1,
    'Super Admin',
    '534721709550598293',
    NULL,
    '$2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK',
    'super admin',
    'Waka Kurikulum',
    NULL,
    'Aktif',
    'Laki-laki',
    'Jl. Admin Pusat'
  ),
  (
    2,
    'Admin Piket',
    '526696256233636967',
    NULL,
    '$2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK',
    'admin',
    'Staf Piket',
    NULL,
    'Aktif',
    'Perempuan',
    'Ruang Piket Lt 1'
  ),
  (
    3,
    'Haris Ramadhani',
    '985317202580583981',
    NULL,
    '$2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK',
    'guru',
    'Guru RPL',
    NULL,
    'Aktif',
    'Laki-laki',
    'Bekasi'
  ),
  (
    4,
    'Bu Teti',
    '694086036736682988',
    NULL,
    '$2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK',
    'guru',
    'Guru Matematika',
    NULL,
    'Aktif',
    'Perempuan',
    'Jakarta'
  ),
  (
    5,
    'Ketua RPL B',
    NULL,
    '0003734048',
    '$2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK',
    'ketua kelas',
    'Siswa',
    1,
    'Aktif',
    'Laki-laki',
    'Kelas XI RPL B'
  ),
  (
    6,
    'Ketua TP A',
    NULL,
    '9960000413',
    '$2y$10$eHQcWh6WzABJAegTov5zWuiG9Yico.jqQEwdu2fTH0qF.l/W/HHrK',
    'ketua kelas',
    'Siswa',
    2,
    'Aktif',
    'Perempuan',
    'Kelas XI TP A'
  );

CREATE TABLE
  `mata_pelajaran` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_mapel` VARCHAR(100) NOT NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
  `mata_pelajaran` (`id`, `nama_mapel`)
VALUES
  (1, 'Pemrograman Web'),
  (2, 'Matematika'),
  (3, 'Bahasa Jepang');

CREATE TABLE
  `jadwal_mengajar` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `guru_nip` VARCHAR(18) NOT NULL,
    `kelas_id` INT NOT NULL,
    `mapel_id` INT NOT NULL,
    `hari` VARCHAR(15) NOT NULL, -- Monday, Tuesday, etc.
    `jam_mulai` TIME NOT NULL,
    `jam_selesai` TIME NOT NULL,
    FOREIGN KEY (`guru_nip`) REFERENCES `users` (`nip`) ON DELETE CASCADE,
    FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
  `jadwal_mengajar` (
    `guru_nip`,
    `kelas_id`,
    `mapel_id`,
    `hari`,
    `jam_mulai`,
    `jam_selesai`
  )
VALUES
  (
    '985317202580583981',
    2,
    1,
    'Thursday',
    '07:30:00',
    '10:00:00'
  ), -- Haris di XI TP A
  (
    '694086036736682988',
    1,
    2,
    'Thursday',
    '10:00:00',
    '12:00:00'
  ), -- Teti di XI RPL B
  (
    '985317202580583981',
    1,
    1,
    'Friday',
    '08:00:00',
    '11:00:00'
  );

CREATE TABLE
  `absensi_harian` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `guru_nip` VARCHAR(18) NOT NULL,
    `tanggal` DATE NOT NULL,
    `jam_datang` TIME NULL,
    `jam_pulang` TIME NULL,
    `status_kehadiran` ENUM (
      'Hadir',
      'Izin',
      'Sakit',
      'Dinas Luar',
      'Terlambat',
      'Alpha'
    ) NOT NULL DEFAULT 'Alpha',
    `keterangan` TEXT NULL,
    `file_bukti` VARCHAR(255) NULL,
    UNIQUE KEY `absen_harian_unik` (`guru_nip`, `tanggal`),
    FOREIGN KEY (`guru_nip`) REFERENCES `users` (`nip`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
  `absensi_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jadwal_id` INT NULL,
    `guru_nip` VARCHAR(18) NOT NULL,
    `tanggal` DATE NOT NULL,
    `jam_masuk` DATETIME NULL,
    `jam_keluar` DATETIME NULL,
    `status` ENUM (
      'Hadir',
      'Izin',
      'Sakit',
      'Terlambat',
      'Absen',
      'Dinas Luar',
      'Tepat Waktu',
      'Selesai',
      'Belum Mulai'
    ) NOT NULL DEFAULT 'Belum Mulai',
    `keterangan_izin` TEXT NULL,
    `file_bukti` VARCHAR(255) NULL,
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`guru_nip`) REFERENCES `users` (`nip`) ON DELETE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
  `koreksi_absensi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jadwal_id` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `keterangan_baru` ENUM ('Hadir', 'Izin', 'Sakit', 'Absen') NOT NULL,
    `alasan` TEXT NOT NULL,
    `status` ENUM ('Diajukan', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Diajukan',
    `catatan_admin` TEXT NULL,
    `diajukan_oleh_nisn` VARCHAR(10) NOT NULL,
    `diproses_oleh_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diajukan_oleh_nisn`) REFERENCES `users` (`nisn`) ON DELETE CASCADE,
    FOREIGN KEY (`diproses_oleh_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

SET
  FOREIGN_KEY_CHECKS = 1;

COMMIT;