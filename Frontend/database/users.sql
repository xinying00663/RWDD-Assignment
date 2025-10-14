-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 07:03 AM
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(200) NOT NULL,
  `Full_Name` varchar(200) NOT NULL,
  `Birth_Date` date DEFAULT NULL,
  `Gender` enum('Male','Female','Others','') DEFAULT NULL,
  `Email` varchar(200) NOT NULL,
  `Password` varchar(200) NOT NULL,
  `Phone_Number` varchar(200) NOT NULL,
  `City_Or_Neighbourhood` varchar(200) DEFAULT NULL,
  `Additional_info` varchar(200) DEFAULT NULL,
  `Join_date` datetime(6) DEFAULT NULL,
  `Last_login` datetime(6) DEFAULT NULL,
  `Reset token` varchar(200) DEFAULT NULL,
  `Reset expiry` datetime(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Full_Name`, `Birth_Date`, `Gender`, `Email`, `Password`, `Phone_Number`, `City_Or_Neighbourhood`, `Additional_info`, `Join_date`, `Last_login`, `Reset token`, `Reset expiry`) VALUES
(1, '', '', NULL, NULL, 'test@example.com', '$2y$10$test', '', NULL, NULL, '2025-10-14 12:41:45.000000', '2025-10-14 12:41:45.000000', NULL, NULL),
(2, '', '', NULL, NULL, 'test_1760417575@example.com', '$2y$10$ss0QoTYnSe7VIBNXaGeVzOOO8qAYhLgMlRCY82xwwt2Q42rmaK1xS', '', NULL, NULL, '2025-10-14 06:52:55.000000', '2025-10-14 06:52:55.000000', NULL, NULL),
(3, '', '', NULL, NULL, 'test_1760417776@example.com', '$2y$10$o.1gsLi4HvgYyL9gbX7v2OwBXcL1xZr4CMGHLPJlOAcOHIL804rmW', '', NULL, NULL, '2025-10-14 06:56:16.000000', '2025-10-14 06:56:16.000000', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
