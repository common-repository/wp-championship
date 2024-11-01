-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 22. Mai 2008 um 19:23
-- Server Version: 4.0.24
-- PHP-Version: 4.4.8

-- --------------------------------------------------------

--
-- Tabellenstruktur fÃ¼r Tabelle `cs_team`
--

CREATE TABLE IF NOT EXISTS `cs_team` (
  `tid` int(11) NOT NULL auto_increment,
  `name` varchar(40) NOT NULL default '',
  `icon` varchar(40) NOT NULL default '',
  `groupid` char(2) NOT NULL default '',
  `qualified` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM AUTO_INCREMENT=31 ;

--
-- Daten fÃ¼r Tabelle `cs_team`
--

INSERT INTO `cs_team` (`tid`, `name`, `icon`, `groupid`, `qualified`) VALUES
(1, 'Deutschland', 'D.gif', 'B', 0),
(2, 'Schweiz', 'CH.gif', 'A', 0),
(3, 'Tschechien', 'CZ.gif', 'A', 0),
(4, 'Portugal', 'P.gif', 'A', 0),
(5, 'TÃ¼rkei', 'TR.gif', 'A', 0),
(6, 'Ãsterreich', 'A.gif', 'B', 0),
(7, 'Kroatien', 'HR.gif', 'B', 0),
(8, 'Polen', 'PL.gif', 'B', 0),
(9, 'RumÃ¤nien', 'RO.gif', 'C', 0),
(10, 'Frankreich', 'F.gif', 'C', 0),
(11, 'Niederlande', 'NL.gif', 'C', 0),
(12, 'Italien', 'I.gif', 'C', 0),
(13, 'Spanien', 'E.gif', 'D', 0),
(14, 'Russland', 'RUS.gif', 'D', 0),
(15, 'Griechenland', 'GR.gif', 'D', 0),
(16, 'Schweden', 'S.gif', 'D', 0),
(17, '#A1', '', '', 1),
(18, '#B2', '', '', 1),
(19, '#B1', '', '', 1),
(20, '#A2', '', '', 1),
(21, '#C1', '', '', 1),
(22, '#D2', '', '', 1),
(23, '#D1', '', '', 1),
(24, '#C2', '', '', 1),
(25, '#W93', '', '', 1),
(26, '#W94', '', '', 1),
(27, '#W95', '', '', 1),
(28, '#W96', '', '', 1),
(29, '#W97', '', '', 1),
(30, '#W98', '', '', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur fÃ¼r Tabelle `cs_match`
--

CREATE TABLE IF NOT EXISTS `cs_match` (
  `mid` int(11) NOT NULL auto_increment,
  `round` char(1) default NULL,
  `tid1` int(11) NOT NULL default '0',
  `tid2` int(11) NOT NULL default '0',
  `location` varchar(80) NOT NULL default '',
  `matchtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `result1` int(11) NOT NULL default '0',
  `result2` int(11) NOT NULL default '0',
  `winner` tinyint(1) NOT NULL default '0',
  `ptid1` int(11) NOT NULL,
  `ptid2` int(11) NOT NULL,
  PRIMARY KEY  (`mid`)
) TYPE=MyISAM AUTO_INCREMENT=100 ;

--
-- Daten fÃ¼r Tabelle `cs_match`
--

INSERT INTO `cs_match` (`mid`, `round`, `tid1`, `tid2`, `location`, `matchtime`, `result1`, `result2`, `winner`, `ptid1`, `ptid2`) VALUES
(92, 'V', 14, 16, 'Innsbruck', '2008-06-18 20:45:00', -1, -1, -1,0,0),
(91, 'V', 15, 13, 'Salzburg', '2008-06-18 20:45:00', -1, -1, -1,0,0),
(90, 'V', 15, 14, 'Salzburg', '2008-06-14 20:45:00', -1, -1, -1,0,0),
(89, 'V', 16, 13, 'Innsbruck', '2008-06-14 18:00:00', -1, -1, -1,0,0),
(88, 'V', 15, 16, 'Salzburg', '2008-06-10 20:45:00', -1, -1, -1,0,0),
(87, 'V', 13, 14, 'Innsbruck', '2008-06-10 18:00:00', -1, -1, -1,0,0),
(86, 'V', 10, 12, 'Bern', '2008-06-17 20:45:00', -1, -1, -1,0,0),
(85, 'V', 11, 9, 'ZÃ¼rich', '2008-06-17 20:45:00', -1, -1, -1,0,0),
(84, 'V', 11, 10, 'Bern', '2008-06-13 20:45:00', -1, -1, -1,0,0),
(83, 'V', 12, 9, 'ZÃ¼rich', '2008-06-13 18:00:00', -1, -1, -1,0,0),
(82, 'V', 11, 12, 'Bern', '2008-06-09 20:45:00', -1, -1, -1,0,0),
(81, 'V', 9, 10, 'ZÃ¼rich', '2008-06-09 18:00:00', -1, -1, -1,0,0),
(80, 'V', 8, 7, 'Klagenfurt', '2008-06-16 20:45:00', -1, -1, -1,0,0),
(79, 'V', 6, 1, 'Wien', '2008-06-16 20:45:00', -1, -1, -1,0,0),
(78, 'V', 6, 8, 'Wien', '2008-06-12 20:45:00', -1, -1, -1,0,0),
(77, 'V', 7, 1, 'Klagenfurt', '2008-06-12 18:00:00', -1, -1, -1,0,0),
(76, 'V', 1, 8, 'Klagenfurt', '2008-06-08 20:45:00', -1, -1, -1,0,0),
(75, 'V', 6, 7, 'Wien', '2008-06-08 18:00:00', -1, -1, -1,0,0),
(74, 'V', 5, 3, 'Genf', '2008-06-15 20:45:00', -1, -1, -1,0,0),
(73, 'V', 2, 4, 'Basel', '2008-06-15 20:45:00', -1, -1, -1,0,0),
(72, 'V', 2, 5, 'Basel', '2008-06-11 20:45:00', -1, -1, -1,0,0),
(71, 'V', 3, 4, 'Genf', '2008-06-11 18:00:00', -1, -1, -1,0,0),
(70, 'V', 4, 5, 'Genf', '2008-06-07 20:45:00', -1, -1, -1,0,0),
(69, 'V', 2, 3, 'Basel', '2008-06-07 18:00:00', -1, -1, -1,0,0),
(93, 'F', 17, 18, 'Basel', '2008-06-19 20:45:00', -1, -1, -1,17,18),
(94, 'F', 19, 20, 'Wien', '2008-06-20 20:45:00', -1, -1, -1,19,20),
(95, 'F', 21, 22, 'Basel', '2008-06-21 20:45:00', -1, -1, -1,21,22),
(96, 'F', 23, 24, 'Wien', '2008-06-22 20:45:00', -1, -1, -1,23,24),
(97, 'F', 25, 26, 'Basel', '2008-06-25 20:45:00', -1, -1, -1,25,26),
(98, 'F', 27, 28, 'Wien', '2008-06-26 20:45:00', -1, -1, -1,27,28),
(99, 'F', 29, 30, 'Wien', '2008-06-29 20:45:00', -1, -1, -1,29,30);

