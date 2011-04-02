<?php
define('FPDF_FONTPATH','./');
require('../fpdf.php');

$pdf=new FPDF();
$pdf->AddFont('Calligrapher','','calligra.php');
$pdf->AddPage();
$pdf->SetFont('Calligrapher','',30);
$pdf->Cell(0,10,'Geniet van nieuwe lettertypes met FPDF!');
$pdf->Output();
?>
