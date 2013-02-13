-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 13, 2013 at 07:13 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `socialconnections`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `fname` varchar(32) NOT NULL,
  `lname` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`,`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`) VALUES
(1, 'IT', 'Management', 'z87654321', '', '598a4553150f08602cf82422bbbb3b07', 'awxGwfzLNFuNx89EeLiQTsyFg5BnNP3Q');

-- --------------------------------------------------------

--
-- Table structure for table `assesment`
--

CREATE TABLE IF NOT EXISTS `assesment` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `gid` mediumint(9) NOT NULL,
  `name` varchar(64) NOT NULL,
  `weight` tinyint(4) NOT NULL COMMENT 'Percentage of total marks for the module',
  PRIMARY KEY (`id`),
  UNIQUE KEY `composite` (`id`,`gid`),
  KEY `assesment_ibfk_1` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE IF NOT EXISTS `attendance` (
  `sid` mediumint(9) NOT NULL,
  `gid` mediumint(9) NOT NULL,
  `isLecture` tinyint(1) NOT NULL DEFAULT '1',
  `present` tinyint(1) NOT NULL DEFAULT '0',
  `excuse` text,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `excuse_viewed` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `composite` (`sid`,`gid`,`time`),
  KEY `attendance_ibfk_2` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE IF NOT EXISTS `class` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `did` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `composite` (`id`,`did`),
  KEY `class_ibfk_1` (`did`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `name`, `did`) VALUES
(1, 'DCOM3', 1),
(2, 'DNET3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE IF NOT EXISTS `department` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `headId` mediumint(9) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`, `headId`) VALUES
(1, 'Computing', 1);

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE IF NOT EXISTS `group` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `moid` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `moid` (`moid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `group_student`
--

CREATE TABLE IF NOT EXISTS `group_student` (
  `gid` mediumint(9) NOT NULL,
  `sid` mediumint(9) NOT NULL,
  PRIMARY KEY (`gid`,`sid`),
  KEY `group_student_ibfk_2` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lecturer`
--

CREATE TABLE IF NOT EXISTS `lecturer` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `fname` varchar(32) NOT NULL,
  `lname` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `did` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`,`email`),
  KEY `lecturer_ibfk_1` (`did`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `lecturer`
--

INSERT INTO `lecturer` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `did`) VALUES
(1, 'Jim', 'O''Dwyer', 'r25836947', 'jim.odwyer@cit.ie', '7dfa0466c9ad48b49df466421b19a464', 'wzkGKc8Vaj6aUoAGTzuXlV2ORi3DHcuF', 1),
(2, 'Mary', 'Davin', 'r12345678', 'mary.davin@cit.ie', '4265f0910b86350037ead6662d51699c', 'jYyPaiH52KxdCDnpPZ0eopACCFeFkbdT', 1);

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `credits` int(11) NOT NULL,
  `CRN` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `moduleoffering`
--

CREATE TABLE IF NOT EXISTS `moduleoffering` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `mid` mediumint(9) NOT NULL,
  `year` year(4) NOT NULL,
  `term` tinyint(4) NOT NULL,
  UNIQUE KEY `composite` (`id`,`mid`),
  KEY `moduleoffering_ibfk_1` (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `moduleoffering_lecturer`
--

CREATE TABLE IF NOT EXISTS `moduleoffering_lecturer` (
  `moid` mediumint(9) NOT NULL,
  `lid` mediumint(9) NOT NULL,
  UNIQUE KEY `composite` (`moid`,`lid`),
  KEY `moduleoffering_lecturer_ibfk_2` (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `url` varchar(256) NOT NULL,
  `gid` mediumint(9) NOT NULL,
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `composite` (`gid`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE IF NOT EXISTS `results` (
  `aid` mediumint(9) NOT NULL,
  `sid` mediumint(9) NOT NULL,
  `grade` tinyint(4) NOT NULL,
  `isDraft` tinyint(1) NOT NULL,
  UNIQUE KEY `composite` (`aid`,`sid`),
  KEY `results_ibfk_3` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `speedlimit`
--

CREATE TABLE IF NOT EXISTS `speedlimit` (
  `ip` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Rate Limiting for Authentication';

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE IF NOT EXISTS `student` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `fname` varchar(32) NOT NULL,
  `lname` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `cid` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`,`email`),
  UNIQUE KEY `composite` (`id`,`cid`),
  KEY `student_ibfk_1` (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `cid`) VALUES
(1, 'Gary', 'Brady', 'r00012345', 'gary.brady@mycit.ie', '1ceafbd665b7bccfef45ae8e573335da', 'IrUN710qV0GG6HmPOj984eIrwFcC4OCK', 1),
(2, 'Preslav', 'Petkov', 'r00073209', 'preslav.petkov@mycit.ie', '8471cd440dfd9cf75402d82c8b820ed9', '1DYrhbckFt5uVB5n9N6lqLN7ppxKkwMo', 1),
(3, 'Rouslan', 'Placella', 'r00077389', 'rouslan.placella@mycit.ie', '25318b182c631b6995b7268c3251aab8', 'NqK3zDnUQAeh6pR3DHFV4NCjKLdT6CRp', 1);

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE IF NOT EXISTS `token` (
  `uid` mediumint(9) NOT NULL,
  `token` char(64) NOT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(64) NOT NULL,
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Remote Authentication Tokens';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assesment`
--
ALTER TABLE `assesment`
  ADD CONSTRAINT `assesment_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`gid`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`sid`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_1` FOREIGN KEY (`did`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group`
--
ALTER TABLE `group`
  ADD CONSTRAINT `group_ibfk_1` FOREIGN KEY (`moid`) REFERENCES `moduleoffering` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_student`
--
ALTER TABLE `group_student`
  ADD CONSTRAINT `group_student_ibfk_2` FOREIGN KEY (`sid`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_student_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lecturer`
--
ALTER TABLE `lecturer`
  ADD CONSTRAINT `lecturer_ibfk_1` FOREIGN KEY (`did`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `moduleoffering`
--
ALTER TABLE `moduleoffering`
  ADD CONSTRAINT `moduleoffering_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `moduleoffering_lecturer`
--
ALTER TABLE `moduleoffering_lecturer`
  ADD CONSTRAINT `moduleoffering_lecturer_ibfk_1` FOREIGN KEY (`moid`) REFERENCES `moduleoffering` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `moduleoffering_lecturer_ibfk_2` FOREIGN KEY (`lid`) REFERENCES `lecturer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`gid`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `assesment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `results_ibfk_3` FOREIGN KEY (`sid`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`cid`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
