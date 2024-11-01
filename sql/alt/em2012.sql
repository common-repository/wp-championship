-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 10. Februar 2012 um 19:36
-- Server Version: 5.1.49
-- PHP-Version: 5.3.3-1ubuntu9.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `wordpress`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_match`
--

CREATE TABLE IF NOT EXISTS `cs_match` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `round` char(1) DEFAULT NULL,
  `spieltag` int(11) NOT NULL,
  `tid1` varchar(8) NOT NULL,
  `tid2` varchar(8) NOT NULL,
  `location` varchar(80) NOT NULL,
  `matchtime` datetime NOT NULL,
  `result1` int(11) NOT NULL,
  `result2` int(11) NOT NULL,
  `winner` tinyint(1) NOT NULL,
  `ptid1` int(11) NOT NULL,
  `ptid2` int(11) NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

--
-- Daten für Tabelle `cs_match`
--

INSERT INTO `cs_match` (`mid`, `round`, `spieltag`, `tid1`, `tid2`, `location`, `matchtime`, `result1`, `result2`, `winner`, `ptid1`, `ptid2`) VALUES
(1, 'V', 0, '4', '2', 'Warschau', '2012-06-08 18:00:00', -1, -1, -1, -1, -1),
(2, 'V', 0, '1', '3', 'Breslau', '2012-06-08 20:45:00', -1, -1, -1, -1, -1),
(3, 'V', 0, '7', '8', 'Charkow', '2012-06-09 18:00:00', -1, -1, -1, -1, -1),
(4, 'V', 0, '5', '6', 'Lwiw', '2012-06-09 20:45:00', -1, -1, -1, -1, -1),
(5, 'V', 0, '9', '10', 'Danzig', '2012-06-10 18:00:00', -1, -1, -1, -1, -1),
(6, 'V', 0, '12', '11', 'Posen', '2012-06-10 20:45:00', -1, -1, -1, -1, -1),
(7, 'V', 0, '14', '13', 'Donezk', '2012-06-11 18:00:00', -1, -1, -1, -1, -1),
(8, 'V', 0, '15', '16', 'Kiew', '2012-06-11 20:45:00', -1, -1, -1, -1, -1),
(9, 'V', 0, '2', '3', 'Breslau', '2012-06-12 18:00:00', -1, -1, -1, -1, -1),
(10, 'V', 0, '4', '1', 'Warschau', '2012-06-12 20:45:00', -1, -1, -1, -1, -1),
(11, 'V', 0, '8', '6', 'Lwiw', '2012-06-13 18:00:00', -1, -1, -1, -1, -1),
(12, 'V', 0, '7', '5', 'Charkow', '2012-06-13 20:45:00', -1, -1, -1, -1, -1),
(13, 'V', 0, '10', '11', 'Posen', '2012-06-14 18:00:00', -1, -1, -1, -1, -1),
(14, 'V', 0, '9', '12', 'Danzig', '2012-06-14 20:45:00', -1, -1, -1, -1, -1),
(15, 'V', 0, '15', '14', 'Donezk', '2012-06-15 18:00:00', -1, -1, -1, -1, -1),
(16, 'V', 0, '16', '13', 'Kiew', '2012-06-15 20:45:00', -1, -1, -1, -1, -1),
(17, 'V', 0, '2', '1', 'Warschau', '2012-06-16 20:45:00', -1, -1, -1, -1, -1),
(18, 'V', 0, '3', '4', 'Breslau', '2012-06-16 20:45:00', -1, -1, -1, -1, -1),
(19, 'V', 0, '6', '7', 'Charkow', '2012-06-17 20:45:00', -1, -1, -1, -1, -1),
(20, 'V', 0, '8', '5', 'Lwiw', '2012-06-17 20:45:00', -1, -1, -1, -1, -1),
(21, 'V', 0, '11', '9', 'Danzig', '2012-06-18 20:45:00', -1, -1, -1, -1, -1),
(22, 'V', 0, '10', '12', 'Posen', '2012-06-18 20:45:00', -1, -1, -1, -1, -1),
(23, 'V', 0, '16', '14', 'Kiew', '2012-06-19 20:45:00', -1, -1, -1, -1, -1),
(24, 'V', 0, '13', '15', 'Donezk', '2012-06-19 20:45:00', -1, -1, -1, -1, -1),
(25, 'F', -1, '17', '18', 'Warschau', '2012-06-21 20:45:00', -1, -1, -1, 17, 18),
(26, 'F', -1, '19', '20', 'Danzig', '2012-06-22 20:45:00', -1, -1, -1, 19, 20),
(27, 'F', -1, '21', '22', 'Donezk', '2012-06-23 20:45:00', -1, -1, -1, 21, 22),
(28, 'F', -1, '23', '24', 'Kiew', '2012-06-24 20:45:00', -1, -1, -1, 23, 24),
(29, 'F', -1, '25', '26', 'Donezk', '2012-06-27 20:45:00', -1, -1, -1, 25, 26),
(30, 'F', -1, '27', '28', 'Warschau', '2012-06-28 20:45:00', -1, -1, -1, 27, 28),
(31, 'F', -1, '29', '30', 'Kiew', '2012-07-01 20:45:00', -1, -1, -1, 29, 30);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_team`
--

CREATE TABLE IF NOT EXISTS `cs_team` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `shortname` varchar(5) NOT NULL,
  `icon` varchar(40) NOT NULL,
  `groupid` varchar(2) NOT NULL,
  `qualified` tinyint(1) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Daten für Tabelle `cs_team`
--

INSERT INTO `cs_team` (`tid`, `name`, `shortname`, `icon`, `groupid`, `qualified`) VALUES
(1, 'Russland', 'RUS', 'Rus.png', 'A', 0),
(2, 'Griechenland', 'GRE', 'Griechenland.png', 'A', 0),
(3, 'Tschechien', 'CZE', 'Tschechien.png', 'A', 0),
(4, 'Polen', 'POL', 'Polen.png', 'A', 0),
(5, 'Deutschland', 'GER', 'Deutschland.png', 'B', 0),
(6, 'Portugal', 'POR', 'Portugal.png', 'B', 0),
(7, 'Niederlande', 'NIE', 'Niederlande.png', 'B', 0),
(8, 'Dänemark', 'DEN', 'Daenemark.png', 'B', 0),
(9, 'Spanien', 'ESP', 'Spanien.png', 'C', 0),
(10, 'Italien', 'ITA', 'Italien.png', 'C', 0),
(11, 'Kroatien', 'KRO', 'Kroatien.png', 'C', 0),
(12, 'Irland', 'IRE', 'Irland.png', 'C', 0),
(13, 'England', 'ENG', 'England.png', 'D', 0),
(14, 'Frankreich', 'FRA', 'Frankreich.png', 'D', 0),
(15, 'Ukraine', 'UKR', 'Ukraine.png', 'D', 0),
(16, 'Schweden', 'SWE', 'Schweden.png', 'D', 0),
(17, '#A1', '', '', '', 1),
(18, '#B2', '', '', '', 1),
(19, '#B1', '', '', '', 1),
(20, '#A2', '', '', '', 1),
(21, '#C1', '', '', '', 1),
(22, '#D2', '', '', '', 1),
(23, '#D1', '', '', '', 1),
(24, '#C2', '', '', '', 1),
(25, '#W25', '', '', '', 1),
(26, '#W27', '', '', '', 1),
(27, '#W26', '', '', '', 1),
(28, '#W28', '', '', '', 1),
(29, '#W29', '', '', '', 1),
(30, '#W30', '', '', '', 1);
