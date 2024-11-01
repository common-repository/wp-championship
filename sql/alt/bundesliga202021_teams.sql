-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 07. Sep 2020 um 10:47
-- Server-Version: 10.3.23-MariaDB-0+deb10u1
-- PHP-Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `wordpress`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cs_team`
--

CREATE TABLE `cs_team` (
  `tid` int(11) NOT NULL,
  `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shortname` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `groupid` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualified` tinyint(1) NOT NULL,
  `penalty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `cs_team`
--

INSERT INTO `cs_team` (`tid`, `name`, `shortname`, `icon`, `groupid`, `qualified`, `penalty`) VALUES
(6, 'Bayer Leverkusen', 'B04', 'bayer-04-leverkusen-wappen.png', 'A', 0, 0),
(7, 'Borussia Dortmund', 'BVB', 'borussia-dortmund-wappen.png', 'A', 0, 0),
(9, 'FC Schalke 04', 'S04', 'fc-schalke-04-wappen.png', 'A', 0, 0),
(16, 'VfB Stuttgart', 'VFB', 'vfb-stuttgart-wappen.png', 'A', 0, 0),
(40, 'FC Bayern', 'FCB', 'fc-bayern-muenchen-wappen.png', 'A', 0, 0),
(54, 'Hertha BSC', 'BSC', 'Hertha_BSC.png', 'A', 0, 0),
(65, '1. FC Köln', 'FCK', 'fc_koeln.png', 'A', 0, 0),
(80, '1. FC Union Berlin', 'UNB', 'unionberlin.png', 'A', 0, 0),
(81, '1. FSV Mainz 05', 'M05', 'fsv-mainz-05-wappen.png', 'A', 0, 0),
(83, 'Arminia Bielefeld', 'ARB', 'arminia-bielefeld.png', 'A', 0, 0),
(87, 'Borussia Mönchengladbach', 'BMG', 'borussia-moenchengladbach-wappen.png', 'A', 0, 0),
(91, 'Eintracht Frankfurt', 'SGE', 'eintracht-frankfurt-wappen.png', 'A', 0, 0),
(95, 'FC Augsburg', 'FCA', 'fc-augsburg-wappen.png', 'A', 0, 0),
(112, 'SC Freiburg', 'SCF', 'sc-freiburg-wappen.png', 'A', 0, 0),
(123, 'TSG 1899 Hoffenheim', 'HOF', 'tsg-1899-hoffenheim-wappen.png', 'A', 0, 0),
(131, 'VfL Wolfsburg', 'WOB', 'vfl-wolfsburg-wappen.png', 'A', 0, 0),
(134, 'Werder Bremen', 'BRE', 'werder-bremen-wappen.png', 'A', 0, 0),
(1635, 'RB Leipzig', 'RBL', 'leipzig.png', 'A', 0, 0);

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
  MODIFY `tid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1636;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
