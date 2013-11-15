-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 15, 2013 at 05:37 PM
-- Server version: 5.5.33
-- PHP Version: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `spider`
--

-- --------------------------------------------------------

--
-- Table structure for table `site_index`
--

CREATE TABLE IF NOT EXISTS `site_index` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `site_id` int(255) NOT NULL,
  `link` varchar(1000) NOT NULL,
  `indexed` int(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=44230 ;

