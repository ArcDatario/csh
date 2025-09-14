-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2025 at 02:17 AM
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
(1, 'admin', 'admin', '$2a$12$W6a/qLGDukm4/xbLq8/2ouSFmwAOI0TH2bDGfM2ucq3p0fpezzUhW', 'Owner', 'admin_1.png', '$2y$10$DvPRR7Vze6fF1wy3.2hubePt79GfDcKi1/lJNdzH0feLJhsCC4rbi', '2025-10-14 01:55:24', '2025-09-14 07:55:24', '2025-04-04 14:28:31'),
(3, 'field', 'field manager', '$2y$10$dNldj0RIra/5ABt/XqG.Dutl36HkU7uDcblzUvDNCc2mgaw9nR5.W', 'Field Manager', '', NULL, NULL, '2025-09-14 07:53:54', '2025-04-10 14:59:04'),
(4, 'secretary', 'secretary', '$2y$10$9x9fhZ.zenNuZOc0NBonh.RrmEkZcf2SqKwWEW0GOAXAZDv3zdxLe', 'Secretary', 'admin_4.jpeg', NULL, NULL, '2025-09-14 07:51:29', '2025-04-10 14:59:32'),
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
(1, 101, '3213', 500),
(2, 102, '3213', 123),
(3, 102, '123', 400),
(4, 103, 'Purple', 100),
(5, 103, 'Brown', 400),
(6, 104, 'Gray', 500),
(7, 105, 'Purple', 250),
(8, 105, 'Pink', 250);

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
(200, 14, 83, 'New Quote, Direct to Film Print, 223', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 10:04:02'),
(201, 14, 83, 'admin just approved a quote price of ₱100.00 on ticket #523741', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-05 10:04:27'),
(202, 14, 83, 'Quote #523741 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-05 10:18:04'),
(203, 14, 83, 'Your order with ticket #523741 is ready for pickup. Our logistics team will pick up the items at your address: asdasdasd asdasdasdasd', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-09-05 10:18:16'),
(204, 14, 83, 'Order #523741 has been picked up and will be processed. Please prepare the materials needed for this order.', 'yes', '', '', '', '', '', '', '', '', '', '', '', '', '2025-09-05 10:18:23'),
(205, 0, 83, 'Order #523741 (Direct to Film Print, Qty: 223) has been picked up. Please prepare materials needed for production.', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'field_notification', '2025-09-05 10:18:23'),
(206, 14, 83, 'Order #523741 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 10:19:15'),
(207, 14, 83, 'Order #523741 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 10:19:18'),
(208, 14, 84, 'New Quote, Screen Printing, 333', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 12:35:17'),
(209, 14, 84, 'admin just approved a quote price of ₱99.00 on ticket #403762', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-05 12:36:42'),
(210, 14, 84, 'Quote #403762 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-05 12:36:57'),
(211, 14, 84, 'Your order with ticket #403762 is ready for pickup. Our logistics team will pick up the items at your address: asdasdasd asdasdasdasd', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-09-05 12:37:14'),
(212, 14, 84, 'Order #403762 has been picked up and will be processed. Please prepare the materials needed for this order.', 'yes', '', '', '', '', '', '', '', '', '', '', '', '', '2025-09-05 12:37:26'),
(213, 0, 84, 'Order #403762 (Screen Printing, Qty: 333) has been picked up. Please prepare materials needed for production.', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'field_notification', '2025-09-05 12:37:26'),
(214, 14, 83, 'Order with ticket #523741 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-05 12:37:38'),
(215, 14, 84, 'Order #403762 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 12:41:42'),
(216, 14, 84, 'Order #403762 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 12:41:45'),
(217, 14, 85, 'New Quote, Direct to Film Print, 1000', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 12:44:22'),
(218, 14, 86, 'New Quote, Emboss Print, 332', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 12:44:34'),
(219, 14, 86, 'admin just approved a quote price of ₱100.00 on ticket #720802', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-05 12:44:57'),
(220, 14, 86, 'Quote #720802 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-05 12:45:28'),
(221, 14, 86, 'Your order with ticket #720802 is ready for pickup. Our logistics team will pick up the items at your address: asdasdasd asdasdasdasd', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-09-05 12:45:48'),
(222, 14, 86, 'Order #720802 has been picked up and will be processed. Please prepare the materials needed for this order.', 'yes', '', '', '', '', '', '', '', '', '', '', '', '', '2025-09-05 12:45:56'),
(223, 0, 86, 'Order #720802 (Emboss Print, Qty: 332) has been picked up. Please prepare materials needed for production.', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'field_notification', '2025-09-05 12:45:56'),
(224, 14, 84, 'Order with ticket #403762 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-05 12:46:10'),
(225, 14, 87, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 12:59:38'),
(226, 14, 88, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:00:06'),
(227, 14, 89, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:03:22'),
(228, 14, 90, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:10:15'),
(229, 14, 91, 'New Quote, Direct to Film Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:16:09'),
(230, 14, 92, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:17:33'),
(231, 14, 93, 'New Quote, Direct to Film Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:27:24'),
(232, 14, 94, 'New Quote, Hi-Density Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:30:51'),
(233, 14, 95, 'New Quote, Hi-Density Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 13:36:19'),
(234, 14, 96, 'New Quote, Emboss Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-05 17:11:54'),
(235, 14, 96, 'Designer just added a quote price of ₱95 on ticket #698146', '', 'yes', 'yes', '', '', '', '', '', '', '', '', '', 'approved', '2025-09-05 17:30:08'),
(236, 14, 96, 'admin just approved a quote price of ₱100.00 on ticket #698146', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-05 17:36:39'),
(237, 14, 96, 'Quote #698146 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-05 17:37:01'),
(238, 14, 96, 'Your order with ticket #698146 is ready for pickup. Our logistics team will pick up the items at your address: asdasdasd asdasdasdasd', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-09-05 17:37:21'),
(239, 14, 96, 'Order #698146 has been picked up and will be processed. Please prepare the materials needed for this order.', 'yes', '', '', '', '', '', '', '', '', '', '', '', '', '2025-09-05 17:38:10'),
(240, 0, 96, 'Order #698146 (Emboss Print, Qty: 500) has been picked up. Please prepare materials needed for production.', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'field_notification', '2025-09-05 17:38:10'),
(241, 14, 96, 'Order #698146 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 17:38:50'),
(242, 14, 96, 'Order #698146 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 17:38:53'),
(243, 14, 96, 'Order #698146 has been marked as ready to ship and will be delivered to: asdasdasd asdasdasdasd', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-05 17:46:56'),
(244, 14, 97, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-07 16:58:29'),
(245, 14, 98, 'New Quote, Emboss Print, 588', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-07 17:01:41'),
(246, 14, 98, 'admin just approved a quote price of ₱100.00 on ticket #584099', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-07 23:17:13'),
(247, 14, 97, 'admin just approved a quote price of ₱100.00 on ticket #538174', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-09-07 23:38:39'),
(248, 14, 99, 'New Quote, Screen Printing, 588', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-12 00:18:23'),
(249, 14, 100, 'New Quote, Direct to Film Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-12 00:18:39'),
(250, 14, 98, 'Quote #584099 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-12 00:41:40'),
(251, 14, 98, 'Quote #584099 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-12 00:43:47'),
(252, 14, 98, 'Quote #584099 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-12 00:46:24'),
(253, 14, 97, 'Quote #538174 has been rejected by the user', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'reject', '2025-09-12 00:46:36'),
(254, 14, 101, 'New Quote, Direct to Film Print, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-14 06:39:56'),
(255, 14, 102, 'New Quote, Screen Printing, 523', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-14 06:41:42'),
(256, 14, 103, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-14 06:46:18'),
(257, 14, 104, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-14 07:48:28'),
(258, 14, 105, 'New Quote, Screen Printing, 500', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-14 07:49:38');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `ticket`, `user_id`, `print_type`, `quantity`, `pricing`, `subtotal`, `total`, `note`, `status`, `address`, `is_approved_designer`, `designer_approved_date`, `is_user_approved`, `user_approved_date`, `is_approved_admin`, `admin_approved_date`, `is_for_pickup`, `pickup_date`, `pickup_attempt`, `is_for_processing`, `processing_date`, `shipping_date`, `is_delivered`, `delivered_date`, `is_approved_field_manager`, `field_manager_approved_date`, `completion_date`, `design_file`, `created_at`) VALUES
(101, 783210, 14, 'Direct to Film Print', 500, NULL, NULL, 0, 'asdasdasdasdasd', 'pending', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68c5f2bc67369_Gemini_Generated_Image_qh3xtvqh3xtvqh3x.png', '2025-09-13 22:39:56'),
(102, 611484, 14, 'Screen Printing', 523, NULL, NULL, 0, 'asdasdasdasd', 'pending', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68c5f326a20b1_Gemini_Generated_Image_qh3xtvqh3xtvqh3x.png', '2025-09-13 22:41:42'),
(103, 987280, 14, 'Screen Printing', 500, NULL, NULL, 0, 'asdasdasdasd', 'pending', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68c5f43a3aaa2_Gemini_Generated_Image_qh3xtvqh3xtvqh3x.png', '2025-09-13 22:46:18'),
(104, 391967, 14, 'Screen Printing', 500, NULL, NULL, 0, 'asdasdasdasd', 'pending', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68c602cc53229_Gemini_Generated_Image_qh3xtvqh3xtvqh3x.png', '2025-09-13 23:48:28'),
(105, 313006, 14, 'Screen Printing', 500, NULL, NULL, 0, 'asdasdasdasdasd', 'pending', 'asdasdasd asdasdasdasd', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, 0, '', NULL, NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68c6031285754_Gemini_Generated_Image_qh3xtvqh3xtvqh3x.png', '2025-09-13 23:49:38');

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
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_from` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(14, 'capstoneproject0101@gmail.com', 'capstones', '09946726471', '$2y$10$ezjIhkPHNXQca9yQG..9KeYitPC7dPkzKfKsxsWsEg6CMGmbE6epq', '', 'asdasdasd asdasdasdasd', '68b48f385049d.png', '2025-06-05 15:53:57', '28cb0b79f83a4e043271a3f5fbddf326869a4c5851b216c4680de94b829cc2b7', '2025-10-14 08:12:14', NULL, NULL, 7);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=259;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

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
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
