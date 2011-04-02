<?php

class Administratie_Bedrijf_Controller extends IHG_Controller_Abstract {
	
	public function bedrijven() {
		$this->breadcrumbs->add_crumb('Bedrijven');
		
		return $this->views->from_file('administratie_bedrijven');
	}
	
	public function list_bedrijven() {
		return $this->views->from_record($this->bedrijven)
			->set_data($this->bedrijven->find_all()->sort('naam', ORDER_ASC))
			->add_column('naam', 'Naam', array($this, '_format_naam'));
			//->add_column('url', 'Website', array($this, '_format_url'));
	}
	
	public function _format_naam($naam, $bedrijf) {
		return sprintf('<a href="%s">%s</a>',
			$this->router->link(__CLASS__, 'bedrijf', $bedrijf->id),
			$naam);
	}
	
	public function _format_url($url) {
		return sprintf('<a href="%s">%1$s</a>', $url);
	}
	
	public function bedrijf_toevoegen()
	{
		$bedrijf = $this->bedrijven->create();
		
		$view = $this->views->from_file('administratie_bedrijf_toevoegen');
		
		if($this->_is_post_request()) {
			try {
				$bedrijf->naam = $_POST['naam'];
				$bedrijf->url = $_POST['url'];
			
				if($bedrijf->save())
					return $this->views->redirect($_POST['_origin']);
				
			} catch(IHG_Record_Exception $e) {
				$view->errors = $e;
			}
		}
		
		$view->bedrijf = $bedrijf;
		
		return $view;
	}
	
	public function bedrijf($bedrijf_id) {
		
		$bedrijf = $this->bedrijven->find($bedrijf_id);
		
		$this->breadcrumbs->add_crumb($bedrijf->naam);
		
		$view = $this->views->from_file('administratie_bedrijf');
		$view->bedrijf = $bedrijf;
		return $view;
	}
	
}

?>