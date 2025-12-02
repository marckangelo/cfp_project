-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 03:58 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cfp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `role` enum('super','content','financial') NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `role`, `last_login`) VALUES
(10, 'super', '2025-11-27 16:47:33'),
(12, 'content', '2025-11-28 23:44:41'),
(13, 'financial', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_permission`
--

CREATE TABLE `admin_permission` (
  `admin_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `orcid` varchar(20) NOT NULL,
  `member_id` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `h_index` int(11) DEFAULT NULL,
  `total_downloads` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `author`
--

INSERT INTO `author` (`orcid`, `member_id`, `bio`, `specialization`, `h_index`, `total_downloads`) VALUES
('0000-0000-0000-0000', 1, 'Initial Member Bio', 'General', NULL, NULL),
('0000-0000-0000-0001', 2, NULL, NULL, NULL, NULL),
('0000-0000-0000-0002', 3, NULL, NULL, NULL, NULL),
('0000-0000-0000-0003', 4, 'Expert in AI and data structures.', 'Computer Science', NULL, NULL),
('0000-0000-0000-0004', 5, 'Researching marine biology ecosystems.', 'Biology', NULL, NULL),
('0000-0000-0000-0005', 6, 'Specialist in organic chemistry compounds.', 'Chemistry', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `charity`
--

CREATE TABLE `charity` (
  `charity_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `mission` varchar(255) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `total_received` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charity`
--

INSERT INTO `charity` (`charity_id`, `name`, `description`, `mission`, `country`, `registration_number`, `status`, `total_received`) VALUES
(1, 'Tech for Youth Canada', 'Providing laptops and coding lessons to underprivileged high school students.', 'To bridge the digital divide in urban communities.', 'Canada', '88888-1111-RR0001', 'active', 15120.50),
(2, 'Global Health Initiative', 'Delivering vaccines and medical supplies to remote regions.', 'Ensuring basic healthcare access for everyone.', 'Switzerland', 'CHE-123.456.789', 'active', 52300.00),
(3, 'Clean Oceans Project', 'Organizing volunteer beach cleanups and plastic recycling research.', 'A plastic-free ocean by 2050.', 'USA', '55-1234567', 'pending', 0.00),
(4, 'The Legacy Arts Fund', 'Preserving historical paintings from the 19th century.', 'Preserving history for future generations.', 'UK', '11223344', 'inactive', 1200.00),
(5, 'Open Access Research Foundation', 'Funding open-source scientific journals and database maintenance.', 'Making knowledge free and accessible to all.', 'Canada', '99999-2222-RR0001', 'active', 8500.75);

--
-- Triggers `charity`
--
DELIMITER $$
CREATE TRIGGER `charity_deletion_donation_cleanup` BEFORE DELETE ON `charity` FOR EACH ROW BEGIN
    DELETE
FROM
    donation
WHERE
    charity_id = OLD.charity_id ;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `text_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `rating` int(11) DEFAULT NULL,
  `status` enum('active','flagged','removed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `member_id`, `text_id`, `parent_comment_id`, `content`, `date`, `is_public`, `rating`, `status`) VALUES
(1, 3, 1, NULL, 'Very constructive and easy to follow!', '2025-11-27 00:00:00', 1, 4, 'active'),
(5, 2, 1, 1, 'Reply to [User2]: Thank you.', '2025-11-27 00:00:00', 1, NULL, 'active'),
(6, 2, 1, 1, 'Reply to [User2]: Thank you.', '2025-11-27 00:00:00', 1, NULL, 'active'),
(7, 2, 1, 1, 'Reply to [User2]: I appreciate your comment!', '2025-11-27 00:00:00', 1, NULL, 'active'),
(8, 2, 1, 1, 'Reply to [User2]: Means a lot! Thank you!', '2025-11-27 00:00:00', 1, NULL, 'active'),
(9, 11, 5, NULL, 'Excellent coverage on topic, the author demonstrates a deep understanding of the topic, while providing simple and easy to follow explanation with easy to visualize examples. ', '2025-11-30 00:00:00', 1, 5, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `committee`
--

CREATE TABLE `committee` (
  `committee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `purpose` text DEFAULT NULL,
  `scope` enum('plagiarism','content','finance','appeals') DEFAULT NULL,
  `formation_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `member_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `committee`
--

INSERT INTO `committee` (`committee_id`, `name`, `purpose`, `scope`, `formation_date`, `status`, `member_count`) VALUES
(1, 'Academic Integrity Board', 'To investigate and rule on reported cases of plagiarism in submitted texts.', 'plagiarism', '2025-01-10', 'active', 3),
(2, 'Scientific Content Review', 'Ensures all scientific papers meet the required rigorous standards before publication.', 'content', '2025-01-15', 'active', 5),
(3, 'Budget & Finance Oversight', 'Manages donations, operational costs, and funding allocation.', 'finance', '2025-02-01', 'active', 4),
(4, 'Dispute Resolution Tribunal', 'Handles appeals from authors regarding rejected manuscripts.', 'appeals', '2024-11-20', 'inactive', 0),
(5, 'Humanities Review Panel', 'Specialized review board for non-scientific, humanities-focused submissions.', 'content', '2025-03-05', 'active', 3);

-- --------------------------------------------------------

--
-- Table structure for table `committee_membership`
--

CREATE TABLE `committee_membership` (
  `membership_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `committee_id` int(11) NOT NULL,
  `join_date` date NOT NULL,
  `role` enum('chair','member','secretary') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `term_end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `committee_membership`
--

INSERT INTO `committee_membership` (`membership_id`, `member_id`, `committee_id`, `join_date`, `role`, `status`, `term_end_date`) VALUES
(1, 3, 1, '2025-11-27', 'chair', 'active', '2026-11-27');

-- --------------------------------------------------------

--
-- Table structure for table `donation`
--

CREATE TABLE `donation` (
  `donation_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `text_id` int(11) NOT NULL,
  `charity_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` datetime NOT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `charity_pct` int(11) NOT NULL,
  `cfp_pct` int(11) NOT NULL,
  `author_pct` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation`
--

INSERT INTO `donation` (`donation_id`, `member_id`, `text_id`, `charity_id`, `amount`, `date`, `currency`, `payment_method`, `transaction_id`, `charity_pct`, `cfp_pct`, `author_pct`) VALUES
(1, 3, 3, 1, 200.00, '2025-11-27 00:55:21', 'CAD', 'card', 'DONA-J9YU-Y3WJ-PESX', 60, 20, 20);

-- --------------------------------------------------------

--
-- Table structure for table `download`
--

CREATE TABLE `download` (
  `download_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `text_id` int(11) NOT NULL,
  `download_date` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `download`
--

INSERT INTO `download` (`download_id`, `member_id`, `text_id`, `download_date`, `ip_address`, `user_agent`, `country`) VALUES
(1, 3, 1, '2025-11-26 23:04:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Canada'),
(2, 2, 5, '2025-11-27 17:16:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Canada'),
(3, 2, 6, '2025-11-27 17:29:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Canada'),
(4, 11, 5, '2025-11-30 00:23:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Canada');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `member_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `organization` varchar(100) NOT NULL,
  `primary_email` varchar(100) NOT NULL,
  `recovery_email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `pseudonym` varchar(50) NOT NULL,
  `verification_matrix` varchar(255) DEFAULT NULL,
  `matrix_expiry_date` date DEFAULT NULL,
  `join_date` date NOT NULL,
  `status` enum('active','suspended','blacklisted') DEFAULT 'active',
  `download_limit` int(11) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `introduced_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`member_id`, `name`, `organization`, `primary_email`, `recovery_email`, `password_hash`, `pseudonym`, `verification_matrix`, `matrix_expiry_date`, `join_date`, `status`, `download_limit`, `street`, `city`, `state`, `country`, `postal_code`, `introduced_by`) VALUES
(1, 'John Doe', 'Initial Org', 'john@example.com', NULL, '123456', 'JohnD', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'User1', 'Test Org', 'user1@test.com', 'user1recovery@test.com', '$2y$10$EZvqPe.Pa/rDURKi5N.2SuWCsAfqCyzKbmIh5aixShgsUZLXs6byy', 'U1_Display', '6OGTH73L9X0Z41O6', '2025-12-27', '2025-11-27', 'active', 8, '123 Test St', 'Montreal', 'QC', 'Canada', 'H1H', 1),
(3, 'User2', 'Test Org', 'user2@test.com', 'user2recovery@test.com', '$2y$10$6Z8csJCEjC4ogc1oJR.1aOhk/aWrxoEhc/aFwZXnEL16kyrticxNW', 'U2_Display', 'FPJW1LD3VA0NTRJV', '2025-12-27', '2025-11-27', 'active', 9, '234 Test St', 'Montreal', 'QC', 'Canada', 'H1H', 1),
(4, 'Alice Author', 'Science Corp', 'alice@test.com', NULL, '123456', 'AliceA', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(5, 'Bob Biologist', 'Bio Labs', 'bob@test.com', NULL, '123456', 'BobB', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(6, 'Charlie Chemist', 'Chem Inc', 'charlie@test.com', NULL, '123456', 'CharlieC', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(7, 'David Reviewer', 'Review Org', 'david@test.com', NULL, '123456', 'DaveR', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(8, 'Eve Enthusiast', 'Uni of Montreal', 'eve@test.com', NULL, '123456', 'EveE', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(9, 'Frank Fan', 'Tech World', 'frank@test.com', NULL, '123456', 'FrankF', NULL, NULL, '2025-11-26', 'active', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(10, 'admin123', 'Admin', 'admin123@admin.com', 'admin123recovery@admin.com', '$2y$10$iwOIEaW9ponie1.5JpprwumxVO6Gr1YPINtwfp3xstfze/DnRNplG', 'admin123', '45UBGCR9A2FRY6KP', '2025-12-27', '2025-11-27', 'active', NULL, 'Admin St', 'Admin City', 'Admin', 'Canada', '0AD M1N', 1),
(11, 'Arshdeep Singh', 'Concordia University', 'arshdeep200423@gmail.com', 'email@email.com', '$2y$10$G5yEaLmbFG2o8dB3tBYmye9bqw7sJ.WSLaYncZTAJxh8vGOkhBU72', 'DeepDeep', 'XKABUD04YAEFO52Y', '2025-12-30', '2025-11-30', 'active', 0, '1560 Place Kennedy', 'Dorval', 'QC', 'Canada', 'H9P 1P9', 10),
(12, 'admin_content', 'Admin', 'adminContent@admin.com', 'adminContentRecovery@admin.com', '$2y$10$RiljOtjNyi5ziNbkulj.0eUDWYGQbX4CAAS2KDmuaWHUP59hMHkMe', 'admin_content', 'D7YTFRWGFGWAZFCO', '2026-01-01', '2025-12-02', 'active', NULL, 'Admin St', 'Admin City', 'Admin', 'Canada', '0AD M1N', 1),
(13, 'admin_financial', 'Admin', 'adminFinancial@admin.com', 'adminFinancialRecovery@admin.com', '$2y$10$V/m/KaVp0wwu1OU2Z7aUBeLMPfNpRJD1PKpMWlXe3XufKW5AnTSju', 'admin_financial', 'V2BQRGV3EC8BTQCP', '2026-01-01', '2025-12-02', 'active', NULL, 'Admin St', 'Admin City', 'Admin', 'Canada', '0AD M1N', 1);

-- --------------------------------------------------------

--
-- Table structure for table `member_interest`
--

CREATE TABLE `member_interest` (
  `member_id` int(11) NOT NULL,
  `area` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_phone`
--

CREATE TABLE `member_phone` (
  `member_id` int(11) NOT NULL,
  `phone_number` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moderator`
--

CREATE TABLE `moderator` (
  `mod_id` int(11) NOT NULL,
  `domain` varchar(50) DEFAULT NULL,
  `approval_rate` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `moderator_expertise`
--

CREATE TABLE `moderator_expertise` (
  `mod_id` int(11) NOT NULL,
  `expertise_tag` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notif_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_date` datetime NOT NULL,
  `type` enum('system','donation','comment','plagiarism') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `priority` enum('low','medium','high','urgent') DEFAULT 'low'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plagiarism_case`
--

CREATE TABLE `plagiarism_case` (
  `case_id` int(11) NOT NULL,
  `committee_id` int(11) DEFAULT NULL,
  `text_id` int(11) DEFAULT NULL,
  `opened_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','under_review','voting','closed','appealed') DEFAULT 'open',
  `resolution` enum('plagiarized','not_plagiarized','appealed') DEFAULT NULL,
  `closed_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plagiarism_case`
--

INSERT INTO `plagiarism_case` (`case_id`, `committee_id`, `text_id`, `opened_date`, `description`, `status`, `resolution`, `closed_date`) VALUES
(1, 1, 1, '2025-11-26', 'Sections of the introduction, specifically regarding the history of React frameworks, appear to be copied verbatim from \"The Evolution of Frontend\" (2023) without proper citation. A preliminary analysis indicates a 15% text similarity score with existing literature, particularly in the third paragraph.', 'voting', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `text`
--

CREATE TABLE `text` (
  `text_id` int(11) NOT NULL,
  `author_orcid` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `abstract` text DEFAULT NULL,
  `topic` varchar(100) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `upload_date` date NOT NULL,
  `status` enum('draft','under_review','published','archived') DEFAULT 'draft',
  `download_count` int(11) DEFAULT NULL,
  `total_donations` decimal(10,2) DEFAULT NULL,
  `avg_rating` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `text`
--

INSERT INTO `text` (`text_id`, `author_orcid`, `title`, `abstract`, `topic`, `version`, `upload_date`, `status`, `download_count`, `total_donations`, `avg_rating`) VALUES
(1, '0000-0000-0000-0001', 'Principles of Modern Web Development', 'An extensive overview of full-stack frameworks and the shift towards reactive frontend libraries.', 'Computer Science', 1, '2025-11-01', 'published', 120, 50.00, 4.50),
(2, '0000-0000-0000-0002', 'Understanding Database Normalization', 'A critical look at why Third Normal Form (3NF) remains essential for data integrity in large-scale systems.', 'Database Systems', 1, '2025-10-15', 'under_review', 45, 10.00, 3.80),
(3, '0000-0000-0000-0003', 'Neural Networks for Beginners', 'A deep dive into the mathematics of backpropagation and how nodes simulate learning.', 'Artificial Intelligence', 1, '2025-01-20', 'published', 0, 0.00, NULL),
(4, '0000-0000-0000-0004', 'Coral Reef Preservation Techniques', 'Analyzing the impact of rising ocean temperatures on marine ecosystems and proposed restoration methods.', 'Biology', 1, '2024-11-20', 'under_review', 89, 150.25, 4.90),
(5, '0000-0000-0000-0005', 'Polymers in Modern Medicine', 'Investigating the use of organic synthetic compounds for targeted drug delivery systems.', 'Chemistry', 2, '2025-11-26', 'published', 30, 25.00, 4.10),
(6, '0000-0000-0000-0002', 'Optimizing SQL Queries for Performance', 'A deep dive into query execution plans, indexing strategies, and common pitfalls in SQL optimization.', 'Database Systems', 1, '2025-11-27', 'published', 0, 0.00, NULL),
(7, '0000-0000-0000-0003', 'Evolution of Neural Networks', 'An in-depth analysis of how neural network architectures have evolved over the last decade.', 'Computer Science', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(8, '0000-0000-0000-0003', 'Optimizing Binary Search Trees', 'A study on balancing algorithms for binary search trees in high-load database environments.', 'Computer Science', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(9, '0000-0000-0000-0003', 'Natural Language Processing APIs', 'Evaluating the efficiency of modern NLP APIs for real-time translation services.', 'Computer Science', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(10, '0000-0000-0000-0004', 'Resilience of Coral Reefs', 'Investigating the survival rates of coral ecosystems in rising ocean temperatures.', 'Biology', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(11, '0000-0000-0000-0004', 'Deep Sea Biodiversity', 'Mapping the unknown species residing in the Mariana Trench ecosystems.', 'Biology', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(12, '0000-0000-0000-0004', 'Microplastics in Marine Food Webs', 'Tracking the accumulation of microplastics in Atlantic fish populations.', 'Biology', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(13, '0000-0000-0000-0005', 'Carbon Chain Synthesis', 'Novel methods for stabilizing long-chain carbon structures in organic compounds.', 'Chemistry', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(14, '0000-0000-0000-0005', 'Hydrophobic Interactions', 'Analyzing the role of hydrophobic interactions in complex organic solvents.', 'Chemistry', 1, '2025-12-01', 'published', 0, 0.00, NULL),
(15, '0000-0000-0000-0005', 'Green Chemistry Catalysts', 'Developing sustainable catalysts for industrial pharmaceutical production.', 'Chemistry', 1, '2025-12-01', 'published', 0, 0.00, NULL);

--
-- Triggers `text`
--
DELIMITER $$
CREATE TRIGGER `remove_downloads_for_archived_texts` AFTER UPDATE ON `text` FOR EACH ROW BEGIN
        IF OLD.status <> 'archived' AND NEW.status = 'archived' THEN
    DELETE
FROM
    download
WHERE
    text_id = NEW.text_id;
    END IF ;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `text_keyword`
--

CREATE TABLE `text_keyword` (
  `text_id` int(11) NOT NULL,
  `keyword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `text_keyword`
--

INSERT INTO `text_keyword` (`text_id`, `keyword`) VALUES
(1, 'frontend'),
(1, 'javascript'),
(1, 'web development'),
(2, 'database'),
(2, 'normalization'),
(2, 'sql'),
(3, 'algorithms'),
(3, 'artificial intelligence'),
(3, 'machine learning'),
(4, 'climate change'),
(4, 'conservation'),
(4, 'marine biology'),
(5, 'chemistry'),
(5, 'drug delivery'),
(5, 'medicine'),
(6, 'optimization'),
(6, 'performance'),
(6, 'sql'),
(7, 'AI'),
(7, 'Deep Learning'),
(7, 'Neural Networks'),
(8, 'Algorithms'),
(8, 'Data Structures'),
(8, 'Trees'),
(9, 'API'),
(9, 'NLP'),
(9, 'Translation'),
(10, 'Conservation'),
(10, 'Ecology'),
(10, 'Marine Biology'),
(11, 'Biodiversity'),
(11, 'Deep Sea'),
(11, 'Oceanography'),
(12, 'Marine'),
(12, 'Microplastics'),
(12, 'Pollution'),
(13, 'Carbon'),
(13, 'Organic Chemistry'),
(13, 'Synthesis'),
(14, 'Chemistry'),
(14, 'Molecular'),
(14, 'Solvents'),
(15, 'Catalysts'),
(15, 'Pharma'),
(15, 'Sustainability');

-- --------------------------------------------------------

--
-- Table structure for table `text_version`
--

CREATE TABLE `text_version` (
  `version_id` int(11) NOT NULL,
  `text_id` int(11) NOT NULL,
  `changes` text NOT NULL,
  `submitted_date` date NOT NULL,
  `review_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `change_summary` varchar(255) DEFAULT NULL,
  `moderator_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote`
--

CREATE TABLE `vote` (
  `vote_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `vote` enum('plagiarized','not_plagiarized','abstain') NOT NULL,
  `date` datetime NOT NULL,
  `rationale` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `admin_permission`
--
ALTER TABLE `admin_permission`
  ADD PRIMARY KEY (`admin_id`,`permission`);

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`orcid`),
  ADD UNIQUE KEY `member_id` (`member_id`);

--
-- Indexes for table `charity`
--
ALTER TABLE `charity`
  ADD PRIMARY KEY (`charity_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `fk_comment_member` (`member_id`),
  ADD KEY `fk_comment_text` (`text_id`),
  ADD KEY `fk_comment_parent` (`parent_comment_id`);

--
-- Indexes for table `committee`
--
ALTER TABLE `committee`
  ADD PRIMARY KEY (`committee_id`);

--
-- Indexes for table `committee_membership`
--
ALTER TABLE `committee_membership`
  ADD PRIMARY KEY (`membership_id`),
  ADD UNIQUE KEY `uq_member_committee` (`member_id`,`committee_id`),
  ADD KEY `fk_cm_committee` (`committee_id`);

--
-- Indexes for table `donation`
--
ALTER TABLE `donation`
  ADD PRIMARY KEY (`donation_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `fk_donation_member` (`member_id`),
  ADD KEY `fk_donation_text` (`text_id`),
  ADD KEY `fk_donation_charity` (`charity_id`);

--
-- Indexes for table `download`
--
ALTER TABLE `download`
  ADD PRIMARY KEY (`download_id`),
  ADD KEY `fk_download_member` (`member_id`),
  ADD KEY `fk_download_text` (`text_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `primary_email` (`primary_email`),
  ADD KEY `fk_member_introduced_by` (`introduced_by`);

--
-- Indexes for table `member_interest`
--
ALTER TABLE `member_interest`
  ADD PRIMARY KEY (`member_id`,`area`);

--
-- Indexes for table `member_phone`
--
ALTER TABLE `member_phone`
  ADD PRIMARY KEY (`member_id`,`phone_number`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `moderator`
--
ALTER TABLE `moderator`
  ADD PRIMARY KEY (`mod_id`);

--
-- Indexes for table `moderator_expertise`
--
ALTER TABLE `moderator_expertise`
  ADD PRIMARY KEY (`mod_id`,`expertise_tag`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `fk_notification_member` (`member_id`);

--
-- Indexes for table `plagiarism_case`
--
ALTER TABLE `plagiarism_case`
  ADD PRIMARY KEY (`case_id`),
  ADD KEY `fk_pc_committee` (`committee_id`),
  ADD KEY `fk_pc_text` (`text_id`);

--
-- Indexes for table `text`
--
ALTER TABLE `text`
  ADD PRIMARY KEY (`text_id`),
  ADD KEY `fk_text_author` (`author_orcid`);

--
-- Indexes for table `text_keyword`
--
ALTER TABLE `text_keyword`
  ADD PRIMARY KEY (`text_id`,`keyword`);

--
-- Indexes for table `text_version`
--
ALTER TABLE `text_version`
  ADD PRIMARY KEY (`version_id`),
  ADD KEY `fk_tv_text` (`text_id`),
  ADD KEY `fk_tv_moderator` (`moderator_id`);

--
-- Indexes for table `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `uq_vote_member_case` (`member_id`,`case_id`),
  ADD KEY `fk_vote_case` (`case_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `charity`
--
ALTER TABLE `charity`
  MODIFY `charity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `committee`
--
ALTER TABLE `committee`
  MODIFY `committee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `committee_membership`
--
ALTER TABLE `committee_membership`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donation`
--
ALTER TABLE `donation`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `download`
--
ALTER TABLE `download`
  MODIFY `download_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plagiarism_case`
--
ALTER TABLE `plagiarism_case`
  MODIFY `case_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `text`
--
ALTER TABLE `text`
  MODIFY `text_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `text_version`
--
ALTER TABLE `text_version`
  MODIFY `version_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vote`
--
ALTER TABLE `vote`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_member` FOREIGN KEY (`admin_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_permission`
--
ALTER TABLE `admin_permission`
  ADD CONSTRAINT `fk_admin_permission_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `author`
--
ALTER TABLE `author`
  ADD CONSTRAINT `fk_author_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `fk_comment_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `comment` (`comment_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_text` FOREIGN KEY (`text_id`) REFERENCES `text` (`text_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `committee_membership`
--
ALTER TABLE `committee_membership`
  ADD CONSTRAINT `fk_cm_committee` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`committee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cm_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donation`
--
ALTER TABLE `donation`
  ADD CONSTRAINT `fk_donation_charity` FOREIGN KEY (`charity_id`) REFERENCES `charity` (`charity_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_donation_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_donation_text` FOREIGN KEY (`text_id`) REFERENCES `text` (`text_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `download`
--
ALTER TABLE `download`
  ADD CONSTRAINT `fk_download_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_download_text` FOREIGN KEY (`text_id`) REFERENCES `text` (`text_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `fk_member_introduced_by` FOREIGN KEY (`introduced_by`) REFERENCES `member` (`member_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `member_interest`
--
ALTER TABLE `member_interest`
  ADD CONSTRAINT `fk_member_interest_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `member_phone`
--
ALTER TABLE `member_phone`
  ADD CONSTRAINT `fk_member_phone_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `moderator`
--
ALTER TABLE `moderator`
  ADD CONSTRAINT `fk_moderator_admin` FOREIGN KEY (`mod_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `moderator_expertise`
--
ALTER TABLE `moderator_expertise`
  ADD CONSTRAINT `fk_moderator_expertise_moderator` FOREIGN KEY (`mod_id`) REFERENCES `moderator` (`mod_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plagiarism_case`
--
ALTER TABLE `plagiarism_case`
  ADD CONSTRAINT `fk_pc_committee` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`committee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_text` FOREIGN KEY (`text_id`) REFERENCES `text` (`text_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `text`
--
ALTER TABLE `text`
  ADD CONSTRAINT `fk_text_author` FOREIGN KEY (`author_orcid`) REFERENCES `author` (`orcid`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `text_keyword`
--
ALTER TABLE `text_keyword`
  ADD CONSTRAINT `fk_text_keyword_text` FOREIGN KEY (`text_id`) REFERENCES `text` (`text_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `text_version`
--
ALTER TABLE `text_version`
  ADD CONSTRAINT `fk_tv_moderator` FOREIGN KEY (`moderator_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tv_text` FOREIGN KEY (`text_id`) REFERENCES `text` (`text_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `fk_vote_case` FOREIGN KEY (`case_id`) REFERENCES `plagiarism_case` (`case_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_member` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
