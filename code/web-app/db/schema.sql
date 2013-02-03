-- phpMyAdmin SQL Dump
-- version 4.0.0-alpha2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2013 at 03:03 PM
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
CREATE DATABASE `socialconnections` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `socialconnections`;

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
-- Table structure for table `token`
--

CREATE TABLE IF NOT EXISTS `token` (
  `uid` mediumint(9) NOT NULL,
  `token` char(64) NOT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(64) NOT NULL,
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Remote Authentication Tokens';

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` mediumint(9) NOT NULL AUTO_INCREMENT,
  `fname` varchar(32) NOT NULL,
  `lname` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`,`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='System Users' AUTO_INCREMENT=7 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `type`) VALUES
(1, 'Super', 'User', 'root', '', 'b5b4468dd1b6fccfd1dbdea6de82cae0', 'bPvg039ulLLZQWvxGFaDW7Sjy8Da69Eh', 4),
(2, 'Rouslan', 'Placella', 'r00077389', 'rouslan.placella@mycit.ie', '25318b182c631b6995b7268c3251aab8', 'NqK3zDnUQAeh6pR3DHFV4NCjKLdT6CRp', 1),
(3, 'Mary', 'Davin', 'r12345678', 'mary.davin@cit.ie', '4265f0910b86350037ead6662d51699c', 'jYyPaiH52KxdCDnpPZ0eopACCFeFkbdT', 2),
(4, 'Gary', 'Brady', 'r00012345', 'gary.brady@mycit.ie', '1ceafbd665b7bccfef45ae8e573335da', 'IrUN710qV0GG6HmPOj984eIrwFcC4OCK', 1),
(5, 'Preslav', 'Petkov', 'r00072727', 'preslav.petkov@mycit.ie', '8471cd440dfd9cf75402d82c8b820ed9', '1DYrhbckFt5uVB5n9N6lqLN7ppxKkwMo', 1),
(6, 'IT', 'Management', 'z87654321', '', '598a4553150f08602cf82422bbbb3b07', 'awxGwfzLNFuNx89EeLiQTsyFg5BnNP3Q', 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
