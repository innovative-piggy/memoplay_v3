-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 25, 2020 at 07:15 AM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `memonpja_orders`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id_n` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `order_id` int(11) NOT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `email` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `firstname` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `lastname` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `city` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `country` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `titre_musique` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `phrase_personnalisee` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `spotify_code` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `taille` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `datep` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `quantity` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `SKU` varchar(22) CHARACTER SET latin1 NOT NULL,
  `shopify_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`order_id_n`),
  UNIQUE KEY `order_id_n` (`order_id_n`),
  UNIQUE KEY `shopify_id` (`shopify_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zip`
--

DROP TABLE IF EXISTS `zip`;
CREATE TABLE IF NOT EXISTS `zip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datep` int(11) NOT NULL,
  `startp` int(11) NOT NULL,
  `endp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
