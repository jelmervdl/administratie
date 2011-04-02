<?php

class Administratie_Contactpersoon extends Administratie_Record {
	
	protected function _table_name() {
		return 'Contactpersonen';
	}
	
	protected function _properties() {
		return array(
			'id',
			'bedrijf_id',
			'achternaam',
			'voornaam',
			'straatnaam',
			'huisnummer',
			'postcode',
			'plaats',
			'telefoon',
			'emailadres',
			'ontvangt_factuur',
			'vervangen_door'
		);
	}
	
	public function _validate() {
		$errors = array();
		
		if(empty($this->bedrijf_id))
			$errors['bedrijf_id'] = 'Een contactpersoon moet aan een bedrijf gekoppeld zijn';
		
		if(empty($this->voornaam))
			$errors['voornaam'] = 'Een voornaam, of tenminste een voorletter is nodig';
		
		if(empty($this->achternaam))
			$errors['achternaam'] = 'Enig idee hoeveel mensen deze voornaam delen?';
		
		if($this->ontvangt_factuur):
		
		if(empty($this->straatnaam))
			$errors['straatnaam'] = 'Straatnaam is noodzakelijk voor mensen die een factuur ontvangen';
		
		if(empty($this->postcode))
			$errors['postcode'] = 'postcode is noodzakelijk voor mensen die een factuur ontvangen';
			
		if(empty($this->plaats))
			$errors['plaats'] = 'plaats is noodzakelijk voor mensen die een factuur ontvangen';
		
		endif;
		
		if(empty($this->emailadres))
			$errors['emailadres'] = 'emailadres is noodzakelijk';
			
		if(!empty($this->telefoon))
			$this->telefoon = str_replace(
				array('+', '(', ')', '.', ' ', '-'),
				array('00', '', '',  '',  '',  ''), $this->telefoon);
		
		return $errors;
	}
	
	public function naam() {
		return sprintf('%s %s', $this->voornaam, $this->achternaam);
	}
	
	public function adres() {
		return sprintf("%s\n%s %s\n%s %s", $this->naam(),
			$this->straatnaam, $this->huisnummer,
			$this->postcode, $this->plaats);
	}
	
	public function is_deletable()
	{
		return !!$this->id;
	}
}