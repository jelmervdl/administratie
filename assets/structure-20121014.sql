
CREATE TABLE `btw_tarieven` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `percentage` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `btw_tarieven` (`id`, `percentage`) VALUES (1, 0.19), (2, 0.21);

ALTER TABLE `Facturen` ADD `btw_tarief_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `Facturen` ADD KEY `btw_tarief_id` (`btw_tarief_id`);
ALTER TABLE `Facturen` ADD CONSTRAINT `Facturen_ibfk_3` FOREIGN KEY (`btw_tarief_id`) REFERENCES `btw_tarieven` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

UPDATE `Facturen` SET `btw_tarief_id` = 1 WHERE `btw_tarief_id` IS NULL;

ALTER TABLE `Facturen` CHANGE `btw_tarief_id` `btw_tarief_id` INT(11) NOT NULL DEFAULT 2;

CREATE OR REPLACE VIEW `PotentieleFacturen`
AS SELECT
   `Bedrijven`.`naam` AS `bedrijf_naam`,
   sum(`OnbetaaldeUren`.`aantal`) AS `aantal`,
   sum(`OnbetaaldeUren`.`prijs`) AS `prijs`,
   min(`OnbetaaldeUren`.`start_tijd`) AS `termijn_start`,
   max(`OnbetaaldeUren`.`eind_tijd`) AS `termijn_eind`,
   NULL AS `deleted`
FROM
  `OnbetaaldeUren`
LEFT JOIN `Bedrijven` ON
  `OnbetaaldeUren`.`bedrijf_id` = `Bedrijven`.`id`
WHERE
  `OnbetaaldeUren`.`deleted` IS NULL
GROUP BY
  `OnbetaaldeUren`.`bedrijf_id`;


CREATE OR REPLACE VIEW `OnbetaaldeUren`
AS SELECT 
   `UrenOverzicht`.`id` AS `id`,
   `UrenOverzicht`.`bedrijf_id` AS `bedrijf_id`,
   `UrenOverzicht`.`factuur_id` AS `factuur_id`,
   `UrenOverzicht`.`start_tijd` AS `start_tijd`,
   `UrenOverzicht`.`eind_tijd` AS `eind_tijd`,
   `UrenOverzicht`.`tarief_id` AS `tarief_id`,
   `UrenOverzicht`.`aantal` AS `aantal`,
   `UrenOverzicht`.`prijs` AS `prijs`,
   `UrenOverzicht`.`beschrijving` AS `beschrijving`,
   `UrenOverzicht`.`deleted` AS `deleted`
FROM
    `UrenOverzicht`
WHERE
  `UrenOverzicht`.`factuur_id` IS NULL
  AND `UrenOverzicht`.`prijs` IS NOT NULL;


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
   `Uren`.`beschrijving` AS `beschrijving`,
   `Uren`.`deleted` AS `deleted`
FROM
   `Uren`
RIGHT JOIN `Tarieven` ON
   `Tarieven`.`id` = `Uren`.`tarief_id`;


CREATE OR REPLACE VIEW `AankopenOverzicht`
AS SELECT
   concat(
      _latin1'uur',
      `UrenOverzicht`.`tarief_id`, ':',
      `UrenOverzicht`.`bedrijf_id`, ':',
      COALESCE(`UrenOverzicht`.`factuur_id`, ''), ':',
      COALESCE(`UrenOverzicht`.`werk_id`, '')
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
   `UrenOverzicht`.`bedrijf_id` AS `bedrijf_id`,
   `UrenOverzicht`.`factuur_id` AS `factuur_id`,
   `UrenOverzicht`.`werk_id` AS `werk_id`,
   `UrenOverzicht`.`deleted` AS `deleted`
FROM
   `UrenOverzicht`
RIGHT JOIN `Tarieven` ON
   `UrenOverzicht`.`tarief_id` = `Tarieven`.`id`
GROUP BY
   `UrenOverzicht`.`factuur_id`,
   `UrenOverzicht`.`bedrijf_id`,
   `UrenOverzicht`.`tarief_id`,
   `Tarieven`.`prijs_per_uur`,
   `UrenOverzicht`.`werk_id`,
   `UrenOverzicht`.`deleted`
UNION
SELECT
   concat(
      _latin1'aankoop',
      `Aankopen`.`product_id`, ':',
      `Aankopen`.`bedrijf_id`, ':',
      COALESCE(`Aankopen`.`factuur_id`, ''), ':',
      COALESCE(`Aankopen`.`werk_id`, ''), ':'
   ) AS `id`,
   cast(`Producten`.`beschrijving` as char charset latin1) AS `beschrijving`,
   SUM(`Aankopen`.`aantal`) AS `aantal`,
   (`Producten`.`prijs` * SUM(`Aankopen`.`aantal`)) AS `prijs`,
   `Aankopen`.`bedrijf_id` AS `bedrijf_id`,
   `Aankopen`.`factuur_id` AS `factuur_id`,
   `Aankopen`.`werk_id` as `werk_id`,
   `Aankopen`.`deleted` AS `deleted`
FROM
   `Aankopen`
RIGHT JOIN `Producten` ON
   `Aankopen`.`product_id` = `Producten`.`id`
GROUP BY
   `Aankopen`.`factuur_id`,
   `Aankopen`.`bedrijf_id`,
   `Aankopen`.`product_id`,
   `Aankopen`.`werk_id`,
   `Aankopen`.`deleted`,
   `Producten`.`beschrijving`,
   `Producten`.`prijs`;


CREATE OR REPLACE VIEW FacturenOverzicht
AS SELECT
   Facturen.id,
   Facturen.bedrijf_id,
   Facturen.contactpersoon_id,
   Facturen.project_naam,
   Facturen.project_beschrijving,
   Bedrijven.naam AS bedrijf_naam,
   Facturen.verzend_datum AS verzend_datum,
   Facturen.uiterste_betaal_datum AS uiterste_betaal_datum,
   sum(AankopenOverzicht.prijs) AS prijs,
   sum(AankopenOverzicht.prijs) * btw_tarieven.percentage AS btw,
   Facturen.voldaan,
   Facturen.aangegeven,
   Facturen.deleted
FROM
  Facturen
INNER JOIN btw_tarieven ON
  btw_tarieven.id = Facturen.btw_tarief_id
INNER JOIN Bedrijven ON
  Bedrijven.id = Facturen.bedrijf_id
LEFT JOIN AankopenOverzicht ON
  AankopenOverzicht.factuur_id = Facturen.id
GROUP BY
  Facturen.id,
  Facturen.bedrijf_id,
  Facturen.contactpersoon_id,
  Facturen.project_naam,
  Facturen.project_beschrijving, 
  Bedrijven.naam,
  Facturen.verzend_datum,
  Facturen.uiterste_betaal_datum,
  Facturen.voldaan,
  Facturen.aangegeven,
  Facturen.deleted;


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
   SUM(a_o.prijs) as prijs,
   w.budget - SUM(a_o.prijs) as beschikbaar
FROM
   Werken as w
LEFT JOIN
   AankopenOverzicht as a_o ON
   a_o.werk_id = w.id
GROUP BY
   w.id,
   w.bedrijf_id,
   w.naam,
   w.taakomschrijving,
   w.budget,
   w.deadline,
   w.tarief_id,
   w.deleted;
