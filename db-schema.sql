SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pfl`
--

-- --------------------------------------------------------

--
-- Table structure for table `pfl_category`
--

DROP TABLE IF EXISTS `pfl_category`;
CREATE TABLE `pfl_category` (
  `hash` char(32) NOT NULL,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_file`
--

DROP TABLE IF EXISTS `pfl_file`;
CREATE TABLE `pfl_file` (
  `hash` char(32) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `package_id` char(32) NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_package`
--

DROP TABLE IF EXISTS `pfl_package`;
CREATE TABLE `pfl_package` (
  `hash` char(32) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `version` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `arch` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `category_id` char(32) NOT NULL,
  `lastmodified` datetime NOT NULL DEFAULT current_timestamp(),
  `importcount` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_package_use`
--

DROP TABLE IF EXISTS `pfl_package_use`;
CREATE TABLE `pfl_package_use` (
  `useword` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `package_id` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pfl_statslog`
--

DROP TABLE IF EXISTS `pfl_statslog`;
CREATE TABLE `pfl_statslog` (
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `value` varchar(254) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `timestmp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pfl_category`
--
ALTER TABLE `pfl_category`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `pfl_file`
--
ALTER TABLE `pfl_file`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `name` (`name`),
  ADD KEY `lastmodified` (`lastmodified`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `packageandname` (`package_id`, `name`),
  ADD KEY `packageandpath` (`package_id`, `path`),
  ADD KEY `path` (`path`);

--
-- Indexes for table `pfl_package`
--
ALTER TABLE `pfl_package`
  ADD PRIMARY KEY (`hash`),
  ADD KEY `lastmodified` (`lastmodified`),
  ADD KEY `name` (`name`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `pfl_package_use`
--
ALTER TABLE `pfl_package_use`
  ADD UNIQUE KEY `package_id` (`package_id`,`useword`);

--
-- Indexes for table `pfl_statslog`
--
ALTER TABLE `pfl_statslog`
  ADD KEY `type` (`type`, `value`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
