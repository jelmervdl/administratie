<?php
error_reporting(E_ALL);

require_once 'lib/FPDF/fpdf.php';

function _iso_8859_1($utf8)
{
	return iconv('UTF-8', 'ISO-8859-1', $utf8);
}

define('NL', "\n");

define('FONT_BOLD',		'HelveticaNeueBold');
define('FONT_NORMAL',	'HelveticaNeueLight');
define('FONT_LIGHT',	'HelveticaNeueUltraLight');

/* Einde testdata */

$pdf = new FPDF();
$pdf->setPadding(FPDF::TOP, '3pt');
$pdf->setPadding(FPDF::LEFT, '0pt');
$pdf->setPadding(FPDF::RIGHT, '0pt');


$pdf->addFont(FONT_BOLD,	'',	'HelveticaNeueBold.php');
$pdf->addFont(FONT_NORMAL, 	'', 'HelveticaNeueLight.php');
$pdf->addFont(FONT_LIGHT,	'', 'HelveticaNeueUltraLight.php');
$pdf->setTextColor(0, 0, 0);
//$pdf->setLineWidth(0.00001);
$pdf->setLineWidth(72.0/254);

$pdf->addPage('P');

/* "Factuur" */
$pdf->setFont(FONT_LIGHT, '', 32);
$pdf->setXY(12.6, 43.0);
$pdf->cell(45.2, 11.3, 'Factuur');

/* Logo + naam */
$pdf->image('./data/ikhoefgeen.png', 161.5, 38, 24, 10.44, 'PNG');
$pdf->setFont(FONT_NORMAL, '', 7);
$pdf->setXY(151.7, 46.1);
$pdf->cell(45.9, 4.9, 'Jelmer van der Linde', 0, 0, 'C');

/* Naam, adres, KvK en email-adres */
$lineheight = 3.1;
$breakheight = 3.6;
$pdf->setFont(FONT_NORMAL, '', 7);
$pdf->setLeftMargin(12.6);
$pdf->setXY(12.6, 105.8);
// adresblokje
$pdf->write($lineheight, "Ganzevoortsingel 24d\n9711 AM Groningen");
$pdf->ln($breakheight);
// referentieblokje
$pdf->write($lineheight, "KvK: 01110869\nBTW: 0133.47.408");
$pdf->ln($breakheight);
// telefoonnummer (met bold T)
$pdf->setFont(FONT_BOLD, '', 7);
$pdf->write($lineheight, 'T');
$pdf->setFont(FONT_NORMAL, '', 7);
$pdf->write($lineheight, ' +31 (0)6 248 244 31');
$pdf->ln($breakheight);
// email-adres & link
$pdf->setTextColor(0, 0, 153);
$pdf->write($lineheight, 'jelmer@ikhoefgeen.nl', 'mailto:jelmer@ikhoefgeen.nl');
$pdf->ln();
$pdf->write($lineheight, 'http://ikhoefgeen.nl', 'http://ikhoefgeen.nl');


/* Page content */
$pdf->setLeftMargin(65.0);
$pdf->setRightMargin((595.28 / (72 / 25.4)) - 184.8);
$pdf->setTextColor(0, 0, 0);

/* Factuurnummer */
$pdf->setFont(FONT_NORMAL, '', 9);
$pdf->setXY(65.0, 46.5);
$pdf->write(5, sprintf('Nr. %s', $factuur->nummer));

/* Adres */
$pdf->ln(18.0);
$pdf->write(4.8, _iso_8859_1($bedrijf->naam . NL . 'T.a.v.: ' . $contactpersoon->adres));

$pdf->setXY(65.0, 105.0);
$lineheight = 4.8;

/* Datum */
$pdf->setFont(FONT_BOLD, '', 9);
$pdf->write($lineheight, 'Datum ' . $factuur->verzend_datum->format('d-m-Y'));
$pdf->setFont(FONT_NORMAL, '', 9);
$pdf->ln();

/* Projectnaam & beschrijving */

$pdf->write($lineheight, 'PROJECTNAAM: ' . _iso_8859_1($factuur->project_naam));
$pdf->ln();
$pdf->write($lineheight, 'PROJECTBESCHRIJVING: ' . _iso_8859_1($factuur->project_beschrijving));
$pdf->ln();
$pdf->write($lineheight, 'TERMIJN: ' . $factuur->termijn . ' dagen');

/* Tabel met aankopen */
$pdf->ln(12);

// Tabelbeschrijving
$pdf->setFont(FONT_BOLD, '', 9);
$pdf->cell(86.7, 5.65, 'Item', 	 'TB', 0, 'L');
$pdf->cell(16.4, 5.65, 'Aantal', 'TB', 0, 'R');
$pdf->cell(16.8, 5.65, 'Kosten', 'TB', 0, 'R');
// Tabelitems
$pdf->setFont(FONT_NORMAL, '', 9);
$pdf->ln(6);

foreach($aankopen as $aankoop) {
	//$pdf->cell(86.7, 5.65, 'Werkuren ' . chr(224) . ' 20 euro per uur', 0, 'L');
	$pdf->cell(86.7, 5.65, _iso_8859_1($aankoop->beschrijving), 0, 0, 'L');
    $pdf->cell(16.4, 5.65, number_format($aankoop->aantal, 2),       0, 0, 'R');
    $pdf->cell(16.8, 5.65, number_format($aankoop->prijs, 2, '.', ','),   0, 0, 'R');
	$pdf->ln();
}
// Tabeltotalen
$pdf->setFont(FONT_BOLD, '', 9);
$pdf->cell(86.7, 5.65, 'Totaal excl. BTW', 'TB', 0, 'L');
$pdf->cell(33.2, 5.65, number_format($factuur->prijs, 2, '.', ','), 'TB', 0, 'R');
$pdf->ln();
$pdf->setFont(FONT_NORMAL, '', 9);
$pdf->cell(86.7, 5.65, $factuur->btw_tarief->percentage * 100 . '% BTW over totaalbedrag', 0, 0, 'L');
$pdf->cell(33.2, 5.65, number_format($factuur->btw, 2, '.', ','), 0, 0, 'R');
$pdf->ln();
$pdf->setFont(FONT_BOLD, '', 9);
$pdf->cell(86.7, 5.65, 'Totaal incl. BTW',	'TB', 0, 'L');
$pdf->cell(33.2, 5.65, number_format($factuur->prijs_incl, 2, '.', ','), 'TB', 0, 'R');

// Mededeling
$pdf->setFont(FONT_NORMAL, '', 9);
$pdf->ln(12);
$pdf->write($lineheight, 'Gelieve het bovenstaande bedrag binnen het vastgestelde termijn over te maken naar NL87 INGB 0008 8902 50 op naam van Jelmer van der Linde te Groningen.');
$pdf->ln(); $pdf->ln();
$pdf->write($lineheight, 'En als u ook nog even het factuurnummer bij de omschrijving zou willen vermelden zou dat helemaal te gek zijn.');

header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="' . $factuur->nummer . '.pdf"');
$pdf->output($factuur->nummer . '.pdf', 'I');
