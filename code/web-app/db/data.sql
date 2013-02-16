-- phpMyAdmin SQL Dump
-- version 4.0.0-beta1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2013 at 02:20 PM
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
(1, 'IT', 'Management', 'admin', 'admin@cit.ie', '598a4553150f08602cf82422bbbb3b07', 'awxGwfzLNFuNx89EeLiQTsyFg5BnNP3Q');


--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`, `headId`) VALUES
(1, 'Computing', 1),
(2, 'Tourism and Hospitality', 4),
(3, 'Physics', 6);

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `name`, `did`) VALUES
(1, 'DCOM1', 1),
(2, 'DCOM2', 1),
(3, 'DCOM3', 1),
(4, 'DNET1', 1),
(5, 'DNET2', 1),
(6, 'DNET3', 1),
(7, 'COOK1', 2),
(8, 'COOK2', 2),
(9, 'PHYS1', 3);

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`id`, `name`, `credits`, `CRN`, `did`) VALUES
(1, 'Databases 1', 5, '123456', 1),
(2, 'Databases 2', 5, '123457', 1),
(3, 'Databases 3', 5, '123458', 1),
(4, 'Requirements Engineering', 5, '123459', 1),
(5, 'Software Engineering', 5, '123657', 1),
(6, 'Work Placement', 10, '123654', 1),
(7, 'Baking 1', 5, '456871', 2),
(8, 'Baking 2', 5, '654982', 2),
(9, 'Cooking 1', 5, '15948', 2),
(10, 'Cooking 2', 5, '26159', 2),
(11, 'Physics 1', 5, '35724', 3),
(12, 'Chemistry 1', 5, '15368', 3);

--
-- Dumping data for table `moduleoffering`
--

INSERT INTO `moduleoffering` (`id`, `mid`, `year`, `term`) VALUES
(1, 1, 2012, 1),
(2, 1, 2012, 1),
(3, 4, 2012, 1),
(4, 4, 2012, 1),
(5, 2, 2012, 1),
(6, 2, 2012, 1),
(7, 5, 2012, 1),
(8, 5, 2012, 1),
(9, 3, 2012, 1),
(10, 6, 2012, 1),
(11, 7, 2012, 1),
(12, 9, 2012, 1),
(13, 8, 2012, 1),
(14, 10, 2012, 1),
(15, 11, 2012, 1),
(16, 12, 2012, 1);

--
-- Dumping data for table `group`
--

INSERT INTO `group` (`id`, `name`, `moid`) VALUES
(1, 'Databases 1 DCOM1', 1),
(2, 'Databases 1 DNET1', 2),
(3, 'ReqEng DCOM1', 3),
(4, 'ReqEng DNET1', 4),
(5, 'Databases 2 DCOM2', 5),
(6, 'Databases 2 DNET2', 6),
(7, 'SoftEng DCOM2', 7),
(8, 'SoftEng DNET2', 8),
(9, 'Databases 3 DCOM3 DNET3', 9),
(10, 'WorkPlacement DCOM3 DNET3', 10),
(11, 'Baking 1 COOK1', 11),
(12, 'Cooking 1 COOK1', 12),
(13, 'Baking 2 COOK2', 13),
(14, 'Cooking 2 COOK2', 14),
(15, 'Physics 1 PHYS1', 15),
(16, 'Chemistry 1 PHYS1', 16);

--
-- Dumping data for table `lecturer`
--

INSERT INTO `lecturer` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `did`) VALUES
(1, 'Jim', 'O''Dwyer', 'r25836947', 'jim.odwyer@cit.ie', '7dfa0466c9ad48b49df466421b19a464', 'wzkGKc8Vaj6aUoAGTzuXlV2ORi3DHcuF', 1),
(2, 'Mary', 'Davin', 'r12345678', 'mary.davin@cit.ie', '4265f0910b86350037ead6662d51699c', 'jYyPaiH52KxdCDnpPZ0eopACCFeFkbdT', 1),
(3, 'Amelia', 'Cheung', 'r67530031', 'amelia.cheung@mycit.ie', '323743b1d970adf4c4efa8fc816bb06f', 'Dg7ux9FOBZG95qeTI9bawWACrVjMQpfu', 1),
(4, 'Kennith', 'Goodrum', 'r94039688', 'kennith.goodrum@mycit.ie', '00fc6e6988c78ccc9415ed5b401f4cf3', 'FmZdwE17DHhJ7wDQFO0bKBNcw7YnweRb', 2),
(5, 'Melony', 'Pappalardo', 'r70761669', 'melony.pappalardo@mycit.ie', '63c2b5dfcfd75a0fdc8adf3545e57a50', 'AQp6vqe98vSg1v6Hk6S5HGheNgBjusv5', 2),
(6, 'Johnathon', 'Woltz', 'r30074709', 'johnathon.woltz@mycit.ie', '480fc7b89fdf30230c1dc70244755c6c', 'jUcPlqYtWQKYmQFHXxMEd4S1kukPXQUg', 3),
(7, 'Margit', 'Strohm', 'r86623630', 'margit.strohm@mycit.ie', '4cd1f37f4d6e35d98336f9ac799b4288', 'K655w4zsUjqha6Y7DKMROFS99dY63SnN', 3);

--
-- Dumping data for table `moduleoffering_lecturer`
--

INSERT INTO `moduleoffering_lecturer` (`moid`, `lid`) VALUES
(7, 1),
(8, 1),
(10, 1),
(3, 2),
(4, 2),
(1, 3),
(2, 3),
(5, 3),
(6, 3),
(9, 3),
(11, 4),
(14, 4),
(12, 5),
(13, 5),
(15, 6),
(16, 7);

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `fname`, `lname`, `username`, `email`, `password`, `salt`, `cid`, `hasGrant`) VALUES
(1, 'Gary', 'Brady', 'r00012345', 'gary.brady@mycit.ie', '1ceafbd665b7bccfef45ae8e573335da', 'IrUN710qV0GG6HmPOj984eIrwFcC4OCK', 6, 0),
(2, 'Preslav', 'Petkov', 'r00073209', 'preslav.petkov@mycit.ie', '8471cd440dfd9cf75402d82c8b820ed9', '1DYrhbckFt5uVB5n9N6lqLN7ppxKkwMo', 3, 0),
(3, 'Rouslan', 'Placella', 'r00077389', 'rouslan.placella@mycit.ie', '25318b182c631b6995b7268c3251aab8', 'NqK3zDnUQAeh6pR3DHFV4NCjKLdT6CRp', 3, 0),
(4, 'Katia', 'Acy', 'r04692441', 'katia.acy@mycit.ie', 'b76b9a07cfe81820f1ff9306948c15fa', 'OXXyFOKilo3tbZvWOvZL6rQ4ym1yPwHE', 6, 0),
(5, 'Irwin', 'Rabin', 'r17764298', 'irwin.rabin@mycit.ie', '1e2fdaaf4325642bc4f88aecd3d753b6', 'tFc9tXrPmvixvOtkkt5qUWusiv081IMv', 3, 0),
(6, 'Muoi', 'Macintosh', 'r03467252', 'muoi.macintosh@mycit.ie', '5d58cba9e9c6bf5f527264aa5bf20d2c', 'oZESX6HjB0R7Olr8OxyJt3bLycTAVG5j', 6, 0),
(7, 'Salvador', 'Sergi', 'r25534535', 'salvador.sergi@mycit.ie', 'e304f93ff7707140f96c56e92d96c580', 'GJbDPTWrTOzH90QYxpH1sTM06GB1nHl3', 6, 1),
(8, 'Richard', 'Stgeorge', 'r28442556', 'richard.stgeorge@mycit.ie', '7bf0147881d42cf1e45ba01f13efada2', 'rxGgqDIjsh1BiRAPgiRIcDJiklkH2FKt', 6, 0),
(9, 'Walton', 'Genao', 'r75087677', 'walton.genao@mycit.ie', '42d30dc38d5fef9ee35d29b3933676e8', 'crKD4tWwKX82PJS51JOdnywHTQpVw9pJ', 3, 0),
(10, 'Stevie', 'Montes', 'r00692743', 'stevie.montes@mycit.ie', '4907fc3e9cdda797d498178fc8302bd0', 'B9mFCicnglq55ja73ZlqxR8qIxmeHLXi', 3, 0),
(11, 'Fran', 'Luu', 'r46422254', 'fran.luu@mycit.ie', '3a1263a44f91f1b7a9e1398a6351c9b6', 'VkYyCbVTxmYCF9JJ84aGWj6ERtTyfQRa', 6, 0),
(12, 'Dexter', 'Laporte', 'r38704014', 'dexter.laporte@mycit.ie', '1f8b9607a85ec994ab601b06dfe03894', 'aQJN1FGy1FbHOVqX0ADWTKBKdujskbDv', 1, 1),
(13, 'Marina', 'Cropp', 'r68252232', 'marina.cropp@mycit.ie', '7347eb5e21191c6f6b22660720076487', '1mj310C3FNKuIbsJM6FGQgq4KKx5WaBX', 1, 0),
(14, 'Kenisha', 'Odowd', 'r26073833', 'kenisha.odowd@mycit.ie', '83c198f18ba4aaf2ac8a2af31e9dafdd', 'xU0zVCCAqm59yxSkDy0uOryzb5E8gg5O', 1, 0),
(15, 'Darrel', 'Fallis', 'r16862896', 'darrel.fallis@mycit.ie', '5a60e62d6bd979f07a0b076ea46333a0', 'b6n6JZH9mMjUkbeYJfsyG17S7M0o36ce', 1, 0),
(16, 'Kurt', 'Sable', 'r28105516', 'kurt.sable@mycit.ie', 'd32fcb4b8e312069f5ef79314abd0a79', 'czkVz25WOoQ9A58kkBS1C0TKNT8QZl4c', 3, 0),
(17, 'Cyrus', 'Rink', 'r74250677', 'cyrus.rink@mycit.ie', '78a41faa7c28d9ef1c44ee66120f079c', 'Vp8urdqgChpcnxxH8pJLpCwcwF2w07IV', 2, 1),
(18, 'Leigh', 'Hunsucker', 'r99206937', 'leigh.hunsucker@mycit.ie', 'a53caed9e82043a3e231fe68196137d5', 'wQqY4ReG9ETwcqekQX6gzCs6hvCiDkea', 2, 0),
(19, 'Esteban', 'Carriere', 'r93704218', 'esteban.carriere@mycit.ie', '2dbcf1df5d67e610eb74067f09ad851f', 'bE8fwmWG0Qccgrx6oEmYhP4ylHRZ259d', 2, 0),
(20, 'Cierra', 'Scurry', 'r65016755', 'cierra.scurry@mycit.ie', '4245a9b94ea05b9ecdb22faf5df2ad80', 'KhthDpXEf9RwApDZ30YkQ3TbKKbMPk0z', 2, 1),
(21, 'Roger', 'Shupe', 'r60680365', 'roger.shupe@mycit.ie', '1b04056dbd420bcd3e8307cfe601e518', 'BtRfSOU8XLFyaiyeiwy8zrkkcv71Q7Br', 4, 0),
(22, 'Angele', 'Lumley', 'r49706303', 'angele.lumley@mycit.ie', 'a20b7deebcc6867f8333b11e6eb64296', 'AsHtgBBenhNxzlLSSk0sLlMXRTZH0B9B', 4, 0),
(23, 'Cornelius', 'Seamon', 'r05147534', 'cornelius.seamon@mycit.ie', '1cf003345971bf69e2bb9b6f4fd493f2', '4Q4krGzPXmmxI8pAtq3fLPdDJckKOulS', 4, 0),
(24, 'Althea', 'Lockwood', 'r83021754', 'althea.lockwood@mycit.ie', '1d29abc602393c4a84862507db1dff7f', 'kqdM7MB59YCS71sArwPdm2Q5fbQ3FcW0', 4, 0),
(25, 'Edison', 'Kennamer', 'r78867042', 'edison.kennamer@mycit.ie', 'bd1d724c67d79841bc0be91bc480c3cc', 'C9NKVoP5nrXvtq5UWV7iYYoe9ehPqdP3', 4, 0),
(26, 'Issac', 'Estevez', 'r80025813', 'issac.estevez@mycit.ie', '7d0e20d7a84febe84c4a8abe150ac77a', 'nCNi1Dop4lUxL0sHWA0Uyo8HCqx3En71', 5, 1),
(27, 'Alma', 'Lisenby', 'r04001002', 'alma.lisenby@mycit.ie', '2ff44e281f667aa882462020bd276fda', '0Uk1yIrD3maPmDxjdxeLWntyO0CsnJuo', 5, 0),
(28, 'Holli', 'Cashin', 'r67944658', 'holli.cashin@mycit.ie', '41f63b765a41f5a3ba98e528b3fe17a9', 'EPqcxRPBd0rADYURw8Csv61j7DMvngT1', 5, 0),
(29, 'Marilee', 'Lindstrom', 'r51788844', 'marilee.lindstrom@mycit.ie', '0571abd3042a9c6e639e974cb6dda090', '6jeEa4fo4HZIFTAb1cDwjEQqiCVFTOHZ', 5, 1),
(30, 'Annabell', 'Killough', 'r04071452', 'annabell.killough@mycit.ie', 'ff59a4d989a3f8bee1571ec8c467aea5', '7VDiZTG4AFNgzorAB57UKXk2AgIu4puc', 5, 0),
(31, 'George', 'Western', 'r11802496', 'george.western@mycit.ie', '170766c052de88a5d7c748d28eee4568', 'l8ul1bqCReSqCk0dq87a5rdGHVaMlEYG', 7, 0),
(32, 'Nelly', 'Enterline', 'r46680894', 'nelly.enterline@mycit.ie', '12652942d530bcce077d676d621baeeb', 'Mt2OEsqwGjWiEXv45Dfb5tRNo1zKGyrs', 7, 0),
(33, 'Rob', 'Dargan', 'r37069630', 'rob.dargan@mycit.ie', '5eb05f17dd1d64847a7494d20e108847', '1tgGVGdC0aUE7qJd3Yp8rhVQivAZ32r5', 7, 0),
(34, 'Meghann', 'Watt', 'r93012221', 'meghann.watt@mycit.ie', '423f1750c8bf2e8153d9538cd263edff', 'vHLroZ3p9Y3goMurLTAcav3t1Et4GU9b', 7, 0),
(35, 'Clayton', 'Haffey', 'r03022035', 'clayton.haffey@mycit.ie', '43d32b3a40e37da58c93806d13b7bc95', 'CVC0UFp3Dtk2gPu1J4eUAhoBVRGCLPNn', 8, 0),
(36, 'Zachariah', 'Orman', 'r49144393', 'zachariah.orman@mycit.ie', '4d689384857325314e5cba16f9ec843c', 'LqoF6OJKi4MyTgzClOxW5Vy1MeDy4rWP', 8, 0),
(37, 'Zoraida', 'Maag', 'r32370177', 'zoraida.maag@mycit.ie', '2b6cec423fd11e0095576328a5c5cd78', 'RlvXaeIsju0dLAQ7pn3uiBw5QaDUBAJt', 8, 0),
(38, 'Jasper', 'Frederic', 'r32595004', 'jasper.frederic@mycit.ie', '4268a1c352488dfb9315b36ffc3ca176', 'Vfr5u9yNEz0p9QwzdA4wbAB2LfWnQGQL', 8, 0),
(39, 'Cleta', 'Ewing', 'r50626709', 'cleta.ewing@mycit.ie', '27bbadb8487de7a5c688de8a9f9401a7', 'br73McNrcVHOC51sRBK20TrMW0QjDq5P', 9, 0),
(40, 'Thu', 'Pareja', 'r81460382', 'thu.pareja@mycit.ie', '1e823819a86595c0210717a5224e584a', 'ScSFpG7BCOqeUsGM3rP4lgQhhHAU8FJ0', 9, 0),
(41, 'Quintin', 'Patague', 'r35333467', 'quintin.patague@mycit.ie', '1970b933ba1b7e02aa7a8a3f9d7c641e', 'SCGhjNTWCjawLRiPj7TFoKXFsxAAdkB6', 9, 0);


--
-- Dumping data for table `group_student`
--

INSERT INTO `group_student` (`gid`, `sid`) VALUES
(9, 1),
(10, 1),
(9, 2),
(10, 2),
(9, 3),
(10, 3),
(9, 4),
(10, 4),
(9, 5),
(10, 5),
(9, 6),
(10, 6),
(9, 7),
(10, 7),
(9, 8),
(10, 8),
(9, 9),
(10, 9),
(9, 10),
(10, 10),
(9, 11),
(10, 11),
(1, 12),
(3, 12),
(1, 13),
(3, 13),
(1, 14),
(3, 14),
(1, 15),
(3, 15),
(9, 16),
(10, 16),
(5, 17),
(7, 17),
(5, 18),
(7, 18),
(2, 19),
(5, 19),
(7, 19),
(5, 20),
(7, 20),
(2, 21),
(4, 21),
(2, 22),
(4, 22),
(2, 23),
(4, 23),
(2, 24),
(4, 24),
(2, 25),
(4, 25),
(6, 26),
(8, 26),
(6, 27),
(8, 27),
(6, 28),
(8, 28),
(6, 29),
(8, 29),
(6, 30),
(8, 30),
(11, 31),
(12, 31),
(11, 32),
(12, 32),
(11, 33),
(12, 33),
(11, 34),
(12, 34),
(13, 35),
(14, 35),
(13, 36),
(14, 36),
(13, 37),
(14, 37),
(13, 38),
(14, 38),
(15, 39),
(16, 39),
(15, 40),
(16, 40),
(15, 41),
(16, 41);


--
-- Dumping data for table `threshold`
--

INSERT INTO `threshold` (`id`, `overall`, `labs`) VALUES
(0, 50, 60);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
