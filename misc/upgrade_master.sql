UPDATE `fields` SET `fieldName`='versionNumber' WHERE `fieldID`=81;

ALTER TABLE `keys`
	ADD CONSTRAINT `keys_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

ALTER TABLE `syncDownloadQueue`
	ADD COLUMN `params` mediumtext NOT NULL AFTER `version`;
