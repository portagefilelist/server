-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 26, 2023 at 10:18 AM
-- Server version: 10.5.19-MariaDB-1:10.5.19+maria~ubu2004-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `d03d2829`
--

-- --------------------------------------------------------

--
-- Table structure for table `pfl_category`
--

DROP TABLE IF EXISTS `pfl_category`;
CREATE TABLE `pfl_category` (
  `hash` char(32) NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_file`
--

DROP TABLE IF EXISTS `pfl_file`;
CREATE TABLE `pfl_file` (
  `hash` char(32) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_id` char(32) NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_package`
--

DROP TABLE IF EXISTS `pfl_package`;
CREATE TABLE `pfl_package` (
  `hash` char(32) NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arch` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(32) NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_package_use`
--

DROP TABLE IF EXISTS `pfl_package_use`;
CREATE TABLE `pfl_package_use` (
  `useword` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_id` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pfl_category`
--
ALTER TABLE `pfl_category`
  ADD PRIMARY KEY (`hash`);

--
-- Indexes for table `pfl_file`
--
ALTER TABLE `pfl_file`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `pfl_package`
--
ALTER TABLE `pfl_package`
  ADD PRIMARY KEY (`hash`);

--
-- Indexes for table `pfl_package_use`
--
ALTER TABLE `pfl_package_use`
  ADD UNIQUE KEY `package_id` (`package_id`,`useword`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
