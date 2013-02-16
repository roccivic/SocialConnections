-- phpMyAdmin SQL Dump
-- version 4.0.0-beta1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2013 at 11:50 AM
-- Server version: 5.5.29-0ubuntu0.12.10.1
-- PHP Version: 5.4.6-1ubuntu1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `socialconnections`
--

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`) VALUES
(1, 'IT', 'Management', 'z87654321', '', '598a4553150f08602cf82422bbbb3b07', 'awxGwfzLNFuNx89EeLiQTsyFg5BnNP3Q');

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `name`, `did`) VALUES
(1, 'DCOM3', 1),
(2, 'DNET3', 1);

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`, `headId`) VALUES
(1, 'Computing', 1);

--
-- Dumping data for table `lecturer`
--

INSERT INTO `lecturer` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `did`) VALUES
(1, 'Jim', 'O''Dwyer', 'r25836947', 'jim.odwyer@cit.ie', '7dfa0466c9ad48b49df466421b19a464', 'wzkGKc8Vaj6aUoAGTzuXlV2ORi3DHcuF', 1),
(2, 'Mary', 'Davin', 'r12345678', 'mary.davin@cit.ie', '4265f0910b86350037ead6662d51699c', 'jYyPaiH52KxdCDnpPZ0eopACCFeFkbdT', 1);

--
-- Dumping data for table `speedlimit`
--

INSERT INTO `speedlimit` (`ip`, `timestamp`) VALUES
('127.0.0.1', '2013-02-16 11:44:04'),
('127.0.0.1', '2013-02-16 11:44:09'),
('127.0.0.1', '2013-02-16 11:44:12'),
('109.255.78.105', '2013-02-16 11:44:17');

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `cid`, `hasGrant`) VALUES
(1, 'Gary', 'Brady', 'r00012345', 'gary.brady@mycit.ie', '1ceafbd665b7bccfef45ae8e573335da', 'IrUN710qV0GG6HmPOj984eIrwFcC4OCK', 1, 0),
(2, 'Preslav', 'Petkov', 'r00073209', 'preslav.petkov@mycit.ie', '8471cd440dfd9cf75402d82c8b820ed9', '1DYrhbckFt5uVB5n9N6lqLN7ppxKkwMo', 1, 0),
(3, 'Rouslan', 'Placella', 'r00077389', 'rouslan.placella@mycit.ie', '25318b182c631b6995b7268c3251aab8', 'NqK3zDnUQAeh6pR3DHFV4NCjKLdT6CRp', 1, 0);

--
-- Dumping data for table `threshold`
--

INSERT INTO `threshold` (`id`, `overall`, `labs`) VALUES
(0, 50, 60);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
