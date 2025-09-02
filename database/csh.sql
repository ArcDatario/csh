-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 09:37 AM
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
(1, 'admin', 'admin', '$2a$12$W6a/qLGDukm4/xbLq8/2ouSFmwAOI0TH2bDGfM2ucq3p0fpezzUhW', 'Owner', 'admin_1.png', NULL, NULL, '2025-09-02 15:04:44', '2025-04-04 14:28:31'),
(3, 'field', 'field manager', '$2y$10$dNldj0RIra/5ABt/XqG.Dutl36HkU7uDcblzUvDNCc2mgaw9nR5.W', 'Field Manager', '', NULL, NULL, '2025-09-02 15:05:38', '2025-04-10 14:59:04'),
(4, 'secretary', 'secretary', '$2y$10$9x9fhZ.zenNuZOc0NBonh.RrmEkZcf2SqKwWEW0GOAXAZDv3zdxLe', 'Secretary', 'admin_4.jpeg', '$2y$10$pn8hXLLdwnx3M2Lp7rAgUuZ3ozPxfg65JaVa1u2UUEQbMMDR1gqba', '2025-10-02 09:06:20', '2025-09-02 15:06:20', '2025-04-10 14:59:32'),
(5, 'generalmanager', 'generalmanager', '$2y$10$BK0jjQyIj6wnRa/GvFCch.uDOYyt13AT/qKJL38N6LbDenXOGmELK', 'General Manager', '', NULL, NULL, '2025-08-27 16:27:02', '2025-04-10 15:00:30'),
(6, 'designer', 'designer', '$2y$10$s45PNv7UuFvz7dWrMomkxOC7ORdzu95m4E6wWJU.D8vwbLiKUv94C', 'Designer', '', NULL, NULL, '2025-08-27 16:32:32', '2025-04-10 15:50:00');

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

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `order_id`, `content`, `notify_user`, `notify_owner`, `notify_manager`, `notify_designer`, `notify_secretary`, `notify_field`, `is_viewed_owner`, `is_viewed_user`, `is_viewed_manager`, `is_viewed_secretary`, `is_viewed_field_manager`, `is_viewed_designer`, `status`, `created_at`) VALUES
(46, 19, NULL, 'New Quote, Screen Printing, 233', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-01 20:01:49'),
(47, 20, NULL, 'New Quote, Emboss Print, 3221', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-01 20:12:41'),
(48, 20, NULL, 'Designer just added a quote price of ₱300 on ticket #111792', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-02 09:02:54'),
(49, 20, NULL, 'New Quote, Screen Printing, 321', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-02 16:32:10'),
(50, 20, NULL, 'Designer just added a quote price of ₱300 on ticket #392779', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-02 16:32:56'),
(51, 20, NULL, 'admin just approved a quote price of ₱299 on ticket #392779', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-02 16:34:00'),
(52, 22, NULL, 'New Quote, Screen Printing, 1231', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-07 10:10:42'),
(53, 22, NULL, 'New Quote, Emboss Print, 322', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-16 09:18:01'),
(54, 22, NULL, 'New Quote, Silk Screen Print, 432', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 06:54:15'),
(55, 22, NULL, 'Designer just added a quote price of ₱99 on ticket #458895', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 06:55:00'),
(56, 22, NULL, 'Designer just added a quote price of ₱89 on ticket #249247', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 06:55:05'),
(57, 22, NULL, 'admin just approved a quote price of ₱98 on ticket #458895', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:00:47'),
(58, 22, NULL, 'admin just approved a quote price of ₱90 on ticket #249247', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:06:31'),
(59, 22, 54, 'Quote #249247 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:44:30'),
(60, 22, 56, 'Quote #458895 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:49:10'),
(61, 22, 56, 'Quote #458895 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:53:20'),
(62, 22, 56, 'Quote #458895 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:56:56'),
(63, 22, 56, 'Quote #458895 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 07:57:29'),
(64, 22, 54, 'Quote #249247 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 08:21:00'),
(65, 22, NULL, 'New Quote, Emboss Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 08:21:28'),
(66, 22, NULL, 'admin just approved a quote price of ₱99 on ticket #204373', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 08:21:47'),
(67, 22, 55, 'Quote #204373 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 08:31:00'),
(68, 22, NULL, 'admin just approved a quote price of ₱99 on ticket #549865', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-19 08:39:33'),
(69, 14, NULL, 'New Quote, Direct to Film Print, 312', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 19:59:12'),
(70, 14, NULL, 'admin just approved a quote price of ₱90 on ticket #521562', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:05:31'),
(71, 14, NULL, 'New Quote, Glitters Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:23:34'),
(72, 14, NULL, 'New Quote, Emboss Print, 321', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:23:48'),
(73, 14, NULL, 'Designer just added a quote price of ₱90 on ticket #213805', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:25:39'),
(74, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:27:46'),
(75, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:27:52'),
(76, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:27:55'),
(77, 14, NULL, 'admin just approved a quote price of ₱ on ticket #213805', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:27:57'),
(78, 14, NULL, 'New Quote, Hi-Density Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:30:36'),
(79, 14, NULL, 'Designer just added a quote price of ₱90 on ticket #311232', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:32:28'),
(80, 14, NULL, 'admin just approved a quote price of ₱ on ticket #311232', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:50:25'),
(81, 14, NULL, 'New Quote, Direct to Film Print, 1321', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:50:45'),
(82, 14, NULL, 'Designer just added a quote price of ₱70 on ticket #387034', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:54:45'),
(83, 14, NULL, 'admin just approved a quote price of ₱ on ticket #387034', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:54:58'),
(84, 14, NULL, 'New Quote, Emboss Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:57:13'),
(85, 14, NULL, 'Designer just added a quote price of ₱78 on ticket #411608', '', 'yes', 'yes', '', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:57:22'),
(86, 14, NULL, 'admin just approved a quote price of ₱78.00 on ticket #411608', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:57:42'),
(87, 14, NULL, 'admin just approved a quote price of ₱90.00 on ticket #914041', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 20:58:08'),
(88, 14, NULL, 'New Quote, Emboss Print, 34', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:19:45'),
(89, 14, NULL, 'admin just approved a quote price of ₱90.00 on ticket #692559', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:20:05'),
(90, 14, NULL, 'New Quote, Emboss Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:20:24'),
(91, 14, NULL, 'admin just approved a quote price of ₱12.00 on ticket #629538', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:20:36'),
(92, 14, NULL, 'New Quote, Glitters Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:28:29'),
(93, 14, NULL, 'admin just approved a quote price of ₱53.00 on ticket #457944', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:28:36'),
(94, 14, NULL, 'New Quote, Screen Printing, 321', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:29:01'),
(95, 14, NULL, 'admin just approved a quote price of ₱42.00 on ticket #930798', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:29:08'),
(96, 14, NULL, 'New Quote, Emboss Print, 321', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-26 21:33:09'),
(97, 14, NULL, 'admin just approved a quote price of ₱70.00 on ticket #189152', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 00:07:14'),
(98, 14, NULL, 'New Quote, Screen Printing, 312', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 00:54:48'),
(99, 14, NULL, 'New Quote, Glitters Print, 32', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 00:55:11'),
(100, 14, 68, 'Quote #189152 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 01:34:21'),
(101, 14, 67, 'Quote #930798 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 01:36:23'),
(102, 14, NULL, 'Your order with ticket #189152 is ready for pickup', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 03:29:52'),
(103, 14, NULL, 'Your order with ticket #189152 is ready for pickup', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 03:42:12'),
(104, 14, NULL, 'Your order with ticket #189152 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 03:55:13'),
(105, 14, 71, 'New Quote, Hi-Density Print, 312', '', '', '', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 04:01:38'),
(106, 14, 72, 'New Quote, Screen Printing, 213', '', 'yes', 'yes', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 05:38:28'),
(107, 14, 73, 'New Quote, Screen Printing, 65', '', 'yes', 'yes', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 05:41:03'),
(108, 14, 74, 'New Quote, Screen Printing, 55', '', 'yes', 'yes', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 05:42:38'),
(109, 14, 75, 'New Quote, Emboss Print, 42', '', 'yes', 'yes', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 05:45:10'),
(110, 14, 76, 'New Quote, Screen Printing, 142', '', 'yes', 'yes', 'yes', '', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-27 05:47:35'),
(111, 14, 67, 'Your order with ticket #930798 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 01:57:40'),
(112, 14, 66, 'Quote #457944 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 03:14:01'),
(113, 14, 68, 'The pickup attempt for your order #189152 has failed. We will try again soon.', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 03:30:21'),
(114, 14, 67, 'Your order #930798 pickup is being reattempted. Our logistics team will try again to pick up your items.', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 03:31:00'),
(115, 14, 68, 'The pickup attempt for your order #189152 has failed. We will try again soon.', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 03:31:29'),
(116, 14, 68, 'Your order #189152 has been rejected due to multiple failed pickup attempts.', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 03:39:45'),
(117, 14, 67, 'Your order #930798 has been successfully picked up. Your items are now being processed.', '', '', '', '', '', 'yes', 'yes', '', '', 'yes', 'yes', '', '', '2025-05-30 04:32:45'),
(118, 14, 76, 'admin just approved a quote price of ₱90.00 on ticket #411058', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:41:28'),
(119, 14, 64, 'Quote #692559 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:42:35'),
(120, 14, 76, 'Quote #411058 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:42:57'),
(121, 14, 76, 'Your order with ticket #411058 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:43:29'),
(122, 14, 76, 'Your order #411058 has been successfully picked up. Your items are now being processed.', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:43:54'),
(123, 14, 66, 'Your order with ticket #457944 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:47:05'),
(124, 14, 66, 'Your order #457944 has been successfully picked up. Your items are now being processed.', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 00:57:34'),
(125, 14, 76, 'Your order with ticket #411058 is ready to be shipped. It will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 03:29:23'),
(126, 14, 76, 'Your order with ticket #411058 is ready to be shipped. It will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', '', '', '', '', 'yes', '', '', '', 'yes', 'yes', '', '', '2025-06-06 03:29:27'),
(127, 14, 76, 'Your order with ticket #411058 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', 'yes', '', '', '2025-06-06 04:18:09'),
(128, 14, 65, 'Quote #629538 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', 'yes', '', 'approved', '2025-06-06 04:30:36'),
(129, 14, 65, 'Your order with ticket #629538 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', 'yes', '', '', '', '', '', '', '', '', '', 'yes', '', 'info', '2025-06-06 05:24:42'),
(130, 14, 75, 'admin just approved a quote price of ₱99.00 on ticket #162420', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', 'approved', '2025-06-06 05:25:07'),
(131, 14, 70, 'admin just approved a quote price of ₱98.00 on ticket #931242', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', 'approved', '2025-06-06 05:25:54'),
(132, 14, 75, 'Quote #162420 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', 'yes', '', 'approved', '2025-06-06 05:27:56'),
(133, 14, 75, 'Your order with ticket #162420 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', 'yes', '', '', '', '', '', '', '', '', '', 'yes', '', 'info', '2025-06-06 05:28:06'),
(134, 14, 75, 'Order #162420 has been picked up and will be processed. Please prepare the materials needed for this order.', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-06 05:28:15'),
(135, 14, 75, 'Order #162420 has been marked as ready to ship and will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', 'yes', '', '', '2025-06-06 05:29:22'),
(136, 14, 75, 'Order #162420 has been marked as ready to ship and will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', 'yes', '', '', '2025-06-06 05:29:26'),
(137, 3, NULL, 'Field manager has requested new stocks', '', '', '', '', 'yes', '', '', '', '', '', 'yes', '', '', '2025-06-07 00:24:20'),
(138, 3, NULL, 'Field manager has requested new stocks', '', '', '', '', 'yes', '', '', '', '', '', 'yes', '', '', '2025-06-07 00:35:26'),
(139, 3, NULL, '234 prints is now Preparing', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 00:55:27'),
(140, 3, NULL, 'Your request for 234 prints is now For Delivery', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 00:57:13'),
(141, 3, NULL, 'Your request for 234 prints is now Completed', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 01:26:32'),
(142, 3, NULL, 'Your request for 334 screen is now For Delivery', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 01:27:16'),
(143, 3, NULL, 'Your request for 334 screen is now Completed', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 01:27:42'),
(144, 3, NULL, 'Your request for 23 chemical is now Preparing', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 01:28:23'),
(145, 3, NULL, 'Your request for 23 chemical is now For Delivery', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 01:32:03'),
(146, 3, NULL, 'Your request for 23 chemical is now Completed', '', '', '', '', '', 'yes', '', '', '', '', 'yes', '', '', '2025-06-07 01:32:14'),
(147, 3, NULL, 'Your request for 22 shirts is now Preparing', '', '', '', '', '', 'yes', '', '', '', '', '', '', '', '2025-06-07 01:39:29'),
(148, 14, 75, 'Order with ticket #162420 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-06-07 02:21:35'),
(149, 14, 65, 'Order #629538 has been picked up and will be processed. Please prepare the materials needed for this order.', '', '', '', '', '', 'yes', '', '', '', '', '', '', '', '2025-06-07 02:58:54'),
(150, 14, 66, 'Order #457944 has been marked as ready to ship and will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-06-07 03:04:31'),
(151, 14, 66, 'Order #457944 has been marked as ready to ship and will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-06-07 03:04:35'),
(152, 14, 66, 'Order with ticket #457944 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-06-07 03:08:54'),
(153, 23, 77, 'New Quote, Direct to Film Print, 100', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-08-27 16:18:31'),
(154, 23, 77, 'Designer just added a quote price of ₱100 on ticket #900930', '', 'yes', 'yes', '', '', '', '', '', '', '', '', '', 'approved', '2025-08-27 16:19:45'),
(155, 23, 77, 'admin just approved a quote price of ₱100.00 on ticket #900930', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-08-27 16:20:41'),
(156, 23, 77, 'Quote #900930 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-08-27 16:21:45'),
(157, 23, 77, 'Your order with ticket #900930 is ready for pickup. Our logistics team will pick up the items at your address: akljshdliajhsdkjl hkajhsdkj  manila', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-08-27 16:24:00'),
(158, 23, 77, 'Order #900930 has been picked up and will be processed. Please prepare the materials needed for this order.', '', '', '', '', '', 'yes', '', '', '', '', '', '', '', '2025-08-27 16:28:20'),
(159, 23, 77, 'Order #900930 has been marked as ready to ship and will be delivered to: akljshdliajhsdkjl hkajhsdkj  manila', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-08-27 16:29:49'),
(160, 23, 77, 'Order #900930 has been marked as ready to ship and will be delivered to: akljshdliajhsdkjl hkajhsdkj  manila', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-08-27 16:29:53'),
(161, 23, 77, 'Order with ticket #900930 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-08-27 16:30:23'),
(162, 23, 78, 'New Quote, Direct to Film Print, 100', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-08-27 16:32:01'),
(163, 23, 78, 'Designer just added a quote price of ₱100 on ticket #670831', '', 'yes', 'yes', '', '', '', '', '', '', '', '', '', 'approved', '2025-08-27 16:32:39'),
(164, 23, 78, 'admin just approved a quote price of ₱100.00 on ticket #670831', '', '', '', '', '', 'yes', '', '', '', '', '', '', 'approved', '2025-08-27 16:32:53'),
(165, 23, 78, 'Quote #670831 has been agreed to the price', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-08-27 16:33:21'),
(166, 23, 78, 'Your order with ticket #670831 is ready for pickup. Our logistics team will pick up the items at your address: akljshdliajhsdkjl hkajhsdkj  manila', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-08-27 16:33:39'),
(167, 23, 78, 'Order #670831 has been picked up and will be processed. Please prepare the materials needed for this order.', '', '', '', '', '', 'yes', '', '', '', '', '', '', '', '2025-08-27 16:33:50'),
(168, 23, 78, 'Order #670831 has been marked as ready to ship and will be delivered to: akljshdliajhsdkjl hkajhsdkj  manila', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-08-27 16:34:14'),
(169, 23, 78, 'Order #670831 has been marked as ready to ship and will be delivered to: akljshdliajhsdkjl hkajhsdkj  manila', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-08-27 16:34:17'),
(170, 23, 78, 'Order with ticket #670831 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-08-27 16:34:40'),
(171, 3, NULL, 'Field manager has requested new stocks', '', '', '', '', 'yes', '', '', '', '', '', '', '', '', '2025-08-27 16:44:45'),
(172, 14, 64, 'Your order with ticket #692559 is ready for pickup. Our logistics team will pick up the items at your address: Dasmarinas Cavite, Sandionisio, blk 16', 'yes', '', '', '', '', '', '', '', '', '', '', '', 'info', '2025-09-02 15:05:02'),
(173, 14, 64, 'Order #692559 has been picked up and will be processed. Please prepare the materials needed for this order.', '', '', '', '', '', 'yes', '', '', '', '', '', '', '', '2025-09-02 15:05:18'),
(174, 14, 65, 'Order #629538 has been marked as ready to ship and will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-02 15:06:04'),
(175, 14, 65, 'Order #629538 has been marked as ready to ship and will be delivered to: Dasmarinas Cavite, Sandionisio, blk 16', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', '', '2025-09-02 15:06:08'),
(176, 14, 65, 'Order with ticket #629538 has been successfully delivered!', '', 'yes', 'yes', '', 'yes', '', '', '', '', '', '', '', 'approved', '2025-09-02 15:17:00'),
(177, 14, 79, 'New Quote, Screen Printing, 232', '', 'yes', 'yes', 'yes', '', '', '', '', '', '', '', '', '', '2025-09-02 15:36:34');

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
(5, 3, 2, 'chemical', 500, 'pending', '2025-08-27 16:44:45', 'no', NULL, 'no', NULL, '', NULL);

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
(14, 'capstoneproject0101@gmail.com', 'capstones', '09946726471', '$2y$10$ezjIhkPHNXQca9yQG..9KeYitPC7dPkzKfKsxsWsEg6CMGmbE6epq', '', 'asdasdasd asdasdasdasd', '68b48f385049d.png', '2025-06-05 15:53:57', 'b1720baa135d303842216e9aa9bb3ca56e4f990c70a6873c39df03d18501af26', '2025-10-01 02:06:26', NULL, NULL, 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
