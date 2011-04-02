<?php
require('../fpdf.php');

$pdf=new FPDF();
$pdf->AddFont('HelveticaNeue', '', 'HelveticaNeue.php');
$pdf->AddPage();
$pdf->SetFont('HelveticaNeue','',32);
$pdf->Cell(40,10,'Hello World!');
$pdf->Output();
?>
