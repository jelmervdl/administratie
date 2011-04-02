<?php

class Administratie_Index_Controller extends IHG_Controller_Abstract {
	
	public function index() {
		
		$this->breadcrumbs->add_crumb('Overzicht');
		
		return $this->views->from_file('administratie_index');
	}
	
}