-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2026 at 08:48 PM
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
-- Database: `blackboard_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `attachment_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignment_id`, `course_id`, `created_by`, `title`, `description`, `due_date`, `created_at`, `attachment_url`) VALUES
(5, 16, 37, 'binary', 'small test', '2026-01-02 14:14:00', '2026-01-01 14:15:05', 'uploads/assignments/assignment_5_1767377003_Screenshot_2025-10-22_093428.png'),
(6, 9, 2, 'idk', 'rdddgr', '2026-01-22 16:36:00', '2026-01-01 16:36:39', NULL),
(9, 10, 37, 'TEST', 'AGAIN', '2026-01-23 20:32:00', '2026-01-02 20:32:58', 'uploads/assignments/assignment_9_1767378778_csci410_sample_final.pdf'),
(10, 10, 37, 'Routing and Switching', 'testing', '2026-01-15 21:59:00', '2026-01-02 21:59:06', 'uploads/assignments/assignment_10_1767383946_Screenshot_2025-10-22_093258.png'),
(11, 21, 37, 'AI module', 'yolo', '2026-01-09 08:34:00', '2026-01-08 08:34:59', 'uploads/assignments/assignment_11_1767854099_Sample_Final_Report.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `conversation_id` int(11) NOT NULL,
  `type` enum('one_to_one','group','course') DEFAULT 'one_to_one',
  `title` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation`
--

