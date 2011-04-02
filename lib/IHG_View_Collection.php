<?php

class IHG_View_Collection implements IHG_View_Interface {
	
	private $_views = array();
	
	public function append_view(IHG_View_Interface $view) {
		$this->_views[] = $view;
	}
	
	public function is_embedded() {
		foreach($this->_views as $view) {
			if(!$view->is_embedded()) return false;
		}
		
		return true;
	}
	
	public function draw() {
		if($this->is_embedded()) {
			foreach($this->_views as $view) {
				$view->draw();
			}
		} else {
			foreach($this->_views as $view) {
				if(!$view->is_embedded()) {
					$view->draw();
					return;
				}
			}
		}
	}
}