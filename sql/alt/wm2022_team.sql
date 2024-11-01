-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 11. Jul 2022 um 06:55
-- Server-Version: 10.5.15-MariaDB-0+deb11u1
-- PHP-Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
(1, 'Katar', 'KAT', 'wm2022/qatar.png', 'A', 0, 0),
(2, 'Ecuador', 'ECU', 'wm2022/ecuador.png', 'A', 0, 0),
(3, 'Senegal', 'SEN', 'wm2022/senegal.png', 'A', 0, 0),
(4, 'Niederlande', 'NED', 'wm2022/holland.png', 'A', 0, 0),
(5, 'England', 'ENG', 'wm2022/england.png', 'B', 0, 0),
(6, 'Iran', 'IRA', 'wm2022/iran.png', 'B', 0, 0),
(7, 'Vereinigte Staaten', 'USA', 'wm2022/america.png', 'B', 0, 0),
(8, 'Wales', 'WAL', 'wm2022/wales.png', 'B', 0, 0),
(9, 'Argentinien', 'ARG', 'wm2022/argentinien.png', 'C', 0, 0),
(10, 'Saudi-Arabien', 'SAU', 'wm2022/saudiarabia.png', 'C', 0, 0),
(11, 'Mexiko', 'MEX', 'wm2022/mexico.png', 'C', 0, 0),
(12, 'Polen', 'POL', 'wm2022/poland.png', 'C', 0, 0),
(13, 'Frankreich', 'FRA', 'wm2022/france.png', 'D', 0, 0),
(14, 'Australien', 'AUS', 'wm2022/australia.png', 'D', 0, 0),
(15, 'Dänemark', 'DEN', 'wm2022/denmark.png', 'D', 0, 0),
(16, 'Tunesien', 'TUN', 'wm2022/tunisia.png', 'D', 0, 0),
(17, 'Spanien', 'ESP', 'wm2022/spain.png', 'E', 0, 0),
(18, 'Costa Rica', 'COR', 'wm2022/costarica.png', 'E', 0, 0),
(19, 'Deutschland', 'GER', 'wm2022/germany.png', 'E', 0, 0),
(20, 'Japan', 'JAP', 'wm2022/japan.png', 'E', 0, 0),
(21, 'Belgien', 'BEL', 'wm2022/belgium.png', 'F', 0, 0),
(22, 'Kanada', 'CAN', 'wm2022/canada.png', 'F', 0, 0),
(23, 'Marokko', 'MOR', 'wm2022/morocco.png', 'F', 0, 0),
(24, 'Kroatien', 'CRO', 'wm2022/croatia.png', 'F', 0, 0),
(25, 'Brasilien', 'BRA', 'wm2022/brazil.png', 'G', 0, 0),
(26, 'Serbien', 'SER', 'wm2022/serbia.png', 'G', 0, 0),
(27, 'Schweiz', 'HEL', 'wm2022/switzerland.png', 'G', 0, 0),
(28, 'Kamerun', 'CAM', 'wm2022/cameroon.png', 'G', 0, 0),
(29, 'Portugal', 'POR', 'wm2022/portugal.png', 'H', 0, 0),
(30, 'Ghana', 'GHA', 'wm2022/ghana.png', 'H', 0, 0),
(31, 'Uruguay', 'URU', 'wm2022/uruguay.png', 'H', 0, 0),
(32, 'Südkorea', 'SCO', 'wm2022/southcorea.png', 'H', 0, 0),
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
  MODIFY `tid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
