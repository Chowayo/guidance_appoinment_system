-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 07:48 AM
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
-- Database: `guidance_appointment`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `urgency_level` enum('Low','Medium','High') DEFAULT 'Low',
  `confirmation_email` varchar(100) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','declined','rescheduled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `student_id`, `counselor_id`, `purpose`, `urgency_level`, `confirmation_email`, `date`, `time`, `reason`, `status`, `created_at`) VALUES
(17, 202460225, 20251, 'Academic concern', 'Low', 'chowxinnshaninlu@gmail.com', '2025-10-15', '09:23:00', 'sdfsdzxcd', 'rescheduled', '2025-10-14 11:23:20');

-- --------------------------------------------------------

--
-- Table structure for table `counselor`
--

CREATE TABLE `counselor` (
  `counselor_id` int(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `grade_level` enum('Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counselor`
--

INSERT INTO `counselor` (`counselor_id`, `first_name`, `last_name`, `grade_level`, `password`, `email`, `reset_token`, `reset_token_expiry`) VALUES
(20251, 'Chow', 'Lu', 'Grade 1', '$2y$10$XJRLhyHGwuyTRRJvgz8C5OC8XGz5DFHdiccKZ3R8Wi04ZsMaqLRsW', 'chowxinnshaninlu@gmail.com', '8bff6b877bba9e3ec45e0b55cec217592a334efe9f98dcc81516e20d433e89d1', '2025-10-15 10:16:16'),
(20252, 'shanin', 'gonzales', 'Grade 2', '$2y$10$agXzYr0C8E4j5O8H8RdY2el3yzZdPFkFczfOOmwutPdgUw5X15Lw2', 'chowxinnlu@gmail.com', 'f4c543ce36495a6202259d29f9872cef83266efd71f8c8fec1dd2774e8abc4a9', '2025-10-15 14:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `counselor_availability`
--

CREATE TABLE `counselor_availability` (
  `id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counselor_availability`
--

INSERT INTO `counselor_availability` (`id`, `counselor_id`, `available_date`, `start_time`, `end_time`, `is_available`) VALUES
(76, 20251, '2025-10-15', '08:00:00', '17:00:00', 1),
(77, 20251, '2025-10-16', '08:00:00', '17:00:00', 1),
(78, 20251, '2025-10-17', '08:00:00', '17:00:00', 1),
(79, 20251, '2025-10-20', '08:00:00', '17:00:00', 1),
(80, 20251, '2025-10-21', '08:00:00', '17:00:00', 1),
(81, 20251, '2025-10-22', '08:00:00', '17:00:00', 1),
(82, 20251, '2025-10-23', '08:00:00', '17:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `grade_level` enum('Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12') NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `first_name`, `last_name`, `email`, `grade_level`, `password`, `verification_token`, `token_expiry`, `is_verified`, `verified_at`, `reset_token`, `reset_token_expiry`) VALUES
(202460225, 'Shanin', 'Lu', 'chowxinnlu@gmail.com', 'Grade 1', '$2y$10$D9.xR76MWi3gjsacw4jpTOgLZmDSJQphdAtklqAoKUG6HofR42hEq', NULL, NULL, 1, '2025-10-07 11:00:18', '22ace119312383f65ad9655ba9cb9c3d3dcc9d134c565f0862d4d9e835f94313', '2025-10-16 09:16:29'),
(202560224, 'Chow', 'Lu', 'chowxinnlu30@gmail.com', 'Grade 2', '$2y$10$pxPQIPpal.trOefQr9e.tewY3OR4x.uDavI3umzPGGkcx79j7swgO', NULL, NULL, 1, '2025-10-15 10:07:56', '847c0f4a3e2fcd767179925e88859f6c8f04961f57cb008aae0bb05b331d78a7', '2025-10-15 14:05:26'),
(202560225, 'chowxinn', 'lu', 'chowxinnlu2002@gmail.com', 'Grade 1', '$2y$10$5Tcd94TbbTML7KN9TyS1meQ7HJDdbfYRzgzGrVS2pb0A9kZO7XmKu', NULL, NULL, 1, '2025-10-15 10:36:32', 'e4957d025a1097eb4ffb882f7703e2ad3f4feb45fa827ba6b621dc9ff2a533fe', '2025-10-15 13:18:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `counselor_id` (`counselor_id`);

--
-- Indexes for table `counselor`
--
ALTER TABLE `counselor`
  ADD PRIMARY KEY (`counselor_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `counselor_availability`
--
ALTER TABLE `counselor_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `counselor_availability`
--
ALTER TABLE `counselor_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`counselor_id`) REFERENCES `counselor` (`counselor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
