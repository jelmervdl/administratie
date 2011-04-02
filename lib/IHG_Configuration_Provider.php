<?php

class IHG_Configuration_Provider extends IHG_Component_Abstract {
	
	protected $_configuration = array();
	
	public function __construct(array $data) {
		$this->load($data);
	}
	
	public function __get($key) {
		return $this->_configuration[$key];
	}
	
	public function load(array $data) {
		$this->_configuration = array_merge($this->_configuration, $data);
		
		return $this;
	}
}