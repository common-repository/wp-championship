-- phpMyAdmin SQL Dump
-- version 2.11.9.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 12. März 2010 um 18:49
-- Server Version: 5.0.90
-- PHP-Version: 5.2.12-pl0-gentoo


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `wp29`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_match`
--

CREATE TABLE IF NOT EXISTS `cs_match` (
  `mid` int(11) NOT NULL auto_increment,
  `round` char(1) default NULL,
  `tid1` varchar(8) NOT NULL,
  `tid2` varchar(8) NOT NULL,
  `location` varchar(80) NOT NULL,
  `matchtime` datetime NOT NULL,
  `result1` int(11) NOT NULL,
  `result2` int(11) NOT NULL,
  `winner` tinyint(1) NOT NULL,
  `ptid1` int(11) NOT NULL,
  `ptid2` int(11) NOT NULL,
  PRIMARY KEY  (`mid`)
) TYPE=MyISAM  AUTO_INCREMENT=65 ;

--
-- Daten für Tabelle `cs_match`
--

INSERT INTO `cs_match` (`mid`, `round`, `tid1`, `tid2`, `location`, `matchtime`, `result1`, `result2`, `winner`, `ptid1`, `ptid2`) VALUES
(1, 'V', '1', '2', 'Johannesburg', '2010-06-11 16:00:00', -1, -1, -1, -1, -1),
(2, 'V', '3', '4', 'Kapstadt', '2010-06-11 20:30:00', -1, -1, -1, -1, -1),
(3, 'V', '5', '6', 'Johannesburg', '2010-06-12 16:00:00', -1, -1, -1, -1, -1),
(4, 'V', '7', '8', 'N. Mandela Bay/Port Elizabeth', '2010-06-12 13:30:00', -1, -1, -1, -1, -1),
(5, 'V', '9', '10', 'Rustenburg', '2010-06-12 20:30:00', -1, -1, -1, -1, -1),
(6, 'V', '11', '12', 'Polokwane', '2010-06-13 13:30:00', -1, -1, -1, -1, -1),
(7, 'V', '13', '14', 'Durban', '2010-06-13 20:30:00', -1, -1, -1, -1, -1),
(8, 'V', '15', '16', 'Tshwane/Pretoria', '2010-06-13 16:00:00', -1, -1, -1, -1, -1),
(9, 'V', '17', '18', 'Johannesburg', '2010-06-14 13:30:00', -1, -1, -1, -1, -1),
(10, 'V', '19', '20', 'Mangaung/ Bloemfontein', '2010-06-14 16:00:00', -1, -1, -1, -1, -1),
(11, 'V', '21', '22', 'Kapstadt', '2010-06-14 20:30:00', -1, -1, -1, -1, -1),
(12, 'V', '23', '24', 'Rustenburg', '2010-06-15 13:30:00', -1, -1, -1, -1, -1),
(13, 'V', '27', '28', 'N. Mandela Bay/Port Elizabeth', '2010-06-15 16:00:00', -1, -1, -1, -1, -1),
(14, 'V', '25', '26', 'Johannesburg', '2010-06-15 20:30:00', -1, -1, -1, -1, -1),
(15, 'V', '31', '32', 'Nelspruit', '2010-06-16 13:30:00', -1, -1, -1, -1, -1),
(16, 'V', '29', '30', 'Durban', '2010-06-16 16:00:00', -1, -1, -1, -1, -1),
(18, 'V', '4', '2', 'Polokwane', '2010-06-17 20:30:00', -1, -1, -1, -1, -1),
(19, 'V', '8', '6', 'Mangaung/Bloemfontein', '2010-06-17 16:00:00', -1, -1, -1, -1, -1),
(20, 'V', '5', '7', 'Johannesburg', '2010-06-17 13:30:00', -1, -1, -1, -1, -1),
(21, 'V', '13', '15', 'N. Mandela Bay/Port Elizabeth', '2010-06-18 13:30:00', -1, -1, -1, -1, -1),
(22, 'V', '12', '10', 'Johannesburg', '2010-06-18 16:00:00', -1, -1, -1, -1, -1),
(23, 'V', '9', '11', 'Kapstadt', '2010-06-18 20:30:00', -1, -1, -1, -1, -1),
(24, 'V', '16', '14', 'Rustenburg', '2010-06-19 16:00:00', -1, -1, -1, -1, -1),
(17, 'V', '1', '3', 'Tshwane/Pretoria', '2010-06-16 20:30:00', -1, -1, -1, -1, -1),
(25, 'V', '17', '19', 'Durban', '2010-06-19 13:30:00', -1, -1, -1, -1, -1),
(26, 'V', '20', '18', 'Tshwane/Pretoria', '2010-06-19 20:30:00', -1, -1, -1, -1, -1),
(27, 'V', '24', '22', 'Mangaung/Bloemfontein', '2010-06-20 13:30:00', -1, -1, -1, -1, -1),
(28, 'V', '21', '23', 'Nelspruit', '2010-06-20 16:00:00', -1, -1, -1, -1, -1),
(29, 'V', '25', '27', 'Johannesburg', '2010-06-20 20:30:00', -1, -1, -1, -1, -1),
(30, 'V', '28', '26', 'Kapstadt', '2010-06-21 13:30:00', -1, -1, -1, -1, -1),
(31, 'V', '32', '30', 'N. Mandela Bay/Port Elizabeth', '2010-06-21 16:00:00', -1, -1, -1, -1, -1),
(32, 'V', '29', '31', 'Johannesburg', '2010-06-21 20:30:00', -1, -1, -1, -1, -1),
(33, 'V', '2', '3', 'Rustenburg', '2010-06-22 16:00:00', -1, -1, -1, -1, -1),
(34, 'V', '4', '1', 'Mangaung/Bloemfontein', '2010-06-22 16:00:00', -1, -1, -1, -1, -1),
(35, 'V', '6', '7', 'Durban', '2010-06-22 20:30:00', -1, -1, -1, -1, -1),
(36, 'V', '8', '5', 'Polokwane', '2010-06-22 20:30:00', -1, -1, -1, -1, -1),
(37, 'V', '12', '9', 'N. Mandela Bay/Port Elizabeth', '2010-06-23 16:00:00', -1, -1, -1, -1, -1),
(38, 'V', '10', '11', 'Tshwane/Pretoria', '2010-06-23 16:00:00', -1, -1, -1, -1, -1),
(39, 'V', '16', '13', 'Johannesburg', '2010-06-23 20:30:00', -1, -1, -1, -1, -1),
(40, 'V', '14', '15', 'Nelspruit', '2010-06-23 20:30:00', -1, -1, -1, -1, -1),
(43, 'V', '18', '19', 'Rustenburg', '2010-06-24 20:30:00', -1, -1, -1, -1, -1),
(44, 'V', '20', '17', 'Kapstadt', '2010-06-24 20:30:00', -1, -1, -1, -1, -1),
(41, 'V', '24', '21', 'Johannesburg', '2010-06-24 16:00:00', -1, -1, -1, -1, -1),
(42, 'V', '22', '23', 'Polokwane', '2010-06-24 16:00:00', -1, -1, -1, -1, -1),
(45, 'V', '28', '25', 'Durban', '2010-06-25 16:00:00', -1, -1, -1, -1, -1),
(46, 'V', '26', '27', 'Nelspruit', '2010-06-25 16:00:00', -1, -1, -1, -1, -1),
(47, 'V', '32', '29', 'Tshwane/Pretoria', '2010-06-25 20:30:00', -1, -1, -1, -1, -1),
(48, 'V', '30', '31', 'Mangaung/ Bloemfontein', '2010-06-25 20:30:00', -1, -1, -1, -1, -1),
(49, 'F', '33', '34', 'N. Mandela Bay/Port Elizabeth', '2010-06-26 16:00:00', -1, -1, -1, 33, 34),
(50, 'F', '35', '36', 'Rustenburg', '2010-06-26 20:30:00', -1, -1, -1, 35, 36),
(51, 'F', '37', '38', 'Mangaung/Bloemfontein', '2010-06-27 16:00:00', -1, -1, -1, 37, 38),
(52, 'F', '39', '40', 'Johannesburg', '2010-06-27 20:30:00', -1, -1, -1, 39, 40),
(53, 'F', '41', '42', 'Durban', '2010-06-28 16:00:00', -1, -1, -1, 41, 42),
(54, 'F', '43', '44', 'Johannesburg', '2010-06-28 20:30:00', -1, -1, -1, 43, 44),
(55, 'F', '45', '46', 'Tshwane/Pretoria', '2010-06-29 16:00:00', -1, -1, -1, 45, 46),
(56, 'F', '47', '48', 'Kapstadt', '2010-06-29 20:30:00', -1, -1, -1, 47, 48),
(57, 'F', '49', '50', 'N. Mandela Bay/Port Elizabeth', '2010-07-02 16:00:00', -1, -1, -1, 49, 50),
(58, 'F', '51', '52', 'Johannesburg', '2010-07-02 20:30:00', -1, -1, -1, 51, 52),
(59, 'F', '53', '54', 'Kapstadt', '2010-07-03 16:00:00', -1, -1, -1, 53, 54),
(60, 'F', '55', '56', 'Johannesburg', '2010-07-03 20:30:00', -1, -1, -1, 55, 56),
(61, 'F', '57', '58', 'Kapstadt', '2010-07-06 20:30:00', -1, -1, -1, 57, 58),
(62, 'F', '59', '60', 'Durban', '2010-07-07 20:30:00', -1, -1, -1, 59, 60),
(63, 'F', '61', '62', 'N. Mandela Bay/Port Elizabeth', '2010-07-10 20:30:00', -1, -1, -1, 61, 62),
(64, 'F', '63', '64', 'Johannesburg', '2010-07-11 20:30:00', -1, -1, -1, 63, 64);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_team`
--

