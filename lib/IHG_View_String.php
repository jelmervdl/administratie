<?php

class IHG_View_String implements IHG_View_Interface {
	
	static public function from_file(IHG_Application_Abstract $application, $file) {
		return new self(file_get_contents($file));
	}
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function is_embedded() {
		return true;
	}
	
	public function draw() {
		echo $this->data;
	}
}

?>