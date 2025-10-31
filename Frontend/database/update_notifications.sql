-- Add ExchangeID column to notifications table to link notifications to swap exchanges
ALTER TABLE `notifications` ADD COLUMN `ExchangeID` INT(11) DEFAULT NULL AFTER `UserID`;
ALTER TABLE `notifications` ADD CONSTRAINT `fk_notifications_exchange` FOREIGN KEY (`ExchangeID`) REFERENCES `exchange` (`ExchangeID`) ON DELETE CASCADE;
