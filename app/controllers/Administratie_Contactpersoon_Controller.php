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
	
	public function contactpersoon_toevoegen($bedrijf_id, $contactpersoon_id = null)
	{
		if ($contactpersoon_id)
			$contactpersoon = $this->contactpersonen->find($contactpersoon_id);
		else
			$contactpersoon = $this->contactpersonen->create();
		
		$bedrijf = $this->bedrijven->find($bedrijf_id);
		
		$contactpersoon->bedrijf = $bedrijf;
		
		$view = $this->views->from_file('administratie_contactpersoon_toevoegen');
		
		if ($this->_is_post_request())
		{
			try
			{
				$contactpersoon->voornaam = $_POST['voornaam'];
				$contactpersoon->achternaam = $_POST['achternaam'];
				$contactpersoon->emailadres = $_POST['emailadres'];
				$contactpersoon->straatnaam = $_POST['straatnaam'];
				$contactpersoon->huisnummer = $_POST['huisnummer'];
				$contactpersoon->postcode = $_POST['postcode'];
				$contactpersoon->plaats = $_POST['plaats'];
				$contactpersoon->telefoon = $_POST['telefoon'];
				$contactpersoon->ontvangt_factuur = !empty($_POST['ontvangt_factuur']);
				
				if ( (isset($_POST['delete']) && $contactpersoon->delete())
					|| $contactpersoon->save())
					return $this->views->redirect($_POST['_origin']);
				
			}
			catch(IHG_Record_Exception $e)
			{
				$view->errors = $e->errors;
			}
		}
		
		$view->bedrijf = $bedrijf;
		$view->contactpersoon = $contactpersoon;
		
		return $view;
	}
	
	public function _format_naam($id, $contactpersoon) {
		return sprintf('<a href="%s" class="open-in-sheet">%s %s</a>',
			$this->router->link(__CLASS__, 'contactpersoon_toevoegen', $contactpersoon->bedrijf_id, $id),
			$contactpersoon->voornaam,
			$contactpersoon->achternaam);
	}
	
	public function _format_ontvangt_factuur($ontvangt_factuur) {
		return $ontvangt_factuur ? 'ontvangt factuur' : '';
	}
	
}

?>