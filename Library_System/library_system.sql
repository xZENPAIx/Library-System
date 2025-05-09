-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 01, 2025 at 03:32 AM
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
-- Database: `library_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `address_tbl`
--

CREATE TABLE `address_tbl` (
  `address_id` int(11) NOT NULL,
  `brgy` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address_tbl`
--

INSERT INTO `address_tbl` (`address_id`, `brgy`, `municipality`, `province`) VALUES
(2, 'Buri', 'Jaro', 'Leyte');

-- --------------------------------------------------------

--
-- Table structure for table `admin_tbl`
--

CREATE TABLE `admin_tbl` (
  `admin_id` varchar(20) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `User_level` enum('Super Admin','Librarian') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_tbl`
--

INSERT INTO `admin_tbl` (`admin_id`, `Name`, `Email`, `User_level`) VALUES
('SA01', 'Super Admin', 'admin@library.com', 'Super Admin'),
('SUPER001', 'System Super Admin', 'superadmin@library.com', 'Super Admin'),
('SUPER002', 'Backup Admin', 'backup@library.com', 'Super Admin');

-- --------------------------------------------------------

--
-- Table structure for table `archive_tbl`
--

CREATE TABLE `archive_tbl` (
  `archive_id` int(11) NOT NULL,
  `lending_id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `std_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `borrow_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_tbl`
--

CREATE TABLE `book_tbl` (
  `isbn` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `publisher` varchar(100) NOT NULL,
  `year_published` year(4) NOT NULL,
  `category` varchar(100) NOT NULL,
  `available` int(11) NOT NULL,
  `lended` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lending_tbl`
--

CREATE TABLE `lending_tbl` (
  `lending_id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `std_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `borrow_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_tbl`
--

CREATE TABLE `login_tbl` (
  `Account_ID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `user_level` enum('student','librarian','super_admin') NOT NULL,
  `std_id` varchar(20) DEFAULT NULL,
  `admin_id` varchar(20) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `login_tbl`
--

INSERT INTO `login_tbl` (`Account_ID`, `username`, `password`, `user_level`, `std_id`, `admin_id`, `is_approved`, `created_at`) VALUES
(3, 'superadmin', 'e51179d82a9dc70ba1ab002f14563bc2ebc58c0d4f5ab689cef789f3b9bd3525', 'super_admin', NULL, 'SUPER001', 1, '2025-04-30 04:49:51'),
(6, 'sa01', 'e51179d82a9dc70ba1ab002f14563bc2ebc58c0d4f5ab689cef789f3b9bd3525', 'super_admin', NULL, 'SA01', 1, '2025-05-01 00:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `program_tbl`
--

CREATE TABLE `program_tbl` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(50) NOT NULL,
  `year_level` varchar(10) NOT NULL,
  `section` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_tbl`
--

INSERT INTO `program_tbl` (`program_id`, `program_name`, `year_level`, `section`) VALUES
(2, 'BSIT', '2', 'A'),
(3, 'BSIT', '1', 'A'),
(4, 'BSIT', '1', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `std_tbl`
--

CREATE TABLE `std_tbl` (
  `std_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `program_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `borrowed_books` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `std_tbl`
--

INSERT INTO `std_tbl` (`std_id`, `name`, `program_id`, `address_id`, `email`, `contact`, `borrowed_books`) VALUES
('A23-01-00212', 'Delicano, Mark Roland, P.', 2, 2, 'markrolanddelicano@gmail.com', '09928614218', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address_tbl`
--
ALTER TABLE `address_tbl`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `admin_tbl`
--
ALTER TABLE `admin_tbl`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `archive_tbl`
--
ALTER TABLE `archive_tbl`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `lending_id` (`lending_id`);

--
-- Indexes for table `book_tbl`
--
ALTER TABLE `book_tbl`
  ADD PRIMARY KEY (`isbn`);

--
-- Indexes for table `lending_tbl`
--
ALTER TABLE `lending_tbl`
  ADD PRIMARY KEY (`lending_id`),
  ADD KEY `isbn` (`isbn`),
  ADD KEY `std_id` (`std_id`);

--
-- Indexes for table `login_tbl`
--
ALTER TABLE `login_tbl`
  ADD PRIMARY KEY (`Account_ID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `std_id` (`std_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `program_tbl`
--
ALTER TABLE `program_tbl`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `std_tbl`
--
ALTER TABLE `std_tbl`
  ADD PRIMARY KEY (`std_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `address_id` (`address_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address_tbl`
--
ALTER TABLE `address_tbl`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `archive_tbl`
--
ALTER TABLE `archive_tbl`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lending_tbl`
--
ALTER TABLE `lending_tbl`
  MODIFY `lending_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `Account_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_tbl`
--
ALTER TABLE `program_tbl`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archive_tbl`
--
ALTER TABLE `archive_tbl`
  ADD CONSTRAINT `archive_tbl_ibfk_1` FOREIGN KEY (`lending_id`) REFERENCES `lending_tbl` (`lending_id`);

--
-- Constraints for table `lending_tbl`
--
ALTER TABLE `lending_tbl`
  ADD CONSTRAINT `lending_tbl_ibfk_1` FOREIGN KEY (`isbn`) REFERENCES `book_tbl` (`isbn`),
  ADD CONSTRAINT `lending_tbl_ibfk_2` FOREIGN KEY (`std_id`) REFERENCES `std_tbl` (`std_id`);

--
-- Constraints for table `login_tbl`
--
ALTER TABLE `login_tbl`
  ADD CONSTRAINT `login_tbl_ibfk_1` FOREIGN KEY (`std_id`) REFERENCES `std_tbl` (`std_id`),
  ADD CONSTRAINT `login_tbl_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_tbl` (`admin_id`);

--
-- Constraints for table `std_tbl`
--
ALTER TABLE `std_tbl`
  ADD CONSTRAINT `std_tbl_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program_tbl` (`program_id`),
  ADD CONSTRAINT `std_tbl_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `address_tbl` (`address_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
