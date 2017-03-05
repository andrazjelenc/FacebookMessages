-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Gostitelj: 127.0.0.1
-- Čas nastanka: 05. mar 2017 ob 12.01
-- Različica strežnika: 5.7.9
-- Različica PHP: 5.6.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Zbirka podatkov: `facebook2`
--

-- --------------------------------------------------------

--
-- Struktura tabele `daily_count`
--

DROP TABLE IF EXISTS `daily_count`;
CREATE TABLE IF NOT EXISTS `daily_count` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `cnt` int(11) NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `person_id` (`person_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12309 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabele `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` bigint(20) NOT NULL,
  `type` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `person_id` (`person_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=651639 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktura tabele `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `person_id` bigint(20) NOT NULL,
  `name` text NOT NULL,
  `active` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
