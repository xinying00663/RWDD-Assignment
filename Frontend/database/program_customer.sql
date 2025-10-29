-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 04:27 PM
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
-- Table structure for table `program_customer`
--

CREATE TABLE `program_customer` (
  `Customer_id` int(11) NOT NULL,
  `Customer_name` varchar(255) NOT NULL,
  `Customer_email` varchar(255) NOT NULL,
  `Customer_phone` varchar(255) NOT NULL,
  `Program_id` int(11) NOT NULL,
  `User_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_customer`
--

INSERT INTO `program_customer` (`Customer_id`, `Customer_name`, `Customer_email`, `Customer_phone`, `Program_id`, `User_id`) VALUES
(1, 'egg', 'kamlamlam20@gmail.com', '+60 16-6410131', 3, 2),
(2, 'egg', 'kamlamlam20@gmail.com', '+60 16-6410131', 4, 2),
(3, 'egg', 'kamlamlam20@gmail.com', '+60 16-6410131', 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `program_customer`
--
ALTER TABLE `program_customer`
  ADD PRIMARY KEY (`Customer_id`),
  ADD KEY `Program_id` (`Program_id`),
  ADD KEY `User_id` (`User_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `program_customer`
--
ALTER TABLE `program_customer`
  MODIFY `Customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `program_customer`
--
ALTER TABLE `program_customer`
  ADD CONSTRAINT `program_customer_ibfk_1` FOREIGN KEY (`Program_id`) REFERENCES `program` (`ProgramID`),
  ADD CONSTRAINT `program_customer_ibfk_2` FOREIGN KEY (`User_id`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
