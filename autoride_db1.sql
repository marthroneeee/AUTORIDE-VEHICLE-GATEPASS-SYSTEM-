-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 10:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autoride_db1`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `Id` int(10) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `mobile_number` varchar(50) NOT NULL,
  `course_year_section` varchar(100) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `request_status` varchar(20) DEFAULT NULL COMMENT 'status of approval',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`is_admin`, `Id`, `firstName`, `lastName`, `id_number`, `mobile_number`, `course_year_section`, `email`, `password`, `request_status`, `created_at`) VALUES
(1, 50, 'Admin', 'User', '123456', '09123456789', NULL, 'AutoRide@gmail.com', '$2y$10$w7P730szr6Z5dvhla6XT5e7p7YEOSlkX5XI5WwrkEoIG/QpGG33a.', 'approved', '2025-06-08 00:11:53'),
(0, 62, 'James', 'Harden', '5231625', '09157253994', 'BSIT2B', 'JamesHarden@gmail.com', '$2y$10$KtLPO7yO97/jkyv8LFtfK.LY9Bwsv72QjaC8M4OkmNQ0QuENYnQCG', 'Approved', '2025-06-09 22:33:06'),
(0, 63, 'Mike', 'Prahinog', '5231553', '09692506594', 'BSIT1B', 'mikeprahinog@gmail.com', '$2y$10$0yxJeP9QPzHmU3kMR6PaMu822/7PpanaQcJXWdBjil7L63AvRlOfi', 'Approved', '2025-06-09 23:42:50'),
(0, 64, 'Mike', 'Gwapo', '5231553', '09929449571', 'BSIT4N', 'mikegwapo@gmail.com', '$2y$10$cfqXn7blHNhU7NDPd6MVYOLbp.Wmd3EYj0.Hxx9yxTsQm2F2ftfu.', 'Approved', '2025-06-10 15:36:14');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_registration`
--

CREATE TABLE `vehicle_registration` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `ownership_type` varchar(255) NOT NULL,
  `or_file` varchar(255) DEFAULT NULL,
  `cr_file` varchar(255) DEFAULT NULL,
  `license_file` varchar(255) DEFAULT NULL,
  `valid_id_file` varchar(255) DEFAULT NULL,
  `proof_of_purchase_file` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending',
  `qr_status` varchar(20) DEFAULT 'Inactive',
  `qr_expiration` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_registration`
--

INSERT INTO `vehicle_registration` (`id`, `user_id`, `vehicle_type`, `ownership_type`, `or_file`, `cr_file`, `license_file`, `valid_id_file`, `proof_of_purchase_file`, `registration_date`, `status`, `qr_status`, `qr_expiration`, `approved_at`, `approval_number`) VALUES
(59, 62, 'MIO', 'First Owner', 'uploads/5231625/1749479635_Screenshot2025-06-04175704.png', 'uploads/5231625/1749479635_Screenshot2025-06-04175308.png', 'uploads/5231625/1749479635_Screenshot2025-06-04170036.png', 'uploads/5231625/1749479635_Screenshot2025-06-04174743.png', NULL, '2025-06-09 14:33:55', 'Approved', 'Active', '2025-06-16 16:34:34', '2025-06-09 22:34:34', 1),
(60, 63, 'SNIPER', 'Second Owner', 'uploads/5231553/1749483807_Screenshot2025-06-04183735.png', 'uploads/5231553/1749483807_Screenshot2025-06-04175308.png', 'uploads/5231553/1749483807_Screenshot2025-06-04180126.png', 'uploads/5231553/1749483807_Screenshot2025-06-04174743.png', 'uploads/5231553/1749483807_Screenshot2025-06-05222816.png', '2025-06-09 15:43:27', 'Rejected', 'Inactive', NULL, NULL, NULL),
(61, 64, 'SNIPER', 'First Owner', 'uploads/5231553/1749541039_Screenshot2025-06-04180126.png', 'uploads/5231553/1749541039_Screenshot2025-06-04175308.png', 'uploads/5231553/1749541039_Screenshot2025-06-04163931.png', 'uploads/5231553/1749541039_Screenshot2025-06-05132711.png', NULL, '2025-06-10 07:37:19', 'Approved', 'Active', '2025-06-17 09:37:48', '2025-06-10 15:37:48', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicle_registration`
--
ALTER TABLE `vehicle_registration`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `vehicle_registration`
--
ALTER TABLE `vehicle_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