CREATE TABLE IF NOT EXISTS `cs_team` (
  `tid` int(11) NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  `icon` varchar(40) NOT NULL,
  `groupid` varchar(2) NOT NULL,
  `qualified` tinyint(1) NOT NULL,
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM  AUTO_INCREMENT=65 ;

--
-- Daten für Tabelle `cs_team`
--

INSERT INTO `cs_team` (`tid`, `name`, `icon`, `groupid`, `qualified`) VALUES
(1, 'Südafrika', 'rsa.gif', 'A', 0),
(2, 'Mexiko', 'mex.gif', 'A', 0),
(3, 'Uruguay', 'uru.gif', 'A', 0),
(4, 'Frankreich', 'fra.gif', 'A', 0),
(5, 'Argentinien', 'arg.gif', 'B', 0),
(6, 'Nigeria', 'nga.gif', 'B', 0),
(7, 'Korea Republik', 'kor.gif', 'B', 0),
(8, 'Griechenland', 'gre.gif', 'B', 0),
(9, 'England', 'eng.gif', 'C', 0),
(10, 'USA', 'usa.gif', 'C', 0),
(11, 'Algerien', 'alg.gif', 'C', 0),
(12, 'Slowenien', 'svn.gif', 'C', 0),
(13, 'Deutschland', 'ger.gif', 'D', 0),
(14, 'Australien', 'aus.gif', 'D', 0),
(15, 'Serbien', 'srb.gif', 'D', 0),
(16, 'Ghana', 'gha.gif', 'D', 0),
(17, 'Niederlande', 'ned.gif', 'E', 0),
(18, 'Dänemark', 'den.gif', 'E', 0),
(19, 'Japan', 'jpn.gif', 'E', 0),
(20, 'Kamerun', 'cmr.gif', 'E', 0),
(21, 'Italien', 'ita.gif', 'F', 0),
(22, 'Paraguay', 'par.gif', 'F', 0),
(23, 'Neuseeland', 'nzl.gif', 'F', 0),
(24, 'Slowakei', 'svk.gif', 'F', 0),
(25, 'Brasilien', 'bra.gif', 'G', 0),
(26, 'Korea DVR', 'prk.gif', 'G', 0),
(27, 'Elfenbeinküste', 'civ.gif', 'G', 0),
(28, 'Portugal', 'por.gif', 'G', 0),
(29, 'Spanien', 'esp.gif', 'H', 0),
(30, 'Schweiz', 'sui.gif', 'H', 0),
(31, 'Honduras', 'hon.gif', 'H', 0),
(32, 'Chile', 'chi.gif', 'H', 0),
(33, '#A1', '', '', 1),
(34, '#B2', '', '', 1),
(35, '#C1', '', '', 1),
(36, '#D2', '', '', 1),
(37, '#D1', '', '', 1),
(38, '#C2', '', '', 1),
(39, '#B1', '', '', 1),
(40, '#A2', '', '', 1),
(41, '#E1', '', '', 1),
(42, '#F2', '', '', 1),
(43, '#G1', '', '', 1),
(44, '#H2', '', '', 1),
(45, '#F1', '', '', 1),
(46, '#E2', '', '', 1),
(47, '#H1', '', '', 1),
(48, '#G2', '', '', 1),
(49, '#W53', '', '', 1),
(50, '#W54', '', '', 1),
(51, '#W49', '', '', 1),
(52, '#W50', '', '', 1),
(53, '#W52', '', '', 1),
(54, '#W51', '', '', 1),
(55, '#W55', '', '', 1),
(56, '#W56', '', '', 1),
(57, '#W58', '', '', 1),
(58, '#W57', '', '', 1),
(59, '#W59', '', '', 1),
(60, '#W60', '', '', 1),
(61, '#V61', '', '', 1),
(62, '#V62', '', '', 1),
(63, '#W61', '', '', 1),
(64, '#W62', '', '', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_tipp`
--

CREATE TABLE IF NOT EXISTS `cs_tipp` (
  `userid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `result1` int(11) NOT NULL,
  `result2` int(11) NOT NULL,
  `tipptime` datetime NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY  (`userid`,`mid`)
) TYPE=MyISAM;

--
-- Daten für Tabelle `cs_tipp`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_users`
--

CREATE TABLE IF NOT EXISTS `cs_users` (
  `userid` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `mailservice` tinyint(1) NOT NULL,
  `stellvertreter` int(11) NOT NULL,
  `champion` int(11) NOT NULL,
  `championtime` datetime NOT NULL
) TYPE=MyISAM;

--
-- Daten für Tabelle `cs_users`
--

INSERT INTO `cs_users` (`userid`, `admin`, `mailservice`, `stellvertreter`, `champion`, `championtime`) VALUES
(1, 1, 0, 0, 0, '0000-00-00 00:00:00');
