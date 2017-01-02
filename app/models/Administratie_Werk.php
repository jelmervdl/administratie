<?php

class Administratie_Werk extends Administratie_Record
{	
	protected function _table_name($query_type)
	{
		return $query_type == self::SELECT_QUERY
			? 'WerkenOverzicht'
			: 'Werken';
	}

	protected function _is_datetime_field($field)
	{
		return $field == 'deadline';
	}
	
	protected function _properties($query_type)
	{
		$props = array(
			'id',
			'bedrijf_id',
			'naam',
			'taakomschrijving',
			'budget',
			'deadline',
			'tarief_id');

		if ($query_type === self::SELECT_QUERY)
			$props['prijs'] = new IHG_SQL_Atom('prijs');

		return $props;
	}
	
	protected function _validate()
	{
		$errors = array();
		
		if (empty($this->bedrijf_id))
			$errors['bedrijf_id'] = 'Er is geen bedrijf aan deze taak gekoppeld';
		
		return $errors;
	}
	
	public function is_deletable()
	{
		// Aleen verwijderdbaar wanneer hij opgeslagen is
		return $this->id;
	}
}
