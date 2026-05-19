-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Bulan Mei 2026 pada 19.25
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbtiket`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `nama_event` varchar(255) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `status` enum('aktif','selesai') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id`, `organizer_id`, `nama_event`, `lokasi`, `tanggal`, `tanggal_selesai`, `jam_mulai`, `jam_selesai`, `deskripsi`, `banner`, `status`, `created_at`) VALUES
(6, 1, 'TYG CUP 2026', 'Pasaman Timur', '2026-05-29', '2026-06-02', '08:00:00', '00:00:00', 'Turnament futsal bergengsi TYG CUP Pasaman Timur 2026', '1778919713_6a08292194372.webp', 'aktif', '2026-05-16 08:21:53'),
(7, 2, 'Badminton CUP 2026', 'Padang', '2026-05-18', '2026-05-18', '08:00:00', '23:00:00', 'Testing', '1779021769_6a09b7c9a2e58.webp', 'selesai', '2026-05-17 12:42:49'),
(8, 3, 'Badminton CUP 2026 II', 'Padang', '2026-05-18', '2026-05-18', '08:00:00', '20:40:00', 'QWEQWEQ', '1779111436_6a0b160c07807.jpg', 'selesai', '2026-05-18 13:37:16'),
(9, 3, 'volly', 'padang', '2026-05-18', '2026-05-19', '08:00:00', '23:00:00', 'dqwdas', '1779111665_6a0b16f1814c5.jpg', 'selesai', '2026-05-18 13:41:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `event_baru`
--

CREATE TABLE `event_baru` (
  `id` int(100) NOT NULL,
  `nama_evnt` varchar(255) NOT NULL,
  `sub_judul` varchar(255) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `tipe_event` enum('berbayar''gratis') NOT NULL,
  `kategori_event` enum('offline''online''hybrid') NOT NULL,
  `banner_event` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeli`
--

CREATE TABLE `pembeli` (
  `id` int(100) NOT NULL,
  `nama_pembeli` varchar(255) NOT NULL,
  `jenis_kelamin` enum('laki laki''perempuan') NOT NULL,
  `tgl_lahir` date NOT NULL,
  `alamat_peserta` varchar(255) NOT NULL,
  `nama_di_BIB` varchar(255) NOT NULL,
  `ukuran_jersey` varchar(255) NOT NULL,
  `riwayat_penyakit` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesan`
--

CREATE TABLE `pemesan` (
  `id` int(100) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `promotor`
--

CREATE TABLE `promotor` (
  `id` int(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_brand` varchar(255) NOT NULL,
  `deskripsi_singkat` varchar(500) NOT NULL,
  `email_bisnis` varchar(255) NOT NULL,
  `no_wa` int(25) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `banner` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promotor`
--

INSERT INTO `promotor` (`id`, `user_id`, `nama_brand`, `deskripsi_singkat`, `email_bisnis`, `no_wa`, `logo`, `banner`, `status`, `created_at`) VALUES
(1, 6, 'promotor1', 'Promotor 1', 'testing@gmail.com', 123, '', '', 'approved', '2026-05-02 19:47:22'),
(2, 15, 'Promotor2', 'Testing', 'promotor2@gmail.com', 123, '', '', 'approved', '2026-05-17 12:30:48'),
(3, 17, 'pm1', 'gkjhihjk', 'budi@gmail.com', 98765432, '', '', 'approved', '2026-05-18 13:26:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `promotor_staff`
--

CREATE TABLE `promotor_staff` (
  `id` int(11) NOT NULL,
  `promotor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_staff` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promotor_staff`
--

INSERT INTO `promotor_staff` (`id`, `promotor_id`, `user_id`, `nama_staff`, `created_at`) VALUES
(1, 1, 13, 'staff 1', '2026-05-15 15:20:23'),
(2, 2, 16, 'staff 2', '2026-05-17 12:45:01'),
(3, 3, 18, 'bagus', '2026-05-18 13:35:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settlements`
--

CREATE TABLE `settlements` (
  `id` int(11) NOT NULL,
  `promotor_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `total_sales` int(11) DEFAULT 0,
  `platform_fee` int(11) DEFAULT 0,
  `qris_fee` int(11) DEFAULT 0,
  `net_amount` int(11) DEFAULT 0,
  `status` enum('pending','approved','paid','rejected') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `nama_tiket` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tickets`
--

INSERT INTO `tickets` (`id`, `event_id`, `nama_tiket`, `harga`, `stok`, `created_at`) VALUES
(11, 6, 'umum', 3000, 998, '2026-05-16 08:21:53'),
(12, 7, 'SELATAN', 50000, 32, '2026-05-17 12:42:49'),
(13, 7, 'UTARA', 50000, 50, '2026-05-17 12:42:49'),
(14, 8, 'Umum', 12000, 799, '2026-05-18 13:37:16'),
(15, 9, 'Barat', 10000, 481, '2026-05-18 13:41:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `gross_total` int(11) DEFAULT NULL,
  `platform_fee` int(11) DEFAULT NULL,
  `payment_gateway_fee` int(11) DEFAULT 0,
  `promoter_income` int(11) DEFAULT NULL,
  `net_promoter_income` int(11) DEFAULT 0,
  `total` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_detail` varchar(100) DEFAULT NULL,
  `ticket_code` varchar(100) DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_status` enum('unused','used') DEFAULT 'unused',
  `used_at` datetime DEFAULT NULL,
  `checkin_method` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `event_id`, `ticket_id`, `qty`, `gross_total`, `platform_fee`, `payment_gateway_fee`, `promoter_income`, `net_promoter_income`, `total`, `payment_method`, `payment_detail`, `ticket_code`, `status`, `created_at`, `used_status`, `used_at`, `checkin_method`) VALUES
(6, 14, 6, 11, 2, 6000, 500, 42, 5500, 5458, 6000, 'qris', 'DANA - 083182004753', 'KZ-20260517-36102A38', 'paid', '2026-05-17 12:10:57', 'used', '2026-05-17 19:21:36', 'manual'),
(7, 14, 7, 12, 1, 50000, 2500, 350, 47500, 47150, 50000, 'qris', 'DANA - 083182004753', 'KZ-20260517-E0906A99', 'paid', '2026-05-17 12:43:44', 'used', '2026-05-17 19:45:34', 'manual'),
(8, 17, 7, 12, 17, 850000, 42500, 5950, 807500, 801550, 850000, 'qris', 'DANA - 083182004753', 'KZ-20260518-1DCE8A19', 'paid', '2026-05-18 13:23:37', 'unused', NULL, NULL),
(9, 19, 8, 14, 1, 12000, 600, 84, 11400, 11316, 12000, 'qris', 'DANA - 083182004753', 'KZ-20260518-41907235', 'paid', '2026-05-18 13:38:38', 'used', '2026-05-18 20:43:28', 'manual'),
(10, 19, 9, 15, 18, 180000, 9000, 1260, 171000, 169740, 180000, 'qris', 'DANA - 083182004753', 'KZ-20260518-F0092216', 'paid', '2026-05-18 13:41:35', 'unused', NULL, NULL),
(11, 19, 9, 15, 1, 10000, 500, 70, 9500, 9430, 10000, 'qris', 'DANA - 083182004753', 'KZ-20260518-1CB616CA', 'paid', '2026-05-18 13:51:00', 'unused', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `role` enum('customer','organizer','superadmin','staff_gate') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `no_whatsapp`, `role`, `created_at`, `profile_image`, `status`) VALUES
(6, 'testing', 'testing@gmail.com', '$2y$10$Y.xPfMLhE/iu8oXEPKu4ceCjNGxtaElsROj1RLO8GoHSmRlsATfYO', '123', 'organizer', '2026-05-01 17:48:13', NULL, 'approved'),
(10, 'customer', 'customer@gmail.com', '$2y$10$59tKRDlni98rsQBcyQze.u/GvT/j8eVwIl7Rjoz9gWffGTCbJuLBm', '123', 'customer', '2026-05-02 19:55:07', NULL, 'pending'),
(11, 'superadmin1', 'superadmin1@karciz.com', '$2y$10$4sO.vOcI7TGM/l6Ks09.OOZR4bPzMqrhQyMHPvfL4P7xsmLdCxTRm', '08123456789', 'superadmin', '2026-05-13 20:22:16', NULL, 'pending'),
(12, 'adin', 'adinda@gmail.com', '$2y$10$54uwWQpPiPD9/uOEaajI1uqKISGgrbsHh0CHJP7jXJmq0CTPvj6Ey', '0', 'customer', '2026-05-14 10:25:50', '1778919325_KTP.jpeg', 'pending'),
(13, 'staff1', 'staff@gmail.com', '$2y$10$SingYtJiZhsZRVzvHORiMuyIljMkh1yoM1c38W/t07lwsP7Sw5jD6', NULL, 'staff_gate', '2026-05-15 15:20:23', NULL, 'approved'),
(14, 'customer 2', 'customer2@gmail.com', '$2y$10$4A8cVcE9WEyo4A2pHKB4r.qZxOwXof2A1VlJ5llFRyyjw5g0Zx0I.', '123', 'customer', '2026-05-17 12:00:28', 'profile_1779019279_6a09ae0fa03ef.jpg', 'pending'),
(15, 'promotor2', 'promotor2@gmail.com', '$2y$10$pR6yC8uN8nzLqN6nWasiNOMilL0X36MuyRtJN9hCGbl7ka2IjFjnC', '123', 'organizer', '2026-05-17 12:27:15', 'default-profile.png', 'approved'),
(16, 'staff 2', 'staff2@gmail.com', '$2y$10$Yc.KXufYB/lEnfFkX9RVI.Fxo3.FTBHm6rmgXzVeg.dsQRffICN3K', NULL, 'staff_gate', '2026-05-17 12:45:01', NULL, 'approved'),
(17, 'budi', 'budi@gmail.com', '$2y$10$Of65rCRuUY.aG9HQ2ZnLkOar7SmAGQCBViStCtbHTQJrApKzNXR8y', '098765432', 'organizer', '2026-05-18 13:20:38', 'default-profile.png', 'approved'),
(18, 'bagus', 'bagus@gmail.com', '$2y$10$Eqn5J0rbcCxY0h5C8OQJ4eOcR4bAroX2URUbEq32IW800KKOKdV4S', NULL, 'staff_gate', '2026-05-18 13:35:15', NULL, 'approved'),
(19, 'cs', 'cs@gsmsad.com', '$2y$10$vTAQjIbNv67lb3Q387tWE.8NxqlJTyWQ/V/niivIL06YuOKI4EYyi', '12345', 'customer', '2026-05-18 13:38:17', 'default-profile.png', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `event_baru`
--
ALTER TABLE `event_baru`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pemesan`
--
ALTER TABLE `pemesan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `promotor`
--
ALTER TABLE `promotor`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `promotor_staff`
--
ALTER TABLE `promotor_staff`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `settlements`
--
ALTER TABLE `settlements`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `event_baru`
--
ALTER TABLE `event_baru`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pemesan`
--
ALTER TABLE `pemesan`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `promotor`
--
ALTER TABLE `promotor`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `promotor_staff`
--
ALTER TABLE `promotor_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `settlements`
--
ALTER TABLE `settlements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
