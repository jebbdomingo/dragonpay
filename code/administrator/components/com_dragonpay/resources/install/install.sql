# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: nucleonplus-db-instance.cjitsulcoyi6.us-west-2.rds.amazonaws.com (MySQL 5.6.27-log)
# Database: nucleonplus
# Generation Time: 2017-04-09 07:00:55 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table #__dragonpay_paymentrates
# ------------------------------------------------------------

DROP TABLE IF EXISTS `#__dragonpay_paymentrates`;

CREATE TABLE `#__dragonpay_paymentrates` (
  `dragonpay_paymentrate_id` int(11) NOT NULL AUTO_INCREMENT,
  `mode` int(11) NOT NULL COMMENT 'Dragonpay mode value, e.g. Online Banking, OTC, etc.',
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`dragonpay_paymentrate_id`),
  KEY `name` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table #__dragonpay_payments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `#__dragonpay_payments`;

CREATE TABLE `#__dragonpay_payments` (
  `dragonpay_payment_id` int(11) NOT NULL,
  `txnid` int(11) NOT NULL,
  `result` varchar(50) NOT NULL,
  `refno` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `digest` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  PRIMARY KEY (`dragonpay_payment_id`),
  UNIQUE KEY `txnid` (`txnid`),
  UNIQUE KEY `dragonpay_payment_id` (`dragonpay_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table #__dragonpay_payouts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `#__dragonpay_payouts`;

CREATE TABLE `#__dragonpay_payouts` (
  `dragonpay_payout_id` int(11) NOT NULL,
  `txnid` int(11) NOT NULL,
  `result` varchar(50) NOT NULL,
  `refno` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `digest` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  PRIMARY KEY (`dragonpay_payout_id`),
  UNIQUE KEY `txnid` (`txnid`),
  UNIQUE KEY `dragonpay_payout_id` (`dragonpay_payout_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
