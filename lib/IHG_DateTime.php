<?php

class IHG_DateTime extends DateTime {

	static public function from_string($timestring) {
		if(trim($timestring) == '') return null;
		
		try {
			return new self($timestring);
		} catch(Exception $e) {
			return null;
		}
	}

	public function __toString() {
		return $this->format('d-m-Y H:i:s');
	}

	public function is_before(DateTime $that) {
		return $this->format('U') < $that->format('U');
	}
	
}

?>