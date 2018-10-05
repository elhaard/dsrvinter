/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `baad`
--

DROP TABLE IF EXISTS `baadformand`;
DROP TABLE IF EXISTS `person`;
DROP TABLE IF EXISTS `baad`;
DROP TABLE IF EXISTS `team`;
DROP TABLE IF EXISTS `baadtype`;

--
-- Table structure for table `baadtype`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `baadtype` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(25) COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `team`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_danish_ci DEFAULT NULL,
  `comment` text COLLATE utf8_danish_ci NOT NULL DEFAULT '',
  `period` varchar(255) COLLATE utf8_danish_ci NOT NULL,
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `baad` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `navn` varchar(135) COLLATE utf8_danish_ci NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `team` int(10) unsigned NOT NULL,
  `beskrivelse` text COLLATE utf8_danish_ci NOT NULL,
  `max_timer` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `navn` (`navn`),
  KEY `type` (`type`),
  KEY `team` (`team`),
  CONSTRAINT `baad_ibfk_1` FOREIGN KEY (`type`) REFERENCES `baadtype` (`ID`) ON UPDATE CASCADE,
  CONSTRAINT `baad_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `person`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person` (
  `ID` int(10) unsigned NOT NULL,
  `navn` varchar(255) COLLATE utf8_danish_ci NOT NULL,
  `tlf` varchar(20) COLLATE utf8_danish_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_danish_ci DEFAULT NULL,
  `hours` int(10) unsigned NOT NULL,
  `km` int(10) unsigned NOT NULL,
  `team` int(10) unsigned DEFAULT NULL,
  `email_sent` int(11) NOT NULL DEFAULT '0',
  `kode` varchar(255) COLLATE utf8_danish_ci NOT NULL,
  `is_admin` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `wished_team` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `team` (`team`),
  KEY `person_ibfk_3` (`wished_team`),
  CONSTRAINT `person_ibfk_3` FOREIGN KEY (`wished_team`) REFERENCES `team` (`ID`) ON UPDATE SET NULL ON DELETE SET NULL,
  CONSTRAINT `person_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`ID`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `baadformand`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `baadformand` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team` int(10) unsigned NOT NULL,
  `formand` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`),
  KEY `formand` (`formand`),
  CONSTRAINT `baadformand_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `baadformand_ibfk_2` FOREIGN KEY (`formand`) REFERENCES `person` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `name`, `description`, `content`, `type`) VALUES (1,'forgot_mail','Tekst, der sendes ved glemt password','Kære %navn%\r\n\r\nDu har bedt om at få en ny kode til vintervedligehold.\r\n\r\nDin nye kode er: %kode%\r\n\r\n','text'),(2,'welcome_mail','Invitations-mail','Kære %navn%\r\n\r\nDu kan nu ønske et bådhold til vindervedligehold.\r\n\r\nGå ind på denne side:\r\n\r\n  https://www.nversion.dk/dsrvinter15/\r\n\r\nHer kan du logge ind med dit medlemsnummer og en kode.\r\n\r\nDin personlige kode er: %kode%\r\n\r\n\r\nDer er begrænsede pladser på hvert bådhold, så skynd dig at melde dig til. Sidste frist er på fredag, den 23. oktober. Hvis du ikke har tilmeldt dig inden da, vil vi finde et hold til dig.\r\n\r\nTilmeldingen er en ønskeliste, og materieludvalget forbeholder sig ret til at flytte medlemmer til et andet bådhold.\r\n\r\n\r\nMed venlig hilsen,\r\n\r\nMaterieludvalget','text'),(3,'year','Årstal for vintervedligehold','2016','number'),(4,'welcome_page','Startsiden med login-knap.','Her kan du tilmelde dig vintervedligehold.\r\n\r\nHvis du ikke når at tilmelde dig inden d. 23. oktober, vil du blive placeret på et vilkårligt bådhold.\r\n\r\nFor at logge ind, skal du bruge et særligt password, som du har fået tilsendt pr. mail.','text'),(5,'booking_percentage','Belægningsprocent','80','number');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
