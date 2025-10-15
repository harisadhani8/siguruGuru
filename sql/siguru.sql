
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guru','admin','ketua kelas','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `nama`, `password`, `role`) VALUES
(1, 'Guru1', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'guru'),
(2, 'Guru2', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'guru'),
(3, 'Guru3', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'guru'),
(4, 'Guru4', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'guru'),
(5, 'Guru5', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'guru'),
(7, 'Guru7', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'guru'),
(8, 'Admin', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'admin'),
(9, 'Ketua Kelas', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'ketua kelas'),
(10, 'Staff', '$2y$10$n2iNffj532oY.9F/E1v.8.LpA9bL5eJ3vG7hB8kZ4aK2lO7iP6eWq', 'staff');

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` enum('Hadir','Sakit','Izin','Dinas Luar') NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `keterangan`, `catatan`, `created_at`) VALUES
(1, 4, '2025-08-08', 'Hadir', '', '2025-10-09 07:50:11'),
(2, 1, '2025-08-08', 'Hadir', '', '2025-10-09 07:50:11'),
(3, 3, '2025-08-08', 'Hadir', '', '2025-10-09 07:50:11'),
(4, 7, '2025-08-08', 'Sakit', 'Demam tinggi.', '2025-10-09 07:50:11'),
(5, 2, '2025-08-08', 'Dinas Luar', 'Rapat di dinas pendidikan.', '2025-10-09 07:50:11'),
(6, 5, '2025-08-08', 'Izin', 'Acara keluarga.', '2025-10-09 07:50:11');

CREATE TABLE `koreksi_absensi` (
  `id` int(11) NOT NULL,
  `absensi_id` int(11) NOT NULL,
  `alasan_koreksi` text NOT NULL,
  `diajukan_oleh` int(11) NOT NULL,
  `status` enum('Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Diajukan',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users` ADD PRIMARY KEY (`id`);
ALTER TABLE `absensi` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);
ALTER TABLE `koreksi_absensi` ADD PRIMARY KEY (`id`), ADD KEY `absensi_id` (`absensi_id`), ADD KEY `diajukan_oleh` (`diajukan_oleh`);

ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `absensi` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `koreksi_absensi` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absensi` ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `koreksi_absensi`
  ADD CONSTRAINT `koreksi_absensi_ibfk_1` FOREIGN KEY (`absensi_id`) REFERENCES `absensi` (`id`),
  ADD CONSTRAINT `koreksi_absensi_ibfk_2` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`);
COMMIT;