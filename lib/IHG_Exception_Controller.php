<?php
class IHG_Exception_Controller extends IHG_Controller_Abstract {
	
	public function index($e) {
		return $this->views->writer()
			->title('Oops!')
			->put('<pre>' . $e->getMessage() . '</pre>')
			->put('<pre>' . $e->getTraceAsString() . '</pre>');
	}
	
}