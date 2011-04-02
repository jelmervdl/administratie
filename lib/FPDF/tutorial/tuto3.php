<?php
require('../fpdf.php');

class PDF extends FPDF
{
function Header()
{
	global $title;

	//Arial vet 15
	$this->SetFont('Arial','B',15);
	//Bereken de breedte van de titel en de positie
	$w=$this->GetStringWidth($title)+6;
	$this->SetX((210-$w)/2);
	//Kleuren van rand, achtergrond en tekst
	$this->SetDrawColor(0,80,180);
	$this->SetFillColor(230,230,0);
	$this->SetTextColor(220,50,50);
	//Dikte van rand (1 mm)
	$this->SetLineWidth(1);
	//Titel
	$this->Cell($w,9,$title,1,1,'C',1);
	//Line break
	$this->Ln(10);
}

function Footer()
{
	//Positie 1.5 cm vanaf onderkant
	$this->SetY(-15);
	//Arial cursief 8
	$this->SetFont('Arial','I',8);
	//Tekstkleur in grijs
	$this->SetTextColor(128);
	//Pagina Nummer
	$this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
}

function ChapterTitle($num,$label)
{
	//Arial 12
	$this->SetFont('Arial','',12);
	//Achtergrond kleur
	$this->SetFillColor(200,220,255);
	//Titel
	$this->Cell(0,6,"Chapter $num : $label",0,1,'L',1);
	//Line break
	$this->Ln(4);
}

function ChapterBody($file)
{
	//Lees tekst bestand
	$f=fopen($file,'r');
	$txt=fread($f,filesize($file));
	fclose($f);
	//Times 12
	$this->SetFont('Times','',12);
	//Geef de uitgevulde tekst weer
	$this->MultiCell(0,5,$txt);
	//Line break
	$this->Ln();
	//Opmerking (cursief)
	$this->SetFont('','I');
	$this->Cell(0,5,'(end of excerpt)');
}

function PrintChapter($num,$title,$file)
{
	$this->AddPage();
	$this->ChapterTitle($num,$title);
	$this->ChapterBody($file);
}
}

$pdf=new PDF();
$title='20000 Leagues Under the Seas';
$pdf->SetTitle($title);
$pdf->SetAuthor('Jules Verne');
$pdf->PrintChapter(1,'A RUNAWAY REEF','20k_c1.txt');
$pdf->PrintChapter(2,'THE PROS AND CONS','20k_c2.txt');
$pdf->Output();
?>
