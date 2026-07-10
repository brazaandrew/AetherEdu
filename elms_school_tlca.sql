-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2026 at 03:42 AM
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
-- Database: `elms_school_tlca`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_score` int(11) NOT NULL DEFAULT 100,
  `deadline` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_submissions`
--

CREATE TABLE `activity_submissions` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `score` int(11) DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_submissions`
--

INSERT INTO `activity_submissions` (`id`, `activity_id`, `student_id`, `answer_text`, `file_path`, `submitted_at`, `score`, `graded_by`, `graded_at`) VALUES
(1, 3, 13, '1. Three Safety Hazards at Home:\r\n\r\n- Loose electrical wires\r\n- Wet or slippery floors\r\n- Unsecured heavy furniture (like cabinets or shelves)\r\n\r\n2. Why Each Hazard is Dangerous:\r\n\r\n- Loose electrical wires – Can cause electric shock or fire if touched or short-circuited.\r\n- Wet or slippery floors – Can cause people to slip and fall, leading to injuries like\r\nbruises or broken bones.\r\n- Unsecured heavy furniture – May tip over and hurt someone, especially children, causing serious injury.\r\n\r\n3. Suggested Solutions:\r\n\r\n- Loose electrical wires – Fix or cover the wires properly and avoid overloading electrical outlets.\r\n- Wet or slippery floors – Wipe spills immediately and use non-slip mats in areas prone to getting wet.\r\n- Unsecured heavy furniture – Anchor furniture to the wall or use safety straps to prevent tipping.', NULL, '2026-02-12 08:42:01', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `target_type`, `target_id`, `details`, `timestamp`) VALUES
(1, 1, 'login', 'user', 1, '[]', '2025-12-20 00:53:47'),
(2, 1, 'logout', 'user', 1, '[]', '2025-12-20 00:54:06'),
(3, 1, 'login', 'user', 1, '[]', '2025-12-20 00:54:20'),
(4, 1, 'logout', 'user', 1, '[]', '2025-12-20 00:54:44'),
(5, 3, 'login', 'user', 3, '[]', '2025-12-20 00:54:58'),
(6, 3, 'logout', 'user', 3, '[]', '2025-12-20 01:02:23'),
(7, 1, 'login', 'user', 1, '[]', '2025-12-20 01:02:49'),
(8, 1, 'login', 'user', 1, '[]', '2025-12-20 01:08:26'),
(9, 1, 'assign', 'folder_teacher', 1, '{\"subject_id\":1,\"teacher_empidno\":\"T002\"}', '2025-12-20 01:53:47'),
(10, 1, 'create', 'activity', 1, '{\"subjectId\":1,\"title\":\"dsfsfsd\"}', '2025-12-20 01:54:24'),
(11, 1, 'delete', 'activity', 1, '[]', '2025-12-20 01:54:29'),
(12, 1, 'register_user', 'user', 6, '[]', '2025-12-20 01:56:31'),
(13, 1, 'create', 'user', 6, '{\"empidno\":\"S003\",\"name\":\"john Doe\",\"email\":\"doe@tlca.com\",\"role\":\"student\",\"gradeLevel\":\"Grade 7\"}', '2025-12-20 01:56:31'),
(14, 1, 'logout', 'user', 1, '[]', '2025-12-20 01:57:37'),
(15, 6, 'login', 'user', 6, '[]', '2025-12-20 01:57:50'),
(16, 6, 'enroll', 'enrollment', 1, '{\"subject_id\":1}', '2025-12-20 01:58:00'),
(17, 6, 'logout', 'user', 6, '[]', '2025-12-20 02:07:54'),
(18, 6, 'login', 'user', 6, '[]', '2025-12-20 02:12:03'),
(19, 6, 'logout', 'user', 6, '[]', '2025-12-20 02:12:11'),
(20, 1, 'login', 'user', 1, '[]', '2025-12-20 02:12:15'),
(21, 1, 'generate_key', 'subject', 1, '{\"enrollment_key\":\"9A5978E5\"}', '2025-12-20 02:28:19'),
(22, 1, 'register_user', 'user', 8, '[]', '2025-12-20 02:29:57'),
(23, 1, 'create', 'user', 8, '{\"empidno\":\"T003\",\"name\":\"Leovy Mae Khey\",\"email\":\"Leovy@tlca.com\",\"role\":\"teacher\",\"gradeLevel\":\"\"}', '2025-12-20 02:29:57'),
(24, 1, 'logout', 'user', 1, '[]', '2025-12-20 03:29:20'),
(25, 1, 'login', 'user', 1, '[]', '2025-12-20 04:49:22'),
(26, 1, 'register_user', 'user', 9, '[]', '2025-12-20 04:50:19'),
(27, 1, 'create', 'user', 9, '{\"empidno\":\"T004\",\"name\":\"Jun Xavier Gicana\",\"email\":\"xavier@tlca.com\",\"role\":\"teacher\",\"gradeLevel\":\"\"}', '2025-12-20 04:50:19'),
(28, 1, 'logout', 'user', 1, '[]', '2025-12-20 04:59:07'),
(29, 1, 'login', 'user', 1, '[]', '2025-12-20 17:47:19'),
(30, 1, 'register_user', 'user', 10, '[]', '2025-12-20 17:47:58'),
(31, 1, 'create', 'user', 10, '{\"empidno\":\"IT001\",\"name\":\"allen\",\"email\":\"allen@tlca.com\",\"role\":\"it_personnel\",\"gradeLevel\":\"\"}', '2025-12-20 17:47:58'),
(32, 1, 'logout', 'user', 1, '[]', '2025-12-20 17:48:07'),
(33, 10, 'login', 'user', 10, '[]', '2025-12-20 17:48:11'),
(34, 10, 'logout', 'user', 10, '[]', '2025-12-20 17:48:50'),
(35, 10, 'login', 'user', 10, '[]', '2025-12-20 17:48:54'),
(36, 10, 'logout', 'user', 10, '[]', '2025-12-20 18:01:16'),
(37, 8, 'login', 'user', 8, '[]', '2025-12-20 18:01:20'),
(38, 8, 'logout', 'user', 8, '[]', '2025-12-20 18:07:56'),
(39, 10, 'login', 'user', 10, '[]', '2025-12-20 18:08:04'),
(40, 10, 'logout', 'user', 10, '[]', '2025-12-20 18:08:21'),
(41, 1, 'login', 'user', 1, '[]', '2025-12-20 18:08:28'),
(42, 1, 'assign', 'folder_teacher', 2, '{\"subject_id\":2,\"teacher_empidno\":\"T004\"}', '2025-12-20 18:08:47'),
(43, 1, 'logout', 'user', 1, '[]', '2025-12-20 18:09:00'),
(44, 8, 'login', 'user', 8, '[]', '2025-12-20 18:09:04'),
(45, 8, 'logout', 'user', 8, '[]', '2025-12-20 18:11:21'),
(46, 1, 'login', 'user', 1, '[]', '2025-12-20 18:11:25'),
(47, 1, 'unassign', 'folder_teacher', 2, '{\"subject_id\":2}', '2025-12-20 18:11:31'),
(48, 1, 'assign', 'folder_teacher', 3, '{\"subject_id\":2,\"teacher_empidno\":\"T003\"}', '2025-12-20 18:11:36'),
(49, 1, 'logout', 'user', 1, '[]', '2025-12-20 18:11:39'),
(50, 8, 'login', 'user', 8, '[]', '2025-12-20 18:11:42'),
(51, 1, 'logout', 'user', 1, '[]', '2025-12-20 21:37:45'),
(52, 8, 'logout', 'user', 8, '[]', '2025-12-21 01:42:02'),
(53, 1, 'login', 'user', 1, '[]', '2025-12-21 01:42:07'),
(54, 1, 'register_user', 'user', 11, '[]', '2025-12-21 01:42:39'),
(55, 1, 'create', 'user', 11, '{\"empidno\":\"T005\",\"name\":\"liezl\",\"email\":\"liezl@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\"}', '2025-12-21 01:42:39'),
(56, 1, 'logout', 'user', 1, '[]', '2025-12-21 21:35:17'),
(57, 1, 'login', 'user', 1, '[]', '2025-12-22 05:09:48'),
(58, 8, 'login', 'user', 8, '[]', '2025-12-28 00:53:46'),
(59, 1, 'login', 'user', 1, '[]', '2025-12-28 00:54:08'),
(60, 8, 'create', 'activity', 2, '{\"subjectId\":2,\"title\":\"hhhhj\"}', '2025-12-28 00:55:30'),
(61, 1, 'register_user', 'user', 13, '[]', '2025-12-28 00:58:01'),
(62, 1, 'create', 'user', 13, '{\"empidno\":\"S004\",\"name\":\"Jiana\",\"email\":\"jiana@tlca.com\",\"role\":\"student\",\"gradeLevel\":\"Grade 7\"}', '2025-12-28 00:58:01'),
(63, 1, 'logout', 'user', 1, '[]', '2025-12-28 00:58:26'),
(64, 13, 'login', 'user', 13, '[]', '2025-12-28 00:58:37'),
(65, 13, 'enroll', 'enrollment', 2, '{\"subject_id\":2}', '2025-12-28 00:59:41'),
(66, 8, 'login', 'user', 8, '[]', '2025-12-28 03:22:40'),
(67, 1, 'login', 'user', 1, '[]', '2025-12-28 03:26:08'),
(68, 8, 'logout', 'user', 8, '[]', '2025-12-28 04:15:21'),
(69, 1, 'login', 'user', 1, '[]', '2025-12-28 04:15:25'),
(70, 1, 'logout', 'user', 1, '[]', '2025-12-28 06:39:23'),
(71, 10, 'login', 'user', 10, '[]', '2025-12-28 06:39:28'),
(72, 10, 'logout', 'user', 10, '[]', '2025-12-28 06:39:36'),
(73, 3, 'login', 'user', 3, '[]', '2025-12-28 06:39:45'),
(74, 3, 'logout', 'user', 3, '[]', '2025-12-28 06:40:31'),
(75, 13, 'login', 'user', 13, '[]', '2025-12-28 06:40:56'),
(76, 13, 'logout', 'user', 13, '[]', '2025-12-28 06:42:18'),
(77, 8, 'login', 'user', 8, '[]', '2025-12-28 06:42:21'),
(78, 8, 'logout', 'user', 8, '[]', '2025-12-28 06:42:40'),
(79, 1, 'login', 'user', 1, '[]', '2025-12-28 06:42:44'),
(80, 1, 'update', 'grade_periods', 0, '{\"action\":\"update_all_periods\"}', '2025-12-28 06:43:02'),
(81, 1, 'logout', 'user', 1, '[]', '2025-12-28 06:43:06'),
(82, 8, 'login', 'user', 8, '[]', '2025-12-28 06:43:11'),
(83, 8, 'update', 'grade', 1, '{\"student_id\":13,\"subject_id\":2,\"quarter\":\"q1\",\"grade\":92}', '2025-12-28 06:43:30'),
(84, 8, 'update_user', 'user', 8, '[]', '2025-12-28 07:39:01'),
(85, 8, 'update', 'profile', 8, '{\"name\":\"Leovy Mae Khey\",\"email\":\"Leovy@tlca.com\",\"image\":\"assets/images/user_1766936341_d7ea62bf2a172a6f.png\"}', '2025-12-28 07:39:01'),
(86, 8, 'update_user', 'user', 8, '[]', '2025-12-28 07:39:12'),
(87, 8, 'update', 'profile', 8, '{\"name\":\"Leovy Mae Khey\",\"email\":\"Leovy@tlca.com\",\"image\":\"assets/images/user_1766936352_54a2805e830bd9ab.png\"}', '2025-12-28 07:39:12'),
(88, 8, 'logout', 'user', 8, '[]', '2025-12-28 07:39:19'),
(89, 6, 'login', 'user', 6, '[]', '2025-12-28 07:39:24'),
(90, 6, 'logout', 'user', 6, '[]', '2025-12-28 07:39:35'),
(91, 1, 'login', 'user', 1, '[]', '2025-12-28 07:39:41'),
(92, 1, 'logout', 'user', 1, '[]', '2025-12-28 20:44:19'),
(93, 8, 'login', 'user', 8, '[]', '2025-12-28 20:44:32'),
(94, 8, 'create', 'quiz', 1, '{\"subjectId\":2,\"title\":\"xczx\"}', '2025-12-29 04:42:32'),
(95, 8, 'create_batch', 'quiz_question', 0, '{\"quiz_id\":1,\"count\":1}', '2025-12-29 04:43:31'),
(96, 8, 'create_batch', 'quiz_question', 0, '{\"quiz_id\":1,\"count\":1}', '2025-12-29 04:43:31'),
(97, 8, 'create_batch', 'quiz_question', 0, '{\"quiz_id\":1,\"count\":1}', '2025-12-29 04:43:54'),
(98, 8, 'create_batch', 'quiz_question', 0, '{\"quiz_id\":1,\"count\":1}', '2025-12-29 04:43:54'),
(99, 8, 'create', 'quiz_question', 5, '{\"quiz_id\":1}', '2025-12-29 04:44:35'),
(100, 8, 'logout', 'user', 8, '[]', '2025-12-29 04:45:05'),
(101, 6, 'login', 'user', 6, '[]', '2025-12-29 04:45:09'),
(102, 6, 'logout', 'user', 6, '[]', '2025-12-29 04:45:24'),
(103, 13, 'login', 'user', 13, '[]', '2025-12-29 04:45:28'),
(104, 13, 'logout', 'user', 13, '[]', '2025-12-29 06:18:48'),
(105, 8, 'login', 'user', 8, '[]', '2025-12-29 06:18:52'),
(106, 8, 'logout', 'user', 8, '[]', '2025-12-29 06:42:58'),
(107, 1, 'login', 'user', 1, '[]', '2025-12-29 06:43:05'),
(108, 8, 'login', 'user', 8, '[]', '2025-12-29 23:36:48'),
(109, 1, 'login', 'user', 1, '[]', '2025-12-31 22:36:32'),
(110, 1, 'update', 'grade_periods', 0, '{\"action\":\"update_all_periods\"}', '2025-12-31 23:06:06'),
(111, 1, 'login', 'user', 1, '[]', '2026-01-11 22:04:56'),
(112, 1, 'logout', 'user', 1, '[]', '2026-01-11 22:05:27'),
(113, 8, 'login', 'user', 8, '[]', '2026-01-11 22:05:32'),
(114, 8, 'logout', 'user', 8, '[]', '2026-01-11 22:16:28'),
(115, 8, 'login', 'user', 8, '[]', '2026-01-14 00:33:30'),
(116, 1, 'login', 'user', 1, '[]', '2026-01-19 06:34:38'),
(117, 8, 'login', 'user', 8, '[]', '2026-01-23 20:03:54'),
(118, 1, 'login', 'user', 1, '[]', '2026-02-05 02:54:57'),
(119, 1, 'logout', 'user', 1, '[]', '2026-02-05 02:59:38'),
(120, 1, 'login', 'user', 1, '[]', '2026-02-05 05:05:51'),
(121, 1, 'register_user', 'user', 14, '[]', '2026-02-05 05:07:20'),
(122, 1, 'create', 'user', 14, '{\"empidno\":\"ADMIN002\",\"name\":\"test\",\"email\":\"admintest1@tlca.com\",\"role\":\"admin\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-02-05 05:07:20'),
(123, 1, 'logout', 'user', 1, '[]', '2026-02-05 05:08:23'),
(124, 13, 'login', 'user', 13, '[]', '2026-02-05 05:09:08'),
(125, 8, 'login', 'user', 8, '[]', '2026-02-12 07:41:52'),
(126, 8, 'create', 'activity', 3, '{\"subjectId\":2,\"title\":\"Home Safety Observation\"}', '2026-02-12 08:02:46'),
(127, 8, 'create', 'quiz', 2, '{\"subjectId\":2,\"title\":\"TECHNOLOGY AND LIVELIHOOD EDUCATION QUIZ\"}', '2026-02-12 08:06:07'),
(128, 8, 'update', 'quiz', 2, '{\"title\":\"TECHNOLOGY AND LIVELIHOOD EDUCATION QUIZ\",\"instructions\":\"Choose the correct answer.\",\"timeLimit\":3,\"maxScore\":100,\"tabProtection\":0}', '2026-02-12 08:06:16'),
(129, 8, 'update', 'quiz', 2, '{\"title\":\"TECHNOLOGY AND LIVELIHOOD EDUCATION QUIZ\",\"instructions\":\"Choose the correct answer.\",\"timeLimit\":3,\"maxScore\":100,\"tabProtection\":0}', '2026-02-12 08:06:18'),
(130, 8, 'create', 'quiz', 3, '{\"subjectId\":2,\"title\":\"TECHNOLOGY\"}', '2026-02-12 08:13:40'),
(131, 8, 'create', 'quiz_question', 6, '{\"quiz_id\":3}', '2026-02-12 08:17:02'),
(132, 8, 'create', 'quiz_question', 7, '{\"quiz_id\":3}', '2026-02-12 08:20:19'),
(133, 8, 'create', 'quiz_question', 8, '{\"quiz_id\":3}', '2026-02-12 08:21:32'),
(134, 8, 'create', 'quiz_question', 9, '{\"quiz_id\":3}', '2026-02-12 08:23:20'),
(135, 8, 'create', 'quiz_question', 10, '{\"quiz_id\":3}', '2026-02-12 08:24:18'),
(136, 8, 'create', 'quiz_question', 11, '{\"quiz_id\":3}', '2026-02-12 08:25:04'),
(137, 8, 'create', 'quiz_question', 12, '{\"quiz_id\":3}', '2026-02-12 08:25:25'),
(138, 8, 'create', 'quiz_question', 13, '{\"quiz_id\":3}', '2026-02-12 08:26:31'),
(139, 8, 'update', 'quiz', 3, '{\"title\":\"TECHNOLOGY\",\"instructions\":\"Multiple Choice\\r\\nDirection: Choose the best answer.\",\"timeLimit\":30,\"maxScore\":50,\"tabProtection\":0}', '2026-02-12 08:26:53'),
(140, 8, 'create', 'quiz_question', 14, '{\"quiz_id\":3}', '2026-02-12 08:27:23'),
(141, 8, 'logout', 'user', 8, '[]', '2026-02-12 08:35:45'),
(142, 13, 'login', 'user', 13, '[]', '2026-02-12 08:37:17'),
(143, 13, 'submit', 'activity_submission', 1, '{\"activity_id\":3}', '2026-02-12 08:42:01'),
(144, 13, 'logout', 'user', 13, '[]', '2026-02-12 09:01:53'),
(145, 8, 'login', 'user', 8, '[]', '2026-02-12 09:03:11'),
(146, 8, 'logout', 'user', 8, '[]', '2026-02-12 09:05:14'),
(147, 14, 'login', 'user', 14, '[]', '2026-02-12 09:05:43'),
(148, 14, 'login', 'user', 14, '[]', '2026-02-13 08:02:51'),
(149, 14, 'logout', 'user', 14, '[]', '2026-02-13 08:37:19'),
(150, 14, 'login', 'user', 14, '[]', '2026-02-13 13:01:40'),
(151, 14, 'login', 'user', 14, '[]', '2026-02-13 18:07:35'),
(152, 14, 'login', 'user', 14, '[]', '2026-02-13 19:53:07'),
(153, 14, 'logout', 'user', 14, '[]', '2026-02-13 19:54:11'),
(154, 8, 'login', 'user', 8, '[]', '2026-02-13 19:54:28'),
(155, 8, 'logout', 'user', 8, '[]', '2026-02-13 19:56:43'),
(156, 13, 'login', 'user', 13, '[]', '2026-02-13 19:57:03'),
(157, 8, 'login', 'user', 8, '[]', '2026-02-13 20:35:17'),
(158, 14, 'logout', 'user', 14, '[]', '2026-02-13 20:36:53'),
(159, 13, 'login', 'user', 13, '[]', '2026-02-13 20:37:08'),
(160, 13, 'logout', 'user', 13, '[]', '2026-02-13 20:47:52'),
(161, 14, 'login', 'user', 14, '[]', '2026-02-13 20:48:13'),
(162, 14, 'update', 'grade_periods', 0, '{\"action\":\"update_all_periods\"}', '2026-02-13 20:50:28'),
(163, 13, 'logout', 'user', 13, '[]', '2026-02-13 21:22:43'),
(164, 14, 'login', 'user', 14, '[]', '2026-02-13 21:23:58'),
(165, 14, 'logout', 'user', 14, '[]', '2026-02-13 21:29:23'),
(166, 8, 'login', 'user', 8, '[]', '2026-02-13 21:29:33'),
(167, 8, 'logout', 'user', 8, '[]', '2026-02-13 21:32:36'),
(168, 13, 'login', 'user', 13, '[]', '2026-02-13 21:33:13'),
(169, 13, 'logout', 'user', 13, '[]', '2026-02-13 21:35:30'),
(170, 8, 'login', 'user', 8, '[]', '2026-03-08 20:25:52'),
(171, 8, 'update', 'grade', 1, '{\"student_id\":13,\"subject_id\":2,\"quarter\":\"q2\",\"grade\":80}', '2026-03-08 20:27:36'),
(172, 8, 'update', 'grade', 1, '{\"student_id\":13,\"subject_id\":2,\"quarter\":\"q2\",\"grade\":90}', '2026-03-08 20:27:46'),
(173, 8, 'logout', 'user', 8, '[]', '2026-03-08 20:27:59'),
(174, 13, 'login', 'user', 13, '[]', '2026-03-08 20:28:42'),
(175, 8, 'login', 'user', 8, '[]', '2026-03-13 22:46:00'),
(176, 14, 'login', 'user', 14, '[]', '2026-04-05 06:55:02'),
(177, 1, 'login', 'user', 1, '[]', '2026-04-06 03:59:34'),
(178, 1, 'login', 'user', 1, '[]', '2026-04-06 04:09:48'),
(179, 1, 'logout', 'user', 1, '[]', '2026-04-06 04:10:02'),
(180, 1, 'login', 'user', 1, '[]', '2026-04-06 04:10:09'),
(181, 1, 'logout', 'user', 1, '[]', '2026-04-06 04:13:50'),
(182, 1, 'login', 'user', 1, '[]', '2026-04-06 04:15:46'),
(183, 1, 'logout', 'user', 1, '[]', '2026-04-06 04:21:44'),
(184, 1, 'login', 'user', 1, '[]', '2026-04-06 04:23:38'),
(185, 1, 'logout', 'user', 1, '[]', '2026-04-06 04:27:22'),
(186, 8, 'login', 'user', 8, '[]', '2026-04-06 04:27:32'),
(187, 8, 'logout', 'user', 8, '[]', '2026-04-06 04:28:12'),
(188, 13, 'login', 'user', 13, '[]', '2026-04-06 04:28:21'),
(189, 13, 'logout', 'user', 13, '[]', '2026-04-06 04:31:06'),
(190, 8, 'login', 'user', 8, '[]', '2026-04-06 04:31:10'),
(191, 8, 'logout', 'user', 8, '[]', '2026-04-06 04:32:06'),
(192, 13, 'login', 'user', 13, '[]', '2026-04-06 04:32:12'),
(193, 13, 'logout', 'user', 13, '[]', '2026-04-06 04:33:05'),
(194, 8, 'login', 'user', 8, '[]', '2026-04-06 04:33:10'),
(195, 8, 'logout', 'user', 8, '[]', '2026-04-06 04:58:03'),
(196, 14, 'login', 'user', 14, '[]', '2026-04-07 18:01:47'),
(197, 14, 'logout', 'user', 14, '[]', '2026-04-07 18:03:27'),
(198, 8, 'login', 'user', 8, '[]', '2026-04-07 18:04:11'),
(199, 8, 'logout', 'user', 8, '[]', '2026-04-07 20:17:20'),
(200, 14, 'login', 'user', 14, '[]', '2026-04-07 20:17:30'),
(201, 14, 'logout', 'user', 14, '[]', '2026-04-07 20:18:54'),
(202, 13, 'login', 'user', 13, '[]', '2026-04-07 20:19:09'),
(203, 1, 'login', 'user', 1, '[]', '2026-04-10 22:53:48'),
(204, 1, 'login', 'user', 1, '[]', '2026-04-18 20:03:47'),
(205, 1, 'login', 'user', 1, '[]', '2026-04-20 21:55:27'),
(206, 1, 'logout', 'user', 1, '[]', '2026-04-20 22:11:25'),
(207, 1, 'login', 'user', 1, '[]', '2026-04-20 23:16:41'),
(208, 1, 'logout', 'user', 1, '[]', '2026-04-20 23:46:31'),
(209, 1, 'login', 'user', 1, '[]', '2026-04-20 23:51:56'),
(210, 1, 'register_user', 'user', 15, '[]', '2026-04-20 23:53:29'),
(211, 1, 'create', 'user', 15, '{\"empidno\":\"REG001\",\"name\":\"jane doe\",\"email\":\"jane123@gmail.com\",\"role\":\"registrar\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-20 23:53:29'),
(212, 1, 'login', 'user', 1, '[]', '2026-04-21 00:12:16'),
(213, 1, 'login', 'user', 1, '[]', '2026-04-21 00:15:57'),
(214, 1, 'logout', 'user', 1, '[]', '2026-04-21 00:31:08'),
(215, 1, 'login', 'user', 1, '[]', '2026-04-21 00:31:16'),
(216, 1, 'logout', 'user', 1, '[]', '2026-04-21 00:31:46'),
(217, 15, 'login', 'user', 15, '[]', '2026-04-21 00:31:54'),
(218, 15, 'logout', 'user', 15, '[]', '2026-04-21 00:35:12'),
(219, 15, 'login', 'user', 15, '[]', '2026-04-21 01:42:42'),
(220, 15, 'logout', 'user', 15, '[]', '2026-04-21 01:43:22'),
(221, 1, 'login', 'user', 1, '[]', '2026-04-21 01:43:27'),
(222, 1, 'logout', 'user', 1, '[]', '2026-04-21 01:44:03'),
(223, 1, 'login', 'user', 1, '[]', '2026-04-21 01:53:54'),
(224, 1, 'logout', 'user', 1, '[]', '2026-04-21 08:26:16'),
(225, 1, 'login', 'user', 1, '[]', '2026-04-22 03:56:31'),
(226, 1, 'login', 'user', 1, '[]', '2026-04-22 19:02:59'),
(227, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:03:45'),
(228, 8, 'login', 'user', 8, '[]', '2026-04-22 19:03:51'),
(229, 8, 'logout', 'user', 8, '[]', '2026-04-22 19:05:17'),
(230, 1, 'login', 'user', 1, '[]', '2026-04-22 19:05:35'),
(231, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:10:16'),
(232, 1, 'login', 'user', 1, '[]', '2026-04-22 19:10:20'),
(233, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:11:08'),
(234, 15, 'login', 'user', 15, '[]', '2026-04-22 19:11:15'),
(235, 1, 'login', 'user', 1, '[]', '2026-04-22 19:11:29'),
(236, 15, 'logout', 'user', 15, '[]', '2026-04-22 19:16:44'),
(237, 1, 'login', 'user', 1, '[]', '2026-04-22 19:16:50'),
(238, 1, 'register_user', 'user', 16, '[]', '2026-04-22 19:17:51'),
(239, 1, 'create', 'user', 16, '{\"empidno\":\"CASH001\",\"name\":\"cashier\",\"email\":\"cashier@gmail.com\",\"role\":\"cashier\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-22 19:17:51'),
(240, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:18:26'),
(241, 16, 'login', 'user', 16, '[]', '2026-04-22 19:18:31'),
(242, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:29:37'),
(243, 15, 'login', 'user', 15, '[]', '2026-04-22 19:30:18'),
(244, 16, 'logout', 'user', 16, '[]', '2026-04-22 19:33:43'),
(245, 1, 'login', 'user', 1, '[]', '2026-04-22 19:33:59'),
(246, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:34:23'),
(247, 15, 'login', 'user', 15, '[]', '2026-04-22 19:34:29'),
(248, 15, 'logout', 'user', 15, '[]', '2026-04-22 19:45:03'),
(249, 1, 'login', 'user', 1, '[]', '2026-04-22 19:45:10'),
(250, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:45:30'),
(251, 1, 'login', 'user', 1, '[]', '2026-04-22 19:45:35'),
(252, 1, 'logout', 'user', 1, '[]', '2026-04-22 19:48:00'),
(253, 15, 'login', 'user', 15, '[]', '2026-04-22 19:48:09'),
(254, 16, 'login', 'user', 16, '[]', '2026-04-22 23:50:09'),
(255, 1, 'login', 'user', 1, '[]', '2026-04-22 23:54:18'),
(256, 1, 'login', 'user', 1, '[]', '2026-04-23 00:25:56'),
(257, 1, 'logout', 'user', 1, '[]', '2026-04-23 00:26:49'),
(258, 15, 'logout', 'user', 15, '[]', '2026-04-23 01:20:54'),
(259, 1, 'login', 'user', 1, '[]', '2026-04-23 01:22:04'),
(260, 1, 'logout', 'user', 1, '[]', '2026-04-23 01:23:35'),
(261, 15, 'login', 'user', 15, '[]', '2026-04-23 01:23:41'),
(262, 15, 'logout', 'user', 15, '[]', '2026-04-23 01:28:09'),
(263, 1, 'login', 'user', 1, '[]', '2026-04-23 01:28:14'),
(264, 1, 'logout', 'user', 1, '[]', '2026-04-23 01:28:35'),
(265, 13, 'login', 'user', 13, '[]', '2026-04-23 01:28:42'),
(266, 13, 'logout', 'user', 13, '[]', '2026-04-23 01:38:31'),
(267, 15, 'login', 'user', 15, '[]', '2026-04-23 01:38:37'),
(268, 15, 'logout', 'user', 15, '[]', '2026-04-23 01:47:51'),
(269, 1, 'login', 'user', 1, '[]', '2026-04-23 01:48:16'),
(270, 1, 'archive_user', 'user', 14, '[]', '2026-04-23 01:48:52'),
(271, 1, 'archive', 'user', 14, '[]', '2026-04-23 01:48:52'),
(272, 1, 'archive_user', 'user', 3, '[]', '2026-04-23 01:48:58'),
(273, 1, 'archive', 'user', 3, '[]', '2026-04-23 01:48:58'),
(274, 1, 'archive_user', 'user', 2, '[]', '2026-04-23 01:49:03'),
(275, 1, 'archive', 'user', 2, '[]', '2026-04-23 01:49:03'),
(276, 1, 'archive_user', 'user', 9, '[]', '2026-04-23 01:49:13'),
(277, 1, 'archive', 'user', 9, '[]', '2026-04-23 01:49:13'),
(278, 1, 'archive_user', 'user', 8, '[]', '2026-04-23 01:49:19'),
(279, 1, 'archive', 'user', 8, '[]', '2026-04-23 01:49:19'),
(280, 1, 'archive_user', 'user', 11, '[]', '2026-04-23 01:49:24'),
(281, 1, 'archive', 'user', 11, '[]', '2026-04-23 01:49:24'),
(282, 1, 'archive_user', 'user', 4, '[]', '2026-04-23 01:49:28'),
(283, 1, 'archive', 'user', 4, '[]', '2026-04-23 01:49:28'),
(284, 1, 'archive_user', 'user', 17, '[]', '2026-04-23 01:49:33'),
(285, 1, 'archive', 'user', 17, '[]', '2026-04-23 01:49:33'),
(286, 1, 'archive_user', 'user', 5, '[]', '2026-04-23 01:49:37'),
(287, 1, 'archive', 'user', 5, '[]', '2026-04-23 01:49:37'),
(288, 1, 'archive_user', 'user', 13, '[]', '2026-04-23 01:49:46'),
(289, 1, 'archive', 'user', 13, '[]', '2026-04-23 01:49:46'),
(290, 1, 'archive_user', 'user', 6, '[]', '2026-04-23 01:49:55'),
(291, 1, 'archive', 'user', 6, '[]', '2026-04-23 01:49:55'),
(292, 1, 'archive_user', 'user', 10, '[]', '2026-04-23 01:50:05'),
(293, 1, 'archive', 'user', 10, '[]', '2026-04-23 01:50:05'),
(294, 1, 'archive_user', 'user', 15, '[]', '2026-04-23 01:50:13'),
(295, 1, 'archive', 'user', 15, '[]', '2026-04-23 01:50:13'),
(296, 1, 'archive_user', 'user', 16, '[]', '2026-04-23 01:50:20'),
(297, 1, 'archive', 'user', 16, '[]', '2026-04-23 01:50:20'),
(298, 1, 'register_user', 'user', 18, '[]', '2026-04-23 01:51:44'),
(299, 1, 'create', 'user', 18, '{\"empidno\":\"REG002\",\"name\":\"Crisundee Sinoy\",\"email\":\"crisundeesinoy8@gmail.com\",\"role\":\"registrar\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 01:51:44'),
(300, 1, 'register_user', 'user', 19, '[]', '2026-04-23 01:52:24'),
(301, 1, 'create', 'user', 19, '{\"empidno\":\"T006\",\"name\":\"Leovy Mae L. Khey\",\"email\":\"leovymaekhey@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 01:52:24'),
(302, 1, 'register_user', 'user', 20, '[]', '2026-04-23 01:54:53'),
(303, 1, 'create', 'user', 20, '{\"empidno\":\"T007\",\"name\":\"Rhealyn S. Villafranca\",\"email\":\"rhealyn.villafranca23@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 01:54:53'),
(304, 1, 'register_user', 'user', 21, '[]', '2026-04-23 01:55:36'),
(305, 1, 'create', 'user', 21, '{\"empidno\":\"T008\",\"name\":\"Danica V. Tubora\",\"email\":\"tuboradanica@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 01:55:36'),
(306, 1, 'register_user', 'user', 22, '[]', '2026-04-23 01:56:29'),
(307, 1, 'create', 'user', 22, '{\"empidno\":\"T009\",\"name\":\"Jonaicy T. Tabotabo\",\"email\":\"vhanecent28@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 01:56:29'),
(308, 1, 'register_user', 'user', 23, '[]', '2026-04-23 01:57:03'),
(309, 1, 'create', 'user', 23, '{\"empidno\":\"T010\",\"name\":\"Jennilyn T. Varela\",\"email\":\"jennilynvarela1998@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 01:57:03'),
(310, 1, 'register_user', 'user', 25, '[]', '2026-04-23 06:57:39'),
(311, 1, 'create', 'user', 25, '{\"empidno\":\"T011\",\"name\":\"Gia Rae L. Santos\",\"email\":\"giaraesantos@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 06:57:39'),
(312, 1, 'register_user', 'user', 26, '[]', '2026-04-23 07:13:31'),
(313, 1, 'create', 'user', 26, '{\"empidno\":\"T012\",\"name\":\"Tyra Jade P. Seruelo\",\"email\":\"tjadeseruelo@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:13:31'),
(314, 1, 'register_user', 'user', 27, '[]', '2026-04-23 07:14:17'),
(315, 1, 'create', 'user', 27, '{\"empidno\":\"T013\",\"name\":\"Joshua Jehiel O. Maravilla\",\"email\":\"joshuajehielmaravilla@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:14:17'),
(316, 1, 'register_user', 'user', 28, '[]', '2026-04-23 07:36:47'),
(317, 1, 'create', 'user', 28, '{\"empidno\":\"T014\",\"name\":\"Julie Ann C. Pigar\",\"email\":\"julieannpigar070802@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:36:47'),
(318, 1, 'register_user', 'user', 29, '[]', '2026-04-23 07:37:48'),
(319, 1, 'create', 'user', 29, '{\"empidno\":\"T015\",\"name\":\"Ferlyn S. Escalant\",\"email\":\"ferlynescalante587@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:37:48'),
(320, 1, 'register_user', 'user', 30, '[]', '2026-04-23 07:38:44'),
(321, 1, 'create', 'user', 30, '{\"empidno\":\"T016\",\"name\":\"Grazel A. Arnaez\",\"email\":\"tlcagrazel@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:38:44'),
(322, 1, 'register_user', 'user', 31, '[]', '2026-04-23 07:39:34'),
(323, 1, 'create', 'user', 31, '{\"empidno\":\"T017\",\"name\":\"Merlyn Mae Joy C. Jocson\",\"email\":\"merlynjocson492@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:39:34'),
(324, 1, 'register_user', 'user', 32, '[]', '2026-04-23 07:40:14'),
(325, 1, 'create', 'user', 32, '{\"empidno\":\"T018\",\"name\":\"Victor P. Flores Jr\",\"email\":\"victorpfloresjr459@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:40:14'),
(326, 1, 'logout', 'user', 1, '[]', '2026-04-23 07:40:25'),
(327, 1, 'login', 'user', 1, '[]', '2026-04-23 07:41:20'),
(328, 1, 'register_user', 'user', 33, '[]', '2026-04-23 07:42:45'),
(329, 1, 'create', 'user', 33, '{\"empidno\":\"T019\",\"name\":\"Jun Xavier B. Gicana\",\"email\":\"jxgicana@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-04-23 07:42:45'),
(330, 1, 'logout', 'user', 1, '[]', '2026-04-23 07:42:52'),
(331, 33, 'login', 'user', 33, '[]', '2026-04-23 07:43:06'),
(332, 33, 'logout', 'user', 33, '[]', '2026-04-23 07:43:43'),
(333, 1, 'login', 'user', 1, '[]', '2026-04-23 07:43:57'),
(334, 33, 'login', 'user', 33, '[]', '2026-04-25 09:46:56'),
(335, 1, 'login', 'user', 1, '[]', '2026-04-25 23:31:28'),
(336, 1, 'login', 'user', 1, '[]', '2026-04-28 01:50:02'),
(337, 18, 'login', 'user', 18, '[]', '2026-05-11 06:37:20'),
(338, 18, 'logout', 'user', 18, '[]', '2026-05-11 06:39:28'),
(339, 1, 'login', 'user', 1, '[]', '2026-05-11 06:39:36'),
(340, 1, 'unarchive_user', 'user', 17, '[]', '2026-05-11 06:41:13'),
(341, 1, 'unarchive', 'user', 17, '[]', '2026-05-11 06:41:13'),
(342, 1, 'unarchive_user', 'user', 17, '[]', '2026-05-11 06:41:13'),
(343, 1, 'unarchive', 'user', 17, '[]', '2026-05-11 06:41:13'),
(344, 1, 'logout', 'user', 1, '[]', '2026-05-11 06:41:16'),
(345, 18, 'login', 'user', 18, '[]', '2026-05-11 06:41:21'),
(346, 18, 'logout', 'user', 18, '[]', '2026-05-11 06:58:02'),
(347, 18, 'login', 'user', 18, '[]', '2026-05-11 06:58:14'),
(348, 18, 'logout', 'user', 18, '[]', '2026-05-11 18:01:52'),
(349, 16, 'login', 'user', 16, '[]', '2026-05-11 18:23:59'),
(350, 16, 'logout', 'user', 16, '[]', '2026-05-11 18:24:37'),
(351, 1, 'login', 'user', 1, '[]', '2026-05-11 18:26:54'),
(352, 1, 'logout', 'user', 1, '[]', '2026-05-11 18:31:04'),
(353, 1, 'login', 'user', 1, '[]', '2026-05-11 18:32:33'),
(354, 1, 'logout', 'user', 1, '[]', '2026-05-11 18:35:53'),
(355, 20, 'login', 'user', 20, '[]', '2026-05-11 18:36:04'),
(356, 20, 'logout', 'user', 20, '[]', '2026-05-11 18:36:23'),
(357, 1, 'login', 'user', 1, '[]', '2026-05-11 18:36:29'),
(358, 1, 'assign', 'folder_teacher', 4, '{\"subject_id\":5,\"teacher_empidno\":\"T007\"}', '2026-05-11 18:36:45'),
(359, 1, 'logout', 'user', 1, '[]', '2026-05-11 18:36:53'),
(360, 20, 'login', 'user', 20, '[]', '2026-05-11 18:36:59'),
(361, 20, 'logout', 'user', 20, '[]', '2026-05-11 18:38:00'),
(362, 1, 'login', 'user', 1, '[]', '2026-05-11 18:38:06'),
(363, 1, 'logout', 'user', 1, '[]', '2026-05-11 18:41:09'),
(364, 18, 'login', 'user', 18, '[]', '2026-05-11 18:41:15'),
(365, 18, 'logout', 'user', 18, '[]', '2026-05-11 18:53:50'),
(366, 1, 'login', 'user', 1, '[]', '2026-05-11 18:53:55'),
(367, 1, 'logout', 'user', 1, '[]', '2026-05-11 18:55:07'),
(368, 33, 'login', 'user', 33, '[]', '2026-05-11 18:55:24'),
(369, 33, 'logout', 'user', 33, '[]', '2026-05-11 18:59:29'),
(370, 33, 'login', 'user', 33, '[]', '2026-05-11 19:07:53'),
(371, 28, 'login', 'user', 28, '[]', '2026-05-11 19:08:27'),
(372, 26, 'login', 'user', 26, '[]', '2026-05-11 19:12:42'),
(373, 18, 'login', 'user', 18, '[]', '2026-05-11 19:42:55'),
(374, 19, 'login', 'user', 19, '[]', '2026-05-11 20:38:06'),
(375, 29, 'login', 'user', 29, '[]', '2026-05-11 20:38:07'),
(376, 29, 'change_password', 'user', 29, '[]', '2026-05-11 20:39:26'),
(377, 29, 'update_user', 'user', 29, '[]', '2026-05-11 20:39:48'),
(378, 29, 'update', 'profile', 29, '{\"name\":\"Ferlyn S. Escalante\",\"email\":\"ferlynescalante587@gmail.com\",\"image\":null}', '2026-05-11 20:39:48'),
(379, 19, 'logout', 'user', 19, '[]', '2026-05-11 20:40:18'),
(380, 1, 'login', 'user', 1, '[]', '2026-05-11 22:10:44'),
(381, 18, 'change_password', 'user', 18, '[]', '2026-05-12 18:42:28'),
(382, 18, 'login', 'user', 18, '[]', '2026-05-12 19:54:14'),
(383, 18, 'login', 'user', 18, '[]', '2026-05-12 20:33:42'),
(384, 18, 'login', 'user', 18, '[]', '2026-05-12 20:42:30'),
(385, 18, 'logout', 'user', 18, '[]', '2026-05-13 00:52:47'),
(386, 62, 'login', 'user', 62, '[]', '2026-05-13 00:54:07'),
(387, 62, 'logout', 'user', 62, '[]', '2026-05-13 00:55:47'),
(388, 62, 'logout', 'user', 62, '[]', '2026-05-13 00:55:47'),
(389, 18, 'login', 'user', 18, '[]', '2026-05-13 00:55:51'),
(390, 1, 'login', 'user', 1, '[]', '2026-05-13 00:56:56'),
(391, 18, 'logout', 'user', 18, '[]', '2026-05-13 00:58:13'),
(392, 18, 'login', 'user', 18, '[]', '2026-05-13 00:58:25'),
(393, 1, 'reset_password', 'user', 62, '[]', '2026-05-13 01:16:29'),
(394, 1, 'reset_password', 'user', 62, '[]', '2026-05-13 01:16:29'),
(395, 1, 'logout', 'user', 1, '[]', '2026-05-13 01:17:08'),
(396, 1, 'login', 'user', 1, '[]', '2026-05-13 01:17:41'),
(397, 1, 'reset_password', 'user', 65, '[]', '2026-05-13 01:19:03'),
(398, 1, 'reset_password', 'user', 65, '[]', '2026-05-13 01:19:03'),
(399, 1, 'logout', 'user', 1, '[]', '2026-05-13 01:19:31'),
(400, 65, 'login', 'user', 65, '[]', '2026-05-13 01:19:42'),
(401, 65, 'logout', 'user', 65, '[]', '2026-05-13 01:20:16'),
(402, 1, 'login', 'user', 1, '[]', '2026-05-13 01:20:23'),
(403, 1, 'logout', 'user', 1, '[]', '2026-05-13 01:20:52'),
(404, 62, 'login', 'user', 62, '[]', '2026-05-13 01:21:00'),
(405, 18, 'login', 'user', 18, '[]', '2026-05-13 23:16:56'),
(406, 1, 'login', 'user', 1, '[]', '2026-05-14 03:01:37'),
(407, 18, 'login', 'user', 18, '[]', '2026-05-17 17:35:59'),
(408, 18, 'login', 'user', 18, '[]', '2026-05-17 23:21:14'),
(409, 18, 'logout', 'user', 18, '[]', '2026-05-18 01:57:10'),
(410, 147, 'login', 'user', 147, '[]', '2026-05-18 01:58:15'),
(411, 147, 'update_user', 'user', 147, '[]', '2026-05-18 01:59:16'),
(412, 147, 'update', 'profile', 147, '{\"name\":\"Carmelino, Ronalyn Paconla\",\"email\":\"ronalyncarmelino@gmail.com\",\"image\":null}', '2026-05-18 01:59:16'),
(413, 147, 'update_user', 'user', 147, '[]', '2026-05-18 01:59:33'),
(414, 147, 'update', 'profile', 147, '{\"name\":\"Carmelino, Ronalyn Paconla\",\"email\":\"ronalyncarmelino@gmail.com\",\"image\":null}', '2026-05-18 01:59:33'),
(415, 147, 'logout', 'user', 147, '[]', '2026-05-18 02:01:05'),
(416, 18, 'login', 'user', 18, '[]', '2026-05-18 02:01:13'),
(417, 18, 'login', 'user', 18, '[]', '2026-05-18 23:22:22'),
(418, 18, 'login', 'user', 18, '[]', '2026-05-19 21:57:14'),
(419, 18, 'login', 'user', 18, '[]', '2026-05-19 23:28:18'),
(420, 18, 'login', 'user', 18, '[]', '2026-05-20 23:48:53'),
(421, 18, 'login', 'user', 18, '[]', '2026-05-21 18:55:27'),
(422, 18, 'logout', 'user', 18, '[]', '2026-05-21 19:28:48'),
(423, 215, 'login', 'user', 215, '[]', '2026-05-21 19:30:03'),
(424, 215, 'update_user', 'user', 215, '[]', '2026-05-21 19:31:27'),
(425, 215, 'update', 'profile', 215, '{\"name\":\"VARGAS, EDRIE CALEB DELOESTE\",\"email\":\"edriecalebvargas@gmail.com\",\"image\":null}', '2026-05-21 19:31:27'),
(426, 215, 'logout', 'user', 215, '[]', '2026-05-21 20:08:44'),
(427, 18, 'login', 'user', 18, '[]', '2026-05-21 20:08:49'),
(428, 1, 'login', 'user', 1, '[]', '2026-05-22 02:59:40'),
(429, 18, 'login', 'user', 18, '[]', '2026-05-24 18:03:23'),
(430, 1, 'login', 'user', 1, '[]', '2026-05-24 18:08:49'),
(431, 1, 'login', 'user', 1, '[]', '2026-05-25 07:04:59'),
(432, 18, 'login', 'user', 18, '[]', '2026-05-25 18:30:17'),
(433, 1, 'login', 'user', 1, '[]', '2026-05-25 19:34:58'),
(434, 1, 'login', 'user', 1, '[]', '2026-05-26 21:05:44'),
(435, 18, 'login', 'user', 18, '[]', '2026-05-27 23:31:31'),
(436, 18, 'logout', 'user', 18, '[]', '2026-05-28 02:09:00'),
(437, 1, 'login', 'user', 1, '[]', '2026-05-28 07:48:21'),
(438, 1, 'login', 'user', 1, '[]', '2026-05-28 09:23:48'),
(439, 1, 'delete', 'activity', 3, '[]', '2026-05-28 09:24:26'),
(440, 1, 'delete', 'activity', 2, '[]', '2026-05-28 09:24:28'),
(441, 1, 'delete', 'quiz', 3, '[]', '2026-05-28 09:24:38'),
(442, 1, 'delete', 'quiz', 2, '[]', '2026-05-28 09:24:42'),
(443, 1, 'delete', 'quiz', 1, '[]', '2026-05-28 09:24:45'),
(444, 18, 'login', 'user', 18, '[]', '2026-05-28 18:05:50'),
(445, 18, 'login', 'user', 18, '[]', '2026-05-28 18:15:50'),
(446, 1, 'login', 'user', 1, '[]', '2026-05-28 23:28:10'),
(447, 29, 'login', 'user', 29, '[]', '2026-05-29 17:40:21'),
(448, 1, 'login', 'user', 1, '[]', '2026-05-30 07:42:54'),
(449, 1, 'logout', 'user', 1, '[]', '2026-05-31 05:22:12'),
(450, 18, 'login', 'user', 18, '[]', '2026-05-31 17:58:56'),
(451, 18, 'login', 'user', 18, '[]', '2026-05-31 18:19:34'),
(452, 18, 'login', 'user', 18, '[]', '2026-06-01 18:43:14'),
(453, 18, 'logout', 'user', 18, '[]', '2026-06-01 20:12:29'),
(454, 18, 'login', 'user', 18, '[]', '2026-06-01 22:27:49'),
(455, 18, 'login', 'user', 18, '[]', '2026-06-01 22:51:39'),
(456, 18, 'login', 'user', 18, '[]', '2026-06-02 22:54:56'),
(457, 18, 'login', 'user', 18, '[]', '2026-06-02 23:51:25'),
(458, 1, 'login', 'user', 1, '[]', '2026-06-03 07:58:25'),
(459, 18, 'login', 'user', 18, '[]', '2026-06-03 23:06:27'),
(460, 18, 'login', 'user', 18, '[]', '2026-06-03 23:58:56'),
(461, 1, 'archive', 'subject', 1, '[]', '2026-06-04 05:57:42'),
(462, 1, 'archive', 'subject', 4, '[]', '2026-06-04 05:57:46'),
(463, 1, 'archive', 'subject', 3, '[]', '2026-06-04 05:57:52'),
(464, 1, 'archive', 'subject', 5, '[]', '2026-06-04 05:57:56'),
(465, 1, 'archive', 'subject', 2, '[]', '2026-06-04 05:58:00'),
(466, 18, 'login', 'user', 18, '[]', '2026-06-05 00:14:57'),
(467, 18, 'logout', 'user', 18, '[]', '2026-06-05 02:03:40'),
(468, 18, 'login', 'user', 18, '[]', '2026-06-06 21:44:48'),
(469, 18, 'logout', 'user', 18, '[]', '2026-06-06 23:16:21'),
(470, 18, 'login', 'user', 18, '[]', '2026-06-07 17:24:10'),
(471, 1, 'login', 'user', 1, '[]', '2026-06-07 23:41:49'),
(472, 18, 'login', 'user', 18, '[]', '2026-06-08 00:37:27'),
(473, 1, 'assign', 'folder_teacher', 5, '{\"subject_id\":6,\"teacher_empidno\":\"T006\"}', '2026-06-08 00:57:07'),
(474, 19, 'login', 'user', 19, '[]', '2026-06-08 01:00:16'),
(475, 1, 'reset_password', 'user', 34, '[]', '2026-06-08 01:33:25'),
(476, 1, 'reset_password', 'user', 34, '[]', '2026-06-08 01:33:25'),
(477, 1, 'logout', 'user', 1, '[]', '2026-06-08 01:33:43'),
(478, 34, 'login', 'user', 34, '[]', '2026-06-08 01:33:56'),
(479, 34, 'logout', 'user', 34, '[]', '2026-06-08 01:34:01'),
(480, 18, 'login', 'user', 18, '[]', '2026-06-08 01:57:16'),
(481, 18, 'logout', 'user', 18, '[]', '2026-06-08 02:12:09'),
(482, 18, 'login', 'user', 18, '[]', '2026-06-08 22:26:18'),
(483, 18, 'login', 'user', 18, '[]', '2026-06-09 22:26:17'),
(484, 18, 'login', 'user', 18, '[]', '2026-06-09 23:42:45'),
(485, 18, 'logout', 'user', 18, '[]', '2026-06-10 02:23:04'),
(486, 18, 'login', 'user', 18, '[]', '2026-06-14 18:58:06'),
(487, 19, 'login', 'user', 19, '[]', '2026-06-14 23:47:52'),
(488, 19, 'logout', 'user', 19, '[]', '2026-06-14 23:48:06'),
(489, 19, 'login', 'user', 19, '[]', '2026-06-14 23:50:40'),
(490, 19, 'logout', 'user', 19, '[]', '2026-06-14 23:51:48'),
(491, 33, 'login', 'user', 33, '[]', '2026-06-14 23:52:26'),
(492, 33, 'logout', 'user', 33, '[]', '2026-06-14 23:52:41'),
(493, 1, 'login', 'user', 1, '[]', '2026-06-14 23:53:00'),
(494, 1, 'login', 'user', 1, '[]', '2026-06-14 23:53:33'),
(495, 1, 'archive', 'subject', 7, '[]', '2026-06-14 23:53:47'),
(496, 1, 'assign', 'folder_teacher', 6, '{\"subject_id\":8,\"teacher_empidno\":\"T006\"}', '2026-06-14 23:54:11'),
(497, 1, 'assign', 'folder_teacher', 7, '{\"subject_id\":9,\"teacher_empidno\":\"T019\"}', '2026-06-14 23:55:11'),
(498, 1, 'logout', 'user', 1, '[]', '2026-06-14 23:55:16'),
(499, 19, 'login', 'user', 19, '[]', '2026-06-14 23:56:24'),
(500, 1, 'login', 'user', 1, '[]', '2026-06-14 23:57:14'),
(501, 1, 'assign', 'folder_teacher', 8, '{\"subject_id\":10,\"teacher_empidno\":\"T011\"}', '2026-06-14 23:57:19'),
(502, 1, 'reset_password', 'user', 82, '[]', '2026-06-14 23:58:09'),
(503, 1, 'reset_password', 'user', 82, '[]', '2026-06-14 23:58:09'),
(504, 82, 'login', 'user', 82, '[]', '2026-06-15 00:00:26'),
(505, 82, 'enroll', 'enrollment', 3, '{\"subject_id\":8}', '2026-06-15 00:01:12'),
(506, 1, 'reset_password', 'user', 176, '[]', '2026-06-15 00:06:45'),
(507, 1, 'reset_password', 'user', 176, '[]', '2026-06-15 00:06:45'),
(508, 176, 'login', 'user', 176, '[]', '2026-06-15 00:07:42'),
(509, 176, 'enroll', 'enrollment', 4, '{\"subject_id\":8}', '2026-06-15 00:08:46'),
(510, 1, 'reset_password', 'user', 311, '[]', '2026-06-15 00:10:30'),
(511, 1, 'reset_password', 'user', 311, '[]', '2026-06-15 00:10:30'),
(512, 311, 'login', 'user', 311, '[]', '2026-06-15 00:10:55'),
(513, 311, 'enroll', 'enrollment', 5, '{\"subject_id\":8}', '2026-06-15 00:11:26'),
(514, 34, 'login', 'user', 34, '[]', '2026-06-15 00:20:11'),
(515, 34, 'enroll', 'enrollment', 6, '{\"subject_id\":8}', '2026-06-15 00:20:44'),
(516, 288, 'login', 'user', 288, '[]', '2026-06-15 00:21:50'),
(517, 144, 'login', 'user', 144, '[]', '2026-06-15 00:22:20'),
(518, 288, 'enroll', 'enrollment', 7, '{\"subject_id\":8}', '2026-06-15 00:22:39'),
(519, 70, 'login', 'user', 70, '[]', '2026-06-15 00:22:43'),
(520, 70, 'enroll', 'enrollment', 8, '{\"subject_id\":8}', '2026-06-15 00:23:22'),
(521, 144, 'enroll', 'enrollment', 9, '{\"subject_id\":8}', '2026-06-15 00:23:33'),
(522, 127, 'login', 'user', 127, '[]', '2026-06-15 00:23:34'),
(523, 127, 'enroll', 'enrollment', 10, '{\"subject_id\":8}', '2026-06-15 00:24:22'),
(524, 1, 'reset_password', 'user', 99, '[]', '2026-06-15 00:26:05'),
(525, 1, 'reset_password', 'user', 99, '[]', '2026-06-15 00:26:05'),
(526, 99, 'login', 'user', 99, '[]', '2026-06-15 00:26:43'),
(527, 1, 'assign', 'folder_teacher', 9, '{\"subject_id\":11,\"teacher_empidno\":\"T016\"}', '2026-06-15 00:26:50'),
(528, 99, 'enroll', 'enrollment', 11, '{\"subject_id\":8}', '2026-06-15 00:27:05'),
(529, 303, 'login', 'user', 303, '[]', '2026-06-15 00:30:23'),
(530, 303, 'enroll', 'enrollment', 12, '{\"subject_id\":8}', '2026-06-15 00:31:14'),
(531, 140, 'login', 'user', 140, '[]', '2026-06-15 00:32:32'),
(532, 140, 'enroll', 'enrollment', 13, '{\"subject_id\":8}', '2026-06-15 00:33:33'),
(533, 1, 'reset_password', 'user', 300, '[]', '2026-06-15 00:33:50'),
(534, 1, 'reset_password', 'user', 300, '[]', '2026-06-15 00:33:50'),
(535, 300, 'login', 'user', 300, '[]', '2026-06-15 00:33:58'),
(536, 300, 'enroll', 'enrollment', 14, '{\"subject_id\":8}', '2026-06-15 00:34:20'),
(537, 1, 'reset_password', 'user', 46, '[]', '2026-06-15 00:35:49'),
(538, 1, 'reset_password', 'user', 46, '[]', '2026-06-15 00:35:49'),
(539, 46, 'login', 'user', 46, '[]', '2026-06-15 00:35:57'),
(540, 46, 'enroll', 'enrollment', 15, '{\"subject_id\":8}', '2026-06-15 00:36:37'),
(541, 1, 'logout', 'user', 1, '[]', '2026-06-15 00:51:11'),
(542, 33, 'login', 'user', 33, '[]', '2026-06-15 00:51:15'),
(543, 33, 'update_user', 'user', 33, '[]', '2026-06-15 00:54:44'),
(544, 33, 'update', 'profile', 33, '{\"name\":\"Jun Xavier B. Gicana\",\"email\":\"jxgicana@gmail.com\",\"image\":\"assets/images/user_1781510084_52995051bc665291.jpg\"}', '2026-06-15 00:54:44'),
(545, 19, 'logout', 'user', 19, '[]', '2026-06-15 00:58:13'),
(546, 1, 'login', 'user', 1, '[]', '2026-06-15 00:58:29'),
(547, 19, 'login', 'user', 19, '[]', '2026-06-15 01:04:20'),
(548, 19, 'login', 'user', 19, '[]', '2026-06-15 18:42:31'),
(549, 18, 'login', 'user', 18, '[]', '2026-06-15 19:02:24'),
(550, 19, 'logout', 'user', 19, '[]', '2026-06-15 22:37:07'),
(551, 1, 'login', 'user', 1, '[]', '2026-06-15 22:37:44'),
(552, 1, 'create', 'subject', 14, '{\"code\":\"FIL7\",\"name\":\"Filipino 7\",\"description\":\"DepEd Grade 7 Subject\"}', '2026-06-15 23:21:48'),
(553, 1, 'create', 'subject', 15, '{\"code\":\"MATH7\",\"name\":\"Mathematics 7\",\"description\":\"DepEd Grade 7 Subject\"}', '2026-06-15 23:21:58'),
(554, 1, 'create', 'subject', 16, '{\"code\":\"SCI7\",\"name\":\"Science 7\",\"description\":\"DepEd Grade 7 Subject\"}', '2026-06-15 23:34:24'),
(555, 1, 'create', 'subject', 17, '{\"code\":\"AP7\",\"name\":\"Araling Panlipunan 7\",\"description\":\"DepEd Grade 7 Subject\"}', '2026-06-15 23:34:24'),
(556, 1, 'create', 'subject', 18, '{\"code\":\"MAPEH7\",\"name\":\"MAPEH 7\",\"description\":\"DepEd Grade 7 Subject\"}', '2026-06-15 23:34:24'),
(557, 1, 'create', 'subject', 19, '{\"code\":\"ESP7\",\"name\":\"Edukasyon sa Pagpapakatao 7\",\"description\":\"DepEd Grade 7 Subject\"}', '2026-06-15 23:34:24'),
(558, 1, 'create', 'subject', 20, '{\"code\":\"FIL8\",\"name\":\"Filipino 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(559, 1, 'create', 'subject', 21, '{\"code\":\"MATH8\",\"name\":\"Mathematics 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(560, 1, 'create', 'subject', 22, '{\"code\":\"SCI8\",\"name\":\"Science 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(561, 1, 'create', 'subject', 23, '{\"code\":\"AP8\",\"name\":\"Araling Panlipunan 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(562, 1, 'create', 'subject', 24, '{\"code\":\"TLE8\",\"name\":\"Technology and Livelihood Education 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(563, 1, 'create', 'subject', 25, '{\"code\":\"MAPEH8\",\"name\":\"MAPEH 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(564, 1, 'create', 'subject', 26, '{\"code\":\"ESP8\",\"name\":\"Edukasyon sa Pagpapakatao 8\",\"description\":\"DepEd Grade 8 Subject\"}', '2026-06-15 23:34:24'),
(565, 1, 'create', 'subject', 27, '{\"code\":\"ENG9\",\"name\":\"English 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(566, 1, 'create', 'subject', 28, '{\"code\":\"FIL9\",\"name\":\"Filipino 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(567, 1, 'create', 'subject', 29, '{\"code\":\"MATH9\",\"name\":\"Mathematics 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(568, 1, 'create', 'subject', 30, '{\"code\":\"SCI9\",\"name\":\"Science 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(569, 1, 'create', 'subject', 31, '{\"code\":\"AP9\",\"name\":\"Araling Panlipunan 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(570, 1, 'create', 'subject', 32, '{\"code\":\"TLE9\",\"name\":\"Technology and Livelihood Education 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(571, 1, 'create', 'subject', 33, '{\"code\":\"MAPEH9\",\"name\":\"MAPEH 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(572, 1, 'create', 'subject', 34, '{\"code\":\"ESP9\",\"name\":\"Edukasyon sa Pagpapakatao 9\",\"description\":\"DepEd Grade 9 Subject\"}', '2026-06-15 23:34:24'),
(573, 1, 'create', 'subject', 35, '{\"code\":\"FIL10\",\"name\":\"Filipino 10\",\"description\":\"DepEd Grade 10 Subject\"}', '2026-06-15 23:34:24'),
(574, 1, 'create', 'subject', 36, '{\"code\":\"MATH10\",\"name\":\"Mathematics 10\",\"description\":\"DepEd Grade 10 Subject\"}', '2026-06-15 23:34:24'),
(575, 1, 'create', 'subject', 37, '{\"code\":\"SCI10\",\"name\":\"Science 10\",\"description\":\"DepEd Grade 10 Subject\"}', '2026-06-15 23:34:24'),
(576, 1, 'create', 'subject', 38, '{\"code\":\"MAPEH10\",\"name\":\"MAPEH 10\",\"description\":\"DepEd Grade 10 Subject\"}', '2026-06-15 23:34:24'),
(577, 1, 'create', 'subject', 39, '{\"code\":\"ESP10\",\"name\":\"Edukasyon sa Pagpapakatao 10\",\"description\":\"DepEd Grade 10 Subject\"}', '2026-06-15 23:34:24'),
(578, 18, 'logout', 'user', 18, '[]', '2026-06-15 23:42:00'),
(579, 18, 'login', 'user', 18, '[]', '2026-06-15 23:42:04'),
(580, 1, 'assign', 'folder_teacher', 10, '{\"subject_id\":17,\"teacher_empidno\":\"T007\"}', '2026-06-15 23:51:40'),
(581, 18, 'logout', 'user', 18, '[]', '2026-06-16 01:46:43'),
(582, 1, 'reset_password', 'user', 282, '[]', '2026-06-16 08:30:22'),
(583, 1, 'reset_password', 'user', 282, '[]', '2026-06-16 08:30:22'),
(584, 282, 'login', 'user', 282, '[]', '2026-06-16 08:32:04'),
(585, 282, 'enroll', 'enrollment', 16, '{\"subject_id\":17}', '2026-06-16 08:32:50'),
(586, 18, 'login', 'user', 18, '[]', '2026-06-16 17:19:28'),
(587, 1, 'login', 'user', 1, '[]', '2026-06-16 23:13:57'),
(588, 1, 'logout', 'user', 1, '[]', '2026-06-16 23:17:27'),
(589, 1, 'login', 'user', 1, '[]', '2026-06-16 23:17:33'),
(590, 1, 'logout', 'user', 1, '[]', '2026-06-16 23:17:50'),
(591, 1, 'login', 'user', 1, '[]', '2026-06-16 23:18:02'),
(592, 1, 'login', 'user', 1, '[]', '2026-06-16 23:18:21'),
(593, 1, 'register_user', 'user', 346, '[]', '2026-06-16 23:19:43'),
(594, 1, 'create', 'user', 346, '{\"empidno\":\"T020\",\"name\":\"Girlie Gasataya\",\"email\":\"gasatayagirlie@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-06-16 23:19:43'),
(595, 1, 'logout', 'user', 1, '[]', '2026-06-16 23:20:19'),
(596, 346, 'login', 'user', 346, '[]', '2026-06-16 23:20:29'),
(597, 346, 'update_user', 'user', 346, '[]', '2026-06-16 23:20:41'),
(598, 346, 'update', 'profile', 346, '{\"name\":\"Girlie Gasataya\",\"email\":\"gasatayagirlie04@gmail.com\",\"image\":null}', '2026-06-16 23:20:41'),
(599, 346, 'logout', 'user', 346, '[]', '2026-06-16 23:20:44'),
(600, 346, 'login', 'user', 346, '[]', '2026-06-16 23:21:01'),
(601, 1, 'logout', 'user', 1, '[]', '2026-06-17 00:59:21'),
(602, 1, 'login', 'user', 1, '[]', '2026-06-17 00:59:25'),
(603, 1, 'register_user', 'user', 349, '[]', '2026-06-17 01:00:45'),
(604, 1, 'create', 'user', 349, '{\"empidno\":\"T021\",\"name\":\"Glen Ruzgal\",\"email\":\"ruzgalglenn6@gmail.com\",\"role\":\"teacher\",\"gradeLevel\":\"\",\"imagePath\":null}', '2026-06-17 01:00:45'),
(605, 18, 'login', 'user', 18, '[]', '2026-06-17 17:28:30'),
(606, 18, 'logout', 'user', 18, '[]', '2026-06-18 01:05:52'),
(607, 18, 'login', 'user', 18, '[]', '2026-06-18 01:10:16'),
(608, 18, 'logout', 'user', 18, '[]', '2026-06-18 01:11:18'),
(609, 18, 'login', 'user', 18, '[]', '2026-06-18 01:58:53'),
(610, 18, 'login', 'user', 18, '[]', '2026-06-18 17:37:29'),
(611, 1, 'login', 'user', 1, '[]', '2026-06-20 00:09:58'),
(612, 349, 'login', 'user', 349, '[]', '2026-06-21 09:15:16'),
(613, 18, 'login', 'user', 18, '[]', '2026-06-21 18:21:52'),
(614, 18, 'logout', 'user', 18, '[]', '2026-06-22 01:35:32'),
(615, 18, 'login', 'user', 18, '[]', '2026-06-22 01:40:33'),
(616, 18, 'logout', 'user', 18, '[]', '2026-06-22 02:09:19'),
(617, 18, 'login', 'user', 18, '[]', '2026-06-22 19:07:24'),
(618, 18, 'login', 'user', 18, '[]', '2026-06-23 17:59:09'),
(619, 18, 'logout', 'user', 18, '[]', '2026-06-24 02:03:52'),
(620, 18, 'login', 'user', 18, '[]', '2026-06-24 17:49:21'),
(621, 18, 'login', 'user', 18, '[]', '2026-06-25 02:15:26'),
(622, 18, 'logout', 'user', 18, '[]', '2026-06-25 02:57:20'),
(623, 18, 'login', 'user', 18, '[]', '2026-06-25 17:24:47'),
(624, 18, 'logout', 'user', 18, '[]', '2026-06-26 02:27:51'),
(625, 349, 'login', 'user', 349, '[]', '2026-06-26 04:39:08'),
(626, 349, 'logout', 'user', 349, '[]', '2026-06-26 04:39:49'),
(627, 29, 'login', 'user', 29, '[]', '2026-06-27 02:37:16'),
(628, 18, 'login', 'user', 18, '[]', '2026-06-28 17:41:24'),
(629, 346, 'login', 'user', 346, '[]', '2026-06-28 22:37:36'),
(630, 346, 'change_password', 'user', 346, '[]', '2026-06-28 22:38:54'),
(631, 346, 'update_user', 'user', 346, '[]', '2026-06-28 22:39:28'),
(632, 346, 'update', 'profile', 346, '{\"name\":\"Girlie Gasataya\",\"email\":\"gasatayagirlie04@gmail.com\",\"image\":null}', '2026-06-28 22:39:28'),
(633, 346, 'update_user', 'user', 346, '[]', '2026-06-28 22:39:45'),
(634, 346, 'update', 'profile', 346, '{\"name\":\"Girlie Gasataya\",\"email\":\"gasatayagirlie04@gmail.com\",\"image\":null}', '2026-06-28 22:39:45'),
(635, 346, 'update_user', 'user', 346, '[]', '2026-06-28 22:40:28'),
(636, 346, 'update', 'profile', 346, '{\"name\":\"Girlie Gasataya\",\"email\":\"gasatayagirlie04@gmail.com\",\"image\":\"assets/images/user_1782711629_6e080035c700252c.jpg\"}', '2026-06-28 22:40:28'),
(637, 18, 'logout', 'user', 18, '[]', '2026-06-28 22:40:31'),
(638, 346, 'login', 'user', 346, '[]', '2026-06-28 22:42:09'),
(639, 346, 'logout', 'user', 346, '[]', '2026-06-28 22:44:59'),
(640, 18, 'login', 'user', 18, '[]', '2026-06-28 22:45:05'),
(641, 18, 'logout', 'user', 18, '[]', '2026-06-29 02:01:14'),
(642, 18, 'login', 'user', 18, '[]', '2026-06-29 17:29:13'),
(643, 18, 'logout', 'user', 18, '[]', '2026-06-30 01:41:34'),
(644, 18, 'login', 'user', 18, '[]', '2026-06-30 17:50:33'),
(645, 1, 'login', 'user', 1, '[]', '2026-07-01 19:38:43'),
(646, 1, 'logout', 'user', 1, '[]', '2026-07-01 19:44:49'),
(647, 1, 'login', 'user', 1, '[]', '2026-07-01 19:45:11'),
(648, 1, 'logout', 'user', 1, '[]', '2026-07-01 19:51:06'),
(649, 19, 'login', 'user', 19, '[]', '2026-07-01 19:51:32'),
(650, 19, 'logout', 'user', 19, '[]', '2026-07-01 20:05:09'),
(651, 1, 'login', 'user', 1, '[]', '2026-07-01 20:09:04'),
(652, 1, 'logout', 'user', 1, '[]', '2026-07-01 20:09:15'),
(653, 1, 'login', 'user', 1, '[]', '2026-07-01 20:17:54'),
(654, 1, 'logout', 'user', 1, '[]', '2026-07-01 22:00:48'),
(655, 1, 'login', 'user', 1, '[]', '2026-07-01 23:02:32'),
(656, 1, 'logout', 'user', 1, '[]', '2026-07-02 01:04:56'),
(657, 19, 'login', 'user', 19, '[]', '2026-07-02 01:05:11'),
(658, 19, 'logout', 'user', 19, '[]', '2026-07-02 01:05:52'),
(659, 1, 'login', 'user', 1, '[]', '2026-07-02 01:06:07'),
(660, 1, 'logout', 'user', 1, '[]', '2026-07-02 01:06:47'),
(661, 1, 'login', 'user', 1, '[]', '2026-07-02 01:16:53'),
(662, 1, 'logout', 'user', 1, '[]', '2026-07-02 01:19:06');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `shelf_location` varchar(50) DEFAULT NULL,
  `status` enum('available','unavailable','damaged','lost') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_borrowings`
--

CREATE TABLE `book_borrowings` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `borrowed_at` timestamp NULL DEFAULT current_timestamp(),
  `due_date` date NOT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinic_visits`
--

CREATE TABLE `clinic_visits` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time DEFAULT NULL,
  `complaint` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `medication_given` text DEFAULT NULL,
  `action_taken` enum('treated','referred','sent_home','rested','other') DEFAULT 'treated',
  `attended_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_201_files`
--

CREATE TABLE `employee_201_files` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `sex` enum('male','female') DEFAULT NULL,
  `civil_status` enum('single','married','widowed','separated','annulled') DEFAULT NULL,
  `citizenship` varchar(50) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `gsis_no` varchar(50) DEFAULT NULL,
  `pagibig_no` varchar(50) DEFAULT NULL,
  `philhealth_no` varchar(50) DEFAULT NULL,
  `sss_no` varchar(50) DEFAULT NULL,
  `tin_no` varchar(50) DEFAULT NULL,
  `agency_employee_no` varchar(50) DEFAULT NULL,
  `residential_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `telephone_no` varchar(20) DEFAULT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `spouse_name` varchar(100) DEFAULT NULL,
  `spouse_occupation` varchar(50) DEFAULT NULL,
  `spouse_employer` varchar(100) DEFAULT NULL,
  `spouse_business_address` text DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(50) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(50) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `employment_status` enum('permanent','temporary','contractual','substitute','part_time') DEFAULT 'permanent',
  `position_title` varchar(100) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `salary_grade` varchar(20) DEFAULT NULL,
  `monthly_salary` decimal(12,2) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `recognitions` text DEFAULT NULL,
  `organizations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `document_name` varchar(100) DEFAULT NULL,
  `document_type` enum('pds','certificate','transcript','clearance','contract','appointment','evaluation','others') DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_education`
--

CREATE TABLE `employee_education` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `level` enum('elementary','secondary','vocational','college','graduate_studies') NOT NULL,
  `school_name` varchar(150) DEFAULT NULL,
  `degree_course` varchar(100) DEFAULT NULL,
  `year_graduated` varchar(10) DEFAULT NULL,
  `highest_level` varchar(50) DEFAULT NULL,
  `year_attended_from` varchar(10) DEFAULT NULL,
  `year_attended_to` varchar(10) DEFAULT NULL,
  `honors_received` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_trainings`
--

CREATE TABLE `employee_trainings` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `type_of_ld` enum('managerial','supervisory','technical','others') DEFAULT NULL,
  `conducted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_work_experience`
--

CREATE TABLE `employee_work_experience` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `position_title` varchar(100) DEFAULT NULL,
  `department_office` varchar(100) DEFAULT NULL,
  `monthly_salary` decimal(12,2) DEFAULT NULL,
  `salary_grade` varchar(20) DEFAULT NULL,
  `status_of_appointment` enum('permanent','temporary','contractual','substitute','part_time') DEFAULT NULL,
  `gov_service` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `enrolled_at` datetime DEFAULT current_timestamp(),
  `retention_status` enum('Promoted','Retained','Irregular') DEFAULT 'Promoted',
  `retention_reason` text DEFAULT NULL,
  `retention_school_year` varchar(20) DEFAULT NULL,
  `retention_updated_at` timestamp NULL DEFAULT NULL,
  `retention_updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`, `enrolled_at`, `retention_status`, `retention_reason`, `retention_school_year`, `retention_updated_at`, `retention_updated_by`) VALUES
(1, 6, 1, '2025-12-20 01:58:00', 'Promoted', NULL, NULL, NULL, NULL),
(2, 13, 2, '2025-12-28 00:59:41', 'Promoted', NULL, NULL, NULL, NULL),
(3, 82, 8, '2026-06-15 00:01:12', 'Promoted', NULL, NULL, NULL, NULL),
(4, 176, 8, '2026-06-15 00:08:46', 'Promoted', NULL, NULL, NULL, NULL),
(5, 311, 8, '2026-06-15 00:11:26', 'Promoted', NULL, NULL, NULL, NULL),
(6, 34, 8, '2026-06-15 00:20:44', 'Promoted', NULL, NULL, NULL, NULL),
(7, 288, 8, '2026-06-15 00:22:39', 'Promoted', NULL, NULL, NULL, NULL),
(8, 70, 8, '2026-06-15 00:23:22', 'Promoted', NULL, NULL, NULL, NULL),
(9, 144, 8, '2026-06-15 00:23:33', 'Promoted', NULL, NULL, NULL, NULL),
(10, 127, 8, '2026-06-15 00:24:22', 'Promoted', NULL, NULL, NULL, NULL),
(11, 99, 8, '2026-06-15 00:27:05', 'Promoted', NULL, NULL, NULL, NULL),
(12, 303, 8, '2026-06-15 00:31:14', 'Promoted', NULL, NULL, NULL, NULL),
(13, 140, 8, '2026-06-15 00:33:33', 'Promoted', NULL, NULL, NULL, NULL),
(14, 300, 8, '2026-06-15 00:34:20', 'Promoted', NULL, NULL, NULL, NULL),
(15, 46, 8, '2026-06-15 00:36:37', 'Promoted', NULL, NULL, NULL, NULL),
(16, 282, 17, '2026-06-16 08:32:50', 'Promoted', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_types`
--

CREATE TABLE `fee_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `frequency` enum('monthly','quarterly','semester','yearly','one_time') DEFAULT 'one_time',
  `grade_level` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fee_types`
--

INSERT INTO `fee_types` (`id`, `name`, `description`, `amount`, `is_recurring`, `frequency`, `grade_level`, `status`, `created_at`) VALUES
(1, 'Tuition Fee', 'Annual tuition fee', 8000.00, 1, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(2, 'Miscellaneous', 'Miscellaneous school fees', 2000.00, 1, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(3, 'Others', 'Other fees / books', 550.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(4, 'Early Bird Discount', 'Discount for early enrollment', 0.00, 0, 'one_time', NULL, 'active', '2026-06-08 07:48:26'),
(5, 'Academic Scholar', 'Academic scholarship discount', 0.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(6, 'Sports Discount', 'Sports scholarship discount', 0.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(7, 'ESC Grant', 'Education Service Contract grant', 9000.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(8, 'SHS Voucher', 'Senior High School voucher', 0.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(9, 'TLCA Scholar', 'TLCA scholarship discount', 0.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26'),
(10, 'Full Scholar', 'Full scholarship discount', 0.00, 0, 'yearly', NULL, 'active', '2026-06-08 07:48:26');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `stored_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folder_teacher`
--

CREATE TABLE `folder_teacher` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_empidno` varchar(50) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `folder_teacher`
--

INSERT INTO `folder_teacher` (`id`, `subject_id`, `teacher_empidno`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 'T002', 1, '2025-12-20 01:53:47'),
(3, 2, 'T003', 1, '2025-12-20 18:11:36'),
(4, 5, 'T007', 1, '2026-05-11 18:36:45'),
(5, 6, 'T006', 1, '2026-06-08 00:57:07'),
(6, 8, 'T006', 1, '2026-06-14 23:54:11'),
(7, 9, 'T019', 1, '2026-06-14 23:55:11'),
(8, 10, 'T011', 1, '2026-06-14 23:57:19'),
(9, 11, 'T016', 1, '2026-06-15 00:26:50'),
(10, 17, 'T007', 1, '2026-06-15 23:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `activity_grade_total` int(11) DEFAULT 0,
  `quiz_grade_total` int(11) DEFAULT 0,
  `final_grade` int(11) DEFAULT 0,
  `computed_at` datetime DEFAULT current_timestamp(),
  `q1_grade` decimal(5,2) DEFAULT NULL,
  `q2_grade` decimal(5,2) DEFAULT NULL,
  `q3_grade` decimal(5,2) DEFAULT NULL,
  `q4_grade` decimal(5,2) DEFAULT NULL,
  `average_grade` decimal(5,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `activity_grade_total`, `quiz_grade_total`, `final_grade`, `computed_at`, `q1_grade`, `q2_grade`, `q3_grade`, `q4_grade`, `average_grade`, `updated_at`) VALUES
(1, 13, 2, 0, 0, 0, '2025-12-28 06:43:30', 92.00, 90.00, NULL, NULL, 45.50, '2026-03-08 20:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `grade_periods`
--

CREATE TABLE `grade_periods` (
  `id` int(11) NOT NULL,
  `quarter` varchar(2) NOT NULL COMMENT 'Q1, Q2, Q3, Q4',
  `is_enabled` tinyint(1) DEFAULT 0 COMMENT '1 = enabled, 0 = disabled',
  `deadline` datetime DEFAULT NULL COMMENT 'Deadline for grade encoding',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_periods`
--

INSERT INTO `grade_periods` (`id`, `quarter`, `is_enabled`, `deadline`, `created_at`, `updated_at`) VALUES
(1, 'Q1', 0, NULL, '2025-12-28 06:35:33', '2025-12-31 23:06:06'),
(2, 'Q2', 1, NULL, '2025-12-28 06:35:33', '2026-02-13 20:50:28'),
(3, 'Q3', 0, NULL, '2025-12-28 06:35:33', '2025-12-28 06:35:33'),
(4, 'Q4', 0, NULL, '2025-12-28 06:35:33', '2025-12-28 06:35:33');

-- --------------------------------------------------------

--
-- Table structure for table `immunization_records`
--

CREATE TABLE `immunization_records` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `date_administered` date DEFAULT NULL,
  `dose_number` int(11) DEFAULT 1,
  `administering_facility` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT '#',
  `icon` varchar(50) DEFAULT 'bi-info-circle',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `icon`, `is_read`, `created_at`) VALUES
(1, 1, 'Welcome to eLMS Portal!', 'Your workspace has been modernized with premium SaaS templates.', 'dashboard.php', 'bi-info-circle-fill', 1, '2026-07-01 12:37:18'),
(2, 1, 'Academic Term Settings Active', 'Check your subject allocations and grades parameters in the menu.', 'subjects.php', 'bi-calendar-event', 1, '2026-07-01 12:37:18'),
(3, 19, 'Welcome to eLMS Portal!', 'Your workspace has been modernized with premium SaaS templates.', 'dashboard.php', 'bi-info-circle-fill', 0, '2026-07-01 17:05:11'),
(4, 19, 'Academic Term Settings Active', 'Check your subject allocations and grades parameters in the menu.', 'subjects.php', 'bi-calendar-event', 0, '2026-07-01 17:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','check','bank_transfer','online') DEFAULT 'cash',
  `reference_number` varchar(100) DEFAULT NULL,
  `or_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_fee_id`, `student_id`, `amount`, `payment_method`, `reference_number`, `or_number`, `notes`, `received_by`, `received_at`) VALUES
(1, 455, 269, 172.22, 'cash', 'REF-20260701-8680', '244', 'Monthly installment payment.', 1, '2026-07-01 13:01:09'),
(2, 455, 269, 172.22, 'cash', 'REF-20260701-762C', '244', 'Monthly installment payment.', 1, '2026-07-01 13:03:29'),
(3, 455, 269, 133.95, 'cash', 'REF-20260701-8D26', '02154587', 'Monthly installment payment. Month: JULY.', 1, '2026-07-01 13:43:41');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `time_limit_minutes` int(11) DEFAULT 0,
  `max_score` int(11) DEFAULT 100,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `student_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `requires_manual_grading` tinyint(1) DEFAULT 0,
  `manual_points_awarded` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `auto_graded_score` int(11) DEFAULT 0,
  `needs_manual_grading` tinyint(1) DEFAULT 0,
  `submitted_at` datetime DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `question_type` enum('mcq','truefalse','id') DEFAULT NULL,
  `choices_json` text DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `points` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'school_latitude', '10.206639', '2026-06-15 08:03:27'),
(2, 'school_longitude', '122.844056', '2026-06-15 08:03:27'),
(3, 'gps_radius_meters', '100', '2026-04-22 11:02:39'),
(4, 'school_name', 'The Light Christian Academy', '2026-07-01 17:06:37'),
(5, 'school_hero_subtitle', 'Providing quality education rooted in Christian values. Enroll students, manage grades, and enhance educational outcomes.', '2026-07-01 17:06:37'),
(6, 'school_about_text', 'We deliver an integrated digital workspace that connects students, teachers, and administrators. Our system simplifies course planning, progress evaluation, and secure data access.', '2026-07-01 17:06:37'),
(7, 'school_contact_email', 'info@school.edu', '2026-07-01 17:06:37'),
(8, 'school_contact_phone', '+63 912 345 6789', '2026-07-01 17:06:37'),
(9, 'school_contact_address', '123 Academic St, Manila, Philippines', '2026-07-01 17:06:37'),
(10, 'school_logo', 'uploads/logos/1_logo_1782925597.png', '2026-07-01 17:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `subject_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL DEFAULT 'absent',
  `remarks` varchar(255) DEFAULT NULL,
  `marked_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollment_documents`
--

CREATE TABLE `student_enrollment_documents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `document_type` enum('birth_certificate','psa_nso','sf10','peac') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_enrollment_documents`
--

INSERT INTO `student_enrollment_documents` (`id`, `student_id`, `document_type`, `file_name`, `file_path`, `file_size`, `mime_type`, `uploaded_by`, `uploaded_at`) VALUES
(1, 136, 'birth_certificate', 'JANE ROSE ENDOMA PSA.pdf', 'uploads/enrollment_documents/136/birth_certificate_1779760371_87e7d22a.pdf', 3091474, 'application/pdf', 18, '2026-05-26 01:52:51'),
(2, 136, 'sf10', 'JANE ROSE ENDOMA SF 10.jpg', 'uploads/enrollment_documents/136/sf10_1779760371_8a11dbba.jpg', 2366391, 'image/jpeg', 18, '2026-05-26 01:52:51'),
(3, 153, 'birth_certificate', 'JOHN ROY ATIENZA LCR.pdf', 'uploads/enrollment_documents/153/birth_certificate_1779761220_fec973a8.pdf', 1861115, 'application/pdf', 18, '2026-05-26 02:07:00'),
(4, 153, 'sf10', 'JOHN ROY ATIENZA SF 10.pdf', 'uploads/enrollment_documents/153/sf10_1779761220_e60fdcbd.pdf', 1209743, 'application/pdf', 18, '2026-05-26 02:07:00'),
(5, 154, 'birth_certificate', 'RYAN CABANDO PSA.pdf', 'uploads/enrollment_documents/154/birth_certificate_1779761589_d8a20038.pdf', 2322063, 'application/pdf', 18, '2026-05-26 02:13:10'),
(6, 154, 'sf10', 'RYAN CABANDO SF 10.pdf', 'uploads/enrollment_documents/154/sf10_1779761589_e5652cdc.pdf', 1207064, 'application/pdf', 18, '2026-05-26 02:13:10'),
(7, 154, 'birth_certificate', 'RYAN CABANDO PSA.pdf', 'uploads/enrollment_documents/154/birth_certificate_1779761877_65f7d6f1.pdf', 2293290, 'application/pdf', 18, '2026-05-26 02:17:58'),
(8, 154, 'sf10', 'RYAN CABANDO SF 10-.jpg', 'uploads/enrollment_documents/154/sf10_1779761877_38347df2.jpg', 1958439, 'image/jpeg', 18, '2026-05-26 02:17:58'),
(9, 161, 'birth_certificate', 'OLIVOS PSA.pdf', 'uploads/enrollment_documents/161/birth_certificate_1779762507_75119959.pdf', 2675915, 'application/pdf', 18, '2026-05-26 02:28:27'),
(10, 161, 'sf10', 'OLIVOS SF 10.pdf', 'uploads/enrollment_documents/161/sf10_1779762507_dc8ef488.pdf', 1212095, 'application/pdf', 18, '2026-05-26 02:28:27'),
(11, 169, 'birth_certificate', 'RENMAR DELOS REYES PSA.pdf', 'uploads/enrollment_documents/169/birth_certificate_1779763617_95206cde.pdf', 2590659, 'application/pdf', 18, '2026-05-26 02:46:58'),
(12, 169, 'sf10', 'RENMAR DELOS REYES SF 10.pdf', 'uploads/enrollment_documents/169/sf10_1779763617_ee97409a.pdf', 1332743, 'application/pdf', 18, '2026-05-26 02:46:58'),
(13, 170, 'birth_certificate', 'JOHN ART SALVADORA LCR.pdf', 'uploads/enrollment_documents/170/birth_certificate_1779763982_f1b25680.pdf', 1895238, 'application/pdf', 18, '2026-05-26 02:53:03'),
(14, 170, 'sf10', 'JOHN ART SALVADORA SF 10.jpg', 'uploads/enrollment_documents/170/sf10_1779763982_08e84f39.jpg', 2329457, 'image/jpeg', 18, '2026-05-26 02:53:03'),
(15, 184, 'birth_certificate', 'ROY MABAYAN PSA.jpg', 'uploads/enrollment_documents/184/birth_certificate_1779764364_f3c09dc0.jpg', 1599264, 'image/jpeg', 18, '2026-05-26 02:59:24'),
(16, 184, 'sf10', 'ROY MABAYAN SF 10.pdf', 'uploads/enrollment_documents/184/sf10_1779764364_6c1a556c.pdf', 1221453, 'application/pdf', 18, '2026-05-26 02:59:24'),
(17, 38, 'birth_certificate', 'ICIL PEDRO PSA.pdf', 'uploads/enrollment_documents/38/birth_certificate_1779764934_d949e97a.pdf', 990317, 'application/pdf', 18, '2026-05-26 03:08:55'),
(18, 38, 'sf10', 'ICIL PEDRO SF 10.pdf', 'uploads/enrollment_documents/38/sf10_1779764934_37be4afc.pdf', 1221399, 'application/pdf', 18, '2026-05-26 03:08:55'),
(19, 44, 'birth_certificate', 'XYRHEN SALVADOR PSA.jpg', 'uploads/enrollment_documents/44/birth_certificate_1779767948_c203bb76.jpg', 4959586, 'image/jpeg', 18, '2026-05-26 03:59:09'),
(20, 44, 'sf10', 'XYRHEN SALVADOR SF 10.pdf', 'uploads/enrollment_documents/44/sf10_1779767948_c4018851.pdf', 1250313, 'application/pdf', 18, '2026-05-26 03:59:09'),
(21, 56, 'birth_certificate', 'MARY GRACE OÑATE LCR.pdf', 'uploads/enrollment_documents/56/birth_certificate_1779772296_61c1334a.pdf', 1401248, 'application/pdf', 18, '2026-05-26 05:11:37'),
(22, 56, 'sf10', 'MARY GRACE OÑATE SF 10.pdf', 'uploads/enrollment_documents/56/sf10_1779772296_4823c980.pdf', 1379086, 'application/pdf', 18, '2026-05-26 05:11:37'),
(23, 91, 'birth_certificate', 'JOENEL MANDE LCR.pdf', 'uploads/enrollment_documents/91/birth_certificate_1779772789_cda91e7b.pdf', 956410, 'application/pdf', 18, '2026-05-26 05:19:49'),
(24, 91, 'sf10', 'JOENEL MANDE SF 10.pdf', 'uploads/enrollment_documents/91/sf10_1779772789_295c5bfc.pdf', 1226112, 'application/pdf', 18, '2026-05-26 05:19:49'),
(25, 96, 'birth_certificate', 'CATHREYN LABEROS PSA.pdf', 'uploads/enrollment_documents/96/birth_certificate_1779773173_aa3500aa.pdf', 2823635, 'application/pdf', 18, '2026-05-26 05:26:14'),
(26, 96, 'sf10', 'CATHREYN LABEROS SF 10.pdf', 'uploads/enrollment_documents/96/sf10_1779773173_ec23e938.pdf', 1142334, 'application/pdf', 18, '2026-05-26 05:26:14'),
(27, 115, 'birth_certificate', 'JOSHUA GANTALAO LCR.jpg', 'uploads/enrollment_documents/115/birth_certificate_1779773494_113d71c0.jpg', 4475971, 'image/jpeg', 18, '2026-05-26 05:31:34'),
(28, 115, 'sf10', 'JOSHUA GANTALAO SF 10.pdf', 'uploads/enrollment_documents/115/sf10_1779773494_2b34a304.pdf', 1201347, 'application/pdf', 18, '2026-05-26 05:31:34'),
(29, 215, 'birth_certificate', 'g8 EDRIE VARGAS - PSA.pdf', 'uploads/enrollment_documents/215/birth_certificate_1779957846_64a08960.pdf', 1447115, 'application/pdf', 18, '2026-05-28 08:44:06'),
(30, 215, 'sf10', 'GR8 EDRIE  VARGAS SF10.pdf', 'uploads/enrollment_documents/215/sf10_1779957846_a8ed233b.pdf', 1381841, 'application/pdf', 18, '2026-05-28 08:44:06'),
(31, 42, 'birth_certificate', 'GR8 PANES NATHANIEL PSA.pdf', 'uploads/enrollment_documents/42/birth_certificate_1779958154_63994bf9.pdf', 2841616, 'application/pdf', 18, '2026-05-28 08:49:14'),
(32, 42, 'sf10', 'GD8 PANES NATHANIEL  CARD.jpg', 'uploads/enrollment_documents/42/sf10_1779958154_cd0d93ec.jpg', 1608837, 'image/jpeg', 18, '2026-05-28 08:49:14'),
(33, 54, 'birth_certificate', 'GR8 ANGELA ROMANO LCR.pdf', 'uploads/enrollment_documents/54/birth_certificate_1779958329_8a23fb2b.pdf', 2735212, 'application/pdf', 18, '2026-05-28 08:52:09'),
(34, 54, 'sf10', 'GR8 ANGELA ROMANO SF10.pdf', 'uploads/enrollment_documents/54/sf10_1779958329_348afd48.pdf', 1274392, 'application/pdf', 18, '2026-05-28 08:52:09'),
(35, 64, 'birth_certificate', 'ARIES LAROYA LCR.pdf', 'uploads/enrollment_documents/64/birth_certificate_1779958446_be544a0b.pdf', 1405204, 'application/pdf', 18, '2026-05-28 08:54:05'),
(36, 64, 'sf10', 'ARIES LAROYA SF10.jpg', 'uploads/enrollment_documents/64/sf10_1779958446_e4527b8c.jpg', 2440837, 'image/jpeg', 18, '2026-05-28 08:54:05'),
(37, 59, 'birth_certificate', 'SEDRICK LAROYA LCR.pdf', 'uploads/enrollment_documents/59/birth_certificate_1779958586_4e46ec1e.pdf', 2151022, 'application/pdf', 18, '2026-05-28 08:56:26'),
(38, 59, 'sf10', 'SEDRICK LAROYA SF10.pdf', 'uploads/enrollment_documents/59/sf10_1779958586_1670fc16.pdf', 1241866, 'application/pdf', 18, '2026-05-28 08:56:26'),
(39, 114, 'birth_certificate', 'LENGIE BURGADO LCR.jpg', 'uploads/enrollment_documents/114/birth_certificate_1780017305_5ac4b1b1.jpg', 3108414, 'image/jpeg', 18, '2026-05-29 01:15:05'),
(40, 114, 'sf10', 'LENGIE BURGADO SF 10.pdf', 'uploads/enrollment_documents/114/sf10_1780017305_5346c5b9.pdf', 1412146, 'application/pdf', 18, '2026-05-29 01:15:05'),
(41, 113, 'birth_certificate', 'JANMAR CASUYON LCR.pdf', 'uploads/enrollment_documents/113/birth_certificate_1780017704_e08cec80.pdf', 1463224, 'application/pdf', 18, '2026-05-29 01:21:44'),
(42, 113, 'sf10', 'JANMAR CASUYON SF 10.jpg', 'uploads/enrollment_documents/113/sf10_1780017704_2d60f0af.jpg', 2398415, 'image/jpeg', 18, '2026-05-29 01:21:44'),
(43, 112, 'birth_certificate', 'ROAN JAMES ATANQUE PSA.pdf', 'uploads/enrollment_documents/112/birth_certificate_1780017936_0ef10210.pdf', 2195338, 'application/pdf', 18, '2026-05-29 01:25:36'),
(44, 112, 'sf10', 'ROAN JAMES ATANQUE SF 10.pdf', 'uploads/enrollment_documents/112/sf10_1780017936_bab1ad7f.pdf', 1206962, 'application/pdf', 18, '2026-05-29 01:25:36'),
(45, 111, 'birth_certificate', 'JESSICA PELARIN LCR.pdf', 'uploads/enrollment_documents/111/birth_certificate_1780018401_4ba68019.pdf', 1081425, 'application/pdf', 18, '2026-05-29 01:33:21'),
(46, 111, 'sf10', 'JESSICA PELARIN SF 10.pdf', 'uploads/enrollment_documents/111/sf10_1780018401_06a4702c.pdf', 1235045, 'application/pdf', 18, '2026-05-29 01:33:21'),
(47, 110, 'birth_certificate', 'VHANE KACELEI MADRIAGA PSA.pdf', 'uploads/enrollment_documents/110/birth_certificate_1780018750_89a9a587.pdf', 3049080, 'application/pdf', 18, '2026-05-29 01:39:10'),
(48, 110, 'sf10', 'VHANE KACELEI MADRIAGA SF 10.jpg', 'uploads/enrollment_documents/110/sf10_1780018750_dd549b4a.jpg', 2426098, 'image/jpeg', 18, '2026-05-29 01:39:10'),
(49, 109, 'birth_certificate', 'AYAN CARMELINO PSA.pdf', 'uploads/enrollment_documents/109/birth_certificate_1780019105_2b7a116a.pdf', 2542066, 'application/pdf', 18, '2026-05-29 01:45:04'),
(50, 109, 'sf10', 'AYAN CARMELINO SF 10.pdf', 'uploads/enrollment_documents/109/sf10_1780019105_cbbbc47d.pdf', 1213667, 'application/pdf', 18, '2026-05-29 01:45:04'),
(51, 108, 'birth_certificate', 'ENA PACONLA PSA.pdf', 'uploads/enrollment_documents/108/birth_certificate_1780019428_2c949f18.pdf', 2590913, 'application/pdf', 18, '2026-05-29 01:50:28'),
(52, 108, 'sf10', 'ENA PACONLA SF 10.pdf', 'uploads/enrollment_documents/108/sf10_1780019428_75e17f3c.pdf', 1183204, 'application/pdf', 18, '2026-05-29 01:50:28'),
(53, 107, 'birth_certificate', 'RANMARK PATOC LCR.pdf', 'uploads/enrollment_documents/107/birth_certificate_1780019816_f1abae6b.pdf', 1172814, 'application/pdf', 18, '2026-05-29 01:56:56'),
(54, 107, 'sf10', 'RANMARK PATOC SF 10.pdf', 'uploads/enrollment_documents/107/sf10_1780019816_7338d1eb.pdf', 1219568, 'application/pdf', 18, '2026-05-29 01:56:56'),
(55, 106, 'birth_certificate', 'CRISLYN TORREFRANCA PSA.pdf', 'uploads/enrollment_documents/106/birth_certificate_1780020238_ca2a8de6.pdf', 2199135, 'application/pdf', 18, '2026-05-29 02:03:59'),
(56, 106, 'sf10', 'CRISLYN TORREFRANCA SF 10.jpg', 'uploads/enrollment_documents/106/sf10_1780020238_4edd757f.jpg', 2428970, 'image/jpeg', 18, '2026-05-29 02:03:59'),
(57, 105, 'birth_certificate', 'RENZO ESTANIEL PSA.pdf', 'uploads/enrollment_documents/105/birth_certificate_1780020476_50ac9955.pdf', 2191743, 'application/pdf', 18, '2026-05-29 02:07:57'),
(58, 105, 'sf10', 'RENZO ESTANIEL SF 10.pdf', 'uploads/enrollment_documents/105/sf10_1780020476_db765112.pdf', 1392470, 'application/pdf', 18, '2026-05-29 02:07:57'),
(59, 225, 'birth_certificate', 'LYN DABLIO LCR.pdf', 'uploads/enrollment_documents/225/birth_certificate_1780022269.pdf', 1120156, 'application/pdf', 18, '2026-05-29 02:37:50'),
(60, 225, 'sf10', 'LYN DABLIO SF 10.pdf', 'uploads/enrollment_documents/225/sf10_1780022269.pdf', 1227620, 'application/pdf', 18, '2026-05-29 02:37:50'),
(61, 226, 'birth_certificate', 'Benz Baylon PSA.jpg', 'uploads/enrollment_documents/226/birth_certificate_1780026119.jpg', 1683430, 'image/jpeg', 18, '2026-05-29 03:41:59'),
(62, 226, 'sf10', 'Benz Baylon SF10.jpg', 'uploads/enrollment_documents/226/sf10_1780026119.jpg', 2314189, 'image/jpeg', 18, '2026-05-29 03:41:59'),
(63, 235, 'birth_certificate', 'Eunice Cañon LCR.pdf', 'uploads/enrollment_documents/235/birth_certificate_1780038196.pdf', 1676589, 'application/pdf', 18, '2026-05-29 07:03:16'),
(64, 235, 'sf10', 'Eunice Cañon SF 10.pdf', 'uploads/enrollment_documents/235/sf10_1780038196.pdf', 1218182, 'application/pdf', 18, '2026-05-29 07:03:16'),
(65, 241, 'birth_certificate', 'Eunice Nicole Serantes PSA.pdf', 'uploads/enrollment_documents/241/birth_certificate_1780039546.pdf', 1675574, 'application/pdf', 18, '2026-05-29 07:25:46'),
(66, 241, 'sf10', 'ALthea Nicole Serantes Sf 10.pdf', 'uploads/enrollment_documents/241/sf10_1780039546.pdf', 1222869, 'application/pdf', 18, '2026-05-29 07:25:46'),
(67, 244, 'birth_certificate', 'Janine Mary Enegrio PSA.pdf', 'uploads/enrollment_documents/244/birth_certificate_1780040578.pdf', 2315344, 'application/pdf', 18, '2026-05-29 07:42:58'),
(68, 244, 'sf10', 'Janine Mary Enegrio SF 10.pdf', 'uploads/enrollment_documents/244/sf10_1780040578.pdf', 1375043, 'application/pdf', 18, '2026-05-29 07:42:58'),
(69, 230, 'sf10', 'Ma. Teresa Sarad SF 10.pdf', 'uploads/enrollment_documents/230/sf10_1780041118_d5f30351.pdf', 1231230, 'application/pdf', 18, '2026-05-29 07:51:58'),
(70, 117, 'birth_certificate', 'Jade Vensam Cañedo LCR.pdf', 'uploads/enrollment_documents/117/birth_certificate_1780281886_b8bf8ab5.pdf', 1739592, 'application/pdf', 18, '2026-06-01 02:44:47'),
(71, 117, 'sf10', 'Jade Vensam Cañedo SF 10.pdf', 'uploads/enrollment_documents/117/sf10_1780281886_3bce9ebe.pdf', 1408793, 'application/pdf', 18, '2026-06-01 02:44:47'),
(72, 251, 'birth_certificate', 'CRISTOPHER BAÑEZ PSA.pdf', 'uploads/enrollment_documents/251/birth_certificate_1780282438_d89e0f06.pdf', 1426924, 'application/pdf', 18, '2026-06-01 02:53:58'),
(73, 251, 'sf10', 'CRISTOPHER BAÑEZ SF 10.pdf', 'uploads/enrollment_documents/251/sf10_1780282438_624d9d5c.pdf', 1386832, 'application/pdf', 18, '2026-06-01 02:53:58'),
(74, 250, 'birth_certificate', 'JOHN CYZAR GARCIA PSA.pdf', 'uploads/enrollment_documents/250/birth_certificate_1780284646_cdcc7eb9.pdf', 2680257, 'application/pdf', 18, '2026-06-01 03:30:47'),
(75, 250, 'sf10', 'JOHN CYZAR GARCIA SF 10.pdf', 'uploads/enrollment_documents/250/sf10_1780284646_4b3f0dc1.pdf', 1374692, 'application/pdf', 18, '2026-06-01 03:30:47'),
(76, 88, 'birth_certificate', 'CARLAINE AMANTE PSA.pdf', 'uploads/enrollment_documents/88/birth_certificate_1780284819_cc4c46a7.pdf', 2023518, 'application/pdf', 18, '2026-06-01 03:33:39'),
(77, 88, 'sf10', 'CARLAINE AMANTE SF10.jpg', 'uploads/enrollment_documents/88/sf10_1780284819_1b56f836.jpg', 2153865, 'image/jpeg', 18, '2026-06-01 03:33:39'),
(78, 75, 'birth_certificate', 'SHAINNIE ARANTE LCR.pdf', 'uploads/enrollment_documents/75/birth_certificate_1780284986_9b8a378e.pdf', 1217038, 'application/pdf', 18, '2026-06-01 03:36:26'),
(79, 75, 'sf10', 'SHAINNIE ARANTE SF10.pdf', 'uploads/enrollment_documents/75/sf10_1780284986_133e862b.pdf', 1217815, 'application/pdf', 18, '2026-06-01 03:36:26'),
(80, 89, 'birth_certificate', 'MEGAN CABANDO PSA.pdf', 'uploads/enrollment_documents/89/birth_certificate_1780285551_5e47d306.pdf', 2811817, 'application/pdf', 18, '2026-06-01 03:45:51'),
(81, 89, 'sf10', 'MEGAN CABANDO SF 10.pdf', 'uploads/enrollment_documents/89/sf10_1780285551_4a2f04f3.pdf', 1211365, 'application/pdf', 18, '2026-06-01 03:45:51'),
(82, 137, 'birth_certificate', 'RYLE CORDOVA PSA.pdf', 'uploads/enrollment_documents/137/birth_certificate_1780285690_4a4a2fc3.pdf', 1951783, 'application/pdf', 18, '2026-06-01 03:48:10'),
(83, 137, 'sf10', 'RYLE CORDOVA SF 10.pdf', 'uploads/enrollment_documents/137/sf10_1780285690_aba600bd.pdf', 1418415, 'application/pdf', 18, '2026-06-01 03:48:10'),
(84, 133, 'birth_certificate', 'RINZ MARK DEMANDANTE LCR.pdf', 'uploads/enrollment_documents/133/birth_certificate_1780285921_b15858a5.pdf', 1719109, 'application/pdf', 18, '2026-06-01 03:52:01'),
(85, 133, 'sf10', 'RINZ MARK DEMANDANTE SF 10.pdf', 'uploads/enrollment_documents/133/sf10_1780285921_f64eb4b1.pdf', 1209088, 'application/pdf', 18, '2026-06-01 03:52:01'),
(86, 262, 'birth_certificate', 'GEROLA PSA.pdf', 'uploads/enrollment_documents/262/birth_certificate_1780286555_b4469e0f.pdf', 1525638, 'application/pdf', 18, '2026-06-01 04:02:35'),
(87, 262, 'sf10', 'GEROLA SF 10.pdf', 'uploads/enrollment_documents/262/sf10_1780286555_ef08f865.pdf', 1423336, 'application/pdf', 18, '2026-06-01 04:02:35'),
(88, 152, 'birth_certificate', 'CRISLYN TORREFRANCA PSA.pdf', 'uploads/enrollment_documents/152/birth_certificate_1780293308_7a2eefea.pdf', 2199135, 'application/pdf', 18, '2026-06-01 05:55:08'),
(89, 152, 'sf10', 'CHRISTOFER LAZARTE SF 10.pdf', 'uploads/enrollment_documents/152/sf10_1780293308_bce19f96.pdf', 1407548, 'application/pdf', 18, '2026-06-01 05:55:08'),
(90, 68, 'birth_certificate', 'REGY MAGTOLIS PSA.pdf', 'uploads/enrollment_documents/68/birth_certificate_1780295022_35e78da9.pdf', 2143787, 'application/pdf', 18, '2026-06-01 06:23:43'),
(91, 68, 'sf10', 'REGY MAGTOLIS SF10.pdf', 'uploads/enrollment_documents/68/sf10_1780295022_32dfb2c4.pdf', 1237736, 'application/pdf', 18, '2026-06-01 06:23:43'),
(92, 66, 'birth_certificate', 'RHEA FAITH OGAHAYON LCR.pdf', 'uploads/enrollment_documents/66/birth_certificate_1780295113_ffba90b6.pdf', 1071976, 'application/pdf', 18, '2026-06-01 06:25:14'),
(93, 66, 'sf10', 'RHEA FAITH OGAHAYON SF10.pdf', 'uploads/enrollment_documents/66/sf10_1780295113_cc88fc93.pdf', 1234220, 'application/pdf', 18, '2026-06-01 06:25:14'),
(94, 87, 'birth_certificate', 'CHLOE PAPAS PSA.pdf', 'uploads/enrollment_documents/87/birth_certificate_1780295171_a805be86.pdf', 2567534, 'application/pdf', 18, '2026-06-01 06:26:12'),
(95, 87, 'sf10', 'CHLOE PAPAS SF10.pdf', 'uploads/enrollment_documents/87/sf10_1780295171_bee0db79.pdf', 1222072, 'application/pdf', 18, '2026-06-01 06:26:12'),
(96, 86, 'birth_certificate', 'ALEXIE SAHNE POBREZA LCR.pdf', 'uploads/enrollment_documents/86/birth_certificate_1780295452_99433ef4.pdf', 2238666, 'application/pdf', 18, '2026-06-01 06:30:53'),
(97, 86, 'sf10', 'ALEXIE SHANE POBREZA SF10.pdf', 'uploads/enrollment_documents/86/sf10_1780295452_71feed5f.pdf', 1231549, 'application/pdf', 18, '2026-06-01 06:30:53'),
(98, 143, 'birth_certificate', 'CYRIL JHON SARASA LCR.jpg', 'uploads/enrollment_documents/143/birth_certificate_1780297919_176c7f63.jpg', 4262652, 'image/jpeg', 18, '2026-06-01 07:12:00'),
(99, 143, 'sf10', 'CYRIL JHON SARASA SF 10.pdf', 'uploads/enrollment_documents/143/sf10_1780297919_25292bc2.pdf', 1234352, 'application/pdf', 18, '2026-06-01 07:12:00'),
(100, 81, 'birth_certificate', 'JAIRUS SARASA PSA.pdf', 'uploads/enrollment_documents/81/birth_certificate_1780298560_26967406.pdf', 2786869, 'application/pdf', 18, '2026-06-01 07:22:41'),
(101, 81, 'sf10', 'JAIRUS SARASA SF10.pdf', 'uploads/enrollment_documents/81/sf10_1780298560_a7ee0ee8.pdf', 1361901, 'application/pdf', 18, '2026-06-01 07:22:41'),
(102, 62, 'birth_certificate', 'IAN JAMES JAYME PSA.pdf', 'uploads/enrollment_documents/62/birth_certificate_1780299117_bd31f8bb.pdf', 2409776, 'application/pdf', 18, '2026-06-01 07:31:57'),
(103, 62, 'sf10', 'IAN JAMES JAYME SF 10.pdf', 'uploads/enrollment_documents/62/sf10_1780299117_96a990fd.pdf', 1368745, 'application/pdf', 18, '2026-06-01 07:31:57'),
(104, 65, 'birth_certificate', 'EDUARDO JAYME LCR.pdf', 'uploads/enrollment_documents/65/birth_certificate_1780299663_38ea0110.pdf', 1447656, 'application/pdf', 18, '2026-06-01 07:41:04'),
(105, 65, 'sf10', 'EDUARDO JAYME SF 10.pdf', 'uploads/enrollment_documents/65/sf10_1780299663_51a3a714.pdf', 1399167, 'application/pdf', 18, '2026-06-01 07:41:04'),
(106, 51, 'birth_certificate', 'ARMAND JAY PILAR PSA.pdf', 'uploads/enrollment_documents/51/birth_certificate_1780300136_db5b96ca.pdf', 1905176, 'application/pdf', 18, '2026-06-01 07:48:57'),
(107, 51, 'sf10', 'ARMAND JAY PILAR SF 10.pdf', 'uploads/enrollment_documents/51/sf10_1780300136_96584613.pdf', 1354703, 'application/pdf', 18, '2026-06-01 07:48:57'),
(108, 74, 'birth_certificate', 'JOROZ GALVEZ PSA.jpg', 'uploads/enrollment_documents/74/birth_certificate_1780300730_07a054a2.jpg', 2902023, 'image/jpeg', 18, '2026-06-01 07:58:51'),
(109, 74, 'sf10', 'JOROZ GALVEZ SF 10.pdf', 'uploads/enrollment_documents/74/sf10_1780300730_dae7bfa7.pdf', 1379277, 'application/pdf', 18, '2026-06-01 07:58:51'),
(110, 83, 'sf10', 'CRISTIAN CASUYON SF 10.pdf', 'uploads/enrollment_documents/83/sf10_1780301387_0f0d386c.pdf', 1371415, 'application/pdf', 18, '2026-06-01 08:09:47'),
(111, 116, 'birth_certificate', 'JONALYN GANAGANAG LCR.pdf', 'uploads/enrollment_documents/116/birth_certificate_1780301690_ef348d67.pdf', 1772792, 'application/pdf', 18, '2026-06-01 08:14:50'),
(112, 116, 'sf10', 'JONALYN GANAGANAG SF 10.pdf', 'uploads/enrollment_documents/116/sf10_1780301690_8e23684e.pdf', 1280356, 'application/pdf', 18, '2026-06-01 08:14:50'),
(113, 49, 'birth_certificate', 'FRANCISCO PASUIT LCR.pdf', 'uploads/enrollment_documents/49/birth_certificate_1780304485_fc3a02ad.pdf', 1984127, 'application/pdf', 18, '2026-06-01 09:01:25'),
(114, 49, 'sf10', 'FRANCISCO PASUIT SF 10.pdf', 'uploads/enrollment_documents/49/sf10_1780304485_257e3983.pdf', 1315375, 'application/pdf', 18, '2026-06-01 09:01:26'),
(115, 277, 'sf10', 'JOHN LUIS ARNAEZ SF 10.pdf', 'uploads/enrollment_documents/277/sf10_1780458927_a4bed76f.pdf', 933779, 'application/pdf', 18, '2026-06-03 03:55:27'),
(116, 281, 'birth_certificate', 'KATE SARASA LCR.pdf', 'uploads/enrollment_documents/281/birth_certificate_1780466140_8cc5ba72.pdf', 1451663, 'application/pdf', 18, '2026-06-03 05:55:40'),
(117, 281, 'sf10', 'KATE SARASA SF 10.pdf', 'uploads/enrollment_documents/281/sf10_1780466140_fd0c1d7e.pdf', 1412181, 'application/pdf', 18, '2026-06-03 05:55:40'),
(118, 257, 'birth_certificate', 'JAMES SANDIG PSA.pdf', 'uploads/enrollment_documents/257/birth_certificate_1780466438_672601fc.pdf', 2243491, 'application/pdf', 18, '2026-06-03 06:00:38'),
(119, 257, 'sf10', 'JAMES SANDIG SF 10.pdf', 'uploads/enrollment_documents/257/sf10_1780466438_fc6c3933.pdf', 1365774, 'application/pdf', 18, '2026-06-03 06:00:38'),
(120, 263, 'birth_certificate', 'GREYCEL ESTORCO LCR.pdf', 'uploads/enrollment_documents/263/birth_certificate_1780466755_f9377dca.pdf', 1041602, 'application/pdf', 18, '2026-06-03 06:05:55'),
(121, 263, 'sf10', 'GREYCEL ESTORCO SF 10.pdf', 'uploads/enrollment_documents/263/sf10_1780466755_e04f0c8c.pdf', 1348478, 'application/pdf', 18, '2026-06-03 06:05:55'),
(122, 264, 'birth_certificate', 'JOHANN CASTILLO PSA.pdf', 'uploads/enrollment_documents/264/birth_certificate_1780469033_5c44a0c0.pdf', 2111692, 'application/pdf', 18, '2026-06-03 06:43:53'),
(123, 264, 'sf10', 'JOHANN CASTILLO SF 10.pdf', 'uploads/enrollment_documents/264/sf10_1780469033_c73bc395.pdf', 1294764, 'application/pdf', 18, '2026-06-03 06:43:53'),
(124, 268, 'birth_certificate', 'RUNA MAE ABAWAG PSA.pdf', 'uploads/enrollment_documents/268/birth_certificate_1780469337_2a61afd4.pdf', 1601091, 'application/pdf', 18, '2026-06-03 06:48:57'),
(125, 268, 'sf10', 'RUNA MAE ABAWAG SF 10.pdf', 'uploads/enrollment_documents/268/sf10_1780469337_898ae58f.pdf', 1307005, 'application/pdf', 18, '2026-06-03 06:48:57'),
(126, 139, 'birth_certificate', 'TERESA BARCELONA LCR.pdf', 'uploads/enrollment_documents/139/birth_certificate_1780469662_0d4e0f84.pdf', 1040070, 'application/pdf', 18, '2026-06-03 06:54:22'),
(127, 139, 'sf10', 'TERESA BARCELONA SF 10.pdf', 'uploads/enrollment_documents/139/sf10_1780469662_8fe3f3a8.pdf', 1331898, 'application/pdf', 18, '2026-06-03 06:54:22'),
(128, 50, 'birth_certificate', 'KOREN JOY PALATA PSA.pdf', 'uploads/enrollment_documents/50/birth_certificate_1780471428_df1dd217.pdf', 2100549, 'application/pdf', 18, '2026-06-03 07:23:48'),
(129, 50, 'sf10', 'KOREN JOY PALATA SF 10.pdf', 'uploads/enrollment_documents/50/sf10_1780471428_589b2501.pdf', 1470357, 'application/pdf', 18, '2026-06-03 07:23:48'),
(130, 216, 'birth_certificate', 'JODEA EVE SIBONGGA PSA.jpg', 'uploads/enrollment_documents/216/birth_certificate_1780476716_cdbd66ab.jpg', 2782283, 'image/jpeg', 18, '2026-06-03 08:51:55'),
(131, 216, 'sf10', 'JODEA EVE SIBONGA CARD.pdf', 'uploads/enrollment_documents/216/sf10_1780476716_8b3ea1b8.pdf', 717669, 'application/pdf', 18, '2026-06-03 08:51:55'),
(132, 229, 'birth_certificate', 'SHEILA MAE SARAD LCR.pdf', 'uploads/enrollment_documents/229/birth_certificate_1780477075_520d219a.pdf', 1239262, 'application/pdf', 18, '2026-06-03 08:57:54'),
(133, 229, 'sf10', 'SHEILA MAE SARAD SF 10.pdf', 'uploads/enrollment_documents/229/sf10_1780477075_49f61516.pdf', 1359140, 'application/pdf', 18, '2026-06-03 08:57:54'),
(134, 52, 'birth_certificate', 'JERRYME SARASA PSA.pdf', 'uploads/enrollment_documents/52/birth_certificate_1780543117_1967e404.pdf', 2231202, 'application/pdf', 18, '2026-06-04 03:18:37'),
(135, 52, 'sf10', 'JERRYME SARASA SF 10.pdf', 'uploads/enrollment_documents/52/sf10_1780543117_2824326c.pdf', 1372077, 'application/pdf', 18, '2026-06-04 03:18:37'),
(136, 57, 'birth_certificate', 'JENLY POLIDO PSA.pdf', 'uploads/enrollment_documents/57/birth_certificate_1780543413_c9785e78.pdf', 2284876, 'application/pdf', 18, '2026-06-04 03:23:34'),
(137, 57, 'sf10', 'JENLY POLIDO SF 10.pdf', 'uploads/enrollment_documents/57/sf10_1780543413_730a5343.pdf', 1393743, 'application/pdf', 18, '2026-06-04 03:23:34'),
(138, 58, 'birth_certificate', 'ATHENA JAVIER PSA.pdf', 'uploads/enrollment_documents/58/birth_certificate_1780543666_02ec1ec5.pdf', 2442795, 'application/pdf', 18, '2026-06-04 03:27:46'),
(139, 58, 'sf10', 'ATHENA JAVIER SF 10.pdf', 'uploads/enrollment_documents/58/sf10_1780543666_c5c7e362.pdf', 1389254, 'application/pdf', 18, '2026-06-04 03:27:46'),
(140, 90, 'birth_certificate', 'DWAYNE AREGLASO PSA.pdf', 'uploads/enrollment_documents/90/birth_certificate_1780543978_d3e2596c.pdf', 2129032, 'application/pdf', 18, '2026-06-04 03:32:58'),
(141, 90, 'sf10', 'DWAYNE AREGLADO SF 10.pdf', 'uploads/enrollment_documents/90/sf10_1780543978_ad821fa2.pdf', 1350124, 'application/pdf', 18, '2026-06-04 03:32:58'),
(142, 125, 'sf10', 'BEN LAWRENZ PANCHO SF 10.pdf', 'uploads/enrollment_documents/125/sf10_1780545651_d73edd66.pdf', 1375406, 'application/pdf', 18, '2026-06-04 04:00:52'),
(143, 131, 'birth_certificate', 'WINNIE MAE ROSALWS PSA.pdf', 'uploads/enrollment_documents/131/birth_certificate_1780551245_11b3058e.pdf', 2680218, 'application/pdf', 18, '2026-06-04 05:34:05'),
(144, 131, 'sf10', 'WINNIE MAE ROSALES SF 10.pdf', 'uploads/enrollment_documents/131/sf10_1780551245_08a0d068.pdf', 1392481, 'application/pdf', 18, '2026-06-04 05:34:05'),
(145, 132, 'birth_certificate', 'REGINE ZARCEDO LCR.pdf', 'uploads/enrollment_documents/132/birth_certificate_1780551557_5c749df3.pdf', 1469545, 'application/pdf', 18, '2026-06-04 05:39:16'),
(146, 132, 'sf10', 'REGINE ZARCEDO SF 10.pdf', 'uploads/enrollment_documents/132/sf10_1780551557_3689ca11.pdf', 1399320, 'application/pdf', 18, '2026-06-04 05:39:16'),
(147, 155, 'birth_certificate', 'JIPEE PASALGON PSA.pdf', 'uploads/enrollment_documents/155/birth_certificate_1780551891_6d72083d.pdf', 1385263, 'application/pdf', 18, '2026-06-04 05:44:51'),
(148, 155, 'sf10', 'JIPEE PASALGON SF 10.pdf', 'uploads/enrollment_documents/155/sf10_1780551891_5800fb08.pdf', 1389783, 'application/pdf', 18, '2026-06-04 05:44:51'),
(149, 181, 'birth_certificate', 'CRIZ JOHN TREBENIA PSA.pdf', 'uploads/enrollment_documents/181/birth_certificate_1780552224_93974e1f.pdf', 2474612, 'application/pdf', 18, '2026-06-04 05:50:24'),
(150, 181, 'sf10', 'CRIZ JOHN TREBENIA SF 10.pdf', 'uploads/enrollment_documents/181/sf10_1780552224_4b306366.pdf', 1370383, 'application/pdf', 18, '2026-06-04 05:50:24'),
(151, 255, 'birth_certificate', 'JOSEPH TALLAFER  LCR.pdf', 'uploads/enrollment_documents/255/birth_certificate_1780553248_8a83de7c.pdf', 1841948, 'application/pdf', 18, '2026-06-04 06:07:27'),
(152, 255, 'sf10', 'JOSEPH TALLAFER SF 10.pdf', 'uploads/enrollment_documents/255/sf10_1780553248_1dcc9c1b.pdf', 1278504, 'application/pdf', 18, '2026-06-04 06:07:27'),
(153, 254, 'birth_certificate', 'CHAD LOIS SERUELO PSA.pdf', 'uploads/enrollment_documents/254/birth_certificate_1780553614_4b6be3ad.pdf', 2450645, 'application/pdf', 18, '2026-06-04 06:13:34'),
(154, 254, 'sf10', 'CHAD LOIS SERUYELO SF 10.pdf', 'uploads/enrollment_documents/254/sf10_1780553614_a3b01945.pdf', 1429414, 'application/pdf', 18, '2026-06-04 06:13:34'),
(155, 253, 'birth_certificate', 'ZYREXIS CARI-AN PSA.pdf', 'uploads/enrollment_documents/253/birth_certificate_1780553951_da030e4f.pdf', 2468009, 'application/pdf', 18, '2026-06-04 06:19:10'),
(156, 253, 'sf10', 'ZYREXIS CARI-AN SF 10.pdf', 'uploads/enrollment_documents/253/sf10_1780553951_e6ce1e39.pdf', 1382887, 'application/pdf', 18, '2026-06-04 06:19:10'),
(157, 252, 'birth_certificate', 'JOECEL EVANGELIO PSA.pdf', 'uploads/enrollment_documents/252/birth_certificate_1780554243_c55a8396.pdf', 2385547, 'application/pdf', 18, '2026-06-04 06:24:02'),
(158, 252, 'sf10', 'JOECEL EVANGELIO SF 10.pdf', 'uploads/enrollment_documents/252/sf10_1780554243_e77369fc.pdf', 1320225, 'application/pdf', 18, '2026-06-04 06:24:02'),
(159, 249, 'birth_certificate', 'KEA MAE SARAD PSA.pdf', 'uploads/enrollment_documents/249/birth_certificate_1780557450_b0a9f763.pdf', 3320871, 'application/pdf', 18, '2026-06-04 07:17:30'),
(160, 249, 'sf10', 'KEA MAE SARAD SF 10.pdf', 'uploads/enrollment_documents/249/sf10_1780557450_5a482340.pdf', 1397198, 'application/pdf', 18, '2026-06-04 07:17:30'),
(161, 248, 'birth_certificate', 'ROMEO INSIGNE LCR.jpg', 'uploads/enrollment_documents/248/birth_certificate_1780557806_768ee5f8.jpg', 1713973, 'image/jpeg', 18, '2026-06-04 07:23:25'),
(162, 248, 'sf10', 'ROMEO INSIGNE SF 10.pdf', 'uploads/enrollment_documents/248/sf10_1780557806_8e211941.pdf', 1335961, 'application/pdf', 18, '2026-06-04 07:23:25'),
(163, 247, 'birth_certificate', 'ANDREA MAE SARAD PSA.pdf', 'uploads/enrollment_documents/247/birth_certificate_1780881843_81a11fc3.pdf', 2681214, 'application/pdf', 18, '2026-06-08 01:24:04'),
(164, 247, 'sf10', 'ANDREA MAE SARAD SF 10.pdf', 'uploads/enrollment_documents/247/sf10_1780881843_5fdaa4ca.pdf', 1405859, 'application/pdf', 18, '2026-06-08 01:24:04'),
(165, 246, 'birth_certificate', 'SHAN ETHAN CAÑON PSA.pdf', 'uploads/enrollment_documents/246/birth_certificate_1780883374_11395c4b.pdf', 1863473, 'application/pdf', 18, '2026-06-08 01:49:35'),
(166, 246, 'sf10', 'SHAN ETHAN CAÑON SF 10.pdf', 'uploads/enrollment_documents/246/sf10_1780883374_cbdc2c31.pdf', 1380324, 'application/pdf', 18, '2026-06-08 01:49:35'),
(167, 245, 'birth_certificate', 'MERICK JAMES INSIGNE LCR.pdf', 'uploads/enrollment_documents/245/birth_certificate_1780885151_2e8a4521.pdf', 1236486, 'application/pdf', 18, '2026-06-08 02:19:11'),
(168, 245, 'sf10', 'MERICK JAMES INSIGNE SF 10 P2.pdf', 'uploads/enrollment_documents/245/sf10_1780885151_f2e32166.pdf', 1137259, 'application/pdf', 18, '2026-06-08 02:19:11'),
(169, 245, 'sf10', 'MERICK JAMES INSIGNE SF 10  P1.pdf', 'uploads/enrollment_documents/245/sf10_1780885311_0689fcb7.pdf', 1308256, 'application/pdf', 18, '2026-06-08 02:21:51'),
(170, 243, 'birth_certificate', 'JAMEL ALOJAMENTO PSA.pdf', 'uploads/enrollment_documents/243/birth_certificate_1780885740_6652391c.pdf', 2482963, 'application/pdf', 18, '2026-06-08 02:29:00'),
(171, 243, 'sf10', 'JAMEL ALOJAMENTO SF 10.pdf', 'uploads/enrollment_documents/243/sf10_1780885740_fbea3e89.pdf', 1362413, 'application/pdf', 18, '2026-06-08 02:29:00'),
(172, 285, 'birth_certificate', 'AMETHYS BAYLON PSA.pdf', 'uploads/enrollment_documents/285/birth_certificate_1780886060_19327a8d.pdf', 1888228, 'application/pdf', 18, '2026-06-08 02:34:20'),
(173, 285, 'sf10', 'AMETHYS BAYLON SF 10.pdf', 'uploads/enrollment_documents/285/sf10_1780886060_7cbcc7b6.pdf', 1188393, 'application/pdf', 18, '2026-06-08 02:34:20'),
(174, 289, 'birth_certificate', 'JANMARK RUDA LCR.pdf', 'uploads/enrollment_documents/289/birth_certificate_1780887305_42f0fdfa.pdf', 1401649, 'application/pdf', 18, '2026-06-08 02:55:05'),
(175, 289, 'sf10', 'JANMARK RUDA SF 10.pdf', 'uploads/enrollment_documents/289/sf10_1780887305_3063a4e0.pdf', 1216586, 'application/pdf', 18, '2026-06-08 02:55:05'),
(176, 291, 'birth_certificate', 'HANNAH GARCESA  PSA.pdf', 'uploads/enrollment_documents/291/birth_certificate_1780888334_5cb61dab.pdf', 2372609, 'application/pdf', 18, '2026-06-08 03:12:14'),
(177, 291, 'sf10', 'HANNAH GARCESA SF 10.pdf', 'uploads/enrollment_documents/291/sf10_1780888334_4e514f17.pdf', 1376551, 'application/pdf', 18, '2026-06-08 03:12:14'),
(178, 317, 'sf10', 'MICHAEL BEKER SF 10.pdf', 'uploads/enrollment_documents/317/sf10_1780890380_cf189bf8.pdf', 1358027, 'application/pdf', 18, '2026-06-08 03:46:20'),
(179, 333, 'birth_certificate', 'Jeferson Pactoran PSA.jpg', 'uploads/enrollment_documents/333/birth_certificate_1780984932.jpg', 2179626, 'image/jpeg', 18, '2026-06-09 06:02:12'),
(180, 333, 'sf10', 'Jeferson Pactoran Card.jpg', 'uploads/enrollment_documents/333/sf10_1780984932.jpg', 1305076, 'image/jpeg', 18, '2026-06-09 06:02:12'),
(181, 330, 'birth_certificate', 'Jesie Pasuit PSA.pdf', 'uploads/enrollment_documents/330/birth_certificate_1780986100_0d34f5f1.pdf', 2572047, 'application/pdf', 18, '2026-06-09 06:21:41'),
(182, 330, 'sf10', 'Jesie Pasuit SF 10.jpg', 'uploads/enrollment_documents/330/sf10_1780986100_2bc6b9d2.jpg', 2006526, 'image/jpeg', 18, '2026-06-09 06:21:41'),
(183, 172, 'sf10', 'BUT-AY CARD.pdf', 'uploads/enrollment_documents/172/sf10_1782805184_4f89227c.pdf', 908711, 'application/pdf', 18, '2026-06-30 07:39:45'),
(184, 172, 'birth_certificate', 'BUT-AY PSA.pdf', 'uploads/enrollment_documents/172/birth_certificate_1782805283_58b97856.pdf', 1846660, 'application/pdf', 18, '2026-06-30 07:41:24'),
(185, 283, 'birth_certificate', 'GATPATAN, JANWIN PSA.pdf', 'uploads/enrollment_documents/283/birth_certificate_1782805860_69ea988a.pdf', 2466033, 'application/pdf', 18, '2026-06-30 07:51:00'),
(186, 283, 'sf10', 'GATPATAN, JUANWIN CARD.pdf', 'uploads/enrollment_documents/283/sf10_1782805860_1e7f5618.pdf', 1794145, 'application/pdf', 18, '2026-06-30 07:51:00'),
(187, 296, 'birth_certificate', 'HEMPAYAN, JELMART LCR.pdf', 'uploads/enrollment_documents/296/birth_certificate_1782806494_3cc8c687.pdf', 982398, 'application/pdf', 18, '2026-06-30 08:01:34'),
(188, 296, 'sf10', 'HEMPAYAN,JELMART CARD.jpg', 'uploads/enrollment_documents/296/sf10_1782806494_14557f66.jpg', 959258, 'image/jpeg', 18, '2026-06-30 08:01:34'),
(189, 167, 'birth_certificate', 'ALCORIN, SHENALYN LCR.pdf', 'uploads/enrollment_documents/167/birth_certificate_1782806980_446c0229.pdf', 1023397, 'application/pdf', 18, '2026-06-30 08:09:40'),
(190, 167, 'sf10', 'ALCORIN, SHENALYN CARD.jpg', 'uploads/enrollment_documents/167/sf10_1782806980_59b05b9e.jpg', 1063858, 'image/jpeg', 18, '2026-06-30 08:09:40'),
(191, 173, 'birth_certificate', 'VALLENTEN, JAMES LCR.pdf', 'uploads/enrollment_documents/173/birth_certificate_1782807433_e76ed811.pdf', 2235384, 'application/pdf', 18, '2026-06-30 08:17:14'),
(192, 173, 'sf10', 'VALLENTEN, JAMES CARD.jpg', 'uploads/enrollment_documents/173/sf10_1782807433_1c95f48e.jpg', 1075764, 'image/jpeg', 18, '2026-06-30 08:17:14'),
(193, 168, 'birth_certificate', 'RUDA, MIKE PSA.pdf', 'uploads/enrollment_documents/168/birth_certificate_1782807909_0d65729a.pdf', 1446698, 'application/pdf', 18, '2026-06-30 08:25:10'),
(194, 168, 'sf10', 'RUDA, MIKE CARD.jpg', 'uploads/enrollment_documents/168/sf10_1782807909_9ff00755.jpg', 881089, 'image/jpeg', 18, '2026-06-30 08:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_type_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','partial','paid','overdue') DEFAULT 'pending',
  `school_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_fees`
--

INSERT INTO `student_fees` (`id`, `student_id`, `fee_type_id`, `amount`, `discount`, `balance`, `due_date`, `status`, `school_year`, `created_at`) VALUES
(1, 34, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(2, 34, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(3, 34, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(4, 34, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(5, 34, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(6, 34, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(7, 34, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(8, 34, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(9, 34, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(10, 34, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(11, 148, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(12, 148, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(13, 148, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(14, 148, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(15, 148, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(16, 148, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(17, 148, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(18, 148, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(19, 148, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(20, 148, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(21, 149, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(22, 149, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(23, 149, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(24, 149, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(25, 149, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(26, 149, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(27, 149, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(28, 149, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(29, 149, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(30, 149, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(31, 150, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(32, 150, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(33, 150, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(34, 150, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(35, 150, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(36, 150, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(37, 150, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(38, 150, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(39, 150, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(40, 150, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(41, 186, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(42, 186, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(43, 186, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(44, 186, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(45, 186, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(46, 186, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(47, 186, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(48, 186, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(49, 186, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(50, 186, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(51, 187, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(52, 187, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(53, 187, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(54, 187, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(55, 187, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(56, 187, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(57, 187, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(58, 187, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(59, 187, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(60, 187, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(61, 188, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(62, 188, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(63, 188, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(64, 188, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(65, 188, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(66, 188, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(67, 188, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(68, 188, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(69, 188, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(70, 188, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(71, 189, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(72, 189, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(73, 189, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(74, 189, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(75, 189, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(76, 189, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(77, 189, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(78, 189, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(79, 189, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(80, 189, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(81, 190, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(82, 190, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(83, 190, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(84, 190, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(85, 190, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(86, 190, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(87, 190, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(88, 190, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(89, 190, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(90, 190, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(91, 191, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(92, 191, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(93, 191, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(94, 191, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(95, 191, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(96, 191, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(97, 191, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(98, 191, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(99, 191, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(100, 191, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(101, 193, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(102, 193, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(103, 193, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(104, 193, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(105, 193, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(106, 193, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(107, 193, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(108, 193, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(109, 193, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(110, 193, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(111, 194, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(112, 194, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(113, 194, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(114, 194, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(115, 194, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(116, 194, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(117, 194, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(118, 194, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(119, 194, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(120, 194, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(121, 197, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(122, 197, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(123, 197, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(124, 197, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(125, 197, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(126, 197, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(127, 197, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(128, 197, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(129, 197, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(130, 197, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(131, 198, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(132, 198, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(133, 198, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(134, 198, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(135, 198, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(136, 198, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(137, 198, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(138, 198, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(139, 198, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(140, 198, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(141, 199, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(142, 199, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(143, 199, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(144, 199, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(145, 199, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(146, 199, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(147, 199, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(148, 199, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(149, 199, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(150, 199, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(151, 200, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(152, 200, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(153, 200, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(154, 200, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(155, 200, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(156, 200, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(157, 200, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(158, 200, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(159, 200, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(160, 200, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(161, 201, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(162, 201, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(163, 201, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(164, 201, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(165, 201, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(166, 201, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(167, 201, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(168, 201, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(169, 201, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(170, 201, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(171, 202, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(172, 202, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(173, 202, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(174, 202, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(175, 202, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(176, 202, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(177, 202, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(178, 202, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(179, 202, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(180, 202, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(181, 203, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(182, 203, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(183, 203, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(184, 203, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(185, 203, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(186, 203, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(187, 203, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(188, 203, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(189, 203, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(190, 203, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(191, 204, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(192, 204, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(193, 204, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(194, 204, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(195, 204, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(196, 204, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(197, 204, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(198, 204, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(199, 204, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(200, 204, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(201, 205, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(202, 205, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(203, 205, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(204, 205, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(205, 205, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(206, 205, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(207, 205, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(208, 205, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(209, 205, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(210, 205, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(211, 206, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(212, 206, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(213, 206, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(214, 206, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(215, 206, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(216, 206, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(217, 206, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(218, 206, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(219, 206, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(220, 206, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(221, 207, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(222, 207, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(223, 207, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(224, 207, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(225, 207, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(226, 207, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(227, 207, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(228, 207, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(229, 207, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(230, 207, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(231, 208, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(232, 208, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(233, 208, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(234, 208, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(235, 208, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(236, 208, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(237, 208, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(238, 208, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(239, 208, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(240, 208, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(241, 209, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(242, 209, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(243, 209, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(244, 209, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(245, 209, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(246, 209, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(247, 209, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(248, 209, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(249, 209, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(250, 209, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(251, 210, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(252, 210, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(253, 210, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(254, 210, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(255, 210, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(256, 210, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(257, 210, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(258, 210, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(259, 210, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(260, 210, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(261, 211, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(262, 211, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(263, 211, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(264, 211, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(265, 211, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(266, 211, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(267, 211, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(268, 211, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(269, 211, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(270, 211, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(271, 212, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(272, 212, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(273, 212, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(274, 212, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(275, 212, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(276, 212, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(277, 212, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(278, 212, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(279, 212, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(280, 212, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(281, 213, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(282, 213, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(283, 213, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(284, 213, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(285, 213, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(286, 213, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(287, 213, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(288, 213, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(289, 213, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(290, 213, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(291, 214, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(292, 214, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(293, 214, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(294, 214, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(295, 214, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(296, 214, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(297, 214, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(298, 214, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(299, 214, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(300, 214, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(301, 239, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(302, 239, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(303, 239, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(304, 239, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(305, 239, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(306, 239, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(307, 239, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(308, 239, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(309, 239, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(310, 239, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(311, 240, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(312, 240, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(313, 240, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(314, 240, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(315, 240, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(316, 240, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(317, 240, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(318, 240, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(319, 240, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(320, 240, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(321, 242, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(322, 242, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(323, 242, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(324, 242, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(325, 242, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(326, 242, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(327, 242, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(328, 242, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(329, 242, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(330, 242, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(331, 256, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(332, 256, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(333, 256, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(334, 256, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(335, 256, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(336, 256, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(337, 256, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(338, 256, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(339, 256, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(340, 256, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(341, 258, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(342, 258, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(343, 258, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(344, 258, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(345, 258, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(346, 258, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(347, 258, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(348, 258, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(349, 258, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(350, 258, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(351, 267, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(352, 267, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(353, 267, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(354, 267, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(355, 267, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(356, 267, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(357, 267, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(358, 267, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(359, 267, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(360, 267, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(361, 282, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(362, 282, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(363, 282, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(364, 282, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(365, 282, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(366, 282, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(367, 282, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(368, 282, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(369, 282, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(370, 282, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(371, 290, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(372, 290, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(373, 290, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(374, 290, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(375, 290, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(376, 290, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(377, 290, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(378, 290, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(379, 290, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(380, 290, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(381, 304, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(382, 304, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(383, 304, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(384, 304, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(385, 304, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(386, 304, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(387, 304, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(388, 304, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(389, 304, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(390, 304, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(391, 305, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(392, 305, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(393, 305, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(394, 305, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(395, 305, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(396, 305, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(397, 305, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(398, 305, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(399, 305, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(400, 305, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(401, 306, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(402, 306, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(403, 306, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(404, 306, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(405, 306, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(406, 306, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(407, 306, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(408, 306, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(409, 306, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(410, 306, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(411, 307, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(412, 307, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(413, 307, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(414, 307, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(415, 307, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(416, 307, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(417, 307, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(418, 307, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(419, 307, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(420, 307, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(421, 308, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(422, 308, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(423, 308, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(424, 308, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(425, 308, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(426, 308, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(427, 308, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(428, 308, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(429, 308, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(430, 308, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(431, 309, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(432, 309, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(433, 309, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(434, 309, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(435, 309, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(436, 309, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(437, 309, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(438, 309, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(439, 309, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(440, 309, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(441, 310, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(442, 310, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(443, 310, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(444, 310, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(445, 310, 2, 2000.00, 0.00, 2000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(446, 310, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(447, 310, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(448, 310, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(449, 310, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(450, 310, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-06-08 07:49:04'),
(451, 269, 5, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(452, 269, 4, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(453, 269, 7, -9000.00, 0.00, -9000.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(454, 269, 10, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(455, 269, 2, 2000.00, 0.00, 1521.61, NULL, 'partial', '2026-2027', '2026-07-01 12:47:04'),
(456, 269, 3, 550.00, 0.00, 550.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(457, 269, 8, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(458, 269, 6, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(459, 269, 9, 0.00, 0.00, 0.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04'),
(460, 269, 1, 8000.00, 0.00, 8000.00, NULL, 'pending', '2026-2027', '2026-07-01 12:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `student_medical_profiles`
--

CREATE TABLE `student_medical_profiles` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(4,2) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  `physician_name` varchar(100) DEFAULT NULL,
  `physician_phone` varchar(20) DEFAULT NULL,
  `insurance_provider` varchar(100) DEFAULT NULL,
  `insurance_policy_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `enrollment_key` varchar(10) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `grade_level`, `enrollment_key`, `created_by`, `created_at`, `archived`) VALUES
(1, 'ENG7', 'English 7', 'DepEd Grade 7 Subject', NULL, '9A5978E5', 1, '2025-12-20 01:34:14', 1),
(2, 'TLE7', 'Technology and Livelihood Education 7', 'DepEd Grade 7 Subject', NULL, '12E2CE85', 1, '2025-12-20 18:08:38', 1),
(3, 'SCI7', 'Science 7', 'DepEd Grade 7 Subject', NULL, '4D25B5D3', 14, '2026-02-13 19:56:16', 1),
(4, 'ORALCOM', 'Oral Communication', 'DepEd SHS - Core Subjects', NULL, '157E0B39', 1, '2026-04-06 04:25:16', 1),
(5, 'SCI8', 'Science 8', 'DepEd Grade 8 Subject', NULL, '12562884', 1, '2026-05-11 18:35:48', 1),
(6, 'TLE7', 'Technology and Livelihood Education 7', 'DepEd Grade 7 Subject', NULL, '9A73CD35', 1, '2026-06-08 00:56:28', 0),
(7, 'TLE10', 'Technology and Livelihood Education 10', 'DepEd Grade 10 Subject', NULL, '34E79A5B', 1, '2026-06-14 23:53:26', 1),
(8, 'TLE10', 'Technology and Livelihood Education 10', 'DepEd Grade 10 Subject', NULL, '7A208025', 1, '2026-06-14 23:53:33', 0),
(9, 'AP10', 'Araling Panlipunan 10', 'DepEd Grade 10 Subject', NULL, '027A5EC6', 1, '2026-06-14 23:54:33', 0),
(10, 'ENG10', 'English 10', 'DepEd Grade 10 Subject', NULL, 'E75BA6FC', 1, '2026-06-14 23:56:04', 0),
(11, 'MATH7', 'Mathematics 7', 'DepEd Grade 7 Subject', NULL, '300B8240', 1, '2026-06-15 00:24:56', 0),
(12, 'ENG8', 'English 8', 'DepEd Grade 8 Subject', NULL, 'ED79F485', 1, '2026-06-15 00:35:00', 0),
(13, 'ENG7', 'English 7', 'DepEd Grade 7 Subject', NULL, 'B97B6A5F', 1, '2026-06-15 23:04:20', 0),
(14, 'FIL7', 'Filipino 7', 'DepEd Grade 7 Subject', NULL, '08C8421C', 1, '2026-06-15 23:21:48', 0),
(15, 'MATH7', 'Mathematics 7', 'DepEd Grade 7 Subject', NULL, 'D2CD3160', 1, '2026-06-15 23:21:58', 0),
(16, 'SCI7', 'Science 7', 'DepEd Grade 7 Subject', NULL, '24A033AB', 1, '2026-06-15 23:34:24', 0),
(17, 'AP7', 'Araling Panlipunan 7', 'DepEd Grade 7 Subject', NULL, 'C6FE294A', 1, '2026-06-15 23:34:24', 0),
(18, 'MAPEH7', 'MAPEH 7', 'DepEd Grade 7 Subject', NULL, '9BA52744', 1, '2026-06-15 23:34:24', 0),
(19, 'ESP7', 'Edukasyon sa Pagpapakatao 7', 'DepEd Grade 7 Subject', NULL, '102E1C2D', 1, '2026-06-15 23:34:24', 0),
(20, 'FIL8', 'Filipino 8', 'DepEd Grade 8 Subject', NULL, 'F95E1185', 1, '2026-06-15 23:34:24', 0),
(21, 'MATH8', 'Mathematics 8', 'DepEd Grade 8 Subject', NULL, '492CF78B', 1, '2026-06-15 23:34:24', 0),
(22, 'SCI8', 'Science 8', 'DepEd Grade 8 Subject', NULL, '099FE78F', 1, '2026-06-15 23:34:24', 0),
(23, 'AP8', 'Araling Panlipunan 8', 'DepEd Grade 8 Subject', NULL, '2C0E6E66', 1, '2026-06-15 23:34:24', 0),
(24, 'TLE8', 'Technology and Livelihood Education 8', 'DepEd Grade 8 Subject', NULL, '218134F6', 1, '2026-06-15 23:34:24', 0),
(25, 'MAPEH8', 'MAPEH 8', 'DepEd Grade 8 Subject', NULL, '2D79CD79', 1, '2026-06-15 23:34:24', 0),
(26, 'ESP8', 'Edukasyon sa Pagpapakatao 8', 'DepEd Grade 8 Subject', NULL, '526767D1', 1, '2026-06-15 23:34:24', 0),
(27, 'ENG9', 'English 9', 'DepEd Grade 9 Subject', NULL, '760AE114', 1, '2026-06-15 23:34:24', 0),
(28, 'FIL9', 'Filipino 9', 'DepEd Grade 9 Subject', NULL, '7A0CFD50', 1, '2026-06-15 23:34:24', 0),
(29, 'MATH9', 'Mathematics 9', 'DepEd Grade 9 Subject', NULL, '1305EE35', 1, '2026-06-15 23:34:24', 0),
(30, 'SCI9', 'Science 9', 'DepEd Grade 9 Subject', NULL, 'E4A5FC85', 1, '2026-06-15 23:34:24', 0),
(31, 'AP9', 'Araling Panlipunan 9', 'DepEd Grade 9 Subject', NULL, '874BB81E', 1, '2026-06-15 23:34:24', 0),
(32, 'TLE9', 'Technology and Livelihood Education 9', 'DepEd Grade 9 Subject', NULL, '622293DA', 1, '2026-06-15 23:34:24', 0),
(33, 'MAPEH9', 'MAPEH 9', 'DepEd Grade 9 Subject', NULL, 'DB2217D4', 1, '2026-06-15 23:34:24', 0),
(34, 'ESP9', 'Edukasyon sa Pagpapakatao 9', 'DepEd Grade 9 Subject', NULL, 'A7BD90DB', 1, '2026-06-15 23:34:24', 0),
(35, 'FIL10', 'Filipino 10', 'DepEd Grade 10 Subject', NULL, 'AF78730A', 1, '2026-06-15 23:34:24', 0),
(36, 'MATH10', 'Mathematics 10', 'DepEd Grade 10 Subject', NULL, 'F9B1C5BC', 1, '2026-06-15 23:34:24', 0),
(37, 'SCI10', 'Science 10', 'DepEd Grade 10 Subject', NULL, '4C89EF75', 1, '2026-06-15 23:34:24', 0),
(38, 'MAPEH10', 'MAPEH 10', 'DepEd Grade 10 Subject', NULL, 'CD0D010C', 1, '2026-06-15 23:34:24', 0),
(39, 'ESP10', 'Edukasyon sa Pagpapakatao 10', 'DepEd Grade 10 Subject', NULL, '18B7A663', 1, '2026-06-15 23:34:24', 0);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `teacher_attendance`
--

INSERT INTO `teacher_attendance` (`id`, `teacher_id`, `date`, `time_in`, `time_out`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 33, '2026-06-15', '01:01:16', '01:01:36', 'present', NULL, '2026-06-15 08:01:16', '2026-06-15 08:01:36'),
(2, 19, '2026-06-15', '01:04:32', '01:05:55', 'present', NULL, '2026-06-15 08:04:32', '2026-06-15 08:05:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `empidno` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('admin','teacher','student','it_personnel','registrar','librarian','cashier') NOT NULL DEFAULT 'student',
  `grade_level` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT 0,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `father_contact` varchar(20) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `mother_contact` varchar(20) DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `guardian_relationship` varchar(50) DEFAULT NULL,
  `last_school_attended` varchar(255) DEFAULT NULL,
  `last_school_address` text DEFAULT NULL,
  `school_year_completed` varchar(20) DEFAULT NULL,
  `general_average` varchar(10) DEFAULT NULL,
  `has_lrn` tinyint(1) DEFAULT 0,
  `lrn_number` varchar(20) DEFAULT NULL,
  `is_returnee` tinyint(1) DEFAULT 0,
  `is_transfer_in` tinyint(1) DEFAULT 0,
  `has_special_needs` tinyint(1) DEFAULT 0,
  `special_needs_type` varchar(255) DEFAULT NULL,
  `is_4ps_beneficiary` tinyint(1) DEFAULT 0,
  `is_indigenous` tinyint(1) DEFAULT 0,
  `indigenous_group` varchar(100) DEFAULT NULL,
  `mother_tongue` varchar(100) DEFAULT NULL,
  `retention_status` enum('promoted','retained','irregular') DEFAULT 'promoted',
  `retention_reason` text DEFAULT NULL,
  `retention_school_year` varchar(20) DEFAULT NULL,
  `retention_updated_at` timestamp NULL DEFAULT NULL,
  `retention_updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `empidno`, `name`, `middle_name`, `email`, `password_hash`, `role`, `grade_level`, `created_at`, `image`, `archived`, `date_of_birth`, `gender`, `age`, `place_of_birth`, `nationality`, `religion`, `home_address`, `contact_number`, `father_name`, `father_occupation`, `father_contact`, `mother_name`, `mother_occupation`, `mother_contact`, `guardian_name`, `guardian_contact`, `guardian_relationship`, `last_school_attended`, `last_school_address`, `school_year_completed`, `general_average`, `has_lrn`, `lrn_number`, `is_returnee`, `is_transfer_in`, `has_special_needs`, `special_needs_type`, `is_4ps_beneficiary`, `is_indigenous`, `indigenous_group`, `mother_tongue`, `retention_status`, `retention_reason`, `retention_school_year`, `retention_updated_at`, `retention_updated_by`) VALUES
(1, 'ADMIN001', 'Admin User', NULL, 'admin@elms.com', '$2y$10$eReqaiXRttV/yW65hxZv5OtxUIvP6gbXNljbCWVG.sor1J73JqfYK', 'admin', NULL, '2025-12-20 00:53:38', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(2, 'T001', 'Juan Dela Cruz', NULL, 'teacher@test.com', '$2y$10$eReqaiXRttV/yW65hxZv5OtxUIvP6gbXNljbCWVG.sor1J73JqfYK', 'teacher', NULL, '2025-12-20 00:53:38', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(3, 'T002', 'Andrew Braza', NULL, 'brazaandrew@tlca.com', '$2y$10$eReqaiXRttV/yW65hxZv5OtxUIvP6gbXNljbCWVG.sor1J73JqfYK', 'teacher', NULL, '2025-12-20 00:53:38', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(4, 'S001', 'Allen Braza', NULL, 'allen@gmail.com', '$2y$10$eReqaiXRttV/yW65hxZv5OtxUIvP6gbXNljbCWVG.sor1J73JqfYK', 'student', NULL, '2025-12-20 00:53:38', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(5, 'S002', 'james doe', NULL, 'james@gmail.com', '$2y$10$eReqaiXRttV/yW65hxZv5OtxUIvP6gbXNljbCWVG.sor1J73JqfYK', 'student', NULL, '2025-12-20 00:53:38', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(6, 'S003', 'john Doe', NULL, 'doe@tlca.com', '$2y$10$5P5LK0wkzc.Ey7K5rS3zl.MBIyxX42hni4e.KziiDwnYtKWyXgRkS', 'student', 'Grade 7', '2025-12-20 01:56:31', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(10, 'IT001', 'allen', NULL, 'allen@tlca.com', '$2y$10$YWjHcut4cMxnVuU6slA24uj.XMmKP5FXygqk7Rzq3srl8PyYlhnkO', 'it_personnel', NULL, '2025-12-20 17:47:58', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(11, 'T005', 'liezl', NULL, 'liezl@gmail.com', '$2y$10$Xr6PwFFWU4MpbFP/Eotwp.N8BuQOeJ7yOSjrTAopwUMlVvkCjVcIa', 'teacher', NULL, '2025-12-21 01:42:39', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(13, 'S004', 'Jiana', NULL, 'jiana@tlca.com', '$2y$10$VWIGzuODIV2nMpjYjNC9n.hplSkQIEimoTQDIXTTGpx31BrfftKT6', 'student', 'Grade 7', '2025-12-28 00:58:01', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(14, 'ADMIN002', 'test', NULL, 'admintest1@tlca.com', '$2y$10$5/CWSCbJ1xw2xT6gYejxAeD9fpHR.PUnB6VA6l/H76x.ORhOh6hSy', 'admin', NULL, '2026-02-05 05:07:20', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(15, 'REG001', 'jane doe', NULL, 'jane123@gmail.com', '$2y$10$eVBbd2QiLIJrGIbiQ.qam.r6m6h0G2fwiWWydJODZLlbUqGCd4Xh6', 'registrar', NULL, '2026-04-20 23:53:29', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(16, 'CASH001', 'cashier', NULL, 'cashier@gmail.com', '$2y$10$UMYAyebnst7jLEciiWVOsevHt6qsNsafgsUmEGne.vL2Stntniwxe', 'cashier', NULL, '2026-04-22 19:17:51', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(17, 'STU-2026-0001', 'Alminza, Ritzmer arrelano', 'Arrelano', 'alminaza@gmail.com', '$2y$10$nuUPyTrGZbCL8rnI/wAaqeEQWG3C80YWHBFCWMIdg8ScuUC5NvYsy', 'student', '11', '2026-04-22 19:53:16', NULL, 0, NULL, '', NULL, '', 'Filipino', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, '', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(18, 'REG002', 'Crisundee Sinoy', NULL, 'crisundeesinoy8@gmail.com', '$2y$10$GYNf7eaI/1fZnvFIKLlZ0eJ3qkr.xan3SEbV5UODXB1Q04JYnuEAa', 'registrar', NULL, '2026-04-23 01:51:44', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(19, 'T006', 'Leovy Mae L. Khey', NULL, 'leovymaekhey@gmail.com', '$2y$10$sc79l.tmpadcRf8/yplFCuQy4hPdp73GxJq0EzLlYY53MMMiwjTBO', 'teacher', NULL, '2026-04-23 01:52:24', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(20, 'T007', 'Rhealyn S. Villafranca', NULL, 'rhealyn.villafranca23@gmail.com', '$2y$10$OhVSGcAMsSLaunyUz0nlouiesaNkXekHDP/ahta0Mr6JJLO/uvipK', 'teacher', NULL, '2026-04-23 01:54:53', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(21, 'T008', 'Danica V. Tubora', NULL, 'tuboradanica@gmail.com', '$2y$10$vyyPacMwrhBjuTuVIMHWIeI1P96lBXos01jJCzUx9sVhLPxkluFPW', 'teacher', NULL, '2026-04-23 01:55:36', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(22, 'T009', 'Jonaicy T. Tabotabo', NULL, 'vhanecent28@gmail.com', '$2y$10$cxy9c5IV3aktxNAiYXbVzeGOv9olEaShe4RnBvvLm04bRUe.hDoQ.', 'teacher', NULL, '2026-04-23 01:56:29', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(23, 'T010', 'Jennilyn T. Varela', NULL, 'jennilynvarela1998@gmail.com', '$2y$10$bxiUPnDXGw9N8p/bDDGQI.xG8GoALmEpWbrubUdVENkoSv2MmJ2Hq', 'teacher', NULL, '2026-04-23 01:57:03', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(25, 'T011', 'Gia Rae L. Santos', NULL, 'giaraesantos@gmail.com', '$2y$10$CVp1spB4dM7j/sUG6mrZzufdozEbVzJNA1v7Fn2PbFBugDV7t0phu', 'teacher', NULL, '2026-04-23 06:57:39', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(26, 'T012', 'Tyra Jade P. Seruelo', NULL, 'tjadeseruelo@gmail.com', '$2y$10$Zy2MEnD02.5yAwvorONw9eEnSJxhXbP70qu0KZoNBWoYPzzjcSEra', 'teacher', NULL, '2026-04-23 07:13:31', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(27, 'T013', 'Joshua Jehiel O. Maravilla', NULL, 'joshuajehielmaravilla@gmail.com', '$2y$10$4xXTusyeFliD.SxvILRgdu4xhAzxemxATDyKXe5DbnWBWzb.1315G', 'teacher', NULL, '2026-04-23 07:14:17', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(28, 'T014', 'Julie Ann C. Pigar', NULL, 'julieannpigar070802@gmail.com', '$2y$10$779pGqbLjETKbloxWW/6AeayDaFFxoq6fDnbJCcCa5w5doeYVd4QK', 'teacher', NULL, '2026-04-23 07:36:47', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(29, 'T015', 'Ferlyn S. Escalante', NULL, 'ferlynescalante587@gmail.com', '$2y$10$VJOIt3TBVoma8CuUHPL.Yeznu12u38Gw3cUWnpwvUkasK2GlLYZLO', 'teacher', NULL, '2026-04-23 07:37:48', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(30, 'T016', 'Grazel A. Arnaez', NULL, 'tlcagrazel@gmail.com', '$2y$10$GLVyEuDvnLRTB9ZRCqS6FuApN2cXIz35OGQfw4UsTxkx7yFmq2CMi', 'teacher', NULL, '2026-04-23 07:38:44', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(31, 'T017', 'Merlyn Mae Joy C. Jocson', NULL, 'merlynjocson492@gmail.com', '$2y$10$Ino/fZ30siyVx5Q1Shmuze0BBhj4VrpGJv5P7P4rscjI5lR6Zp7ZC', 'teacher', NULL, '2026-04-23 07:39:34', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(32, 'T018', 'Victor P. Flores Jr', NULL, 'victorpfloresjr459@gmail.com', '$2y$10$8LeevQStz9p2R7w1.FbiDOwr9NG56eoOm3bcH0dqm3.cOqvxPyFmu', 'teacher', NULL, '2026-04-23 07:40:14', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(33, 'T019', 'Jun Xavier B. Gicana', NULL, 'jxgicana@gmail.com', '$2y$10$tNCicf5f2rGJsXKVtx5sW.Xmle/iqUO8fA239tacmfMpnr0hHIbYK', 'teacher', NULL, '2026-04-23 07:42:45', 'assets/images/user_1781510084_52995051bc665291.jpg', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(34, 'STU-2026-0002', 'SARAD CATHERINE MILLADA', 'MILLADA', 'catherinesarad@gmail.com', '$2y$10$iqgqFBVOVi/FR2W9Y6RrmOBcBGdvQ8AHrPJxT.ih7FAqcqlFQQIYG', 'student', '10', '2026-05-11 18:43:36', NULL, 0, '2010-12-29', 'Female', 15, 'bACOLOD CITY, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Prk. guihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Charlie Gepa Sarad', 'Fisherman', '', 'Susana Millada Sarad', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. guihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '95', 1, '116868160025', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(35, 'STU-2026-0003', 'Pobreza, Juna Gundao', 'Gundao', 'junapobreza@gmail.com', '$2y$10$TnP7GkYEVG4QZZMeU0QRPuc5/hbZFa3ijp/ENqaYunRsLUxJeblgW', 'student', '11', '2026-05-12 20:27:05', NULL, 0, '2010-01-28', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Baptist', 'Prk. Tuway, Brgy. Canmoros, Binalbagan, Negros Occidental', '09388211834', 'Jeffrey Balavino Pobreza', 'Fisherman', '09388211834', 'Gina Sedario Gundao', 'housewife', '09388211834', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Neg. Occ.', '2025-2026', '92', 1, '440568150022', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(36, 'STU-2026-0004', 'MARAON, PRECIOUS ELIJAH LAHAYLAHAY', 'LAHAYLAHAY', 'maraonelijah@gmail.com', '$2y$10$MPeHZxApMLC/mRp0uqHgvOQy3lY6M71BJyVE5GAia2CZQnImL1I0W', 'student', '11', '2026-05-12 20:46:10', NULL, 0, '2010-05-15', 'Female', 15, 'MANILA', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09690963287', 'REY MARAON', 'LABORER', 'N/A', 'MA. CECILIA LAHAYLAHAY MARAON', 'OFW', '09690963287', 'ELAINE CASUYON LAHAYLAHAY', '09690963287', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '109469150125', 0, 0, 0, '', 0, 0, '', 'TAGALOG', 'promoted', NULL, NULL, NULL, NULL),
(37, 'STU-2026-0005', 'PELAGIO, ALJEAN ROSE ECUBIN', 'ECUBIN', 'pelagioaljean@gmail.com', '$2y$10$W7jMiN6Fozvr.CuocZryX.w3lkX5eCNdzL8Bbt54UIzSEKVKRY/qe', 'student', '10', '2026-05-12 20:48:33', NULL, 0, '2011-02-28', 'Female', 15, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09812084503', 'ALMER SARAD PELAGIO', 'LABORER', '09812084503', 'ROSY GESTALAO ECUBIN', 'HOUSEWIFE', '09812084503', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '95', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(38, 'STU-2026-0006', 'Pedro, Icil Gregorio', 'Gregorio', 'icilpedro@gmail.com', '$2y$10$NI9sZqyVN8WmJsJ.FIKGdOk/HQciQebKuovgf3tBckTgsalMn9HvO', 'student', '8', '2026-05-12 20:48:37', NULL, 0, '2013-05-31', 'Female', 12, NULL, 'Filipino', 'Catholic', 'Sitio Guihobon, Brgy. Amontay, Binalbagan, Negros Occidental', '09534731923', 'Teddy Pecore Pedro', 'Fisherman', '09534731923', 'Lecil Agravante Gregorio', 'housewife', '09534731923', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(39, 'STU-2026-0007', 'SARAD, MARY JOY VILLAHERMOSA', 'VILLAHERMOSA', 'maryjoysarad@gmail.com', '$2y$10$OJlVFGgeQNFVUhYrZcB9Y.mW3/sKmPeNMgnCMizns.PcK.iZnHPYW', 'student', '10', '2026-05-12 20:58:36', NULL, 0, '2011-01-09', 'Female', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09386196703', 'ARMANDO POLIDO SARAD', 'LABORER', '09386196703', 'MARIA VILLAHERMOSA CARCIDO', 'HOUSEWIFE', '09386196703', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '116868160052', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(40, 'STU-2026-0008', 'Pedro, Dave Gregorio', 'Gregorio', 'davepedro@gmail.com', '$2y$10$FxQeO9MnIed9x1LEc3N8iu2IwiqkW96s3a.WFwYxEXd5jyX/7FPea', 'student', '11', '2026-05-12 20:59:18', NULL, 0, '2010-07-31', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Guihobon, Bgry. Amontay, Binalbagan, Negros Occidental', '09534731923', 'Teddy Pecore Pedro', 'Fisherman', '09534731923', 'Lecil Agravante Gregorio', 'housewife', '09534731923', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '86', 1, '116864150024', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(41, 'STU-2026-0009', 'TUBIGON, ARVEN CORDOVA', 'CORDOVA', 'arventubigon@gmail.com', '$2y$10$8X4EeT13rabrncf6.KAZfuYSUEgwwvyDOgprvK1BYw6nA9/dJHO7u', 'student', '11', '2026-05-12 21:07:34', NULL, 0, '2010-06-18', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO SERENA, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09605392912', 'N/A (DECEASED)', 'N/A', 'N/A', 'REGIE CORDOVA TUBIGON', 'HOUSEWIFE', '09275369136', 'RECHEL ZAMORA CORDOVA', '09605392912', 'AUNT', 'AGUISAN NATIONAL HIGH SCHOOL', 'AGUISAN, HIMAMAYLAN CITY. NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116871150054', 0, 1, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(42, 'STU-2026-0010', 'PANES, NATHANIEL CANDIDO', 'CANDIDO', 'nathanielpanes@gmail.com', '$2y$10$GUzbVMSPt.DGmxBod.GR.uthWtbLogDYnvoUM9aPgHmR1MMXavRSO', 'student', '8', '2026-05-12 21:12:24', NULL, 0, '2011-11-13', 'Male', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09163620391', 'DONNY MACAPAZ PANES', 'N/A', '09163620391', 'CRIS JEAN SABELA CANDIDO', 'HOUSEWIFE', 'N/A', 'DONNY PANES MACAPAZ', '09163620391', 'FATHER', 'BATASAN HILLS NATIONAL HIGH SCHOOL', 'IBP ROAD, BATASAN HILLS, QUEZON CITY', '2025-2026', '75', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(43, 'STU-2026-0011', 'CUEVAS, MYCIL JORGE', 'JORGE', 'mycilcuevas@gmail.com', '$2y$10$OeQWjdF6RHKzgG2SkboUv.2n3sKwryJi8BZIL7Lbu8BL6y5liSsz2', 'student', '11', '2026-05-12 21:17:48', NULL, 0, '2004-07-16', 'Female', 21, 'BINALBAGAN', 'Filipino', '', 'SITIO INAPUGAN, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'ROSAVILLA PACONLA CARMELINO', 'N/A', '', 'ALTERNATIVE LEARNING SYSTEM', '', '2025-2026', '', 1, '120332090016', 0, 1, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(44, 'STU-2026-0012', 'Salvador, Xyrhen Mezhia Delos Santos', 'Delos Santos', 'xyrhenmezhiasalvador@gmail.com', '$2y$10$DBFVpWLwrpVxSFEpEXuu6.q0g60sBXCvTRkAVyxh1Oj7oruKhsu7S', 'student', '8', '2026-05-12 21:19:32', NULL, 0, '2013-03-03', 'Female', 13, NULL, 'Filipino', 'Catholic', 'Purok 2, Brgy. San Jose, Binalbagan, Negros Occidental', '09109202638', 'Orlando Inobaya Salvador', 'Fisherman', '09109202638', 'Rose Ann De Asis Delos Santos', 'housewife', '09109202638', 'Ma. Elena De Asis Delos Santos', '09126080454', 'Aunt', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(45, 'STU-2026-0013', 'PACONLA, MARLIN', '', 'marlinpaconla@gmail.com', '$2y$10$UC2ZuujglNAU6/4u.xziY./K65Qa1Mkd.KqV32xuXlwY.UM6lAGYO', 'student', '11', '2026-05-12 21:20:57', NULL, 0, '0000-00-00', 'Female', 0, 'BINALBAGAN', 'Filipino', '', 'SITIO INAPUGAN, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', '', '', '', '', '', '', 'ROSAVILLA PACONLA CARMELINO', 'N/A', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 1, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(46, 'STU-2026-0014', 'ROMANO, ANGEL BLESS PASUIT', 'PASUIT', 'angelblessromano@gmail.com', '$2y$10$6RXCpkW9PzbKxPoB5mYjZeckoMNG8XIk9PJxggtFssR8Ug/jDavjK', 'student', '10', '2026-05-12 22:45:53', NULL, 0, '2011-09-19', 'Female', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09109559428', 'ORLANDO MONTESINO ROMANO', 'LABORER', '09109559428', 'RAZEL LEGASPI PASUIT', 'HOUSEWIFE', '09109559428', 'RAZEL LEGASPI PASUIT', '09109559428', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116868150065', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(47, 'STU-2026-0015', 'PASUIT, ARISH ALABANSAS', 'ALABANSAS', 'arishpasuit@gmail.com', '$2y$10$O.6WU0tYhAHzPGEXvNZZW.biDGijUO66vQMPN9yw.VF9O624.E4Q.', 'student', '10', '2026-05-12 22:48:43', NULL, 0, '2011-03-22', 'Male', 15, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'FERNANDO VALDEZ PASUIT', 'LABORER', 'N/A', 'ANNALIZA ALABANSAS REYES', 'HOUSEWIFE', 'N/A', 'ANNALIZA REYES PASUIT', 'N/A', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(48, 'STU-2026-0016', 'BERNADEZ, RIAN PAUL POBREZA', 'POBREZA', 'rianpaulbernadez@gmail.com', '$2y$10$.ZU/dQfG/Mi6jP4vIOUtK.MNz6w/MB.K6XiLmeynTp/gdwz2nZdRu', 'student', '10', '2026-05-12 22:54:53', NULL, 0, '2010-11-15', 'Male', 15, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'PHOMEL CABUNAG BERNADEZ', 'LABORER', 'N/A', 'RITCHEL POBREZA BERNADEZ', 'HOUSEWIFE', 'N/A', 'RITCHEL POBREZA BERNADEZ', 'N/A', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(49, 'STU-2026-0017', 'PASUIT, FRANCISCO REYES', 'REYES', 'francisco@gmail.com', '$2y$10$nuLmwtz/FhjEsp.LAcLnX.NcLZjFzTv4hLadywbH35UnAJl1pDUgu', 'student', '9', '2026-05-12 22:58:47', NULL, 0, '2008-10-12', 'Male', 17, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'FERNANDO VALDEZ PASUIT', 'LABORER', 'N/A', 'ANNALIZA ALABANSAS REYES', 'HOUSEWIFE', 'N/', 'ANNALIZA REYES PASUIT', 'N/A', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '73', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(50, 'STU-2026-0018', 'PALATA, KOREN JOY PASU-IT', 'PASU-IT', 'korenpalata@gmail.com', '$2y$10$VzPsxcPpMMFUH22b3uIYbuFND.X2Y6zVcg3WG6UjBu4c9lSRY/pS.', 'student', '9', '2026-05-12 23:04:55', NULL, 0, '2012-01-17', 'Female', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09300548728', 'FELIMON GUSTILO PALATA', 'LABORER', 'N/A', 'ROSIE LEGASPI PASU-IT', 'HOUSEWIFE', '09300548728', 'ROSIE PASU-IT PALATA', '09300548728', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(51, 'STU-2026-0019', 'Pilar, Armand Jay Jamili', 'Jamili', 'armandjaypilar@gmail.com', '$2y$10$Wi5e/e/vDZosqeeeo5SUV.XvWRNJdvlwtB.1jKcEi3vPFmTCDXtL2', 'student', '9', '2026-05-12 23:13:45', NULL, 0, '2011-04-09', 'Male', 15, NULL, 'Filipino', 'Catholic', '4795 Purok Tuway, Brgy. Canmoros Binalbagan Negros Occidental, Philippines', '09625233216', '', '', '', '', '', '', 'Elechicon, Ma. Rosemarie Pilar.', '09625233216', 'Aunt', 'TLCA - Bin', 'Purok Aguihis, Brgy. Canmoros Binalbagan Negros Occidental.', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(52, 'STU-2026-0020', 'SARASA, JERRYME GANTES', 'GANTES', 'jerrymesarasa@gmail.com', '$2y$10$P5KMFfd18KRYv2R.0AmxTOlHWcvaLDN4QTl5KSIbjc2Xor3.4dIMe', 'student', '9', '2026-05-12 23:19:01', NULL, 0, '2012-06-24', 'Male', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'GERARDO MACABANI SARASA', '', '', 'EMELINDA GANTES SARASA', '', '', 'EMELINDA GANTES SARASA', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116876170004', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(53, 'STU-2026-0021', 'Adriano, Klarenze Jayme', 'Jayme', 'klarenzeadriano@gmail.com', '$2y$10$TxacrDg8NBgpEy5EomULM.FP5/t0qg2q5Lg.T5l0CLJGESBDjsWEq', 'student', '10', '2026-05-12 23:30:15', NULL, 0, '2009-03-02', 'Male', 17, 'Pampanga', 'Filipino', 'Catholic', 'Prk. Alimango Sitio Nabuswang Brgy Canmoros Binalbagan Negros Occidental', 'N/A', '', '', '', '', '', '', 'Ma. Cristina Layson Jayme', '09506718821', 'Aunt', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '79', 1, '116876150015', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(54, 'STU-2026-0022', 'ROMANO, ANGELA PASU-IT', 'PASU-IT', 'angelaromano@gmail.com', '$2y$10$kkEKN5GD1PTv/rCCtm1Pdu7i8DLMeszeyJHCd8IJwWAg2PXaNSDW6', 'student', '8', '2026-05-12 23:30:38', NULL, 0, '2012-11-16', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09109559428', 'ORLANDO MONTESINO ROMANO', 'LABORER', '09109559428', 'RAZEL LEGASPI PASUIT', 'HOUSEWIFE', '09109559428', 'RAZEL LEGASPI PASUIT', '09109559428', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(55, 'STU-2026-0023', 'PURA, KENNETH TOTANES', 'TOTANES', 'kennethpura@gmail.com', '$2y$10$9vySywDz0SfwwLVhdB/Ip.KleLRCQ/5YZDeocGzbGYQ2NEolKxWTC', 'student', '11', '2026-05-12 23:35:15', NULL, 0, '2010-05-10', 'Male', 16, 'PALAWAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'ALBERTO FERCOL PURA', '', '', 'CARINA BAUTISTA TOTANES', '', '', 'CARINA BAUTISTA TOTANES', '', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 1, '111253150018', 0, 0, 0, '', 0, 0, '', 'TAGALOG', 'promoted', NULL, NULL, NULL, NULL),
(56, 'STU-2026-0024', 'Onate, Mary Grace Laroya', 'Laroya', 'marygraceonate@gmail.com', '$2y$10$2eeC6JRyAzrMrW5HszZz/uQQF.j5Lx9OqjQymFNKqqmfwCuEqoP1O', 'student', '8', '2026-05-12 23:38:31', NULL, 0, '2013-04-03', 'Female', 13, NULL, 'Filipino', 'Catholic', 'Prk, Alimango Sitio Nabuswang Brgy Canmoros Binalbagan Negros Occidental Philippines', 'N/A', 'Danilo Onate', 'Fisherman', '', 'Rechel Laroya', 'housewife', '', 'Rechel Laroya', '09637801406', 'Mother', 'TLCA - Bin', 'Prk, Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(57, 'STU-2026-0025', 'POLIDO, JENLY HERMOSO', 'HERMOSO', 'jenlypolido@gmail.com', '$2y$10$ca0bfdyvvs1/LlQyAt8nn.SsJ5MVGd7KRLCEU9p154gZHuL80Qfv2', 'student', '9', '2026-05-12 23:47:21', NULL, 0, '2012-01-24', 'Female', 14, 'SILAY CITY', 'Filipino', 'BAPTIST', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09917513806', 'LULY ERILLO POLIDO', 'LABORER', '09939384911', 'JENNIFER VILLANUEVA HERMOSO', 'HOUSEWIFE', '09917513806', 'JENNIFER VILLANUEVA HERMOSO', '09917513806', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '95', 1, '116868170045', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(58, 'STU-2026-0026', 'JAVIER, ATHENA ORTIZ', 'ORTIZ', 'athenaortiz@gmail.com', '$2y$10$w15ip/XDpqDGKqTITK2fkuA66K/4UNAhZbKNOpagwrqswFwZo4Jje', 'student', '9', '2026-05-12 23:52:25', NULL, 0, '2011-09-16', 'Female', 14, 'SILAY CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09363380965', 'RONNEL PANGANIBAN JAVIER', '', '', 'HEIDI DAMAYON ORTIZ', 'HOUSEWIFE', '09363380965', 'HEIDI DAMAYON ORTIZ', '09363380965', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 1, '117192170071', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(59, 'STU-2026-0027', 'LAROYA, SEDRICK BALASABAS', 'BALASABAS', 'sedricklaroya@gmail.com', '$2y$10$o64tVxChiVIrHVPa8MYZBuGQb4SsCGLWJ/IvKoygv1ru0hrpWV0bG', 'student', '8', '2026-05-12 23:59:33', NULL, 0, '2013-01-20', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK ALIMANGO, SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09631723156', 'SIDNEY TAMAYO LAROYA', 'LABORER', 'N/A', 'MARISSA NONIFARA LAROYA', 'HOUSEWIFE', '09631723156', 'MARISSA NONIFARA LAROYA', '09631723156', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(60, 'STU-2026-0028', 'Solanoy, Rodolfo Jr. Bonga-ita', 'Bonga-ita', 'rodolfosolanoy@gmail.com', '$2y$10$0JO8uwigxzhBWCy1VyPBHuYMOuD1sHmPOqefZFjsxFQViRU1q8pJa', 'student', '12', '2026-05-13 00:00:37', NULL, 0, '2008-12-26', 'Male', 17, 'Himamaylan', 'Filipino', 'Catholic', 'Serina Enclaro Binalbagan Negros Occidental Philippines', 'N/A', 'Rodolfo Solanoy Sr.', 'Fisherman', '', 'Rosie Banga-ita', 'housewife', '09386704767', 'Rodolfo Solanoy Sr.', '09123491316', 'Father', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '81', 1, '117089140259', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(61, 'STU-2026-0029', 'Mande, Jennifer Laroya', 'Laroya', 'jennifermande@gmail.com', '$2y$10$5QD18aOSK83udVV/LycJ5.pYxaIx.oWzLwbCsc9rTWWwXM7r3PzMW', 'student', '10', '2026-05-13 00:07:37', NULL, 0, '2010-08-10', 'Female', 15, 'Binalbagan', 'Filipino', 'Catholic', 'Prk. Alimango Sitio Nabuswang Brgy Canmoros Negros Occidental Philippines', 'N/A', 'Jose Mande', 'Fisherman', '', 'Pinky Mande', 'housewife', '', 'Pinky Mande', '09463317094', 'Father', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '77', 1, '116876150010', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(62, 'STU-2026-0030', 'Jayme, Ian James Bugna', 'Bugna', 'ianjamesjayme@gmail.com', '$2y$10$iCL8iY2X6gLh.LW5V0LlHu/TxA8C9SsO0VEUMY/auXQUjXVOF86uG', 'student', '9', '2026-05-13 00:16:10', NULL, 0, '2012-08-01', 'Male', 13, NULL, 'Filipino', 'Catholic', 'Prk Alimango Sitio Nabuswang Brgy Canmoros Binalbagan Negros Occidental', 'N/A', 'Ricardo Jayme Jr.', 'Fisherman', '', 'Emee Jayme', 'housewife', '', 'Emee Jayme', '09629633810', 'mother', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '91', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(64, 'STU-2026-0031', 'LAROYA, ARIES JUL SARASA', 'SARASA', 'arieslaroya@gmail.com', '$2y$10$GIyzDLTaAeCs8fEzhOEMsOZJwr1xiSDdPL0MMKBJrBxyK6JM7TljS', 'student', '8', '2026-05-13 00:21:52', NULL, 0, '2013-07-11', 'Male', 12, '', 'Filipino', 'ROMAN CATHOLIC', 'PUROK ALIMANGO, SO. NABUSWANG, BINALBAGAN, NEGROS OCCIDENTAL', '09509315749', 'RUDY TAMAYO LAROYA', 'LABORER', '', 'JEAN SARASA LAROYA', 'HOUSEWIFE', '09509315749', 'JEAN SARASA LAROYA', '09509315749', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 0, '116876180006', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(65, 'STU-2026-0032', 'Jayme, Eduardo', '', 'eduardojayme@gmail.com', '$2y$10$qjBibnnzXAFRcUtr5lqpu.W1paZQPqe3lfxe4UNOFCFSTGW3bL2u6', 'student', '9', '2026-05-13 00:23:13', NULL, 0, '2010-06-03', 'Male', 15, NULL, 'Filipino', 'Catholic', 'Prk. Alimango Sitio Nabuswang Brgy Canmoros Negros Occidental Philippines', 'N/A', '', '', '', '', '', '', 'Ma. Christina Jayme', '', 'mother', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '77', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(66, 'STU-2026-0033', 'OGAHAYON, RHEA FAITH', '', 'rheaogahayon@gmail.com', '$2y$10$AR15Wx68cmByz735A27/KetFBMqYAtvLI6maUhY2.uatlMW3J60UO', 'student', '8', '2026-05-13 00:25:48', NULL, 0, '2012-03-24', 'Female', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'N/A', '', '', 'LANIE CEDONIO OGAHAYON', 'HOUSEWIFE', 'N/A', 'LANIE CEDONIO OGAHAYON', 'N/A', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(68, 'STU-2026-0034', 'MAGTOLIS, REGY TANALGO', 'TANALGO', 'regymagtolis@gmail.com', '$2y$10$6ngE/X4hLrkmM29zNNwinerTJ0g6l1wzaEZSpV8eYrXR8ZmEz.zcO', 'student', '8', '2026-05-13 00:36:34', NULL, 0, '2012-09-29', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09859738938', 'GARY SARAD MAGTOLIS', 'LABORER', '', 'ROSEMARIE TANALGO MAGTOLIS', 'HOUSEWIFE', '09859738938', 'ROSEMARIE TANALGO MAGTOLIS', '09859738938', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(69, 'STU-2026-0035', 'DAGUNAN, JAMES LAURENCE ERESTAIN', 'ERESTAIN', 'jamesdagunan@gmail.com', '$2y$10$VgyCVgRFyEWwVdi.33DH0.dsDHkbXz/U/Tq4SAIQE.YLilhIAfIje', 'student', '10', '2026-05-13 00:41:53', NULL, 0, '2010-12-12', 'Male', 15, 'BATANGAS CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09639145713', 'JERAHMIEL DURON DAGUNAN', 'LABORER', 'N/A', 'ROWENA BACQUIAN ERESTAIN', '', '', 'DELVA DURON DAGUNAN', '09639145713', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116868160007', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(70, 'STU-2026-0036', 'SELGA, FRENZ ALIZAR ANTIQUIN', 'ANTIQUIN', 'frenzselga@gmail.com', '$2y$10$ZuXZlV99yb8FQP50CnCxP.pQmjbjHeAs54AqBQGXhKfArhJDZplV2', 'student', '10', '2026-05-13 00:46:45', NULL, 0, '2009-09-22', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09186473128', 'N/A', '', '', 'EVELYN ANTIQUIN SELGA', 'HOUSEWIFE', '', 'PATRICIO JANAYA SELGA', '09186473128', 'GRANDFATHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '116868140085', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(71, 'STU-2026-0037', 'ALUNAN, HANNAH PAULA TALLAFER', 'TALLAFER', 'hannahpaula@gmail.com', '$2y$10$zYHgve4p9MV6hJAOI.0YUOWcZIxB4cT1zUPQKJ1ivH.2WBh877vJq', 'student', '12', '2026-05-13 00:52:21', NULL, 0, '2009-02-28', 'Female', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '0912400050', 'N/A', '', '', 'IRENE TALLAFER ALUNAN', 'HOUSEWIFE', '0912400050', 'IRENE TALLAFER ALUNAN', '0912400050', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '104768150244', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(72, 'STU-2026-0038', 'LABOA, MARY ROSE ANN BALASABAS', 'BALASABAS', 'maryroselaboa@gmail.com', '$2y$10$2p0edivfK9ELNl7KfPfQ4..uis7oa1I4qdYUTbnLHF2AacXuRrqci', 'student', '12', '2026-05-13 00:58:13', NULL, 0, '2008-12-11', 'Female', 17, 'LAGUNA', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09630259490', 'REYNALDO ALCUNIA LABOA', 'LABORER', '09630259490', 'ROMILA NONIFARA BALASABAS', 'HOUSEWIFE', '09630259490', 'REYNALDO ALCUNIA LABOA', '09630259490', 'FATHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '116876140022', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(73, 'STU-2026-0039', 'DONATO, NIÑO PUTONG', 'PUTONG', 'ninodonato@gmail.com', '$2y$10$Ixkn/oIs6FSMoKcoDrhg2.hahaDDjdTL8rPz00CpibTDTmvhfBy26', 'student', '12', '2026-05-13 01:02:43', NULL, 0, '2009-09-10', 'Male', 16, '', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDETNAL', '0912400050', 'GAUDENCIO PATRIBO DONATO', '', '09129400050', 'NENIA SARASA PUTONG', 'OFW', '09129400050', 'NENIA SARASA PUTONG', '09129400050', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 0, '', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(74, 'STU-2026-0040', 'Galvez, Joroz S.', 'S.', 'jorozgalvez@gmail.com', '$2y$10$Y0j9r8RLQp2KyXjNsBYKzekHtO3FQ2X75kYk4cWc.oA0ey26FLxhe', 'student', '9', '2026-05-13 01:05:49', NULL, 0, '2012-11-14', 'Male', 13, NULL, 'Filipino', 'Catholic', 'Sitio Nabuswang Brgy Canmoros Binalbagan Negros Occidental.', 'N/A', 'Jodie Galvez', 'Fisherman', '', 'Rosana Salazar', 'housewife', '', 'Roasana Salazar', '', 'Mother', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '84', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(75, 'STU-2026-0041', 'ARANTE, SHAINNIE MAE JUNIO', 'JUNIO', 'shainniearante@gmail.com', '$2y$10$AgLVgAmg.qiH5CJYZU/qGe67BcrHqu40GhQXGG400drzE1g/mqudm', 'student', '8', '2026-05-13 01:09:55', NULL, 0, '2013-09-24', 'Female', 12, NULL, 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09629336054', 'MARIO GENSANIO ARANTE', 'LABORER', '09629336054', 'SHENNIE TRINIDAD JUNIO', 'HOUSEWIFE', '09621954725', 'SHENNIE TRINIDAD JUNIO', '09621954725', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(76, 'STU-2026-0042', 'Salon, Judah Nieves', 'Nieves', 'judahsalon@gmail.com', '$2y$10$PdD3OGaCgnezVibmb2Pcr..YicUrQw.mGRPthn6yf8UQiEAu.IPWy', 'student', '12', '2026-05-13 01:13:46', NULL, 0, '2009-04-10', 'Male', 17, 'Binalbagan', 'Filipino', 'Catholic', 'Prk Punaw Brgy Canmoros Binalbagan Negros Occidental', 'N/A', 'Gerardo Salon Jr.', 'Fisherman', '', 'Veronica Salon', 'housewife', '', 'Veronica Salon', '09071840292', 'Mother', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '77', 1, '116876140071', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(77, 'STU-2026-0043', 'Tanalgo, Mary Grace D', 'D', 'marygracetanalgo@gmail.com', '$2y$10$nVk3h928WpJh./E45Rgwy.eoxIMEv0buQY3wmf7VyxtXnw5HzBVw.', 'student', '10', '2026-05-13 01:33:14', NULL, 0, '2011-05-06', 'Female', 15, 'Binalbagan', 'Filipino', 'Catholic', 'Prk Punaw Brgy Canmoros Binalbagan Negros Occidental', 'N/A', 'Facipico Tanalgo', 'Fisherman', '', 'Jenny Dedoyco', 'housewife', '', 'Facipico Tanalgo', '', 'Father', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '2025-2026', '88', 1, '115906160019', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(78, 'STU-2026-0044', 'VILLAESPIN, PETER JOHN PEROLINO', 'PEROLINO', 'peterjohn@gmail.com', '$2y$10$CAv.sGlL6IYU30px4//vPu2P9jfe5OrB.ziTkt2.tnGrrQX1p5I2W', 'student', '8', '2026-05-13 01:34:45', NULL, 0, '2013-05-18', 'Male', 12, 'HINIGARAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO IPIL-IPIL, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09932174299', 'PEPITO SARANILLO VILLAESPIN', 'DRIVER', '09932174299', 'MARJORIE GONZALES PEROLINO', 'HOUSEWIFE', '', 'PEPITO SARANILLO VILLAESPIE', '09932174299', 'FATHER', 'BINALBAGAN NATIONAL HIGH SCHOOL', 'BRGY. PAGLAUM, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 1, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(79, 'STU-2026-0045', 'TEGIO, PATRICK JAMES VILLAESPIE', 'VILLAESPIE', 'patrickjames@gmail.com', '$2y$10$10C4Nquuaknhuy/4Qtc4H.UM/Jm.Cmkq9.EKfRWODd0k2doBojpki', 'student', '8', '2026-05-13 01:41:36', NULL, 0, '2012-01-07', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO IPIL-IPIL, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09916049420', 'ALLAN BOY JAPSON TEGIO', 'DRIVER', '09916049420', 'NOLA SARANILLO VILLAESPIE', 'HOUSEWIFE', '', 'ALLAN BOY JAPSON TEGIO', '09916049420', 'FATHER', 'BINALBAGAN NATIONAL HIGH SCHOOL', 'BRGY. PAGLAUM, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(80, 'STU-2026-0046', 'BASILIO, CHARLOTTE PARAICO', 'PARAICO', 'charlottebasilio@gmail.com', '$2y$10$SlCWPb8OjTG.FyUy9H/SqO4mtUrjR821vZ8k6METLEY4HSA8H7DRy', 'student', '12', '2026-05-13 01:47:45', NULL, 0, '2009-02-05', 'Female', 17, 'QUEZON CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09386327025', 'JOEVEL EVANGELIO BASILIO', 'LABORER', '', 'SHIRLEY LADUA PARAICO', 'HOUSEWIFE', '', 'SHIRLEY LADUA PARAICO', '09386327025', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 1, '116868150042', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(81, 'STU-2026-0047', 'SARASA, JAIRUS', '', 'jairussarasa@gmail.com', '$2y$10$wCpKFYzYdq7JSGq4TShBRuUOUppfCl2ISnQ40sx59azEKwlOBOE5O', 'student', '8', '2026-05-13 01:52:50', NULL, 0, '2012-09-16', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09635296756', 'N/A', '', '', 'SHEILA SARASA TULALI', 'LABORER', '', 'GLORIA EMBOD VALERIO', '09635296756', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(82, 'STU-2026-0048', 'MILLADA, LINDCY ESPARAGOZA', 'ESPARAGOZA', 'lindcymillada@gmail.com', '$2y$10$So5kS7KKmNcn8iRpIgSss.p6sph/Wmhnyz7bVB1txmnowG49Dj626', 'student', '10', '2026-05-13 01:54:43', NULL, 0, '2011-03-30', 'Female', 15, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09304763576', 'RAMON MODICO MILLADA', 'LABORER', '09304763576', 'REMILDA ESPARAGOZA MILLADA', 'HOUSEWIFE', '09304763576', 'REMILDA ESPARAGOZA MILLADA', '09304763576', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '96', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(83, 'STU-2026-0049', 'Casuyon, Cristian S.', 'S.', 'christiancasuyon@gmail.com', '$2y$10$4Tm7lKIiZA7xXoeQIkbIvusIAMOp7xeMAd.H2caeXfPHogZiDQ/aS', 'student', '9', '2026-05-13 02:03:38', NULL, 0, '2012-01-22', 'Male', 14, NULL, 'Filipino', 'Catholic', 'Brgy Canmoros Binalbagan Negros Occidental', 'N/A', 'Radny Casuyon', 'Fisherman', '', 'Gina Casuyon', 'housewife', '', 'Gina Casuyon', '', 'mother', 'TLCA - Bin', 'Prk Aguihis Brgy Canmoros Binalbagan Negros Occidental', '', '78', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(84, 'STU-2026-0050', 'TUYAY, ZYREL SOLAICA ANDALES', 'ANDALES', 'zyrelsolaica@gmail.com', '$2y$10$sj2RKnoN3YfPmtgixoCK3.UhC4tiiZ1jQXKACe.PouPagw/jhZNLy', 'student', '10', '2026-05-13 02:07:04', NULL, 0, '2010-05-06', 'Female', 16, 'CALOOCAN CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09772612672', 'BILLARDO DANCALAN TUYAY', 'LABORER', '09772612672', 'BONNA SAN-ORIL ANDALES', 'HOUSEWIFE', '09772612672', 'BONNA SAN-ORIL ANDALES', '09772612672', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '95', 1, '223501160158', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(85, 'STU-2026-0051', 'EVANGELIO, AIRA MAE BONTOGON', 'BONTOGON', 'airamae@gmail.com', '$2y$10$.wdw7Ya2ijdPM7b7gsghfOgmFliDxvVDHmaENXxtCsyDsbvpB.OW2', 'student', '10', '2026-05-13 02:08:38', NULL, 0, '2011-02-17', 'Female', 15, NULL, 'Filipino', 'ROMAN CATHOLIC', '', 'N/A', 'JOERIE DUMIP-IG EVANGELIO', 'LABORER', '', 'JERAMAE CACHOCO BONTOGON', 'HOUSEWIFE', '', 'JOERIE DUMIP-IG EVANGELIO', '', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(86, 'STU-2026-0052', 'POBREZA, ALEXI SHANE ALIT', 'ALIT', 'alexishane@gmail.com', '$2y$10$klGiC0EE31I9WZ3vh8BB..Ck5HRNrVvAAoT5Oy5wVRjsTxh2mvhmG', 'student', '8', '2026-05-13 02:14:37', NULL, 0, '2012-10-31', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '0912339730', 'ROMEO BALADINO POBREZA', 'LABORER', '', 'REJANE PALIC ALIT', 'HOUSEWIFE', '', 'REJANE ALIT POBREZA', '09102339730', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(87, 'STU-2026-0053', 'PAPAS, CHLOE PASUIT', 'PASUIT', 'chloepapas@gmail.com', '$2y$10$IHlHBrX9H2DWaxMCP9XY8u6SWb749licylk5zM2CibDnnXSOJL8su', 'student', '8', '2026-05-13 02:20:29', NULL, 0, '2013-06-17', 'Female', 12, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK  TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09812032139', 'BERNARD DEBALDERO PAPAS', 'LABORER', '', 'RENIA LEGASPI PASUIT', 'LABORER', '09812032139', 'RENIA LEGASPI PASUIT', '09812032139', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(88, 'STU-2026-0054', 'AMANTE, CARLAINE MAE CASUYON', 'CASUYON', 'carlainemae@gmail.com', '$2y$10$HBNkfRzNQIuWrWtNFklS8.O61uHAdXtbI5DGt27IsJqSHqIMcWhu6', 'student', '8', '2026-05-13 02:24:17', NULL, 0, '2013-08-05', 'Female', 12, '', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09630885760', 'JUANITO BADANA AMANTE', 'LABORER', '0963688760', 'MANNYLYN ZAMORA CASUYON', 'HOUSEWIFE', '', 'JUANITO BADANA AMANTE', '0963688760', 'FATHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 0, '116868180073', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(89, 'STU-2026-0055', 'CABANDO, MEGAN', '', 'megancabando@gmail.com', '$2y$10$0NS3d03IyGDn3UBOqD3d6urN3xd4UpyOIty1jYcWphwoPuXmk8cEC', 'student', '8', '2026-05-13 02:28:56', NULL, 0, '2013-12-27', 'Female', 12, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09510765773', '', '', '', '', '', '', 'JONAFIL BELARMINO CABANDO', '09510765773', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(90, 'STU-2026-0056', 'AREGLADO, DWAYNE LEBRON CABANDO', 'CABANDO', 'dwaynelebron@gmail.com', '$2y$10$Log1CVn0sl/TLYJE9sKOmeuf2vQ7Gu9PNvAY6LtAv2uxOK43bKkWC', 'student', '9', '2026-05-13 02:31:30', NULL, 0, '2011-08-14', 'Male', 14, 'ISABELA', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09510765773', '', '', '', '', '', '', 'JONAFIL BELARMINO CABANDO', '09510765773', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116868170052', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(91, 'STU-2026-0057', 'Mande, Joenel Laroya', 'Laroya', 'joenelmande@gmail.com', '$2y$10$2QiK4mangECC71igtXoWM.uqewGOg6SYvLWFpTFO6iWQWxQzi7kGO', 'student', '8', '2026-05-13 17:56:52', NULL, 0, '2012-10-05', 'Male', 13, NULL, 'Filipino', 'Catholic', 'Brgy. Canmoros, Binalbagan, Negros Occidental', '09463317094', 'Jose Judilla Mande', 'Fisherman', '09463317094', 'Pinky Laroya Mande', 'housewife', '09463317094', 'Pinky Mande', '09463317094', 'Mother', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '78', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(92, 'STU-2026-0058', 'Labor, Jierose Cordova', 'Cordova', 'jieroselabor@gmail.com', '$2y$10$9/jF7HNPe5mUBfRVkgFHxeHgDMRJn08xZODlWM1Odtm1ivPZoyytm', 'student', '12', '2026-05-13 18:07:13', NULL, 0, '2008-12-20', 'Male', 17, 'Manila, Philippines', 'Filipino', 'Catholic', 'Prk. Greenshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09485332227', 'Benjamen Pilongo Labor', 'Fisherman', '09485332227', 'Rosie Cordova Labor', 'Housewife', '09485332227', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2024-2025', '88', 1, '116868150039', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(93, 'STU-2026-0059', 'Medalla, Dave Mahilum', 'Mahilum', 'davemedalla@gmail.com', '$2y$10$yetfdKKjvboAWgB6VdsWaOMrM06snX.2KnKPVkdjdlLfZFTS3pItu', 'student', '12', '2026-05-13 18:16:57', NULL, 0, '2009-03-30', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Tuway, Brgy. Canmoros, Binalbagan, Negros. Occidental', '', 'Denny Mandin Medalla', 'Fisherman', '', 'Melynda Mahilum Medalla', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros. Occidental', '2025-2026', '89', 1, '116870140012', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(94, 'STU-2026-0060', 'Tablang, Khobe Villaflor', 'Villaflor', 'khobetablang@gmail.com', '$2y$10$bew9n3Btt/xxOWVA9FJxw.6mVgjNA.bOthl4G0FXlfSVPNFqu.qf.', 'student', '10', '2026-05-13 19:14:05', NULL, 0, '2008-09-17', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09518793406', 'Manny Corbilla Tablang', 'Fisherman', '09518793406', 'Razel Villaflor Tablang', 'Housewife', '09518793406', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalabagan, Negros Occidental', '2025-2026', '83', 1, '116876170018', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `empidno`, `name`, `middle_name`, `email`, `password_hash`, `role`, `grade_level`, `created_at`, `image`, `archived`, `date_of_birth`, `gender`, `age`, `place_of_birth`, `nationality`, `religion`, `home_address`, `contact_number`, `father_name`, `father_occupation`, `father_contact`, `mother_name`, `mother_occupation`, `mother_contact`, `guardian_name`, `guardian_contact`, `guardian_relationship`, `last_school_attended`, `last_school_address`, `school_year_completed`, `general_average`, `has_lrn`, `lrn_number`, `is_returnee`, `is_transfer_in`, `has_special_needs`, `special_needs_type`, `is_4ps_beneficiary`, `is_indigenous`, `indigenous_group`, `mother_tongue`, `retention_status`, `retention_reason`, `retention_school_year`, `retention_updated_at`, `retention_updated_by`) VALUES
(95, 'STU-2026-0061', 'Laroya, Reymark Gallano', 'Gallano', 'reymarklaroya@gmail.com', '$2y$10$eHlY3Hc5VmIW5D0EbfciVOmP7K6jhWn5ahv.B8Rnx8eZ3gNEaZnWa', 'student', '10', '2026-05-13 19:38:19', NULL, 0, '2009-02-13', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09941484526', 'Jomarie Tamayo Laroya', 'Fisherman', '09941484526', 'Annaliza Gallano Laroya', 'Housewife', '09941484526', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '77', 1, '116876140010', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(96, 'STU-2026-0062', 'Laberos, Cathreyn Palacios', 'Palacios', 'cathreynlaberos@gmail.com', '$2y$10$NPvwSkplEhlRB2sONImkruhPBRUWP2OzzfRRZ6HvBD6SORyrFsdY2', 'student', '8', '2026-05-13 22:41:28', NULL, 0, '2012-10-09', 'Female', 13, NULL, 'Filipino', 'Catholic', 'Prk Narra, Brgy. San Teodoro, Binalabagan, Negros Occidental', '09638099497', 'Reynaldo Tianga Laberos', 'Fisherman', '09638099497', 'Menchie Palacios Laberos', 'Housewife', '09508742262', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '95', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(97, 'STU-2026-0063', 'Mayoga, Gelian Tallafer', 'Tallafer', 'gelianmayoga@gmail.com', '$2y$10$YQJz51uqtnWeY3xpTVGwx.0EEi5Hg4FjmgG9y83iqoUv5xoI2Coj2', 'student', '10', '2026-05-13 22:49:14', NULL, 0, '2011-03-08', 'Female', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Angelito Mayoga', 'Fisherman', '', 'Gemma Tallafer Mayoga', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '88', 1, '116876160007', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(98, 'STU-2026-0064', 'TORILLO, RICHARD JR. TABURADA', 'TABURADA', 'richardtorillo@gmail.com', '$2y$10$OLJ6h53.ccG10AWUhD1n1uWAKBTDDHZBct.vJ2aY6bs5CYosV6yRK', 'student', '8', '2026-05-13 23:20:34', NULL, 0, '2013-01-27', 'Male', 13, NULL, 'Filipino', '', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', 'N/A', 'RICHARD TORILLO', 'LABORER', 'N/A', 'JOY JEAN ABSIN FABURADA', 'HOUSEWIFE', 'N/A', 'MARILOU ABSIN FABURADA', 'N/A', 'GRANDMOTHER', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(99, 'STU-2026-0065', 'ENEGRIO, NIKA TAMAYO', 'TAMAYO', 'nikaenegrio@gmail.com', '$2y$10$o//dfFL5WSwUrJ2LiQKbBeD0wMFGeMs8WXglmEViuMnP4WXf9WZKu', 'student', '10', '2026-05-13 23:24:28', NULL, 0, '2010-12-20', 'Female', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCC.', 'N/A', 'EDMOND DELACRUZ ENEGRIO', '', '', 'GINA TALLAFER TAMAYO', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BIN. NEG. OCC.', '2025-2026', '90', 1, '116876160017', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(100, 'STU-2026-0066', 'IMPERIAL, VEY JEAN MEDECINIO', 'MEDECINIO', 'veyjeanimperial@gmail.com', '$2y$10$Gq1iUQlmaJ7UeEc3VFCld.VjrkVsxgVKt5.Q2i7qUVUltipZNy6Lm', 'student', '12', '2026-05-13 23:33:17', NULL, 0, '2009-07-09', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG', 'N/A', 'HARVEY SARASA IMPERIAL', '', '', 'REGINE TAMAYO MEDECINIO', '', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS', '2025-2026', '94', 1, '116876150018', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(101, 'STU-2026-0067', 'FLORES, RALPH RR MAGTOLIS', 'MAGTOLIS', 'ralphrr@gmail.com', '$2y$10$DiYC5JACLsUbm2iLGAkCbOO7rCstdOPYrbMAaznrsnPyhVExg4Ug.', 'student', '12', '2026-05-14 00:01:36', NULL, 0, '2009-03-14', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL', '', 'RANDY SADIO FLORES', 'LABORER', '', 'ROMELA ESTARIN MAGTOLIS', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS', '2025-2026', '87', 1, '116868140039', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(102, 'STU-2026-0068', 'JAWOD, REINHART DAGUNAN', 'DAGUNAN', 'reinhartjawod@gmail.com', '$2y$10$S472ihY6J8YwiBOhTv3.h.LMzPupEaTUtO20tOmLWYK.CPtTc9Kee', 'student', '10', '2026-05-14 00:06:40', NULL, 0, '2011-06-18', 'Male', 14, 'CAVITE CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK 2 (MARAGTAS), BRGY. SAN JOSE, BINALBAGAN, NEGROS OCC.', '09639145713', 'REX VINCENT HONOFRE JAWOD', 'DRIVER', '09639145713', 'DARE MADEL DAGUNAN JAWOD', 'EMPLOYED', '09639145713', 'DELVA DURON DAGUNAN', '09639145713', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS', '2025-2026', '93', 1, '116892160011', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(103, 'STU-2026-0069', 'LAROYA, EJAY BALASABAS', 'BALASABAS', 'ejaylaroya@gmail.com', '$2y$10$YdxWDi3D4yJlIMT2QgqSk.fq3NO2tPmbDN.sPc2i0lP05ZKX9N8r6', 'student', '10', '2026-05-14 00:10:12', NULL, 0, '2010-08-12', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK ALIMANGO, SITIO NABUSWANG, BRGY. CANMOROS', '09631723156', 'SIDNEY TAMAYO LAROYA', 'LABORER', 'N/A', 'MARISSA NONIFARA LAROYA', 'HOUSEWIFE', '09631723156', 'MARISSA NONIFARA LAROYA', '09631723156', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BIN. NEG. OCC.', '2025-2026', '80', 1, '116876160011', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(104, 'STU-2026-0070', 'JARDIN, JEMMA ROSE GERMO', 'GERMO', 'jemmarosejardin@gmail.com', '$2y$10$54rx.W4ewG5NayfLAY8QueVdHvU6HxY039l55.Sz0L29gk/aTIB6W', 'student', '11', '2026-05-14 00:16:09', NULL, 0, '2010-06-26', 'Female', 15, 'MOLANON, MANILA', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS', '09816137701', 'JOEL JARDIN', 'OFW', '', 'MILA ROSE DAVIDAS GERMO', 'OFW', '', 'KAYE CUENCA', '09816137701', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS', '2025-2026', '85', 1, '116868150031', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(105, 'STU-2026-0071', 'Estaniel, Renzo Estoconing', 'Estoconing', 'renzoestaniel@gmail.com', '$2y$10$WdvDW2T6t3qjqYrvGfSuAuolNHiJk.TpIM9kZ7xDgJpf5tNsd17TW', 'student', '8', '2026-05-17 17:46:10', NULL, 0, '2013-10-19', 'Male', 12, '', 'Filipino', 'Catholic', 'Sitio Cabadbaran, Brgy. Santol, Binalbagan, Negros Occidental', '', 'Renold Paculanang Estaniel', 'Fisherman', '', 'Merry Quistorio Estoconing', 'Housewife', '', 'Merry E. Estaniel', '', 'Mother', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '88', 0, '116872180014', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(106, 'STU-2026-0072', 'Torrefranca, Crislyn Gatpatan', 'Gatpatan', 'crislyntorrefranca@gmail.com', '$2y$10$OSWAL65jFpNpynoD3dBqAuW0fzgO3CgHItbi0RacRb1vi/HDE.QTe', 'student', '8', '2026-05-17 18:05:18', NULL, 0, '2012-04-22', 'Female', 14, NULL, 'Filipino', 'Catholic', 'Prk. Green Shell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09700326318', 'Crisanto Francisco Torrefranca', 'Fisherman', '09700326318', 'Jayne Gatpatan Torrefranca', 'Housewife', '09700326318', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '89', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(107, 'STU-2026-0073', 'Patoc, Ranmark Peñero', 'Peñero', 'ranmarkpatoc@gmail.com', '$2y$10$N8u1qSrTNbDC4z5Y.O4ByuqHmq0b9AGtprG9gOwnxOzc2ywCuEQJ6', 'student', '8', '2026-05-17 18:16:21', NULL, 0, '2012-02-28', 'Male', 14, NULL, 'Filipino', 'Catholic', 'Sitio Inapugan, Brgy. Santol, Binalbagan, Negros Occidental', '', 'Randy Gañolang Patoc', 'Fisherman', '', 'Maribel Peñero Patoc', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(108, 'STU-2026-0074', 'Paconla, Ena Peñero', '', 'enapaconla@gmail.com', '$2y$10$V/1AoYTmUPnOiSJ/VZ7R..UnNWGS.TRpdIe/SP8bYJGKSHBIuHGgu', 'student', '8', '2026-05-17 18:49:49', NULL, 0, '2012-09-29', 'Female', 13, NULL, 'Filipino', 'Catholic', 'Sitio Inapugan, Brgy. Santol, Binalbagan, Negros Occidental', '', '', '', '', 'Mary Jean Ella', 'Housewife', '', 'Rosacilla Paconla Carmelino', '', 'Aunt', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '86', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(109, 'STU-2026-0075', 'Carmelino, Ayan Paconla', 'Paconla', 'ayancarmelino@gmail.com', '$2y$10$86N4s9bvtlBTy.Ejq7xa.e6Qle6uiSDivxHZkxj.3DYbZJf1QsOoC', 'student', '8', '2026-05-17 18:57:19', NULL, 0, '2012-12-22', 'Male', 13, NULL, 'Filipino', 'Catholic', 'Sitio Inapugan, Brgy. Santol, Binalbagan, Negros Occidental', '', 'Cleopas Carmelino', 'Laborer', '', 'Rosacilla Paconla Carmelino', 'Housewife', '', 'Rosacilla Paconla Carmelino', '', 'Mother', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(110, 'STU-2026-0076', 'Madriaga, Vhane Kacelei Tabotabo', 'Tabotabo', 'vhanekaceleimadriaga@gmail.com', '$2y$10$jzn8Ddbe5AqJ9aoXF12jleVkEaOCgX47VTrC619R0HZiP5JeVLPty', 'student', '8', '2026-05-17 19:22:55', NULL, 0, '2012-01-07', 'Female', 14, NULL, 'Filipino', 'Catholic', 'Prk. 8, Seaside Plaza, Brgy. Aguisan, Himamaylan, Negros Occidental', '09453607822', 'Marben Panelo Madriaga', 'Laborer', '09453607822', 'Jonaicy Tundag Tabotabo', 'Teacher', '09453604822', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros., Binalbagan, Negros Occidental', '2025-2026', '96', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(111, 'STU-2026-0077', 'Pelarin, Jessica Tanalgo', 'Tanalgo', 'jessicapelarin@gmail.com', '$2y$10$RObNreEWlO2ag8CR5JAyU.dJTuU9WkP0/wOOIJMqAZ6CcxH45KPFW', 'student', '8', '2026-05-17 19:54:32', NULL, 0, '2012-10-26', 'Female', 13, NULL, 'Filipino', 'Catholic', 'Prk. Punaw, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Marcelo Villaran Pelarin', 'Fisherman', '', 'Jocelyn Tanalgo Pelarin', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan. Negros Occidental', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(112, 'STU-2026-0078', 'Atanque, Roan James Estarin', 'Estarin', 'roanjamesatanque@gmail.com', '$2y$10$5lsEjWoXsfNV60KjS0c8luDGRc2/1FK0xOawbSqSqT9Rh8CDrodL.', 'student', '8', '2026-05-17 20:11:46', NULL, 0, '2012-10-31', 'Male', 13, NULL, 'Filipino', 'Catholic', 'Prk. Nylon Shell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09945136682', 'Efraim Atanque', 'Fisherman', '09945136682', 'Rowela Estarin', 'Housewife', '09945136682', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '81', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(113, 'STU-2026-0079', 'Casuyon, Janmar Sasel', 'Sasel', 'janmarcasuyon@gmail.com', '$2y$10$U3MY3e0oP0Jh32RN/szGluIc5RY33OSHrXJxXMF/an4.SKxat7YdW', 'student', '8', '2026-05-17 20:26:16', NULL, 0, '2013-03-27', 'Male', 13, NULL, 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Marco Ortenila Casuyon', 'Fisherman', '', 'Frezel Hantok Sasel', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '84', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(114, 'STU-2026-0080', 'Burdago, Lengie Topes', 'Topes', 'lengieburdago@gmail.com', '$2y$10$PB9CIzH2fv1vIWDbnACNN.LWwIB3Q/PcTD5zp2WsqLZl8Ilrb6wze', 'student', '8', '2026-05-17 20:41:03', NULL, 0, '2013-09-26', 'Female', 12, '', 'Filipino', 'Catholic', 'Sitio Kabalan-tianan, Brgy. Amontay, Binalbagan, Negros Occidental', '', 'Jilwar Pecore Burdago', 'Fisherman', '', 'Leziel Absin Topes', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '89', 0, '116879180036', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(115, 'STU-2026-0081', 'Gantalao, Joshua Topis', 'Topis', 'joshuagantalao@gmail.com', '$2y$10$e6UsuySKRur1J6DL1STH/OZhSBiCxFCzJULaOR746G2fnMbQ4492u', 'student', '8', '2026-05-17 20:51:05', NULL, 0, '2012-10-26', 'Male', 13, '', 'Filipino', 'Catholic', 'Sitio Cabalanti-anan, Brgy. Amontay, Binalbagan, Negros Occidental', '09534151871', 'Jessie Mojello Gantalao', 'Fisherman', '09534151871', 'Jesa Absin Topis', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '86', 0, '116879180032', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(116, 'STU-2026-0082', 'Ganaganag, Jonalyn Tino', 'Tino', 'jonalynganaganag@gmail.com', '$2y$10$7wtNt2bzEYndBcH18.clQOe7eQIe68awCHfUekLXvfrB/8Ne69Egi', 'student', '9', '2026-05-17 20:59:41', NULL, 0, '2012-04-29', 'Female', 14, NULL, 'Filipino', 'Catholic', 'Sitio Cabadbaran, Brgy. Santol, Binalbagan, Negros Occidental', '', 'Jelwar Carmelino Ganaganag', 'Fisherman', '', 'Analie Gordoncillo Tino', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '88', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(117, 'STU-2026-0083', 'Cañedo, Jade Vensam Jocson', 'Jocson', 'jadevensamcanedo@gmail.com', '$2y$10$L9wa9q9R8kG3u8EGdNwHvuvNmHyN/SLj53usa5EtKKeRQJSNqOjhq', 'student', '9', '2026-05-17 23:15:19', NULL, 0, '2012-02-13', 'Male', 14, NULL, 'Filipino', 'INC', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Noel Colimbo Cañedo', 'Fisherman', '', 'Jinky Jocson Cañedo', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '80', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(118, 'STU-2026-0084', 'Atanque, Rex Belle Estarin', 'Estarin', 'rexbelleatanque@gmail.com', '$2y$10$LwAhXsrV9XLsDeovi1C1NORO3NnxuKsLxOsKRGBnfGtSNL2PhHe3q', 'student', '10', '2026-05-17 23:25:29', NULL, 0, '2010-12-15', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylon Shell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09945136682', 'Efraim Atanque', 'Fisherman', '', 'Rowela Estarin Atanque', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '79', 0, '116868160028', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(119, 'STU-2026-0085', 'Callora, Cheryl Recla', 'Recla', 'cherylcallora@gmail.com', '$2y$10$ogAMKhyUWt4qRIC6ZBaP4ubw1BAhsm1M1diHl656G0wmdI9Y9eus.', 'student', '10', '2026-05-17 23:31:49', NULL, 0, '2011-09-04', 'Female', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Maslog, Brgy. Amontay, Binalbagan, Negros Occidental', '09945136682', 'Charlie Yu Callora', 'Fisherman', '', 'Nancy But-ay Recla', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 1, '116879170030', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(120, 'STU-2026-0086', 'SARAD, ROSEMARIE TESORO', 'TESORO', 'rosemariesarad@gmail.com', '$2y$10$cEIqlkAVeWYgRd7JBiOvuu/46wE3Jil4hgEJDmC79uIlUWQbybA4e', 'student', '11', '2026-05-17 23:31:50', NULL, 0, '2009-11-11', 'Female', 16, 'BINALBAGAN', 'Filipino', 'BAPTIST', 'PUROK GREENSHELL, BRGY. CANMOROS', '09850876258', 'RONIE POLIDO SARAD', 'FISHERMAN', '', 'FANNY UNTAL TESORO', 'HOUSEWIFE', '09850876258', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116868150037', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(121, 'STU-2026-0087', 'VALERIO, JYRAH MAE TAGLE', 'TAGLE', 'jyrahmaevalerio@gmail.com', '$2y$10$HKvoncnsqXCMPXzI.4UCQ.4JRV.mlfdlj2YNBuADVuRQ.M2v90Jwq', 'student', '11', '2026-05-17 23:36:28', NULL, 0, '2010-04-26', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SO. NABUSWANG, BRGY. CANMOROS, BINALBAGAN', '09056776157', 'BENJIE GEBORA VALERIO', '', '', 'MARRY JANE TAGLE', '', '', 'NENITA GEBORA VALERIO', '09056776157', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116876150021', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(122, 'STU-2026-0088', 'TIBAJARES, TIZZA VILLARMA', 'VILLARMA', 'tizzatibajares@gmail.com', '$2y$10$3XWUVm0pgowqd72roaHrzOuYqC368rUQ0Yg4LdZ5vTcb3WNzwcQye', 'student', '11', '2026-05-17 23:39:45', NULL, 0, '2010-09-26', 'Female', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09946805490', 'RADNEY MANONG TIBAJARES', '', '', 'CARMELITA MALAGNAO VILLARMA', 'HOUSEWIFE', '', 'CARMELITA VILLARMA TIBAJARES', '09946805490', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116868150066', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(123, 'STU-2026-0089', 'Abkilan, Cassiel Nietes', 'Nietes', 'cassielabkilan@gmail.com', '$2y$10$.1jF1dV4bsijNsP0lwuuLugpCIsp/csSGv1EmUjveD5BH0QM9nXtW', 'student', '10', '2026-05-17 23:45:46', NULL, 0, '2011-12-03', 'Female', 14, 'Himamaylan, Negros Occidental', 'Filipino', 'Catholic', 'Crossing Aguisan, Brgy. Aguisan, Himamaylan, Negros Occidental', '09706373872', 'Jose Maria Locsin Abkilan', 'Laborer', '09706373872', 'Anne Bitalac Nietes', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '92', 1, '404050170028', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(124, 'STU-2026-0090', 'Sarasa, Kent Osorio', 'Osorio', 'kentsarasa@gmail.com', '$2y$10$YEfGrAzyM.adqdME4gyVEOknl2MlTlf0XuUDzcUR2zKsYe7VZMR6i', 'student', '10', '2026-05-17 23:53:26', NULL, 0, '2011-03-06', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Johnrey Trinidad Sarasa', 'Fisherman', '', 'Meriam Aldea Osorio', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '77', 1, '116876160015', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(125, 'STU-2026-0091', 'PANCHO, BEN LAWRENZ SALON', 'SALON', 'benlawrenzpancho@gmail.com', '$2y$10$kNpJ9cZ5SIDjiqMkoEfp/elch5gOd3fzIpQScbO9hxbpkCMEWfogK', 'student', '9', '2026-05-17 23:54:48', NULL, 0, '2011-07-06', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW,BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ROGER C GAMPOSILAO', '', '', 'WILMA O. ANONO', '', '', 'GIEBELYN SALON PANCHO', '', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '122814170064', 0, 0, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(126, 'STU-2026-0092', 'CASUYON, RODGIN SABLON', 'SABLON', 'rodgincasusyon@gmail.com', '$2y$10$LUI./cjJ4Fv3vE6avX0wL.7Ulpn0K8q8Z2vPB2T/KuQ745jjlda2W', 'student', '10', '2026-05-17 23:59:39', NULL, 0, '2010-01-30', 'Male', 16, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RADNEY ZAMORA CASUYON', 'FISHERMAN', '', 'GINA SABLON CASUYON', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116868150020', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(127, 'STU-2026-0093', 'Romero, Angelica Aguilar', 'Aguilar', 'angelicaromero@gmail.com', '$2y$10$K0sFFL2U4Tb3vGlMC611oOuy82BJ4Ajl7KHcae/x7JCFMMQhVjFw6', 'student', '10', '2026-05-18 00:00:30', NULL, 0, '2010-11-04', 'Female', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Ariel Tamayo Romero', 'Fisherman', '', 'Snooky Nanggal Aguilar', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '80', 1, '116876160003', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(128, 'STU-2026-0094', 'PANCHO, JOHN GIVEN SABLON', 'SABLON', 'johngivenpancho@gmail.com', '$2y$10$fwCYc2Sv8TjU.Cmp8qSHjua9aPGRoQQYZ6TD.z/CKCGwRzA7L6r/2', 'student', '12', '2026-05-18 00:04:36', NULL, 0, '2009-01-03', 'Male', 17, '', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ROGER C GAMPOSILAO', '', '', 'WILMA O. ANONO', '', '', 'GIEBELYN SALON PANCHO', '', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '122814140042', 0, 0, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(129, 'STU-2026-0095', 'Piojo, Joylyn Fate Buhat', 'Buhat', 'joylynfatepiojo@gmail.com', '$2y$10$i1kmQyA1RDcI9n3CaZ8g/e3ZmWX.pFGJYtUvDwcREkgWTmoyx3F4.', 'student', '11', '2026-05-18 00:08:26', NULL, 0, '2010-04-20', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylon Shell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09482495604', 'Joemarie Flores Piojo', 'Fisherman', '09482495604', 'Jessebelle Moncada Buhat', 'Housewife', '09482495604', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '94', 0, '116876160003', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(130, 'STU-2026-0096', 'NIEVES, LAURENCE JOHN DELEJERO', 'DELEJERO', 'laurencejohnnieves@gmail.com', '$2y$10$1nRhMwmpwJ49FeR/uULJuOEGOvwVS8.WC0bdi9FTcJEc881fWARy.', 'student', '12', '2026-05-18 00:10:22', NULL, 0, '2009-04-14', 'Male', 17, '', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09516938896', '', '', '', 'MERCY GABASAN DELEJERO', 'FISH VENDEE', '09516938896', 'MERCY GABASAN DELEJERO', '09516938896', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 0, '116868140070', 0, 0, 0, '', 0, 0, '', '', 'promoted', NULL, NULL, NULL, NULL),
(131, 'STU-2026-0097', 'ROSALES, WINNIE MAE IMPERIAL', 'IMPERIAL', 'winniemaerosales@gmail.com', '$2y$10$Fdu3gSNBIwQA9AUtIBJqze3dnrB.s4Kcb6IHbRsURQlIoiGfgXP5G', 'student', '9', '2026-05-18 00:13:47', NULL, 0, '2011-04-29', 'Female', 15, 'MAKATI CITY', 'Filipino', 'ROMAN CATHOLIC', 'SO. NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09070988650', 'WILSON ELITIROS ROSALES', '', '', 'MERRY ROSE SARASA IMPERIAL', 'FISH VENDOR', '09070988650', 'MERRY ROSE SARASA IMPERIAL', '09070988650', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '80', 1, '224001160271', 0, 0, 0, '', 0, 0, '', 'TAGALOG, HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(132, 'STU-2026-0098', 'ZARCEDO, REGINE TRINIDAD', 'TRINIDAD', 'reginezarcedo@gmail.com', '$2y$10$kCFL0yhgW3s/JQusJZ3Si.Dxl5jz5WxJxIqfzP8Ic79WFKkTNvPSK', 'student', '9', '2026-05-18 00:17:57', NULL, 0, '2011-12-20', 'Female', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SO. NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09103744041', 'JOSE CONDA ZARCEDO SR.', 'FISHERMAN', '', 'ROSE ANN TAMAYO TRINIDAD', 'HOUSEWIFE', '09103744041', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '80', 1, '116876170008', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(133, 'STU-2026-0099', 'DEMANDANTE, RINZ MARK TRINIDAD', 'TRINIDAD', 'rinzmarkdemandante@gmail.com', '$2y$10$y45lMoj6BWr.0h5vuVgIUOeGMSCQ8SZNuVQEwy9eYQMHCpX9x4Mm.', 'student', '8', '2026-05-18 00:23:17', NULL, 0, '2011-12-17', 'Male', 14, '', 'Filipino', 'ROMAN CATHOLIC', 'SO. NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ROGEL BASILES DEMANDANTE', 'LABORER', '', 'MARISSA TAMAYO TRINIDAD', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 0, '116876170002', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(134, 'STU-2026-0100', 'TALLAFER, CLARENZ PHILIP VILLAFLOR', 'VILLAFLOR', 'clarenzphiliptallafer@gmail.com', '$2y$10$igd.8pzkwv15qM0O4G1KDe1DpypqtW1KVBxwi3eDtCiTtTsPp6t0W', 'student', '12', '2026-05-18 00:30:05', NULL, 0, '2007-09-01', 'Female', 18, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SO. NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09631527247', 'SHERWIN SARASA TALLAFER', 'FISHERMAN', '09631527247', 'RAQUEL ZULUETA VILLAFLOR', 'VENDOR', '09649528143', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '76', 1, '116876130007', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(135, 'STU-2026-0101', 'CANILLO, JAYMARK MANGUBAT', 'MANGUBAT', 'jaymarkcanillo@gmail.com', '$2y$10$mmRsBDrxgbPNntR/GNgmqehwGvSIdHRYBCrrbJ1XgnCb37kT5nQmW', 'student', '10', '2026-05-18 00:35:24', NULL, 0, '2010-11-24', 'Male', 15, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', '', '09939565954', 'REY MARK ILANAN CANILLO', 'LABORER', '', 'JOCELYN MANGUBAT CANILLO', 'OFW', '', 'REY MARK ILANAN CANILLO', '09939565954', 'FATHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116868160030', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(136, 'STU-2026-0102', 'ENDOMA, JANE ROSE CERBOLLES', 'CERBOLLES', 'janeroseendoma@gmail.com', '$2y$10$SzSt9NwTZ.0xZyRj0m3kieHTjSmsC5.MG8Lr0ivJPPHjPHKRSCi3e', 'student', '8', '2026-05-18 00:48:26', NULL, 0, '2012-12-02', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK APITON, BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '09207146225', 'JONERIE GARGARITA ENDOMA', 'LABORER', '09207146225', 'REJANE GONZALES CERBOLLES', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(137, 'STU-2026-0103', 'CORDOVA, RYLE OGAHAYON', 'OGAHAYON', 'rylecordova@gmail.com', '$2y$10$CSGVQB8j7LWlR9KYpWI2c.g5yFLq8T5kluoMNwNWFukIXjOyiPcPe', 'student', '8', '2026-05-18 00:51:04', NULL, 0, '2013-04-30', 'Male', 13, '', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09509408963', 'RAFFY GATPATAN CORDOVA', 'FISHERMAN', '', 'MA. LILY ELLADORA OGAHAYON', 'FISH VENDOR', '09509408963', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, '116868180043', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(138, 'STU-2026-0104', 'ESTIMADA, ALLEN REY GREGORIO', 'GREGORIO', 'allenreyestimada@gmail.com', '$2y$10$dTaMMwQqXmOOvgeep1bvSOQaLsaEKYLP3GnZj7x5tWf/XhXaZ3nri', 'student', '11', '2026-05-18 00:58:43', NULL, 0, '2010-04-11', 'Male', 16, 'DAUIN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09941855026', 'ANGEL ESPARAGOZA ESTIMADA', 'LABORER', '', 'DIVINA GREGORIO ESTIMADA', 'HOUSEWIFE', '09945575685', '', '', '', 'DAUIN NATIONAL HIGH SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '80', 1, '120229150013', 0, 1, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(139, 'STU-2026-0105', 'BARCELONA, TERESA', '', 'teresabarcelona@gmail.com', '$2y$10$1LITK.BAToQONpy32hplEemLzU8EMMHCCc3lRTcV8F6bI6E7bn9e.', 'student', '9', '2026-05-18 01:03:12', NULL, 0, '2012-02-09', 'Female', 14, '', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09517550610', '', '', '', '', '', '', 'MILKY GEPA BARCELONA', '09517550610', 'SISTER-IN-LAW', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, '', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(140, 'STU-2026-0106', 'ALVAREZ, JOSHUA TORREFRANCA', 'TORREFRANCA', 'joshuaalvarez@gmail.com', '$2y$10$ryfMegCIqol20k4ZO2eenOcbcF1xgDzw1uy/XJFsZ1.c7ew2WxOH2', 'student', '10', '2026-05-18 01:16:24', NULL, 0, '2010-10-29', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'JESUMBERT MARIBUJOC ALVAREZ', '', '', 'GEMMA NOMBRE TORREFRANCA', '', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116868160001', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(141, 'STU-2026-0107', 'EVANGELIO, JESSERE PIDO', 'PIDO', 'jessereevangelio@gmail.com', '$2y$10$YSIDKYe0Z5x1PahO/YMSfOuHp0IfgHEBVHnztwIQWUNuJJaNzo9iW', 'student', '11', '2026-05-18 01:20:11', NULL, 0, '2010-01-28', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09512508829', '', '', '', 'RICHEL PONTARON PIDO', '', '', 'RICHEL PIDO EVANGELIO', '09512508829', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '116868150027', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(142, 'STU-2026-0108', 'ROMANO, ERIKA PIDO', 'PIDO', 'erikaromano@gmail.com', '$2y$10$nCGNCyqPQvxYvm7vTCv0..mVczkMn4Gi1z93/C4LX9AiReYwfd7Jy', 'student', '10', '2026-05-18 01:23:16', NULL, 0, '2010-11-23', 'Female', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ENRIQUE DIEGA ROMANO', '', '', 'ROSALIE PONTARON PIDO', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 1, '116868160023', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(143, 'STU-2026-0109', 'SARASA, CYRIL JOHN VILLAFLOR', 'VILLAFLOR', 'cyriljohnsarasa@gmail.com', '$2y$10$ZETnfi7xHiDO7kzhASCKee8yLXSpRZLbqOIAMBIiLhLfVHkvv6JWG', 'student', '8', '2026-05-18 01:28:20', NULL, 0, '2013-01-29', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'SO. NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09507675697', 'EDUARD SARASA SR.', 'FISHERMAN', '', 'MA. TERESA LUCENO VILLAFLOR', 'HOUSEWIFE', '', 'JENNYFER ZULUETA SARASA', '09507675697', 'GUARDIAN', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(144, 'STU-2026-0110', 'MEDIODIA, DARA FAYE', '', 'darafayemediodia@gmail.com', '$2y$10$sdscRMAwaPf31sH8aPydju5zznimcItdYI6YW9JlbZMifnfqt8/FW', 'student', '10', '2026-05-18 01:31:58', NULL, 0, '2009-11-13', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'ZONE 5, BRGY. SAN PEDRO, BINALBAGAN, NEGROS OCCIDENTAL', '', '', '', '', 'DINAH NOBLEZA MEDIODIA', '', '', 'MA. DAISY NOBLEZA MEDIODIA', '', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116867160005', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(145, 'STU-2026-0111', 'Abkilan, Shyle Nietes', 'Nietes', 'shyleabkilan@gmail.com', '$2y$10$EB3IWzO8BHy7/uulmEvuU.yyBej/uDaURR.D7IP1xfewxS7..7cUy', 'student', '11', '2026-05-18 01:37:27', NULL, 0, '2010-10-15', 'Female', 15, 'Himamaylan, Negros Occidental', 'Filipino', 'Catholic', 'Crossing Aguisan, Brgy. Aguisan, Himamaylan, Negros Occidental', '09706373812', 'Jose Maria Locsin Abkilan', 'Laborer', '09706373812', 'Anne Bitalac Nietes', 'Housewife', '09706373812', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '97', 1, '117089150030', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(146, 'STU-2026-0112', 'Amante, Jonalyn Casuyon', 'Casuyon', 'jonalynamante@gmail.com', '$2y$10$2DHJ8NDgSFOLstW7B8p0Ne9ciavwSlX5q2dJwLcu9Pw1OTqM71CFS', 'student', '11', '2026-05-18 01:46:26', NULL, 0, '2010-02-27', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Tuway, Brgy. Canmoros, Binalbagan, Negros Occidental', '09630885760', 'Juanito Badana Amante', 'Foreman', '09630885760', 'Mannilyn Zamora Casuyon', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 1, '116868150056', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', '', '', NULL, NULL),
(147, 'STU-2026-0113', 'Carmelino, Ronalyn Paconla', 'Paconla', 'ronalyncarmelino@gmail.com', '$2y$10$KeiZNCfpdY1TbWXsCwsxOOgcsGQ3Mmlbyjk02iNIUEKNldp0nNeZ2', 'student', '11', '2026-05-18 01:53:15', NULL, 0, '2010-03-22', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Inapugan, Brgy. Santol, Binalbagan, Negros Occidental', '09706373812', 'Cleopas Carmelino', 'Laborer', '', 'Rosacilla', 'Housewife', '', '', '', '', 'TLCA - Bin', '', '', '90', 1, '116872150035', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', '', '', NULL, NULL),
(148, 'STU-2026-0114', 'HEMPAYAN, SHEENA MAY MATEO', 'MATEO', 'sheenamayhempayan@gmail.com', '$2y$10$5TeiBqCRlnFnFj9/xrHNnewWHHUNZRqSaFrgWORxXme9XHDevlmIm', 'student', '7', '2026-05-18 23:24:18', NULL, 0, '2012-09-10', 'Female', 13, 'BINALBAGAN', 'Filipino', '', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ERWIN AURELLO HEMPAYAN', 'LABORER', '', 'INA MAY ESTANIEL MATEO', 'HOUSEWIFE', '', '', '', '', 'AMONTAY ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116879180019', 0, 0, 0, '', 1, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(149, 'STU-2026-0115', 'GARSOLA, CHRISTINE GALABASA', 'GALABASA', 'christinegarsola@gmail.com', '$2y$10$hIHHt/C4O0NV0kjusFpyN.hfmJY1yCDmq08xj1ftRZMKRqDMGHn4.', 'student', '7', '2026-05-18 23:28:56', NULL, 0, '2012-06-22', 'Female', 13, 'BINALBAGAN', 'Filipino', '', 'SO. KABALAN-TIANAN, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09853033317', 'RONEL ACABO GARSOLA', 'LABORER', '', 'GLESA GALABASA GARSOLA', 'HOUSEWIFE', '09853033317', '', '', '', 'AMONTAY ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116879180007', 0, 0, 0, '', 1, 0, '', 'BISAYA, HILIGAYANON', 'promoted', '', '', NULL, NULL),
(150, 'STU-2026-0116', 'METCHABE, JESSERRY FUNDADOR', 'FUNDADOR', 'jesserrymetchabe@gmail.com', '$2y$10$h5yNLYuD.JK1P0EbxZvZsuFkF0vH9e0F5oWUMstayDUNzCEoU4jAa', 'student', '7', '2026-05-18 23:32:44', NULL, 0, '2014-03-22', 'Female', 12, 'BINALBAGAN', 'Filipino', '', 'SO. NABILOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09317865262', 'JOEAVANE ELARCOSA METCHABE', 'LABORER', '', 'RENILYN PELLAPE FUNDADOR', 'HOUSEWIFE', '', '', '', '', 'AMONTAY ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116864200013', 0, 0, 0, '', 1, 0, '', 'BISAYA, HILIGAYANON', 'promoted', '', '', NULL, NULL),
(151, 'STU-2026-0117', 'CRISPE, JENOAH IVAN GAVILE', 'GAVILE', 'jenoahcrispe@gmail.com', '$2y$10$w44TB2YVVpcssTfWhaeIne/BRuaclG0SuihqN49P9FNiZTjAOR6Nm', 'student', '10', '2026-05-18 23:38:26', NULL, 0, '2011-03-20', 'Male', 15, 'QUEZON CITY', 'Filipino', 'ROMAN CATHOLIC', '1829, SITIO IPIL-IPIL, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09291768543', 'JOVANNE IVAN CASUYON CRISPE', 'VENDOR', '09291768543', 'RODA DELACRUZ GAVILE', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '440556160013', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(152, 'STU-2026-0118', 'LAZARTE, CHRISTOFER TEORIMA', 'TEORIMA', 'christoferlazarte@gmail.com', '$2y$10$w1xjGzXVIxcd1zmK9pKziOYnVjMF1Nm2wzn6LRPu7R14B2CYCydwm', 'student', '8', '2026-05-18 23:40:27', NULL, 0, '2013-08-05', 'Male', 12, '', 'Filipino', 'ROMAN CATHOLIC', '', '0938746', '', '', '', '', '', '', 'APRIL MAY SARAD TEORIMA', '09387498341', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 0, '116880180016', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(153, 'STU-2026-0119', 'ATIENZA, JOHN ROY GELLE', 'GELLE', 'johnroyatienza@gmail.com', '$2y$10$hc5lW3IFFc9Wqe.kQ9iT1eu7X4GDBrF/v9APrd29o.ONk7BLsL6ku', 'student', '8', '2026-05-18 23:45:40', NULL, 0, '2012-10-26', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ROYROY SALVACION ATIENZA', 'LABORER', '', 'SUZIE GELLE ATIENZA', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(154, 'STU-2026-0120', 'CABANDO, RYAN', '', 'ryancabando@gmail.com', '$2y$10$l4yVJgFRL8QxSxJLjdAKn.J6NKzVOFOTV8HALhLN8uPCLnd9piGqe', 'student', '8', '2026-05-18 23:48:30', NULL, 0, '2012-07-04', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09510765773', '', '', '', '', '', '', 'JONAFIL BELARMINO CABANDO', '09510765773', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(155, 'STU-2026-0121', 'PASALGON, JIPEE PINEDA', 'PINEDA', 'jipeepasalgon@gmail.com', '$2y$10$BlQJZrzHguhH6HRXWWVWGOkQaCEnE495EsMj81hLQvKXTiLU9SYSO', 'student', '9', '2026-05-18 23:51:20', NULL, 0, '2011-08-13', 'Female', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09072501036', 'JUPITER FLORES PASALGON', 'FISHERMAN', '', 'MARICEL PINEDA PASALGON', 'VENDOR', '09072501036', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868160011', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(156, 'STU-2026-0122', 'ESCARO, JAY-R GELLE', 'GELLE', 'jayrescaro@gmail.com', '$2y$10$g36UcG3Rxl9fay./2MaX9eyTehs52QGG9O3mHEkPk3k7cYPZAERca', 'student', '12', '2026-05-18 23:54:38', NULL, 0, '0000-00-00', 'Male', 0, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09310687320', 'ROMY VESINO ESCARO', 'LABORER', '', 'GEMMAVEL GELLE ESCARO', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868140061', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(157, 'STU-2026-0123', 'CABANDO, JANMARK', '', 'janmarkcabando@gmail.com', '$2y$10$CSerThHXFoU1dxNY735WhOn6NINlQW5NxGJvAAr9rMajmv5gwGq/u', 'student', '12', '2026-05-18 23:56:55', NULL, 0, '2009-06-09', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09510765773', '', '', '', '', '', '', 'JONAFIL BELARMINO CABANDO', '09510765773', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 1, '116868140035', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(158, 'STU-2026-0124', 'ATIENZA, JOHN LOYD GELLE', 'GELLE', 'johnloydatienza@gmail.com', '$2y$10$H6CDnhsA3AeaR2Gc.VJk7.gK64V0kqrAJjEZkdfKbF2hMvpa3qE12', 'student', '12', '2026-05-18 23:59:11', NULL, 0, '0000-00-00', 'Male', 0, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09629312253', 'ROYROY SALVACION ATIENZA', 'LABORER', '', 'SUZIE GELLE ATIENZA', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868140031', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(159, 'STU-2026-0125', 'CABANDO, JAIRUS BELARMINO', 'BELARMINO', 'jairuscabando@gmail.com', '$2y$10$fo0txGhMNVS.8/zOFrsxleuALXkvckB/r00DJneS29k..pjBlCOq6', 'student', '12', '2026-05-19 00:03:29', NULL, 0, '2008-11-20', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09510765773', '', '', '', '', '', '', 'JONAFIL BELARMINO CABANDO', '09510765773', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 1, '116868140034', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(160, 'STU-2026-0126', 'PASALGON, JOMARI PINEDA', 'PINEDA', 'jomaripasalgon@gmail.com', '$2y$10$X0KKoLnx9.6bFd2M.rK.OOpPbp70GMT6C6iGA59x/.g/IHtRPc5qe', 'student', '12', '2026-05-19 00:06:19', NULL, 0, '2009-03-15', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09072501036', 'JUPITER FLORES PASALGON', 'FISHERMAN', '', 'MARICEL PINEDA PASALGON', 'VENDOR', '09072501036', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868140022', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(161, 'STU-2026-0127', 'OLIVOS, RAIZA MAE BERANG-BERANG', 'BERANG-BERANG', 'raizamaeolivos@gmail.com', '$2y$10$jgNCGd6StURM2s4.mVbeMe02Gm7IouLGu4AEgj/K8YOIGwXaXP2A.', 'student', '8', '2026-05-19 00:09:22', NULL, 0, '2013-02-10', 'Female', 13, NULL, 'Filipino', 'INC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09508965174', '', '', '', '', '', '', 'MARICOR FRANCISCO BALANGAO', '09508965174', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(162, 'STU-2026-0128', 'FRANCISCO, MARK ZAIJAN SELGA', 'SELGA', 'markzaijan@gmail.com', '$2y$10$CHNRbS4imvD2aZ77LI.tnOdwg1tUQBaicIsl8ZMkGz2agAoTVNc0C', 'student', '11', '2026-05-19 00:12:08', NULL, 0, '2010-01-02', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09295362557', 'JASON YUDE FRANCISCO', 'FISHERMAN', '', 'MARY ANN SELGA FRANCISCO', 'VENDOR', '09293562557', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '116868150004', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(163, 'STU-2026-0129', 'TORREFRANCA, JV PARCON', 'PARCON', 'jvtorrefranca@gmail.com', '$2y$10$hyR79x0KrZwT.FjqjxE8MutH2O5f8M5/hgM7koW5TegezywH5eebC', 'student', '11', '2026-05-19 22:36:12', NULL, 0, '2009-11-09', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCC.', '', 'VICENTE TORREFRANCA', 'FISHERMAN', '', 'PINKY INAPAN TORREFRANCA', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 1, '116868150026', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(164, 'STU-2026-0130', 'TORREFRANCA, CHRISTOPHER JOHN GATPATAN', 'GATPATAN', 'christopherjohn@gmail.com', '$2y$10$uv9iZ5et6x2kelUG/3YRK.QperB/yf2VdIpWtJmeKrVabeqDfakiG', 'student', '11', '2026-05-19 22:41:02', NULL, 0, '2010-07-06', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'CRISANTO FRANCISCO TORREFRANCA', 'FISHERMAN', '', 'JAYNE GATPATAN TORREFRANCA', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 1, '440568150015', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(165, 'STU-2026-0131', 'EVANGELIO, JOHN VINCENT', '', 'johnvincent@gmail.com', '$2y$10$045yuvRKdpXxwOnqmWQRZ.MVnv9rCzXi0YUH1Sjpkfv96LZP/XDa.', 'student', '11', '2026-05-19 22:44:25', NULL, 0, '2010-09-27', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09648186908', 'FRANCISCO PADILLA EVANGELIO', '', '', 'FLORDELIZA FRANCISCO ESTOQUIA', 'HOUSEWIFE', '', 'JOCELYN ESTOQUIA EVANGELIO', '09648186908', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 1, '116868150062', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(167, 'STU-2026-0132', 'ALCORIN, SHENALYN MATEO', 'MATEO', 'shenalynalcorin@gmail.com', '$2y$10$hLugpw46AcjNFBDWDU3iv.y2GbLFP8jX5cEIn4yeV4NFhpH8X8cYm', 'student', '8', '2026-05-19 22:59:18', NULL, 0, '2013-01-18', 'Female', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'TAMBU, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RECHARLD MATIAS ALCORIN', 'LABORER', '', 'MA. AILEEN MATEO ALCORIN', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '116879180018', 0, 0, 0, '', 0, 1, 'ITOMAN MAGHAT-BUKIDNON', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(168, 'STU-2026-0133', 'RUDA, MIKE SAN-ORIL', 'SAN-ORIL', 'mikeruda@gmail.com', '$2y$10$UtkUlwxYmDun64vvPJ/fn.tiXLO8w7oglZmFKhzThtGL7.JbWJugu', 'student', '8', '2026-05-19 23:04:12', NULL, 0, '2011-12-30', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09387969950', 'RUDA GRAPA ROPILIANO', 'LABORER', '', 'EMILY MATEO SAN-ORIL', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116872170011', 0, 1, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', '', '', NULL, NULL),
(169, 'STU-2026-0134', 'DELOS REYES, RENMAR BORDAGO', 'BORDAGO', 'renmardelosreyes@gmail.com', '$2y$10$wqdQL3zncfbR8gB7VwAbaemG8jxY851wdZkgSlFrQ54N27GMQ5AGK', 'student', '8', '2026-05-19 23:08:49', NULL, 0, '2013-01-28', 'Male', 13, '', 'Filipino', 'ROMAN CATHOLIC', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09309611678', 'RENANTE SINTO DELOS REYES', 'LABORER', '', 'MARICEL PECORE DELOS REYES', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 0, '116879180015', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(170, 'STU-2026-0135', 'SALVADORA, JOHN ART METSABE', 'METSABE', 'johnartsalvadora@gmail.com', '$2y$10$HYPabsjdrWYKrvDjHri3YujPaMOwTpeOaR/3xEtXB/L9E7SxjM2WW', 'student', '8', '2026-05-19 23:13:27', NULL, 0, '2012-05-08', 'Male', 14, '', 'Filipino', 'ROMAN CATHOLIC', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '096509336108', 'RENATO AWA SALVADORA', 'LABORER', '', 'MILAGROS CUEVAS METSABE', 'VENDOR', '', 'MELGIE METSABE SALVADORA', '096509336108', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 0, '116879170023', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(171, 'STU-2026-0136', 'Casuyon, Jeymar Sasel', 'Sasel', 'jeymarcasuyon@gmail.com', '$2y$10$/3UJrflauSWlMuPRWpC8zev.0We0CF3tPa05QUrSieim5esVORh8.', 'student', '11', '2026-05-19 23:31:32', NULL, 0, '2010-04-25', 'Male', 16, 'Antique', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Marco Ortenila Casuyon', 'Fisherman', '', 'Frezel Hantok Sasel', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '85', 1, '116868150035', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(172, 'STU-2026-0137', 'BUT-AY, ROSABEL CAPASILAN', 'CAPASILAN', 'rosabelbutay@gmail.com', '$2y$10$CZDrIZMYq2dwE39KbXUsIey0b8vZJs.mYK1Mh.ovApWf8972uZaIy', 'student', '8', '2026-05-19 23:40:31', NULL, 0, '2009-10-10', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'Prk. Nabirasan, Brgy. Amontay, Binalbagan, Negros Occidental', '', 'JUNE AURELIO BUT-AY', 'LABORER', '', 'VANGE SAEL CAPASILAN', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 1, '116864170025', 0, 1, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(173, 'STU-2026-0138', 'VALLENTEN, JAMES METSABE', 'METSABE', 'jamesvallenten@gmail.com', '$2y$10$HJS/0k9eg09kGwgj0IDF7Ooqpd7cIpgObbGl8K.p9JOYnTdJRLK6.', 'student', '8', '2026-05-19 23:43:40', NULL, 0, '2009-06-05', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABIRASAN, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'NONEBOY GARY VALLENTEN', 'LABORER', '', 'GINELYN BUSTAMANTE METSABE', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 1, '116864180028', 0, 1, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', '', '', NULL, NULL);
INSERT INTO `users` (`id`, `empidno`, `name`, `middle_name`, `email`, `password_hash`, `role`, `grade_level`, `created_at`, `image`, `archived`, `date_of_birth`, `gender`, `age`, `place_of_birth`, `nationality`, `religion`, `home_address`, `contact_number`, `father_name`, `father_occupation`, `father_contact`, `mother_name`, `mother_occupation`, `mother_contact`, `guardian_name`, `guardian_contact`, `guardian_relationship`, `last_school_attended`, `last_school_address`, `school_year_completed`, `general_average`, `has_lrn`, `lrn_number`, `is_returnee`, `is_transfer_in`, `has_special_needs`, `special_needs_type`, `is_4ps_beneficiary`, `is_indigenous`, `indigenous_group`, `mother_tongue`, `retention_status`, `retention_reason`, `retention_school_year`, `retention_updated_at`, `retention_updated_by`) VALUES
(174, 'STU-2026-0139', 'ALCORIN, RAFAEL MATEO', 'MATEO', 'rafaelalcorin@gmail.com', '$2y$10$L64kAf.z1FlOPpeYXHa5DOP.EQ1.87P2oJLd9BNRaeYcTsgFqiffC', 'student', '10', '2026-05-19 23:46:27', NULL, 0, '2011-09-15', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO TAMBU, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RECHARLD MATIAS ALCORIN', 'LABORER', '', 'MA. AILEEN MATEO ALCORIN', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116879170046', 0, 1, 0, '', 1, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(175, 'STU-2026-0140', 'Gantalao, Breksboy Topis', 'Topis', 'breksboygantalao@gmail.com', '$2y$10$NwrGcDY8YerW5MicIc8Idu4A7ytxLFa6kXb/Y0kTVLED4PpVyCxxK', 'student', '11', '2026-05-19 23:49:56', NULL, 0, '2010-09-25', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Cabalanti-anan, Brgy. Amontay, Binalbagan, Negros Occidental', '09534151871', 'Jessie Mojello Gantalao', 'Fisherman', '09534151871', 'Jesa Absin Topis', 'Housewife', '09534151871', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalabagan, Negros Occidental', '2025-2026', '83', 1, '116879160003', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', '', '', NULL, NULL),
(176, 'STU-2026-0141', 'DELOS REYES, MECHIE ANN BURDAGO', 'BURDAGO', 'mechieanndelosreyes@gmail.com', '$2y$10$QXPyi3iTDRjvEA4IovBt0Oa/kkX4e3KezfQNdvMnWXc4ThQhKdCKi', 'student', '10', '2026-05-19 23:52:21', NULL, 0, '2010-12-12', 'Female', 15, 'BINALBAGAN', 'Filipino', '', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09309611678', 'RENANTE  SINTO DELOS REYES', 'LABORER', '', 'MARICEL PECORE DELOS REYES', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '', 0, 0, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(177, 'STU-2026-0142', 'Jocson, Qued Jun Suarez', 'Suarez', 'quedjunjocson@gmail.com', '$2y$10$VtI1tQSyWohrVfZG8FeRuOCTD1SQrWcDqVdc.r1z3sevxg1xQlVCG', 'student', '11', '2026-05-20 00:01:56', NULL, 0, '2009-12-14', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'INC', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Randy Jocson', 'Fisherman', '', 'Anabel Suarez', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 0, '116868150005', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(178, 'STU-2026-0143', 'Cañedo, Justin Noejin Jocson', 'Jocson', 'justinnoejincanedo@gmail.com', '$2y$10$qqxqTDZndcJRneTkWwAPFOwee.MsKIBJn0o3kUYmw8wNGaMkpIBNy', 'student', '12', '2026-05-20 00:15:19', NULL, 0, '2009-08-12', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'INC', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Noel Colimbo Cañedo', 'Fisherman', '', 'Jinky Jocson Cañedo', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '81', 1, '116868140059', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(179, 'STU-2026-0144', 'METCHABE, JANIEL FUNDADOR', 'FUNDADOR', 'janielmetchabe@gmail.com', '$2y$10$w9vvjeF1n7QMnGdrM8xpIu2armb38GHEFkNhyMhLkPBm5T8/BiUCy', 'student', '11', '2026-05-20 00:17:16', NULL, 0, '2010-09-30', 'Female', 15, 'BINALBAGAN', 'Filipino', '', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09317865262', 'JOEAVANE ELARCOSA METCHABE', 'LABORER', '', 'RENILYN PELLAPE FUNDADOR', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116878150055', 0, 0, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', '', '', NULL, NULL),
(180, 'STU-2026-0145', 'SALVADORA, RENA JEAN METSABE', 'METSABE', 'renajeansalvadora@gmail.com', '$2y$10$wZF/I.GXs.ubrLa2eCpID.bEwjxJb2CyJDJhV6FLNr293zFJHHngu', 'student', '12', '2026-05-20 00:24:01', NULL, 0, '2008-08-08', 'Female', 17, '', 'Filipino', 'ROMAN CATHOLIC', 'SITIO MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '096509336107', 'RENATO AWA SALVADORA', 'LABORER', '', 'MILAGROS CUEVAS METSABE', 'HOUSEWIFE', '', 'MELGIE METSABE SALVADORA', '096509336107', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 0, '116879150007', 0, 0, 0, '', 0, 0, '', '', 'promoted', '', '', NULL, NULL),
(181, 'STU-2026-0146', 'TREBENIA, CRIZ JOHN', '', 'crizjohntrebenia@gmail.com', '$2y$10$4UFYeKArBLq0gpLVBpG5sOi8GexKM30rew/1zGCMFeJh7t0uoIoOG', 'student', '9', '2026-05-20 00:29:45', NULL, 0, '2012-08-01', 'Male', 13, 'MANDALUYONG CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09105626419', '', '', '', 'MAYBEL AGUILLON TREBENIA', 'HOUSEWIFE', '', 'MAYBEL AGUILLON TREBENIA', '09105626419', 'MOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868170039', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(182, 'STU-2026-0147', 'Estaniel, Rowel Joy Anastacio', 'Anastacio', 'roweljoyestaniel@gmail.com', '$2y$10$5df883YFRLf.SMxbzPcD9uG0/4aWIGU52WYzSYEc/wN29LiwdHbbe', 'student', '12', '2026-05-20 00:30:40', NULL, 0, '2008-10-29', 'Female', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Cabadbaran, Brgy. Santol, Binalbagan, Negros Occidental', '', 'Rowel Sitoy ESataniel', 'Laborer', '', 'Sherly Galabasa Anastacio', 'Housewife', '', 'Shyne Nicole Estaniel Bonghanoy', '', 'Aunt', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '84', 1, '116872140029', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(183, 'STU-2026-0148', 'MABAYAN, LUELYN ANONO', 'ANONO', 'luelynmabayan@gmail.com', '$2y$10$DC3iZCy.HB.SAJqUZNHhyOujo4sjabb3U.t8S37GONmdzB7FT19Ki', 'student', '12', '2026-05-20 00:34:44', NULL, 0, '2009-01-22', 'Female', 17, 'CAUAYAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RUEL MOLITA MABAYAN', 'LABORER', '', 'MARIA LUZ PACONLA ANONO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 1, '116868140029', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(184, 'STU-2026-0149', 'MABAYAN, ROY ANONO', 'ANONO', 'roymabayan@gmail.com', '$2y$10$NgumJbFQZKkx5JoRp6Dllu5bZ4XhrAtvcOqK2cD7yBeY/JRz0VuNK', 'student', '8', '2026-05-20 00:43:25', NULL, 0, '2012-12-14', 'Male', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09649919609', 'RUEL MOLITA MABAYAN', 'LABORER', '', 'MARIA LUZ PACONLA ANONO', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(185, 'STU-2026-0150', 'JUNSAY, JHONRAY PINEDA', 'PINEDA', 'jhonrayjunsay@gmail.com', '$2y$10$IjJkjG4MCDu4t1V9aHgY8eL7DQOBUg7JDyqhXpCPhsO6YdkVaXD1C', 'student', '11', '2026-05-20 00:47:04', NULL, 0, '2010-04-04', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09637422917', 'RODOLFO TRIBENIA JUNSAY', '', '', 'MARY JOY PIORNATO PINEDA', 'VENDOR', '09637422917', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116868150024', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(186, 'STU-2026-0151', 'ZARCEDO, REZA JOY TRINIDAD', 'TRINIDAD', 'rezazarcedo@gmail.com', '$2y$10$t4Dd29xazgsHHWIbzdFwjeCq43QRRkNJbo4sLJCqMAjWeN3UMS8hW', 'student', '7', '2026-05-20 00:52:24', NULL, 0, '2014-02-19', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09103744041', 'JOSE CONDA ZARCEDO SR.', 'FISHERMAN', '', 'ROSE ANN TAMAYO TRINIDAD', 'VENDOR', '', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116876190003', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(187, 'STU-2026-0152', 'DEMANDANTE, RICHMOND TRINIDAD', 'TRINIDAD', 'richmonddemandante@gmail.com', '$2y$10$vOm7FfYGbhfM2SNHGBcwDuZyM8tXWDeLlxaOX5HB7xRQDZkJ9yhUS', 'student', '7', '2026-05-20 00:57:14', NULL, 0, '2013-06-27', 'Male', 12, 'LEMERY, ILOILO', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ROGEL BASILES DEMANDANTE', 'LABORER', '', 'MARISSA TAMAYO TRINIDAD', 'HOUSEWIFE', '', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 1, '116876180004', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(188, 'STU-2026-0153', 'PELARIN, MARCELINA TANALGO', 'TANALGO', 'marcelinapelarin@gmail.com', '$2y$10$//V64l/YXHswb/QMJgWutedqKNDiBlmLLbriUP09iksELyRYis3pm', 'student', '7', '2026-05-20 01:09:06', NULL, 0, '2010-06-04', 'Female', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'MARCELO VILLARAN PELARIN', '', '', 'JOCELYN TANALGO PELARIN', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116868160046', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(189, 'STU-2026-0154', 'TORREFRANCA, ZYREN GATPATAN', 'GATPATAN', 'zyrentorrefranca@gmail.com', '$2y$10$P5vR5dG9Fp6oHqGyqp6acOsjYCPSeX0eYGPuFG7KP2AnkJoeL8gWe', 'student', '7', '2026-05-20 01:17:01', NULL, 0, '2013-11-12', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09700326318', 'CRISANTO FRANCISCO TORREFRANCA', 'FISHERMAN', '', 'JAYNE GATPATAN TORREFRANCA', 'LABORER', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(190, 'STU-2026-0155', 'JOCSON, QUENNIE SUAREZ', 'SUAREZ', 'queeniejocson@gmail.com', '$2y$10$0hPQVf0tYigrayBKtMcQ5eCKeiNrfGNHEPtITpxUI0rGXyVMRgXIi', 'student', '7', '2026-05-20 01:20:22', NULL, 0, '2013-09-18', 'Female', 12, 'BINALBAGAN', 'Filipino', 'INC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09942848796', 'RANDY ASTORIA JOCSON', 'LABORER', '', 'ANABEL SUAREZ JOCSON', 'LABORER', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '116868190019', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(191, 'STU-2026-0156', 'MILLENDEZ, GAVIN JOSHUA', '', 'gavinjoshua@gmail.com', '$2y$10$ptDhvpf4QfvcPW6Cpu8VWuNl0.5Iin0/ybNed2OpVZBRggxL5xQ2u', 'student', '7', '2026-05-20 01:24:37', NULL, 0, '2014-08-14', 'Male', 11, 'CAGAYAN DE ORO CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK MAHOGANY, BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '09289361979', '', '', '', 'JOAN MYRR SEVILLA MILLENDEZ', 'LABORER', '09289361979', 'NICO JARDINICO PAJA', '09497124242', '', 'SAN TEODORO ELEMENTARY SCHOOL', 'BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 1, '116866190064', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(192, 'STU-2026-0157', 'Galabasa, Christine Jane Tapis', 'Tapic', 'christinejanegalabasa@gmail.com', '$2y$10$odzt9HHrHkGX/J1wcqK3ReaSCPhLdGD9cqeOmz4Z3ao9qdvxSfmXq', 'student', '12', '2026-05-20 01:28:20', NULL, 0, '2009-02-14', 'Female', 17, 'Aguisan, Himamaylan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Cabadbaran, Brgy. Santol, Binalbagan. Negros Occidental', '09551165516', 'Jimboy Ruda Galabasa', 'Laborer', '09551165516', 'Juvilyn Tapis Galabasa', 'Housewife', '09551165516', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '91', 1, '116872150002', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', '', '', NULL, NULL),
(193, 'STU-2026-0158', 'JOCSON, KENT EMMANUEL MANDIN', 'MANDIN', 'kentemmanuel@gmail.com', '$2y$10$hg0DlC45Nx90CdDvtMRLd.d0DOv3NswaIy8Hgg7ezH72qzSvTrSsC', 'student', '7', '2026-05-20 01:30:42', NULL, 0, '2013-09-18', 'Male', 12, 'BINALBAGAN', 'Filipino', '', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'EDWIN CLAVEL JOCSON', 'LABORER', '', 'JOCELYN TANALGO MANDIN', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116868190030', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(194, 'STU-2026-0159', 'TILA, JERSEN PASALGON', 'PASALGON', 'jersentila@gmail.com', '$2y$10$XHcUJaY8ztpAbOeNmGIUh.nDdF7kWhS0XcC6HFg5Rtdnne1tTmOPG', 'student', '7', '2026-05-20 01:38:57', NULL, 0, '2011-03-08', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09852190665', 'JOEREN BERNESTO TILA', 'LABORER', '', 'VENUS FLORES PASALGON', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 1, '116868190012', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(195, 'STU-2026-0160', 'Callora, Norlyn Recla', 'Recla', 'norlyncallora@gmail.com', '$2y$10$LnqxH3iePdhaaRjmuFwA7Owge6oCy5.DIwPUEKrmSMPoXTXY7PpS.', 'student', '12', '2026-05-20 01:44:27', NULL, 0, '2009-03-15', 'Female', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Maslog, Brgy. Amontay, Binalbagan, Negros Occidental', '', 'Charlie Yu Callora', 'Laborer', '', 'Nancy But-ay Recla', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '85', 1, '116879140029', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(196, 'STU-2026-0161', 'Gante, Emily Anastacio', 'Anastacio', 'emilygante@gmail.com', '$2y$10$Cszsgr5N0aqhKdu6wh9Y4eJXwYTERC7dZzzqdexsEBdBiB7uIYEPK', 'student', '12', '2026-05-20 01:50:34', NULL, 0, '2009-01-02', 'Female', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Cabadbaran, Brgy. Santol, Binalbagan, Negros Occidental', '09940417983', 'Roberto Bordago Gante', 'Laborer', '', 'Susan Galabasa Anastacio', 'Housewife', '09940417983', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '89', 1, '116872140009', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(197, 'STU-2026-0162', 'VALENCIANO, BRIANA CZARA', '', 'brianaczara@gmail.com', '$2y$10$x3TQuAWkw7O10u.ucy327OtPYYPhZWHSm6qvoy.aKBNPv40B.LMCq', 'student', '7', '2026-05-20 18:30:19', NULL, 0, '2014-02-04', 'Female', 12, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK MAHOGANY, BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '09930926242', '', '', '', 'CZARINA IVY ZARA VALENCIANO', 'FREELANCER', '09930926242', '', '', '', 'SAN TEODORO ELEMENTARY SCHOOL', 'BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '116893190018', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(198, 'STU-2026-0163', 'TABUADA, JOHN DAVID MAGALLANES', 'MAGALLANES', 'johndavidtabuada@gmail.com', '$2y$10$bxHI5c/ALTXKOKzfkZ0EluCWGEEPv.mdC7qXEdJciw9ClTanKHI.q', 'student', '7', '2026-05-20 18:34:52', NULL, 0, '2012-10-25', 'Male', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK MAHOGANY, BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '09818024745', 'RICARTE MANDAL TABUADA', 'LABORER', '09818024745', 'ZARAH MAE ARAWIRAN MAGALLANES', 'BH WORKER', '09511488003', '', '', '', 'SAN TEODORO ELEMENTARY SCHOOL', 'BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '116893180023', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(199, 'STU-2026-0164', 'PELAGIO, ALDEN REX ECUBIN', 'ECUBIN', 'aldenrexpelagio@gmail.com', '$2y$10$uRDlA7Z4fjnkX.H2R8AZH.G.0zsZwisU3.CVwnkiIsEOi5N2qDLQy', 'student', '7', '2026-05-20 18:37:30', NULL, 0, '2014-08-01', 'Male', 11, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09812084503', 'ALMER SARAD PELAGIO', 'FISHERMAN', '', 'ROSY GESTALAO ECUBIN', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 1, '116868190011', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(200, 'STU-2026-0165', 'MAKILAN, KHIANA MARIE YEE', 'YEE', 'khianamarie@gmail.com', '$2y$10$tdYNRNxs2wsW6Ogb5K9icu8GVqhJoCXcXyeUwOp.C7UCQul9J01Xi', 'student', '7', '2026-05-20 18:45:59', NULL, 0, '2014-01-22', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'ZONE 7C, BRGY. SAN PEDRO, BINALBAGAN, NEGROS OCCIDENTAL', '09850701705', 'RONALD ALARCON MAKILAN', 'LABORER', '09850701705', 'JOEHINA YEE MAKILAN', 'HOUSEWIFE', '09945864341', '', '', '', 'TORRES ELEMENTARY SCHOOL', 'TORRES, BRGY. PROGRESO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '116880190022', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(201, 'STU-2026-0166', 'CASUYON, TRIXIE ANN LABARGAN', 'LABARGAN', 'trixieanncasuyon@gmail.com', '$2y$10$qFU.8C0l0xnSN2EM42OfX.rbGCRND43i2e40MULz6HsBxpoZibB8W', 'student', '7', '2026-05-20 19:35:17', NULL, 0, '2014-02-02', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NARRA, BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '09924799283', 'ALLEN AMBROSIO CASUYON', '', '', 'JENNETH JOY FLORES LABARGAN', '', '', 'JASON FLORES LABARGAN', '09924799283', 'UNCLE', 'SAN TEODORO ELEMENTARY SCHOOL', 'BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(202, 'STU-2026-0167', 'LAROYA, RICO JAN GALLANO', 'GALLANO', 'ricojanlaroya@gmail.com', '$2y$10$UwmkzSqn3Sju8UuCzCu28.UvoOEge3bb4KIdip2iGEFMLAPNM/h/S', 'student', '7', '2026-05-20 19:53:33', NULL, 0, '2013-04-01', 'Male', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09941484526', 'JOMARIE TAMAYO LAROYA', 'FISHERMAN', '', 'ANNALIZA GALLANO LAROYA', 'HOUSEWIFE', '09941484526', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 0, '116876180002', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(203, 'STU-2026-0168', 'TALLAFER, QUEENIE VILLAFLOR', 'VILLAFLOR', 'quennietallafer@gmail.com', '$2y$10$6NrYrCADPlCQp/sKIhEXVuMVXOUGWfYfv/.S1m5J.BQcPKq08h3Gi', 'student', '7', '2026-05-20 19:58:09', NULL, 0, '2014-01-25', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09649528143', 'SHERWIN SARASA TALLAFER', 'LABORER', '', 'RAQUEL ZULUETA VILLAFLOR', 'VENDOR', '09649528143', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 1, '116876180009', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(204, 'STU-2026-0169', 'SARASA, KEAN JAY OCTAVIO', 'OCTAVIO', 'keanjaysarasa@gmail.com', '$2y$10$XmXp1J1TgGQkaayHv/5vXev952wFwTiZWvJZsxeVI9NJmwf9I1RnW', 'student', '7', '2026-05-20 20:02:10', NULL, 0, '2014-05-20', 'Male', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09706928177', 'ROQUE TRINIDAD SARASA JR.', 'FISHERMAN', '09706928177', 'KATHERINE VALERIO OCTAVIO', 'HOUSEWIFE', '', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '116876190008', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(205, 'STU-2026-0170', 'CHAVEZ, ALJOMAR TAMAYO', 'TAMAYO', 'aljomarchavez@gmail.com', '$2y$10$QY77n/3z4oFQR9/VFx8hQ.voF7cayRw/mJGWEHUdjXl1WQ3ycQgx.', 'student', '7', '2026-05-20 20:13:16', NULL, 0, '2014-06-14', 'Male', 11, 'MANILA', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09309859640', 'JOE MAHUSAY CHAVEZ', 'FISHERMAN', '', 'ALMA BIASAC TAMAYO', 'HOUSEWIFE', '09309859640', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 1, '116876180021', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(206, 'STU-2026-0171', 'ROSALES, MIKEE IMPERIAL', 'IMPERIAL', 'mikeerosales@gmail.com', '$2y$10$1wUTGVrDuB9XPnicdbB/FulwfEDTgmXRfAgpRVAsIRnQNEh8y3O2O', 'student', '7', '2026-05-20 20:15:58', NULL, 0, '2013-09-20', 'Female', 12, NULL, 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '0970988650', 'WILSON ELITIROS ROSALES', 'FISHERMAN', '', 'MERRY ROSE SARASA IMPERIAL', 'HOUSEWIFE', '', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(207, 'STU-2026-0172', 'ESTIMADA, KENETH GABRIEL GOMEZ', 'GOMEZ', 'kenethgabriel@gmail.com', '$2y$10$ARF7uaTCi1wzdsSG3BVD3OGbY2n7awywS2ej2c1bR/oRPFX3aJaLK', 'student', '7', '2026-05-20 21:02:28', NULL, 0, '2014-06-14', 'Male', 11, 'MANILA', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RYAN ORDILLA ESTIMADA', 'LABORER', '', 'MARELYN PABILLO GOMEZ', 'LABORER', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116868190002', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(208, 'STU-2026-0173', 'ARELLANO, JUNE BETITA', 'BETITA', 'junearellano@gmail.com', '$2y$10$w0z0m02.mPyfyZVz/oPl4.eTY2KDaC7NO1FUY3jNBta5zV4vyVDjO', 'student', '7', '2026-05-20 21:08:23', NULL, 0, '2014-06-20', 'Male', 11, 'CAVITE CITY', 'Filipino', 'INC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09304342281', 'JONAS MALACA ARELLANO', '', '', 'JILL TALERO BETITA', '', '', 'LEONOR TALERO BETITA', '09304342281', 'GRANDMOTHER', 'OTON CENTRAL ELEMENTARY SCHOOL', 'OTON CENTRAL, ILOILO CITY', '2025-2026', '83', 1, '117308190004', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(209, 'STU-2026-0174', 'BERNADEZ, JOHN MICHAEL POBREZA', 'POBREZA', 'johnmichael@gmail.com', '$2y$10$b6kecH.dq2GqG7QNEAAp6uBVOKFVT5ZrYtYvmlXEjdJO99SFo13vO', 'student', '7', '2026-05-20 21:11:59', NULL, 0, '2012-12-30', 'Male', 13, 'BINALBAGAN', 'Filipino', 'BAPTIST', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RHOMEL CABUNAG BERNADEZ', 'FISHERMAN', '', 'RITCHEL POBREZA BERNADEZ', 'VENDOR', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(210, 'STU-2026-0175', 'SON, ROSE YENN JUNIO', 'JUNIO', 'roseyennson@gmail.com', '$2y$10$xjlvS012JASe36qkK4XRrOEVw6VDOWTMyAVmw2yHeXDgjbHgHd5fG', 'student', '7', '2026-05-20 22:33:22', NULL, 0, '2014-02-19', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09641005763', '', '', '', 'CHRISTINE JUNIO RUBIO', 'HOUSEWIFE', '09641005763', 'SHENNIE TRINIDAD JUNIO', '09621954725', 'AUNT', 'CANMOROS ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116876180007', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(211, 'STU-2026-0176', 'BASILIO, SHAINA MAE PARAICO', 'PARAICO', 'shainamaebasilio@gmail.com', '$2y$10$w0FMySEmNXlgwIrDaZFQo.fAXVO2PQUWKcUU8SnwpxkrP/kMrhZra', 'student', '7', '2026-05-20 22:42:43', NULL, 0, '2014-05-23', 'Female', 11, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09386327025', 'JOEVEL EVANGELIO BASILIO', 'LABORER', '', 'SHIRLEY LADUA PARAICO', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(212, 'STU-2026-0177', 'PAPAS, BERNARD JR. PASUIT', 'PASUIT', 'bernardpapas@gmail.com', '$2y$10$YqRW9W9ehjOWoywU3q6kPuEAqQnmkiilOOQbRo8EeEXxiS6li0uQS', 'student', '7', '2026-05-20 22:47:20', NULL, 0, '2013-06-17', 'Male', 12, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09812032139', 'BERNARD DEBALDERO PAPAS', 'FISHERMAN', '', 'RENIA LEGASPI PASUIT', 'VENDOR', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(213, 'STU-2026-0178', 'GARCIA, JOEY MONEZ', 'MONEZ', 'joeygarcia@gmail.com', '$2y$10$IQ3eQadMzj1ZZpXUNb.te.wfanQntzjASlVkeOUiMSpwkj7QzTEWS', 'student', '7', '2026-05-20 22:50:57', NULL, 0, '2013-05-23', 'Male', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'JOEY ZAMORA GARCIA SR.', 'FISHERMAN', '', 'ANNABELLE FERNANDEZ MONEZ', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(214, 'STU-2026-0179', 'LAGRADA, LEI ARPHAXAD PABONTOSA', 'PABONTOSA', 'LAlagrada@gmail.com', '$2y$10$4Tg1mXBM9XkE5ZzQM/bbHewcWJs0mHL9y1THYqpWk6/1QUT1hZXV6', 'student', '7', '2026-05-20 22:57:47', NULL, 0, '2014-09-12', 'Male', 11, 'BACOLOD CITY', 'Filipino', 'BAPTIST', 'PUROK MAHOGANY, BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '09176347213', 'DONNY REY CASTILLO LAGRADA', 'LOCALLY EMPLOYED', '09176347213', 'HAZEL GAY GAVILAN PABONTOSA', 'LOCALLY EMPLOYED', '09935149525', '', '', '', 'BINALBAGAN BEREAN CHRISTIAN ACADEMY, INC.', 'BRGY. SAN PEDRO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '404036200019', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(215, 'STU-2026-0180', 'VARGAS, EDRIE CALEB DELOESTE', 'DELOESTE', 'edriecalebvargas@gmail.com', '$2y$10$hDEU9fG7Advpb1SAaDgWyOYwCMzEZW6st6BwB9FwN.mVwGM0RfzbC', 'student', '8', '2026-05-20 23:06:32', NULL, 0, '2013-07-25', 'Male', 12, NULL, 'Filipino', 'BAPTIST', 'BRGY. PAGLAUM, BINALBAGAN, NEGROS OCCIDENTAL', '09702037483', 'EDWIN LARIDA VARGAS', 'LABORER', '', 'LOVELYN GAIL DELOESTE VARGAS', 'LOCALLY EMPLOYED', '09702037483', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(216, 'STU-2026-0181', 'SIBONGA, JODEA EVE LASTIMOSO', 'LASTIMOSO', 'jodeaevesibonga@gmail.com', '$2y$10$/jK/l6bEBht7uqRkk59WOuMHbggx/p6IhBDT9DjC3eHsUbw4HcCxy', 'student', '9', '2026-05-20 23:54:52', NULL, 0, '2012-03-23', 'Female', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09505003736', 'DYRIC ARROYO SIBONGA', '', '', 'EVALYN LASTIMOSO SIBONGA', '', '', 'VAL UMADHAY LASTIMOSO', '09505003736', 'UNCLE', 'TANZA NATIONAL TRADE SCHOOL', 'PARADAHAN I, TANZA, CAVITE', '2025-2026', '82', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(217, 'STU-2026-0182', 'FLORES, KRISTIAN JHADE GABUELO', 'GABUELO', 'kristianflores@gmail.com', '$2y$10$hCTF9rdIfxix..rNkjXiPeH6DN9JS/af.X43YwVHetmTkGZLKTFJO', 'student', '12', '2026-05-20 23:57:55', NULL, 0, '2009-06-14', 'Male', 16, 'MANILA', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09704731519', 'BENJIE PASANGHILAN FLORES', '', '', 'JEANYROSE LAYSON GABUELO', '', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(218, 'STU-2026-0183', 'Alojamiento, Ken Mark Patina', 'Patina', 'kenmarkalojamiento@gmail.com', '$2y$10$29rnfUCHswpC.A52ccFc6epCzL3CSYddTMB1csZjW3kxbH9uGom0G', 'student', '10', '2026-05-28 18:22:07', NULL, 0, '2011-01-20', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'Purok Kasag, Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09124759960', 'BRANDO SARASA ALOJAMIENTO', 'SCHOOL GUARD', '09124759960', 'REMA TADOY PATINA', 'SCHOOL UTILITY', '09124759960', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116876160013', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(219, 'STU-2026-0184', 'GATPATAN, RENIEL AMANTILLO', 'AMANTILLO', 'renielgatpatan@gmail.com', '$2y$10$bIlunRo9RWO1BwghNUEG8edGbIGePGPhm9Uv40.8ADHWCSiGO3EWK', 'student', '10', '2026-05-28 18:27:33', NULL, 0, '2011-12-03', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09812119208', 'DANIEL SARAD GATPATAN', 'FISHERMAN', '09812119808', 'ALMA MAGTOLIS AMANTILLO', 'HOUSEWIFE', '09812119808', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 1, '116868160009', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(220, 'STU-2026-0185', 'BOCA, ZENITH CLAIRE CAMION', 'CAMION', 'zenithclaireboca@gmail.com', '$2y$10$35bPRgpKemxnnhTdPU.cKuUi9j5T2I9a160D9tTdWgLgUct1/q2cC', 'student', '11', '2026-05-28 18:37:22', NULL, 0, '2010-12-02', 'Female', 15, 'BINALBAGAN', 'Filipino', '', 'PUROK VANDA, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '09098447621', 'TONY GARNICA BOCA', 'LABORER', '09098447621', 'MARICEL CAMION BOCA', 'HOUSEWIFE', '09098447621', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '116878150066', 0, 1, 0, '', 0, 1, 'ITOMAN MAGHAT-BUKIDNON', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(221, 'STU-2026-0186', 'TALLAFER, JAY AR VILLAFLOR', 'VILLAFLOR', 'jayartallafer@gmail.com', '$2y$10$JelBXH0.zmVMK.40L62XpubWjbU0Lh7G5y.WqISWX1CPNFBtws6qm', 'student', '11', '2026-05-28 18:41:16', NULL, 0, '2009-09-05', 'Male', 16, NULL, 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09631527247', 'SHERWIN SARASA TALLAFER', 'LABORER', '09631527247', 'RAQUEL ZULUETA VILLAFLOR', 'VENDOR', '09649528143', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL', 'BRGY. PAGLAUM, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(222, 'STU-2026-0187', 'MANCIBA, WENNA PATRIBO', 'PATRIBO', 'wennamanciba@gmail.com', '$2y$10$86pF3s4cOtoqJQPlxu4fNeCIo.GABPvxpt9MOIE7uSd4iW9pRMs9m', 'student', '11', '2026-05-28 18:48:06', NULL, 0, '2010-07-31', 'Female', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09657416991', 'JODY PATRIBO MANCIBA', 'LABORER', '', 'JOLIA PATRIBO MANCIBA', 'HOUSEWIFE', '', '', '', '', 'DAMPOL 1ST NATIONAL HIGH SCHOOL', 'DAMPOL, PLARIDEL, BULACAN', '2025-2026', '81', 1, '116876150009', 0, 1, 0, '', 0, 0, '', 'TAGALOG, HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(223, 'STU-2026-0188', 'BOCA, PRINCESS CAMPOS', 'CAMPOS', 'princessboca@gmail.com', '$2y$10$wIQjXmzV7PVdXA65z5GUXuuEtdjRklu.Z6Ztj97OQ/Ee64MiOv63i', 'student', '11', '2026-05-28 18:56:30', NULL, 0, '2010-01-28', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK SUNFLOWER, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '09670651449', 'SONY PECORE BOCA', 'LABORER', '', 'LEA MIE CAMPOS BOCA', 'HOUSEWIFE', '09670651449', '', '', '', 'LA CONSOLACION COLLEGE ISABELA', 'BURGOS ST., BRGY. 4, ISABELA, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116866150095', 0, 1, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(224, 'STU-2026-0189', 'SUCALDITO, DOME RICH LASPINAS', 'LASPINAS', 'domerichsucaldito@gmail.com', '$2y$10$nh7BVQ/okY4wQn8AOK6OrOnxeDMXxef2mZXHr3R1FXzaI8lsBh1DS', 'student', '11', '2026-05-28 19:02:00', NULL, 0, '2010-03-26', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09772164231', 'SIMEON NATAN SUCALDITO', '', '', 'JOYLEN SUANQUE LASPINAS', 'OFW', '', 'MELCY LASPINAS MAULOD', '09772164231', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116868150010', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(225, 'STU-2026-0190', 'DABLIO, LYN VILLADAR', 'VILLADAR', 'lyndablio@gmail.com', '$2y$10$39/m1g9bhwO43duUWPwf3.KEsqZxNIYdBfKmoOxfeMUQyq/ZgibZ6', 'student', '8', '2026-05-28 19:37:50', NULL, 0, '2012-05-29', 'Female', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'CAINGIN, DANCALAN, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09167686187', 'NILO ENCARNACION DABLIO', 'Laborer', '09167686187', 'ANNALIZA RACA VILLADAR', 'Housewife', '', 'LINIL VILLADAR DABLIO', '09563553875', 'UNCLE', 'TLCA - Bin', 'PRK. AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '116870170023', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(226, 'STU-2026-0191', 'BAYLON, BENZ BUHAT', 'BUHAT', 'benzbaylon@gmail.com', '$2y$10$A8b4NktJZqJJz4.0ZQjzWumwW/S5tVXZo3jW21jS9srBgDIWM.p/e', 'student', '8', '2026-05-28 20:41:59', NULL, 0, '2013-04-30', 'Male', 13, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Biunalbagan, Negros Occidental', '', 'Ben Baylon', 'Fisherman', '', 'Wilme Buhat Baylon', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '91', 1, '116868180001', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(227, 'STU-2026-0192', 'MANGANA, MARK JOHN MONCADA', 'MONCADA', 'markjohnmangana@gmail.com', '$2y$10$4X2NHp9xEEfoEKsobyia8upzAitZmK8L277zUZZmLvQ6pG1SKf1uC', 'student', '10', '2026-05-28 23:18:20', NULL, 0, '2010-09-26', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'MARVIN SARAD MANGANA', 'FISHERMAN', '', 'JIGI FERIA MONCADA', 'VENDOR', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '116868150054', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(228, 'STU-2026-0193', 'POSEO, RAINIER GAMPOSILAO', 'GAMPOSILAO', 'rainierposeo@gmail.com', '$2y$10$cXnHb2AVKoDqcNaAcrEYyOj63UecNgtZx88XiEUpd/SmTpOqpelpS', 'student', '10', '2026-05-28 23:23:48', NULL, 0, '2009-11-29', 'Male', 16, 'HIMAMAYLAN CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09107811157', 'REY MOCERO POSEO', '', '', 'NALIE CALLAO GAMPOSILAO', '', '', 'PEARL JOY GAMPOSILAO POSEO', '09107811157', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 1, '136882140123', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(229, 'STU-2026-0194', 'SARAD, SHEILA MAE VILLAHERMOSA', 'VILLAHERMOSA', 'sheilamaesarad@gmail.com', '$2y$10$xqXL.sue9qLd7x9Lj0Qw5uuwOxcf/XwY9i5/huGnwe5p.gYPM5FA.', 'student', '9', '2026-05-28 23:28:58', NULL, 0, '2012-08-14', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09850852028', 'ARMANDO POLIDO SARAD', 'FISHERMAN', '', 'MARIA VILLAHERMOSA CARCIDO', 'VENDOR', '09850852028', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(230, 'STU-2026-0195', 'SARAD, MA. TERESA AMANTILLO', 'AMANTILLO', 'materesasarad@gmail.com', '$2y$10$semn1h9HGcjKBJ0ksRwDX.mhtnYb/FpjrAbkodBPNEmmGPyoW5TSC', 'student', '8', '2026-05-28 23:34:03', NULL, 0, '2013-01-01', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'REYMUNDO VILLARMA SARAD', 'FISHERMAN', '', 'MARIA AMANTILLO SARAD', 'VENDOR', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(231, 'STU-2026-0196', 'CEBALLOS, KARL ANTON DURON', 'DURON', 'karlantonceballos@gmail.com', '$2y$10$cmUkPt3bfQdoLxlqQmzm7u1Vi8Ky.XhHiDyBp136xkbr0zbtAp3k.', 'student', '11', '2026-05-28 23:40:13', NULL, 0, '2010-01-22', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'MIKEL ARTAJO CEBALLOS', 'NONE', '', 'ZAINA NIEVES CEBALLOS', 'OFW', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 1, '116868150001', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(232, 'STU-2026-0197', 'GARCIA, JOHN MATHEW COSTALES', 'COSTALES', 'johnmathewgarcia@gmail.com', '$2y$10$NTkZJ9NJcFfpgj5.lENcMe5TYEdY2UHM2XBJwemCZrygu1CxwqZ3O', 'student', '11', '2026-05-28 23:46:04', NULL, 0, '2009-11-01', 'Male', 16, 'TAGUIG CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'CESAR GIDUCOS GARCIA', '', '', 'ROSELYN ESCANILLA COSTALES', 'DRESSMAKER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '117524150074', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(233, 'STU-2026-0198', 'PADREQUIL, ROMEO JR. CABRERA', 'CABRERA', 'romeopadrequil@gmail.com', '$2y$10$sH0xKM/J58y0jqJuRy5MAuQ/GV9viFDLugfw6hRaqU9PNmgJFAAaO', 'student', '11', '2026-05-28 23:51:21', NULL, 0, '2009-06-03', 'Male', 16, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09512578328', 'ROMEO ARSAGA PADREQUIL SR.', 'LOCALLY EMPLOYED', '', 'LEA BAUTISTA CABRERA', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(234, 'STU-2026-0199', 'ALOJAMENTO, MARJIE ALCABO', 'ALCABO', 'marjiealojamento@gmail.com', '$2y$10$GVAp7Pk5o2j3GHceHs2pdulCHDOMw6GcTfdOFOaVXFirqFqrZSjUe', 'student', '12', '2026-05-28 23:57:52', NULL, 0, '2008-12-09', 'Female', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITION NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09544973133', 'MARVIN SARASA ALOJAMENTO', 'LABORER', '', 'JINGJING ESTORCO ALCABO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '116876140020', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(235, 'STU-2026-0200', 'CAÑON, EUNICE SERUELO', 'SERUELO', 'eunicecanon@gmail.com', '$2y$10$Wzu1o/8HsEyM7k1UDukX1OQ5YJplljUCF9tXXsT2jo9uh2FgJvJUi', 'student', '8', '2026-05-29 00:03:16', NULL, 0, '2012-10-29', 'Female', 13, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nawasa, Brgy. San Jose, Binalbagan, Negros Occidental', '', 'Val Sartorio Cañon', 'Laborer', '', 'Trizzia Grace Padios Seruelo', 'Housewife', '', 'Jezyl Raya Seruelo', '', 'Aunt', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '93', 1, '116866190104', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(236, 'STU-2026-0201', 'MISSION, MARJORIE FIRMEZA', 'FIRMEZA', 'marjoriemission@gmail.com', '$2y$10$0IdnMGvkwPIx3OlDlM0M7ePESiF4z0MOSputKSym78raHTsHprkpq', 'student', '12', '2026-05-29 00:03:35', NULL, 0, '2009-06-15', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09513151427', 'MARJOHN GATPATAN MISSION', 'FISHERMAN', '', 'MODECAR MAGBANUA FIRMEZA', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116868140025', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(237, 'STU-2026-0202', 'ALOJAMIENTO, VINCE Patina', 'Patina', 'vincealojamiento@gmail.com', '$2y$10$rS.gMVLbXz5YywWHJ0JJY.ggriHmsL4wwzn4GEt8G/jkcrDytZLmC', 'student', '12', '2026-05-29 00:07:42', NULL, 0, '2009-01-21', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09124759960', 'BRANDO SARASA ALOJAMIENTO', 'SCHOOL GUARD', '', 'REMA TADOY PATINA', 'SCHOOL UTILITY', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116876140003', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(238, 'STU-2026-0203', 'CASTILLO, JOHN ANDREI JOCSON', 'JOCSON', 'johnandreicastillo@gmail.com', '$2y$10$/7CB6hAOV67Ifd69XNaS.uAoiPYJH43ZqCWlQ7TgdJTlbEAapNrL2', 'student', '12', '2026-05-29 00:12:58', NULL, 0, '2009-05-29', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09948899197', 'JENNY ALBERCA CASTILLO', 'FISHERMAN', '', 'JENNETTE SARMIENTO JOCSON', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 1, '116868140013', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(239, 'STU-2026-0204', 'INSIGNE, JULIE ANNE BERANIO', 'BERANIO', 'julieanneinsigne@gmail.com', '$2y$10$b1fyhx8kV/.bSXQrCuhedeEg7FlDsgzK3qd1u6B4Zhef0faLO/S9.', 'student', '7', '2026-05-29 00:20:26', NULL, 0, '2012-12-03', 'Female', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'NOEL PADRONIA INSIGNE', 'LABORER', '', 'ROMELYN ANTONIO BERANIO', 'Housewife', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868180026', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(240, 'STU-2026-0205', 'ALOJAMEINTO, MARJORIE ALCABO', 'ALCABO', 'marjoriealojameinto@gmail.com', '$2y$10$FVfjGLZcmqUfVIJP9p1pDeMqxyDxvWv2fkyTKRAF45.3UM2bqYRWK', 'student', '7', '2026-05-29 00:24:01', NULL, 0, '2014-04-08', 'Female', 12, 'BINALBAGAN', 'Filipino', 'TCM', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09544973133', 'MARVIN SARASA ALOJAMENTO', 'LABORER', '', 'JINGJING ESTORCO ALCABO', 'LABORER', '', '', '', '', 'NABUSWANG ELEMENTARY SCHOOL', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116876190001', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(241, 'STU-2026-0206', 'SERANTES, ALTHEA NICOLE MONCADA', 'MONCADA', 'altheanicoleserantes@gmail.com', '$2y$10$VkGXEr42n6iRYpWWqZpMBeDMgSsH.0e99w6LQ/9LSRNwRFPF50bgu', 'student', '8', '2026-05-29 00:25:46', NULL, 0, '2012-11-06', 'Female', 13, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Greenshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'John Sarad Serantes', 'Fisherman', '', 'Zion Joy Gamposilao Moncada', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '87', 1, '116868180069', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(242, 'STU-2026-0207', 'GENE, JASHLY NICOLE OLIVA', 'OLIVA', 'jashlynicolegene@gmail.com', '$2y$10$OLLX2Qo.MSWagwxXpMynDuD5.U7z0fDL6HaaRKRCDmF2UeWoD.X5W', 'student', '7', '2026-05-29 00:27:42', NULL, 0, '2012-02-08', 'Female', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09464337353', 'JETHER BADONG GENE', 'LABORER', '', 'RENILDA OLIVA GENE', 'HOUSEWIFE', '09464337353', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '117094180077', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(243, 'STU-2026-0208', 'ALOJAMENTO, JAMEL ALCABO', 'ALCABO', 'jamelalojamento@gmail.com', '$2y$10$Y62LYSYJHI..0z/4gDf6juVk.GQUt2rZko0uxmqcOxpPV7fy/N9aS', 'student', '9', '2026-05-29 00:35:59', NULL, 0, '2010-11-01', 'Male', 15, 'BINALBAGAN', 'Filipino', 'TCM', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09544973133', 'MARVIN SARASA ALOJAMENTO', 'LABORER', '', 'JINGJING ESTORCO ALCABO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '77', 1, '116876160009', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(244, 'STU-2026-0209', 'ENEGRIO, JANINE MARY SARMIENTO', 'SARMIENTO', 'janinemaryenegrio@gmail.com', '$2y$10$gbgu3QduJsNCOrlYvVAcqeuTTCVKAaAGfT5ykVN/0ITDedecqX7Ou', 'student', '8', '2026-05-29 00:42:58', NULL, 0, '2013-01-04', 'Female', 13, 'Himamaylan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Tuway< brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Edgar Duron Enegrio', 'Fisherman', '', 'Johanna Gentile Sarmiento', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '91', 1, '116868180020', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(245, 'STU-2026-0210', 'INSIGNE, MERICK JAMES BERANIO', 'BERANIO', 'merickjameinsigne@gmail.com', '$2y$10$kP0HXfpi/469mOaUWQOrCO.NVplordLWkD2XauOupITg/yH5cL4F2', 'student', '9', '2026-05-29 00:46:04', NULL, 0, '2010-03-03', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW , BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'NOEL PADRONIA INSIGNE', '', '', 'ROMELYN ANTONIO BERANIO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', '70', 1, '116868150023', 1, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(246, 'STU-2026-0211', 'CAÑON, SHAN ETHAN SERUELO', 'SERUELO', 'shanethancanon@gmail.com', '$2y$10$2sQ2x6JE3z9o9voc/1gsZ.E7rkkrKzdh2nRwE6irk1tp2JYJJT6ze', 'student', '9', '2026-05-29 00:48:59', NULL, 0, '2012-10-14', 'Male', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NAWASA, BRGY. SAN JOSE, BINALBAGAN, NEGROS OCCIDENTAL', '', '', '', '', '', '', '', 'JEZYL SERUELO', '', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '116866180105', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(247, 'STU-2026-0212', 'SARAD, ANDREA MAE VILLALUZ', 'VILLALUZ', 'andreamaesarad@gmail.com', '$2y$10$gnc0w0DAxFPu61MHU68a4.LAq7SuEMAh8X1AnumAUgT/s4Lyp9UeO', 'student', '9', '2026-05-29 00:51:40', NULL, 0, '2011-10-12', 'Female', 14, 'HIMAMAYLAN CITY', 'Filipino', 'BAPTIST', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09506139221', 'RODICOR GANGIS SARAD', 'LOCALLY EMPLOYED', '', 'LORENA VILLALUZ SARAD', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116868120048', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(248, 'STU-2026-0213', 'INSIGNE, ROMEO BERANIO', 'BERANIO', 'romeoinsigne@gmail.com', '$2y$10$AvdIJOXTIX.nZbHJP8RLiOjeXJ8NKOMT2eTMx.s9dZo37hlDiplSe', 'student', '9', '2026-05-29 00:53:42', NULL, 0, '2011-04-01', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'NOEL PADRONIA INSIGNE', 'NONE', '', 'ROMELYN ANTONIO BERANIO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '75', 1, '116868160010', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(249, 'STU-2026-0214', 'SARAD, KEA MAE MONGCAL', 'MONGCAL', 'keamaesarad@gmail.com', '$2y$10$fLEP66FMtqRHQQ6TRVcWbOltmotB5rexYXfNl3ve/061qNbX2Jfcq', 'student', '9', '2026-05-29 00:55:58', NULL, 0, '2011-09-08', 'Female', 14, 'BINALBAGAN', 'Filipino', 'BAPTIST', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RODRIGO GANGIS SARAD', 'LABORER', '', 'ANALEAH MONGCAL SARAD', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '116868170049', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `empidno`, `name`, `middle_name`, `email`, `password_hash`, `role`, `grade_level`, `created_at`, `image`, `archived`, `date_of_birth`, `gender`, `age`, `place_of_birth`, `nationality`, `religion`, `home_address`, `contact_number`, `father_name`, `father_occupation`, `father_contact`, `mother_name`, `mother_occupation`, `mother_contact`, `guardian_name`, `guardian_contact`, `guardian_relationship`, `last_school_attended`, `last_school_address`, `school_year_completed`, `general_average`, `has_lrn`, `lrn_number`, `is_returnee`, `is_transfer_in`, `has_special_needs`, `special_needs_type`, `is_4ps_beneficiary`, `is_indigenous`, `indigenous_group`, `mother_tongue`, `retention_status`, `retention_reason`, `retention_school_year`, `retention_updated_at`, `retention_updated_by`) VALUES
(250, 'STU-2026-0215', 'GARCIA, JOHN CYZAR COSTALES', 'COSTALES', 'johncyzargarcia@gmail.com', '$2y$10$bJlkBm.dT030M2hdxnWHAub.rFKO3E8jT9lZ42xnleaE7fHNBpyG2', 'student', '9', '2026-05-29 00:58:33', NULL, 0, '2011-12-01', 'Male', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'CESAR GIDUCOS GARCIA', 'LABORER', '', 'ROSELYN ESCANILLA COSTALES', 'DRESSMAKER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '80', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(251, 'STU-2026-0216', 'BANEZ, CRISTOPHER GEPA', 'GEPA', 'cristopherbanez@gmail.com', '$2y$10$PqpBms9v28HXSWQ9ZAFMKeinx4bWEp1yQQUIVqL1eXePpKKW3/Eam', 'student', '9', '2026-05-29 01:01:16', NULL, 0, '2012-01-05', 'Male', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'CRISPIN BANEZ', '', '', 'ERENE MOLANAN GEPA', '', '', 'LUMINADA MOLANAN GEPA', '', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(252, 'STU-2026-0217', 'EVANGELIO, JOECEL PIDO', 'PIDO', 'joecelevangelio@gmail.com', '$2y$10$cC4lcAAov2GB5.MpAOjxBO/Tot7NDAd4FZiufrnkstqB1HxQCmzn6', 'student', '9', '2026-05-29 01:10:22', NULL, 0, '2012-02-24', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', '', '095012508829', '', '', '', 'RICHEL PONTARON PIDO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 1, '116868170031', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(253, 'STU-2026-0218', 'CARI-AN, ZYREXIS DELA CRUZ', 'DELA CRUZ', 'zyrexiscarian@gmail.com', '$2y$10$RsVijYBi7cGYtK0PDOrd.uAp91nVeRksbuw2DiILzIdWpuzSOvtG6', 'student', '9', '2026-05-29 01:13:28', NULL, 0, '2012-07-21', 'Male', 13, 'BINALBAGAN', 'Filipino', 'BAPTIST', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09075842667', 'ZERXIS SIAMEN CARI-AN', 'LOCALLY EMPLOYED', '', 'MICHELLE DESALES DELA CRUZ', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 1, '116889170003', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(254, 'STU-2026-0219', 'SERUELO, CHAD LOIS', '', 'chadloisseruelo@gmail.com', '$2y$10$bMx851S/RLpeaFc5ZuLWsuzjSa2yQnMcPsY9EF05iBw13nTCfS2dm', 'student', '9', '2026-05-29 01:16:09', NULL, 0, '2012-01-26', 'Male', 14, 'CANDONI', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NAWASA, BRGY. SAN JOSE, BINALBAGAN, NEGROS OCCIDENTAL', '', '', '', '', '', '', '', 'JEZYL SERUELO', '', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '116892170043', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(255, 'STU-2026-0220', 'TALLAFER, JOSEPH PATRIBO', 'PATRIBO', 'josephtallafer@gmail.com', '$2y$10$V50OEcXpkMJvwXwznIFky.kzy2VgVXf/nGPEf8yd3A.2mnEBD6NAW', 'student', '9', '2026-05-29 01:19:14', NULL, 0, '2010-09-17', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'GILBERT SARASA TALLAFER', 'FISHERMAN', '', 'PATRIA LUCENIO PATRIBO', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '74', 1, '116876150020', 1, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(256, 'STU-2026-0221', 'EVANGELIO, MARK ZIAN BONTOGON', 'BONTOGON', 'markzianevangelio@gmail.com', '$2y$10$95Qe.wgiRoY2.kIPKjoiNOrlpkOceiH7zefsmwjbhj0gwrloCQLsi', 'student', '7', '2026-05-29 01:21:54', NULL, 0, '2013-11-01', 'Male', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09386128893', 'JOERIE DUMIP-IG EVANGELIO', 'LABORER', '', 'JERAMAE CACHOCO BONTOGON', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '116868190027', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(257, 'STU-2026-0222', 'SANDIG, JAMES MISSION', 'MISSION', 'jamessandig@gmail.com', '$2y$10$GAcgEcCwxzycW/hA42iaP.8TnlJCxI0iZcZvVtt4lMhoyS6.o//Hm', 'student', '9', '2026-05-31 18:24:11', NULL, 0, '2012-02-22', 'Male', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09467638596', 'JOEMARIE FERNANDEZ SANDIG', 'LABORER', '09323952175', 'NORMELITA FRANCISCO MISSION', 'HOUSEWIFE', '09467638596', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '96', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(258, 'STU-2026-0223', 'ATIENZA, SHANE GELLE', 'GELLE', 'shaneatienza@gmail.com', '$2y$10$ZWXLjflE2ywVwigNZ.ctvenP.QjG2QqF./VltuhT1F/VXEzLXXayO', 'student', '7', '2026-05-31 18:27:09', NULL, 0, '2014-04-15', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09942851924', 'ROYROY SALVACION ATIENZA', 'LABORER', '', 'SUZIE GELLE ATIENZA', 'HOUSEWIFE', '09942851924', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(259, 'STU-2026-0224', 'DELEJERO, BRYAN NATORIO', 'NATORIO', 'bryandelejero@gmail.com', '$2y$10$FRUZYwBPG6/58sJ3f0UEIOx73BMdfbNBDE5GhMJgYFlaJ8FVykzH.', 'student', '11', '2026-05-31 18:58:51', NULL, 0, '2010-06-21', 'Male', 15, 'HIMAMAYLAN CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09917145406', 'BERNANDINO GABASAN DELEJERO', 'DRIVER', '09917145406', 'JULIE ANN DALUMPINES NATORIO', 'HOUSEWIFE', '09917145406', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 1, '116868150015', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(260, 'STU-2026-0225', 'ANIAN, MIKE GIMADO', 'GIMADO', 'mikeanian@gmail.com', '$2y$10$NNBtkYVI.voLM2iIj0oRfu6LWJKafCQBaxQlzXiJW3rX9xYq8JIKa', 'student', '12', '2026-05-31 19:01:39', NULL, 0, '2007-03-10', 'Male', 19, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO PAG-ASA, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '', 'MARK BALTAZAR ANIAN', 'LABORER', '', 'ROSALIE ACADEMIA GIMADO', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2024-2025', '78', 1, '116870120046', 1, 0, 1, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(261, 'STU-2026-0226', 'BURGOS, PRINCESS KAZUMI GENADA', 'GENADA', 'princesskazumi@gmail.com', '$2y$10$BNzdOMJKW9ZcJGLXH3f6Mu2K68u0jny5iCvhB59XPlVDGRl9LAV8G', 'student', '8', '2026-05-31 19:08:39', NULL, 0, '2012-10-04', 'Male', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09569480350', 'ROGER GIRAY BURGOS', 'LABORER', '09569480350', '', '', '', 'ROGER GIRAY BURGOS', '09569480350', 'FATHER', 'FELLOWSHIP BAPTIST SCHOOL', 'BRGY. SAN TEODORO, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 1, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(262, 'STU-2026-0227', 'GEROLA, FRENZ ZHIEJAN BOB PAURILLO', 'PAURILLO', 'frenzzhiejanbob@gmail.com', '$2y$10$uDgOE9Zx0xwroL6LzeMtlu7y3fGNkaRKNJJ00DwBPQp.DhRNfjDxi', 'student', '8', '2026-05-31 20:01:58', NULL, 0, '2013-01-28', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'OLD MOHON, BRGY. CABANBANAN, HIMAMAYLAN CITY, NEGROS OCCIDENTAL', '09946679022', 'ZYRUS BAN GEROLA', 'LABORER', '09383973678', 'JENEVIE PAURILLO GEROLA', 'VENDOR', '09946679022', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(263, 'STU-2026-0228', 'ESTORCO, GREYCEL CUANHIN', 'CUANHIN', 'greycelestorco@gmail.com', '$2y$10$ATIaq7VCXWfiyzKtbbs9mOe2zDzk5ogD.fiNzfSvpniAGBzj3sAfO', 'student', '9', '2026-05-31 20:48:38', NULL, 0, '2012-07-02', 'Female', 13, NULL, 'Filipino', 'ROMAN CATHOLIC', 'SITIO CAINGIN, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09648541617', 'GERRY ZERNA ESTORCO', 'DRIVER', '', 'ROCEL NASARENO CUANHIN', 'HOUSEWIFE', '09517679083', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(264, 'STU-2026-0229', 'CASTILLO, JOHANN JOCSON', 'JOCSON', 'johanncastillo@gmail.com', '$2y$10$OUbHVyaUWbvuj5mjGthBx.6CmAgfMyLe3DzGV.xuZbPXwdn4h6nSq', 'student', '9', '2026-05-31 20:52:34', NULL, 0, NULL, 'Male', NULL, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09948899197', 'JENNY ALBERCA CASTILLO', 'FISHERMAN', '', 'JENNETTE SARMIENTO JOCSON', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(265, 'STU-2026-0230', 'DABLIO, MARCH VILLADAR', 'VILLADAR', 'marchdablio@gmail.com', '$2y$10$xYN5FUykr9.9cPGoN7P8Rey9RZX4qHEUDYhvGagBPkApMpQaaKDMK', 'student', '11', '2026-05-31 20:55:35', NULL, 0, '2009-03-10', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', '213, SITIO CAINGIN, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09563553875', 'NILO ENCARNACION DABLIO', 'LOCALLY EMPLOYED', '09167686187', 'ANNA LIZA RACA VILLADAR', 'LABORER', '', 'NIELNIEL VILLADAR DABLIO', '09563553875', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '116870140006', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(266, 'STU-2026-0231', 'RUBIO, CHASSIL MAY DISOY', 'DISOY', 'chassilmayrubio@gmail.com', '$2y$10$kj4amKFHM3rXzBKgDEK8XO.pk.8C8ZbOfhGMBLDLMXr2apLOHS5P2', 'student', '12', '2026-05-31 20:58:57', NULL, 0, '2009-05-26', 'Female', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK SANTAN, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '09318775069', 'SILVESTRE CANOY RUBIO', 'LABORER', '09318775069', 'CHARIE DISOY RUBIO', 'OFW', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '116876140031', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(267, 'STU-2026-0232', 'PANCHO, BENJAMIN III SALON', 'SALON', 'benjaminpancho@gmail.com', '$2y$10$YXcswMZQ2Bcd2nR0bLLCBuGib9PGFdLtl4cxepHuvCqPp9HI3Z.ze', 'student', '7', '2026-05-31 21:02:30', NULL, 0, '2012-09-04', 'Male', 13, 'NORTHERN SAMAR', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09922310602', 'BENJAMIN GITO PANCHO', 'LABORER', '', 'GERALYN MAYUGA SALON', 'HOUSEWIFE', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '122814190044', 0, 0, 0, '', 0, 0, '', 'TAGALOG, HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(268, 'STU-2026-0233', 'ABAWAG, RUNA MAE CANETE', 'CANETE', 'runamaeabawag@gmail.com', '$2y$10$cLusO8KfL.MlL0biqEGoUu.68M0MnQiN/U4VdbSiT7.yb/MDU6r7a', 'student', '9', '2026-06-01 00:26:37', NULL, 0, '2012-04-27', 'Female', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'CARMEN STREET, BRGY. SAN PEDRO, BINALBAGAN, NEGROS OCCIDENTAL', '09484979099', '', '', '', 'JANNA SERVAN CANETE', 'HOUSEKEEPER', '09484979099', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(269, 'STU-2026-0234', 'ABAWAG, JANUEL CANETE', 'CANETE', 'januelabawag@gmail.com', '$2y$10$0rWjF0IqtdomDRTLRSxVw.UuFM31HZCa75Ge9k5fGG0exZWMDnHyG', 'student', '8', '2026-06-01 00:28:52', NULL, 0, '2013-08-01', 'Male', 12, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', 'CARMEN STREET, BRGY. SAN PEDRO, BINALBAGAN, NEGROS OCCIDENTAL', '09484979099', '', '', '', 'JANNA SERVAN CANETE', 'HOUSEKEEPER', '09484979099', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 1, '117478170042', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(270, 'STU-2026-0235', 'SARAD, RYKA MEA TESORO', 'TESORO', 'rykameasarad@gmail.com', '$2y$10$uO1z2GlzH4LkBjc.9XhZCeocI74n1YR5OGgc9KyCU2IlknSQS0opu', 'student', '8', '2026-06-01 00:33:31', NULL, 0, '2013-07-29', 'Female', 12, 'BINALBAGAN', 'Filipino', 'BAPTIST', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09850876258', 'RONIE POLIDO SARAD', 'FISHERMAN', '', 'FANNY UNTAL TESORO', 'HOUSEWIFE', '09850876258', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '89', 1, '116868180068', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(271, 'STU-2026-0236', 'GARGANIAN, REIGNECEL ALCAZAR', 'ALCAZAR', 'reignecelgarganian@gmail.com', '$2y$10$eRpmeu5dcyuuqFPV6Dvxf.Tj.Xr/K8lSegFIqmHBdVr7Y9wSBDw1y', 'student', '8', '2026-06-01 00:37:43', NULL, 0, '2012-12-04', 'Female', 13, 'HIMAMAYLAN CITY', 'Filipino', 'BAPTIST', 'PUROK 12, ST. PETER VILLAGE, AGUISAN, HIMAMAYLAN CITY, NEGROS OCCIDENTAL', '09483520202', 'RENE VILLADICENCIO GARGANIAN', 'LOCALLY EMPLOYED', '09850525757', 'LAVELLA TEOLOGO ALCAZAR', 'BH WORKER', '09483520202', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '116868180039', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(272, 'STU-2026-0237', 'JUNSAY, KISHA ANN BUARON', 'BUARON', 'kishaannjunsay@gmail.com', '$2y$10$6X0zP7jkJLf86gJcVSuRBuq4m0uzK2AkzqhRxr/RVlES2TXdmjEbm', 'student', '12', '2026-06-01 18:47:14', NULL, 0, '2009-06-24', 'Female', 16, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'RONALD TREBENIA JUNSAY', 'LABORER', '', 'RUFFA BUARON', '', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(273, 'STU-2026-0238', 'BEKER, NOEMIE BACLAYO', 'BACLAYO', 'noemiebeker@gmail.com', '$2y$10$8UY3fpXcC98StQptZpRteOH0D1Im3Y.IOje27CgyQjT7uGT9sHace', 'student', '12', '2026-06-01 19:15:18', NULL, 0, '2009-01-09', 'Female', 17, 'MANILA', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'LODUVICO GENGOS BEKER', 'LABORER', '', 'MYRA VARELA BACLAYO', 'HOUSEWIFE', '', 'GINA SARAD BEKER', '', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 1, '116868150075', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(274, 'STU-2026-0239', 'BUHAT, CATHLYN NIEVES', 'NIEVES', 'cathlynbuhat@gmail.com', '$2y$10$dhyDJW3UiOpvDRozuCpAae9GnQmt2B7gLAjnbskXecRuthfSg9VBC', 'student', '12', '2026-06-01 19:18:44', NULL, 0, '2009-08-25', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09277993100', 'NESTOR SARAD BUHAT', 'LABORER', '', 'LORY PERI-IRA NIEVES', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '116868140036', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(275, 'STU-2026-0240', 'TILA, DORENNA PASALGON', 'PASALGON', 'dorennatila@gmail.com', '$2y$10$HYhY5OeG2OA/Ncuh.rTdXuu3ywk4qDSd5U2aNo7M.Zd1M.YacWA5a', 'student', '12', '2026-06-01 19:22:04', NULL, 0, '2009-10-18', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09852190665', 'JOEREN BERNESTO TILA', 'FISHERMAN', '', 'VENUS FLORES PASALGON', 'VENDOR', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 1, '116868140017', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(276, 'STU-2026-0241', 'BERDEN, JOHN ALLEN SOTEO', 'SOTEO', 'johnallenberden@gmail.com', '$2y$10$Pa/ZntQiSgqLFEwPwA1jqO4JpcGhHE0aXMKqMEWTakmQdB6mUKPDO', 'student', '11', '2026-06-01 19:40:38', NULL, 0, '2007-06-06', 'Male', 18, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', '6TH STREET, BRGY. PROGRESO, BINALBAGAN, NEGROS OCCIDENTAL', '09275179159', 'ALLAN VILLARITO BERDERN', '', '', 'ANNABELA NIFRAS SOTEO', 'LOCALLY EMPLOYED', '00', '', '', '', '', '', '', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(277, 'STU-2026-0242', 'ARNAEZ, JOHN LUIS ANACAN', 'ANACAN', 'johnluisarnaez@gmail.com', '$2y$10$iDRVBPu5z8NWtpqHzC/YNeLlpd8iBz5swIuEqIN5GI1wWNb//DREq', 'student', '9', '2026-06-01 19:49:12', NULL, 0, NULL, 'Male', 15, NULL, 'Filipino', 'ROMAN CATHOLIC', 'HDA. FIDEL, BRGY. TORTOSA, BINALBAGAN, NEGROS OCCIDENTAL', '09317160744', 'RENE FELIZARDO ARNAEZ', 'DRIVER', '', 'LUCELLE JALANDONI ANACAN', 'HOUSEWIFE', '', 'GRAZEL ANACAN ARNAEZ', '09317160744', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(278, 'STU-2026-0243', 'COLETA, NICOLE ESLLAMADO', 'ESLLAMADO', 'nicolecoleta@gmail.com', '$2y$10$BcmL056XW.ZW89ApuG4VOepwvTcvq54jmHKUvTH9y8ybnhaUyEUwm', 'student', '12', '2026-06-01 19:52:47', NULL, 0, '2009-09-29', 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09306427685', 'RICKY BARCELONA COLETA', '', '', 'MARY LENY POLIDO ESLLAMADO', 'LABORER', '09306427685', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '116868140028', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(279, 'STU-2026-0244', 'COLETA, NICKEA FAITH ESLLAMADO', 'ESLLAMADO', 'nickeafaithcoleta@gmail.com', '$2y$10$HDKE.mN.dPBP6wSwH3srJu16f1WcJ/v5Lm2LQ4IxeElcTF4WH1qMC', 'student', '8', '2026-06-01 19:56:04', NULL, 0, '2013-12-07', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09306427685', 'RICKY BARCELONA COLETA', '', '', 'MARY LENY POLIDO ESLLAMADO', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '91', 1, '116868180057', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(280, 'STU-2026-0245', 'ESGRINA, JAMES BRYAN TANALGO', 'TANALGO', 'jamesbryanesgrina@gmail.com', '$2y$10$c2KvSZ8Y9Td6iaNqaXydwOXS.atO8oRuNfzAn0gsnfbo4nDlI3L0K', 'student', '10', '2026-06-01 19:57:39', NULL, 0, '2008-10-07', 'Male', 17, NULL, 'Filipino', 'ROMAN CATHOLIC', 'PUROK IPIL-IPIL, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ROGELIO MENDIOLA ESGRINA', 'LABORER', '', 'HERMINIA TANALGO ESGRINA', 'VENDOR', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '65', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(281, 'STU-2026-0246', 'SARASA, KATE CABASAN', 'CABASAN', 'katesarasa@gmail.com', '$2y$10$Ed4xCSTyP2U0mIY2TGFFZOOBdUUzngYtcdqx81PEcJPJARLz1O4Y6', 'student', '9', '2026-06-01 22:36:53', NULL, 0, '2011-10-24', 'Female', 14, NULL, 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'KELVIN JOHN VILLAFLOR SARASA', 'FISHERMAN', '', '', '', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(282, 'STU-2026-0247', 'ALANES, DANIEL NIEVES', 'NIEVES', 'danielalanes@gmail.com', '$2y$10$jCP.ITWJ7hFkRhUklUkHBuz2BRUJqjo14d5FWbc3nNL4QKDjaLfey', 'student', '7', '2026-06-01 23:17:45', NULL, 0, '2014-03-07', 'Male', 12, 'SILAY CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK PUNAW, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09858636069', 'ARNIL POBLADOR ALANES', 'LABORER', '', 'EMELY CELES NIEVES', 'LABORER', '09858636069', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '445006190012', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(283, 'STU-2026-0248', 'GATPATAN, JANWIN PARRENO', 'PARRENO', 'janwingatpatan@gmail.com', '$2y$10$dEFAkstcLWZLBnf4lAI/f.nrT7yCrBDaL.e8g2EjHYHPbKv/fuPqC', 'student', '8', '2026-06-02 19:38:52', NULL, 0, '2012-08-08', 'Male', 13, 'MUNTINLUPA CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09932724876', 'DARWIN FLORETE', 'LABORER', '', 'JANICE LENOS PARRENO', 'HOUSEWIFE', '', '', '', '', 'SAN ROQUE CATHOLIC SCHOOL, INC.', 'MENDIOLA ST,. ALABANG, MUNTINLUPA CITY', '2025-2026', '86', 1, '407320180087', 0, 0, 0, '', 0, 0, '', 'TAGALOG, HILIGAYNON', 'promoted', '', '', NULL, NULL),
(284, 'STU-2026-0249', 'PABORITO, JEAN JOCSON', 'JOCSON', 'jeanpaborito@gmail.com', '$2y$10$TeqWh5kL5j0k9cjfmWnTt.3UlG15mOGDQY7pcsFzH8nOO3irnX1jm', 'student', '12', '2026-06-03 00:36:59', NULL, 0, '2008-04-08', 'Female', 18, 'MANAPLA', 'Filipino', 'ROMAN CATHOLIC', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09514981020', 'NOEL SEBALLOS PABORITO', 'LABORER', '', 'JULIET FLORES JOCSON', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 1, '116868130028', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(285, 'STU-2026-0250', 'BAYLON, AMETHYS BUHAT', 'BUHAT', 'amethysbaylon@gmail.com', '$2y$10$pgtlqP1IxwFwuaWnZ5gKH.fxAYgXORW/ne2Y5BYYic1az0YYhE3U.', 'student', '9', '2026-06-03 00:40:30', NULL, 0, '2011-09-21', 'Female', 14, 'BINALBAGAN', 'Filipino', 'BAPTIST', 'PUROK NYLONSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09123516893', 'BEN GERONAGA BAYLON', 'LABORER', '', 'WILME BUHAT BAYLON', 'HOUSEWIFE', '', 'AMELITA MONCADA BUHAT', '09462362144', 'AUNT', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '440568150006', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(286, 'STU-2026-0251', 'ENEGRIO, SHIAZIE LIANE GILTENDEZ', 'GILTENDEZ', 'shiazielianeenegrio@gmail.com', '$2y$10$85T.Q.aa1LEOQv0l19.pR.xDS8Hd6aIQn8nogCy.59WXHeaG.jgWu', 'student', '8', '2026-06-03 00:47:04', NULL, 0, '2013-05-11', 'Female', 13, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09120792147', 'JIGER DELA CRUZ ENEGRIO', 'LABORER', '', 'VERCEL CHONG GILTENDEZ', 'OFW', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '117483180049', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(287, 'STU-2026-0252', 'PELARIN, KIM ARTAJO', 'ARTAJO', 'kimpelarin@gmail.com', '$2y$10$Eav.6kSUZx/T76RR.KEGQuAS9sUK9c0NI0bNIr1g1ZsHuorPSPdje', 'student', '11', '2026-06-03 00:51:45', NULL, 0, NULL, 'Female', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09096349776', 'JOHN REY PASU-IT PELARIN', 'LABORER', '', 'MARICAR ARTAJO PELARIN', 'HOUSEKEEPER', '', 'JENELYN PELARIN', '09096349776', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116868150033', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(288, 'STU-2026-0253', 'LEBRILLA, JUSTIN AGUILLON', 'AGUILLON', 'justinlebrilla@gmail.com', '$2y$10$knfe6We8mtL3hyM5N2rVZuH7tKRLhsRTkwEQarlrXhx.7KgU/h4m2', 'student', '10', '2026-06-03 00:55:04', NULL, 0, '2010-11-12', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK IPIL-IPIL, BRGY. ENCLARO, BINALBAGAN, NEGROS OCCIDENTAL', '09944620342', 'MIKE SAN-ORIL LEBRILLA', 'LABORER', '', 'JUVY GEDOR AGUILLON', 'LOCALLY EMPLOYED', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116870160026', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(289, 'STU-2026-0254', 'RUDA, JANMARK MOHILLO', 'MOHILLO', 'janmarkruda@gmail.com', '$2y$10$SrZUATRV2ocCGuWysvgB9uFHR4I5he6oScTPeaWrAS8HnKAPwHyT2', 'student', '9', '2026-06-03 00:59:07', NULL, 0, '2011-01-05', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO CABADBARAN, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '', 'NORBERTO GALABASA RUDA', 'LABORER', '', 'GERLIE GANAGANAG MOHILLO', 'LABORER', '', 'JESSICA MOHILLO RUDA', '', 'SISTER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 1, '116872170008', 0, 0, 0, '', 0, 1, 'ITOMAN MAGHAT-BUKIDNON', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(290, 'STU-2026-0255', 'PELARIN, MARGIE ARTAJO', 'ARTAJO', 'margiepelarin@gmail.conm', '$2y$10$3WRejuTp/7W6OlFMxs3ecOcTtkL302.aWNTiFBaY/2BLzKPYwfy2.', 'student', '7', '2026-06-03 01:03:34', NULL, 0, '2014-02-05', 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09096349776', 'JOHN REY PASU-IT PELARIN', 'LABORER', '', 'MARICAR ARTAJO PELARIN', 'LABORER', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '90', 1, '116868190036', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(291, 'STU-2026-0256', 'GARCESA, HANNAH FLORES', 'FLORES', 'hannahgarcesa@gmail.com', '$2y$10$JLg884RNSv55uee86dTx2eokLMFGX6Vj1a24H9BmDSoKvXkegZEwW', 'student', '9', '2026-06-03 01:06:35', NULL, 0, '2012-03-04', 'Female', 14, 'HIMAMAYLAN CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09108039167', 'JOSE REX VALDEZ GARCESA', 'FISHERMAN', '', 'RODESA FLORES GARCESA', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '116868170041', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(292, 'STU-2026-0257', 'ENLAGADA, JAYMARK PAULINO', 'PAULINO', 'jaymarkenlegada@gmail.com', '$2y$10$ifXUuimu.cD.mXnFhps5UukrNyWSuxfSv/Azudh5sDS6QG7V3U37.', 'student', '10', '2026-06-03 22:21:07', NULL, 0, '2011-08-12', 'Male', 14, 'ISABELA', 'Filipino', '', 'Upper Tambu, Brgy. Tambu, Binalbagan, Negros Occidental', '09557341541', 'EDGAR SARAHINA ENLEGADA', 'LABORER', '', 'RECA FABELLAR SARAHINA', 'HOUSEWIFE', '', 'JOCELYN PAULINO ENLEGADA', '09557341541', 'AUNT', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '80', 1, '116878160009', 0, 1, 0, '', 1, 1, 'BUKIGNON', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(293, 'STU-2026-0258', 'SAN-OREL, JHON REL MORENIO', 'MORENIO', 'jhonrelsanorel@gmail.com', '$2y$10$dO57keswZVrmnGO7AnQ0yuGNRpNUdPG4vlr7utH3hU11sI.C3DT7C', 'student', '10', '2026-06-03 22:30:15', NULL, 0, '2011-05-26', 'Male', 15, '', 'Filipino', '', 'SITIO TAMBU, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '09756708507', 'JUNATHAN MECHABE SAN-OREL', 'LABORER', '', 'ELMA ENDONELIA MORENIO', 'LABORER', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '80', 1, '116879160010', 0, 1, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(294, 'STU-2026-0259', 'SALEM, JONABEL PONDADOR', 'PONDADOR', 'jonabelsalem@gmail.com', '$2y$10$uOKOBhVSy8WXtkG68AtY9uCjv3B8eW.6Wmak.Fy9/7mtcXsrX748K', 'student', '11', '2026-06-03 22:41:29', NULL, 0, '2009-07-06', 'Female', 16, 'BINALBAGAN', 'Filipino', '', 'SITIO CABALANTIANAN, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'CANY BACALSO SALEM', 'LABORER', '', 'UNELYN  ENOTIBO PONDADOR', 'LABORER', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2024-2025', '78', 1, '116879150005', 0, 1, 0, '', 0, 1, 'BUKIGNON', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(295, 'STU-2026-0260', 'FABURADA, WINGIL PABILLAR', 'PABILLAR', 'wingilfaburada@gmail.com', '$2y$10$KZ5TqYoJVxsWUICaCT7gZud86pGEifTYWECO9Lv6jAKVdgZU7Wvzq', 'student', '9', '2026-06-03 22:50:52', NULL, 0, '2011-07-22', 'Female', 14, 'BINALBAGAN', 'Filipino', '', 'PUROK ROSE, BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '09937503517', 'WILQUIM PABILLAR FABURADA', 'LABORER', '', 'GINA PABILLAR FABURADA', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '116878160050', 0, 1, 0, '', 0, 1, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(296, 'STU-2026-0261', 'HEMPAYAN, JELMART MOHILLO', 'MOHILLO', 'jelmarthempayan@gmail.com', '$2y$10$vNT/bB1D5baT66cYbeCWO.glZ6ZIL7GkveWjSOFLeRBGmBNLn3jm6', 'student', '8', '2026-06-03 22:58:40', NULL, 0, '2013-05-04', 'Male', 13, 'BINALBAGAN', 'Filipino', 'NEW APOSTOLIC CHURCH', 'SO. MASLOG, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'JENNY MORE HEMPAYAN', 'LABORER', '', 'ANALIE BELONI MOHILLO', 'LABORER', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116879180028', 0, 1, 0, '', 1, 1, 'ITUMAN MAGHAT-BUKIDNON', 'BISAYA, HILIGAYANON', 'promoted', '', '', NULL, NULL),
(297, 'STU-2026-0262', 'ADON, KINGSLY BENZ SARAD', 'SARAD', 'kingslybenzadon@gmail.com', '$2y$10$4p/39MQJJtKSxFgKbIjsbeHX8iTQ.WcgEOJGAP3ks3UCl93sI7HzG', 'student', '11', '2026-06-03 23:05:32', NULL, 0, '2010-04-08', 'Male', 16, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09369504878', 'BENJIE LIMSON ADON', 'BRGY. KAGAWAD', '09126217283', 'DEWANIE SALCEDO SARAD', 'LOCALLY EMPLOYED`', '09369504878', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL', 'BRGY. PAGLAUM, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '440568150011', 0, 1, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(298, 'STU-2026-0263', 'JOMAYAO, DWENSON TRINIDAD', 'TRINIDAD', 'dwensonjomayao@gmail.com', '$2y$10$W78Cz57hhwtIipHcLEDkNunljGsH2Z5qxMxMAEs/gj1YCzaMLn5/a', 'student', '12', '2026-06-03 23:11:49', NULL, 0, '2009-01-29', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09480097816', 'JONIE VALINTINO JUMAYAO', 'LABORER', '', 'MILLISA TAMAYO TRINIDAD', 'VENDOR', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 1, '116876140008', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(299, 'STU-2026-0264', 'GARCESA, JAN DAVE FLORES', 'FLORES', 'jandavegarcesa@gmail.com', '$2y$10$9R4ODOk0YRACOGSjziqjyOpTf6W8pP4oEDwVoVmJETqLUM9mmP5ri', 'student', '12', '2026-06-03 23:15:03', NULL, 0, '2008-08-24', 'Male', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09464337353', 'JOSE REX VALDEZ GARCESA', 'LABORER', '', 'RODESA FLORES GARCESA', 'VENDOR', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 1, '116868140006', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(300, 'STU-2026-0265', 'SARASA, JAYLORD PLESARIO', 'PLESARIO', 'jaylordsarasa@gmail.com', '$2y$10$YsC9rC7kKV2079/xyotcteBpKdQLjr6yCeqNL.FciEPK4kWTmPRRy', 'student', '10', '2026-06-03 23:21:32', NULL, 0, '2011-10-25', 'Male', 14, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', '', '', '', 'JANSIE PLESARIO SARASA', '', '', 'CEDITH PLESARIO', '', 'GRANDMOTHER', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 0, '116876170013', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(301, 'STU-2026-0266', 'SARASA, LAURENZ SALAZAR', 'SALAZAR', 'laurenzsarasa@gmail.com', '$2y$10$eBOgmJ1aqKJ0V8W0liPLSeDAIwFM/A69g4IHVKxqwoBjM0wLtxVV6', 'student', '10', '2026-06-03 23:24:42', NULL, 0, '2011-04-21', 'Male', 15, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'SITIO NABUSWANG, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ENRIQUE TAMAYO SARASA', 'LABORER', '', 'GINA GARCIA SARASA', 'HOUSEWIFE', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116876160005', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(302, 'STU-2026-0267', 'ORTADO, ANGELICA PRECILDA', 'PRECILDA', 'angelicaortado@gmail.com', '$2y$10$xmBgCreVhqA0KdGbdxLSa.06PMvDXQF..Kd7LOqQ2T4emDDhQYEM2', 'student', '12', '2026-06-03 23:29:03', NULL, 0, '2009-02-25', 'Female', 17, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'REQUER ESCO ORTADO SR.', 'LABORER', '', 'ANGELYN GATILOGO PRECILDA', 'LABORER', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 1, '116868140076', 0, 0, 0, '', 1, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(303, 'STU-2026-0268', 'BADERA, JOHN MECKENTH BANGCOLIO', 'BANGCOLIO', 'johnmckenthbadera@gmail.com', '$2y$10$oEfIVsb9xmXe8uvqP/s7eu.6LHJeQ31B.Jz7dubl/jcmMVOxht3m6', 'student', '10', '2026-06-03 23:44:42', NULL, 0, '2010-11-15', 'Male', 15, 'BACOLOD CITY', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '', 'JOMARIE MARFIEL BADERA', 'LABORER', '', 'JENELYN AMPARADO', 'LOCALLY EMPLOYED', '', '', '', '', 'TLCA-BINALBAGAN', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '94', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(304, 'STU-2026-0269', 'PEDRO, VINCENT CANTILA', 'CANTILA', 'vincentpedro@gmail.com', '$2y$10$6HzhtBqRxR4gI/xG32jySeDRZ0dYV9X1fsfop/MmojMEpiv8NiDGq', 'student', '7', '2026-06-04 00:01:57', NULL, 0, '2012-11-06', 'Male', 13, 'BINALBAGAN', 'Filipino', 'UCCP', 'SITIO TAMBU, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'MIRARY ABSIN PEDRO', 'LABORER', '', 'EVELYN ELARDO CANTILA', 'LABORER', '', '', '', '', 'TAMBU ELEMENTARY SCHOOL', 'SITIO TAMBU, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '92', 1, '116879180035', 0, 0, 0, '', 1, 1, 'ITUMAN MAGHAT-BUKIDNON', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(305, 'STU-2026-0270', 'JORGE, RENADELL SINICOLAS', 'SINICOLAS', 'renadelljorge@gmail.com', '$2y$10$yTKbU/0Fmz/zGYJaG/9dEOHjnDugux/bjFM8bZweL.cHkvLe0heb.', 'student', '7', '2026-06-04 00:07:17', NULL, 0, NULL, 'Female', 12, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK GREENSHELL, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09850079060', 'RENE BUDAON JORGE', 'LABORER', '', 'MARY DELIA SINICOLAS JORGE', 'Housewife', '', 'MARY CHRISTINE PALMA LIRAZAN', '09850079060', 'EMPLOYER', 'TAMBU ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 1, '', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(306, 'STU-2026-0271', 'LEQUIS, MICHELL JANE ROMANO', 'ROMANO', 'michelljanelequis@gmail.com', '$2y$10$/axE87jhFSl8dUHV2WRqau7qPT2y90aLRtrcasX58F9xKUJ5wgKdS', 'student', '7', '2026-06-04 00:14:22', NULL, 0, '2012-12-02', 'Female', 13, 'BINALBAGAN', 'Filipino', 'ROMAN CATHOLIC', 'PUROK TUWAY, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '09071810129', 'GORDITO ERTEMOSO LEQUIS', 'LABORER', '', 'EDITHA MONTISIMO ROMANO', 'LABORER', '', '', '', '', 'CANMOROS ELEMENTARY SCHOOL', 'PUROK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '82', 1, '116868180062', 0, 0, 0, '', 0, 0, '', 'HILIGAYNON', 'promoted', NULL, NULL, NULL, NULL),
(307, 'STU-2026-0272', 'NORIEGA, BRISBANE BUT-AY', 'BUT-AY', 'brisbanenoriega@gmail.com', '$2y$10$gGW6HPSLVFHfMj.hPVHCIu.GV68/AUINGWB5eWBRtYVutgpQO1el.', 'student', '7', '2026-06-04 00:36:04', NULL, 0, '0000-00-00', 'Male', 11, 'BINALBAGAN', 'Filipino', '', '', '', '', '', '', '', '', '', '', '', '', 'SANTOL ELEMENTARY SCHOOL', 'BRGY. SANTOL, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '85', 1, '', 0, 0, 0, '', 0, 0, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(308, 'STU-2026-0273', 'SALEM, JONEL PONDADOR', 'PONDADOR', 'jonelsalem@gmail.com', '$2y$10$ccSqs6.TThPLaFB.PKFBm.Tr7iJjxMZwCIfKZDb1BJqWzq4Fnpl.q', 'student', '7', '2026-06-04 00:48:07', NULL, 0, '2011-08-30', 'Male', 14, 'BINALBAGAN', 'Filipino', 'CHURCH OF CHRIST', 'SO. CABALANTIANAN, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'GANY BACALSO SALEM', 'LABORER', '', 'UNELYN  ENOTIBO PONDADOR', 'LABORER', '', '', '', '', 'TAMBU ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116879180006', 0, 0, 0, '', 1, 1, 'BUKIGNON', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(309, 'STU-2026-0274', 'GREGORIO, JEREMY MOHILLO', 'MOHILLO', 'jeremygregorio@gmail.com', '$2y$10$MWqJZ7JRgZaByiWXu/P2O.IFGEuuPY9FVJf2ksoA/C1xMSV9vhJ/W', 'student', '7', '2026-06-04 00:59:03', NULL, 0, '2013-07-08', 'Male', 12, 'BINALBAGAN', 'Filipino', 'NEW APOSTOLIC CHURCH', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'JESON AGRAVANTE GREGORIO', 'LABORER', '', 'MELLY GRACE BELONI MOHILLO', 'Housewife', '', '', '', '', 'TAMBU ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116879180013', 0, 0, 0, '', 1, 1, '', 'HILIGAYNON', 'promoted', '', '', NULL, NULL),
(310, 'STU-2026-0275', 'VALLENTEN, IRISH METSABE', 'METSABE', 'irishvallenten@gmail.com', '$2y$10$zAuMM3Hstn763g2cZPqszeLUmexRAKaxvzYeSqYQ4Y06ZY4rQh6Zi', 'student', '7', '2026-06-04 01:59:03', NULL, 0, '2013-01-26', 'Female', 13, 'BINALBAGAN', 'Filipino', 'Christian Fellowship', 'SO. NABIRASAN, BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'NONEBOY GARY VALLENTEN', 'LABORER', '', 'GINELYN BUSTAMANTE METSABE', 'LABORER', '', '', '', '', 'AMONTAY ELEMENTARY SCHOOL', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '81', 1, '116864200044', 0, 0, 0, '', 1, 1, '', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(311, 'STU-2026-0276', 'HEMPAYAN, MELODY MATEO', 'MATEO', 'melodyhempayan@gmail.com', '$2y$10$0zo4qe3j9ApcFVWrHU4CluoRacOtMq7NYshp/sKMFYW1L44hAidYS', 'student', '10', '2026-06-04 02:08:22', NULL, 0, '2010-11-05', 'Female', 15, 'BINALBAGAN', 'Filipino', 'NEW APOSTOLIC CHURCH', 'BRGY. AMONTAY, BINALBAGAN, NEGROS OCCIDENTAL', '', 'ERWIN AURELLO HEMPAYAN', 'LABORER', '', 'INA MAY ESTANIEL MATEO', 'HOUSEWIFE', '', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'BRGY. SANTOL BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '86', 1, '116879160013', 0, 1, 0, '', 1, 1, 'BUKIGNON', 'BISAYA, HILIGAYANON', 'promoted', NULL, NULL, NULL, NULL),
(312, 'STU-2026-0277', 'DELGADO, LUNA GUMBAN', 'GUMBAN', 'lunadelgado@gmail.com', '$2y$10$9L7Bex92G6u9G2DZsF4GF.P7P8zznLUh.is3pW4nfbNc6bP8A2Ozu', 'student', '12', '2026-06-04 22:53:23', NULL, 0, '2009-11-25', 'Female', 16, 'Himamaylan, Negros Occidental', 'Filipino', 'Catholic', 'BRGY. TALABAN, HIMAMAYLAN, NEGROS OCCIDENTAL', '', 'ROY MAHINAY DELGADO', 'Fisherman', '', 'LYNETTE  GUMBAN DELGADO', 'Housewife', '', '', '', '', 'TLCA - Bin', 'PRK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '93', 1, '117102140020', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(313, 'STU-2026-0278', 'SALAMANCA, JOHN CARLO MEDECINIO', 'MEDECINIO', 'johncarlosalamanca@gmail.com', '$2y$10$rzmt9i1L5UxZ51Gsjw0G8uIXQY8j.Qi6woUb0uv.UN777hKBxb5VW', 'student', '8', '2026-06-05 00:23:55', NULL, 0, '2012-10-15', 'Male', 13, 'ANGELES, PAMPANGA', 'Filipino', 'Catholic', 'Prk. Alimango, So Nabuswang, Brgy. Canmoros, Binalbagan, Negros occidental', '', 'Rey Lacorte Salamanca', 'Fisherman', '', 'Chanel Tamayo Medecinio', 'Housewife', '', '', '', '', 'TLCA - Bin', 'PRK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '83', 1, '116876180011', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(314, 'STU-2026-0279', 'ARELLANO, SHEAN CHRISTIAN VERDE', 'VERDE', 'sheanchristianarellano@gmail.com', '$2y$10$pmCR.CFBnUdXdAMetxwuOuiKjacGxIqzMTaNZnDqNsztpmIxYGNMO', 'student', '12', '2026-06-05 00:31:07', NULL, 0, '2008-01-09', 'Male', 18, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', '4th Street, Brgy. Progreso, Binalbagan, Negros occidental', '09661472264', 'Vall Perez Arellano', 'Fisherman', '', 'Lyra  Verde Arellano', 'Housewife', '', '', '', '', 'TLCA - Bin', 'PRK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '78', 0, '116870130031', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(315, 'STU-2026-0280', 'NIEVES, JONATHAN ROBLES', 'ROBLES', 'jonathannieves@gmail.com', '$2y$10$Q4HP5tWyi5eM.BEjvUWIeuaSMx/zlllXi5HqFV8bZISextZphLU1i', 'student', '9', '2026-06-05 00:43:25', NULL, 0, '2011-11-23', 'Male', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk Punaw, Brgy. Canmoros, Binalbagan, Negros Occidental', '09930926214', 'Jonathan Mayoga Nieves Sr.', 'Fisherman', '09930926214', 'Anabel Robles Nieves', 'Housewife', '', '', '', '', '', 'PRK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '', 0, '', 0, 1, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(316, 'STU-2026-0281', 'SEVILLA, JIMUEL GARCENIEGO', 'GARCENIEGO', 'jimuelsevilla@gmail.com', '$2y$10$1bqQ5XkrAOrm7ecpEzWItej3O5enP6aJ8DdhD1Ai62zmLEX2LqT1K', 'student', '10', '2026-06-05 00:56:38', NULL, 0, '2011-07-12', 'Male', 14, 'Himamaylan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Punaw, Brgy. Canmoros, Binalbagan, Negros Occidental', '09815650875', 'Cris Rodriguez Sevilla', 'Fisherman', '', 'Cathyrine Garceniego Sevilla', 'Housewife', '09815650875', '', '', '', 'BINALBAGAN NATIONAL HIGH SCHOOL-SANTOL EXTENSION', 'Binalbagan, Negros Occidental', '2025-2026', '', 0, '', 0, 1, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', '', '', NULL, NULL),
(317, 'STU-2026-0282', 'BEKER, MICHAEL BACLAYO', 'BACLAYO', 'michaelbeker@gmail.com', '$2y$10$NA5zVrPQPFViFm0sTbbed.UzGj3jUbd4TYam5MJaDJW83iWqUcxvW', 'student', '9', '2026-06-05 01:06:30', NULL, 0, '2011-02-27', 'Male', 15, 'Tacbalugan, Samar', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '209218769831', 'Loduvico Hengos Beker', 'Fisherman', '', 'Myra Varela Baclayo', 'Housewife', '09218769831', 'Geva Sarad Beker', '09303723441', 'Aunt', 'TLCA - Bin', 'PRK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '88', 1, '136483170151', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(318, 'STU-2026-0283', 'DONATO, ANGELINE ZULUETA', 'ZULUETA', 'angelinedonato@gmail.com', '$2y$10$MIqTG0neWQKD7rN82ov3l.uTGBJRIoBdxKDw.iHvbCqNtZdxW8kpi', 'student', '10', '2026-06-05 01:53:33', NULL, 0, '2011-08-10', 'Female', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'So. Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09076568137', 'Edwin Patribo Donato', 'Fisherman', '09070568137', 'Nenita Tamayo Zulueta', 'Housewife', '09070568137', 'Niño Patong Donato', '09124560040', 'Ancle', 'TLCA - Bin', 'PRK AGUIHIS, BRGY. CANMOROS, BINALBAGAN, NEGROS OCCIDENTAL', '2025-2026', '79', 1, '116876160004', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(319, 'STU-2026-0284', 'SARASA, REIZA SALAZAR', 'SALAZAR', 'reizasarasa@gmail.com', '$2y$10$jt9rfc2uSxIdisk61r6FOOZONcOMZsCnPD8EPNXYUThPoRxyAO2ZK', 'student', '12', '2026-06-06 21:51:32', NULL, 0, '2009-07-04', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09708143161', 'Enrique Salazar', 'Fisherman', '09708143161', 'Gina Salazar Sarasa', 'Housewife', '09708143161', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '89', 1, '116876140023', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(320, 'STU-2026-0285', 'SALAZAR, XENON CATAGUE', 'CATAGUE', 'xenonsalazar@gmail.com', '$2y$10$zws9jK3qEU/JBe1CtyA6YOaiGZ9elyreEve6m94p5mHm0nhg/Y47G', 'student', '12', '2026-06-06 22:02:01', NULL, 0, '2008-11-19', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09922238226', 'Nicanor Garcia Salazar', 'Fisherman', '09922238226', 'Renalyn Salazar', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '95', 1, '116876140012', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', '', '', NULL, NULL),
(321, 'STU-2026-0286', 'ELIJAN, DARIE JR. SALAZAR', 'SALAZAR', 'darieelijan@gmail.com', '$2y$10$CJ5IDefnCLB8QnkJ6bcaSuqa2EYvYI5qw8BXPKypVR7wmukVcSI9m', 'student', '12', '2026-06-06 22:08:51', NULL, 0, '2009-05-27', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Darie Casuyon Elijan', 'Fisherman', '', 'Memesola Garcia Salazar', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '88', 1, '116876140006', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(322, 'STU-2026-0287', 'LAURILLA, SHAN ROMER FUENTES', 'FUENTES', 'shanromerlaurilla@gmail.com', '$2y$10$g8XWHUt7roug.9cS7geheufMlHx9AhbgVAm.Q0QbHFMo8p1LAIRrK', 'student', '12', '2026-06-06 22:21:05', NULL, 0, '2009-01-08', 'Male', 17, 'bACOLOD CITY, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Carmen St., Brgy. San Pedro, Binalbagan, Negros Occidental', '09300146672', '', 'Fisherman', '', 'Merliza Fuentes Laurilla', 'Housewife', '09300146672', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '83', 1, '116867140058', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(323, 'STU-2026-0288', 'Borigas, Gerald Pasionela', 'Pasionela', 'geraldborigas@gmail.com', '$2y$10$fjYACDDPO/6sgZypUoLVTekMxqF/zmdya.iBbINEZxj8rKJ1HLVSu', 'student', '12', '2026-06-06 22:44:59', NULL, 0, '2008-08-19', 'Male', 17, 'Cainta, Rizal', 'Filipino', 'Catholic', 'Hacienda, Brgy. Sto Rosario, Binalbagan, Negros Occidental', '', 'Flaviano Ligbos Borigas', 'Laborer', '', 'Berve Estorco Pasionela', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '77', 1, '116867130050', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(324, 'STU-2026-0289', 'Estarin, Allyza Lyndawn Florete', 'Florete', 'allyzaestarin@gmail.com', '$2y$10$crC9a0ipaoXNDRu76BdFFOpuc0O3Om0kFj5MoneYe1OvqGTmiLxpy', 'student', '8', '2026-06-06 22:51:47', NULL, 0, '2013-09-08', 'Female', 12, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Greenshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Darry Sibidin Estarin Sr.', 'Fisherman', '', 'Ailyn Florete Estarin', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '93', 1, '116868180060', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(325, 'STU-2026-0290', 'Romero, Ara Loren Sablon', 'Sablon', 'aralorenromero@gmail.com', '$2y$10$nRC/lZwOnxpCtDk/.r3qL.WOMqX9SEAN/BrlpODtLE17STh0Yez3i', 'student', '10', '2026-06-06 22:58:40', NULL, 0, '2011-03-19', 'Female', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Punaw, Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Alexander Villanueve Romero', 'Fisherman', '', 'Jessica Alojado Sablon', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 1, '116868160024', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(326, 'STU-2026-0291', 'Valero, Jewell Grace Española', 'Española', 'jewellgracevalero@gmail.com', '$2y$10$KG32CrGZQI.lkBwc8yoSDOd7Vqrkntb3hKbMJQZJEvRwnMSMVpcl6', 'student', '12', '2026-06-06 23:06:44', NULL, 0, '2009-07-21', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '09104351291', 'Romeo Jocson Valero Jr.', 'Laborer', '09104351291', 'Charina Española Valero', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 1, '116868150043', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL);
INSERT INTO `users` (`id`, `empidno`, `name`, `middle_name`, `email`, `password_hash`, `role`, `grade_level`, `created_at`, `image`, `archived`, `date_of_birth`, `gender`, `age`, `place_of_birth`, `nationality`, `religion`, `home_address`, `contact_number`, `father_name`, `father_occupation`, `father_contact`, `mother_name`, `mother_occupation`, `mother_contact`, `guardian_name`, `guardian_contact`, `guardian_relationship`, `last_school_attended`, `last_school_address`, `school_year_completed`, `general_average`, `has_lrn`, `lrn_number`, `is_returnee`, `is_transfer_in`, `has_special_needs`, `special_needs_type`, `is_4ps_beneficiary`, `is_indigenous`, `indigenous_group`, `mother_tongue`, `retention_status`, `retention_reason`, `retention_school_year`, `retention_updated_at`, `retention_updated_by`) VALUES
(327, 'STU-2026-0292', 'Tallafer, Rochet Chloe Marie Jayme', 'Jayme', 'rochettallafer@gmail.com', '$2y$10$chWOfLQiTuQni24rwcgFSeS3Rp6UdjeuGAguyHYnabKtWaMyde03i', 'student', '11', '2026-06-06 23:13:04', NULL, 0, '2010-08-19', 'Female', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros mOccidental', '09668307708', 'Roger Sarasa Tallafer', 'Fisherman', '', 'Presentacion Sisduero Jayme', 'Housewife', '09668307708', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '86', 1, '116868150058', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(328, 'STU-2026-0293', 'SARAD, CHARMAINE MILLADA', 'MILLADA', 'charmainesarad@gmail.com', '$2y$10$RRQNPFLlKEoz3XadYlPFkudL3Gfh06csWnpZxMVukS0l4ygP/sqyS', 'student', '12', '2026-06-08 01:01:38', NULL, 0, '2009-01-31', 'Female', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. guihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Charlie Gepa Sarad', 'Fisherman', '', 'Susana Millada Sarad', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. guihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '93', 1, '116868150038', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(329, 'STU-2026-0294', 'CUENCA, ARJAY SEMILLA', 'SEMILLA', 'arjaycuenca@gmail.com', '$2y$10$N3nb5q4HD7ydmGCskf2XoOBAHVzHll5fi/dcNn8ccKePPcw3y1aZ6', 'student', '10', '2026-06-08 01:29:32', NULL, 0, '2011-02-05', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Eddie Sibidin Cuenca', 'Fisherman', '', 'Margie Mucas Semilla', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. guihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '83', 1, '116868160032', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(330, 'STU-2026-0295', 'PASUIT, JESIE LEGASPE', 'LEGASPE', 'jesiepasuit@gmail.com', '$2y$10$CCt.gwoBS3r7GJQS5Y2hpufwp8e4IXtxaBbKUyWipRD2p5bLcPbTO', 'student', '9', '2026-06-08 01:47:26', NULL, 0, '2008-12-24', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Tuway, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Antonio Pasuit', 'Fisherman', '', 'Quintera Legaspe', 'Housewife', '', 'Razel Pasuit Romano', '', 'Aunt', 'TLCA - Bin', 'Prk. guihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '73', 1, '117262140020', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(331, 'STU-2026-0296', 'PIOJO, JOHN PROYLAND CASA', 'CASA', 'johnproylandpiojo@gmail.com', '$2y$10$snch8Yt67oOZE.kfUq4CmeVhECOyx9oY6czymJ1gMtFv6yMNM2wKC', 'student', '11', '2026-06-08 01:57:55', NULL, 0, '2010-10-30', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '09563265100', 'vecente Flores Piojo', 'Fisherman', '09563265100', 'Noralyn Casa Piojo', 'Housewife', '', 'Razel Pasuit Romano', '', 'Aunt', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '85', 1, '116868150025', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(332, 'STU-2026-0297', 'GARCIA, MYRNA MONEZ', 'MONEZ', 'myrnagarcia@gmail.com', '$2y$10$foK6up2pvxPjCzGNSKQm1uc2tNwRGaQOKHCCjIqc5NnyHmDz6OC/q', 'student', '10', '2026-06-08 02:08:31', NULL, 0, '2006-11-18', 'Female', 19, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Jeey Zamora Garcia', 'Fisherman', '', 'Annabell Monez', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '', '', 1, '', 1, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(333, 'STU-2026-0298', 'PACTORAN, JEFERSON OBARRA', 'OBARRA', 'jefersonpactoran@gmail.com', '$2y$10$81s2YTQQvhjhzv0CfS8kleIpiUR4q9yqPbkczj48dtNMiqF/jGrOW', 'student', '11', '2026-06-08 23:02:12', NULL, 0, '2009-12-15', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Vanda, Brgy Santol, Binalbagan, Negros Occidental', '09534151871', 'Jomar Esong Pactoran', 'Laborer', '', 'Melanie Obarra Pactoran', 'Housewife', '', '', '', '', 'BNHS - Santol Ext.', 'Brgy. Santl, Binalbagan, Negros Occidental', '2025-2026', '86', 1, '116878150063', 0, 1, 0, '', 0, 1, 'Ituman, Maghat, Bukidnon', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(334, 'STU-2026-0299', 'RIVERA, JOHN ERIC BABARAN', 'BABARAN', 'johnericrivera@gmail.com', '$2y$10$d8qTEm2RdmrVbrGycko90u5/TWWmWeeeqAlEc7Cp/kosviTqfPz0m', 'student', '9', '2026-06-09 23:49:42', NULL, 0, '2011-03-10', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Tuway, Brgy. Canmoros, Binalbagan, Negros Occidental', '', '', '', '', 'Manilyn Babaran Rivera', 'Housewife', '', '', '', '', 'TLCA  - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '78', 1, '116868160013', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(335, 'STU-2026-0300', 'PATEÑO, JOSHUA CAPASILAN', 'CAPASILAN', 'joshuapateno@gmail.com', '$2y$10$IYfL81rGuCWfH6HlIbDb8.uIKg.9Gtnm9qD4LVrsb3DfCkPY1rP8C', 'student', '9', '2026-06-10 01:21:16', NULL, 0, '2010-05-12', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Ipil-ipil, Sitio Tap-ok, Brgy. Enclaro, Binalbagan, Negros Occidental', '09810870379', 'Wenny Perinal Pateño Sr.', 'Laborer', '09810890379', 'Rudilyn Capasilan', 'Housewife', '09560671817', '', '', '', 'TLCA  - Bin', 'Prk Aguihis, Brgy. Canmoros, Binalbagn. Negros Occidental', '2025-2026', '72', 1, '116870150059', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(336, 'STU-2026-0301', 'SARAD, KEZIAH FLORES', 'FLORES', 'keziahsarad@gmail.com', '$2y$10$TcCNRb9PpjFYlbTYbDRSVOqmYpU1fRpKZexypPEwRjhdSgmDF1xvi', 'student', '9', '2026-06-14 23:31:18', NULL, 0, '2012-01-24', 'Female', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09633205669', 'Ernie Mission Sarad', 'Fisherman', '09121789890', 'Ma. Elisa Glaraga Flores', 'Housewife', '09633205669', '', '', '', 'TLCA  - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, anegros Occidental', '2025-2026', '88', 1, '116868170021', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(337, 'STU-2026-0302', 'GELLA, JANILA DELA PIÑA', 'DELA PIÑA', 'janilagella@gmail.com', '$2y$10$tnDCAEBzHDKReuu2HVXAYOx3fw5jp7.ErNtlNr2Z4MAHhBZPYRhP6', 'student', '11', '2026-06-14 23:58:42', NULL, 0, '1994-05-03', 'Female', 30, 'Guihulngan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental.', '09633205669', 'Danny Atong Gella', 'Fisherman', '', 'Enriquita Gella', 'Housewife', '', '', '', '', 'ALS', 'Binalbagan, Negros Occidental', '', '73.5', 1, '508062400705', 0, 1, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(338, 'STU-2026-0303', 'ROBLES, JAY SARASA', 'SARASA', 'jayrobles@gmail.com', '$2y$10$WXHEIe.meojMHFqX/buYOuDcv0zOBDbLSt/aoNohZD7zoVisNXb1.', 'student', '11', '2026-06-15 00:53:00', NULL, 0, '2006-10-04', 'Male', 19, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09933164387', 'Jay Robles', 'Fisherman', '09933164387', 'Cheryl mSarasa Robles', 'Housewife', '09933164387', '', '', '', 'ALS', 'Binalabagan, Negros Occidental', '2025-2026', '69', 1, '', 0, 1, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(339, 'STU-2026-0304', 'FRANCISCO, JENEL ESCONDE', 'ESCONDE', 'jenelfrancisco@gmail.com', '$2y$10$0SfVI8wbuiQPfb7vL7m7l.GsXLoZJPvEK9T.Hulma1RYttXURxqX.', 'student', '11', '2026-06-15 01:17:01', NULL, 0, '2009-06-05', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Ork. Greenshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09126461372', 'Johnny Cadiena Francisco', 'Fisherman', '09126461372', 'Leonisa Esconde Francisco', 'Housewife', '09126461372', '', '', '', 'TLCA - BIN', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '72', 1, '116866140058', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(340, 'STU-2026-0305', 'GAMPOSILAO, REMARK ANONO', 'ANONO', 'remarkgamposilao@gmail.com', '$2y$10$SJOSOR1od54O3tibHsaHL.irDlAGXx5cNWE2yrWBUVIJFJpSURQne', 'student', '10', '2026-06-15 18:31:21', NULL, 0, '2011-06-04', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', '', '09815580449', 'Roger Castillo Gamposilao', 'Fisherman', '09815580449', 'Wilma Anono Gamposilao', 'Housewife', '', '', '', '', 'TLCA - BIN', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '87', 1, '116868160034', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(341, 'STU-2026-0306', 'CARI-AN, ZERXYLL DELA CRUZ', 'DELA CRUZ', 'zerxyllcarian@gmail.com', '$2y$10$Q2Q0mwwsKG7qLtE2teL6Su0yTP0QqPPU/WmirGI1/R2c1nTHT4kvK', 'student', '12', '2026-06-15 18:43:17', NULL, 0, '2009-10-02', 'Male', 16, 'KABANKALAN, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09075842667', 'ZERXIS SIAMEN CARI-AN', 'Fisherman', '09072001128', 'MICHELLE DESALES DELA CRUZ', 'Housewife', '09075842667', '', '', '', 'TLCA - BIN', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '95', 1, '116867140065', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(342, 'STU-2026-0307', 'SADIA, ANGELICA VILLANUEVA', 'VILLANUEVA', 'angelicasadia@gmail.com', '$2y$10$.8K3NjP5XM/P6JWjnDYc9OYvaqLC1u/fQvsB/Uv544vZ0sM2k5rwK', 'student', '12', '2026-06-15 18:51:38', NULL, 0, '2009-12-04', 'Female', 16, 'LA CASTELLANA, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Prk. Vanda, Brgy. Santol, Binalbagan, Negros Occidental', '09693691463', 'Gilbert Balbon Sadia', 'Laborer', '', 'Ailyn Villanueva Sadia', 'Housewife', '', 'Alma Barrientos Villanueva', '09693691463', 'Aunt', 'TLCA - BIN', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 1, '117213140117', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(343, 'STU-2026-0308', 'RENDON, CYBELLE OSORIO', 'OSORIO', 'cybellerendon@gmail.com', '$2y$10$g6Qsx74x2DfntnEVttxWMuGKDQzThqb0oVYZtZ4QEUhW78UfNhoX6', 'student', '12', '2026-06-15 19:59:33', NULL, 0, '2008-08-04', 'Female', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', '', '09628199429', 'Andy Pesales Rendon', 'Laborer', '', 'Cessie Rayclan Osorio', 'Housewife', '', 'Susalyn Rendon Cesar', '09628199429', 'Aunt', 'TLCA - BIN', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '84', 0, '116867130097', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(344, 'STU-2026-0309', 'TAYOBA`, EARL JOHN IPILI', 'IPILI', 'earljohntayoba@gmail.com', '$2y$10$Sstdt/iuoi/zyM6Ia2C0s.oAmHYWQhJPtNPugsAoHO.xcfoTyot6a', 'student', '12', '2026-06-15 23:25:25', NULL, 0, '2009-03-20', 'Male', 17, 'Quezon City, Manila', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '', 'Generoso Tallo Tayoba', 'Fisherman', '', 'Juanita Mag-aso Ipili', 'Housewife', '', '', '', '', 'TLCA - BIN', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '91', 1, '116868140021', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', NULL, NULL, NULL, NULL),
(345, 'STU-2026-0310', 'BASILIO, JONDEL PUYONG', 'PUYONG', 'jondelbasilio@gmail.com', '$2y$10$3Cl2M.kZ8xAVGY4GSpZf5Ok6r7jDCQNjkyjCPIWesz.VNb1jhS5oe', 'student', '11', '2026-06-16 23:11:20', NULL, 0, '2010-05-08', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Greenshell, Brgy. Canmoros, Binalbagan. Negros Occidental', NULL, 'Jose Jonie Evangelio Basilio', 'Fisherman', NULL, 'Elisa Jonela Puyong', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '116868150050', 1, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-17 06:11:20', 18),
(346, 'T020', 'Girlie Gasataya', NULL, 'gasatayagirlie04@gmail.com', '$2y$10$sCyrGprpkZS.0.yjBZnjGeCkKxtHb.RKd25iHhH60d3XleJwRAU3q', 'teacher', NULL, '2026-06-16 23:19:43', 'assets/images/user_1782711629_6e080035c700252c.jpg', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(347, 'STU-2026-0311', 'JUMAYAO, QUIRES SARASA', 'SARASA', 'quiresjumayao@gmail.com', '$2y$10$8ur/0Cbs0iy.MpzJ7apA8ecE2xcK4Retojx7EZuJhYZ2ecs/ZZgSK', 'student', '10', '2026-06-16 23:29:36', NULL, 0, '2010-10-26', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy Canmoros, Binalbagan, Negros Occidental', NULL, 'Joaquin Valentino Jumayao', 'Fisherman', NULL, 'Jojgie Jumayao', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '79', 1, '116876150002', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-17 06:29:36', 18),
(348, 'STU-2026-0312', 'DONATO, RHUM JOHN PUTONG', 'PUTONG', 'rhumjohndonato@gmail.com', '$2y$10$gh/fSqqXvZbaepzuuvK.Eegqu6dtnziQ0rxyautROBRbApkCZdAky', 'student', '10', '2026-06-17 00:50:53', NULL, 0, '2010-12-16', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09928307617', NULL, NULL, NULL, 'Nenia Sarasa Putong', 'OFW', '09928307617', NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, BRGY. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '76', 1, '116876160008', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-17 07:50:53', 18),
(349, 'T021', 'Glen Ruzgal', NULL, 'ruzgalglenn6@gmail.com', '$2y$10$UaDO/zvxVdc4.md/h2/nlevtDlY6wf1OydfPVXS2PQNIRu1P00IWq', 'teacher', NULL, '2026-06-17 01:00:45', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, NULL, 'promoted', NULL, NULL, NULL, NULL),
(350, 'STU-2026-0313', 'TALANQUINES, ROYGEN PACIENTE', 'PACIENTE', 'roygentalanquines@gmail.com', '$2y$10$KRYz8ZHjWwJurLuMIs.X8OgL/8DaJsrkzCUb7KI0KEQ.AlstE7IeC', 'student', '9', '2026-06-17 01:08:46', NULL, 0, '2012-01-21', 'Male', 14, 'Misamis Oriental', 'Filipino', 'Catholic', NULL, '09708069170', 'Mikko Evangelio Talanquines', 'Fisherman', '09708069170', 'Cherry Mae Macasampay Paciente', 'Housewife', '09708069170', NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '81', 1, '440568150004', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-17 08:08:46', 18),
(351, 'STU-2026-0314', 'ROMANO, JHEZMAR PELARIN', 'PELARIN', 'jhezmarromano@gmail.com', '$2y$10$07YbJE.kj31e39K6BkXNyeuS0Ez9bh50/Ug95CiCnUtx7U2OCmTZm', 'student', '11', '2026-06-17 18:43:46', NULL, 0, '2010-01-06', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '09631988302', 'Jomar Romano', 'Fisherman', '0963198302', 'Jolin Pelarin Romano', 'Housewife', '0963198302', NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '83', 1, '116868150007', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 01:43:46', 18),
(352, 'STU-2026-0315', 'CABUCTOLAN, LAYSA LABAO', 'LABAO', 'laysacabuctolan@gmail.com', '$2y$10$qWkqu96hy7pSH9Rhn4vwqOBPIoMgdksASKH6SqqXB5Ai/yfnZztmK', 'student', '11', '2026-06-17 18:52:31', NULL, 0, '2009-10-07', 'Female', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09106906854', 'Mansueto Tallafer Cabuctolan', 'Fisherman', '09106906854', NULL, NULL, NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalabagan, Negros Occidental', '2025-2026', '79', 1, '116876150006', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 01:52:31', 18),
(353, 'STU-2026-0316', 'TABOTABO, PHILIP KAISER LABAO', 'LABAO', 'philipkaisertabotabo@gmail.com', '$2y$10$2BEp9BDKjmZs2kavzeT1j.MO2Pfeu2l92jNqUT0kk.U279HvcbZQG', 'student', '10', '2026-06-17 19:08:01', NULL, 0, NULL, 'Male', NULL, 'Quezon City, Manila', 'Filipino', 'Catholic', 'Seaside Plaza, Prk. Burgos, Aguisan, Himamaylan, Negros Occidental', '09105217392', NULL, NULL, NULL, 'Jeonila Tundag Tabotabo', 'Housewife', '09105217392', NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis,Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '82', 1, '404065160002', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 02:08:01', 18),
(354, 'STU-2026-0317', 'MONCADA, JOHN CARLO DELA CRUZ', 'DELA CRUZ', 'johncarlomoncada@gmail.com', '$2y$10$Ye9S2.0OwSc8aFKdRzzNqebYYpTeaQoK6lFAg486UsYqa1CFqihSu', 'student', '12', '2026-06-17 19:16:40', NULL, 0, '2009-05-30', 'Male', 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Greenshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09120983596', 'Efraim Gamposilao Moncada', 'Fisherman', '09120983596', 'Joy Dela Cruz Moncada', 'Housewife', '09120983596', NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros. Binalbagan, Negros Occidental', '2025-2026', '78', 1, '116868150048', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 02:16:40', 18),
(355, 'STU-2026-0318', 'DAMAYON, SARAH JANE ABARQUIZ', 'ABARQUIZ', 'sarahjanedamayo@gmail.com', '$2y$10$1kO2r8vUNYf6iO648zW/5.LmOgl82QoB08T0KceWp5/qwK7lOaGLm', 'student', '10', '2026-06-17 19:26:23', NULL, 0, '2010-12-09', 'Female', 15, 'Bayawan City, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09519251986', 'Christolito Cabelis Damayon', 'Fisherman', '09519251986', 'Gina Abarquiz Damayon', 'Housewife', '09519251986', NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '90', 1, '116868160041', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 02:26:23', 18),
(356, 'STU-2026-0319', 'SARASA, JUSTINE TAMAYO', 'TAMAYO', 'justinetmayo@gmail.com', '$2y$10$wCgGLypfLKQr.apjMA6mkunehKACm.2F5A3JJ4uRVvRJPsfYoFXmq', 'student', '10', '2026-06-17 19:35:55', NULL, 0, '2010-01-09', 'Male', 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', NULL, 'Renante Sarasa', 'Fisherman', NULL, 'Renabel Tamayo', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '77', 1, '116876150004', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 02:35:55', 18),
(357, 'STU-2026-0320', 'ALUNAN, TRISHA MAY', '', 'trishamayalunan@gmail.com', '$2y$10$wDfQurGtNimeYyLQ5QTXYOD1w/hdCK7vQE7QG1/xC0WqxYvy.Gc3a', 'student', '10', '2026-06-17 19:52:23', NULL, 0, '2010-12-14', 'Female', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', NULL, NULL, NULL, NULL, 'Annaliza Alunan', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '83', 1, '116876170017', 0, 0, 0, NULL, 1, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-18 02:52:23', 18),
(358, 'STU-2026-0321', 'SARASA, ROMEO MUSES', 'MUSES', 'romeosarasa@gmail.com', '$2y$10$B4840oQbHXkFMLlaEhOVreqzoRWSiWAXHUL7M2f7K5nwnVr/O/tZG', 'student', '10', '2026-06-17 23:20:52', NULL, 0, '2009-01-09', 'Male', 17, 'bACOLOD CITY, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbgan, Negros Occidental', NULL, 'Remy Villaflor Sarasa', 'Fisherman', NULL, 'RemiaLuston Muses', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Camoros, Binalabagan, Negros Occidental', '2025-2026', '65', 1, '116876150022', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'retained', 'Attendance issues', '2025-2026', '2026-06-18 06:20:52', 18),
(359, 'STU-2026-0322', 'SARASA, ALVIN JOHN TAMAYO', 'TAMAYO', 'alvinjohnsarasa@gmail.com', '$2y$10$V0PuXBC6A.HEDQdXUdB0PeUKKG7UGAxqEmE9Zqa.0Tfdt7tQbYQGe', 'student', '9', '2026-06-21 18:36:33', NULL, 0, '2011-08-08', 'Male', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', NULL, 'Renante Sarasa', 'Fisherman', NULL, 'Renabel Tamayo Sarasa', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '77', 0, '116876160016', 0, 0, 0, NULL, 1, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-22 01:36:33', 18),
(360, 'STU-2026-0323', 'ROMERO, ANGELO AGUILAR', 'AGUILAR', 'angeloromero@gmail.com', '$2y$10$95u5B.R/TVEdRkAxqAQlf.9FgUYJxNaf1SIP3wEkH6IHu7ZqcSOau', 'student', '11', '2026-06-21 18:49:04', NULL, 0, '2006-04-10', 'Male', 20, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09705940571', 'Ariel Tamayo Romero', 'Fisherman', '09705940571', 'Snooky Nangan Aguilar', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '70', 0, '116876120007', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'retained', 'Attendance issues', '2025-2026', '2026-06-22 01:49:04', 18),
(361, 'STU-2026-0324', 'JUMAYAO, JOAQUIN  JR. SARASA', 'SARASA', 'joaquinjumayao@gmail.com', '$2y$10$49OcvMUAyl3tU8MKNV5.0.jgMb2K/wwRIWicdHUWkbZmcEik74Lrm', 'student', '11', '2026-06-21 19:11:02', NULL, 0, '2007-10-19', 'Male', 18, 'KABANKALAN, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Binalabagan, Negros Occidental', '09708143161', 'Joaquin Jumayao Sr.', 'Fisherman', '09708143161', 'Joyci Tamayo Sarasa', 'Housewife', NULL, NULL, NULL, NULL, 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '69', 1, '116876120002', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'retained', 'Attendance issues', '2025-2026', '2026-06-22 02:11:02', 18),
(362, 'STU-2026-0325', 'TANALGO, JONAS JR. SUBOC', 'SUBOC', 'jonastanalgo@gmail.com', '$2y$10$Tu0TjtqOBb9FxtE3Pk6DdOasqfRJuGXhCI02JgcmJxQEwqU/.OQ06', 'student', '12', '2026-06-21 19:18:58', NULL, 0, '2007-12-26', 'Male', 18, 'Quezon City, Manila', 'Filipino', 'Catholic', 'Prk. Punaw, Brgy. Canmoros, Binalbagan, Negros Occidental', '09309837833', 'Jonas Daquilos Tanalgo Sr.', 'Fisherman', '09309837833', 'Gina Suboc Tanalgo', 'Housewife', '', '', '', '', 'TLCA - Bin', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2024-2025', '79', 1, '116868140045', 0, 0, 0, '', 1, 0, '', 'Hiligaynon', 'irregular', 'Other', '2024-2025', '2026-06-22 02:18:59', 18),
(363, 'STU-2026-0326', 'ABKILAN, RAFAEL NIEVES', 'NIEVES', 'rafaelabkilan@gmail.com', '$2y$10$FHPcs0xe4lXNgDD3PuZ9OuhRSSuHb2JNTZUO1NuYqPAaMU04TOY0G', 'student', '7', '2026-06-21 19:35:31', NULL, 0, '2010-07-20', 'Male', 15, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Punaw, Brgy. Canmoros, Binalbagan, Negros Occidental', '09285492849', 'Jimmy GipollanoAbkilan', 'Fisherman', NULL, 'Arlyn Mayuga Nieves', 'Housewife', NULL, 'Annamay Nieves', '09285492849', 'Aunt', 'ALS', 'ALS Binalbagan, Negros Occidental', '2025-2026', '75.56', 0, NULL, 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-22 02:35:31', 18),
(364, 'STU-2026-0327', 'MONCADA, EFRAIM JR. DELA CRUZ', 'DELA CRUZ', 'efraimmoncada@gmail.com', '$2y$10$AfUA/66l1iqOsBPKcVeNoObSw7HEfKTk7.XwOdgVsZW0itOEnxEw.', 'student', '7', '2026-06-21 19:44:58', NULL, 0, '2012-05-21', 'Male', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Greenshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09120983596', 'Efraim Gamposilao Moncada Jr.', 'Fisherman', NULL, 'Joy Dela Cruz Moncada', 'Housewife', '09120983596', NULL, NULL, NULL, 'Canmoros Elementary School', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '83', 1, '116868170037', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-22 02:44:58', 18),
(365, 'STU-2026-0328', 'VALLENTEN, JAMEPOLD METSABE', 'METSABE', 'jamepoldvallenten@gmail.com', '$2y$10$qoovMbUHn8xR3eQG15YpiuMzbItDnHflKZkWBn.KNVQto.hsg5SWK', 'student', '7', '2026-06-21 20:04:22', NULL, 0, '2012-02-10', 'Male', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Stio Nabirasan, Brgy. Amontay, Binalbagan, Negros Occidental', '', 'Noneboy Gary Vallenten', 'Fisherman', '', 'Ginelyn Bustamante Metsabe', 'Housewife', '', '', '', '', 'CABCAB Elementary School', 'Isabela, Negros Occidental', '2025-2026', '83', 1, '116864200040', 0, 0, 0, '', 0, 0, '', 'Hiligaynon', 'promoted', '', '', '2026-06-22 03:04:22', 18),
(366, 'STU-2026-0329', 'QUINAMOT, JOSHUA IMPERIAL', 'IMPERIAL', 'joshuaquinamot@gmail.com', '$2y$10$pOD5.D4aw6o9SwxcnYJ5LOUvuUvM9RUV.Z6SwlQPq83r0pEeT6vjO', 'student', '7', '2026-06-22 00:59:14', NULL, 0, '2013-05-27', 'Male', 13, 'Cabuyao, Laguna', 'Filipino', 'Catholic', 'Prk. Alimango, Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '09318156428', 'Joseph Pis-an Quinamot', 'Fisherman', '09318156428', 'Herlyn Sarasa Imperial', 'Housewife', NULL, NULL, NULL, NULL, 'Nabuswang Elementary School', 'Sitio Nabuswang, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '82', 1, '116876180005', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-22 07:59:14', 18),
(367, 'STU-2026-0330', 'GAYAPA, RENZ PAUL SARAD', 'SARAD', 'renzpaulgayapa@gmail.com', '$2y$10$J.0BWoGhsPMHLYKvYOuNjuJ8UE4iIkbbCwfGgkhwlKeTYskQN/hLC', 'student', '7', '2026-06-24 20:28:26', NULL, 0, '2013-12-11', 'Male', 12, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '09307686926', 'Jecar Porras Gayapa', 'Fisherman', '09307686926', 'Sheina Rose Jovero Sarad', 'Housewife', '09629533154', NULL, NULL, NULL, 'Canmoros Elementary School', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', '89', 1, '116868190031', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-25 03:28:26', 18),
(368, 'STU-2026-0331', 'DE GUZMAN, CHRIS NHIEL GAYABAN', 'GAYABAN', 'chrisnhieldeguzman@gmail.com', '$2y$10$VZ5K7naaPDEDyi/c//yYk.zHrawzuyATN09jM53SERkLVeFzoh3CS', 'student', '8', '2026-06-25 18:12:21', NULL, 0, '2013-03-06', 'Male', 13, 'KABANKALAN, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Sitio Tap-ok, Brgy. Enclaro, Binalbagan, Negros Occidental', '09564091920', NULL, NULL, NULL, 'Lyan Grace Esmores Gayaban', 'Housewife', '09564091920', NULL, NULL, NULL, 'KABANKALAN NATIONAL HIGH School', 'KABANKALAN, NEGROS OCCIDENTAL', '2025-2026', '84', 1, '117843180038', 0, 0, 0, NULL, 1, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-26 01:12:21', 18),
(369, 'STU-2026-0332', 'DE GUZMAN, NOYANN GAYABAN', 'GAYABAN', 'noyanndeguzman@gmail.com', '$2y$10$UhGC4Qb2VN21Gvas84dYiOSiDTt4pejxEunu8r1NvNLjlgUJgOhnG', 'student', '10', '2026-06-25 18:24:08', NULL, 0, '2010-10-15', 'Male', 15, 'KABANKALAN, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Sitio Tap-ok, Brgy.Enclaro, Binalbagan, Negros Occidental', '09564091920', NULL, NULL, NULL, 'Lyan Grace Esmores Gayaban', 'Housewife', '09564091920', NULL, NULL, NULL, 'KABANKALAN NATIONAL HIGH School', 'KABANKALAN, NEGROS OCCIDENTAL', '2025-2026', '87', 1, '116870160042', 0, 0, 0, NULL, 1, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-26 01:24:08', 18),
(370, 'STU-2026-0333', 'LAROYA, JOHN PRINCE GAMOZA', 'GAMOZA', 'johnprincelaroya@gmail.com', '$2y$10$pHBXHcEsMdch311PQ//V1ukO1xct8QAKbN3Y7ihgFZQqb.krScYdq', 'student', '11', '2026-06-25 18:48:15', NULL, 0, '2009-11-09', 'Male', 16, 'MANILA', 'Filipino', 'Catholic', 'Sitio Nabuswang, Brgy. Canmoros, Negros Occidental', '09319156596', 'Jongie Aliyaga Laroya', 'Fisherman', NULL, 'Rodelyn Gamoza Laroya', 'Housewife', NULL, 'Ariane Bayotas Laroya', '09319156596', 'Aunt', 'TLCA', 'Prk. Aguihis, Brgy. Canmoros. Binalbagan, Negros Occidental', '2025-2026', '86', 1, '136441141303', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-26 01:48:15', 18),
(371, 'STU-2026-0334', 'POSEO, RENZ GAMPOSILAO', 'GAMPOSILAO', 'renzposeo@gmail.com', '$2y$10$2J9W.hMlQn0gmLQ2NCLBNOw4h/FwgvJGHI2UNVx.Ah3j51RzW6OSm', 'student', '11', '2026-06-25 18:56:22', NULL, 0, '2008-09-30', NULL, 17, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Nylonshell, Brgy. Canmoros, Binalbagan, Negros Occidental', '09109715670', 'Rey Mocero Poseo', 'Fisherman', NULL, 'Narlie Galiac Gamposilao', 'Housewife', NULL, 'Pearl Joy Gamposilao Poseo', '09109715670', 'Sister', 'TLCA', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '2025-2026', NULL, 1, '116868120086', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-26 01:56:22', 18),
(372, 'STU-2026-0335', 'MADALAG, RHIANNA MUALLA', 'MUALLA', 'rhiannamadalag@gmail.com', '$2y$10$btvgsgsqHd1ppvEtdGEhqehRWW1Qa6xyurL1b9mKVh3cmExcdF6k6', 'student', '11', '2026-06-28 18:17:19', NULL, 0, '2009-12-03', 'Female', 16, 'KABANKALAN, NEGROS OCCIDENTAL', 'Filipino', 'Catholic', 'Akina Subd., Brgy. Balicotoc, Kabankalan City, Negros Occident6al', '09203450681', NULL, NULL, NULL, 'Daisy Mualla Madalag', 'Housewife', '09203450681', 'Ruby Mualla Madalag', '09464228015', 'Sister', 'BNHS', 'Binalbagan, Negrtos Occidental.', '2025-2026', NULL, 1, NULL, 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-29 01:17:19', 18),
(373, 'STU-2026-0336', 'EMPERADO, MA. ROWELA BUT-AY', 'BUT-AY', 'marowelaemperado@gmail.com', '$2y$10$cclDE8Hos/KfPlDen4rTj.5RHMyrnbKc2HJNLtIQnXxt7yO0BQYDS', 'student', '10', '2026-06-28 18:24:12', NULL, 0, '2009-11-09', NULL, 16, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Sitio Omot, Brgy. Santol, Binalbagan, Negros Occidental', '09162776580', 'Efren Emperado', 'Laborer', NULL, 'Merlyn But-ay Emperado', 'Housewife', '09162776580', NULL, NULL, NULL, 'BNHS-Santol Ext.', 'Brgy. Santol, Binalbagan, Negros Occidental', '2025-2026', NULL, 1, NULL, 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'promoted', '', '', '2026-06-29 01:24:12', 18),
(374, 'STU-2026-0337', 'JOCSON, DHANDREX VILLANOS', 'VILLANOS', 'dhandrexjocson@gmail.com', '$2y$10$gjcUkMLJNEwRWnO4PhFs/ujWp840vpkAH2ZphTAQG8L23IRUrEory', 'student', '8', '2026-06-28 20:07:43', NULL, 0, '2011-11-26', 'Male', 14, 'Binalbagan, Negros Occidental', 'Filipino', 'Catholic', 'Prk. Aguihis, Brgy. Canmoros, Binalbagan, Negros Occidental', '09942851924', 'Danilo Flores Jocson', 'Laborer', NULL, 'Dexie Villanos', 'Housewife', '09942851924', 'Susie Atienza', '09942851924', 'Aunt', 'Andres A. Nocon National High School', 'Caballero St., Buenavista II, General Trias City, Cavite', '2025-2026', '73', 1, '107966170190', 0, 0, 0, NULL, 0, 0, NULL, 'Hiligaynon', 'retained', 'Academic performance', '2025-2026', '2026-06-29 03:07:43', 18);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `activity_submissions`
--
ALTER TABLE `activity_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_id` (`activity_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `book_borrowings`
--
ALTER TABLE `book_borrowings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `clinic_visits`
--
ALTER TABLE `clinic_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `attended_by` (`attended_by`);

--
-- Indexes for table `employee_201_files`
--
ALTER TABLE `employee_201_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `employee_education`
--
ALTER TABLE `employee_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee_work_experience`
--
ALTER TABLE `employee_work_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject_enrollment` (`student_id`,`subject_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_subject_id` (`subject_id`);

--
-- Indexes for table `fee_types`
--
ALTER TABLE `fee_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `folder_teacher`
--
ALTER TABLE `folder_teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_teacher_empidno` (`teacher_empidno`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject` (`student_id`,`subject_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_quarterly_grades` (`student_id`,`subject_id`);

--
-- Indexes for table `grade_periods`
--
ALTER TABLE `grade_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_quarter` (`quarter`);

--
-- Indexes for table `immunization_records`
--
ALTER TABLE `immunization_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_fee_id` (`student_fee_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subject_id` (`subject_id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempt_id` (`attempt_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_id` (`quiz_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_id` (`quiz_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`date`,`subject_id`,`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `marked_by` (`marked_by`);

--
-- Indexes for table `student_enrollment_documents`
--
ALTER TABLE `student_enrollment_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_type_id` (`fee_type_id`);

--
-- Indexes for table `student_medical_profiles`
--
ALTER TABLE `student_medical_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_enrollment_key` (`enrollment_key`),
  ADD KEY `idx_grade_level` (`grade_level`),
  ADD KEY `idx_grade_archived` (`grade_level`,`archived`);

--
-- Indexes for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_teacher_date` (`teacher_id`,`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empidno` (`empidno`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_empidno` (`empidno`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_retention_status` (`retention_status`),
  ADD KEY `idx_retention_school_year` (`retention_school_year`),
  ADD KEY `idx_retention_updated_by` (`retention_updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `activity_submissions`
--
ALTER TABLE `activity_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=663;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `book_borrowings`
--
ALTER TABLE `book_borrowings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clinic_visits`
--
ALTER TABLE `clinic_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_201_files`
--
ALTER TABLE `employee_201_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_education`
--
ALTER TABLE `employee_education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_work_experience`
--
ALTER TABLE `employee_work_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `fee_types`
--
ALTER TABLE `fee_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `folder_teacher`
--
ALTER TABLE `folder_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grade_periods`
--
ALTER TABLE `grade_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `immunization_records`
--
ALTER TABLE `immunization_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_enrollment_documents`
--
ALTER TABLE `student_enrollment_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=461;

--
-- AUTO_INCREMENT for table `student_medical_profiles`
--
ALTER TABLE `student_medical_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_retention_updated_by` FOREIGN KEY (`retention_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
