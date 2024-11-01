-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 18. Jul 2018 um 10:26
-- Server Version: 5.5.60-0+deb8u1
-- PHP-Version: 5.6.36-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `wordpress`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_team`
--

CREATE TABLE IF NOT EXISTS `cs_team` (
`tid` int(11) NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shortname` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `groupid` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualified` tinyint(1) NOT NULL,
  `penalty` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `cs_team`
--

INSERT INTO `cs_team` (`tid`, `name`, `shortname`, `icon`, `groupid`, `qualified`, `penalty`) VALUES
(1, 'Bayern München', 'FCB', 'fc-bayern-muenchen-wappen.png', 'A', 0, 0),
(2, 'Borussia Dortmund', 'BVB', 'borussia-dortmund-wappen.png', 'A', 0, 0),
(3, 'Bayer Leverkusen', 'B04', 'bayer-04-leverkusen-wappen.png', 'A', 0, 0),
(4, 'FC Schalke 04', 'S04', 'fc-schalke-04-wappen.png', 'A', 0, 0),
(5, 'VfB Stuttgart', 'VFB', 'vfb-stuttgart-wappen.png', 'A', 0, 0),
(6, 'Eintracht Frankfurt', 'SGE', 'eintracht-frankfurt-wappen.png', 'A', 0, 0),
(7, 'Bor. Mönchengladbach', 'BMG', 'borussia-moenchengladbach-wappen.png', 'A', 0, 0),
(8, 'RB Leipzig', 'RBL', 'leipzig.png', 'A', 0, 0),
(9, 'Hannover 96', 'HAN', 'hannover-96-wappen.png', 'A', 0, 0),
(10, 'VfL Wolfsburg', 'WOB', 'vfl-wolfsburg-wappen.png', 'A', 0, 0),
(11, 'SC Freiburg', 'SCF', 'freiburg.png', 'A', 0, 0),
(12, '1. FSV Mainz 05', 'M05', 'fsv-mainz-05-wappen.png', 'A', 0, 0),
(13, 'Werder Bremen', 'BRE', 'werder-bremen-wappen.png', 'A', 0, 0),
(14, 'FC Augsburg', 'FCA', 'fc-augsburg-wappen.png', 'A', 0, 0),
(15, '1899 Hoffenheim', 'HOF', 'tsg-1899-hoffenheim-wappen.png', 'A', 0, 0),
(16, 'Hertha BSC Berlin', 'BSC', 'Hertha_BSC.png', 'A', 0, 0),
(17, '1. FC Nürnberg', 'FCN', '1-fc-nuernberg-wappen.png', 'A', 0, 0),
(18, 'Fortuna Düsseldorf', 'F95', 'fortuna-duesseldorf-wappen.png', 'A', 0, 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `cs_team`
--
ALTER TABLE `cs_team`
 ADD PRIMARY KEY (`tid`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cs_team`
--
ALTER TABLE `cs_team`
MODIFY `tid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
