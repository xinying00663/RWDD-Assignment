-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 03:35 AM
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
-- Database: `ecogo`
--

-- --------------------------------------------------------

--
-- Table structure for table `energy`
--

CREATE TABLE `energy` (
  `Energy_id` int(11) NOT NULL,
  `Energy_title` varchar(255) NOT NULL,
  `Energy_category` enum('Tutorial','Daily Habit','Planning & checklist') NOT NULL,
  `Energy_contributor` varchar(255) NOT NULL,
  `Energy_duration` varchar(255) NOT NULL,
  `Energy_media` varchar(255) NOT NULL,
  `Energy_summary` text NOT NULL,
  `Energy_link` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `energy`
--
ALTER TABLE `energy`
  ADD PRIMARY KEY (`Energy_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `energy`
--
ALTER TABLE `energy`
  MODIFY `Energy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `energy`
--
ALTER TABLE `energy`
  ADD CONSTRAINT `energy_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
