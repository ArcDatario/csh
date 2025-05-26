-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 11:53 PM
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
(1, 'admin', 'admin', '$2a$12$6qfcEBPijiPjljmsgzT/Z.Vts08sppSiEckZzKKalQmAc/w1hSRd2', 'Owner', '$2y$10$YUCdWWPzTD69Nj54rNmUmeNjsWssDDJJcjowvdNMrZIl2PyekKPZ2', '2025-06-25 23:49:19', '2025-05-27 05:49:19', '2025-04-04 14:28:31'),
(3, 'field', 'field manager', '$2y$10$dNldj0RIra/5ABt/XqG.Dutl36HkU7uDcblzUvDNCc2mgaw9nR5.W', 'Field Manager', NULL, NULL, '2025-05-27 02:23:14', '2025-04-10 14:59:04'),
(4, 'secretary', 'secretary', '$2y$10$9x9fhZ.zenNuZOc0NBonh.RrmEkZcf2SqKwWEW0GOAXAZDv3zdxLe', 'Secretary', NULL, NULL, '2025-05-27 03:58:38', '2025-04-10 14:59:32'),
(5, 'generalmanager', 'generalmanager', '$2y$10$0fKTvr4qKLmTaHNA5z1rxOg/Shs8eyyNya9ytYUCIJWovdht2Xx.i', 'General Manager', NULL, NULL, '2025-05-27 02:21:27', '2025-04-10 15:00:30'),
(6, 'designer', 'designer', '$2y$10$Q6KTpNkRNA7ulPe6GKGDVuFVZzF3eW6GtZ1y2PGO5CKQNve.4/lh.', 'Designer', NULL, NULL, '2025-05-27 02:23:21', '2025-04-10 15:50:00');

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
(46, 19, NULL, 'New Quote, Screen Printing, 233', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-01 20:01:49'),
(47, 20, NULL, 'New Quote, Emboss Print, 3221', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-01 20:12:41'),
(48, 20, NULL, 'Designer just added a quote price of ₱300 on ticket #111792', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-02 09:02:54'),
(49, 20, NULL, 'New Quote, Screen Printing, 321', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-02 16:32:10'),
(50, 20, NULL, 'Designer just added a quote price of ₱300 on ticket #392779', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-02 16:32:56'),
(51, 20, NULL, 'admin just approved a quote price of ₱299 on ticket #392779', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-02 16:34:00'),
(52, 22, NULL, 'New Quote, Screen Printing, 1231', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-07 10:10:42'),
(53, 22, NULL, 'New Quote, Emboss Print, 322', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-16 09:18:01'),
(54, 22, NULL, 'New Quote, Silk Screen Print, 432', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-19 06:54:15'),
(55, 22, NULL, 'Designer just added a quote price of ₱99 on ticket #458895', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-19 06:55:00'),
(56, 22, NULL, 'Designer just added a quote price of ₱89 on ticket #249247', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-19 06:55:05'),
(57, 22, NULL, 'admin just approved a quote price of ₱98 on ticket #458895', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-19 07:00:47'),
(58, 22, NULL, 'admin just approved a quote price of ₱90 on ticket #249247', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-19 07:06:31'),
(59, 22, 54, 'Quote #249247 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 07:44:30'),
(60, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 07:49:10'),
(61, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 07:53:20'),
(62, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 07:56:56'),
(63, 22, 56, 'Quote #458895 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 07:57:29'),
(64, 22, 54, 'Quote #249247 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 08:21:00'),
(65, 22, NULL, 'New Quote, Emboss Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-19 08:21:28'),
(66, 22, NULL, 'admin just approved a quote price of ₱99 on ticket #204373', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-19 08:21:47'),
(67, 22, 55, 'Quote #204373 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-19 08:31:00'),
(68, 22, NULL, 'admin just approved a quote price of ₱99 on ticket #549865', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-19 08:39:33'),
(69, 14, NULL, 'New Quote, Direct to Film Print, 312', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 19:59:12'),
(70, 14, NULL, 'admin just approved a quote price of ₱90 on ticket #521562', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:05:31'),
(71, 14, NULL, 'New Quote, Glitters Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:23:34'),
(72, 14, NULL, 'New Quote, Emboss Print, 321', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:23:48'),
(73, 14, NULL, 'Designer just added a quote price of ₱90 on ticket #213805', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:25:39'),
(74, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:27:46'),
(75, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:27:52'),
(76, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:27:55'),
(77, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:27:57'),
(78, 14, NULL, 'New Quote, Hi-Density Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:30:36'),
(79, 14, NULL, 'Designer just added a quote price of ₱90 on ticket #311232', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:32:28'),
(80, 14, NULL, 'admin just approved a quote price of ₱ on ticket #311232', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:50:25'),
(81, 14, NULL, 'New Quote, Direct to Film Print, 1321', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:50:45'),
(82, 14, NULL, 'Designer just added a quote price of ₱70 on ticket #387034', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:54:45'),
(83, 14, NULL, 'admin just approved a quote price of ₱ on ticket #387034', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:54:58'),
(84, 14, NULL, 'New Quote, Emboss Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:57:13'),
(85, 14, NULL, 'Designer just added a quote price of ₱78 on ticket #411608', 'yes', 'yes', '', '', '', 'yes', '', '', '', '', '', '2025-05-26 20:57:22'),
(86, 14, NULL, 'admin just approved a quote price of ₱78.00 on ticket #411608', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:57:42'),
(87, 14, NULL, 'admin just approved a quote price of ₱90.00 on ticket #914041', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 20:58:08'),
(88, 14, NULL, 'New Quote, Emboss Print, 34', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 21:19:45'),
(89, 14, NULL, 'admin just approved a quote price of ₱90.00 on ticket #692559', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 21:20:05'),
(90, 14, NULL, 'New Quote, Emboss Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 21:20:24'),
(91, 14, NULL, 'admin just approved a quote price of ₱12.00 on ticket #629538', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 21:20:36'),
(92, 14, NULL, 'New Quote, Glitters Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 21:28:29'),
(93, 14, NULL, 'admin just approved a quote price of ₱53.00 on ticket #457944', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 21:28:36'),
(94, 14, NULL, 'New Quote, Screen Printing, 321', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 21:29:01'),
(95, 14, NULL, 'admin just approved a quote price of ₱42.00 on ticket #930798', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-26 21:29:08'),
(96, 14, NULL, 'New Quote, Emboss Print, 321', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-26 21:33:09'),
(97, 14, NULL, 'admin just approved a quote price of ₱70.00 on ticket #189152', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-27 00:07:14'),
(98, 14, NULL, 'New Quote, Screen Printing, 312', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 00:54:48'),
(99, 14, NULL, 'New Quote, Glitters Print, 32', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 00:55:11'),
(100, 14, 68, 'Quote #189152 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-27 01:34:21'),
(101, 14, 67, 'Quote #930798 has been agreed to the price', 'yes', 'yes', '', 'yes', '', 'yes', '', '', '', '', '', '2025-05-27 01:36:23'),
(102, 14, NULL, 'Your order with ticket #189152 is ready for pickup', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-27 03:29:52'),
(103, 14, NULL, 'Your order with ticket #189152 is ready for pickup', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-27 03:42:12'),
(104, 14, NULL, 'Your order with ticket #189152 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', 'yes', 'yes', '', '', '', '', '', '2025-05-27 03:55:13'),
(105, 14, 71, 'New Quote, Hi-Density Print, 312', '', '', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 04:01:38'),
(106, 14, 72, 'New Quote, Screen Printing, 213', 'yes', 'yes', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 05:38:28'),
(107, 14, 73, 'New Quote, Screen Printing, 65', 'yes', 'yes', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 05:41:03'),
(108, 14, 74, 'New Quote, Screen Printing, 55', 'yes', 'yes', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 05:42:38'),
(109, 14, 75, 'New Quote, Emboss Print, 42', 'yes', 'yes', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 05:45:10'),
(110, 14, 76, 'New Quote, Screen Printing, 142', 'yes', 'yes', 'yes', '', '', 'yes', '', '', '', '', '', '2025-05-27 05:47:35');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `ticket`, `user_id`, `print_type`, `quantity`, `pricing`, `subtotal`, `total`, `note`, `status`, `address`, `is_approved_designer`, `designer_approved_date`, `is_user_approved`, `user_approved_date`, `is_approved_admin`, `admin_approved_date`, `is_for_pickup`, `pickup_date`, `is_for_processing`, `processing_date`, `is_delivered`, `delivered_date`, `is_approved_field_manager`, `field_manager_approved_date`, `complete_date`, `design_file`, `created_at`) VALUES
(64, 692559, 14, 'Emboss Print', 34, 90.00, 3060.00, 0, 'asdasd asdasd asd', 'approved', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'yes', '2025-05-26 21:20:05', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68346a7165731_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 13:19:45'),
(65, 629538, 14, 'Emboss Print', 32, 12.00, 384.00, 0, 'adsasd asd asd as', 'approved', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'yes', '2025-05-26 21:20:36', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68346a98cb9bd_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 13:20:24'),
(66, 457944, 14, 'Glitters Print', 32, 53.00, 1696.00, 0, 'asdasdasdasd', 'approved', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'yes', '2025-05-26 21:28:36', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68346c7d46d02_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 13:28:29'),
(67, 930798, 14, 'Screen Printing', 321, 42.00, 13482.00, 0, 'asdas dasdas dasd asd', 'to-pick-up', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', 'yes', '2025-05-27 01:36:23', 'yes', '2025-05-26 21:29:08', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68346c9d00a9d_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 13:29:01'),
(68, 189152, 14, 'Emboss Print', 321, 70.00, 22470.00, 0, 'asdasdasd', 'to-pick-up', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', 'yes', '2025-05-27 01:34:21', 'yes', '2025-05-27 00:07:14', 'yes', '2025-05-27 03:55:13', '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68346d953d7bd_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 13:33:09'),
(69, 300804, 14, 'Screen Printing', 312, NULL, NULL, 0, 'asdasdasd', 'pending', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68349cd8821a8_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 16:54:48'),
(70, 931242, 14, 'Glitters Print', 32, NULL, NULL, 0, 'asdasdsad', 'pending', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/68349cefd80da_flametrack_logo_related_to_fire_estinguisher.jpeg', '2025-05-26 16:55:11'),
(75, 162420, 14, 'Emboss Print', 42, NULL, NULL, 0, 'asdasd asdasd', 'pending', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/6834e0e643756_457944-glitters-print.jpeg', '2025-05-26 21:45:10'),
(76, 411058, 14, 'Screen Printing', 142, NULL, NULL, 0, 'asdasdasdasd', 'pending', 'Dasmarinas Cavite, Sandionisio, blk 16', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '', NULL, '', NULL, '', NULL, '', '0000-00-00 00:00:00', NULL, 'uploads/6834e1772e252_457944-glitters-print.jpeg', '2025-05-26 21:47:35');

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
(14, 'capstoneproject0101@gmail.com', 'capstone', '09686409348', '$2a$12$UTRmgRu.XX6.7R9LpidjsufwjL2D8SVpJ1jnn5Gf6rpayPgdD2xV2', '', 'Dasmarinas Cavite, Sandionisio, blk 16', '', '2025-04-05 15:53:57', 'd946dd7bd4abd7641e41e8ce34724144bb37163bfeed84b224318779ba11b94d', '2025-06-26 04:00:54', NULL, NULL, 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

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
