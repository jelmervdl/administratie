<?php

class Administratie_Factuur extends Administratie_Record
{
	private $_uren = array();
	
	protected function _table_name($mode)
	{
		if ($mode == self::SELECT_QUERY)
			return 'FacturenOverzicht';
		else
			return 'Facturen';
	}
	
	protected function _properties($query_type)
	{
		$props = array(
			'id',
			'bedrijf_id',
			'contactpersoon_id',
			'project_naam',
			'project_beschrijving',
			'verzend_datum',
			'uiterste_betaal_datum',
			'voldaan',
			'aangegeven',
			'btw_tarief_id'
		);

		if ($query_type == self::SELECT_QUERY) {
			$props = array_merge($props, [
				'termijn' => new IHG_SQL_Atom('DATEDIFF(uiterste_betaal_datum, verzend_datum)', [], ['uiterste_betaal_datum', 'verzend_datum']),
				'termijn_resterend' => new IHG_SQL_Atom('DATEDIFF(uiterste_betaal_datum, CURDATE())', [], ['uiterste_betaal_datum']),
				'prijs',
				'btw',
				'valuta_naam',
				'valuta_symbool',
				'start_tijd' => new IHG_SQL_Atom('(SELECT MIN(start_tijd) FROM Uren WHERE Uren.factuur_id = id)', [], ['id']),
				'eind_tijd' => new IHG_SQL_Atom('(SELECT MAX(eind_tijd) FROM Uren WHERE Uren.factuur_id = id)', [], ['id'])
			]);
		}

		return $props;
	}
	
	protected function _is_datetime_field($key)
	{
		return $key == 'voldaan' || $key == 'aangegeven' || parent::_is_datetime_field($key);
	}
	
	protected function _validate()
	{
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
	
	public function prijs_incl()
	{
		return $this->prijs + $this->btw;
	}
	
	public function nummer()
	{
		return sprintf('%06d', $this->id);
	}
	
	public function uren()
	{
		return self::find_records($this->pdo, 'Administratie_Uur', array('factuur_id' => $this->id));
	}
	
	public function aankopen()
	{
		return self::find_records($this->pdo, 'Administratie_Aankoop', array('factuur_id' => $this->id));
	}
	
	public function is_deletable()
	{
		return $this->id && !$this->voldaan;
	}
	
	public function add_uur(Administratie_Uur $uur)
	{
		if ($uur->factuur_id && $uur->factuur_id != $this->id)
			throw new Exception('Je kan niet een uur van een ander factuur aan dit factuur koppelen');
		
		$this->_uren[] = $uur;
	}
	
	public function save()
	{
		if (!parent::save())
			return false;
		
		foreach ($this->_uren as $uur)
		{
			$uur->factuur_id = $this->id;
			$uur->save();
		}
		
		return true;
	}
	
	public function delete()
	{
		foreach ($this->uren() as $uur)
		{
			$uur->factuur_id = null;
			$uur->save();
		}
		
		return parent::delete();
	}
}