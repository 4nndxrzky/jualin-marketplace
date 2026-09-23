-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 23, 2026 at 08:03 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `olx_clone`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
    `id` int NOT NULL,
    `user_id` int NOT NULL,
    `category_id` int NOT NULL,
    `title` varchar(50) NOT NULL,
    `description` text,
    `price` decimal(15, 2) NOT NULL,
    `location` varchar(100) DEFAULT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `location`, `created_at`) VALUES
(2, 2, 1, 'bmw m3 (2022)', 'Bonus!!!\r\n\r\n-Free Towing selama 1tahun\r\n\r\n-Lulus otospector\r\n\r\n-Autofame siap (General Check Up) ke bengkel resmi untuk membuktikan mobil kita berkualitas\r\n\r\nTerm and Condition\r\n\r\n.\r\n\r\nKM 9.000 BMW M3C Competition xDrive Sedan NIK 2022 Toronto Red Metallic On Black\r\n\r\nPlat Ganjil, Toronto Red Metallic On Black, NIK 2022, Pajak Sangat Panjang Sampai 06 2027, KM 9.000 PERAK, BMW M3 Competition Sedan xDrive 3.0L Twin-Turbocharged Inline-6, 510HP, All Wheel Drive, Power Back Door, Parking Assistance Plus with Sorround View, BMW Laserlight Headlamp, Msport Exhaust, Adaptive M Suspension, Active M Differential, M Compound Brakes, 19\"/20\" inch M Forged Wheels, Black Merino Leather Seats, M Carbon Fiber Instrument, BMW Live Cockpit Professional with 12,3inch Instrument Display, Control Display, Leather/Carbon Steering Wheel, Premium Sorround Harman-Kardon, Tinggal Pakai.\r\n\r\n.\r\n\r\n.\r\n\r\nFor Cash : Rp 1.665 M\r\n\r\nFor Credit : Rp 1.585 M\r\n\r\n.\r\n\r\n3 Tahun\r\n\r\nTDP : Rp 475.587.896 Juta\r\n\r\nAngsuran : Rp 44.387.953 Juta X 35\r\n\r\n.\r\n\r\n4 Tahun\r\n\r\nTDP : Rp 480.473.679 Juta\r\n\r\nAngsuran : Rp. 35.438.769 Juta X 47\r\n\r\n.\r\n\r\nTerima Tukar Tambah Mobil lama Anda\r\n\r\nHarga Bisa Nego Langsung Di Tempat Sampai Deal\r\n\r\nAUTOFAME MENERIMA\r\n\r\n•Menerima Jual Beli dan Tukar Tambah\r\n\r\n•Pembayaran bisa dengan (Cash / Kredit)\r\n\r\n•Autofame siap (General Check Up) ke bengkel resmi untuk membuktikan mobil kita berkualitas\r\n\r\n.\r\n\r\nSearch google : Autofame\r\n\r\nSearch waze : Autofame\r\n\r\n.\r\n\r\nBuka Setiap Hari: 09:00 - 18.00\r\n\r\n(Sabtu / Minggu / Tanggal Merah tetap buka yaaa!!)\r\n\r\n-QUALITY IS OUR PRIORITY-\r\n\r\n.\r\n\r\nAUTOFame Cibubur\r\n\r\nKota Wisata Somerset N 8 no. 5\r\n\r\nJalan Transyogie Km.6 Cibubur\r\n\r\n.\r\n\r\nAUTOFame Mangga dua square)\r\n\r\nBursa otomotif Mangga dua square (BOM\'s) Lot L18-23\r\n\r\nJalan Gunung Sahari No. 1 Jakarta utara', '1585000000.00', 'Cilandak, Jakarta Selatan', '2026-09-23 11:31:06'),
(3, 1, 1, 'Toyota Avanza 1.3 G MT 2020 Putih Mulus Terawat', 'Toyota Avanza 1.3 G Manual tahun 2020 warna Putih Mutiara. Tangan pertama dari baru, service record bengkel resmi Toyota. Odometer 38.500 km.', '185000000.00', 'Jakarta Selatan', '2026-09-20 10:00:00'),
(4, 1, 4, 'iPhone 15 Pro Max 256GB Natural Titanium Fullset', 'iPhone 15 Pro Max 256GB warna Natural Titanium. Garansi resmi iBox aktif, battery health 98%, kelengkapan fullset original mulus tanpa lecet.', '18500000.00', 'Bandung', '2026-09-19 14:20:00'),
(5, 1, 3, 'Rumah Cluster Minimalis 2 Lantai Siap Huni', 'Rumah baru cluster modern minimalis 2 lantai di lokasi strategis BSD / Tangerang Selatan. 3 kamar tidur, 2 kamar mandi, SHM, bebas banjir.', '750000000.00', 'Tangerang Selatan', '2026-09-19 09:15:00'),
(6, 1, 4, 'MacBook Air M2 2023 8/256GB Midnight Garansi Resmi', 'MacBook Air M2 Chip 2023 warna Midnight. RAM 8GB SSD 256GB, cycle count rendah 45, garansi resmi aktif. Kondisi like new pemakaian pribadi.', '14200000.00', 'Surabaya', '2026-09-18 16:45:00'),
(7, 1, 2, 'Honda Beat Street 2023 Hitam KM Rendah Pajak Panja', 'Honda Beat Street CBS 2023 warna hitam doff. Plat D, surat lengkap STNK BPKB faktur, pajak panjang, KM 8.000 asli terawat siap pakai.', '16500000.00', 'Yogyakarta', '2026-09-18 11:30:00'),
(8, 1, 5, 'Sofa L Minimalis Bahan Oscar Anti Air Bisa Custom', 'Sofa sudut model L modern untuk ruang keluarga. Menggunakan busa royal density tinggi dan bahan oscar premium tahan tumpahan air.', '3200000.00', 'Bekasi', '2026-09-17 15:10:00'),
(9, 1, 7, 'PS5 Slim Digital Edition + 2 Controller Fullset Bo', 'PlayStation 5 Slim Digital Version. Lengkap dengan 2 stik DualSense wireless controller, kabel HDMI ultra high speed, dus dan nota pembelian.', '6800000.00', 'Medan', '2026-09-17 13:00:00'),
(10, 1, 7, 'Sepeda Lipat 20 Inch 7 Speed Shimano Mulus', 'Sepeda lipat frame alloy ringan 20 inch dengan groupset Shimano 7 speed. Rem cakram pakem, ban kenda tebal, mudah dilipat masuk bagasi mobil.', '2500000.00', 'Semarang', '2026-09-16 08:30:00'),
(11, 1, 4, 'Samsung Galaxy S24 Ultra 12/256 Titanium Gray SEIN', 'Samsung Galaxy S24 Ultra 12GB RAM 256GB ROM Titanium Gray garansi resmi SEIN Indonesia. Fitur Galaxy AI lengkap, kamera 200MP tajam.', '15900000.00', 'Depok', '2026-09-15 17:00:00'),
(12, 1, 5, 'Standing Desk Elektrik 120x60 Adjustable Height', 'Meja kerja elektrik dengan fitur naik turun otomatis dual motor. Memori 4 ketinggian preset, material kayu solid tebal kokoh.', '1850000.00', 'Bogor', '2026-09-14 10:20:00'),
(13, 1, 7, 'Canon EOS R50 Kit 18-45mm IS STM Garansi Datascrip', 'Kamera mirrorless Canon EOS R50 plus lensa kit 18-45mm STM. Sangat cocok untuk content creator YouTube/TikTok, sensor 24MP 4K video.', '11200000.00', 'Malang', '2026-09-13 14:40:00'),
(14, 1, 3, 'Kost Eksklusif Bulanan Full Furnished AC WiFi UI', 'Kamar kost eksklusif mahasiswa / karyawan dekat stasiun dan kampus UI. Fasilitas lengkap AC, WiFi, kamar mandi dalam water heater, kasur springbed.', '2000000.00', 'Depok', '2026-09-12 19:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ad_images`
--

CREATE TABLE `ad_images` (
    `id` int NOT NULL,
    `ad_id` int NOT NULL,
    `image_path` varchar(255) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ad_images`
--

INSERT INTO
    `ad_images` (`id`, `ad_id`, `image_path`)
VALUES (
        1,
        2,
        'uploads/ads/ad_72ec9dd8799d29c4_1790137866.jpg'
    ),
    (
        2,
        2,
        'uploads/ads/ad_fe713594495e20d0_1790137866.jpg'
    ),
    (
        3,
        2,
        'uploads/ads/ad_3bf0553371cec8c0_1790137866.jpg'
    ),
    (
        4,
        2,
        'uploads/ads/ad_4e042ad50f386304_1790137866.jpg'
    );

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
    `id` int NOT NULL,
    `name` varchar(50) NOT NULL,
    `icon` varchar(100) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO
    `categories` (`id`, `name`, `icon`)
VALUES (1, 'Mobil', 'fa-solid fa-car'),
    (
        2,
        'Motor',
        'fa-solid fa-motorcycle'
    ),
    (
        3,
        'Properti',
        'fa-solid fa-house'
    ),
    (
        4,
        'Elektronik & Gadget',
        'fa-solid fa-mobile-screen-button'
    ),
    (
        5,
        'Perabotan Rumah Tangga',
        'fa-solid fa-couch'
    ),
    (
        6,
        'Fashion & Pakaian',
        'fa-solid fa-shirt'
    ),
    (
        7,
        'Hobi & Olahraga',
        'fa-solid fa-futbol'
    ),
    (
        8,
        'Jasa & Layanan',
        'fa-solid fa-wrench'
    ),
    (
        9,
        'Lowongan Kerja',
        'fa-solid fa-briefcase'
    ),
    (
        10,
        'Keperluan Lainnya',
        'fa-solid fa-box-open'
    );

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
    `id` int NOT NULL,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `whatsapp` varchar(20) DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO
    `users` (
        `id`,
        `name`,
        `email`,
        `whatsapp`,
        `password`,
        `created_at`
    )
VALUES (
        1,
        'Rizky Pratama',
        'rizky@email.com',
        '081234567890',
        '$2y$10$TKzbrKe3qzfd6hf0.jevKeR4yn.yRaqlVn7dL9gFCJg32gcjBgqoO',
        '2024-01-15 10:00:00'
    ),
    (
        2,
        'Ananda Rizky',
        'akunbaru291222@gmail.com',
        '085693557069',
        '$2y$10$TKzbrKe3qzfd6hf0.jevKeR4yn.yRaqlVn7dL9gFCJg32gcjBgqoO',
        '2026-09-21 20:56:17'
    );

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
ADD PRIMARY KEY (`id`),
ADD KEY `user_id` (`user_id`),
ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `ad_images`
--
ALTER TABLE `ad_images`
ADD PRIMARY KEY (`id`),
ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads` MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 15;

--
-- AUTO_INCREMENT for table `ad_images`
--
ALTER TABLE `ad_images` MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories` MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users` MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ads`
--
ALTER TABLE `ads`
ADD CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
ADD CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `ad_images`
--
ALTER TABLE `ad_images`
ADD CONSTRAINT `ad_images_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;