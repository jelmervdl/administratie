<?php

class Administratie_Uur extends Administratie_Record {
	
	protected function _table_name($query_type) {
		return $query_type == self::SELECT_QUERY
			? 'UrenOverzicht'
			: 'Uren';
	}
	
	protected function _properties() {
		return array(
			'id',
			'bedrijf_id',
			'factuur_id',
			'start_tijd',
			'eind_tijd',
			'beschrijving',
			'tarief_id',
			'aankopenoverzicht_id' => new IHG_SQL_Atom('aankopenoverzicht_id'), // nasty trick to keep this entry out of the update query
			'aantal' => new IHG_SQL_Atom('aantal'),
			'duur'	=> new IHG_SQL_Atom('TIMESTAMPDIFF(SECOND, start_tijd, eind_tijd) / 3600.0'),
			'prijs' => new IHG_SQL_Atom('prijs'),
			'btw' => new IHG_SQL_Atom('btw')
		);
	}
	
	protected function _validate() {
		$errors = array();
		
		if(empty($this->bedrijf_id)) $errors['bedrijf_id'] = 'Er is geen bedrijf aan het uur gekoppeld';
		
		if(!$this->start_tijd instanceof IHG_DateTime)
			$errors['start_tijd'] = 'Er is geen starttijd opgegeven';
		
		if(!$this->eind_tijd instanceof IHG_DateTime)
			$errors['eind_tijd'] = 'Er is geen eindtijd opgegeven';
		
		if($this->start_tijd && $this->eind_tijd && $this->eind_tijd->is_before($this->start_tijd))
			$errors['eind_tijd'] = 'De eindtijd is eerder dan de starttijd';
		
		if(empty($this->tarief_id))
			$errors['tarief_id'] = 'Er is geen tarief opgegeven';
		
		return $errors;
	}
	
	public function is_deletable()
	{
		// Aleen verwijderdbaar wanneer hij opgeslagen is, maar nog niet bij
		// een factuur hoort.
		return $this->id && !$this->factuur_id;
	}
}

?>