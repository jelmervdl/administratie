<?php
require('../fpdf.php');

class PDF extends FPDF
{
//Page header
function Header()
{
	//Logo
	$this->Image('logo_pb.png',10,8,33);
	//Arial vet 15
	$this->SetFont('Arial','B',15);
	//Beweeg naar rechts
	$this->Cell(80);
	//Titel
	$this->Cell(30,10,'Title',1,0,'C');
	//Line break
	$this->Ln(20);
}

//Page footer
function Footer()
{
	//Positie 1.5 cm van de onderkant
	$this->SetY(-15);
	//Arial cursief 8
	$this->SetFont('Arial','I',8);
	//Pagina nummer
	$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

//Instanciation of inherited class
$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
for($i=1;$i<=40;$i++)
	$pdf->Cell(0,10,'Regelnummer '.$i,0,1);
$pdf->Output();
?>
