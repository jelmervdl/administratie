<?php

include_once 'simpletest/autorun.php';
include_once __DIR__ . '/../../lib/IHG_Formatter_List.php';

class IHG_Formatter_List_TestCase extends UnitTestCase
{
	public function testEmpty()
	{
		$formatter = new IHG_Formatter_List();
		
		$input = "";
		
		$output = "";
		
		$this->assertEqual($formatter($input), $output);
	}
	
	public function testNotAList()
	{
		$formatter = new IHG_Formatter_List();
		
		$input = "Alfa\nBeta\nGamma";
		
		$output = "Alfa\nBeta\nGamma";
		
		$this->assertEqual($formatter($input), $output);
	}
	
	public function testSingleBlock()
	{
		$formatter = new IHG_Formatter_List();
		
		$input = "- Alfa\n- Beta\n- Gamma";
		
		$output = "<ul>\n\t<li>Alfa</li>\n\t<li>Beta</li>\n\t<li>Gamma</li>\n</ul>\n";
		
		$this->assertEqual($formatter($input), $output);
	}
	
	public function testMultipleBlocks()
	{
		$formatter = new IHG_Formatter_List();
		
		$input = "- Alfa\n- Beta\n- Gamma\nEn nog een\n- Alfa\n- Beta\n- Gamma";
		
		$output = "<ul>\n\t<li>Alfa</li>\n\t<li>Beta</li>\n\t<li>Gamma</li>\n</ul>\n"
	            . "En nog een\n"
	            . "<ul>\n\t<li>Alfa</li>\n\t<li>Beta</li>\n\t<li>Gamma</li>\n</ul>\n";
		
		$this->assertEqual($formatter($input), $output);
	}
	
	public function testTrashAtFrontRear()
	{
		$formatter = new IHG_Formatter_List();
		
		$input = "Bada\n\n- Alfa\n- Beta\n- Gamma\n\nBing";
		
		$output = "Bada\n\n<ul>\n\t<li>Alfa</li>\n\t<li>Beta</li>\n\t<li>Gamma</li>\n</ul>\n\nBing";
		
		$this->assertEqual($formatter($input), $output);
	}
	
	public function testChaining()
	{
		$formatter = new IHG_Formatter_List('strrev');

		$input = "- Alfa\n- Beta\n- Gamma";

		$output = "<ul>\n\t<li>aflA</li>\n\t<li>ateB</li>\n\t<li>ammaG</li>\n</ul>\n";

		$this->assertEqual($formatter($input), $output);
	}
}