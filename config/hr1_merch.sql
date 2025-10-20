-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Oct 17, 2025 at 09:13 PM
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
-- Database: `hr1_merch`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `source` varchar(80) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `skills_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills_json`)),
  `status` enum('new','screening','shortlisted','interview','hired','rejected') NOT NULL DEFAULT 'new',
  `score` tinyint(3) UNSIGNED DEFAULT NULL,
  `communication` tinyint(3) UNSIGNED DEFAULT NULL,
  `experience` tinyint(3) UNSIGNED DEFAULT NULL,
  `culture_fit` tinyint(3) UNSIGNED DEFAULT NULL,
  `shortlisted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL,
  `mobile` varchar(40) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `education` varchar(120) DEFAULT NULL,
  `yoe` int(11) DEFAULT NULL,
  `role` varchar(120) NOT NULL,
  `site` varchar(80) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `date_hired` datetime DEFAULT NULL,
  `onboarding_token` varchar(64) DEFAULT NULL,
  `onboarding_token_expires` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`id`, `full_name`, `email`, `phone`, `source`, `resume_path`, `skills_json`, `status`, `score`, `communication`, `experience`, `culture_fit`, `shortlisted`, `created_at`, `archived`, `name`, `mobile`, `address`, `education`, `yoe`, `role`, `site`, `start_date`, `date_hired`, `onboarding_token`, `onboarding_token_expires`, `updated_at`) VALUES
(9, 'Danilo Vergara Jr', 'danilovergarajr610@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251008-105831-1bc2283d-Danilo-Vergara-Jr.pdf', NULL, 'hired', 0, 80, 90, 85, 1, '2025-10-08 02:58:32', 0, '', '09919317609', '245 Steve Str. Brgy. Commonwealth Qc.', 'College Undergraduate', 0, 'Order Processor', 'Banawe', '2025-10-10', NULL, '545376cb855e9b25cbdd61e9928f5aca0f7f1e880f0238e9', '2025-10-18 20:35:51', '2025-10-14 20:42:07'),
(10, 'Juan Dela Cruz', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251008-111015-3be3c67b-Juan-Dela-Cruz.pdf', NULL, 'hired', 67, NULL, NULL, NULL, 0, '2025-10-08 03:10:15', 1, '', '09272966548', 'Martan', 'Senior High', 5, 'Store Part Timer', 'Banawe', '2025-10-19', NULL, NULL, NULL, '2025-10-11 01:30:18'),
(11, 'Kevin Durant', 'kevindurant@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251008-202052-88880aa1-Kevin-Durant.pdf', NULL, 'hired', 78, NULL, NULL, NULL, 0, '2025-10-08 12:20:52', 1, '', '0973424242', 'canada', 'college', 5, 'Deputy Store Manager', 'Banawe', '2025-10-18', NULL, NULL, NULL, '2025-10-11 05:54:08'),
(12, 'Mark Juan', 'mark@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251008-203606-b4147231-Mark-Juan.pdf', NULL, 'hired', 0, NULL, NULL, NULL, 1, '2025-10-08 12:36:06', 0, '', '09209343', 'Steve', 'High school', 4, 'Store Part Timer', 'Banawe', '2025-10-11', NULL, 'f532c15d04c0eebace6e3ed8744e0680a2c0150986ac370f', '2025-10-18 20:35:30', '2025-10-12 02:35:30'),
(13, 'Alex Santos', 'alex@gmal.com', NULL, NULL, 'uploads/resumes/cv-20251008-212016-bd09ce85-Alex-Santos.pdf', NULL, 'hired', 32, NULL, NULL, NULL, 1, '2025-10-08 13:20:16', 0, '', '0954332', 'martan', 'High school', 5, 'Store Part Timer', 'Banawe', '2025-10-22', NULL, NULL, NULL, '2025-10-14 20:42:58'),
(14, 'James Villanueva', 'james@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251008-222800-c6f310ca-James-Villanueva.pdf', NULL, 'hired', 87, NULL, NULL, NULL, 1, '2025-10-08 14:28:00', 0, '', '0932534', 'Metom', 'High School', 1, 'Store Part Timer', 'Banawe', '2025-10-10', NULL, '2af9d53f510a53346c0e55ce49ae99fafe86a1b6f3a30b56', '2025-10-18 02:38:20', '2025-10-14 20:47:23'),
(15, 'Mellisa Co', 'mellisa@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-060758-6fcab887-Mellisa-Co.pdf', NULL, '', 0, NULL, NULL, NULL, 1, '2025-10-08 22:07:58', 1, '', '093222344', 'sandigan', 'College Graduate', 0, 'Store Part Timer', 'O!Save – Biñan', '2025-10-11', NULL, NULL, NULL, '2025-10-12 05:57:07'),
(16, 'Mike  Reyes', 'mike@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-071446-c4f572dd-Mike-Reyes.pdf', NULL, '', 0, NULL, NULL, NULL, 1, '2025-10-08 23:14:46', 1, '', '099332245334', 'Novaliches', 'College Graduate', 3, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-11', NULL, NULL, NULL, '2025-10-12 05:31:27'),
(17, 'John Cena', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-094336-aab2a6e1-John-Cena.pdf', NULL, 'hired', 87, NULL, NULL, NULL, 0, '2025-10-09 01:43:36', 0, '', '09393334', '', 'High School', 2, 'Store Part Timer', 'Banawe', NULL, NULL, NULL, NULL, '2025-10-14 20:46:18'),
(19, 'Jake Luigi', 'jake@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-142008-76c5a1e0-Jake-Luigi.pdf', NULL, 'hired', 78, NULL, NULL, NULL, 0, '2025-10-09 06:20:08', 0, '', '09485455', 'metom', 'College', 4, 'Store Part Timer', 'Banawe', '2025-10-10', NULL, 'aaa554f867edf6bbcbbe34e8d1cf5a6f', '2025-10-24 17:00:22', '2025-10-17 23:00:22'),
(20, 'Hans San jose', 'gagaga@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-171236-251a6079-Hans-San-jose.pdf', NULL, 'rejected', 0, NULL, NULL, NULL, 0, '2025-10-09 09:12:36', 0, '', '0934242423', '', 'Colle', 4, 'Deputy Store Manager', 'Banawe', '2025-10-09', NULL, NULL, NULL, '2025-10-11 05:53:17'),
(21, 'Angelica Dorado', 'angel@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-181732-14857a09-Angelica-Dorado.pdf', NULL, 'hired', 56, NULL, NULL, NULL, 0, '2025-10-09 10:17:32', 1, '', '09234242', 'Farview', '', 3, 'Deputy Store Manager', 'Banawe', NULL, NULL, NULL, NULL, '2025-10-11 05:53:35'),
(22, 'Kate Velasco', 'kate@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-222633-32470824-Kate-Velasco.pdf', NULL, 'hired', 56, NULL, NULL, NULL, 0, '2025-10-09 14:26:33', 0, '', '098655443', 'LItex', 'High School', 2, 'Cashier', 'Banawe', '2025-10-11', NULL, NULL, NULL, '2025-10-11 01:30:18'),
(23, 'Juan De Jesus', 'dan@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251009-224805-df986456-Juan-De-Jesus.pdf', NULL, 'rejected', 55, NULL, NULL, NULL, 1, '2025-10-09 14:48:06', 0, '', '09845433', 'sandigan', 'colle', 3, 'Merchandiser / Promodiser', 'Banawe', '2025-10-10', NULL, '90bde207740127c841623211eb827f63f7a490bd48eb8c37', '2025-10-17 22:50:58', '2025-10-11 06:03:06'),
(24, 'Raymart Castro', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251010-081548-82f5cd0b-Raymart-Castro.pdf', NULL, 'hired', 65, NULL, NULL, NULL, 0, '2025-10-10 00:15:48', 0, '', '093432', 'martan', 'High School', 2, 'Order Processor', 'Banawe', '2025-10-10', NULL, NULL, NULL, '2025-10-11 01:30:18'),
(25, 'Josh Dendi', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251010-121310-7cf4d4ad-Josh-Dendi.pdf', NULL, 'hired', 0, NULL, NULL, NULL, 1, '2025-10-10 04:13:10', 0, '', '0983832453', 'Martan St', 'College Grad', 7, 'Cashier', 'Banawe', '2025-10-20', NULL, NULL, NULL, '2025-10-11 01:30:18'),
(26, 'Dan Vergara', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251010-155057-d0d9bdda-Dan-Vergara.pdf', NULL, 'hired', NULL, NULL, NULL, NULL, 0, '2025-10-10 07:50:57', 0, '', '09343345433', 'Martan', 'College', 4, 'Order Processor', 'Banawe', '2025-10-13', NULL, 'b12e44917faec48a9e14406dcaa10df128d6c7dc6b1d6a00', '2025-10-17 23:26:20', '2025-10-11 05:26:20'),
(27, 'Kyle Varga', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251010-161211-27b19c15-Kyle-Varga.pdf', NULL, 'hired', NULL, NULL, NULL, NULL, 0, '2025-10-10 08:12:11', 0, '', '09343234', 'Steve St', 'College Graduate', 2, 'Store Manager', 'Banawe', '2025-10-11', NULL, 'ae5272727d590018067fa0d1530d861e9a8a9d5b07dec9b1', '2025-10-18 08:17:53', '2025-10-11 14:17:53'),
(29, 'Lester Santos', 'vergara.136541132229@depedqc.ph', NULL, NULL, 'uploads/resumes/cv-20251011-221203-90cf2782-Lester-Santos.pdf', NULL, 'hired', NULL, NULL, NULL, NULL, 0, '2025-10-11 14:12:06', 0, '', '09234242324', '', 'High School', 3, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-13', NULL, '7144e967b87f8db3445c5248ebc87fb8e4b9f8e651c8e2d5', '2025-10-18 23:06:09', '2025-10-12 05:06:09'),
(32, 'Jake Miranda', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251012-003245-063377a6-Jake-Miranda.docx', NULL, 'hired', 78, NULL, NULL, NULL, 0, '2025-10-11 16:32:45', 0, '', '09343223', 'Steve st', 'College', 32, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-13', NULL, '92a4aa6017b228d9a7ee97b5de755fe51e88f456f383f1ff', '2025-10-19 00:33:21', '2025-10-14 20:47:41'),
(33, 'Phoenix Luigi', 'gakdwead@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-144110-05493760-Phoenix-Luigi.pdf', NULL, 'hired', 89, NULL, NULL, NULL, 0, '2025-10-14 06:41:10', 0, '', '093242432', 'Marta', '', 2, 'Merchandiser / Promodiser', 'Banawe', '2025-10-15', NULL, NULL, NULL, '2025-10-14 20:41:33'),
(34, 'Chamber Juan', 'gam@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-145416-15406438-Chamber-Juan.pdf', NULL, 'hired', 65, NULL, NULL, NULL, 0, '2025-10-14 06:54:17', 0, '', '009394433', 'Steve', 'College', 4, 'Order Processor', 'Banawe', '2025-10-14', NULL, NULL, NULL, '2025-10-14 20:54:35'),
(35, 'Dani Meniscola', 'danilovergara610@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-145726-506884b4-Dani-Meniscola.pdf', NULL, '', NULL, NULL, NULL, NULL, 0, '2025-10-14 06:57:26', 1, '', '0943243242', 'Martan', 'College Graduate', 3, 'Store Manager', 'Banawe', '2025-10-15', NULL, NULL, NULL, '2025-10-14 20:57:39'),
(36, 'Dans Vergara', 'danilovergarajr610@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-145959-682c84cc-Dans-Vergara.pdf', NULL, 'hired', 87, NULL, NULL, NULL, 0, '2025-10-14 06:59:59', 0, '', '0934242342', 'Steve', 'College', 3, 'Order Processor', 'Banawe', '2025-10-15', NULL, NULL, NULL, '2025-10-14 21:00:14'),
(37, 'Veto Santos', 'danilovergarajr610@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-152106-7b92613c-Veto-Santos.pdf', NULL, 'hired', 76, NULL, NULL, NULL, 0, '2025-10-14 07:21:06', 0, '', '09342432432', 'Martan Street', 'High school', 3, 'Order Processor', 'Banawe', '2025-10-14', NULL, NULL, NULL, '2025-10-14 21:21:22'),
(38, 'Breach Gomez', 'danilovergarajr610@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-152348-52dde52b-Breach-Gomez.pdf', NULL, 'hired', 98, NULL, NULL, NULL, 0, '2025-10-14 07:23:48', 0, '', '0943243242', 'Steve', 'College', 3, 'Merchandiser / Promodiser', 'Banawe', '2025-10-15', NULL, NULL, NULL, '2025-10-14 21:24:22'),
(39, 'Lancelot Maliper', 'danilovergarajr610@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251014-154517-3953b4df-Lancelot-Maliper.pdf', NULL, 'hired', 86, NULL, NULL, NULL, 0, '2025-10-14 07:45:17', 0, '', '0943242342', 'Martan', 'College', 3, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-14', NULL, '528da4383e5b9d62349c2f1a7260e6190867a232be825cf3', '2025-10-21 17:39:34', '2025-10-14 23:39:34'),
(40, 'Cassandra Lopez', 'vergara.136541132229@depedqc.ph', NULL, NULL, 'uploads/resumes/cv-20251014-173328-3319625b-Cassandra-Lopez.pdf', NULL, 'hired', 78, NULL, NULL, NULL, 0, '2025-10-14 09:33:28', 0, '', '0943243242', 'Metom', 'College', 3, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-14', NULL, '697eeb0d408a45cb2c45aaf2a7d258fe', '2025-10-21 17:34:01', '2025-10-14 23:34:01'),
(41, 'Klein De Jesus', 'vergara.136541132229@depedqc.ph', NULL, NULL, 'uploads/resumes/cv-20251014-193207-ab6cca47-Klein-De-Jesus.pdf', NULL, 'hired', 78, NULL, NULL, NULL, 0, '2025-10-14 11:32:07', 0, '', '09432432432', 'Sanidgan', 'College', 3, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-16', NULL, '32ed8b1ece4caa45e60dfb0a9afe8891', '2025-10-21 19:32:30', '2025-10-15 01:32:30'),
(42, 'Kyle Kuzma', 'danv66215@gmail.com', NULL, NULL, 'uploads/resumes/cv-20251017-205347-075ef194-Kyle-Kuzma.pdf', NULL, 'hired', 66, NULL, NULL, NULL, 0, '2025-10-17 12:53:47', 0, '', '094324242', 'dsfsefs', 'College', 2, 'Inventory Clerk / Stockman', 'Banawe', '2025-10-24', NULL, 'b05fc23680c715e0c1d3ee4229c2555d', '2025-10-24 20:54:07', '2025-10-18 02:54:07');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `action` varchar(80) DEFAULT NULL,
  `entity` varchar(80) DEFAULT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `before_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_json`)),
  `after_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `entity`, `entity_id`, `before_json`, `after_json`, `created_at`) VALUES
