-- phpMyAdmin SQL Dump
-- version 3.5.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 12, 2013 at 05:55 AM
-- Server version: 5.6.11
-- PHP Version: 5.2.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `dms`
--

-- --------------------------------------------------------

--
-- Table structure for table `plradicaciondocuments`
--

CREATE TABLE IF NOT EXISTS `plradicaciondocuments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `id_usuario_radicador` int(10) unsigned NOT NULL,
  `codigo_radicacion` char(11) NOT NULL,
  `correlativo` int(10) unsigned NOT NULL,
  `tipo_radicacion` int(10) NOT NULL,
  `fecha_radicacion` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `plradicadorlabels`
--

CREATE TABLE IF NOT EXISTS `plradicadorlabels` (
  `RadicacionID` int(10) NOT NULL,
  `Orientacion` char(1) NOT NULL,
  `Largo` tinyint(4) NOT NULL,
  `Alto` tinyint(4) NOT NULL,
  `CB` varchar(60) NOT NULL,
  `QR` varchar(60) NOT NULL,
  `Fecha` varchar(100) NOT NULL,
  `Texto1` varchar(100) NOT NULL,
  `Texto2` varchar(100) NOT NULL,
  UNIQUE KEY `RadicacionID` (`RadicacionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `plradicadorlabels`
--

INSERT INTO `plradicadorlabels` (`RadicacionID`, `Orientacion`, `Largo`, `Alto`, `CB`, `QR`, `Fecha`, `Texto1`, `Texto2`) VALUES
(-1, 'L', 8, 4, 'a:3:{i:0;b:1;i:1;s:3:"2.5";i:2;s:1:"1";}', 'a:3:{i:0;b:1;i:1;s:3:"0.5";i:2;s:1:"1";}', 'a:4:{i:0;b:0;i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}', 'a:5:{i:0;b:0;i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";}', 'a:5:{i:0;b:0;i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";}')

-- --------------------------------------------------------

--
-- Table structure for table `plradicadorradicaciones`
--

CREATE TABLE IF NOT EXISTS `plradicadorradicaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(25) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `fieldset_asignados` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;


-- --------------------------------------------------------

--
-- Table structure for table `plrradicaciontemplates`
--

CREATE TABLE IF NOT EXISTS `plrradicaciontemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radicacion_id` int(10) NOT NULL,
  `documento_id` int(10) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;