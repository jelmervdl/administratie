CREATE TABLE valuta (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL DEFAULT '',
  symbol varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO valuta (name, symbol) VALUES ('Euro', '€'), ('British Pound', '£');

ALTER TABLE Tarieven ADD COLUMN valuta_id int(10) unsigned NOT NULL DEFAULT '1';

ALTER TABLE Tarieven ADD CONSTRAINT Tarieven_ibfk_1 FOREIGN KEY (valuta_id) REFERENCES valuta (id) ON UPDATE CASCADE;

ALTER TABLE Producten ADD COLUMN valuta_id int(10) unsigned NOT NULL DEFAULT '1';

ALTER TABLE Producten ADD CONSTRAINT Producten_ibfk_1 FOREIGN KEY (valuta_id) REFERENCES valuta (id) ON UPDATE CASCADE; 

CREATE OR REPLACE VIEW UrenOverzicht
AS SELECT
   Uren.id AS id,
   Uren.bedrijf_id AS bedrijf_id,
   Uren.factuur_id AS factuur_id,
   Uren.werk_id AS werk_id,
   Uren.start_tijd AS start_tijd,
   Uren.eind_tijd AS eind_tijd,
   Uren.tarief_id AS tarief_id,
   concat(
      'uur',
      Uren.tarief_id,':',
      Uren.bedrijf_id,':',
      COALESCE(Uren.factuur_id, 0)
   ) AS aankopenoverzicht_id,
   timestampdiff(SECOND,Uren.start_tijd,Uren.eind_tijd) / 3600.0 AS aantal,
   (timestampdiff(SECOND,Uren.start_tijd,Uren.eind_tijd) / 3600.0) * Tarieven.prijs_per_uur AS prijs,
   valuta.name as valuta_naam,
   valuta.symbol as valuta_symbool,
   Uren.beschrijving AS beschrijving,
   Uren.deleted AS deleted
FROM
   Uren
LEFT JOIN Tarieven ON
   Tarieven.id = Uren.tarief_id
LEFT JOIN valuta ON
   valuta.id = Tarieven.valuta_id;


CREATE OR REPLACE VIEW OnbetaaldeUren
AS SELECT 
   UrenOverzicht.id AS id,
   UrenOverzicht.bedrijf_id AS bedrijf_id,
   UrenOverzicht.factuur_id AS factuur_id,
   UrenOverzicht.start_tijd AS start_tijd,
   UrenOverzicht.eind_tijd AS eind_tijd,
   UrenOverzicht.tarief_id AS tarief_id,
   UrenOverzicht.aantal AS aantal,
   UrenOverzicht.prijs AS prijs,
   UrenOverzicht.valuta_naam,
   UrenOverzicht.valuta_symbool,
   UrenOverzicht.beschrijving AS beschrijving,
   UrenOverzicht.deleted AS deleted
FROM
    UrenOverzicht
WHERE
  UrenOverzicht.factuur_id IS NULL
  AND UrenOverzicht.prijs IS NOT NULL;


CREATE OR REPLACE VIEW PotentieleFacturen
AS SELECT
   Bedrijven.naam AS bedrijf_naam,
   sum(OnbetaaldeUren.aantal) AS aantal,
   sum(OnbetaaldeUren.prijs) AS prijs,
   OnbetaaldeUren.valuta_naam,
   OnbetaaldeUren.valuta_symbool,
   min(OnbetaaldeUren.start_tijd) AS termijn_start,
   max(OnbetaaldeUren.eind_tijd) AS termijn_eind,
   NULL AS deleted
FROM
  OnbetaaldeUren
LEFT JOIN Bedrijven ON
  OnbetaaldeUren.bedrijf_id = Bedrijven.id
WHERE
  OnbetaaldeUren.deleted IS NULL
GROUP BY
  OnbetaaldeUren.bedrijf_id,
  OnbetaaldeUren.valuta_naam,
  OnbetaaldeUren.valuta_symbool;


CREATE OR REPLACE VIEW AankopenOverzicht
AS SELECT
   concat(
      'uur',
      UrenOverzicht.tarief_id, ':',
      UrenOverzicht.bedrijf_id, ':',
      COALESCE(UrenOverzicht.factuur_id, ''), ':',
      COALESCE(UrenOverzicht.werk_id, '')
   ) AS id,
   cast(
      concat(
         'Uren ',
         Tarieven.prijs_per_uur,
         ' ',
         UrenOverzicht.valuta_naam,
         ' per uur'
      ) as char charset latin1
   ) AS beschrijving,
   sum(UrenOverzicht.aantal) AS aantal,
   sum(UrenOverzicht.prijs) AS prijs,
   UrenOverzicht.valuta_naam,
   UrenOverzicht.valuta_symbool,
   UrenOverzicht.bedrijf_id AS bedrijf_id,
   UrenOverzicht.factuur_id AS factuur_id,
   UrenOverzicht.werk_id AS werk_id,
   UrenOverzicht.deleted AS deleted
FROM
   UrenOverzicht
RIGHT JOIN Tarieven ON
   UrenOverzicht.tarief_id = Tarieven.id
GROUP BY
   UrenOverzicht.factuur_id,
   UrenOverzicht.bedrijf_id,
   UrenOverzicht.tarief_id,
   UrenOverzicht.valuta_naam,
   UrenOverzicht.valuta_symbool,
   Tarieven.prijs_per_uur,
   UrenOverzicht.werk_id,
   UrenOverzicht.deleted
UNION
SELECT
   concat(
      'aankoop',
      Aankopen.product_id, ':',
      Aankopen.bedrijf_id, ':',
      COALESCE(Aankopen.factuur_id, ''), ':',
      COALESCE(Aankopen.werk_id, ''), ':'
   ) AS id,
   Producten.beschrijving,
   SUM(Aankopen.aantal) AS aantal,
   Producten.prijs * SUM(Aankopen.aantal) AS prijs,
   valuta.name as valuta_naam,
   valuta.symbol as valuta_symbool,
   Aankopen.bedrijf_id AS bedrijf_id,
   Aankopen.factuur_id AS factuur_id,
   Aankopen.werk_id as werk_id,
   Aankopen.deleted AS deleted
FROM
   Aankopen
LEFT JOIN Producten ON
   Aankopen.product_id = Producten.id
LEFT JOIN valuta ON
   valuta.id = Producten.valuta_id
GROUP BY
   Aankopen.factuur_id,
   Aankopen.bedrijf_id,
   Aankopen.product_id,
   Aankopen.werk_id,
   Aankopen.deleted,
   Producten.beschrijving,
   Producten.prijs,
   Producten.valuta_id,
   valuta.name,
   valuta.symbol;


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
   Facturen.btw_tarief_id,
   AankopenOverzicht.valuta_naam,
   AankopenOverzicht.valuta_symbool,
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
  Facturen.btw_tarief_id,
  AankopenOverzicht.valuta_naam,
  AankopenOverzicht.valuta_symbool,
  Facturen.voldaan,
  Facturen.aangegeven,
  Facturen.deleted;