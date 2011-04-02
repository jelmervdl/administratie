<?php

class IHG_View_JSON implements IHG_View_Interface {
	
	private $_data;
	
	public function __construct($data) {
		$this->_data = $data;
	}
	
	public function is_embedded() {
		return false;
	}
	
	public function draw() {
		echo json_encode($this->_data);
	}
	
}