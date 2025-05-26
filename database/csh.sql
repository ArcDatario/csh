-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 05:04 PM
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
-- Database: `csh`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `fullname` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` text NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `fullname`, `password`, `role`, `token`, `token_expiry`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin', '$2a$12$6qfcEBPijiPjljmsgzT/Z.Vts08sppSiEckZzKKalQmAc/w1hSRd2', 'Owner', NULL, NULL, '2025-05-19 06:55:13', '2025-04-04 14:28:31'),
(3, 'field', 'field manager', '$2y$10$dNldj0RIra/5ABt/XqG.Dutl36HkU7uDcblzUvDNCc2mgaw9nR5.W', 'Field Manager', NULL, NULL, '2025-04-14 22:55:54', '2025-04-10 14:59:04'),
(4, 'secretary', 'secretary', '$2y$10$9x9fhZ.zenNuZOc0NBonh.RrmEkZcf2SqKwWEW0GOAXAZDv3zdxLe', 'Secretary', NULL, NULL, '2025-04-11 10:17:06', '2025-04-10 14:59:32'),
(5, 'generalmanager', 'generalmanager', '$2y$10$0fKTvr4qKLmTaHNA5z1rxOg/Shs8eyyNya9ytYUCIJWovdht2Xx.i', 'General Manager', NULL, NULL, NULL, '2025-04-10 15:00:30'),
(6, 'designer', 'designer', '$2y$10$Q6KTpNkRNA7ulPe6GKGDVuFVZzF3eW6GtZ1y2PGO5CKQNve.4/lh.', 'Designer', NULL, NULL, '2025-05-19 06:54:45', '2025-04-10 15:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `quantity`, `created_at`, `updated_at`) VALUES
(2, 'chemical', 2321, '2025-04-08 17:24:24', '2025-05-02 08:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `notify_owner` text NOT NULL,
  `notify_manager` text NOT NULL,
  `notify_designer` text NOT NULL,
  `notify_secretary` text NOT NULL,
  `notify_field` text NOT NULL,
  `is_viewed_owner` text NOT NULL,
  `is_viewed_manager` text NOT NULL,
  `is_viewed_secretary` text NOT NULL,
  `is_viewed_field_manager` text NOT NULL,
  `is_viewed_designer` text NOT NULL,
  `status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `order_id`, `content`, `notify_owner`, `notify_manager`, `notify_designer`, `notify_secretary`, `notify_field`, `is_viewed_owner`, `is_viewed_manager`, `is_viewed_secretary`, `is_viewed_field_manager`, `is_viewed_designer`, `status`, `created_at`) VALUES
