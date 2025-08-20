-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 04, 2025 at 02:35 PM
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
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`id`, `patient_id`, `doctor_id`, `visit_type`, `location`, `notes`, `status`, `created_at`, `day_of_week`, `appointment_time`) VALUES
(26, 4, 1, 'Consultation', 'Clinic A', 'Regular checkup', 'Scheduled', '2025-07-04 14:21:06', 'Friday', '09:00:00'),
(25, 4, 7, 'Consultation', 'Clinic C', 'Free appointment booking', 'Scheduled', '2025-07-04 03:03:14', 'Wensday', '00:07:50'),
(24, 4, 5, 'Consultation', 'Clinic C', 'Free appointment booking', 'Scheduled', '2025-07-04 02:54:14', 'Tuesday', '11:00:00'),
(12, 4, 3, 'Consultation', 'Clinic C', 'Free appointment booking', 'Scheduled', '2025-06-25 02:40:03', 'Sunday', '08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `appointment_id` int NOT NULL,
  `sender_type` enum('doctor','patient') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(1, 'Ihf001', 'Safi', '', 'safaa.cardiology1@example.com', '01000111111', '$2y$10$5Pk4oscDdRNvP/.KvDn2ZeFIehvcEHWFzwtFlLe0MlhevWj8c0otu', '123', 'Dr.', 'Cardiology', 'Male', 1, 'D:\\wamp64\\www\\MVC\\uploads\\doctors\\11.jpg'),
(2, 'Ihf002', 'Mai', '', 'mai.cardiology2@example.com', '01000222222', 'pass', 'pass', 'Dr.', 'Cardiology', 'Female', 0, 'D:\\wamp64\\www\\MVC\\uploads\\doctors\\11.jpg'),
(3, 'Ihf003', 'Sara', '', 'sara.cardiology3@example.com', '01000333333', '$2y$10$5Pk4oscDdRNvP/.KvDn2ZeFIehvcEHWFzwtFlLe0MlhevWj8c0otu', 'pass', 'Dr.', 'Cardiology', 'Female', 1, 'D:\\wamp64\\www\\MVC\\uploads\\doctors\\11.jpg'),
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
  `slot_fee` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor_schedule`
--

INSERT INTO `doctor_schedule` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `location`, `notes`, `availability`, `slot_fee`) VALUES
(1, 1, 'Sunday', '08:00:00', '11:00:00', 'Clinic A', 'Cardiology Checkup', 'Unavailable', 200.00),
(2, 1, 'Monday', '13:00:00', '15:00:00', 'Clinic B', 'Cardiology Follow-up', 'Unavailable', 0.00),
(3, 1, 'Thursday', '10:00:00', '11:00:00', 'Clinic C', 'Emergency hours', 'Available', 100.00),
(4, 3, 'Sunday', '08:00:00', '10:00:00', 'Clinic D', 'Heart patients only', 'Unavailable', 0.00),
(5, 3, 'Sunday', '09:00:00', '11:00:00', 'Clinic A', 'ECG Sessions', 'Available', 0.00),
(6, 3, 'Friday', '12:00:00', '14:00:00', 'Clinic B', 'Consultation', 'Available', 0.00),
(7, 5, 'Tuesday', '10:00:00', '12:00:00', 'Clinic C', 'Acne treatment', 'Unavailable', 0.00),
(8, 5, 'Thursday', '14:00:00', '16:00:00', 'Clinic D', 'Laser clinic', 'Available', 0.00),
(9, 5, 'Saturday', '09:00:00', '11:00:00', 'Clinic A', 'Follow-up', 'Available', 100.00),
(10, 7, 'Monday', '10:00:00', '12:00:00', 'Clinic B', 'General Pediatrics', 'Available', 0.00),
(11, 7, 'Wednesday', '13:00:00', '15:00:00', 'Clinic C', 'Vaccination Day', 'Unavailable', 0.00),
(12, 7, 'Friday', '08:00:00', '10:00:00', 'Clinic D', 'Newborn Check', 'Available', 0.00),
(13, 9, 'Sunday', '09:30:00', '11:30:00', 'Clinic A', 'Checkups', 'Unavailable', 0.00),
(14, 9, 'Tuesday', '11:00:00', '13:00:00', 'Clinic B', 'Emergency slots', 'Available', 0.00),
(15, 9, 'Thursday', '12:00:00', '14:00:00', 'Clinic C', 'Follow-up', 'Available', 0.00),
(16, 11, 'Sunday', '15:40:00', '16:40:00', 'Clinic D', 'ana', 'Available', 0.00),
(17, 1, 'Friday', '09:00:00', '17:00:00', 'Clinic A', 'Full day consultation', 'Available', 250.00),
(19, 12, 'Tuesday', '08:00:00', '09:00:00', 'Clinic A', '', 'Unavailable', 0.00),
(20, 1, 'Friday', '09:00:00', '11:00:00', 'Clinic A', 'Full day consultation', 'Unavailable', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_consumption`
--

DROP TABLE IF EXISTS `inventory_consumption`;
CREATE TABLE IF NOT EXISTS `inventory_consumption` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ItemName` varchar(255) DEFAULT NULL,
  `Quantity` int DEFAULT NULL,
  `ConsumeDate` date DEFAULT NULL,
  `DoctorID` int DEFAULT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_consumption`
--

INSERT INTO `inventory_consumption` (`ID`, `ItemName`, `Quantity`, `ConsumeDate`, `DoctorID`, `Notes`) VALUES
(1, 'Gauze Pads', 100, '2025-06-11', 101, 'Used in ER'),
(2, 'Gloves', 300, '2025-06-12', 102, 'Daily rounds'),
(3, 'Face Masks', 500, '2025-06-12', 103, 'Distributed to staff'),
(4, 'Syringes', 120, '2025-06-13', 104, ''),
(5, 'IV Bags', 50, '2025-06-13', 105, 'Used for dehydration cases'),
(6, 'Alcohol Swabs', 150, '2025-06-14', 101, ''),
(7, 'Bandages', 200, '2025-06-14', 102, 'Post-operation dressing'),
(8, 'Thermometers', 10, '2025-06-15', 103, 'Room distribution'),
(9, 'Cotton Rolls', 80, '2025-06-15', 104, ''),
(10, 'Surgical Blades', 30, '2025-06-16', 105, 'Minor surgeries');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_receipts`
--

DROP TABLE IF EXISTS `inventory_receipts`;
CREATE TABLE IF NOT EXISTS `inventory_receipts` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ItemName` varchar(255) DEFAULT NULL,
  `Quantity` int DEFAULT NULL,
  `IssueDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `ReceptionID` int DEFAULT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_receipts`
--

INSERT INTO `inventory_receipts` (`ID`, `ItemName`, `Quantity`, `IssueDate`, `EndDate`, `ReceptionID`, `Notes`) VALUES
(1, 'Gauze Pads', 500, '2025-06-01', '2025-12-01', 1, 'Used in surgery rooms'),
(2, 'Gloves', 1000, '2025-06-02', '2025-11-30', 2, 'Sterile gloves'),
(3, 'Face Masks', 2000, '2025-06-03', '2025-12-31', 3, 'N95 masks'),
(4, 'Syringes', 300, '2025-06-04', '2025-12-15', 1, 'Disposable syringes'),
(5, 'IV Bags', 150, '2025-06-05', '2025-12-20', 2, '500ml saline solution'),
(6, 'Alcohol Swabs', 400, '2025-06-06', '2025-11-25', 4, ''),
(7, 'Bandages', 600, '2025-06-07', '2025-12-10', 3, 'Various sizes'),
(8, 'Thermometers', 50, '2025-06-08', '2026-06-08', 1, 'Digital'),
(9, 'Cotton Rolls', 200, '2025-06-09', '2026-01-15', 4, 'For wound dressing'),
(10, 'Surgical Blades', 100, '2025-06-10', '2025-12-30', 2, 'Size 10 and 11');

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
(1, 'MGR500', '$2y$10$6OQGCr7lkxf4E1NBOpPgS.pW1.PtjzRRzZ1vi244aI4RG1go8zl4.', 'safy', NULL, NULL, NULL, 1);

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
(1, 'Mahmoud', 'Fawzy', 'mf242095@gmail.com', '10', '$2y$10$TJ6bso4xQhsJO6gOrdqP.ehURtlg9FixDzj.8nJwS9btNWqVaXHaK', 21, '241805', NULL, 'Egypt', 'studet', 'male', 'single', 0, 'Mahmoud1282005', ''),
(2, 'Body', 'Taha', 'mahmoud.mohamed114@msa.edu.eg', '01207703807', '$2y$10$UFU6JrwO5ZWZsmFNUZw0HOwmpSrneSdOV0eNc9GVxVYaDeJd7.leO', 20, NULL, '3030383827232', 'octoper', 'studet', 'male', 'single', 1, 'body12082005', NULL),
(3, 'ali', 'ahmed', 'ali@gmail.com', '01111111111', '$2y$10$IxNJc1uZwXMQ2H8NoO0DN.Ad2qwRolY5A.ydBmWRj08NI8uk4Fn5O', 19, '248971', NULL, 'octoper', 'studet', 'female', 'married', 0, 'mahmoudfawzy2022', NULL),
(4, 'Youssif', 'Mohamed', 'mohamed@gmail.com', '01018146088', '$2y$10$3blwHA.LL.dPMM.HIGeDJ.Xz3qAI416/VsTEJ1RTTe4F6D.1Q7BFa', 19, '2222', NULL, 'as', 'JOP', 'male', 'single', 0, 'Ihf12082005', 'D:\\wamp64\\www\\MVC\\uploads\\doctors\\11.jpg'),
(9, 'wahed', 'tany', NULL, '01018146099', '$2y$10$D52LQDJCNF5TvGmibYNEyeXjU9l8PTa.Sj29Va0lqOCFJC2KINQ6W', 110, '4444', '', 'tt', '', 'male', 'single', 0, '01018146099', NULL);

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
(3, 'STAFF003', '$2y$10$jFmjYANMNRTU51XUHZ6/5O1k8pq2qjKGbGcrWdbW1Y8pmgQ9rOysG', 'Sara', '', 'Receptionist', '01000333333', 'sara.reception3@example.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `treatment_plans`
--

DROP TABLE IF EXISTS `treatment_plans`;
CREATE TABLE IF NOT EXISTS `treatment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `diagnosis` text,
  `duration` varchar(50) DEFAULT NULL,
  `total_sessions` int DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `treatment_plans`
--

INSERT INTO `treatment_plans` (`id`, `patient_id`, `appointment_id`, `diagnosis`, `duration`, `total_sessions`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 12, 'Lower back pain with sciatica', '8 weeks', 5, 'Patient showing good progress with physical therapy exercises. Pain level reduced from 8/10 to 5/10.', '2025-06-25 02:41:46', '2025-06-25 03:05:12'),
(3, 4, 16, '', '4 weeks', 1, '', '2025-06-29 03:04:10', '2025-06-29 03:04:10');

-- --------------------------------------------------------

--
-- Table structure for table `treatment_sessions`
--

DROP TABLE IF EXISTS `treatment_sessions`;
CREATE TABLE IF NOT EXISTS `treatment_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treatment_plan_id` int NOT NULL,
  `session_number` int NOT NULL,
  `session_date` date NOT NULL,
  `notes` text,
  `status` enum('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `treatment_plan_id` (`treatment_plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `treatment_sessions`
--

INSERT INTO `treatment_sessions` (`id`, `treatment_plan_id`, `session_number`, `session_date`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(7, 1, 3, '2024-01-15', 'Advanced stretching and mobility exercises', 'completed', '2025-06-25 02:41:58', '2025-06-25 02:41:58'),
(6, 1, 2, '2024-01-08', 'Core strengthening exercises and heat therapy', 'completed', '2025-06-25 02:41:58', '2025-06-25 02:41:58'),
(5, 1, 1, '2024-01-01', 'Initial assessment and gentle stretching exercises', 'completed', '2025-06-25 02:41:58', '2025-06-25 02:41:58'),
(8, 1, 4, '2024-01-22', 'Strength training and posture correction', 'scheduled', '2025-06-25 02:41:58', '2025-06-25 02:41:58'),
(9, 1, 5, '2024-01-29', 'Balance training and posture review', 'scheduled', '2025-06-25 02:41:58', '2025-06-25 02:41:58');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
