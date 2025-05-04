SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `pflv4_category`
--

DROP TABLE IF EXISTS `pflv4_category`;
CREATE TABLE `pflv4_category` (
  `hash` char(32) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv4_file`
--

DROP TABLE IF EXISTS `pflv4_file`;
CREATE TABLE `pflv4_file` (
  `hash` char(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv4_package`
--

DROP TABLE IF EXISTS `pflv4_package`;
CREATE TABLE `pflv4_package` (
  `hash` char(32) NOT NULL,
  `fk_category` char(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `version` varchar(32) DEFAULT NULL,
  `arch` varchar(10) DEFAULT NULL,
  `repository` varchar(16) DEFAULT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp(),
  `topicality` date DEFAULT NULL,
  `topicalityLastSeen` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv4_package_use`
--

DROP TABLE IF EXISTS `pflv4_package_use`;
CREATE TABLE `pflv4_package_use` (
  `useword` varchar(64) NOT NULL,
  `fk_package` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv4_pkg2file`
--

DROP TABLE IF EXISTS `pflv4_pkg2file`;
CREATE TABLE `pflv4_pkg2file` (
  `fk_package` char(32) NOT NULL,
  `fk_file` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv4_statslog`
--

DROP TABLE IF EXISTS `pflv4_statslog`;
CREATE TABLE `pflv4_statslog` (
  `type` varchar(16) NOT NULL,
  `value` varchar(254) NOT NULL,
  `timestmp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pflv4_category`
--
ALTER TABLE `pflv4_category`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `pflv4_file`
--
ALTER TABLE `pflv4_file`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`),
  ADD KEY `path` (`path`);

--
-- Indexes for table `pflv4_package`
--
ALTER TABLE `pflv4_package`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `lastmodified` (`lastmodified`),
  ADD KEY `name` (`name`),
  ADD KEY `arch` (`arch`),
  ADD KEY `topicality` (`topicality`),
  ADD KEY `repository` (`repository`),
  ADD KEY `version` (`version`),
  ADD KEY `fk_category` (`fk_category`);

--
-- Indexes for table `pflv4_package_use`
--
ALTER TABLE `pflv4_package_use`
  ADD UNIQUE KEY `packageIdAndUseword` (`fk_package`,`useword`),
  ADD KEY `useword` (`useword`),
  ADD KEY `packageId` (`fk_package`);

--
-- Indexes for table `pflv4_pkg2file`
--
ALTER TABLE `pflv4_pkg2file`
  ADD UNIQUE KEY `packageIdAndFileId` (`fk_package`,`fk_file`),
  ADD KEY `packageId` (`fk_package`),
  ADD KEY `fileId` (`fk_file`);

--
-- Indexes for table `pflv4_statslog`
--
ALTER TABLE `pflv4_statslog`
  ADD KEY `type` (`type`,`value`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pflv4_file`
--
ALTER TABLE `pflv4_file`
  ADD CONSTRAINT `pflv4_file_ibfk_1` FOREIGN KEY (`hash`) REFERENCES `pflv4_pkg2file` (`fk_file`) ON DELETE CASCADE;

--
-- Constraints for table `pflv4_package`
--
ALTER TABLE `pflv4_package`
  ADD CONSTRAINT `pflv4_package_ibfk_1` FOREIGN KEY (`fk_category`) REFERENCES `pflv4_category` (`hash`) ON DELETE CASCADE;

--
-- Constraints for table `pflv4_package_use`
--
ALTER TABLE `pflv4_package_use`
  ADD CONSTRAINT `pflv4_package_use_ibfk_1` FOREIGN KEY (`fk_package`) REFERENCES `pflv4_package` (`hash`) ON DELETE CASCADE;

--
-- Constraints for table `pflv4_pkg2file`
--
ALTER TABLE `pflv4_pkg2file`
  ADD CONSTRAINT `pflv4_pkg2file_ibfk_1` FOREIGN KEY (`fk_package`) REFERENCES `pflv4_package` (`hash`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
