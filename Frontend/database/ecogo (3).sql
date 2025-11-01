-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2025 at 01:37 PM
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
CREATE DATABASE IF NOT EXISTS `ecogo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecogo`;

-- --------------------------------------------------------

--
-- Table structure for table `community`
--

CREATE TABLE `community` (
  `Community_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `Community_title` varchar(255) NOT NULL,
  `Community_category` enum('projects','tips') NOT NULL,
  `Community_contributor` varchar(255) DEFAULT NULL,
  `Community_location` varchar(255) DEFAULT NULL,
  `Community_media` varchar(500) DEFAULT NULL,
  `Community_summary` text DEFAULT NULL,
  `Community_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community`
--

INSERT INTO `community` (`Community_id`, `user_id`, `Community_title`, `Community_category`, `Community_contributor`, `Community_location`, `Community_media`, `Community_summary`, `Community_link`, `created_at`) VALUES
(1, 16, 'Hello', 'projects', 'Sigma', 'ABC', 'upload/community/69022b5ce2863.png', 'wertyjhnbvcdfrujhgfd', '', '2025-10-29 14:57:32'),
(2, 9, 'Hello', 'projects', 'Sigma', 'ABC', 'upload/community/69022b8411f01.png', 'btr hbyjunykuyjhbtyh', '', '2025-10-29 14:58:12'),
(3, 9, 'Hello', 'projects', 'Sigma', 'ABC', 'upload/community/69023bb98e23e.jpg', 'kijuhygtrfcdexjnhybgvfcd', '', '2025-10-29 16:07:21'),
(4, 9, 'Hello', 'tips', 'Sigma', 'ABC', 'upload/community/6902d2fe2779d.png', 'deddee', '', '2025-10-30 02:52:46');

-- --------------------------------------------------------

--
-- Table structure for table `energy`
--

CREATE TABLE `energy` (
  `Energy_id` int(11) NOT NULL,
  `Energy_title` varchar(255) NOT NULL,
  `Energy_category` enum('tutorial','habit','planning') NOT NULL,
  `Energy_contributor` varchar(255) DEFAULT NULL,
  `Energy_media` varchar(255) NOT NULL,
  `Energy_summary` text NOT NULL,
  `Energy_link` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `energy`
--

INSERT INTO `energy` (`Energy_id`, `Energy_title`, `Energy_category`, `Energy_contributor`, `Energy_media`, `Energy_summary`, `Energy_link`, `user_id`) VALUES
(1, 'get it', 'tutorial', 'john', 'upload/energyGrid/68fef019e7020.png', 'get it', '', 2),
(3, 'get it', 'tutorial', 'john', 'upload/energyGrid/68ff2a847a574.png', 'get it', '', 2),
(4, '12', 'tutorial', '123', 'upload/energyGrid/6901f3687d9c7.png', 'fvbghjkljhgfghiop;oiuyt', '', 9),
(9, 'vbnm', 'habit', 'fghj', 'upload/energyGrid/69036af57a5b0.png', 'ghjkl', '', 17);

-- --------------------------------------------------------

--
-- Table structure for table `exchange`
--

CREATE TABLE `exchange` (
  `ExchangeID` int(11) NOT NULL,
  `ItemID` int(11) DEFAULT NULL,
  `RequesterID` int(11) DEFAULT NULL,
  `OwnerID` int(11) DEFAULT NULL,
  `Offer_title` varchar(200) DEFAULT NULL,
  `Offer_description` text DEFAULT NULL,
  `Offer_notes` text DEFAULT NULL,
  `Offer_image` varchar(200) DEFAULT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `Exchange_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange`
--

INSERT INTO `exchange` (`ExchangeID`, `ItemID`, `RequesterID`, `OwnerID`, `Offer_title`, `Offer_description`, `Offer_notes`, `Offer_image`, `status`, `Exchange_timestamp`) VALUES
(1, 3, 9, 17, 'ijuhygtfrde', 'ujhygtfrd', 'jnyhbgtvfrde', 'upload/offers/1761835794_offer_concert_ticketing_system-userview-concert_ticketing_system.png', 'pending', '2025-10-30 14:49:54'),
(2, 4, 19, 9, 'ygt', 'gvtvtt', '', 'upload/offers/1761926442_offer_Logical Design -Page-5.drawio.png', 'accepted', '2025-10-31 16:00:42'),
(3, 2, 19, 9, '，空间和规范', 'tynyughbvctrfv', 'hgfdertjmhnf', 'upload/offers/1761929432_offer_Logical Design -Page-5.drawio (1).png', 'accepted', '2025-10-31 16:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `ItemID` int(11) NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Category` enum('home-grown','eco-friendly','') NOT NULL,
  `Description` varchar(200) DEFAULT NULL,
  `Item_condition` varchar(200) NOT NULL,
  `Preferred_exchange` varchar(200) NOT NULL,
  `Image_path` varchar(500) NOT NULL,
  `Status` enum('Available','Exchanged','Removed') DEFAULT 'Available',
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`ItemID`, `Title`, `Category`, `Description`, `Item_condition`, `Preferred_exchange`, `Image_path`, `Status`, `UserID`) VALUES
(2, 'pp', '', 'fghjk', 'mjj', 'jjkj', 'upload/swapItems/69034cd4623a6.png', 'Exchanged', 9),
(3, 'mnjhbgvfd', '', 'ikjuhygtrfd7ujyhbgtfv', 'kjhgf', 'jmnhgfd', 'upload/swapItems/690379dbeb73f.png', 'Available', 17),
(4, 'mnjhbgvfd', '', '很过分的事', 'kjhgf', 'jmnhgfd', 'upload/swapItems/6904d77fac90d.jpg', 'Exchanged', 9),
(6, 'mnjhbgvfd', 'home-grown', 'ikjuyhgtfd', 'kjhgf', 'jmnhgfd', 'upload/swapItems/6905e5f8de235.jpg', 'Available', 9);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ExchangeID` int(11) DEFAULT NULL,
  `Message` text NOT NULL,
  `Is_read` tinyint(1) DEFAULT 0,
  `Notification_Timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`NotificationID`, `UserID`, `ExchangeID`, `Message`, `Is_read`, `Notification_Timestamp`) VALUES
