-- phpMyAdmin SQL Dump
-- version 4.0.10.20
-- https://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Stř 28. lis 2018, 13:14
-- Verze serveru: 5.6.40
-- Verze PHP: 5.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `xsopfp00`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `autor`
--

CREATE TABLE IF NOT EXISTS `autor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jmeno` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `prijmeni` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `datum_narozeni` date NOT NULL,
  `zeme` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `pocet_titulu` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `last_name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `birthdate` date NOT NULL,
  `member_until` date NOT NULL,
  `employee` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `literature`
--

CREATE TABLE IF NOT EXISTS `literature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publisher_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `publication_date` date NOT NULL,
  `pages_number` int(11) NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `pieces_total` int(11) NOT NULL DEFAULT '0',
  `pieces_borrowed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `literature_publisher` (`publisher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `literature_has_author`
--

CREATE TABLE IF NOT EXISTS `literature_has_author` (
  `literature_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  UNIQUE KEY `literature_author_unique` (`literature_id`,`author_id`),
  KEY `literature_author_author` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reservation`
--

CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `literature_id` int(11) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_user` (`user_id`),
  KEY `reservation_literature` (`literature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `reminder`
--

CREATE TABLE IF NOT EXISTS `reminder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrowing_id` int(11) NOT NULL,
  `days_over` int(11) NOT NULL,
  `fee` int(11) NOT NULL,
  `pay_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reminder_borrowing` (`borrowing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `publisher`
--

CREATE TABLE IF NOT EXISTS `publisher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `borrowing`
--

CREATE TABLE IF NOT EXISTS `borrowing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `literature_id` int(11) NOT NULL,
  `borrowing_date` date NOT NULL,
  `return_until_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `borrowing_user` (`user_id`),
  KEY `borrowing_literature` (`literature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `literature`
--
ALTER TABLE `literature`
  ADD CONSTRAINT `literature_user` FOREIGN KEY (`publisher_id`) REFERENCES `publisher` (`id`);

--
-- Omezení pro tabulku `literature_has_author`
--
ALTER TABLE `literature_has_author`
  ADD CONSTRAINT `literature_author_author` FOREIGN KEY (`author_id`) REFERENCES `autor` (`id`),
  ADD CONSTRAINT `literature_author_literature` FOREIGN KEY (`literature_id`) REFERENCES `literature` (`id`);

--
-- Omezení pro tabulku `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `reservation_literature` FOREIGN KEY (`literature_id`) REFERENCES `literature` (`id`);

--
-- Omezení pro tabulku `reminder`
--
ALTER TABLE `reminder`
  ADD CONSTRAINT `reminder_borrowing` FOREIGN KEY (`borrowing_id`) REFERENCES `borrowing` (`id`);

--
-- Omezení pro tabulku `borrowing`
--
ALTER TABLE `borrowing`
  ADD CONSTRAINT `borrowing_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `borrowing_literature` FOREIGN KEY (`literature_id`) REFERENCES `literature` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
