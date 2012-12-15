ALTER TABLE btw_tarieven ADD deleted DATETIME NULL DEFAULT NULL AFTER percentage;

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
  Facturen.voldaan,
  Facturen.aangegeven,
  Facturen.deleted;