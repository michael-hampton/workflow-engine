-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2017 at 08:46 PM
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
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `field_id` int(11) NOT NULL,
  `field_identifier` varchar(100) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `is_readonly` int(11) NOT NULL,
  `field_type` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `data_type` int(11) NOT NULL,
  `field_class` varchar(255) NOT NULL,
  `default_value` varchar(255) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `maxlength` int(11) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `validation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`field_id`, `field_identifier`, `field_name`, `is_readonly`, `field_type`, `label`, `data_type`, `field_class`, `default_value`, `placeholder`, `maxlength`, `type`, `validation`) VALUES
(1, 'username', 'form[username]', 0, 1, 'Username', 0, '', '', '', 0, '', ''),
(2, 'password', 'form[password]', 0, 1, 'Password', 0, '', '', '', 0, '', ''),
(3, 'confirm', 'form[confirm]', 0, 1, 'Confirm Password', 0, '', '', '', 0, '', ''),
(4, 'firstName', 'form[firstName]', 0, 1, 'First Name:', 0, '', '', '', 0, '', ''),
(5, 'surname', 'form[surname]', 0, 1, 'Surname', 0, '', '', '', 0, '', ''),
(6, 'email', 'form[email]', 0, 1, 'Email:', 0, '', '', '', 0, '', ''),
(7, 'address', 'form[address]', 0, 1, 'Address:', 0, '', '', '', 0, '', ''),
(8, 'reason', 'form[reason]', 0, 1, 'Reason:', 31, '', '', '', 0, '', ''),
(9, 'notes', 'form[notes]', 0, 2, 'Notes:', 33, '', '', '', 0, '', ''),
(10, 'code', 'form[code]', 0, 1, 'Code:', 32, '', '', '', 0, '', ''),
(11, 'hasCertificate', 'form[hasCertificate]', 0, 3, 'Has Certificate', 2, '', '', '', 0, '', ''),
(12, 'qualityApproved', 'form[qualityApproved]', 0, 3, 'Quality Approved:', 30, '', '', '', 0, '', ''),
(13, 'fileupload', 'fileUpload', 0, 4, 'Upload File', 0, 'form-control', '', '', NULL, '', ''),
(15, 'testField', 'testField', 0, 1, 'testField', 0, '', '', '', 0, '', ''),
(18, 'testSelect', 'testSelect', 0, 3, 'test select', 0, '', '', '', 0, '', ''),
(19, 'testSelect2', 'testSelect2', 0, 3, 'test select', 0, '', '', '', 0, '', ''),
(20, 'testSelect3', 'testSelect3', 0, 3, 'test select', 0, '', '', '', 0, '', ''),
(21, 'testSelect4', 'testSelect4', 0, 3, 'test select', 0, '', '', '', 0, '', ''),
(22, 'testSelect8', 'testSelect8', 0, 3, 'test select 8', 3, '', '', '', 0, '', ''),
(23, 'location', 'form[location]', 0, 1, 'Location', 0, '', '', '', 0, '', ''),
(24, 'batch', 'form[batch]', 0, 1, 'Batch', 0, '', '', '', 0, '', ''),
(25, 'description', 'form[description]', 0, 1, 'Description', 23, 'form-control', '', 'Description', NULL, '', ''),
(26, 'name', 'form[name]', 0, 1, 'Name', 0, 'form-control', 'default value', 'Name', 22, '', ''),
(27, 'dueDate', 'form[dueDate]', 0, 1, 'Due Date', 0, 'form-control', '', 'Due Date', NULL, '', ''),
(28, 'sampleRef', 'form[sampleRef]', 0, 1, 'Sample Ref', 0, 'form-control', '', 'Sample Ref', NULL, '', ''),
(29, 'priority', 'form[priority]', 0, 3, 'Priority', 12, 'form-control', '', 'Priority', NULL, '', ''),
(55, 'button-1492446334390', 'button-1492446334390', 0, 5, 'Test Button', 0, 'button-input btn btn-danger', 'Test Button', '', NULL, '', ''),
(56, 'text-1492446365689', 'text-1492446365689', 0, 1, 'Test Text Field', 0, 'form-control', '', 'Placeholder', 29, '', ''),
(57, 'textarea-1492446401312', 'textarea-1492446401312', 0, 2, 'Text Area', 0, 'form-control', '', 'Textarea placeholder', 100, '', ''),
(58, 'checkbox-1492451234020', 'checkbox-1492451234020', 0, 6, 'Checkbox', 0, 'checkbox', 'This is a checkbox', '', NULL, '', ''),
(59, 'select-1492446430488', 'select-1492446430488', 0, 3, 'Test Select', 10, 'form-control', '', '', NULL, '', ''),
(60, 'date-1492451179250', 'date-1492451179250', 0, 8, 'Date Field', 0, 'form-control', 'value', '', NULL, '', ''),
(61, 'file-1492446959898', 'file-1492446959898', 0, 4, 'File Upload', 0, 'form-control', '', '', NULL, '', ''),
(65, 'form[description]', 'form[description]', 0, 1, 'Description', 0, 'form-control', '', 'Description', NULL, '', ''),
(66, 'sampleRef', 'form[sampleRef]', 0, 1, 'Sample Ref', 0, 'form-control', '', 'Sample Ref', NULL, '', ''),
(67, 'fileupload', 'fileUpload', 0, 4, 'Upload File', 0, 'form-control', '', '', NULL, '', ''),
(68, 'description', 'form[description]', 0, 1, 'Description', 0, 'form-control', '', 'Description', NULL, '', ''),
(69, 'sampleRef', 'form[sampleRef]', 0, 1, 'Sample Ref', 0, 'form-control', '', 'Sample Ref', NULL, '', ''),
(70, 'fileupload', 'fileUpload', 0, 4, 'Upload File', 0, 'form-control', '', '', NULL, '', ''),
(71, 'text-1493836074201', 'text-1493836074201', 0, 1, 'Text Field', 0, 'form-control', '', '', NULL, 'int', 'url'),
(72, 'select-1493836573113', 'select-1493836573113', 0, 3, 'Select', 13, 'form-control', '', 'Test Select', NULL, '', ''),
(99, 'form[name]', 'form[name]', 0, 1, 'Name', 0, 'form-control', 'default value', 'Name', 22, '', ''),
(100, 'mikeid', 'select-mike', 0, 3, 'Test Select Mike', 0, 'form-control', '', 'placeholder', NULL, '', ''),
(105, 'select-mike2', 'select-mike2', 0, 3, 'Select', 29, 'form-control', '', 'placeholder 2', NULL, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`field_id`),
  ADD KEY `field_type` (`field_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `fields`
--
ALTER TABLE `fields`
  ADD CONSTRAINT `fk_field_type` FOREIGN KEY (`field_type`) REFERENCES `field_types` (`field_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
