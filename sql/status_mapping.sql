-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2017 at 08:45 PM
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
  `is_active` int(11) NOT NULL,
  `TAS_UID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `status_mapping`
--

INSERT INTO `status_mapping` (`id`, `workflow_id`, `step_from`, `step_to`, `step_condition`, `step_trigger`, `first_step`, `order_id`, `loc`, `is_active`, `TAS_UID`) VALUES
(5, 1, 2, 3, '{"claimStep":"Yes","permissionRequired":"1","departmentRequired":"1","errorStep":"1","nextWorkflow":"1","sendNotification":"Yes","autoAssign":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":"INTERMEDIATE"},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":"START"},"autoAssign":{"max_attempts":3,"time_unit":"HOURS","estimated_duration":0,"event_when":0,"when_occurs":"","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":"START"}},"receiveNotification":"Yes"}', '', 0, 2, '', 0, 2),
(6, 1, 3, 4, '{"claimStep":"Yes", "receiveNotification": "Yes" }', '', 0, 3, '', 0, 3),
(8, 1, 4, 5, '{"doAllocation":"Yes","permissionId":200}', '', 0, 4, '', 0, 4),
(12, 6, 7, 8, '', '{"moveTo":{"workflow_id":"16","workflow_to":null,"trigger_type":"step","step_to":"26"}}', 1, 0, '', 0, 7),
(13, 6, 8, 9, '', '', 0, 0, '', 0, 8),
(14, 6, 9, 0, '', '', 0, 0, '', 0, 9),
(15, 7, 10, 11, '{"autoAssign":"Yes"}', '', 1, 1, '', 0, 10),
(16, 7, 11, 12, '{"autoAssign":"Yes","task_properties":{"TAS_ASSIGN_TYPE":"MANUAL","TAS_DURATION":"1","TAS_TIMEUNIT":"DAYS","TAS_TYPE_DAY":"1","TAS_CALENDAR":"15","TAS_UID":"16","PRO_UID":"7","TAS_SEND_LAST_EMAIL":"FALSE","TAS_TRANSFER_FLY":"FALSE"}}', '', 0, 2, '', 0, 11),
(17, 7, 12, 13, '{"autoAssign":"Yes"}', '', 0, 3, '', 0, 12),
(18, 7, 13, 14, '{"autoAssign":"Yes"}', '', 0, 4, '', 0, 13),
(19, 7, 14, 0, '{"reject": "Yes"}', '', 0, 5, '', 0, 14),
(24, 1, 5, 0, '', '', 0, 6, '', 0, 5),
(25, 9, 7, 8, '', '', 1, 1, '', 0, 7),
(26, 9, 8, 9, '', '', 0, 2, '', 0, 8),
(27, 9, 9, 0, '', '', 0, 3, '', 0, 9),
(28, 10, 7, 8, '', '', 1, 1, '', 0, 7),
(29, 10, 8, 9, '', '', 0, 2, '', 0, 8),
(30, 10, 9, 0, '', '', 0, 3, '', 0, 9),
(31, 1, 1, 2, '{"autoAssign":"Yes"}', '', 1, 1, '', 0, 1),
(44, 44, 32, 33, '{"hold":"Yes","doAllocation":"Yes"}', '', 0, 3, '343.15625 127', 0, 32),
(45, 44, 33, 34, '{"reject":"Yes"}', '', 0, 4, '510.15625 131', 0, 33),
(46, 44, 34, 0, '[]', '', 0, 5, '659.15625 131', 0, 34),
(47, 44, 36, 32, '{"autoAssign":"Yes"}', '', 0, 2, '159.54716762619802 125.21038118928976', 0, 36),
(48, 44, 36, 37, '', '', 0, 6, '159.54716762619802 125.21038118928976', 0, 36),
(49, 44, 35, 36, '{"autoAssign":"Yes"}', '', 1, 1, '35.54716762619802 112.21038118928976', 0, 35),
(50, 44, 37, 33, '{"gateway":"Yes"}', '', 0, 8, '187.54716762619802 282.21038118928976', 0, 37),
(51, 8, 38, 39, '{"autoAssign":"Yes","sendNotification":"No","receiveNotification":"Yes","hold":"No","task_properties":{"TAS_ASSIGN_TYPE":"MANUAL","TAS_DURATION":"5","TAS_TIMEUNIT":"DAYS","TAS_TYPE_DAY":"1","TAS_CALENDAR":"15","TAS_UID":"51","PRO_UID":"8","TAS_SEND_LAST_EMAIL":"FALSE","TAS_TRANSFER_FLY":"FALSE"}}', '', 1, 0, '50 100', 0, 38),
(52, 8, 39, 40, '{"reject":"Yes","autoAssign":"Yes"}', '', 0, 1, '210 100', 0, 39),
(53, 8, 40, 41, '{"hold":"Yes"}', '', 0, 2, '370 100', 0, 40),
(54, 8, 41, 0, '[]', '', 0, 3, '530 100', 0, 41),
(58, 45, 46, 47, '{"autoAssign":"Yes"}', '', 1, 1, '76.66802518269145 113.47199254884111', 0, 46),
(59, 45, 47, 48, '{"sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:7:\\"subject\\";s:2:\\"TO\\";s:2:\\"to\\";s:2:\\"CC\\";s:2:\\"cc\\";s:3:\\"BCC\\";s:3:\\"bcc\\";}","evn_type":"INTERMEDIATE"},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""}},"receiveNotification":"Yes"}', '', 0, 2, '228.66802518269145 113.47199254884111', 0, 47),
(60, 45, 48, 49, '{"sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:7:\\"subject\\";s:2:\\"TO\\";s:2:\\"to\\";s:2:\\"CC\\";s:2:\\"cc\\";s:3:\\"BCC\\";s:3:\\"bcc\\";}","evn_type":"INTERMEDIATE"}},"receiveNotification":"Yes"}', '', 0, 3, '608.6680251826915 113.47199254884111', 0, 48),
(61, 45, 49, 0, '', '', 0, 4, '416.66802518269145 113.47199254884111', 0, 49),
(62, 47, 50, 51, '', '', 1, 1, '', 1, 50),
(63, 47, 51, 52, '{"sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"hold":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":"INTERMEDIATE"}},"receiveNotification":"Yes","hold":"Yes"}', '', 0, 2, '', 1, 51),
(64, 47, 52, 0, '', '', 0, 3, '', 1, 52),
(65, 16, 53, 54, '{"autoAssign":"Yes","sendNotification":"Yes","params":{"sendNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":"INTERMEDIATE"},"receiveNotification":{"max_attempts":3,"time_unit":"DAYS","estimated_duration":0,"event_when":0,"when_occurs":"SINGLE","action_params":"O:8:\\"stdClass\\":4:{s:7:\\"SUBJECT\\";s:0:\\"\\";s:2:\\"TO\\";s:0:\\"\\";s:2:\\"CC\\";s:0:\\"\\";s:3:\\"BCC\\";s:0:\\"\\";}","evn_type":""},"autoAssign":{"max_attempts":3,"time_unit":"HOURS","estimated_duration":0,"event_when":0,"when_occurs":"","action_params":"O:8:\\"stdClass\\":0:{}","evn_type":""}},"receiveNotification":"Yes"}', '', 1, 0, '50.15625 115', 1, 53),
(66, 16, 54, 55, '[]', '', 0, 1, '159.15625 119', 1, 54),
(67, 16, 55, 56, '[]', '', 0, 2, '323.15625 120', 1, 55),
(68, 16, 56, 57, '[]', '', 0, 3, '485.15625 123', 1, 56),
(69, 16, 57, 0, '[]', '', 0, 4, '646.15625 119', 1, 57);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `status_mapping`
--
ALTER TABLE `status_mapping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workflow_id` (`workflow_id`),
  ADD KEY `TAS_UID` (`TAS_UID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `status_mapping`
--
ALTER TABLE `status_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `status_mapping`
--
ALTER TABLE `status_mapping`
  ADD CONSTRAINT `FK_TAS_UID` FOREIGN KEY (`TAS_UID`) REFERENCES `task` (`TAS_UID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_workflow15` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`workflow_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
