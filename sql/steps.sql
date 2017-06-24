-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2017 at 05:22 PM
-- Server version: 10.1.8-MariaDB
-- PHP Version: 5.6.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `workflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `steps`
--

DROP TABLE IF EXISTS `steps`;
CREATE TABLE `steps` (
  `step_id` int(11) NOT NULL,
  `step_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `steps`
--

INSERT INTO `steps` (`step_id`, `step_name`) VALUES
(1, 'Brand Hopper'),
(2, 'Account'),
(3, 'Profile'),
(4, 'Warning'),
(5, 'Finish'),
(6, 'MRF'),
(7, 'New Project'),
(8, 'In Progress'),
(9, 'Complete'),
(10, 'Start Sample'),
(11, 'Received'),
(12, 'In Test'),
(13, 'Test Results'),
(14, 'Complete'),
(27, 'Start'),
(29, 'Service\nTask'),
(32, 'User Task'),
(33, 'Task 1'),
(34, 'Task 2'),
(35, 'Start'),
(36, 'Task 3'),
(37, 'Parallel'),
(38, 'Start'),
(39, 'Received'),
(40, 'In Progress'),
(41, 'Complete'),
(42, 'Task'),
(43, 'Task'),
(44, 'Task'),
(46, 'Start'),
(47, 'Task1'),
(48, 'Message Task'),
(49, 'Task 2'),
(50, 'Timer Task 1'),
(51, 'Timer Task 2'),
(52, 'Timer Task 3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `steps`
--
ALTER TABLE `steps`
  ADD PRIMARY KEY (`step_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `steps`
--
ALTER TABLE `steps`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
