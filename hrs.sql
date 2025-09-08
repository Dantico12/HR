-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2025 at 03:03 PM
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
-- Database: `hrs`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowance_types`
--

CREATE TABLE `allowance_types` (
  `allowance_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allowance_types`
--

INSERT INTO `allowance_types` (`allowance_type_id`, `type_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'House Allowance', 'Allowance for housing expenses', 1, '2025-08-29 09:21:33', '2025-08-29 09:21:33'),
(2, 'Commuter Allowance', 'Allowance for transportation costs', 1, '2025-08-29 09:21:33', '2025-08-29 09:21:33'),
(3, 'Dirty Allowance', 'Allowance for working in hazardous or difficult conditions', 1, '2025-08-29 09:21:33', '2025-08-29 09:21:33'),
(5, 'Leave Allowance', 'Allocated to employees on annual leave', 1, '2025-09-01 13:01:08', '2025-09-01 13:01:41'),
(6, 'Dirty Allowance', 'Allocated to employees whose designation involves a dirty environment', 1, '2025-09-01 13:02:27', '2025-09-01 13:02:27');

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_cycles`
--

CREATE TABLE `appraisal_cycles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appraisal_cycles`
--

INSERT INTO `appraisal_cycles` (`id`, `name`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Q4 2025/2026 Performance Review', '2026-04-01', '2026-06-30', 'active', '2025-08-11 11:29:14', '2025-08-11 13:55:02'),
(2, 'Q1 2025/2026 Performance Review', '2025-07-01', '2025-09-30', 'active', '2025-08-11 11:29:14', '2025-08-11 13:53:26'),
(3, 'Q2 2025/2026 Performance Review', '2025-10-01', '2025-12-31', 'active', '2025-08-11 11:29:14', '2025-08-11 13:53:36'),
(4, 'Annual Review 2025', '2025-01-01', '2025-12-31', 'active', '2025-08-11 11:29:14', '2025-08-11 11:29:14'),
(5, 'Q3 2025/2026 Performance Review', '2026-01-01', '2026-03-31', 'active', '2025-08-11 13:52:29', '2025-08-11 13:52:29'),
(6, 'Q1 2024/2025 Performance Review', '2024-07-01', '2024-09-30', 'active', '2024-08-01 07:00:00', '2025-09-04 06:54:13'),
(7, 'Q2 2024/2025 Performance Review', '2024-10-01', '2024-12-31', 'completed', '2024-11-01 07:00:00', '2025-08-15 07:47:04'),
(8, 'Mid-Year Review 2025', '2025-01-01', '2025-06-30', 'active', '2025-02-01 07:00:00', '2025-08-15 07:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_scores`
--

