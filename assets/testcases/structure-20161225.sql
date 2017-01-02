CREATE OR REPLACE VIEW AankopenOverzicht
AS SELECT
   concat(
      'uur',
      UrenOverzicht.tarief_id, ':',
      UrenOverzicht.bedrijf_id, ':',
      UrenOverzicht.factuur_id, ':',
      UrenOverzicht.werk_id
   ) AS id,
   concat(
      'Uren ',
      Tarieven.prijs_per_uur,
      ' euro per uur'
   ) AS beschrijving,
   sum(UrenOverzicht.aantal) AS aantal,
   sum(UrenOverzicht.prijs) AS prijs,
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
   UrenOverzicht.werk_id,
   UrenOverzicht.deleted,
   Tarieven.prijs_per_uur
UNION
SELECT
   concat(
      'aankoop',
      Aankopen.factuur_id, ':',
      Aankopen.bedrijf_id, ':',
      Aankopen.werk_id, ':',
      Aankopen.product_id
   ) AS id,
   Producten.beschrijving AS beschrijving,
   SUM(Aankopen.aantal) AS aantal,
   (Producten.prijs * SUM(Aankopen.aantal)) AS prijs,
   Aankopen.bedrijf_id AS bedrijf_id,
   Aankopen.factuur_id AS factuur_id,
   Aankopen.werk_id as werk_id,
   Aankopen.deleted AS deleted
FROM
   Aankopen
RIGHT JOIN Producten ON
   Aankopen.product_id = Producten.id
GROUP BY
   Aankopen.factuur_id,
   Aankopen.bedrijf_id,
   Aankopen.product_id,
   Aankopen.werk_id,
   Aankopen.deleted,
   Producten.beschrijving,
   Producten.prijs;
