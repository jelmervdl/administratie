<?php
require('../fpdf.php');

class PDF extends FPDF
{
var $B;
var $I;
var $U;
var $HREF;

function PDF($orientation='P',$unit='mm',$format='A4')
{
	//Roep de constructor aan
	$this->FPDF($orientation,$unit,$format);
	//Initialisatie
	$this->B=0;
	$this->I=0;
	$this->U=0;
	$this->HREF='';
}

function WriteHTML($html)
{
	//HTML parser
	$html=str_replace("\n",' ',$html);
	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			//Tekst
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			else
				$this->Write(5,$e);
		}
		else
		{
			//Tag
			if($e{0}=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				//Filter de attributen
				$a2=explode(' ',$e);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
					if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

function OpenTag($tag,$attr)
{
	//Open tag
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,true);
	if($tag=='A')
		$this->HREF=$attr['HREF'];
	if($tag=='BR')
		$this->Ln(5);
}

function CloseTag($tag)
{
	//Sluit tag
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF='';
}

function SetStyle($tag,$enable)
{
	//Wijzig stijl en selecteer lettertype
	$this->$tag+=($enable ? 1 : -1);
	$style='';
	foreach(array('B','I','U') as $s)
		if($this->$s>0)
			$style.=$s;
	$this->SetFont('',$style);
}

function PutLink($URL,$txt)
{
	//Plaatst een  hyperlink
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}
}

$html='U kunt nu eenvoudig tekst weergeven met verschillende stijlen: <B>bold
(=vet)</B>, <I>italic (=cursief)</I>, <U>underlined (=onderstreept)</U>, of
<B><I><U>allemaal tegelijk</U></I></B>!<BR>U kunt ook links toevoegen op tekst,
zoals <A HREF="http://www.fpdf.org">www.fpdf.org</A>, of op een afbeelding:
Klik dan op het logo.';

$pdf=new PDF();
//Eerste pagina
$pdf->AddPage();
$pdf->SetFont('Arial','',20);
$pdf->Write(5,'Om uit te zoeken wat er nieuw is aan deze tutorial, klik ');
$pdf->SetFont('','U');
$link=$pdf->AddLink();
$pdf->Write(5,'hier',$link);
$pdf->SetFont('');
//Tweede pagina
$pdf->AddPage();
$pdf->SetLink($link);
$pdf->Image('logo.png',10,10,30,0,'','http://www.fpdf.org');
$pdf->SetLeftMargin(45);
$pdf->SetFontSize(14);
$pdf->WriteHTML($html);
$pdf->Output();
?>
