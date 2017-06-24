-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2017 at 05:20 PM
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
-- Table structure for table `status_mapping`
--

CREATE TABLE `status_mapping` (
  `id` int(11) NOT NULL,
  `workflow_id` int(11) NOT NULL,
  `step_from` int(11) NOT NULL,
  `step_to` int(11) NOT NULL,
  `step_condition` text NOT NULL,
  `step_trigger` varchar(255) NOT NULL,
  `first_step` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `loc` varchar(255) NOT NULL,
  `is_active` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `status_mapping`
--

INSERT INTO `status_mapping` (`id`, `workflow_id`, `step_from`, `step_to`, `step_condition`, `step_trigger`, `first_step`, `order_id`, `loc`, `is_active`) VALUES
(5, 1, 2, 3, '{"claimStep":"Yes","permissionRequired":"1","departmentRequired":"1","errorStep":"1","nextWorkflow":"1","sendNotification":"Yes","autoAssign":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":"INTERMEDIATE"},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":"START"},"autoAssign":{"max_attempts":3,"time_unit":"HOURS","estimated_duration":0,"event_when":0,"when_occurs":"","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":"START"}},"receiveNotification":"Yes"}', '', 0, 2, '', 0),
(6, 1, 3, 4, '{"claimStep":"Yes", "receiveNotification": "Yes" }', '', 0, 3, '', 0),
(8, 1, 4, 5, '{"doAllocation":"Yes","permissionId":200}', '', 0, 4, '', 0),
(12, 6, 7, 8, '', '{"moveTo":{"workflow_id":"16","workflow_to":null,"trigger_type":"step","step_to":"26"}}', 1, 0, '', 0),
(13, 6, 8, 9, '', '', 0, 0, '', 0),
(14, 6, 9, 0, '', '', 0, 0, '', 0),
(15, 7, 10, 11, '{"autoAssign":"Yes"}', '', 1, 1, '', 0),
(16, 7, 11, 12, '{"autoAssign":"Yes"}', '', 0, 2, '', 0),
(17, 7, 12, 13, '{"autoAssign":"Yes"}', '', 0, 3, '', 0),
(18, 7, 13, 14, '{"autoAssign":"Yes"}', '', 0, 4, '', 0),
(19, 7, 14, 0, '{"reject": "Yes"}', '', 0, 5, '', 0),
(24, 1, 5, 0, '', '', 0, 6, '', 0),
(25, 9, 7, 8, '', '', 1, 1, '', 0),
(26, 9, 8, 9, '', '', 0, 2, '', 0),
(27, 9, 9, 0, '', '', 0, 3, '', 0),
(28, 10, 7, 8, '', '', 1, 1, '', 0),
(29, 10, 8, 9, '', '', 0, 2, '', 0),
(30, 10, 9, 0, '', '', 0, 3, '', 0),
(31, 1, 1, 2, '{"autoAssign":"Yes"}', '', 1, 1, '', 0),
(44, 44, 32, 33, '{"hold":"Yes","doAllocation":"Yes"}', '', 0, 3, '343.15625 127', 0),
(45, 44, 33, 34, '{"reject":"Yes"}', '', 0, 4, '510.15625 131', 0),
(46, 44, 34, 0, '[]', '', 0, 5, '659.15625 131', 0),
(47, 44, 36, 32, '{"autoAssign":"Yes"}', '', 0, 2, '159.54716762619802 125.21038118928976', 0),
(48, 44, 36, 37, '', '', 0, 6, '159.54716762619802 125.21038118928976', 0),
(49, 44, 35, 36, '{"autoAssign":"Yes"}', '', 1, 1, '35.54716762619802 112.21038118928976', 0),
(50, 44, 37, 33, '{"gateway":"Yes"}', '', 0, 8, '187.54716762619802 282.21038118928976', 0),
(51, 8, 38, 39, '{"autoAssign":"Yes","sendNotification":"No","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"hold":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":"INTERMEDIATE"},"autoAssign":{"max_attempts":3,"time_unit":"HOURS","estimated_duration":0,"event_when":0,"when_occurs":"","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":"INTERMEDIATE"}},"receiveNotification":"Yes","hold":"No"}', '', 1, 0, '50 100', 0),
(52, 8, 39, 40, '{"reject":"Yes","autoAssign":"Yes"}', '', 0, 1, '210 100', 0),
(53, 8, 40, 41, '{"hold":"Yes"}', '', 0, 2, '370 100', 0),
(54, 8, 41, 0, '[]', '', 0, 3, '530 100', 0),
(58, 45, 46, 47, '{"autoAssign":"Yes"}', '', 1, 1, '76.66802518269145 113.47199254884111', 0),
(59, 45, 47, 49, '{"sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:7:\\"subject\\";s:2:\\"TO\\";s:2:\\"to\\";s:2:\\"CC\\";s:2:\\"cc\\";s:3:\\"BCC\\";s:3:\\"bcc\\";}","evn_type":"INTERMEDIATE"},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""}},"receiveNotification":"Yes"}', '', 0, 2, '228.66802518269145 113.47199254884111', 0),
(60, 45, 49, 48, '{"sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:7:\\"subject\\";s:2:\\"TO\\";s:2:\\"to\\";s:2:\\"CC\\";s:2:\\"cc\\";s:3:\\"BCC\\";s:3:\\"bcc\\";}","evn_type":"INTERMEDIATE"}},"receiveNotification":"Yes"}', '', 0, 3, '608.6680251826915 113.47199254884111', 0),
(61, 45, 48, 0, '', '', 0, 4, '416.66802518269145 113.47199254884111', 0),
(62, 47, 50, 51, '', '', 1, 1, '', 1),
(63, 47, 51, 52, '{"sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"hold":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":"INTERMEDIATE"}},"receiveNotification":"Yes","hold":"Yes"}', '', 0, 2, '', 1),
(64, 47, 52, 0, '', '', 0, 3, '', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `status_mapping`
--
ALTER TABLE `status_mapping`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `status_mapping`
--
ALTER TABLE `status_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
