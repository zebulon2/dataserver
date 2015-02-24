CREATE TABLE `itemFulltext` (
  `itemID` int(10) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`itemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `itemFulltext`
	ADD CONSTRAINT `itemFulltext_ibfk_1` FOREIGN KEY (`itemID`) REFERENCES `itemAttachments` (`itemID`);

ALTER TABLE `relations`
	DROP COLUMN `serverDateModifiedMS`;

ALTER TABLE `relations`
	ADD COLUMN `key` char(32) CHARACTER SET ascii NOT NULL AFTER `libraryID`;

UPDATE `relations` SET `key`=MD5(CONCAT(`subject`, ' ', `predicate`, ' ', `object`));

ALTER TABLE `relations`
	ADD KEY `subject` (`libraryID`,`subject`);

ALTER TABLE `relations`
	DROP KEY `uniqueRelations`;

ALTER TABLE `relations`
	ADD UNIQUE KEY `uniqueRelations` (`libraryID`, `key`);
