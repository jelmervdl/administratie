<?php

class IHG_View_Redirect implements IHG_View_Interface {
	public function __construct($url) {
		$this->_url = $url;
	}
	
	public function is_embedded() {
		return false;
	}
	
	public function draw() {
		header('Location: ' . $this->_url);
	}
}