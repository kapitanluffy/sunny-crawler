-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 15, 2013 at 05:38 PM
-- Server version: 5.5.33
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `spider`
--

-- --------------------------------------------------------

--
-- Table structure for table `mp3rehab`
--

CREATE TABLE IF NOT EXISTS `mp3rehab` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `artist` varchar(1000) NOT NULL,
  `title` varchar(1000) NOT NULL,
  `link` varchar(1000) NOT NULL,
  `referrer` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2665 ;

