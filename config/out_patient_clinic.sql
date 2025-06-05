-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 30, 2025 at 08:46 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `out patient clinic`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

DROP TABLE IF EXISTS `appointment`;
CREATE TABLE IF NOT EXISTS `appointment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `visit_type` enum('Consultation','Follow-up','Emergency','Check-up') DEFAULT 'Consultation',
  `location` varchar(255) DEFAULT NULL,
  `notes` text,
  `status` enum('Scheduled','Cancelled','Completed') DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `day_of_week` varchar(20) DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT '0',
  `confirmation_sent_at` datetime DEFAULT NULL,
  `reminder_sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`id`, `patient_id`, `doctor_id`, `visit_type`, `location`, `notes`, `status`, `created_at`, `day_of_week`, `appointment_time`, `reminder_sent`, `confirmation_sent_at`, `reminder_sent_at`) VALUES
(1, 1, 1, 'Consultation', 'Clinic A', 'Follow-up required', 'Scheduled', '2025-05-16 22:00:59', 'Tuesday', '10:00:00', 0, NULL, NULL),
(2, 1, 1, '', 'Clinic A', 'Initial consultation for cardiology', 'Scheduled', '2025-05-19 09:00:00', 'Sunday', '08:00:00', 0, NULL, NULL),
(3, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 12:27:50', 'Tuesday', '13:00:00', 0, NULL, NULL),
(4, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 12:34:41', 'Tuesday', '13:00:00', 0, NULL, NULL),
(5, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 12:43:25', 'Tuesday', '13:00:00', 0, NULL, NULL),
(6, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 13:11:01', 'Tuesday', '14:00:00', 0, NULL, NULL),
(7, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:05:33', 'Tuesday', NULL, 0, NULL, NULL),
(8, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:05:37', 'Tuesday', NULL, 0, NULL, NULL),
(9, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:07:47', 'Tuesday', NULL, 0, NULL, NULL),
(10, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:10:40', 'Tuesday', NULL, 0, NULL, NULL),
(11, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:10:58', 'Sunday', NULL, 0, NULL, NULL),
(12, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:12:45', 'Sunday', NULL, 0, NULL, NULL),
(13, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 14:15:16', 'Thursday', NULL, 0, NULL, NULL),
(14, 4, 1, 'Consultation', 'Clinic', NULL, 'Scheduled', '2025-05-19 21:26:14', 'Sunday', '11:00:00', 0, NULL, NULL),
(15, 4, 1, 'Consultation', 'Clinic', NULL, 'Scheduled', '2025-05-19 21:26:52', 'Tuesday', '03:00:00', 0, NULL, NULL),
(16, 4, 1, 'Consultation', 'Clinic', NULL, 'Scheduled', '2025-05-19 21:27:34', 'Tuesday', '03:00:00', 0, NULL, NULL),
(17, 4, 1, 'Consultation', 'Clinic', NULL, 'Scheduled', '2025-05-19 21:34:16', 'Tuesday', '03:00:00', 0, NULL, NULL),
(18, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 23:37:39', 'Tuesday', '13:00:00', 0, NULL, NULL),
(19, 4, 1, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-19 23:58:48', 'Sunday', '11:00:00', 0, NULL, NULL),
(20, 4, 3, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-20 00:06:31', 'Monday', '10:00:00', 0, NULL, NULL),
(21, 4, 3, 'Consultation', NULL, NULL, 'Scheduled', '2025-05-20 00:10:37', 'Wednesday', '09:00:00', 0, NULL, NULL),
(22, 4, 1, 'Consultation', 'Clinic A', 'Emergency hours', 'Scheduled', '2025-05-20 00:14:19', 'Thursday', '10:00:00', 0, NULL, NULL),
(23, 4, 1, 'Consultation', 'Clinic A', 'Cardiology Checkup', 'Scheduled', '2025-05-20 00:16:27', 'Sunday', '08:00:00', 0, NULL, NULL),
(24, 4, 1, 'Consultation', 'Clinic A', 'Cardiology Checkup', 'Scheduled', '2025-05-21 17:33:52', 'Sunday', '08:00:00', 0, NULL, NULL),
(25, 4, 3, 'Consultation', 'Clinic C', 'Heart patients only', 'Scheduled', '2025-05-21 20:26:05', 'Monday', '08:00:00', 0, NULL, NULL),
(26, 4, 1, 'Consultation', 'Clinic A', 'Cardiology Checkup', 'Scheduled', '2025-05-21 21:47:02', 'Sunday', '08:00:00', 0, NULL, NULL),
(27, 4, 3, 'Consultation', 'Clinic A', 'ECG Sessions', 'Scheduled', '2025-05-22 06:38:32', 'Wednesday', '09:00:00', 0, NULL, NULL),
(28, 4, 1, 'Consultation', 'Clinic A', 'Emergency hours', 'Scheduled', '2025-05-26 00:37:16', 'Thursday', '10:00:00', 0, NULL, NULL),
(29, 4, 1, 'Consultation', 'Clinic A', 'Cardiology Checkup', 'Scheduled', '2025-05-27 10:38:03', 'Sunday', '08:00:00', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

DROP TABLE IF EXISTS `doctor`;
CREATE TABLE IF NOT EXISTS `doctor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ID_NUMBER` varchar(20) DEFAULT NULL,
  `FN` varchar(100) DEFAULT NULL,
  `LN` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `plain_password` varchar(255) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `flag_login` tinyint(1) DEFAULT '0',
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`id`, `ID_NUMBER`, `FN`, `LN`, `email`, `phone`, `password`, `plain_password`, `title`, `specialty`, `gender`, `flag_login`, `photo`) VALUES
(1, 'Ihf001', 'Safi', '', 'safaa.cardiology1@example.com', '01000111111', '$2y$10$5Pk4oscDdRNvP/.KvDn2ZeFIehvcEHWFzwtFlLe0MlhevWj8c0otu', '123', 'Dr.', 'Cardiology', 'Male', 1, ''),
(2, 'Ihf002', 'Mai', '', 'mai.cardiology2@example.com', '01000222222', 'pass', 'pass', 'Dr.', 'Cardiology', 'Female', 0, 'D:\\wamp64\\www\\MVC\\uploads\\doctors\\11.jpg'),
(3, 'Ihf003', 'Sara', '', 'sara.cardiology3@example.com', '01000333333', 'pass', 'pass', 'Dr.', 'Cardiology', 'Female', 0, ''),
(4, 'Ihf004', 'Asmaa', '', 'asmaa.derma1@example.com', '01000444444', 'pass', 'pass', 'Dr.', 'Dermatology', 'Female', 0, ''),
(5, 'Ihf005', 'Mohamed', 'Yehia', 'mohamed.derma2@example.com', '01000555555', 'pass', 'pass', 'Dr.', 'Dermatology', 'Male', 0, ''),
(6, 'Ihf006', 'Omnia', 'Saeed', 'omina.derma3@example.com', '01000666666', 'pass', 'pass', 'Dr.', 'Dermatology', 'Female', 0, ''),
(7, 'Ihf007', 'Shaimaa', '', 'shaimaa.pedia1@example.com', '01000777777', 'pass', 'pass', 'Dr.', 'Pediatrics', 'Female', 0, ''),
(8, 'Ihf008', 'Mahitab', '', 'mahitab.pedia2@example.com', '01000888888', 'pass', 'pass', 'Dr.', 'Pediatrics', 'Female', 0, ''),
(9, 'Ihf009', 'Reham', '', 'reham.pedia3@example.com', '01000999999', 'pass', 'pass', 'Dr.', 'Pediatrics', 'Female', 0, ''),
(10, 'Ihf010', 'Omnia', 'Elgendy', 'manar.op1@example.com', '01001010101', 'pass', 'pass', 'Dr.', 'Ophthalmology', 'Female', 0, ''),
(11, 'Ihf011', 'Marwa', '', 'marwa.op2@example.com', '01001111112', 'pass', 'pass', 'Dr.', 'Ophthalmology', 'Female', 0, ''),
(12, 'Ihf012', 'Doaa', '', 'Doaa.op3@example.com', '01001212121', 'pass', 'pass', 'Dr.', 'Ophthalmology', 'Female', 0, ''),
(13, 'Ihf013', 'Ahmed', 'Nagy', 'Ahmed.ent1@example.com', '01001313131', 'pass', 'pass', 'Dr.', 'ENT', 'Male', 0, ''),
(14, 'Ihf014', 'Mohamed', 'Soliman', 'Mohamedd.ent2@example.com', '01001414141', 'pass', 'pass', 'Dr.', 'ENT', 'Male', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_details`
--

DROP TABLE IF EXISTS `doctor_details`;
CREATE TABLE IF NOT EXISTS `doctor_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doctor_id` int NOT NULL,
  `academic_degree` varchar(100) DEFAULT NULL,
  `experience_years` int DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `qualifications` json DEFAULT NULL,
  `office_hours` json DEFAULT NULL,
  `courses` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor_details`
--

INSERT INTO `doctor_details` (`id`, `doctor_id`, `academic_degree`, `experience_years`, `office`, `qualifications`, `office_hours`, `courses`) VALUES
(1, 1, 'PhD in Orthopedics', 10, 'Building A, Room 305', '[{\"year\": 2015, \"degree\": \"PhD in Orthopedics\", \"institution\": \"Cairo University\"}, {\"year\": 2012, \"degree\": \"MSc in Physical Therapy\", \"institution\": \"Ain Shams University\"}]', '{\"Sunday\": \"10:00 - 14:00\", \"Tuesday\": \"12:00 - 16:00\", \"Thursday\": \"09:00 - 12:00\"}', '[{\"code\": \"PT101\", \"name\": \"Rehabilitation Basics\", \"semester\": \"Fall 2024\"}, {\"code\": \"PT202\", \"name\": \"Sports Injuries\", \"semester\": \"Spring 2025\"}]'),
(2, 2, 'MD, Cardiology', 10, 'Clinic B', '[\"Fellowship in Cardiology\", \"Certified Cardiologist\"]', '{\"Monday\": \"09:00-12:00\", \"Wednesday\": \"14:00-16:00\"}', '[\"Cardiac Care\", \"Heart Disease Management\"]'),
(3, 3, 'MD, Cardiology', 8, 'Clinic C', '[\"Fellowship in Cardiology\", \"Certified Cardiologist\"]', '{\"Friday\": \"12:00-14:00\", \"Monday\": \"08:00-10:00\", \"Wednesday\": \"09:00-11:00\"}', '[\"Heart Health\", \"Cardiac Rehabilitation\"]'),
(4, 4, 'MD, Dermatology', 12, 'Dermatology Center', '[\"Fellowship in Dermatology\", \"Certified Dermatologist\"]', '{\"Tuesday\": \"09:00-12:00\", \"Thursday\": \"14:00-16:00\"}', '[\"Skin Care\", \"Dermatological Procedures\"]');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedule`
--

DROP TABLE IF EXISTS `doctor_schedule`;
CREATE TABLE IF NOT EXISTS `doctor_schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doctor_id` int NOT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text,
  `availability` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor_schedule`
--

INSERT INTO `doctor_schedule` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `location`, `notes`, `availability`) VALUES
(1, 1, 'Sunday', '08:00:00', '11:00:00', 'Clinic A', 'Cardiology Checkup', 'Unavailable'),
(2, 1, 'Tuesday', '13:00:00', '15:00:00', 'Clinic B', 'Cardiology Follow-up', ''),
(3, 1, 'Thursday', '10:00:00', '12:00:00', 'Clinic A', 'Emergency hours', 'Available'),
(4, 3, 'Monday', '08:00:00', '10:00:00', 'Clinic C', 'Heart patients only', 'Available'),
(5, 3, 'Wednesday', '09:00:00', '11:00:00', 'Clinic A', 'ECG Sessions', 'Unavailable'),
(6, 3, 'Friday', '12:00:00', '14:00:00', 'Clinic B', 'Consultation', 'Available'),
(7, 5, 'Tuesday', '10:00:00', '12:00:00', 'Skin Center', 'Acne treatment', 'Available'),
(8, 5, 'Thursday', '14:00:00', '16:00:00', 'Skin Center', 'Laser clinic', 'Available'),
(9, 5, 'Saturday', '09:00:00', '11:00:00', 'Branch D', 'Follow-up', 'Available'),
(10, 7, 'Monday', '10:00:00', '12:00:00', 'Kids Clinic', 'General Pediatrics', 'Available'),
(11, 7, 'Wednesday', '13:00:00', '15:00:00', 'Main Hospital', 'Vaccination Day', 'Available'),
(12, 7, 'Friday', '08:00:00', '10:00:00', 'Kids Clinic', 'Newborn Check', 'Available'),
(13, 9, 'Sunday', '09:30:00', '11:30:00', 'Branch C', 'Checkups', 'Available'),
(14, 9, 'Tuesday', '11:00:00', '13:00:00', 'Main Hospital', 'Emergency slots', 'Available'),
(15, 9, 'Thursday', '12:00:00', '14:00:00', 'Clinic D', 'Follow-up', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

DROP TABLE IF EXISTS `manager`;
CREATE TABLE IF NOT EXISTS `manager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `flag_login` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `manager`
--

INSERT INTO `manager` (`id`, `username`, `password`, `first_name`, `last_name`, `phone`, `email`, `flag_login`) VALUES
(1, 'MGR500', 'PT123', 'safy', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

DROP TABLE IF EXISTS `notification_logs`;
CREATE TABLE IF NOT EXISTS `notification_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `notification_type` enum('email','sms') NOT NULL,
  `status` enum('sent','failed') NOT NULL,
  `message` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `id` int NOT NULL AUTO_INCREMENT,
  `FN` varchar(100) DEFAULT NULL,
  `LN` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `age` int DEFAULT NULL,
  `idnumber` varchar(20) DEFAULT NULL,
  `NN` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `job` varchar(100) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `marital` enum('single','married') DEFAULT NULL,
  `flag_login` tinyint(1) DEFAULT '0',
  `plain_password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `idnumber` (`idnumber`),
  UNIQUE KEY `NN` (`NN`)
) ;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`id`, `FN`, `LN`, `email`, `phone`, `password`, `age`, `idnumber`, `NN`, `address`, `job`, `gender`, `marital`, `flag_login`, `plain_password`, `photo`) VALUES
(1, 'Mahmoud', 'Fawzy', 'mf242095@gmail.com', '01018146088', '$2y$10$TJ6bso4xQhsJO6gOrdqP.ehURtlg9FixDzj.8nJwS9btNWqVaXHaK', 21, '241805', NULL, 'Egypt', 'studet', 'male', 'single', 0, 'Mahmoud1282005', NULL),
(2, 'Body', 'Taha', 'mahmoud.mohamed114@msa.edu.eg', '01207703807', '$2y$10$UFU6JrwO5ZWZsmFNUZw0HOwmpSrneSdOV0eNc9GVxVYaDeJd7.leO', 20, NULL, '3030383827232', 'octoper', 'studet', 'male', 'single', 1, 'body12082005', NULL),
(3, 'ali', 'ahmed', 'ali@gmail.com', '01111111111', '$2y$10$IxNJc1uZwXMQ2H8NoO0DN.Ad2qwRolY5A.ydBmWRj08NI8uk4Fn5O', 19, '248971', NULL, 'octoper', 'studet', 'female', 'married', 0, 'mahmoudfawzy2022', NULL),
(4, 'Youssif', 'Mohamed', 'mohamed@gmail.com', '01207766607', '$2y$10$3blwHA.LL.dPMM.HIGeDJ.Xz3qAI416/VsTEJ1RTTe4F6D.1Q7BFa', 19, '2222', NULL, 'Egypthhhhhhhhhhhh', 'studet', 'male', 'single', 1, 'Ihf12082005', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ID_NUMBER` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `FN` varchar(100) DEFAULT NULL,
  `LN` varchar(100) DEFAULT NULL,
  `role` enum('Receptionist','Admin') DEFAULT 'Receptionist',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `flag_login` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID_NUMBER` (`ID_NUMBER`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `ID_NUMBER`, `password`, `FN`, `LN`, `role`, `phone`, `email`, `flag_login`) VALUES
(1, 'STAFF001', '$2y$10$d2UjsGojF2spJGyGhs1LC.vBZgEUdLiLj75mOrR25bhIFWoeOOnQS', 'Youssif', 'Moahmed ', 'Receptionist', '01000111111', 'safaa.reception1@example.com', 1),
(2, 'STAFF002', '$2y$10$.F2zSiMIcpWVVhV1fPRaie3yU1LRyBI.3tr2C93IPuxAVlc209Qj.', 'Mai', '', 'Receptionist', '01000222222', 'mai.reception2@example.com', 1),
(3, 'STAFF003', '123', 'Sara', '', 'Receptionist', '01000333333', 'sara.reception3@example.com', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
