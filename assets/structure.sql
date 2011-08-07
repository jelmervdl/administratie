# ************************************************************
# Sequel Pro SQL dump
# Version 3257
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.49-1ubuntu8.1)
# Database: Werk
# Generation Time: 2011-04-02 15:03:11 +0200
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table Aankopen
# ------------------------------------------------------------

CREATE TABLE `Aankopen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bedrijf_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `factuur_id` int(11) DEFAULT NULL,
  `aankoop_datum` date NOT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bedrijf_id` (`bedrijf_id`),
  KEY `product_id` (`product_id`),
  KEY `factuur_id` (`factuur_id`),
  CONSTRAINT `Aankopen_ibfk_1` FOREIGN KEY (`bedrijf_id`) REFERENCES `Bedrijven` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `Aankopen_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `Producten` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `Aankopen_ibfk_3` FOREIGN KEY (`factuur_id`) REFERENCES `Facturen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;



# Dump of table AankopenOverzicht
# ------------------------------------------------------------

CREATE TABLE `AankopenOverzicht` (
   `id` VARBINARY(40) DEFAULT NULL,
   `beschrijving` VARCHAR(255) DEFAULT NULL,
   `aantal` DECIMAL(47) DEFAULT NULL,
   `prijs` DOUBLE DEFAULT NULL,
   `btw` DOUBLE DEFAULT NULL,
   `bedrijf_id` INT(11) NOT NULL DEFAULT '0',
   `factuur_id` INT(11) DEFAULT NULL,
   `deleted` DATETIME DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table Bedrijven
# ------------------------------------------------------------

CREATE TABLE `Bedrijven` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(50) NOT NULL,
  `url` text,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Bedrijven_naam` (`naam`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;



# Dump of table Contactpersonen
# ------------------------------------------------------------

CREATE TABLE `Contactpersonen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bedrijf_id` int(11) DEFAULT NULL,
  `achternaam` varchar(100) DEFAULT NULL,
  `voornaam` varchar(50) DEFAULT NULL,
  `straatnaam` varchar(255) NOT NULL,
  `huisnummer` varchar(5) NOT NULL,
  `postcode` varchar(7) NOT NULL,
  `plaats` varchar(255) NOT NULL,
  `telefoon` varchar(20) DEFAULT NULL,
  `emailadres` varchar(255) NOT NULL,
  `ontvangt_factuur` tinyint(1) NOT NULL,
  `vervangen_door` int(11) DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bedrijf_id` (`bedrijf_id`),
  KEY `vervangen_door` (`vervangen_door`),
  CONSTRAINT `Contactpersonen_ibfk_1` FOREIGN KEY (`bedrijf_id`) REFERENCES `Bedrijven` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `Contactpersonen_ibfk_2` FOREIGN KEY (`vervangen_door`) REFERENCES `Contactpersonen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;



# Dump of table Facturen
# ------------------------------------------------------------

CREATE TABLE `Facturen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bedrijf_id` int(11) NOT NULL DEFAULT '0',
  `contactpersoon_id` int(11) DEFAULT NULL,
  `project_naam` varchar(100) NOT NULL,
  `project_beschrijving` text NOT NULL,
  `verzend_datum` date NOT NULL,
  `uiterste_betaal_datum` date DEFAULT NULL,
  `voldaan` date DEFAULT NULL,
  `aangegeven` date DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bedrijf_id` (`bedrijf_id`),
  KEY `contactpersoon_id` (`contactpersoon_id`),
  CONSTRAINT `Facturen_ibfk_1` FOREIGN KEY (`bedrijf_id`) REFERENCES `Bedrijven` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `Facturen_ibfk_2` FOREIGN KEY (`contactpersoon_id`) REFERENCES `Contactpersonen` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;



# Dump of table FacturenOverzicht
# ------------------------------------------------------------

CREATE TABLE `FacturenOverzicht` (
   `factuur_id` INT(11) NOT NULL DEFAULT '0',
   `bedrijf_id` INT(11) NOT NULL DEFAULT '0',
   `bedrijf_naam` VARCHAR(50) DEFAULT NULL,
   `verzend_datum` DATE NOT NULL,
   `uiterste_betaal_datum` DATE DEFAULT NULL,
   `prijs` DOUBLE DEFAULT NULL,
   `btw` DOUBLE DEFAULT NULL,
   `voldaan` DATE DEFAULT NULL,
   `aangegeven` DATE DEFAULT NULL,
   `deleted` DATETIME DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table OnbetaaldeUren
# ------------------------------------------------------------

CREATE TABLE `OnbetaaldeUren` (
   `id` INT(11) NOT NULL DEFAULT '0',
   `bedrijf_id` INT(11) NOT NULL,
   `factuur_id` INT(11) DEFAULT NULL,
   `start_tijd` DATETIME NOT NULL,
   `eind_tijd` DATETIME DEFAULT NULL,
   `tarief_id` INT(11) NOT NULL DEFAULT '1',
   `aantal` DECIMAL(25) DEFAULT NULL,
   `prijs` DOUBLE DEFAULT NULL,
   `btw` DOUBLE DEFAULT NULL,
   `beschrijving` TEXT DEFAULT NULL,
   `deleted` DATETIME DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table PotentieleFacturen
# ------------------------------------------------------------

CREATE TABLE `PotentieleFacturen` (
   `bedrijf_naam` VARCHAR(50) DEFAULT NULL,
   `aantal` DECIMAL(47) DEFAULT NULL,
   `prijs` DOUBLE DEFAULT NULL,
   `btw` DOUBLE DEFAULT NULL,
   `termijn_start` DATETIME DEFAULT NULL,
   `termijn_eind` DATETIME DEFAULT NULL,
   `deleted` BINARY(0) DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table Producten
# ------------------------------------------------------------

CREATE TABLE `Producten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `beschrijving` varchar(255) NOT NULL,
  `prijs` decimal(6,2) NOT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;



# Dump of table Taken
# ------------------------------------------------------------

CREATE TABLE `Taken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `beschrijving` varchar(255) NOT NULL,
  `notities` text NOT NULL,
  `bedrijfId` int(11) DEFAULT NULL,
  `uurId` int(11) DEFAULT NULL,
  `aanmaakDatum` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bedrijfId` (`bedrijfId`),
  KEY `uurId` (`uurId`),
  CONSTRAINT `Taken_ibfk_1` FOREIGN KEY (`bedrijfId`) REFERENCES `Bedrijven` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Taken_ibfk_2` FOREIGN KEY (`uurId`) REFERENCES `Uren` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;



# Dump of table Tarieven
# ------------------------------------------------------------

CREATE TABLE `Tarieven` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prijs_per_uur` float NOT NULL,
  `naam` varchar(20) NOT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;



# Dump of table Uren
# ------------------------------------------------------------

CREATE TABLE `Uren` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bedrijf_id` int(11) NOT NULL,
  `factuur_id` int(11) DEFAULT NULL,
  `start_tijd` datetime NOT NULL,
  `eind_tijd` datetime DEFAULT NULL,
  `beschrijving` text,
  `tarief_id` int(11) NOT NULL DEFAULT '1',
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bedrijf_id` (`bedrijf_id`),
  KEY `factuur_id` (`factuur_id`),
  KEY `tarief_id` (`tarief_id`),
  CONSTRAINT `Uren_ibfk_4` FOREIGN KEY (`bedrijf_id`) REFERENCES `Bedrijven` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `Uren_ibfk_5` FOREIGN KEY (`factuur_id`) REFERENCES `Facturen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Uren_ibfk_6` FOREIGN KEY (`tarief_id`) REFERENCES `Tarieven` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=utf8;



# Dump of table UrenOverzicht
# ------------------------------------------------------------

CREATE TABLE `UrenOverzicht` (
   `id` INT(11) NOT NULL DEFAULT '0',
   `bedrijf_id` INT(11) NOT NULL,
   `factuur_id` INT(11) DEFAULT NULL,
   `start_tijd` DATETIME NOT NULL,
   `eind_tijd` DATETIME DEFAULT NULL,
   `tarief_id` INT(11) NOT NULL DEFAULT '1',
   `aantal` DECIMAL(25) DEFAULT NULL,
   `prijs` DOUBLE DEFAULT NULL,
   `btw` DOUBLE DEFAULT NULL,
   `beschrijving` TEXT DEFAULT NULL,
   `deleted` DATETIME DEFAULT NULL
) ENGINE=MyISAM;





# Replace placeholder table for PotentieleFacturen with correct view syntax
# ------------------------------------------------------------

DROP TABLE `PotentieleFacturen`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `PotentieleFacturen`
AS select
   `Bedrijven`.`naam` AS `bedrijf_naam`,sum(`OnbetaaldeUren`.`aantal`) AS `aantal`,sum(`OnbetaaldeUren`.`prijs`) AS `prijs`,sum(`OnbetaaldeUren`.`btw`) AS `btw`,min(`OnbetaaldeUren`.`start_tijd`) AS `termijn_start`,max(`OnbetaaldeUren`.`eind_tijd`) AS `termijn_eind`,NULL AS `deleted`
from (`OnbetaaldeUren` left join `Bedrijven` on((`OnbetaaldeUren`.`bedrijf_id` = `Bedrijven`.`id`))) where isnull(`OnbetaaldeUren`.`deleted`) group by `OnbetaaldeUren`.`bedrijf_id`;


# Replace placeholder table for AankopenOverzicht with correct view syntax
# ------------------------------------------------------------

DROP TABLE `AankopenOverzicht`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `AankopenOverzicht`
AS SELECT
   concat(
      _latin1'uur',
      `UrenOverzicht`.`tarief_id`, ':',
      `UrenOverzicht`.`bedrijf_id`, ':',
      if(`UrenOverzicht`.`factuur_id`,
         `UrenOverzicht`.`factuur_id`,
         0
      )
   ) AS `id`,
   cast(
      concat(
         _latin1'Uren ',
         `Tarieven`.`prijs_per_uur`,
         _latin1' euro per uur'
      ) as char charset latin1
   ) AS `beschrijving`,
   sum(`UrenOverzicht`.`aantal`) AS `aantal`,
   sum(`UrenOverzicht`.`prijs`) AS `prijs`,
   sum(`UrenOverzicht`.`btw`) AS `btw`,
   `UrenOverzicht`.`bedrijf_id` AS `bedrijf_id`,
   `UrenOverzicht`.`factuur_id` AS `factuur_id`,
   `UrenOverzicht`.`deleted` AS `deleted`
FROM
   `UrenOverzicht`
RIGHT JOIN `Tarieven` ON
   `UrenOverzicht`.`tarief_id` = `Tarieven`.`id`
GROUP BY
   `UrenOverzicht`.`factuur_id`,
   `UrenOverzicht`.`bedrijf_id`,
   `UrenOverzicht`.`tarief_id`
UNION
SELECT
   concat(
      _latin1'aankoop',
      `Aankopen`.`factuur_id`, ':',
      `Aankopen`.`bedrijf_id`, ':',
      `Aankopen`.`product_id`
   ) AS `id`,
   cast(`Producten`.`beschrijving` as char charset latin1) AS `beschrijving`,
   count(`Aankopen`.`product_id`) AS `aantal`,
   (`Producten`.`prijs` * count(`Aankopen`.`product_id`)) AS `prijs`,
   ((`Producten`.`prijs` * count(`Aankopen`.`product_id`)) * 0.19) AS `btw`,
   `Aankopen`.`bedrijf_id` AS `bedrijf_id`,
   `Aankopen`.`factuur_id` AS `factuur_id`,
   `Aankopen`.`deleted` AS `deleted`
FROM
   `Aankopen`
RIGHT JOIN `Producten` ON
   `Aankopen`.`product_id` = `Producten`.`id`
GROUP BY
   `Aankopen`.`factuur_id`,
   `Aankopen`.`bedrijf_id`,
   `Aankopen`.`product_id`;


# Replace placeholder table for OnbetaaldeUren with correct view syntax
# ------------------------------------------------------------

DROP TABLE `OnbetaaldeUren`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `OnbetaaldeUren`
AS select
   `UrenOverzicht`.`id` AS `id`,
   `UrenOverzicht`.`bedrijf_id` AS `bedrijf_id`,
   `UrenOverzicht`.`factuur_id` AS `factuur_id`,
   `UrenOverzicht`.`start_tijd` AS `start_tijd`,
   `UrenOverzicht`.`eind_tijd` AS `eind_tijd`,
   `UrenOverzicht`.`tarief_id` AS `tarief_id`,
   `UrenOverzicht`.`aantal` AS `aantal`,
   `UrenOverzicht`.`prijs` AS `prijs`,
   `UrenOverzicht`.`btw` AS `btw`,
   `UrenOverzicht`.`beschrijving` AS `beschrijving`,
   `UrenOverzicht`.`deleted` AS `deleted`
from `UrenOverzicht`
where (isnull(`UrenOverzicht`.`factuur_id`) and (`UrenOverzicht`.`prijs` is not null));


# Replace placeholder table for UrenOverzicht with correct view syntax
# ------------------------------------------------------------

DROP TABLE `UrenOverzicht`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `UrenOverzicht`
AS SELECT
   `Uren`.`id` AS `id`,
   `Uren`.`bedrijf_id` AS `bedrijf_id`,
   `Uren`.`factuur_id` AS `factuur_id`,
   `Uren`.`start_tijd` AS `start_tijd`,
   `Uren`.`eind_tijd` AS `eind_tijd`,
   `Uren`.`tarief_id` AS `tarief_id`,
   concat(
      _latin1'uur',
      `Uren`.`tarief_id`,':',
      `Uren`.`bedrijf_id`,':',
      if(`Uren`.`factuur_id`,
         `Uren`.`factuur_id`,
         0
      )
   ) AS `aankopenoverzicht_id`,
   timestampdiff(SECOND,`Uren`.`start_tijd`,`Uren`.`eind_tijd`) / 3600.0 AS `aantal`,
   (timestampdiff(SECOND,`Uren`.`start_tijd`,`Uren`.`eind_tijd`) / 3600.0) * `Tarieven`.`prijs_per_uur` AS `prijs`,
   ((timestampdiff(SECOND,`Uren`.`start_tijd`,`Uren`.`eind_tijd`) / 3600.0) * `Tarieven`.`prijs_per_uur`) * 0.19 AS `btw`,
   `Uren`.`beschrijving` AS `beschrijving`,
   `Uren`.`deleted` AS `deleted`
FROM
   `Uren`
RIGHT JOIN `Tarieven` ON
   `Tarieven`.`id` = `Uren`.`tarief_id`;


# Replace placeholder table for FacturenOverzicht with correct view syntax
# ------------------------------------------------------------

DROP TABLE `FacturenOverzicht`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `FacturenOverzicht`
AS select
   `Facturen`.`id` AS `factuur_id`,
   `Facturen`.`bedrijf_id` AS `bedrijf_id`,
   `Bedrijven`.`naam` AS `bedrijf_naam`,
   `Facturen`.`verzend_datum` AS `verzend_datum`,
   `Facturen`.`uiterste_betaal_datum` AS `uiterste_betaal_datum`,sum(`AankopenOverzicht`.`prijs`) AS `prijs`,sum(`AankopenOverzicht`.`btw`) AS `btw`,
   `Facturen`.`voldaan` AS `voldaan`,
   `Facturen`.`aangegeven` AS `aangegeven`,
   `Facturen`.`deleted` AS `deleted`
from ((`Facturen` left join `Bedrijven` on((`Bedrijven`.`id` = `Facturen`.`bedrijf_id`))) left join `AankopenOverzicht` on((`AankopenOverzicht`.`factuur_id` = `Facturen`.`id`))) group by `Facturen`.`id`;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
