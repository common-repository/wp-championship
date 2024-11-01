-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 13. Apr 2014 um 15:30
-- Server Version: 5.5.33a-MariaDB
-- PHP-Version: 5.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `wordpresstable`
--

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
  `penalty` int(11) NOT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;

--
-- Daten für Tabelle `cs_team`
--

INSERT INTO `cs_team` (`tid`, `name`, `shortname`, `icon`, `groupid`, `qualified`, `penalty`) VALUES
(1, 'Brasilien', 'BRA', 'bra.png', 'A', 0, 0),
(2, 'Kroatien', 'CRO', 'cro.png', 'A', 0, 0),
(3, 'Mexiko', 'MEX', 'mex.png', 'A', 0, 0),
(4, 'Kamerun', 'CMR', 'cmr.png', 'A', 0, 0),
(5, 'Spanien', 'ESP', 'esp.png', 'B', 0, 0),
(6, 'Niederlande', 'NED', 'ned.png', 'B', 0, 0),
(7, 'Chile', 'CHI', 'chi.png', 'B', 0, 0),
(8, 'Australien', 'AUS', 'aus.png', 'B', 0, 0),
(9, 'Kolumbien', 'COL', 'col.png', 'C', 0, 0),
(10, 'Griechenland', 'GRE', 'gre.png', 'C', 0, 0),
(11, 'Elfenbeinküste', 'CIV', 'civ.png', 'C', 0, 0),
(12, 'Japan', 'JPN', 'jpn.png', 'C', 0, 0),
(13, 'Uruguay', 'URU', 'uru.png', 'D', 0, 0),
(14, 'Costa Rica', 'CRC', 'crc.png', 'D', 0, 0),
(15, 'England', 'ENG', 'eng.png', 'D', 0, 0),
(16, 'Italien', 'ITA', 'ita.png', 'D', 0, 0),
(17, 'Schweiz', 'SUI', 'sui.png', 'E', 0, 0),
(18, 'Ecuador', 'ECU', 'ecu.png', 'E', 0, 0),
(19, 'Frankreich', 'FRA', 'fra.png', 'E', 0, 0),
(20, 'Honduras', 'HON', 'hon.png', 'E', 0, 0),
(21, 'Argentinien', 'ARG', 'arg.png', 'F', 0, 0),
(22, 'Bosnien-Herzegowina', 'BIH', 'bih.png', 'F', 0, 0),
(23, 'Iran', 'IRN', 'irn.png', 'F', 0, 0),
(24, 'Nigeria', 'NGA', 'nga.png', 'F', 0, 0),
(25, 'Deutschland', 'GER', 'ger.png', 'G', 0, 0),
(26, 'Portugal', 'POR', 'por.png', 'G', 0, 0),
(27, 'Ghana', 'GHA', 'gha.png', 'G', 0, 0),
(28, 'USA', 'USA', 'usa.png', 'G', 0, 0),
(29, 'Belgien', 'BEL', 'bel.png', 'H', 0, 0),
(30, 'Algerien', 'ALG', 'alg.png', 'H', 0, 0),
(31, 'Russland', 'RUS', 'rus.png', 'H', 0, 0),
(32, 'Korea Republik', 'KOR', 'kor.png', 'H', 0, 0),
(33, '#A1', '', '', '', 1, 0),
(34, '#B2', '', '', '', 1, 0),
(35, '#C1', '', '', '', 1, 0),
(36, '#D2', '', '', '', 1, 0),
(37, '#B1', '', '', '', 1, 0),
(38, '#A2', '', '', '', 1, 0),
(39, '#D1', '', '', '', 1, 0),
(40, '#C2', '', '', '', 1, 0),
(41, '#E1', '', '', '', 1, 0),
(42, '#F2', '', '', '', 1, 0),
(43, '#G1', '', '', '', 1, 0),
(44, '#H1', '', '', '', 1, 0),
(45, '#H2', '', '', '', 1, 0),
(46, '#F1', '', '', '', 1, 0),
(47, '#E2', '', '', '', 1, 0),
(48, '#G2', '', '', '', 1, 0),
(49, '#W49', '', '', '', 1, 0),
(50, '#W50', '', '', '', 1, 0),
(51, '#W53', '', '', '', 1, 0),
(52, '#W54', '', '', '', 1, 0),
(53, '#W52', '', '', '', 1, 0),
(54, '#W55', '', '', '', 1, 0),
(55, '#W56', '', '', '', 1, 0),
(56, '#W57', '', '', '', 1, 0),
(57, '#W58', '', '', '', 1, 0),
(58, '#W59', '', '', '', 1, 0),
(59, '#W60', '', '', '', 1, 0),
(60, '#V61', '', '', '', 1, 0),
(61, '#V62', '', '', '', 1, 0),
(62, '#W61', '', '', '', 1, 0),
(63, '#W62', '', '', '', 1, 0),
(64, '#W51', '', '', '', 1, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
