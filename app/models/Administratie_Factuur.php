<?php

class Administratie_Factuur extends Administratie_Record {
	protected function _table_name() {
		return 'Facturen';
	}
	
	protected function _properties() {
		return array(
			'id',
			'bedrijf_id',
			'contactpersoon_id',
			'project_naam',
			'project_beschrijving',
			'verzend_datum',
			'uiterste_betaal_datum',
			'termijn'				=> new IHG_SQL_Atom('DATEDIFF(uiterste_betaal_datum, verzend_datum)'),
			'termijn_resterend'		=> new IHG_SQL_Atom('DATEDIFF(uiterste_betaal_datum, CURDATE())'),
			'voldaan',
			'aangegeven',
			'prijs'					=> new IHG_SQL_Atom('(SELECT SUM(prijs) FROM AankopenOverzicht WHERE AankopenOverzicht.factuur_id = Facturen.id)'),
			'btw'					=> new IHG_SQL_Atom('(SELECT SUM(btw) FROM AankopenOverzicht WHERE AankopenOverzicht.factuur_id = Facturen.id)'),
			'start_tijd'			=> new IHG_SQL_Atom('(SELECT MIN(start_tijd) FROM Uren WHERE Uren.factuur_id = Facturen.id)'),
			'eind_tijd'				=> new IHG_SQL_Atom('(SELECT MAX(eind_tijd) FROM Uren WHERE Uren.factuur_id = Facturen.id)')
		);
	}
	
	protected function _contains_date_indicator($key) {
		
		if($key == 'voldaan' || $key == 'aangegeven')
			return true;
		else
			return parent::_contains_date_indicator($key);
	}
	
	protected function _validate() {
		$errors = array();
		
		if(empty($this->bedrijf_id)) 
			$errors['bedrijf_id'] = 'Er moet een bedrijf aan een factuur gekoppeld zijn';
		
		if(empty($this->contactpersoon_id))
			$errors['contactpersoon_id'] = 'Er moet een ontvanger voor de factuur zijn';
		
		if(empty($this->project_naam))
			$errors['project_naam'] = 'Een projectnaam is verplicht';
			
		if(empty($this->project_beschrijving))
			$errors['project_beschrijving'] = 'Een projectbeschrijving is verplicht';
		
		if(empty($this->verzend_datum))
			$errors['verzend_datum'] = 'Een verzenddatum is noodzakelijk. Vandaag is een goeie keuze, gister was beter';
		
		if(empty($this->uiterste_betaal_datum))
			$errors['uiterste_betaal_datum'] = 'Ze krijgen toch niet eeuwig de tijd?';
		
		if(!empty($this->verzend_datum) && !empty($this->uiterste_betaal_datum) && $this->uiterste_betaal_datum->is_before($this->verzend_datum))
			$errors['uiterste_betaal_datum'] = 'Bij voorbaat te laat. Dat is gemeen!';
		
		return $errors;
	}
	
	public function prijs_incl() {
		return $this->prijs + $this->btw;
	}
	
	public function nummer() {
		return sprintf('%06d', $this->id);
	}
	
	public function aankopen() {
		return self::find_records($this->pdo, 'Administratie_Aankoop', array('factuur_id' => $this->id));
	}
}