(1, 9, 2, 'Hi, XINYING12 wants to swap with you using \"ygt\" for your item \"mnjhbgvfd\"', 1, '2025-10-31 16:00:42'),
(2, 19, 2, 'Great news! XY has accepted your swap request for \"mnjhbgvfd\"', 1, '2025-10-31 16:11:42'),
(3, 9, 3, 'Hi, XINYING12 wants to swap with you using \"，空间和规范\" for your item \"pp\"', 1, '2025-10-31 16:50:32'),
(4, 19, 3, 'Great news! XY has accepted your swap request for \"pp\"', 1, '2025-10-31 16:51:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`) VALUES
(1, 9, 'cc0242071c77f638d70c42289282a9efd29c2e4d95e66aece218b7dac62704f5', '2025-10-24 01:56:34'),
(2, 9, '61f7cb665522c41bcc472fb1d36cbb1626a30dcc2d40530db7600a35ac71084b', '2025-10-24 02:18:38'),
(3, 9, '4982408f8d3dba286637e4288fb973b3896657201bbd0927431f09d41fdae71a', '2025-10-24 02:18:45'),
(4, 9, '98eca3ea04a2665570f2d63e4453ac2ad21e365503993621bcab3239f90b6c1e', '2025-10-24 02:18:54'),
(5, 9, '18c153d5d1d0bcd8e3316048dc591b154b838ee78a142b638cf51f97234be9ac', '2025-10-24 02:23:26'),
(6, 9, '247bd1b5d4d40bbdb19794b9afe8c3cd006941103dd468833a60ddb650d8a2f9', '2025-10-24 02:32:23'),
(7, 9, 'd10ab7ff5a5679ad561e1b23115553bc79b352f27b4c31ccc045b27555afaa1a', '2025-10-24 02:32:26'),
(8, 9, '44b00edcfde88595c54f197a65bfa786916d4b80e544d0c6788d6482e31ac629', '2025-10-24 02:32:27'),
(9, 9, '5bebe1ffa71108e95887da5dc7361898336a44f93b3eef54aa6bd4f6a791baca', '2025-10-24 02:32:40'),
(10, 9, 'a8fbb8bd2c83cc1eb2648ca1ac7981299585a19304ccd1b6b1ebe399b31992a6', '2025-10-24 02:32:41'),
(11, 9, '43a4310a45a9af280c9a7d90185b1b2f21ab18ced40727bc4ae38c913830171d', '2025-10-24 02:32:44'),
(12, 9, '1e775745f8d7259c1819b80bb3909605f3e5e4d19faf40c399ac69d40549a78e', '2025-10-24 02:32:44'),
(13, 9, '4ffea345ec35c77daccc5c15e5ff94ee043e7f4cd1701bdbc35b25623b4469ec', '2025-10-24 02:32:44'),
(14, 9, '553f517b0dd1fbe2d53fe4b1339c8c24bf59f8e2b8736d86c85686205f4f0be1', '2025-10-24 02:32:44'),
(15, 9, '6cd7efb8faa6609479b738d86fd279e08e4878a5245192f25d5c5f1601364351', '2025-10-24 02:32:45'),
(16, 9, '45b6289aec4c47eee4469143f1641e94eb5b7760866c994798f166f52c657b94', '2025-10-24 02:32:45'),
(17, 9, '999f833996fd686d20b1f45b92efa87bc4e991bb30e91044677a0e13b1c204d3', '2025-10-24 02:32:45'),
(18, 9, 'afbeeaad5dc64537b2e7272d7720b5c07f1c27cda087ee35a9e41bbf963c216a', '2025-10-24 02:32:45'),
(19, 9, 'bda2fa101eb165726f2119ab4bc4ceac40f1a936f437a71290409dfff979ca41', '2025-10-24 02:32:45'),
(20, 9, '0f87a917b6b16bf0e675eac5ae5aa98fda05b5243502937b7e2eb283824fc0b9', '2025-10-24 02:32:46'),
(21, 9, 'ce26ec06183ccbae89312acfed293a810be3f3d82124070af95b570ea8380717', '2025-10-24 04:28:31'),
(22, 9, '5b5132ea7df073d31fecd6bc5e92ac2d7b49f1271b243c77995a2099b903179f', '2025-10-24 04:05:19'),
(23, 10, '365acb78fb8419be0ceb322b5958325c0169812502433978e8e8a6c614535825', '2025-10-24 06:00:23'),
(24, 9, '7e93ac28980703cf1dce9955a967586e594c4ccceba72b96b429f38d3e44c605', '2025-10-24 06:00:56'),
(25, 9, 'eb6e0ddcdc906efacf8fc34886d605f3bd2cf5dbb8d6b4e47cdd1451d95d6299', '2025-10-24 06:02:02'),
(26, 10, '1195010b03b92402c742e3261c1ccee46297e9c77bbc96d4987f120bee54ed0d', '2025-10-24 06:05:10'),
(27, 9, '9f0586dadc86bb1534c634de29300dea2e2b90fa888e38271eb1e6d9b36a8c32', '2025-10-24 06:07:14'),
(28, 9, '0d6941b58b7804fec40691d15846e09f95847221f2600556bbf335db97e2427f', '2025-10-24 06:20:08'),
(29, 9, '8617f89b6f1db0e279d779942496751274d843d606f788f69ac9140b2acf8a3f', '2025-10-24 06:23:48'),
(30, 9, '331892783da2722d11af3c1c8be8dbd551631cb71afc60fd84326d23ab61ff6e', '2025-10-24 06:32:44'),
(31, 9, 'b1700e25f31b3e51c39800511f3a8130150742bc59b5ac831cde8a88fd47c48d', '2025-10-24 06:32:53'),
(32, 9, '33e9e241c581507923396207a2417beabf3cc548dd7cc426c8a8b8d9a05e86d8', '2025-10-24 06:33:09'),
(40, 9, 'd61606af749c5c63d3e7688eb02fbd47785011dde83a2c87fde8703c255d60c2', '2025-10-30 20:45:53');

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
(5, '123', 'Laos', '2025-10-28', '2025-10-31', 'yes', 'john', 'kamlamlam20@gmail.com', '+60166410131', 2, 20, 102, '2025-10-28 08:44:13'),
(9, 'ffv', 'Kuala Lumpur City Centre, Kuala Lumpur, Federal Territory of Kuala Lumpur, Malaysia', '2025-10-25', '2025-10-27', 'rfcrgtvht', 'rtgrtgrg', 'gvrgrr@gmail.com', '0123456789', 16, 3, 102, '2025-10-29 14:25:32'),
(10, 'ffv', 'Taman Metropolitan Relau, Bayan Lepas, Pulau Pinang, Malaysia', '2025-10-25', '2025-10-27', 'frgvrgtgrtr', 'rtgrtgrg', 'gvrgrr@gmail.com', '0123456789', 17, 5, 100, '2025-10-30 14:01:06');

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
(3, 'egg', 'kamlamlam20@gmail.com', '+60 16-6410131', 3, 1),
(4, 'Lim Xin Ying', 'limxinying060327@gmail.com', '01116116331', 9, 9),
(5, 'Lim Xin Ying', 'limxinying060327@gmail.com', '+60 12-345 6789', 5, 9);

-- --------------------------------------------------------

--
-- Table structure for table `program_sections`
--

CREATE TABLE `program_sections` (
  `section_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_sections`
--

INSERT INTO `program_sections` (`section_id`, `program_id`, `section_title`, `section_description`, `created_at`) VALUES
(6, 9, 'oijuhygfd8iiu7ytre', '', '2025-10-29 14:25:32'),
(7, 10, 'oijuhygfd8iiu7ytre', 'frtgvyhbjyunjy', '2025-10-30 14:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(455) DEFAULT NULL,
  `Full_Name` varchar(455) DEFAULT NULL,
  `Gender` enum('Male','Female','Others') DEFAULT NULL,
  `Email` varchar(455) NOT NULL,
  `Password` varchar(455) NOT NULL,
  `Role` enum('user','admin') NOT NULL DEFAULT 'user',
  `Phone_Number` varchar(455) DEFAULT NULL,
  `City_Or_Neighbourhood` varchar(455) DEFAULT NULL,
  `Additional_info` varchar(455) DEFAULT NULL,
  `Join_date` date DEFAULT NULL,
  `Last_login` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Username`, `Full_Name`, `Gender`, `Email`, `Password`, `Role`, `Phone_Number`, `City_Or_Neighbourhood`, `Additional_info`, `Join_date`, `Last_login`) VALUES
(8, 'Xin Ying', 'Lim Xin YIng', 'Female', 'TP081765@mail.apu.edu.my', '$2y$10$GTc8FCRATXE2svxulGKV7uCTe5J0gPWhHXPDEbWqPEAL6VmuXEbru', 'user', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'Nothing..', '2025-10-20', '2025-10-20'),
(9, 'ha', 'Lim Xin Ying', 'Female', 'limxinying060327@gmail.com', '$2y$10$dvA/aIT0SggjFNehk52DCeOGH4qMja3QFlW4z/ZFM3jcdar/pZp6i', 'user', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'Nothing to write..', '2025-10-20', '2025-11-01'),
(10, 'nice', 'Lim Xin YIng', 'Female', 'tp081765@mail.edu.apu.my', '$2y$10$BswV66rlEmb5.USY2JRQJ.1clBnmtN9pUKFgf0SF5eAYsNZCJfrzO', 'user', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'xeded', '2025-10-21', '2025-10-21'),
(12, 'victor', 'Lim Xin YIng', 'Male', 'victorbcchia7@gmail.com', '$2y$10$UN58j3FfLW/NbRg98M4NXecSGrqHsgn6kjSlcvcDtDbVzyBwJDURW', 'user', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'thgfhyy', '2025-10-22', '2025-10-22'),
(13, 'Lim', 'Lim Xin YIng', 'Female', 'limlim123@gmail.com', '$2y$10$FkD95J/3UmfxAkbp/Ieq3O7H.KjLCMuLf5FnhZpBIAZ8aRLxJ3Npq', 'admin', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'ghjk', '2025-10-24', '2025-10-24'),
(14, 'xyy', 'Lim Xin YIng', 'Male', '123445@gmail.com', '$2y$10$i1DSh.IXLXR9OmgKOx6wu.kpy4zKqFodTuEbUesG49tfh0/03F0Pi', 'admin', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'fghjkl', '2025-10-24', '2025-10-24'),
(15, 'jason', 'Teo Jun Jie', 'Male', 'jasonteo1408@gmail.com', '$2y$10$JwzsdHpuC2HJznhWgU0svuRaTr75h7Axi87P7dI8Pjl/3h454LWMC', 'user', '+60 12-345 6789', 'Kuala Lumpur, Malaysia', 'no idea...', '2025-10-24', '2025-10-24'),
(16, 'jason', 'Ng Tian Xin', 'Female', 'tianxin0406@gmail.com', '$2y$10$itrM.ULvfpyWOyHq7bfcpOg4KmyaiNWKW3GdlF69byEYz2Gz3uMsS', 'admin', '+601126672929', 'Kuala Lumpur, Malaysia', '12345678', '2025-10-29', '2025-10-29'),
(17, 'XINYING', 'Lim Xin Ying', 'Female', 'xiuography123@gmail.com', '$2y$10$Jp0m/T6qHpnpVDO2pnYXm.QnWEr73I5sQgZuUZev73sHP33wfH6yG', 'admin', '+601116116331', 'Kuala Lumpur, Malaysia', 'idkkk', '2025-10-30', '2025-11-01'),
(18, 'XINYING1', 'Lim Xin Ying', 'Male', '123@gmail.com', '$2y$10$ZvUADkINi88Tqh38XAWUrei1oVkpO46XiAEq7vZvrem6kr6w7DNtu', 'admin', '+601116116331', 'Kuala Lumpur, Malaysia', NULL, '2025-10-31', '2025-10-31'),
(19, 'XINYING12', 'Lim Xin Ying', 'Male', 'abc@gmail.com', '$2y$10$izHAljDtJhMWY2Xhyp4UguHbYZRy7wSFJJGvTtTx7FoDwC8Pc6SO2', 'user', '+601116116331', 'Kuala Lumpur, Malaysia', NULL, '2025-10-31', '2025-10-31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `community`
--
ALTER TABLE `community`
  ADD PRIMARY KEY (`Community_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `energy`
--
ALTER TABLE `energy`
  ADD PRIMARY KEY (`Energy_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `exchange`
--
ALTER TABLE `exchange`
  ADD PRIMARY KEY (`ExchangeID`),
  ADD KEY `ItemID` (`ItemID`),
  ADD KEY `RequesterID` (`RequesterID`),
  ADD KEY `OwnerID` (`OwnerID`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`ItemID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `fk_notifications_exchange` (`ExchangeID`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`ProgramID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `program_customer`
--
ALTER TABLE `program_customer`
  ADD PRIMARY KEY (`Customer_id`),
  ADD KEY `Program_id` (`Program_id`),
  ADD KEY `User_id` (`User_id`);

--
-- Indexes for table `program_sections`
--
ALTER TABLE `program_sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `program_sections_ibfk_1` (`program_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `community`
--
ALTER TABLE `community`
  MODIFY `Community_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `energy`
--
ALTER TABLE `energy`
  MODIFY `Energy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `exchange`
--
ALTER TABLE `exchange`
  MODIFY `ExchangeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `ItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `ProgramID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `program_customer`
--
ALTER TABLE `program_customer`
  MODIFY `Customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `program_sections`
--
ALTER TABLE `program_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community`
--
ALTER TABLE `community`
  ADD CONSTRAINT `community_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `exchange`
--
ALTER TABLE `exchange`
  ADD CONSTRAINT `exchange_ibfk_1` FOREIGN KEY (`ItemID`) REFERENCES `items` (`ItemID`),
  ADD CONSTRAINT `exchange_ibfk_2` FOREIGN KEY (`RequesterID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `exchange_ibfk_3` FOREIGN KEY (`OwnerID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_exchange` FOREIGN KEY (`ExchangeID`) REFERENCES `exchange` (`ExchangeID`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `program_sections`
--
ALTER TABLE `program_sections`
  ADD CONSTRAINT `program_sections_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program` (`ProgramID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
