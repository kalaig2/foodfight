-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 18, 2016 at 11:04 PM
-- Server version: 5.5.49-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `food_fight`
--

-- --------------------------------------------------------

--
-- Table structure for table `aff16_gift_cards`
--

CREATE TABLE IF NOT EXISTS `aff16_gift_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_number` varchar(25) NOT NULL,
  `purchased_user_id` int(11) DEFAULT NULL,
  `purchased_user_email` varchar(75) DEFAULT NULL,
  `on_hold` tinyint(1) NOT NULL DEFAULT '0',
  `purchased` tinyint(1) NOT NULL DEFAULT '0',
  `price` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `to_email` varchar(75) DEFAULT NULL,
  `notes` text,
  `created_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `aff16_gift_cards`
--

INSERT INTO `aff16_gift_cards` (`id`, `card_number`, `purchased_user_id`, `purchased_user_email`, `on_hold`, `purchased`, `price`, `active`, `to_email`, `notes`, `created_on`, `updated_on`) VALUES
(5, '603628737462001019268', NULL, NULL, 0, 0, 0, 1, NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
