<?php
require('../fpdf.php');

class PDF extends FPDF
{
//Laad gegevens
function LoadData($file)
{
	//Lees regel nummers
	$lines=file($file);
	$data=array();
	foreach($lines as $line)
		$data[]=explode(';',chop($line));
	return $data;
}

//Eenvoudige tabel
function BasicTable($header,$data)
{
	//Koptekst
	foreach($header as $col)
		$this->Cell(40,7,$col,1);
	$this->Ln();
	//Gegevens
	foreach($data as $row)
	{
		foreach($row as $col)
			$this->Cell(40,6,$col,1);
		$this->Ln();
	}
}

//Betere tabel
function ImprovedTable($header,$data)
{
	//Kolom breedtes
	$w=array(40,35,40,45);
	//Koptekst
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,$header[$i],1,0,'C');
	$this->Ln();
	//Gegevens
	foreach($data as $row)
	{
		$this->Cell($w[0],6,$row[0],'LR');
		$this->Cell($w[1],6,$row[1],'LR');
		$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
		$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
		$this->Ln();
	}
	//Regel die de tabel afsluit
	$this->Cell(array_sum($w),0,'','T');
}

//Gekleurde tabel
function FancyTable($header,$data)
{
	//Kleuren, lijn dikte en vet lettertype
	$this->SetFillColor(255,0,0);
	$this->SetTextColor(255);
	$this->SetDrawColor(128,0,0);
	$this->SetLineWidth(.3);
	$this->SetFont('','B');
	//Koptekst
	$w=array(40,35,40,45);
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,$header[$i],1,0,'C',1);
	$this->Ln();
	//Herstel van kleuren en lettertype
	$this->SetFillColor(224,235,255);
	$this->SetTextColor(0);
	$this->SetFont('');
	//Gegevens
	$fill=0;
	foreach($data as $row)
	{
		$this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
		$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
		$this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
		$this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
		$this->Ln();
		$fill=!$fill;
	}
	$this->Cell(array_sum($w),0,'','T');
}
}

$pdf=new PDF();
//Kolom titels
$header=array('Country','Capital','Area (sq km)','Pop. (thousands)');
//Gegevens laden
$data=$pdf->LoadData('countries.txt');
$pdf->SetFont('Arial','',14);
$pdf->AddPage();
$pdf->BasicTable($header,$data);
$pdf->AddPage();
$pdf->ImprovedTable($header,$data);
$pdf->AddPage();
$pdf->FancyTable($header,$data);
$pdf->Output();
?>
