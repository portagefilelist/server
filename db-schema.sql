SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `pflv3_cat2pkg`
--

DROP TABLE IF EXISTS `pflv3_cat2pkg`;
CREATE TABLE `pflv3_cat2pkg` (
  `categoryId` char(32) NOT NULL,
  `packageId` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv3_category`
--

DROP TABLE IF EXISTS `pflv3_category`;
CREATE TABLE `pflv3_category` (
  `hash` char(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv3_file`
--

DROP TABLE IF EXISTS `pflv3_file`;
CREATE TABLE `pflv3_file` (
  `hash` char(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv3_package`
--

DROP TABLE IF EXISTS `pflv3_package`;
CREATE TABLE `pflv3_package` (
  `hash` char(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `version` varchar(32) DEFAULT NULL,
  `arch` varchar(10) DEFAULT NULL,
  `repository` varchar(16) DEFAULT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp(),
  `importcount` int(11) NOT NULL DEFAULT 1,
  `topicality` date DEFAULT NULL,
  `topicalityLastSeen` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv3_package_use`
--

DROP TABLE IF EXISTS `pflv3_package_use`;
CREATE TABLE `pflv3_package_use` (
  `useword` varchar(64) NOT NULL,
  `packageId` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv3_pkg2file`
--

DROP TABLE IF EXISTS `pflv3_pkg2file`;
CREATE TABLE `pflv3_pkg2file` (
  `packageId` char(32) NOT NULL,
  `fileId` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pflv3_statslog`
--

DROP TABLE IF EXISTS `pflv3_statslog`;
CREATE TABLE `pflv3_statslog` (
  `type` varchar(16) NOT NULL,
  `value` varchar(254) NOT NULL,
  `timestmp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pflv3_cat2pkg`
--
ALTER TABLE `pflv3_cat2pkg`
  ADD UNIQUE KEY `categoryIdAndPackageId` (`categoryId`,`packageId`),
  ADD KEY `categoryId` (`categoryId`),
  ADD KEY `packageId` (`packageId`);

--
-- Indexes for table `pflv3_category`
--
ALTER TABLE `pflv3_category`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `pflv3_file`
--
ALTER TABLE `pflv3_file`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`),
  ADD KEY `lastmodified` (`lastmodified`),
  ADD KEY `path` (`path`);

--
-- Indexes for table `pflv3_package`
--
ALTER TABLE `pflv3_package`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `lastmodified` (`lastmodified`),
  ADD KEY `name` (`name`),
  ADD KEY `arch` (`arch`),
  ADD KEY `topicality` (`topicality`);

--
-- Indexes for table `pflv3_package_use`
--
ALTER TABLE `pflv3_package_use`
  ADD UNIQUE KEY `packageIdAndUseword` (`packageId`,`useword`),
  ADD KEY `useword` (`useword`),
  ADD KEY `packageId` (`packageId`);

--
-- Indexes for table `pflv3_pkg2file`
--
ALTER TABLE `pflv3_pkg2file`
  ADD UNIQUE KEY `packageIdAndFileId` (`packageId`,`fileId`),
  ADD KEY `packageId` (`packageId`),
  ADD KEY `fileId` (`fileId`);

--
-- Indexes for table `pflv3_statslog`
--
ALTER TABLE `pflv3_statslog`
  ADD KEY `type` (`type`,`value`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
