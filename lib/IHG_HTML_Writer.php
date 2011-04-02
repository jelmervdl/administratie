<?php

class IHG_HTML_Writer implements IHG_View_Interface {
	
	const ORDERED_LIST = 1;
	const UNORDERED_LIST = 2;
	
	protected $_tag_stack = array();
	
	protected $_stream = '';
	
	public function __construct() {
		$this->stream =& $this->_stream;
		
		$this->reset();
	}
	
	protected function _start($tag_name, $tag = null) {
		$this->_stream .= $tag ? $tag : sprintf('<%s>', $tag_name);
		array_push($this->_tag_stack, $tag_name);
	}
	
	protected function _end() {
		$this->_stream .= sprintf('</%s>', array_pop($this->_tag_stack));
	}
	
	protected function _end_till($tag_names, $search_limit = array()) {
		if(!is_array($tag_names)) {
			$tag_names = array($tag_names);
		}
		
		while(($tag = end($this->_tag_stack)) !== false) {
			if(in_array($tag, $search_limit)) {
				break;
			}
			elseif(in_array($tag, $tag_names)) {
				$this->_end();
				break;
			}
			else {
				$this->_end();
			}
		}
	}
	
	public function start_list($type = self::UNORDERED_LIST) {
		
		switch($type) {
			case self::ORDERED_LIST:
				$tag_name = 'ol';
				break;
			case self::UNORDERED_LIST:
			default:
				$tag_name = 'ul';
				break;
		}
		
		$this->_end_till('li', array('ol', 'ul'));
		
		$this->_start($tag_name);
		
		return $this;
	}
	
	public function end_list() {
		$this->_end_till(array('ol', 'ul'));
		
		return $this;
	}
	
	public function item() {
		$this->_end_till('li', array('ul', 'ol'));
		
		$this->_start('li');
		
		return $this;
	}
	
	public function link($url) {
		$this->_end_till('a', array('li'));
		
		$this->_start('a', sprintf('<a href="%s">', $url));
		
		return $this;
	}
	
	public function title($content = null) {
		if($content) {
			$this->title();
			$this->put($content);
			$this->end();
		} else {
			$this->_end_till('h1', array('h2'));
		
			$this->_start('h1');
		}
		
		return $this;
	}
	
	public function section() {
		$this->_end_till('h2', array('h1'));

		$this->_start('h2');
		
		return $this;
	}
	
	public function put($text) {
		$this->_stream .= $text;
		
		return $this;
	}
	
	public function end() {
		$this->_end();
		
		return $this;
	}
	
	public function reset() {
		$this->_tag_stack = array();
		$this->_stream = '';
	}
	
	
	public function draw() {
		echo $this->_stream;
	}
	
	public function is_embedded() {
		return true;
	}
}