-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2025 at 02:45 PM
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
(1, 'admin', 'admin', '$2a$12$W6a/qLGDukm4/xbLq8/2ouSFmwAOI0TH2bDGfM2ucq3p0fpezzUhW', 'Owner', 'admin_1.png', '$2y$10$u/CTXa4VdvBxtdC.JEkTiu09Dc3Mh6BrtkOVLIT8V5xOAOQHDw8LG', '2025-10-20 07:56:53', '2025-09-20 13:56:53', '2025-04-04 14:28:31'),
(3, 'field', 'field manager', '$2y$10$dNldj0RIra/5ABt/XqG.Dutl36HkU7uDcblzUvDNCc2mgaw9nR5.W', 'Field Manager', '', '$2y$10$TkxHTc5bVy6qB8Q0TyYDdOiYmNScGPcMU5KQ60yWr8vvr.yol7lAS', '2025-10-20 08:14:02', '2025-09-20 14:14:02', '2025-04-10 14:59:04'),
(4, 'secretary', 'secretary', '$2y$10$9x9fhZ.zenNuZOc0NBonh.RrmEkZcf2SqKwWEW0GOAXAZDv3zdxLe', 'Secretary', 'admin_4.jpeg', NULL, NULL, '2025-09-16 04:49:44', '2025-04-10 14:59:32'),
(5, 'generalmanager', 'generalmanager', '$2y$10$rb9NPHDhtrGbe6DaTLSdgedNP7F9gdllkT99pdyasHfIXDWgOe.Q6', 'General Manager', '', NULL, NULL, '2025-09-03 09:28:15', '2025-04-10 15:00:30'),
(6, 'designer', 'designer', '$2y$10$s45PNv7UuFvz7dWrMomkxOC7ORdzu95m4E6wWJU.D8vwbLiKUv94C', 'Designer', '', NULL, NULL, '2025-09-14 07:49:06', '2025-04-10 15:50:00');

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
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `shirt_color` text NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `order_id`, `shirt_color`, `quantity`) VALUES
(14, 114, 'Shirt (Maroon)', 500),
(15, 115, 'Shirt (Teal)', 500),
(16, 116, 'Shirt (Teal)', 500),
(17, 117, 'Shirt (Maroon)', 500);

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

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `order_id`, `content`, `notify_user`, `notify_owner`, `notify_manager`, `notify_designer`, `notify_secretary`, `notify_field`, `is_viewed_owner`, `is_viewed_user`, `is_viewed_manager`, `is_viewed_secretary`, `is_viewed_field_manager`, `is_viewed_designer`, `status`, `created_at`) VALUES
(273, 14, 114, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-20 13:57:35'),
(274, 14, 115, 'New Quote, Hi-Density Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-20 14:07:42'),
(275, 14, 115, 'admin has cancelled your order with ticket #611199. Reason: Quantity is Over', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'cancelled', '2025-09-20 14:09:41'),
(276, 14, 114, 'admin has cancelled your order with ticket #491869. Reason: Out of Stock', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'cancelled', '2025-09-20 14:11:59'),
(277, 14, 116, 'New Quote, Direct to Film Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-20 14:12:32'),
(278, 14, 116, 'admin just approved a quote price of ₱100.00 on ticket #906848', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-20 14:12:49'),
(279, 14, 116, 'Quote #906848 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-20 14:13:16'),
(280, 14, 116, 'Your order with ticket #906848 is ready for pickup. Our logistics team will pick up the items at your address: asdasdasd asdasdasdasd', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-09-20 14:13:22'),
(281, 14, 116, 'Order #906848 has been picked up and will be processed. Please prepare the materials needed for this order.', 'yes', '', '', '', '', '', '', '', '', '', '', '', '', '2025-09-20 14:13:29'),
(282, 14, 116, 'Order #906848 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-20 14:14:18'),
(283, 14, 116, 'Order with ticket #906848 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-20 14:14:31'),
(284, 14, 117, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-20 14:17:47'),
(285, 14, 117, 'admin just approved a quote price of ₱100.00 on ticket #592750', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-20 20:41:48');

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
  `cancelled_date` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `is_approved_field_manager` text NOT NULL,
  `field_manager_approved_date` datetime NOT NULL,
  `completion_date` datetime DEFAULT NULL,
  `design_file` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `ticket`, `user_id`, `print_type`, `quantity`, `pricing`, `subtotal`, `total`, `note`, `status`, `address`, `is_approved_designer`, `designer_approved_date`, `is_user_approved`, `user_approved_date`, `is_approved_admin`, `admin_approved_date`, `is_for_pickup`, `pickup_date`, `pickup_attempt`, `is_for_processing`, `processing_date`, `shipping_date`, `is_delivered`, `delivered_date`, `cancelled_date`, `cancellation_reason`, `is_approved_field_manager`, `field_manager_approved_date`, `completion_date`, `design_file`, `created_at`) VALUES
(114, 491869, 14, 'Screen Printing', 500, NULL, NULL, 0, 'asdasdasdasdasd', 'cancelled', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '2025-09-20 14:11:59', 'Out of Stock', '', '0000-00-00 00:00:00', NULL, 'uploads/68ce424f4857e_68cd6006ba7dd_987280-screen-printing.png', '2025-09-20 05:57:35'),
(115, 611199, 14, 'Hi-Density Print', 500, NULL, NULL, 0, 'asdasdasdasdasdasdasdasd', 'cancelled', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '2025-09-20 14:09:41', 'Quantity is Over', '', '0000-00-00 00:00:00', NULL, 'uploads/68ce44aed88d9_987280-screen-printing.png', '2025-09-20 06:07:42'),
(116, 906848, 14, 'Direct to Film Print', 500, 100.00, 50000.00, 50000, 'asd', 'completed', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', 'yes', '2025-09-20 14:13:16', 'yes', '2025-09-20 14:12:49', 'yes', '2025-09-20 14:13:29', 1, 'yes', '2025-09-20 14:14:08', '2025-09-20 14:14:18', '', NULL, NULL, NULL, '', '0000-00-00 00:00:00', '2025-09-20 14:14:31', 'uploads/68ce45d078af9_987280-screen-printing.png', '2025-09-20 06:12:32'),
(117, 592750, 14, 'Screen Printing', 500, 100.00, 50000.00, 0, 'asdasdasd', 'approved', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'yes', '2025-09-20 20:41:48', '', NULL, 0, '', NULL, NULL, '', NULL, NULL, NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68ce470b7c6d9_white_blank_paper_scroll.psd', '2025-09-20 06:17:47');

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
(12, 'Sublimation Printing', 'Vibrant, all-over prints that won\'t crack or fade with our sublimation process.', 'uploads/services/service_67f3989e77156.jpeg', '2025-04-07 15:00:03'),
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

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_from` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `account_id`, `content`, `is_from`, `created_at`) VALUES
(1, 1, 'update admin #3: username: \'field\' → \'fields\'', 'admin_management', '2025-09-14 08:34:24'),
(2, 1, 'update admin #3: username: \'fields\' → \'field\'', 'admin_management', '2025-09-14 08:36:01'),
(3, 1, 'Approved a quote of ₱100.00 (Subtotal: ₱50,000.00) for Ticket #313006', 'Orders', '2025-09-19 19:43:31'),
(4, 1, 'Approved a quote of ₱100.00 (Subtotal: ₱50,000.00) for Ticket #391967', 'Orders', '2025-09-19 20:07:57'),
(5, 1, 'Cancelled order with Ticket #987280', 'Orders', '2025-09-19 20:18:51'),
(6, 1, 'Cancelled order with Ticket #300617. Reason: Quantity is to Large', 'Orders', '2025-09-19 22:06:54'),
(7, 1, 'Approved a quote of ₱99.00 (Subtotal: ₱49,500.00) for Ticket #256199', 'Orders', '2025-09-20 02:16:47'),
(8, 1, 'Cancelled order with Ticket #611199. Reason: Quantity is Over', 'Orders', '2025-09-20 14:09:45'),
(9, 1, 'Cancelled order with Ticket #491869. Reason: Out of Stock', 'Orders', '2025-09-20 14:12:06'),
(10, 1, 'Approved a quote of ₱100.00 (Subtotal: ₱50,000.00) for Ticket #906848', 'Orders', '2025-09-20 14:12:52'),
(11, 1, 'Marked Ticket #906848 for pickup', 'Orders', '2025-09-20 14:13:25'),
(12, 1, 'Order Ticket #906848 picked up (Quantity: 500)', 'Orders', '2025-09-20 14:13:32'),
(13, 3, 'Marked Ticket #906848 as processing', 'Orders', '2025-09-20 14:14:08'),
(14, 3, 'Marked Ticket #906848 (Quantity: 500, ₱50,000.00) as ready to ship', 'Orders', '2025-09-20 14:14:21'),
(15, 1, 'Marked Ticket #906848 (₱50,000.00) as completed / delivered on Sep 20, 2025', 'Orders', '2025-09-20 14:14:34'),
(16, 1, 'Approved a quote of ₱100.00 (Subtotal: ₱50,000.00) for Ticket #592750', 'Orders', '2025-09-20 20:41:52');

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
(14, 'capstoneproject0101@gmail.com', 'capstones', '09946726471', '$2y$10$ezjIhkPHNXQca9yQG..9KeYitPC7dPkzKfKsxsWsEg6CMGmbE6epq', '', 'asdasdasd asdasdasdasd', '68b48f385049d.png', '2025-06-05 15:53:57', '56c0f00a1e37c29d64159d8b23638d8250322698947ebc01bf9924e54ab6cb61', '2025-10-19 21:43:06', NULL, NULL, 8);

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
-- Indexes for table `items`
--
ALTER TABLE `items`
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
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
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
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=286;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
