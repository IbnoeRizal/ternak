/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `anak_kambing` (
  `id_kelahiran` int NOT NULL,
  `id_kambing_anak` int NOT NULL,
  PRIMARY KEY (`id_kelahiran`,`id_kambing_anak`),
  KEY `id_kambing_anak` (`id_kambing_anak`),
  CONSTRAINT `anak_kambing_ibfk_1` FOREIGN KEY (`id_kelahiran`) REFERENCES `kelahiran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `anak_kambing_ibfk_2` FOREIGN KEY (`id_kambing_anak`) REFERENCES `kambing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `kambing` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pemilik` int DEFAULT NULL,
  `id_induk` int DEFAULT NULL COMMENT 'FK ke id kambing (induk betina)',
  `id_pejantan` int DEFAULT NULL COMMENT 'FK ke id kambing (pejantan)',
  `id_kandang` int DEFAULT NULL COMMENT 'FK ke tabel kandang',
  `kode_tag` varchar(50) DEFAULT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('jantan','betina') DEFAULT NULL,
  `id_ras` int DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `status` enum('sehat','sakit','bunting','mati','dijual') DEFAULT 'sehat',
  `asal` enum('kelahiran','pembelian') NOT NULL DEFAULT 'pembelian',
  `harga_beli` decimal(12,2) DEFAULT '0.00',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_tag` (`kode_tag`),
  KEY `id_pemilik` (`id_pemilik`),
  KEY `fk_kambing_induk` (`id_induk`),
  KEY `fk_kambing_pejantan` (`id_pejantan`),
  KEY `fk_kambing_kandang` (`id_kandang`),
  KEY `fk_kambing_ras` (`id_ras`),
  CONSTRAINT `fk_kambing_induk` FOREIGN KEY (`id_induk`) REFERENCES `kambing` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kambing_kandang` FOREIGN KEY (`id_kandang`) REFERENCES `kandang` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kambing_pejantan` FOREIGN KEY (`id_pejantan`) REFERENCES `kambing` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kambing_ras` FOREIGN KEY (`id_ras`) REFERENCES `ras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kambing_ibfk_1` FOREIGN KEY (`id_pemilik`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `kambing` (`id`, `id_pemilik`, `id_induk`, `id_pejantan`, `id_kandang`, `kode_tag`, `nama`, `jenis_kelamin`, `id_ras`, `tanggal_lahir`, `status`, `asal`, `harga_beli`, `dibuat_pada`, `foto`) VALUES
	(1, 2, NULL, NULL, 1, 'KMB-J01', 'Raja', 'jantan', 1, '2023-02-15', 'sehat', 'pembelian', 5000000.00, '2025-07-08 09:07:14', 'kambing.jpg'),
	(3, 3, NULL, NULL, 2, 'KMB-B02', 'Manis', 'betina', 3, '2024-01-10', 'sehat', 'pembelian', 2000000.00, '2025-07-08 09:07:14', 'kambing.jpg'),
	(4, 2, NULL, NULL, 2, 'KMB-B03', 'Belang', 'betina', 2, '2023-08-20', 'dijual', 'pembelian', 3000000.00, '2025-07-08 09:07:14', 'kambing.jpg'),
	(5, 2, NULL, 1, 3, 'KMB-A01', 'Pangeran', 'jantan', 5, '2025-07-08', 'sehat', 'kelahiran', 0.00, '2025-07-08 09:07:14', 'kambing.jpg'),
	(6, 2, NULL, 1, 3, 'KMB-A02', 'Putri', 'betina', 5, '2025-07-08', 'sehat', 'kelahiran', 0.00, '2025-07-08 09:07:14', 'kambing.jpg'),
	(11, 2, NULL, NULL, 2, '12', 'kangdiki', 'jantan', 1, '2025-07-09', 'sehat', 'pembelian', 100000.00, '2025-07-09 07:56:31', 'kambing_1752047791_41272a51.jpg');

CREATE TABLE IF NOT EXISTS `kandang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_kandang` varchar(100) NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `kapasitas` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `kandang` (`id`, `nama_kandang`, `lokasi`, `kapasitas`) VALUES
	(1, 'Kandang A1 - Pejantan', 'Area Utara', 10),
	(2, 'Kandang B1 - Indukan', 'Area Barat', 25),
	(3, 'Kandang C1 - Anakan', 'Area Barat', 30);

CREATE TABLE IF NOT EXISTS `kategori_keuangan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `kategori_keuangan` (`id`, `nama`) VALUES
	(3, 'Biaya Kesehatan'),
	(4, 'Operasional Kandang'),
	(2, 'Pembelian Pakan'),
	(5, 'Pembelian Ternak'),
	(1, 'Penjualan Ternak');

CREATE TABLE IF NOT EXISTS `kelahiran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_reproduksi` int NOT NULL,
  `jumlah_anak` int DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `catatan` text,
  `foto_anak` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_reproduksi` (`id_reproduksi`),
  CONSTRAINT `kelahiran_ibfk_1` FOREIGN KEY (`id_reproduksi`) REFERENCES `reproduksi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `keuangan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengguna` int DEFAULT NULL,
  `jenis` enum('pemasukan','pengeluaran') DEFAULT NULL,
  `jumlah` decimal(12,2) DEFAULT NULL,
  `sumber_dana` enum('kas_peternakan','pribadi') NOT NULL DEFAULT 'kas_peternakan',
  `status_pembayaran` enum('lunas','belum_diganti') NOT NULL DEFAULT 'lunas',
  `id_kategori` int DEFAULT NULL,
  `id_kambing` int DEFAULT NULL,
  `deskripsi` text,
  `tanggal_transaksi` date DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengguna` (`id_pengguna`),
  KEY `fk_keuangan_kategori` (`id_kategori`),
  KEY `fk_keuangan_kambing` (`id_kambing`),
  CONSTRAINT `fk_keuangan_kambing` FOREIGN KEY (`id_kambing`) REFERENCES `kambing` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_keuangan_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_keuangan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `keuangan_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `keuangan` (`id`, `id_pengguna`, `jenis`, `jumlah`, `sumber_dana`, `status_pembayaran`, `id_kategori`, `id_kambing`, `deskripsi`, `tanggal_transaksi`, `bukti`) VALUES
	(1, 2, 'pengeluaran', 5000000.00, 'kas_peternakan', 'lunas', 5, 1, 'Pembelian pejantan Boer KMB-J01', '2023-02-15', 'kambing.jpg'),
	(2, 2, 'pengeluaran', 3500000.00, 'kas_peternakan', 'lunas', 5, NULL, 'Pembelian indukan Etawa KMB-B01', '2023-04-01', 'kambing.jpg'),
	(3, 2, 'pengeluaran', 150000.00, 'pribadi', 'belum_diganti', 3, 3, 'Beli Vitamin Ternak Super pakai uang pribadi', '2025-06-20', 'kambing.jpg'),
	(4, 2, 'pemasukan', 4500000.00, 'kas_peternakan', 'lunas', 1, 4, 'Penjualan kambing Betina KMB-B03', '2025-07-05', 'kambing.jpg'),
	(5, 2, 'pengeluaran', 250000.00, 'pribadi', 'belum_diganti', 2, NULL, 'Beli pakan darurat 1 karung, talangan dulu', '2025-07-07', 'kambing.jpg'),
	(6, 2, 'pengeluaran', 12000.00, 'pribadi', 'belum_diganti', 4, NULL, 'x c', '2025-07-09', 'kambing.jpg'),
	(7, 2, 'pemasukan', 12000.00, 'kas_peternakan', 'lunas', 2, NULL, 'wwqwqw', '2025-07-09', 'kambing.jpg'),
	(8, 2, 'pengeluaran', 20000.00, 'kas_peternakan', 'lunas', 2, NULL, '1', '2025-07-09', 'kambing.jpg'),
	(11, 1, 'pengeluaran', 1777777.00, 'kas_peternakan', 'lunas', 4, 1, 'w', '2025-07-09', 'kambing.jpg'),
	(12, 1, 'pemasukan', 10000.00, 'kas_peternakan', 'lunas', 3, 4, 'qww', '2025-07-09', 'kambing.jpg'),
	(13, 1, 'pemasukan', 10000.00, 'kas_peternakan', 'lunas', 3, 4, 'qww', '2025-07-09', 'kambing.jpg'),
	(14, 1, 'pemasukan', 10000.00, 'kas_peternakan', 'lunas', 3, 4, 'qqwq', '2025-07-09', 'kambing.jpg');

CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengguna` int DEFAULT NULL,
  `pesan` text,
  `dibaca` tinyint(1) DEFAULT '0',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pengguna` (`id_pengguna`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `notifikasi` (`id`, `id_pengguna`, `pesan`, `dibaca`, `dibuat_pada`) VALUES
	(1, 2, 'Kambing "Ratu" (KMB-B01) telah melahirkan 2 ekor anak.', 0, '2025-07-08 09:07:15'),
	(2, 1, 'Ada 2 transaksi pengeluaran dari dana pribadi yang belum diganti.', 0, '2025-07-08 09:07:15');

CREATE TABLE IF NOT EXISTS `pengguna` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `peran` enum('admin','peternak','penitip') NOT NULL DEFAULT 'penitip',
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `pengguna` (`id`, `nama`, `email`, `password`, `peran`, `nomor_telepon`, `alamat`, `dibuat_pada`, `foto`) VALUES
	(1, 'Admin Utama', 'admin@kandang.com', 'admin', 'admin', '081234567890', 'Jl. Admin No. 1', '2025-07-08 09:07:14', 'tidakada.jpg'),
	(2, 'Budi Peternak', 'peternak@kandang.com', 'peternak', 'peternak', '081234567891', 'Jl. Ternak No. 2', '2025-07-08 09:07:14', 'tidakada.jpg'),
	(3, 'Citra Penitip', 'penitip@kandang.com', 'penitip', 'penitip', '081234567892', 'Jl. Titip No. 3', '2025-07-08 09:07:14', 'tidakada.jpg');

CREATE TABLE IF NOT EXISTS `penjualan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kambing` int NOT NULL,
  `tanggal_jual` date NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `id_pembeli` int DEFAULT NULL,
  `catatan` text,
  PRIMARY KEY (`id`),
  KEY `id_kambing` (`id_kambing`),
  KEY `id_pembeli` (`id_pembeli`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_kambing`) REFERENCES `kambing` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`id_pembeli`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `penjualan` (`id`, `id_kambing`, `tanggal_jual`, `harga_jual`, `id_pembeli`, `catatan`) VALUES
	(1, 4, '2025-07-05', 4500000.00, 3, 'Dijual ke penitip Citra untuk kurban.');

CREATE TABLE IF NOT EXISTS `ras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_ras` varchar(100) NOT NULL,
  `deskripsi` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_ras_unique` (`nama_ras`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `ras` (`id`, `nama_ras`, `deskripsi`) VALUES
	(1, 'Boer', 'Ras kambing pedaging unggul dari Afrika Selatan.'),
	(2, 'Etawa', 'Ras kambing perah dan pedaging, dikenal juga sebagai Jamnapari.'),
	(3, 'Gibas', 'Ras kambing lokal Indonesia, dikenal juga sebagai kambing kacang.'),
	(4, 'Saanen', 'Ras kambing perah unggul dari Swiss, produksi susu tinggi.'),
	(5, 'Etawa-Boer', 'Hasil persilangan antara Etawa dan Boer.');

CREATE TABLE IF NOT EXISTS `reproduksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_betina` int DEFAULT NULL,
  `id_jantan` int DEFAULT NULL,
  `tanggal_kawin` date DEFAULT NULL,
  `bunting` tinyint(1) DEFAULT '0',
  `perkiraan_lahir` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_betina` (`id_betina`),
  KEY `id_jantan` (`id_jantan`),
  CONSTRAINT `reproduksi_ibfk_1` FOREIGN KEY (`id_betina`) REFERENCES `kambing` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reproduksi_ibfk_2` FOREIGN KEY (`id_jantan`) REFERENCES `kambing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `riwayat_berat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kambing` int NOT NULL,
  `berat` decimal(5,2) NOT NULL,
  `tanggal_timbang` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kambing` (`id_kambing`),
  CONSTRAINT `riwayat_berat_ibfk_1` FOREIGN KEY (`id_kambing`) REFERENCES `kambing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `riwayat_berat` (`id`, `id_kambing`, `berat`, `tanggal_timbang`) VALUES
	(1, 1, 60.50, '2025-07-01'),
	(3, 3, 35.00, '2025-07-01'),
	(4, 5, 8.50, '2025-07-08'),
	(5, 6, 7.80, '2025-07-08');

CREATE TABLE IF NOT EXISTS `riwayat_kesehatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kambing` int DEFAULT NULL,
  `tanggal_periksa` date DEFAULT NULL,
  `kondisi` text,
  `penanganan` text,
  `obat_yang_diberikan` text,
  `jadwal_periksa_berikutnya` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kambing` (`id_kambing`),
  CONSTRAINT `riwayat_kesehatan_ibfk_1` FOREIGN KEY (`id_kambing`) REFERENCES `kambing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `riwayat_kesehatan` (`id`, `id_kambing`, `tanggal_periksa`, `kondisi`, `penanganan`, `obat_yang_diberikan`, `jadwal_periksa_berikutnya`) VALUES
	(1, 3, '2025-06-20', 'Nafsu makan sedikit menurun', 'Pemberian vitamin tambahan', 'Vitamin Ternak Super', NULL);

CREATE TABLE IF NOT EXISTS `riwayat_pakan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_kambing` int DEFAULT NULL,
  `jenis_pakan` varchar(100) DEFAULT NULL,
  `jumlah_kg` decimal(5,2) DEFAULT NULL,
  `waktu_pakan` datetime DEFAULT NULL,
  `catatan` text,
  PRIMARY KEY (`id`),
  KEY `id_kambing` (`id_kambing`),
  CONSTRAINT `riwayat_pakan_ibfk_1` FOREIGN KEY (`id_kambing`) REFERENCES `kambing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `riwayat_pakan` (`id`, `id_kambing`, `jenis_pakan`, `jumlah_kg`, `waktu_pakan`, `catatan`) VALUES
	(1, 1, 'Konsentrat A + Rumput', 2.50, '2025-07-08 08:00:00', NULL);

CREATE TABLE IF NOT EXISTS `stok_barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(150) NOT NULL,
  `jenis` enum('pakan','obat','vitamin','lainnya') NOT NULL,
  `satuan` varchar(50) DEFAULT NULL COMMENT 'Contoh: kg, liter, botol',
  `jumlah_stok` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `stok_barang` (`id`, `nama_barang`, `jenis`, `satuan`, `jumlah_stok`) VALUES
	(1, 'Konsentrat A', 'pakan', 'kg', 150.50),
	(2, 'Rumput Gajah', 'pakan', 'kg', 500.00),
	(3, 'Vitamin Ternak Super', 'vitamin', 'botol', 20.00),
	(4, 'Obat Cacing', 'obat', 'tablet', 100.00);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
