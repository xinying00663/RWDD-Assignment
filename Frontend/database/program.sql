-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 09:28 AM
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
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `ProgramID` int(11) NOT NULL,
  `Program_name` varchar(100) NOT NULL,
  `Program_location` varchar(150) NOT NULL,
  `Event_date_start` date NOT NULL,
  `Event_date_end` date NOT NULL,
  `Program_description` text NOT NULL,
  `Coordinator_name` varchar(100) NOT NULL,
  `Coordinator_email` varchar(100) NOT NULL,
  `Coordinator_phone` varchar(20) NOT NULL,
  `userID` int(11) NOT NULL,
  `latitude` decimal(10,0) NOT NULL,
  `longitude` decimal(10,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program`
--

INSERT INTO `program` (`ProgramID`, `Program_name`, `Program_location`, `Event_date_start`, `Event_date_end`, `Program_description`, `Coordinator_name`, `Coordinator_email`, `Coordinator_phone`, `userID`, `latitude`, `longitude`, `created_at`) VALUES
(3, '123', 'Rawang, Selangor, Malaysia', '2025-10-29', '2025-10-31', 'yessss', 'john', 'kamlamlam20@gmail.com', '+60166410131', 2, 0, 0, '2025-10-27 04:47:15'),
(4, '123', 'Hong Kong', '2025-10-27', '2025-10-31', 'yess', 'john', 'kamlamlam20@gmail.com', '+60166410131', 2, 0, 0, '2025-10-27 04:47:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`ProgramID`),
  ADD KEY `userID` (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `ProgramID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `program`
--
ALTER TABLE `program`
  ADD CONSTRAINT `program_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
