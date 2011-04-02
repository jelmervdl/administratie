<?php

class Administratie_Contactpersoon_Controller extends IHG_Controller_Abstract {
	
	public function list_contactpersonen($bedrijf_id = null) {
		$contactpersonen = $this->contactpersonen->find_all($bedrijf_id ? array('bedrijf_id' => $bedrijf_id) : array());
		
		return $this->views->from_record($this->contactpersonen)
			->set_data($contactpersonen)
			->add_column('id', 'Naam', array($this, '_format_naam'))
			->add_column('emailadres', 'Email')
			->add_column('ontvangt_factuur', '', array($this, '_format_ontvangt_factuur'));
	}
	
	public function contactpersoon_toevoegen($contactpersoon_id = null, $bedrijf_id) {
		$contactpersoon = $this->contactpersonen->create();
		
		$bedrijf = $this->bedrijven->find($bedrijf_id);
		
		$contactpersoon->bedrijf = $bedrijf;
		
		$view = $this->views->from_file('administratie_contactpersoon_toevoegen');
		
		if($this->_is_post_request()) {
			try {
				$contactpersoon->voornaam = $_POST['voornaam'];
				$contactpersoon->achternaam = $_POST['achternaam'];
				$contactpersoon->emailadres = $_POST['emailadres'];
				$contactpersoon->straatnaam = $_POST['straatnaam'];
				$contactpersoon->huisnummer = $_POST['huisnummer'];
				$contactpersoon->postcode = $_POST['postcode'];
				$contactpersoon->plaats = $_POST['plaats'];
				$contactpersoon->telefoon = $_POST['telefoon'];
				$contactpersoon->ontvangt_factuur = !empty($_POST['ontvangt_factuur']);
				
				if($contactpersoon->save()) {
					$view = $this->views->reload();
					$contactpersoon = $this->contactpersonen->create();
				}
			} catch(IHG_Record_Exception $e) {
				$view->errors = $e->errors;
			}
		}
		
		$view->contactpersoon = $contactpersoon;
		
		return $view;
	}
	
	public function _format_naam($id, $contactpersoon) {
		return sprintf('<a href="%s">%s %s</a>',
			$this->router->link(__CLASS__, 'contactpersoon', $id),
			$contactpersoon->voornaam,
			$contactpersoon->achternaam);
	}
	
	public function _format_ontvangt_factuur($ontvangt_factuur) {
		return $ontvangt_factuur ? 'ontvangt factuur' : '';
	}
	
}

?>