-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2025 at 07:09 PM
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
  `image` text NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `fullname`, `password`, `role`, `image`, `token`, `token_expiry`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin', '$2a$12$W6a/qLGDukm4/xbLq8/2ouSFmwAOI0TH2bDGfM2ucq3p0fpezzUhW', 'Owner', 'admin_1.png', NULL, NULL, '2025-09-04 18:13:11', '2025-04-04 14:28:31'),
(3, 'field', 'field manager', '$2y$10$dNldj0RIra/5ABt/XqG.Dutl36HkU7uDcblzUvDNCc2mgaw9nR5.W', 'Field Manager', '', NULL, NULL, '2025-09-04 18:09:14', '2025-04-10 14:59:04'),
(4, 'secretary', 'secretary', '$2y$10$9x9fhZ.zenNuZOc0NBonh.RrmEkZcf2SqKwWEW0GOAXAZDv3zdxLe', 'Secretary', 'admin_4.jpeg', '$2y$10$a4WwlNpPFcHvRliYkZHkSON.MacFSLqJX75K4rEMJh1EGIbntnA.O', '2025-10-04 17:41:59', '2025-09-04 23:41:59', '2025-04-10 14:59:32'),
(5, 'generalmanager', 'generalmanager', '$2y$10$rb9NPHDhtrGbe6DaTLSdgedNP7F9gdllkT99pdyasHfIXDWgOe.Q6', 'General Manager', '', NULL, NULL, '2025-09-03 09:28:15', '2025-04-10 15:00:30'),
(6, 'designer', 'designer', '$2y$10$s45PNv7UuFvz7dWrMomkxOC7ORdzu95m4E6wWJU.D8vwbLiKUv94C', 'Designer', '', NULL, NULL, '2025-09-04 10:26:13', '2025-04-10 15:50:00');

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
(2, 'chemical', 2344, '2025-04-08 17:24:24', '2025-06-06 17:32:14'),
(3, 'shirts', 321, '2025-06-06 16:11:10', '2025-06-06 16:11:10'),
(4, 'screen', 656, '2025-06-06 16:11:21', '2025-06-06 17:27:42'),
(5, 'prints', 22, '2025-06-06 16:11:42', '2025-06-06 16:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `notify_user` text NOT NULL,
  `notify_owner` text NOT NULL,
  `notify_manager` text NOT NULL,
  `notify_designer` text NOT NULL,
  `notify_secretary` text NOT NULL,
  `notify_field` text NOT NULL,
  `is_viewed_owner` text NOT NULL,
  `is_viewed_user` text NOT NULL,
  `is_viewed_manager` text NOT NULL,
  `is_viewed_secretary` text NOT NULL,
  `is_viewed_field_manager` text NOT NULL,
  `is_viewed_designer` text NOT NULL,
  `status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `pickup_attempt` int(11) NOT NULL,
  `is_for_processing` text NOT NULL,
  `processing_date` datetime DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  `is_delivered` text NOT NULL,
  `delivered_date` datetime DEFAULT NULL,
  `is_approved_field_manager` text NOT NULL,
  `field_manager_approved_date` datetime NOT NULL,
  `completion_date` datetime DEFAULT NULL,
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
-- Table structure for table `stock_requests`
--

CREATE TABLE `stock_requests` (
  `id` int(11) NOT NULL,
  `field_manager_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `status` text NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `is_prepairing` text NOT NULL,
  `prepairing_date` datetime DEFAULT NULL,
  `is_for_delivery` text NOT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `is_completed` text NOT NULL,
  `completed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_requests`
--

INSERT INTO `stock_requests` (`id`, `field_manager_id`, `item_id`, `item_name`, `quantity_requested`, `status`, `request_date`, `is_prepairing`, `prepairing_date`, `is_for_delivery`, `delivery_date`, `is_completed`, `completed_date`) VALUES
(1, 3, 2, 'chemical', 23, 'completed', '2025-06-07 00:24:20', 'yes', '2025-06-07 01:28:23', 'yes', '2025-06-07 01:32:03', '', '2025-06-07 01:32:14'),
(2, 3, 3, 'shirts', 22, 'preparing', '2025-06-07 00:24:20', 'yes', '2025-06-07 01:39:29', '', NULL, '', NULL),
(3, 3, 4, 'screen', 334, 'completed', '2025-06-07 00:35:26', 'yes', '2025-06-07 00:49:05', 'yes', '2025-06-07 01:27:16', '', '2025-06-07 01:27:42'),
(4, 3, 5, 'prints', 234, 'completed', '2025-06-07 00:35:26', 'yes', '2025-06-07 00:55:27', 'yes', '2025-06-07 00:57:13', 'yes', '2025-06-07 01:26:32'),
(5, 3, 2, 'chemical', 500, 'pending', '2025-08-27 16:44:45', 'no', NULL, 'no', NULL, '', NULL),
(6, 3, 2, 'chemical', 100, 'pending', '2025-09-04 16:53:12', 'no', NULL, 'no', NULL, '', NULL),
(7, 3, 3, 'shirts', 22, 'pending', '2025-09-04 16:53:12', 'no', NULL, 'no', NULL, '', NULL),
(8, 3, 4, 'screen', 22, 'pending', '2025-09-04 16:53:12', 'no', NULL, 'no', NULL, '', NULL),
(9, 3, 5, 'prints', 22, 'pending', '2025-09-04 16:53:12', 'no', NULL, 'no', NULL, '', NULL);

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
(14, 'capstoneproject0101@gmail.com', 'capstones', '09946726471', '$2y$10$ezjIhkPHNXQca9yQG..9KeYitPC7dPkzKfKsxsWsEg6CMGmbE6epq', '', 'asdasdasd asdasdasdasd', '68b48f385049d.png', '2025-06-05 15:53:57', 'a9db65d136f87603ba2dc89af5721c34562b035695d0f137c204fee3298745d0', '2025-10-03 09:26:12', NULL, NULL, 5);

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
-- Indexes for table `stock_requests`
--
ALTER TABLE `stock_requests`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `work`
--
ALTER TABLE `work`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
