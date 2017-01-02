<?php

class Administratie_Tarief extends Administratie_Record
{
	protected function _table_name($query_type)
	{
		return 'Tarieven';
	}
	
	protected function _properties($query_type)
	{
		return array(
			'id',
			'prijs_per_uur',
			'naam'
		);
	}
	
	protected function _validate()
	{
		$errors = array();
		
		if (!ctype_digit($this->prijs_per_uur))
			$errors['prijs_per_uur'] = 'Prijs per uur moet in centen zijn';
			
		if (trim($this->naam) == '') 
			$errors['naam'] = 'Een naam is verplicht';
			
		return $errors;
	}
	
	public function is_deletable()
	{
		return !!$this->id;
	}
}

?>