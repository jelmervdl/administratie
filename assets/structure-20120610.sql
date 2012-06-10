CREATE TABLE `Werken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bedrijf_id` int(11) NOT NULL,
  `naam` varchar(255) NOT NULL DEFAULT '',
  `taakomschrijving` text NOT NULL,
  `budget` decimal(8,2) DEFAULT NULL,
  `deadline` DATE DEFAULT NULL,
  `tarief_id` int(11) DEFAULT NULL,
  `deleted` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bedrijf_id` (`bedrijf_id`),
  KEY `tarief_id` (`tarief_id`),
  CONSTRAINT `werken_ibfk_1` FOREIGN KEY (`bedrijf_id`) REFERENCES `Bedrijven` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `werken_ibfk_2` FOREIGN KEY (`tarief_id`) REFERENCES `Tarieven` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `Uren` ADD `werk_id` INT NULL DEFAULT NULL AFTER `factuur_id`;
ALTER TABLE `Uren` ADD KEY `werk_id` (`werk_id`);
ALTER TABLE `Uren` ADD CONSTRAINT `Uren_ibfk_7` FOREIGN KEY (`werk_id`) REFERENCES `Werken` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

CREATE OR REPLACE VIEW `UrenOverzicht`
AS SELECT
   `Uren`.`id` AS `id`,
   `Uren`.`bedrijf_id` AS `bedrijf_id`,
   `Uren`.`factuur_id` AS `factuur_id`,
   `Uren`.`werk_id` AS `werk_id`,
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


CREATE OR REPLACE VIEW `WerkenOverzicht`
AS SELECT
	w.id,
	w.bedrijf_id,
	w.naam,
	w.taakomschrijving,
	w.budget,
	w.deadline,
	w.tarief_id,
	w.deleted,
	SUM(u_o.prijs) as prijs
FROM
	Werken as w
LEFT JOIN
	UrenOverzicht as u_o ON
	u_o.werk_id = w.id
GROUP BY
	w.id,
	w.bedrijf_id,
	w.naam,
	w.taakomschrijving,
	w.budget,
	w.deadline,
	w.tarief_id,
	w.deleted;
