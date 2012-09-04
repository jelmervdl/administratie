ALTER TABLE `Aankopen` ADD `aantal` INT NOT NULL DEFAULT 1 AFTER `factuur_id`;

CREATE OR REPLACE VIEW `AankopenOverzicht`
AS SELECT
   concat(
      _latin1'uur',
      `UrenOverzicht`.`tarief_id`, ':',
      `UrenOverzicht`.`bedrijf_id`, ':',
      `UrenOverzicht`.`factuur_id`, ':',
      `UrenOverzicht`.`werk_id`
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
   `UrenOverzicht`.`werk_id`
UNION
SELECT
   concat(
      _latin1'aankoop',
      `Aankopen`.`factuur_id`, ':',
      `Aankopen`.`bedrijf_id`, ':',
      `Aankopen`.`werk_id`, ':',
      `Aankopen`.`product_id`
   ) AS `id`,
   cast(`Producten`.`beschrijving` as char charset latin1) AS `beschrijving`,
   SUM(`Aankopen`.`aantal`) AS `aantal`,
   (`Producten`.`prijs` * SUM(`Aankopen`.`aantal`)) AS `prijs`,
   ((`Producten`.`prijs` * SUM(`Aankopen`.`aantal`)) * 0.19) AS `btw`,
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
   `Aankopen`.`werk_id`;

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
   SUM(a_o.btw) as btw,
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