(46, 19, NULL, 'New Quote, Screen Printing, 233', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-01 20:01:49'),
(47, 20, NULL, 'New Quote, Emboss Print, 3221', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-01 20:12:41'),
(48, 20, NULL, 'Designer just added a quote price of ₱300 on ticket #111792', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-05-02 09:02:54'),
(49, 20, NULL, 'New Quote, Screen Printing, 321', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-02 16:32:10'),
(50, 20, NULL, 'Designer just added a quote price of ₱300 on ticket #392779', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-05-02 16:32:56'),
(51, 20, NULL, 'admin just approved a quote price of ₱299 on ticket #392779', '', '', '', '', 'yes', '', '', '', '', '', '', '2025-05-02 16:34:00'),
(52, 22, NULL, 'New Quote, Screen Printing, 1231', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-07 10:10:42'),
(53, 22, NULL, 'New Quote, Emboss Print, 322', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-16 09:18:01'),
(54, 22, NULL, 'New Quote, Silk Screen Print, 432', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-19 06:54:15'),
(55, 22, NULL, 'Designer just added a quote price of ₱99 on ticket #458895', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-05-19 06:55:00'),
(56, 22, NULL, 'Designer just added a quote price of ₱89 on ticket #249247', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-05-19 06:55:05'),
(57, 22, NULL, 'admin just approved a quote price of ₱98 on ticket #458895', '', '', '', '', 'yes', '', '', '', '', '', '', '2025-05-19 07:00:47'),
(58, 22, NULL, 'admin just approved a quote price of ₱90 on ticket #249247', '', '', '', '', 'yes', '', '', '', '', '', '', '2025-05-19 07:06:31'),
(59, 22, 54, 'Quote #249247 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 07:44:30'),
(60, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 07:49:10'),
(61, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 07:53:20'),
(62, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 07:56:56'),
(63, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 07:57:29'),
(64, 22, 54, 'Quote #249247 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 08:21:00'),
(65, 22, NULL, 'New Quote, Emboss Print, 32', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-05-19 08:21:28'),
(66, 22, NULL, 'admin just approved a quote price of ₱99 on ticket #204373', '', '', '', '', 'yes', '', '', '', '', '', '', '2025-05-19 08:21:47'),
(67, 22, 55, 'Quote #204373 has been agreed to the price', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '2025-05-19 08:31:00'),
(68, 22, NULL, 'admin just approved a quote price of ₱99 on ticket #549865', '', '', '', '', 'yes', '', '', '', '', '', '', '2025-05-19 08:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `ticket` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `print_type` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `pricing` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `total` int(11) NOT NULL,
  `note` text NOT NULL,
  `status` text NOT NULL DEFAULT 'pending',
  `address` text NOT NULL,
  `is_approved_designer` text NOT NULL DEFAULT 'no',
  `designer_approved_date` datetime NOT NULL,
  `is_user_approved` text NOT NULL,
  `user_approved_date` datetime NOT NULL,
  `is_approved_admin` text NOT NULL,
  `admin_approved_date` datetime NOT NULL,
  `is_for_pickup` text NOT NULL,
  `pickup_date` datetime DEFAULT NULL,
  `is_for_processing` text NOT NULL,
  `processing_date` datetime DEFAULT NULL,
  `is_delivered` text NOT NULL,
  `delivered_date` datetime DEFAULT NULL,
  `is_approved_field_manager` text NOT NULL,
  `field_manager_approved_date` datetime NOT NULL,
  `complete_date` datetime DEFAULT NULL,
  `design_file` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` text NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `description`, `image`, `created_at`) VALUES
(12, 'Sublimation Printing', 'Vibrant, all-over prints that won\\\'t crack or fade with our sublimation process.', 'uploads/services/service_67f3989e77156.jpeg', '2025-04-07 15:00:03'),
(13, 'Direct-to-Film', 'High-resolution digital printing for complex designs with no color limitations.', 'uploads/services/service_67f398248190e.jpeg', '2025-04-07 17:07:00'),
(14, 'Screen Printing', 'Traditional screen printing for vibrant, long-lasting designs on all fabric types.', 'uploads/services/service_67f39b1369806.jpeg', '2025-04-07 17:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `name` text NOT NULL,
  `phone_number` text NOT NULL,
  `password` text NOT NULL,
  `status` text NOT NULL,
  `address` text NOT NULL,
  `image` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `completed_orders` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `phone_number`, `password`, `status`, `address`, `image`, `created_at`, `remember_token`, `remember_expiry`, `reset_token`, `reset_expiry`, `completed_orders`) VALUES
(14, 'capstonehosting0101@gmail.com', 'capstone', '09686409348', '$2a$12$UTRmgRu.XX6.7R9LpidjsufwjL2D8SVpJ1jnn5Gf6rpayPgdD2xV2', '', 'Dasmarinas Cavite, Sandionisio, blk 16', '', '2025-04-05 15:53:57', 'f2a109009fb76da377f3346d7ab800a0296fef29254330c9626d911e482ec2a9', '2025-05-11 12:33:55', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `work`
--

CREATE TABLE `work` (
  `id` int(11) NOT NULL,
  `work_name` text NOT NULL,
  `image` text NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work`
--

INSERT INTO `work` (`id`, `work_name`, `image`, `create_at`) VALUES
(18, 'Band Merchandise', 'uploads/works/work_67f3994366edc.jpeg', '2025-04-07 16:13:11'),
(19, 'Custom Hoodies', 'uploads/works/work_67f39950ddd36.jpeg', '2025-04-07 17:22:24'),
(20, 'Eco Bags', 'uploads/works/work_67f39bf1a5d05.jpeg', '2025-04-07 17:22:34'),
(21, 'Team Jersey', 'uploads/works/work_67f399684d487.jpeg', '2025-04-07 17:22:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `work`
--
ALTER TABLE `work`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `work`
--
ALTER TABLE `work`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
