-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 02:54 PM
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
-- Database: `autoride_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `is_admin` tinyint(1) NOT NULL,
  `Id` int(10) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `mobile_number` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `request_status` varchar(20) DEFAULT NULL COMMENT 'status of approval'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`is_admin`, `Id`, `firstName`, `lastName`, `id_number`, `mobile_number`, `email`, `password`, `request_status`) VALUES
(0, 21, 'Admin', 'User', '', '', 'admin1216@gmail.com', '$2y$10$Qqy2n34dOcF4yr7gQbThceQ.8CPBiBWG.HvO14cgssr', NULL),
(0, 22, 'Marthrone Andro', 'Quijano', '5231625', '09157253994', 'marthroneandroquijano@gmail.com', '25f9e794323b453885f5181f1b624d0b', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_registration`
--

CREATE TABLE `vehicle_registration` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `course_year_section` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `license_file` varchar(255) DEFAULT NULL,
  `orcr_file` varchar(255) DEFAULT NULL,
  `parent_id_file` varchar(255) DEFAULT NULL,
  `proof_of_purchase_file` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending',
  `qr_status` varchar(20) DEFAULT 'Inactive',
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_registration`
--

INSERT INTO `vehicle_registration` (`id`, `name`, `id_number`, `mobile_number`, `email`, `course_year_section`, `vehicle_type`, `license_file`, `orcr_file`, `parent_id_file`, `proof_of_purchase_file`, `registration_date`, `status`, `qr_status`, `approved_at`) VALUES
(10, 'Marthrone Andro Quijano', '5231625', '09157253994', 'marthroneandroquijano@gmail.com', 'BSIT - 2B', 'MOTORCYCLE', '../uploads/5231625/1747718400_Screenshot 2025-05-13 005349.png', '../uploads/5231625/1747718400_Screenshot 2025-05-13 005510.png', '../uploads/5231625/1747718400_Screenshot 2025-05-13 005611.png', '../uploads/5231625/1747718400_Screenshot 2025-05-18 132027.png', '2025-05-20 05:20:00', 'Pending', 'Inactive', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

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
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `vehicle_registration`
--
ALTER TABLE `vehicle_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
