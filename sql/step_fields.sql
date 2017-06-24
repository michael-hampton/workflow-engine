-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2017 at 05:24 PM
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
-- Table structure for table `step_fields`
--

DROP TABLE IF EXISTS `step_fields`;
CREATE TABLE `step_fields` (
  `field_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `is_disabled` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `system_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `field_conditions` varchar(255) NOT NULL,
  `custom_javascript` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `step_fields`
--

INSERT INTO `step_fields` (`field_id`, `step_id`, `is_disabled`, `order_id`, `system_id`, `id`, `field_conditions`, `custom_javascript`) VALUES
(18, 6, 0, 5, 1, 14, '', ''),
(19, 6, 0, 4, 1, 15, '', ''),
(20, 6, 0, 3, 1, 16, '', ''),
(21, 6, 0, 2, 1, 17, '', ''),
(22, 6, 0, 1, 1, 18, '', ''),
(1, 2, 0, 1, 0, 142, '', ''),
(2, 2, 0, 2, 0, 143, '', ''),
(3, 2, 0, 3, 0, 144, '', ''),
(4, 3, 0, 1, 0, 145, '', ''),
(5, 3, 0, 2, 0, 146, '', ''),
(6, 3, 0, 3, 0, 147, '', ''),
(7, 3, 0, 4, 0, 148, '', ''),
(8, 4, 0, 1, 0, 149, '', ''),
(10, 4, 0, 2, 0, 150, '', ''),
(9, 4, 0, 3, 0, 151, '', ''),
(11, 5, 0, 1, 0, 152, '', ''),
(12, 5, 0, 2, 0, 153, '', ''),
(13, 12, 0, 0, 0, 313, '', ''),
(4, 51, 0, 1, 0, 315, '', ''),
(5, 51, 0, 2, 0, 316, '', ''),
(6, 51, 0, 3, 0, 317, '', ''),
(7, 51, 0, 4, 0, 318, '', ''),
(4, 51, 0, 1, 0, 319, '', ''),
(5, 51, 0, 2, 0, 320, '', ''),
(6, 51, 0, 3, 0, 321, '', ''),
(7, 51, 0, 4, 0, 322, '', ''),
(26, 61, 0, 1, 0, 323, '', ''),
(29, 61, 0, 2, 0, 324, '', ''),
(27, 61, 0, 3, 0, 325, '', ''),
(25, 61, 0, 4, 0, 326, '', ''),
(1, 33, 0, 0, 0, 327, '', ''),
(2, 33, 0, 1, 0, 328, '', ''),
(1, 32, 0, 0, 0, 331, '', ''),
(2, 32, 0, 1, 0, 332, '', ''),
(1, 35, 0, 0, 0, 333, '', ''),
(2, 35, 0, 1, 0, 334, '', ''),
(11, 36, 0, 1, 1, 336, '', ''),
(23, 10, 0, 0, 0, 337, '', ''),
(24, 10, 0, 1, 0, 338, '', ''),
(27, 10, 0, 2, 0, 339, '', ''),
(29, 10, 0, 3, 0, 340, '', ''),
(28, 10, 0, 4, 0, 341, '', ''),
(67, 13, 0, 0, 0, 342, '', ''),
(1, 1, 0, 0, 0, 346, '', ''),
(2, 1, 0, 1, 0, 347, '', ''),
(25, 10, 0, 7, 1, 368, '', ''),
(26, 10, 0, 11, 1, 369, '', ''),
(1, 1, 0, 0, 0, 370, '', ''),
(2, 1, 0, 1, 0, 371, '', ''),
(26, 1, 0, 2, 0, 372, '', ''),
(25, 1, 0, 3, 0, 373, '', ''),
(26, 38, 0, 3, 0, 377, '', ''),
(26, 38, 0, 3, 0, 381, '', ''),
(26, 38, 0, 3, 0, 385, '', ''),
(26, 38, 0, 3, 0, 389, '', ''),
(26, 38, 0, 3, 0, 393, '', ''),
(1, 35, 0, 0, 0, 394, '', ''),
(2, 35, 0, 1, 0, 395, '', ''),
(1, 35, 0, 0, 0, 396, '', ''),
(2, 35, 0, 1, 0, 397, '', ''),
(26, 35, 0, 2, 0, 398, '', ''),
(25, 35, 0, 3, 0, 399, '', ''),
(1, 47, 0, 0, 0, 400, '', ''),
(2, 47, 0, 1, 0, 401, '', ''),
(4, 49, 0, 0, 0, 402, '', ''),
(5, 49, 0, 1, 0, 403, '', ''),
(4, 48, 0, 0, 0, 404, '', ''),
(5, 48, 0, 1, 0, 405, '', ''),
(26, 38, 0, 0, 0, 406, '', ''),
(25, 38, 0, 1, 0, 407, '', ''),
(26, 46, 0, 0, 0, 408, '', ''),
(25, 46, 0, 1, 0, 409, '', ''),
(26, 50, 0, 0, 0, 410, '', ''),
(26, 50, 0, 0, 0, 412, '', ''),
(25, 50, 0, 1, 0, 413, '', ''),
(67, 41, 0, 0, 0, 414, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `step_fields`
--
ALTER TABLE `step_fields`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `step_fields`
--
ALTER TABLE `step_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=415;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