(1, NULL, 'update_status', NULL, '11', NULL, NULL, '2025-10-08 04:15:40'),
(2, NULL, 'update_status', NULL, '11', NULL, NULL, '2025-10-08 04:15:48'),
(3, NULL, 'update_status', NULL, '13', NULL, NULL, '2025-10-08 04:15:50'),
(4, NULL, 'update_status', NULL, '7', NULL, NULL, '2025-10-08 04:16:06'),
(5, NULL, 'update_status', NULL, '8', NULL, NULL, '2025-10-08 04:16:10'),
(6, NULL, 'update_status', NULL, '18', NULL, NULL, '2025-10-08 04:16:13'),
(7, NULL, 'update_status', NULL, '10', NULL, NULL, '2025-10-08 04:16:21'),
(8, NULL, 'update_status', NULL, '13', NULL, NULL, '2025-10-08 04:16:29'),
(9, NULL, 'update_status', NULL, '15', NULL, NULL, '2025-10-08 04:16:47'),
(10, NULL, 'add', NULL, '22', NULL, NULL, '2025-10-08 05:38:05'),
(11, NULL, 'update_status', NULL, '22', NULL, NULL, '2025-10-08 05:38:07'),
(12, NULL, 'update_status', NULL, '22', NULL, NULL, '2025-10-08 05:38:08'),
(13, NULL, 'public_apply', 'applicant', '5', NULL, NULL, '2025-10-08 01:21:17'),
(14, NULL, 'forward_resume', 'applicant', '1', NULL, NULL, '2025-10-08 01:37:57'),
(15, NULL, 'archive', 'applicant', '2', NULL, NULL, '2025-10-08 01:47:41'),
(16, NULL, 'archive', 'applicant', '1', NULL, NULL, '2025-10-08 02:05:37'),
(17, NULL, 'archive', 'applicant', '1', NULL, NULL, '2025-10-08 02:05:48'),
(18, NULL, 'archive', 'applicant', '2', NULL, NULL, '2025-10-08 02:05:57'),
(19, NULL, 'public_apply', 'applicant', '6', NULL, NULL, '2025-10-08 02:08:18'),
(20, NULL, 'public_apply', 'applicant', '7', NULL, NULL, '2025-10-08 02:18:31'),
(21, NULL, 'public_apply', 'applicant', '8', NULL, NULL, '2025-10-08 02:24:27'),
(22, NULL, 'update_status', 'applicant', '7', NULL, NULL, '2025-10-08 02:24:49'),
(23, NULL, 'update_status', 'applicant', '7', NULL, NULL, '2025-10-08 02:24:56'),
(24, NULL, 'update_status', 'applicant', '7', NULL, NULL, '2025-10-08 02:25:05'),
(25, NULL, 'archive', 'applicant', '7', NULL, NULL, '2025-10-08 02:25:15'),
(26, NULL, 'update_status', NULL, '15', NULL, NULL, '2025-10-08 08:49:57'),
(27, NULL, 'update_status', NULL, '16', NULL, NULL, '2025-10-08 08:50:04'),
(28, NULL, 'update_status', NULL, '16', NULL, NULL, '2025-10-08 08:50:07'),
(29, NULL, 'delete', 'applicant', '1', NULL, NULL, '2025-10-08 02:55:25'),
(30, NULL, 'delete', 'applicant', '2', NULL, NULL, '2025-10-08 02:55:45'),
(31, NULL, 'archive', 'applicant', '3', NULL, NULL, '2025-10-08 02:55:48'),
(32, NULL, 'delete', 'applicant', '3', NULL, NULL, '2025-10-08 02:55:52'),
(33, NULL, 'archive', 'applicant', '4', NULL, NULL, '2025-10-08 02:55:58'),
(34, NULL, 'delete', 'applicant', '4', NULL, NULL, '2025-10-08 02:56:02'),
(35, NULL, 'archive', 'applicant', '5', NULL, NULL, '2025-10-08 02:56:06'),
(36, NULL, 'delete', 'applicant', '5', NULL, NULL, '2025-10-08 02:56:10'),
(37, NULL, 'archive', 'applicant', '6', NULL, NULL, '2025-10-08 02:56:14'),
(38, NULL, 'delete', 'applicant', '6', NULL, NULL, '2025-10-08 02:56:21'),
(39, NULL, 'archive', 'applicant', '8', NULL, NULL, '2025-10-08 02:56:51'),
(40, NULL, 'delete', 'applicant', '7', NULL, NULL, '2025-10-08 02:56:56'),
(41, NULL, 'delete', 'applicant', '8', NULL, NULL, '2025-10-08 02:56:59'),
(42, NULL, 'public_apply', 'applicant', '9', NULL, NULL, '2025-10-08 02:58:33'),
(43, NULL, 'public_apply', 'applicant', '10', NULL, NULL, '2025-10-08 03:10:15'),
(44, NULL, 'delete', NULL, '15', NULL, NULL, '2025-10-08 09:13:20'),
(45, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 03:46:08'),
(46, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 03:49:31'),
(47, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-08 03:50:01'),
(48, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 04:03:55'),
(49, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-08 04:04:08'),
(50, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-08 04:20:59'),
(51, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 04:21:55'),
(52, NULL, 'notify', 'applicant', '10', NULL, NULL, '2025-10-08 04:22:18'),
(53, NULL, 'update_status', NULL, '22', NULL, NULL, '2025-10-08 10:36:35'),
(54, NULL, 'update_status', NULL, '16', NULL, NULL, '2025-10-08 10:36:45'),
(55, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:01:27'),
(56, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:01:49'),
(57, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:06:00'),
(58, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:07:08'),
(59, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 09:48:32'),
(60, NULL, 'notify', 'applicant', '10', NULL, NULL, '2025-10-08 09:48:40'),
(61, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:49:51'),
(62, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:50:06'),
(63, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:57:06'),
(64, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:57:18'),
(65, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:58:21'),
(66, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:58:30'),
(67, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 09:58:41'),
(68, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 10:00:11'),
(69, NULL, 'update_status', NULL, '12', NULL, NULL, '2025-10-08 16:00:32'),
(70, NULL, 'update_status', NULL, '13', NULL, NULL, '2025-10-08 16:00:37'),
(71, NULL, 'update_status', NULL, '13', NULL, NULL, '2025-10-08 16:02:51'),
(72, NULL, 'update_status', NULL, '12', NULL, NULL, '2025-10-08 16:02:59'),
(73, NULL, 'update_status', NULL, '13', NULL, NULL, '2025-10-08 16:03:04'),
(74, NULL, 'update_status', NULL, '19', NULL, NULL, '2025-10-08 16:03:10'),
(75, NULL, 'delete', NULL, '16', NULL, NULL, '2025-10-08 16:20:12'),
(76, NULL, 'delete', NULL, '11', NULL, NULL, '2025-10-08 16:20:54'),
(77, NULL, 'delete', NULL, '11', NULL, NULL, '2025-10-08 16:20:56'),
(78, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 10:25:22'),
(79, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 10:25:30'),
(80, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 10:47:10'),
(81, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 10:47:53'),
(82, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 11:18:33'),
(83, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:18:41'),
(84, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:18:50'),
(85, NULL, 'auto_add', NULL, '18', NULL, NULL, '2025-10-08 17:18:50'),
(86, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 11:19:26'),
(87, NULL, 'auto_add', NULL, '19', NULL, NULL, '2025-10-08 17:19:26'),
(88, NULL, 'delete', NULL, '17', NULL, NULL, '2025-10-08 17:19:38'),
(89, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:20:02'),
(90, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:20:13'),
(91, NULL, 'auto_add', NULL, '21', NULL, NULL, '2025-10-08 17:20:13'),
(92, NULL, 'delete', NULL, '21', NULL, NULL, '2025-10-08 17:20:40'),
(93, NULL, 'delete', NULL, '20', NULL, NULL, '2025-10-08 17:22:03'),
(94, NULL, 'delete', NULL, '18', NULL, NULL, '2025-10-08 17:22:49'),
(95, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:22:56'),
(96, NULL, 'auto_add', NULL, '23', NULL, NULL, '2025-10-08 17:22:57'),
(97, NULL, 'delete', NULL, '23', NULL, NULL, '2025-10-08 17:29:00'),
(98, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:33:42'),
(99, NULL, 'delete', NULL, '24', NULL, NULL, '2025-10-08 17:50:15'),
(100, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:50:23'),
(101, NULL, 'delete', NULL, '25', NULL, NULL, '2025-10-08 17:51:05'),
(102, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:51:14'),
(103, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:51:26'),
(104, NULL, 'auto_add', NULL, '27', NULL, NULL, '2025-10-08 17:51:26'),
(105, NULL, 'delete', NULL, '26', NULL, NULL, '2025-10-08 17:58:29'),
(106, NULL, 'delete', NULL, '27', NULL, NULL, '2025-10-08 17:58:32'),
(107, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 11:58:44'),
(108, NULL, 'delete', NULL, '28', NULL, NULL, '2025-10-08 18:03:37'),
(109, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 12:03:52'),
(110, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 12:12:18'),
(111, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 12:12:42'),
(112, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-08 12:19:01'),
(113, NULL, 'public_apply', 'applicant', '11', NULL, NULL, '2025-10-08 12:20:52'),
(114, NULL, 'update_status', 'applicant', '11', NULL, NULL, '2025-10-08 12:21:11'),
(115, NULL, 'add_from_applicant', NULL, '31', NULL, NULL, '2025-10-08 18:21:11'),
(116, NULL, 'archive', 'applicant', '10', NULL, NULL, '2025-10-08 12:21:30'),
(117, NULL, 'public_apply', 'applicant', '12', NULL, NULL, '2025-10-08 12:36:06'),
(118, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-08 12:36:21'),
(119, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-08 12:56:02'),
(120, NULL, 'unarchive', 'applicant', '11', NULL, NULL, '2025-10-08 18:56:10'),
(121, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-08 12:56:31'),
(122, NULL, 'forward_resume', 'applicant', '12', NULL, NULL, '2025-10-08 12:57:00'),
(123, NULL, 'update_status', 'applicant', '12', NULL, NULL, '2025-10-08 12:57:13'),
(124, NULL, 'add_from_applicant', NULL, '32', NULL, NULL, '2025-10-08 18:57:13'),
(125, NULL, 'public_apply', 'applicant', '13', NULL, NULL, '2025-10-08 13:20:16'),
(126, NULL, 'update_status', 'applicant', '13', NULL, NULL, '2025-10-08 13:28:57'),
(127, NULL, 'add_from_applicant', NULL, '33', NULL, NULL, '2025-10-08 19:28:57'),
(128, NULL, 'update_status', NULL, '19', NULL, NULL, '2025-10-08 20:13:56'),
(129, NULL, 'update_status', NULL, '19', NULL, NULL, '2025-10-08 20:13:59'),
(130, NULL, 'update_status', NULL, '17', NULL, NULL, '2025-10-08 20:14:02'),
(131, NULL, 'delete', NULL, '13', NULL, NULL, '2025-10-08 20:19:33'),
(132, NULL, 'add', NULL, '23', NULL, NULL, '2025-10-08 20:20:38'),
(133, NULL, 'update_status', NULL, '23', NULL, NULL, '2025-10-08 20:21:10'),
(134, NULL, 'add', NULL, '24', NULL, NULL, '2025-10-08 20:22:47'),
(135, NULL, 'delete', NULL, '24', NULL, NULL, '2025-10-08 20:22:58'),
(136, NULL, 'add', NULL, '25', NULL, NULL, '2025-10-08 20:23:12'),
(137, NULL, 'add', NULL, '26', NULL, NULL, '2025-10-08 20:24:34'),
(138, NULL, 'delete', NULL, '9', NULL, NULL, '2025-10-08 20:25:11'),
(139, NULL, 'delete', NULL, '4', NULL, NULL, '2025-10-08 20:25:20'),
(140, NULL, 'delete', NULL, '12', NULL, NULL, '2025-10-08 20:25:23'),
(141, NULL, 'delete', NULL, '8', NULL, NULL, '2025-10-08 20:25:51'),
(142, NULL, 'public_apply', 'applicant', '14', NULL, NULL, '2025-10-08 14:28:00'),
(143, NULL, 'update_status', 'applicant', '14', NULL, NULL, '2025-10-08 14:30:22'),
(144, NULL, 'update_status', 'applicant', '14', NULL, NULL, '2025-10-08 14:30:34'),
(145, NULL, 'add_from_applicant', NULL, '34', NULL, NULL, '2025-10-08 20:30:34'),
(146, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-08 15:17:13'),
(147, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-08 15:17:17'),
(148, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-08 15:24:03'),
(149, NULL, 'public_apply', 'applicant', '15', NULL, NULL, '2025-10-08 22:07:58'),
(150, NULL, 'update_status', 'applicant', '13', NULL, NULL, '2025-10-08 22:17:10'),
(151, NULL, 'public_apply', 'applicant', '16', NULL, NULL, '2025-10-08 23:14:46'),
(152, NULL, 'update_status', 'applicant', '16', NULL, NULL, '2025-10-08 23:15:30'),
(153, NULL, 'add_from_applicant', NULL, '35', NULL, NULL, '2025-10-09 05:15:30'),
(154, NULL, 'update_status', NULL, '25', NULL, NULL, '2025-10-09 05:19:46'),
(155, NULL, 'public_apply', 'applicant', '17', NULL, NULL, '2025-10-09 01:43:36'),
(156, NULL, 'public_apply', 'applicant', '18', NULL, NULL, '2025-10-09 01:44:34'),
(157, NULL, 'archive', 'applicant', '18', NULL, NULL, '2025-10-09 01:44:48'),
(158, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-09 02:57:25'),
(159, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-09 02:57:25'),
(160, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 02:57:48'),
(161, NULL, 'submit_score', 'applicant', '17', NULL, NULL, '2025-10-09 02:58:06'),
(162, NULL, 'set_status', 'applicant', '17', NULL, NULL, '2025-10-09 02:58:06'),
(163, NULL, 'update_status', 'applicant', '12', NULL, NULL, '2025-10-09 02:59:10'),
(164, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 02:59:24'),
(165, NULL, 'update_status', 'applicant', '14', NULL, NULL, '2025-10-09 02:59:32'),
(166, NULL, 'update_status', 'applicant', '16', NULL, NULL, '2025-10-09 02:59:38'),
(167, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 02:59:56'),
(168, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 03:00:29'),
(169, NULL, 'notify', 'applicant', '17', NULL, NULL, '2025-10-09 03:01:38'),
(170, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 03:02:21'),
(171, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 03:02:21'),
(172, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 03:02:44'),
(173, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 03:02:44'),
(174, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 03:03:13'),
(175, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-09 03:03:17'),
(176, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 03:03:18'),
(177, NULL, 'toggle_shortlist', 'applicant', '15', NULL, NULL, '2025-10-09 03:03:21'),
(178, NULL, 'toggle_shortlist', 'applicant', '16', NULL, NULL, '2025-10-09 03:03:22'),
(179, NULL, 'toggle_shortlist', 'applicant', '17', NULL, NULL, '2025-10-09 03:03:23'),
(180, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 03:03:24'),
(181, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 03:03:26'),
(182, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 03:06:40'),
(183, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 03:06:40'),
(184, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 03:07:00'),
(185, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 03:07:00'),
(186, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 03:07:58'),
(187, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 03:07:58'),
(188, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 03:08:25'),
(189, NULL, 'submit_score', 'applicant', '14', NULL, NULL, '2025-10-09 03:21:25'),
(190, NULL, 'set_status', 'applicant', '14', NULL, NULL, '2025-10-09 03:21:25'),
(191, NULL, 'submit_score', 'applicant', '15', NULL, NULL, '2025-10-09 03:22:08'),
(192, NULL, 'set_status', 'applicant', '15', NULL, NULL, '2025-10-09 03:22:08'),
(193, NULL, 'update_status', 'applicant', '15', NULL, NULL, '2025-10-09 03:22:46'),
(194, NULL, 'add_from_applicant', NULL, '36', NULL, NULL, '2025-10-09 09:22:46'),
(195, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 03:25:50'),
(196, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 03:25:50'),
(197, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 03:27:10'),
(198, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 03:27:10'),
(199, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 03:27:24'),
(200, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 03:27:24'),
(201, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 04:11:07'),
(202, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 04:11:07'),
(203, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 04:11:19'),
(204, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 04:11:19'),
(205, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 04:11:38'),
(206, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 04:11:38'),
(207, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-09 04:12:31'),
(208, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-09 04:12:31'),
(209, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 04:13:19'),
(210, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 04:13:19'),
(211, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 04:13:34'),
(212, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 04:13:34'),
(213, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 04:13:56'),
(214, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 04:13:56'),
(215, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 04:15:43'),
(216, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 04:15:44'),
(217, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 04:15:45'),
(218, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 04:15:45'),
(219, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-09 04:15:53'),
(220, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-09 04:15:56'),
(221, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-09 04:15:57'),
(222, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-09 04:15:57'),
(223, NULL, 'update_status', 'applicant', '17', NULL, NULL, '2025-10-09 04:29:19'),
(224, NULL, 'add_from_applicant', NULL, '37', NULL, NULL, '2025-10-09 10:29:19'),
(225, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 04:29:36'),
(226, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 04:29:36'),
(227, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-09 04:42:18'),
(228, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-09 04:42:18'),
(229, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 04:42:47'),
(230, NULL, 'unarchive', 'applicant', '11', NULL, NULL, '2025-10-09 11:20:29'),
(231, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-09 05:20:38'),
(232, NULL, 'delete', NULL, '31', NULL, NULL, '2025-10-09 11:20:55'),
(233, NULL, 'update_status', NULL, '26', NULL, NULL, '2025-10-09 11:57:04'),
(234, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 06:18:38'),
(235, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 06:18:43'),
(236, NULL, 'public_apply', 'applicant', '19', NULL, NULL, '2025-10-09 06:20:08'),
(237, NULL, 'submit_score', 'applicant', '19', NULL, NULL, '2025-10-09 06:20:36'),
(238, NULL, 'set_status', 'applicant', '19', NULL, NULL, '2025-10-09 06:20:36'),
(239, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 06:22:28'),
(240, NULL, 'toggle_shortlist', 'applicant', '14', NULL, NULL, '2025-10-09 06:22:28'),
(241, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 07:18:52'),
(242, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 07:19:14'),
(243, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 07:19:14'),
(244, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 07:19:52'),
(245, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 07:20:42'),
(246, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 07:20:42'),
(247, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 07:21:24'),
(248, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 07:21:25'),
(249, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 07:28:22'),
(250, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 07:28:37'),
(251, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 07:28:37'),
(252, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:21:01'),
(253, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 08:21:14'),
(254, NULL, 'update_status', 'applicant', '12', NULL, NULL, '2025-10-09 08:22:14'),
(255, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:23:55'),
(256, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:24:00'),
(257, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:24:09'),
(258, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:35:57'),
(259, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 08:36:09'),
(260, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:36:09'),
(261, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 08:36:44'),
(262, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:39:31'),
(263, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 08:39:42'),
(264, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:39:42'),
(265, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:44:59'),
(266, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 08:45:13'),
(267, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:45:13'),
(268, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 08:58:14'),
(269, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 09:03:46'),
(270, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 09:04:09'),
(271, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:05:27'),
(272, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:11:15'),
(273, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 09:11:25'),
(274, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:11:26'),
(275, NULL, 'public_apply', 'applicant', '20', NULL, NULL, '2025-10-09 09:12:36'),
(276, NULL, 'submit_score', 'applicant', '20', NULL, NULL, '2025-10-09 09:13:03'),
(277, NULL, 'set_status', 'applicant', '20', NULL, NULL, '2025-10-09 09:13:03'),
(278, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:20:31'),
(279, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:20:41'),
(280, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 09:20:52'),
(281, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:20:52'),
(282, NULL, 'update_status', 'applicant', '20', NULL, NULL, '2025-10-09 09:22:32'),
(283, NULL, 'notify', 'applicant', '20', NULL, NULL, '2025-10-09 09:22:38'),
(284, NULL, 'submit_score', 'applicant', '20', NULL, NULL, '2025-10-09 09:22:46'),
(285, NULL, 'set_status', 'applicant', '20', NULL, NULL, '2025-10-09 09:22:46'),
(286, NULL, 'update_status', 'applicant', '15', NULL, NULL, '2025-10-09 09:23:10'),
(287, NULL, 'submit_score', 'applicant', '15', NULL, NULL, '2025-10-09 09:23:20'),
(288, NULL, 'set_status', 'applicant', '15', NULL, NULL, '2025-10-09 09:23:20'),
(289, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:26:39'),
(290, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 09:26:45'),
(291, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 09:27:13'),
(292, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 09:27:35'),
(293, NULL, 'update_status', 'applicant', '12', NULL, NULL, '2025-10-09 09:30:40'),
(294, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 09:30:52'),
(295, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 09:30:53'),
(296, NULL, 'update_status', 'applicant', '16', NULL, NULL, '2025-10-09 09:41:26'),
(297, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-09 09:41:44'),
(298, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-09 09:41:44'),
(299, NULL, 'public_apply', 'applicant', '21', NULL, NULL, '2025-10-09 10:17:32'),
(300, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 10:18:19'),
(301, NULL, 'toggle_shortlist', 'applicant', '21', NULL, NULL, '2025-10-09 10:18:52'),
(302, NULL, 'toggle_shortlist', 'applicant', '21', NULL, NULL, '2025-10-09 10:18:53'),
(303, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 10:41:23'),
(304, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 10:41:27'),
(305, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 10:41:46'),
(306, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 10:41:50'),
(307, NULL, 'update_status', 'applicant', '17', NULL, NULL, '2025-10-09 10:43:03'),
(308, NULL, 'notify', 'applicant', '17', NULL, NULL, '2025-10-09 10:43:07'),
(309, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 10:53:27'),
(310, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 10:53:30'),
(311, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 10:54:07'),
(312, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 10:54:17'),
(313, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 10:54:24'),
(314, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-09 11:02:15'),
(315, NULL, 'schedule_interview', 'applicant', '9', NULL, NULL, '2025-10-09 11:07:27'),
(316, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-09 11:07:32'),
(317, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 11:19:38'),
(318, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 11:19:39'),
(319, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 11:19:40'),
(320, NULL, 'toggle_shortlist', 'applicant', '9', NULL, NULL, '2025-10-09 11:19:40'),
(321, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:32:38'),
(322, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:32:40'),
(323, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 11:34:29'),
(324, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 11:34:32'),
(325, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 11:34:34'),
(326, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 11:34:34'),
(327, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-09 11:34:43'),
(328, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-09 11:34:43'),
(329, NULL, 'submit_score', 'applicant', '21', NULL, NULL, '2025-10-09 11:34:54'),
(330, NULL, 'set_status', 'applicant', '21', NULL, NULL, '2025-10-09 11:34:54'),
(331, NULL, 'submit_score', 'applicant', '21', NULL, NULL, '2025-10-09 11:35:10'),
(332, NULL, 'set_status', 'applicant', '21', NULL, NULL, '2025-10-09 11:35:10'),
(333, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:37:36'),
(334, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:37:36'),
(335, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:37:38'),
(336, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:37:39'),
(337, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-09 11:37:39'),
(338, NULL, 'archive', 'applicant', '20', NULL, NULL, '2025-10-09 11:54:50'),
(339, NULL, 'submit_score', 'applicant', '21', NULL, NULL, '2025-10-09 11:55:42'),
(340, NULL, 'set_status', 'applicant', '21', NULL, NULL, '2025-10-09 11:55:42'),
(341, NULL, 'archive', 'applicant', '12', NULL, NULL, '2025-10-09 12:04:37'),
(342, NULL, 'archive', 'applicant', '13', NULL, NULL, '2025-10-09 12:04:38'),
(343, NULL, 'archive', 'applicant', '14', NULL, NULL, '2025-10-09 12:04:39'),
(344, NULL, 'archive', 'applicant', '15', NULL, NULL, '2025-10-09 12:04:40'),
(345, NULL, 'archive', 'applicant', '16', NULL, NULL, '2025-10-09 12:04:41'),
(346, NULL, 'archive', 'applicant', '17', NULL, NULL, '2025-10-09 12:04:41'),
(347, NULL, 'archive', 'applicant', '19', NULL, NULL, '2025-10-09 12:04:42'),
(348, NULL, 'archive', 'applicant', '21', NULL, NULL, '2025-10-09 12:04:43'),
(349, NULL, 'archive', 'applicant', '12', NULL, NULL, '2025-10-09 12:04:59'),
(350, NULL, 'archive', 'applicant', '13', NULL, NULL, '2025-10-09 12:05:00'),
(351, NULL, 'archive', 'applicant', '14', NULL, NULL, '2025-10-09 12:05:00'),
(352, NULL, 'archive', 'applicant', '15', NULL, NULL, '2025-10-09 12:05:01'),
(353, NULL, 'archive', 'applicant', '16', NULL, NULL, '2025-10-09 12:05:02'),
(354, NULL, 'archive', 'applicant', '17', NULL, NULL, '2025-10-09 12:05:03'),
(355, NULL, 'archive', 'applicant', '19', NULL, NULL, '2025-10-09 12:05:03'),
(356, NULL, 'archive', 'applicant', '21', NULL, NULL, '2025-10-09 12:05:05'),
(357, NULL, 'archive', 'applicant', '12', NULL, NULL, '2025-10-09 12:05:12'),
(358, NULL, 'archive', 'applicant', '13', NULL, NULL, '2025-10-09 12:05:14'),
(359, NULL, 'archive', 'applicant', '14', NULL, NULL, '2025-10-09 12:05:14'),
(360, NULL, 'archive', 'applicant', '15', NULL, NULL, '2025-10-09 12:05:15'),
(361, NULL, 'archive', 'applicant', '16', NULL, NULL, '2025-10-09 12:05:16'),
(362, NULL, 'archive', 'applicant', '17', NULL, NULL, '2025-10-09 12:05:16'),
(363, NULL, 'archive', 'applicant', '19', NULL, NULL, '2025-10-09 12:05:17'),
(364, NULL, 'archive', 'applicant', '21', NULL, NULL, '2025-10-09 12:05:17'),
(365, NULL, 'unarchive', 'applicant', '12', NULL, NULL, '2025-10-09 18:11:52'),
(366, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-09 12:13:14'),
(367, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-09 12:13:14'),
(368, NULL, 'archive', 'applicant', '12', NULL, NULL, '2025-10-09 13:58:29'),
(369, NULL, 'unarchive', 'applicant', '13', NULL, NULL, '2025-10-09 19:58:55'),
(370, NULL, 'public_apply', 'applicant', '22', NULL, NULL, '2025-10-09 14:26:33'),
(371, NULL, 'submit_score', 'applicant', '22', NULL, NULL, '2025-10-09 14:27:41'),
(372, NULL, 'set_status', 'applicant', '22', NULL, NULL, '2025-10-09 14:27:41'),
(373, NULL, 'unarchive', 'applicant', '11', NULL, NULL, '2025-10-09 20:32:18'),
(374, NULL, 'submit_score', 'applicant', '11', NULL, NULL, '2025-10-09 14:32:28'),
(375, NULL, 'set_status', 'applicant', '11', NULL, NULL, '2025-10-09 14:32:28'),
(376, NULL, 'update_status', 'applicant', '22', NULL, NULL, '2025-10-09 14:35:52'),
(377, NULL, 'submit_score', 'applicant', '22', NULL, NULL, '2025-10-09 14:36:10'),
(378, NULL, 'set_status', 'applicant', '22', NULL, NULL, '2025-10-09 14:36:10'),
(379, NULL, 'update_status', 'applicant', '22', NULL, NULL, '2025-10-09 14:38:00'),
(380, NULL, 'update_status', 'applicant', '22', NULL, NULL, '2025-10-09 14:38:12'),
(381, NULL, 'add_from_applicant', NULL, '38', NULL, NULL, '2025-10-09 20:38:12'),
(382, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-09 14:45:54'),
(383, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-09 14:46:16'),
(384, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-09 14:46:26'),
(385, NULL, 'unarchive', 'applicant', '11', NULL, NULL, '2025-10-09 20:46:37'),
(386, NULL, 'submit_score', 'applicant', '11', NULL, NULL, '2025-10-09 14:46:51'),
(387, NULL, 'set_status', 'applicant', '11', NULL, NULL, '2025-10-09 14:46:51'),
(388, NULL, 'public_apply', 'applicant', '23', NULL, NULL, '2025-10-09 14:48:06'),
(389, NULL, 'submit_score', 'applicant', '23', NULL, NULL, '2025-10-09 14:48:25'),
(390, NULL, 'submit_score', 'applicant', '23', NULL, NULL, '2025-10-09 14:56:20'),
(391, NULL, 'set_status', 'applicant', '23', NULL, NULL, '2025-10-09 14:56:20'),
(392, NULL, 'update_status', 'applicant', '23', NULL, NULL, '2025-10-09 14:58:40'),
(393, NULL, 'add_from_applicant', NULL, '39', NULL, NULL, '2025-10-09 20:58:40'),
(394, NULL, 'update_status', 'applicant', '11', NULL, NULL, '2025-10-09 15:00:10'),
(395, NULL, 'add_from_applicant', NULL, '40', NULL, NULL, '2025-10-09 21:00:10'),
(396, NULL, 'unarchive', 'applicant', '14', NULL, NULL, '2025-10-09 21:01:02'),
(397, NULL, 'submit_score', 'applicant', '14', NULL, NULL, '2025-10-09 15:01:12'),
(398, NULL, 'set_status', 'applicant', '14', NULL, NULL, '2025-10-09 15:01:12'),
(399, NULL, 'update_status', 'applicant', '14', NULL, NULL, '2025-10-09 15:01:28'),
(400, NULL, 'archive', 'applicant', '23', NULL, NULL, '2025-10-09 15:02:13'),
(401, NULL, 'unarchive', 'applicant', '21', NULL, NULL, '2025-10-09 21:07:21'),
(402, NULL, 'submit_score', 'applicant', '21', NULL, NULL, '2025-10-09 15:07:32'),
(403, NULL, 'set_status', 'applicant', '21', NULL, NULL, '2025-10-09 15:07:32'),
(404, NULL, 'update_status', 'applicant', '21', NULL, NULL, '2025-10-09 15:07:54'),
(405, NULL, 'add_from_applicant', NULL, '41', NULL, NULL, '2025-10-09 21:07:55'),
(406, NULL, 'update_status', 'applicant', '21', NULL, NULL, '2025-10-09 15:08:36'),
(407, NULL, 'unarchive', 'applicant', '10', NULL, NULL, '2025-10-09 21:12:05'),
(408, NULL, 'submit_score', 'applicant', '10', NULL, NULL, '2025-10-09 15:12:15'),
(409, NULL, 'set_status', 'applicant', '10', NULL, NULL, '2025-10-09 15:12:15'),
(410, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-09 15:12:32'),
(411, NULL, 'add_from_applicant', NULL, '42', NULL, NULL, '2025-10-09 21:12:32'),
(412, NULL, 'update_status', 'applicant', '10', NULL, NULL, '2025-10-09 15:15:43'),
(413, NULL, 'delete', 'applicant', '18', NULL, NULL, '2025-10-10 00:12:51'),
(414, NULL, 'public_apply', 'applicant', '24', NULL, NULL, '2025-10-10 00:15:48'),
(415, NULL, 'schedule_interview', 'applicant', '24', NULL, NULL, '2025-10-10 00:16:19'),
(416, NULL, 'notify', 'applicant', '24', NULL, NULL, '2025-10-10 00:16:22'),
(417, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-10 00:31:21'),
(418, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-10 00:31:21'),
(419, NULL, 'submit_score', 'applicant', '24', NULL, NULL, '2025-10-10 00:31:49'),
(420, NULL, 'set_status', 'applicant', '24', NULL, NULL, '2025-10-10 00:31:49'),
(421, NULL, 'update_status', 'applicant', '24', NULL, NULL, '2025-10-10 00:32:01'),
(422, NULL, 'add_from_applicant', NULL, '43', NULL, NULL, '2025-10-10 06:32:01'),
(423, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-10 01:13:38'),
(424, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-10 01:13:38'),
(425, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-10 01:13:51'),
(426, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-10 01:13:52'),
(427, NULL, 'update_status', 'applicant', '14', NULL, NULL, '2025-10-10 01:14:03'),
(428, NULL, 'submit_score', 'applicant', '14', NULL, NULL, '2025-10-10 01:14:26'),
(429, NULL, 'set_status', 'applicant', '14', NULL, NULL, '2025-10-10 01:14:26'),
(430, NULL, 'update_status', 'applicant', '14', NULL, NULL, '2025-10-10 01:14:44'),
(431, NULL, 'update_status', 'applicant', '24', NULL, NULL, '2025-10-10 01:45:09'),
(432, NULL, 'delete', NULL, '3', NULL, NULL, '2025-10-10 08:56:53'),
(433, NULL, 'unarchive', 'applicant', '15', NULL, NULL, '2025-10-10 09:57:27'),
(434, NULL, 'unarchive', 'applicant', '16', NULL, NULL, '2025-10-10 09:57:29'),
(435, NULL, 'unarchive', 'applicant', '17', NULL, NULL, '2025-10-10 09:57:30'),
(436, NULL, 'unarchive', 'applicant', '19', NULL, NULL, '2025-10-10 09:57:32'),
(437, NULL, 'unarchive', 'applicant', '20', NULL, NULL, '2025-10-10 09:57:33'),
(438, NULL, 'unarchive', 'applicant', '23', NULL, NULL, '2025-10-10 09:57:35'),
(439, NULL, 'unarchive', 'applicant', '12', NULL, NULL, '2025-10-10 09:57:37'),
(440, NULL, 'public_apply', 'applicant', '25', NULL, NULL, '2025-10-10 04:13:10'),
(441, NULL, 'schedule_interview', 'applicant', '25', NULL, NULL, '2025-10-10 04:13:37'),
(442, NULL, 'notify', 'applicant', '25', NULL, NULL, '2025-10-10 04:13:41'),
(443, NULL, 'submit_score', 'applicant', '25', NULL, NULL, '2025-10-10 04:14:13'),
(444, NULL, 'set_status', 'applicant', '25', NULL, NULL, '2025-10-10 04:14:13'),
(445, NULL, 'toggle_shortlist', 'applicant', '25', NULL, NULL, '2025-10-10 04:14:24'),
(446, NULL, 'notify', 'applicant', '25', NULL, NULL, '2025-10-10 04:14:44'),
(447, NULL, 'submit_score', 'applicant', '25', NULL, NULL, '2025-10-10 04:15:12'),
(448, NULL, 'set_status', 'applicant', '25', NULL, NULL, '2025-10-10 04:15:12'),
(449, NULL, 'update_status', 'applicant', '25', NULL, NULL, '2025-10-10 04:15:30'),
(450, NULL, 'add_from_applicant', NULL, '44', NULL, NULL, '2025-10-10 10:15:30'),
(451, NULL, 'update_status', 'applicant', '25', NULL, NULL, '2025-10-10 04:50:39'),
(452, NULL, 'update_status', 'applicant', '25', NULL, NULL, '2025-10-10 04:56:45'),
(453, NULL, 'update', NULL, '33', NULL, NULL, '2025-10-10 11:47:11'),
(454, NULL, 'update', NULL, '44', NULL, NULL, '2025-10-10 11:47:54'),
(455, NULL, 'update', NULL, '35', NULL, NULL, '2025-10-10 12:04:59'),
(456, NULL, 'update', NULL, '44', NULL, NULL, '2025-10-10 12:06:42'),
(457, NULL, 'add', NULL, '27', NULL, NULL, '2025-10-10 12:33:31'),
(458, NULL, 'add', NULL, '28', NULL, NULL, '2025-10-10 12:33:31'),
(459, NULL, 'add', NULL, '29', NULL, NULL, '2025-10-10 12:33:32'),
(460, NULL, 'add', NULL, '30', NULL, NULL, '2025-10-10 12:33:32'),
(461, NULL, 'add', NULL, '31', NULL, NULL, '2025-10-10 12:33:32'),
(462, NULL, 'add', NULL, '32', NULL, NULL, '2025-10-10 12:33:32'),
(463, NULL, 'add', NULL, '33', NULL, NULL, '2025-10-10 12:33:32'),
(464, NULL, 'add', NULL, '34', NULL, NULL, '2025-10-10 12:33:32'),
(465, NULL, 'add', NULL, '35', NULL, NULL, '2025-10-10 12:33:32'),
(466, NULL, 'add', NULL, '36', NULL, NULL, '2025-10-10 12:33:32'),
(467, NULL, 'add', NULL, '37', NULL, NULL, '2025-10-10 12:33:32'),
(468, NULL, 'add', NULL, '38', NULL, NULL, '2025-10-10 12:33:32'),
(469, NULL, 'add', NULL, '39', NULL, NULL, '2025-10-10 12:33:32'),
(470, NULL, 'add', NULL, '40', NULL, NULL, '2025-10-10 12:34:06'),
(471, NULL, 'add', NULL, '41', NULL, NULL, '2025-10-10 12:34:06'),
(472, NULL, 'add', NULL, '42', NULL, NULL, '2025-10-10 12:34:06'),
(473, NULL, 'add', NULL, '43', NULL, NULL, '2025-10-10 12:34:06'),
(474, NULL, 'add', NULL, '44', NULL, NULL, '2025-10-10 12:34:06'),
(475, NULL, 'add', NULL, '45', NULL, NULL, '2025-10-10 12:34:06'),
(476, NULL, 'add', NULL, '46', NULL, NULL, '2025-10-10 12:34:06'),
(477, NULL, 'add', NULL, '47', NULL, NULL, '2025-10-10 12:34:06'),
(478, NULL, 'add', NULL, '48', NULL, NULL, '2025-10-10 12:34:06'),
(479, NULL, 'add', NULL, '49', NULL, NULL, '2025-10-10 12:34:06'),
(480, NULL, 'add', NULL, '50', NULL, NULL, '2025-10-10 12:34:06'),
(481, NULL, 'add', NULL, '51', NULL, NULL, '2025-10-10 12:34:06'),
(482, NULL, 'update_status', NULL, '40', NULL, NULL, '2025-10-10 12:34:30'),
(483, NULL, 'update_status', NULL, '40', NULL, NULL, '2025-10-10 12:34:41'),
(484, NULL, 'add', NULL, '52', NULL, NULL, '2025-10-10 12:34:47'),
(485, NULL, 'add', NULL, '53', NULL, NULL, '2025-10-10 12:34:47'),
(486, NULL, 'add', NULL, '54', NULL, NULL, '2025-10-10 12:34:47'),
(487, NULL, 'add', NULL, '55', NULL, NULL, '2025-10-10 12:34:47'),
(488, NULL, 'add', NULL, '56', NULL, NULL, '2025-10-10 12:34:47'),
(489, NULL, 'add', NULL, '57', NULL, NULL, '2025-10-10 12:34:47'),
(490, NULL, 'add', NULL, '58', NULL, NULL, '2025-10-10 12:34:47'),
(491, NULL, 'add', NULL, '59', NULL, NULL, '2025-10-10 12:34:47'),
(492, NULL, 'add', NULL, '60', NULL, NULL, '2025-10-10 12:34:47'),
(493, NULL, 'add', NULL, '61', NULL, NULL, '2025-10-10 12:34:47'),
(494, NULL, 'add', NULL, '62', NULL, NULL, '2025-10-10 12:34:47'),
(495, NULL, 'add', NULL, '63', NULL, NULL, '2025-10-10 12:34:47'),
(496, NULL, 'add', NULL, '64', NULL, NULL, '2025-10-10 12:34:56'),
(497, NULL, 'add', NULL, '65', NULL, NULL, '2025-10-10 12:34:56'),
(498, NULL, 'add', NULL, '66', NULL, NULL, '2025-10-10 12:34:56'),
(499, NULL, 'add', NULL, '67', NULL, NULL, '2025-10-10 12:34:56'),
(500, NULL, 'add', NULL, '68', NULL, NULL, '2025-10-10 12:34:57'),
(501, NULL, 'add', NULL, '69', NULL, NULL, '2025-10-10 12:34:57'),
(502, NULL, 'add', NULL, '70', NULL, NULL, '2025-10-10 12:34:57'),
(503, NULL, 'add', NULL, '71', NULL, NULL, '2025-10-10 12:34:57'),
(504, NULL, 'add', NULL, '72', NULL, NULL, '2025-10-10 12:34:57'),
(505, NULL, 'add', NULL, '73', NULL, NULL, '2025-10-10 12:34:57'),
(506, NULL, 'add', NULL, '74', NULL, NULL, '2025-10-10 12:34:57'),
(507, NULL, 'add', NULL, '75', NULL, NULL, '2025-10-10 12:34:57'),
(508, NULL, 'delete', NULL, '75', NULL, NULL, '2025-10-10 12:35:09'),
(509, NULL, 'delete', NULL, '74', NULL, NULL, '2025-10-10 12:35:11'),
(510, NULL, 'delete', NULL, '73', NULL, NULL, '2025-10-10 12:35:12'),
(511, NULL, 'delete', NULL, '72', NULL, NULL, '2025-10-10 12:35:13'),
(512, NULL, 'delete', NULL, '71', NULL, NULL, '2025-10-10 12:35:14'),
(513, NULL, 'delete', NULL, '70', NULL, NULL, '2025-10-10 12:35:15'),
(514, NULL, 'delete', NULL, '69', NULL, NULL, '2025-10-10 12:35:17'),
(515, NULL, 'delete', NULL, '68', NULL, NULL, '2025-10-10 12:35:18'),
(516, NULL, 'delete', NULL, '67', NULL, NULL, '2025-10-10 12:35:20'),
(517, NULL, 'delete', NULL, '66', NULL, NULL, '2025-10-10 12:35:21'),
(518, NULL, 'delete', NULL, '65', NULL, NULL, '2025-10-10 12:35:23'),
(519, NULL, 'delete', NULL, '64', NULL, NULL, '2025-10-10 12:35:24'),
(520, NULL, 'delete', NULL, '63', NULL, NULL, '2025-10-10 12:35:25'),
(521, NULL, 'delete', NULL, '62', NULL, NULL, '2025-10-10 12:35:27'),
(522, NULL, 'delete', NULL, '61', NULL, NULL, '2025-10-10 12:35:28'),
(523, NULL, 'delete', NULL, '60', NULL, NULL, '2025-10-10 12:35:30'),
(524, NULL, 'delete', NULL, '59', NULL, NULL, '2025-10-10 12:35:31'),
(525, NULL, 'delete', NULL, '58', NULL, NULL, '2025-10-10 12:35:32'),
(526, NULL, 'delete', NULL, '57', NULL, NULL, '2025-10-10 12:35:34'),
(527, NULL, 'delete', NULL, '56', NULL, NULL, '2025-10-10 12:35:35'),
(528, NULL, 'delete', NULL, '55', NULL, NULL, '2025-10-10 12:35:36'),
(529, NULL, 'delete', NULL, '54', NULL, NULL, '2025-10-10 12:35:37'),
(530, NULL, 'delete', NULL, '53', NULL, NULL, '2025-10-10 12:35:39'),
(531, NULL, 'delete', NULL, '52', NULL, NULL, '2025-10-10 12:35:40'),
(532, NULL, 'delete', NULL, '51', NULL, NULL, '2025-10-10 12:35:41'),
(533, NULL, 'delete', NULL, '50', NULL, NULL, '2025-10-10 12:35:43'),
(534, NULL, 'delete', NULL, '49', NULL, NULL, '2025-10-10 12:35:45'),
(535, NULL, 'delete', NULL, '48', NULL, NULL, '2025-10-10 12:35:46'),
(536, NULL, 'delete', NULL, '47', NULL, NULL, '2025-10-10 12:35:47'),
(537, NULL, 'delete', NULL, '46', NULL, NULL, '2025-10-10 12:35:49'),
(538, NULL, 'delete', NULL, '45', NULL, NULL, '2025-10-10 12:35:50'),
(539, NULL, 'delete', NULL, '40', NULL, NULL, '2025-10-10 12:35:53'),
(540, NULL, 'delete', NULL, '41', NULL, NULL, '2025-10-10 12:35:55'),
(541, NULL, 'delete', NULL, '42', NULL, NULL, '2025-10-10 12:35:56'),
(542, NULL, 'delete', NULL, '43', NULL, NULL, '2025-10-10 12:35:58'),
(543, NULL, 'delete', NULL, '44', NULL, NULL, '2025-10-10 12:35:59'),
(544, NULL, 'add', NULL, '76', NULL, NULL, '2025-10-10 12:36:07'),
(545, NULL, 'add', NULL, '77', NULL, NULL, '2025-10-10 12:36:07'),
(546, NULL, 'add', NULL, '78', NULL, NULL, '2025-10-10 12:36:07'),
(547, NULL, 'add', NULL, '79', NULL, NULL, '2025-10-10 12:36:07'),
(548, NULL, 'add', NULL, '80', NULL, NULL, '2025-10-10 12:36:07'),
(549, NULL, 'add', NULL, '81', NULL, NULL, '2025-10-10 12:36:07'),
(550, NULL, 'add', NULL, '82', NULL, NULL, '2025-10-10 12:36:07'),
(551, NULL, 'add', NULL, '83', NULL, NULL, '2025-10-10 12:36:07'),
(552, NULL, 'add', NULL, '84', NULL, NULL, '2025-10-10 12:36:07'),
(553, NULL, 'add', NULL, '85', NULL, NULL, '2025-10-10 12:36:07'),
(554, NULL, 'add', NULL, '86', NULL, NULL, '2025-10-10 12:36:07'),
(555, NULL, 'add', NULL, '87', NULL, NULL, '2025-10-10 12:36:07'),
(556, NULL, 'add', NULL, '88', NULL, NULL, '2025-10-10 12:36:28'),
(557, NULL, 'add', NULL, '89', NULL, NULL, '2025-10-10 12:36:28'),
(558, NULL, 'add', NULL, '90', NULL, NULL, '2025-10-10 12:36:28'),
(559, NULL, 'add', NULL, '91', NULL, NULL, '2025-10-10 12:36:28'),
(560, NULL, 'add', NULL, '92', NULL, NULL, '2025-10-10 12:36:28'),
(561, NULL, 'add', NULL, '93', NULL, NULL, '2025-10-10 12:36:28'),
(562, NULL, 'add', NULL, '94', NULL, NULL, '2025-10-10 12:36:28'),
(563, NULL, 'add', NULL, '95', NULL, NULL, '2025-10-10 12:36:28'),
(564, NULL, 'add', NULL, '96', NULL, NULL, '2025-10-10 12:36:28'),
(565, NULL, 'add', NULL, '97', NULL, NULL, '2025-10-10 12:36:28'),
(566, NULL, 'add', NULL, '98', NULL, NULL, '2025-10-10 12:36:28'),
(567, NULL, 'add', NULL, '99', NULL, NULL, '2025-10-10 12:36:29'),
(568, NULL, 'add', NULL, '100', NULL, NULL, '2025-10-10 12:36:52'),
(569, NULL, 'add', NULL, '101', NULL, NULL, '2025-10-10 12:36:52'),
(570, NULL, 'add', NULL, '102', NULL, NULL, '2025-10-10 12:36:52'),
(571, NULL, 'add', NULL, '103', NULL, NULL, '2025-10-10 12:36:52'),
(572, NULL, 'add', NULL, '104', NULL, NULL, '2025-10-10 12:36:52'),
(573, NULL, 'add', NULL, '105', NULL, NULL, '2025-10-10 12:36:52'),
(574, NULL, 'add', NULL, '106', NULL, NULL, '2025-10-10 12:36:52'),
(575, NULL, 'add', NULL, '107', NULL, NULL, '2025-10-10 12:36:52'),
(576, NULL, 'add', NULL, '108', NULL, NULL, '2025-10-10 12:36:52'),
(577, NULL, 'add', NULL, '109', NULL, NULL, '2025-10-10 12:36:52'),
(578, NULL, 'add', NULL, '110', NULL, NULL, '2025-10-10 12:36:52'),
(579, NULL, 'add', NULL, '111', NULL, NULL, '2025-10-10 12:36:52'),
(580, NULL, 'add', NULL, '112', NULL, NULL, '2025-10-10 12:36:52'),
(581, NULL, 'update_status', NULL, '88', NULL, NULL, '2025-10-10 12:37:17'),
(582, NULL, 'update_status', NULL, '89', NULL, NULL, '2025-10-10 12:37:18'),
(583, NULL, 'update_status', NULL, '90', NULL, NULL, '2025-10-10 12:37:20'),
(584, NULL, 'update_status', NULL, '91', NULL, NULL, '2025-10-10 12:37:23'),
(585, NULL, 'update_status', NULL, '92', NULL, NULL, '2025-10-10 12:37:26'),
(586, NULL, 'update_status', NULL, '93', NULL, NULL, '2025-10-10 12:37:30'),
(587, NULL, 'update_status', NULL, '94', NULL, NULL, '2025-10-10 12:37:31'),
(588, NULL, 'update_status', NULL, '95', NULL, NULL, '2025-10-10 12:37:34'),
(589, NULL, 'update_status', NULL, '96', NULL, NULL, '2025-10-10 12:37:36'),
(590, NULL, 'update_status', NULL, '97', NULL, NULL, '2025-10-10 12:37:37'),
(591, NULL, 'update_status', NULL, '98', NULL, NULL, '2025-10-10 12:37:38'),
(592, NULL, 'update_status', NULL, '99', NULL, NULL, '2025-10-10 12:37:42'),
(593, NULL, 'add', NULL, '113', NULL, NULL, '2025-10-10 12:42:28'),
(594, NULL, 'add', NULL, '114', NULL, NULL, '2025-10-10 12:42:28'),
(595, NULL, 'add', NULL, '115', NULL, NULL, '2025-10-10 12:42:28'),
(596, NULL, 'add', NULL, '116', NULL, NULL, '2025-10-10 12:42:28'),
(597, NULL, 'add', NULL, '117', NULL, NULL, '2025-10-10 12:42:28'),
(598, NULL, 'add', NULL, '118', NULL, NULL, '2025-10-10 12:42:28'),
(599, NULL, 'add', NULL, '119', NULL, NULL, '2025-10-10 12:42:28'),
(600, NULL, 'add', NULL, '120', NULL, NULL, '2025-10-10 12:42:28'),
(601, NULL, 'add', NULL, '121', NULL, NULL, '2025-10-10 12:42:28'),
(602, NULL, 'add', NULL, '122', NULL, NULL, '2025-10-10 12:42:28'),
(603, NULL, 'add', NULL, '123', NULL, NULL, '2025-10-10 12:42:28'),
(604, NULL, 'add', NULL, '124', NULL, NULL, '2025-10-10 12:42:29'),
(605, NULL, 'add', NULL, '125', NULL, NULL, '2025-10-10 12:42:29'),
(606, NULL, 'add', NULL, '126', NULL, NULL, '2025-10-10 12:42:42'),
(607, NULL, 'add', NULL, '127', NULL, NULL, '2025-10-10 12:42:42'),
(608, NULL, 'add', NULL, '128', NULL, NULL, '2025-10-10 12:42:42'),
(609, NULL, 'add', NULL, '129', NULL, NULL, '2025-10-10 12:42:42'),
(610, NULL, 'add', NULL, '130', NULL, NULL, '2025-10-10 12:42:42'),
(611, NULL, 'add', NULL, '131', NULL, NULL, '2025-10-10 12:42:42'),
(612, NULL, 'add', NULL, '132', NULL, NULL, '2025-10-10 12:42:42'),
(613, NULL, 'add', NULL, '133', NULL, NULL, '2025-10-10 12:42:42'),
(614, NULL, 'add', NULL, '134', NULL, NULL, '2025-10-10 12:42:42'),
(615, NULL, 'add', NULL, '135', NULL, NULL, '2025-10-10 12:42:42'),
(616, NULL, 'add', NULL, '136', NULL, NULL, '2025-10-10 12:42:42'),
(617, NULL, 'add', NULL, '137', NULL, NULL, '2025-10-10 12:42:42'),
(618, NULL, 'add', NULL, '138', NULL, NULL, '2025-10-10 12:42:42'),
(619, NULL, 'add', NULL, '139', NULL, NULL, '2025-10-10 12:50:11'),
(620, NULL, 'add', NULL, '140', NULL, NULL, '2025-10-10 12:50:11'),
(621, NULL, 'add', NULL, '141', NULL, NULL, '2025-10-10 12:50:11'),
(622, NULL, 'add', NULL, '142', NULL, NULL, '2025-10-10 12:50:11'),
(623, NULL, 'add', NULL, '143', NULL, NULL, '2025-10-10 12:50:11'),
(624, NULL, 'add', NULL, '144', NULL, NULL, '2025-10-10 12:50:11'),
(625, NULL, 'add', NULL, '145', NULL, NULL, '2025-10-10 12:50:11'),
(626, NULL, 'add', NULL, '146', NULL, NULL, '2025-10-10 12:50:11'),
(627, NULL, 'add', NULL, '147', NULL, NULL, '2025-10-10 12:50:11'),
(628, NULL, 'add', NULL, '148', NULL, NULL, '2025-10-10 12:50:11'),
(629, NULL, 'add', NULL, '149', NULL, NULL, '2025-10-10 12:50:11'),
(630, NULL, 'add', NULL, '150', NULL, NULL, '2025-10-10 12:50:12'),
(631, NULL, 'update_status', NULL, '27', NULL, NULL, '2025-10-10 12:57:55'),
(632, NULL, 'update_status', NULL, '27', NULL, NULL, '2025-10-10 12:57:57'),
(633, NULL, 'add', NULL, '151', NULL, NULL, '2025-10-10 13:01:58'),
(634, NULL, 'add', NULL, '152', NULL, NULL, '2025-10-10 13:01:58'),
(635, NULL, 'add', NULL, '153', NULL, NULL, '2025-10-10 13:01:58'),
(636, NULL, 'add', NULL, '154', NULL, NULL, '2025-10-10 13:01:58'),
(637, NULL, 'add', NULL, '155', NULL, NULL, '2025-10-10 13:01:58'),
(638, NULL, 'add', NULL, '156', NULL, NULL, '2025-10-10 13:01:58'),
(639, NULL, 'add', NULL, '157', NULL, NULL, '2025-10-10 13:01:58'),
(640, NULL, 'add', NULL, '158', NULL, NULL, '2025-10-10 13:01:58'),
(641, NULL, 'add', NULL, '159', NULL, NULL, '2025-10-10 13:01:58'),
(642, NULL, 'add', NULL, '160', NULL, NULL, '2025-10-10 13:01:58'),
(643, NULL, 'add', NULL, '161', NULL, NULL, '2025-10-10 13:01:58'),
(644, NULL, 'add', NULL, '162', NULL, NULL, '2025-10-10 13:01:58'),
(645, NULL, 'add', NULL, '163', NULL, NULL, '2025-10-10 13:01:58'),
(646, NULL, 'delete', NULL, '27', NULL, NULL, '2025-10-10 13:02:01'),
(647, NULL, 'update_due', NULL, '28', NULL, NULL, '2025-10-10 13:04:29'),
(648, NULL, 'delete', NULL, '29', NULL, NULL, '2025-10-10 13:12:32'),
(649, NULL, 'delete', NULL, '28', NULL, NULL, '2025-10-10 13:12:35'),
(650, NULL, 'update_due', NULL, '140', NULL, NULL, '2025-10-10 13:16:26'),
(651, NULL, 'update_due', NULL, '139', NULL, NULL, '2025-10-10 13:16:34'),
(652, NULL, 'update_due', NULL, '139', NULL, NULL, '2025-10-10 13:16:35'),
(653, NULL, 'update_due', NULL, '139', NULL, NULL, '2025-10-10 13:16:35'),
(654, NULL, 'update_due', NULL, '140', NULL, NULL, '2025-10-10 13:16:58'),
(655, NULL, 'update_due', NULL, '140', NULL, NULL, '2025-10-10 13:16:58'),
(656, NULL, 'update_due', NULL, '141', NULL, NULL, '2025-10-10 13:17:12'),
(657, NULL, 'update_due', NULL, '142', NULL, NULL, '2025-10-10 13:17:26'),
(658, NULL, 'update_due', NULL, '149', NULL, NULL, '2025-10-10 13:17:45'),
(659, NULL, 'update_due', NULL, '150', NULL, NULL, '2025-10-10 13:18:18'),
(660, NULL, 'update_due', NULL, '126', NULL, NULL, '2025-10-10 13:18:51'),
(661, NULL, 'update_due', NULL, '127', NULL, NULL, '2025-10-10 13:19:00'),
(662, NULL, 'update_due', NULL, '128', NULL, NULL, '2025-10-10 13:19:21'),
(663, NULL, 'update_due', NULL, '129', NULL, NULL, '2025-10-10 13:19:30'),
(664, NULL, 'update_due', NULL, '130', NULL, NULL, '2025-10-10 13:19:39'),
(665, NULL, 'update_due', NULL, '133', NULL, NULL, '2025-10-10 13:19:46');
INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `entity`, `entity_id`, `before_json`, `after_json`, `created_at`) VALUES
(666, NULL, 'add', NULL, '164', NULL, NULL, '2025-10-10 13:21:31'),
(667, NULL, 'add', NULL, '165', NULL, NULL, '2025-10-10 13:21:33'),
(668, NULL, 'add', NULL, '166', NULL, NULL, '2025-10-10 13:21:37'),
(669, NULL, 'add', NULL, '167', NULL, NULL, '2025-10-10 13:21:38'),
(670, NULL, 'add', NULL, '168', NULL, NULL, '2025-10-10 13:21:38'),
(671, NULL, 'add', NULL, '170', NULL, NULL, '2025-10-10 13:21:38'),
(672, NULL, 'add', NULL, '169', NULL, NULL, '2025-10-10 13:21:38'),
(673, NULL, 'add', NULL, '171', NULL, NULL, '2025-10-10 13:21:38'),
(674, NULL, 'add', NULL, '174', NULL, NULL, '2025-10-10 13:21:40'),
(675, NULL, 'add', NULL, '172', NULL, NULL, '2025-10-10 13:21:40'),
(676, NULL, 'add', NULL, '173', NULL, NULL, '2025-10-10 13:21:40'),
(677, NULL, 'add', NULL, '175', NULL, NULL, '2025-10-10 13:21:40'),
(678, NULL, 'add', NULL, '177', NULL, NULL, '2025-10-10 13:21:42'),
(679, NULL, 'add', NULL, '176', NULL, NULL, '2025-10-10 13:21:43'),
(680, NULL, 'add', NULL, '178', NULL, NULL, '2025-10-10 13:21:46'),
(681, NULL, 'add', NULL, '181', NULL, NULL, '2025-10-10 13:21:46'),
(682, NULL, 'add', NULL, '179', NULL, NULL, '2025-10-10 13:21:47'),
(683, NULL, 'add', NULL, '180', NULL, NULL, '2025-10-10 13:21:49'),
(684, NULL, 'add', NULL, '182', NULL, NULL, '2025-10-10 13:21:49'),
(685, NULL, 'add', NULL, '183', NULL, NULL, '2025-10-10 13:21:49'),
(686, NULL, 'add', NULL, '184', NULL, NULL, '2025-10-10 13:21:50'),
(687, NULL, 'add', NULL, '185', NULL, NULL, '2025-10-10 13:21:50'),
(688, NULL, 'add', NULL, '186', NULL, NULL, '2025-10-10 13:21:50'),
(689, NULL, 'add', NULL, '187', NULL, NULL, '2025-10-10 13:21:51'),
(690, NULL, 'add', NULL, '188', NULL, NULL, '2025-10-10 13:21:51'),
(691, NULL, 'add', NULL, '189', NULL, NULL, '2025-10-10 13:21:53'),
(692, NULL, 'add', NULL, '190', NULL, NULL, '2025-10-10 13:21:54'),
(693, NULL, 'add', NULL, '192', NULL, NULL, '2025-10-10 13:21:54'),
(694, NULL, 'add', NULL, '191', NULL, NULL, '2025-10-10 13:21:54'),
(695, NULL, 'add', NULL, '193', NULL, NULL, '2025-10-10 13:21:57'),
(696, NULL, 'add', NULL, '194', NULL, NULL, '2025-10-10 13:21:57'),
(697, NULL, 'add', NULL, '195', NULL, NULL, '2025-10-10 13:21:57'),
(698, NULL, 'add', NULL, '196', NULL, NULL, '2025-10-10 13:22:06'),
(699, NULL, 'add', NULL, '197', NULL, NULL, '2025-10-10 13:22:08'),
(700, NULL, 'add', NULL, '198', NULL, NULL, '2025-10-10 13:22:09'),
(701, NULL, 'add', NULL, '199', NULL, NULL, '2025-10-10 13:22:10'),
(702, NULL, 'add', NULL, '200', NULL, NULL, '2025-10-10 13:22:14'),
(703, NULL, 'add', NULL, '201', NULL, NULL, '2025-10-10 13:22:14'),
(704, NULL, 'add', NULL, '202', NULL, NULL, '2025-10-10 13:22:14'),
(705, NULL, 'add', NULL, '203', NULL, NULL, '2025-10-10 13:22:14'),
(706, NULL, 'add', NULL, '204', NULL, NULL, '2025-10-10 13:22:14'),
(707, NULL, 'add', NULL, '205', NULL, NULL, '2025-10-10 13:22:15'),
(708, NULL, 'add', NULL, '206', NULL, NULL, '2025-10-10 13:22:15'),
(709, NULL, 'add', NULL, '207', NULL, NULL, '2025-10-10 13:22:17'),
(710, NULL, 'add', NULL, '208', NULL, NULL, '2025-10-10 13:22:19'),
(711, NULL, 'add', NULL, '209', NULL, NULL, '2025-10-10 13:22:20'),
(712, NULL, 'add', NULL, '210', NULL, NULL, '2025-10-10 13:22:22'),
(713, NULL, 'add', NULL, '211', NULL, NULL, '2025-10-10 13:22:23'),
(714, NULL, 'add', NULL, '212', NULL, NULL, '2025-10-10 13:22:25'),
(715, NULL, 'add', NULL, '213', NULL, NULL, '2025-10-10 13:22:26'),
(716, NULL, 'add', NULL, '214', NULL, NULL, '2025-10-10 13:22:26'),
(717, NULL, 'add', NULL, '215', NULL, NULL, '2025-10-10 13:22:26'),
(718, NULL, 'add', NULL, '216', NULL, NULL, '2025-10-10 13:22:26'),
(719, NULL, 'add', NULL, '217', NULL, NULL, '2025-10-10 13:22:27'),
(720, NULL, 'add', NULL, '218', NULL, NULL, '2025-10-10 13:22:27'),
(721, NULL, 'add', NULL, '219', NULL, NULL, '2025-10-10 13:22:27'),
(722, NULL, 'add', NULL, '220', NULL, NULL, '2025-10-10 13:22:46'),
(723, NULL, 'add', NULL, '221', NULL, NULL, '2025-10-10 13:22:46'),
(724, NULL, 'add', NULL, '222', NULL, NULL, '2025-10-10 13:22:46'),
(725, NULL, 'add', NULL, '223', NULL, NULL, '2025-10-10 13:22:46'),
(726, NULL, 'add', NULL, '224', NULL, NULL, '2025-10-10 13:22:50'),
(727, NULL, 'add', NULL, '225', NULL, NULL, '2025-10-10 13:22:51'),
(728, NULL, 'add', NULL, '226', NULL, NULL, '2025-10-10 13:22:52'),
(729, NULL, 'add', NULL, '227', NULL, NULL, '2025-10-10 13:22:54'),
(730, NULL, 'add', NULL, '228', NULL, NULL, '2025-10-10 13:22:55'),
(731, NULL, 'add', NULL, '229', NULL, NULL, '2025-10-10 13:22:57'),
(732, NULL, 'add', NULL, '230', NULL, NULL, '2025-10-10 13:22:57'),
(733, NULL, 'add', NULL, '231', NULL, NULL, '2025-10-10 13:23:02'),
(734, NULL, 'add', NULL, '232', NULL, NULL, '2025-10-10 13:23:04'),
(735, NULL, 'delete', NULL, '113', NULL, NULL, '2025-10-10 13:23:29'),
(736, NULL, 'delete', NULL, '114', NULL, NULL, '2025-10-10 13:23:32'),
(737, NULL, 'delete', NULL, '115', NULL, NULL, '2025-10-10 13:23:36'),
(738, NULL, 'add', NULL, '233', NULL, NULL, '2025-10-10 13:28:07'),
(739, NULL, 'add', NULL, '234', NULL, NULL, '2025-10-10 13:28:10'),
(740, NULL, 'add', NULL, '235', NULL, NULL, '2025-10-10 13:28:13'),
(741, NULL, 'add', NULL, '236', NULL, NULL, '2025-10-10 13:28:15'),
(742, NULL, 'add', NULL, '237', NULL, NULL, '2025-10-10 13:28:16'),
(743, NULL, 'add', NULL, '238', NULL, NULL, '2025-10-10 13:28:16'),
(744, NULL, 'add', NULL, '239', NULL, NULL, '2025-10-10 13:28:18'),
(745, NULL, 'add', NULL, '240', NULL, NULL, '2025-10-10 13:28:20'),
(746, NULL, 'add', NULL, '241', NULL, NULL, '2025-10-10 13:28:21'),
(747, NULL, 'add', NULL, '242', NULL, NULL, '2025-10-10 13:28:23'),
(748, NULL, 'add', NULL, '243', NULL, NULL, '2025-10-10 13:28:25'),
(749, NULL, 'add', NULL, '244', NULL, NULL, '2025-10-10 13:28:26'),
(750, NULL, 'update_due', NULL, '106', NULL, NULL, '2025-10-10 13:29:16'),
(751, NULL, 'delete', NULL, '43', NULL, NULL, '2025-10-10 13:31:08'),
(752, NULL, 'delete', NULL, '43', NULL, NULL, '2025-10-10 13:31:08'),
(753, NULL, 'delete', NULL, '39', NULL, NULL, '2025-10-10 13:31:19'),
(754, NULL, 'update_status', 'applicant', '24', NULL, NULL, '2025-10-10 07:31:31'),
(755, NULL, 'add_from_applicant', NULL, '45', NULL, NULL, '2025-10-10 13:31:34'),
(756, NULL, 'update_due', NULL, '30', NULL, NULL, '2025-10-10 13:33:57'),
(757, NULL, 'add', NULL, '245', NULL, NULL, '2025-10-10 13:34:08'),
(758, NULL, 'add', NULL, '246', NULL, NULL, '2025-10-10 13:34:09'),
(759, NULL, 'add', NULL, '247', NULL, NULL, '2025-10-10 13:34:12'),
(760, NULL, 'add', NULL, '248', NULL, NULL, '2025-10-10 13:34:13'),
(761, NULL, 'add', NULL, '249', NULL, NULL, '2025-10-10 13:34:15'),
(762, NULL, 'add', NULL, '250', NULL, NULL, '2025-10-10 13:34:16'),
(763, NULL, 'add', NULL, '251', NULL, NULL, '2025-10-10 13:34:17'),
(764, NULL, 'add', NULL, '252', NULL, NULL, '2025-10-10 13:34:18'),
(765, NULL, 'add', NULL, '253', NULL, NULL, '2025-10-10 13:34:19'),
(766, NULL, 'add', NULL, '254', NULL, NULL, '2025-10-10 13:34:22'),
(767, NULL, 'add', NULL, '255', NULL, NULL, '2025-10-10 13:34:23'),
(768, NULL, 'add', NULL, '256', NULL, NULL, '2025-10-10 13:34:26'),
(769, NULL, 'add', NULL, '257', NULL, NULL, '2025-10-10 13:37:19'),
(770, NULL, 'add', NULL, '258', NULL, NULL, '2025-10-10 13:37:20'),
(771, NULL, 'add', NULL, '259', NULL, NULL, '2025-10-10 13:37:23'),
(772, NULL, 'add', NULL, '260', NULL, NULL, '2025-10-10 13:37:26'),
(773, NULL, 'add', NULL, '261', NULL, NULL, '2025-10-10 13:37:27'),
(774, NULL, 'add', NULL, '262', NULL, NULL, '2025-10-10 13:37:30'),
(775, NULL, 'add', NULL, '263', NULL, NULL, '2025-10-10 13:37:32'),
(776, NULL, 'add', NULL, '264', NULL, NULL, '2025-10-10 13:37:33'),
(777, NULL, 'add', NULL, '265', NULL, NULL, '2025-10-10 13:37:36'),
(778, NULL, 'add', NULL, '266', NULL, NULL, '2025-10-10 13:37:38'),
(779, NULL, 'add', NULL, '267', NULL, NULL, '2025-10-10 13:37:39'),
(780, NULL, 'add', NULL, '268', NULL, NULL, '2025-10-10 13:37:41'),
(781, NULL, 'delete', NULL, '14', NULL, NULL, '2025-10-10 13:40:53'),
(782, NULL, 'delete', NULL, '15', NULL, NULL, '2025-10-10 13:40:55'),
(783, NULL, 'delete', NULL, '16', NULL, NULL, '2025-10-10 13:40:56'),
(784, NULL, 'add', NULL, '269', NULL, NULL, '2025-10-10 13:40:58'),
(785, NULL, 'add', NULL, '270', NULL, NULL, '2025-10-10 13:40:58'),
(786, NULL, 'add', NULL, '271', NULL, NULL, '2025-10-10 13:40:58'),
(787, NULL, 'add', NULL, '272', NULL, NULL, '2025-10-10 13:40:58'),
(788, NULL, 'add', NULL, '273', NULL, NULL, '2025-10-10 13:40:58'),
(789, NULL, 'add', NULL, '274', NULL, NULL, '2025-10-10 13:40:58'),
(790, NULL, 'add', NULL, '275', NULL, NULL, '2025-10-10 13:40:58'),
(791, NULL, 'add', NULL, '276', NULL, NULL, '2025-10-10 13:40:59'),
(792, NULL, 'add', NULL, '277', NULL, NULL, '2025-10-10 13:40:59'),
(793, NULL, 'update_due', NULL, '269', NULL, NULL, '2025-10-10 13:41:07'),
(794, NULL, 'update_due', NULL, '270', NULL, NULL, '2025-10-10 13:41:12'),
(795, NULL, 'update_due', NULL, '271', NULL, NULL, '2025-10-10 13:41:18'),
(796, NULL, 'update_due', NULL, '275', NULL, NULL, '2025-10-10 13:41:45'),
(797, NULL, 'update_due', NULL, '100', NULL, NULL, '2025-10-10 13:42:05'),
(798, NULL, 'delete', NULL, '19', NULL, NULL, '2025-10-10 13:43:04'),
(799, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-10 07:43:13'),
(800, NULL, 'add_from_applicant', NULL, '46', NULL, NULL, '2025-10-10 13:43:13'),
(801, NULL, 'public_apply', 'applicant', '26', NULL, NULL, '2025-10-10 07:50:57'),
(802, NULL, 'schedule_interview', 'applicant', '26', NULL, NULL, '2025-10-10 07:51:28'),
(803, NULL, 'notify', 'applicant', '26', NULL, NULL, '2025-10-10 07:51:32'),
(804, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-10 07:53:28'),
(805, NULL, 'toggle_shortlist', 'applicant', '12', NULL, NULL, '2025-10-10 07:53:30'),
(806, NULL, 'update_status', 'applicant', '26', NULL, NULL, '2025-10-10 07:53:44'),
(807, NULL, 'add_from_applicant', NULL, '47', NULL, NULL, '2025-10-10 13:53:45'),
(808, NULL, 'notify', 'applicant', '26', NULL, NULL, '2025-10-10 07:53:56'),
(809, NULL, 'add', NULL, '278', NULL, NULL, '2025-10-10 13:54:31'),
(810, NULL, 'add', NULL, '279', NULL, NULL, '2025-10-10 13:54:31'),
(811, NULL, 'add', NULL, '280', NULL, NULL, '2025-10-10 13:54:31'),
(812, NULL, 'add', NULL, '281', NULL, NULL, '2025-10-10 13:54:31'),
(813, NULL, 'add', NULL, '282', NULL, NULL, '2025-10-10 13:54:31'),
(814, NULL, 'add', NULL, '283', NULL, NULL, '2025-10-10 13:54:31'),
(815, NULL, 'add', NULL, '284', NULL, NULL, '2025-10-10 13:54:31'),
(816, NULL, 'add', NULL, '285', NULL, NULL, '2025-10-10 13:54:31'),
(817, NULL, 'add', NULL, '286', NULL, NULL, '2025-10-10 13:54:31'),
(818, NULL, 'add', NULL, '287', NULL, NULL, '2025-10-10 13:54:31'),
(819, NULL, 'add', NULL, '288', NULL, NULL, '2025-10-10 13:54:31'),
(820, NULL, 'add', NULL, '289', NULL, NULL, '2025-10-10 13:54:32'),
(821, NULL, 'update_due', NULL, '278', NULL, NULL, '2025-10-10 13:54:45'),
(822, NULL, 'update_status', NULL, '278', NULL, NULL, '2025-10-10 13:54:50'),
(823, NULL, 'update_status', NULL, '279', NULL, NULL, '2025-10-10 13:54:55'),
(824, NULL, 'update_status', NULL, '280', NULL, NULL, '2025-10-10 13:54:58'),
(825, NULL, 'update_status', NULL, '281', NULL, NULL, '2025-10-10 13:55:01'),
(826, NULL, 'update_status', NULL, '283', NULL, NULL, '2025-10-10 13:55:05'),
(827, NULL, 'submit_score', 'applicant', '15', NULL, NULL, '2025-10-10 08:07:29'),
(828, NULL, 'set_status', 'applicant', '15', NULL, NULL, '2025-10-10 08:07:29'),
(829, NULL, 'public_apply', 'applicant', '27', NULL, NULL, '2025-10-10 08:12:11'),
(830, NULL, 'notify', 'applicant', '27', NULL, NULL, '2025-10-10 08:12:42'),
(831, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-10 08:14:32'),
(832, NULL, 'archive', 'applicant', '10', NULL, NULL, '2025-10-10 08:14:33'),
(833, NULL, 'archive', 'applicant', '9', NULL, NULL, '2025-10-10 08:15:00'),
(834, NULL, 'update_status', 'applicant', '27', NULL, NULL, '2025-10-10 08:16:23'),
(835, NULL, 'add_from_applicant', NULL, '48', NULL, NULL, '2025-10-10 14:16:23'),
(836, NULL, 'add', NULL, '290', NULL, NULL, '2025-10-10 14:16:41'),
(837, NULL, 'add', NULL, '291', NULL, NULL, '2025-10-10 14:16:41'),
(838, NULL, 'add', NULL, '292', NULL, NULL, '2025-10-10 14:16:41'),
(839, NULL, 'add', NULL, '293', NULL, NULL, '2025-10-10 14:16:41'),
(840, NULL, 'add', NULL, '294', NULL, NULL, '2025-10-10 14:16:41'),
(841, NULL, 'add', NULL, '295', NULL, NULL, '2025-10-10 14:16:41'),
(842, NULL, 'add', NULL, '296', NULL, NULL, '2025-10-10 14:16:41'),
(843, NULL, 'add', NULL, '297', NULL, NULL, '2025-10-10 14:16:41'),
(844, NULL, 'add', NULL, '298', NULL, NULL, '2025-10-10 14:16:41'),
(845, NULL, 'add', NULL, '299', NULL, NULL, '2025-10-10 14:16:41'),
(846, NULL, 'add', NULL, '300', NULL, NULL, '2025-10-10 14:16:41'),
(847, NULL, 'add', NULL, '301', NULL, NULL, '2025-10-10 14:16:41'),
(848, NULL, 'update_due', NULL, '290', NULL, NULL, '2025-10-10 14:16:58'),
(849, NULL, 'update_due', NULL, '290', NULL, NULL, '2025-10-10 14:17:03'),
(850, NULL, 'archive', 'applicant', '9', NULL, NULL, '2025-10-10 08:18:11'),
(851, NULL, 'unarchive', 'applicant', '9', NULL, NULL, '2025-10-10 14:18:22'),
(852, NULL, 'update_status', 'applicant', '12', NULL, NULL, '2025-10-10 08:33:59'),
(853, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-10 10:29:12'),
(854, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-10 10:39:18'),
(855, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-10 10:41:53'),
(856, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-10 10:41:58'),
(857, NULL, 'public_apply', 'applicant', '28', NULL, NULL, '2025-10-10 10:43:12'),
(858, NULL, 'update_status', 'applicant', '28', NULL, NULL, '2025-10-10 10:43:35'),
(859, NULL, 'add_from_applicant', NULL, '49', NULL, NULL, '2025-10-10 16:43:35'),
(860, NULL, 'update_status', 'applicant', '28', NULL, NULL, '2025-10-10 10:47:30'),
(861, NULL, 'update_status', 'applicant', '28', NULL, NULL, '2025-10-10 10:48:19'),
(862, NULL, 'update_status', 'applicant', '28', NULL, NULL, '2025-10-10 10:56:41'),
(863, NULL, 'update_status', 'applicant', '9', NULL, NULL, '2025-10-10 11:00:55'),
(864, NULL, 'add', NULL, '302', NULL, NULL, '2025-10-10 17:08:15'),
(865, NULL, 'add', NULL, '303', NULL, NULL, '2025-10-10 17:08:16'),
(866, NULL, 'add', NULL, '304', NULL, NULL, '2025-10-10 17:08:16'),
(867, NULL, 'add', NULL, '305', NULL, NULL, '2025-10-10 17:08:16'),
(868, NULL, 'add', NULL, '306', NULL, NULL, '2025-10-10 17:08:16'),
(869, NULL, 'add', NULL, '307', NULL, NULL, '2025-10-10 17:08:16'),
(870, NULL, 'add', NULL, '308', NULL, NULL, '2025-10-10 17:08:16'),
(871, NULL, 'add', NULL, '309', NULL, NULL, '2025-10-10 17:08:16'),
(872, NULL, 'add', NULL, '310', NULL, NULL, '2025-10-10 17:08:16'),
(873, NULL, 'add', NULL, '311', NULL, NULL, '2025-10-10 17:08:16'),
(874, NULL, 'add', NULL, '312', NULL, NULL, '2025-10-10 17:08:16'),
(875, NULL, 'add', NULL, '313', NULL, NULL, '2025-10-10 17:08:16'),
(876, NULL, 'add', NULL, '314', NULL, NULL, '2025-10-10 18:43:52'),
(877, NULL, 'add', NULL, '315', NULL, NULL, '2025-10-10 18:43:52'),
(878, NULL, 'add', NULL, '316', NULL, NULL, '2025-10-10 18:43:52'),
(879, NULL, 'add', NULL, '317', NULL, NULL, '2025-10-10 18:43:52'),
(880, NULL, 'add', NULL, '318', NULL, NULL, '2025-10-10 18:43:52'),
(881, NULL, 'add', NULL, '319', NULL, NULL, '2025-10-10 18:43:52'),
(882, NULL, 'add', NULL, '320', NULL, NULL, '2025-10-10 18:43:52'),
(883, NULL, 'add', NULL, '321', NULL, NULL, '2025-10-10 18:43:52'),
(884, NULL, 'add', NULL, '322', NULL, NULL, '2025-10-10 18:43:53'),
(885, NULL, 'add', NULL, '323', NULL, NULL, '2025-10-10 18:43:53'),
(886, NULL, 'add', NULL, '324', NULL, NULL, '2025-10-10 18:43:53'),
(887, NULL, 'add', NULL, '325', NULL, NULL, '2025-10-10 18:43:53'),
(888, NULL, 'update_due', NULL, '314', NULL, NULL, '2025-10-10 18:44:01'),
(889, NULL, 'update_due', NULL, '315', NULL, NULL, '2025-10-10 18:44:07'),
(890, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-10 14:00:11'),
(891, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-10 14:00:11'),
(892, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-10 14:00:36'),
(893, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-10 14:00:36'),
(894, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-10 14:15:22'),
(895, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-10 14:15:22'),
(896, NULL, 'notify', 'applicant', '12', NULL, NULL, '2025-10-10 14:20:45'),
(897, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-10 14:33:45'),
(898, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-10 14:33:46'),
(899, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-10 14:45:29'),
(900, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-10 14:45:29'),
(901, NULL, 'submit_score', 'applicant', '12', NULL, NULL, '2025-10-10 14:45:43'),
(902, NULL, 'set_status', 'applicant', '12', NULL, NULL, '2025-10-10 14:45:45'),
(903, NULL, 'add_from_applicant', NULL, '50', NULL, NULL, '2025-10-10 20:51:05'),
(904, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-10 15:38:02'),
(905, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-10 15:38:02'),
(906, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 15:44:55'),
(907, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 15:44:55'),
(908, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 15:44:57'),
(909, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 15:44:57'),
(910, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 15:44:58'),
(911, NULL, 'submit_score', 'applicant', '20', NULL, NULL, '2025-10-10 15:53:17'),
(912, NULL, 'set_status', 'applicant', '20', NULL, NULL, '2025-10-10 15:53:17'),
(913, NULL, 'archive', 'applicant', '21', NULL, NULL, '2025-10-10 15:53:35'),
(914, NULL, 'archive', 'applicant', '10', NULL, NULL, '2025-10-10 15:53:46'),
(915, NULL, 'archive', 'applicant', '11', NULL, NULL, '2025-10-10 15:53:50'),
(916, NULL, 'toggle_shortlist', 'applicant', '11', NULL, NULL, '2025-10-10 15:54:08'),
(917, NULL, 'toggle_shortlist', 'applicant', '11', NULL, NULL, '2025-10-10 15:54:08'),
(918, NULL, 'archive', 'applicant', '10', NULL, NULL, '2025-10-10 15:54:11'),
(919, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 16:03:06'),
(920, NULL, 'toggle_shortlist', 'applicant', '23', NULL, NULL, '2025-10-10 16:03:06'),
(921, NULL, 'add', NULL, '326', NULL, NULL, '2025-10-10 22:09:11'),
(922, NULL, 'add', NULL, '327', NULL, NULL, '2025-10-10 22:09:11'),
(923, NULL, 'add', NULL, '328', NULL, NULL, '2025-10-10 22:09:11'),
(924, NULL, 'add', NULL, '329', NULL, NULL, '2025-10-10 22:09:11'),
(925, NULL, 'add', NULL, '330', NULL, NULL, '2025-10-10 22:09:11'),
(926, NULL, 'add', NULL, '331', NULL, NULL, '2025-10-10 22:09:11'),
(927, NULL, 'add', NULL, '332', NULL, NULL, '2025-10-10 22:09:11'),
(928, NULL, 'add', NULL, '333', NULL, NULL, '2025-10-10 22:09:11'),
(929, NULL, 'add', NULL, '334', NULL, NULL, '2025-10-10 22:09:11'),
(930, NULL, 'add', NULL, '335', NULL, NULL, '2025-10-10 22:09:12'),
(931, NULL, 'add', NULL, '336', NULL, NULL, '2025-10-10 22:09:12'),
(932, NULL, 'add', NULL, '337', NULL, NULL, '2025-10-10 22:09:12'),
(933, NULL, 'update_due', NULL, '326', NULL, NULL, '2025-10-10 22:09:19'),
(934, NULL, 'update_due', NULL, '327', NULL, NULL, '2025-10-10 22:09:27'),
(935, NULL, 'delete', NULL, '30', NULL, NULL, '2025-10-10 22:09:36'),
(936, NULL, 'delete', NULL, '31', NULL, NULL, '2025-10-10 22:09:38'),
(937, NULL, 'delete', NULL, '32', NULL, NULL, '2025-10-10 22:09:39'),
(938, NULL, 'delete', NULL, '33', NULL, NULL, '2025-10-10 22:09:40'),
(939, NULL, 'delete', NULL, '34', NULL, NULL, '2025-10-10 22:09:42'),
(940, NULL, 'delete', NULL, '35', NULL, NULL, '2025-10-10 22:09:43'),
(941, NULL, 'delete', NULL, '36', NULL, NULL, '2025-10-10 22:09:48'),
(942, NULL, 'delete', NULL, '37', NULL, NULL, '2025-10-10 22:09:49'),
(943, NULL, 'delete', NULL, '38', NULL, NULL, '2025-10-10 22:09:51'),
(944, NULL, 'delete', NULL, '39', NULL, NULL, '2025-10-10 22:09:52'),
(945, NULL, 'delete', NULL, '151', NULL, NULL, '2025-10-10 22:09:53'),
(946, NULL, 'delete', NULL, '152', NULL, NULL, '2025-10-10 22:09:55'),
(947, NULL, 'delete', NULL, '153', NULL, NULL, '2025-10-10 22:09:56'),
(948, NULL, 'delete', NULL, '154', NULL, NULL, '2025-10-10 22:09:57'),
(949, NULL, 'delete', NULL, '155', NULL, NULL, '2025-10-10 22:09:58'),
(950, NULL, 'delete', NULL, '156', NULL, NULL, '2025-10-10 22:09:59'),
(951, NULL, 'delete', NULL, '157', NULL, NULL, '2025-10-10 22:10:01'),
(952, NULL, 'delete', NULL, '158', NULL, NULL, '2025-10-10 22:10:02'),
(953, NULL, 'delete', NULL, '159', NULL, NULL, '2025-10-10 22:10:03'),
(954, NULL, 'delete', NULL, '160', NULL, NULL, '2025-10-10 22:10:04'),
(955, NULL, 'delete', NULL, '161', NULL, NULL, '2025-10-10 22:10:06'),
(956, NULL, 'delete', NULL, '162', NULL, NULL, '2025-10-10 22:10:08'),
(957, NULL, 'delete', NULL, '163', NULL, NULL, '2025-10-10 22:10:09'),
(958, NULL, 'delete', NULL, '139', NULL, NULL, '2025-10-10 22:10:45'),
(959, NULL, 'delete', NULL, '140', NULL, NULL, '2025-10-10 22:10:50'),
(960, NULL, 'delete', NULL, '141', NULL, NULL, '2025-10-10 22:10:53'),
(961, NULL, 'delete', NULL, '142', NULL, NULL, '2025-10-10 22:10:54'),
(962, NULL, 'delete', NULL, '143', NULL, NULL, '2025-10-10 22:10:55'),
(963, NULL, 'delete', NULL, '144', NULL, NULL, '2025-10-10 22:10:57'),
(964, NULL, 'delete', NULL, '145', NULL, NULL, '2025-10-10 22:10:59'),
(965, NULL, 'delete', NULL, '146', NULL, NULL, '2025-10-10 22:11:01'),
(966, NULL, 'delete', NULL, '147', NULL, NULL, '2025-10-10 22:11:03'),
(967, NULL, 'delete', NULL, '148', NULL, NULL, '2025-10-10 22:11:05'),
(968, NULL, 'delete', NULL, '149', NULL, NULL, '2025-10-10 22:11:07'),
(969, NULL, 'update_due', NULL, '150', NULL, NULL, '2025-10-10 22:11:12'),
(970, NULL, 'delete', NULL, '150', NULL, NULL, '2025-10-10 22:11:16'),
(971, NULL, 'delete', NULL, '302', NULL, NULL, '2025-10-10 22:11:18'),
(972, NULL, 'delete', NULL, '303', NULL, NULL, '2025-10-10 22:11:19'),
(973, NULL, 'delete', NULL, '304', NULL, NULL, '2025-10-10 22:11:21'),
(974, NULL, 'delete', NULL, '305', NULL, NULL, '2025-10-10 22:11:24'),
(975, NULL, 'delete', NULL, '306', NULL, NULL, '2025-10-10 22:11:26'),
(976, NULL, 'delete', NULL, '307', NULL, NULL, '2025-10-10 22:11:28'),
(977, NULL, 'delete', NULL, '308', NULL, NULL, '2025-10-10 22:11:30'),
(978, NULL, 'delete', NULL, '309', NULL, NULL, '2025-10-10 22:11:32'),
(979, NULL, 'delete', NULL, '310', NULL, NULL, '2025-10-10 22:11:33'),
(980, NULL, 'delete', NULL, '311', NULL, NULL, '2025-10-10 22:11:35'),
(981, NULL, 'delete', NULL, '312', NULL, NULL, '2025-10-10 22:11:36'),
(982, NULL, 'delete', NULL, '313', NULL, NULL, '2025-10-10 22:11:37'),
(983, NULL, 'archive', 'applicant', '28', NULL, NULL, '2025-10-10 16:20:08'),
(984, NULL, 'delete', 'applicant', '28', NULL, NULL, '2025-10-10 16:20:24'),
(985, NULL, 'delete', NULL, '100', NULL, NULL, '2025-10-10 22:21:37'),
(986, NULL, 'delete', NULL, '101', NULL, NULL, '2025-10-10 22:21:39'),
(987, NULL, 'delete', NULL, '102', NULL, NULL, '2025-10-10 22:21:40'),
(988, NULL, 'delete', NULL, '103', NULL, NULL, '2025-10-10 22:21:42'),
(989, NULL, 'delete', NULL, '104', NULL, NULL, '2025-10-10 22:21:43'),
(990, NULL, 'delete', NULL, '105', NULL, NULL, '2025-10-10 22:21:45'),
(991, NULL, 'delete', NULL, '106', NULL, NULL, '2025-10-10 22:21:47'),
(992, NULL, 'delete', NULL, '107', NULL, NULL, '2025-10-10 22:21:48'),
(993, NULL, 'delete', NULL, '108', NULL, NULL, '2025-10-10 22:21:50'),
(994, NULL, 'delete', NULL, '109', NULL, NULL, '2025-10-10 22:21:51'),
(995, NULL, 'delete', NULL, '110', NULL, NULL, '2025-10-10 22:21:53'),
(996, NULL, 'delete', NULL, '111', NULL, NULL, '2025-10-10 22:21:54'),
(997, NULL, 'delete', NULL, '112', NULL, NULL, '2025-10-10 22:21:56'),
(998, NULL, 'delete', NULL, '233', NULL, NULL, '2025-10-10 22:22:09'),
(999, NULL, 'delete', NULL, '234', NULL, NULL, '2025-10-10 22:22:10'),
(1000, NULL, 'delete', NULL, '235', NULL, NULL, '2025-10-10 22:22:11'),
(1001, NULL, 'delete', NULL, '236', NULL, NULL, '2025-10-10 22:22:13'),
(1002, NULL, 'delete', NULL, '237', NULL, NULL, '2025-10-10 22:22:14'),
(1003, NULL, 'delete', NULL, '238', NULL, NULL, '2025-10-10 22:22:17'),
(1004, NULL, 'delete', NULL, '239', NULL, NULL, '2025-10-10 22:22:19'),
(1005, NULL, 'delete', NULL, '240', NULL, NULL, '2025-10-10 22:22:21'),
(1006, NULL, 'delete', NULL, '241', NULL, NULL, '2025-10-10 22:22:22'),
(1007, NULL, 'delete', NULL, '242', NULL, NULL, '2025-10-10 22:22:24'),
(1008, NULL, 'delete', NULL, '243', NULL, NULL, '2025-10-10 22:22:25'),
(1009, NULL, 'delete', NULL, '244', NULL, NULL, '2025-10-10 22:22:27'),
(1010, NULL, 'delete', NULL, '326', NULL, NULL, '2025-10-10 22:22:58'),
(1011, NULL, 'delete', NULL, '327', NULL, NULL, '2025-10-10 22:23:00'),
(1012, NULL, 'delete', NULL, '328', NULL, NULL, '2025-10-10 22:23:01'),
(1013, NULL, 'delete', NULL, '329', NULL, NULL, '2025-10-10 22:23:03'),
(1014, NULL, 'delete', NULL, '330', NULL, NULL, '2025-10-10 22:23:05'),
(1015, NULL, 'delete', NULL, '331', NULL, NULL, '2025-10-10 22:23:06'),
(1016, NULL, 'delete', NULL, '332', NULL, NULL, '2025-10-10 22:23:07'),
(1017, NULL, 'delete', NULL, '333', NULL, NULL, '2025-10-10 22:23:11'),
(1018, NULL, 'delete', NULL, '334', NULL, NULL, '2025-10-10 22:23:14'),
(1019, NULL, 'delete', NULL, '335', NULL, NULL, '2025-10-10 22:23:15'),
(1020, NULL, 'delete', NULL, '336', NULL, NULL, '2025-10-10 22:23:16'),
(1021, NULL, 'delete', NULL, '337', NULL, NULL, '2025-10-10 22:23:18'),
(1022, NULL, 'delete', NULL, '290', NULL, NULL, '2025-10-10 22:23:23'),
(1023, NULL, 'delete', NULL, '291', NULL, NULL, '2025-10-10 22:23:25'),
(1024, NULL, 'delete', NULL, '292', NULL, NULL, '2025-10-10 22:23:27'),
(1025, NULL, 'delete', NULL, '293', NULL, NULL, '2025-10-10 22:23:31'),
(1026, NULL, 'delete', NULL, '294', NULL, NULL, '2025-10-10 22:23:32'),
(1027, NULL, 'delete', NULL, '295', NULL, NULL, '2025-10-10 22:23:34'),
(1028, NULL, 'delete', NULL, '296', NULL, NULL, '2025-10-10 22:23:35'),
(1029, NULL, 'delete', NULL, '297', NULL, NULL, '2025-10-10 22:23:37'),
(1030, NULL, 'delete', NULL, '298', NULL, NULL, '2025-10-10 22:23:38'),
(1031, NULL, 'delete', NULL, '299', NULL, NULL, '2025-10-10 22:23:39'),
(1032, NULL, 'delete', NULL, '300', NULL, NULL, '2025-10-10 22:23:43'),
(1033, NULL, 'delete', NULL, '301', NULL, NULL, '2025-10-10 22:23:45'),
(1034, NULL, 'delete', NULL, '269', NULL, NULL, '2025-10-10 22:24:15'),
(1035, NULL, 'delete', NULL, '270', NULL, NULL, '2025-10-10 22:24:16'),
(1036, NULL, 'delete', NULL, '271', NULL, NULL, '2025-10-10 22:24:17'),
(1037, NULL, 'delete', NULL, '272', NULL, NULL, '2025-10-10 22:24:19'),
(1038, NULL, 'delete', NULL, '273', NULL, NULL, '2025-10-10 22:24:20'),
(1039, NULL, 'delete', NULL, '274', NULL, NULL, '2025-10-10 22:24:21'),
(1040, NULL, 'delete', NULL, '275', NULL, NULL, '2025-10-10 22:24:22'),
(1041, NULL, 'delete', NULL, '276', NULL, NULL, '2025-10-10 22:24:23'),
(1042, NULL, 'delete', NULL, '277', NULL, NULL, '2025-10-10 22:24:24'),
(1043, NULL, 'delete', NULL, '196', NULL, NULL, '2025-10-10 22:24:30'),
(1044, NULL, 'delete', NULL, '197', NULL, NULL, '2025-10-10 22:24:32'),
(1045, NULL, 'delete', NULL, '198', NULL, NULL, '2025-10-10 22:24:34'),
(1046, NULL, 'delete', NULL, '199', NULL, NULL, '2025-10-10 22:24:35'),
(1047, NULL, 'delete', NULL, '200', NULL, NULL, '2025-10-10 22:24:36'),
(1048, NULL, 'delete', NULL, '201', NULL, NULL, '2025-10-10 22:24:38'),
(1049, NULL, 'delete', NULL, '202', NULL, NULL, '2025-10-10 22:24:39'),
(1050, NULL, 'delete', NULL, '203', NULL, NULL, '2025-10-10 22:24:40'),
(1051, NULL, 'delete', NULL, '204', NULL, NULL, '2025-10-10 22:24:41'),
(1052, NULL, 'delete', NULL, '205', NULL, NULL, '2025-10-10 22:24:43'),
(1053, NULL, 'delete', NULL, '206', NULL, NULL, '2025-10-10 22:24:44'),
(1054, NULL, 'delete', NULL, '207', NULL, NULL, '2025-10-10 22:24:46'),
(1055, NULL, 'delete', NULL, '208', NULL, NULL, '2025-10-10 22:24:47'),
(1056, NULL, 'delete', NULL, '209', NULL, NULL, '2025-10-10 22:24:48'),
(1057, NULL, 'delete', NULL, '210', NULL, NULL, '2025-10-10 22:24:49'),
(1058, NULL, 'delete', NULL, '211', NULL, NULL, '2025-10-10 22:24:51'),
(1059, NULL, 'delete', NULL, '212', NULL, NULL, '2025-10-10 22:24:52'),
(1060, NULL, 'delete', NULL, '213', NULL, NULL, '2025-10-10 22:24:54'),
(1061, NULL, 'update_due', NULL, '215', NULL, NULL, '2025-10-10 22:24:57'),
(1062, NULL, 'update_due', NULL, '215', NULL, NULL, '2025-10-10 22:25:00'),
(1063, NULL, 'delete', NULL, '215', NULL, NULL, '2025-10-10 22:25:01'),
(1064, NULL, 'delete', NULL, '214', NULL, NULL, '2025-10-10 22:25:03'),
(1065, NULL, 'delete', NULL, '216', NULL, NULL, '2025-10-10 22:25:05'),
(1066, NULL, 'delete', NULL, '217', NULL, NULL, '2025-10-10 22:25:06'),
(1067, NULL, 'delete', NULL, '218', NULL, NULL, '2025-10-10 22:25:11'),
(1068, NULL, 'delete', NULL, '219', NULL, NULL, '2025-10-10 22:25:12'),
(1069, NULL, 'delete', NULL, '256', NULL, NULL, '2025-10-10 22:25:32'),
(1070, NULL, 'delete', NULL, '255', NULL, NULL, '2025-10-10 22:25:33'),
(1071, NULL, 'delete', NULL, '254', NULL, NULL, '2025-10-10 22:25:36'),
(1072, NULL, 'delete', NULL, '253', NULL, NULL, '2025-10-10 22:25:40'),
(1073, NULL, 'delete', NULL, '252', NULL, NULL, '2025-10-10 22:25:42'),
(1074, NULL, 'delete', NULL, '251', NULL, NULL, '2025-10-10 22:25:43'),
(1075, NULL, 'delete', NULL, '250', NULL, NULL, '2025-10-10 22:25:45'),
(1076, NULL, 'delete', NULL, '249', NULL, NULL, '2025-10-10 22:25:46'),
(1077, NULL, 'delete', NULL, '248', NULL, NULL, '2025-10-10 22:25:48'),
(1078, NULL, 'delete', NULL, '247', NULL, NULL, '2025-10-10 22:25:55'),
(1079, NULL, 'delete', NULL, '246', NULL, NULL, '2025-10-10 22:25:58'),
(1080, NULL, 'delete', NULL, '245', NULL, NULL, '2025-10-10 22:26:14'),
(1081, NULL, 'delete', NULL, '220', NULL, NULL, '2025-10-10 22:26:58'),
(1082, NULL, 'delete', NULL, '221', NULL, NULL, '2025-10-10 22:26:59'),
(1083, NULL, 'delete', NULL, '222', NULL, NULL, '2025-10-10 22:27:00'),
(1084, NULL, 'delete', NULL, '223', NULL, NULL, '2025-10-10 22:27:02'),
(1085, NULL, 'delete', NULL, '224', NULL, NULL, '2025-10-10 22:27:03'),
(1086, NULL, 'delete', NULL, '225', NULL, NULL, '2025-10-10 22:27:04'),
(1087, NULL, 'delete', NULL, '226', NULL, NULL, '2025-10-10 22:27:05'),
(1088, NULL, 'delete', NULL, '227', NULL, NULL, '2025-10-10 22:27:07'),
(1089, NULL, 'delete', NULL, '228', NULL, NULL, '2025-10-10 22:27:08'),
(1090, NULL, 'delete', NULL, '229', NULL, NULL, '2025-10-10 22:27:09'),
(1091, NULL, 'delete', NULL, '230', NULL, NULL, '2025-10-10 22:27:10'),
(1092, NULL, 'delete', NULL, '231', NULL, NULL, '2025-10-10 22:27:11'),
(1093, NULL, 'delete', NULL, '232', NULL, NULL, '2025-10-10 22:27:12'),
(1094, NULL, 'delete', NULL, '314', NULL, NULL, '2025-10-10 22:27:21'),
(1095, NULL, 'delete', NULL, '315', NULL, NULL, '2025-10-10 22:27:22'),
(1096, NULL, 'delete', NULL, '316', NULL, NULL, '2025-10-10 22:27:23'),
(1097, NULL, 'delete', NULL, '317', NULL, NULL, '2025-10-10 22:27:25'),
(1098, NULL, 'delete', NULL, '318', NULL, NULL, '2025-10-10 22:27:28'),
(1099, NULL, 'delete', NULL, '319', NULL, NULL, '2025-10-10 22:27:30'),
(1100, NULL, 'delete', NULL, '320', NULL, NULL, '2025-10-10 22:27:31'),
(1101, NULL, 'delete', NULL, '321', NULL, NULL, '2025-10-10 22:27:33'),
(1102, NULL, 'delete', NULL, '322', NULL, NULL, '2025-10-10 22:27:34'),
(1103, NULL, 'delete', NULL, '323', NULL, NULL, '2025-10-10 22:27:35'),
(1104, NULL, 'delete', NULL, '324', NULL, NULL, '2025-10-10 22:27:36'),
(1105, NULL, 'delete', NULL, '325', NULL, NULL, '2025-10-10 22:27:37'),
(1106, NULL, 'delete', NULL, '116', NULL, NULL, '2025-10-10 22:27:46'),
(1107, NULL, 'delete', NULL, '117', NULL, NULL, '2025-10-10 22:27:47'),
(1108, NULL, 'delete', NULL, '118', NULL, NULL, '2025-10-10 22:27:50'),
(1109, NULL, 'delete', NULL, '119', NULL, NULL, '2025-10-10 22:27:54'),
(1110, NULL, 'delete', NULL, '120', NULL, NULL, '2025-10-10 22:27:57'),
(1111, NULL, 'delete', NULL, '121', NULL, NULL, '2025-10-10 22:27:59'),
(1112, NULL, 'delete', NULL, '122', NULL, NULL, '2025-10-10 22:28:00'),
(1113, NULL, 'delete', NULL, '123', NULL, NULL, '2025-10-10 22:28:01'),
(1114, NULL, 'delete', NULL, '124', NULL, NULL, '2025-10-10 22:28:03'),
(1115, NULL, 'delete', NULL, '125', NULL, NULL, '2025-10-10 22:28:04'),
(1116, NULL, 'delete', NULL, '126', NULL, NULL, '2025-10-10 22:28:22'),
(1117, NULL, 'delete', NULL, '127', NULL, NULL, '2025-10-10 22:28:24'),
(1118, NULL, 'delete', NULL, '128', NULL, NULL, '2025-10-10 22:28:25'),
(1119, NULL, 'delete', NULL, '129', NULL, NULL, '2025-10-10 22:28:27'),
(1120, NULL, 'delete', NULL, '130', NULL, NULL, '2025-10-10 22:28:28'),
(1121, NULL, 'delete', NULL, '131', NULL, NULL, '2025-10-10 22:28:29'),
(1122, NULL, 'delete', NULL, '132', NULL, NULL, '2025-10-10 22:28:31'),
(1123, NULL, 'delete', NULL, '133', NULL, NULL, '2025-10-10 22:28:36'),
(1124, NULL, 'delete', NULL, '135', NULL, NULL, '2025-10-10 22:28:41'),
(1125, NULL, 'update_due', NULL, '134', NULL, NULL, '2025-10-10 22:28:56'),
(1126, NULL, 'delete', NULL, '136', NULL, NULL, '2025-10-10 22:28:59'),
(1127, NULL, 'delete', NULL, '137', NULL, NULL, '2025-10-10 22:29:01'),
(1128, NULL, 'delete', NULL, '138', NULL, NULL, '2025-10-10 22:29:02'),
(1129, NULL, 'submit_score', 'applicant', '16', NULL, NULL, '2025-10-10 17:31:28'),
(1130, NULL, 'set_status', 'applicant', '16', NULL, NULL, '2025-10-10 17:31:28'),
(1131, NULL, 'submit_score', 'applicant', '17', NULL, NULL, '2025-10-10 18:05:04'),
(1132, NULL, 'set_status', 'applicant', '17', NULL, NULL, '2025-10-10 18:05:04'),
(1133, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-10 18:55:38'),
(1134, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-10 18:55:38'),
(1135, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-10 18:56:06'),
(1136, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-10 18:56:06'),
(1137, NULL, 'submit_score', 'applicant', '14', NULL, NULL, '2025-10-10 19:01:00'),
(1138, NULL, 'set_status', 'applicant', '14', NULL, NULL, '2025-10-10 19:01:00'),
(1139, NULL, 'add', NULL, '338', NULL, NULL, '2025-10-11 06:02:33'),
(1140, NULL, 'add', NULL, '339', NULL, NULL, '2025-10-11 06:02:33'),
(1141, NULL, 'add', NULL, '340', NULL, NULL, '2025-10-11 06:02:33'),
(1142, NULL, 'add', NULL, '341', NULL, NULL, '2025-10-11 06:02:33'),
(1143, NULL, 'add', NULL, '342', NULL, NULL, '2025-10-11 06:02:33'),
(1144, NULL, 'add', NULL, '343', NULL, NULL, '2025-10-11 06:02:33'),
(1145, NULL, 'add', NULL, '344', NULL, NULL, '2025-10-11 06:02:33'),
(1146, NULL, 'add', NULL, '345', NULL, NULL, '2025-10-11 06:02:33'),
(1147, NULL, 'add', NULL, '346', NULL, NULL, '2025-10-11 06:02:33'),
(1148, NULL, 'add', NULL, '347', NULL, NULL, '2025-10-11 06:02:33'),
(1149, NULL, 'add', NULL, '348', NULL, NULL, '2025-10-11 06:02:33'),
(1150, NULL, 'add', NULL, '349', NULL, NULL, '2025-10-11 06:02:33'),
(1151, NULL, 'delete', NULL, '338', NULL, NULL, '2025-10-11 06:02:45'),
(1152, NULL, 'delete', NULL, '339', NULL, NULL, '2025-10-11 06:02:47'),
(1153, NULL, 'delete', NULL, '340', NULL, NULL, '2025-10-11 06:02:48'),
(1154, NULL, 'delete', NULL, '341', NULL, NULL, '2025-10-11 06:02:50'),
(1155, NULL, 'delete', NULL, '342', NULL, NULL, '2025-10-11 06:02:52'),
(1156, NULL, 'delete', NULL, '343', NULL, NULL, '2025-10-11 06:02:53'),
(1157, NULL, 'delete', NULL, '344', NULL, NULL, '2025-10-11 06:02:54'),
(1158, NULL, 'delete', NULL, '345', NULL, NULL, '2025-10-11 06:02:55'),
(1159, NULL, 'delete', NULL, '346', NULL, NULL, '2025-10-11 06:02:56'),
(1160, NULL, 'delete', NULL, '347', NULL, NULL, '2025-10-11 06:02:58'),
(1161, NULL, 'delete', NULL, '348', NULL, NULL, '2025-10-11 06:02:59'),
(1162, NULL, 'delete', NULL, '349', NULL, NULL, '2025-10-11 06:03:00'),
(1163, NULL, 'add', NULL, '350', NULL, NULL, '2025-10-11 09:42:40'),
(1164, NULL, 'add', NULL, '351', NULL, NULL, '2025-10-11 09:42:40'),
(1165, NULL, 'add', NULL, '352', NULL, NULL, '2025-10-11 09:42:40'),
(1166, NULL, 'add', NULL, '353', NULL, NULL, '2025-10-11 09:42:40'),
(1167, NULL, 'add', NULL, '354', NULL, NULL, '2025-10-11 09:42:40'),
(1168, NULL, 'add', NULL, '355', NULL, NULL, '2025-10-11 09:42:40'),
(1169, NULL, 'add', NULL, '356', NULL, NULL, '2025-10-11 09:42:40'),
(1170, NULL, 'add', NULL, '357', NULL, NULL, '2025-10-11 09:42:40'),
(1171, NULL, 'add', NULL, '358', NULL, NULL, '2025-10-11 09:42:40'),
(1172, NULL, 'add', NULL, '359', NULL, NULL, '2025-10-11 09:42:40'),
(1173, NULL, 'add', NULL, '360', NULL, NULL, '2025-10-11 09:42:40'),
(1174, NULL, 'add', NULL, '361', NULL, NULL, '2025-10-11 09:42:40'),
(1175, NULL, 'update_due', NULL, '350', NULL, NULL, '2025-10-11 09:42:45'),
(1176, NULL, 'update_due', NULL, '351', NULL, NULL, '2025-10-11 09:42:48'),
(1177, NULL, 'update_due', NULL, '351', NULL, NULL, '2025-10-11 09:42:53'),
(1178, NULL, 'delete', NULL, '350', NULL, NULL, '2025-10-11 09:42:56'),
(1179, NULL, 'delete', NULL, '351', NULL, NULL, '2025-10-11 09:42:58'),
(1180, NULL, 'delete', NULL, '352', NULL, NULL, '2025-10-11 09:42:59'),
(1181, NULL, 'delete', NULL, '353', NULL, NULL, '2025-10-11 09:43:01'),
(1182, NULL, 'delete', NULL, '354', NULL, NULL, '2025-10-11 09:43:02'),
(1183, NULL, 'delete', NULL, '355', NULL, NULL, '2025-10-11 09:43:04'),
(1184, NULL, 'delete', NULL, '356', NULL, NULL, '2025-10-11 09:43:05'),
(1185, NULL, 'delete', NULL, '357', NULL, NULL, '2025-10-11 09:43:07'),
(1186, NULL, 'delete', NULL, '358', NULL, NULL, '2025-10-11 09:43:08'),
(1187, NULL, 'delete', NULL, '359', NULL, NULL, '2025-10-11 09:43:10'),
(1188, NULL, 'delete', NULL, '360', NULL, NULL, '2025-10-11 09:43:15'),
(1189, NULL, 'delete', NULL, '361', NULL, NULL, '2025-10-11 09:43:17'),
(1190, NULL, 'add', NULL, '362', NULL, NULL, '2025-10-11 09:44:05'),
(1191, NULL, 'add', NULL, '363', NULL, NULL, '2025-10-11 09:44:05'),
(1192, NULL, 'add', NULL, '364', NULL, NULL, '2025-10-11 09:44:05'),
(1193, NULL, 'add', NULL, '365', NULL, NULL, '2025-10-11 09:44:05'),
(1194, NULL, 'add', NULL, '366', NULL, NULL, '2025-10-11 09:44:05'),
(1195, NULL, 'add', NULL, '367', NULL, NULL, '2025-10-11 09:44:05'),
(1196, NULL, 'add', NULL, '368', NULL, NULL, '2025-10-11 09:44:05'),
(1197, NULL, 'add', NULL, '369', NULL, NULL, '2025-10-11 09:44:05'),
(1198, NULL, 'add', NULL, '370', NULL, NULL, '2025-10-11 09:44:05'),
(1199, NULL, 'add', NULL, '371', NULL, NULL, '2025-10-11 09:44:05'),
(1200, NULL, 'add', NULL, '372', NULL, NULL, '2025-10-11 09:44:05'),
(1201, NULL, 'add', NULL, '373', NULL, NULL, '2025-10-11 09:44:05'),
(1202, NULL, 'update_due', NULL, '362', NULL, NULL, '2025-10-11 09:44:39'),
(1203, NULL, 'delete', NULL, '362', NULL, NULL, '2025-10-11 09:45:17'),
(1204, NULL, 'delete', NULL, '363', NULL, NULL, '2025-10-11 09:45:19'),
(1205, NULL, 'delete', NULL, '364', NULL, NULL, '2025-10-11 09:45:20'),
(1206, NULL, 'delete', NULL, '365', NULL, NULL, '2025-10-11 09:45:21'),
(1207, NULL, 'delete', NULL, '366', NULL, NULL, '2025-10-11 09:45:23'),
(1208, NULL, 'delete', NULL, '367', NULL, NULL, '2025-10-11 09:45:24'),
(1209, NULL, 'delete', NULL, '368', NULL, NULL, '2025-10-11 09:45:26'),
(1210, NULL, 'delete', NULL, '369', NULL, NULL, '2025-10-11 09:45:28'),
(1211, NULL, 'delete', NULL, '370', NULL, NULL, '2025-10-11 09:45:29'),
(1212, NULL, 'delete', NULL, '371', NULL, NULL, '2025-10-11 09:45:30'),
(1213, NULL, 'delete', NULL, '372', NULL, NULL, '2025-10-11 09:45:32'),
(1214, NULL, 'delete', NULL, '373', NULL, NULL, '2025-10-11 09:45:33'),
(1215, NULL, 'add', NULL, '374', NULL, NULL, '2025-10-11 13:12:42'),
(1216, NULL, 'add', NULL, '375', NULL, NULL, '2025-10-11 13:12:42'),
(1217, NULL, 'add', NULL, '376', NULL, NULL, '2025-10-11 13:12:42'),
(1218, NULL, 'add', NULL, '377', NULL, NULL, '2025-10-11 13:12:43'),
(1219, NULL, 'add', NULL, '378', NULL, NULL, '2025-10-11 13:12:43'),
(1220, NULL, 'add', NULL, '379', NULL, NULL, '2025-10-11 13:12:43'),
(1221, NULL, 'add', NULL, '380', NULL, NULL, '2025-10-11 13:12:43'),
(1222, NULL, 'add', NULL, '381', NULL, NULL, '2025-10-11 13:12:43'),
(1223, NULL, 'add', NULL, '382', NULL, NULL, '2025-10-11 13:12:43'),
(1224, NULL, 'add', NULL, '383', NULL, NULL, '2025-10-11 13:12:43'),
(1225, NULL, 'add', NULL, '384', NULL, NULL, '2025-10-11 13:12:43'),
(1226, NULL, 'add', NULL, '385', NULL, NULL, '2025-10-11 13:12:43'),
(1227, NULL, 'add', NULL, '386', NULL, NULL, '2025-10-11 13:12:43'),
(1228, NULL, 'delete', NULL, '374', NULL, NULL, '2025-10-11 13:12:48'),
(1229, NULL, 'delete', NULL, '375', NULL, NULL, '2025-10-11 13:12:50'),
(1230, NULL, 'delete', NULL, '376', NULL, NULL, '2025-10-11 13:12:52'),
(1231, NULL, 'delete', NULL, '377', NULL, NULL, '2025-10-11 13:12:53'),
(1232, NULL, 'delete', NULL, '378', NULL, NULL, '2025-10-11 13:12:54'),
(1233, NULL, 'delete', NULL, '379', NULL, NULL, '2025-10-11 13:12:57'),
(1234, NULL, 'delete', NULL, '380', NULL, NULL, '2025-10-11 13:12:58'),
(1235, NULL, 'delete', NULL, '381', NULL, NULL, '2025-10-11 13:13:00'),
(1236, NULL, 'delete', NULL, '382', NULL, NULL, '2025-10-11 13:13:01'),
(1237, NULL, 'delete', NULL, '383', NULL, NULL, '2025-10-11 13:13:02'),
(1238, NULL, 'delete', NULL, '384', NULL, NULL, '2025-10-11 13:13:03'),
(1239, NULL, 'delete', NULL, '385', NULL, NULL, '2025-10-11 13:13:04'),
(1240, NULL, 'delete', NULL, '386', NULL, NULL, '2025-10-11 13:13:05'),
(1241, NULL, 'add', NULL, '387', NULL, NULL, '2025-10-11 15:20:24'),
(1242, NULL, 'add', NULL, '388', NULL, NULL, '2025-10-11 15:20:24'),
(1243, NULL, 'add', NULL, '389', NULL, NULL, '2025-10-11 15:20:24'),
(1244, NULL, 'add', NULL, '390', NULL, NULL, '2025-10-11 15:20:24'),
(1245, NULL, 'add', NULL, '391', NULL, NULL, '2025-10-11 15:20:24'),
(1246, NULL, 'add', NULL, '392', NULL, NULL, '2025-10-11 15:20:24'),
(1247, NULL, 'add', NULL, '393', NULL, NULL, '2025-10-11 15:20:24'),
(1248, NULL, 'add', NULL, '394', NULL, NULL, '2025-10-11 15:20:24'),
(1249, NULL, 'add', NULL, '395', NULL, NULL, '2025-10-11 15:20:25'),
(1250, NULL, 'add', NULL, '396', NULL, NULL, '2025-10-11 15:20:25'),
(1251, NULL, 'add', NULL, '397', NULL, NULL, '2025-10-11 15:20:25'),
(1252, NULL, 'add', NULL, '398', NULL, NULL, '2025-10-11 15:20:25'),
(1253, NULL, 'add', NULL, '399', NULL, NULL, '2025-10-11 15:20:25'),
(1254, NULL, 'update_due', NULL, '387', NULL, NULL, '2025-10-11 15:20:30'),
(1255, NULL, 'delete', NULL, '387', NULL, NULL, '2025-10-11 15:20:32'),
(1256, NULL, 'delete', NULL, '388', NULL, NULL, '2025-10-11 15:20:34'),
(1257, NULL, 'delete', NULL, '389', NULL, NULL, '2025-10-11 15:20:35'),
(1258, NULL, 'delete', NULL, '390', NULL, NULL, '2025-10-11 15:20:36'),
(1259, NULL, 'delete', NULL, '391', NULL, NULL, '2025-10-11 15:20:38'),
(1260, NULL, 'delete', NULL, '392', NULL, NULL, '2025-10-11 15:20:39'),
(1261, NULL, 'delete', NULL, '393', NULL, NULL, '2025-10-11 15:20:41'),
(1262, NULL, 'delete', NULL, '394', NULL, NULL, '2025-10-11 15:20:42'),
(1263, NULL, 'delete', NULL, '395', NULL, NULL, '2025-10-11 15:20:43'),
(1264, NULL, 'delete', NULL, '396', NULL, NULL, '2025-10-11 15:20:44'),
(1265, NULL, 'delete', NULL, '397', NULL, NULL, '2025-10-11 15:20:45'),
(1266, NULL, 'delete', NULL, '398', NULL, NULL, '2025-10-11 15:20:46'),
(1267, NULL, 'delete', NULL, '399', NULL, NULL, '2025-10-11 15:20:47'),
(1268, NULL, 'delete', NULL, '42', NULL, NULL, '2025-10-11 18:09:25'),
(1269, NULL, 'add', NULL, '400', NULL, NULL, '2025-10-11 18:09:32'),
(1270, NULL, 'add', NULL, '401', NULL, NULL, '2025-10-11 18:09:34'),
(1271, NULL, 'add', NULL, '402', NULL, NULL, '2025-10-11 18:09:35'),
(1272, NULL, 'add', NULL, '403', NULL, NULL, '2025-10-11 18:09:36'),
(1273, NULL, 'add', NULL, '404', NULL, NULL, '2025-10-11 18:09:38'),
(1274, NULL, 'add', NULL, '405', NULL, NULL, '2025-10-11 18:09:39'),
(1275, NULL, 'add', NULL, '406', NULL, NULL, '2025-10-11 18:09:40'),
(1276, NULL, 'add', NULL, '407', NULL, NULL, '2025-10-11 18:09:42'),
(1277, NULL, 'add', NULL, '408', NULL, NULL, '2025-10-11 18:09:43'),
(1278, NULL, 'add', NULL, '409', NULL, NULL, '2025-10-11 18:09:44'),
(1279, NULL, 'add', NULL, '410', NULL, NULL, '2025-10-11 18:09:45'),
(1280, NULL, 'add', NULL, '411', NULL, NULL, '2025-10-11 18:09:46'),
(1281, NULL, 'update_due', NULL, '400', NULL, NULL, '2025-10-11 18:09:56'),
(1282, NULL, 'update_status', NULL, '401', NULL, NULL, '2025-10-11 18:10:02'),
(1283, NULL, 'update_due', NULL, '402', NULL, NULL, '2025-10-11 18:10:10'),
(1284, NULL, 'update_due', NULL, '282', NULL, NULL, '2025-10-11 18:10:22'),
(1285, NULL, 'add', NULL, '412', NULL, NULL, '2025-10-11 18:10:28'),
(1286, NULL, 'add', NULL, '413', NULL, NULL, '2025-10-11 18:10:30'),
(1287, NULL, 'add', NULL, '414', NULL, NULL, '2025-10-11 18:10:31'),
(1288, NULL, 'add', NULL, '415', NULL, NULL, '2025-10-11 18:10:32'),
(1289, NULL, 'add', NULL, '416', NULL, NULL, '2025-10-11 18:10:33'),
(1290, NULL, 'add', NULL, '417', NULL, NULL, '2025-10-11 18:10:34'),
(1291, NULL, 'add', NULL, '418', NULL, NULL, '2025-10-11 18:10:36'),
(1292, NULL, 'add', NULL, '419', NULL, NULL, '2025-10-11 18:10:37'),
(1293, NULL, 'add', NULL, '420', NULL, NULL, '2025-10-11 18:10:38'),
(1294, NULL, 'add', NULL, '421', NULL, NULL, '2025-10-11 18:10:39'),
(1295, NULL, 'add', NULL, '422', NULL, NULL, '2025-10-11 18:10:42'),
(1296, NULL, 'add', NULL, '423', NULL, NULL, '2025-10-11 18:10:45'),
(1297, NULL, 'add', NULL, '424', NULL, NULL, '2025-10-11 18:10:46'),
(1298, NULL, 'update_due', NULL, '403', NULL, NULL, '2025-10-11 18:11:24'),
(1299, NULL, 'update_due', NULL, '405', NULL, NULL, '2025-10-11 18:11:30'),
(1300, NULL, 'update_due', NULL, '407', NULL, NULL, '2025-10-11 18:11:36'),
(1301, NULL, 'add', NULL, '425', NULL, NULL, '2025-10-11 18:11:43'),
(1302, NULL, 'add', NULL, '426', NULL, NULL, '2025-10-11 18:11:43'),
(1303, NULL, 'add', NULL, '427', NULL, NULL, '2025-10-11 18:11:44'),
(1304, NULL, 'add', NULL, '428', NULL, NULL, '2025-10-11 18:11:45'),
(1305, NULL, 'add', NULL, '429', NULL, NULL, '2025-10-11 18:11:46'),
(1306, NULL, 'add', NULL, '430', NULL, NULL, '2025-10-11 18:11:49'),
(1307, NULL, 'add', NULL, '431', NULL, NULL, '2025-10-11 18:11:49'),
(1308, NULL, 'add', NULL, '432', NULL, NULL, '2025-10-11 18:11:51'),
(1309, NULL, 'add', NULL, '433', NULL, NULL, '2025-10-11 18:11:53'),
(1310, NULL, 'add', NULL, '434', NULL, NULL, '2025-10-11 18:11:54'),
(1311, NULL, 'add', NULL, '435', NULL, NULL, '2025-10-11 18:11:54'),
(1312, NULL, 'add', NULL, '436', NULL, NULL, '2025-10-11 18:11:55'),
(1313, NULL, 'add', NULL, '437', NULL, NULL, '2025-10-11 18:11:57'),
(1314, NULL, 'add', NULL, '438', NULL, NULL, '2025-10-11 18:15:42'),
(1315, NULL, 'add', NULL, '439', NULL, NULL, '2025-10-11 18:15:45'),
(1316, NULL, 'add', NULL, '440', NULL, NULL, '2025-10-11 18:15:47'),
(1317, NULL, 'add', NULL, '441', NULL, NULL, '2025-10-11 18:15:48'),
(1318, NULL, 'add', NULL, '442', NULL, NULL, '2025-10-11 18:15:49'),
(1319, NULL, 'add', NULL, '443', NULL, NULL, '2025-10-11 18:15:51'),
(1320, NULL, 'add', NULL, '444', NULL, NULL, '2025-10-11 18:15:51'),
(1321, NULL, 'add', NULL, '445', NULL, NULL, '2025-10-11 18:15:53'),
(1322, NULL, 'add', NULL, '446', NULL, NULL, '2025-10-11 18:15:54'),
(1323, NULL, 'add', NULL, '447', NULL, NULL, '2025-10-11 18:15:55'),
(1324, NULL, 'add', NULL, '448', NULL, NULL, '2025-10-11 18:15:55'),
(1325, NULL, 'add', NULL, '449', NULL, NULL, '2025-10-11 18:15:56'),
(1326, NULL, 'update_due', NULL, '443', NULL, NULL, '2025-10-11 18:16:15'),
(1327, NULL, 'update_due', NULL, '448', NULL, NULL, '2025-10-11 18:16:23'),
(1328, NULL, 'add', NULL, '450', NULL, NULL, '2025-10-11 18:41:36'),
(1329, NULL, 'add', NULL, '451', NULL, NULL, '2025-10-11 18:41:38'),
(1330, NULL, 'add', NULL, '452', NULL, NULL, '2025-10-11 18:41:40'),
(1331, NULL, 'add', NULL, '453', NULL, NULL, '2025-10-11 18:41:42'),
(1332, NULL, 'add', NULL, '454', NULL, NULL, '2025-10-11 18:41:43'),
(1333, NULL, 'add', NULL, '455', NULL, NULL, '2025-10-11 18:41:44'),
(1334, NULL, 'add', NULL, '456', NULL, NULL, '2025-10-11 18:41:45'),
(1335, NULL, 'add', NULL, '457', NULL, NULL, '2025-10-11 18:41:47'),
(1336, NULL, 'add', NULL, '458', NULL, NULL, '2025-10-11 18:41:48'),
(1337, NULL, 'add', NULL, '459', NULL, NULL, '2025-10-11 18:41:49'),
(1338, NULL, 'add', NULL, '460', NULL, NULL, '2025-10-11 18:41:50'),
(1339, NULL, 'add', NULL, '461', NULL, NULL, '2025-10-11 18:41:51'),
(1340, NULL, 'add', NULL, '462', NULL, NULL, '2025-10-11 18:46:25'),
(1341, NULL, 'add', NULL, '463', NULL, NULL, '2025-10-11 18:46:26'),
(1342, NULL, 'add', NULL, '464', NULL, NULL, '2025-10-11 18:46:27'),
(1343, NULL, 'add', NULL, '465', NULL, NULL, '2025-10-11 18:46:30'),
(1344, NULL, 'add', NULL, '466', NULL, NULL, '2025-10-11 18:46:31'),
(1345, NULL, 'add', NULL, '467', NULL, NULL, '2025-10-11 18:46:33'),
(1346, NULL, 'add', NULL, '468', NULL, NULL, '2025-10-11 18:46:34'),
(1347, NULL, 'add', NULL, '469', NULL, NULL, '2025-10-11 18:46:36'),
(1348, NULL, 'add', NULL, '470', NULL, NULL, '2025-10-11 18:46:37'),
(1349, NULL, 'add', NULL, '471', NULL, NULL, '2025-10-11 18:46:37'),
(1350, NULL, 'add', NULL, '472', NULL, NULL, '2025-10-11 18:46:38'),
(1351, NULL, 'add', NULL, '473', NULL, NULL, '2025-10-11 18:46:39'),
(1352, NULL, 'add', NULL, '474', NULL, NULL, '2025-10-11 19:06:03'),
(1353, NULL, 'add', NULL, '475', NULL, NULL, '2025-10-11 19:06:05'),
(1354, NULL, 'add', NULL, '476', NULL, NULL, '2025-10-11 19:06:07'),
(1355, NULL, 'add', NULL, '477', NULL, NULL, '2025-10-11 19:06:10'),
(1356, NULL, 'add', NULL, '478', NULL, NULL, '2025-10-11 19:06:14'),
(1357, NULL, 'add', NULL, '479', NULL, NULL, '2025-10-11 19:06:16'),
(1358, NULL, 'add', NULL, '480', NULL, NULL, '2025-10-11 19:06:20'),
(1359, NULL, 'add', NULL, '481', NULL, NULL, '2025-10-11 19:06:22'),
(1360, NULL, 'add', NULL, '482', NULL, NULL, '2025-10-11 19:06:24'),
(1361, NULL, 'add', NULL, '483', NULL, NULL, '2025-10-11 19:06:27'),
(1362, NULL, 'add', NULL, '484', NULL, NULL, '2025-10-11 19:06:29'),
(1363, NULL, 'add', NULL, '485', NULL, NULL, '2025-10-11 19:06:32'),
(1364, NULL, 'add', NULL, '486', NULL, NULL, '2025-10-11 19:06:35'),
(1365, NULL, 'add', NULL, '487', NULL, NULL, '2025-10-11 19:16:51'),
(1366, NULL, 'add', NULL, '488', NULL, NULL, '2025-10-11 19:16:53'),
(1367, NULL, 'add', NULL, '489', NULL, NULL, '2025-10-11 19:16:56'),
(1368, NULL, 'add', NULL, '490', NULL, NULL, '2025-10-11 19:16:59'),
(1369, NULL, 'add', NULL, '491', NULL, NULL, '2025-10-11 19:17:02'),
(1370, NULL, 'add', NULL, '492', NULL, NULL, '2025-10-11 19:17:04'),
(1371, NULL, 'add', NULL, '493', NULL, NULL, '2025-10-11 19:17:08'),
(1372, NULL, 'add', NULL, '494', NULL, NULL, '2025-10-11 19:17:11'),
(1373, NULL, 'add', NULL, '495', NULL, NULL, '2025-10-11 19:17:13'),
(1374, NULL, 'add', NULL, '496', NULL, NULL, '2025-10-11 19:17:15'),
(1375, NULL, 'add', NULL, '497', NULL, NULL, '2025-10-11 19:17:18'),
(1376, NULL, 'add', NULL, '498', NULL, NULL, '2025-10-11 19:17:21'),
(1377, NULL, 'add', NULL, '499', NULL, NULL, '2025-10-11 19:17:23'),
(1378, NULL, 'update_due', NULL, '401', NULL, NULL, '2025-10-11 19:19:09'),
(1379, NULL, 'update_due', NULL, '403', NULL, NULL, '2025-10-11 19:19:19'),
(1380, NULL, 'update_due', NULL, '405', NULL, NULL, '2025-10-11 19:19:26'),
(1381, NULL, 'update_due', NULL, '411', NULL, NULL, '2025-10-11 19:19:32');
INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `entity`, `entity_id`, `before_json`, `after_json`, `created_at`) VALUES
(1382, NULL, 'update_due', NULL, '411', NULL, NULL, '2025-10-11 19:19:38'),
(1383, NULL, 'update_due', NULL, '410', NULL, NULL, '2025-10-11 19:19:47'),
(1384, NULL, 'update_due', NULL, '409', NULL, NULL, '2025-10-11 19:19:57'),
(1385, NULL, 'update_due', NULL, '410', NULL, NULL, '2025-10-11 19:20:04'),
(1386, NULL, 'update_due', NULL, '406', NULL, NULL, '2025-10-11 19:20:12'),
(1387, NULL, 'delete', NULL, '487', NULL, NULL, '2025-10-11 19:20:26'),
(1388, NULL, 'delete', NULL, '488', NULL, NULL, '2025-10-11 19:20:30'),
(1389, NULL, 'delete', NULL, '489', NULL, NULL, '2025-10-11 19:20:34'),
(1390, NULL, 'delete', NULL, '490', NULL, NULL, '2025-10-11 19:20:40'),
(1391, NULL, 'delete', NULL, '491', NULL, NULL, '2025-10-11 19:20:44'),
(1392, NULL, 'delete', NULL, '492', NULL, NULL, '2025-10-11 19:21:13'),
(1393, NULL, 'delete', NULL, '493', NULL, NULL, '2025-10-11 19:21:21'),
(1394, NULL, 'delete', NULL, '494', NULL, NULL, '2025-10-11 19:21:29'),
(1395, NULL, 'delete', NULL, '495', NULL, NULL, '2025-10-11 19:21:32'),
(1396, NULL, 'delete', NULL, '496', NULL, NULL, '2025-10-11 19:21:36'),
(1397, NULL, 'delete', NULL, '497', NULL, NULL, '2025-10-11 19:21:40'),
(1398, NULL, 'delete', NULL, '498', NULL, NULL, '2025-10-11 19:21:46'),
(1399, NULL, 'delete', NULL, '499', NULL, NULL, '2025-10-11 19:21:53'),
(1400, NULL, 'delete', NULL, '462', NULL, NULL, '2025-10-11 19:22:13'),
(1401, NULL, 'delete', NULL, '463', NULL, NULL, '2025-10-11 19:22:19'),
(1402, NULL, 'delete', NULL, '464', NULL, NULL, '2025-10-11 19:22:25'),
(1403, NULL, 'delete', NULL, '465', NULL, NULL, '2025-10-11 19:22:58'),
(1404, NULL, 'delete', NULL, '466', NULL, NULL, '2025-10-11 19:23:02'),
(1405, NULL, 'delete', NULL, '467', NULL, NULL, '2025-10-11 19:23:08'),
(1406, NULL, 'delete', NULL, '468', NULL, NULL, '2025-10-11 19:23:11'),
(1407, NULL, 'delete', NULL, '469', NULL, NULL, '2025-10-11 19:23:15'),
(1408, NULL, 'delete', NULL, '470', NULL, NULL, '2025-10-11 19:23:19'),
(1409, NULL, 'delete', NULL, '471', NULL, NULL, '2025-10-11 19:23:22'),
(1410, NULL, 'delete', NULL, '472', NULL, NULL, '2025-10-11 19:23:26'),
(1411, NULL, 'delete', NULL, '473', NULL, NULL, '2025-10-11 19:23:29'),
(1412, NULL, 'delete', NULL, '425', NULL, NULL, '2025-10-11 19:23:42'),
(1413, NULL, 'delete', NULL, '426', NULL, NULL, '2025-10-11 19:23:44'),
(1414, NULL, 'delete', NULL, '427', NULL, NULL, '2025-10-11 19:23:54'),
(1415, NULL, 'delete', NULL, '428', NULL, NULL, '2025-10-11 19:23:57'),
(1416, NULL, 'delete', NULL, '429', NULL, NULL, '2025-10-11 19:23:59'),
(1417, NULL, 'delete', NULL, '430', NULL, NULL, '2025-10-11 19:24:02'),
(1418, NULL, 'delete', NULL, '431', NULL, NULL, '2025-10-11 19:24:05'),
(1419, NULL, 'delete', NULL, '432', NULL, NULL, '2025-10-11 19:24:09'),
(1420, NULL, 'delete', NULL, '433', NULL, NULL, '2025-10-11 19:24:11'),
(1421, NULL, 'delete', NULL, '434', NULL, NULL, '2025-10-11 19:24:14'),
(1422, NULL, 'delete', NULL, '435', NULL, NULL, '2025-10-11 19:24:17'),
(1423, NULL, 'delete', NULL, '436', NULL, NULL, '2025-10-11 19:24:20'),
(1424, NULL, 'delete', NULL, '437', NULL, NULL, '2025-10-11 19:24:23'),
(1425, NULL, 'delete', NULL, '412', NULL, NULL, '2025-10-11 19:24:29'),
(1426, NULL, 'delete', NULL, '413', NULL, NULL, '2025-10-11 19:24:33'),
(1427, NULL, 'delete', NULL, '414', NULL, NULL, '2025-10-11 19:24:35'),
(1428, NULL, 'delete', NULL, '415', NULL, NULL, '2025-10-11 19:24:39'),
(1429, NULL, 'delete', NULL, '416', NULL, NULL, '2025-10-11 19:24:42'),
(1430, NULL, 'delete', NULL, '417', NULL, NULL, '2025-10-11 19:24:46'),
(1431, NULL, 'delete', NULL, '418', NULL, NULL, '2025-10-11 19:24:49'),
(1432, NULL, 'delete', NULL, '419', NULL, NULL, '2025-10-11 19:24:52'),
(1433, NULL, 'delete', NULL, '420', NULL, NULL, '2025-10-11 19:24:55'),
(1434, NULL, 'delete', NULL, '421', NULL, NULL, '2025-10-11 19:24:57'),
(1435, NULL, 'delete', NULL, '422', NULL, NULL, '2025-10-11 19:24:59'),
(1436, NULL, 'delete', NULL, '423', NULL, NULL, '2025-10-11 19:25:01'),
(1437, NULL, 'delete', NULL, '424', NULL, NULL, '2025-10-11 19:25:04'),
(1438, NULL, 'update_due', NULL, '282', NULL, NULL, '2025-10-11 19:25:17'),
(1439, NULL, 'update_due', NULL, '284', NULL, NULL, '2025-10-11 19:25:22'),
(1440, NULL, 'update_due', NULL, '284', NULL, NULL, '2025-10-11 19:25:24'),
(1441, NULL, 'update_due', NULL, '285', NULL, NULL, '2025-10-11 19:25:30'),
(1442, NULL, 'update_due', NULL, '288', NULL, NULL, '2025-10-11 19:25:39'),
(1443, NULL, 'delete', NULL, '278', NULL, NULL, '2025-10-11 19:25:49'),
(1444, NULL, 'delete', NULL, '279', NULL, NULL, '2025-10-11 19:25:56'),
(1445, NULL, 'delete', NULL, '280', NULL, NULL, '2025-10-11 19:26:01'),
(1446, NULL, 'delete', NULL, '281', NULL, NULL, '2025-10-11 19:28:33'),
(1447, NULL, 'delete', NULL, '282', NULL, NULL, '2025-10-11 19:28:36'),
(1448, NULL, 'delete', NULL, '283', NULL, NULL, '2025-10-11 19:28:40'),
(1449, NULL, 'delete', NULL, '450', NULL, NULL, '2025-10-11 19:28:56'),
(1450, NULL, 'delete', NULL, '451', NULL, NULL, '2025-10-11 19:29:00'),
(1451, NULL, 'delete', NULL, '452', NULL, NULL, '2025-10-11 19:29:04'),
(1452, NULL, 'delete', NULL, '453', NULL, NULL, '2025-10-11 19:29:07'),
(1453, NULL, 'delete', NULL, '454', NULL, NULL, '2025-10-11 19:29:09'),
(1454, NULL, 'delete', NULL, '455', NULL, NULL, '2025-10-11 19:29:12'),
(1455, NULL, 'delete', NULL, '456', NULL, NULL, '2025-10-11 19:29:15'),
(1456, NULL, 'delete', NULL, '457', NULL, NULL, '2025-10-11 19:29:18'),
(1457, NULL, 'delete', NULL, '458', NULL, NULL, '2025-10-11 19:29:21'),
(1458, NULL, 'delete', NULL, '459', NULL, NULL, '2025-10-11 19:29:24'),
(1459, NULL, 'delete', NULL, '460', NULL, NULL, '2025-10-11 19:29:26'),
(1460, NULL, 'delete', NULL, '461', NULL, NULL, '2025-10-11 19:29:29'),
(1461, NULL, 'add', NULL, '500', NULL, NULL, '2025-10-11 19:29:53'),
(1462, NULL, 'add', NULL, '501', NULL, NULL, '2025-10-11 19:29:54'),
(1463, NULL, 'add', NULL, '502', NULL, NULL, '2025-10-11 19:29:55'),
(1464, NULL, 'add', NULL, '503', NULL, NULL, '2025-10-11 19:29:56'),
(1465, NULL, 'add', NULL, '504', NULL, NULL, '2025-10-11 19:29:59'),
(1466, NULL, 'add', NULL, '505', NULL, NULL, '2025-10-11 19:30:00'),
(1467, NULL, 'add', NULL, '506', NULL, NULL, '2025-10-11 19:30:02'),
(1468, NULL, 'add', NULL, '507', NULL, NULL, '2025-10-11 19:30:04'),
(1469, NULL, 'add', NULL, '508', NULL, NULL, '2025-10-11 19:30:05'),
(1470, NULL, 'add', NULL, '509', NULL, NULL, '2025-10-11 19:30:06'),
(1471, NULL, 'add', NULL, '510', NULL, NULL, '2025-10-11 19:30:08'),
(1472, NULL, 'add', NULL, '511', NULL, NULL, '2025-10-11 19:30:09'),
(1473, NULL, 'add', NULL, '512', NULL, NULL, '2025-10-11 19:30:33'),
(1474, NULL, 'add', NULL, '513', NULL, NULL, '2025-10-11 19:30:34'),
(1475, NULL, 'add', NULL, '514', NULL, NULL, '2025-10-11 19:30:37'),
(1476, NULL, 'add', NULL, '515', NULL, NULL, '2025-10-11 19:30:38'),
(1477, NULL, 'add', NULL, '516', NULL, NULL, '2025-10-11 19:30:40'),
(1478, NULL, 'add', NULL, '517', NULL, NULL, '2025-10-11 19:30:41'),
(1479, NULL, 'add', NULL, '518', NULL, NULL, '2025-10-11 19:30:43'),
(1480, NULL, 'add', NULL, '519', NULL, NULL, '2025-10-11 19:30:44'),
(1481, NULL, 'add', NULL, '520', NULL, NULL, '2025-10-11 19:30:45'),
(1482, NULL, 'add', NULL, '521', NULL, NULL, '2025-10-11 19:30:47'),
(1483, NULL, 'add', NULL, '522', NULL, NULL, '2025-10-11 19:30:50'),
(1484, NULL, 'add', NULL, '523', NULL, NULL, '2025-10-11 19:30:50'),
(1485, NULL, 'delete', NULL, '512', NULL, NULL, '2025-10-11 19:31:01'),
(1486, NULL, 'delete', NULL, '513', NULL, NULL, '2025-10-11 19:31:05'),
(1487, NULL, 'delete', NULL, '514', NULL, NULL, '2025-10-11 19:31:07'),
(1488, NULL, 'delete', NULL, '515', NULL, NULL, '2025-10-11 19:31:11'),
(1489, NULL, 'delete', NULL, '516', NULL, NULL, '2025-10-11 19:31:14'),
(1490, NULL, 'delete', NULL, '517', NULL, NULL, '2025-10-11 19:31:17'),
(1491, NULL, 'delete', NULL, '518', NULL, NULL, '2025-10-11 19:31:21'),
(1492, NULL, 'delete', NULL, '519', NULL, NULL, '2025-10-11 19:31:24'),
(1493, NULL, 'delete', NULL, '520', NULL, NULL, '2025-10-11 19:31:27'),
(1494, NULL, 'delete', NULL, '521', NULL, NULL, '2025-10-11 19:31:28'),
(1495, NULL, 'delete', NULL, '522', NULL, NULL, '2025-10-11 19:31:31'),
(1496, NULL, 'delete', NULL, '523', NULL, NULL, '2025-10-11 19:31:33'),
(1497, NULL, 'delete', NULL, '134', NULL, NULL, '2025-10-11 19:38:01'),
(1498, NULL, 'delete', NULL, '474', NULL, NULL, '2025-10-11 19:38:05'),
(1499, NULL, 'delete', NULL, '475', NULL, NULL, '2025-10-11 19:38:12'),
(1500, NULL, 'delete', NULL, '476', NULL, NULL, '2025-10-11 19:38:16'),
(1501, NULL, 'update_due', NULL, '477', NULL, NULL, '2025-10-11 19:38:23'),
(1502, NULL, 'delete', NULL, '477', NULL, NULL, '2025-10-11 19:38:27'),
(1503, NULL, 'delete', NULL, '478', NULL, NULL, '2025-10-11 19:38:31'),
(1504, NULL, 'delete', NULL, '479', NULL, NULL, '2025-10-11 19:38:35'),
(1505, NULL, 'delete', NULL, '480', NULL, NULL, '2025-10-11 19:38:37'),
(1506, NULL, 'delete', NULL, '481', NULL, NULL, '2025-10-11 19:38:39'),
(1507, NULL, 'delete', NULL, '482', NULL, NULL, '2025-10-11 19:38:43'),
(1508, NULL, 'delete', NULL, '483', NULL, NULL, '2025-10-11 19:38:46'),
(1509, NULL, 'delete', NULL, '484', NULL, NULL, '2025-10-11 19:38:49'),
(1510, NULL, 'delete', NULL, '485', NULL, NULL, '2025-10-11 19:38:51'),
(1511, NULL, 'delete', NULL, '486', NULL, NULL, '2025-10-11 19:38:53'),
(1512, NULL, 'delete', NULL, '500', NULL, NULL, '2025-10-11 19:39:14'),
(1513, NULL, 'delete', NULL, '501', NULL, NULL, '2025-10-11 19:39:16'),
(1514, NULL, 'delete', NULL, '502', NULL, NULL, '2025-10-11 19:39:19'),
(1515, NULL, 'delete', NULL, '503', NULL, NULL, '2025-10-11 19:39:24'),
(1516, NULL, 'delete', NULL, '504', NULL, NULL, '2025-10-11 19:39:27'),
(1517, NULL, 'delete', NULL, '505', NULL, NULL, '2025-10-11 19:39:39'),
(1518, NULL, 'delete', NULL, '506', NULL, NULL, '2025-10-11 19:39:42'),
(1519, NULL, 'delete', NULL, '507', NULL, NULL, '2025-10-11 19:39:44'),
(1520, NULL, 'delete', NULL, '508', NULL, NULL, '2025-10-11 19:39:47'),
(1521, NULL, 'delete', NULL, '509', NULL, NULL, '2025-10-11 19:39:51'),
(1522, NULL, 'delete', NULL, '510', NULL, NULL, '2025-10-11 19:39:54'),
(1523, NULL, 'delete', NULL, '511', NULL, NULL, '2025-10-11 19:39:56'),
(1524, NULL, 'delete', NULL, '438', NULL, NULL, '2025-10-11 19:40:05'),
(1525, NULL, 'delete', NULL, '439', NULL, NULL, '2025-10-11 19:40:08'),
(1526, NULL, 'delete', NULL, '440', NULL, NULL, '2025-10-11 19:40:11'),
(1527, NULL, 'delete', NULL, '441', NULL, NULL, '2025-10-11 19:40:14'),
(1528, NULL, 'delete', NULL, '442', NULL, NULL, '2025-10-11 19:40:16'),
(1529, NULL, 'delete', NULL, '443', NULL, NULL, '2025-10-11 19:40:19'),
(1530, NULL, 'update_due', NULL, '444', NULL, NULL, '2025-10-11 19:40:37'),
(1531, NULL, 'update_due', NULL, '445', NULL, NULL, '2025-10-11 19:40:48'),
(1532, NULL, 'update_due', NULL, '284', NULL, NULL, '2025-10-11 19:40:57'),
(1533, NULL, 'update_due', NULL, '284', NULL, NULL, '2025-10-11 19:41:03'),
(1534, NULL, 'update_due', NULL, '285', NULL, NULL, '2025-10-11 19:41:10'),
(1535, NULL, 'delete', NULL, '40', NULL, NULL, '2025-10-11 19:43:35'),
(1536, NULL, 'delete', NULL, '37', NULL, NULL, '2025-10-11 19:43:47'),
(1537, NULL, 'update_due', NULL, '88', NULL, NULL, '2025-10-11 20:08:38'),
(1538, NULL, 'update_due', NULL, '88', NULL, NULL, '2025-10-11 20:08:41'),
(1539, NULL, 'delete', NULL, '88', NULL, NULL, '2025-10-11 20:08:43'),
(1540, NULL, 'delete', NULL, '89', NULL, NULL, '2025-10-11 20:08:47'),
(1541, NULL, 'delete', NULL, '90', NULL, NULL, '2025-10-11 20:08:52'),
(1542, NULL, 'public_apply', 'applicant', '29', NULL, NULL, '2025-10-11 14:12:08'),
(1543, NULL, 'public_apply', 'applicant', '30', NULL, NULL, '2025-10-11 15:07:14'),
(1544, NULL, 'submit_score', 'applicant', '19', NULL, NULL, '2025-10-11 15:12:40'),
(1545, NULL, 'set_status', 'applicant', '19', NULL, NULL, '2025-10-11 15:12:40'),
(1546, NULL, 'public_apply', 'applicant', '31', NULL, NULL, '2025-10-11 15:19:07'),
(1547, NULL, 'delete', NULL, '91', NULL, NULL, '2025-10-11 21:29:14'),
(1548, NULL, 'delete', NULL, '92', NULL, NULL, '2025-10-11 21:29:16'),
(1549, NULL, 'delete', NULL, '93', NULL, NULL, '2025-10-11 21:29:18'),
(1550, NULL, 'add', NULL, '524', NULL, NULL, '2025-10-11 21:29:25'),
(1551, NULL, 'add', NULL, '525', NULL, NULL, '2025-10-11 21:29:25'),
(1552, NULL, 'add', NULL, '526', NULL, NULL, '2025-10-11 21:29:25'),
(1553, NULL, 'add', NULL, '527', NULL, NULL, '2025-10-11 21:29:25'),
(1554, NULL, 'add', NULL, '528', NULL, NULL, '2025-10-11 21:29:25'),
(1555, NULL, 'add', NULL, '529', NULL, NULL, '2025-10-11 21:29:25'),
(1556, NULL, 'add', NULL, '530', NULL, NULL, '2025-10-11 21:29:25'),
(1557, NULL, 'add', NULL, '531', NULL, NULL, '2025-10-11 21:29:25'),
(1558, NULL, 'add', NULL, '532', NULL, NULL, '2025-10-11 21:29:25'),
(1559, NULL, 'add', NULL, '533', NULL, NULL, '2025-10-11 21:29:25'),
(1560, NULL, 'add', NULL, '534', NULL, NULL, '2025-10-11 21:29:25'),
(1561, NULL, 'add', NULL, '535', NULL, NULL, '2025-10-11 21:29:26'),
(1562, NULL, 'add', NULL, '536', NULL, NULL, '2025-10-11 21:29:26'),
(1563, NULL, 'delete', NULL, '524', NULL, NULL, '2025-10-11 21:29:28'),
(1564, NULL, 'delete', NULL, '525', NULL, NULL, '2025-10-11 21:29:30'),
(1565, NULL, 'delete', NULL, '526', NULL, NULL, '2025-10-11 21:29:31'),
(1566, NULL, 'delete', NULL, '527', NULL, NULL, '2025-10-11 21:29:33'),
(1567, NULL, 'delete', NULL, '528', NULL, NULL, '2025-10-11 21:29:34'),
(1568, NULL, 'delete', NULL, '529', NULL, NULL, '2025-10-11 21:29:35'),
(1569, NULL, 'delete', NULL, '530', NULL, NULL, '2025-10-11 21:29:37'),
(1570, NULL, 'delete', NULL, '531', NULL, NULL, '2025-10-11 21:29:40'),
(1571, NULL, 'delete', NULL, '532', NULL, NULL, '2025-10-11 21:29:43'),
(1572, NULL, 'delete', NULL, '533', NULL, NULL, '2025-10-11 21:29:47'),
(1573, NULL, 'delete', NULL, '534', NULL, NULL, '2025-10-11 21:29:48'),
(1574, NULL, 'delete', NULL, '535', NULL, NULL, '2025-10-11 21:29:50'),
(1575, NULL, 'delete', NULL, '536', NULL, NULL, '2025-10-11 21:29:51'),
(1576, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-11 15:30:37'),
(1577, NULL, 'toggle_shortlist', 'applicant', '13', NULL, NULL, '2025-10-11 15:30:37'),
(1578, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-11 15:30:40'),
(1579, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-11 15:30:40'),
(1580, NULL, 'add', NULL, '51', NULL, NULL, '2025-10-11 21:41:27'),
(1581, NULL, 'delete', NULL, '51', NULL, NULL, '2025-10-11 21:41:38'),
(1582, NULL, 'public_apply', 'applicant', '32', NULL, NULL, '2025-10-11 16:32:45'),
(1583, NULL, 'add', NULL, '52', NULL, NULL, '2025-10-11 22:33:52'),
(1584, NULL, 'add', NULL, '537', NULL, NULL, '2025-10-11 22:33:57'),
(1585, NULL, 'add', NULL, '538', NULL, NULL, '2025-10-11 22:33:57'),
(1586, NULL, 'add', NULL, '539', NULL, NULL, '2025-10-11 22:33:57'),
(1587, NULL, 'add', NULL, '540', NULL, NULL, '2025-10-11 22:33:57'),
(1588, NULL, 'add', NULL, '541', NULL, NULL, '2025-10-11 22:33:57'),
(1589, NULL, 'add', NULL, '542', NULL, NULL, '2025-10-11 22:33:57'),
(1590, NULL, 'add', NULL, '543', NULL, NULL, '2025-10-11 22:33:57'),
(1591, NULL, 'add', NULL, '544', NULL, NULL, '2025-10-11 22:33:57'),
(1592, NULL, 'add', NULL, '545', NULL, NULL, '2025-10-11 22:33:58'),
(1593, NULL, 'add', NULL, '546', NULL, NULL, '2025-10-11 22:33:58'),
(1594, NULL, 'add', NULL, '547', NULL, NULL, '2025-10-11 22:33:58'),
(1595, NULL, 'add', NULL, '548', NULL, NULL, '2025-10-11 22:33:58'),
(1596, NULL, 'archive', 'applicant', '9', NULL, NULL, '2025-10-11 16:35:02'),
(1597, NULL, 'unarchive', 'applicant', '9', NULL, NULL, '2025-10-11 22:35:06'),
(1598, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-11 16:35:20'),
(1599, NULL, 'toggle_shortlist', 'applicant', '17', NULL, NULL, '2025-10-14 06:00:18'),
(1600, NULL, 'notify', 'applicant', '9', NULL, NULL, '2025-10-14 06:16:06'),
(1601, NULL, 'update_due', NULL, '284', NULL, NULL, '2025-10-14 12:16:47'),
(1602, NULL, 'delete', NULL, '284', NULL, NULL, '2025-10-14 12:16:49'),
(1603, NULL, 'delete', NULL, '285', NULL, NULL, '2025-10-14 12:16:52'),
(1604, NULL, 'delete', NULL, '286', NULL, NULL, '2025-10-14 12:16:53'),
(1605, NULL, 'delete', NULL, '287', NULL, NULL, '2025-10-14 12:16:55'),
(1606, NULL, 'delete', NULL, '288', NULL, NULL, '2025-10-14 12:16:57'),
(1607, NULL, 'delete', NULL, '289', NULL, NULL, '2025-10-14 12:16:59'),
(1608, NULL, 'add', NULL, '549', NULL, NULL, '2025-10-14 12:17:21'),
(1609, NULL, 'add', NULL, '550', NULL, NULL, '2025-10-14 12:17:21'),
(1610, NULL, 'add', NULL, '551', NULL, NULL, '2025-10-14 12:17:21'),
(1611, NULL, 'add', NULL, '552', NULL, NULL, '2025-10-14 12:17:22'),
(1612, NULL, 'add', NULL, '553', NULL, NULL, '2025-10-14 12:17:22'),
(1613, NULL, 'add', NULL, '554', NULL, NULL, '2025-10-14 12:17:22'),
(1614, NULL, 'add', NULL, '555', NULL, NULL, '2025-10-14 12:17:22'),
(1615, NULL, 'add', NULL, '556', NULL, NULL, '2025-10-14 12:17:22'),
(1616, NULL, 'add', NULL, '557', NULL, NULL, '2025-10-14 12:17:22'),
(1617, NULL, 'add', NULL, '558', NULL, NULL, '2025-10-14 12:17:22'),
(1618, NULL, 'add', NULL, '559', NULL, NULL, '2025-10-14 12:17:22'),
(1619, NULL, 'add', NULL, '560', NULL, NULL, '2025-10-14 12:17:22'),
(1620, NULL, 'archive', 'applicant', '32', NULL, NULL, '2025-10-14 06:39:15'),
(1621, NULL, 'unarchive', 'applicant', '32', NULL, NULL, '2025-10-14 12:39:21'),
(1622, NULL, 'public_apply', 'applicant', '33', NULL, NULL, '2025-10-14 06:41:10'),
(1623, NULL, 'submit_score', 'applicant', '33', NULL, NULL, '2025-10-14 06:41:33'),
(1624, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-14 06:42:00'),
(1625, NULL, 'set_status', 'applicant', '9', NULL, NULL, '2025-10-14 06:42:00'),
(1626, NULL, 'submit_score', 'applicant', '9', NULL, NULL, '2025-10-14 06:42:07'),
(1627, NULL, 'submit_score', 'applicant', '13', NULL, NULL, '2025-10-14 06:42:58'),
(1628, NULL, 'set_status', 'applicant', '13', NULL, NULL, '2025-10-14 06:42:58'),
(1629, NULL, 'submit_score', 'applicant', '17', NULL, NULL, '2025-10-14 06:46:18'),
(1630, NULL, 'submit_score', 'applicant', '14', NULL, NULL, '2025-10-14 06:47:23'),
(1631, NULL, 'set_status', 'applicant', '14', NULL, NULL, '2025-10-14 06:47:23'),
(1632, NULL, 'submit_score', 'applicant', '32', NULL, NULL, '2025-10-14 06:47:41'),
(1633, NULL, 'public_apply', 'applicant', '34', NULL, NULL, '2025-10-14 06:54:17'),
(1634, NULL, 'submit_score', 'applicant', '34', NULL, NULL, '2025-10-14 06:54:34'),
(1635, NULL, 'set_status', 'applicant', '34', NULL, NULL, '2025-10-14 06:54:35'),
(1636, NULL, 'public_apply', 'applicant', '35', NULL, NULL, '2025-10-14 06:57:26'),
(1637, NULL, 'public_apply', 'applicant', '36', NULL, NULL, '2025-10-14 06:59:59'),
(1638, NULL, 'submit_score', 'applicant', '36', NULL, NULL, '2025-10-14 07:00:14'),
(1639, NULL, 'set_status', 'applicant', '36', NULL, NULL, '2025-10-14 07:00:14'),
(1640, NULL, 'public_apply', 'applicant', '37', NULL, NULL, '2025-10-14 07:21:06'),
(1641, NULL, 'submit_score', 'applicant', '37', NULL, NULL, '2025-10-14 07:21:22'),
(1642, NULL, 'set_status', 'applicant', '37', NULL, NULL, '2025-10-14 07:21:22'),
(1643, NULL, 'public_apply', 'applicant', '38', NULL, NULL, '2025-10-14 07:23:48'),
(1644, NULL, 'submit_score', 'applicant', '38', NULL, NULL, '2025-10-14 07:24:22'),
(1645, NULL, 'set_status', 'applicant', '38', NULL, NULL, '2025-10-14 07:24:22'),
(1646, NULL, 'public_apply', 'applicant', '39', NULL, NULL, '2025-10-14 07:45:17'),
(1647, NULL, 'submit_score', 'applicant', '39', NULL, NULL, '2025-10-14 07:45:49'),
(1648, NULL, 'set_status', 'applicant', '39', NULL, NULL, '2025-10-14 07:45:52'),
(1649, NULL, 'public_apply', 'applicant', '40', NULL, NULL, '2025-10-14 09:33:28'),
(1650, NULL, 'submit_score', 'applicant', '40', NULL, NULL, '2025-10-14 09:34:01'),
(1651, NULL, 'set_status', 'applicant', '40', NULL, NULL, '2025-10-14 09:34:05'),
(1652, NULL, 'add', NULL, '561', NULL, NULL, '2025-10-14 15:35:29'),
(1653, NULL, 'add', NULL, '562', NULL, NULL, '2025-10-14 15:35:29'),
(1654, NULL, 'add', NULL, '563', NULL, NULL, '2025-10-14 15:35:29'),
(1655, NULL, 'add', NULL, '564', NULL, NULL, '2025-10-14 15:35:29'),
(1656, NULL, 'add', NULL, '565', NULL, NULL, '2025-10-14 15:35:29'),
(1657, NULL, 'add', NULL, '566', NULL, NULL, '2025-10-14 15:35:29'),
(1658, NULL, 'add', NULL, '567', NULL, NULL, '2025-10-14 15:35:29'),
(1659, NULL, 'add', NULL, '568', NULL, NULL, '2025-10-14 15:35:29'),
(1660, NULL, 'add', NULL, '569', NULL, NULL, '2025-10-14 15:35:30'),
(1661, NULL, 'add', NULL, '570', NULL, NULL, '2025-10-14 15:35:30'),
(1662, NULL, 'add', NULL, '571', NULL, NULL, '2025-10-14 15:35:30'),
(1663, NULL, 'add', NULL, '572', NULL, NULL, '2025-10-14 15:35:30'),
(1664, NULL, 'add', NULL, '573', NULL, NULL, '2025-10-14 15:35:50'),
(1665, NULL, 'add', NULL, '574', NULL, NULL, '2025-10-14 15:35:50'),
(1666, NULL, 'add', NULL, '575', NULL, NULL, '2025-10-14 15:35:50'),
(1667, NULL, 'add', NULL, '576', NULL, NULL, '2025-10-14 15:35:50'),
(1668, NULL, 'add', NULL, '577', NULL, NULL, '2025-10-14 15:35:50'),
(1669, NULL, 'add', NULL, '578', NULL, NULL, '2025-10-14 15:35:50'),
(1670, NULL, 'add', NULL, '579', NULL, NULL, '2025-10-14 15:35:50'),
(1671, NULL, 'add', NULL, '580', NULL, NULL, '2025-10-14 15:35:50'),
(1672, NULL, 'add', NULL, '581', NULL, NULL, '2025-10-14 15:35:50'),
(1673, NULL, 'add', NULL, '582', NULL, NULL, '2025-10-14 15:35:50'),
(1674, NULL, 'add', NULL, '583', NULL, NULL, '2025-10-14 15:35:50'),
(1675, NULL, 'add', NULL, '584', NULL, NULL, '2025-10-14 15:35:51'),
(1676, NULL, 'add', NULL, '585', NULL, NULL, '2025-10-14 15:36:21'),
(1677, NULL, 'add', NULL, '586', NULL, NULL, '2025-10-14 15:36:21'),
(1678, NULL, 'add', NULL, '587', NULL, NULL, '2025-10-14 15:36:21'),
(1679, NULL, 'add', NULL, '588', NULL, NULL, '2025-10-14 15:36:21'),
(1680, NULL, 'add', NULL, '589', NULL, NULL, '2025-10-14 15:36:21'),
(1681, NULL, 'add', NULL, '590', NULL, NULL, '2025-10-14 15:36:21'),
(1682, NULL, 'add', NULL, '591', NULL, NULL, '2025-10-14 15:36:21'),
(1683, NULL, 'add', NULL, '592', NULL, NULL, '2025-10-14 15:36:21'),
(1684, NULL, 'add', NULL, '593', NULL, NULL, '2025-10-14 15:36:21'),
(1685, NULL, 'add', NULL, '594', NULL, NULL, '2025-10-14 15:36:21'),
(1686, NULL, 'add', NULL, '595', NULL, NULL, '2025-10-14 15:36:21'),
(1687, NULL, 'add', NULL, '596', NULL, NULL, '2025-10-14 15:36:21'),
(1688, NULL, 'add', NULL, '597', NULL, NULL, '2025-10-14 15:36:21'),
(1689, NULL, 'delete', NULL, '561', NULL, NULL, '2025-10-14 15:36:45'),
(1690, NULL, 'delete', NULL, '562', NULL, NULL, '2025-10-14 15:36:46'),
(1691, NULL, 'delete', NULL, '563', NULL, NULL, '2025-10-14 15:36:48'),
(1692, NULL, 'delete', NULL, '564', NULL, NULL, '2025-10-14 15:36:50'),
(1693, NULL, 'delete', NULL, '565', NULL, NULL, '2025-10-14 15:36:51'),
(1694, NULL, 'delete', NULL, '566', NULL, NULL, '2025-10-14 15:36:53'),
(1695, NULL, 'delete', NULL, '567', NULL, NULL, '2025-10-14 15:36:54'),
(1696, NULL, 'delete', NULL, '568', NULL, NULL, '2025-10-14 15:36:56'),
(1697, NULL, 'delete', NULL, '569', NULL, NULL, '2025-10-14 15:36:57'),
(1698, NULL, 'delete', NULL, '570', NULL, NULL, '2025-10-14 15:37:00'),
(1699, NULL, 'delete', NULL, '571', NULL, NULL, '2025-10-14 15:37:01'),
(1700, NULL, 'delete', NULL, '572', NULL, NULL, '2025-10-14 15:37:02'),
(1701, NULL, 'delete', NULL, '537', NULL, NULL, '2025-10-14 15:37:09'),
(1702, NULL, 'delete', NULL, '538', NULL, NULL, '2025-10-14 15:37:11'),
(1703, NULL, 'delete', NULL, '539', NULL, NULL, '2025-10-14 15:37:13'),
(1704, NULL, 'delete', NULL, '540', NULL, NULL, '2025-10-14 15:37:14'),
(1705, NULL, 'delete', NULL, '541', NULL, NULL, '2025-10-14 15:37:16'),
(1706, NULL, 'delete', NULL, '542', NULL, NULL, '2025-10-14 15:37:17'),
(1707, NULL, 'delete', NULL, '543', NULL, NULL, '2025-10-14 15:37:19'),
(1708, NULL, 'delete', NULL, '544', NULL, NULL, '2025-10-14 15:37:21'),
(1709, NULL, 'update_due', NULL, '545', NULL, NULL, '2025-10-14 15:37:24'),
(1710, NULL, 'update_due', NULL, '545', NULL, NULL, '2025-10-14 15:37:33'),
(1711, NULL, 'delete', NULL, '545', NULL, NULL, '2025-10-14 15:37:36'),
(1712, NULL, 'delete', NULL, '546', NULL, NULL, '2025-10-14 15:37:37'),
(1713, NULL, 'delete', NULL, '547', NULL, NULL, '2025-10-14 15:37:39'),
(1714, NULL, 'delete', NULL, '548', NULL, NULL, '2025-10-14 15:37:40'),
(1715, NULL, 'delete', NULL, '573', NULL, NULL, '2025-10-14 15:37:53'),
(1716, NULL, 'delete', NULL, '574', NULL, NULL, '2025-10-14 15:37:55'),
(1717, NULL, 'delete', NULL, '575', NULL, NULL, '2025-10-14 15:37:57'),
(1718, NULL, 'delete', NULL, '576', NULL, NULL, '2025-10-14 15:37:59'),
(1719, NULL, 'delete', NULL, '577', NULL, NULL, '2025-10-14 15:38:00'),
(1720, NULL, 'delete', NULL, '578', NULL, NULL, '2025-10-14 15:38:02'),
(1721, NULL, 'delete', NULL, '579', NULL, NULL, '2025-10-14 15:38:03'),
(1722, NULL, 'delete', NULL, '580', NULL, NULL, '2025-10-14 15:38:05'),
(1723, NULL, 'delete', NULL, '581', NULL, NULL, '2025-10-14 15:38:07'),
(1724, NULL, 'delete', NULL, '582', NULL, NULL, '2025-10-14 15:38:09'),
(1725, NULL, 'delete', NULL, '583', NULL, NULL, '2025-10-14 15:38:10'),
(1726, NULL, 'delete', NULL, '584', NULL, NULL, '2025-10-14 15:38:11'),
(1727, NULL, 'delete', NULL, '585', NULL, NULL, '2025-10-14 15:38:25'),
(1728, NULL, 'delete', NULL, '586', NULL, NULL, '2025-10-14 15:38:27'),
(1729, NULL, 'delete', NULL, '587', NULL, NULL, '2025-10-14 15:38:30'),
(1730, NULL, 'delete', NULL, '588', NULL, NULL, '2025-10-14 15:38:35'),
(1731, NULL, 'delete', NULL, '589', NULL, NULL, '2025-10-14 15:38:38'),
(1732, NULL, 'delete', NULL, '590', NULL, NULL, '2025-10-14 15:38:42'),
(1733, NULL, 'delete', NULL, '591', NULL, NULL, '2025-10-14 15:38:46'),
(1734, NULL, 'delete', NULL, '592', NULL, NULL, '2025-10-14 15:38:48'),
(1735, NULL, 'delete', NULL, '593', NULL, NULL, '2025-10-14 15:38:50'),
(1736, NULL, 'delete', NULL, '594', NULL, NULL, '2025-10-14 15:38:51'),
(1737, NULL, 'delete', NULL, '595', NULL, NULL, '2025-10-14 15:38:53'),
(1738, NULL, 'delete', NULL, '596', NULL, NULL, '2025-10-14 15:38:55'),
(1739, NULL, 'delete', NULL, '597', NULL, NULL, '2025-10-14 15:38:56'),
(1740, NULL, 'public_apply', 'applicant', '41', NULL, NULL, '2025-10-14 11:32:07'),
(1741, NULL, 'submit_score', 'applicant', '41', NULL, NULL, '2025-10-14 11:32:30'),
(1742, NULL, 'set_status', 'applicant', '41', NULL, NULL, '2025-10-14 11:32:33'),
(1743, NULL, 'add', NULL, '598', NULL, NULL, '2025-10-14 17:34:39'),
(1744, NULL, 'add', NULL, '599', NULL, NULL, '2025-10-14 17:34:39'),
(1745, NULL, 'add', NULL, '600', NULL, NULL, '2025-10-14 17:34:39'),
(1746, NULL, 'add', NULL, '601', NULL, NULL, '2025-10-14 17:34:39'),
(1747, NULL, 'add', NULL, '602', NULL, NULL, '2025-10-14 17:34:39'),
(1748, NULL, 'add', NULL, '603', NULL, NULL, '2025-10-14 17:34:39'),
(1749, NULL, 'add', NULL, '604', NULL, NULL, '2025-10-14 17:34:39'),
(1750, NULL, 'add', NULL, '605', NULL, NULL, '2025-10-14 17:34:39'),
(1751, NULL, 'add', NULL, '606', NULL, NULL, '2025-10-14 17:34:39'),
(1752, NULL, 'add', NULL, '607', NULL, NULL, '2025-10-14 17:34:39'),
(1753, NULL, 'add', NULL, '608', NULL, NULL, '2025-10-14 17:34:39'),
(1754, NULL, 'add', NULL, '609', NULL, NULL, '2025-10-14 17:34:39'),
(1755, NULL, 'delete', NULL, '598', NULL, NULL, '2025-10-14 17:34:44'),
(1756, NULL, 'delete', NULL, '599', NULL, NULL, '2025-10-14 17:34:46'),
(1757, NULL, 'delete', NULL, '600', NULL, NULL, '2025-10-14 17:34:48'),
(1758, NULL, 'delete', NULL, '601', NULL, NULL, '2025-10-14 17:34:50'),
(1759, NULL, 'delete', NULL, '602', NULL, NULL, '2025-10-14 17:34:51'),
(1760, NULL, 'delete', NULL, '603', NULL, NULL, '2025-10-14 17:34:52'),
(1761, NULL, 'delete', NULL, '604', NULL, NULL, '2025-10-14 17:34:53'),
(1762, NULL, 'delete', NULL, '605', NULL, NULL, '2025-10-14 17:34:55'),
(1763, NULL, 'delete', NULL, '606', NULL, NULL, '2025-10-14 17:34:56'),
(1764, NULL, 'delete', NULL, '607', NULL, NULL, '2025-10-14 17:34:57'),
(1765, NULL, 'delete', NULL, '608', NULL, NULL, '2025-10-14 17:34:58'),
(1766, NULL, 'delete', NULL, '609', NULL, NULL, '2025-10-14 17:34:59'),
(1767, NULL, 'archive', 'applicant', '19', NULL, NULL, '2025-10-17 08:59:30'),
(1768, NULL, 'unarchive', 'applicant', '19', NULL, NULL, '2025-10-17 14:59:35'),
(1769, NULL, 'submit_score', 'applicant', '19', NULL, NULL, '2025-10-17 09:00:22'),
(1770, NULL, 'set_status', 'applicant', '19', NULL, NULL, '2025-10-17 09:00:26'),
(1771, NULL, 'public_apply', 'applicant', '42', NULL, NULL, '2025-10-17 12:53:47'),
(1772, NULL, 'submit_score', 'applicant', '42', NULL, NULL, '2025-10-17 12:54:07'),
(1773, NULL, 'set_status', 'applicant', '42', NULL, NULL, '2025-10-17 12:54:11');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `applicant_id` int(11) DEFAULT NULL,
  `emp_code` varchar(30) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `status` enum('probation','regular','inactive') DEFAULT 'probation',
  `profile_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`profile_json`)),
  `source_plan_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role` varchar(150) DEFAULT NULL,
  `department` varchar(150) DEFAULT 'Operations',
  `site` varchar(150) DEFAULT 'Main Store',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `role_id` int(11) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `applicant_id`, `emp_code`, `date_hired`, `status`, `profile_json`, `source_plan_id`, `name`, `role`, `department`, `site`, `updated_at`, `role_id`, `is_archived`) VALUES
(1, 3, NULL, 'EMP001', '2025-09-01', 'probation', '{\"address\":\"QC\",\"emergency_contact\":\"09171234567\"}', NULL, NULL, NULL, 'Operations', 'Main Store', NULL, NULL, 0),
(2, NULL, NULL, NULL, '2025-10-05', '', NULL, 12, NULL, NULL, 'Operations', 'Main Store', NULL, NULL, 0),
(3, NULL, NULL, NULL, '2025-10-05', '', NULL, 13, NULL, NULL, 'Operations', 'Main Store', NULL, NULL, 0),
(4, NULL, NULL, NULL, '2025-10-05', '', NULL, 9, NULL, NULL, 'Operations', 'Main Store', NULL, NULL, 0),
(5, NULL, 9, NULL, '2025-10-11', 'regular', NULL, NULL, 'Danilo Vergara Jr', 'Order Processor', 'Operations', 'Main Store', '2025-10-11 05:28:30', NULL, 0),
(6, NULL, 10, NULL, '2025-10-19', 'regular', NULL, NULL, 'Juan Dela Cruz', 'Store Part Timer', 'Operations', 'Main Store', '2025-10-10 18:10:35', NULL, 0),
(7, NULL, 14, NULL, '2025-10-10', 'regular', NULL, NULL, 'James Villanueva', 'Store Part Timer', 'Operations', 'Main Store', '2025-10-12 06:01:37', NULL, 0),
(8, NULL, 15, NULL, '2025-10-11', 'regular', NULL, NULL, 'Mellisa Co', 'Store Part Timer', 'Operations', 'Main Store', '2025-10-10 00:13:32', NULL, 0),
(11, NULL, 12, NULL, '2025-10-11', 'regular', NULL, NULL, 'Mark Juan', 'Store Part Timer', 'Operations', 'Main Store', '2025-10-10 00:13:34', NULL, 0),
(12, NULL, 13, NULL, '2025-10-09', 'regular', NULL, NULL, 'Alex Santos', 'Store Part Timer', 'Operations', 'Banawe', '2025-10-12 06:45:57', NULL, 0),
(13, NULL, 19, NULL, '2025-10-17', '', NULL, NULL, 'Jake Luigi', 'Store Part Timer', 'Operations', 'Banawe', '2025-10-17 23:00:22', NULL, 1),
(14, NULL, 20, NULL, '2025-10-09', 'regular', NULL, NULL, 'Hans San jose', 'Deputy Store Manager', 'Operations', 'Main Store', '2025-10-12 06:45:46', NULL, 1),
(15, NULL, 22, NULL, '2025-10-11', 'regular', NULL, NULL, 'Kate Velasco', 'Cashier', 'Operations', 'Main Store', '2025-10-11 19:29:15', NULL, 1),
(16, NULL, 23, NULL, '2025-10-11', 'regular', NULL, NULL, 'Juan De Jesus', 'Merchandiser / Promodiser', 'Operations', 'Main Store', '2025-10-11 04:51:06', NULL, 0),
(17, NULL, 11, NULL, '2025-10-18', 'regular', NULL, NULL, 'Kevin Durant', 'Deputy Store Manager', 'Operations', 'Main Store', NULL, NULL, 0),
(18, NULL, 21, NULL, '2025-10-09', 'regular', NULL, NULL, 'Angelica Dorado', 'Deputy Store Manager', 'Operations', 'Main Store', '2025-10-12 06:00:39', NULL, 0),
(19, NULL, 24, NULL, '2025-10-10', 'regular', NULL, NULL, 'Raymart Castro', 'Order Processor', 'Operations', 'Main Store', '2025-10-10 21:31:36', NULL, 0),
(21, NULL, 25, NULL, '2025-10-10', 'regular', NULL, NULL, 'Josh Dendi', 'Cashier', 'Operations', 'Main Store', NULL, NULL, 0),
(22, NULL, 26, NULL, '2025-10-11', 'regular', NULL, NULL, 'Dan Vergara', 'Order Processor', 'Operations', 'Main Store', '2025-10-11 05:26:25', NULL, 0),
(23, NULL, 27, NULL, '2025-10-10', 'regular', NULL, NULL, 'Kyle Varga', 'Store Manager', 'Operations', 'Main Store', NULL, NULL, 0),
(24, NULL, NULL, NULL, '2025-10-10', 'regular', NULL, NULL, 'Robert Gonzales', 'Order Processor', 'Operations', 'Main Store', '2025-10-11 06:21:07', NULL, 1),
(29, NULL, NULL, NULL, '2025-10-12', 'regular', NULL, NULL, 'Jay Gomez', 'Store Manager', 'Operation', 'Main Store', '2025-10-14 20:00:43', NULL, 0),
(30, NULL, NULL, NULL, '2025-10-12', '', NULL, NULL, 'dwadsa', 'dwasda', 'dwasa', 'Main Store', '2025-10-12 07:40:42', NULL, 1),
(31, NULL, NULL, NULL, '2025-10-12', '', NULL, NULL, 'sdawda', 'sdawdsa', 'sdawd', 'Main Store', '2025-10-12 07:40:40', NULL, 1),
(32, NULL, NULL, NULL, '2025-10-12', '', NULL, NULL, 'awdsa', 'dwasa', 'dwads', 'Main Store', '2025-10-12 07:40:37', NULL, 1),
(33, NULL, 34, NULL, '2025-10-14', 'regular', NULL, NULL, 'Chamber Juan', 'Order Processor', 'Operations', 'Main Store', '2025-10-14 20:55:26', NULL, 0),
(34, NULL, 36, NULL, '2025-10-14', 'regular', NULL, NULL, 'Dans Vergara', 'Order Processor', 'Operations', 'Main Store', '2025-10-14 21:50:41', NULL, 0),
(35, NULL, 37, NULL, '2025-10-14', 'regular', NULL, NULL, 'Veto Santos', 'Order Processor', 'Operations', 'Main Store', '2025-10-14 21:50:48', NULL, 0),
(36, NULL, 38, NULL, '2025-10-14', 'regular', NULL, NULL, 'Breach Gomez', 'Merchandiser / Promodiser', 'Operations', 'Main Store', '2025-10-14 21:50:53', NULL, 0),
(37, NULL, 39, NULL, '2025-10-14', 'probation', NULL, NULL, 'Lancelot Maliper', 'Inventory Clerk / Stockman', 'Operations', 'Main Store', '2025-10-14 21:50:58', NULL, 0),
(38, NULL, 40, NULL, '2025-10-14', '', NULL, NULL, 'Cassandra Lopez', 'Inventory Clerk / Stockman', 'Operations', 'Main Store', '2025-10-14 23:34:01', NULL, 0),
(39, NULL, 41, NULL, '2025-10-14', '', NULL, NULL, 'Klein De Jesus', 'Inventory Clerk / Stockman', 'Operations', 'Main Store', '2025-10-15 01:32:30', NULL, 0),
(40, NULL, 42, NULL, '2025-10-17', '', NULL, NULL, 'Kyle Kuzma', 'Inventory Clerk / Stockman', 'Operations', 'Main Store', '2025-10-18 02:54:07', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `period` varchar(40) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','due_soon','overdue','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `rubric_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rubric_json`)),
  `scores_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scores_json`)),
  `overall_score` decimal(5,2) DEFAULT NULL,
  `narrative` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `employee_id`, `period`, `due_date`, `status`, `notes`, `rubric_json`, `scores_json`, `overall_score`, `narrative`, `created_at`, `updated_at`) VALUES
(8, 5, 'Initial 60 Day', '2025-10-16', 'completed', '', NULL, NULL, 4.00, NULL, '2025-10-10 05:21:20', '2025-10-10 18:47:11'),
(10, 16, 'Mid-Year', '2025-10-12', 'completed', '', NULL, NULL, 3.00, NULL, '2025-10-10 07:34:39', '2025-10-10 21:58:15'),
(11, 8, 'Initial 90 Day', '2025-10-14', 'completed', '', NULL, NULL, 4.00, NULL, '2025-10-10 07:34:54', '2025-10-11 14:04:11'),
(12, 19, 'Initial 30 Day', '2025-11-07', 'completed', '', NULL, NULL, NULL, NULL, '2025-10-10 07:35:04', '2025-10-11 18:19:40'),
(13, 11, 'Mid-Year', '2025-10-17', 'completed', '', NULL, NULL, 3.00, NULL, '2025-10-10 07:35:18', '2025-10-10 21:58:31'),
(14, 22, 'Initial 60 Day', '2025-10-10', 'completed', '', NULL, NULL, 4.00, NULL, '2025-10-10 13:57:47', '2025-10-10 21:58:06'),
(15, 22, 'Initial 30 Day', '2025-10-06', 'completed', '', NULL, NULL, NULL, NULL, '2025-10-11 01:07:23', '2025-10-11 18:19:09'),
(16, 12, 'Initial 30 Day', '2025-10-11', 'pending', NULL, NULL, NULL, NULL, NULL, '2025-10-11 23:33:25', '2025-10-12 07:33:25');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_forms`
--

CREATE TABLE `evaluation_forms` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `template` varchar(120) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eval_forms`
--

CREATE TABLE `eval_forms` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `template` varchar(80) NOT NULL,
  `status` enum('Pending','Submitted') NOT NULL DEFAULT 'Pending',
  `score` tinyint(4) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `communication` tinyint(4) DEFAULT NULL,
  `experience` tinyint(4) DEFAULT NULL,
  `culture_fit` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eval_forms`
--

INSERT INTO `eval_forms` (`id`, `applicant_id`, `template`, `status`, `score`, `remarks`, `created_at`, `communication`, `experience`, `culture_fit`) VALUES
(1, 13, '', 'Pending', 32, NULL, '2025-10-14 20:42:58', NULL, NULL, NULL),
(2, 17, '', 'Pending', 87, NULL, '2025-10-14 20:46:18', NULL, NULL, NULL),
(3, 12, '', 'Pending', 0, NULL, '2025-10-11 04:45:39', NULL, NULL, NULL),
(4, 9, '', 'Pending', 0, NULL, '2025-10-14 20:42:07', NULL, NULL, NULL),
(5, 14, '', 'Pending', 87, NULL, '2025-10-14 20:47:22', NULL, NULL, NULL),
(6, 15, '', 'Pending', 0, NULL, '2025-10-10 22:07:29', NULL, NULL, NULL),
(7, 16, '', 'Pending', 0, NULL, '2025-10-11 07:31:28', NULL, NULL, NULL),
(8, 19, '', 'Pending', 78, NULL, '2025-10-17 23:00:22', NULL, NULL, NULL),
(9, 20, '', 'Pending', 0, NULL, '2025-10-11 05:53:17', NULL, NULL, NULL),
(10, 21, '', 'Pending', 56, NULL, '2025-10-10 05:07:32', NULL, NULL, NULL),
(11, 22, '', 'Pending', 56, NULL, '2025-10-10 04:36:10', NULL, NULL, NULL),
(12, 11, '', 'Pending', 78, NULL, '2025-10-10 04:46:51', NULL, NULL, NULL),
(13, 23, '', 'Pending', 55, NULL, '2025-10-10 04:56:20', NULL, NULL, NULL),
(14, 10, '', 'Pending', 67, NULL, '2025-10-10 05:12:15', NULL, NULL, NULL),
(15, 24, '', 'Pending', 65, NULL, '2025-10-10 14:31:49', NULL, NULL, NULL),
(16, 25, '', 'Pending', 0, NULL, '2025-10-10 18:15:12', NULL, NULL, NULL),
(17, 33, '', 'Pending', 89, NULL, '2025-10-14 20:41:33', NULL, NULL, NULL),
(18, 32, '', 'Pending', 78, NULL, '2025-10-14 20:47:41', NULL, NULL, NULL),
(19, 34, '', 'Pending', 65, NULL, '2025-10-14 20:54:34', NULL, NULL, NULL),
(20, 36, '', 'Pending', 87, NULL, '2025-10-14 21:00:14', NULL, NULL, NULL),
(21, 37, '', 'Pending', 76, NULL, '2025-10-14 21:21:22', NULL, NULL, NULL),
(22, 38, '', 'Pending', 98, NULL, '2025-10-14 21:24:22', NULL, NULL, NULL),
(23, 39, '', 'Pending', 86, NULL, '2025-10-14 21:45:49', NULL, NULL, NULL),
(24, 40, '', 'Pending', 78, NULL, '2025-10-14 23:34:01', NULL, NULL, NULL),
(25, 41, '', 'Pending', 78, NULL, '2025-10-15 01:32:30', NULL, NULL, NULL),
(26, 42, '', 'Pending', 66, NULL, '2025-10-18 02:54:07', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `req_no` varchar(32) DEFAULT NULL,
  `i_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `panel` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `applicant_id`, `req_no`, `i_date`, `start_time`, `end_time`, `panel`, `created_at`) VALUES
(1, 9, NULL, '0000-00-00', '01:07:00', '00:00:00', '', '2025-10-09 19:07:27'),
(2, 24, NULL, '0000-00-00', '14:16:00', '00:00:00', '', '2025-10-10 08:16:19'),
(3, 25, NULL, '0000-00-00', '18:13:00', '00:00:00', '', '2025-10-10 12:13:37'),
(4, 26, NULL, '0000-00-00', '21:51:00', '00:00:00', '', '2025-10-10 15:51:28'),
(5, 9, NULL, '0000-00-00', '00:00:00', '00:00:00', '', '2025-10-14 20:10:47'),
(6, 9, NULL, '0000-00-00', '00:00:00', '00:00:00', '', '2025-10-14 20:12:19'),
(7, 9, NULL, '0000-00-00', '00:00:00', '00:00:00', '', '2025-10-14 20:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `interview_batches`
--

CREATE TABLE `interview_batches` (
  `id` int(11) NOT NULL,
  `req_no` varchar(32) NOT NULL,
  `iv_date` date DEFAULT NULL,
  `iv_start` time DEFAULT NULL,
  `iv_end` time DEFAULT NULL,
  `panel` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interview_batches`
--

INSERT INTO `interview_batches` (`id`, `req_no`, `iv_date`, `iv_start`, `iv_end`, `panel`, `created_at`) VALUES
(1, 'REQ-2025-003', '0000-00-00', '05:30:00', '07:31:00', 'Anna', '2025-10-06 21:31:07'),
(2, 'REQ-2025-004', '0000-00-00', '00:00:00', '00:00:00', 'dwadsdwa', '2025-10-06 22:03:23');

-- --------------------------------------------------------

--
-- Table structure for table `interview_schedules`
--

CREATE TABLE `interview_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `req_no` varchar(64) NOT NULL,
  `applicant_id` int(10) UNSIGNED DEFAULT NULL,
  `sched_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `panel` text DEFAULT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interview_schedules`
--

INSERT INTO `interview_schedules` (`id`, `req_no`, `applicant_id`, `sched_date`, `start_time`, `end_time`, `panel`, `is_done`, `created_at`, `created_by`) VALUES
(1, 'REQ-2025-001', NULL, '2025-10-07', '10:00:00', '11:00:00', 'HR – Anna, Ops – Kyle', 1, '2025-10-07 15:19:52', NULL),
(2, 'REQ-2025-004', NULL, '2025-10-08', '13:30:00', '14:00:00', 'HR – Bea; TL – Jay', 1, '2025-10-07 15:19:52', NULL),
(3, 'REQ-2025-001', NULL, '2025-10-07', '10:00:00', '11:00:00', 'HR – Anna, Ops – Kyle', 1, '2025-10-07 15:20:30', 1),
(4, 'REQ-2025-001', NULL, '2025-10-07', '23:35:00', '23:36:00', 'Anna', 1, '2025-10-07 15:35:51', NULL),
(5, 'REQ-2025-004', NULL, '2025-10-07', '23:37:00', '23:39:00', 'Heeeeee', 1, '2025-10-07 15:36:17', NULL),
(7, 'REQ-2025-006', NULL, '2025-10-16', '23:49:00', '23:50:00', 'dwdwdwd', 1, '2025-10-07 15:47:40', NULL),
(9, 'REQ-2025-006', NULL, '2025-10-07', '23:54:00', '12:55:00', 'adaa', 1, '2025-10-07 15:55:04', NULL),
(10, 'REQ-2025-004', NULL, '2025-10-08', '02:03:00', '03:03:00', 'gegege', 1, '2025-10-07 16:03:45', NULL),
(11, 'REQ-2025-001', NULL, '2025-10-08', '00:06:00', '05:03:00', 'adada', 1, '2025-10-07 16:04:02', NULL),
(12, 'REQ-2025-008', NULL, '2025-10-08', '01:20:00', '01:22:00', 'nice', 1, '2025-10-07 17:20:40', NULL),
(13, 'REQ-2025-008', NULL, '2025-10-09', '12:50:00', '12:51:00', '', 1, '2025-10-09 04:50:41', NULL),
(14, 'REQ-2025-010', NULL, '2025-10-12', '14:23:00', '18:23:00', 'HR - Anna', 1, '2025-10-09 06:24:16', NULL),
(15, 'REQ-2025-010', NULL, '2025-10-10', '00:22:00', '00:23:00', 'sheeehhhee', 1, '2025-10-09 16:22:35', NULL),
(16, 'REQ-2025-012', NULL, '2025-10-11', '04:19:00', '04:19:00', 'Renz', 1, '2025-10-10 20:20:05', NULL),
(17, 'REQ-2025-012', NULL, '2025-10-20', '07:48:00', '18:49:00', 'Mark', 1, '2025-10-10 23:49:24', NULL),
(18, 'REQ-2025-012', NULL, '2025-10-12', '09:50:00', '19:50:00', 'Mark', 1, '2025-10-10 23:50:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `newhire_uploads`
--

CREATE TABLE `newhire_uploads` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `file_key` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newhire_uploads`
--

INSERT INTO `newhire_uploads` (`id`, `applicant_id`, `file_key`, `file_path`, `uploaded_at`) VALUES
(1, 9, 'gov_id1', 'uploads/newhire/9/gov_id1_20251010_203955.jpg', '2025-10-11 02:39:55'),
(2, 9, 'gov_id2', 'uploads/newhire/9/gov_id2_20251010_203955.jpg', '2025-10-11 02:39:55'),
(3, 9, 'sss', 'uploads/newhire/9/sss_20251010_203955.jpg', '2025-10-11 02:39:55'),
(4, 9, 'pagibig', 'uploads/newhire/9/pagibig_20251010_203955.jpg', '2025-10-11 02:39:55'),
(5, 9, 'philhealth', 'uploads/newhire/9/philhealth_20251010_203955.jpg', '2025-10-11 02:39:55'),
(6, 9, 'tin', 'uploads/newhire/9/tin_20251010_203955.jpg', '2025-10-11 02:39:55'),
(7, 9, 'nbi', 'uploads/newhire/9/nbi_20251010_203955.jpg', '2025-10-11 02:39:55'),
(8, 9, 'photo2x2', 'uploads/newhire/9/photo2x2_20251010_203955.jpg', '2025-10-11 02:39:55'),
(9, 9, 'diploma', 'uploads/newhire/9/diploma_20251010_203955.jpg', '2025-10-11 02:39:55'),
(37, 26, 'gov_id1', 'uploads/newhire/26/gov_id1_20251011_034514.jpg', '2025-10-11 09:45:14'),
(38, 26, 'gov_id2', 'uploads/newhire/26/gov_id2_20251011_034514.jpg', '2025-10-11 09:45:14'),
(39, 26, 'sss', 'uploads/newhire/26/sss_20251011_034514.jpg', '2025-10-11 09:45:14'),
(40, 26, 'pagibig', 'uploads/newhire/26/pagibig_20251011_034514.jpg', '2025-10-11 09:45:14'),
(41, 26, 'philhealth', 'uploads/newhire/26/philhealth_20251011_034514.jpg', '2025-10-11 09:45:14'),
(42, 26, 'tin', 'uploads/newhire/26/tin_20251011_034514.jpg', '2025-10-11 09:45:14'),
(43, 26, 'nbi', 'uploads/newhire/26/nbi_20251011_034514.jpg', '2025-10-11 09:45:14'),
(44, 26, 'photo2x2', 'uploads/newhire/26/photo2x2_20251011_034514.pdf', '2025-10-11 09:45:14'),
(45, 26, 'diploma', 'uploads/newhire/26/diploma_20251011_034514.pdf', '2025-10-11 09:45:14'),
(55, 29, 'gov_id1', 'uploads/newhire/29/gov_id1_20251011_221753.jpg', '2025-10-12 04:17:53'),
(56, 29, 'gov_id2', 'uploads/newhire/29/gov_id2_20251011_221754.jpg', '2025-10-12 04:17:54'),
(57, 29, 'sss', 'uploads/newhire/29/sss_20251011_221754.jpg', '2025-10-12 04:17:54'),
(58, 29, 'philhealth', 'uploads/newhire/29/philhealth_20251011_221755.jpg', '2025-10-12 04:17:55'),
(59, 29, 'tin', 'uploads/newhire/29/tin_20251011_221756.jpg', '2025-10-12 04:17:56'),
(60, 29, 'photo2x2', 'uploads/newhire/29/photo2x2_20251011_221757.jpg', '2025-10-12 04:17:57'),
(61, 29, 'diploma', 'uploads/newhire/29/diploma_20251011_221757.jpg', '2025-10-12 04:17:57'),
(62, 32, 'gov_id1', 'uploads/newhire/32/gov_id1_20251012_003715.jpg', '2025-10-12 06:37:15'),
(63, 39, 'gov_id1', 'uploads/newhire/39/gov_id1_20251014_172441.png', '2025-10-14 23:24:41'),
(64, 39, 'gov_id2', 'uploads/newhire/39/gov_id2_20251014_172441.png', '2025-10-14 23:24:41'),
(65, 39, 'sss', 'uploads/newhire/39/sss_20251014_172441.pdf', '2025-10-14 23:24:41'),
(66, 39, 'pagibig', 'uploads/newhire/39/pagibig_20251014_172441.pdf', '2025-10-14 23:24:41'),
(67, 39, 'philhealth', 'uploads/newhire/39/philhealth_20251014_172441.pdf', '2025-10-14 23:24:41'),
(68, 39, 'tin', 'uploads/newhire/39/tin_20251014_172441.pdf', '2025-10-14 23:24:41'),
(69, 39, 'nbi', 'uploads/newhire/39/nbi_20251014_172441.pdf', '2025-10-14 23:24:41'),
(70, 39, 'photo2x2', 'uploads/newhire/39/photo2x2_20251014_172441.png', '2025-10-14 23:24:41'),
(79, 41, 'gov_id1', 'uploads/newhire/41/gov_id1_20251014_193349.png', '2025-10-15 01:33:49'),
(80, 41, 'gov_id2', 'uploads/newhire/41/gov_id2_20251014_193349.jpeg', '2025-10-15 01:33:49');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `channel_email` tinyint(1) NOT NULL DEFAULT 0,
  `channel_sms` tinyint(1) NOT NULL DEFAULT 0,
  `subject` varchar(190) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status_from` varchar(40) DEFAULT NULL,
  `status_to` varchar(40) DEFAULT NULL,
  `sent_ok` tinyint(1) NOT NULL DEFAULT 0,
  `error_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `applicant_id`, `type`, `payload`, `sent_at`, `channel_email`, `channel_sms`, `subject`, `message`, `status_from`, `status_to`, `sent_ok`, `error_text`, `created_at`) VALUES
(1, 9, '', NULL, '2025-10-08 09:50:01', 1, 0, NULL, 'Hi Danilo, Thank you for applying', NULL, NULL, 0, NULL, '2025-10-08 09:50:01'),
(2, 9, '', NULL, '2025-10-08 10:04:06', 1, 0, NULL, 'Hi Danilo Vergara Jr,\nThis is to inform you that your application status is now: PENDING.\nThank you for applying to HR1 MerchFlow.', NULL, NULL, 0, NULL, '2025-10-08 10:04:06'),
(3, 9, '', NULL, '2025-10-08 10:20:57', 1, 0, 'Application Update – New', 'Hi Danilo Vergara Jr,\nThis is to inform you that your application status is now: NEW.\nThank you for applying to HR1 MerchFlow.', '', 'new', 1, NULL, '2025-10-08 10:20:57'),
(4, 10, '', NULL, '2025-10-08 10:22:14', 1, 0, 'Application Update – Pending', 'Hi Juan Dela Cruz,\nThis is to inform you that your application status is now: PENDING.\nThank you for applying to HR1 MerchFlow.', '', 'pending', 1, NULL, '2025-10-08 10:22:14'),
(5, 10, '', NULL, '2025-10-08 15:48:40', 1, 0, 'Application Update – Rejected', 'Hi Juan Dela Cruz,\nThis is to inform you that your application status is now: REJECTED.\nThank you for applying to HR1 MerchFlow.', 'rejected', 'Rejected', 1, NULL, '2025-10-08 15:48:40'),
(6, 9, '', NULL, '2025-10-08 21:17:17', 1, 0, 'Application Update – Hired', 'Hi Danilo Vergara Jr,\n\nCongratulations! You are HIRED for the position of Order Processor.\n\nYour onboarding interview/briefing is scheduled on:\nOct 10, 2025, 05:16 AM (On-site)\n\nNotes: Be early\n\nPlease reply to confirm your availability. See you!', 'hired', 'Hired', 1, NULL, '2025-10-08 21:17:17'),
(7, 9, '', NULL, '2025-10-08 21:24:03', 1, 0, 'Application Update – Hired', 'Hi Danilo Vergara Jr,\nThis is to inform you that your application status is now: HIRED.\nThank you for applying to HR1 MerchFlow.', 'hired', 'hired', 1, NULL, '2025-10-08 21:24:03'),
(8, 9, '', NULL, '2025-10-09 09:00:29', 1, 0, 'Application Update – Update', 'Hi Danilo Vergara Jr,\n\nWe’d like to invite you to an interview for the Order Processor.\n\nWhen: 2025-10-09 17:00\nMode: On-site\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', 'new', 'update', 1, NULL, '2025-10-09 09:00:29'),
(9, 17, '', NULL, '2025-10-09 09:01:38', 1, 0, 'Application Update – Update', 'Hi John Cena,\n\nWe’d like to invite you to an interview for the Store Part Timer.\n\nWhen: 2025-10-09 20:01\nMode: Video\n\nBe ready\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', 'screening', 'update', 1, NULL, '2025-10-09 09:01:38'),
(10, 20, '', NULL, '2025-10-09 15:22:38', 1, 0, 'Application Update – Offered', 'Hi Hans San jose,\nThis is to inform you that your application status is now: OFFERED.\nThank you for applying to HR1 MerchFlow.', 'screening', 'Offered', 1, NULL, '2025-10-09 15:22:38'),
(11, 9, '', NULL, '2025-10-09 15:26:45', 1, 0, 'Application Update – Hired', 'Hi Danilo Vergara Jr,\nThis is to inform you that your application status is now: HIRED.\nThank you for applying to HR1 MerchFlow.', 'hired', 'Hired', 1, NULL, '2025-10-09 15:26:45'),
(12, 9, '', NULL, '2025-10-09 15:27:35', 1, 0, 'Application Update – Update', 'Hi Danilo Vergara Jr,\n\nWe’d like to invite you to an interview for the Order Processor.\n\nWhen: 2025-10-09 13:27\nMode: On-site\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', 'screening', 'update', 1, NULL, '2025-10-09 15:27:35'),
(13, 9, '', NULL, '2025-10-09 16:41:27', 1, 0, 'Application Update – Hired', 'Hi Danilo Vergara Jr,\n\nCongratulations! You are HIRED for the position of Order Processor.\n\nYour onboarding interview/briefing is scheduled on:\nOct 12, 2025, 12:41 AM (On-site)\n\nNotes: paranas\n\nPlease reply to confirm your availability. See you!', 'hired', 'Hired', 1, NULL, '2025-10-09 16:41:27'),
(14, 9, '', NULL, '2025-10-09 16:41:50', 1, 0, 'Application Update – Hired', 'Hi Danilo Vergara Jr,\n\nCongratulations! You are HIRED for the position of Order Processor.\n\nYour onboarding interview/briefing is scheduled on:\nOct 10, 2025, 12:41 AM (On-site)\n\nNotes: manggaha\n\nPlease reply to confirm your availability. See you!', 'hired', 'Hired', 1, NULL, '2025-10-09 16:41:50'),
(15, 17, '', NULL, '2025-10-09 16:43:07', 1, 0, 'Application Update – Hired', 'Hi John Cena,\n\nCongratulations! You are HIRED for the position of Store Part Timer.\n\nYour onboarding interview/briefing is scheduled on:\nOct 10, 2025, 12:42 AM (On-site)\n\nNotes: leeee\n\nPlease reply to confirm your availability. See you!', 'hired', 'Hired', 1, NULL, '2025-10-09 16:43:07'),
(16, 9, '', NULL, '2025-10-09 16:53:30', 1, 0, 'Welcome aboard – Order Processor', 'Hi Danilo Vergara Jr,\n\nCongratulations! You are HIRED for the position of Order Processor.\n\nYour onboarding interview/briefing is scheduled on:\nOct 10, 2025, 12:53 AM (On-site)\n\nPlease reply to confirm your availability. See you!', 'hired', 'Hired', 1, NULL, '2025-10-09 16:53:30'),
(17, 9, '', NULL, '2025-10-09 16:54:21', 1, 0, 'Welcome aboard – Order Processor', 'Hi Danilo Vergara Jr,\n\nCongratulations! You are HIRED for the position of Order Processor.\n\nYour onboarding interview/briefing is scheduled on:\nOct 10, 2025, 12:54 AM (On-site)\n\nPlease reply to confirm your availability. See you!', 'hired', 'Hired', 1, NULL, '2025-10-09 16:54:21'),
(18, 9, '', NULL, '2025-10-09 17:07:32', 1, 0, 'Interview Schedule – Order Processor', 'Hi Danilo Vergara Jr,\n\nThis is to confirm your interview schedule:\n\nDate/Time: Oct 10, 2025, 01:07 AM\nMode: On-site\n\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Interview', 1, NULL, '2025-10-09 17:07:32'),
(19, 24, '', NULL, '2025-10-10 06:16:22', 1, 0, 'Interview Schedule – Order Processor', 'Hi Raymart Castro,\n\nThis is to confirm your interview schedule:\n\nDate/Time: Oct 11, 2025, 02:16 PM\nMode: Phone\n\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Interview', 1, NULL, '2025-10-10 06:16:22'),
(20, 25, '', NULL, '2025-10-10 10:13:41', 1, 0, 'Interview Schedule – Cashier', 'Hi Josh Dendi,\n\nThis is to confirm your interview schedule:\n\nDate/Time: Oct 19, 2025, 06:13 PM\nMode: Video\n\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Interview', 1, NULL, '2025-10-10 10:13:41'),
(21, 25, '', NULL, '2025-10-10 10:14:43', 1, 0, 'Application Update – Update', 'Hi Josh Dendi,\n\nWe’d like to invite you to an interview for the Cashier.\n\nWhen: 2025-10-11 18:16\nMode: On-site\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', 'screening', 'update', 1, NULL, '2025-10-10 10:14:43'),
(22, 26, '', NULL, '2025-10-10 13:51:32', 1, 0, 'Interview Schedule – Order Processor', 'Hi Dan Vergara,\n\nThis is to confirm your interview schedule:\n\nDate/Time: Oct 13, 2025, 09:51 PM\nMode: On-site\n\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Interview', 1, NULL, '2025-10-10 13:51:32'),
(23, 26, '', NULL, '2025-10-10 13:53:56', 1, 0, 'Welcome aboard – Order Processor', 'Hi Dan Vergara,\nThis is to inform you that your application status is now: HIRED.\nThank you for applying to HR1 MerchFlow.', 'hired', 'Hired', 1, NULL, '2025-10-10 13:53:56'),
(24, 27, '', NULL, '2025-10-10 14:12:42', 1, 0, 'Application Update', 'Hi Kyle Varga,\nThis is to inform you that your application status is now: NEW.\nThank you for applying to HR1 MerchFlow.', 'new', 'new', 1, NULL, '2025-10-10 14:12:42'),
(25, 9, 'email', NULL, '2025-10-10 17:58:49', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=8d9974da92edc61d16753338aae0344c8eb93691429c5cf3\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-10 17:58:49'),
(26, 9, 'email', NULL, '2025-10-10 18:07:43', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=69fa94f1158c0e15593eae7b21375af4833dc2f57d43a21a\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-10 18:07:43'),
(27, 9, 'email', NULL, '2025-10-10 18:16:06', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=20cbe88a6c9a694e59401a3ff2f175555cf5272a690f2e24\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-10 18:16:06'),
(28, 9, 'email', NULL, '2025-10-10 18:23:34', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=80d5d628f72189cae24b82084750bd9765363c78897ebd9e\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-10 18:23:34'),
(29, 12, '', NULL, '2025-10-10 20:20:45', 1, 0, 'Application Update – Update', 'Hi Mark Juan,\n\nWe’d like to invite you to an interview for the Store Part Timer.\n\nWhen: 2025-10-10 04:20\nMode: On-site\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', 'screening', 'update', 1, NULL, '2025-10-10 20:20:45'),
(30, 23, 'email', NULL, '2025-10-10 20:51:02', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=90bde207740127c841623211eb827f63f7a490bd48eb8c37\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'rejected', 'Hired', 1, NULL, '2025-10-10 20:51:02'),
(31, 26, 'email', NULL, '2025-10-10 21:26:25', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=b12e44917faec48a9e14406dcaa10df128d6c7dc6b1d6a00\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-10 21:26:25'),
(32, 9, 'email', NULL, '2025-10-10 21:28:29', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=76de0a7aab0da31feb80b96ac8df78928c0fee1c978cc56a\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-10 21:28:29'),
(33, 14, 'email', NULL, '2025-10-11 00:38:24', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=2af9d53f510a53346c0e55ce49ae99fafe86a1b6f3a30b56\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'rejected', 'Hired', 1, NULL, '2025-10-11 00:38:24'),
(34, 27, 'email', NULL, '2025-10-11 06:17:56', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=ae5272727d590018067fa0d1530d861e9a8a9d5b07dec9b1\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 06:17:56'),
(35, 9, 'email', NULL, '2025-10-11 11:27:57', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=3f7208bbc8a5dbb5eea8615deedabfd9d7acdf0918b84e61\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 11:27:57'),
(36, 12, 'email', NULL, '2025-10-11 18:35:34', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=f532c15d04c0eebace6e3ed8744e0680a2c0150986ac370f\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', '', 'Hired', 1, NULL, '2025-10-11 18:35:34'),
(37, 9, 'email', NULL, '2025-10-11 18:35:54', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=545376cb855e9b25cbdd61e9928f5aca0f7f1e880f0238e9\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 18:35:54'),
(38, 29, 'email', NULL, '2025-10-11 20:17:06', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=0bf1f3807325024e75af90e32e303325462d5ae277d9c459\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'new', 'Hired', 1, NULL, '2025-10-11 20:17:06'),
(39, 29, 'email', NULL, '2025-10-11 20:21:52', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=f2f376bcfd809783f4a670d328af810e48ba2475ba7caf08\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 20:21:52'),
(40, 29, 'email', NULL, '2025-10-11 20:26:54', 0, 0, 'Application Update - Hired', 'Hi Lester Santos,\n  This is to inform you that your application status is now: HIRED.\n  Thank you for applying to HR1 MerchFlow.', 'hired', 'hired', 1, NULL, '2025-10-11 20:26:54'),
(41, 29, 'email', NULL, '2025-10-11 20:27:36', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=707d0ee28ad6ed16cb7066e172ee54c09ac4f5d2afd8e157\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 20:27:36'),
(42, 29, 'email', NULL, '2025-10-11 20:40:49', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=f88e148e8623f93aca3fe71a4cffd1068e1850b9d76fe9a7\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 20:40:49'),
(43, 29, 'email', NULL, '2025-10-11 20:44:07', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=13df8d29bf0202b86bb81471d73c2141ac2468fc8298f9a2\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 20:44:07'),
(44, 29, 'email', NULL, '2025-10-11 20:59:15', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=fd1cee8750bdb319efe1dbe6b858d8153c3843c50dadb619\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 20:59:15'),
(45, 29, 'email', NULL, '2025-10-11 21:04:15', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=779d4b649910c50713acf8ce245d1b1cb91566ee6f6b190d\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:04:15'),
(46, 29, 'email', NULL, '2025-10-11 21:06:12', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=7144e967b87f8db3445c5248ebc87fb8e4b9f8e651c8e2d5\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:06:12'),
(47, 30, 'email', NULL, '2025-10-11 21:07:39', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=a8a59248250a91a96f98bb92757019a99805d80daad82c86\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'new', 'Hired', 1, NULL, '2025-10-11 21:07:39'),
(48, 30, 'email', NULL, '2025-10-11 21:08:57', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=1759d4caf2294fcb6091f020f29db9accf1cede736dbe0bd\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:08:57'),
(49, 30, 'email', NULL, '2025-10-11 21:12:21', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=21238f61c6357e605f83abae94833757269f8dc9e11f796b\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:12:21'),
(50, 31, 'email', NULL, '2025-10-11 21:32:05', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=e6eb4d8eef431ca37e67d6476f0123ec4925400338866b7e\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'new', 'Hired', 1, NULL, '2025-10-11 21:32:05'),
(51, 31, 'email', NULL, '2025-10-11 21:36:28', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=9a2447cf8d9085adb07f94534902bd8364100c9b52495b80\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:36:28'),
(52, 31, 'email', NULL, '2025-10-11 21:36:32', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=b55435d34de248b64edf39b2b029dbf9d4bc873031dbea31\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:36:32'),
(53, 31, 'email', NULL, '2025-10-11 21:40:16', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=5a167be739822539e55b0cf043e6e499e1d0d2a417b075de\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 21:40:16'),
(54, 9, 'email', NULL, '2025-10-11 22:19:39', 0, 0, 'Application Update - Hired', 'Hi Danilo Vergara Jr,\n  This is to inform you that your application status is now: HIRED.\n  Thank you for applying to HR1 MerchFlow.', 'hired', 'hired', 1, NULL, '2025-10-11 22:19:39'),
(55, 31, 'email', NULL, '2025-10-11 22:23:45', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=4f1a9611ddd050c49fbf5be08ccc415544470c158ec380c5\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 22:23:45'),
(56, 31, 'email', NULL, '2025-10-11 22:26:11', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=b0680ba4ea5c44909071a3b228d8c8b5bd3d20cda7658424\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-11 22:26:11'),
(57, 32, 'email', NULL, '2025-10-11 22:33:24', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=92a4aa6017b228d9a7ee97b5de755fe51e88f456f383f1ff\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'new', 'Hired', 1, NULL, '2025-10-11 22:33:24'),
(58, 9, '', NULL, '2025-10-11 22:35:20', 1, 0, 'Application Update – Update', 'Hi Danilo Vergara Jr,\n\nWe’d like to invite you to an interview for the Order Processor.\n\nWhen: (to be confirmed)\nMode: On-site\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', '', 'update', 1, NULL, '2025-10-11 22:35:20'),
(59, 9, 'email', NULL, '2025-10-14 12:10:51', 0, 0, 'Interview Schedule Confirmation', 'Hi Applicant,\n\nThis is to confirm your interview schedule:\n\nDate/Time: 2025-10-14 20:11\nMode: On-site\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Screening', 1, NULL, '2025-10-14 12:10:51'),
(60, 9, 'email', NULL, '2025-10-14 12:12:23', 0, 0, 'Interview Schedule Confirmation', 'Hi Applicant,\n\nThis is to confirm your interview schedule:\n\nDate/Time: 2025-10-22 20:12\nMode: Phone\nNotes: Lucas\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Screening', 1, NULL, '2025-10-14 12:12:23'),
(61, 9, 'email', NULL, '2025-10-14 12:15:25', 0, 0, 'Interview Schedule Confirmation', 'Hi Applicant,\n\nThis is to confirm your interview schedule:\n\nDate/Time: 2025-10-14 20:15\nMode: Video\nNotes: Jayson\n\nPlease reply to confirm your availability. Thank you.', 'screening', 'Screening', 1, NULL, '2025-10-14 12:15:25'),
(62, 9, '', NULL, '2025-10-14 12:16:06', 1, 0, 'Application Update – Update', 'Hi Danilo Vergara Jr,\n\nWe’d like to invite you to an interview for the Order Processor.\n\nWhen: 2025-10-14 20:15\nMode: Video\n\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow', 'screening', 'update', 1, NULL, '2025-10-14 12:16:06'),
(63, 39, '', NULL, '2025-10-14 13:45:52', 1, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi Lancelot Maliper,<br><br>\r\n           Congratulations! Your application status is now <b>Hired</b>.<br><br>\r\n           Please complete your new-hire requirements using this secure link:<br>\r\n           <a href=\"http://localhost/hr1_Merchflow/newhire.php?t=6d29cd91cfbff753daa637a409762e5d\">http://localhost/hr1_Merchflow/newhire.php?t=6d29cd91cfbff753daa637a409762e5d</a><br><br>\r\n           This link will expire in 7 days.<br><br>\r\n           Thank you,<br>HR1 Nextgenmms – HR Department', 'hired', 'onboarding', 1, '', '2025-10-14 13:45:52'),
(64, 40, '', NULL, '2025-10-14 15:34:05', 1, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi Cassandra Lopez,<br><br>\r\n           Congratulations! Your application status is now <b>Hired</b>.<br><br>\r\n           Please complete your new-hire requirements using this secure link:<br>\r\n           <a href=\"http://localhost/hr1_Merchflow/newhire.php?t=697eeb0d408a45cb2c45aaf2a7d258fe\">http://localhost/hr1_Merchflow/newhire.php?t=697eeb0d408a45cb2c45aaf2a7d258fe</a><br><br>\r\n           This link will expire in 7 days.<br><br>\r\n           Thank you,<br>HR1 Nextgenmms – HR Department', 'hired', 'onboarding', 1, '', '2025-10-14 15:34:05'),
(65, 39, 'email', NULL, '2025-10-14 15:39:39', 0, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi ,\n\nCongratulations! Your application status is now Hired.\n\nPlease complete your new-hire requirements using this secure link:\nhttp://localhost/hr1_Merchflow/api/../newhire.php?t=528da4383e5b9d62349c2f1a7260e6190867a232be825cf3\n\nThis link will expire in 7 days.\n\nThank you,\nHR1 Nextgenmms – HR Department', 'hired', 'Hired', 1, NULL, '2025-10-14 15:39:39'),
(66, 41, '', NULL, '2025-10-14 17:32:33', 1, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi Klein De Jesus,<br><br>\r\n           Congratulations! Your application status is now <b>Hired</b>.<br><br>\r\n           Please complete your new-hire requirements using this secure link:<br>\r\n           <a href=\"http://localhost/hr1_Merchflow/newhire.php?t=32ed8b1ece4caa45e60dfb0a9afe8891\">http://localhost/hr1_Merchflow/newhire.php?t=32ed8b1ece4caa45e60dfb0a9afe8891</a><br><br>\r\n           This link will expire in 7 days.<br><br>\r\n           Thank you,<br>HR1 Nextgenmms – HR Department', 'hired', 'onboarding', 1, '', '2025-10-14 17:32:33'),
(67, 19, '', NULL, '2025-10-17 15:00:26', 1, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi Jake Luigi,<br><br>\r\n           Congratulations! Your application status is now <b>Hired</b>.<br><br>\r\n           Please complete your new-hire requirements using this secure link:<br>\r\n           <a href=\"http://localhost/hr1_Merchflow/newhire.php?t=aaa554f867edf6bbcbbe34e8d1cf5a6f\">http://localhost/hr1_Merchflow/newhire.php?t=aaa554f867edf6bbcbbe34e8d1cf5a6f</a><br><br>\r\n           This link will expire in 7 days.<br><br>\r\n           Thank you,<br>HR1 Nextgenmms – HR Department', 'hired', 'onboarding', 1, '', '2025-10-17 15:00:26'),
(68, 42, '', NULL, '2025-10-17 18:54:11', 1, 0, 'Welcome to HR1 Nextgenmms – Onboarding', 'Hi Kyle Kuzma,<br><br>\r\n           Congratulations! Your application status is now <b>Hired</b>.<br><br>\r\n           Please complete your new-hire requirements using this secure link:<br>\r\n           <a href=\"http://localhost/hr1_Merchflow/newhire.php?t=b05fc23680c715e0c1d3ee4229c2555d\">http://localhost/hr1_Merchflow/newhire.php?t=b05fc23680c715e0c1d3ee4229c2555d</a><br><br>\r\n           This link will expire in 7 days.<br><br>\r\n           Thank you,<br>HR1 Nextgenmms – HR Department', 'hired', 'onboarding', 1, '', '2025-10-17 18:54:11');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `offer_date` date DEFAULT NULL,
  `status` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onboarding_plans`
--

CREATE TABLE `onboarding_plans` (
  `id` int(11) NOT NULL,
  `hire_name` varchar(120) NOT NULL,
  `role` varchar(80) DEFAULT NULL,
  `site` varchar(120) NOT NULL DEFAULT 'Banawe',
  `start_date` date DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `plan_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`plan_json`)),
  `status` enum('Pending','In Progress','Completed','On Hold') NOT NULL DEFAULT 'Pending',
  `progress` int(11) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `finalized` tinyint(1) NOT NULL DEFAULT 0,
  `finalized_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onboarding_plans`
--

INSERT INTO `onboarding_plans` (`id`, `hire_name`, `role`, `site`, `start_date`, `employee_id`, `role_id`, `plan_json`, `status`, `progress`, `completed_at`, `finalized`, `finalized_at`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 'Ana Villanueva', 'Accountant', 'Cabuyao', '2025-10-04', NULL, NULL, NULL, 'Pending', 0, '2025-10-06 00:10:19', 0, NULL, 1, '2025-10-04 18:53:22', '2025-10-10 13:40:56'),
(30, '', 'Store Part Timer', 'Banawe', '2025-10-08', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-08 18:18:59', '2025-10-08 18:18:59'),
(32, 'Mark Juan', 'Store Part Timer', 'Banawe', '2025-10-11', NULL, NULL, NULL, 'Completed', 100, NULL, 0, NULL, NULL, '2025-10-08 18:57:13', '2025-10-09 11:57:04'),
(33, 'Alex Santos', 'Store Part Timer', 'Banawe', '2025-10-13', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-08 19:28:57', '2025-10-10 11:47:11'),
(34, 'James Villanueva', 'Store Part Timer', 'Banawe', '2025-10-10', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-08 20:30:34', '2025-10-08 20:30:34'),
(35, 'Mike  Reyes', 'Inventory Clerk / Stockman', 'Banawe', '2025-10-12', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-09 05:15:30', '2025-10-10 12:04:58'),
(36, 'Mellisa Co', 'Store Part Timer', 'Banawe', '2025-10-11', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-09 09:22:46', '2025-10-09 09:22:46'),
(38, 'Kate Velasco', 'Cashier', 'Banawe', '2025-10-11', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-09 20:38:12', '2025-10-09 20:38:12'),
(41, 'Angelica Dorado', 'Deputy Store Manager', 'Banawe', '2025-10-09', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-09 21:07:54', '2025-10-09 21:07:54'),
(44, 'Josh Dendi', 'Cashier', 'Banawe', '2025-10-13', NULL, NULL, NULL, 'Completed', 100, NULL, 0, NULL, NULL, '2025-10-10 10:15:30', '2025-10-10 12:37:42'),
(45, 'Raymart Castro', 'Order Processor', 'Banawe', '2025-10-10', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-10 13:31:32', '2025-10-10 13:31:32'),
(46, 'Danilo Vergara Jr', 'Order Processor', 'Banawe', '2025-10-10', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-10 13:43:13', '2025-10-10 13:43:13'),
(47, 'Dan Vergara', 'Order Processor', 'Banawe', '2025-10-13', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-10 13:53:45', '2025-10-11 19:28:41'),
(48, 'Kyle Varga', 'Store Manager', 'Banawe', '2025-10-11', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-10 14:16:23', '2025-10-10 14:16:23'),
(49, 'Robert Gonzales', 'Order Processor', 'Banawe', '2025-10-11', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-10 16:43:35', '2025-10-10 16:43:35'),
(50, 'Juan De Jesus', 'Merchandiser / Promodiser', 'Banawe', '2025-10-10', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-10 20:51:03', '2025-10-10 20:51:03'),
(52, 'Jake Miranda', 'Merchandiser / Promodiser', '', '2025-10-13', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-11 22:33:52', '2025-10-11 22:33:52'),
(53, 'Cassandra Lopez', 'Inventory Clerk / Stockman', 'Banawe', '2025-10-14', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-14 09:34:01', '2025-10-14 15:34:01'),
(54, 'Klein De Jesus', 'Inventory Clerk / Stockman', 'Banawe', '2025-10-14', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-14 11:32:30', '2025-10-14 17:32:30'),
(55, 'Jake Luigi', 'Store Part Timer', 'Banawe', '2025-10-17', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-17 09:00:22', '2025-10-17 15:00:22'),
(56, 'Kyle Kuzma', 'Inventory Clerk / Stockman', 'Banawe', '2025-10-17', NULL, NULL, NULL, 'Pending', 0, NULL, 0, NULL, NULL, '2025-10-17 12:54:07', '2025-10-17 18:54:07');

-- --------------------------------------------------------

--
-- Table structure for table `onboarding_tasks`
--

CREATE TABLE `onboarding_tasks` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `title` varchar(160) NOT NULL,
  `owner` enum('HR','Employee','IT','Manager') NOT NULL DEFAULT 'HR',
  `due_date` date DEFAULT NULL,
  `assignee` enum('employee','manager','it','hr') DEFAULT 'employee',
  `status` enum('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `order_index` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onboarding_tasks`
--

INSERT INTO `onboarding_tasks` (`id`, `plan_id`, `title`, `owner`, `due_date`, `assignee`, `status`, `remarks`, `completed_at`, `order_index`) VALUES
(23, 32, 'Arrange Stock', 'Employee', '2025-10-07', 'employee', 'Completed', NULL, NULL, 0),
(25, 32, 'Early Shift', 'Employee', '2025-10-06', 'employee', 'Completed', NULL, NULL, 0),
(26, 32, 'Clean Storage', 'Employee', '2025-10-09', 'employee', 'Completed', NULL, NULL, 0),
(94, 44, 'Understand attendance & payroll cutoffs', 'Employee', '2025-10-13', 'employee', 'Completed', NULL, NULL, 0),
(95, 44, '30-Day check-in', 'Employee', '2025-11-11', 'employee', 'Completed', NULL, NULL, 0),
(96, 44, '90-Day probationary evaluation', 'Employee', '2026-01-10', 'employee', 'Completed', NULL, NULL, 0),
(97, 44, 'POS & cash handling training', 'Employee', '2025-10-13', 'employee', 'Completed', NULL, NULL, 0),
(98, 44, 'End-of-day balancing practice', 'Employee', '2025-10-14', 'employee', 'Completed', NULL, NULL, 0),
(99, 44, 'Refund/void/exchange policy', 'Employee', '2025-10-15', 'employee', 'Completed', NULL, NULL, 0),
(444, 35, 'Understand attendance & payroll cutoffs', 'Employee', '2025-10-10', 'employee', 'Pending', NULL, NULL, 0),
(445, 35, '30-Day check-in', 'Employee', '2025-10-11', 'employee', 'Pending', NULL, NULL, 0),
(446, 35, '90-Day probationary evaluation', 'Employee', '2026-01-09', 'employee', 'Pending', NULL, NULL, 0),
(447, 35, 'Warehouse safety orientation', 'Employee', '2025-10-12', 'employee', 'Pending', NULL, NULL, 0),
(448, 35, 'Receiving & stock transfer SOP', 'Employee', '2025-11-04', 'employee', 'Pending', NULL, NULL, 0),
(449, 35, 'Cycle count procedure', 'Employee', '2025-10-14', 'employee', 'Pending', NULL, NULL, 0),
(549, 45, 'Submit pre-employment requirements', 'Employee', '2025-10-07', 'employee', 'Pending', NULL, NULL, 0),
(550, 45, 'Sign contract & NDA', 'Employee', '2025-10-09', 'employee', 'Pending', NULL, NULL, 0),
(551, 45, 'Attend HR orientation (policies & house rules)', 'Employee', '2025-10-09', 'employee', 'Pending', NULL, NULL, 0),
(552, 45, 'Enroll to biometric/timekeeping', 'Employee', '2025-10-09', 'employee', 'Pending', NULL, NULL, 0),
(553, 45, 'Safety & emergency briefing', 'Employee', '2025-10-09', 'employee', 'Pending', NULL, NULL, 0),
(554, 45, 'Store tour & meet the team', 'Employee', '2025-10-10', 'employee', 'Pending', NULL, NULL, 0),
(555, 45, 'Understand attendance & payroll cutoffs', 'Employee', '2025-10-10', 'employee', 'Pending', NULL, NULL, 0),
(556, 45, '30-Day check-in', 'Employee', '2025-11-08', 'employee', 'Pending', NULL, NULL, 0),
(557, 45, '90-Day probationary evaluation', 'Employee', '2026-01-07', 'employee', 'Pending', NULL, NULL, 0),
(558, 45, 'Order picking/packing flow', 'Employee', '2025-10-10', 'employee', 'Pending', NULL, NULL, 0),
(559, 45, 'Dispatch cutoff & SLA', 'Employee', '2025-10-11', 'employee', 'Pending', NULL, NULL, 0),
(560, 45, 'Returns/exceptions handling', 'Employee', '2025-10-12', 'employee', 'Pending', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `label` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recognitions`
--

CREATE TABLE `recognitions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `badge` enum('helpful','teamwork','customer_hero','innovation','excellence') NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recognitions`
--

INSERT INTO `recognitions` (`id`, `employee_id`, `from_user_id`, `badge`, `note`, `created_at`) VALUES
(1, 1, 2, 'teamwork', 'Assisted in store restocking during rush hour.', '2025-09-20 17:56:19');

-- --------------------------------------------------------

--
-- Table structure for table `recruitments`
--

CREATE TABLE `recruitments` (
  `id` int(11) NOT NULL,
  `req_no` varchar(20) NOT NULL,
  `site` varchar(100) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `applicant_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `stage` enum('screen','interview','offer','accepted','rejected') DEFAULT 'screen',
  `needed` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recruitments`
--

INSERT INTO `recruitments` (`id`, `req_no`, `site`, `role`, `applicant_id`, `role_id`, `stage`, `needed`, `notes`, `score`, `created_at`) VALUES
(13, 'REQ-2025-009', '', 'Cashier', 0, 0, 'interview', 1, NULL, NULL, '2025-10-09 02:59:45'),
(15, 'REQ-2025-010', '', 'Store Manager', 0, 0, 'accepted', 56, NULL, NULL, '2025-10-09 06:09:55'),
(17, 'REQ-2025-012', '', 'Deputy Store Manager', 0, 0, 'interview', 10, NULL, NULL, '2025-10-10 14:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL,
  `req_no` varchar(32) NOT NULL,
  `role` varchar(120) NOT NULL,
  `site` varchar(120) NOT NULL,
  `stage` enum('Screening','Interview','Offer','Hired','Closed') DEFAULT 'Screening',
  `needed` int(11) NOT NULL DEFAULT 1,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `req_no`, `role`, `site`, `stage`, `needed`, `is_open`, `created_at`) VALUES
(1, 'REQ-2025-001', 'Customer Success Rep', 'O!Save – Calamba', 'Screening', 2, 1, '2025-10-06 02:50:50'),
(2, 'REQ-2025-002', 'Field Ops Associate', 'DC – Laguna', 'Interview', 1, 1, '2025-10-06 02:50:50');

-- --------------------------------------------------------

--
-- Table structure for table `review_tasks`
--

CREATE TABLE `review_tasks` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Pending','Done') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_tasks`
--

INSERT INTO `review_tasks` (`id`, `employee_id`, `plan_id`, `title`, `due_date`, `status`, `created_at`) VALUES
(1, 0, 12, '30-Day Check-in', '2025-11-04', 'Pending', '2025-10-06 00:15:12'),
(2, 0, 12, '60-Day Check-in', '2025-12-04', 'Pending', '2025-10-06 00:15:12'),
(3, 0, 12, '90-Day Check-in', '2026-01-03', 'Pending', '2025-10-06 00:15:12'),
(4, 0, 13, '30-Day Check-in', '2025-11-04', 'Pending', '2025-10-06 00:15:18'),
(5, 0, 13, '60-Day Check-in', '2025-12-04', 'Pending', '2025-10-06 00:15:18'),
(6, 0, 13, '90-Day Check-in', '2026-01-03', 'Pending', '2025-10-06 00:15:18'),
(7, 0, 9, '30-Day Check-in', '2025-11-04', 'Pending', '2025-10-06 00:16:10'),
(8, 0, 9, '60-Day Check-in', '2025-12-04', 'Pending', '2025-10-06 00:16:10'),
(9, 0, 9, '90-Day Check-in', '2026-01-03', 'Pending', '2025-10-06 00:16:10');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `department` varchar(120) DEFAULT NULL,
  `competencies_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`competencies_json`)),
  `kpi_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kpi_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `title`, `department`, `competencies_json`, `kpi_json`, `created_at`) VALUES
(1, 'Sales Associate', 'Store', '{\"must\":[\"customer handling\",\"POS\"]}', '{\"sales_target\":100000,\"attendance\":95}', '2025-09-20 17:55:31'),
(2, 'Inventory Clerk', 'Warehouse', '{\"must\":[\"inventory\",\"excel\"]}', '{\"accuracy\":98,\"attendance\":95}', '2025-09-20 17:55:31');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `role_id`) VALUES
(2, 'HR Manager', 'hr@osave.com', '4435a4e88e984616a91c109524caf662', 'manager', 1, '2025-09-20 17:55:18', NULL),
(3, 'Employee One', 'emp1@osave.com', '0314ee502c6f4e284128ad14e84e37d5', 'employee', 1, '2025-09-20 17:55:18', NULL),
(5, 'Admin', 'admin@osave.com', '$2y$10$jcy8oYiQYXuC0fqbBf.0yuD6i2JxHoRs.pZyVM2Ymd730/kHm1606', 'admin', 1, '2025-09-22 17:03:16', NULL),
(18, 'Dani Vergara', 'vergara.136541132229@depedqc.ph', '$2y$10$H73fCAumnJFBXibKino7wuxH48Bo.9J6CONZHBoFEZUmcaGsY1syK', 'admin', 1, '2025-10-17 17:04:04', NULL),
(19, 'Dan Vergara', 'danv66215@gmail.com', '$2y$10$7Lc8y2W78TMJWCzKnj0g2u4zJN8bpC5rG/SomlN9pasFeXSPlGAmm', 'employee', 1, '2025-10-17 17:11:46', NULL),
(20, 'Dans Vergara', 'danilovergarajr610@gmail.com', '$2y$10$Yyw3DnhW8PXqZ70PFHySLu.QBrtHW.ETlPa03PUnAbUB0Is9UBs8q', 'admin', 1, '2025-10-17 17:52:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_otps`
--

CREATE TABLE `user_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_code` (`emp_code`),
  ADD UNIQUE KEY `uniq_applicant` (`applicant_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_source_plan` (`source_plan_id`),
  ADD KEY `fk_emp_role` (`role_id`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_eval_employee` (`employee_id`);

--
-- Indexes for table `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eval_forms`
--
ALTER TABLE `eval_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ev_app` (`applicant_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_iv_app` (`applicant_id`);

--
-- Indexes for table `interview_batches`
--
ALTER TABLE `interview_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_req` (`req_no`),
  ADD KEY `idx_date` (`sched_date`);

--
-- Indexes for table `newhire_uploads`
--
ALTER TABLE `newhire_uploads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_app_key` (`applicant_id`,`file_key`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_applicant_id` (`applicant_id`),
  ADD KEY `idx_status_to` (`status_to`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `onboarding_plans`
--
ALTER TABLE `onboarding_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_onb_emp` (`employee_id`),
  ADD KEY `ix_plans_start` (`start_date`),
  ADD KEY `ix_plans_status` (`status`),
  ADD KEY `ix_plans_name_role` (`hire_name`,`role`);

--
-- Indexes for table `onboarding_tasks`
--
ALTER TABLE `onboarding_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_plan` (`plan_id`),
  ADD KEY `idx_tasks_due_status` (`status`,`due_date`),
  ADD KEY `ix_tasks_plan` (`plan_id`),
  ADD KEY `ix_tasks_plan_stat` (`plan_id`,`status`),
  ADD KEY `ix_tasks_due_stat` (`due_date`,`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `recognitions`
--
ALTER TABLE `recognitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `from_user_id` (`from_user_id`);

--
-- Indexes for table `recruitments`
--
ALTER TABLE `recruitments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_req_no` (`req_no`),
  ADD KEY `applicant_id` (`applicant_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `req_no` (`req_no`);

--
-- Indexes for table `review_tasks`
--
ALTER TABLE `review_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uniq_users_email` (`email`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- Indexes for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1774;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `evaluation_forms`
--
ALTER TABLE `evaluation_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eval_forms`
--
ALTER TABLE `eval_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `interview_batches`
--
ALTER TABLE `interview_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `newhire_uploads`
--
ALTER TABLE `newhire_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `onboarding_plans`
--
ALTER TABLE `onboarding_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `onboarding_tasks`
--
ALTER TABLE `onboarding_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=610;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recognitions`
--
ALTER TABLE `recognitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `recruitments`
--
ALTER TABLE `recruitments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `review_tasks`
--
ALTER TABLE `review_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`),
  ADD CONSTRAINT `fk_emp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `fk_eval_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eval_forms`
--
ALTER TABLE `eval_forms`
  ADD CONSTRAINT `fk_ev_app` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `fk_iv_app` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `newhire_uploads`
--
ALTER TABLE `newhire_uploads`
  ADD CONSTRAINT `fk_newhire_app` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `onboarding_plans`
--
ALTER TABLE `onboarding_plans`
  ADD CONSTRAINT `fk_onb_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `onboarding_plans_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `onboarding_tasks`
--
ALTER TABLE `onboarding_tasks`
  ADD CONSTRAINT `onboarding_tasks_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `onboarding_plans` (`id`);

--
-- Constraints for table `recognitions`
--
ALTER TABLE `recognitions`
  ADD CONSTRAINT `recognitions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `recognitions_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
