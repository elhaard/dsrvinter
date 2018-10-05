DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_danish_ci DEFAULT NULL,
  `comment` text COLLATE utf8_danish_ci NOT NULL DEFAULT '',
  `period` varchar(255) COLLATE utf8_danish_ci NOT NULL,
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;


ALTER TABLE `baad` ADD COLUMN team int(10) unsigned NULL;

INSERT INTO team (ID, period, hidden)
  SELECT ID, periode, hidden FROM baad;

UPDATE baad SET team = ID;

ALTER TABLE `baad`
  DROP COLUMN periode,
  DROP COLUMN hidden,
  MODIFY COLUMN team int(10) unsigned NOT NULL,
  ADD KEY `team` (`team`),
  ADD CONSTRAINT `baad_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`ID`) ON UPDATE CASCADE;


ALTER TABLE `person`
  DROP FOREIGN KEY `person_ibfk_2`,
  DROP FOREIGN KEY `person_ibfk_3`,
  DROP KEY `baad`,
  CHANGE COLUMN `baad` `team` int(10) unsigned DEFAULT NULL,
  CHANGE COLUMN `wished_boat`  `wished_team` int(10) unsigned DEFAULT NULL,
  ADD KEY `team` (`team`);

ALTER TABLE `person`
  ADD CONSTRAINT `person_ibfk_3` FOREIGN KEY (`wished_team`) REFERENCES `team` (`ID`) ON UPDATE SET NULL ON DELETE SET NULL,
  ADD CONSTRAINT `person_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`ID`) ON UPDATE CASCADE ON DELETE SET NULL;


ALTER TABLE `baadformand`
  DROP FOREIGN KEY `baadformand_ibfk_1`,
  DROP KEY `baad`,
  CHANGE COLUMN `baad` `team` int(10) unsigned NOT NULL,
  ADD KEY `team` (`team`);

ALTER TABLE `baadformand`
  ADD CONSTRAINT `baadformand_ibfk_1` FOREIGN KEY (`team`) REFERENCES `team` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
