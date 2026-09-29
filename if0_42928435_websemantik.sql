-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Waktu pembuatan: 28 Sep 2026 pada 04.58
-- Versi server: 11.4.13-MariaDB
-- Versi PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42928435_websemantik`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `fakultas`
--

CREATE TABLE `fakultas` (
  `kode_fakultas` varchar(10) NOT NULL,
  `nama_fakultas` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `fakultas`
--

INSERT INTO `fakultas` (`kode_fakultas`, `nama_fakultas`) VALUES
('FE', 'Fakultas Ekonomi'),
('FH', 'Fakultas Hukum'),
('FKIP', 'Fakultas  Keguruan dan Ilmu Pendidikan'),
('FT', 'Fakultas Teknik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `npm` varchar(15) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `kode_prodi` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`npm`, `id_pengguna`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `tanggal_masuk`, `alamat`, `kode_prodi`) VALUES
('2126201001', 3, 'L', 'Bengkulu', '2003-05-14', '2021-08-01', 'Jl. Merdeka No. 12, Bengkulu', 'TI'),
('2126201002', 4, 'P', 'Curup', '2003-11-02', '2021-08-01', 'Jl. Sudirman No. 5, Curup', 'TI'),
('2126201003', 5, 'L', 'Manna', '2002-09-20', '2021-08-01', 'Jl. Raya Manna No. 8', 'TE');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengelola_prodi`
--

CREATE TABLE `pengelola_prodi` (
  `id_pengelola` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `kode_prodi` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pengelola_prodi`
--

INSERT INTO `pengelola_prodi` (`id_pengelola`, `id_pengguna`, `kode_prodi`) VALUES
(1, 2, 'TI');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','prodi','mahasiswa') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `username`, `password`, `nama_lengkap`, `role`, `email`, `created_at`) VALUES
(1, 'admin', 'admin123', 'Administrator Sistem', 'admin', 'admin@umb.ac.id', '2026-09-28 08:52:16'),
(2, 'kaprodi_ti', 'prodi123', 'Ahmad Fauzi, M.Kom', 'prodi', 'kaprodi.ti@umb.ac.id', '2026-09-28 08:52:16'),
(3, 'andi123', 'password123', 'Andi Saputra', 'mahasiswa', 'andi@student.umb.ac.id', '2026-09-28 08:52:16'),
(4, 'siti123', 'password123', 'Siti Aminah', 'mahasiswa', 'siti@student.umb.ac.id', '2026-09-28 08:52:16'),
(5, 'budi123', 'password123', 'Budi Santoso', 'mahasiswa', 'budi@student.umb.ac.id', '2026-09-28 08:52:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `program_studi`
--

CREATE TABLE `program_studi` (
  `kode_prodi` varchar(10) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `kode_fakultas` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `program_studi`
--

INSERT INTO `program_studi` (`kode_prodi`, `nama_prodi`, `kode_fakultas`) VALUES
('HK', 'Ilmu Hukum', 'FH'),
('MJ', 'Manajemen', 'FE'),
('PB', 'Pendidikan Biologi', 'FKIP'),
('TE', 'Teknik Elektro', 'FT'),
('TI', 'Teknik Informatika', 'FT');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `fakultas`
--
ALTER TABLE `fakultas`
  ADD PRIMARY KEY (`kode_fakultas`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`npm`),
  ADD UNIQUE KEY `uq_mahasiswa_pengguna` (`id_pengguna`),
  ADD KEY `fk_mahasiswa_prodi` (`kode_prodi`);

--
-- Indeks untuk tabel `pengelola_prodi`
--
ALTER TABLE `pengelola_prodi`
  ADD PRIMARY KEY (`id_pengelola`),
  ADD UNIQUE KEY `uq_pengguna_prodi` (`id_pengguna`,`kode_prodi`),
  ADD KEY `fk_pengelola_prodi` (`kode_prodi`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `uq_username` (`username`);

--
-- Indeks untuk tabel `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`kode_prodi`),
  ADD KEY `fk_prodi_fakultas` (`kode_fakultas`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pengelola_prodi`
--
ALTER TABLE `pengelola_prodi`
  MODIFY `id_pengelola` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `fk_mahasiswa_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mahasiswa_prodi` FOREIGN KEY (`kode_prodi`) REFERENCES `program_studi` (`kode_prodi`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengelola_prodi`
--
ALTER TABLE `pengelola_prodi`
  ADD CONSTRAINT `fk_pengelola_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengelola_prodi` FOREIGN KEY (`kode_prodi`) REFERENCES `program_studi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `program_studi`
--
ALTER TABLE `program_studi`
  ADD CONSTRAINT `fk_prodi_fakultas` FOREIGN KEY (`kode_fakultas`) REFERENCES `fakultas` (`kode_fakultas`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
