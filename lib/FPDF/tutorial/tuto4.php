<?php
require('../fpdf.php');

class PDF extends FPDF
{
//Huidige Kolom
var $col=0;
//Ordinaat (Y coördinaat) van de startpositie van de kolom
var $y0;

function Header()
{
	//Pagina koptekst
	global $title;

	$this->SetFont('Arial','B',15);
	$w=$this->GetStringWidth($title)+6;
	$this->SetX((210-$w)/2);
	$this->SetDrawColor(0,80,180);
	$this->SetFillColor(230,230,0);
	$this->SetTextColor(220,50,50);
	$this->SetLineWidth(1);
	$this->Cell($w,9,$title,1,1,'C',1);
	$this->Ln(10);
	//Sla de ordinaat op
	$this->y0=$this->GetY();
}

function Footer()
{
	//Pagina voettekst
	$this->SetY(-15);
	$this->SetFont('Arial','I',8);
	$this->SetTextColor(128);
	$this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
}

function SetCol($col)
{
	//Stel de positie in op een gegeven kolom
	$this->col=$col;
	$x=10+$col*65;
	$this->SetLeftMargin($x);
	$this->SetX($x);
}

function AcceptPageBreak()
{
	//Methode die wel of niet de automatische pagina break accepteert
	if($this->col<2)
	{
		//Ga naar volgende kolom
		$this->SetCol($this->col+1);
		//Stel ordinaat in op bovenkant
		$this->SetY($this->y0);
		//Blijf op de pagina
		return false;
	}
	else
	{
		//Ga terug naar eerste kolom
		$this->SetCol(0);
		//Pagina break
		return true;
	}
}

function ChapterTitle($num,$label)
{
	//Titel
	$this->SetFont('Arial','',12);
	$this->SetFillColor(200,220,255);
	$this->Cell(0,6,"Chapter  $num : $label",0,1,'L',1);
	$this->Ln(4);
	//Sla de ordinaat op
	$this->y0=$this->GetY();
}

function ChapterBody($fichier)
{
	//Lees het tekst bestand
	$f=fopen($fichier,'r');
	$txt=fread($f,filesize($fichier));
	fclose($f);
	//Font
	$this->SetFont('Times','',12);
	//Geeft een 6 cm brede kolom weer
	$this->MultiCell(60,5,$txt);
	$this->Ln();
	//Opmerking
	$this->SetFont('','I');
	$this->Cell(0,5,'(end of excerpt)');
	//Ga terug naar eerste kolom
	$this->SetCol(0);
}

function PrintChapter($num,$title,$file)
{
	//Voeg hoofdstuk toe
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