INSERT INTO `conversation` (`conversation_id`, `type`, `title`, `created_at`) VALUES
(13, 'one_to_one', 'Chat Instructor-Student', '2025-12-31 22:49:10'),
(16, 'one_to_one', 'Chat Student-User', '2026-01-01 11:17:54'),
(17, 'one_to_one', 'Chat Student-User', '2026-01-01 11:18:04'),
(19, 'one_to_one', 'Chat Student-User', '2026-01-07 20:52:35'),
(20, 'one_to_one', 'Chat Admin-3 User-4', '2026-01-08 07:16:16'),
(21, 'one_to_one', 'Chat Admin-3 User-5', '2026-01-08 08:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_member`
--

CREATE TABLE `conversation_member` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_member`
--

INSERT INTO `conversation_member` (`conversation_id`, `user_id`, `joined_at`) VALUES
(13, 3, '2025-12-31 22:49:10'),
(13, 37, '2025-12-31 22:49:10'),
(16, 37, '2026-01-01 11:17:54'),
(17, 3, '2026-01-01 11:18:04'),
(19, 4, '2026-01-07 20:52:35'),
(19, 37, '2026-01-07 20:52:35'),
(20, 3, '2026-01-08 07:16:16'),
(20, 4, '2026-01-08 07:16:16'),
(21, 3, '2026-01-08 08:25:07'),
(21, 5, '2026-01-08 08:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `code`, `title`, `description`, `semester_id`, `created_by`, `created_at`) VALUES
(2, 'CS101', 'Test Course', 'Test course for chat permission', 1, 2, '2025-12-17 14:10:46'),
(3, 'CS210', 'Database Systems', 'SQL + ERD + Normalization', 1, 2, '2025-12-25 11:41:01'),
(4, 'CS301-Updated', 'Web Dev Advanced', 'Updated description', 1, 2, '2025-12-25 16:18:43'),
(9, 'CSCI800', 'NAnoTech', 'dk', 1, 2, '2025-12-26 19:01:44'),
(10, 'CSCI400', 'Network', 'cisco networking', 1, 37, '2025-12-26 19:40:20'),
(12, 'cs', 'trjr', 'jftgn', 1, 2, '2025-12-27 12:03:42'),
(14, 'CSCI300', 'oop', 'java', 1, 37, '2025-12-28 15:50:58'),
(15, 'CSCI270', 'Numerical', 'math', 1, 37, '2025-12-28 20:37:27'),
(16, '2', 'CSCI90', 'overview', 1, 37, '2026-01-01 14:14:27'),
(17, 'csci500', 'jff', 'huyo', 1, 37, '2026-01-04 14:00:46'),
(19, '1', 'CSCI400', 'Data Structure', 1, 37, '2026-01-04 22:37:06'),
(20, 'CSCI399', 'Numerical methods', 'Mathmatics', 2, 37, '2026-01-07 20:53:53'),
(21, '70', 'deep learning', 'ai', 1, 37, '2026-01-08 08:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `course_material`
--

CREATE TABLE `course_material` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `name`) VALUES
(3, 'Arts'),
(1, 'Business'),
(2, 'Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `enrollment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `role_in_course` enum('student','TA','auditor') DEFAULT 'student',
  `status` varchar(50) DEFAULT 'active',
  `grade` float DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment`
--

INSERT INTO `enrollment` (`enrollment_id`, `course_id`, `student_id`, `role_in_course`, `status`, `grade`, `created_at`) VALUES
(2, 10, 38, 'student', 'active', NULL, '2025-12-31 23:11:05'),
(10, 3, 4, 'student', 'active', NULL, '2026-01-06 14:28:20'),
(11, 4, 4, 'student', 'active', NULL, '2026-01-06 14:32:36'),
(12, 14, 4, 'student', 'active', NULL, '2026-01-06 14:39:44'),
(13, 15, 4, 'student', 'active', NULL, '2026-01-06 14:48:34'),
(14, 19, 4, 'student', 'active', NULL, '2026-01-06 14:49:53'),
(15, 17, 4, 'student', 'active', 90, '2026-01-06 14:50:03'),
(16, 9, 4, 'student', 'active', NULL, '2026-01-06 14:55:45'),
(17, 16, 4, 'student', 'active', NULL, '2026-01-06 15:09:13'),
(18, 12, 4, 'student', 'active', NULL, '2026-01-07 20:49:53'),
(19, 2, 4, 'student', 'active', NULL, '2026-01-07 20:50:02'),
(20, 20, 4, 'student', 'active', 90, '2026-01-07 20:54:16'),
(21, 21, 4, 'student', 'active', NULL, '2026-01-08 08:41:15'),
(22, 21, 5, 'student', 'active', NULL, '2026-01-08 08:43:10');

-- --------------------------------------------------------

--
-- Table structure for table `exam`
--

CREATE TABLE `exam` (
  `exam_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` enum('quiz','midterm','final') DEFAULT 'final',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `total_marks` float DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam`
--

INSERT INTO `exam` (`exam_id`, `course_id`, `created_by`, `title`, `type`, `start_time`, `end_time`, `total_marks`, `created_at`) VALUES
(5, 10, 37, 'FINAL', 'final', '2026-01-30 16:33:00', '2026-01-31 16:33:00', 100, '2026-01-02 16:33:53'),
(7, 10, 37, 'Security (Room342)', 'midterm', '2026-01-02 22:17:00', '2026-01-02 22:18:00', 50, '2026-01-02 22:16:05'),
(8, 10, 37, 'SNOOPING', 'final', '2026-01-02 23:10:00', '2026-01-02 23:11:00', 222, '2026-01-02 23:09:15'),
(11, 15, 37, 'gussian', 'midterm', '2026-01-06 19:27:00', '2026-01-06 19:28:00', 9, '2026-01-06 19:26:40'),
(12, 17, 37, 'testing', 'quiz', '2026-01-06 19:27:00', '2026-01-06 19:28:00', 88, '2026-01-06 19:27:01'),
(13, 15, 37, 'xzx', 'quiz', '2026-01-07 21:55:00', '2026-01-07 12:55:00', 90, '2026-01-07 20:55:47'),
(14, 21, 37, 'ai', 'midterm', '2026-01-08 08:44:00', '2026-01-08 08:45:00', 20, '2026-01-08 08:44:25');

-- --------------------------------------------------------

--
-- Table structure for table `exam_petition`
--

CREATE TABLE `exam_petition` (
  `petition_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `admin_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_petition`
--

INSERT INTO `exam_petition` (`petition_id`, `exam_id`, `student_id`, `reason`, `status`, `created_at`, `admin_comment`) VALUES
(7, 11, 4, 'sickness', 'approved', '2026-01-06 19:28:45', 'Approved by admin'),
(8, 12, 4, 'accident', 'rejected', '2026-01-06 19:38:50', 'Rejected by admin'),
(9, 14, 5, 'sick', 'approved', '2026-01-08 08:45:51', 'Approved by admin');

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedule`
--

CREATE TABLE `exam_schedule` (
  `exam_schedule_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `major`
--

CREATE TABLE `major` (
  `major_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `major`
--

INSERT INTO `major` (`major_id`, `department_id`, `name`, `created_at`) VALUES
(1, 1, 'Business Administration', '2025-12-22 19:57:14'),
(2, 1, 'Accounting', '2025-12-22 19:57:14'),
(3, 1, 'Finance', '2025-12-22 19:57:14'),
(4, 1, 'Marketing', '2025-12-22 19:57:14'),
(5, 1, 'Management', '2025-12-22 19:57:14'),
(6, 2, 'Computer Engineering', '2025-12-22 19:57:14'),
(7, 2, 'Electrical Engineering', '2025-12-22 19:57:14'),
(8, 2, 'Mechanical Engineering', '2025-12-22 19:57:14'),
(9, 2, 'Civil Engineering', '2025-12-22 19:57:14'),
(10, 2, 'Software Engineering', '2025-12-22 19:57:14'),
(11, 3, 'Graphic Design', '2025-12-22 19:57:14'),
(12, 3, 'Fine Arts', '2025-12-22 19:57:14'),
(13, 3, 'Music', '2025-12-22 19:57:14'),
(14, 3, 'Theater', '2025-12-22 19:57:14'),
(15, 3, 'Media Studies', '2025-12-22 19:57:14');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `message_type` enum('text','file','system') DEFAULT 'text',
  `sent_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `conversation_id`, `sender_id`, `body`, `message_type`, `sent_at`, `is_read`) VALUES
(71, 17, NULL, 'hiii admin hw r u', 'text', '2026-01-01 11:18:15', 1),
(72, 16, NULL, 'hii hasan hw r u', 'text', '2026-01-01 11:18:30', 1),
(73, 16, 37, 'hii std fine', 'text', '2026-01-01 11:18:53', 1),
(74, 13, 37, 'hii admin', 'text', '2026-01-01 11:19:01', 1),
(75, 17, 3, 'good', 'text', '2026-01-01 11:19:10', 1),
(76, 13, 3, 'great', 'text', '2026-01-01 11:19:16', 1),
(77, 17, NULL, '/blackboard-backend/uploads/chat/chat_6957e6962cbeb6.89335934.png', 'file', '2026-01-02 17:39:02', 1),
(78, 13, 37, 'h', 'text', '2026-01-02 19:03:57', 1),
(81, 19, 4, 'Hi', 'text', '2026-01-07 20:57:34', 1),
(82, 13, 3, 'helo instructor', 'text', '2026-01-08 08:24:20', 1),
(83, 13, 3, '/blackboard-backend/uploads/chat/chat_695f4d99ae69b6.65672555.png', 'file', '2026-01-08 08:24:25', 1),
(84, 20, 3, 'helo student', 'text', '2026-01-08 08:24:34', 0),
(85, 20, 3, '/blackboard-backend/uploads/chat/chat_695f4db5bbebb3.44788676.pdf', 'file', '2026-01-08 08:24:53', 0),
(86, 21, 3, 'helo ali', 'text', '2026-01-08 08:25:15', 0),
(87, 21, 3, '/blackboard-backend/uploads/chat/chat_695f4dd10c2509.80572927.pdf', 'file', '2026-01-08 08:25:21', 0),
(88, 19, 37, 'helo from hasan', 'text', '2026-01-08 08:26:23', 0),
(89, 19, 37, '/blackboard-backend/uploads/chat/chat_695f4e1593eb71.37619519.pdf', 'file', '2026-01-08 08:26:29', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `recipient_id`, `sender_id`, `title`, `message`, `created_at`, `is_read`) VALUES
(4, 38, 37, 'Test Course Notification', 'This notification should go to all enrolled students.', '2026-01-01 19:28:40', 0),
(8, 4, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 1),
(9, 5, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 1),
(10, 12, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(11, 13, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(12, 18, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(13, 22, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(14, 23, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(15, 24, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(16, 25, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(17, 27, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(18, 29, 3, 'Department Announcement', 'This is a department-wide notification from admin.', '2026-01-01 19:35:01', 0),
(82, 2, 3, 'Instructor Dept Test', 'Testing instructor department notifications', '2026-01-01 21:34:41', 0),
(83, 36, 3, 'Instructor Dept Test', 'Testing instructor department notifications', '2026-01-01 21:34:41', 0),
(84, 37, 3, 'Instructor Dept Test', 'Testing instructor department notifications', '2026-01-01 21:34:41', 0),
(85, 38, 3, 'Instructor Dept Test', 'Testing instructor department notifications', '2026-01-01 21:34:41', 0),
(102, 2, 3, 'ey', 'ey', '2026-01-01 21:49:21', 0),
(103, 36, 3, 'ey', 'ey', '2026-01-01 21:49:21', 0),
(104, 37, 3, 'ey', 'ey', '2026-01-01 21:49:21', 0),
(105, 38, 3, 'ey', 'ey', '2026-01-01 21:49:21', 0),
(106, 38, 37, 'Course Notice Test', 'Hello class — this is a course notification test.', '2026-01-02 13:42:45', 0),
(108, 38, 37, 'LAST CHECK', 'CONGRATES', '2026-01-02 14:03:41', 0),
(110, 3, NULL, '📌 Exam Petition', 'Petition ID: 1\nExam ID: 7\nStudent ID: 1\nReason: I had internet issue', '2026-01-02 22:40:28', 0),
(112, 3, NULL, '📌 Exam Petition', 'Petition ID: 2\nExam: SNOOPING (ID: 8)\nStudent ID: 1\nReason: Testing title in notification', '2026-01-02 23:14:57', 0),
(114, 4, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 1),
(115, 5, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(116, 12, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(117, 13, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(118, 18, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(119, 22, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(120, 23, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(121, 24, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(122, 25, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(123, 27, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(124, 29, 3, 'hellooo', 'back to uni', '2026-01-06 15:07:03', 0),
(125, 3, 4, '📌 Exam Petition', 'Petition ID: 7\nExam ID: 11\nStudent ID: 4\nReason: sickness', '2026-01-06 19:28:45', 0),
(126, 3, 4, '📌 Exam Petition', 'Petition ID: 8\nExam ID: 12\nStudent ID: 4\nReason: accident', '2026-01-06 19:38:50', 0),
(127, 4, 3, '✅ Exam Petition Result', 'Your petition for Exam ID 11 was approved.\nAdmin Comment: Approved by admin', '2026-01-06 20:13:25', 1),
(128, 4, 3, '✅ Exam Petition Result', 'Your petition for Exam ID 12 was rejected.\nAdmin Comment: Rejected by admin', '2026-01-06 20:13:30', 1),
(129, 4, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(130, 5, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(131, 12, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(132, 13, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(133, 18, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(134, 22, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(135, 23, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(136, 24, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(137, 25, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(138, 27, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(139, 29, 3, 'Hello everyone', 'fall end in January 8', '2026-01-08 07:14:58', 0),
(140, 3, 5, '📌 Exam Petition', 'Petition ID: 9\nExam ID: 14\nStudent ID: 5\nReason: sick', '2026-01-08 08:45:51', 0),
(141, 5, 3, '✅ Exam Petition Result', 'Your petition for Exam ID 14 was approved.\nAdmin Comment: Approved by admin', '2026-01-08 08:46:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `office_hour_booking`
--

CREATE TABLE `office_hour_booking` (
  `booking_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('confirmed','cancelled') DEFAULT 'confirmed',
  `booked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_hour_booking`
--

INSERT INTO `office_hour_booking` (`booking_id`, `slot_id`, `student_id`, `status`, `booked_at`) VALUES
(2, 4, 4, 'confirmed', '2026-01-08 07:07:02'),
(3, 5, 5, 'confirmed', '2026-01-08 08:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `office_hour_slot`
--

CREATE TABLE `office_hour_slot` (
  `slot_id` int(11) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_hour_slot`
--

INSERT INTO `office_hour_slot` (`slot_id`, `instructor_id`, `course_id`, `start_time`, `end_time`, `location`, `capacity`, `created_at`) VALUES
(2, 37, 10, '2026-01-01 10:30:00', '2026-01-01 11:30:00', 'Room 301', 5, '2025-12-28 22:30:23'),
(4, 37, 15, '2026-01-09 07:05:00', '2026-01-09 08:05:00', 'Room 205E', 3, '2026-01-08 07:05:50'),
(5, 37, 21, '2026-01-08 08:00:00', '2026-01-09 08:48:00', 'Room 301', 5, '2026-01-08 08:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `semester_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`semester_id`, `name`, `start_date`, `end_date`, `is_active`) VALUES
(1, 'Fall 2025', '2025-09-01', '2025-12-31', 1),
(2, 'spring', '2026-03-04', '2026-04-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `session_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `host_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `join_url` varchar(255) DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`session_id`, `course_id`, `host_id`, `title`, `start_time`, `end_time`, `platform`, `join_url`, `room`, `created_at`) VALUES
(1, 2, 2, 'Intro to Web Dev', '2025-12-30 10:00:00', '2025-12-30 11:00:00', 'Zoom', 'https://zoom.us/j/999999999', NULL, '2025-12-27 13:43:54'),
(2, 3, 2, 'Intro to Web Dev', '2025-12-30 10:00:00', '2025-12-30 11:00:00', 'Zoom', 'https://zoom.us/j/999999999', NULL, '2025-12-27 13:46:23'),
(7, 14, 37, 'javaaaa', '2026-01-01 16:00:00', '2026-01-01 17:00:00', NULL, NULL, 'Room 301', '2025-12-28 16:02:37'),
(8, 10, 37, 'efa', '2026-01-20 16:46:00', '2026-01-12 16:46:00', NULL, NULL, 'Room5564', '2026-01-01 16:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `student_progress`
--

CREATE TABLE `student_progress` (
  `progress_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `gpa` float DEFAULT NULL,
  `completed_assignments` int(11) DEFAULT 0,
  `total_assignments` int(11) DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submission`
--

CREATE TABLE `submission` (
  `submission_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `grade` float DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission`
--

INSERT INTO `submission` (`submission_id`, `assignment_id`, `submitted_by`, `file_url`, `submitted_at`, `grade`, `feedback`) VALUES
(2, 9, NULL, 'uploads/submissions/submission_9_1_1767383771_csci410_sample_final.pdf', '2026-01-02 21:56:11', NULL, NULL),
(3, 10, NULL, 'uploads/submissions/submission_10_1_1767383997_network2_commands.pdf', '2026-01-02 21:59:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('student','instructor','admin') NOT NULL DEFAULT 'student',
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login_at` datetime DEFAULT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `department_id` int(11) NOT NULL,
  `major_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `created_at`, `last_login_at`, `status`, `department_id`, `major_id`) VALUES
(2, 'Test', 'Instructor', 'instructor@test.com', '$2y$10$edUkmOIG/PMO07WINTOF8uJUMsVTE0GR.YfkQIScMMWwU2hogofqq', 'instructor', '2025-12-07 18:37:05', NULL, 'active', 1, 1),
(3, 'Test', 'Admin', 'admin@test.com', '$2y$10$wM55VBsCVYjEDHKLhL1CEeohz6fK6bA9AUzci2bto0.RltjQDNCRS', 'admin', '2025-12-20 14:24:31', NULL, 'active', 1, 1),
(4, 'New', 'Student', 'newstudent@test.com', '$2y$10$P0DdtiOvVttSmOR5BixVs.wp1ujRyvfR8r3S.4TQh03bippWdMj6q', 'student', '2025-12-20 17:49:31', NULL, 'active', 1, 1),
(5, 'ali', 'hhh', 'ali@test.com', '$2y$10$/Guh/PXRIvjNaXWBM4qx/e92P4VzE45eeDq.7/8xLa3Oplc7Q6Hem', 'student', '2025-12-20 17:59:10', NULL, 'active', 1, NULL),
(6, 'mhmd', 'kh', 'mhmd@test.com', '$2y$10$9K58tFIBNdqzhUhA7qeLmeHPpy6tDXg9Ta5JqFZXxkCju3viNOVsG', 'admin', '2025-12-20 18:03:31', NULL, 'active', 1, NULL),
(11, 'hadi', 'alloush', 'hadi@test.com', '$2y$10$WtTrvG6aqx4Kh5AGFEyeS.fXwW8XHIfgIwCTFZqoTPnGvOG2t8.3a', 'admin', '2025-12-20 19:06:03', NULL, 'active', 1, NULL),
(12, 'hadil', 'alloush', 'hadil@test.com', '$2y$10$uRlizpyvoxsl3LJlmm/ZOOA7YDoDBbuflqWQyZWPmO8UsX9NtLjde', 'student', '2025-12-21 14:20:25', NULL, 'active', 1, NULL),
(13, 'abir', 'tr', 'abir@test.com', '$2y$10$NHw2JHlohiD6t99ANDpjeeGPg1femqX2G2HxZ.2B1JqxvbH8ilX1y', 'student', '2025-12-21 14:27:04', NULL, 'active', 1, NULL),
(14, 'John', 'Admin', 'john.admin@liu.edu.lb', '$2y$10$CDnG0O1SqeJClQL4cnVmruhiD7CKRaaPAOhMglFB8u0dtnTes9v0G', 'admin', '2025-12-21 15:35:08', NULL, 'active', 1, NULL),
(18, 'hello', 'h', 'hello.h@liu.edu.lb', '$2y$10$IMaM0Lsm/5TLuzlWATx/nu/RZgAxmgYbUkRm6XjWKccXaGr9Kttsq', 'student', '2025-12-21 16:03:16', NULL, 'active', 1, NULL),
(22, 'fat', 'tf', 'fat@liu.edu.lb', '$2y$10$5w/Hkt41.EJXAvDacWRpb.YsWUELMm/3zlHP8oMBRdpBsyhT5FNl.', 'student', '2025-12-21 19:47:12', NULL, 'active', 1, NULL),
(23, 'hbb', 'aa', 'hbb@liu.edu.lb', '$2y$10$M62q.Ny5olAsa6HPKB/uMOpOK.5uLHo65BETMeEYwJxneYQULcure', 'student', '2025-12-21 19:52:13', NULL, 'active', 1, NULL),
(24, 'Ali', 'Haddad', 'ali.haddad@student.liu.edu.lb', '$2y$10$yAAA3tS7GAFhtXTOQoyrR.TCSkoR3r7jPkoDTj3TTkNcUTNP9T1/2', 'student', '2025-12-22 20:44:03', NULL, 'active', 1, 2),
(25, 'Test', 'Student', 'teststudent001@student.liu.edu.lb', '$2y$10$tr040.1rZqYbRdQIyZJsJ.YqDlZejRQfoNzPsk8sS2r9tOHIsGe1K', 'student', '2025-12-22 21:48:59', NULL, 'active', 1, 2),
(26, 'ahmad', 'alloush', 'ahmad@liu.edu.lb', '$2y$10$MieJztmkEzAAMri4elKwDeYMnjhIz.aU6mBcOWCnPd8WxXLqExj9y', 'student', '2025-12-22 22:02:56', NULL, 'active', 2, 10),
(27, 'Ali', 'Haddad', 'ali.haddad2@student.liu.edu.lb', '$2y$10$fUqhfHk/eaHWjLrKytX5x.CrXuOJR/miNGJ2Mg8OnVZ1Fbai0LY72', 'student', '2025-12-22 22:08:22', NULL, 'active', 1, 2),
(28, 'abbas', 'alloush', 'abbas@liu.edu.lb', '$2y$10$nST3yzlKL6kK2sakdipia.89PDgZrelLBCi6lkjn9TE61LaJb3aay', 'student', '2025-12-22 22:29:06', NULL, 'active', 3, 12),
(29, 'Sara', 'Khalil', 'sara.khalil@student.liu.edu.lb', '$2y$10$CtGtOEFeFmJI3mRJV8JcTO5Ezn9vbg4cVju6EEkI/d1WPH98DTVNS', 'student', '2025-12-22 22:34:01', NULL, 'active', 1, 2),
(30, 'yaakoub', 'khalil', 'yaakoub@liu.edu.lb', '$2y$10$QSfZuVoi4R3SzawnRPJHoupu4f5WibQBjzGcKJO2aoNr4IyTz.6Xm', 'student', '2025-12-24 15:37:15', NULL, 'active', 2, 6),
(34, 'Test', 'Instructor2', 'test_instructor2@example.com', '$2y$10$e0NRKXlVxk5aRqY6lW7qUu7cG8FqYxN7uBfMzo0y7R5p0n5T6b1xO', 'instructor', '2025-12-25 19:10:05', NULL, 'active', 2, NULL),
(36, 'Test', 'Instructor', 'testinstructor@example.com', '$2y$10$WjF6SGyFYx6dTfqt9.Q55OlEG5Wb7v3StLUtbVoTPof1ie1j0wSca', 'instructor', '2025-12-25 21:43:28', NULL, 'active', 1, NULL),
(37, 'hasan', 'ib', 'hasan.ib@liu.edu.lb', '$2y$10$LiwTIKC2d4ZrjhKqYfzZ5u2UT6KNcLgYmcAc2RdzoH5p8aKLTC3Hu', 'instructor', '2025-12-26 12:34:13', NULL, 'active', 1, 1),
(38, 'mousa', 'kar', 'mousa.kar@liu.edu.lb', '$2y$10$Rxoj09kFf54vB1tCxH2U4u2h70VYEIEIotC8dhKNctrE/6liJZMnq', 'instructor', '2025-12-27 13:51:30', NULL, 'active', 1, 1),
(39, 'mar', 'kh', 'mar@test.con', '$2y$10$E4Vp/wnDRXK8.ETaP2VHueyELX8EeoS.3pD24N79t9tBRW8B83I6S', 'student', '2025-12-31 14:43:30', NULL, 'active', 3, 14);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `fk_assignment_creator` (`created_by`),
  ADD KEY `idx_assignment_course` (`course_id`);

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `idx_conv_created_at` (`created_at`);

--
-- Indexes for table `conversation_member`
--
ALTER TABLE `conversation_member`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `fk_conv_member_user` (`user_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `fk_course_creator` (`created_by`),
  ADD KEY `idx_course_semester` (`semester_id`);

--
-- Indexes for table `course_material`
--
ALTER TABLE `course_material`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `fk_material_course` (`course_id`),
  ADD KEY `fk_material_uploader` (`uploaded_by`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `fk_enroll_course` (`course_id`),
  ADD KEY `fk_enroll_student` (`student_id`);

--
-- Indexes for table `exam`
--
ALTER TABLE `exam`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `fk_exam_creator` (`created_by`),
  ADD KEY `idx_exam_course` (`course_id`);

--
-- Indexes for table `exam_petition`
--
ALTER TABLE `exam_petition`
  ADD PRIMARY KEY (`petition_id`),
  ADD KEY `fk_petition_exam` (`exam_id`),
  ADD KEY `fk_petition_student` (`student_id`);

--
-- Indexes for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD PRIMARY KEY (`exam_schedule_id`),
  ADD KEY `fk_exam_schedule_exam` (`exam_id`);

--
-- Indexes for table `major`
--
ALTER TABLE `major`
  ADD PRIMARY KEY (`major_id`),
  ADD UNIQUE KEY `unique_major_per_department` (`department_id`,`name`),
  ADD KEY `fk_major_department` (`department_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_message_conv` (`conversation_id`),
  ADD KEY `fk_message_sender` (`sender_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notification_recipient` (`recipient_id`),
  ADD KEY `fk_notification_sender` (`sender_id`);

--
-- Indexes for table `office_hour_booking`
--
ALTER TABLE `office_hour_booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking_slot` (`slot_id`),
  ADD KEY `fk_booking_student` (`student_id`);

--
-- Indexes for table `office_hour_slot`
--
ALTER TABLE `office_hour_slot`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `fk_office_course` (`course_id`),
  ADD KEY `idx_office_slot_instructor` (`instructor_id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `fk_session_host` (`host_id`),
  ADD KEY `idx_session_course` (`course_id`);

--
-- Indexes for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `fk_progress_student` (`student_id`),
  ADD KEY `fk_progress_course` (`course_id`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `fk_submission_user` (`submitted_by`),
  ADD KEY `idx_submission_assignment` (`assignment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_department` (`department_id`),
  ADD KEY `fk_users_major` (`major_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `course_material`
--
ALTER TABLE `course_material`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `exam`
--
ALTER TABLE `exam`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `exam_petition`
--
ALTER TABLE `exam_petition`
  MODIFY `petition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  MODIFY `exam_schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `major`
--
ALTER TABLE `major`
  MODIFY `major_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `office_hour_booking`
--
ALTER TABLE `office_hour_booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `office_hour_slot`
--
ALTER TABLE `office_hour_slot`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_progress`
--
ALTER TABLE `student_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission`
--
ALTER TABLE `submission`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `fk_assignment_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `conversation_member`
--
ALTER TABLE `conversation_member`
  ADD CONSTRAINT `fk_conv_member_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conv_member_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_course_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_course_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON DELETE SET NULL;

--
-- Constraints for table `course_material`
--
ALTER TABLE `course_material`
  ADD CONSTRAINT `fk_material_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_material_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam`
--
ALTER TABLE `exam`
  ADD CONSTRAINT `fk_exam_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `exam_petition`
--
ALTER TABLE `exam_petition`
  ADD CONSTRAINT `fk_petition_exam` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_petition_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_schedule`
--
ALTER TABLE `exam_schedule`
  ADD CONSTRAINT `fk_exam_schedule_exam` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `major`
--
ALTER TABLE `major`
  ADD CONSTRAINT `fk_major_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `office_hour_booking`
--
ALTER TABLE `office_hour_booking`
  ADD CONSTRAINT `fk_booking_slot` FOREIGN KEY (`slot_id`) REFERENCES `office_hour_slot` (`slot_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `office_hour_slot`
--
ALTER TABLE `office_hour_slot`
  ADD CONSTRAINT `fk_office_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_office_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `fk_session_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_session_host` FOREIGN KEY (`host_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD CONSTRAINT `fk_progress_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progress_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `fk_submission_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignment` (`assignment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_submission_user` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`),
  ADD CONSTRAINT `fk_user_major` FOREIGN KEY (`major_id`) REFERENCES `major` (`major_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_major` FOREIGN KEY (`major_id`) REFERENCES `major` (`major_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
