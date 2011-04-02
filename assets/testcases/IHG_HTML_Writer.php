<?php

class IHG_HTML_Writer_TestCase extends UnitTestCase {
	
	protected $writer;
	
	public function setUp() {
		$this->writer = new IHG_HTML_Writer();
	}
	
	public function testList() {
		$this->writer->start_list(IHG_HTML_Writer::ORDERED_LIST);
		
		$this->writer->item();
		
		$this->writer->put("Hello");
		
		$this->writer->item();
		
		$this->writer->put("World");
		
		$this->writer->end_list();
		
		$this->assertEqual($this->writer->stream, '<ol><li>Hello</li><li>World</li></ol>');
	}
	
	public function testChaining() {
		$this->writer->start_list(IHG_HTML_Writer::ORDERED_LIST)
			->item()->put("Hello")
			->item()->put("World")
			->end_list();
			
		$this->assertEqual($this->writer->stream, '<ol><li>Hello</li><li>World</li></ol>');
	}
	
	public function testNestedList() {
		$this->writer
			->start_list(IHG_HTML_Writer::ORDERED_LIST)
				->item()->put("Hello")
				->start_list(IHG_HTML_Writer::ORDERED_LIST)
					->item()->put("Goodbye")
					->item()->put("Doggy")
					->start_list(IHG_HTML_Writer::UNORDERED_LIST)
						->item()->put("Pubbles")
						->item()->put("Adam")
						->item()->put("Eva")
					->end_list()
				->end_list()
				->item()->put("World")
			->end_list();
		
		$this->assertEqual($this->writer->stream, '<ol><li>Hello</li><ol><li>Goodbye</li><li>Doggy</li><ul><li>Pubbles</li><li>Adam</li><li>Eva</li></ul></ol><li>World</li></ol>');
	}
	
	public function testLink() {
		$this->writer
			->link('http://www.google.com')
			->put("Google")
			->end();
		
		$this->assertEqual($this->writer->stream, '<a href="http://www.google.com">Google</a>');
		
		$this->writer->reset();
		
		$this->writer
			->link('http://a.com')
			->put('A')
			->link('http://b.com')
			->put('B')
			->end();
		
		$this->assertEqual($this->writer->stream, '<a href="http://a.com">A</a><a href="http://b.com">B</a>');
	}
	
	public function testLinkList() {
		$this->writer
			->start_list()
			->item()->put("Alfa")
			->item()->link("http://b.com")->put("Beta")
			->item()->put("Gamma")
			->end_list();
			
		$this->assertEqual($this->writer->stream, '<ul><li>Alfa</li><li><a href="http://b.com">Beta</a></li><li>Gamma</li></ul>');
	}
}