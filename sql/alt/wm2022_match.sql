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
-- Tabellenstruktur für Tabelle `cs_match`
--

CREATE TABLE `cs_match` (
  `mid` int(11) NOT NULL,
  `round` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spieltag` int(11) NOT NULL,
  `tid1` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tid2` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matchtime` datetime NOT NULL,
  `result1` int(11) NOT NULL,
  `result2` int(11) NOT NULL,
  `winner` tinyint(1) NOT NULL,
  `ptid1` int(11) NOT NULL,
  `ptid2` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `cs_match`
--

INSERT INTO `cs_match` (`mid`, `round`, `spieltag`, `tid1`, `tid2`, `location`, `matchtime`, `result1`, `result2`, `winner`, `ptid1`, `ptid2`) VALUES
(1, 'V', 0, '3', '4', 'Al Thumama', '2022-11-21 17:00:00', -1, -1, -1, -1, -1),
(2, 'V', 0, '1', '2', 'Al Bayt', '2022-11-20 17:00:00', -1, -1, -1, -1, -1),
(3, 'V', 0, '1', '3', 'Al Thumama', '2022-11-25 14:00:00', -1, -1, -1, -1, -1),
(4, 'V', 0, '4', '2', 'Khalifa-International', '2022-11-25 17:00:00', -1, -1, -1, -1, -1),
(5, 'V', 0, '4', '1', 'Al Bayt', '2022-11-29 16:00:00', -1, -1, -1, -1, -1),
(6, 'V', 0, '2', '3', 'Khalifa-International', '2022-11-29 16:00:00', -1, -1, -1, -1, -1),
(7, 'V', 0, '5', '6', 'Khalifa-International', '2022-11-21 14:00:00', -1, -1, -1, -1, -1),
(8, 'V', 0, '7', '8', 'Al Rayyan', '2022-11-21 20:00:00', -1, -1, -1, -1, -1),
(9, 'V', 0, '8', '6', 'Al Rayyan', '2022-11-25 11:00:00', -1, -1, -1, -1, -1),
(10, 'V', 0, '5', '7', 'Al Bayt', '2022-11-25 20:00:00', -1, -1, -1, -1, -1),
(11, 'V', 0, '8', '5', 'Al Rayyan', '2022-11-29 20:00:00', -1, -1, -1, -1, -1),
(12, 'V', 0, '6', '7', 'Al Thumama', '2022-11-29 20:00:00', -1, -1, -1, -1, -1),
(13, 'V', 0, '9', '10', 'Lusail', '2022-11-22 11:00:00', -1, -1, -1, -1, -1),
(14, 'V', 0, '11', '12', 'Stadium 974', '2022-11-22 17:00:00', -1, -1, -1, -1, -1),
(15, 'V', 0, '12', '10', 'Education City', '2022-11-26 14:00:00', -1, -1, -1, -1, -1),
(16, 'V', 0, '9', '11', 'Lusail', '2022-11-26 20:00:00', -1, -1, -1, -1, -1),
(17, 'V', 0, '12', '9', 'Stadium 974', '2022-11-30 20:00:00', -1, -1, -1, -1, -1),
(18, 'V', 0, '10', '11', 'Lusail', '2022-11-30 20:00:00', -1, -1, -1, -1, -1),
(19, 'V', 0, '15', '16', 'Education City', '2022-11-22 14:00:00', -1, -1, -1, -1, -1),
(20, 'V', 0, '13', '14', 'Al Janoub', '2022-11-22 20:00:00', -1, -1, -1, -1, -1),
(21, 'V', 0, '16', '14', 'Al Janoub', '2022-11-26 11:00:00', -1, -1, -1, -1, -1),
(22, 'V', 0, '13', '15', 'Stadium 974', '2022-11-26 17:00:00', -1, -1, -1, -1, -1),
(23, 'V', 0, '16', '13', 'Education City', '2022-11-30 16:00:00', -1, -1, -1, -1, -1),
(24, 'V', 0, '14', '15', 'Al Janoub', '2022-11-30 16:00:00', -1, -1, -1, -1, -1),
(25, 'V', 0, '19', '20', 'Khalifa-International', '2022-11-23 14:00:00', -1, -1, -1, -1, -1),
(26, 'V', 0, '17', '18', 'Al Thumama', '2022-11-23 17:00:00', -1, -1, -1, -1, -1),
(27, 'V', 0, '20', '18', 'Al Rayyan', '2022-11-27 11:00:00', -1, -1, -1, -1, -1),
(28, 'V', 0, '17', '19', 'Al Bayt', '2022-11-27 20:00:00', -1, -1, -1, -1, -1),
(29, 'V', 0, '20', '17', 'Khalifa-International', '2022-12-01 20:00:00', -1, -1, -1, -1, -1),
(30, 'V', 0, '18', '19', 'Al Bayt', '2022-12-01 20:00:00', -1, -1, -1, -1, -1),
(31, 'V', 0, '23', '24', 'Al Bayt', '2022-11-23 11:00:00', -1, -1, -1, -1, -1),
(32, 'V', 0, '21', '22', 'Al Rayyan', '2022-11-23 20:00:00', -1, -1, -1, -1, -1),
(33, 'V', 0, '21', '23', 'Al Thumama', '2022-11-27 14:00:00', -1, -1, -1, -1, -1),
(34, 'V', 0, '24', '22', 'Khalifa-International', '2022-11-27 17:00:00', -1, -1, -1, -1, -1),
(35, 'V', 0, '24', '21', 'Al Rayyan', '2022-12-01 16:00:00', -1, -1, -1, -1, -1),
(36, 'V', 0, '22', '23', 'Al Thumama', '2022-12-01 16:00:00', -1, -1, -1, -1, -1),
(37, 'V', 0, '27', '28', 'Al Janoub', '2022-11-24 11:00:00', -1, -1, -1, -1, -1),
(38, 'V', 0, '25', '26', 'Lusail', '2022-11-24 20:00:00', -1, -1, -1, -1, -1),
(39, 'V', 0, '28', '26', 'Al Janoub', '2022-11-28 11:00:00', -1, -1, -1, -1, -1),
(40, 'V', 0, '25', '27', 'Stadium 974', '2022-11-28 17:00:00', -1, -1, -1, -1, -1),
(41, 'V', 0, '28', '25', 'Lusail', '2022-12-02 20:00:00', -1, -1, -1, -1, -1),
(42, 'V', 0, '26', '27', 'Stadium 974', '2022-12-02 20:00:00', -1, -1, -1, -1, -1),
(43, 'V', 0, '31', '32', 'Education City', '2022-11-24 14:00:00', -1, -1, -1, -1, -1),
(44, 'V', 0, '29', '30', 'Stadium 974', '2022-11-24 17:00:00', -1, -1, -1, -1, -1),
(45, 'V', 0, '32', '30', 'Education City', '2022-11-28 14:00:00', -1, -1, -1, -1, -1),
(46, 'V', 0, '29', '31', 'Lusail', '2022-11-28 20:00:00', -1, -1, -1, -1, -1),
(47, 'V', 0, '32', '29', 'Education City', '2022-12-02 16:00:00', -1, -1, -1, -1, -1),
(48, 'V', 0, '30', '31', 'Al Janoub', '2022-12-02 16:00:00', -1, -1, -1, -1, -1),
(49, 'F', -1, '33', '34', 'Khalifa-International', '2022-12-03 16:00:00', -1, -1, -1, 33, 34),
(50, 'F', -1, '35', '36', 'Al Rayyan', '2022-12-03 20:00:00', -1, -1, -1, 35, 36),
(51, 'F', -1, '39', '40', 'Al Thumama', '2022-12-04 16:00:00', -1, -1, -1, 39, 40),
(52, 'F', -1, '37', '38', 'Al Bayt', '2022-12-04 20:00:00', -1, -1, -1, 37, 38),
(53, 'F', -1, '41', '42', 'Al Janoub', '2022-12-05 16:00:00', -1, -1, -1, 41, 42),
(54, 'F', -1, '43', '45', 'Stadium 974', '2022-12-05 20:00:00', -1, -1, -1, 43, 45),
(55, 'F', -1, '46', '47', 'Education City', '2022-12-06 16:00:00', -1, -1, -1, 46, 47),
(56, 'F', -1, '44', '48', 'Lusail', '2022-12-06 20:00:00', -1, -1, -1, 44, 48),
(57, 'F', -1, '51', '52', 'Education City', '2022-12-09 16:00:00', -1, -1, -1, 51, 52),
(58, 'F', -1, '49', '50', 'Lusail', '2022-12-09 20:00:00', -1, -1, -1, 49, 50),
(59, 'F', -1, '54', '55', 'Al Thumama', '2022-12-10 16:00:00', -1, -1, -1, 54, 55),
(60, 'F', -1, '64', '53', 'Al Bayt', '2022-12-10 20:00:00', -1, -1, -1, 64, 53),
(61, 'F', -1, '57', '56', 'Lusail', '2022-12-13 20:00:00', -1, -1, -1, 57, 56),
(62, 'F', -1, '59', '58', 'Al Bayt', '2022-12-14 20:00:00', -1, -1, -1, 59, 58),
(63, 'F', -1, '60', '61', 'Al Rayyan', '2022-12-17 16:00:00', -1, -1, -1, 60, 61),
(64, 'F', -1, '62', '63', 'Lusail', '2022-12-18 16:00:00', -1, -1, -1, 62, 63);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `cs_match`
--
ALTER TABLE `cs_match`
  ADD PRIMARY KEY (`mid`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cs_match`
--
ALTER TABLE `cs_match`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
