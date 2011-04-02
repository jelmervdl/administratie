<?php

class IHG_View_Reload implements IHG_View_Interface {
	
	public function is_embedded() {
		return true;
	}
	
	public function draw() {
		if(!headers_sent()) {
			header('Location: ' . $this->_url);
		} else {
			echo '<script>document.location.href = document.location.href + "?nocache=" + (new Date().getTime().toString())</script>';
		}
	}
}