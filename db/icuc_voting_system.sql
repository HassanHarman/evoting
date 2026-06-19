-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 10:14 PM
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
-- Database: `icuc_voting_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit`
--

CREATE TABLE `admin_audit` (
  `id` int(11) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_table` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `action_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_audit`
--

INSERT INTO `admin_audit` (`id`, `admin_username`, `action_type`, `target_table`, `target_id`, `old_value`, `new_value`, `ip_address`, `action_timestamp`) VALUES
(1, 'ops@afrisoko.test', 'FAILED_LOGIN', 'students', 0, '', '::1', '::1', '2026-05-21 16:43:13'),
(2, 'ICU/2024/001', 'FAILED_LOGIN', 'students', 0, '', '::1', '::1', '2026-05-21 17:03:47'),
(3, 'nasser@aedis-hub.umsc.or.ug', 'FAILED_LOGIN', 'students', 0, '', '::1', '::1', '2026-06-06 20:11:51');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('chairperson','it_admin','returning_officer','clerk') DEFAULT 'clerk',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'ec_chairperson', 'chairperson@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. James Mwangi', 'chairperson', 1, NULL, '2026-05-21 17:23:30'),
(2, 'it_admin', 'it@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter Okello', 'it_admin', 1, NULL, '2026-05-21 17:23:30'),
(3, 'returning_officer', 'ro@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Nambi', 'returning_officer', 1, NULL, '2026-05-21 17:23:30'),
(4, 'clerk1', 'clerk1@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Clerk One', 'clerk', 1, NULL, '2026-05-21 17:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `campaign_agents`
--

CREATE TABLE `campaign_agents` (
  `id` int(11) NOT NULL,
  `agent_code` varchar(20) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaign_agents`
--

INSERT INTO `campaign_agents` (`id`, `agent_code`, `candidate_id`, `full_name`, `email`, `phone`, `password`, `last_login`, `is_active`, `created_at`) VALUES
(1, 'AGT001', 1, 'Maria Agent', 'agent1@icuc.ac.ug', '0700000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, '2026-05-21 17:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `registration_number` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `manifesto` text DEFAULT NULL,
  `slogan` varchar(200) DEFAULT NULL,
  `is_independent` tinyint(1) DEFAULT 0,
  `party_affiliation` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `category_id`, `full_name`, `registration_number`, `photo`, `manifesto`, `slogan`, `is_independent`, `party_affiliation`, `is_active`, `created_at`, `email`, `password`, `last_login`, `login_attempts`) VALUES
(1, 1, 'Aisha Mbabazi', '241-22301-9809', NULL, 'I will improve student welfare', NULL, 0, 'Unity Party', 1, '2026-05-07 06:15:45', 'alice.mbabazi@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 0),
(2, 1, 'Sharifah Kabali', '241-22323-3232', NULL, 'Fight for academic excellence', NULL, 0, 'Progress Alliance', 1, '2026-05-07 06:15:45', 'bob.musoke@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 0),
(3, 2, 'Sudaice Sonde', '241-22422-9874', NULL, 'Supporting student initiatives', NULL, 0, 'Unity Party', 1, '2026-05-07 06:15:45', 'carol.nambi@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 0),
(4, 3, 'Hassan Sennoga', '241-44909-3333', NULL, 'Transparent communication', NULL, 0, 'Independent', 1, '2026-05-07 06:15:45', 'david.ssemwanga@icuc.ac.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_votes_per_voter` int(11) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `max_votes_per_voter`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'President', 'Student Union President', 1, 1, 1, '2026-05-07 06:15:44'),
(2, 'Vice President', 'Student Union Vice President', 1, 2, 1, '2026-05-07 06:15:44'),
(3, 'General Secretary', 'Student Union General Secretary', 1, 3, 1, '2026-05-07 06:15:44'),
(4, 'Treasurer', 'Student Union Treasurer', 1, 4, 1, '2026-05-07 06:15:44'),
(5, 'Academic Affairs Director', 'Director for Academic Affairs', 1, 5, 1, '2026-05-07 06:15:44');

-- --------------------------------------------------------

--
-- Table structure for table `election_settings`
--

CREATE TABLE `election_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election_settings`
--

INSERT INTO `election_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'election_status', 'active', 'upcoming, active, closed', '2026-05-21 16:22:38'),
(2, 'election_start_date', '', 'When voting starts', '2026-05-07 06:15:44'),
(3, 'election_end_date', '', 'When voting ends', '2026-05-07 06:15:44'),
(4, 'require_faculty_restriction', 'false', 'Restrict voting to specific faculty', '2026-05-07 06:15:44'),
(5, 'allow_admin_deletion', 'false', 'Allow admins to delete votes', '2026-05-07 06:15:44'),
(6, 'max_login_attempts', '3', 'Max failed login attempts', '2026-05-07 06:15:44'),
(7, 'session_timeout_minutes', '30', 'Session timeout duration', '2026-05-07 06:15:44'),
(8, 'display_live_results', '0', 'Show live results to candidates (0=No, 1=Yes)', '2026-05-21 16:34:53'),
(9, 'results_published', '0', 'Whether results are published to public (0=No, 1=Yes)', '2026-05-21 16:34:53'),
(10, 'results_hash', '', 'SHA-256 hash for results verification', '2026-05-21 16:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `portal_access_log`
--

CREATE TABLE `portal_access_log` (
  `id` int(11) NOT NULL,
  `portal_type` enum('student','admin','candidate','agent') NOT NULL,
  `user_identifier` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `login_success` tinyint(1) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `year_of_study` int(11) DEFAULT NULL,
  `has_voted` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `registration_number`, `full_name`, `mobile_number`, `password`, `email`, `faculty`, `department`, `year_of_study`, `has_voted`, `is_active`, `created_at`, `updated_at`, `remember_token`) VALUES
(1, '241-22490-3333', 'Kayondo Ibrahim', '0771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Computing', 'Computer Science', 2, 0, 1, '2026-05-07 06:15:45', '2026-05-07 06:15:45', NULL),
(2, '241-44343-2312', 'Katasi Sumayya', '0782345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Business', 'Accounting', 3, 0, 1, '2026-05-07 06:15:45', '2026-05-07 06:15:45', NULL),
(3, '241-44232-0987', 'Musa Katungulu', '0753456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Engineering', 'Civil', 4, 0, 1, '2026-05-07 06:15:45', '2026-05-07 06:15:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voter_registration` varchar(20) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `voting_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote_completion`
--

CREATE TABLE `vote_completion` (
  `id` int(11) NOT NULL,
  `voter_registration` varchar(20) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_registration` (`registration_number`),
  ADD KEY `idx_token` (`session_token`);

--
-- Indexes for table `admin_audit`
--
ALTER TABLE `admin_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin` (`admin_username`),
  ADD KEY `idx_timestamp` (`action_timestamp`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `campaign_agents`
--
ALTER TABLE `campaign_agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agent_code` (`agent_code`),
  ADD KEY `idx_candidate` (`candidate_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `election_settings`
--
ALTER TABLE `election_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `portal_access_log`
--
ALTER TABLE `portal_access_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `idx_registration` (`registration_number`),
  ADD KEY `idx_voted` (`has_voted`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voting_token` (`voting_token`),
  ADD UNIQUE KEY `unique_vote_per_category` (`voter_registration`,`category_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_voter` (`voter_registration`),
  ADD KEY `idx_candidate` (`candidate_id`),
  ADD KEY `idx_token` (`voting_token`);

--
-- Indexes for table `vote_completion`
--
ALTER TABLE `vote_completion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verification_hash` (`verification_hash`),
  ADD UNIQUE KEY `unique_voter_completion` (`voter_registration`),
  ADD KEY `idx_completed` (`completed_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_sessions`
--
ALTER TABLE `active_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_audit`
--
ALTER TABLE `admin_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `campaign_agents`
--
ALTER TABLE `campaign_agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `election_settings`
--
ALTER TABLE `election_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `portal_access_log`
--
ALTER TABLE `portal_access_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vote_completion`
--
ALTER TABLE `vote_completion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaign_agents`
--
ALTER TABLE `campaign_agents`
  ADD CONSTRAINT `campaign_agents_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
