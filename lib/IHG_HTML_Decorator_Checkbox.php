<?php

class IHG_HTML_Decorator_Checkbox {
	
	protected $_name;
	
	protected $_checked;
	
	public function __construct($element_name) {
		$this->_name = $element_name;
	}
	
	public function decorate($property_value, $record) {
		return sprintf('<input type="checkbox" name="%s[]" value="%s"%s>',
			$this->_name,
			$property_value,
			$this->_is_checked($property_value) ? ' checked="checked"' : '');
	}
	
	protected function _is_checked($property_value) {
		if($this->_checked === null) {
			return isset($_POST[$this->_name]) && in_array($property_value, $_POST[$this->_name]);
		} else {
			return $this->_checked;
		}
	}
}