CREATE TABLE `appraisal_scores` (
  `id` int(11) NOT NULL,
  `employee_appraisal_id` int(11) NOT NULL,
  `performance_indicator_id` int(11) NOT NULL,
  `score` decimal(3,2) DEFAULT NULL,
  `appraiser_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appraisal_scores`
--

INSERT INTO `appraisal_scores` (`id`, `employee_appraisal_id`, `performance_indicator_id`, `score`, `appraiser_comment`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1.00, '', '2025-08-11 13:39:38', '2025-08-15 07:47:04'),
(2, 2, 2, 1.00, '', '2025-08-11 13:39:38', '2025-08-15 07:47:04'),
(3, 2, 3, 1.00, '', '2025-08-11 13:39:38', '2025-08-15 07:47:04'),
(4, 2, 4, 1.00, '', '2025-08-11 13:39:38', '2025-08-15 07:47:04'),
(5, 2, 5, 1.00, '', '2025-08-11 13:39:39', '2025-08-15 07:47:04'),
(6, 2, 6, 1.00, '', '2025-08-11 13:39:39', '2025-08-15 07:47:04'),
(7, 2, 7, 1.00, '', '2025-08-11 13:39:39', '2025-08-15 07:47:04'),
(8, 8, 1, 5.00, '', '2025-08-13 05:03:58', '2025-08-15 07:47:04'),
(9, 8, 2, 0.00, '', '2025-08-13 05:03:59', '2025-08-15 07:47:04'),
(10, 8, 3, 0.00, '', '2025-08-13 05:03:59', '2025-08-15 07:47:04'),
(11, 8, 4, 0.00, '', '2025-08-13 05:03:59', '2025-08-15 07:47:04'),
(12, 8, 5, 0.00, '', '2025-08-13 05:03:59', '2025-08-15 07:47:04'),
(13, 8, 6, 0.00, '', '2025-08-13 05:03:59', '2025-08-15 07:47:04'),
(14, 8, 7, 0.00, '', '2025-08-13 05:03:59', '2025-08-15 07:47:04'),
(15, 3, 1, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(16, 3, 2, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(18, 3, 3, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(19, 3, 4, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(20, 3, 5, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(21, 3, 6, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(22, 3, 7, 1.00, '', '2025-08-13 07:32:01', '2025-08-15 07:47:04'),
(135, 5, 1, 4.30, '', '2025-08-13 07:51:10', '2025-08-15 07:47:04'),
(136, 5, 2, 9.99, '', '2025-08-13 07:51:10', '2025-08-15 07:47:04'),
(138, 5, 3, 0.00, '', '2025-08-13 07:51:11', '2025-08-15 07:47:04'),
(139, 5, 4, 0.00, '', '2025-08-13 07:51:11', '2025-08-15 07:47:04'),
(140, 5, 5, 0.00, '', '2025-08-13 07:51:11', '2025-08-15 07:47:04'),
(141, 5, 6, 0.00, '', '2025-08-13 07:51:11', '2025-08-15 07:47:04'),
(142, 5, 7, 0.00, '', '2025-08-13 07:51:11', '2025-08-15 07:47:04'),
(199, 7, 1, 3.00, '', '2025-08-14 05:58:06', '2025-08-15 07:47:04'),
(200, 7, 2, 2.10, '', '2025-08-14 05:58:08', '2025-08-15 07:47:04'),
(201, 7, 3, 3.00, '', '2025-08-14 05:58:09', '2025-08-15 07:47:04'),
(202, 7, 4, 3.00, '', '2025-08-14 05:58:09', '2025-08-15 07:47:04'),
(203, 7, 5, 3.00, '', '2025-08-14 05:58:10', '2025-08-15 07:47:04'),
(204, 7, 6, 3.00, '', '2025-08-14 05:58:10', '2025-08-15 07:47:04'),
(205, 7, 7, 3.00, '', '2025-08-14 05:58:10', '2025-08-15 07:47:04'),
(311, 1, 1, 2.00, '', '2025-08-14 06:24:45', '2025-08-15 07:47:04'),
(312, 1, 2, 2.00, '', '2025-08-14 06:24:46', '2025-08-15 07:47:04'),
(313, 1, 3, 2.00, '', '2025-08-14 06:24:46', '2025-08-15 07:47:04'),
(314, 1, 4, 2.00, '', '2025-08-14 06:24:46', '2025-08-15 07:47:04'),
(315, 1, 5, 2.00, '', '2025-08-14 06:24:47', '2025-08-15 07:47:04'),
(316, 1, 6, 2.00, '', '2025-08-14 06:24:47', '2025-08-15 07:47:04'),
(317, 1, 7, 2.00, '', '2025-08-14 06:24:48', '2025-08-15 07:47:04'),
(381, 24, 1, 4.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(382, 24, 2, 3.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(383, 24, 3, 2.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(384, 24, 4, 3.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(385, 24, 5, 2.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(386, 24, 6, 2.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(387, 24, 7, 1.00, '', '2025-08-14 08:29:57', '2025-08-15 07:47:04'),
(451, 25, 1, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(452, 25, 2, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(453, 25, 3, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(454, 25, 4, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(455, 25, 5, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(456, 25, 6, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(457, 25, 7, 0.00, '', '2025-08-14 08:37:40', '2025-08-15 07:47:04'),
(458, 26, 1, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(459, 26, 2, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(460, 26, 3, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(461, 26, 4, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(462, 26, 5, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(463, 26, 6, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(464, 26, 7, 0.00, '', '2025-08-14 08:57:21', '2025-08-15 07:47:04'),
(465, 10, 1, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(466, 10, 2, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(467, 10, 3, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(468, 10, 4, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(469, 10, 5, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(470, 10, 6, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(471, 10, 7, 0.00, '', '2025-08-14 08:57:41', '2025-08-15 07:47:04'),
(472, 6, 1, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(473, 6, 2, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(474, 6, 3, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(475, 6, 4, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(476, 6, 5, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(477, 6, 6, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(478, 6, 7, 0.00, '', '2025-08-14 08:58:22', '2025-08-15 07:47:04'),
(486, 19, 1, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(487, 19, 2, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(488, 19, 3, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(489, 19, 4, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(490, 19, 5, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(491, 19, 6, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(492, 19, 7, 4.00, '', '2025-08-14 09:49:43', '2025-08-15 07:47:04'),
(528, 1, 9, 4.32, 'Good performance in Customer Service Excellence. Shows consistent effort and results.', '2025-08-11 12:08:07', '2025-08-15 07:47:04'),
(529, 3, 9, 3.79, 'Good performance in Customer Service Excellence. Shows consistent effort and results.', '2025-08-12 08:41:45', '2025-08-15 07:47:04'),
(530, 24, 9, 3.01, 'Good performance in Customer Service Excellence. Shows consistent effort and results.', '2025-08-14 08:29:44', '2025-08-15 07:47:04'),
(531, 1, 10, 4.68, 'Good performance in Technical Competency. Shows consistent effort and results.', '2025-08-11 12:08:07', '2025-08-15 07:47:04'),
(532, 3, 10, 3.38, 'Good performance in Technical Competency. Shows consistent effort and results.', '2025-08-12 08:41:45', '2025-08-15 07:47:04'),
(533, 24, 10, 3.83, 'Good performance in Technical Competency. Shows consistent effort and results.', '2025-08-14 08:29:44', '2025-08-15 07:47:04'),
(534, 1, 11, 4.03, 'Good performance in Leadership Potential. Shows consistent effort and results.', '2025-08-11 12:08:07', '2025-08-15 07:47:04'),
(535, 3, 11, 3.65, 'Good performance in Leadership Potential. Shows consistent effort and results.', '2025-08-12 08:41:45', '2025-08-15 07:47:04'),
(536, 24, 11, 3.16, 'Good performance in Leadership Potential. Shows consistent effort and results.', '2025-08-14 08:29:44', '2025-08-15 07:47:04'),
(537, 1, 12, 3.85, 'Good performance in Adaptability. Shows consistent effort and results.', '2025-08-11 12:08:07', '2025-08-15 07:47:04'),
(538, 3, 12, 4.77, 'Good performance in Adaptability. Shows consistent effort and results.', '2025-08-12 08:41:45', '2025-08-15 07:47:04'),
(539, 24, 12, 3.28, 'Good performance in Adaptability. Shows consistent effort and results.', '2025-08-14 08:29:44', '2025-08-15 07:47:04'),
(543, 33, 1, 5.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(544, 33, 2, 3.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(545, 33, 10, 3.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(546, 33, 3, 1.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(547, 33, 9, 1.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(548, 33, 4, 2.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(549, 33, 12, 1.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(550, 33, 5, 2.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(551, 33, 11, 2.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(552, 33, 6, 1.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(553, 33, 7, 2.00, '', '2025-08-15 12:00:51', '2025-08-15 12:01:07'),
(664, 4, 1, 3.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(665, 4, 2, 4.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(666, 4, 10, 4.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(667, 4, 3, 2.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(668, 4, 9, 3.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(669, 4, 4, 3.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(670, 4, 12, 3.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(671, 4, 5, 3.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(672, 4, 11, 3.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(673, 4, 6, 4.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(674, 4, 7, 2.00, '', '2025-08-15 12:33:23', '2025-08-15 12:33:44'),
(796, 28, 1, 3.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(797, 28, 2, 2.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(798, 28, 10, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(799, 28, 3, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(800, 28, 9, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(801, 28, 4, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(802, 28, 12, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(803, 28, 5, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(804, 28, 11, 4.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(805, 28, 6, 3.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(806, 28, 7, 3.00, '', '2025-08-15 12:34:05', '2025-08-15 12:34:26'),
(906, 37, 1, 3.00, '', '2025-08-15 12:34:56', '2025-08-15 12:35:10'),
(907, 37, 2, 2.00, '', '2025-08-15 12:34:56', '2025-08-15 12:35:10'),
(908, 37, 10, 3.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(909, 37, 3, 3.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(910, 37, 9, 3.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(911, 37, 4, 2.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(912, 37, 12, 2.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(913, 37, 5, 2.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(914, 37, 11, 2.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(915, 37, 6, 2.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(916, 37, 7, 2.00, '', '2025-08-15 12:34:57', '2025-08-15 12:35:10'),
(1027, 18, 1, 2.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:51'),
(1028, 18, 2, 2.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:51'),
(1029, 18, 10, 3.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:51'),
(1030, 18, 3, 3.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:51'),
(1031, 18, 9, 3.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:51'),
(1032, 18, 4, 3.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:52'),
(1033, 18, 12, 3.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:52'),
(1034, 18, 5, 2.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:52'),
(1035, 18, 11, 3.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:52'),
(1036, 18, 6, 4.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:52'),
(1037, 18, 7, 4.00, '', '2025-08-15 12:35:28', '2025-08-15 12:35:52'),
(1103, 38, 1, 1.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1104, 38, 2, 1.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1105, 38, 10, 1.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1106, 38, 3, 1.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1107, 38, 9, 1.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1108, 38, 4, 1.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1109, 38, 12, 2.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1110, 38, 5, 2.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1111, 38, 11, 2.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1112, 38, 6, 2.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1113, 38, 7, 2.00, '', '2025-08-15 12:36:12', '2025-08-15 12:36:27'),
(1224, 20, 1, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1225, 20, 2, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1226, 20, 10, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1227, 20, 3, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1228, 20, 9, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1229, 20, 4, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1230, 20, 12, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1231, 20, 5, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1232, 20, 11, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1233, 20, 6, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1234, 20, 7, 0.00, '', '2025-08-18 07:38:37', '2025-08-18 07:38:37'),
(1235, 39, 19, 2.00, 'good', '2025-08-22 12:11:00', '2025-08-22 12:12:14'),
(1236, 39, 20, 5.00, 'better', '2025-08-22 12:11:00', '2025-08-22 12:12:14'),
(1237, 39, 31, 4.00, 'extaordinary', '2025-08-22 12:11:00', '2025-08-22 12:12:14'),
(1238, 39, 30, 3.00, 'excellent', '2025-08-22 12:11:00', '2025-08-22 12:12:14'),
(1239, 39, 32, 2.00, 'good', '2025-08-22 12:11:00', '2025-08-22 12:12:14'),
(1240, 39, 33, 3.00, 'excellent', '2025-08-22 12:11:00', '2025-08-22 12:12:15'),
(1241, 39, 34, 2.00, 'better', '2025-08-22 12:11:00', '2025-08-22 12:12:15'),
(1333, 46, 19, 1.00, 'good', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1334, 46, 20, 2.00, 'excellent', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1335, 46, 31, 3.00, 'good', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1336, 46, 30, 2.00, 'good', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1337, 46, 32, 2.00, 'beter', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1338, 46, 33, 2.00, 'good improvement', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1339, 46, 34, 2.00, 'good improvement', '2025-08-22 12:12:29', '2025-08-22 12:13:16'),
(1431, 47, 19, 1.00, 'poor performance on team  performnce', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1432, 47, 20, 2.00, 'good', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1433, 47, 31, 2.00, 'better', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1434, 47, 30, 3.00, 'better', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1435, 47, 32, 2.00, 'good', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1436, 47, 33, 3.00, 'good', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1437, 47, 34, 2.00, 'poor', '2025-08-22 12:13:42', '2025-08-22 12:15:26'),
(1536, 31, 13, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1537, 31, 17, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1538, 31, 25, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1539, 31, 18, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1540, 31, 27, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1541, 31, 26, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1542, 31, 28, 5.00, '', '2025-08-24 12:59:26', '2025-08-24 12:59:33'),
(1543, 31, 29, 5.00, '', '2025-08-24 12:59:27', '2025-08-24 12:59:33'),
(1600, 56, 32, 4.00, '', '2025-08-25 11:21:08', '2025-08-25 11:21:30'),
(1601, 56, 33, 3.00, '', '2025-08-25 11:21:08', '2025-08-25 11:21:31'),
(1602, 56, 34, 2.00, '', '2025-08-25 11:21:08', '2025-08-25 11:21:31'),
(1603, 56, 20, 3.00, '', '2025-08-25 11:21:09', '2025-08-25 11:21:31'),
(1604, 56, 31, 2.00, '', '2025-08-25 11:21:09', '2025-08-25 11:21:31'),
(1605, 56, 30, 3.00, '', '2025-08-25 11:21:09', '2025-08-25 11:21:31'),
(1606, 56, 19, 2.00, '', '2025-08-25 11:21:09', '2025-08-25 11:21:31'),
(1649, 57, 32, 1.00, '', '2025-08-25 11:22:06', '2025-08-25 11:22:19'),
(1650, 57, 33, 2.00, '', '2025-08-25 11:22:06', '2025-08-25 11:22:19'),
(1651, 57, 34, 3.00, '', '2025-08-25 11:22:06', '2025-08-25 11:22:19'),
(1652, 57, 20, 3.00, '', '2025-08-25 11:22:06', '2025-08-25 11:22:19'),
(1653, 57, 31, 3.00, '', '2025-08-25 11:22:07', '2025-08-25 11:22:19'),
(1654, 57, 30, 4.00, '', '2025-08-25 11:22:07', '2025-08-25 11:22:19'),
(1655, 57, 19, 5.00, '', '2025-08-25 11:22:07', '2025-08-25 11:22:19'),
(1705, 58, 18, 2.00, '', '2025-09-03 08:11:07', '2025-09-03 08:11:23'),
(1706, 58, 27, 2.00, '', '2025-09-03 08:11:08', '2025-09-03 08:11:23'),
(1707, 58, 26, 2.00, '', '2025-09-03 08:11:08', '2025-09-03 08:11:23'),
(1708, 58, 17, 2.00, '', '2025-09-03 08:11:08', '2025-09-03 08:11:23'),
(1709, 58, 28, 2.00, '', '2025-09-03 08:11:08', '2025-09-03 08:11:23'),
(1710, 58, 29, 2.00, '', '2025-09-03 08:11:08', '2025-09-03 08:11:23'),
(1711, 58, 25, 2.00, '', '2025-09-03 08:11:09', '2025-09-03 08:11:24'),
(1712, 58, 13, 2.00, '', '2025-09-03 08:11:09', '2025-09-03 08:11:24'),
(1777, 49, 18, 0.00, '', '2025-09-03 13:06:08', '2025-09-03 13:06:08'),
(1778, 49, 27, 0.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1779, 49, 26, 0.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1780, 49, 17, 0.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1781, 49, 28, 0.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1782, 49, 29, 1.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1783, 49, 25, 0.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1784, 49, 13, 0.00, '', '2025-09-03 13:06:09', '2025-09-03 13:06:09'),
(1785, 59, 18, 2.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:06'),
(1786, 59, 27, 3.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:06'),
(1787, 59, 26, 3.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:07'),
(1788, 59, 17, 4.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:07'),
(1789, 59, 28, 5.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:07'),
(1790, 59, 29, 4.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:07'),
(1791, 59, 25, 3.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:07'),
(1792, 59, 13, 3.00, '', '2025-09-04 07:05:10', '2025-09-04 07:06:07');

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_summary_cache`
--

CREATE TABLE `appraisal_summary_cache` (
  `id` int(11) NOT NULL,
  `appraisal_cycle_id` int(11) NOT NULL,
  `quarter` varchar(10) NOT NULL,
  `total_completed` int(11) DEFAULT 0,
  `average_score` decimal(5,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appraisal_summary_cache`
--

INSERT INTO `appraisal_summary_cache` (`id`, `appraisal_cycle_id`, `quarter`, `total_completed`, `average_score`, `last_updated`) VALUES
(1, 1, 'Q2', 1, 41.07, '2025-08-15 07:47:04'),
(2, 3, 'Q4', 1, 56.15, '2025-08-15 07:47:04'),
(3, 5, 'Q1', 1, 55.05, '2025-08-15 07:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `bank_id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`bank_id`, `bank_name`) VALUES
(1, 'Equity'),
(2, 'Cooperative Bank'),
(3, 'KCB bank'),
(4, 'FAMILY BANK'),
(5, 'NCBA BANK');

-- --------------------------------------------------------

--
-- Stand-in structure for view `completed_appraisals_view`
-- (See below for the actual view)
--
CREATE TABLE `completed_appraisals_view` (
`id` int(11)
,`employee_id` int(11)
,`appraiser_id` int(11)
,`appraisal_cycle_id` int(11)
,`employee_comment` text
,`employee_comment_date` timestamp
,`submitted_at` timestamp
,`status` enum('draft','awaiting_employee','submitted','completed','awaiting_submission')
,`created_at` timestamp
,`updated_at` timestamp
,`cycle_name` varchar(100)
,`start_date` date
,`end_date` date
,`first_name` varchar(100)
,`last_name` varchar(100)
,`emp_id` varchar(50)
,`designation` varchar(50)
,`department_name` varchar(100)
,`section_name` varchar(100)
,`appraiser_first_name` varchar(100)
,`appraiser_last_name` varchar(100)
,`quarter` varchar(7)
,`average_score_percentage` decimal(14,10)
);

-- --------------------------------------------------------

--
-- Table structure for table `deduction_formulas`
--

CREATE TABLE `deduction_formulas` (
  `formula_id` int(11) NOT NULL,
  `deduction_type_id` int(11) NOT NULL,
  `formula_name` varchar(255) NOT NULL,
  `formula_expression` text NOT NULL,
  `applicable_from` date NOT NULL,
  `applicable_to` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deduction_types`
--

CREATE TABLE `deduction_types` (
  `deduction_type_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `calculation_method` enum('percentage','fixed','formula') DEFAULT 'fixed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deduction_types`
--

INSERT INTO `deduction_types` (`deduction_type_id`, `type_name`, `description`, `is_active`, `calculation_method`, `created_at`, `updated_at`) VALUES
(1, 'PAYE', 'Pay As You Earn Tax', 1, 'formula', '2025-09-01 17:43:09', '2025-09-01 17:43:09'),
(2, 'NSSF', 'National Social Security Fund', 1, 'percentage', '2025-09-01 17:43:09', '2025-09-01 17:43:09'),
(3, 'Equity Bank Loan', 'Monthly loan repayment', 1, 'fixed', '2025-09-01 17:43:09', '2025-09-01 17:43:09'),
(4, 'Health Insurance', 'Monthly health insurance premium', 1, 'fixed', '2025-09-01 17:43:09', '2025-09-01 17:43:09'),
(5, 'Pension', 'Retirement contribution', 1, 'percentage', '2025-09-01 17:43:09', '2025-09-01 17:43:09');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Manages employee relations and company policies', '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(2, 'Commercial', 'Handles sales, marketing, and customer relations', '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(3, 'Technical', 'Manages technical operations and development', '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(4, 'Corporate Affairs', 'Handles legal, compliance, and corporate governance', '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(5, 'Fort-Aqua', 'Water management and supply operations', '2025-07-19 06:04:13', '2025-07-19 06:04:13');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `gender` varchar(10) NOT NULL,
  `national_id` int(10) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `designation` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `address` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `employment_type` varchar(20) NOT NULL,
  `employee_type` varchar(20) NOT NULL,
  `profile_image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `employee_status` enum('active','inactive','resigned','fired','retired') NOT NULL DEFAULT 'active',
  `job_group` varchar(10) DEFAULT NULL,
  `scale_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `first_name`, `last_name`, `gender`, `national_id`, `email`, `designation`, `phone`, `date_of_birth`, `address`, `department_id`, `section_id`, `position`, `salary`, `hire_date`, `employment_type`, `employee_type`, `profile_image_url`, `created_at`, `updated_at`, `employee_status`, `job_group`, `scale_id`) VALUES
(5, 'EMP009', 'Josephine', 'Kangara', '', 3987654, 'josephine@gmail.com', '0', '0768525478', '1971-12-12', 'Kiambu', 2, 4, NULL, NULL, '2025-07-03', 'permanent', 'section_head', NULL, '2025-07-22 10:20:00', '2025-08-29 12:09:53', 'active', 'section_he', '5'),
(104, 'EMP001', 'duncan', 'karenju', '', 40135584, 'karenjuduncan750@gmail.com', '0', '0112554479', '2008-03-04', 'Kiambu', 1, 2, NULL, NULL, '2025-07-18', 'contract', 'section_head', NULL, '2025-07-22 06:20:38', '2025-08-29 12:09:53', 'active', 'section_he', '5'),
(111, '003', 'joseph', 'kamau', '', 105021, 'joseph@gmail.com', 'Employee', 'undefined', '0000-00-00', '1050', 3, 7, NULL, NULL, '2025-07-02', 'permanent', 'manager', NULL, '2025-07-21 10:48:26', '2025-08-29 12:09:53', 'active', 'manager', '8'),
(112, '004', 'jack', 'kamau', '', 1050, 'jack@gmail.com', '0', 'undefined', '2025-07-02', '1050', 2, 5, NULL, NULL, '2025-07-01', 'permanent', 'officer', NULL, '2025-07-21 10:49:38', '2025-08-29 12:09:53', 'active', 'officer', '4'),
(113, '001', 'john', 'kamau', '', 1050, 'john@gmail.com', 'Employee', '0707699054', '0000-00-00', '1050', NULL, NULL, NULL, NULL, '2025-07-01', 'permanent', 'managing_director', NULL, '2025-07-21 10:39:55', '2025-08-29 12:09:53', 'active', 'managing_d', '10'),
(114, '002', 'mike', 'kamau', '', 1245, 'mike@gmail.com', 'Employee', 'undefined', '0000-00-00', '1050', 2, NULL, NULL, NULL, '2025-07-02', 'permanent', 'dept_head', NULL, '2025-07-21 10:43:36', '2025-08-29 12:09:53', 'active', 'dept_head', '7'),
(118, 'EMP008', 'Mwangi', 'Kabii', 'male', 3987654, 'mwangikabii@gmail.com', '0', '0790765431', '1999-03-11', 'Kiambu', 2, 4, NULL, NULL, '2025-07-04', 'permanent', 'officer', NULL, '2025-07-22 07:23:07', '2025-08-29 12:09:53', 'active', 'officer', '4'),
(121, 'EMP10', 'Hezron', 'Njoroge', '', 3987654, 'hezronnjoro@gmail.com', '0', '0786542982', '1987-03-11', 'Mukurweini', 2, NULL, NULL, NULL, '2025-01-01', 'permanent', 'dept_head', 'uploads/profile_images/68ba953e7e13e.jpg', '2025-07-22 10:32:58', '2025-09-05 07:46:06', 'active', 'dept_head', '7'),
(122, '150', 'will', 'smith', '', 123546, 'will@gmail.com', '0', '0786542982', '2025-07-01', 'Mukurweini', 2, 5, NULL, NULL, '2025-07-15', 'permanent', 'officer', 'uploads/profile_images/68baa43e17311.jpg', '2025-07-23 16:16:36', '2025-09-05 08:50:06', 'active', 'officer', '4'),
(134, '161', 'hash', 'pappy', '', 126354, 'hash@gmail.com', '0', '0707070708', '2025-07-01', '1050', 2, 5, NULL, NULL, '2025-07-21', 'permanent', 'section_head', NULL, '2025-07-23 16:45:44', '2025-08-29 12:09:53', 'active', 'section_he', '5'),
(135, 'EMP020', 'John', 'Doe', 'female', 123987, 'lucy@gmail.com', '0', '0707070708', '2025-07-01', 'Kiambu', NULL, 1, NULL, NULL, '2025-07-01', 'permanent', 'hr_manager', 'uploads/profile_images/68baa44bc4bb3.jpg', '2025-07-24 18:24:31', '2025-09-05 08:50:19', 'active', 'hr_manager', '8'),
(136, 'EMP015', 'Mwangi', 'Mwangi', '', 33679875, 'martinmwangi14@gmail.com', '0', '073354566645', '1967-03-12', 'Kihoya', 2, 4, NULL, NULL, '2023-08-25', 'permanent', 'officer', NULL, '2025-07-25 05:03:20', '2025-08-29 12:09:53', 'active', 'officer', '4'),
(143, 'EMP019', 'Dancan', 'karenju', '', 33890765, 'karenjuduncan70@gmail.com', 'Innovation', '0112554479', '1987-09-08', 'Kiambu', NULL, NULL, NULL, NULL, '2024-10-10', 'permanent', 'managing_director', NULL, '2025-07-29 10:09:46', '2025-08-29 12:09:53', 'active', 'managing_d', '10'),
(145, 'EMP021', 'Peter', 'Maina', 'male', 30198987, 'petermaina19@gmail.com', 'PME', '0707454717', '1999-10-31', 'Muranga town', 2, 3, NULL, NULL, '2025-03-13', 'contract', 'officer', NULL, '2025-08-26 07:50:42', '2025-08-29 12:09:53', 'active', 'officer', '4'),
(146, 'EMP022', 'Constatine', 'Andrew', 'male', 39087651, 'constatine12@gmail.com', 'software enginer', '0790765431', '2000-07-01', 'Kiambu', 2, 5, NULL, NULL, '2024-05-06', 'contract', 'officer', NULL, '2025-08-26 07:55:26', '2025-08-29 12:09:53', 'active', 'officer', '4'),
(147, 'EMP023', 'Charles', 'Gatambi', 'male', 45871324, 'charlo17@gmail.com', 'sales', '0768525478', '1997-10-31', 'Kiambu', 4, 10, NULL, NULL, '2024-05-28', 'permanent', 'officer', NULL, '2025-08-26 08:00:13', '2025-08-29 12:10:13', 'active', 'officer', '4'),
(148, 'EMP024', 'Caleb', 'Joes', 'male', 45871324, 'joe7@gmail.com', 'sales', '0768525478', '1997-10-31', 'Kiambu', 2, 4, NULL, NULL, '2024-05-28', 'contract', 'officer', 'uploads/profile_images/68ba992b9e463.jpg', '2025-08-26 08:03:57', '2025-09-05 08:02:51', 'active', 'officer', '4'),
(149, 'EMP025', 'Stanley', 'Mwaura', 'male', 39087651, 'stanley@gmail.com', 'MD', '0707699054', '1976-10-20', 'Kirigiti', NULL, NULL, NULL, NULL, '2024-05-01', 'permanent', 'managing_director', 'uploads/profile_images/68ba947fcf8a5.jpg', '2025-09-04 08:30:52', '2025-09-05 07:42:55', 'active', '10', NULL),
(150, 'EMP026', 'Judy', 'Wawira', 'female', 12398790, 'judy@gmail.com', 'MD', '0707699054', '2025-09-06', 'Thika', NULL, NULL, NULL, NULL, '2023-11-02', 'permanent', 'managing_director', 'uploads/profile_images/68ba966658fc0.jpg', '2025-09-04 08:44:55', '2025-09-05 07:51:02', 'active', '10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_allowances`
--

CREATE TABLE `employee_allowances` (
  `allowance_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `allowance_type_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_allowances`
--

INSERT INTO `employee_allowances` (`allowance_id`, `period_id`, `emp_id`, `allowance_type_id`, `amount`, `effective_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 1, 148, 1, 13552.00, '2025-09-01', '2025-09-02', 'active', 135, '2025-09-01 12:32:52', '2025-09-01 12:32:52'),
(9, 1, 147, 1, 13552.00, '2025-09-02', '2025-09-03', 'active', 135, '2025-09-01 12:42:34', '2025-09-01 12:42:34'),
(10, 1, 121, 1, 27104.00, '2025-09-02', '2025-09-02', 'active', 135, '2025-09-01 12:50:48', '2025-09-01 12:50:48'),
(11, 1, 5, 1, 13552.00, '2025-09-02', '2025-09-02', 'active', 135, '2025-09-01 12:57:39', '2025-09-01 12:57:39'),
(12, 1, 148, 3, 0.00, '2025-09-01', '2025-09-02', 'active', 135, '2025-09-01 17:06:23', '2025-09-01 17:06:23');

-- --------------------------------------------------------

--
-- Table structure for table `employee_appraisals`
--

CREATE TABLE `employee_appraisals` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `appraiser_id` int(11) NOT NULL,
  `appraisal_cycle_id` int(11) NOT NULL,
  `employee_comment` text DEFAULT NULL,
  `employee_comment_date` timestamp NULL DEFAULT NULL,
  `supervisors_comment` text NOT NULL,
  `supervisors_comment_date` datetime NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','awaiting_employee','submitted','completed','awaiting_submission') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_appraisals`
--

INSERT INTO `employee_appraisals` (`id`, `employee_id`, `appraiser_id`, `appraisal_cycle_id`, `employee_comment`, `employee_comment_date`, `supervisors_comment`, `supervisors_comment_date`, `submitted_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 118, 5, 3, 'I am satisfied with this appraisal. The feedback provided is constructive and will help me improve my performance in the coming period. I appreciate the recognition of my efforts in teamwork and quality of work.', '2025-08-14 06:26:15', '', '2025-08-22 15:54:21', '2025-08-18 12:08:07', 'submitted', '2025-08-11 12:08:07', '2025-08-15 07:47:04'),
(2, 136, 5, 3, 'NO THANK YOU', '2025-08-14 08:32:40', '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-11 12:54:09', '2025-08-14 09:37:52'),
(3, 118, 5, 1, 'Thank you for the comprehensive evaluation. I agree with most of the assessments and will work on the areas identified for improvement, particularly in initiative and innovation. The appraisal process was fair and transparent.', '2025-08-13 07:33:08', '', '2025-08-22 15:54:21', '2025-08-13 07:37:01', 'submitted', '2025-08-12 08:41:45', '2025-08-15 07:47:04'),
(4, 136, 5, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-12 16:59:40', '2025-08-15 12:33:44'),
(5, 143, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-12 17:57:25', '2025-08-13 08:14:53'),
(6, 104, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-12 17:57:37', '2025-08-14 08:58:22'),
(7, 134, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-12 17:57:44', '2025-08-14 08:19:30'),
(8, 121, 135, 1, 'Good job', '2025-08-15 11:31:38', '', '2025-08-22 15:54:21', NULL, 'awaiting_submission', '2025-08-12 17:57:53', '2025-08-22 12:46:13'),
(10, 112, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-12 18:14:29', '2025-08-14 08:57:41'),
(11, 113, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-12 18:14:30', '2025-08-12 18:14:30'),
(12, 111, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-12 18:29:42', '2025-08-12 18:29:42'),
(13, 5, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-12 18:29:43', '2025-08-12 18:29:43'),
(14, 114, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-12 18:29:43', '2025-08-12 18:29:43'),
(17, 122, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-12 18:36:02', '2025-08-12 18:36:02'),
(18, 118, 5, 2, 'please my comment is wrong', '2025-08-15 12:37:39', 'done', '2025-08-24 16:27:23', '2025-08-24 13:27:23', 'submitted', '2025-08-13 07:40:36', '2025-08-24 13:27:23'),
(19, 136, 5, 2, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-13 07:40:36', '2025-08-14 09:49:53'),
(20, 104, 135, 5, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-13 18:25:43', '2025-08-18 07:38:37'),
(21, 104, 135, 3, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-13 18:25:50', '2025-08-13 18:25:50'),
(22, 104, 135, 2, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-13 18:25:52', '2025-08-13 18:25:52'),
(23, 118, 135, 5, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-13 18:26:09', '2025-08-13 18:26:09'),
(24, 136, 5, 5, 'NOOOOO', '2025-08-14 08:31:37', '', '2025-08-22 15:54:21', '2025-08-14 10:02:30', 'submitted', '2025-08-14 08:29:44', '2025-08-14 10:02:30'),
(25, 121, 135, 5, 'good job', '2025-08-15 11:31:49', '', '2025-08-22 15:54:21', NULL, 'awaiting_submission', '2025-08-14 08:37:34', '2025-08-22 12:45:58'),
(26, 134, 135, 5, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-14 08:42:52', '2025-08-14 08:57:21'),
(27, 135, 135, 1, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-14 08:58:09', '2025-08-14 08:58:09'),
(28, 136, 5, 4, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-14 09:37:09', '2025-08-15 12:34:26'),
(30, 136, 135, 8, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-15 10:03:22', '2025-08-15 10:03:22'),
(31, 112, 121, 5, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'awaiting_employee', '2025-08-15 11:32:43', '2025-08-24 12:59:33'),
(32, 112, 121, 3, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-15 11:32:58', '2025-08-15 11:32:58'),
(33, 5, 121, 3, 'halllo,', '2025-08-15 12:52:39', '', '2025-08-22 15:54:21', '2025-08-15 12:54:08', 'submitted', '2025-08-15 12:00:30', '2025-08-15 12:54:08'),
(35, 121, 135, 3, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-15 12:30:31', '2025-08-15 12:30:31'),
(36, 121, 135, 2, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-15 12:31:45', '2025-08-15 12:31:45'),
(37, 118, 5, 4, 'halooo', '2025-08-15 12:38:22', '', '2025-08-22 15:54:21', NULL, 'awaiting_submission', '2025-08-15 12:34:48', '2025-08-22 12:45:43'),
(38, 118, 5, 8, 'halooo', '2025-08-15 12:38:08', 'donee', '2025-08-24 16:29:03', '2025-08-24 13:29:03', 'submitted', '2025-08-15 12:36:05', '2025-08-24 13:29:03'),
(39, 5, 121, 5, 'perfect', '2025-08-22 12:17:02', 'PERFECTO', '2025-08-24 16:37:27', '2025-08-24 13:37:27', 'submitted', '2025-08-15 12:53:53', '2025-08-24 13:37:27'),
(40, 143, 135, 3, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-18 07:19:41', '2025-08-18 07:19:41'),
(41, 143, 135, 5, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-18 20:21:17', '2025-08-18 20:21:17'),
(42, 121, 135, 4, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-21 12:38:15', '2025-08-21 12:38:15'),
(43, 121, 135, 8, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-21 12:38:22', '2025-08-21 12:38:22'),
(44, 113, 135, 3, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-22 12:08:38', '2025-08-22 12:08:38'),
(45, 5, 135, 4, NULL, NULL, '', '2025-08-22 15:54:21', NULL, 'draft', '2025-08-22 12:09:22', '2025-08-22 12:09:22'),
(46, 5, 121, 8, 'yeah', '2025-08-22 12:17:36', 'Good performance', '2025-08-25 15:10:27', '2025-08-25 12:10:27', 'submitted', '2025-08-22 12:12:22', '2025-08-25 12:10:27'),
(47, 5, 121, 2, 'am contented', '2025-08-22 12:17:23', 'ok', '2025-08-25 09:38:29', '2025-08-25 06:38:30', 'submitted', '2025-08-22 12:13:31', '2025-08-25 06:38:30'),
(48, 112, 121, 8, NULL, NULL, '', '2025-08-22 16:18:43', NULL, 'draft', '2025-08-22 13:18:43', '2025-08-22 13:18:43'),
(49, 112, 121, 2, NULL, NULL, '', '2025-08-24 15:59:53', NULL, 'awaiting_employee', '2025-08-24 12:59:53', '2025-09-03 13:06:09'),
(50, 111, 135, 8, NULL, NULL, '', '2025-08-25 12:41:14', NULL, 'draft', '2025-08-25 09:41:14', '2025-08-25 09:41:14'),
(51, 111, 135, 4, NULL, NULL, '', '2025-08-25 12:41:18', NULL, 'draft', '2025-08-25 09:41:18', '2025-08-25 09:41:18'),
(52, 111, 135, 5, NULL, NULL, '', '2025-08-25 12:41:24', NULL, 'draft', '2025-08-25 09:41:24', '2025-08-25 09:41:24'),
(53, 111, 135, 3, NULL, NULL, '', '2025-08-25 12:41:27', NULL, 'draft', '2025-08-25 09:41:27', '2025-08-25 09:41:27'),
(54, 111, 135, 2, NULL, NULL, '', '2025-08-25 12:41:31', NULL, 'draft', '2025-08-25 09:41:31', '2025-08-25 09:41:31'),
(55, 114, 121, 5, NULL, NULL, '', '2025-08-25 13:52:00', NULL, 'draft', '2025-08-25 10:52:00', '2025-08-25 10:52:00'),
(56, 134, 121, 2, NULL, NULL, '', '2025-08-25 14:20:50', NULL, 'awaiting_employee', '2025-08-25 11:20:50', '2025-08-25 11:21:31'),
(57, 134, 121, 8, NULL, NULL, '', '2025-08-25 14:21:54', NULL, 'awaiting_employee', '2025-08-25 11:21:54', '2025-08-25 11:22:19'),
(58, 148, 135, 1, 'SATISFIED', '2025-09-03 08:12:34', 'thank you for your comment', '2025-09-04 09:53:42', '2025-09-04 06:53:42', 'submitted', '2025-09-03 08:10:55', '2025-09-04 06:53:42'),
(59, 146, 121, 1, NULL, NULL, '', '2025-09-03 16:05:53', NULL, 'awaiting_employee', '2025-09-03 13:05:53', '2025-09-04 07:06:07'),
(60, 148, 135, 5, NULL, NULL, '', '2025-09-04 09:53:42', NULL, 'draft', '2025-09-04 06:53:42', '2025-09-04 06:53:42'),
(61, 148, 135, 3, NULL, NULL, '', '2025-09-04 09:53:50', NULL, 'draft', '2025-09-04 06:53:50', '2025-09-04 06:53:50'),
(62, 148, 135, 4, NULL, NULL, '', '2025-09-04 09:53:56', NULL, 'draft', '2025-09-04 06:53:56', '2025-09-04 06:53:56'),
(63, 146, 121, 4, NULL, NULL, '', '2025-09-04 10:06:15', NULL, 'draft', '2025-09-04 07:06:15', '2025-09-04 07:06:15'),
(64, 146, 135, 3, NULL, NULL, '', '2025-09-05 09:43:22', NULL, 'draft', '2025-09-05 06:43:22', '2025-09-05 06:43:22'),
(65, 121, 135, 6, NULL, NULL, '', '2025-09-05 09:43:53', NULL, 'draft', '2025-09-05 06:43:53', '2025-09-05 06:43:53'),
(66, 146, 135, 5, NULL, NULL, '', '2025-09-05 09:54:10', NULL, 'draft', '2025-09-05 06:54:10', '2025-09-05 06:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

CREATE TABLE `employee_deductions` (
  `deduction_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `deduction_type_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_documents`
--

INSERT INTO `employee_documents` (`id`, `employee_id`, `document_name`, `file_name`, `uploaded_at`) VALUES
(1, 149, 'CURRICULUM VITAE', '68ba8e3b1d85e.pdf', '2025-09-05 07:16:11');

-- --------------------------------------------------------

--
-- Table structure for table `employee_leave_balances`
--

CREATE TABLE `employee_leave_balances` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `financial_year_id` int(11) NOT NULL,
  `allocated_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `used_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `remaining_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_days` int(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_leave_balances`
--

INSERT INTO `employee_leave_balances` (`id`, `employee_id`, `leave_type_id`, `financial_year_id`, `allocated_days`, `used_days`, `remaining_days`, `total_days`, `created_at`, `updated_at`) VALUES
(562, '5', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:03', '2029-08-06 07:32:03'),
(563, '5', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(564, '5', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(565, '5', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(566, '104', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(567, '104', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(568, '104', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(569, '111', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(570, '111', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(571, '111', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(572, '111', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(573, '112', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(574, '112', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(575, '112', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(576, '112', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(577, '113', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(578, '113', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(579, '113', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(580, '113', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(581, '114', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(582, '114', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(583, '114', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(584, '114', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(585, '118', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(586, '118', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(587, '118', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(588, '118', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(589, '121', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(590, '121', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(591, '121', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(592, '121', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(593, '122', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(594, '122', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(595, '122', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(596, '122', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(597, '134', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(598, '134', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(599, '134', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(600, '134', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(601, '135', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(602, '135', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(603, '135', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(604, '135', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(605, '136', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(606, '136', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(607, '136', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(608, '136', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(609, '143', 1, 21, 30.00, 0.00, 30.00, 30, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(610, '143', 2, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(611, '143', 3, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(612, '143', 4, 21, 10.00, 0.00, 10.00, 10, '2029-08-06 07:32:04', '2029-08-06 07:32:04'),
(613, '5', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(614, '5', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(615, '5', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(616, '5', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(617, '104', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(618, '104', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(619, '104', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:53', '2030-08-06 07:37:53'),
(620, '111', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(621, '111', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(622, '111', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(623, '111', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(624, '112', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(625, '112', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(626, '112', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(627, '112', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(628, '113', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(629, '113', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(630, '113', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(631, '113', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(632, '114', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(633, '114', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(634, '114', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(635, '114', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(636, '118', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(637, '118', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(638, '118', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(639, '118', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(640, '121', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(641, '121', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(642, '121', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(643, '121', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(644, '122', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(645, '122', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(646, '122', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(647, '122', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(648, '134', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(649, '134', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(650, '134', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(651, '134', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(652, '135', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(653, '135', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(654, '135', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(655, '135', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(656, '136', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(657, '136', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(658, '136', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(659, '136', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(660, '143', 1, 22, 30.00, 0.00, 60.00, 60, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(661, '143', 2, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(662, '143', 3, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(663, '143', 4, 22, 10.00, 0.00, 10.00, 10, '2030-08-06 07:37:54', '2030-08-06 07:37:54'),
(664, '5', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(665, '5', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(666, '5', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(667, '5', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(668, '104', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(669, '104', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(670, '104', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(671, '111', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(672, '111', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(673, '111', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(674, '111', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(675, '112', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(676, '112', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(677, '112', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:48', '2032-08-06 09:50:48'),
(678, '112', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:50', '2032-08-06 09:50:50'),
(679, '113', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(680, '113', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(681, '113', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(682, '113', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(683, '114', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(684, '114', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(685, '114', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(686, '114', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(687, '118', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(688, '118', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(689, '118', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(690, '118', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(691, '121', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(692, '121', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(693, '121', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(694, '121', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(695, '122', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(696, '122', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(697, '122', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(698, '122', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(699, '134', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(700, '134', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(701, '134', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(702, '134', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(703, '135', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(704, '135', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(705, '135', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(706, '135', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(707, '136', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(708, '136', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(709, '136', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(710, '136', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(711, '143', 1, 23, 30.00, 0.00, 90.00, 90, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(712, '143', 5, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(713, '143', 3, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(714, '143', 7, 23, 10.00, 0.00, 10.00, 10, '2032-08-06 09:50:51', '2032-08-06 09:50:51'),
(715, '5', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(716, '5', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(717, '5', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(718, '5', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(719, '104', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(720, '104', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(721, '104', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(722, '111', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(723, '111', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(724, '111', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(725, '111', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(726, '112', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(727, '112', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(728, '112', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(729, '112', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(730, '113', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(731, '113', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(732, '113', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(733, '113', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(734, '114', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:49', '2033-08-06 09:57:49'),
(735, '114', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(736, '114', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(737, '114', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(738, '118', 1, 24, 30.00, 35.00, 85.00, 120, '2033-08-06 09:57:53', '2025-09-03 08:52:40'),
(739, '118', 5, 24, 10.00, 1.00, 9.00, 10, '2033-08-06 09:57:53', '2025-08-11 09:03:08'),
(740, '118', 2, 24, 10.00, 5.00, 5.00, 10, '2033-08-06 09:57:53', '2025-08-10 21:31:54'),
(741, '118', 7, 24, 10.00, 1.00, 9.00, 10, '2033-08-06 09:57:53', '2025-08-10 21:17:49'),
(742, '121', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(743, '121', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(744, '121', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(745, '121', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(746, '122', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(747, '122', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(748, '122', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(749, '122', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(750, '134', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(751, '134', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(752, '134', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(753, '134', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(754, '135', 1, 24, 30.00, 6.00, 114.00, 120, '2033-08-06 09:57:53', '2025-08-10 19:46:27'),
(755, '135', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(756, '135', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(757, '135', 3, 24, 120.00, 0.00, 120.00, 120, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(758, '135', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(759, '136', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(760, '136', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(761, '136', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(762, '136', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(763, '143', 1, 24, 30.00, 0.00, 120.00, 120, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(764, '143', 5, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(765, '143', 2, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(766, '143', 7, 24, 10.00, 0.00, 10.00, 10, '2033-08-06 09:57:53', '2033-08-06 09:57:53'),
(767, '5', 1, 25, 30.00, 0.00, 33.00, 30, '2026-06-03 11:57:25', '2025-09-04 06:25:40'),
(768, '5', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(769, '5', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(770, '5', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(771, '5', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(772, '5', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(773, '5', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(774, '104', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(775, '104', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(776, '104', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(777, '104', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(778, '104', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(779, '104', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(780, '111', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(781, '111', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(782, '111', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(783, '111', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(784, '111', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(785, '111', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(786, '111', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(787, '112', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(788, '112', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(789, '112', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(790, '112', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:25', '2026-06-03 11:57:25'),
(791, '112', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(792, '112', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(793, '112', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(794, '113', 1, 25, 30.00, 4.00, 26.00, 30, '2026-06-03 11:57:26', '2025-09-03 14:03:36'),
(795, '113', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(796, '113', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(797, '113', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(798, '113', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(799, '113', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(800, '113', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(801, '114', 1, 25, 30.00, 42.00, -12.00, 30, '2026-06-03 11:57:26', '2025-09-03 14:02:32'),
(802, '114', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(803, '114', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(804, '114', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(805, '114', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(806, '114', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(807, '114', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(808, '118', 1, 25, 30.00, 15.00, 15.00, 30, '2026-06-03 11:57:26', '2025-09-03 14:02:30'),
(809, '118', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(810, '118', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(811, '118', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(812, '118', 4, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(813, '118', 7, 25, 10.00, 2.00, 8.00, 10, '2026-06-03 11:57:26', '2025-09-03 14:02:27'),
(814, '118', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(815, '118', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(816, '121', 1, 25, 30.00, 6.00, 24.00, 30, '2026-06-03 11:57:26', '2025-09-03 14:02:17'),
(817, '121', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(818, '121', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(819, '121', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(820, '121', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(821, '121', 9, 25, 0.00, 2.00, -2.00, 0, '2026-06-03 11:57:26', '2025-09-03 13:02:06'),
(822, '121', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(823, '122', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(824, '122', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(825, '122', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(826, '122', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(827, '122', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(828, '122', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(829, '122', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(830, '134', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(831, '134', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(832, '134', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(833, '134', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(834, '134', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(835, '134', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(836, '134', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(837, '135', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:26', '2026-06-03 11:57:26'),
(838, '135', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(839, '135', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(840, '135', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(841, '135', 3, 25, 120.00, 0.00, 120.00, 120, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(842, '135', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(843, '135', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(844, '135', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(845, '136', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(846, '136', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(847, '136', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(848, '136', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(849, '136', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(850, '136', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(851, '136', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(852, '143', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(853, '143', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(854, '143', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(855, '143', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(856, '143', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(857, '143', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(858, '143', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(859, '145', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(860, '145', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(861, '145', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(862, '145', 4, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(863, '145', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(864, '145', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(865, '145', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(866, '146', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(867, '146', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(868, '146', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(869, '146', 4, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(870, '146', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(871, '146', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(872, '146', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(873, '147', 1, 25, 30.00, 0.00, 30.00, 30, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(874, '147', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(875, '147', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(876, '147', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(877, '147', 4, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(878, '147', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(879, '147', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(880, '147', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(881, '148', 6, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(882, '148', 5, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(883, '148', 2, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(884, '148', 4, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(885, '148', 7, 25, 10.00, 0.00, 10.00, 10, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(886, '148', 9, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(887, '148', 8, 25, 0.00, 0.00, 0.00, 0, '2026-06-03 11:57:27', '2026-06-03 11:57:27'),
(888, '5', 1, 26, 30.00, 27.00, 18.00, 45, '2027-06-01 07:01:13', '2025-09-05 07:33:58'),
(889, '5', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(890, '5', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(891, '5', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(892, '5', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(893, '5', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(894, '5', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(895, '104', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(896, '104', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(897, '104', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(898, '104', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(899, '104', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(900, '104', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(901, '111', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(902, '111', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(903, '111', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(904, '111', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(905, '111', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(906, '111', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(907, '111', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(908, '112', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(909, '112', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(910, '112', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(911, '112', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(912, '112', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(913, '112', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(914, '112', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(915, '113', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(916, '113', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(917, '113', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(918, '113', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(919, '113', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(920, '113', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(921, '113', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(922, '114', 1, 26, 30.00, 18.00, 0.00, 18, '2027-06-01 07:01:13', '2025-09-04 07:30:17'),
(923, '114', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(924, '114', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(925, '114', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(926, '114', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(927, '114', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(928, '114', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(929, '118', 1, 26, 30.00, 5.00, 40.00, 45, '2027-06-01 07:01:13', '2025-09-04 07:31:15'),
(930, '118', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(931, '118', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(932, '118', 2, 26, 10.00, 4.00, 10.00, 10, '2027-06-01 07:01:13', '2025-09-04 07:31:18'),
(933, '118', 4, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(934, '118', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(935, '118', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(936, '118', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(937, '121', 1, 26, 30.00, 0.00, 48.00, 45, '2027-06-01 07:01:13', '2025-09-04 07:34:10'),
(938, '121', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(939, '121', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(940, '121', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(941, '121', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(942, '121', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(943, '121', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(944, '122', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(945, '122', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(946, '122', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(947, '122', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(948, '122', 7, 26, 10.00, 6.00, 4.00, 10, '2027-06-01 07:01:13', '2025-09-04 07:28:42'),
(949, '122', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(950, '122', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(951, '134', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(952, '134', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(953, '134', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(954, '134', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(955, '134', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(956, '134', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(957, '134', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(958, '135', 1, 26, 30.00, 4.00, 41.00, 45, '2027-06-01 07:01:13', '2025-09-04 07:31:24'),
(959, '135', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(960, '135', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(961, '135', 2, 26, 10.00, 4.00, 10.00, 10, '2027-06-01 07:01:13', '2025-09-04 07:29:28'),
(962, '135', 3, 26, 120.00, 0.00, 120.00, 120, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(963, '135', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(964, '135', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(965, '135', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(966, '136', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(967, '136', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(968, '136', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(969, '136', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(970, '136', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(971, '136', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(972, '136', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(973, '143', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(974, '143', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(975, '143', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(976, '143', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(977, '143', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(978, '143', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(979, '143', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(980, '145', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(981, '145', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(982, '145', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(983, '145', 4, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(984, '145', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(985, '145', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(986, '145', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(987, '146', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(988, '146', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(989, '146', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(990, '146', 4, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(991, '146', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(992, '146', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(993, '146', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(994, '147', 1, 26, 30.00, 0.00, 45.00, 45, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(995, '147', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(996, '147', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(997, '147', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(998, '147', 4, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(999, '147', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1000, '147', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1001, '147', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1002, '148', 6, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1003, '148', 5, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1004, '148', 2, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1005, '148', 4, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1006, '148', 7, 26, 10.00, 0.00, 10.00, 10, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1007, '148', 9, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13'),
(1008, '148', 8, 26, 0.00, 0.00, 0.00, 0, '2027-06-01 07:01:13', '2027-06-01 07:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `financial_years`
--

CREATE TABLE `financial_years` (
  `id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `year_name` varchar(100) NOT NULL,
  `total_days` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_years`
--

INSERT INTO `financial_years` (`id`, `start_date`, `end_date`, `year_name`, `total_days`, `is_active`, `created_at`, `updated_at`) VALUES
(25, '2026-07-01', '2027-06-30', '2026/27', 365, 1, '2026-06-03 11:57:24', '2026-06-03 11:57:24'),
(26, '2027-07-01', '2028-06-30', '2027/28', 366, 1, '2027-06-01 07:01:13', '2027-06-01 07:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `name`, `date`, `description`, `is_recurring`, `created_at`) VALUES
(1, 'Jamhuri day', '2025-12-12', 'To become a republic', 1, '2025-07-22 06:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` int(11) NOT NULL,
  `reason` text NOT NULL,
  `deduction_details` text DEFAULT NULL COMMENT 'JSON storage of deduction plan',
  `primary_days` int(11) DEFAULT 0 COMMENT 'Days deducted from primary leave type',
  `annual_days` int(11) DEFAULT 0 COMMENT 'Days deducted from annual leave',
  `unpaid_days` int(11) DEFAULT 0 COMMENT 'Days that are unpaid',
  `status` enum('pending','pending_section_head','pending_dept_head','pending_managing_director','pending_hr_manager','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `section_head_approval` enum('pending','approved','rejected') DEFAULT 'pending',
  `section_head_approved_by` varchar(50) DEFAULT NULL,
  `section_head_approved_at` timestamp NULL DEFAULT NULL,
  `dept_head_approval` enum('pending','approved','rejected') DEFAULT 'pending',
  `dept_head_approved_by` varchar(50) DEFAULT NULL,
  `dept_head_approved_at` timestamp NULL DEFAULT NULL,
  `hr_processed_by` varchar(50) DEFAULT NULL,
  `hr_processed_at` timestamp NULL DEFAULT NULL,
  `hr_comments` text DEFAULT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `section_head_emp_id` int(11) DEFAULT NULL,
  `dept_head_emp_id` int(11) DEFAULT NULL,
  `days_deducted` int(11) DEFAULT 0,
  `days_from_annual` int(11) DEFAULT 0,
  `managing_director_approved_by` int(11) DEFAULT NULL,
  `hr_approved_by` int(11) DEFAULT NULL,
  `hr_approved_at` datetime DEFAULT NULL,
  `managing_director_approved_at` datetime DEFAULT NULL,
  `md_emp_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_requested`, `reason`, `deduction_details`, `primary_days`, `annual_days`, `unpaid_days`, `status`, `applied_at`, `section_head_approval`, `section_head_approved_by`, `section_head_approved_at`, `dept_head_approval`, `dept_head_approved_by`, `dept_head_approved_at`, `hr_processed_by`, `hr_processed_at`, `hr_comments`, `approver_id`, `section_head_emp_id`, `dept_head_emp_id`, `days_deducted`, `days_from_annual`, `managing_director_approved_by`, `hr_approved_by`, `hr_approved_at`, `managing_director_approved_at`, `md_emp_id`) VALUES
(1, 112, 6, '2025-07-22', '2025-07-28', 5, 'medical emergency', NULL, 0, 0, 0, 'approved', '2025-07-22 06:38:14', 'pending', NULL, NULL, 'pending', NULL, NULL, 'admin-001', '2025-07-22 06:38:25', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(2, 118, 4, '2025-07-23', '2025-07-30', 6, 'sick', NULL, 0, 0, 0, 'approved', '2025-07-22 07:27:34', 'pending', NULL, NULL, 'pending', NULL, NULL, '3', '2025-07-22 08:26:03', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(3, 118, 6, '2025-07-24', '2025-07-28', 3, 'short', NULL, 0, 0, 0, 'pending', '2025-07-24 15:04:28', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(4, 118, 6, '2025-07-25', '2025-07-29', 3, 'short', NULL, 0, 0, 0, '', '2025-07-24 17:32:17', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(5, 118, 6, '2025-07-25', '2025-07-29', 3, 'short', NULL, 0, 0, 0, '', '2025-07-24 17:32:45', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(6, 118, 6, '2025-07-25', '2025-07-29', 3, 'short', NULL, 0, 0, 0, 'pending', '2025-07-24 17:34:04', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(7, 118, 6, '2025-07-24', '2025-08-02', 7, 'short leave', NULL, 0, 0, 0, 'pending', '2025-07-24 17:34:40', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(8, 118, 6, '2025-07-24', '2025-07-31', 6, 'TEST', NULL, 0, 0, 0, 'pending', '2025-07-24 17:51:34', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(9, 118, 6, '2025-07-25', '2025-07-26', 1, 'TEST', NULL, 0, 0, 0, '', '2025-07-24 18:36:41', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(10, 118, 4, '2025-07-26', '2025-07-28', 1, 'TEST', NULL, 0, 0, 0, '', '2025-07-24 18:40:58', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(11, 118, 5, '2025-07-26', '2025-07-31', 4, 'school', NULL, 0, 0, 0, 'rejected', '2025-07-25 03:16:34', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:32', NULL, NULL),
(12, 135, 6, '2025-07-28', '2025-07-31', 4, 'short', NULL, 0, 0, 0, 'approved', '2025-07-25 04:36:00', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-09-04 10:31:24', NULL, NULL),
(13, 118, 2, '2025-07-28', '2025-07-31', 4, 'sick leave', NULL, 0, 0, 0, 'approved', '2025-07-25 05:00:04', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:31:18', NULL, NULL),
(14, 136, 2, '2025-07-28', '2025-08-01', 5, 'checkup', NULL, 0, 0, 0, 'rejected', '2025-07-25 05:04:21', 'approved', '5', '2025-07-25 05:26:20', 'pending', '121', '2025-08-10 21:33:08', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(22, 135, 2, '2025-07-30', '2025-08-04', 4, 'sick', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":4,\"warnings\":[\"No available balance. All 4 days will be unpaid.\"],\"is_valid\":true,\"total_days\":4}', 0, 0, 4, 'approved', '2025-07-29 03:44:04', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-09-04 10:29:28', NULL, NULL),
(23, 118, 6, '2025-07-30', '2025-08-01', 3, 'short', '{\"primary_deduction\":0,\"annual_deduction\":3,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 3 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":3}', 0, 3, 0, 'approved', '2025-07-29 06:03:59', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:31:15', NULL, NULL),
(25, 5, 2, '2025-07-30', '2025-08-04', 4, 'sick', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":4,\"warnings\":[\"No available balance. All 4 days will be unpaid.\"],\"is_valid\":true,\"total_days\":4}', 0, 0, 4, 'approved', '2025-07-29 09:04:27', 'pending', NULL, NULL, 'approved', '121', '2025-07-29 09:07:44', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(26, 5, 1, '2025-07-30', '2025-08-06', 6, 'annual leave', '{\"primary_deduction\":6,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":6}', 6, 0, 0, 'approved', '2025-07-29 09:14:31', 'pending', NULL, NULL, 'approved', '121', '2025-07-29 09:15:24', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(27, 121, 1, '2025-07-30', '2025-08-05', 5, 'ANNUAL', '{\"primary_deduction\":5,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":5}', 5, 0, 0, 'rejected', '2025-07-29 09:22:27', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-04 10:31:09', NULL, NULL),
(28, 118, 1, '2025-08-08', '2025-08-11', 2, 'annual', '{\"primary_deduction\":2,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":2}', 2, 0, 0, 'approved', '2025-08-07 13:01:49', 'approved', '5', '2025-08-07 13:02:36', 'approved', '121', '2025-08-07 13:03:12', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(29, 118, 1, '2025-08-08', '2025-08-21', 10, 'annual', '{\"primary_deduction\":10,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":10}', 10, 0, 0, 'approved', '2025-08-08 07:46:07', 'approved', '5', '2025-08-08 07:57:00', 'approved', '121', '2025-09-03 13:06:55', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(30, 112, 1, '2025-08-08', '2025-08-12', 3, 'compassionate', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'rejected', '2025-08-08 09:35:07', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 134, 121, 0, 0, NULL, 135, '2025-09-04 10:31:05', NULL, NULL),
(31, 135, 1, '2025-08-08', '2025-08-12', 3, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-08 09:37:30', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-08-10 22:46:27', NULL, NULL),
(32, 118, 1, '2025-08-08', '2025-08-12', 3, 'annual', '{\"primary_deduction\":3,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":3}', 3, 0, 0, 'rejected', '2025-08-08 09:44:49', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:31:00', NULL, NULL),
(33, 118, 1, '2025-08-08', '2025-08-09', 1, 'final test', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'rejected', '2025-08-08 09:48:09', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:56', NULL, NULL),
(34, 135, 1, '2025-08-08', '2025-08-12', 3, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-08 10:01:51', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-08-10 20:05:38', NULL, NULL),
(35, 118, 1, '2025-08-14', '2025-08-15', 2, 'APPLY', '{\"primary_deduction\":2,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":2}', 2, 0, 0, 'approved', '2025-08-08 11:14:20', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-08-10 19:52:00', NULL, NULL),
(36, 136, 1, '2025-08-11', '2025-08-19', 7, 'SEVEN CLEAN', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":7,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 7 days will be unpaid.\"],\"is_valid\":true,\"total_days\":7}', 0, 0, 7, '', '2025-08-10 14:58:29', 'approved', '5', '2025-08-10 15:00:10', 'approved', '121', '2025-08-10 15:57:28', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(37, 118, 1, '2025-08-11', '2025-08-14', 4, 'apply sunday test', '{\"primary_deduction\":4,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":4}', 4, 0, 0, 'approved', '2025-08-10 17:06:42', 'approved', '5', '2025-08-10 17:07:15', 'approved', '121', '2025-08-10 17:07:50', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(38, 118, 1, '2025-08-18', '2025-08-18', 1, 'sun 1', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'rejected', '2025-08-10 17:10:25', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:48', NULL, NULL),
(39, 104, 7, '2025-08-11', '2025-08-13', 3, 'COM', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"No available balance. All 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-10 17:33:18', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(40, 104, 7, '2025-08-11', '2025-08-13', 3, 'COM', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"No available balance. All 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-10 17:36:06', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(41, 104, 7, '2025-08-11', '2025-08-13', 3, 'COM', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"No available balance. All 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-10 17:36:19', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(42, 104, 7, '2025-08-11', '2025-08-13', 3, 'COM', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"No available balance. All 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-10 17:36:32', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(43, 118, 1, '2025-08-13', '2025-08-13', 1, 'SUN TEST', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'rejected', '2025-08-10 17:36:56', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:44', NULL, NULL),
(44, 118, 1, '2025-08-13', '2025-08-13', 1, 'SUN TEST', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'approved', '2025-08-10 17:38:32', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:40', NULL, NULL),
(45, 118, 1, '2025-08-13', '2025-08-13', 1, 'SUN TEST', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'rejected', '2025-08-10 18:01:21', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:36', NULL, NULL),
(46, 118, 1, '2025-08-13', '2025-08-13', 1, 'SUN TEST', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'approved', '2025-08-10 18:01:30', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:26', NULL, NULL),
(47, 118, 1, '2025-08-11', '2025-08-25', 11, 'apply sun', '{\"primary_deduction\":11,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":11}', 11, 0, 0, 'rejected', '2025-08-10 18:03:17', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:29:33', NULL, NULL),
(48, 118, 1, '2025-08-11', '2025-08-25', 11, 'apply sun', '{\"primary_deduction\":11,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":11}', 11, 0, 0, 'approved', '2025-08-10 19:23:29', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-08-10 22:46:12', NULL, NULL),
(49, 118, 1, '2025-08-11', '2025-08-12', 2, 'APPLY ANNUAL', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 2 days will be unpaid.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'rejected', '2025-08-10 20:17:31', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:29:14', NULL, NULL),
(50, 118, 1, '2025-08-11', '2025-08-12', 2, 'APPLY ANNUAL', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 2 days will be unpaid.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'rejected', '2025-08-10 20:31:16', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:30:21', NULL, NULL),
(51, 118, 1, '2025-08-11', '2025-08-12', 2, 'APPLY ANNUAL', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 2 days will be unpaid.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'rejected', '2025-08-10 20:31:50', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:29:07', NULL, NULL),
(52, 135, 3, '2025-08-11', '2025-08-13', 3, 'MAT', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"No available balance. All 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-10 20:36:33', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(53, 135, 3, '2025-08-11', '2025-08-13', 3, 'MAT', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":3,\"warnings\":[\"No available balance. All 3 days will be unpaid.\"],\"is_valid\":true,\"total_days\":3}', 0, 0, 3, 'approved', '2025-08-10 20:46:24', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(54, 135, 3, '2025-08-11', '2025-08-13', 3, 'MAT', '{\"primary_deduction\":3,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Maternity Leave balance.\"],\"is_valid\":true,\"total_days\":3}', 3, 0, 0, 'approved', '2025-08-10 20:47:31', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(55, 135, 3, '2025-08-11', '2025-08-13', 3, 'MAT', '{\"primary_deduction\":3,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Maternity Leave balance.\"],\"is_valid\":true,\"total_days\":3}', 3, 0, 0, 'approved', '2025-08-10 20:48:53', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(56, 135, 3, '2025-08-11', '2025-08-13', 3, 'MAT', '{\"primary_deduction\":3,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Maternity Leave balance.\"],\"is_valid\":true,\"total_days\":3}', 3, 0, 0, 'approved', '2025-08-10 21:12:40', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(57, 118, 1, '2025-08-12', '2025-08-13', 2, 'apply', '{\"primary_deduction\":2,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":2}', 2, 0, 0, 'approved', '2025-08-10 21:13:15', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-08-11 00:16:35', NULL, NULL),
(58, 135, 7, '2025-08-29', '2025-08-31', 1, 'apply', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Compassionate Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'approved', '2025-08-10 21:15:57', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(59, 118, 7, '2025-08-12', '2025-08-12', 1, 'comp', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Compassionate Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'approved', '2025-08-10 21:17:39', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-08-11 00:17:49', NULL, NULL),
(60, 118, 7, '2025-08-12', '2025-08-12', 1, 'compo', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Compassionate Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'rejected', '2025-08-10 21:19:16', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:29:00', NULL, NULL),
(61, 118, 7, '2025-08-12', '2025-08-12', 1, 'compo', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Compassionate Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'approved', '2025-08-10 21:26:03', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(62, 118, 2, '2025-08-12', '2025-08-13', 2, 'unwell', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"No available balance. All 2 days will be unpaid.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'approved', '2025-08-10 21:27:00', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(63, 118, 1, '2025-08-12', '2025-08-13', 2, 'ANN', '{\"primary_deduction\":2,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":2}', 2, 0, 0, 'approved', '2025-08-10 21:28:38', 'approved', '5', '2025-08-10 21:28:53', 'approved', '121', '2025-08-10 21:29:17', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(64, 118, 2, '2025-08-26', '2025-09-01', 5, 'SICK', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":5,\"warnings\":[\"No available balance. All 5 days will be unpaid.\"],\"is_valid\":true,\"total_days\":5}', 0, 0, 5, 'approved', '2025-08-10 21:29:57', 'approved', '5', '2025-08-10 21:31:28', 'approved', '121', '2025-08-10 21:31:54', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(65, 118, 5, '2025-08-12', '2025-08-12', 1, 'study exams', '{\"primary_deduction\":1,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Study Leave balance.\"],\"is_valid\":true,\"total_days\":1}', 1, 0, 0, 'approved', '2025-08-11 09:01:55', 'approved', '5', '2025-08-11 09:02:39', 'approved', '121', '2025-08-11 09:03:08', NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(66, 114, 5, '2025-08-26', '2025-09-06', 9, 'study', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":9,\"warnings\":[\"Insufficient leave balance. 0 days from Study Leave, 0 days from Annual Leave, 9 days will be unpaid.\"],\"is_valid\":true,\"total_days\":9}', 0, 0, 9, 'approved', '2025-08-25 12:53:41', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-04 10:30:17', NULL, NULL),
(67, 114, 5, '2025-08-26', '2025-09-06', 9, 'study', '{\"primary_deduction\":9,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Study Leave balance.\"],\"is_valid\":true,\"total_days\":9}', 9, 0, 0, 'approved', '2025-08-25 13:32:56', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-04 10:30:11', NULL, NULL),
(68, 135, 2, '2025-08-26', '2025-11-25', 66, 'leave', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":66,\"warnings\":[\"No available balance. All 66 days will be unpaid.\"],\"is_valid\":true,\"total_days\":66}', 0, 0, 66, 'approved', '2025-08-25 19:03:55', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(69, 135, 2, '2025-08-26', '2025-11-25', 66, 'leave', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":66,\"warnings\":[\"No available balance. All 66 days will be unpaid.\"],\"is_valid\":true,\"total_days\":66}', 0, 0, 66, 'approved', '2025-08-25 19:16:48', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(70, 135, 2, '2025-08-26', '2025-11-25', 66, 'leave', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":66,\"warnings\":[\"No available balance. All 66 days will be unpaid.\"],\"is_valid\":true,\"total_days\":66}', 0, 0, 66, 'approved', '2025-08-25 19:46:36', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(71, 121, 1, '2025-09-03', '2025-09-18', 12, 'apply', '{\"primary_deduction\":12,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":12}', 12, 0, 0, 'approved', '2025-09-03 08:18:44', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(72, 118, 2, '2025-09-05', '2025-09-10', 4, 'aasgbfh', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":4,\"warnings\":[\"No available balance. All 4 days will be unpaid.\"],\"is_valid\":true,\"total_days\":4}', 0, 0, 4, 'approved', '2025-09-03 08:26:00', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(73, 118, 5, '2025-09-04', '2025-09-09', 4, 'Study eave', '{\"primary_deduction\":4,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Study Leave balance.\"],\"is_valid\":true,\"total_days\":4}', 4, 0, 0, 'rejected', '2025-09-03 08:36:02', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:28:52', NULL, NULL),
(74, 118, 1, '2025-09-04', '2025-09-26', 17, 'topaz', '{\"primary_deduction\":17,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":17}', 17, 0, 0, 'rejected', '2025-09-03 08:41:15', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:28:48', NULL, NULL),
(75, 5, 1, '2025-09-04', '2025-09-11', 6, 'annual', '{\"primary_deduction\":6,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":6}', 6, 0, 0, 'rejected', '2025-09-03 08:46:51', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-03 11:51:06', NULL, NULL),
(76, 118, 1, '2025-09-06', '2025-09-25', 14, 'annual', '{\"primary_deduction\":14,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":14}', 14, 0, 0, 'approved', '2025-09-03 08:52:14', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-03 11:52:40', NULL, NULL),
(77, 122, 7, '2025-09-08', '2025-09-15', 6, 'compassionate', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":6,\"warnings\":[\"No available balance. All 6 days will be unpaid.\"],\"is_valid\":true,\"total_days\":6}', 0, 0, 6, 'rejected', '2025-09-03 08:53:56', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 134, 121, 0, 0, NULL, 135, '2025-09-04 10:28:56', NULL, NULL),
(78, 122, 7, '2025-09-08', '2025-09-15', 6, 'compassionate', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":6,\"warnings\":[\"No available balance. All 6 days will be unpaid.\"],\"is_valid\":true,\"total_days\":6}', 0, 0, 6, 'approved', '2025-09-03 08:56:31', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 134, 121, 0, 0, NULL, 135, '2025-09-04 10:28:42', NULL, NULL),
(79, 114, 1, '2025-09-04', '2025-10-31', 42, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":42,\"warnings\":[\"Requested days (42) exceed maximum allowed per year (30).\",\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 42 days will be unpaid.\"],\"is_valid\":true,\"total_days\":42}', 0, 0, 42, 'approved', '2025-09-03 08:56:57', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-03 17:02:32', NULL, NULL),
(80, 118, 1, '2025-09-04', '2025-09-10', 5, 'apply', '{\"primary_deduction\":5,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":5}', 5, 0, 0, 'approved', '2025-09-03 11:33:43', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-03 17:02:29', NULL, NULL),
(81, 118, 7, '2025-09-04', '2025-09-05', 2, 'apply', '{\"primary_deduction\":2,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Compassionate Leave balance.\"],\"is_valid\":true,\"total_days\":2}', 2, 0, 0, 'approved', '2025-09-03 11:39:16', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-03 17:02:27', NULL, NULL),
(82, 121, 9, '2025-09-04', '2025-09-06', 2, 'adding', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 2 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":2,\"add_to_annual\":2}', 0, 0, 0, 'approved', '2025-09-03 13:00:47', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-03 16:02:05', NULL, NULL),
(83, 146, 9, '2025-07-02', '2025-07-05', 3, 'reason', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 3 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":3,\"add_to_annual\":3}', 0, 0, 0, 'approved', '2025-09-03 13:37:52', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 134, 121, 0, 0, NULL, 135, '2025-09-04 09:18:22', NULL, NULL),
(84, 146, 9, '2025-07-02', '2025-07-05', 3, 'reason', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 3 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":3,\"add_to_annual\":3}', 0, 0, 0, 'approved', '2025-09-03 13:38:52', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 134, 121, 0, 0, NULL, 135, '2025-09-03 17:02:22', NULL, NULL),
(85, 121, 1, '2025-09-04', '2025-09-11', 6, 'annual leave', '{\"primary_deduction\":null,\"annual_deduction\":6,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Annual Leave, 6 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":6}', NULL, 6, 0, 'approved', '2025-09-03 13:40:09', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-03 17:02:17', NULL, NULL),
(86, 146, 9, '2025-08-31', '2025-09-02', 2, 'reason', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 2 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":2,\"add_to_annual\":2}', 0, 0, 0, 'approved', '2025-09-03 14:00:58', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 134, 121, 0, 0, NULL, 135, '2025-09-03 17:02:04', NULL, NULL),
(87, 113, 6, '2025-09-04', '2025-09-09', 4, 'short', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":4,\"warnings\":[\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 4 days will be unpaid.\"],\"is_valid\":true,\"total_days\":4}', NULL, 0, 4, 'approved', '2025-09-03 14:03:22', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-09-03 17:03:36', NULL, NULL),
(88, 5, 8, '2025-09-04', '2025-09-06', 2, 'absence', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"\\u2139\\ufe0f You will be absent for 2 days.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'approved', '2025-09-03 14:04:20', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-03 17:04:27', NULL, NULL),
(89, 5, 9, '2025-06-11', '2025-06-14', 3, 'claim a day', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 3 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":3,\"add_to_annual\":3}', 0, 0, 0, 'approved', '2025-09-04 06:25:33', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 09:25:40', NULL, NULL),
(90, 121, 9, '2025-07-02', '2025-07-06', 3, 'claim leave', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 3 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":3,\"add_to_annual\":3}', 0, 0, 0, 'approved', '2025-09-04 07:33:48', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 121, 0, 0, NULL, 135, '2025-09-04 10:34:10', NULL, NULL),
(91, 5, 6, '2025-09-05', '2025-09-09', 3, 'Short leave', '{\"primary_deduction\":null,\"annual_deduction\":3,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Short Leave, 3 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":3}', NULL, 3, 0, 'approved', '2025-09-04 07:37:01', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, 135, '2025-09-04 10:37:37', NULL, NULL),
(92, 5, 6, '2025-09-05', '2025-09-08', 2, 'short', '{\"primary_deduction\":null,\"annual_deduction\":2,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Short Leave, 2 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":2}', NULL, 2, 0, 'pending_section_head', '2025-09-04 07:38:34', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(93, 5, 6, '2025-09-05', '2025-09-08', 2, 'SHORT LEAVE', '{\"primary_deduction\":null,\"annual_deduction\":2,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Short Leave, 2 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":2}', NULL, 2, 0, 'pending_dept_head', '2025-09-04 07:41:28', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, 5, 121, 0, 0, NULL, NULL, NULL, NULL, NULL),
(94, 121, 1, '2025-09-05', '2025-09-27', 16, 'annual leave', '{\"primary_deduction\":null,\"annual_deduction\":16,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Annual Leave, 16 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":16}', NULL, 16, 0, 'approved', '2025-09-04 07:55:46', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(95, 5, 1, '2025-09-08', '2025-09-30', 17, 'anual leave', '{\"primary_deduction\":null,\"annual_deduction\":17,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Annual Leave, 17 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":17}', NULL, 17, 0, 'approved', '2025-09-04 07:56:28', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(96, 121, 1, '2025-09-05', '2025-09-30', 18, 'annual leave', '{\"primary_deduction\":null,\"annual_deduction\":18,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Annual Leave, 18 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":18}', NULL, 18, 0, 'approved', '2025-09-04 07:59:32', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(97, 5, 6, '2025-09-05', '2025-09-10', 4, 'short', '{\"primary_deduction\":null,\"annual_deduction\":4,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Short Leave, 4 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":4}', NULL, 4, 0, 'approved', '2025-09-04 08:03:56', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(98, 121, 6, '2025-09-05', '2025-09-10', 4, 'Short', '{\"primary_deduction\":null,\"annual_deduction\":4,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Short Leave, 4 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":4}', NULL, 4, 0, 'approved', '2025-09-04 08:05:00', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(99, 5, 9, '2025-09-18', '2025-09-25', 6, 'short', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 6 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":6,\"add_to_annual\":6}', 0, 0, 0, 'approved', '2025-09-04 08:05:34', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(100, 121, 1, '2025-09-05', '2025-09-30', 18, 'annual', '{\"primary_deduction\":null,\"annual_deduction\":18,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient.  days from Annual Leave, 18 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":18}', NULL, 18, 0, 'approved', '2025-09-04 08:06:37', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(101, 113, 1, '2025-09-05', '2025-10-09', 25, 'Annual', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":25,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 25 days will be unpaid.\"],\"is_valid\":true,\"total_days\":25}', 0, 0, 25, 'pending_section_head', '2025-09-04 09:07:05', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(102, 5, 1, '2025-09-05', '2025-10-09', 25, 'annual', '{\"primary_deduction\":24,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 24 days from Annual Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":25}', 24, 1, 0, 'pending_section_head', '2025-09-04 09:07:45', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(103, 121, 1, '2025-09-05', '2025-10-08', 24, 'annual', '{\"primary_deduction\":24,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":24}', 24, 0, 0, 'pending_section_head', '2025-09-04 09:08:21', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(104, 121, 1, '2025-09-06', '2025-10-01', 18, 'annual', '{\"primary_deduction\":18,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":18}', 18, 0, 0, 'pending_section_head', '2025-09-04 09:17:03', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(105, 5, 6, '2025-09-05', '2025-09-11', 5, 'Short', '{\"primary_deduction\":0,\"annual_deduction\":5,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 5 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":5}', 0, 5, 0, 'rejected', '2025-09-04 09:18:13', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-09-04 12:19:02', NULL, NULL),
(106, 121, 1, '2025-09-06', '2025-09-30', 17, 'annual', '{\"primary_deduction\":17,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":17}', 17, 0, 0, 'pending_section_head', '2025-09-04 09:23:45', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(107, 5, 6, '2025-09-04', '2025-09-05', 2, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":2,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 2 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":2}', 0, 2, 0, 'pending_managing_director', '2025-09-04 09:26:33', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(108, 5, 6, '2025-09-05', '2025-09-08', 2, 'short..', '{\"primary_deduction\":0,\"annual_deduction\":2,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 2 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":2}', 0, 2, 0, 'pending_section_head', '2025-09-04 09:40:02', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(109, 5, 6, '2025-09-05', '2025-09-10', 4, 'short,,', '{\"primary_deduction\":0,\"annual_deduction\":4,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 4 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":4}', 0, 4, 0, 'pending_managing_director', '2025-09-04 09:42:13', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(110, 5, 6, '2025-09-05', '2025-09-06', 1, 'short', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_section_head', '2025-09-04 09:55:07', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(111, 121, 6, '2025-09-05', '2025-09-07', 1, 'apply', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_managing_director', '2025-09-04 09:56:12', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(112, 5, 6, '2025-09-08', '2025-09-09', 2, 'apply', '{\"primary_deduction\":0,\"annual_deduction\":2,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 2 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":2}', 0, 2, 0, 'pending_dept_head', '2025-09-04 09:56:56', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(113, 5, 6, '2025-09-09', '2025-09-09', 1, 'apply', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_dept_head', '2025-09-04 09:57:58', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(114, 118, 6, '2025-09-10', '2025-09-10', 1, 'apply', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_dept_head', '2025-09-04 09:58:52', 'approved', '5', '2025-09-04 09:59:47', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(115, 121, 6, '2025-09-11', '2025-09-11', 1, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_managing_director', '2025-09-04 10:00:56', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(116, 5, 6, '2025-09-12', '2025-09-12', 1, 'short', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_section_head', '2025-09-04 10:01:35', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(117, 118, 6, '2025-09-15', '2025-09-15', 1, 'short', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_section_head', '2025-09-04 10:02:31', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(118, 5, 6, '2025-09-16', '2025-09-16', 1, 'short', '{\"primary_deduction\":0,\"annual_deduction\":1,\"unpaid_days\":0,\"warnings\":[\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"],\"is_valid\":true,\"total_days\":1}', 0, 1, 0, 'pending_section_head', '2025-09-04 10:05:36', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(119, 5, 1, '2025-09-06', '2025-09-30', 17, 'annual', '{\"primary_deduction\":17,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":17}', 17, 0, 0, 'pending_section_head', '2025-09-04 11:21:04', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(120, 135, 1, '2025-09-05', '2025-09-30', 18, 'annual leave', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":18,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"],\"is_valid\":true,\"total_days\":18}', 0, 0, 18, 'pending_section_head', '2025-09-04 11:24:29', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(121, 112, 1, '2025-09-05', '2025-09-30', 18, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":18,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"],\"is_valid\":true,\"total_days\":18}', 0, 0, 18, 'pending_section_head', '2025-09-04 11:25:44', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(122, 5, 1, '2025-09-05', '2025-09-30', 18, 'annual', '{\"primary_deduction\":18,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":18}', 18, 0, 0, 'pending_section_head', '2025-09-04 11:26:17', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(123, 121, 1, '2025-09-05', '2025-10-06', 22, 'annual', '{\"primary_deduction\":22,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":22}', 22, 0, 0, 'pending_section_head', '2025-09-04 11:27:32', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(124, 121, 1, '2025-09-05', '2025-10-06', 22, 'annual', '{\"primary_deduction\":22,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":22}', 22, 0, 0, 'pending_section_head', '2025-09-04 11:27:35', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(125, 118, 1, '2025-09-05', '2025-09-30', 18, 'annual', '{\"primary_deduction\":18,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":18}', 18, 0, 0, 'pending_section_head', '2025-09-04 11:29:33', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(126, 135, 6, '2025-09-05', '2025-09-05', 1, 'short', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"Insufficient leave balance. 0 days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"],\"is_valid\":true,\"total_days\":1}', 0, 0, 1, 'pending_section_head', '2025-09-04 11:47:54', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(127, 135, 6, '2025-09-05', '2025-09-05', 1, 'short', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"],\"is_valid\":true,\"total_days\":1}', NULL, 0, 1, 'pending_section_head', '2025-09-04 11:49:00', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(128, 135, 6, '2025-09-05', '2025-09-05', 1, 'short', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"],\"is_valid\":true,\"total_days\":1}', NULL, 0, 1, 'pending_section_head', '2025-09-04 11:51:18', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(129, 135, 9, '2025-09-05', '2025-09-05', 1, 'apply', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 1 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":1,\"add_to_annual\":1}', 0, 0, 0, 'pending_section_head', '2025-09-04 12:09:01', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(130, 135, 1, '2025-09-05', '2025-09-30', 18, 'annual', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":18,\"warnings\":[\"Insufficient leave balance.  days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"],\"is_valid\":true,\"total_days\":18}', NULL, 0, 18, 'approved', '2025-09-04 12:17:11', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(131, 135, 3, '2025-09-17', '2025-10-09', 23, 'annual', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":23,\"warnings\":[\"No available balance. All 23 days will be unpaid.\"],\"is_valid\":true,\"total_days\":23}', NULL, 0, 23, 'approved', '2025-09-04 12:18:28', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(132, 135, 3, '2025-09-17', '2025-10-09', 23, 'annual', '{\"primary_deduction\":23,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Maternity Leave balance.\"],\"is_valid\":true,\"total_days\":23}', 23, 0, 0, 'approved', '2025-09-04 12:26:09', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `leave_applications` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `days_requested`, `reason`, `deduction_details`, `primary_days`, `annual_days`, `unpaid_days`, `status`, `applied_at`, `section_head_approval`, `section_head_approved_by`, `section_head_approved_at`, `dept_head_approval`, `dept_head_approved_by`, `dept_head_approved_at`, `hr_processed_by`, `hr_processed_at`, `hr_comments`, `approver_id`, `section_head_emp_id`, `dept_head_emp_id`, `days_deducted`, `days_from_annual`, `managing_director_approved_by`, `hr_approved_by`, `hr_approved_at`, `managing_director_approved_at`, `md_emp_id`) VALUES
(133, 135, 3, '2025-09-17', '2025-10-09', 23, 'annual', '{\"primary_deduction\":23,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Maternity Leave balance.\"],\"is_valid\":true,\"total_days\":23}', 23, 0, 0, 'approved', '2025-09-04 12:26:20', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(134, 135, 3, '2025-09-17', '2025-10-09', 23, 'annual', '{\"primary_deduction\":23,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Maternity Leave balance.\"],\"is_valid\":true,\"total_days\":23}', 23, 0, 0, 'approved', '2025-09-04 12:30:31', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(135, 135, 3, '2025-09-17', '2025-10-09', 23, 'annual', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":23,\"warnings\":[\"No available balance. All 23 days will be unpaid.\"],\"is_valid\":true,\"total_days\":23}', NULL, 0, 23, 'pending_section_head', '2025-09-04 12:46:53', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(136, 135, 9, '2025-09-04', '2025-09-04', 1, 'claim', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"\\u2705 This will add 1 days to your annual leave upon approval.\"],\"is_valid\":true,\"total_days\":1,\"add_to_annual\":1}', 0, 0, 0, 'pending_section_head', '2025-09-04 12:47:42', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(137, 135, 8, '2025-09-05', '2025-09-05', 1, 'apply', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"\\u2139\\ufe0f You will be absent for 1 days.\"],\"is_valid\":true,\"total_days\":1}', 0, 0, 1, 'pending_section_head', '2025-09-04 12:48:21', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(138, 135, 6, '2025-09-05', '2025-09-05', 1, 'short', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"],\"is_valid\":true,\"total_days\":1}', NULL, 0, 1, 'pending_section_head', '2025-09-04 12:51:22', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(139, 135, 6, '2025-09-05', '2025-09-05', 1, 'short', '{\"primary_deduction\":null,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"],\"is_valid\":true,\"total_days\":1}', NULL, 0, 1, 'pending_section_head', '2025-09-04 13:23:45', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(140, 135, 6, '2025-09-05', '2025-09-05', 1, 'short', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":1,\"warnings\":[\"Insufficient leave balance. 0 days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"],\"is_valid\":true,\"total_days\":1}', 0, 0, 1, 'pending_section_head', '2025-09-04 13:25:35', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(141, 135, 1, '2025-09-05', '2025-09-30', 18, 'annual', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":18,\"warnings\":[\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"],\"is_valid\":true,\"total_days\":18}', 0, 0, 18, 'pending_section_head', '2025-09-04 13:28:20', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(142, 135, 6, '2025-09-05', '2025-09-10', 4, 'Short', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":4,\"warnings\":[\"Insufficient leave balance. 0 days from Short Leave, 0 days from Annual Leave, 4 days will be unpaid.\"],\"is_valid\":true,\"total_days\":4}', 0, 0, 4, 'approved', '2025-09-04 13:32:08', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(143, 5, 8, '2025-09-11', '2025-09-13', 2, 'absence', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"\\u2139\\ufe0f You will be absent for 2 days.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'pending_section_head', '2025-09-04 13:32:54', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(144, 5, 1, '2025-09-05', '2025-10-08', 24, 'annual', '{\"primary_deduction\":24,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":24}', 24, 0, 0, 'approved', '2025-09-04 13:34:12', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-09-05 10:33:58', NULL, NULL),
(145, 5, 1, '2025-09-05', '2025-10-01', 19, 'apply annual', '{\"primary_deduction\":19,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"Will be deducted from Annual Leave balance.\"],\"is_valid\":true,\"total_days\":19}', 19, 0, 0, 'approved', '2025-09-04 13:55:14', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(146, 135, 6, '2025-09-06', '2025-09-30', 17, 'Short leave', '{\"primary_deduction\":17,\"annual_deduction\":17,\"unpaid_days\":0,\"warnings\":[\"17 days from Short Leave (balance may go negative).\",\"Primary balance insufficient. 17 days from Short Leave.\"],\"is_valid\":true,\"total_days\":17}', 17, 17, 0, 'pending_managing_director', '2025-09-05 06:45:16', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL),
(147, 135, 8, '2025-09-11', '2025-09-14', 2, 'Absence', '{\"primary_deduction\":0,\"annual_deduction\":0,\"unpaid_days\":2,\"warnings\":[\"\\u2139\\ufe0f You will be absent for 2 days.\"],\"is_valid\":true,\"total_days\":2}', 0, 0, 2, 'rejected', '2025-09-05 07:23:41', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 135, '2025-09-05 10:24:57', NULL, NULL),
(148, 135, 2, '2025-09-06', '2025-09-10', 3, 'sick leave', '{\"primary_deduction\":3,\"annual_deduction\":0,\"unpaid_days\":0,\"warnings\":[\"3 days from Sick Leave (balance may go negative).\"],\"is_valid\":true,\"total_days\":3}', 3, 0, 0, 'pending_managing_director', '2025-09-05 07:31:48', 'pending', NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `financial_year` varchar(10) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `annual_leave_entitled` int(11) DEFAULT 30,
  `annual_leave_used` int(11) DEFAULT 0,
  `annual_leave_balance` int(11) DEFAULT 30,
  `sick_leave_used` int(11) DEFAULT 0,
  `other_leave_used` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`id`, `employee_id`, `financial_year`, `leave_type_id`, `annual_leave_entitled`, `annual_leave_used`, `annual_leave_balance`, `sick_leave_used`, `other_leave_used`, `created_at`, `updated_at`) VALUES
(3, 135, '2025', 4, 0, 0, 0, 0, 0, '2025-07-29 02:53:42', '2025-07-29 02:53:42'),
(6, 118, '2025', 1, 30, 2, 28, 0, 0, '2025-07-29 06:03:59', '2025-08-07 13:03:12'),
(7, 5, '2025', 1, 30, 6, 24, 0, 0, '2025-07-29 06:42:22', '2025-07-29 09:15:24'),
(8, 121, '2025', 1, 30, 0, 30, 0, 0, '2025-07-29 09:18:27', '2025-07-29 09:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `leave_history`
--

CREATE TABLE `leave_history` (
  `id` int(11) NOT NULL,
  `leave_application_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `performed_by` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_history`
--

INSERT INTO `leave_history` (`id`, `leave_application_id`, `action`, `performed_by`, `comments`, `performed_at`) VALUES
(1, 22, 'applied', 9, 'Leave application submitted for 4 days', '2025-07-29 03:44:04'),
(2, 23, 'applied', 4, 'Leave application submitted for 3 days', '2025-07-29 06:03:59'),
(3, 24, 'applied', 5, 'Leave application submitted for 6 days', '2025-07-29 06:42:22'),
(4, 25, 'applied', 5, 'Leave application submitted for 4 days', '2025-07-29 09:04:27'),
(5, 25, 'dept_head_approved', 6, 'Approved by department head', '2025-07-29 09:07:44'),
(6, 26, 'applied', 5, 'Leave application submitted for 6 days', '2025-07-29 09:14:31'),
(7, 26, 'dept_head_approved', 6, 'Approved by department head', '2025-07-29 09:15:24'),
(8, 27, 'applied', 6, 'Leave application submitted for 5 days', '2025-07-29 09:22:27'),
(9, 28, 'applied', 4, 'Leave application submitted for 2 days', '2025-08-07 13:01:49'),
(10, 28, 'section_head_approved', 5, 'Approved by section head', '2025-08-07 13:02:36'),
(11, 28, 'dept_head_approved', 6, 'Approved by department head', '2025-08-07 13:03:12'),
(12, 29, 'applied', 4, 'Leave application submitted for 10 days', '2025-08-08 07:46:07'),
(13, 30, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-08 09:35:07'),
(14, 31, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-08 09:37:30'),
(15, 32, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-08 09:44:50'),
(16, 33, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-08 09:48:09'),
(17, 34, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-08 10:01:51'),
(18, 35, 'applied', 9, 'Leave application submitted for 2 days', '2025-08-08 11:14:20'),
(19, 36, 'applied', 9, 'Leave application submitted for 7 days', '2025-08-10 14:58:29'),
(20, 37, 'applied', 9, 'Leave application submitted for 4 days', '2025-08-10 17:06:42'),
(21, 38, 'applied', 4, 'Leave application submitted for 1 days', '2025-08-10 17:10:25'),
(22, 39, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 17:33:18'),
(23, 40, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 17:36:06'),
(24, 41, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 17:36:19'),
(25, 42, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 17:36:32'),
(26, 43, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 17:36:56'),
(27, 44, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 17:38:32'),
(28, 45, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 18:01:21'),
(29, 46, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 18:01:30'),
(30, 47, 'applied', 9, 'Leave application submitted for 11 days', '2025-08-10 18:03:17'),
(31, 48, 'applied', 9, 'Leave application submitted for 11 days', '2025-08-10 19:23:29'),
(32, 49, 'applied', 9, 'Leave application submitted for 2 days', '2025-08-10 20:17:31'),
(33, 50, 'applied', 9, 'Leave application submitted for 2 days', '2025-08-10 20:31:16'),
(34, 51, 'applied', 9, 'Leave application submitted for 2 days', '2025-08-10 20:31:50'),
(35, 52, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 20:36:33'),
(36, 53, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 20:46:24'),
(37, 54, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 20:47:31'),
(38, 55, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 20:48:53'),
(39, 56, 'applied', 9, 'Leave application submitted for 3 days', '2025-08-10 21:12:40'),
(40, 57, 'applied', 9, 'Leave application submitted for 2 days', '2025-08-10 21:13:15'),
(41, 58, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 21:15:57'),
(42, 59, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 21:17:39'),
(43, 60, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 21:19:16'),
(44, 61, 'applied', 9, 'Leave application submitted for 1 days', '2025-08-10 21:26:03'),
(45, 62, 'applied', 9, 'Leave application submitted for 2 days', '2025-08-10 21:27:00'),
(46, 63, 'applied', 5, 'Leave application submitted for 2 days', '2025-08-10 21:28:39'),
(47, 64, 'applied', 6, 'Leave application submitted for 5 days', '2025-08-10 21:29:57'),
(48, 65, 'applied', 4, 'Leave application submitted for 1 days', '2025-08-11 09:01:55'),
(49, 66, 'applied', 6, 'Leave application submitted for 9 days', '2025-08-25 12:53:41'),
(50, 67, 'applied', 6, 'Leave application submitted for 9 days', '2025-08-25 13:32:56'),
(51, 68, 'applied', 9, 'Leave application submitted for 66 days', '2025-08-25 19:03:55'),
(52, 69, 'applied', 9, 'Leave application submitted for 66 days', '2025-08-25 19:16:48'),
(53, 70, 'applied', 9, 'Leave application submitted for 66 days', '2025-08-25 19:46:36'),
(54, 71, 'applied', 9, 'Leave application submitted for 12 days', '2025-09-03 08:18:44'),
(55, 72, 'applied', 9, 'Leave application submitted for 4 days', '2025-09-03 08:26:00'),
(56, 73, 'applied', 4, 'Leave application submitted for 4 days', '2025-09-03 08:36:02'),
(57, 74, 'applied', 4, 'Leave application submitted for 17 days', '2025-09-03 08:41:15'),
(58, 75, 'applied', 9, 'Leave application submitted for 6 days', '2025-09-03 08:46:51'),
(59, 76, 'applied', 9, 'Leave application submitted for 14 days', '2025-09-03 08:52:14'),
(60, 77, 'applied', 9, 'Leave application submitted for 6 days', '2025-09-03 08:53:56'),
(61, 78, 'applied', 9, 'Leave application submitted for 6 days', '2025-09-03 08:56:31'),
(62, 79, 'applied', 9, 'Leave application submitted for 42 days', '2025-09-03 08:56:58'),
(63, 80, 'applied', 9, 'Leave application submitted for 5 days', '2025-09-03 11:33:43'),
(64, 81, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-03 11:39:16'),
(65, 82, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-03 13:00:47'),
(66, 83, 'applied', 6, 'Leave application submitted for 3 days', '2025-09-03 13:37:52'),
(67, 84, 'applied', 6, 'Leave application submitted for 3 days', '2025-09-03 13:38:52'),
(68, 85, 'applied', 6, 'Leave application submitted for 6 days', '2025-09-03 13:40:09'),
(69, 86, 'applied', 6, 'Leave application submitted for 2 days', '2025-09-03 14:00:58'),
(70, 87, 'applied', 9, 'Leave application submitted for 4 days', '2025-09-03 14:03:22'),
(71, 88, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-03 14:04:20'),
(72, 89, 'applied', 9, 'Leave application submitted for 3 days', '2025-09-04 06:25:33'),
(73, 90, 'applied', 9, 'Leave application submitted for 3 days', '2025-09-04 07:33:48'),
(74, 91, 'applied', 9, 'Leave application submitted for 3 days', '2025-09-04 07:37:01'),
(75, 92, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-04 07:38:34'),
(76, 93, 'applied', 5, 'Leave application submitted for 2 days', '2025-09-04 07:41:28'),
(77, 94, 'applied', 9, 'Leave application submitted for 16 days', '2025-09-04 07:55:46'),
(78, 95, 'applied', 9, 'Leave application submitted for 17 days', '2025-09-04 07:56:28'),
(79, 96, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 07:59:32'),
(80, 97, 'applied', 9, 'Leave application submitted for 4 days', '2025-09-04 08:03:56'),
(81, 98, 'applied', 9, 'Leave application submitted for 4 days', '2025-09-04 08:05:00'),
(82, 99, 'applied', 9, 'Leave application submitted for 6 days', '2025-09-04 08:05:34'),
(83, 100, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 08:06:37'),
(84, 101, 'applied', 9, 'Leave application submitted for 25 days', '2025-09-04 09:07:05'),
(85, 102, 'applied', 9, 'Leave application submitted for 25 days', '2025-09-04 09:07:45'),
(86, 103, 'applied', 9, 'Leave application submitted for 24 days', '2025-09-04 09:08:21'),
(87, 104, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 09:17:03'),
(88, 105, 'applied', 9, 'Leave application submitted for 5 days', '2025-09-04 09:18:13'),
(89, 106, 'applied', 9, 'Leave application submitted for 17 days', '2025-09-04 09:23:45'),
(90, 107, 'applied', 5, 'Leave application submitted for 2 days', '2025-09-04 09:26:33'),
(91, 108, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-04 09:40:02'),
(92, 109, 'applied', 5, 'Leave application submitted for 4 days', '2025-09-04 09:42:13'),
(93, 110, 'applied', 6, 'Leave application submitted for 1 days', '2025-09-04 09:55:07'),
(94, 111, 'applied', 6, 'Leave application submitted for 1 days', '2025-09-04 09:56:12'),
(95, 112, 'applied', 5, 'Leave application submitted for 2 days', '2025-09-04 09:56:56'),
(96, 113, 'applied', 5, 'Leave application submitted for 1 days', '2025-09-04 09:57:58'),
(97, 114, 'applied', 5, 'Leave application submitted for 1 days', '2025-09-04 09:58:52'),
(98, 115, 'applied', 6, 'Leave application submitted for 1 days', '2025-09-04 10:00:56'),
(99, 116, 'applied', 6, 'Leave application submitted for 1 days', '2025-09-04 10:01:35'),
(100, 117, 'applied', 6, 'Leave application submitted for 1 days', '2025-09-04 10:02:31'),
(101, 118, 'applied', 6, 'Leave application submitted for 1 days', '2025-09-04 10:05:36'),
(102, 119, 'applied', 6, 'Leave application submitted for 17 days', '2025-09-04 11:21:04'),
(103, 120, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 11:24:29'),
(104, 121, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 11:25:44'),
(105, 122, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 11:26:17'),
(106, 123, 'applied', 9, 'Leave application submitted for 22 days', '2025-09-04 11:27:32'),
(107, 124, 'applied', 9, 'Leave application submitted for 22 days', '2025-09-04 11:27:35'),
(108, 125, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 11:29:33'),
(109, 126, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 11:47:54'),
(110, 127, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 11:49:00'),
(111, 128, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 11:51:18'),
(112, 129, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 12:09:01'),
(113, 130, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 12:17:11'),
(114, 131, 'applied', 9, 'Leave application submitted for 23 days', '2025-09-04 12:18:28'),
(115, 132, 'applied', 9, 'Leave application submitted for 23 days', '2025-09-04 12:26:09'),
(116, 133, 'applied', 9, 'Leave application submitted for 23 days', '2025-09-04 12:26:20'),
(117, 134, 'applied', 9, 'Leave application submitted for 23 days', '2025-09-04 12:30:31'),
(118, 135, 'applied', 9, 'Leave application submitted for 23 days', '2025-09-04 12:46:53'),
(119, 136, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 12:47:42'),
(120, 137, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 12:48:21'),
(121, 138, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 12:51:22'),
(122, 139, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 13:23:45'),
(123, 140, 'applied', 9, 'Leave application submitted for 1 days', '2025-09-04 13:25:35'),
(124, 141, 'applied', 9, 'Leave application submitted for 18 days', '2025-09-04 13:28:20'),
(125, 142, 'applied', 9, 'Leave application submitted for 4 days', '2025-09-04 13:32:08'),
(126, 143, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-04 13:32:54'),
(127, 144, 'applied', 5, 'Leave application submitted for 24 days', '2025-09-04 13:34:12'),
(128, 145, 'applied', 5, 'Leave application submitted for 19 days with status: approved', '2025-09-04 13:55:14'),
(129, 146, 'applied', 9, 'Leave application submitted for 17 days', '2025-09-05 06:45:16'),
(130, 147, 'applied', 9, 'Leave application submitted for 2 days', '2025-09-05 07:23:41'),
(131, 148, 'applied', 9, 'Leave application submitted for 3 days', '2025-09-05 07:31:48');

-- --------------------------------------------------------

--
-- Table structure for table `leave_transactions`
--

CREATE TABLE `leave_transactions` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `transaction_type` enum('deduction','restoration','adjustment') NOT NULL,
  `details` text DEFAULT NULL COMMENT 'JSON storage of transaction details',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Audit trail for all leave transactions';

--
-- Dumping data for table `leave_transactions`
--

INSERT INTO `leave_transactions` (`id`, `application_id`, `employee_id`, `transaction_date`, `transaction_type`, `details`, `created_at`) VALUES
(8, 22, 135, '2025-07-29 09:44:04', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":4,\"warnings\":\"No available balance. All 4 days will be unpaid.\"}', '2025-07-29 03:44:04'),
(9, 23, 118, '2025-07-29 12:03:59', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":3,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 3 days from Annual Leave.\"}', '2025-07-29 06:03:59'),
(11, 25, 5, '2025-07-29 15:04:27', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":4,\"warnings\":\"No available balance. All 4 days will be unpaid.\"}', '2025-07-29 09:04:27'),
(12, 26, 5, '2025-07-29 15:14:31', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":6,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-07-29 09:14:31'),
(13, 27, 121, '2025-07-29 15:22:27', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":5,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-07-29 09:22:27'),
(14, 28, 118, '2025-08-07 16:01:49', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":2,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-07 13:01:49'),
(15, 29, 118, '2025-08-08 10:46:07', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":10,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-08 07:46:07'),
(16, 30, 112, '2025-08-08 12:35:07', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 3 days will be unpaid.\"}', '2025-08-08 09:35:07'),
(17, 31, 135, '2025-08-08 12:37:30', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 3 days will be unpaid.\"}', '2025-08-08 09:37:30'),
(18, 32, 118, '2025-08-08 12:44:49', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":3,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-08 09:44:49'),
(19, 33, 118, '2025-08-08 12:48:09', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-08 09:48:09'),
(20, 34, 135, '2025-08-08 13:01:51', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 3 days will be unpaid.\"}', '2025-08-08 10:01:51'),
(21, 35, 118, '2025-08-08 14:14:20', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":2,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-08 11:14:20'),
(22, 36, 136, '2025-08-10 17:58:29', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":7,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 7 days will be unpaid.\"}', '2025-08-10 14:58:29'),
(23, 37, 118, '2025-08-10 20:06:42', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":4,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 17:06:42'),
(24, 38, 118, '2025-08-10 20:10:25', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 17:10:25'),
(25, 39, 104, '2025-08-10 20:33:18', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"No available balance. All 3 days will be unpaid.\"}', '2025-08-10 17:33:18'),
(26, 40, 104, '2025-08-10 20:36:06', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"No available balance. All 3 days will be unpaid.\"}', '2025-08-10 17:36:06'),
(27, 41, 104, '2025-08-10 20:36:19', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"No available balance. All 3 days will be unpaid.\"}', '2025-08-10 17:36:19'),
(28, 42, 104, '2025-08-10 20:36:32', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":3,\"warnings\":\"No available balance. All 3 days will be unpaid.\"}', '2025-08-10 17:36:32'),
(29, 43, 118, '2025-08-10 20:36:56', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 17:36:56'),
(30, 44, 118, '2025-08-10 20:38:32', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 17:38:32'),
(31, 45, 118, '2025-08-10 21:01:21', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 18:01:21'),
(32, 46, 118, '2025-08-10 21:01:30', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 18:01:30'),
(33, 47, 118, '2025-08-10 21:03:17', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":11,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 18:03:17'),
(34, 48, 118, '2025-08-10 22:23:29', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":11,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 19:23:29'),
(35, 54, 135, '2025-08-10 23:47:31', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":3,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Maternity Leave balance.\"}', '2025-08-10 20:47:31'),
(36, 55, 135, '2025-08-10 23:48:53', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":3,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Maternity Leave balance.\"}', '2025-08-10 20:48:53'),
(37, 56, 135, '2025-08-11 00:12:40', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":3,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Maternity Leave balance.\"}', '2025-08-10 21:12:40'),
(38, 57, 118, '2025-08-11 00:13:15', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":2,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 21:13:15'),
(39, 58, 135, '2025-08-11 00:15:57', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Compassionate Leave balance.\"}', '2025-08-10 21:15:57'),
(40, 59, 118, '2025-08-11 00:17:39', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Compassionate Leave balance.\"}', '2025-08-10 21:17:39'),
(41, 60, 118, '2025-08-11 00:19:16', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Compassionate Leave balance.\"}', '2025-08-10 21:19:16'),
(42, 61, 118, '2025-08-11 00:26:03', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Compassionate Leave balance.\"}', '2025-08-10 21:26:03'),
(43, 62, 118, '2025-08-11 00:27:00', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":2,\"warnings\":\"No available balance. All 2 days will be unpaid.\"}', '2025-08-10 21:27:00'),
(44, 63, 118, '2025-08-11 00:28:39', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":2,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-08-10 21:28:39'),
(45, 64, 118, '2025-08-11 00:29:57', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":5,\"warnings\":\"No available balance. All 5 days will be unpaid.\"}', '2025-08-10 21:29:57'),
(46, 65, 118, '2025-08-11 12:01:55', 'deduction', '{\"primary_leave_type\":5,\"primary_days\":1,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Study Leave balance.\"}', '2025-08-11 09:01:55'),
(47, 66, 114, '2025-08-25 15:53:41', 'deduction', '{\"primary_leave_type\":5,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":9,\"warnings\":\"Insufficient leave balance. 0 days from Study Leave, 0 days from Annual Leave, 9 days will be unpaid.\"}', '2025-08-25 12:53:41'),
(48, 67, 114, '2025-08-25 16:32:56', 'deduction', '{\"primary_leave_type\":5,\"primary_days\":9,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Study Leave balance.\"}', '2025-08-25 13:32:56'),
(49, 68, 135, '2025-08-25 22:03:55', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":66,\"warnings\":\"No available balance. All 66 days will be unpaid.\"}', '2025-08-25 19:03:55'),
(50, 69, 135, '2025-08-25 22:16:48', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":66,\"warnings\":\"No available balance. All 66 days will be unpaid.\"}', '2025-08-25 19:16:48'),
(51, 70, 135, '2025-08-25 22:46:36', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":66,\"warnings\":\"No available balance. All 66 days will be unpaid.\"}', '2025-08-25 19:46:36'),
(52, 71, 121, '2025-09-03 11:18:44', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":12,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-03 08:18:44'),
(53, 72, 118, '2025-09-03 11:26:00', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":4,\"warnings\":\"No available balance. All 4 days will be unpaid.\"}', '2025-09-03 08:26:00'),
(54, 73, 118, '2025-09-03 11:36:02', 'deduction', '{\"primary_leave_type\":5,\"primary_days\":4,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Study Leave balance.\"}', '2025-09-03 08:36:02'),
(55, 74, 118, '2025-09-03 11:41:15', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":17,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-03 08:41:15'),
(56, 75, 5, '2025-09-03 11:46:51', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":6,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-03 08:46:51'),
(57, 76, 118, '2025-09-03 11:52:14', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":14,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-03 08:52:14'),
(58, 77, 122, '2025-09-03 11:53:56', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":6,\"warnings\":\"No available balance. All 6 days will be unpaid.\"}', '2025-09-03 08:53:56'),
(59, 78, 122, '2025-09-03 11:56:31', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":6,\"warnings\":\"No available balance. All 6 days will be unpaid.\"}', '2025-09-03 08:56:31'),
(60, 79, 114, '2025-09-03 11:56:58', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":42,\"warnings\":\"Requested days (42) exceed maximum allowed per year (30).; Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 42 days will be unpaid.\"}', '2025-09-03 08:56:58'),
(61, 80, 118, '2025-09-03 14:33:43', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":5,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-03 11:33:43'),
(62, 81, 118, '2025-09-03 14:39:16', 'deduction', '{\"primary_leave_type\":7,\"primary_days\":2,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Compassionate Leave balance.\"}', '2025-09-03 11:39:16'),
(63, 82, 121, '2025-09-03 16:00:47', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 2 days to your annual leave upon approval.\"}', '2025-09-03 13:00:47'),
(64, 83, 146, '2025-09-03 16:37:52', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 3 days to your annual leave upon approval.\"}', '2025-09-03 13:37:52'),
(65, 84, 146, '2025-09-03 16:38:52', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 3 days to your annual leave upon approval.\"}', '2025-09-03 13:38:52'),
(66, 85, 121, '2025-09-03 16:40:09', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":null,\"annual_days\":6,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Annual Leave, 6 days from Annual Leave.\"}', '2025-09-03 13:40:09'),
(67, 86, 146, '2025-09-03 17:00:58', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 2 days to your annual leave upon approval.\"}', '2025-09-03 14:00:58'),
(68, 87, 113, '2025-09-03 17:03:22', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":4,\"warnings\":\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 4 days will be unpaid.\"}', '2025-09-03 14:03:22'),
(69, 88, 5, '2025-09-03 17:04:20', 'deduction', '{\"primary_leave_type\":8,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":2,\"warnings\":\"\\u2139\\ufe0f You will be absent for 2 days.\"}', '2025-09-03 14:04:20'),
(70, 89, 5, '2025-09-04 09:25:33', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 3 days to your annual leave upon approval.\"}', '2025-09-04 06:25:33'),
(71, 90, 121, '2025-09-04 10:33:48', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 3 days to your annual leave upon approval.\"}', '2025-09-04 07:33:48'),
(72, 91, 5, '2025-09-04 10:37:01', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":3,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Short Leave, 3 days from Annual Leave.\"}', '2025-09-04 07:37:01'),
(73, 92, 5, '2025-09-04 10:38:34', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":2,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Short Leave, 2 days from Annual Leave.\"}', '2025-09-04 07:38:34'),
(74, 93, 5, '2025-09-04 10:41:28', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":2,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Short Leave, 2 days from Annual Leave.\"}', '2025-09-04 07:41:28'),
(75, 94, 121, '2025-09-04 10:55:46', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":null,\"annual_days\":16,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Annual Leave, 16 days from Annual Leave.\"}', '2025-09-04 07:55:46'),
(76, 95, 5, '2025-09-04 10:56:28', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":null,\"annual_days\":17,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Annual Leave, 17 days from Annual Leave.\"}', '2025-09-04 07:56:28'),
(77, 96, 121, '2025-09-04 10:59:32', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":null,\"annual_days\":18,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Annual Leave, 18 days from Annual Leave.\"}', '2025-09-04 07:59:32'),
(78, 97, 5, '2025-09-04 11:03:56', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":4,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Short Leave, 4 days from Annual Leave.\"}', '2025-09-04 08:03:56'),
(79, 98, 121, '2025-09-04 11:05:00', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":4,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Short Leave, 4 days from Annual Leave.\"}', '2025-09-04 08:05:00'),
(80, 99, 5, '2025-09-04 11:05:34', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 6 days to your annual leave upon approval.\"}', '2025-09-04 08:05:34'),
(81, 100, 121, '2025-09-04 11:06:37', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":null,\"annual_days\":18,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient.  days from Annual Leave, 18 days from Annual Leave.\"}', '2025-09-04 08:06:37'),
(82, 101, 113, '2025-09-04 12:07:05', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":25,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 25 days will be unpaid.\"}', '2025-09-04 09:07:05'),
(83, 102, 5, '2025-09-04 12:07:45', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":24,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 24 days from Annual Leave, 1 days from Annual Leave.\"}', '2025-09-04 09:07:45'),
(84, 103, 121, '2025-09-04 12:08:21', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":24,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 09:08:21'),
(85, 104, 121, '2025-09-04 12:17:03', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":18,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 09:17:03'),
(86, 105, 5, '2025-09-04 12:18:13', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":5,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 5 days from Annual Leave.\"}', '2025-09-04 09:18:13'),
(87, 106, 121, '2025-09-04 12:23:45', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":17,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 09:23:45'),
(88, 107, 5, '2025-09-04 12:26:33', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":2,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 2 days from Annual Leave.\"}', '2025-09-04 09:26:33'),
(89, 108, 5, '2025-09-04 12:40:02', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":2,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 2 days from Annual Leave.\"}', '2025-09-04 09:40:02'),
(90, 109, 5, '2025-09-04 12:42:13', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":4,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 4 days from Annual Leave.\"}', '2025-09-04 09:42:13'),
(91, 110, 5, '2025-09-04 12:55:07', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 09:55:07'),
(92, 111, 121, '2025-09-04 12:56:12', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 09:56:12'),
(93, 112, 5, '2025-09-04 12:56:56', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":2,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 2 days from Annual Leave.\"}', '2025-09-04 09:56:56'),
(94, 113, 5, '2025-09-04 12:57:58', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 09:57:58'),
(95, 114, 118, '2025-09-04 12:58:52', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 09:58:52'),
(96, 115, 121, '2025-09-04 13:00:56', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 10:00:56'),
(97, 116, 5, '2025-09-04 13:01:35', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 10:01:35'),
(98, 117, 118, '2025-09-04 13:02:31', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 10:02:31'),
(99, 118, 5, '2025-09-04 13:05:36', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":1,\"unpaid_days\":0,\"warnings\":\"Primary balance insufficient. 0 days from Short Leave, 1 days from Annual Leave.\"}', '2025-09-04 10:05:36'),
(100, 119, 5, '2025-09-04 14:21:04', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":17,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 11:21:04'),
(101, 120, 135, '2025-09-04 14:24:29', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":18,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"}', '2025-09-04 11:24:29'),
(102, 121, 112, '2025-09-04 14:25:44', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":18,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"}', '2025-09-04 11:25:44'),
(103, 122, 5, '2025-09-04 14:26:17', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":18,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 11:26:17'),
(104, 123, 121, '2025-09-04 14:27:32', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":22,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 11:27:32'),
(105, 124, 121, '2025-09-04 14:27:35', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":22,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 11:27:35'),
(106, 125, 118, '2025-09-04 14:29:33', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":18,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 11:29:33'),
(107, 126, 135, '2025-09-04 14:47:54', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"Insufficient leave balance. 0 days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"}', '2025-09-04 11:47:54'),
(108, 127, 135, '2025-09-04 14:49:00', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"}', '2025-09-04 11:49:00'),
(109, 128, 135, '2025-09-04 14:51:18', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"}', '2025-09-04 11:51:18'),
(110, 129, 135, '2025-09-04 15:09:01', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 1 days to your annual leave upon approval.\"}', '2025-09-04 12:09:01'),
(111, 130, 135, '2025-09-04 15:17:11', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":18,\"warnings\":\"Insufficient leave balance.  days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"}', '2025-09-04 12:17:11'),
(112, 131, 135, '2025-09-04 15:18:28', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":23,\"warnings\":\"No available balance. All 23 days will be unpaid.\"}', '2025-09-04 12:18:28'),
(113, 132, 135, '2025-09-04 15:26:09', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":23,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Maternity Leave balance.\"}', '2025-09-04 12:26:09'),
(114, 133, 135, '2025-09-04 15:26:20', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":23,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Maternity Leave balance.\"}', '2025-09-04 12:26:20'),
(115, 134, 135, '2025-09-04 15:30:31', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":23,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Maternity Leave balance.\"}', '2025-09-04 12:30:31'),
(116, 135, 135, '2025-09-04 15:46:53', 'deduction', '{\"primary_leave_type\":3,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":23,\"warnings\":\"No available balance. All 23 days will be unpaid.\"}', '2025-09-04 12:46:53'),
(117, 136, 135, '2025-09-04 15:47:42', 'deduction', '{\"primary_leave_type\":9,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"\\u2705 This will add 1 days to your annual leave upon approval.\"}', '2025-09-04 12:47:42'),
(118, 137, 135, '2025-09-04 15:48:21', 'deduction', '{\"primary_leave_type\":8,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"\\u2139\\ufe0f You will be absent for 1 days.\"}', '2025-09-04 12:48:21'),
(119, 138, 135, '2025-09-04 15:51:22', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"}', '2025-09-04 12:51:22'),
(120, 139, 135, '2025-09-04 16:23:45', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":null,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"Insufficient leave balance.  days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"}', '2025-09-04 13:23:45'),
(121, 140, 135, '2025-09-04 16:25:35', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":1,\"warnings\":\"Insufficient leave balance. 0 days from Short Leave, 0 days from Annual Leave, 1 days will be unpaid.\"}', '2025-09-04 13:25:35'),
(122, 141, 135, '2025-09-04 16:28:20', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":18,\"warnings\":\"Insufficient leave balance. 0 days from Annual Leave, 0 days from Annual Leave, 18 days will be unpaid.\"}', '2025-09-04 13:28:20'),
(123, 142, 135, '2025-09-04 16:32:08', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":4,\"warnings\":\"Insufficient leave balance. 0 days from Short Leave, 0 days from Annual Leave, 4 days will be unpaid.\"}', '2025-09-04 13:32:08'),
(124, 143, 5, '2025-09-04 16:32:54', 'deduction', '{\"primary_leave_type\":8,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":2,\"warnings\":\"\\u2139\\ufe0f You will be absent for 2 days.\"}', '2025-09-04 13:32:54'),
(125, 144, 5, '2025-09-04 16:34:12', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":24,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 13:34:12'),
(126, 145, 5, '2025-09-04 16:55:14', 'deduction', '{\"primary_leave_type\":1,\"primary_days\":19,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"Will be deducted from Annual Leave balance.\"}', '2025-09-04 13:55:14'),
(127, 146, 135, '2025-09-05 09:45:16', 'deduction', '{\"primary_leave_type\":6,\"primary_days\":17,\"annual_days\":17,\"unpaid_days\":0,\"warnings\":\"17 days from Short Leave (balance may go negative).; Primary balance insufficient. 17 days from Short Leave.\"}', '2025-09-05 06:45:16'),
(128, 147, 135, '2025-09-05 10:23:41', 'deduction', '{\"primary_leave_type\":8,\"primary_days\":0,\"annual_days\":0,\"unpaid_days\":2,\"warnings\":\"\\u2139\\ufe0f You will be absent for 2 days.\"}', '2025-09-05 07:23:41'),
(129, 148, 135, '2025-09-05 10:31:48', 'deduction', '{\"primary_leave_type\":2,\"primary_days\":3,\"annual_days\":0,\"unpaid_days\":0,\"warnings\":\"3 days from Sick Leave (balance may go negative).\"}', '2025-09-05 07:31:48');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_days_per_year` int(11) DEFAULT NULL,
  `counts_weekends` tinyint(1) DEFAULT 0,
  `deducted_from_annual` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `description`, `max_days_per_year`, `counts_weekends`, `deducted_from_annual`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Annual Leave', 'Regular annual vacation leave', 30, 0, 1, 1, '2025-07-21 07:55:35', '2025-07-21 07:55:35'),
(2, 'Sick Leave', 'Medical leave for illness', NULL, 0, 0, 1, '2025-07-21 07:55:35', '2025-07-21 07:55:35'),
(3, 'Maternity Leave', 'Maternity leave for female employees', 120, 1, 0, 1, '2025-07-21 07:55:35', '2025-07-28 04:32:29'),
(4, 'Paternity Leave', 'Paternity leave for male employees', 14, 0, 0, 1, '2025-07-21 07:55:35', '2025-07-21 07:55:35'),
(5, 'Study Leave', 'Educational or training leave', 10, 0, 1, 1, '2025-07-21 07:55:35', '2025-07-28 04:32:29'),
(6, 'Short Leave', 'Short duration leave (half day, few hours)', NULL, 0, 1, 1, '2025-07-21 07:55:35', '2025-07-21 07:55:35'),
(7, 'Compassionate Leave', 'Emergency or bereavement leave', 10, 0, 0, 1, '2025-07-21 07:55:35', '2025-07-28 04:32:29'),
(8, 'leave of absence', 'selected to perform official duties', NULL, 0, 0, 1, '2025-09-03 09:30:20', '2025-09-03 09:30:20'),
(9, 'claim a day', 'Claim unused days', 0, 0, 1, 1, '2025-09-03 09:31:48', '2026-06-03 11:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `objectives`
--

CREATE TABLE `objectives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `strategic_plan_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `objectives`
--

INSERT INTO `objectives` (`id`, `strategic_plan_id`, `name`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'Customer satisfaction ', '2025-07-01', '2026-06-30', '2025-09-05 11:18:12', '2025-09-05 11:18:12');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `salary` decimal(12,2) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_id` int(11) DEFAULT NULL,
  `SHA_number` varchar(50) DEFAULT NULL,
  `KRA_pin` varchar(20) DEFAULT NULL,
  `NSSF` varchar(50) DEFAULT NULL,
  `Gross_pay` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) DEFAULT NULL,
  `job_group` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `emp_id`, `employment_type`, `status`, `salary`, `bank_account`, `bank_id`, `SHA_number`, `KRA_pin`, `NSSF`, `Gross_pay`, `net_pay`, `job_group`) VALUES
(2, 145, 'permanent', 'active', 65000.00, '1029987388485', 4, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 146, 'contract', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 147, 'permanent', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 148, 'contract', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 149, 'permanent', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10'),
(7, 150, 'permanent', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `period_name` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pay_date` date NOT NULL,
  `frequency` varchar(20) DEFAULT 'Monthly',
  `status` varchar(20) DEFAULT 'Draft',
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_periods`
--

INSERT INTO `payroll_periods` (`id`, `period_name`, `start_date`, `end_date`, `pay_date`, `frequency`, `status`, `is_locked`, `created_at`, `updated_at`) VALUES
(1, 'JULY2025', '2025-07-01', '2025-07-31', '2025-07-31', 'Monthly', 'Draft', 1, '2025-08-26 12:33:52', '2025-08-26 13:02:48');

-- --------------------------------------------------------

--
-- Table structure for table `performance_indicators`
--

CREATE TABLE `performance_indicators` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `max_score` int(11) NOT NULL DEFAULT 5,
  `role` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_indicators`
--

INSERT INTO `performance_indicators` (`id`, `name`, `description`, `max_score`, `role`, `department_id`, `is_active`, `created_at`, `updated_at`, `section_id`) VALUES
(1, 'Quality of Work', 'Accuracy, thoroughness, and attention to detail in work output', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-12 19:49:48', NULL),
(2, 'Productivity', 'Efficiency in completing tasks and meeting deadlines', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-11 11:29:14', NULL),
(3, 'Communication Skills', 'Effectiveness in verbal and written communication', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-15 08:38:43', NULL),
(4, 'Teamwork & Collaboration', 'Ability to work effectively with colleagues and contribute to team goals', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-11 11:29:14', NULL),
(5, 'Initiative & Innovation', 'Proactive approach and creative problem-solving abilities', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-11 11:29:14', NULL),
(6, 'Professional Development', 'Commitment to learning and skill improvement', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-11 11:29:14', NULL),
(7, 'Attendance & Punctuality', 'Reliability in attendance and meeting scheduled commitments', 5, NULL, NULL, 1, '2025-08-11 11:29:14', '2025-08-11 11:29:14', NULL),
(9, 'Customer Service Excellence', 'Quality of customer interaction and problem resolution', 5, NULL, NULL, 1, '2025-08-15 07:47:04', '2025-08-15 07:47:04', NULL),
(10, 'Technical Competency', 'Mastery of job-related technical skills and knowledge', 5, NULL, NULL, 1, '2025-08-15 07:47:04', '2025-08-15 07:47:04', NULL),
(11, 'Leadership Potential', 'Demonstration of leadership qualities and mentoring abilities', 5, NULL, NULL, 1, '2025-08-15 07:47:04', '2025-08-15 07:47:04', NULL),
(12, 'Adaptability', 'Flexibility in handling change and new challenges', 5, NULL, NULL, 1, '2025-08-15 07:47:04', '2025-08-15 07:47:04', NULL),
(13, 'Workplan', 'Ensure departmnta goals are aligned with the organizational goals', 5, 'officer', 2, 1, '2025-08-15 08:25:00', '2025-08-21 12:06:42', 4),
(14, 'compliance', 'Regulatory:Ensure 10% compliance with local and legistlative bodies', 5, 'hr_manager', 2, 1, '2025-08-15 08:25:56', '2025-08-18 20:42:31', 4),
(15, 'strategies formulated', 'Enhanced employer branding:Formulate strategies on enhancing employers brand', 5, NULL, NULL, 1, '2025-08-15 08:28:27', '2025-08-15 08:28:27', 1),
(16, 'Workplans', 'Ensure departmentalk goals are aligned with the organizatinal goals', 5, 'dept_head', 2, 1, '2025-08-15 09:41:22', '2025-08-15 09:41:22', NULL),
(17, 'Field Reports Timeliness', 'Submit reports within set deadlines', 5, 'officer', 2, 1, '2025-08-22 12:04:31', '2025-08-22 12:04:31', 4),
(18, 'Client Satisfaction', 'Handle client feedback and ensure satisfaction', 5, 'officer', 2, 1, '2025-08-22 12:04:31', '2025-08-22 12:04:31', 4),
(19, 'Team Oversight', 'Manage and oversee team performance', 5, 'section_head', 2, 1, '2025-08-22 12:05:01', '2025-08-22 12:05:01', 4),
(20, 'Section Planning', 'Develop and review sectional workplans', 5, 'section_head', 2, 1, '2025-08-22 12:05:01', '2025-08-22 12:05:01', 4),
(21, 'Department Performance Review', 'Monitor department KPIs', 5, 'dept_head', 2, 1, '2025-08-22 12:05:21', '2025-08-22 12:05:21', NULL),
(22, 'Strategic Planning', 'Lead the creation of strategic goals', 5, 'dept_head', 2, 1, '2025-08-22 12:05:21', '2025-08-22 12:05:21', NULL),
(23, 'Recruitment Efficiency', 'Complete hiring processes timely', 5, 'hr_manager', 2, 1, '2025-08-22 12:05:45', '2025-08-22 12:05:45', NULL),
(24, 'Training Programs', 'Implement employee development programs', 5, 'hr_manager', 2, 1, '2025-08-22 12:05:45', '2025-08-22 12:05:45', NULL),
(25, 'Task Completion', 'Complete assigned tasks within deadlines', 5, 'officer', 2, 1, '2025-08-22 12:06:28', '2025-08-22 12:06:28', 4),
(26, 'Field Accuracy', 'Ensure accuracy in field data collection', 5, 'officer', 2, 1, '2025-08-22 12:06:28', '2025-08-22 12:06:28', 4),
(27, 'Community Engagement', 'Maintain positive relations with local communities', 5, 'officer', 2, 1, '2025-08-22 12:06:28', '2025-08-22 12:06:28', 4),
(28, 'Incident Reporting', 'Timely reporting of issues and incidents', 5, 'officer', 2, 1, '2025-08-22 12:06:28', '2025-08-22 12:06:28', 4),
(29, 'Resource Management', 'Efficient use of resources during field operations', 5, 'officer', 2, 1, '2025-08-22 12:06:28', '2025-08-22 12:06:28', 4),
(30, 'Staff Supervision', 'Ensure proper supervision of team members', 5, 'section_head', 2, 1, '2025-08-22 12:06:47', '2025-08-22 12:06:47', 4),
(31, 'Section Planning', 'Create and manage sectional plans effectively', 5, 'section_head', 2, 1, '2025-08-22 12:06:47', '2025-08-22 12:06:47', 4),
(32, 'Budget Oversight', 'Track and manage section budgets', 5, 'section_head', 2, 1, '2025-08-22 12:06:47', '2025-08-22 12:06:47', 4),
(33, 'Compliance Checks', 'Ensure policies and procedures are followed', 5, 'section_head', 2, 1, '2025-08-22 12:06:47', '2025-08-22 12:06:47', 4),
(34, 'Quarterly Reports', 'Submit accurate and timely section reports', 5, 'section_head', 2, 1, '2025-08-22 12:06:47', '2025-08-22 12:06:47', 4),
(35, 'Department Planning', 'Develop annual and quarterly department plans', 5, 'dept_head', 2, 1, '2025-08-22 12:07:08', '2025-08-22 12:07:08', NULL),
(36, 'Policy Implementation', 'Ensure department policies are followed', 5, 'dept_head', 2, 1, '2025-08-22 12:07:08', '2025-08-22 12:07:08', NULL),
(37, 'Performance Reviews', 'Oversee performance evaluation across department', 5, 'dept_head', 2, 1, '2025-08-22 12:07:08', '2025-08-22 12:07:08', NULL),
(38, 'Cross-Team Coordination', 'Coordinate efforts across different teams', 5, 'dept_head', 2, 1, '2025-08-22 12:07:08', '2025-08-22 12:07:08', NULL),
(39, 'Budget Planning', 'Prepare and review department budgets', 5, 'dept_head', 2, 1, '2025-08-22 12:07:08', '2025-08-22 12:07:08', NULL),
(40, 'Staff Onboarding', 'Manage effective onboarding processes', 5, 'hr_manager', 2, 1, '2025-08-22 12:07:32', '2025-08-22 12:07:32', NULL),
(41, 'Performance Metrics', 'Define KPIs for different roles', 5, 'hr_manager', 2, 1, '2025-08-22 12:07:32', '2025-08-22 12:07:32', NULL),
(42, 'Training & Development', 'Organize staff training sessions', 5, 'hr_manager', 2, 1, '2025-08-22 12:07:32', '2025-08-22 12:07:32', NULL),
(43, 'Employee Satisfaction', 'Measure and improve staff satisfaction', 5, 'hr_manager', 2, 1, '2025-08-22 12:07:32', '2025-08-22 12:07:32', NULL),
(44, 'Leave Management', 'Track leave applications and approvals', 5, 'hr_manager', 2, 1, '2025-08-22 12:07:32', '2025-08-22 12:07:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `salary_bands`
--

CREATE TABLE `salary_bands` (
  `scale_id` varchar(10) NOT NULL,
  `min_salary` decimal(10,2) DEFAULT NULL,
  `max_salary` decimal(10,2) DEFAULT NULL,
  `house_allowance` decimal(10,2) DEFAULT NULL,
  `commuter_allowance` decimal(10,2) DEFAULT NULL,
  `leave_allowance` decimal(10,2) DEFAULT NULL,
  `Dirty_allowance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_bands`
--

INSERT INTO `salary_bands` (`scale_id`, `min_salary`, `max_salary`, `house_allowance`, `commuter_allowance`, `leave_allowance`, `Dirty_allowance`) VALUES
('1', 17895.00, 35608.00, 8518.00, 4050.00, NULL, NULL),
('10', 307718.00, 637867.00, 58080.00, 0.00, NULL, NULL),
('2', 20844.00, 39673.00, 10261.00, 4050.00, NULL, NULL),
('3', 21787.00, 41468.00, 10842.00, 5200.00, NULL, NULL),
('3A', 22987.00, 43752.00, 10842.00, 5200.00, NULL, NULL),
('3B', 24187.00, 48128.00, 10842.00, 5200.00, NULL, NULL),
('3C', 27412.00, 58174.00, 10842.00, 5200.00, NULL, NULL),
('4', 27509.00, 62119.00, 13552.00, 5200.00, NULL, NULL),
('5', 31387.00, 64664.00, 13552.00, 6500.00, NULL, NULL),
('6', 38182.00, 74132.00, 19360.00, 6500.00, NULL, NULL),
('7', 58097.00, 103819.00, 27104.00, 8000.00, NULL, NULL),
('8', 80413.00, 160009.00, 32912.00, 11000.00, NULL, NULL),
('9', 118178.00, 397383.00, 38720.00, 20000.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `name`, `description`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 'Human Resources', 'Employee management and policies', 1, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(2, 'Finance', 'Financial planning and accounting', 1, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(3, 'Sales', 'Direct sales operations', 2, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(4, 'Marketing', 'Brand promotion and advertising', 2, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(5, 'Customer Service', 'Customer support and relations', 2, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(6, 'Software Development', 'Application and system development', 3, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(7, 'IT Support', 'Technical support and maintenance', 3, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(8, 'Network Operations', 'Network infrastructure management', 3, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(9, 'Legal Affairs', 'Legal compliance and contracts', 4, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(10, 'Public Relations', 'Media and public communications', 4, '2025-07-19 06:04:13', '2025-07-19 06:04:13'),
(11, 'Water Supply', 'Water distribution and supply management', 5, '2025-07-19 06:04:13', '2025-07-19 06:04:13');

-- --------------------------------------------------------

--
-- Table structure for table `strategic_plan`
--

CREATE TABLE `strategic_plan` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `strategic_plan`
--

INSERT INTO `strategic_plan` (`id`, `name`, `start_date`, `end_date`, `created_at`, `updated_at`, `image_path`) VALUES
(1, '2025-2030', '2025-01-01', '2030-03-11', '2025-09-05 11:16:47', '2025-09-05 11:17:03', NULL),
(2, 'strategic plan 2025-2030', '2025-01-01', '2031-03-12', '2025-09-05 12:19:43', '2025-09-05 12:19:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `strategies`
--

CREATE TABLE `strategies` (
  `id` int(11) NOT NULL,
  `strategic_plan_id` int(11) NOT NULL,
  `objective_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `strategies`
--

INSERT INTO `strategies` (`id`, `strategic_plan_id`, `objective_id`, `name`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'To increase  customer  water  connections  from 25,000  to 30,000 by  the year 2030 ', '2025-01-01', '2030-12-31', '2025-09-05 12:45:09', '2025-09-05 12:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `gender` varchar(10) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('bod_chairman','super_admin','hr_manager','dept_head','section_head','manager','officer','managing_director') DEFAULT 'officer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `employee_id` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `first_name`, `last_name`, `gender`, `password`, `role`, `phone`, `address`, `profile_image_url`, `created_at`, `updated_at`, `employee_id`) VALUES
(1, 'admin@company.com', 'Admin', 'User', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', NULL, NULL, NULL, '2025-07-19 06:04:12', '2025-07-22 07:16:46', NULL),
(2, 'depthead@company.com', 'Department', 'Head', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dept_head', NULL, NULL, NULL, '2025-07-19 06:04:13', '2025-07-22 07:16:57', NULL),
(3, 'hr@company.com', 'HR', 'Manager', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr_manager', NULL, NULL, NULL, '2025-07-19 06:04:12', '2025-07-22 09:59:13', '118'),
(4, 'mwangikabii@gmail.com', 'Mwangi', 'Kabii', '', '$2y$10$/J.oUW3wIME./WaSRBb1G.m1/nBPQGtPZsIpvaYSoP8Tlri5RXtSS', 'officer', '0790765431', 'Kiambu', NULL, '2025-07-22 07:23:07', '2025-08-21 12:24:26', 'EMP008'),
(5, 'josephine@gmail.com', 'Josephine', 'Kangara', '', '$2y$10$c9v.Xk94usNFLIw2zveKJeZ1bdhdHNw14480WuyCpFwH19Ap3lYQW', 'section_head', '0768525478', 'Kiambu', NULL, '2025-07-22 10:20:00', '2025-07-22 10:20:00', 'EMP009'),
(6, 'hezronnjoro@gmail.com', 'Hezron', 'Njoroge', '', '$2y$10$0VLFP04KxABJW3pO6yi2Pe4GSZ2LeKZDMXWMZnn.bYBDwcAPi6GrO', 'dept_head', '0786542982', 'Mukurweini', NULL, '2025-07-22 10:32:58', '2025-07-22 10:32:58', 'EMP10'),
(7, 'will@gmail.com', 'will', 'smith', '', '$2y$10$3gQ6ENYU8s6P/hWaizpoeOjuUGsJOBmuviaMlIXbZ/HcmJas7Z63y', 'officer', '0786542982', 'Mukurweini', NULL, '2025-07-23 16:16:36', '2025-08-21 12:24:37', '150'),
(8, 'hash@gmail.com', 'hash', 'pappy', '', '$2y$10$dESswOfiUCrrw.n5j5MtZOubdEpDglzhsg5sgC1Iue4KQzu2nWe7W', 'section_head', '0707070708', '1050', NULL, '2025-07-23 16:45:44', '2025-07-23 16:45:44', '161'),
(9, 'lucy@gmail.com', 'John', 'Doe', 'female', '$2y$10$em8thbHRaO/1b0.W.HoG6uKL435rDnDbEsHJzxP5XdYC/wb8O2a9m', 'hr_manager', '0707070708', 'Kiambu', NULL, '2025-07-24 18:24:31', '2025-08-29 08:50:50', 'EMP020'),
(10, 'martinmwangi14@gmail.com', 'Mwangi', 'Mwangi', '', '$2y$10$Rf6GexZC1nDDg1gD73WpIeDIeJOmX8QI56pmfH0NzavJfpNfbmYUG', 'officer', '073354566645', 'Kihoya', NULL, '2025-07-25 05:03:20', '2025-08-21 12:24:57', 'EMP015'),
(11, 'karenjuduncan70@gmail.com', 'Dancan', 'karenju', '', '$2y$10$mYYnwoy3bAbecDsaVwopbORSa1P2piRb/Iir/crANtRJFpbnkWekK', 'super_admin', '0112554479', 'Kiambu', NULL, '2025-07-29 10:09:46', '2025-07-29 10:09:46', 'EMP019'),
(12, 'petermaina19@gmail.com', 'Peter', 'Maina', 'male', '$2y$10$YPHoJk/LlGUo5jWp6j9Ud.SIR64MWZIjWzWo/aL6Kv4zvnG5HHyMO', '', '0707454717', 'Muranga town', NULL, '2025-08-26 07:50:42', '2025-08-26 07:50:42', 'EMP021'),
(13, 'constatine12@gmail.com', 'Constatine', 'Andrew', 'male', '$2y$10$PHtIm7.oq8st1e1p4gcYKeA6gSGJPROSvaZdf38s4xwjqcQbiPhMC', '', '0790765431', 'Kiambu', NULL, '2025-08-26 07:55:26', '2025-08-26 07:55:26', 'EMP022'),
(14, 'charlo17@gmail.com', 'Charles', 'Gatambi', 'male', '$2y$10$RM9VYqD8LwMTwilsZd2eVuQTJcgYo.njdCaeYabaXOK9ocwfHCcgy', '', '0768525478', 'Kiambu', NULL, '2025-08-26 08:00:13', '2025-08-26 08:00:13', 'EMP023'),
(15, 'joe7@gmail.com', 'Caleb', 'Joes', 'male', '$2y$10$y3mpYiCsg1hJ1tlgOVAahuYLlBaOQpglCQe0QZjGQRKqK.S1Iz6JK', '', '0768525478', 'Kiambu', NULL, '2025-08-26 08:03:58', '2025-08-26 08:03:58', 'EMP024'),
(16, 'stanley@gmail.com', 'Stanley', 'Mwaura', 'male', '$2y$10$45cucQdyU44D8Ot./02eoOncKjAmk5QGkLdR3PdBBTtAtQe9CAXc2', 'super_admin', '0707699054', 'Kirigiti', NULL, '2025-09-04 08:30:52', '2025-09-04 08:30:52', 'EMP025'),
(17, 'judy@gmail.com', 'Judy', 'Wawira', 'female', '$2y$10$o59WMXETOf.dVUIMSvKw1OsLC0JVF9vgs53r35UYmb4pMRZO1RmGG', 'managing_director', '0707699054', 'Thika', NULL, '2025-09-04 08:44:55', '2025-09-04 08:44:55', 'EMP026');

-- --------------------------------------------------------

--
-- Structure for view `completed_appraisals_view`
--
DROP TABLE IF EXISTS `completed_appraisals_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `completed_appraisals_view`  AS SELECT `ea`.`id` AS `id`, `ea`.`employee_id` AS `employee_id`, `ea`.`appraiser_id` AS `appraiser_id`, `ea`.`appraisal_cycle_id` AS `appraisal_cycle_id`, `ea`.`employee_comment` AS `employee_comment`, `ea`.`employee_comment_date` AS `employee_comment_date`, `ea`.`submitted_at` AS `submitted_at`, `ea`.`status` AS `status`, `ea`.`created_at` AS `created_at`, `ea`.`updated_at` AS `updated_at`, `ac`.`name` AS `cycle_name`, `ac`.`start_date` AS `start_date`, `ac`.`end_date` AS `end_date`, `e`.`first_name` AS `first_name`, `e`.`last_name` AS `last_name`, `e`.`employee_id` AS `emp_id`, `e`.`designation` AS `designation`, `d`.`name` AS `department_name`, `s`.`name` AS `section_name`, `e_appraiser`.`first_name` AS `appraiser_first_name`, `e_appraiser`.`last_name` AS `appraiser_last_name`, CASE WHEN month(`ac`.`start_date`) in (1,2,3) THEN 'Q1' WHEN month(`ac`.`start_date`) in (4,5,6) THEN 'Q2' WHEN month(`ac`.`start_date`) in (7,8,9) THEN 'Q3' WHEN month(`ac`.`start_date`) in (10,11,12) THEN 'Q4' ELSE 'Unknown' END AS `quarter`, (select avg(`as_`.`score` / `pi`.`max_score` * 100) from (`appraisal_scores` `as_` join `performance_indicators` `pi` on(`as_`.`performance_indicator_id` = `pi`.`id`)) where `as_`.`employee_appraisal_id` = `ea`.`id`) AS `average_score_percentage` FROM (((((`employee_appraisals` `ea` join `employees` `e` on(`ea`.`employee_id` = `e`.`id`)) left join `departments` `d` on(`e`.`department_id` = `d`.`id`)) left join `sections` `s` on(`e`.`section_id` = `s`.`id`)) join `appraisal_cycles` `ac` on(`ea`.`appraisal_cycle_id` = `ac`.`id`)) join `employees` `e_appraiser` on(`ea`.`appraiser_id` = `e_appraiser`.`id`)) WHERE `ea`.`status` = 'submitted' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowance_types`
--
ALTER TABLE `allowance_types`
  ADD PRIMARY KEY (`allowance_type_id`);

--
-- Indexes for table `appraisal_cycles`
--
ALTER TABLE `appraisal_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appraisal_cycles_dates` (`start_date`,`end_date`);

--
-- Indexes for table `appraisal_scores`
--
ALTER TABLE `appraisal_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_appraisal_indicator` (`employee_appraisal_id`,`performance_indicator_id`),
  ADD KEY `employee_appraisal_id` (`employee_appraisal_id`),
  ADD KEY `performance_indicator_id` (`performance_indicator_id`);

--
-- Indexes for table `appraisal_summary_cache`
--
ALTER TABLE `appraisal_summary_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cycle_quarter` (`appraisal_cycle_id`,`quarter`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`bank_id`);

--
-- Indexes for table `deduction_formulas`
--
ALTER TABLE `deduction_formulas`
  ADD PRIMARY KEY (`formula_id`),
  ADD KEY `deduction_type_id` (`deduction_type_id`);

--
-- Indexes for table `deduction_types`
--
ALTER TABLE `deduction_types`
  ADD PRIMARY KEY (`deduction_type_id`),
  ADD UNIQUE KEY `unique_type_name` (`type_name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `idx_employees_dept_section` (`department_id`,`section_id`);

--
-- Indexes for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  ADD PRIMARY KEY (`allowance_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `allowance_type_id` (`allowance_type_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `period_id` (`period_id`);

--
-- Indexes for table `employee_appraisals`
--
ALTER TABLE `employee_appraisals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_cycle` (`employee_id`,`appraisal_cycle_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `appraiser_id` (`appraiser_id`),
  ADD KEY `appraisal_cycle_id` (`appraisal_cycle_id`),
  ADD KEY `idx_employee_appraisals_status` (`status`),
  ADD KEY `idx_employee_appraisals_submitted_at` (`submitted_at`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`deduction_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `deduction_type_id` (`deduction_type_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_leave_year` (`employee_id`,`leave_type_id`,`financial_year_id`),
  ADD KEY `financial_year_id` (`financial_year_id`),
  ADD KEY `employee_leave_balances_ibfk_1` (`leave_type_id`);

--
-- Indexes for table `financial_years`
--
ALTER TABLE `financial_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_year` (`start_date`,`end_date`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `leave_type_id` (`leave_type_id`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_year` (`employee_id`,`financial_year`),
  ADD KEY `fk_leave_type` (`leave_type_id`);

--
-- Indexes for table `leave_history`
--
ALTER TABLE `leave_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_transactions`
--
ALTER TABLE `leave_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_transaction_date` (`transaction_date`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`user_id`) USING BTREE;

--
-- Indexes for table `objectives`
--
ALTER TABLE `objectives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `strategic_plan_id` (`strategic_plan_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `fk_bank_id` (`bank_id`);

--
-- Indexes for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `performance_indicators`
--
ALTER TABLE `performance_indicators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_bands`
--
ALTER TABLE `salary_bands`
  ADD PRIMARY KEY (`scale_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `strategic_plan`
--
ALTER TABLE `strategic_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `strategies`
--
ALTER TABLE `strategies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `strategic_plan_id` (`strategic_plan_id`),
  ADD KEY `objective_id` (`objective_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allowance_types`
--
ALTER TABLE `allowance_types`
  MODIFY `allowance_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `appraisal_cycles`
--
ALTER TABLE `appraisal_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `appraisal_scores`
--
ALTER TABLE `appraisal_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1897;

--
-- AUTO_INCREMENT for table `appraisal_summary_cache`
--
ALTER TABLE `appraisal_summary_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `deduction_formulas`
--
ALTER TABLE `deduction_formulas`
  MODIFY `formula_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deduction_types`
--
ALTER TABLE `deduction_types`
  MODIFY `deduction_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  MODIFY `allowance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employee_appraisals`
--
ALTER TABLE `employee_appraisals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `deduction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1009;

--
-- AUTO_INCREMENT for table `financial_years`
--
ALTER TABLE `financial_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_history`
--
ALTER TABLE `leave_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `leave_transactions`
--
ALTER TABLE `leave_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `objectives`
--
ALTER TABLE `objectives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `performance_indicators`
--
ALTER TABLE `performance_indicators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `strategic_plan`
--
ALTER TABLE `strategic_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `strategies`
--
ALTER TABLE `strategies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appraisal_scores`
--
ALTER TABLE `appraisal_scores`
  ADD CONSTRAINT `appraisal_scores_ibfk_1` FOREIGN KEY (`employee_appraisal_id`) REFERENCES `employee_appraisals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appraisal_scores_ibfk_2` FOREIGN KEY (`performance_indicator_id`) REFERENCES `performance_indicators` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appraisal_summary_cache`
--
ALTER TABLE `appraisal_summary_cache`
  ADD CONSTRAINT `appraisal_summary_cache_ibfk_1` FOREIGN KEY (`appraisal_cycle_id`) REFERENCES `appraisal_cycles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deduction_formulas`
--
ALTER TABLE `deduction_formulas`
  ADD CONSTRAINT `deduction_formulas_ibfk_1` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`deduction_type_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  ADD CONSTRAINT `employee_allowances_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_allowances_ibfk_2` FOREIGN KEY (`allowance_type_id`) REFERENCES `allowance_types` (`allowance_type_id`),
  ADD CONSTRAINT `employee_allowances_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employee_appraisals`
--
ALTER TABLE `employee_appraisals`
  ADD CONSTRAINT `employee_appraisals_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_appraisals_ibfk_2` FOREIGN KEY (`appraiser_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_appraisals_ibfk_3` FOREIGN KEY (`appraisal_cycle_id`) REFERENCES `appraisal_cycles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD CONSTRAINT `employee_deductions_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_deductions_ibfk_2` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`deduction_type_id`),
  ADD CONSTRAINT `employee_deductions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `employee_documents_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_leave_balances`
--
ALTER TABLE `employee_leave_balances`
  ADD CONSTRAINT `employee_leave_balances_ibfk_1` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `leave_applications_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_applications_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`);

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `fk_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_transactions`
--
ALTER TABLE `leave_transactions`
  ADD CONSTRAINT `leave_transactions_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_transactions_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `fk_bank_id` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`bank_id`),
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `strategies`
--
ALTER TABLE `strategies`
  ADD CONSTRAINT `strategies_ibfk_1` FOREIGN KEY (`strategic_plan_id`) REFERENCES `strategic_plan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `strategies_ibfk_2` FOREIGN KEY (`objective_id`) REFERENCES `objectives` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
